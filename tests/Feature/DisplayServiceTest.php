<?php

namespace Tests\Feature;

use App\Models\DisplayDevice;
use App\Models\DisplayMapping;
use App\Models\OperatingRoom;
use App\Models\SurgerySchedule;
use App\Models\WardClass;
use App\Models\WardAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_state_returns_404_for_unknown_display()
    {
        $response = $this->getJson('/display/UNKNOWN/state');
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'DISPLAY_NOT_FOUND',
                ]
            ]);
    }

    public function test_state_returns_correct_data_for_ward_class_mapping()
    {
        $device = DisplayDevice::create([
            'display_id' => 'DSP001',
            'name' => 'Lobby Ward',
        ]);

        $ward = WardClass::create([
            'bpjs_class_code' => 'VIP',
            'name' => 'VIP Class',
        ]);

        WardAvailability::create([
            'ward_class_id' => $ward->id,
            'bed_total' => 10,
            'bed_occupied' => 6,
            'bed_available' => 4,
            'synced_at' => now(),
        ]);

        DisplayMapping::create([
            'display_device_id' => $device->id,
            'target_type' => 'ward_class',
            'target_id' => $ward->id,
        ]);

        $response = $this->getJson('/display/DSP001/state');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'display_id' => 'DSP001',
                    'mapped' => true,
                    'target_type' => 'ward_class',
                    'content' => [
                        'bpjs_class_code' => 'VIP',
                        'class_name' => 'VIP Class',
                        'bed_total' => 10,
                        'bed_occupied' => 6,
                        'bed_available' => 4,
                    ]
                ]
            ]);

        // Status should be set to online due to state call
        $device->refresh();
        $this->assertEquals('online', $device->status);
        $this->assertNotNull($device->last_heartbeat_at);
    }

    public function test_state_returns_correct_data_with_patient_masking_for_operating_room_mapping()
    {
        $device = DisplayDevice::create([
            'display_id' => 'DSP002',
            'name' => 'Lobby OR',
        ]);

        $room = OperatingRoom::create([
            'bpjs_or_code' => 'OK1',
            'name' => 'Kamar Operasi 1',
        ]);

        SurgerySchedule::create([
            'bpjs_schedule_id' => 'SCH001',
            'operating_room_id' => $room->id,
            'patient_name' => 'Ahmad Fauzi',
            'scheduled_start_at' => now(),
            'status' => 'sedang_dilaksanakan',
        ]);

        DisplayMapping::create([
            'display_device_id' => $device->id,
            'target_type' => 'operating_room',
            'target_id' => $room->id,
        ]);

        $response = $this->getJson('/display/DSP002/state');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'display_id' => 'DSP002',
                    'mapped' => true,
                    'target_type' => 'operating_room',
                ]
            ]);

        // Assert patient name is masked ("Ahmad Fauzi" -> "A***d F***i")
        $schedules = $response->json('data.content.schedules');
        $this->assertCount(1, $schedules);
        $this->assertEquals('A***d F***i', $schedules[0]['patient_name']);
    }

    public function test_heartbeat_updates_display_status()
    {
        $device = DisplayDevice::create([
            'display_id' => 'DSP001',
            'name' => 'Monitor Lobby',
            'status' => 'offline',
        ]);

        $response = $this->postJson('/display/DSP001/heartbeat');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'online',
                ]
            ]);

        $device->refresh();
        $this->assertEquals('online', $device->status);
        $this->assertNotNull($device->last_heartbeat_at);
    }

    public function test_health_check_returns_ok()
    {
        \Illuminate\Support\Facades\Redis::shouldReceive('connection->ping')
            ->andReturn(true);

        $response = $this->getJson('/health');
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'db' => 'ok',
                'redis' => 'ok',
            ]);
    }
}
