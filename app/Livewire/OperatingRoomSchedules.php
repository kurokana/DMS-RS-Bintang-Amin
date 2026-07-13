<?php

namespace App\Livewire;

use App\Models\OperatingRoom;
use App\Models\SurgerySchedule;
use App\Services\BpjsOperatingRoomSyncService;
use Livewire\Component;

class OperatingRoomSchedules extends Component
{
    public string $selectedRoomId = '';
    public string $syncMessage = '';

    public function render()
    {
        $rooms = OperatingRoom::all();
        
        $query = SurgerySchedule::with('operatingRoom');
        
        if ($this->selectedRoomId) {
            $query->where('operating_room_id', $this->selectedRoomId);
        }

        $schedules = $query->orderBy('scheduled_start_at')->get();

        return view('livewire.operating-room-schedules', [
            'rooms' => $rooms,
            'schedules' => $schedules,
        ])->layout('layouts.app', ['title' => 'Jadwal Operasi DMS']);
    }

    public function syncManual(BpjsOperatingRoomSyncService $syncService)
    {
        $syncService->sync();
        $this->syncMessage = 'Sinkronisasi jadwal operasi berhasil dilakukan pada ' . now()->format('H:i:s');
    }
}
