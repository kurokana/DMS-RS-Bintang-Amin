<?php

namespace Database\Seeders;

use App\Models\UserDms;
use App\Models\WardClass;
use App\Models\WardAvailability;
use App\Models\OperatingRoom;
use App\Models\DisplayDevice;
use App\Models\DisplayMapping;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        $admin = UserDms::create([
            'email' => 'admin@dms.local',
            'password' => 'password', // will be hashed automatically by attribute casting in UserDms model
            'role' => 'admin',
        ]);

        $operator = UserDms::create([
            'email' => 'operator@dms.local',
            'password' => 'password',
            'role' => 'operator',
        ]);

        // 2. Seed Ward Classes and Initial Availabilities
        $wards = [
            ['code' => 'VIP', 'name' => 'VIP', 'total' => 10, 'occupied' => 6],
            ['code' => 'K1', 'name' => 'Kelas 1', 'total' => 20, 'occupied' => 12],
            ['code' => 'K2', 'name' => 'Kelas 2', 'total' => 30, 'occupied' => 22],
            ['code' => 'K3', 'name' => 'Kelas 3', 'total' => 50, 'occupied' => 45],
            ['code' => 'ICU', 'name' => 'ICU', 'total' => 8, 'occupied' => 5],
            ['code' => 'ISO', 'name' => 'Isolasi', 'total' => 6, 'occupied' => 2],
        ];

        $wardInstances = [];
        foreach ($wards as $w) {
            $wc = WardClass::create([
                'bpjs_class_code' => $w['code'],
                'name' => $w['name'],
                'synced_at' => now(),
            ]);

            WardAvailability::create([
                'ward_class_id' => $wc->id,
                'bed_total' => $w['total'],
                'bed_occupied' => $w['occupied'],
                'bed_available' => $w['total'] - $w['occupied'],
                'synced_at' => now(),
            ]);

            $wardInstances[$w['code']] = $wc;
        }

        // 3. Seed Operating Rooms
        $rooms = [
            ['code' => 'OK1', 'name' => 'Kamar Operasi 1'],
            ['code' => 'OK2', 'name' => 'Kamar Operasi 2'],
            ['code' => 'OK3', 'name' => 'Kamar Operasi 3'],
        ];

        $orInstances = [];
        foreach ($rooms as $r) {
            $or = OperatingRoom::create([
                'bpjs_or_code' => $r['code'],
                'name' => $r['name'],
                'synced_at' => now(),
            ]);
            $orInstances[$r['code']] = $or;
        }

        // 4. Seed Display Devices & Mappings
        $dsp1 = DisplayDevice::create([
            'display_id' => 'DSP001',
            'name' => 'Lobby Rawat Inap Utama',
            'status' => 'offline',
        ]);

        // Map DSP001 to WardClass K1
        DisplayMapping::create([
            'display_device_id' => $dsp1->id,
            'target_type' => 'ward_class',
            'target_id' => $wardInstances['K1']->id,
            'effective_at' => now(),
        ]);

        $dsp2 = DisplayDevice::create([
            'display_id' => 'DSP002',
            'name' => 'Ruang Tunggu Operasi',
            'status' => 'offline',
        ]);

        // Map DSP002 to OperatingRoom OK1
        DisplayMapping::create([
            'display_device_id' => $dsp2->id,
            'target_type' => 'operating_room',
            'target_id' => $orInstances['OK1']->id,
            'effective_at' => now(),
        ]);

        $this->call(SurgeryScheduleSeeder::class);
    }
}
