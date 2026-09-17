@extends('layouts.app')

@section('styles')
<style>
    .timeline-step {
        position: relative;
        padding-left: 50px;
        margin-bottom: 30px;
    }
    .timeline-step::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 35px;
        bottom: -30px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-step:last-child::before {
        display: none;
    }
    .timeline-badge {
        position: absolute;
        left: 0;
        top: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
        color: #fff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .flow-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .card-workflow {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
    }
    .card-workflow:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    .step-shortcut-btn {
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
    }
    @media print {
        .main-sidebar, .main-header, .main-footer, .no-print, .btn, .breadcrumb {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            break-inside: avoid;
        }
    }
</style>
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-8">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded p-3 mr-3 shadow-sm">
                            <i class="fas fa-book-reader fa-2x"></i>
                        </div>
                        <div>
                            <h1 class="m-0 text-dark font-weight-bold">
                                {{ __('Panduan Lengkap & Alur Kerja SiNilai') }}
                            </h1>
                            <p class="text-muted small mb-0 mt-1">
                                Standar Operasional Prosedur (SOP) terintegrasi mulai dari konfigurasi awal master data, asesmen guru, catatan wali kelas, hingga penerbitan rapor resmi.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 text-right no-print">
                    <button class="btn btn-outline-secondary font-weight-bold mr-2 shadow-sm" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Cetak / Simpan PDF
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-home mr-1"></i> Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- ── RINGKASAN STATUS SISTEM AKTIF ──────────────────────────────── -->
            <div class="row mb-3 no-print">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 10px; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #fff;">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-school fa-2x text-warning mr-3"></i>
                                        <div>
                                            <h5 class="font-weight-bold mb-0 text-white">{{ $sekolah->nama_sekolah ?? 'Nama Sekolah Belum Diatur' }}</h5>
                                            <small class="text-light opacity-75">
                                                NPSN: {{ $sekolah->npsn ?? '-' }} &bull; Periode Berjalan: 
                                                @if($activeFst)
                                                    <span class="badge badge-success px-2">{{ $activeFst->tahun_ajaran }} ({{ $activeFst->semester }})</span>
                                                @else
                                                    <span class="badge badge-warning px-2">Belum Ada Periode Aktif</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <span class="badge badge-light px-3 py-2 mr-1">
                                        <i class="fas fa-chalkboard text-primary mr-1"></i> {{ $totalKelas }} Rombel Kelas
                                    </span>
                                    <span class="badge badge-light px-3 py-2 mr-1">
                                        <i class="fas fa-book text-info mr-1"></i> {{ $totalMapel }} Mata Pelajaran
                                    </span>
                                    <span class="badge badge-light px-3 py-2">
                                        <i class="fas fa-user-graduate text-success mr-1"></i> {{ $totalSiswa }} Siswa Terdaftar
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── DIAGRAM VISUAL 5 FASE UTAMA (STEPPER) ────────────────────────── -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="font-weight-bold text-dark mb-0">
                        <i class="fas fa-project-diagram text-primary mr-2"></i>Peta Alur Kerja End-to-End (5 Fase Berurutan)
                    </h6>
                </div>
                <div class="card-body pt-0 px-3 pb-3">
                    <div class="row text-center">
                        <div class="col-md col-sm-6 mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded border h-100">
                                <span class="badge badge-primary rounded-circle mb-2 px-3 py-2 font-weight-bold" style="font-size: 14px;">1</span>
                                <h6 class="font-weight-bold text-dark mb-1">Setup Master</h6>
                                <p class="small text-muted mb-0">Profil Sekolah, FST, Kelas, Mapel, Siswa & Akun.</p>
                                <span class="flow-badge bg-primary text-white mt-2">Admin</span>
                            </div>
                        </div>
                        <div class="col-md col-sm-6 mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded border h-100">
                                <span class="badge badge-info rounded-circle mb-2 px-3 py-2 font-weight-bold" style="font-size: 14px;">2</span>
                                <h6 class="font-weight-bold text-dark mb-1">Mapping Mapel</h6>
                                <p class="small text-muted mb-0">Hubungkan mapel ke kelas agar guru bisa input nilai.</p>
                                <span class="flow-badge bg-primary text-white mt-2">Admin</span>
                            </div>
                        </div>
                        <div class="col-md col-sm-6 mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded border h-100">
                                <span class="badge badge-success rounded-circle mb-2 px-3 py-2 font-weight-bold" style="font-size: 14px;">3</span>
                                <h6 class="font-weight-bold text-dark mb-1">Input Nilai Guru</h6>
                                <p class="small text-muted mb-0">Upload Excel Cepat (TP, Formatif, dan Sumatif).</p>
                                <span class="flow-badge bg-success text-white mt-2">Guru Mapel</span>
                            </div>
                        </div>
                        <div class="col-md col-sm-6 mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded border h-100">
                                <span class="badge badge-warning rounded-circle mb-2 px-3 py-2 font-weight-bold text-dark" style="font-size: 14px;">4</span>
                                <h6 class="font-weight-bold text-dark mb-1">Catatan & Ekskul</h6>
                                <p class="small text-muted mb-0">Presensi, Catatan Walas, Ekskul, dan Projek P5.</p>
                                <span class="flow-badge bg-warning text-dark mt-2">Wali Kelas</span>
                            </div>
                        </div>
                        <div class="col-md col-sm-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <span class="badge badge-danger rounded-circle mb-2 px-3 py-2 font-weight-bold" style="font-size: 14px;">5</span>
                                <h6 class="font-weight-bold text-dark mb-1">Cetak & Tutup TA</h6>
                                <p class="small text-muted mb-0">Print Rapor, Download ZIP, Kunci FST & Naik Kelas.</p>
                                <span class="flow-badge bg-dark text-white mt-2">Walas & Admin</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── DETAIL ALUR KERJA BERDASARKAN FASE ───────────────────────────── -->
            <div class="row">
                <div class="col-lg-12">

                    <!-- ── FASE 1: PERSIAPAN MASTER DATA ──────────────────────── -->
                    <div class="timeline-step">
                        <div class="timeline-badge bg-primary">1</div>
                        <div class="card card-workflow shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-primary mr-2 px-2 py-1">FASE 1</span>
                                    <strong class="text-dark" style="font-size: 16px;">Inisialisasi & Persiapan Master Data</strong>
                                </div>
                                <span class="badge badge-light border text-muted">Penanggung Jawab: Administrator</span>
                            </div>
                            <div class="card-body">
                                <p class="text-secondary small mb-3">
                                    Fase ini dilakukan <strong>sekali pada awal tahun ajaran</strong> atau saat pertama kali mengoperasikan aplikasi SiNilai. Semua data harus disiapkan sebelum guru dapat menginput nilai.
                                </p>

                                <div class="row">
                                    <!-- 1.1 Data Sekolah -->
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-light rounded h-100 border">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="font-weight-bold text-dark mb-0">1.1 Profil Sekolah & TTD Rapor</h6>
                                                <a href="{{ route('datasekolah.index') }}" class="btn btn-sm btn-primary step-shortcut-btn no-print">
                                                    Buka <i class="fas fa-external-link-alt ml-1"></i>
                                                </a>
                                            </div>
                                            <p class="small text-muted mb-2">
                                                Isi identitas resmi: Nama Sekolah, NPSN, NSS, Alamat lengkap, hingga Nama Kepala Sekolah & NIP.
                                            </p>
                                            <span class="badge badge-info small"><i class="fas fa-info-circle mr-1"></i> Otomatis menjadi Kop & Cover Rapor</span>
                                        </div>
                                    </div>

                                    <!-- 1.2 FST Periode -->
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-light rounded h-100 border">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="font-weight-bold text-dark mb-0">1.2 Periode & FST (Fase / Semester)</h6>
                                                <a href="{{ route('mfst.index') }}" class="btn btn-sm btn-primary step-shortcut-btn no-print">
                                                    Buka <i class="fas fa-external-link-alt ml-1"></i>
                                                </a>
                                            </div>
                                            <p class="small text-muted mb-2">
                                                Tentukan Fase E (Kelas 10) atau Fase F (Kelas 11 & 12), Semester (Ganjil/Genap), Tahun Ajaran (misal <code>2024/2025</code>), dan Status Kunci: <strong>Terbuka</strong>.
                                            </p>
                                            <span class="badge badge-warning text-dark small"><i class="fas fa-lock-open mr-1"></i> Kunci Nilai dilakukan saat tutup semester</span>
                                        </div>
                                    </div>

                                    <!-- 1.3 Data Kelas -->
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-light rounded h-100 border">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="font-weight-bold text-dark mb-0">1.3 Master Kelas / Rombel</h6>
                                                <a href="{{ route('class.index') }}" class="btn btn-sm btn-primary step-shortcut-btn no-print">
                                                    Buka <i class="fas fa-external-link-alt ml-1"></i>
                                                </a>
                                            </div>
                                            <p class="small text-muted mb-2">
                                                Daftarkan rombongan belajar (contoh: <code>X RPL</code>, <code>XI TKJ 1</code>, <code>XII DKV 2</code>). Penamaan kelas harus baku karena akan menjadi judul sheet impor siswa.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- 1.4 Data Mapel -->
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-light rounded h-100 border">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="font-weight-bold text-dark mb-0">1.4 Master Mata Pelajaran</h6>
                                                <a href="{{ route('mapel.index') }}" class="btn btn-sm btn-primary step-shortcut-btn no-print">
                                                    Buka <i class="fas fa-external-link-alt ml-1"></i>
                                                </a>
                                            </div>
                                            <p class="small text-muted mb-2">
                                                Daftarkan seluruh mata pelajaran kurikulum merdeka dan muatan lokal yang diajarkan di sekolah.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- 1.5 Import Siswa -->
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-light rounded h-100 border">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="font-weight-bold text-dark mb-0">1.5 Impor Data Siswa (Excel)</h6>
                                                <a href="{{ route('student.index') }}" class="btn btn-sm btn-primary step-shortcut-btn no-print">
                                                    Buka <i class="fas fa-external-link-alt ml-1"></i>
                                                </a>
                                            </div>
                                            <p class="small text-muted mb-2">
                                                Unduh <strong>Template Siswa Semua Kelas</strong>, isi data identitas (NIS, NISN, Nama Siswa, JK, TTL, dll) pada tab sheet masing-masing kelas, lalu unggah kembali.
                                            </p>
                                            <span class="badge badge-success small"><i class="fas fa-check mr-1"></i> NIS wajib unik per siswa</span>
                                        </div>
                                    </div>

                                    <!-- 1.6 Akun Guru & Walas -->
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-light rounded h-100 border">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="font-weight-bold text-dark mb-0">1.6 Akun Pengguna & Wali Kelas</h6>
                                                <a href="{{ route('muser.index') }}" class="btn btn-sm btn-primary step-shortcut-btn no-print">
                                                    Buka <i class="fas fa-external-link-alt ml-1"></i>
                                                </a>
                                            </div>
                                            <p class="small text-muted mb-2">
                                                Buat akun untuk setiap guru. Khusus Wali Kelas, pilih kelas yang diampu pada kolom <strong>Kelas Walas</strong>. Gunakan tombol <strong>Ekspor Kredensial Walas</strong> untuk mencetak username & password default.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── FASE 2: MAPPING MAPEL & PERSIAPAN NILAI ─────────────── -->
                    <div class="timeline-step">
                        <div class="timeline-badge bg-info">2</div>
                        <div class="card card-workflow shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-info mr-2 px-2 py-1">FASE 2</span>
                                    <strong class="text-dark" style="font-size: 16px;">Pemetaan Mata Pelajaran ke Kelas (Mapping Mapel)</strong>
                                </div>
                                <span class="badge badge-light border text-muted">Penanggung Jawab: Administrator / Kurikulum</span>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning border-0 mb-3" style="border-radius: 8px;">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Langkah Kunci Sangat Penting:</strong> Jika mata pelajaran belum dipetakan ke suatu kelas, guru mapel <strong>TIDAK AKAN BISA</strong> menginput nilai atau mengunduh template Excel untuk kelas tersebut!
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="font-weight-bold text-dark mb-1">Cara Melakukan Mapping Mapel:</h6>
                                        <ol class="small pl-3 mb-0 text-secondary">
                                            <li>Buka menu <a href="{{ route('mapel_mapping.index') }}" class="font-weight-bold text-primary">Mapping Mapel</a>.</li>
                                            <li>Pilih Periode Semester aktif dan pilih Kelas yang ingin dipetakan.</li>
                                            <li>Centang (Aktifkan) mata pelajaran apa saja yang dipelajari oleh kelas tersebut.</li>
                                            <li>Tentukan guru pengampunya jika diperlukan.</li>
                                        </ol>
                                    </div>
                                    <div class="no-print">
                                        <a href="{{ route('mapel_mapping.index') }}" class="btn btn-info font-weight-bold px-3 shadow-sm">
                                            <i class="fas fa-network-wired mr-1"></i> Buka Mapping Mapel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── FASE 3: ASESMEN & INPUT NILAI OLEH GURU ─────────────── -->
                    <div class="timeline-step">
                        <div class="timeline-badge bg-success">3</div>
                        <div class="card card-workflow shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-success mr-2 px-2 py-1">FASE 3</span>
                                    <strong class="text-dark" style="font-size: 16px;">Pengisian Asesmen & Nilai Pembelajaran</strong>
                                </div>
                                <span class="badge badge-light border text-muted">Penanggung Jawab: Guru Mata Pelajaran</span>
                            </div>
                            <div class="card-body">
                                <p class="text-secondary small mb-3">
                                    Guru mata pelajaran dapat memilih salah satu dari 2 metode pengisian nilai di bawah ini:
                                </p>

                                <div class="row">
                                    <!-- Jalur Cepat (Excel) -->
                                    <div class="col-md-7 mb-3">
                                        <div class="p-3 rounded border h-100" style="background-color: #f0fdf4; border-color: #bbf7d0 !important;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="font-weight-bold text-success mb-0">
                                                    <i class="fas fa-star mr-1"></i> Metode 1: Upload Nilai (Excel) &mdash; Rekomendasi Cepat
                                                </h6>
                                                <a href="{{ route('nilai_import.index') }}" class="btn btn-sm btn-success step-shortcut-btn no-print">
                                                    Buka <i class="fas fa-file-excel ml-1"></i>
                                                </a>
                                            </div>
                                            <ul class="small pl-3 mb-0 text-dark">
                                                <li class="mb-1"><strong>Langkah 1:</strong> Buka menu <strong>Upload Nilai (Excel)</strong>, pilih Mata Pelajaran dan Periode.</li>
                                                <li class="mb-1"><strong>Langkah 2:</strong> Klik tombol <strong>Download Format Excel (Multi-Sheet Semua Kelas)</strong>. Seluruh siswa otomatis sudah terdaftar di tab sheet masing-masing kelas.</li>
                                                <li class="mb-1"><strong>Langkah 3:</strong> Isi teks Tujuan Pembelajaran (TP), Nilai Formatif (1–100), dan Nilai Sumatif (STS/SAS).</li>
                                                <li><strong>Langkah 4:</strong> Upload file Excel tersebut. Sistem akan memproses seluruh kelas sekaligus dalam hitungan detik!</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Jalur Manual (Web) -->
                                    <div class="col-md-5 mb-3">
                                        <div class="p-3 bg-light rounded border h-100">
                                            <h6 class="font-weight-bold text-dark mb-2">Metode 2: Input Manual Melalui Web</h6>
                                            <ul class="small pl-3 mb-0 text-muted">
                                                <li class="mb-1">
                                                    <strong>Tujuan Pembelajaran:</strong> Masukkan kode dan deskripsi TP di menu <a href="{{ route('mastertp.index') }}">Tujuan Pembelajaran</a>.
                                                </li>
                                                <li class="mb-1">
                                                    <strong>Asesmen Formatif:</strong> Input capaian per-TP di menu <a href="{{ route('formatif.index') }}">Asesmen Formatif</a>.
                                                </li>
                                                <li>
                                                    <strong>Asesmen Sumatif:</strong> Input nilai tes & non-tes di menu <a href="{{ route('value.index') }}">Asesmen Sumatif</a>.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── FASE 4: KELENGKAPAN RAPOR OLEH WALI KELAS ───────────── -->
                    <div class="timeline-step">
                        <div class="timeline-badge bg-warning text-dark">4</div>
                        <div class="card card-workflow shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-warning text-dark mr-2 px-2 py-1">FASE 4</span>
                                    <strong class="text-dark" style="font-size: 16px;">Kelengkapan Rapor & Catatan Wali Kelas</strong>
                                </div>
                                <span class="badge badge-light border text-muted">Penanggung Jawab: Wali Kelas</span>
                            </div>
                            <div class="card-body">
                                <p class="text-secondary small mb-3">
                                    Sebelum mencetak rapor, wali kelas bertanggung jawab melengkapi instrumen non-akademik siswa di kelasnya:
                                </p>

                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="p-3 bg-light rounded border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="text-dark">1. Presensi & Catatan</strong>
                                                <a href="{{ route('walas.index') }}" class="text-primary small font-weight-bold no-print">Buka &rarr;</a>
                                            </div>
                                            <p class="small text-muted mb-0">Input jumlah ketidakhadiran (Sakit, Izin, Alpa) dan tulis narasi Catatan Perkembangan Sikap/Karakter siswa.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="p-3 bg-light rounded border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="text-dark">2. Ekstrakurikuler</strong>
                                                <a href="{{ route('peskul.index') }}" class="text-primary small font-weight-bold no-print">Buka &rarr;</a>
                                            </div>
                                            <p class="small text-muted mb-0">Tentukan ekstrakurikuler yang diikuti siswa (Pramuka, PMR, Olahraga, dll) beserta predikat dan narasi capaiannya.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="p-3 bg-light rounded border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="text-dark">3. Rapor Projek P5</strong>
                                                <a href="{{ route('p5.index') }}" class="text-primary small font-weight-bold no-print">Buka &rarr;</a>
                                            </div>
                                            <p class="small text-muted mb-0">Khusus Kurikulum Merdeka: Nilai sub-elemen projek penguatan profil pelajar pancasila (MB, SB, BSH, SAB).</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── FASE 5: REKAPITULASI, CETAK RAPOR & TUTUP SEMESTER ──── -->
                    <div class="timeline-step">
                        <div class="timeline-badge bg-danger">5</div>
                        <div class="card card-workflow shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-danger mr-2 px-2 py-1">FASE 5</span>
                                    <strong class="text-dark" style="font-size: 16px;">Nilai Akhir, Penerbitan Rapor & Tutup Tahun Ajaran</strong>
                                </div>
                                <span class="badge badge-light border text-muted">Penanggung Jawab: Wali Kelas & Administrator</span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-light rounded border h-100">
                                            <h6 class="font-weight-bold text-dark mb-2">
                                                <i class="fas fa-file-signature text-teal mr-1"></i> A. Alur Wali Kelas di Halaman Nilai Akhir
                                            </h6>
                                            <ol class="small pl-3 mb-0 text-secondary">
                                                <li class="mb-1">
                                                    Buka menu <a href="{{ route('nilaiakhir.index') }}" class="font-weight-bold text-primary">Nilai Akhir & Rapor</a>.
                                                </li>
                                                <li class="mb-1">
                                                    <strong>Langkah 1:</strong> Pilih Periode Semester yang ingin dicetak, lalu pilih Kelas.
                                                </li>
                                                <li class="mb-1">
                                                    <strong>Langkah 2 (Pengaturan Rapor):</strong> Klik tombol <em>Pengaturan Rapor</em> untuk mengatur Titimangsa (contoh: <code>Bandung, 20 Desember 2024</code>) dan Keputusan Kenaikan Kelas jika semester genap.
                                                </li>
                                                <li class="mb-1">
                                                    <strong>Langkah 3 (Cetak):</strong>
                                                    <ul class="pl-3 mt-1">
                                                        <li><strong>Print Rapor Berurutan:</strong> Langsung cetak lembar rapor per siswa urut nomor absen.</li>
                                                        <li><strong>Download ZIP Rapor:</strong> Mengunduh seluruh berkas PDF rapor kelas dalam satu folder ZIP.</li>
                                                        <li><strong>Download Leger Nilai:</strong> Ekspor leger rekap nilai seluruh mapel dalam format Excel.</li>
                                                    </ul>
                                                </li>
                                            </ol>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-light rounded border h-100">
                                            <h6 class="font-weight-bold text-dark mb-2">
                                                <i class="fas fa-lock text-danger mr-1"></i> B. Alur Administrator Tutup Semester & Kenaikan
                                            </h6>
                                            <ol class="small pl-3 mb-0 text-secondary">
                                                <li class="mb-1">
                                                    <strong>Kunci Nilai Semester (Lock):</strong> Buka menu <a href="{{ route('mfst.index') }}" class="font-weight-bold text-primary">FST</a>, klik <strong>Aksi &rarr; Kunci Nilai</strong> pada periode yang selesai agar guru tidak dapat mengubah nilai lagi.
                                                </li>
                                                <li class="mb-1">
                                                    <strong>Kenaikan Kelas & Kelulusan:</strong> Buka menu <a href="{{ route('kenaikan_kelas.index') }}" class="font-weight-bold text-primary">Kenaikan Kelas</a> di akhir tahun ajaran untuk memindahkan siswa kelas X ke XI, XI ke XII, dan meluluskan kelas XII.
                                                </li>
                                                <li>
                                                    <strong>Backup Database:</strong> Buka menu <a href="{{ route('backup.index') }}" class="font-weight-bold text-primary">Backup Database</a> untuk mengunduh arsip cadangan SQL database sekolah.
                                                </li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ── TANYA JAWAB & TROUBLESHOOTING POPULER ───────────────────────── -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="font-weight-bold text-dark mb-0">
                        <i class="fas fa-question-circle text-warning mr-2"></i>Tanya Jawab & Penyelesaian Masalah Populer (FAQ)
                    </h6>
                </div>
                <div class="card-body pt-0 px-3 pb-3">
                    <div class="accordion" id="faqAccordion">

                        <!-- FAQ 1 -->
                        <div class="card shadow-none border mb-2" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2" id="headingOne">
                                <h6 class="mb-0">
                                    <button class="btn btn-link btn-block text-left font-weight-bold text-dark p-0" type="button" data-toggle="collapse" data-target="#collapseOne">
                                        <i class="fas fa-chevron-right mr-2 text-primary"></i> Mengapa Guru tidak bisa menginput nilai atau kelas tidak muncul di dropdown?
                                    </button>
                                </h6>
                            </div>
                            <div id="collapseOne" class="collapse show" data-parent="#faqAccordion">
                                <div class="card-body py-2 px-3 small text-secondary">
                                    Mata pelajaran tersebut belum dipetakan ke kelas tujuan. Administrator wajib membuka menu <a href="{{ route('mapel_mapping.index') }}" class="font-weight-bold">Mapping Mapel</a>, pilih kelas bersangkutan, lalu centang aktifkan mata pelajaran tersebut.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div class="card shadow-none border mb-2" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2" id="headingTwo">
                                <h6 class="mb-0">
                                    <button class="btn btn-link btn-block text-left font-weight-bold text-dark p-0 collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo">
                                        <i class="fas fa-chevron-right mr-2 text-primary"></i> Mengapa nama Wali Kelas kosong pada lembar tanda tangan rapor?
                                    </button>
                                </h6>
                            </div>
                            <div id="collapseTwo" class="collapse" data-parent="#faqAccordion">
                                <div class="card-body py-2 px-3 small text-secondary">
                                    Akun guru wali kelas belum ditugaskan ke kelasnya. Buka menu <a href="{{ route('muser.index') }}" class="font-weight-bold">Manajemen Pengguna</a>, cari nama guru tersebut, klik tombol <strong>Edit</strong>, lalu pilih rombel pada kolom <strong>Kelas Walas</strong>.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div class="card shadow-none border mb-2" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2" id="headingThree">
                                <h6 class="mb-0">
                                    <button class="btn btn-link btn-block text-left font-weight-bold text-dark p-0 collapsed" type="button" data-toggle="collapse" data-target="#collapseThree">
                                        <i class="fas fa-chevron-right mr-2 text-primary"></i> Bagaimana cara mencetak ulang rapor untuk siswa yang sudah lulus / alumni?
                                    </button>
                                </h6>
                            </div>
                            <div id="collapseThree" class="collapse" data-parent="#faqAccordion">
                                <div class="card-body py-2 px-3 small text-secondary">
                                    Siswa alumni tetap dapat dicetak rapornya dengan riwayat kelas aslinya. Buka menu <a href="{{ route('nilaiakhir.index') }}" class="font-weight-bold">Nilai Akhir & Rapor</a>, pilih <strong>Periode Semester</strong> lampau saat siswa tersebut masih aktif, lalu pilih kelas aslinya. Sistem akan otomatis memuat rekam jejak nilai historis siswa tersebut.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 4 -->
                        <div class="card shadow-none border mb-0" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2" id="headingFour">
                                <h6 class="mb-0">
                                    <button class="btn btn-link btn-block text-left font-weight-bold text-dark p-0 collapsed" type="button" data-toggle="collapse" data-target="#collapseFour">
                                        <i class="fas fa-chevron-right mr-2 text-primary"></i> Bagaimana jika ada guru yang ingin memperbaiki nilai susulan setelah semester ditutup?
                                    </button>
                                </h6>
                            </div>
                            <div id="collapseFour" class="collapse" data-parent="#faqAccordion">
                                <div class="card-body py-2 px-3 small text-secondary">
                                    Administrator dapat membuka kunci sementara melalui menu <a href="{{ route('mfst.index') }}" class="font-weight-bold">FST</a> &rarr; pilih <strong>Buka Kunci Nilai</strong> pada periode tersebut. Setelah guru selesai memperbarui nilai dan wali kelas mencetak ulang rapor, kunci kembali semester tersebut.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
