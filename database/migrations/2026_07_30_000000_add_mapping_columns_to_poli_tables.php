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
        Schema::table('polyclinics', function (Blueprint $table) {
            if (!Schema::hasColumn('polyclinics', 'ruangan_code')) {
                $table->string('ruangan_code', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('polyclinics', 'simrs_code')) {
                $table->string('simrs_code', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('polyclinics', 'bpjs_code')) {
                $table->string('bpjs_code', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('polyclinics', 'display_name')) {
                $table->string('display_name')->nullable();
            }
        });

        Schema::table('polyclinic_doctors', function (Blueprint $table) {
            if (!Schema::hasColumn('polyclinic_doctors', 'doctor_code')) {
                $table->string('doctor_code', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('polyclinic_doctors', 'master_doctor_uuid')) {
                $table->uuid('master_doctor_uuid')->nullable()->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polyclinics', function (Blueprint $table) {
            $table->dropColumn(['ruangan_code', 'simrs_code', 'bpjs_code', 'display_name']);
        });

        Schema::table('polyclinic_doctors', function (Blueprint $table) {
            $table->dropColumn(['doctor_code', 'master_doctor_uuid']);
        });
    }
};
