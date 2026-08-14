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
        // 1. Tabel Master Perangkat ESP32
        Schema::create('iot_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('serial_number', 64)->unique();
            $table->string('name', 100)->default('Sensor Ruang Server');
            $table->string('location', 100)->default('Ruang Server Utama');
            $table->string('mac_address', 17)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('firmware_version', 32)->default('1.0.0');
            $table->enum('status', ['ONLINE', 'OFFLINE'])->default('OFFLINE');
            $table->string('api_token', 64)->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Log Telemetry Sensor Suhu & Kelembapan
        Schema::create('iot_sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('device_id')->constrained('iot_devices')->cascadeOnDelete();
            $table->decimal('temperature', 5, 2);
            $table->decimal('humidity', 5, 2);
            $table->integer('rssi')->default(0);
            $table->integer('latency_ms')->nullable();
            $table->unsignedBigInteger('uptime_seconds')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['device_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_sensor_readings');
        Schema::dropIfExists('iot_devices');
    }
};
