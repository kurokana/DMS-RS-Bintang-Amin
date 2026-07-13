<?php

namespace Database\Seeders;

use App\Models\OperatingRoom;
use App\Models\SurgerySchedule;
use Illuminate\Database\Seeder;

class SurgeryScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find operating rooms
        $or1 = OperatingRoom::where('bpjs_or_code', 'OK1')->first();
        $or2 = OperatingRoom::where('bpjs_or_code', 'OK2')->first();
        $or3 = OperatingRoom::where('bpjs_or_code', 'OK3')->first();

        // 1. Seed schedules for Kamar Operasi 1 (OK1)
        if ($or1) {
            SurgerySchedule::create([
                'bpjs_schedule_id' => 'SCH-1001',
                'operating_room_id' => $or1->id,
                'patient_name' => 'Budi Santoso',
                'scheduled_start_at' => now()->subHours(4),
                'actual_start_at' => now()->subHours(4),
                'status' => 'selesai',
                'synced_at' => now(),
            ]);

            SurgerySchedule::create([
                'bpjs_schedule_id' => 'SCH-1002',
                'operating_room_id' => $or1->id,
                'patient_name' => 'Dewi Lestari',
                'scheduled_start_at' => now()->subHours(1),
                'actual_start_at' => now()->subHours(1),
                'status' => 'sedang_dilaksanakan',
                'synced_at' => now(),
            ]);

            SurgerySchedule::create([
                'bpjs_schedule_id' => 'SCH-1003',
                'operating_room_id' => $or1->id,
                'patient_name' => 'Joko Widodo',
                'scheduled_start_at' => now()->addHours(2),
                'actual_start_at' => null,
                'status' => 'menunggu',
                'synced_at' => now(),
            ]);
        }

        // 2. Seed schedules for Kamar Operasi 2 (OK2)
        if ($or2) {
            SurgerySchedule::create([
                'bpjs_schedule_id' => 'SCH-2001',
                'operating_room_id' => $or2->id,
                'patient_name' => 'Siti Aminah',
                'scheduled_start_at' => now()->subHours(2),
                'actual_start_at' => now()->subHours(2),
                'status' => 'selesai',
                'synced_at' => now(),
            ]);

            SurgerySchedule::create([
                'bpjs_schedule_id' => 'SCH-2002',
                'operating_room_id' => $or2->id,
                'patient_name' => 'Rian Hidayat',
                'scheduled_start_at' => now()->addHours(1),
                'actual_start_at' => null,
                'status' => 'menunggu',
                'synced_at' => now(),
            ]);
        }

        // 3. Seed schedules for Kamar Operasi 3 (OK3)
        if ($or3) {
            SurgerySchedule::create([
                'bpjs_schedule_id' => 'SCH-3001',
                'operating_room_id' => $or3->id,
                'patient_name' => 'Anisa Bahar',
                'scheduled_start_at' => now()->subHours(3),
                'actual_start_at' => now()->subHours(3),
                'status' => 'selesai',
                'synced_at' => now(),
            ]);

            SurgerySchedule::create([
                'bpjs_schedule_id' => 'SCH-3002',
                'operating_room_id' => $or3->id,
                'patient_name' => 'Guntur Bumi',
                'scheduled_start_at' => now()->addHours(3),
                'actual_start_at' => null,
                'status' => 'menunggu',
                'synced_at' => now(),
            ]);
        }
    }
}
