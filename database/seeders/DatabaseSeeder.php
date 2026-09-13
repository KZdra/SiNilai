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
        $this->call(TestingDataSeeder::class);
        
        // =========================================================================
        // JIKA BUTUH DUMMY LENGKAP DENGAN NILAI TERISI (AUDIT, STATUS KENAIKAN, DLL):
        // Aktifkan baris di bawah ini:
        // =========================================================================
        // $this->call(CompleteDummyDataSeeder::class);
    }
}
