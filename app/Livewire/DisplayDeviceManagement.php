<?php

namespace App\Livewire;

use App\Models\DisplayDevice;
use App\Models\OperatingRoom;
use App\Models\WardClass;
use App\Services\DisplayDeviceService;
use App\Services\DisplayMappingService;
use Livewire\Component;

class DisplayDeviceManagement extends Component
{
    // Registration form inputs
    public string $displayId = '';
    public string $name = '';

    // Mapping form inputs
    public string $selectedDeviceId = '';
    public string $targetType = 'ward_class';
    public string $targetId = '';

    public string $message = '';
    public string $errorMessage = '';

    public function render()
    {
        $devices = DisplayDevice::with(['mappings.target'])->get();
        $wards = WardClass::all();
        $rooms = OperatingRoom::all();

        return view('livewire.display-device-management', [
            'devices' => $devices,
            'wards' => $wards,
            'rooms' => $rooms,
        ])->layout('layouts.app', ['title' => 'Manajemen Display DMS']);
    }

    public function registerDevice(DisplayDeviceService $deviceService)
    {
        $this->validate([
            'displayId' => 'required|string|max:50',
            'name' => 'required|string|max:255',
        ]);

        if (auth()->user()->role !== 'admin') {
            $this->errorMessage = 'Hanya Admin yang dapat mendaftarkan perangkat monitor baru.';
            return;
        }

        try {
            $deviceService->createDevice([
                'display_id' => strtoupper($this->displayId),
                'name' => $this->name,
            ]);

            $this->message = "Perangkat monitor {$this->displayId} berhasil terdaftar!";
            $this->reset(['displayId', 'name']);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function updateMapping(DisplayMappingService $mappingService)
    {
        $this->validate([
            'selectedDeviceId' => 'required|uuid',
            'targetType' => 'required|string|in:ward_class,operating_room',
            'targetId' => 'required|uuid',
        ]);

        $device = DisplayDevice::findOrFail($this->selectedDeviceId);

        try {
            $mappingService->updateMapping($device, $this->targetType, $this->targetId);
            $this->message = "Mapping monitor {$device->display_id} berhasil diperbarui!";
            $this->reset(['selectedDeviceId', 'targetId']);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }
}
