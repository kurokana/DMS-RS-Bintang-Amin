<?php

namespace App\Livewire;

use App\Models\WardClass;
use App\Services\BpjsWardSyncService;
use Livewire\Component;

class WardAvailability extends Component
{
    public string $syncMessage = '';

    public function render()
    {
        $wards = WardClass::with('currentAvailability')->get();

        return view('livewire.ward-availability', [
            'wards' => $wards,
        ])->layout('layouts.app', ['title' => 'Ketersediaan Kamar DMS']);
    }

    public function syncManual(BpjsWardSyncService $syncService)
    {
        $syncService->sync();
        $this->syncMessage = 'Sinkronisasi ketersediaan kamar berhasil dilakukan pada ' . now()->format('H:i:s');
    }
}
