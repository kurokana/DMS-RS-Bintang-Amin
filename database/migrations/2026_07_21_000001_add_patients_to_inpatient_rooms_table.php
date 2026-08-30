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
        if (Schema::hasTable('inpatient_rooms') && !Schema::hasColumn('inpatient_rooms', 'patients')) {
            Schema::table('inpatient_rooms', function (Blueprint $table) {
                $table->json('patients')->nullable()->after('bed_available');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inpatient_rooms') && Schema::hasColumn('inpatient_rooms', 'patients')) {
            Schema::table('inpatient_rooms', function (Blueprint $table) {
                $table->dropColumn('patients');
            });
        }
    }
};
