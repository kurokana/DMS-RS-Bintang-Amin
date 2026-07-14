<?php

namespace App\Services;

use App\Events\DisplayMappingUpdated;
use App\Models\DisplayDevice;
use App\Models\DisplayMapping;
use App\Models\OperatingRoom;
use App\Models\WardClass;
use App\Models\InpatientRoom;
use Illuminate\Validation\ValidationException;

class DisplayMappingService
{
    /**
     * Update display mapping for a device and broadcast the change.
     */
    public function updateMapping(DisplayDevice $device, string $targetType, string $targetId): DisplayMapping
    {
        // 1. Validate target type and exists
        if ($targetType === 'ward_class') {
            if (!WardClass::where('id', $targetId)->exists()) {
                throw ValidationException::withMessages([
                    'target_id' => ['Kelas rawat inap tidak ditemukan.'],
                ]);
            }
        } elseif ($targetType === 'operating_room') {
            if (!OperatingRoom::where('id', $targetId)->exists()) {
                throw ValidationException::withMessages([
                    'target_id' => ['Kamar operasi tidak ditemukan.'],
                ]);
            }
        } elseif ($targetType === 'inpatient_room') {
            if (!InpatientRoom::where('id', $targetId)->exists()) {
                throw ValidationException::withMessages([
                    'target_id' => ['Ruangan rawat inap tidak ditemukan.'],
                ]);
            }
        } elseif ($targetType === 'ward_summary') {
            // No need to validate target_id for ward_summary, usually 'all'
        } else {
            throw ValidationException::withMessages([
                'target_type' => ['Target type tidak valid.'],
            ]);
        }

        // 2. Update or create the mapping
        $mapping = DisplayMapping::updateOrCreate(
            ['display_device_id' => $device->id],
            [
                'target_type' => $targetType,
                'target_id' => $targetId,
                'effective_at' => now(),
            ]
        );

        // Load relations for payload
        if (in_array($targetType, ['ward_class', 'operating_room', 'inpatient_room'])) {
            $mapping->load('target');
        }

        // 3. Broadcast the MappingUpdated event
        event(new DisplayMappingUpdated($device->display_id, [
            'target_type' => $mapping->target_type,
            'target_id' => $mapping->target_id,
            'target_name' => $mapping->target?->name ?? '',
        ]));

        return $mapping;
    }
}
