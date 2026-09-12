<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Carbon\Carbon;

class CompleteDummyDataSeeder extends Seeder
{
    /**
     * Run all-in-one comprehensive dummy data seeds.
     * Dapat dipanggil mandiri: php artisan db:seed --class=CompleteDummyDataSeeder
     */
    public function run(): void
    {
        $this->command->info("=== [1/12] Inisialisasi Kelas & Data Sekolah ===");
        $classes = $this->seedClasses();
        $this->seedSchoolAndSettings();

        $this->command->info("=== [2/12] Inisialisasi Roles & Akun Pengguna ===");
        $this->seedRolesAndUsers($classes);

        $this->command->info("=== [3/12] Inisialisasi Mata Pelajaran ===");
        $mapels = $this->seedMapels();

        $this->command->info("=== [4/12] Inisialisasi FST Lengkap (Fase E & F, Smt 1 & 2 2024/2025, TA Baru 2025/2026) ===");
        $fstMap = $this->seedFstAndMapping($classes, $mapels);

        $this->command->info("=== [5/12] Inisialisasi Master Ekstrakurikuler & TP Lengkap ===");
        $eskuls = $this->seedEskuls();
        $this->seedTujuanPembelajaran($mapels, $fstMap);

        $this->command->info("=== [6/12] Inisialisasi Data Siswa (12 Siswa per Rombel = 144 Siswa) ===");
        $studentsByClass = $this->seedStudents($classes);

        $this->command->info("=== [7/12] Menghasilkan Penilaian Formatif (TP Siswa Smt 1 & 2) ===");
        $this->seedFormatifScores($classes, $studentsByClass, $fstMap);

        $this->command->info("=== [8/12] Menghasilkan Penilaian Sumatif Lengkap (STS, SAS, UH Smt 1 & 2) ===");
        $this->seedSumatifScores($classes, $studentsByClass, $mapels, $fstMap);

        $this->command->info("=== [9/12] Menghasilkan Nilai Eskul & Catatan Walas (Status Kenaikan, Kelulusan & Tinggal Kelas) ===");
        $this->seedEskulAndCatatanWalas($classes, $studentsByClass, $eskuls, $fstMap);

        $this->command->info("=== [10/12] Inisialisasi Master & Penilaian Projek P5 Lengkap ===");
        $this->seedP5($classes, $fstMap);

        $this->command->info("=== [11/12] Menghasilkan Akun Portal Mandiri 144 Siswa (Password: siswa123) ===");
        $this->seedStudentPortalAccounts($studentsByClass);

        $this->command->info("=== [12/12] Menghasilkan Audit Trail & Riwayat Sinkronisasi CBT ===");
        $this->seedAuditAndCbtLogs($classes, $fstMap);

        $this->command->info("==========================================================");
        $this->command->info(" SEEDER SELESAI DENGAN SUKSES! DATA DUMMY LENGKAP TERSEDIA");
        $this->command->info(" Struktur Rombel : 12 Kelas (X, XI, XII RPL 1-2 & TKJ 1-2)");
        $this->command->info(" Data Nilai      : Lengkap Semester I (Ganjil) & II (Genap)");
        $this->command->info(" Status Kenaikan : Siap Diuji pada Menu Kenaikan Kelas / Tutup Tahun");
        $this->command->info("   - Kelas X  : 46 Siswa Naik ke XI, 2 Tinggal di Kelas");
        $this->command->info("   - Kelas XI : 47 Siswa Naik ke XII, 1 Tinggal di Kelas");
        $this->command->info("   - Kelas XII: 48 Siswa Lulus");
        $this->command->info(" Akun Admin      : admin / admin");
        $this->command->info(" Akun Walas 10   : walikelas1 / walikelas1 (X RPL 1), walikelas2 / walikelas2 (X TKJ 1)");
        $this->command->info(" Akun Walas 11   : walikelas3 / walikelas3 (XI RPL 1), walikelas4 / walikelas4 (XI TKJ 1)");
        $this->command->info(" Akun Walas 12   : walikelas5 / walikelas5 (XII RPL 1), walikelas6 / walikelas6 (XII TKJ 1)");
        $this->command->info(" Akun Siswa      : Username NISN atau NIS / Password: siswa123");
        $this->command->info("==========================================================");
    }

    /**
     * 1. Roles & Default System Users
     */
    private function seedRolesAndUsers(array $classes): void
    {
        $roles = [
            ['id' => 1, 'role_name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'role_name' => 'guru', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'role_name' => 'siswa', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(['id' => $r['id']], $r);
        }

        $classMap = collect($classes)->keyBy('class_name');

        $users = [
            [
                'username' => 'admin',
                'name' => 'Indra Hardika (Administrator)',
                'email' => 'admin@sinilai.sch.id',
                'role_id' => 1,
                'class_id' => null,
                'student_id' => null,
                'nip' => '198507152010011005',
                'password' => Hash::make('admin'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Walas Kelas 10
            [
                'username' => 'walikelas1',
                'name' => 'Drs. Budi Santoso, M.Pd (Walas X RPL 1)',
                'email' => 'budisantoso@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => $classMap['X RPL 1']->id ?? ($classes[0]->id ?? null),
                'student_id' => null,
                'nip' => '197803122005011003',
                'password' => Hash::make('walikelas1'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'walikelas2',
                'name' => 'Siti Aminah, S.Kom (Walas X TKJ 1)',
                'email' => 'sitiaminah@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => $classMap['X TKJ 1']->id ?? ($classes[2]->id ?? null),
                'student_id' => null,
                'nip' => '198904222015022001',
                'password' => Hash::make('walikelas2'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Walas Kelas 11
            [
                'username' => 'walikelas3',
                'name' => 'Asep Sunandar, S.T. (Walas XI RPL 1)',
                'email' => 'asepsunandar@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => $classMap['XI RPL 1']->id ?? ($classes[4]->id ?? null),
                'student_id' => null,
                'nip' => '198205102008011007',
                'password' => Hash::make('walikelas3'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'walikelas4',
                'name' => 'Rina Nurhasanah, S.Pd (Walas XI TKJ 1)',
                'email' => 'rinanur@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => $classMap['XI TKJ 1']->id ?? ($classes[6]->id ?? null),
                'student_id' => null,
                'nip' => '198711032014022003',
                'password' => Hash::make('walikelas4'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Walas Kelas 12
            [
                'username' => 'walikelas5',
                'name' => 'Hendra Gunawan, M.Kom (Walas XII RPL 1)',
                'email' => 'hendragunawan@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => $classMap['XII RPL 1']->id ?? ($classes[8]->id ?? null),
                'student_id' => null,
                'nip' => '198006152006041002',
                'password' => Hash::make('walikelas5'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'walikelas6',
                'name' => 'Dewi Sartika, S.Pd (Walas XII TKJ 1)',
                'email' => 'dewisartika@sinilai.sch.id',
                'role_id' => 2,
                'class_id' => $classMap['XII TKJ 1']->id ?? ($classes[10]->id ?? null),
                'student_id' => null,
                'nip' => '198409282010012015',
                'password' => Hash::make('walikelas6'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $u) {
            DB::table('users')->updateOrInsert(['username' => $u['username']], $u);
        }
    }

    /**
     * 2. Data Sekolah & Settings
     */
    private function seedSchoolAndSettings(): void
    {
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

        $settings = [
            ['key' => 'auth_method', 'value' => 'internal'],
            ['key' => 'sso_client_id', 'value' => '9d67...'],
            ['key' => 'sso_client_secret', 'value' => 'secret...'],
            ['key' => 'sso_redirect_uri', 'value' => 'http://localhost:8002/auth/callback'],
            ['key' => 'sso_server_url', 'value' => 'http://localhost:8001'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }

    /**
     * 3. Classes (12 Rombel: X, XI, XII dengan Kompetensi RPL & TKJ)
     */
    private function seedClasses(): array
    {
        $classNames = [
            // Tingkat 10 (Fase E)
            'X RPL 1',
            'X RPL 2',
            'X TKJ 1',
            'X TKJ 2',
            // Tingkat 11 (Fase F)
            'XI RPL 1',
            'XI RPL 2',
            'XI TKJ 1',
            'XI TKJ 2',
            // Tingkat 12 (Fase F)
            'XII RPL 1',
            'XII RPL 2',
            'XII TKJ 1',
            'XII TKJ 2',
        ];

        $classes = [];
        foreach ($classNames as $name) {
            $existing = DB::table('class')->where('class_name', $name)->first();
            if ($existing) {
                $classes[] = $existing;
            } else {
                $id = DB::table('class')->insertGetId([
                    'class_name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $classes[] = (object) ['id' => $id, 'class_name' => $name];
            }
        }
        return $classes;
    }

    /**
     * 3b. Mapels
     */
    private function seedMapels(): array
    {
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
            'Dasar-Dasar Kejuruan',
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
        return $mapels;
    }

    /**
     * 4. Master FST & Mapping Rombel-Mapel
     */
    private function seedFstAndMapping(array $classes, array $mapels): array
    {
        $definitions = [
            // Tahun Ajaran 2024/2025
            'E_S1_2425' => [
                'fase' => 'E',
                'semester' => 'I (Satu)',
                'tahun_ajaran' => '2024/2025',
                'ta' => 'tengah',
                'is_locked' => true,
            ],
            'E_S2_2425' => [
                'fase' => 'E',
                'semester' => 'II (Dua)',
                'tahun_ajaran' => '2024/2025',
                'ta' => 'akhir',
                'is_locked' => false,
            ],
            'F_S1_2425' => [
                'fase' => 'F',
                'semester' => 'I (Satu)',
                'tahun_ajaran' => '2024/2025',
                'ta' => 'tengah',
                'is_locked' => true,
            ],
            'F_S2_2425' => [
                'fase' => 'F',
                'semester' => 'II (Dua)',
                'tahun_ajaran' => '2024/2025',
                'ta' => 'akhir',
                'is_locked' => false,
            ],
            // Tahun Ajaran Baru 2025/2026
            'E_S1_2526' => [
                'fase' => 'E',
                'semester' => 'I (Satu)',
                'tahun_ajaran' => '2025/2026',
                'ta' => 'tengah',
                'is_locked' => false,
            ],
            'F_S1_2526' => [
                'fase' => 'F',
                'semester' => 'I (Satu)',
                'tahun_ajaran' => '2025/2026',
                'ta' => 'tengah',
                'is_locked' => false,
            ],
        ];

        $fstMap = [];
        foreach ($definitions as $key => $def) {
            $existing = DB::table('m_fst_pembelajaran')
                ->where('fase', $def['fase'])
                ->where('semester', $def['semester'])
                ->where('tahun_ajaran', $def['tahun_ajaran'])
                ->first();

            if ($existing) {
                // Update status is_locked & ta
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

        // Mapping Mapel-Kelas-FST
        $batchMapping = [];
        foreach ($classes as $cls) {
            $isLevel10 = preg_match('/^X\b/i', $cls->class_name);
            
            // FST yang berlaku untuk kelas ini
            $relevantFstKeys = $isLevel10
                ? ['E_S1_2425', 'E_S2_2425', 'E_S1_2526']
                : ['F_S1_2425', 'F_S2_2425', 'F_S1_2526'];

            foreach ($relevantFstKeys as $fKey) {
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

        foreach (array_chunk($batchMapping, 300) as $chunk) {
            DB::table('mapel_class_fst')->upsert(
                $chunk,
                ['mapel_id', 'class_id', 'fst_id'],
                ['is_active', 'updated_at']
            );
        }

        return $fstMap;
    }

    /**
     * 5. Master Ekstrakurikuler
     */
    private function seedEskuls(): array
    {
        $names = [
            'Pramuka Penegak',
            'Paskibra',
            'Palang Merah Remaja (PMR)',
            'Futsal & Sepakbola',
            'Rohani Islam (Rohis)',
            'English Conversation Club'
        ];

        $eskuls = [];
        foreach ($names as $name) {
            $existing = DB::table('m_eskul')->where('nama_eskul', $name)->first();
            if ($existing) {
                $eskuls[] = $existing;
            } else {
                $id = DB::table('m_eskul')->insertGetId([
                    'nama_eskul' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $eskuls[] = (object) ['id' => $id, 'nama_eskul' => $name];
            }
        }
        return $eskuls;
    }

    /**
     * 5b. Tujuan Pembelajaran (TP) Lengkap untuk Fase E & F
     */
    private function seedTujuanPembelajaran(array $mapels, array $fstMap): void
    {
        $tpS1 = [
            'Memahami konsep dasar, teori, dan metodologi pembelajaran pada semester ganjil.',
            'Menganalisis permasalahan kontekstual dan menemukan solusi pemecahan masalah yang efektif.',
            'Menerapkan prinsip berpikir kritis dan kolaboratif dalam pengerjaan tugas proyek.',
            'Mempresentasikan hasil karya dan gagasan dengan argumentasi yang jelas dan terstruktur.'
        ];

        $tpS2 = [
            'Mengembangkan aplikasi dan implementasi konsep lanjutan pada semester genap.',
            'Menganalisis studi kasus kejuruan dan merumuskan solusi berbasis teknologi terkini.',
            'Berkolaborasi aktif dalam penugasan akhir dan produk karya mandiri berkualitas.',
            'Mendokumentasikan hasil belajar dan mempublikasikan laporan secara sistematis.'
        ];

        $fstTargets = [
            $fstMap['E_S1_2425']->id => $tpS1,
            $fstMap['E_S2_2425']->id => $tpS2,
            $fstMap['F_S1_2425']->id => $tpS1,
            $fstMap['F_S2_2425']->id => $tpS2,
        ];

        $batchTp = [];
        foreach ($fstTargets as $fstId => $samples) {
            foreach ($mapels as $mpl) {
                $existingCount = DB::table('m_tp')->where('mapel_id', $mpl->id)->where('fst_id', $fstId)->count();
                if ($existingCount === 0) {
                    foreach ($samples as $desc) {
                        $batchTp[] = [
                            'mapel_id' => $mpl->id,
                            'fst_id' => $fstId,
                            'class_id' => null,
                            'tp_deskripsi' => $desc,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        if (!empty($batchTp)) {
            foreach (array_chunk($batchTp, 300) as $chunk) {
                DB::table('m_tp')->insert($chunk);
            }
        }
    }

    /**
     * 6. Siswa (12 Siswa per Kelas = 144 Siswa)
     */
    private function seedStudents(array $classes): array
    {
        $faker = Faker::create('id_ID');
        $studentsByClass = [];

        $sampleNames = [
            'Muhammad Rizky Pratama', 'Ahmad Fauzi Rahman', 'Bagas Aditya Nugraha', 'Dimas Arya Saputra',
            'Fajar Ramadhan Putra', 'Gilang Bayu Permana', 'Hafizh Ilham Maulana', 'Irfan Hakim Maulana',
            'Kevin Alamsyah', 'Naufal Rafi Rabbani', 'Randi Kurniawan', 'Zidane Alfarizi',
            'Annisa Nur Aini', 'Cantika Putri Maharani', 'Dian Lestari Wulandari', 'Fitri Rahmawati',
            'Gisella Azzahra', 'Indah Permatasari', 'Lestari Ayu Ningsih', 'Nabila Shafa Kamila',
            'Putri Salma Zahira', 'Riska Dewi Anggraeni', 'Syifa Aulia Rahma', 'Zahra Amelia'
        ];

        $nameIndex = 0;

        foreach ($classes as $cls) {
            $studentsByClass[$cls->id] = [];
            
            $existing = DB::table('students')->where('class_id', $cls->id)->orderBy('id', 'asc')->get();
            if ($existing->count() >= 12) {
                $studentsByClass[$cls->id] = $existing->toArray();
                continue;
            }

            for ($i = 1; $i <= 12; $i++) {
                $nama = $sampleNames[$nameIndex % count($sampleNames)] . ($nameIndex >= count($sampleNames) ? ' ' . ($cls->id) : '');
                $nameIndex++;
                $isMale = !str_contains($nama, 'Annisa') && !str_contains($nama, 'Cantika') && !str_contains($nama, 'Dian') && !str_contains($nama, 'Fitri') && !str_contains($nama, 'Gisella') && !str_contains($nama, 'Indah') && !str_contains($nama, 'Lestari') && !str_contains($nama, 'Nabila') && !str_contains($nama, 'Putri') && !str_contains($nama, 'Riska') && !str_contains($nama, 'Syifa') && !str_contains($nama, 'Zahra');

                $nis = '2425' . str_pad($cls->id, 2, '0', STR_PAD_LEFT) . str_pad($i, 3, '0', STR_PAD_LEFT);
                $nisn = '00' . rand(7, 9) . rand(1000000, 9999999);

                $stdId = DB::table('students')->insertGetId([
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'class_id' => $cls->id,
                    'jenis_kelamin' => $isMale ? 'L' : 'P',
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
                    'sakit' => rand(0, 2),
                    'izin' => rand(0, 2),
                    'alpa' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $studentsByClass[$cls->id][] = (object) [
                    'id' => $stdId,
                    'nama' => $nama,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'class_id' => $cls->id
                ];
            }
        }

        return $studentsByClass;
    }

    /**
     * 7. Penilaian Formatif (TP Siswa) untuk Smt 1 & Smt 2
     */
    private function seedFormatifScores(array $classes, array $studentsByClass, array $fstMap): void
    {
        $batchFormatif = [];

        foreach ($classes as $cls) {
            $isLevel10 = preg_match('/^X\b/i', $cls->class_name);
            $fstKeys = $isLevel10 ? ['E_S1_2425', 'E_S2_2425'] : ['F_S1_2425', 'F_S2_2425'];
            $students = $studentsByClass[$cls->id] ?? [];

            foreach ($fstKeys as $fKey) {
                $fstId = $fstMap[$fKey]->id;
                $tpList = DB::table('m_tp')->where('fst_id', $fstId)->limit(6)->get();
                if ($tpList->isEmpty()) continue;

                foreach ($students as $std) {
                    foreach ($tpList as $tp) {
                        $batchFormatif[] = [
                            'siswa_id' => $std->id,
                            'class_id' => $cls->id,
                            'mapel_id' => $tp->mapel_id,
                            'tp_id' => $tp->id,
                            'fst_id' => $fstId,
                            'kktp' => rand(1, 10) > 1 ? 1 : 0, // 90% tercapai
                            'tampilkan' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($batchFormatif, 400) as $chunk) {
            DB::table('tpsiswas')->upsert(
                $chunk,
                ['siswa_id', 'class_id', 'mapel_id', 'tp_id', 'fst_id'],
                ['kktp', 'tampilkan', 'updated_at']
            );
        }
    }

    /**
     * 8. Penilaian Sumatif (STS, SAS, Nilai Harian) untuk Smt 1 & Smt 2
     */
    private function seedSumatifScores(array $classes, array $studentsByClass, array $mapels, array $fstMap): void
    {
        $batchValues = [];

        foreach ($classes as $cls) {
            $isLevel10 = preg_match('/^X\b/i', $cls->class_name);
            $fstKeys = $isLevel10 ? ['E_S1_2425', 'E_S2_2425'] : ['F_S1_2425', 'F_S2_2425'];
            $students = $studentsByClass[$cls->id] ?? [];

            foreach ($fstKeys as $fKey) {
                $fstId = $fstMap[$fKey]->id;
                $isS2 = str_contains($fKey, '_S2_');

                foreach ($students as $idx => $std) {
                    // Cek jika siswa sampel tinggal kelas (siswa ke-12 di X RPL 1, X TKJ 1, XI RPL 1)
                    $isRetainedSample = ($idx === 11 && (
                        $cls->class_name === 'X RPL 1' ||
                        $cls->class_name === 'X TKJ 1' ||
                        $cls->class_name === 'XI RPL 1'
                    ));

                    foreach ($mapels as $mpl) {
                        if ($isRetainedSample && $isS2) {
                            $valDaily1 = rand(58, 68);
                            $valDaily2 = rand(60, 69);
                            $valDaily3 = rand(55, 67);
                            $valSts    = rand(60, 68);
                            $valSas    = rand(62, 69);
                        } else {
                            $valDaily1 = rand(76, 96);
                            $valDaily2 = rand(78, 98);
                            $valDaily3 = rand(75, 95);
                            $valSts    = rand(76, 95);
                            $valSas    = rand(78, 97);
                        }

                        $batchValues[] = [
                            'student_id' => $std->id,
                            'class_id' => $cls->id,
                            'mapel_id' => $mpl->id,
                            'fst_id' => $fstId,
                            'value_daily' => $valDaily1,
                            'value_daily_2' => $valDaily2,
                            'value_daily_3' => $valDaily3,
                            'value_daily_4' => rand(75, 95),
                            'value_daily_5' => rand(75, 95),
                            'value_sts' => $valSts,
                            'value_sas' => $valSas,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($batchValues, 400) as $chunk) {
            DB::table('values')->upsert(
                $chunk,
                ['student_id', 'class_id', 'mapel_id', 'fst_id'],
                ['value_daily', 'value_daily_2', 'value_daily_3', 'value_daily_4', 'value_daily_5', 'value_sts', 'value_sas', 'updated_at']
            );
        }
    }

    /**
     * 9. Nilai Eskul & Catatan Walas (Smt 1 & Smt 2 dengan Keputusan Kenaikan & Kelulusan)
     */
    private function seedEskulAndCatatanWalas(array $classes, array $studentsByClass, array $eskuls, array $fstMap): void
    {
        $batchEskul = [];
        $batchWalas = [];

        foreach ($classes as $cls) {
            $isLevel10 = preg_match('/^X\b/i', $cls->class_name);
            $isLevel11 = preg_match('/^XI\b/i', $cls->class_name);
            $isLevel12 = preg_match('/^XII\b/i', $cls->class_name);

            $fstKeys = $isLevel10 ? ['E_S1_2425', 'E_S2_2425'] : ['F_S1_2425', 'F_S2_2425'];
            $students = $studentsByClass[$cls->id] ?? [];

            foreach ($fstKeys as $fKey) {
                $fstId = $fstMap[$fKey]->id;
                $isS2  = str_contains($fKey, '_S2_');

                foreach ($students as $idx => $std) {
                    // Eskul
                    $selectedEskul = $eskuls[$idx % count($eskuls)];
                    $batchEskul[] = [
                        'eskul_id' => $selectedEskul->id,
                        'student_id' => $std->id,
                        'fst_id' => $fstId,
                        'nilai_eskul' => rand(0, 1) === 1 ? 'Sangat Baik' : 'Baik',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Catatan Walas & Status Kenaikan
                    $token = Str::random(32);
                    $sakit = rand(0, 2);
                    $izin  = rand(0, 2);
                    $alpa  = 0;
                    $statusKenaikan = null;
                    $catatan = 'Ananda aktif dalam pembelajaran semester ganjil dan menunjukkan perkembangan yang positif.';

                    if ($isS2) {
                        // Semester 2: Penentuan Kenaikan Kelas / Kelulusan
                        $isRetainedSample = ($idx === 11 && (
                            $cls->class_name === 'X RPL 1' ||
                            $cls->class_name === 'X TKJ 1' ||
                            $cls->class_name === 'XI RPL 1'
                        ));

                        if ($isRetainedSample) {
                            $sakit = 4;
                            $izin  = 5;
                            $alpa  = 14;
                            $statusKenaikan = 'Tinggal di Kelas';
                            $catatan = 'Berdasarkan evaluasi tingkat kehadiran serta ketuntasan belajar yang belum tercapai, diputuskan ananda Tinggal di Kelas.';
                        } elseif ($isLevel10) {
                            $statusKenaikan = 'Naik ke Kelas XI';
                            $catatan = 'Selamat atas ketuntasan seluruh capaian pembelajaran Fase E dan berhasil Naik ke Kelas XI.';
                        } elseif ($isLevel11) {
                            $statusKenaikan = 'Naik ke Kelas XII';
                            $catatan = 'Prestasi praktik kejuruan sangat baik. Selamat atas keberhasilan Naik ke Kelas XII.';
                        } elseif ($isLevel12) {
                            $statusKenaikan = 'Lulus';
                            $catatan = 'Selamat dan sukses atas kelulusan dari SMK ICB Cinta Teknika Bandung! Jadilah teknisi unggul dan berakhlak mulia.';
                        }
                    }

                    $batchWalas[] = [
                        'student_id' => $std->id,
                        'fst_id' => $fstId,
                        'class_id' => $cls->id,
                        'sakit' => $sakit,
                        'izin' => $izin,
                        'alpa' => $alpa,
                        'catatan' => $catatan,
                        'status_kenaikan' => $statusKenaikan,
                        'verification_token' => $token,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($batchEskul, 300) as $chunk) {
            DB::table('nilai_eskuls')->upsert(
                $chunk,
                ['eskul_id', 'student_id', 'fst_id'],
                ['nilai_eskul', 'updated_at']
            );
        }

        foreach (array_chunk($batchWalas, 300) as $chunk) {
            DB::table('catatan_walikelas')->upsert(
                $chunk,
                ['student_id', 'fst_id'],
                ['class_id', 'sakit', 'izin', 'alpa', 'catatan', 'status_kenaikan', 'verification_token', 'updated_at']
            );
        }
    }

    /**
     * 10. Projek P5 & Penilaian untuk Fase E & F
     */
    private function seedP5(array $classes, array $fstMap): void
    {
        $this->call(P5MasterSeeder::class);

        $subelemenList = DB::table('p5_subelemen')->limit(8)->get();
        if ($subelemenList->isEmpty()) return;

        $guru = DB::table('users')->where('role_id', 2)->first() ?? DB::table('users')->first();
        $classX = $classes[0]; // X RPL 1
        $classXI = $classes[4] ?? $classes[0]; // XI RPL 1

        $p5Configs = [
            [
                'class' => $classX,
                'fst_id' => $fstMap['E_S1_2425']->id,
                'tema' => 'Gaya Hidup Berkelanjutan',
                'nama' => 'Eco-Bricks: Pemanfaatan Limbah Plastik Menjadi Produk Bernilai Guna',
                'deskripsi' => 'Projek ini melatih peserta didik mengenali dampak limbah plastik terhadap lingkungan sekolah dan menciptakan solusi daur ulang.',
            ],
            [
                'class' => $classX,
                'fst_id' => $fstMap['E_S2_2425']->id,
                'tema' => 'Kewirausahaan',
                'nama' => 'Digital Creative Agency & Mini Web Studio',
                'deskripsi' => 'Peserta didik merancang produk jasa digital dan membuat portofolio komersial ramah lingkungan.',
            ],
            [
                'class' => $classXI,
                'fst_id' => $fstMap['F_S1_2425']->id,
                'tema' => 'Rekayasa dan Teknologi',
                'nama' => 'Smart IoT School Automation & Energy Saver',
                'deskripsi' => 'Peserta didik merancang prototipe otomatisasi perangkat sekolah berbasis mikrokontroler hemat energi.',
            ],
        ];

        foreach ($p5Configs as $cfg) {
            $existingP = DB::table('p5_projek')
                ->where('class_id', $cfg['class']->id)
                ->where('fst_id', $cfg['fst_id'])
                ->where('nama_projek', $cfg['nama'])
                ->first();

            $projekId = $existingP ? $existingP->id : DB::table('p5_projek')->insertGetId([
                'class_id' => $cfg['class']->id,
                'fst_id' => $cfg['fst_id'],
                'tema' => $cfg['tema'],
                'nama_projek' => $cfg['nama'],
                'deskripsi' => $cfg['deskripsi'],
                'fasilitator_id' => $guru ? $guru->id : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $targets = $subelemenList->slice(0, 3);
            foreach ($targets as $sub) {
                DB::table('p5_projek_subelemen')->updateOrInsert(
                    ['projek_id' => $projekId, 'subelemen_id' => $sub->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            // Nilai siswa untuk projek ini
            $students = DB::table('students')->where('class_id', $cfg['class']->id)->get();
            $predikats = ['BSH', 'SB', 'SAB', 'BSH'];

            $batchP5Nilai = [];
            foreach ($students as $sIdx => $std) {
                foreach ($targets as $tIdx => $sub) {
                    $batchP5Nilai[] = [
                        'projek_id' => $projekId,
                        'student_id' => $std->id,
                        'subelemen_id' => $sub->id,
                        'predikat' => $predikats[($sIdx + $tIdx) % count($predikats)],
                        'catatan_proses' => 'Peserta didik menunjukkan partisipasi aktif dan tanggung jawab tinggi selama pengerjaan projek.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            foreach (array_chunk($batchP5Nilai, 200) as $chunk) {
                DB::table('p5_penilaian')->upsert(
                    $chunk,
                    ['projek_id', 'student_id', 'subelemen_id'],
                    ['predikat', 'catatan_proses', 'updated_at']
                );
            }
        }
    }

    /**
     * 11. Student Portal Accounts
     */
    private function seedStudentPortalAccounts(array $studentsByClass): void
    {
        $count = 0;
        foreach ($studentsByClass as $classId => $students) {
            foreach ($students as $std) {
                $username = !empty($std->nisn) ? $std->nisn : $std->nis;

                DB::table('users')->updateOrInsert(
                    ['username' => $username],
                    [
                        'name' => $std->nama,
                        'email' => strtolower($username) . '@siswa.sinilai.sch.id',
                        'password' => Hash::make('siswa123'),
                        'role_id' => 3, // Role Siswa
                        'class_id' => $std->class_id,
                        'student_id' => $std->id,
                        'nip' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $count++;
            }
        }
        $this->command->info("-> Berhasil membuat {$count} akun portal mandiri siswa.");
    }

    /**
     * 12. Audit Logs & CBT Sync Logs
     */
    private function seedAuditAndCbtLogs(array $classes, array $fstMap): void
    {
        $admin = DB::table('users')->where('role_id', 1)->first();
        $student = DB::table('students')->first();
        $mapel = DB::table('mata_pelajarans')->first();
        $activeFst = $fstMap['E_S2_2425']->id;

        if (!$student || !$mapel) return;

        $auditSamples = [
            [
                'user_id' => $admin ? $admin->id : 1,
                'student_id' => $student->id,
                'mapel_id' => $mapel->id,
                'fst_id' => $activeFst,
                'action' => 'INPUT_BARU',
                'old_values' => null,
                'new_values' => json_encode(['value_daily' => 85, 'value_sts' => 88, 'value_sas' => 90]),
                'field' => null,
                'old_value' => null,
                'new_value' => null,
                'reason' => 'Input nilai awal semester genap oleh walas',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/128.0',
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => $admin ? $admin->id : 1,
                'student_id' => $student->id,
                'mapel_id' => $mapel->id,
                'fst_id' => $activeFst,
                'action' => 'SYNC_CBT',
                'old_values' => json_encode(['value_sas' => 90]),
                'new_values' => json_encode(['value_sas' => 95]),
                'field' => 'value_sas',
                'old_value' => '90',
                'new_value' => '95',
                'reason' => 'Sinkronisasi nilai ujian akhir SAS otomatis dari server CBT',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'SiNilai-CBT-SyncClient/1.0',
                'created_at' => now()->subHours(3),
            ]
        ];

        foreach ($auditSamples as $log) {
            DB::table('nilai_audit_logs')->insert($log);
        }

        $cbtLogs = [
            [
                'class_id' => $student->class_id,
                'mapel_id' => $mapel->id,
                'fst_id' => $activeFst,
                'user_id' => $admin ? $admin->id : 1,
                'sync_type' => 'pull',
                'target_field' => 'value_sas',
                'total_synced' => 12,
                'total_failed' => 0,
                'status' => 'success',
                'message' => 'Sinkronisasi nilai ujian SAS dari server CBT berhasil disematkan ke 12 siswa.',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]
        ];

        foreach ($cbtLogs as $clog) {
            DB::table('cbt_sync_logs')->insert($clog);
        }
    }
}
