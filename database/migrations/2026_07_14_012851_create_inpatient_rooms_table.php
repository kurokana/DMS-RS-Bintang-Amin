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
        Schema::create('inpatient_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('room_code')->unique();
            $table->string('name');
            $table->string('floor');
            $table->string('building');
            $table->integer('bed_total')->default(0);
            $table->integer('bed_occupied')->default(0);
            $table->integer('bed_available')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inpatient_rooms');
    }
};
