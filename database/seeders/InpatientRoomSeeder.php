<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InpatientRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'room_code' => 'RM-VVIP-01',
                'name' => 'Ruang VVIP Mawar',
                'floor' => 'Lantai 5',
                'building' => 'Gedung Utama',
                'bed_total' => 1,
                'bed_occupied' => 1,
                'bed_available' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'room_code' => 'RM-VIP-01',
                'name' => 'Ruang VIP Melati',
                'floor' => 'Lantai 4',
                'building' => 'Gedung Utama',
                'bed_total' => 2,
                'bed_occupied' => 1,
                'bed_available' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'room_code' => 'RM-K1-01',
                'name' => 'Ruang Kelas 1 Anggrek',
                'floor' => 'Lantai 3',
                'building' => 'Gedung B',
                'bed_total' => 4,
                'bed_occupied' => 2,
                'bed_available' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'room_code' => 'RM-K2-01',
                'name' => 'Ruang Kelas 2 Kenanga',
                'floor' => 'Lantai 2',
                'building' => 'Gedung B',
                'bed_total' => 6,
                'bed_occupied' => 6,
                'bed_available' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'room_code' => 'RM-K3-01',
                'name' => 'Ruang Kelas 3 Flamboyan',
                'floor' => 'Lantai 1',
                'building' => 'Gedung C',
                'bed_total' => 10,
                'bed_occupied' => 4,
                'bed_available' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \Illuminate\Support\Facades\DB::table('inpatient_rooms')->insert($rooms);
    }
}
