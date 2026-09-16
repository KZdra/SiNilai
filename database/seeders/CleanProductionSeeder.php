<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CleanProductionSeeder extends Seeder
{
    /**
     * Seeder Bersih untuk Deploy Baru / Produksi (Fresh Deployment):
     * - Hanya mengisi: Roles, 1 Akun Admin Utama, Master Standar P5 Kemdikbud, dan Settings.
     * - 100% BEBAS DARI data dummy (Tanpa siswa, tanpa nilai, tanpa kelas dummy, tanpa guru dummy).
     * - Admin baru dapat langsung login dan melengkapi data melalui checklist "Kesiapan Master Data" di dashboard.
     * 
     * Cara menjalankan:
     * php artisan migrate:fresh --seed --seeder=CleanProductionSeeder
     * atau
     * php artisan db:seed --class=CleanProductionSeeder
     */
    public function run(): void
    {
        $this->command->info("=== [1/4] Menginisialisasi Roles Sistem ===");
        $this->call(RoleSeeder::class);

        $this->command->info("=== [2/4] Membuat Akun Administrator Utama ===");
        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            [
                'name' => 'Admin Sekolah',
                'email' => 'admin@sekolah.sch.id',
                'role_id' => 1,
                'class_id' => null,
                'student_id' => null,
                'nip' => null,
                'password' => Hash::make('admin'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info("=== [3/4] Menginisialisasi Master P5 Standar Kemdikbudristek ===");
        $this->call(P5MasterSeeder::class);

        $this->command->info("=== [4/4] Menginisialisasi Pengaturan Sistem & Modul ===");
        $this->call(SettingSeeder::class);

        \App\Services\MasterDataCache::clearAll();

        $this->command->info("===============================================================");
        $this->command->info(" SEEDER BERSIH PRODUKSI BERHASIL DIJALANKAN!");
        $this->command->info(" Login Admin: Username 'admin', Password 'admin'");
        $this->command->info(" Sistem 100% bersih dan siap diisi master data sekolah baru.");
        $this->command->info("===============================================================");
    }
}
