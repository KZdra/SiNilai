# Panduan & Master Prompt Pengembangan Aplikasi CBT dan Integrasi ke SiNilai (e-Rapor)

Dokumen ini berisi panduan arsitektur, skema integrasi, alur kerja (workflow), serta **kumpulan Master Prompt AI** yang siap Anda gunakan untuk membuat aplikasi **CBT (Computer Based Test)** dari nol hingga nilainya otomatis masuk ke SiNilai pada kolom **STS (Sumatif Tengah Semester)** dan **SAS (Sumatif Akhir Semester)**.

---

## 1. Arsitektur & Alur Integrasi (Overview)

```mermaid
graph LR
    subgraph SSO Server [SSO ICB]
        A[Data Akun Guru & Siswa]
    end

    subgraph CBT App [Aplikasi CBT]
        B[Bank Soal & Jadwal Ujian]
        C[Siswa Mengerjakan Ujian]
        D[Nilai Akhir Ujian STS / SAS]
    end

    subgraph SiNilai App [Aplikasi SiNilai / Rapor]
        E[Tabel values: value_sts / value_sas]
        F[Pengolahan Rapor & Cetak]
    end

    A -->|Login Siswa / Guru| CBT App
    A -->|Login Guru / Admin| SiNilai App
    D -->|Otomatisasi API Sync / Tarik Nilai| E
```

### Kunci Pencocokan Data (Data Mapping Key)
Agar nilai dari CBT dapat masuk ke siswa dan mata pelajaran yang tepat di SiNilai, kedua sistem harus memiliki referensi data yang sama:
1. **Identitas Siswa**: `nisn` atau `nis` (primary identifier unik antar sistem).
2. **Identitas Mapel**: `mapel_id` atau kode mapel (`mapel_code`).
3. **Identitas Kelas**: `class_id` atau nama kelas (`class_name`).
4. **Tahun Ajaran & Semester**: `fst_id` (Fase / Semester / Tahun Pelajaran).
5. **Kategori Ujian**: `type_exam` (`STS` atau `SAS`).

---

## 2. Kumpulan Prompt Siap Pakai (Master Prompts)

Gunakan prompt di bawah ini secara bertahap saat membuat aplikasi CBT menggunakan AI / coding assistant:

---

### 🚀 PROMPT 1: Inisialisasi & Pembuatan Aplikasi CBT (Core System)

> **Gunakan prompt ini saat membuat project CBT baru (Laravel / Vue / React / dsb):**

```text
Saya ingin membuat aplikasi Computer Based Test (CBT) berbasis Laravel 11 dan Tailwind CSS / Vue.js untuk sekolah, yang nantinya akan diintegrasikan dengan aplikasi e-Rapor (SiNilai) dan Server SSO OAuth2.

Tolong rancang arsitektur, database migration, model, controller, dan tampilan responsif dengan spesifikasi berikut:

1. Modul & Hak Akses:
   - Admin: Kelola Tahun Ajaran/Semester, Kelas, Mata Pelajaran, Guru, Siswa, dan Pengaturan Sistem.
   - Guru: Kelola Bank Soal (Pilihan Ganda, Pilihan Ganda Kompleks, Benar/Salah, Menjodohkan, Essay Singkat), Buat Jadwal Ujian (Durasi, Acak Soal, Acak Opsi, Batas Akses, Token Ujian, Kategori Ujian: UH / STS / SAS), Analisis Butir Soal, Koreksi Essay, dan Monitoring Ujian Realtime.
   - Siswa: Dashboard Ujian, Masukkan Token, Halaman Pengerjaan Ujian (dengan Timer, Navigasi Nomor, Tandai Ragu-ragu, Auto-save jawaban setiap detik/klik, Anti-cheat/Lockdown deteksi pindah tab), Hasil Nilai (opsional ditampilkan).

2. Skema Database Inti:
   - students (id, nis, nisn, nama, class_id, is_active)
   - classes (id, class_name, tingkat)
   - subjects (id, code, name)
   - academic_years (id, tahun_ajaran, semester, is_active)
   - question_banks (id, subject_id, teacher_id, title, total_questions)
   - questions (id, question_bank_id, type, question_text, question_image, audio, options_json, correct_answer_json, points)
   - exams (id, question_bank_id, subject_id, class_ids_json, title, category ['UH', 'STS', 'SAS'], duration_minutes, start_time, end_time, token, random_questions, random_options, show_score)
   - exam_sessions (id, exam_id, student_id, start_time, end_time, status ['in_progress', 'submitted', 'blocked'], score_mc, score_essay, final_score, ip_address, violation_count)
   - student_answers (id, exam_session_id, question_id, student_answer_json, is_correct, score_obtained)

3. Fitur Keamanan CBT:
   - Auto Submit saat waktu habis.
   - Deteksi kehilangan fokus layar / pindah tab (Full-screen enforcement & window blur warning).
   - Jawaban tersimpan otomatis secara periodik (Local storage cache + async DB AJAX).
   - Pencegahan double submit / race conditions.

Buatkan struktur migration lengkap, relasi model eloquent, dan alur pengerjaan ujian yang robust dan tahan beban concurrency siswa satu sekolah.
```

---

### 🚀 PROMPT 2: Pembuatan Modul Integrasi & REST API di Aplikasi CBT

> **Gunakan prompt ini pada aplikasi CBT agar menyediakan Endpoint API yang siap dikonsumsi SiNilai:**

```text
Buatkan modul Integrasi API pada aplikasi CBT agar nilai hasil ujian (khususnya kategori STS dan SAS) dapat ditarik oleh aplikasi e-Rapor (SiNilai).

Spesifikasi Endpoint API di CBT:

1. Endpoint Ambil Nilai Hasil Ujian:
   - Route: GET /api/v1/scores/export
   - Headers: Authorization: Bearer {API_SECRET_TOKEN}
   - Query Parameters:
     - academic_year_id (int, required)
     - semester (int, required: 1 atau 2)
     - category (string, required: 'STS' atau 'SAS')
     - subject_id (int, required)
     - class_id (int, required)
   
   - Format Response JSON:
   {
     "status": "success",
     "meta": {
       "category": "STS",
       "subject_id": 1,
       "subject_name": "Matematika",
       "class_id": 2,
       "class_name": "X RPL 1",
       "total_students": 36
     },
     "data": [
       {
         "nis": "20241001",
         "nisn": "0081234567",
         "student_name": "Ahmad Fauzi",
         "score": 85.50,
         "submitted_at": "2026-09-01 10:30:00",
         "status": "submitted"
       },
       ...
     ]
   }

2. Keamanan & Konfigurasi:
   - Gunakan API Token Authentication (API Key / Personal Access Token) yang dapat diatur di file .env (`CBT_API_KEY`).
   - Sediakan juga fitur Export Excel/CSV dengan format yang sesuai dengan template import SiNilai sebagai alternatif manual jika server offline.
```

---

### 🚀 PROMPT 3: Menambahkan Fitur "Tarik Nilai CBT" di Aplikasi SiNilai

> **Gunakan prompt ini di repository SiNilai untuk menambahkan tombol dan logic sinkronisasi:**

```text
Saya ingin menambahkan fitur "Tarik Nilai dari CBT" pada aplikasi SiNilai (Laravel) di halaman penginputan nilai (`NilaiController` dan `resources/views/nilai/index.blade.php`).

Struktur Nilai di SiNilai saat ini:
- Tabel `values`:
  - `student_id` (foreign key ke `students.id`)
  - `mapel_id` (foreign key ke `mata_pelajarans.id`)
  - `fst_id` (foreign key ke `m_fst_pembelajaran.id`)
  - `value_daily`, `value_daily_2`, ... `value_daily_10`
  - `value_sts` (Nilai Sumatif Tengah Semester)
  - `value_sas` (Nilai Sumatif Akhir Semester)
- Tabel `students` memiliki kolom: `id`, `nis`, `nisn`, `nama`, `class_id`.

Kebutuhan Fitur Baru di SiNilai:
1. Konfigurasi Environment (`.env`):
   - `CBT_API_BASE_URL=http://cbt.sekolah.sch.id/api/v1`
   - `CBT_API_SECRET=rahasia_token_cbt`

2. Controller Action:
   - Buat method `syncFromCbt(Request $request)` di `NilaiController`:
     - Menerima parameter: `class_id`, `mapel_id`, `fst_id`, dan `target_field` ('value_sts' atau 'value_sas').
     - Melakukan HTTP Request (via `Illuminate\Support\Facades\Http`) ke API CBT untuk mengambil daftar nilai ujian berdasarkan kelas & mapel tersebut.
     - Melakukan matching data siswa berdasarkan `nisn` (atau `nis`).
     - Meng-update atau membuat record di tabel `values` untuk kolom `value_sts` atau `value_sas` sesuai `target_field`.
     - Mengembalikan response JSON jumlah data yang berhasil disinkronisasi & siswa yang nilainya tidak ditemukan.

3. Frontend UI (`resources/views/nilai/index.blade.php`):
   - Tambahkan tombol dropdown/modal "Tarik Nilai CBT" di dekat tombol filter/simpan.
   - Pilihan:
     - Tarik Nilai ke STS
     - Tarik Nilai ke SAS
   - Menggunakan AJAX / SweetAlert2 dengan konfirmasi dan loading bar/spinner, serta menampilkan log ringkasan siswa yang berhasil di-update nilainya.

Tolong buatkan implementasi kode lengkapnya.
```

---

## 3. Contoh Implementasi Teknis di SiNilai

Berikut adalah implementasi langsung potongan kode untuk SiNilai:

### A. Tambahan di `.env` dan `config/services.php`

Di `.env`:
```env
CBT_API_URL=http://localhost:8001/api/v1
CBT_API_KEY=your_secure_cbt_api_key_here
```

Di `config/services.php`:
```php
'cbt' => [
    'url' => env('CBT_API_URL', 'http://localhost:8001/api/v1'),
    'key' => env('CBT_API_KEY'),
],
```

### B. Method Sync di `App\Http\Controllers\NilaiController.php`

```php
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

public function syncFromCbt(Request $request)
{
    $request->validate([
        'class_id'     => 'required|integer',
        'mapel_id'     => 'required|integer',
        'fst_id'       => 'required|integer',
        'target_field' => 'required|in:value_sts,value_sas', // target pengisian nilai
    ]);

    $cbtUrl = config('services.cbt.url') . '/scores/export';
    $cbtKey = config('services.cbt.key');

    // Tentukan kategori ujian CBT berdasarkan target field
    $category = ($request->target_field === 'value_sts') ? 'STS' : 'SAS';

    try {
        // 1. Panggil API CBT
        $response = Http::withToken($cbtKey)->timeout(15)->get($cbtUrl, [
            'class_id'   => $request->class_id,
            'subject_id' => $request->mapel_id,
            'category'   => $category,
            'fst_id'     => $request->fst_id,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke server CBT: ' . $response->body()
            ], 400);
        }

        $cbtData = $response->json('data') ?? [];
        if (empty($cbtData)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data nilai ujian yang ditemukan di CBT untuk filter ini.'
            ], 404);
        }

        // 2. Ambil daftar siswa kelas terkait di SiNilai
        $students = DB::table('students')
            ->where('class_id', $request->class_id)
            ->select('id', 'nis', 'nisn', 'nama')
            ->get();

        $updatedCount = 0;
        $notFound = [];

        DB::beginTransaction();

        foreach ($cbtData as $cbtItem) {
            // Cocokkan berdasarkan NISN atau NIS
            $student = $students->first(function ($s) use ($cbtItem) {
                return (!empty($cbtItem['nisn']) && $s->nisn === $cbtItem['nisn'])
                    || (!empty($cbtItem['nis']) && $s->nis === $cbtItem['nis']);
            });

            if ($student) {
                $score = $cbtItem['score'] ?? null;

                // Update or create record di tabel values
                DB::table('values')->updateOrInsert(
                    [
                        'student_id' => $student->id,
                        'mapel_id'   => $request->mapel_id,
                        'fst_id'     => $request->fst_id,
                    ],
                    [
                        $request->target_field => $score,
                        'updated_at'           => now(),
                    ]
                );
                $updatedCount++;
            } else {
                $notFound[] = $cbtItem['student_name'] ?? ($cbtItem['nis'] ?? 'Tanpa Nama');
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Berhasil menarik {$updatedCount} nilai {$category} dari CBT!",
            'updated_count' => $updatedCount,
            'not_found'     => $notFound,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
        ], 500);
    }
}
```

---

## 4. Opsi Alternatif: Semi-Otomatis (Export-Import Excel)

Jika server CBT dan SiNilai berada di jaringan offline/lokal terpisah (tanpa koneksi internet/API langsung), siapkan alur:
1. **Di CBT**: Klik tombol **Export Nilai STS/SAS (Format SiNilai)** -> Menghasilkan file Excel/CSV dengan kolom: `[NISN, NIS, Nama Siswa, Nilai_STS, Nilai_SAS]`.
2. **Di SiNilai**: Di halaman input nilai, tambahkan tombol **Import Excel Nilai** -> Otomatis mengisi kolom STS / SAS berdasarkan NISN siswa.

---

## 5. Ringkasan Rekomendasi Fitur CBT

| Fitur CBT | Manfaat untuk Rapor SiNilai |
| :--- | :--- |
| **Kategori Ujian (STS / SAS)** | Nilai langsung diarahkan ke kolom yang tepat di tabel `values` |
| **Single Sign-On (SSO ICB)** | Akun siswa dan guru otomatis sinkron antara CBT & SiNilai |
| **Pencocokan NISN/NIS** | Mencegah salah input nama siswa yang mirip/identik |
| **Auto-rekap & Koreksi Otomatis** | Guru tidak perlu menghitung manual; selesai ujian nilai langsung siap ditarik |

---
*File panduan ini dibuat khusus untuk integrasi ekosistem aplikasi SiNilai & SSO ICB.*
