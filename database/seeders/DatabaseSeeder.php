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
        $this->call(P5MasterSeeder::class);
        $this->call(SettingSeeder::class);
        
        // =========================================================================
        // ALL-IN-ONE DUMMY SEEDER
        // Cukup aktifkan 1 baris ini jika ingin database langsung terisi data dummy
        // lengkap (User, Kelas, Siswa, Nilai, TP, P5, Eskul, Portal Siswa, Audit):
        // =========================================================================
        // $this->call(CompleteDummyDataSeeder::class);
    }
}
