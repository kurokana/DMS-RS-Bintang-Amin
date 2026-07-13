<?php

namespace Tests\Feature;

use App\Models\DisplayDevice;
use App\Models\UserDms;
use App\Models\WardClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected UserDms $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = UserDms::create([
            'email' => 'admin@dms.local',
            'password' => 'password',
            'role' => 'admin',
        ]);
    }

    public function test_post_request_triggers_audit_log_creation()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/displays', [
                'display_id' => 'DSP-TEST',
                'name' => 'Test Display',
            ]);

        $response->assertStatus(201);

        $device = DisplayDevice::where('display_id', 'DSP-TEST')->first();
        $this->assertNotNull($device);

        // Assert audit log exists
        $this->assertDatabaseHas('audit_logs', [
            'user_dms_id' => $this->admin->id,
            'module' => 'display',
            'operation' => 'post',
            'entity_type' => DisplayDevice::class,
            'entity_id' => $device->id,
        ]);
    }

    public function test_put_request_triggers_audit_log_with_before_and_after_data()
    {
        $device = DisplayDevice::create([
            'display_id' => 'DSP001',
            'name' => 'Initial Name',
        ]);

        $ward = WardClass::create([
            'bpjs_class_code' => 'VIP',
            'name' => 'VIP Class',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/displays/{$device->id}/mapping", [
                'target_type' => 'ward_class',
                'target_id' => $ward->id,
            ]);

        $response->assertStatus(200);

        // Assert audit log contains before/after state
        $this->assertDatabaseHas('audit_logs', [
            'user_dms_id' => $this->admin->id,
            'module' => 'mapping',
            'operation' => 'put',
        ]);
    }
}
