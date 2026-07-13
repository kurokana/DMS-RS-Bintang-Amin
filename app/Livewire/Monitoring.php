<?php

namespace App\Livewire;

use App\Models\DisplayDevice;
use App\Models\SyncLog;
use Livewire\Component;

class Monitoring extends Component
{
    public function render()
    {
        $devices = DisplayDevice::with(['mappings.target'])->get();
        $syncLogs = SyncLog::latest()->take(10)->get();

        return view('livewire.monitoring', [
            'devices' => $devices,
            'syncLogs' => $syncLogs,
        ])->layout('layouts.app', ['title' => 'Monitoring Status DMS']);
    }
}
