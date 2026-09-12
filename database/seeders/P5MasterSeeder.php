<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class P5MasterSeeder extends Seeder
{
    /**
     * Seed 6 Dimensi, Elemen, dan Subelemen Profil Pelajar Pancasila (Standar Kemendikbudristek).
     */
    public function run(): void
    {
        if (DB::table('p5_dimensi')->count() > 0) {
            return;
        }

        $dimensiData = [
            [
                'kode' => 'D1',
                'nama_dimensi' => 'Beriman, Bertakwa Kepada Tuhan YME, dan Berakhlak Mulia',
                'elemen' => [
                    [
                        'nama_elemen' => 'Akhlak Beragama',
                        'subelemen' => [
                            ['nama_subelemen' => 'Mengenal dan Mencintai Tuhan YME', 'capaian_fase' => 'Menerapkan pemahaman tentang kualitas atau sifat-sifat Tuhan dalam ritual ibadah dan kehidupan sehari-hari.'],
                            ['nama_subelemen' => 'Pemahaman Agama/Kepercayaan', 'capaian_fase' => 'Memahami struktur organisasi, unsur-unsur utama agama/kepercayaan dalam konteks Indonesia.'],
                            ['nama_subelemen' => 'Pelaksanaan Ritual Ibadah', 'capaian_fase' => 'Melaksanakan ibadah secara rutin dan mandiri sesuai dengan tuntunan agama/kepercayaan.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Akhlak Pribadi',
                        'subelemen' => [
                            ['nama_subelemen' => 'Integritas', 'capaian_fase' => 'Menyadari bahwa aturan agama dan sosial merupakan aturan yang baik dan menunjang kebaikan diri.'],
                            ['nama_subelemen' => 'Merawat Diri secara Fisik, Mental, dan Spiritual', 'capaian_fase' => 'Melakukan aktivitas fisik, sosial, dan ibadah secara seimbang demi kesehatan jiwa dan raga.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Akhlak kepada Manusia',
                        'subelemen' => [
                            ['nama_subelemen' => 'Mengutamakan persamaan dengan orang lain dan menghargai perbedaan', 'capaian_fase' => 'Mengidentifikasi hal yang menjadi kesamaan dan menghargai perbedaan budaya, pendapat, dan keyakinan.'],
                            ['nama_subelemen' => 'Berempati kepada orang lain', 'capaian_fase' => 'Memahami perasaan dan sudut pandang orang lain dan memberikan respons yang penuh kasih.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Akhlak kepada Alam',
                        'subelemen' => [
                            ['nama_subelemen' => 'Memahami Keterhubungan Ekosistem Bumi', 'capaian_fase' => 'Mengidentifikasi masalah lingkungan hidup di tempat ia tinggal dan melakukan langkah pelestarian.'],
                            ['nama_subelemen' => 'Menjaga Lingkungan Alam Sekitar', 'capaian_fase' => 'Mewujudkan rasa syukur dengan berinisiatif menyelesaikan masalah lingkungan sekitarnya.'],
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'D2',
                'nama_dimensi' => 'Berkebinekaan Global',
                'elemen' => [
                    [
                        'nama_elemen' => 'Mengenal dan Menghargai Budaya',
                        'subelemen' => [
                            ['nama_subelemen' => 'Mendalami budaya dan identitas budaya', 'capaian_fase' => 'Menganalisis pengaruh keanggotaan kelompok lokal, regional, nasional pada identitas diri.'],
                            ['nama_subelemen' => 'Mengeksplorasi dan membandingkan pengetahuan budaya', 'capaian_fase' => 'Mempromosikan pertukaran budaya dan kolaborasi dalam dunia yang saling terhubung.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Komunikasi dan Interaksi Antar Budaya',
                        'subelemen' => [
                            ['nama_subelemen' => 'Berkomunikasi antar budaya', 'capaian_fase' => 'Menganalisis hubungan antara bahasa, pikiran, dan konteks sosial budaya dalam interaksi.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Berkeadilan Sosial',
                        'subelemen' => [
                            ['nama_subelemen' => 'Turut serta aktif membangun masyarakat yang adil', 'capaian_fase' => 'Berpartisipasi menentukan pilihan dan keputusan untuk kepentingan bersama melalui musyawarah.'],
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'D3',
                'nama_dimensi' => 'Bergotong Royong',
                'elemen' => [
                    [
                        'nama_elemen' => 'Kolaborasi',
                        'subelemen' => [
                            ['nama_subelemen' => 'Kerja sama', 'capaian_fase' => 'Membangun tim dan mengelola kerja sama untuk mencapai tujuan bersama sesuai target yang disepakati.'],
                            ['nama_subelemen' => 'Komunikasi untuk mencapai tujuan bersama', 'capaian_fase' => 'Aktif menyimak dan menyampaikan gagasan secara efektif dan menghargai masukan orang lain.'],
                            ['nama_subelemen' => 'Koordinasi Sosial', 'capaian_fase' => 'Menyelaraskan dan menjaga tindakan diri dan anggota kelompok agar sesuai arahan target bersama.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Kepedulian',
                        'subelemen' => [
                            ['nama_subelemen' => 'Tanggap terhadap lingkungan Sosial', 'capaian_fase' => 'Tanggap terhadap kondisi sosial lingkungan masyarakat dan menghasilkan solusi produktif.'],
                            ['nama_subelemen' => 'Persepsi Sosial', 'capaian_fase' => 'Memahami berbagai alasan orang lain bertindak dalam konteks situasi tertentu.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Berbagi',
                        'subelemen' => [
                            ['nama_subelemen' => 'Berbagi sumber daya bersama', 'capaian_fase' => 'Mengupayakan memberi hal yang dianggap penting dan berharga kepada orang-orang yang membutuhkan.'],
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'D4',
                'nama_dimensi' => 'Mandiri',
                'elemen' => [
                    [
                        'nama_elemen' => 'Pemahaman Diri dan Situasi yang Dihadapi',
                        'subelemen' => [
                            ['nama_subelemen' => 'Mengenali kualitas dan minat diri serta tantangan yang dihadapi', 'capaian_fase' => 'Mengidentifikasi kekuatan dan kelemahan diri serta situasi yang mendukung dan menghambat pengembangan diri.'],
                            ['nama_subelemen' => 'Mengembangkan refleksi diri', 'capaian_fase' => 'Melakukan refleksi terhadap umpan balik dari orang lain untuk perbaikan diri yang berkelanjutan.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Regulasi Diri',
                        'subelemen' => [
                            ['nama_subelemen' => 'Regulasi emosi', 'capaian_fase' => 'Mampu mengendalikan diri dan menyesuaikan emosi saat menghadapi situasi yang menantang.'],
                            ['nama_subelemen' => 'Penetapan tujuan dan rencana strategis', 'capaian_fase' => 'Merancang strategi yang sesuai untuk mencapai tujuan belajar dan pengembangan diri.'],
                            ['nama_subelemen' => 'Percaya diri, tangguh, dan adaptif', 'capaian_fase' => 'Menyesuaikan diri dengan perubahan situasi dan tidak mudah putus asa saat menghadapi kegagalan.'],
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'D5',
                'nama_dimensi' => 'Bernalar Kritis',
                'elemen' => [
                    [
                        'nama_elemen' => 'Memperoleh dan Memproses Informasi dan Gagasan',
                        'subelemen' => [
                            ['nama_subelemen' => 'Mengajukan pertanyaan', 'capaian_fase' => 'Mengajukan pertanyaan untuk klarifikasi dan interpretasi informasi, serta mencari solusi masalah.'],
                            ['nama_subelemen' => 'Mengidentifikasi, mengklarifikasi, dan mengolah informasi dan gagasan', 'capaian_fase' => 'Secara kritis mengklarifikasi serta menganalisis gagasan dan informasi yang kompleks dari berbagai sumber.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Menganalisis dan Mengevaluasi Penalaran dan Prosedurnya',
                        'subelemen' => [
                            ['nama_subelemen' => 'Penalaran dan pembuktian logis', 'capaian_fase' => 'Menjelaskan alasan untuk mendukung pemikirannya dan memikirkan pandangan yang berlawanan.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Refleksi Pemikiran dan Proses Berpikir',
                        'subelemen' => [
                            ['nama_subelemen' => 'Merefleksi dan mengevaluasi pemikirannya sendiri', 'capaian_fase' => 'Menjelaskan asumsi yang digunakan, mengevaluasi proses berpikir, dan membuat kesimpulan.'],
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'D6',
                'nama_dimensi' => 'Kreatif',
                'elemen' => [
                    [
                        'nama_elemen' => 'Menghasilkan Gagasan yang Orisinal',
                        'subelemen' => [
                            ['nama_subelemen' => 'Gagasan Orisinal', 'capaian_fase' => 'Menghubungkan gagasan yang ia miliki dengan informasi baru untuk menghasilkan kombinasi gagasan baru dan imajinatif.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Menghasilkan Karya dan Tindakan yang Orisinal',
                        'subelemen' => [
                            ['nama_subelemen' => 'Karya dan Tindakan Orisinal', 'capaian_fase' => 'Mengeksplorasi dan mengekspresikan pikiran dan perasaannya dalam bentuk karya nyata yang bermakna.'],
                        ]
                    ],
                    [
                        'nama_elemen' => 'Memiliki Keluwesan Berpikir dalam Mencari Alternatif Solusi Permasalahan',
                        'subelemen' => [
                            ['nama_subelemen' => 'Keluwesan Berpikir', 'capaian_fase' => 'Bereksperimen dengan berbagai pilihan secara kreatif ketika rencana awal tidak berjalan lancar.'],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($dimensiData as $dim) {
            $dimensiId = DB::table('p5_dimensi')->insertGetId([
                'kode' => $dim['kode'],
                'nama_dimensi' => $dim['nama_dimensi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($dim['elemen'] as $el) {
                $elemenId = DB::table('p5_elemen')->insertGetId([
                    'dimensi_id' => $dimensiId,
                    'nama_elemen' => $el['nama_elemen'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($el['subelemen'] as $sub) {
                    DB::table('p5_subelemen')->insert([
                        'elemen_id' => $elemenId,
                        'nama_subelemen' => $sub['nama_subelemen'],
                        'capaian_fase' => $sub['capaian_fase'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
