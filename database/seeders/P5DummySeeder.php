<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class P5DummySeeder extends Seeder
{
    /**
     * Seed dummy projek P5 and student ratings for testing.
     */
    public function run(): void
    {
        $class = DB::table('class')->first();
        $fst = DB::table('m_fst_pembelajaran')->first();
        $user = DB::table('users')->first();

        if (!$class || !$fst) {
            $this->command->warn("Kelas atau Semester tidak ditemukan. Lewati P5DummySeeder.");
            return;
        }

        // Ambil beberapa subelemen acak untuk target projek
        $subelemenList = DB::table('p5_subelemen')->limit(6)->get();
        if ($subelemenList->isEmpty()) {
            $this->command->warn("Data subelemen P5 kosong. Harap jalankan P5MasterSeeder terlebih dahulu.");
            return;
        }

        // 1. Buat Projek 1
        $projek1Id = DB::table('p5_projek')->insertGetId([
            'class_id' => $class->id,
            'fst_id' => $fst->id,
            'tema' => 'Gaya Hidup Berkelanjutan',
            'nama_projek' => 'Pengelolaan Sampah Plastik Menjadi Produk Bernilai Guna',
            'deskripsi' => 'Projek ini bertujuan membangun kesadaran peserta didik tentang dampak sampah plastik bagi ekosistem dan melatih keterampilan daur ulang limbah menjadi barang bernilai ekonomis.',
            'fasilitator_id' => $user ? $user->id : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Hubungkan 3 subelemen pertama ke Projek 1
        $target1 = $subelemenList->slice(0, 3);
        foreach ($target1 as $sub) {
            DB::table('p5_projek_subelemen')->updateOrInsert(
                ['projek_id' => $projek1Id, 'subelemen_id' => $sub->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2. Buat Projek 2
        $projek2Id = DB::table('p5_projek')->insertGetId([
            'class_id' => $class->id,
            'fst_id' => $fst->id,
            'tema' => 'Kewirausahaan',
            'nama_projek' => 'Rintisan Usaha Kuliner Berbasis Pangan Lokal Nusantara',
            'deskripsi' => 'Peserta didik mengeksplorasi potensi bahan pangan lokal daerah dan merancang model bisnis mini kuliner dengan kemasan ramah lingkungan.',
            'fasilitator_id' => $user ? $user->id : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Hubungkan 3 subelemen berikutnya ke Projek 2
        $target2 = $subelemenList->slice(3, 3);
        foreach ($target2 as $sub) {
            DB::table('p5_projek_subelemen')->updateOrInsert(
                ['projek_id' => $projek2Id, 'subelemen_id' => $sub->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 3. Nilai Siswa di Kelas Tersebut
        $students = DB::table('students')->where('class_id', $class->id)->get();
        $predikats = ['SB', 'BSH', 'BSH', 'SAB', 'MB'];

        $sampleCatatan = [
            'Sangat aktif berdiskusi dalam kelompok, memiliki ide kreatif pemilahan sampah dan menunjukkan jiwa kepemimpinan.',
            'Mampu bekerja sama dengan rekan kelompok dengan baik dan konsisten menyelesaikan tugas proyek tepat waktu.',
            'Menunjukkan inisiatif tinggi dalam perancangan produk dan mampu mempresentasikan karya dengan percaya diri.',
            'Perlu sedikit dorongan dalam menyampaikan gagasan di depan kelompok, namun sangat tekun saat proses praktik.',
        ];

        foreach ($students as $index => $std) {
            // Penilaian Projek 1
            foreach ($target1 as $subIdx => $sub) {
                $predikat = $predikats[($index + $subIdx) % count($predikats)];
                DB::table('p5_penilaian')->updateOrInsert(
                    [
                        'projek_id' => $projek1Id,
                        'student_id' => $std->id,
                        'subelemen_id' => $sub->id,
                    ],
                    [
                        'predikat' => $predikat,
                        'catatan_proses' => $sampleCatatan[$index % count($sampleCatatan)],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Penilaian Projek 2
            foreach ($target2 as $subIdx => $sub) {
                $predikat = $predikats[($index + $subIdx + 1) % count($predikats)];
                DB::table('p5_penilaian')->updateOrInsert(
                    [
                        'projek_id' => $projek2Id,
                        'student_id' => $std->id,
                        'subelemen_id' => $sub->id,
                    ],
                    [
                        'predikat' => $predikat,
                        'catatan_proses' => $sampleCatatan[($index + 1) % count($sampleCatatan)],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
