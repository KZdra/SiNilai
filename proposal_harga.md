# Skema Penawaran Sistem Informasi Akademik & SSO (SiNilai)

Dokumen ini berisi panduan dan opsi skema penawaran aplikasi **SiNilai & SSO (Single Sign-On)** untuk sekolah swasta menengah ke bawah, di mana aplikasi akan di-host/di-deploy pada server milik sekolah (On-Premise / School Hosting).

---

## 💡 Pendekatan Penjualan (Pitching)
Karena sekolah yang disasar adalah swasta kelas menengah, **jangan** fokus pada kecanggihan teknologi (seperti Docker atau OAuth2). Fokuslah pada manfaat langsung:
1. **Meningkatkan Gengsi Sekolah:** Sistem sudah terpusat (SSO) setara dengan kampus atau sekolah elit. Sangat bagus untuk bahan promosi PPDB (Penerimaan Peserta Didik Baru).
2. **Ekosistem Aplikasi Terintegrasi (Roadmap):** Tekankan bahwa dengan adanya SSO, sekolah tidak hanya membeli 1 aplikasi. SSO ini adalah *pondasi dasar*. Ke depannya, jika sekolah butuh **Sistem Absensi (Kehadiran)**, Perpustakaan, atau E-Learning, semuanya tinggal dicolok ke SSO ini tanpa perlu buat akun baru lagi untuk guru dan siswa. Ini adalah *Super App* versi sekolah!
3. **Efisiensi Kerja Guru:** Tidak ada lagi lembur rekap nilai manual di Excel. Sistem *Bulk Input* mempercepat kerja wali kelas.
4. **Lebih Aman & Terpusat:** Data nilai tidak tercecer di flashdisk masing-masing guru.
5. **Minim Investasi:** Sekolah tidak perlu membeli software puluhan juta.

---

## 🗺️ Roadmap Ekosistem Aplikasi (Peluang Upselling)
Dengan SSO sebagai pusat akun, kamu punya banyak peluang untuk menawarkan (menjual) modul tambahan di semester/tahun berikutnya tanpa harus merombak sistem dari nol. Berikut adalah fitur-fitur incaran sekolah swasta yang bisa kamu buatkan project-nya ke depan:

1. **Modul Absensi Digital:** Pencatatan kehadiran siswa harian. Sangat berguna untuk bahan evaluasi kedisiplinan.
2. **Modul Keuangan (E-SPP):** Sistem pencatatan pembayaran SPP dan tunggakan siswa. Ini adalah **urat nadi sekolah swasta**, kepala sekolah pasti sangat tertarik karena ini menyangkut arus kas (cashflow).
3. **Portal Orang Tua (Parent App/Web):** Modul khusus orang tua agar mereka bisa login (via SSO) untuk memantau Absensi, Nilai (Raport), dan status pembayaran SPP anak mereka dari HP.
4. **Sistem PPDB Online:** Aplikasi penerimaan siswa baru yang terintegrasi, di mana data pendaftar otomatis masuk ke database pusat jika diterima.
5. **CBT (Computer Based Test):** Modul ujian online untuk UTS/UAS mandiri yang menghemat biaya kertas sekolah.

---

## 💰 Opsi Skema Harga (Dipilih Sesuai Karakter Yayasan)

*Catatan: Harga di bawah ini adalah untuk paket "SiNilai + SSO". Ke depannya saat aplikasi Absensi selesai, kamu bisa melakukan **Upselling** (menawarkan aplikasi absensi dengan biaya tambahan).*

Karena aplikasi di-host di server sekolah (kamu tidak menanggung biaya server bulanan), margin keuntunganmu bisa 100% masuk sebagai jasa sistem dan pemeliharaan (maintenance).

### Opsi 1: Skema Beban Siswa (Paling Direkomendasikan)
Biaya software dimasukkan ke dalam komponen SPP atau biaya kegiatan akhir semester siswa. Pihak Yayasan/Sekolah tidak perlu mengeluarkan budget dari kas operasional mereka.

*   **Tarif:** Rp 3.000 s/d Rp 5.000 per siswa / semester.
*   **Simulasi:** 
    *   Jika jumlah siswa 500 anak.
    *   Pendapatan: 500 x Rp 5.000 = **Rp 2.500.000 per semester** (Rp 5.000.000 per tahun).
*   **Kelebihan:** Sangat ringan bagi orang tua (harga setara jajan), tapi menguntungkan buat kamu sebagai *passive income* jangka panjang.
*   **Layanan Termasuk:** Lisensi aplikasi berjalan, support teknis jika ada error, dan pendampingan input nilai saat akhir semester.

### Opsi 2: Skema Flat Tahunan (Sewa Lisensi & Jasa IT)
Jika pihak sekolah menolak membebankan biaya ke siswa, tawarkan paket biaya per tahun sebagai pengganti "gaji staf IT".

*   **Tarif:** **Rp 3.000.000 s/d Rp 4.500.000 per tahun** (Bisa dibayar per semester).
*   **Kelebihan:** Harga yang sangat wajar (kurang dari Rp 400.000/bulan) untuk sistem canggih berstandar SSO. Jauh lebih murah daripada sekolah merekrut satu pegawai staf IT khusus programmer.
*   **Layanan Termasuk:** Lisensi penggunaan SiNilai + SSO, maintenance, *remote support* ketika server/aplikasi bermasalah.

### Opsi 3: Skema Jual Lisensi Terbatas (One-Time Setup + Maintenance)
Cocok jika sekolah sedang punya dana cair/BOS dan ingin bayar agak besar di depan.

*   **Biaya Setup & Instalasi (Bayar di Awal):** Rp 7.500.000 s/d Rp 10.000.000 (Satu kali bayar).
*   **Biaya Maintenance Tahunan (Tahun ke-2 dst):** Rp 1.500.000 s/d Rp 2.000.000 per tahun.
*   **Syarat:** *Source code* tetap menjadi milikmu (jangan berikan master code utuh, atau berikan dengan *license key* / enkripsi ringan jika memungkinkan), sekolah hanya berhak menggunakan di satu server mereka.

---

## 🛠️ Tugas Kamu (Sebagai Penyedia Sistem)
Karena server dari sekolah, tugasmu adalah:
1. **Setup Awal:** Datang ke sekolah (atau via Remote/AnyDesk) untuk menginstal aplikasi menggunakan Docker di server/PC sekolah.
2. **Training Guru:** Memberikan pelatihan 1-2 hari cara input nilai bagi guru dan admin.
3. **Standby Masa Rawan:** Siap ditelepon saat minggu-minggu pengisian raport dan pencetakan (biasanya bulan Desember dan Juni) untuk memastikan tidak ada *bug* atau server *down*.

## 🚀 Trik Closing / Deal & Upselling
1. **Tawarkan Trial Gratis:** *"Pak/Bu, semester ini coba pakai sistem Raport (SiNilai) dulu secara GRATIS. Biar guru-guru rasakan kemudahannya. Kalau nanti ternyata tidak cocok, tidak usah dilanjut tidak apa-apa."* (Biasanya, kalau sudah pakai dan merasa gampang, sekolah tidak akan mau kembali ke manual).
2. **Jual Pondasinya, Upsell Sisanya:** Beri tahu yayasan *"Sistem login (SSO) ini saya berikan satu paket. Nanti kalau yayasan butuh modul Absensi Digital untuk kedisiplinan, sistemnya sudah siap nampung, tinggal kita aktifkan saja dengan biaya tambahan yang terjangkau."*
3. **Kustomisasi Ringan:** Janjikan bahwa logo sekolah dan nama Yayasan akan dipasang cantik di dashboard agar sistem terasa eskslusif milik mereka.
