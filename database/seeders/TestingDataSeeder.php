<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Faker\Factory as Faker;

class TestingDataSeeder extends Seeder
{
    /**
     * Seeder untuk Master & Operational Data untuk Testing:
     * - Roles & Users (Admin, Wali Kelas berkelas, Guru Mapel non-walas, Akun Siswa)
     * - Profil Sekolah & Settings (Semua modul aktif)
     * - Kelas & Siswa (Lengkap dengan data identitas dan orang tua)
     * - Mata Pelajaran & Mapping Kelas FST
     * - Periode Akademik (Fase E & F, Semester I & II)
     * - Master Ekstrakurikuler
     * - Master Tujuan Pembelajaran (TP)
     * - Master P5 (Dimensi, Elemen, Subelemen)
     * 
     * PERHATIAN: Tabel nilai siswa (values, tpsiswas, nilai_eskuls, p5_penilaian)
     * DIKOSONGKAN / TIDAK DIISI agar pengguna dapat menguji input nilai & upload excel dari nol.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $this->command->info('=== [1/8] Bersihkan Tabel Nilai (Fresh Test Mode) ===');
        Schema::disableForeignKeyConstraints();
        DB::table('values')->truncate();
        DB::table('tpsiswas')->truncate();
        DB::table('nilai_eskuls')->truncate();
        DB::table('p5_penilaian')->truncate();
        DB::table('catatan_walikelas')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info('=== [2/8] Inisialisasi Roles & Profil Sekolah & Settings ===');
        // Roles
        $roles = [
            ['id' => 1, 'role_name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'role_name' => 'guru', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'role_name' => 'siswa', 'created_at' => now(), 'updated_at' => now()],
        ];
        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(['id' => $r['id']], $r);
        }

        // Data Sekolah
        DB::table('data_sekolah')->updateOrInsert(
            ['id' => 1],
            [
                'nama_sekolah' => 'SMK ICB Cinta Teknika Bandung',
                'npsn' => '20219245',
                'nss' => '324026008001',
                'alamat_sekolah' => 'Jl. Pahlawan No. 19B, Cikutra, Kec. Cibeunying Kidul',
                'kode_pos' => 40124,
                'desa_kelurahan' => 'Cikutra',
                'kecamatan' => 'Cibeunying Kidul',
                'kabupaten_kota' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'website' => 'https://smkicbct.sch.id',
                'email' => 'info@smkicbct.sch.id',
                'nama_kepala_sekolah' => 'Dr. H. Rahmat Hidayat, M.M., M.Pd.',
                'nip_kepala_sekolah' => '196805121994031008',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Settings (Semua modul diaktifkan untuk testing)
        $settings = [
            ['key' => 'auth_method', 'value' => 'internal'],
            ['key' => 'module_akademik_enabled', 'value' => '1'],
            ['key' => 'module_p5_enabled', 'value' => '1'],
            ['key' => 'module_cbt_enabled', 'value' => '1'],
            ['key' => 'module_portal_siswa_enabled', 'value' => '1'],
            ['key' => 'module_audit_trail_enabled', 'value' => '1'],
            ['key' => 'sso_client_id', 'value' => ''],
            ['key' => 'sso_client_secret', 'value' => ''],
            ['key' => 'sso_redirect_uri', 'value' => 'http://localhost:8000/auth/callback'],
            ['key' => 'sso_server_url', 'value' => 'http://localhost:8001'],
        ];
        foreach ($settings as $s) {
            \App\Models\Setting::updateOrCreate(['key' => $s['key']], ['value' => $s['value']]);
        }

        $this->command->info('=== [3/8] Inisialisasi Kelas (Rombel) ===');
        $classNames = [
            'X RPL',
            'XI RPL 1',
            'XI RPL 2',
            'XII RPL',
            'X TKJ',
            'XI TKJ',
            'XII TKJ 1',
            'XII TKJ 2',
        ];

        $classes = [];
        foreach ($classNames as $name) {
            $existing = DB::table('class')->where('class_name', $name)->first();
            if ($existing) {
                $classes[$name] = $existing;
            } else {
                $id = DB::table('class')->insertGetId([
                    'class_name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $classes[$name] = (object) ['id' => $id, 'class_name' => $name];
            }
        }

        $this->command->info('=== [4/8] Inisialisasi Akun Pengguna (Admin, Walas, Guru Mapel) ===');
        $users = [
            // Administrator
            [
                'username' => 'admin',
                'name' => 'Administrator Sekolah',
                'email' => 'admin@sinilai.sch.id',
                'role_id' => 1,
                'class_id' => null,
                'student_id' => null,
                'nip' => '198507152010011005',
                'password' => Hash::make('admin'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Wali Kelas X RPL (Class ID 1)
            [
                'username' => 'walikelas1',
                'name' => 'Drs. Budi Santoso, M.Pd (Walas X RPL)',
                'email' => 'walikelas1@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => $classes['X RPL']->id ?? 1,
                'student_id' => null,
                'nip' => '197803122005011003',
                'password' => Hash::make('walikelas1'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Wali Kelas XI RPL 1 (Class ID 2)
            [
                'username' => 'walikelas2',
                'name' => 'Siti Aminah, S.Kom (Walas XI RPL 1)',
                'email' => 'walikelas2@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => $classes['XI RPL 1']->id ?? 2,
                'student_id' => null,
                'nip' => '198904222015022001',
                'password' => Hash::make('walikelas2'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Wali Kelas X TKJ (Class ID 5)
            [
                'username' => 'walikelas3',
                'name' => 'Asep Sunandar, S.T. (Walas X TKJ)',
                'email' => 'walikelas3@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => $classes['X TKJ']->id ?? 5,
                'student_id' => null,
                'nip' => '198205102008011007',
                'password' => Hash::make('walikelas3'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Guru Mapel Non-Walas (Multi-Class Excel Upload Test)
            [
                'username' => 'gurumapel1',
                'name' => 'Rina Nurhasanah, S.Pd (Guru Bahasa Indonesia)',
                'email' => 'gurumapel1@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => null,
                'student_id' => null,
                'nip' => '198711032014022003',
                'password' => Hash::make('gurumapel1'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'gurumapel2',
                'name' => 'Hendra Gunawan, M.Kom (Guru Produktif RPL)',
                'email' => 'gurumapel2@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => null,
                'student_id' => null,
                'nip' => '198006152006041002',
                'password' => Hash::make('gurumapel2'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $u) {
            DB::table('users')->updateOrInsert(['username' => $u['username']], $u);
        }

        $this->command->info('=== [5/8] Inisialisasi Periode FST & Mata Pelajaran & Mapping ===');
        // Periode FST
        $definitions = [
            'E_S1_2425' => ['fase' => 'E', 'semester' => 'I (Satu)', 'tahun_ajaran' => '2024/2025', 'ta' => 'tengah', 'is_locked' => true],
            'E_S2_2425' => ['fase' => 'E', 'semester' => 'II (Dua)', 'tahun_ajaran' => '2024/2025', 'ta' => 'akhir', 'is_locked' => false],
            'F_S1_2425' => ['fase' => 'F', 'semester' => 'I (Satu)', 'tahun_ajaran' => '2024/2025', 'ta' => 'tengah', 'is_locked' => true],
            'F_S2_2425' => ['fase' => 'F', 'semester' => 'II (Dua)', 'tahun_ajaran' => '2024/2025', 'ta' => 'akhir', 'is_locked' => false],
            'E_S1_2526' => ['fase' => 'E', 'semester' => 'I (Satu)', 'tahun_ajaran' => '2025/2026', 'ta' => 'tengah', 'is_locked' => false],
            'F_S1_2526' => ['fase' => 'F', 'semester' => 'I (Satu)', 'tahun_ajaran' => '2025/2026', 'ta' => 'tengah', 'is_locked' => false],
        ];

        $fstMap = [];
        foreach ($definitions as $key => $def) {
            $existing = DB::table('m_fst_pembelajaran')
                ->where('fase', $def['fase'])
                ->where('semester', $def['semester'])
                ->where('tahun_ajaran', $def['tahun_ajaran'])
                ->first();

            if ($existing) {
                DB::table('m_fst_pembelajaran')->where('id', $existing->id)->update([
                    'ta' => $def['ta'],
                    'is_locked' => $def['is_locked'],
                    'updated_at' => now(),
                ]);
                $fstMap[$key] = (object) array_merge((array) $existing, ['ta' => $def['ta'], 'is_locked' => $def['is_locked']]);
            } else {
                $id = DB::table('m_fst_pembelajaran')->insertGetId(array_merge($def, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $fstMap[$key] = (object) array_merge($def, ['id' => $id]);
            }
        }

        // Mata Pelajaran
        $mapelNames = [
            'Pendidikan Agama dan Budi Pekerti',
            'Pendidikan Pancasila dan Kewarganegaraan',
            'Bahasa Indonesia',
            'Pendidikan Jasmani Olahraga dan Kesehatan',
            'Sejarah',
            'Seni Budaya',
            'Matematika',
            'Bahasa Inggris',
            'Informatika',
            'Projek Ilmu Pengetahuan Alam dan Sosial (PIPAS)',
            'Dasar Kejuruan',
            'Bahasa Sunda',
            'Bahasa Jepang'
        ];

        $mapels = [];
        foreach ($mapelNames as $name) {
            $existing = DB::table('mata_pelajarans')->where('nama_mapel', $name)->first();
            if ($existing) {
                $mapels[] = $existing;
            } else {
                $id = DB::table('mata_pelajarans')->insertGetId([
                    'nama_mapel' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $mapels[] = (object) ['id' => $id, 'nama_mapel' => $name];
            }
        }

        // Mapping Mapel-Kelas-FST
        $batchMapping = [];
        foreach ($classes as $cls) {
            $isLevel10 = preg_match('/^X\b/i', $cls->class_name);
            $relevantFstKeys = $isLevel10
                ? ['E_S1_2425', 'E_S2_2425', 'E_S1_2526']
                : ['F_S1_2425', 'F_S2_2425', 'F_S1_2526'];

            foreach ($relevantFstKeys as $fKey) {
                if (!isset($fstMap[$fKey])) continue;
                $fstObj = $fstMap[$fKey];
                foreach ($mapels as $mpl) {
                    $batchMapping[] = [
                        'mapel_id' => $mpl->id,
                        'class_id' => $cls->id,
                        'fst_id' => $fstObj->id,
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($batchMapping, 200) as $chunk) {
            DB::table('mapel_class_fst')->upsert(
                $chunk,
                ['mapel_id', 'class_id', 'fst_id'],
                ['is_active', 'updated_at']
            );
        }

        $this->command->info('=== [6/8] Inisialisasi Data Siswa (12 Siswa per Rombel) ===');
        $sampleNames = [
            'Muhammad Rizky Pratama', 'Ahmad Fauzi Rahman', 'Bagas Aditya Nugraha', 'Dimas Arya Saputra',
            'Fajar Ramadhan Putra', 'Gilang Bayu Permana', 'Hafizh Ilham Maulana', 'Irfan Hakim Maulana',
            'Kevin Alamsyah', 'Naufal Rafi Rabbani', 'Randi Kurniawan', 'Zidane Alfarizi',
            'Annisa Nur Aini', 'Cantika Putri Maharani', 'Dian Lestari Wulandari', 'Fitri Rahmawati',
            'Gisella Azzahra', 'Indah Permatasari', 'Lestari Ayu Ningsih', 'Nabila Shafa Kamila',
            'Putri Salma Zahira', 'Riska Dewi Anggraeni', 'Syifa Aulia Rahma', 'Zahra Amelia'
        ];

        $nameIndex = 0;
        $allInsertedStudents = [];

        foreach ($classes as $cls) {
            $existingCount = DB::table('students')->where('class_id', $cls->id)->count();
            if ($existingCount >= 10) {
                continue;
            }

            for ($i = 1; $i <= 12; $i++) {
                $nama = $sampleNames[$nameIndex % count($sampleNames)];
                if ($nameIndex >= count($sampleNames)) {
                    $nama .= ' ' . $cls->class_name;
                }
                $nameIndex++;

                $isFemale = str_contains($nama, 'Annisa') || str_contains($nama, 'Cantika') || 
                            str_contains($nama, 'Dian') || str_contains($nama, 'Fitri') || 
                            str_contains($nama, 'Gisella') || str_contains($nama, 'Indah') || 
                            str_contains($nama, 'Lestari') || str_contains($nama, 'Nabila') || 
                            str_contains($nama, 'Putri') || str_contains($nama, 'Riska') || 
                            str_contains($nama, 'Syifa') || str_contains($nama, 'Zahra');

                $nis = '2425' . str_pad($cls->id, 2, '0', STR_PAD_LEFT) . str_pad($i, 3, '0', STR_PAD_LEFT);
                $nisn = '00' . rand(7, 9) . rand(1000000, 9999999);

                $stdId = DB::table('students')->insertGetId([
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'class_id' => $cls->id,
                    'jenis_kelamin' => $isFemale ? 'P' : 'L',
                    'tempat_lahir' => 'Bandung',
                    'tanggal_lahir' => Carbon::now()->subYears(rand(15, 17))->subMonths(rand(1, 11))->toDateString(),
                    'agama' => 'Islam',
                    'pendidikan_sebelumnya' => 'SMP Negeri Bandung',
                    'alamat' => $faker->address,
                    'nama_ayah' => 'Bpk. ' . $faker->firstNameMale . ' ' . $faker->lastName,
                    'nama_ibu' => 'Ibu ' . $faker->firstNameFemale . ' ' . $faker->lastName,
                    'pekerjaan_ayah' => $faker->randomElement(['Karyawan Swasta', 'Wiraswasta', 'PNS', 'TNI/Polri', 'Guru']),
                    'pekerjaan_ibu' => $faker->randomElement(['Ibu Rumah Tangga', 'Wiraswasta', 'Karyawan Swasta', 'PNS']),
                    'alamat_orang_tua' => $faker->address,
                    'sakit' => 0,
                    'izin' => 0,
                    'alpa' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $allInsertedStudents[] = [
                    'id' => $stdId,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'class_id' => $cls->id,
                ];
            }
        }

        $this->command->info('=== [7/8] Inisialisasi Master Eskul & Tujuan Pembelajaran (TP) ===');
        // Master Eskul
        $eskulNames = [
            'Pramuka Penegak',
            'Paskibra',
            'Palang Merah Remaja (PMR)',
            'Futsal & Sepakbola',
            'Rohani Islam (Rohis)',
            'English Conversation Club',
            'Pencak Silat'
        ];
        foreach ($eskulNames as $eName) {
            DB::table('m_eskul')->updateOrInsert(['nama_eskul' => $eName], [
                'nama_eskul' => $eName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Master TP Sample (Tanpa Nilai)
        $sampleTp = [
            'Memahami konsep dasar, teori, dan metodologi pembelajaran secara komprehensif.',
            'Menganalisis permasalahan kontekstual dan menemukan solusi pemecahan masalah yang efektif.',
            'Menerapkan prinsip berpikir kritis dan kolaboratif dalam pengerjaan penugasan mandiri maupun kelompok.',
            'Mempresentasikan hasil karya dan gagasan dengan argumentasi yang jelas, logis, dan terstruktur.'
        ];
        foreach ($mapels as $mpl) {
            foreach ($fstMap as $fstObj) {
                $count = DB::table('m_tp')->where('mapel_id', $mpl->id)->where('fst_id', $fstObj->id)->count();
                if ($count === 0) {
                    foreach ($sampleTp as $tpDesc) {
                        DB::table('m_tp')->insert([
                            'mapel_id' => $mpl->id,
                            'fst_id' => $fstObj->id,
                            'class_id' => null,
                            'tp_deskripsi' => $tpDesc,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('=== [8/8] Inisialisasi Master P5 (Dimensi, Elemen, Subelemen) ===');
        $this->call(P5MasterSeeder::class);

        // Akun Siswa (Beberapa sample akun portal siswa dengan role_id = 3)
        $sampleStudents = DB::table('students')->limit(20)->get();
        foreach ($sampleStudents as $std) {
            $username = !empty($std->nisn) ? $std->nisn : $std->nis;
            $exists = DB::table('users')->where('username', $username)->first();
            if (!$exists) {
                DB::table('users')->insert([
                    'name' => $std->nama,
                    'username' => $username,
                    'email' => strtolower(str_replace(' ', '', $username)) . '@siswa.sinilai.sch.id',
                    'password' => Hash::make('siswa123'),
                    'role_id' => 3,
                    'class_id' => $std->class_id,
                    'student_id' => $std->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('========================================================================');
        $this->command->info(' SEEDER DATA TESTING BERHASIL DIJALANKAN!');
        $this->command->info(' Data penting tersedia lengkap, tabel NILAI (values) BERSIH 100% KOSONG.');
        $this->command->info('------------------------------------------------------------------------');
        $this->command->info(' Akun untuk Login Test:');
        $this->command->info(' 1. Admin        : admin / admin');
        $this->command->info(' 2. Wali Kelas 1 : walikelas1 / walikelas1 (Kelas: X RPL)');
        $this->command->info(' 3. Wali Kelas 2 : walikelas2 / walikelas2 (Kelas: XI RPL 1)');
        $this->command->info(' 4. Guru Mapel 1 : gurumapel1 / gurumapel1 (Guru Non-Walas, Uji Upload Excel)');
        $this->command->info(' 5. Guru Mapel 2 : gurumapel2 / gurumapel2 (Guru Non-Walas Produktif)');
        $this->command->info(' 6. Portal Siswa : NISN/NIS siswa / siswa123');
        $this->command->info('========================================================================');
    }
}
