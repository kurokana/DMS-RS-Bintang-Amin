<?php

namespace App\Http\Controllers\DisplayService;

use App\Http\Controllers\Controller;
use App\Models\DisplayDevice;
use App\Models\OperatingRoom;
use App\Models\SurgerySchedule;
use App\Models\WardClass;
use App\Models\InpatientRoom;
use App\Models\Polyclinic;
use App\Models\PolyclinicDoctor;
use App\Models\PolyclinicQueue;
use App\Services\DisplayDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DisplayServiceController extends Controller
{
    protected DisplayDeviceService $deviceService;

    public function __construct(DisplayDeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Get snapshot state for an STB display device.
     * Implements continuous caching & offline fallback resilience.
     */
    public function state(string $displayId): JsonResponse
    {
        $cacheKey = "display_state_{$displayId}";

        try {
            $device = DisplayDevice::where('display_id', $displayId)->first();

            if (!$device) {
                if (Cache::has($cacheKey)) {
                    return response()->json(Cache::get($cacheKey));
                }

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DISPLAY_NOT_FOUND',
                        'message' => 'Display device tidak terdaftar.'
                    ]
                ], 404);
            }

            // Record heartbeat safely
            try {
                $this->deviceService->recordHeartbeat($displayId);
            } catch (\Throwable $e) {
                // Ignore transient heartbeat recording errors
            }

            // Fetch current mapping
            $mapping = $device->mappings()->latest('effective_at')->first();

            if (!$mapping) {
                $unmappedPayload = [
                    'success' => true,
                    'data' => [
                        'display_id' => $device->display_id,
                        'name' => $device->name,
                        'mapped' => false,
                    ]
                ];
                Cache::put($cacheKey, $unmappedPayload, now()->addDays(30));
                return response()->json($unmappedPayload);
            }

            $stateData = [
                'display_id' => $device->display_id,
                'name' => $device->name,
                'mapped' => true,
                'target_type' => $mapping->target_type,
                'target_id' => $mapping->target_id,
            ];

            if ($mapping->target_type === 'ward_class') {
                $wardClass = WardClass::with(['currentAvailability'])->find($mapping->target_id);
                if ($wardClass) {
                    $availability = $wardClass->currentAvailability;
                    $stateData['content'] = [
                        'bpjs_class_code' => $wardClass->bpjs_class_code,
                        'class_name' => $wardClass->name,
                        'bed_total' => $availability?->bed_total ?? 0,
                        'bed_occupied' => $availability?->bed_occupied ?? 0,
                        'bed_available' => $availability?->bed_available ?? 0,
                        'synced_at' => $wardClass->synced_at?->toIso8601String(),
                    ];
                }
            } elseif ($mapping->target_type === 'operating_room') {
                $room = OperatingRoom::find($mapping->target_id);
                if ($room) {
                    // Fetch schedules
                    $schedules = SurgerySchedule::where('operating_room_id', $room->id)
                        ->orderBy('scheduled_start_at')
                        ->get()
                        ->map(fn($sch) => [
                            'bpjs_schedule_id' => $sch->bpjs_schedule_id,
                            'patient_name' => $this->maskPatientName($sch->patient_name),
                            'scheduled_start_at' => $sch->scheduled_start_at->toIso8601String(),
                            'actual_start_at' => $sch->actual_start_at?->toIso8601String(),
                            'status' => $sch->status,
                        ])
                        ->toArray();

                    $stateData['content'] = [
                        'bpjs_or_code' => $room->bpjs_or_code,
                        'room_name' => $room->name,
                        'schedules' => $schedules,
                        'synced_at' => $room->synced_at?->toIso8601String(),
                    ];
                }
            } elseif ($mapping->target_type === 'ward_summary') {
                $wards = WardClass::with(['currentAvailability'])->get();
                $summaryData = [];
                
                foreach ($wards as $wardClass) {
                    $availability = $wardClass->currentAvailability;
                    $summaryData[] = [
                        'bpjs_class_code' => $wardClass->bpjs_class_code,
                        'class_name' => $wardClass->name,
                        'bed_total' => $availability?->bed_total ?? 0,
                        'bed_occupied' => $availability?->bed_occupied ?? 0,
                        'bed_available' => $availability?->bed_available ?? 0,
                        'synced_at' => $wardClass->synced_at?->toIso8601String(),
                    ];
                }

                $stateData['content'] = $summaryData;
            } elseif ($mapping->target_type === 'inpatient_room') {
                $inpatientRoom = InpatientRoom::find($mapping->target_id);
                if ($inpatientRoom) {
                    $patientsData = is_array($inpatientRoom->patients) 
                        ? $inpatientRoom->patients 
                        : (json_decode($inpatientRoom->patients ?? '[]', true) ?: []);

                    $stateData['content'] = [
                        'id' => $inpatientRoom->id,
                        'room_code' => $inpatientRoom->room_code,
                        'name' => $inpatientRoom->name,
                        'floor' => $inpatientRoom->floor,
                        'building' => $inpatientRoom->building,
                        'bed_total' => $inpatientRoom->bed_total,
                        'bed_occupied' => $inpatientRoom->bed_occupied,
                        'bed_available' => $inpatientRoom->bed_available,
                        'patients' => $patientsData,
                        'updated_at' => $inpatientRoom->updated_at?->toIso8601String(),
                    ];
                }
            } elseif ($mapping->target_type === 'polyclinic') {
                $polyclinic = Polyclinic::with([
                    'doctors' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'),
                    'doctors.todayQueue',
                ])->find($mapping->target_id);

                if ($polyclinic) {
                    $stateData['content'] = [
                        'polyclinic_code' => $polyclinic->code,
                        'polyclinic_name' => $polyclinic->name,
                        'doctors' => $polyclinic->doctors->map(fn(PolyclinicDoctor $doc) => [
                            'id' => $doc->id,
                            'name' => $doc->name,
                            'photo_url' => $doc->photo_url,
                            'specialty' => $doc->specialty,
                            'queue' => $doc->todayQueue->map(fn(PolyclinicQueue $q) => [
                                'queue_number' => $q->queue_number,
                                'patient_name' => $q->patient_name,
                                'status' => $q->status,
                                'called_at' => $q->called_at?->toIso8601String(),
                            ])->toArray(),
                        ])->toArray(),
                    ];
                }
            }

            $responsePayload = [
                'success' => true,
                'data' => $stateData
            ];

            // Cache state payload for 30 days so STBs remain 100% resilient if backoffice/SIMRS/BPJS go down
            Cache::put($cacheKey, $responsePayload, now()->addDays(30));

            return response()->json($responsePayload);

        } catch (\Throwable $e) {
            // Offline Fallback: If DB query or external API fails, serve cached state if available
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVICE_UNAVAILABLE',
                    'message' => 'Layanan middleware sedang mengalami kendala jaringan dan belum ada data cache.',
                    'details' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Heartbeat keep-alive endpoint for STB.
     */
    public function heartbeat(string $displayId): JsonResponse
    {
        try {
            $device = $this->deviceService->recordHeartbeat($displayId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $device->status,
                    'last_heartbeat_at' => $device->last_heartbeat_at->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DISPLAY_NOT_FOUND',
                    'message' => 'Display device tidak terdaftar.'
                ]
            ], 404);
        }
    }

    /**
     * Anonymize patient names for public view (Req 2.6).
     * Example: "Ahmad Fauzi" -> "A***d F***i"
     */
    protected function maskPatientName(string $name): string
    {
        $parts = explode(' ', $name);
        $maskedParts = array_map(function ($part) {
            $len = strlen($part);
            if ($len <= 2) {
                return $part;
            }
            return substr($part, 0, 1) . str_repeat('*', $len - 2) . substr($part, -1);
        }, $parts);

        return implode(' ', $maskedParts);
    }
}
