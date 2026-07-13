<?php

namespace Tests\Feature;

use App\Events\OperatingRoomStatusChanged;
use App\Events\WardAvailabilityChanged;
use App\Models\DisplayDevice;
use App\Models\DisplayMapping;
use App\Models\OperatingRoom;
use App\Models\SurgerySchedule;
use App\Models\WardClass;
use App\Models\WardAvailability;
use App\Services\BpjsOperatingRoomSyncService;
use App\Services\BpjsWardSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BpjsSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_ward_sync_populates_tables_and_broadcasts_events()
    {
        Event::fake([WardAvailabilityChanged::class]);

        // Seed a Display mapped to VIP
        $device = DisplayDevice::create([
            'display_id' => 'DSP001',
            'name' => 'Monitor Lobby',
        ]);

        $vipClass = WardClass::create([
            'bpjs_class_code' => 'VIP',
            'name' => 'VIP Class',
        ]);

        DisplayMapping::create([
            'display_device_id' => $device->id,
            'target_type' => 'ward_class',
            'target_id' => $vipClass->id,
        ]);

        // Run sync
        $syncService = app(BpjsWardSyncService::class);
        $syncService->sync();

        // Assert database populated
        $this->assertDatabaseHas('ward_availability', [
            'ward_class_id' => $vipClass->id,
        ]);

        // Assert log was created
        $this->assertDatabaseHas('sync_logs', [
            'source' => 'bpjs_ward',
            'status' => 'success',
        ]);

        // Assert event was broadcasted because availability changed from nothing
        Event::assertDispatched(WardAvailabilityChanged::class, function ($event) use ($device) {
            return $event->displayId === $device->display_id && $event->availability['bpjs_class_code'] === 'VIP';
        });
    }

    public function test_operating_room_sync_populates_tables_and_broadcasts_events()
    {
        Event::fake([OperatingRoomStatusChanged::class]);

        // Seed a Display mapped to room OK1
        $device = DisplayDevice::create([
            'display_id' => 'DSP002',
            'name' => 'Monitor OR',
        ]);

        $room = OperatingRoom::create([
            'bpjs_or_code' => 'OK1',
            'name' => 'Kamar Operasi 1',
        ]);

        DisplayMapping::create([
            'display_device_id' => $device->id,
            'target_type' => 'operating_room',
            'target_id' => $room->id,
        ]);

        // Run sync
        $syncService = app(BpjsOperatingRoomSyncService::class);
        $syncService->sync();

        // Assert database populated
        $this->assertDatabaseHas('surgery_schedules', [
            'operating_room_id' => $room->id,
        ]);

        $this->assertDatabaseHas('sync_logs', [
            'source' => 'bpjs_operating_room',
            'status' => 'success',
        ]);

        // Assert event was broadcasted because schedules changed from nothing
        Event::assertDispatched(OperatingRoomStatusChanged::class, function ($event) use ($device) {
            return $event->displayId === $device->display_id && $event->schedules['bpjs_or_code'] === 'OK1';
        });
    }
}
