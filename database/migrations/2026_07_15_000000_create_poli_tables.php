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
        // 1. Polyclinics (Master Poli)
        Schema::create('polyclinics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();   // e.g. "POLI-UMUM"
            $table->string('name');              // e.g. "Poliklinik Umum"
            $table->string('ruangan_code', 50)->nullable()->index();
            $table->string('simrs_code', 50)->nullable()->index();
            $table->string('bpjs_code', 50)->nullable()->index();
            $table->string('display_name')->nullable();
            $table->timestamps();
        });

        // 2. Polyclinic Doctors (Dokter per Poli)
        Schema::create('polyclinic_doctors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('polyclinic_id');
            $table->string('doctor_code', 50)->nullable()->index();
            $table->uuid('master_doctor_uuid')->nullable()->index();
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->string('specialty')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('polyclinic_id')
                ->references('id')
                ->on('polyclinics')
                ->onDelete('cascade');
        });

        // 3. Polyclinic Queue (Antrian pasien per dokter per poli)
        Schema::create('polyclinic_queue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('polyclinic_id');
            $table->uuid('doctor_id');
            $table->integer('queue_number');
            $table->string('patient_name');
            $table->string('status')->default('menunggu'); // menunggu | dilayani | selesai | terlewat
            $table->date('queue_date');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('polyclinic_id')
                ->references('id')
                ->on('polyclinics')
                ->onDelete('cascade');

            $table->foreign('doctor_id')
                ->references('id')
                ->on('polyclinic_doctors')
                ->onDelete('cascade');

            $table->index(['polyclinic_id', 'doctor_id', 'queue_date']);
            $table->index(['queue_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polyclinic_queue');
        Schema::dropIfExists('polyclinic_doctors');
        Schema::dropIfExists('polyclinics');
    }
};
