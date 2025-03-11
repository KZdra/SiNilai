<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class MapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table("mata_pelajarans")->truncate();
        Schema::enableForeignKeyConstraints();
        $mapels = [
            ['nama_mapel' => 'Pendidikan Agama dan Budi Pekerti'],
            ['nama_mapel' => 'Pendidikan Pancasila dan Kewarganegaraan'],
            ['nama_mapel' => 'Bahasa Indonesia'],
            ['nama_mapel' => 'Pendidikan Jasmani Olahraga dan Kesehatan'],
            ['nama_mapel' => 'Sejarah'],
            ['nama_mapel' => 'Seni'],
            ['nama_mapel' => 'Matematika'],
            ['nama_mapel' => 'Bahasa Inggris'],
            ['nama_mapel' => 'Informatika'],
            ['nama_mapel' => 'Projek Ilmu Pengetahuan Alam dan Sosial'],
            ['nama_mapel' => 'Dasar Kejuruan'],
            ['nama_mapel' => 'Bahasa Sunda'],
            ['nama_mapel' => 'Bahasa Jepang']
        ];
        foreach ($mapels as $value) {
            DB::table('mata_pelajarans')->insert([
                'nama_mapel' => $value['nama_mapel'],
                'created_at' => Carbon::now(),
            ]);
        }
    }
}
