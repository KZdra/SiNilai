# CODEBASE.md — Dokumentasi Arsitektur & Rekayasa Sistem SiNilai

Dokumen ini memuat spesifikasi teknis, arsitektur, skema relasional, alur logika bisnis, integrasi eksternal, dan panduan modifikasi untuk sistem informasi **SiNilai**. Seluruh informasi disusun berdasarkan verifikasi langsung terhadap kode sumber aktual.

---

## 1. Project Overview

**SiNilai** adalah sistem informasi penilaian akademik, e-rapor Kurikulum Merdeka, dan sinkronisasi hasil ujian daring Computer Based Test (CBT).

- **Backend Runtime**: PHP `^8.2`, Laravel `^11.31`
- **Database Engine**: MySQL 8.0+ / MariaDB (Production), SQLite (Pengembangan Lokal)
- **Database Pattern**: **Non-ORM (Direct Query Builder `DB::table`)** pada seluruh operasi data transaksional akademik
- **Frontend & UI**: AdminLTE 3 / Bootstrap `~4.6.1`, Blade Templates, Vite `^6.0`, PostCSS, TailwindCSS `^3.4`
- **Client Scripting**: jQuery `3.3.1`, DataTables BS4 `2.2.2`, SweetAlert2 `11.16`, Chart.js `4.4`
- **Dokumentasi & Laporan**: DomPDF `^3.1`, Chillerlan QR Code `^6.0`, Maatwebsite Excel `^3.1`
- **Integrasi Eksternal**: Server SSO OAuth2 (Authorization Code Flow & SLO), Server CBT (Bidirectional REST API)

---

## 2. Architecture & Design Patterns

### 2.1 Arsitektur Non-ORM (Direct Database Query Builder)
Aplikasi meminimalkan penggunaan model Eloquent untuk kalkulasi akademik. Hanya terdapat dua model Eloquent:
- [`app/Models/User.php`](file:///c:/laragon/www/SiNilai/app/Models/User.php) (Autentikasi & Identitas)
- [`app/Models/Setting.php`](file:///c:/laragon/www/SiNilai/app/Models/Setting.php) (Konfigurasi Key-Value Dinamis & Modul Toggle)

Seluruh entitas akademik (`values`, `students`, `class`, `m_fst_pembelajaran`, `mapel_class_fst`, `catatan_walikelas`, `tpsiswas`, `p5_*`, `student_class_history`) diproses langsung melalui `DB::table(...)` dan `DB::select(...)`. Hal ini menghilangkan overhead pemakaian memori saat mengagregasi ratusan nilai siswa dalam satu rombel.

### 2.2 Dynamic SQL Pivot Aggregation Engine
Pada [`NilaiAkhirController.php`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/NilaiAkhirController.php#L117-L175), sistem secara dinamis menyusun klausa SQL pivot `CASE WHEN v.mapel_id = X THEN ...` berdasarkan daftar mata pelajaran yang aktif (`is_active = 1`) pada tabel `mapel_class_fst`. Query ini menghitung rata-rata harian (1–10), STS, dan SAS secara horizontal dalam 1 query tunggal ke database tanpa perulangan $N+1$ query di PHP.

### 2.3 Dual-Engine Batch PDF & ZIP Packaging
Untuk mencegah habisnya batas memori PHP (`memory_limit`) saat merender puluhan buku rapor PDF secara bersamaan, sistem menyediakan dua engine:
1. **Client-Side Progressive Chunk Engine** ([`NilaiAkhirController::generateZipChunk`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/NilaiAkhirController.php#L823)): Merender 3–4 PDF siswa per request AJAX dari browser, lalu menyatukan hasilnya menjadi file `.zip` via [`finalizeZipChunk`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/NilaiAkhirController.php#L882).
2. **Background Queue Engine** ([`GenerateClassRaportZipJob.php`](file:///c:/laragon/www/SiNilai/app/Jobs/GenerateClassRaportZipJob.php)): Menerima dispatch antrean dari [`dispatchZipQueue`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/NilaiAkhirController.php#L943), merender PDF di background worker, menyimpan progress persentase ke Cache Laravel, dan mengemas ZIP.

### 2.4 Centralized Caching Layer
[`MasterDataCache.php`](file:///c:/laragon/www/SiNilai/app/Services/MasterDataCache.php) meng-cache data profil sekolah, daftar FST (semester), dan master kelas selama 24 jam (`TTL_LONG = 86400`). Untuk statistik dashboard, sistem menerapkan teknik versioning cache (`dashboard_cache_ver`) sehingga cache dapat diinvalidasi secara instan tanpa perlu menjalankan wildcard flush.

### 2.5 Immutable Mutation Audit Logging
[`NilaiAuditService.php`](file:///c:/laragon/www/SiNilai/app/Services/NilaiAuditService.php) merekam setiap mutasi nilai (`INPUT_BARU`, `UPDATE`, `DELETE`, `IMPORT_CSV`, `SYNC_CBT`) ke dalam tabel `nilai_audit_logs`, menyimpan snapshot data lama (`old_values`) dan data baru (`new_values`) dalam format JSON bersama ID pengguna dan IP address.

---

## 3. Directory Structure

```text
SiNilai/
├── app/
│   ├── Exports/                         # Class export spreadsheet (Maatwebsite)
│   │   ├── ClassSheetExport.php         # Sheet nilai sumatif per kelas
│   │   ├── LegerNilaiExport.php         # Export buku leger nilai lengkap
│   │   ├── MultiClassStudentTemplateExport.php # Template data siswa multi-kelas
│   │   ├── MultiClassTemplateNilaiExport.php   # Workbook template nilai guru mapel
│   │   ├── NilaiAkhirExport.php         # Export ringkasan nilai akhir
│   │   ├── RankingExport.php            # Export perankingan siswa
│   │   ├── StudentClassSheetExport.php  # Sheet siswa individual
│   │   ├── TpFormatifSheetExport.php    # Sheet penilaian formatif per TP
│   │   └── TpTemplateExport.php         # Sheet daftar referensi TP
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── CbtSyncController.php    # REST API endpoints untuk CBT Sync
│   │   │   ├── Auth/                    # Laravel default authentication controllers
│   │   │   ├── BackupController.php         # Manajemen backup & dump database .sql
│   │   │   ├── CatatanWalasController.php   # Presensi (S/I/A) & catatan walas per semester
│   │   │   ├── ClassController.php          # CRUD master rombongan belajar
│   │   │   ├── DataSekolahContoller.php     # Profil instansi & data kepala sekolah
│   │   │   ├── EskulController.php          # Master ekstrakurikuler & predikat
│   │   │   ├── FstController.php            # Master Fase, Semester, TA & Semester Lock
│   │   │   ├── HomeController.php           # Dashboard statistik utama
│   │   │   ├── KenaikanKelasController.php  # Algoritma promosi berjenjang & tutup TA
│   │   │   ├── MapelController.php          # Master mata pelajaran
│   │   │   ├── MapelMappingController.php   # Pemetaan mapel aktif per kelas & semester
│   │   │   ├── NilaiAkhirController.php     # Engine nilai akhir, ranking, cetak rapor, ZIP
│   │   │   ├── NilaiAuditController.php     # Viewer log mutasi nilai
│   │   │   ├── NilaiController.php          # Entry nilai sumatif, import CSV, & pull CBT
│   │   │   ├── NilaiImportController.php    # Upload Excel multi-sheet guru mapel
│   │   │   ├── P5Controller.php             # Modul Projek Profil Pelajar Pancasila
│   │   │   ├── PanduanController.php        # Halaman dokumentasi alur kerja guru
│   │   │   ├── PeskulController.php         # Penilaian capaian ekstrakurikuler
│   │   │   ├── PortalSiswaController.php    # Portal mandiri siswa & wali murid
│   │   │   ├── ProfileController.php        # Pengaturan profil pengguna login
│   │   │   ├── PublicVerificationController.php # Verifikasi publik rapor via QR Code
│   │   │   ├── RaportExplorerController.php # Penjelajah arsip rapor PDF (jsTree)
│   │   │   ├── RaportStatusController.php   # Alur status & pengesahan kuncian rapor
│   │   │   ├── SettingController.php        # Konfigurasi SSO & toggle modul sistem
│   │   │   ├── SiswaController.php          # Master data siswa & cetak cover rapor
│   │   │   ├── SsoController.php            # OAuth2 Client (Redirect, Callback, SLO)
│   │   │   ├── TpController.php             # Master TP & Asesmen Formatif (KKTP)
│   │   │   └── UserController.php           # Manajemen user, role, & generate akun siswa
│   │   └── Middleware/
│   │       ├── CbtSyncAuth.php              # Guard API CBT via Bearer Token & hash_equals
│   │       ├── CheckAssignedClass.php       # Enforcer penugasan rombel wali kelas
│   │       └── RoleCheck.php                # Role-based Access Control guard
│   ├── Jobs/
│   │   └── GenerateClassRaportZipJob.php    # Queue worker pembuatan arsip ZIP rapor
│   ├── Models/
│   │   ├── Setting.php                      # Model pengaturan modul & konfigurasi
│   │   └── User.php                         # Authenticatable model identitas pengguna
│   └── Services/
│       ├── DataTableHelper.php              # Pipeline server-side pagination & filter DataTables
│       ├── MasterDataCache.php              # Caching service data induk & versi dashboard
│       └── NilaiAuditService.php            # Logger mutasi nilai transaksional
├── bootstrap/
│   └── app.php                              # Registrasi middleware aliases & CSRF exclusion
├── config/                                  # Konfigurasi aplikasi (services, database, auth)
├── database/
│   ├── migrations/                          # 35 migrasi skema database relasional
│   └── seeders/                             # Database seeders (Role, User, P5, Dummy Data)
├── public/                                  # Public entrypoint, asset statis, templates
├── resources/
│   ├── css/ & js/                           # Script client (apiservice, swalHelper, chart)
│   └── views/                               # Template Blade per modul sistem
├── routes/
│   ├── modules/                             # Sub-routing modular terpisah
│   │   ├── admin.php                        # Route Administrator (role_id = 1)
│   │   ├── akademik.php                     # Route Penilaian & Asesmen (checkClass)
│   │   ├── master.php                       # Route Data Induk (auth)
│   │   └── portal.php                       # Route Portal Mandiri Siswa
│   ├── api.php                              # Inbound REST API CBT
│   └── web.php                              # Root web routes & module loader
├── deploy.sh                                # Script otomatisasi deployment Nginx/PHP-FPM
├── PANDUAN_DAN_PROMPT_CBT_SINILAI.md        # Spesifikasi teknis integrasi aplikasi CBT
└── SSO_CLIENT_INTEGRATION.md                # Spesifikasi integrasi protokol OAuth2 SSO
```

---

## 4. Authentication & Authorization Flows

### 4.1 Skema Autentikasi
1. **Internal Session Authentication**: Login standar Laravel via form web (`username` / `password`) menggunakan session database.
2. **OAuth2 SSO Client Flow** ([`SsoController.php`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/SsoController.php)):
   - Pengguna diarahkan ke `/auth/redirect` $\rightarrow$ generate string acak `state` (disimpan pada session) $\rightarrow$ redirect ke URL SSO Server `/oauth/authorize`.
   - SSO Server mengembalikan authorization code ke callback `/auth/callback`.
   - Kode ditukar dengan Access Token via HTTP `POST /oauth/token`.
   - Profil pengguna ditarik melalui HTTP `GET /api/me` (atau fallback `/api/user`).
   - Akun disinkronkan ke tabel `users` lokal, session diregenerasi, dan pengguna di-login-kan via `Auth::login($user, true)`.
3. **Single Logout (SLO)**: Endpoint `POST /sso/slo` menerima notifikasi dari SSO Server untuk menghancurkan session pengguna di tabel `sessions`.
4. **CBT Bearer Token Guard** ([`CbtSyncAuth.php`](file:///c:/laragon/www/SiNilai/app/Http/Middleware/CbtSyncAuth.php)): Endpoint `/api/cbt/*` memeriksa header `Authorization: Bearer <token>` dan mencocokkannya dengan `CBT_SYNC_TOKEN` menggunakan `hash_equals()`.

### 4.2 Struktur Role Pengguna (RBAC)
- **Role 1 (Administrator)**: Mengakses seluruh menu sistem, manajemen user, backup database, tutup tahun ajaran, pengaturan modul, dan membuka kuncian rapor.
- **Role 2 (Guru / Tenaga Pendidik)**:
  - **Guru Mapel (Non-Walas)**: Memiliki `class_id = null`, diizinkan mengakses menu upload nilai Excel ([`NilaiImportController.php`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/NilaiImportController.php)).
  - **Wali Kelas**: Memiliki `class_id` tertentu, dapat mengelola nilai kelas binaannya, catatan presensi/kenaikan, memverifikasi nilai rapor, dan mencetak rapor kelas.
- **Role 3 (Siswa)**: Memiliki `student_id` yang terikat ke tabel `students`, diarahkan ke dashboard mandiri portal siswa ([`PortalSiswaController.php`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/PortalSiswaController.php)).

---

## 5. Database Entities & Relationships

### 5.1 Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    users ||--o{ roles : "belongs to (role_id)"
    users ||--o{ class : "assigned as walas (class_id)"
    users ||--o{ students : "linked student account (student_id)"

    class ||--o{ students : "current enrollment (class_id)"
    class ||--o{ mapel_class_fst : "maps subjects"
    class ||--o{ student_class_history : "historical classes"
    class ||--o{ raport_statuses : "status per semester"

    m_fst_pembelajaran ||--o{ mapel_class_fst : "scopes semester"
    m_fst_pembelajaran ||--o{ values : "scopes grades"
    m_fst_pembelajaran ||--o{ tpsiswas : "scopes formatif"
    m_fst_pembelajaran ||--o{ catatan_walikelas : "scopes attendance"
    m_fst_pembelajaran ||--o{ student_class_history : "scopes history"
    m_fst_pembelajaran ||--o{ raport_statuses : "scopes lock"

    mata_pelajarans ||--o{ mapel_class_fst : "mapped to"
    mata_pelajarans ||--o{ values : "graded in"
    mata_pelajarans ||--o{ m_tp : "has objectives"

    students ||--o{ values : "receives"
    students ||--o{ tpsiswas : "evaluated on"
    students ||--o{ nilai_eskuls : "achieves"
    students ||--o{ catatan_walikelas : "presensi & QR token"
    students ||--o{ student_class_history : "promotions"
    students ||--o{ p5_penilaian : "P5 scores"

    p5_dimensi ||--o{ p5_elemen : "contains"
    p5_elemen ||--o{ p5_subelemen : "contains"
    p5_projek ||--o{ p5_projek_subelemen : "targets"
    p5_subelemen ||--o{ p5_projek_subelemen : "targeted"
    p5_projek ||--o{ p5_penilaian : "records"
    p5_subelemen ||--o{ p5_penilaian : "scored"
```

### 5.2 Rangkuman Entitas Kunci
- **`users`**: Akun autentikasi login (`name`, `username`, `email`, `password`, `role_id`, `class_id`, `student_id`).
- **`students`**: Master data siswa (`nis`, `nisn`, `nama`, `jenis_kelamin`, `class_id`, `status` [aktif/lulus/mutasi], `tahun_lulus`, `foto_siswa_path`).
- **`class`**: Master rombongan belajar (`class_name`).
- **`m_fst_pembelajaran`**: Periode akademik (`fase` [E/F], `semester` [I/II], `tahun_ajaran`, `ta` [tengah/akhir], `is_locked`, `locked_at`, `locked_by`).
- **`mapel_class_fst`**: Pivot konfigurasi mata pelajaran aktif per kelas dan semester (`class_id`, `mapel_id`, `fst_id`, `is_active`).
- **`values`**: Rekam nilai sumatif siswa (`student_id`, `class_id`, `mapel_id`, `fst_id`, `value_daily` s.d. `value_daily_10`, `value_sts`, `value_sas`).
- **`m_tp`**: Master Tujuan Pembelajaran per mapel & FST (`mapel_id`, `fst_id`, `tp_deskripsi`).
- **`tpsiswas`**: Asesmen formatif siswa per TP (`siswa_id`, `tp_id`, `mapel_id`, `fst_id`, `class_id`, `kktp` [1=Tercapai, 0=Belum], `tampilkan` [1/0]).
- **`catatan_walikelas`**: Presensi dan catatan wali kelas (`student_id`, `class_id`, `fst_id`, `sakit`, `izin`, `alpa`, `catatan`, `status_kenaikan`, `verification_token` [32-char unique]).
- **`raport_statuses`**: Siklus persetujuan kuncian rapor (`class_id`, `fst_id`, `status` [draft/submitted/verified/approved_locked], `submitted_by`, `verified_by`, `approved_by`).
- **`student_class_history`**: Snapshot mutasi kelas saat kenaikan kelas (`student_id`, `class_id`, `fst_id`, `status` [aktif/naik_kelas/tinggal_kelas/lulus/mutasi]).
- **`p5_projek`, `p5_penilaian`, `p5_subelemen`**: Pengelolaan projek P5, target subelemen dimensi, dan rubrik predikat 4 skala (`MB`, `SB`, `BSH`, `SAB`).
- **`nilai_audit_logs`**: Audit trail perubahan nilai (`user_id`, `student_id`, `mapel_id`, `fst_id`, `action`, `old_values`, `new_values`, `ip_address`).

---

## 6. Important Routes & Access Control Matrix

| Method | URI | Controller & Action | Middleware Guard | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | `HomeController@index` | `auth` | Dashboard statistik (siswa di-redirect ke portal) |
| `GET` | `/auth/redirect` | `SsoController@redirect` | *Public* | Inisiasi login SSO OAuth2 |
| `GET` | `/auth/callback` | `SsoController@callback` | *Public* | Callback pertukaran token SSO |
| `POST` | `/sso/slo` | `SsoController@slo` | *Public (CSRF Exempt)* | Webhook Single Logout dari server SSO |
| `GET` | `/verifikasi-raport/{token}`| `PublicVerificationController@verify` | *Public* | Halaman publik verifikasi rapor via scan QR |
| **Admin** | | | `auth`, `roleCheck:1` | |
| `POST` | `/kenaikan-kelas/tutup-tahun`| `KenaikanKelasController@tutupTahunAjaran`| `roleCheck:1` | Eksekusi tutup tahun ajaran berjenjang |
| `POST` | `/backup` | `BackupController@store` | `roleCheck:1` | Generate backup file `.sql` ke private storage |
| `GET` | `/raport-explorer/tree` | `RaportExplorerController@getTreeData` | `roleCheck:1` | Data struktur pohon direktori rapor untuk jsTree |
| **Akademik** | | | `auth`, `checkClass` | |
| `POST` | `/nilai` | `NilaiController@store` | `checkClass` | Simpan entri nilai sumatif harian/STS/SAS |
| `POST` | `/nilai/cbt-sync` | `NilaiController@syncFromCbt` | `checkClass` | Pull nilai hasil ujian langsung dari server CBT |
| `POST` | `/upload-nilai-excel/process` | `NilaiImportController@importExcel` | *Guru Mapel Allowed* | Upload file Excel nilai multi-sheet |
| `POST` | `/akhir/zip-chunk` | `NilaiAkhirController@generateZipChunk` | `checkClass` | Render batch 3–4 PDF siswa (Client Chunk) |
| `POST` | `/akhir/zip-queue` | `NilaiAkhirController@dispatchZipQueue` | `checkClass` | Dispatch background job pembuatan ZIP rapor |
| `POST` | `/raport-status/approve-lock`| `RaportStatusController@approveAndLock` | `checkClass` | Pengesahan & kuncian akhir kepala sekolah |
| **Master Data (Admin)** | | | `auth`, `roleCheck:1` | |
| `POST` | `/kelas` | `ClassController@store` | `roleCheck:1` | Tambah rombel kelas |
| `POST` | `/mapel-mapping/toggle` | `MapelMappingController@toggleMapel` | `roleCheck:1` | Toggle mapel aktif/non-aktif per kelas & FST |
| `POST` | `/mfst/{id}/toggle-lock` | `FstController@toggleLock` | `roleCheck:1` | Kunci / buka kunci semester kurikulum |
| **Data Siswa (Admin & Walas)** | | | `auth`, `roleCheck:1,2`, `checkClass` | |
| `GET` | `/siswa` | `SiswaController@index` | `roleCheck:1,2`, `checkClass` | Kelola data siswa sekolah (Admin) / kelas (Walas) |
| **Portal** | | | `auth` | |
| `GET` | `/portal` | `PortalSiswaController@dashboard` | `auth` | Dashboard portal nilai mandiri siswa/ortu |
| `GET` | `/portal/nilai/raport-download`| `PortalSiswaController@downloadRaport` | `auth` | Unduh file rapor PDF resmi |
| **API CBT** | | | `throttle:cbt-sync`, `cbt.auth` | |
| `GET` | `/api/cbt/classes` | `Api\CbtSyncController@classes` | `cbt.auth` | Mengambil data kelas untuk CBT (delta sync) |
| `GET` | `/api/cbt/students` | `Api\CbtSyncController@students` | `cbt.auth` | Mengambil data siswa aktif untuk CBT |
| `POST` | `/api/cbt/scores` | `Api\CbtSyncController@storeScores` | `cbt.auth` | Push hasil ujian CBT langsung ke tabel `values` |

---

## 7. Backend & Frontend Communication

### 7.1 Standar AJAX & CSRF
Seluruh interaksi client-side menggunakan library jQuery AJAX yang dibungkus oleh [`resources/js/apiservice.js`](file:///c:/laragon/www/SiNilai/resources/js/apiservice.js). Header token `X-CSRF-TOKEN` diambil otomatis dari tag meta:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 7.2 Standardisasi DataTables Server-Side
Untuk tabel dengan ribuan data (siswa, audit log, user, master data), controller memanggil utility [`DataTableHelper::process()`](file:///c:/laragon/www/SiNilai/app/Services/DataTableHelper.php#L22):
- Request menerima parameter: `draw`, `start`, `length`, `search['value']`, `order[0]['column']`, `order[0]['dir']`.
- Response mengembalikan JSON terstruktur:
```json
{
  "draw": 1,
  "recordsTotal": 500,
  "recordsFiltered": 12,
  "data": [...]
}
```

### 7.3 Polling Progress Batch Rapor
Pada proses pembuatan ZIP rapor kelas via AJAX:
- **Client Chunk**: Browser mengirim request bertahap dengan parameter `offset` dan `limit` ke `/akhir/zip-chunk`. Server merespons persentase `percent` dan boolean `is_complete`. Saat tuntas, browser memanggil `/akhir/zip-finalize`.
- **Queue Worker**: Browser memanggil `/akhir/zip-queue` untuk men-dispatch job, lalu melakukan polling interval (2 detik) ke `/akhir/zip-queue-status` yang membaca data status dan persentase progress langsung dari Cache Laravel.

---

## 8. Important Business Rules

### 8.1 Formula Agregasi Nilai Kurikulum Merdeka
Rata-rata mata pelajaran dihitung dinamis dengan mengabaikan nilai `NULL` (tidak dianggap nilai nol):
$$\text{Rata Harian} = \frac{\sum_{i=1}^{10} \text{value\_daily}_i}{N_{\text{daily filled}}}$$
$$\text{Rata Tes} = \frac{\text{value\_sts} + \text{value\_sas}}{N_{\text{test filled}}}$$
$$\text{Nilai Akhir Mapel} = \text{ROUND}\left( \frac{\text{Rata Harian} + \text{Rata Tes}}{N_{\text{komponen aktif}}}, 2 \right)$$

### 8.2 Dynamic Subject Mapping Rule
Jika mata pelajaran berstatus `is_active = 0` pada tabel `mapel_class_fst` untuk kelas dan semester terkait, mata pelajaran tersebut:
- Tidak ditampilkan pada form penginputan guru kelas tersebut.
- Dikeluarkan dari klausa `CASE WHEN` kalkulasi nilai akhir.
- Tidak membebani pembagi rata-rata nilai siswa pada leger dan ranking.

### 8.3 Algoritma Resolusi Kelas Historis (`resolveHistoricalClassId`)
Mencegah hilangnya konteks kelas pada buku rapor semester lampau setelah siswa naik kelas atau lulus:
1. Periksa tabel `student_class_history` berdasarkan `student_id` dan `fst_id`.
2. Jika tidak ada, periksa `values.class_id` pada semester terkait.
3. Jika tidak ada, periksa `catatan_walikelas.class_id` pada semester terkait.
4. Jika tidak ada, periksa `tpsiswas.class_id` pada semester terkait.
5. Fallback ke `students.class_id` aktif atau kelas terakhir yang pernah tercatat.

### 8.4 Alur Promosi Tutup Tahun Ajaran (Top-Down Safety)
Dieksekusi dari tingkat tertinggi ke terendah:
1. **Tingkat XII (12)**: `class_id` diubah menjadi `NULL`, `status` menjadi `'lulus'`, dan `tahun_lulus` dicatat.
2. **Tingkat XI (11)**: Dipromosikan ke rombel Tingkat XII pasangannya.
3. **Tingkat X (10)**: Dipromosikan ke rombel Tingkat XI pasangannya.
4. **Siswa Tinggal Kelas**: Siswa yang memiliki catatan `status_kenaikan` *Tinggal Kelas* / *Tidak Naik* pada `catatan_walikelas` dilewati dari pemindahan rombel jika opsi `skip_tinggal_kelas = true`.
5. **Otomasi FST**: Seluruh FST lama dikunci (`is_locked = true`), dan FST Tahun Ajaran Baru Semester Ganjil (Fase E dan F) otomatis dibuat.

---

## 9. External Integrations

### 9.1 Aplikasi Computer Based Test (CBT)
- **Mode Push (CBT $\rightarrow$ SiNilai)**: Aplikasi CBT mengirim nilai via `POST /api/cbt/scores` dengan header Bearer Token. Server mencocokkan siswa via NISN/NIS dan menyimpan nilai ke kolom yang ditentukan (`target_field`: `value_sts`, `value_sas`, atau `value_daily`).
- **Mode Pull (SiNilai $\rightarrow$ CBT)**: Tombol "Tarik Nilai CBT" di SiNilai memicu request HTTP GET ke endpoint CBT `/scores/export` via [`NilaiController::syncFromCbt`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/NilaiController.php#L948).

### 9.2 Server SSO OAuth2
- Protokol: OAuth2 Authorization Code Flow.
- Endpoint terdaftar di `.env`: `SSO_SERVER_URL`, `SSO_CLIENT_ID`, `SSO_CLIENT_SECRET`, `SSO_REDIRECT_URI`.
- Menerima event Single Logout (SLO) via `POST /sso/slo`.

### 9.3 Engine PDF & QR Code
- **DomPDF (`barryvdh/laravel-dompdf`)**: Merender view Blade [`docs/nilai.blade.php`](file:///c:/laragon/www/SiNilai/resources/views/docs/nilai.blade.php) dan [`docs/cover_identitas.blade.php`](file:///c:/laragon/www/SiNilai/resources/views/docs/cover_identitas.blade.php) menjadi dokumen PDF resmi.
- **Chillerlan QR Code (`chillerlan/php-qrcode`)**: Men-generate QR Code Data-URI 32-karakter unik untuk stempel legalitas lembar rapor yang mengarah ke endpoint publik `/verifikasi-raport/{token}`.

---

## 10. Deployment Requirements

### 10.1 Kebutuhan Lingkungan Server
- **Sistem Operasi**: Linux Debian 11/12/13 atau Ubuntu 20.04/22.04/24.04 LTS (disiapkan via [`deploy.sh`](file:///c:/laragon/www/SiNilai/deploy.sh))
- **PHP**: `^8.2` atau `8.3` dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `xml`, `gd`, `zip`, `curl`, `bcmath`
- **Konfigurasi `php.ini`**:
  - `memory_limit = 512M` (wajib untuk DomPDF batch rendering)
  - `max_execution_time = 300`
  - `upload_max_filesize = 64M` & `post_max_size = 64M`
- **Web Server**: Nginx dengan reverse proxy PHP-FPM pada port `8002` (terisolasi agar berdampingan dengan CBT di port 80/8001).
- **Daemon Antrean**: `php artisan queue:listen --tries=1` atau supervisor daemon jika menggunakan mode antrean ZIP.

### 10.2 Matriks Variabel Lingkungan (`.env`)
```env
APP_NAME=SiNilai
APP_ENV=production
APP_DEBUG=false
APP_URL=http://ip-server:8002

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sinilai_db
DB_USERNAME=cbt_user
DB_PASSWORD=secret_db_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# Integrasi CBT
CBT_SYNC_TOKEN=secret_token_cbt_2026
CBT_API_URL=http://ip-server:8001/api/v1
CBT_API_KEY=secret_token_cbt_2026

# Integrasi SSO OAuth2
SSO_SERVER_URL=http://ip-server:8000
SSO_CLIENT_ID=client_id_dari_sso
SSO_CLIENT_SECRET=client_secret_dari_sso
SSO_REDIRECT_URI=http://ip-server:8002/auth/callback
```

---

## 11. Known Technical Debt

1. **Vendor SQL Lock-in (MySQL Specific `IF(...)`)**:
   Query pembentukan pivot nilai di [`NilaiAkhirController.php#L128`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/NilaiAkhirController.php#L128) menggunakan fungsi bawaan MySQL `IF(expr, 1, 0)`. Sintaks ini tidak kompatibel dengan SQLite standar (SQLite membutuhkan `CASE WHEN` atau `IIF()`), sehingga testing lokal dengan SQLite in-memory akan gagal saat menjalankan kalkulasi nilai akhir.
2. **Redundansi Kolom Presensi Siswa**:
   Kolom `sakit`, `izin`, dan `alpa` tersimpan di dua tempat: tabel `students` (nilai statis tunggal) dan tabel `catatan_walikelas` (per semester/FST). Method [`CatatanWalasController::store`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/CatatanWalasController.php#L140) memperbarui keduanya secara bersamaan, sehingga tabel `students` hanya mencerminkan presensi semester terakhir yang disentuh.
3. **Keterbatasan Pola Regex Promosi Rombel**:
   Method [`KenaikanKelasController::buildAutoPromotionMap`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/KenaikanKelasController.php#L56-L84) hanya menggunakan pola regex angka Romawi SMA (`/^X\b/`, `/^XI\b/`, `/^(XII|12)\b/`). Nama kelas dengan angka numerik (`10 RPL`, `11 IPA`) atau jenjang SMP (7, 8, 9) dan SD (1–6) tidak cocok secara otomatis dan menghasilkan status *Unmatched*.
4. **Desinkronisasi `users.class_id` saat Tutup Tahun Ajaran**:
   Proses tutup tahun ajaran hanya memutasi kolom `class_id` pada tabel `students`. Kolom `users.class_id` milik akun siswa tidak diperbarui ke rombel barunya.
5. **Endpoint Peninggalan (Legacy Code)**:
   Rute peninggalan `/users` pada [`routes/modules/admin.php#L43`](file:///c:/laragon/www/SiNilai/routes/modules/admin.php#L43) masih dipertahankan untuk kompatibilitas. Endpoint pengujian `/es` yang sebelumnya ada di `routes/web.php` telah dihapus (SEC-03).
6. **Typo Penamaan Berkas Controller**:
   Berkas [`app/Http/Controllers/DataSekolahContoller.php`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/DataSekolahContoller.php) memiliki salah ketik nama ("Contoller" tanpa huruf "r").

---

## 12. Areas That Require Caution When Modifying

### ✅ 1. Otorisasi Route Master Data (`routes/modules/master.php`) — Teratasi
**Status**: Telah diperbaiki dengan pemisahan grup middleware RBAC:
- Rute Master Data (`/kelas`, `/mapel`, `/mapel-mapping`, `/meskul`, `/mfst`, `/datasekolah`) dilindungi oleh `['auth', 'roleCheck:1']` (Khusus Administrator).
- Rute Data Siswa (`/siswa`) dilindungi oleh `['auth', 'roleCheck:1,2', 'checkClass']` (Administrator & Wali Kelas terdaftar).
- Rute Profil (`/profile`) tetap diakses oleh seluruh pengguna terotentikasi (`auth`).
**Catatan Modifikasi**: Non-admin (siswa dan guru biasa) sekarang akan menerima HTTP 403 Forbidden jika mencoba mengakses endpoint CRUD data master secara langsung.

### ⚠️ 2. Pengecualian Role Siswa pada `CheckAssignedClass`
**Risiko**: Pada [`CheckAssignedClass.php#L26`](file:///c:/laragon/www/SiNilai/app/Http/Middleware/CheckAssignedClass.php#L26), pengecekan rombel wali kelas mengecualikan role 1 dan 3: `!in_array($user->role_id, [1, 3])`. Karena modul akademik ([`routes/modules/akademik.php`](file:///c:/laragon/www/SiNilai/routes/modules/akademik.php)) hanya dibungkus oleh `['auth', 'checkClass']`, akun siswa secara teknis lolos dari filter ini jika tidak ada pengecekan role di dalam controller.
**Rekomendasi**: Tetapkan pembatasan `roleCheck:1,2` pada level grup route `akademik.php`.

### ⚠️ 3. Webhook Single Logout (`POST /sso/slo`)
**Risiko**: Endpoint ini dikecualikan dari verifikasi CSRF di [`bootstrap/app.php`](file:///c:/laragon/www/SiNilai/bootstrap/app.php#L22) dan tidak memerlukan autentikasi. Pemanggilan sembarang dengan payload form `username` akan menghapus seluruh session database user terkait (`DB::table('sessions')->where('user_id', $user->id)->delete()`).
**Rekomendasi**: Tambahkan verifikasi shared secret atau signature HMAC antara SSO Server dan SiNilai sebelum memproses request SLO.

### ⚠️ 4. Konsistensi Semester Lock pada Push Skor CBT
**Risiko**: Method [`NilaiController::syncFromCbt`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/NilaiController.php#L980) memvalidasi `$this->isSemesterLocked($fstId)`. Sebaliknya, method inbound [`Api\CbtSyncController::storeScores`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/Api/CbtSyncController.php#L301) **hanya** mengecek `raport_statuses.status === 'approved_locked'`, dan tidak memvalidasi flag global `m_fst_pembelajaran.is_locked`.
**Rekomendasi**: Jika memodifikasi `CbtSyncController`, selalu sertakan pengecekan kuncian global semester (`m_fst_pembelajaran.is_locked`).

### ⚠️ 5. Inisialisasi Setting pada Pengujian Otomatis (`CbtSyncApiTest`)
**Risiko**: Eksekusi `php artisan test` secara default gagal pada 10 feature test API CBT jika nilai `module_cbt_sync` pada tabel `settings` bernilai `"0"`. Middleware `CbtSyncAuth` akan menolak request dengan status 403 sebelum token divalidasi.
**Rekomendasi**: Saat menjalankan atau memperbarui unit/feature tests, pastikan method `setUp()` menginisialisasi setting:
```php
\App\Models\Setting::updateOrCreate(['key' => 'module_cbt_sync'], ['value' => '1']);
```

### ⚠️ 6. Konsumsi Memori DomPDF pada Rombel Besar
**Risiko**: Engine DomPDF merender seluruh canvas HTML ke memori sebelum file PDF di-stream atau disimpan ke disk storage.
**Rekomendasi**: Jangan pernah menghapus batasan chunk limit (default 3–4 siswa per batch) pada [`NilaiAkhirController::generateZipChunk`](file:///c:/laragon/www/SiNilai/app/Http/Controllers/NilaiAkhirController.php#L831). Menghapus batasan ini akan memicu fatal error `Allowed memory size exhausted` pada rombel dengan lebih dari 30 siswa.
