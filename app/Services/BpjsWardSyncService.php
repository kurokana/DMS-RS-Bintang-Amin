<?php

namespace App\Services;

use App\Contracts\BpjsWardAdapterInterface;
use App\Events\WardAvailabilityChanged;
use App\Models\DisplayMapping;
use App\Models\SyncLog;
use App\Models\WardAvailability;
use App\Models\WardClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BpjsWardSyncService
{
    protected BpjsWardAdapterInterface $adapter;

    public function __construct(BpjsWardAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Synchronize ward availability from BPJS to local cache.
     */
    public function sync(): void
    {
        $syncedAt = now();
        
        try {
            $data = $this->adapter->fetchWardAvailability();

            DB::transaction(function () use ($data, $syncedAt) {
                foreach ($data as $item) {
                    // 1. Find or create WardClass
                    $wardClass = WardClass::firstOrCreate(
                        ['bpjs_class_code' => $item['bpjs_class_code']],
                        [
                            'name' => $this->getClassNameByCode($item['bpjs_class_code']),
                            'synced_at' => $syncedAt,
                        ]
                    );

                    $wardClass->update(['synced_at' => $syncedAt]);

                    // 2. Fetch previous availability to check if changed
                    $previous = WardAvailability::where('ward_class_id', $wardClass->id)
                        ->first();

                    $bedTotal = $item['bed_total'];
                    $bedOccupied = $item['bed_occupied'];
                    $bedAvailable = max(0, $bedTotal - $bedOccupied);

                    $hasChanged = !$previous || 
                        $previous->bed_total !== $bedTotal || 
                        $previous->bed_occupied !== $bedOccupied ||
                        $previous->bed_available !== $bedAvailable;

                    // 3. Update or create current availability
                    WardAvailability::updateOrCreate(
                        ['ward_class_id' => $wardClass->id],
                        [
                            'bed_total' => $bedTotal,
                            'bed_occupied' => $bedOccupied,
                            'bed_available' => $bedAvailable,
                            'synced_at' => $syncedAt,
                        ]
                    );

                    // 4. If changed, broadcast to mapped displays
                    if ($hasChanged) {
                        $this->broadcastChange($wardClass, [
                            'bpjs_class_code' => $wardClass->bpjs_class_code,
                            'class_name' => $wardClass->name,
                            'bed_total' => $bedTotal,
                            'bed_occupied' => $bedOccupied,
                            'bed_available' => $bedAvailable,
                        ]);
                    }
                }
            });

            // Log successful sync
            SyncLog::create([
                'source' => 'bpjs_ward',
                'status' => 'success',
                'synced_at' => $syncedAt,
            ]);

        } catch (\Exception $e) {
            Log::error('BPJS Ward Sync failed: ' . $e->getMessage());

            // Log failed sync, keeping cached data intact (Req 1.4 / 5.2)
            SyncLog::create([
                'source' => 'bpjs_ward',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'synced_at' => $syncedAt,
            ]);
        }
    }

    /**
     * Broadcast changes to all Display Devices displaying this Ward Class.
     */
    protected function broadcastChange(WardClass $wardClass, array $data): void
    {
        $mappings = DisplayMapping::where('target_type', 'ward_class')
            ->where('target_id', $wardClass->id)
            ->with('device')
            ->get();



        foreach ($mappings as $mapping) {
            if ($mapping->device) {
                event(new WardAvailabilityChanged($mapping->device->display_id, $data));
            }
        }
    }

    /**
     * Helper to resolve ward class names from codes.
     */
    protected function getClassNameByCode(string $code): string
    {
        $classes = [
            'VIP' => 'VIP',
            'K1' => 'Kelas 1',
            'K2' => 'Kelas 2',
            'K3' => 'Kelas 3',
            'ICU' => 'ICU',
            'ISO' => 'Isolasi',
        ];

        return $classes[$code] ?? 'Kelas ' . $code;
    }
}
