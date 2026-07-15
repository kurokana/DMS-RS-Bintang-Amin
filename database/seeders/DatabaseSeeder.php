<?php

namespace Database\Seeders;

use App\Models\UserDms;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        UserDms::create([
            'email' => 'admin@dms.local',
            'password' => 'password', // will be hashed automatically by attribute casting in UserDms model
            'role' => 'admin',
        ]);

        UserDms::create([
            'email' => 'operator@dms.local',
            'password' => 'password',
            'role' => 'operator',
        ]);
    }
}
