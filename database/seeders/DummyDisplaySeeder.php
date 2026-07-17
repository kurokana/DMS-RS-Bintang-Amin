<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Polyclinic;
use App\Models\PolyclinicDoctor;
use App\Models\PolyclinicQueue;

class DummyDisplaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a polyclinic
        $polyclinic = Polyclinic::firstOrCreate(
            ['code' => 'UMUM'],
            ['name' => 'Poli Umum']
        );

        // 2. Create a doctor
        $doctor = PolyclinicDoctor::firstOrCreate(
            ['polyclinic_id' => $polyclinic->id, 'name' => 'Dr. Dummy Tester'],
            [
                'specialty' => 'Umum',
                'is_active' => true,
                'sort_order' => 1
            ]
        );

        // 3. Create 100 queue entries for today
        $now = now();
        $baseDate = $now->format('Y-m-d');
        
        for ($i = 1; $i <= 100; $i++) {
            $status = 'menunggu';
            $calledAt = null;
            $completedAt = null;

            // Make some of them called/completed for testing
            if ($i <= 10) {
                $status = 'selesai';
                $calledAt = $now->copy()->subMinutes(120 - $i);
                $completedAt = $now->copy()->subMinutes(110 - $i);
            } elseif ($i == 11) {
                $status = 'dipanggil';
                $calledAt = $now->copy()->subMinutes(5);
            }

            PolyclinicQueue::create([
                'id' => Str::uuid()->toString(),
                'polyclinic_id' => $polyclinic->id,
                'doctor_id' => $doctor->id,
                'queue_number' => $i,
                'patient_name' => 'Pasien Dummy ' . $i,
                'status' => $status,
                'queue_date' => $baseDate,
                'called_at' => $calledAt,
                'completed_at' => $completedAt,
            ]);
        }
        
        $this->command->info('Created 100 queue records for testing!');
    }
}
