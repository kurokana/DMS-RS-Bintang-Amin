<?php

namespace Tests\Feature;

use App\Models\DisplayDevice;
use App\Models\OperatingRoom;
use App\Models\UserDms;
use App\Models\WardClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayApiTest extends TestCase
{
    use RefreshDatabase;

    protected UserDms $admin;
    protected UserDms $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = UserDms::create([
            'email' => 'admin@dms.local',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->operator = UserDms::create([
            'email' => 'operator@dms.local',
            'password' => 'password',
            'role' => 'operator',
        ]);
    }

    public function test_list_displays_requires_authentication()
    {
        $response = $this->getJson('/api/v1/displays');
        $response->assertStatus(401);
    }

    public function test_admin_can_create_display_device()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/displays', [
                'display_id' => 'DSP-NEW',
                'name' => 'Kamar ICU Monitor',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'display_id' => 'DSP-NEW',
                    'name' => 'Kamar ICU Monitor',
                    'status' => 'offline',
                ]
            ]);

        $this->assertDatabaseHas('display_devices', [
            'display_id' => 'DSP-NEW',
        ]);
    }

    public function test_operator_cannot_create_display_device()
    {
        $response = $this->actingAs($this->operator, 'sanctum')
            ->postJson('/api/v1/displays', [
                'display_id' => 'DSP-NEW',
                'name' => 'Kamar ICU Monitor',
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_create_display_device_with_duplicate_id()
    {
        DisplayDevice::create([
            'display_id' => 'DSP-DUP',
            'name' => 'Existing',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/displays', [
                'display_id' => 'DSP-DUP',
                'name' => 'Duplicate Name',
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'DISPLAY_ID_TAKEN',
                ]
            ]);
    }

    public function test_operator_can_update_mapping_to_ward_class()
    {
        $device = DisplayDevice::create([
            'display_id' => 'DSP001',
            'name' => 'Monitor Ward',
        ]);

        $ward = WardClass::create([
            'bpjs_class_code' => 'VIP',
            'name' => 'VIP Class',
        ]);

        $response = $this->actingAs($this->operator, 'sanctum')
            ->putJson("/api/v1/displays/{$device->id}/mapping", [
                'target_type' => 'ward_class',
                'target_id' => $ward->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'display_device_id' => $device->id,
                    'target_type' => 'ward_class',
                    'target_id' => $ward->id,
                ]
            ]);

        $this->assertDatabaseHas('display_mappings', [
            'display_device_id' => $device->id,
            'target_type' => 'ward_class',
            'target_id' => $ward->id,
        ]);
    }

    public function test_cannot_update_mapping_with_invalid_target_id()
    {
        $device = DisplayDevice::create([
            'display_id' => 'DSP001',
            'name' => 'Monitor Ward',
        ]);

        $response = $this->actingAs($this->operator, 'sanctum')
            ->putJson("/api/v1/displays/{$device->id}/mapping", [
                'target_type' => 'ward_class',
                'target_id' => '00000000-0000-0000-0000-000000000000', // non-existent UUID
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'MAPPING_TARGET_INVALID',
                ]
            ]);
    }

    public function test_monitoring_returns_correct_statistics()
    {
        DisplayDevice::create([
            'display_id' => 'DSP-ON',
            'name' => 'Online Display',
            'status' => 'online',
        ]);

        DisplayDevice::create([
            'display_id' => 'DSP-OFF',
            'name' => 'Offline Display',
            'status' => 'offline',
        ]);

        $response = $this->actingAs($this->operator, 'sanctum')
            ->getJson('/api/v1/monitoring/displays');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary' => [
                        'total',
                        'online',
                        'offline',
                    ],
                    'devices',
                ]
            ]);

        $summary = $response->json('data.summary');
        $this->assertEquals(2, $summary['total']);
        $this->assertEquals(1, $summary['online']);
        $this->assertEquals(1, $summary['offline']);
    }
}
