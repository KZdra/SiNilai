<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Insert Fase/Semester/Tahun Ajaran
        $fstId = DB::table('m_fst_pembelajaran')->insertGetId([
            'fase' => 'Fase F',
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'ta' => 'tengah',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Ensure we have Class and Mapel from ClassSeeder & MapelSeeder
        $classes = DB::table('class')->pluck('id')->toArray();
        $mapels = DB::table('mata_pelajarans')->pluck('id')->toArray();
        
        if(empty($classes) || empty($mapels)) {
            // Biarkan jika class atau mapel kosong
            return;
        }

        // 2. Insert Students
        $studentIds = [];
        foreach ($classes as $classId) {
            for ($i = 1; $i <= 10; $i++) {
                $studentIds[] = DB::table('students')->insertGetId([
                    'nis' => $faker->unique()->numerify('#####'),
                    'nisn' => $faker->unique()->numerify('##########'),
                    'nama' => $faker->name,
                    'class_id' => $classId,
                    'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                    'tempat_lahir' => $faker->city,
                    'tanggal_lahir' => $faker->date(),
                    'agama' => 'Islam',
                    'pendidikan_sebelumnya' => 'SMP',
                    'alamat' => $faker->address,
                    'nama_ayah' => $faker->name('male'),
                    'nama_ibu' => $faker->name('female'),
                    'pekerjaan_ayah' => 'Wiraswasta',
                    'pekerjaan_ibu' => 'Ibu Rumah Tangga',
                    'alamat_orang_tua' => $faker->address,
                    'sakit' => random_int(0, 3),
                    'izin' => random_int(0, 3),
                    'alpa' => random_int(0, 1),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }

        // 3. Insert Values (Nilai)
        foreach ($studentIds as $studentId) {
            $classId = DB::table('students')->where('id', $studentId)->value('class_id');
            foreach ($mapels as $mapelId) {
                DB::table('values')->insert([
                    'student_id' => $studentId,
                    'mapel_id' => $mapelId,
                    'class_id' => $classId,
                    'fst_id' => $fstId,
                    'value_daily' => random_int(75, 100),
                    'value_daily_2' => random_int(75, 100),
                    'value_daily_3' => random_int(75, 100),
                    'value_daily_4' => random_int(75, 100),
                    'value_daily_5' => random_int(75, 100),
                    'value_daily_6' => random_int(75, 100),
                    'value_daily_7' => random_int(75, 100),
                    'value_daily_8' => random_int(75, 100),
                    'value_daily_9' => random_int(75, 100),
                    'value_daily_10' => random_int(75, 100),
                    'value_sts' => random_int(75, 100),
                    'value_sas' => random_int(75, 100),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }
        
    }
}
