# SiNilai - Sistem Informasi Penilaian & Raport Kurikulum Merdeka

SiNilai adalah aplikasi berbasis web yang dirancang untuk mempermudah guru dan wali kelas dalam melakukan manajemen nilai, tujuan pembelajaran (TP), asesmen formatif, serta cetak raport Kurikulum Merdeka. Aplikasi ini menggunakan pendekatan **Non-ORM (Direct Database Query Builder)** untuk menjamin kecepatan dan kesederhanaan query.

---

## 📸 Antarmuka Aplikasi (Screenshots)

<img width="1920" height="929" alt="screencapture-127-0-0-1-8000-2026-07-16-02_02_56" src="https://github.com/user-attachments/assets/2093212b-e00c-439c-a90e-3cb674c31553" />
<img width="1920" height="1174" alt="screencapture-127-0-0-1-8000-akhir-2026-07-16-02_05_58" src="https://github.com/user-attachments/assets/59d32dc1-e2ae-4cb4-841e-5130d4fc338a" />
<img width="1920" height="1328" alt="screencapture-127-0-0-1-8000-mapel-mapping-2026-07-16-02_03_44" src="https://github.com/user-attachments/assets/b76be78a-5389-4ccc-ba5c-772063f63bca" />
<img width="1920" height="1598" alt="screencapture-127-0-0-1-8000-nilai-2026-07-16-02_04_17" src="https://github.com/user-attachments/assets/576fa9a8-eea2-4694-897e-0f568ab817e8" />

---
## 📸 Hasil Cetak Raport Kurikulum Merdeka (Screenshots)
<p align="center">
<img width="1241" height="1754" alt="Artanto_Hutasoit_8_page-0001" src="https://github.com/user-attachments/assets/451fae63-0419-49b8-8dda-7807c48a38b1" />
    
</p>
<p align="center">
<img width="1241" height="1754" alt="Artanto_Hutasoit_8_page-0002" src="https://github.com/user-attachments/assets/d99cd1ee-f0fa-4c52-ad6a-88d8476fa3c4" />
    
</p>
<p align="center">
<img width="1241" height="1754" alt="Artanto_Hutasoit_8_page-0003" src="https://github.com/user-attachments/assets/592c816c-24b0-47f2-a504-9e9203c1f716" />
    
</p>



---

## 🌟 Fitur Utama

- **Dynamic Subject Mapping (Pemetaan Mapel Dinamis)**:
  Mengatur aktif/tidaknya (*ON/OFF*) mata pelajaran tertentu untuk setiap kelas dan fase/semester (FST) secara dinamis via AJAX. Berguna untuk membedakan mata pelajaran antar jurusan/tingkat kelas.
- **Manajemen Nilai Terintegrasi**:
  Input nilai harian (1-10), Asesmen Tengah Semester (STS), dan Asesmen Akhir Semester (SAS). Form input mapel secara dinamis ter-filter berdasarkan kelas dan semester aktif.
- **Tujuan Pembelajaran (TP) & Asesmen Formatif**:
  Pembuatan deskripsi TP untuk setiap mapel dan input penilaian formatif (Kriteria Ketercapaian Tujuan Pembelajaran / KKTP) untuk mendeskripsikan capaian kompetensi siswa di raport secara otomatis.
- **Cetak Raport & Export Data**:
  - Cetak Raport Kurikulum Merdeka (Format PDF & Excel).
  - Export Ranking Siswa per kelas berdasarkan rata-rata nilai mapel yang aktif saja.
  - Pengaturan tanggal cetak raport dan keputusan kenaikan kelas/kelulusan langsung dari sistem.
- **Multi-Role Access Control**:
  Hak akses terbagi menjadi Admin (akses penuh & pemetaan mapel) dan Wali Kelas/Guru (mengelola nilai kelas yang ditugaskan).

---

## 🛠️ Spesifikasi Teknologi

- **Framework**: Laravel 11/12
- **Database**: SQLite (Default) / MySQL / PostgreSQL (Menggunakan Laravel Query Builder `DB::table` tanpa Eloquent ORM)
- **UI & Frontend**: AdminLTE 3 (Bootstrap 4), SweetAlert2 (`SwalHelper` terintegrasi), jQuery, dan DataTables.

---

## 🚀 Panduan Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lingkungan lokal Anda:

### 1. Prasyarat
Pastikan komputer Anda sudah terinstal:
- PHP >= 8.2
- Composer
- SQLite / MySQL

### 2. Kloning Repositori
```bash
git clone https://github.com/username/SiNilai.git
cd SiNilai
```

### 3. Instal Dependensi
```bash
composer install
npm install && npm run build
```

### 4. Konfigurasi Environment
Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasinya (terutama bagian koneksi database):
```bash
cp .env.example .env
php artisan key:generate
```

Secara default, aplikasi menggunakan database SQLite. Jika menggunakan SQLite, pastikan file database sudah dibuat:
```bash
touch database/database.sqlite
```

### 5. Migrasi & Seeder Database
Jalankan migrasi tabel beserta data awal/dummy seeder:
```bash
php artisan migrate --seed
```

### 6. Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui browser di: `http://127.0.0.1:8000`

---

## 📝 Catatan Teknis (Khusus Pengembang)

1. **Pendekatan Tanpa ORM**: 
   Demi efisiensi, proyek ini **tidak menggunakan Eloquent ORM**. Semua interaksi database harus menggunakan Query Builder bawaan Laravel.
   *Contoh salah:* `MataPelajaran::all()`
   *Contoh benar:* `DB::table('mata_pelajarans')->get()`
   
2. **Integritas Rata-Rata Nilai Akhir**:
   Perhitungan rata-rata nilai akhir dan ranking di `NilaiAkhirController` sudah terintegrasi secara dinamis dengan tabel pivot `mapel_class_fst`. Jika mata pelajaran di-*disable* (OFF), maka nilai mapel tersebut tidak akan masuk dalam pembagi rata-rata keseluruhan siswa.
