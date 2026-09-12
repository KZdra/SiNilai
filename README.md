# SiNilai — Sistem Informasi Penilaian, E-Rapor Kurikulum Merdeka & Sinkronisasi CBT

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-red?style=for-the-badge&logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-^8.2-blue?style=for-the-badge&logo=php" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Architecture-Non--ORM%20Query%20Builder-orange?style=for-the-badge" alt="Non-ORM">
  <img src="https://img.shields.io/badge/Kurikulum-Merdeka-green?style=for-the-badge" alt="Kurikulum Merdeka">
  <img src="https://img.shields.io/badge/License-MIT-purple?style=for-the-badge" alt="MIT License">
</p>

**SiNilai** adalah platform sistem informasi akademik tingkat enterprise yang didesain khusus untuk mengotomatisasi seluruh siklus evaluasi, rekapitulasi capaian belajar, penerbitan buku rapor resmi Kurikulum Merdeka, serta pengarsipan digital terverifikasi.

Aplikasi ini menggunakan pendekatan **Non-ORM (Direct Database Query Builder `DB::table`)** guna menjamin performa agregasi nilai masal yang instan tanpa overhead memori objek.

---

## 📸 Antarmuka Aplikasi (Screenshots)

<p align="center">
  <img width="1920" height="929" alt="Dashboard SiNilai" src="https://github.com/user-attachments/assets/2093212b-e00c-439c-a90e-3cb674c31553" />
</p>
<p align="center">
  <img width="1920" height="1174" alt="Rekap Nilai Akhir & Ranking" src="https://github.com/user-attachments/assets/59d32dc1-e2ae-4cb4-841e-5130d4fc338a" />
</p>
<p align="center">
  <img width="1920" height="1328" alt="Pemetaan Mapel Dinamis" src="https://github.com/user-attachments/assets/b76be78a-5389-4ccc-ba5c-772063f63bca" />
</p>
<p align="center">
  <img width="1920" height="1598" alt="Manajemen Nilai Terintegrasi" src="https://github.com/user-attachments/assets/576fa9a8-eea2-4694-897e-0f568ab817e8" />
</p>

---

## 🌟 Fitur Utama Sistem

### 1. 📊 Manajemen Nilai & Asesmen Kurikulum Merdeka
- **Multi-Komponen Penilaian**: Input nilai Sumatif Harian (1 s.d. 10), Sumatif Tengah Semester (STS), dan Sumatif Akhir Semester (SAS).
- **Tujuan Pembelajaran (TP) & Asesmen Formatif (KKTP)**: Perumusan deskripsi capaian kompetensi otomatis yang langsung terintegrasi ke narasi rapor.
- **Dynamic Subject Mapping (`mapel_class_fst`)**: Fitur ON/OFF mata pelajaran per rombel dan semester via AJAX. Nilai mapel non-aktif diabaikan dari pembagi rata-rata.
- **Semester Lock (`is_locked`)**: Otoritas kurikulum untuk mengunci semester agar nilai tidak dapat dimanipulasi setelah batas waktu berakhir.

### 2. 🖨️ Pengarsipan Rapor & Validasi Digital Anti-Pemalsuan
- **Cetak Raport Lengkap (PDF & Excel)**: Cetak rapor akademik resmi, buku leger per kelas, dan export ranking siswa.
- **Stempel Digital QR-Code**: Setiap cetakan rapor menyematkan QR-Code verifikasi publik unik 32-karakter (`/verifikasi-raport/{token}`). Siapapun (orang tua/instansi) dapat memvalidasi keaslian rapor tanpa perlu login.
- **Penyimpanan Storage Berjenjang Otomatis**: PDF rapor tersimpan rapi per tahun ajaran, fase, dan semester:
  ```text
  storage/app/public/raport/{Nama_Kelas}/{TahunAjaran_Fase_Semester}/{Nama_Siswa}.pdf
  ```

### 3. 🎯 Modul Projek P5 (Profil Pelajar Pancasila)
- Master 6 Dimensi, Elemen, dan Subelemen Profil Pelajar Pancasila lengkap dengan capaian akhir fase.
- Manajemen Projek Rombel & penugasan Guru Fasilitator.
- Rubrik penilaian 4 skala standar BSKAP: **MB** (Mulai Berkembang), **SB** (Sedang Berkembang), **BSH** (Berkembang Sesuai Harapan), dan **SAB** (Sangat Berkembang).
- Cetak lembar Rapor Projek P5 resmi terpisah dari rapor akademik.

### 4. 🚀 Kenaikan Kelas Berjenjang & Tutup Tahun Ajaran Otomatis
- **Algoritma Top-Down Cascading**: Eksekusi promosi berjenjang otomatis (Kelas XII ➔ Alumni/Lulus, Kelas XI ➔ XII, Kelas X ➔ XI) tanpa risiko rombel bertabrakan.
- **Retention Safeguard**: Siswa berstatus *Tinggal Kelas* pada catatan wali kelas otomatis dilewati dan dipertahankan di rombel asalnya.
- **FST Lifecycle Automation**: 
  - FST semester lama otomatis dikunci permanen (`is_locked = true`).
  - FST Tahun Ajaran Baru (Semester Ganjil) otomatis dihitung dan dibuat (misal: `2024/2025` ➔ `2025/2026`).

### 5. 👨‍🎓 Portal Mandiri Siswa & Orang Tua
- Akses mandiri untuk siswa dan orang tua melihat rekap nilai, catatan kehadiran, catatan wali kelas, dan rapor P5.
- Multi-Semester Historical Navigator: Siswa dapat meninjau arsip nilai dari semester terdahulu kapan saja.
- Otomasi generate akun login masal berbasis NISN/NIS dengan fitur mandiri ganti kata sandi.

### 6. 🌐 Interoperabilitas CBT & Single Sign-On (SSO)
- **Bidirectional CBT Integration**:
  - *Push Mode*: Server CBT mengirim nilai ujian langsung ke API SiNilai (`POST /api/cbt/scores`) via Bearer Token.
  - *Pull Mode*: Guru menarik nilai hasil ujian dari CBT secara instan lewat tombol "Tarik Nilai CBT".
- **SSO OAuth2 Client**: Terintegrasi penuh dengan server SSO (Authorization Code Flow) dan Single Logout (SLO).
- **Audit Trail Mutasi Nilai**: Pencatatan riwayat setiap penambahan, pengeditan, penghapusan, dan import nilai ke tabel `nilai_audit_logs`.

---

## 🛠️ Spesifikasi Teknologi

| Komponen | Teknologi | Keterangan |
| :--- | :--- | :--- |
| **Backend Engine** | PHP `^8.2`, Laravel `^11.31` | Arsitektur MVC Non-ORM (Direct Query Builder) |
| **Database** | SQLite / MySQL 8.0+ / MariaDB | SQLite untuk portable dev; MySQL untuk produksi |
| **UI & Theme** | AdminLTE 3, Bootstrap 4.6 | Antarmuka intuitif untuk guru dan tenaga pendidik |
| **Client Scripting** | jQuery, DataTables BS4, SweetAlert2 | Interaksi AJAX asinkron dan modal interaktif |
| **PDF & QR Engine** | DomPDF `^3.1`, Chillerlan QR Code `^6.0` | Pembuatan rapor dan stempel validasi publik |
| **Spreadsheet** | Maatwebsite Excel `^3.1` | Import template nilai dan export leger nilai |
| **Asset Pipeline** | Vite `^6.0`, PostCSS, TailwindCSS | Kompilasi asset modern dan bundling cepat |

---

## 🚀 Panduan Instalasi Lokal

### 1. Prasyarat Sistem
- PHP `>= 8.2` (ekstensi: `pdo`, `mbstring`, `openssl`, `xml`, `gd`, `zip`, `curl`)
- Composer `>= 2.6.x`
- Node.js `>= 18.x` & NPM
- SQLite atau MySQL

### 2. Kloning & Instal Dependensi
```bash
git clone https://github.com/KZdra/SiNilai.git
cd SiNilai

composer install
npm install && npm run build
```

### 3. Konfigurasi Environtment
```bash
cp .env.example .env
php artisan key:generate
```
*Sesuaikan konfigurasi database pada `.env` (Default: `DB_CONNECTION=sqlite`). Jika menggunakan SQLite:*
```bash
touch database/database.sqlite
```

### 4. Migrasi & Seeder Database
```bash
php artisan migrate --seed
php artisan storage:link
```

> [!TIP]
> Jika ingin database langsung terisi data simulasi lengkap (sekolah, rombel X-XII, guru, siswa, nilai, P5, dan akun portal):
> ```bash
> php artisan db:seed --class=CompleteDummyDataSeeder
> ```

### 5. Jalankan Server
```bash
php artisan serve
```
Akses aplikasi melalui browser di: `http://127.0.0.1:8000`

**Kredensial Default:**
- **Admin**: `username: admin` | `password: password`
- **Siswa (Portal)**: `username: [NISN / NIS]` | `password: siswa123`

---

## 📖 Dokumentasi Lengkap & Arsitektur

Untuk panduan teknis enterprise yang mencakup diagram arsitektur C4, pohon direktori mendalam, skema database ERD lengkap, dan kamus API:
👉 Buka berkas [documentation.md](file:///c:/Users/Indruyy/Documents/codingan/SiNilai/documentation.md)

---

## 📝 Konvensi & Lisensi

- **Non-ORM Rule**: Semua interaksi data transaksional akademik menggunakan `DB::table('nama_tabel')`. Hindari penggunaan hidrasi model Eloquent untuk kalkulasi agregat.
- Proyek ini dirilis di bawah lisensi [MIT License](LICENSE).
