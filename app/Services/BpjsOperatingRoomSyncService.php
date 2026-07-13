<?php

namespace App\Services;

use App\Contracts\BpjsOperatingRoomAdapterInterface;
use App\Events\OperatingRoomStatusChanged;
use App\Models\DisplayMapping;
use App\Models\OperatingRoom;
use App\Models\SurgerySchedule;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BpjsOperatingRoomSyncService
{
    protected BpjsOperatingRoomAdapterInterface $adapter;

    public function __construct(BpjsOperatingRoomAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Synchronize operating room schedules from BPJS to local cache.
     */
    public function sync(): void
    {
        $syncedAt = now();

        try {
            $data = $this->adapter->fetchOperatingRoomSchedules();

            DB::transaction(function () use ($data, $syncedAt) {
                foreach ($data as $roomData) {
                    // 1. Find or create OperatingRoom
                    $room = OperatingRoom::firstOrCreate(
                        ['bpjs_or_code' => $roomData['bpjs_or_code']],
                        [
                            'name' => $roomData['room_name'],
                            'synced_at' => $syncedAt,
                        ]
                    );

                    $room->update(['synced_at' => $syncedAt]);

                    $hasRoomChanges = false;

                    // Keep track of active schedules to detect changes
                    foreach ($roomData['schedules'] as $sch) {
                        $previous = SurgerySchedule::where('bpjs_schedule_id', $sch['bpjs_schedule_id'])->first();

                        $statusChanged = !$previous || $previous->status !== $sch['status'];
                        $nameChanged = !$previous || $previous->patient_name !== $sch['patient_name'];
                        $actualStartChanged = !$previous || 
                            ($previous->actual_start_at?->format('Y-m-d H:i:s') !== $sch['actual_start_at']);

                        if ($statusChanged || $nameChanged || $actualStartChanged) {
                            $hasRoomChanges = true;
                        }

                        // Update or create schedule
                        SurgerySchedule::updateOrCreate(
                            ['bpjs_schedule_id' => $sch['bpjs_schedule_id']],
                            [
                                'operating_room_id' => $room->id,
                                'patient_name' => $sch['patient_name'],
                                'scheduled_start_at' => $sch['scheduled_start_at'],
                                'actual_start_at' => $sch['actual_start_at'],
                                'status' => $sch['status'],
                                'synced_at' => $syncedAt,
                            ]
                        );
                    }

                    // If any changes occurred in this room, broadcast the updated schedule list
                    if ($hasRoomChanges) {
                        $this->broadcastRoomSchedules($room);
                    }
                }
            });

            // Log successful sync
            SyncLog::create([
                'source' => 'bpjs_operating_room',
                'status' => 'success',
                'synced_at' => $syncedAt,
            ]);

        } catch (\Exception $e) {
            Log::error('BPJS Operating Room Sync failed: ' . $e->getMessage());

            // Log failed sync, keeping cached data intact (Req 2.7 / 5.2)
            SyncLog::create([
                'source' => 'bpjs_operating_room',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'synced_at' => $syncedAt,
            ]);
        }
    }

    /**
     * Broadcast all schedules of a room to all mapped displays.
     */
    protected function broadcastRoomSchedules(OperatingRoom $room): void
    {
        $mappings = DisplayMapping::where('target_type', 'operating_room')
            ->where('target_id', $room->id)
            ->with('device')
            ->get();

        // Get all schedules for the room (e.g. today's schedules)
        $schedules = SurgerySchedule::where('operating_room_id', $room->id)
            ->orderBy('scheduled_start_at')
            ->get()
            ->map(fn($sch) => [
                'bpjs_schedule_id' => $sch->bpjs_schedule_id,
                'patient_name' => $sch->patient_name, // raw name, will be masked in display frontend or controller
                'scheduled_start_at' => $sch->scheduled_start_at->toIso8601String(),
                'actual_start_at' => $sch->actual_start_at?->toIso8601String(),
                'status' => $sch->status,
            ])
            ->toArray();

        foreach ($mappings as $mapping) {
            if ($mapping->device) {
                event(new OperatingRoomStatusChanged($mapping->device->display_id, [
                    'bpjs_or_code' => $room->bpjs_or_code,
                    'room_name' => $room->name,
                    'schedules' => $schedules,
                ]));
            }
        }
    }
}
