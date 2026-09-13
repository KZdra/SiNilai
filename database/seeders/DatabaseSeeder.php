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
        // Jalankan Seeder Utama untuk Testing (Semua master & operasional data siap, nilai siswa 100% kosong)
        // $this->call(TestingDataSeeder::class);
        
        // =========================================================================
        // ALL-IN-ONE DUMMY SEEDER
        // Cukup aktifkan 1 baris ini jika ingin database langsung terisi data dummy
        // lengkap (User, Kelas, Siswa, Nilai, TP, P5, Eskul, Portal Siswa, Audit):
        // =========================================================================
        // $this->call(CompleteDummyDataSeeder::class);

        // Panggil Seeder Produksi: Hanya master data, Tanpa nilai, Tanpa nilai siswa, Tanpa wali kelas, Tanpa user
        $this->call(RoleSeeder::class);
        $this->call(AccountSeeder::class);
        $this->call(ClassSeeder::class);
        $this->call(MapelSeeder::class);
        $this->call(P5MasterSeeder::class);
        $this->call(SettingSeeder::class);
    }
}
