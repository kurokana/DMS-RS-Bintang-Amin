<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Display Devices
        Schema::create('display_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('display_id')->unique();
            $table->string('name');
            $table->string('status')->default('offline'); // online | offline
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();
        });

        // 2. Display Mappings
        Schema::create('display_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('display_device_id');
            $table->string('target_type'); // ward_class | operating_room
            $table->uuid('target_id'); // FK dinamis ke ward_classes / operating_rooms
            $table->timestamp('effective_at')->useCurrent();
            $table->timestamps();

            $table->foreign('display_device_id')
                ->references('id')
                ->on('display_devices')
                ->onDelete('cascade');

            $table->index(['target_type', 'target_id']);
        });

        // 3. Ward Classes
        Schema::create('ward_classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bpjs_class_code')->unique();
            $table->string('name');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        // 4. Ward Availability
        Schema::create('ward_availability', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ward_class_id');
            $table->integer('bed_total');
            $table->integer('bed_occupied');
            $table->integer('bed_available');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('ward_class_id')
                ->references('id')
                ->on('ward_classes')
                ->onDelete('cascade');
        });

        // 5. Operating Rooms
        Schema::create('operating_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bpjs_or_code')->unique();
            $table->string('name');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        // 6. Surgery Schedules
        Schema::create('surgery_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bpjs_schedule_id')->unique();
            $table->uuid('operating_room_id');
            $table->string('patient_name');
            $table->timestamp('scheduled_start_at');
            $table->timestamp('actual_start_at')->nullable();
            $table->string('status')->default('menunggu'); // menunggu | sedang_dilaksanakan | selesai
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('operating_room_id')
                ->references('id')
                ->on('operating_rooms')
                ->onDelete('cascade');
        });

        // 7. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_dms_id')->nullable();
            $table->string('module');
            $table->string('operation');
            $table->string('entity_type');
            $table->uuid('entity_id');
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_dms_id')
                ->references('id')
                ->on('users_dms')
                ->onDelete('set null');
        });

        // 8. Sync Logs
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source'); // bpjs_ward | bpjs_operating_room
            $table->string('status'); // success | failed
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('surgery_schedules');
        Schema::dropIfExists('operating_rooms');
        Schema::dropIfExists('ward_availability');
        Schema::dropIfExists('ward_classes');
        Schema::dropIfExists('display_mappings');
        Schema::dropIfExists('display_devices');
    }
};
