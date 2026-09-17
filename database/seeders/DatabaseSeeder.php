<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder Bersih Produksi (Hanya Roles, Admin, Master P5 Kemdikbud, Settings)
        $this->call(CleanProductionSeeder::class);
    }
}
