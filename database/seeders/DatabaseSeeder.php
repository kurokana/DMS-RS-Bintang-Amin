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
        // Tidak memerlukan seeder. Akun admin dikelola dan diinisialisasi secara dinamis dari .env saat login.
    }
}
