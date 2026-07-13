<?php

namespace App\Livewire;

use App\Models\DisplayDevice;
use App\Models\SyncLog;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $devices = DisplayDevice::all();
        $totalDevices = $devices->count();
        $onlineDevices = $devices->where('status', 'online')->count();
        $offlineDevices = $devices->where('status', 'offline')->count();

        // Check BPJS Sync Log
        $wardLastSync = SyncLog::where('source', 'bpjs_ward')->latest()->first();
        $orLastSync = SyncLog::where('source', 'bpjs_operating_room')->latest()->first();

        $bpjsStatus = 'connected';
        if (($wardLastSync && $wardLastSync->status === 'failed') || 
            ($orLastSync && $orLastSync->status === 'failed') ||
            (!$wardLastSync) || 
            (!$orLastSync)) {
            $bpjsStatus = 'disconnected';
        }

        return view('livewire.dashboard', [
            'totalDevices' => $totalDevices,
            'onlineDevices' => $onlineDevices,
            'offlineDevices' => $offlineDevices,
            'bpjsStatus' => $bpjsStatus,
            'wardLastSync' => $wardLastSync,
            'orLastSync' => $orLastSync,
        ])->layout('layouts.app', ['title' => 'Dashboard DMS Admin']);
    }
}
