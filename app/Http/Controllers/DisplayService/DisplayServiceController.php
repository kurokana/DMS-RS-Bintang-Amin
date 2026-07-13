<?php

namespace App\Http\Controllers\DisplayService;

use App\Http\Controllers\Controller;
use App\Models\DisplayDevice;
use App\Models\OperatingRoom;
use App\Models\SurgerySchedule;
use App\Models\WardClass;
use App\Services\DisplayDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisplayServiceController extends Controller
{
    protected DisplayDeviceService $deviceService;

    public function __construct(DisplayDeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Get snapshot state for an STB display device.
     */
    public function state(string $displayId): JsonResponse
    {
        $device = DisplayDevice::where('display_id', $displayId)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DISPLAY_NOT_FOUND',
                    'message' => 'Display device tidak terdaftar.'
                ]
            ], 404);
        }

        // Record heartbeat (Req 4.5 - update online status)
        $this->deviceService->recordHeartbeat($displayId);

        // Fetch current mapping
        $mapping = $device->mappings()->latest('effective_at')->first();

        if (!$mapping) {
            return response()->json([
                'success' => true,
                'data' => [
                    'display_id' => $device->display_id,
                    'name' => $device->name,
                    'mapped' => false,
                ]
            ]);
        }

        $stateData = [
            'display_id' => $device->display_id,
            'name' => $device->name,
            'mapped' => true,
            'target_type' => $mapping->target_type,
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
                        'patient_name' => $this->maskPatientName($sch->patient_name), // Req 2.6 - penyamaran nama
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
        }

        return response()->json([
            'success' => true,
            'data' => $stateData
        ]);
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
