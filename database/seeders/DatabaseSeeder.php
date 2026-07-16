<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(AccountSeeder::class);
        $this->call(ClassSeeder::class);
        $this->call(MapelSeeder::class);
        $this->call(DummyDataSeeder::class);
        $this->call(SettingSeeder::class);
    }
}
