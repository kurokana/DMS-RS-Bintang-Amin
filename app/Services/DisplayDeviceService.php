<?php

namespace App\Services;

use App\Events\DeviceStatusChanged;
use App\Models\DisplayDevice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class DisplayDeviceService
{
    /**
     * Get all display devices.
     */
    public function getAllDevices(): Collection
    {
        return DisplayDevice::with('mappings.target')->get();
    }

    /**
     * Create a new display device.
     */
    public function createDevice(array $data): DisplayDevice
    {
        if (DisplayDevice::where('display_id', $data['display_id'])->exists()) {
            throw ValidationException::withMessages([
                'display_id' => ['Display ID sudah digunakan.'],
            ]);
        }

        return DisplayDevice::create([
            'display_id' => $data['display_id'],
            'name' => $data['name'],
            'status' => 'offline', // initial status is offline
        ]);
    }

    /**
     * Update an existing display device.
     */
    public function updateDevice(string $displayId, array $data): DisplayDevice
    {
        $device = DisplayDevice::where('display_id', $displayId)->firstOrFail();
        $device->update([
            'name' => $data['name'],
        ]);
        
        return $device;
    }

    /**
     * Update display heartbeat status to online.
     */
    public function recordHeartbeat(string $displayId): DisplayDevice
    {
        $device = DisplayDevice::where('display_id', $displayId)->firstOrFail();

        $previousStatus = $device->status;

        $device->update([
            'status' => 'online',
            'last_heartbeat_at' => now(),
        ]);

        // Broadcast only when status actually changes (offline -> online)
        if ($previousStatus !== 'online') {
            event(new DeviceStatusChanged(
                $device->display_id,
                $device->name,
                'online',
                $device->last_heartbeat_at->toIso8601String(),
            ));
        }

        return $device;
    }
}
