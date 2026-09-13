@extends('layouts.app')

@section('styles')
<style>
    /* ─── Sumatif Spreadsheet Custom Styles ─── */
    #gridCard {
        max-width: 100%;
        overflow: hidden;
    }

    .sumatif-table-wrapper {
        position: relative;
        display: block;
        width: 100%;
        max-width: 100%;
        max-height: calc(100vh - 240px);
        min-height: 380px;
        overflow-x: auto !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .sumatif-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: max-content;
        min-width: 100%;
    }

    .sumatif-table thead th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        color: #1e293b;
        font-weight: 700;
        font-size: 0.83rem;
        padding: 10px 8px;
        border-bottom: 2px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
        vertical-align: middle;
        text-align: center;
        z-index: 20;
    }

    /* ─── Sticky Columns (Desktop & Tablet vs Mobile) ─── */
    @media (min-width: 768px) {
        .sumatif-table .col-sticky-no {
            position: sticky;
            left: 0;
            width: 48px;
            min-width: 48px;
            max-width: 48px;
            background-color: #ffffff !important;
            text-align: center;
            border-right: 1px solid #e2e8f0;
            z-index: 15;
        }
        .sumatif-table thead th.col-sticky-no {
            position: sticky;
            top: 0;
            left: 0;
            z-index: 30;
            background-color: #f8fafc !important;
        }

        .sumatif-table .col-sticky-name {
            position: sticky;
            left: 48px;
            min-width: 200px;
            max-width: 250px;
            background-color: #ffffff !important;
            border-right: 2px solid #cbd5e1 !important;
            box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.08);
            z-index: 15;
        }
        .sumatif-table thead th.col-sticky-name {
            position: sticky;
            top: 0;
            left: 48px;
            z-index: 30;
            background-color: #f8fafc !important;
        }
    }

    @media (max-width: 767.98px) {
        .sumatif-table .col-sticky-no {
            position: static !important;
            width: 40px;
            min-width: 40px;
            text-align: center;
        }
        .sumatif-table .col-sticky-name {
            position: static !important;
            min-width: 170px;
            max-width: 220px;
            box-shadow: none !important;
        }
        .sumatif-table thead th.col-sticky-no,
        .sumatif-table thead th.col-sticky-name {
            position: sticky;
            top: 0;
            left: auto !important;
            z-index: 20 !important;
            box-shadow: none !important;
        }
    }

    /* Cell & Input Styling */
    .sumatif-table tbody td {
        padding: 5px 6px;
        vertical-align: middle;
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
    }

    .sumatif-table tbody tr:hover td {
        background-color: #f8fafc;
    }
    .sumatif-table tbody tr:hover .col-sticky-no,
    .sumatif-table tbody tr:hover .col-sticky-name {
        background-color: #f1f5f9 !important;
    }

    .cell-score-input {
        width: 100%;
        min-width: 58px;
        max-width: 75px;
        text-align: center;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 5px 4px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        transition: all 0.15s ease-in-out;
        background-color: #ffffff;
    }

    .cell-score-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        outline: none;
        background-color: #eff6ff;
    }

    .cell-score-input.border-sts:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25);
        background-color: #fffbeb;
    }

    .cell-score-input.border-sas:focus {
        border-color: #06b6d4;
        box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.25);
        background-color: #ecfeff;
    }

    .cell-score-input.is-changed {
        background-color: #fef3c7 !important;
        border-color: #f59e0b !important;
    }

    /* Columns Width & Header Badges */
    .col-sumatif {
        width: 75px;
        min-width: 75px;
        text-align: center;
    }

    .col-sts {
        width: 85px;
        min-width: 85px;
        background-color: #fffdf5;
        text-align: center;
    }
    .col-sas {
        width: 85px;
        min-width: 85px;
        background-color: #f8fcff;
        text-align: center;
    }
    .col-na {
        width: 90px;
        min-width: 90px;
        background-color: #f0fdf4;
        text-align: center;
    }

    .na-badge {
        font-size: 0.92rem;
        font-weight: 700;
        padding: 5px 8px;
        border-radius: 6px;
        display: inline-block;
        min-width: 45px;
    }

    /* Floating Save Bar */
    .sticky-floating-bar {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1040;
        min-width: 320px;
        max-width: 780px;
        width: 90%;
        background: rgba(15, 23, 42, 0.94);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 50px;
        padding: 10px 24px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
        display: none;
        animation: slideUp 0.3s ease-out;
    }

    @media (max-width: 767.98px) {
        .sticky-floating-bar {
            width: 95% !important;
            min-width: 280px !important;
            padding: 8px 14px !important;
            bottom: 12px !important;
            border-radius: 16px !important;
        }
        .sticky-floating-bar .btn {
            padding: 4px 10px !important;
            font-size: 0.78rem !important;
        }
    }

    @keyframes slideUp {
        from { transform: translate(-50%, 40px); opacity: 0; }
        to { transform: translate(-50%, 0); opacity: 1; }
    }
</style>
@endsection

@section('content')
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-calculator text-primary mr-2"></i>{{ __('Input Asesmen Sumatif') }}
                    </h1>
                    <p class="text-muted small mb-0">Kelola nilai harian (sumatif lingkup materi), STS, SAS, dan hitung nilai akhir rapor secara interaktif.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active">Asesmen Sumatif</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- Panel Filter Terpadu (Unified 1-Row Filter) -->
            <div class="card card-outline card-primary shadow-sm mb-3" id="filterCard">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-filter text-primary mr-1"></i> Filter Kelas, Periode & Mata Pelajaran
                    </h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body py-3">
                    <form id="unifiedFilterForm">
                        <div class="row align-items-end">
                            <!-- Dropdown 1: Kelas -->
                            <div class="col-md-4 col-sm-6 col-12 mb-2">
                                <label for="filter_class_id" class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-school text-primary mr-1"></i> 1. Kelas
                                </label>
                                @if ($className)
                                    <select name="class_id" id="filter_class_id" class="form-control font-weight-bold" disabled>
                                        <option value="{{ Auth::user()->class_id }}" selected>{{ $className }}</option>
                                    </select>
                                @else
                                    <select name="class_id" id="filter_class_id" class="form-control font-weight-bold" required>
                                        <option value="" selected disabled>-- Pilih Kelas --</option>
                                        @include('partials.select_class_options', ['classList' => $classList])
                                    </select>
                                @endif
                            </div>

                            <!-- Dropdown 2: Periode / Semester -->
                            <div class="col-md-4 col-sm-6 col-12 mb-2">
                                <label for="filter_fst_id" class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-calendar-alt text-warning mr-1"></i> 2. Periode / Semester
                                </label>
                                <select name="fst_id" id="filter_fst_id" class="form-control font-weight-bold" required>
                                    <option value="" selected disabled>-- Pilih Periode Semester --</option>
                                    @include('partials.select_fst_options', ['fstList' => $fstList])
                                </select>
                            </div>

                            <!-- Dropdown 3: Mata Pelajaran -->
                            <div class="col-md-4 col-12 mb-2">
                                <label for="filter_mapel_id" class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-book text-success mr-1"></i> 3. Mata Pelajaran
                                </label>
                                <select name="mapel_id" id="filter_mapel_id" class="form-control font-weight-bold" required disabled>
                                    <option value="" selected disabled>Pilih Kelas & Periode Terlebih Dahulu...</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Empty State Awal -->
            <div id="initialPlaceholder" class="card shadow-sm border-0 py-5 text-center">
                <div class="card-body">
                    <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                    <h5 class="font-weight-bold text-secondary">Silakan Pilih Kelas, Periode, dan Mata Pelajaran</h5>
                    <p class="text-muted small mb-0">Pilih filter di atas untuk menampilkan lembar spreadsheet nilai siswa.</p>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingGridPlaceholder" class="card shadow-sm border-0 py-5 text-center" style="display: none;">
                <div class="card-body">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                    <h5 class="font-weight-bold text-dark">Memuat Data Nilai Siswa...</h5>
                    <p class="text-muted small mb-0">Menyiapkan daftar siswa dan data penilaian sumatif.</p>
                </div>
            </div>

            <!-- Panel Matriks Spreadsheet Nilai Sumatif -->
            <div class="card card-outline card-secondary shadow-sm mb-5" id="gridCard" style="display: none;">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                        <!-- Left: Badges Konteks -->
                        <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                            <span class="badge badge-light border text-dark px-2 py-2">
                                <i class="fas fa-school text-primary mr-1"></i> <span id="badgeClassText">-</span>
                            </span>
                            <span class="badge badge-light border text-dark px-2 py-2">
                                <i class="fas fa-book text-success mr-1"></i> <span id="badgeMapelText">-</span>
                            </span>
                            <span class="badge badge-light border text-dark px-2 py-2">
                                <i class="fas fa-calendar-alt text-warning mr-1"></i> <span id="badgeFstText">-</span>
                            </span>
                            <span class="badge badge-info px-2 py-2">
                                <i class="fas fa-user-graduate mr-1"></i> <span id="badgeTotalStudent">0</span> Siswa
                            </span>
                        </div>

                        <!-- Right: Actions Buttons -->
                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                            <button type="button" class="btn btn-outline-info btn-sm font-weight-bold" id="upCsvBtn" title="Upload format Excel">
                                <i class="fas fa-file-excel mr-1"></i> Import Excel
                            </button>
                            @if (\App\Models\Setting::isModuleEnabled('cbt_sync', true))
                                <button type="button" class="btn btn-outline-warning text-dark btn-sm font-weight-bold" id="cbtSyncBtn" title="Tarik nilai hasil ujian CBT">
                                    <i class="fas fa-sync-alt mr-1"></i> Tarik CBT
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold" id="cbtLogsBtn" title="Lihat riwayat sinkronisasi CBT">
                                    <i class="fas fa-history mr-1"></i> Log CBT
                                </button>
                            @endif
                            <button type="button" class="btn btn-success btn-sm font-weight-bold btn-save-main px-3" id="saveTopBtn">
                                <i class="fas fa-save mr-1"></i> Simpan Semua
                            </button>
                        </div>
                    </div>

                    <!-- Quick Stats Widget & Controls Bar -->
                    <div class="row align-items-center mt-3 pt-3 border-top">
                        <!-- Stats Summary Pills -->
                        <div class="col-lg-6 col-12 mb-2 mb-lg-0">
                            <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                <span class="badge badge-light border text-secondary px-2 py-1">
                                    Rata-rata Kelas: <strong class="text-dark ml-1" id="statAvg">-</strong>
                                </span>
                                <span class="badge badge-light border text-secondary px-2 py-1">
                                    Tertinggi: <strong class="text-success ml-1" id="statMax">-</strong>
                                </span>
                                <span class="badge badge-light border text-secondary px-2 py-1">
                                    Terendah: <strong class="text-danger ml-1" id="statMin">-</strong>
                                </span>
                                <span class="badge badge-light border text-secondary px-2 py-1">
                                    Tuntas (≥75): <strong class="text-primary ml-1" id="statTuntas">-</strong>
                                </span>
                            </div>
                        </div>

                        <!-- Search Bar -->
                        <div class="col-lg-6 col-12 d-flex justify-content-lg-end align-items-center">
                            <div class="input-group input-group-sm" style="max-width: 260px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="studentSearchInput" class="form-control border-left-0" placeholder="Cari nama siswa...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Touch Scroll Hint for Mobile Devices -->
                <div class="d-md-none bg-light px-3 py-2 border-bottom text-muted small d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-arrows-alt-h text-primary mr-1"></i> Geser ke samping untuk melihat kolom nilai lainnya</span>
                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-hand-point-right mr-1"></i> Swipe</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive sumatif-table-wrapper mb-0">
                        <table class="table sumatif-table" id="sumatifTable">
                            <thead>
                                <tr>
                                    <th class="col-sticky-no">No</th>
                                    <th class="col-sticky-name">Nama Siswa</th>
                                    <!-- Sumatif 1 to 10 Langsung Tampil Semua -->
                                    <th class="col-sumatif col-s1">S 1</th>
                                    <th class="col-sumatif col-s2">S 2</th>
                                    <th class="col-sumatif col-s3">S 3</th>
                                    <th class="col-sumatif col-s4">S 4</th>
                                    <th class="col-sumatif col-s5">S 5</th>
                                    <th class="col-sumatif col-s6">S 6</th>
                                    <th class="col-sumatif col-s7">S 7</th>
                                    <th class="col-sumatif col-s8">S 8</th>
                                    <th class="col-sumatif col-s9">S 9</th>
                                    <th class="col-sumatif col-s10">S 10</th>
                                    <!-- STS & SAS -->
                                    <th class="col-sts">
                                        <span class="badge badge-warning text-dark px-2 py-1">STS</span>
                                    </th>
                                    <th class="col-sas">
                                        <span class="badge badge-info text-white px-2 py-1">SAS</span>
                                    </th>
                                    <!-- Nilai Akhir -->
                                    <th class="col-na">
                                        <span class="badge badge-success px-2 py-1">Nilai Akhir</span>
                                    </th>
                                    <!-- Aksi Bersihkan -->
                                    <th style="width: 50px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="sumatifTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light py-2 text-muted small d-flex justify-content-between align-items-center">
                    <div>
                        <span id="studentCountText">Menampilkan 0 siswa</span>
                        <span class="ml-3 d-none d-md-inline text-muted"><i class="fas fa-keyboard text-secondary mr-1"></i> Tekan <b>Enter</b>: baris bawah | <b>Tab</b>: kolom kanan | <b>Ctrl+S</b>: simpan</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold btn-save-main">
                            <i class="fas fa-save mr-1"></i> Simpan Semua Nilai
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- /.container-fluid -->
    </div><!-- /.content -->

    <!-- Floating Action Save Bar (Sticky di Bawah Saat Ada Perubahan) -->
    <div id="stickySaveBar" class="sticky-floating-bar">
        <div class="d-flex justify-content-between align-items-center text-white">
            <div class="d-flex align-items-center mr-2">
                <i class="fas fa-edit text-warning fa-lg mr-2 mr-sm-3"></i>
                <div>
                    <div class="font-weight-bold text-white small" style="letter-spacing: 0.02em;">LEMBAR ASESMEN SUMATIF</div>
                    <div class="text-light text-xs" style="opacity: 0.85;">
                        <span id="unsavedCountText">Ada nilai yang belum disimpan</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center flex-shrink-0">
                <button type="button" class="btn btn-sm btn-outline-light mr-2 font-weight-bold" id="btnDiscardChanges">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
                <button type="button" class="btn btn-sm btn-success font-weight-bold px-2 px-sm-3 btn-save-main" id="btnFloatingSave">
                    <i class="fas fa-save mr-1"></i> Simpan Nilai
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Upload Excel Nilai Siswa -->
    <div class="modal fade" id="upCsvModal" tabindex="-1" role="dialog" aria-labelledby="upCsvModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white py-2">
                    <h5 class="modal-title font-weight-bold" id="upCsvModalLabel">
                        <i class="fas fa-file-excel mr-1"></i> Upload Excel Nilai Siswa
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="csvForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="alert alert-info py-2 px-3 small mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Format berkas menggunakan <strong>Microsoft Excel (.xlsx)</strong> dengan urutan kolom Sumatif 1 s.d 10, Nilai STS, dan Nilai SAS. Kolom yang belum dinilai biarkan kosong.
                        </div>
                        <div class="alert alert-light border py-2 px-3 small mb-3">
                            <strong><i class="fas fa-chalkboard-teacher text-primary mr-1"></i> Target Kelas Aktif:</strong>
                            <span class="font-weight-bold text-success" id="modalTargetClassBadge">-</span>
                        </div>
                        <h6 class="font-weight-bold mb-1">Unduh Template Excel:</h6>
                        <a href="javascript:void(0)" id="btnDownloadExcelTemplate" class="btn btn-outline-success btn-block mb-3 font-weight-bold">
                            <i class="fas fa-download mr-1"></i> Download Template Excel Kelas Ini
                        </a>

                        <div class="form-group mb-2">
                            <label for="csv" class="font-weight-bold">Pilih Berkas Excel (.xlsx / .xls):</label>
                            <input type="file" class="form-control-file border p-2 rounded w-100" id="csv" name="csv" accept=".xlsx, .xls, .csv" required>
                            <small class="form-text text-muted">Maksimal ukuran file 10 MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success font-weight-bold">
                            <i class="fas fa-upload mr-1"></i> Unggah & Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (\App\Models\Setting::isModuleEnabled('cbt_sync', true))
    <!-- Modal Tarik Nilai CBT -->
    <div class="modal fade" id="cbtSyncModal" tabindex="-1" role="dialog" aria-labelledby="cbtSyncModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-warning text-dark py-2">
                    <h5 class="modal-title font-weight-bold" id="cbtSyncModalLabel">
                        <i class="fas fa-sync-alt mr-2"></i>Tarik Nilai dari CBT (Computer Based Test)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="cbtSyncForm">
                    <div class="modal-body">
                        <!-- Status Server CBT Box -->
                        <div class="card card-outline card-secondary mb-3">
                            <div class="card-body p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <span class="text-muted d-block small">Endpoint CBT Server:</span>
                                        <code class="font-weight-bold text-dark" id="cbtEndpointDisplay">{{ config('services.cbt.url', env('CBT_API_URL', 'http://localhost:8001/api/v1')) }}</code>
                                    </div>
                                    <div class="mt-2 mt-sm-0 d-flex align-items-center">
                                        <span id="cbtStatusBadge" class="badge badge-secondary px-2 py-1 mr-2">
                                            <i class="fas fa-question-circle mr-1"></i>Belum dicek
                                        </span>
                                        <button type="button" class="btn btn-xs btn-outline-primary" id="btnTestCbtConnection">
                                            <i class="fas fa-plug mr-1"></i>Tes Koneksi
                                        </button>
                                    </div>
                                </div>
                                <div id="cbtConnectionMsg" class="small mt-1 text-muted d-none"></div>
                            </div>
                        </div>

                        <!-- Target Data Summary -->
                        <div class="alert alert-info py-2 px-3 mb-3">
                            <div class="row">
                                <div class="col-sm-4">
                                    <strong>Kelas:</strong> <span id="cbtModalClass">-</span>
                                </div>
                                <div class="col-sm-4">
                                    <strong>Mapel:</strong> <span id="cbtModalMapel">-</span>
                                </div>
                                <div class="col-sm-4">
                                    <strong>Fase/Semester:</strong> <span id="cbtModalFst">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Options -->
                        <div class="form-group">
                            <label for="cbt_target_field" class="font-weight-bold">
                                <i class="fas fa-bullseye mr-1 text-primary"></i>Simpan ke Kolom Nilai Rapor:
                            </label>
                            <select name="target_field" id="cbt_target_field" class="form-control font-weight-bold">
                                <optgroup label="Asesmen Sumatif Kurikulum Merdeka">
                                    <option value="value_sts" selected>Sumatif Tengah Semester (STS)</option>
                                    <option value="value_sas">Sumatif Akhir Semester (SAS)</option>
                                </optgroup>
                                <optgroup label="Asesmen Formatif / Nilai Harian (UH)">
                                    <option value="value_daily">Nilai Harian 1 (Sumatif 1)</option>
                                    <option value="value_daily_2">Nilai Harian 2 (Sumatif 2)</option>
                                    <option value="value_daily_3">Nilai Harian 3 (Sumatif 3)</option>
                                    <option value="value_daily_4">Nilai Harian 4 (Sumatif 4)</option>
                                    <option value="value_daily_5">Nilai Harian 5 (Sumatif 5)</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold d-block">
                                <i class="fas fa-cog mr-1 text-primary"></i>Opsi Pengisian:
                            </label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="overwrite_true" name="overwrite" value="1" class="custom-control-input" checked>
                                <label class="custom-control-label" for="overwrite_true">Timpa nilai yang sudah ada (Overwrite)</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="overwrite_false" name="overwrite" value="0" class="custom-control-input">
                                <label class="custom-control-label" for="overwrite_false">Hanya isi nilai yang masih kosong</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning font-weight-bold" id="btnSubmitCbtSync">
                            <i class="fas fa-cloud-download-alt mr-1"></i>Mulai Tarik Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Log CBT -->
    <div class="modal fade" id="cbtLogsModal" tabindex="-1" role="dialog" aria-labelledby="cbtLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white py-2">
                    <h5 class="modal-title font-weight-bold" id="cbtLogsModalLabel">
                        <i class="fas fa-history mr-2"></i>Riwayat Sinkronisasi Nilai CBT
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped" id="cbtLogsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Tipe</th>
                                    <th>Target</th>
                                    <th>Berhasil</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Memuat riwayat sinkronisasi...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
    <script type="module">
    $(document).ready(function() {
        // ── State Variables ───────────────────────────────────────────
        let class_id = {{ Auth::user()->class_id ? Auth::user()->class_id : 'null' }};
        let class_name = "{{ $className ?? '' }}";
        let fst_id = null;
        let fst_name = '';
        let mapel_id = null;
        let mapel_name = '';

        let currentStudents = [];
        let isDirty = false;

        // Auto-detect class if already selected (for wali kelas)
        if (class_id) {
            class_name = $('#filter_class_id option:selected').text();
        }

        // ── Filter Triggers ───────────────────────────────────────────
        $('#filter_class_id').on('change', function() {
            class_id = $(this).val();
            class_name = $('#filter_class_id option:selected').text();
            resetMapelDropdown();
            fetchMapels();
        });

        $('#filter_fst_id').on('change', function() {
            fst_id = $(this).val();
            fst_name = $('#filter_fst_id option:selected').text();
            resetMapelDropdown();
            fetchMapels();
        });

        $('#filter_mapel_id').on('change', function() {
            mapel_id = $(this).val();
            mapel_name = $('#filter_mapel_id option:selected').text();
            if (class_id && fst_id && mapel_id) {
                loadSumatifData();
            }
        });

        function resetMapelDropdown() {
            $('#filter_mapel_id').html('<option value="" selected disabled>Loading Mata Pelajaran...</option>').prop('disabled', true);
            $('#gridCard').hide();
            $('#stickySaveBar').hide();
            $('#initialPlaceholder').show();
            mapel_id = null;
            isDirty = false;
        }

        function fetchMapels() {
            let cVal = class_id || $('#filter_class_id').val();
            let fVal = fst_id || $('#filter_fst_id').val();

            if (!cVal || !fVal) {
                $('#filter_mapel_id').html('<option value="" selected disabled>Pilih Kelas & Periode Terlebih Dahulu...</option>').prop('disabled', true);
                return;
            }

            $.ajax({
                url: "{{ route('value.getMapel') }}",
                type: "GET",
                data: { class_id: cVal, fst_id: fVal },
                beforeSend: function() {
                    $('#filter_mapel_id').html('<option value="" selected disabled>Memuat mapel...</option>').prop('disabled', true);
                },
                success: function(response) {
                    let html = '<option value="" selected disabled>-- Pilih Mata Pelajaran --</option>';
                    if (!response || response.length === 0) {
                        html = '<option value="" selected disabled>Belum ada mapel aktif untuk kelas & periode ini.</option>';
                        $('#filter_mapel_id').html(html).prop('disabled', true);
                    } else {
                        response.forEach(function(item) {
                            html += `<option value="${item.id}">${item.nama_mapel}</option>`;
                        });
                        $('#filter_mapel_id').html(html).prop('disabled', false);
                    }
                },
                error: function() {
                    $('#filter_mapel_id').html('<option value="" selected disabled>Gagal mengambil data mapel.</option>').prop('disabled', true);
                }
            });
        }

        // ── Load Sumatif Data via AJAX ─────────────────────────────────
        function loadSumatifData() {
            if (!class_id || !fst_id || !mapel_id) return;

            $('#badgeClassText').text(class_name);
            $('#badgeFstText').text(fst_name);
            $('#badgeMapelText').text(mapel_name);

            $('#initialPlaceholder').hide();
            $('#gridCard').hide();
            $('#loadingGridPlaceholder').show();
            $('#stickySaveBar').hide();
            isDirty = false;

            $.ajax({
                url: "{{ route('value.getByClass') }}",
                type: "GET",
                data: {
                    class_id: class_id,
                    mapel_id: mapel_id,
                    fst_id: fst_id
                },
                success: function(res) {
                    $('#loadingGridPlaceholder').hide();
                    currentStudents = res.data || [];

                    $('#badgeTotalStudent').text(currentStudents.length);
                    $('#studentCountText').text(`Total ${currentStudents.length} siswa`);

                    renderTable(currentStudents);
                    recalculateStats();
                    $('#gridCard').fadeIn(200);
                },
                error: function() {
                    $('#loadingGridPlaceholder').hide();
                    $('#initialPlaceholder').show();
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat memuat data penilaian sumatif.', 'error');
                }
            });
        }

        // ── Render HTML Table ──────────────────────────────────────────
        function renderTable(students) {
            let html = '';
            students.forEach(function(student, idx) {
                let s1 = student.value_daily !== null ? Math.round(student.value_daily) : '';
                let s2 = student.value_daily_2 !== null ? Math.round(student.value_daily_2) : '';
                let s3 = student.value_daily_3 !== null ? Math.round(student.value_daily_3) : '';
                let s4 = student.value_daily_4 !== null ? Math.round(student.value_daily_4) : '';
                let s5 = student.value_daily_5 !== null ? Math.round(student.value_daily_5) : '';
                let s6 = student.value_daily_6 !== null ? Math.round(student.value_daily_6) : '';
                let s7 = student.value_daily_7 !== null ? Math.round(student.value_daily_7) : '';
                let s8 = student.value_daily_8 !== null ? Math.round(student.value_daily_8) : '';
                let s9 = student.value_daily_9 !== null ? Math.round(student.value_daily_9) : '';
                let s10 = student.value_daily_10 !== null ? Math.round(student.value_daily_10) : '';
                let sts = student.value_sts !== null ? Math.round(student.value_sts) : '';
                let sas = student.value_sas !== null ? Math.round(student.value_sas) : '';

                html += `
                    <tr class="student-row" data-studentid="${student.student_id}">
                        <td class="col-sticky-no font-weight-bold text-muted">${idx + 1}</td>
                        <td class="col-sticky-name">
                            <div class="font-weight-bold text-dark student-name-text">${student.student_name}</div>
                            <small class="text-muted"><i class="fas fa-id-card-alt mr-1"></i>ID: ${student.student_id}</small>
                        </td>
                        <td class="col-sumatif col-s1">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily" value="${s1}">
                        </td>
                        <td class="col-sumatif col-s2">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily_2" value="${s2}">
                        </td>
                        <td class="col-sumatif col-s3">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily_3" value="${s3}">
                        </td>
                        <td class="col-sumatif col-s4">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily_4" value="${s4}">
                        </td>
                        <td class="col-sumatif col-s5">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily_5" value="${s5}">
                        </td>
                        <td class="col-sumatif col-s6">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily_6" value="${s6}">
                        </td>
                        <td class="col-sumatif col-s7">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily_7" value="${s7}">
                        </td>
                        <td class="col-sumatif col-s8">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily_8" value="${s8}">
                        </td>
                        <td class="col-sumatif col-s9">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily_9" value="${s9}">
                        </td>
                        <td class="col-sumatif col-s10">
                            <input type="number" min="0" max="100" class="cell-score-input" data-field="value_daily_10" value="${s10}">
                        </td>
                        <td class="col-sts">
                            <input type="number" min="0" max="100" class="cell-score-input border-sts font-weight-bold" data-field="value_sts" value="${sts}">
                        </td>
                        <td class="col-sas">
                            <input type="number" min="0" max="100" class="cell-score-input border-sas font-weight-bold" data-field="value_sas" value="${sas}">
                        </td>
                        <td class="col-na">
                            <span class="na-badge badge-secondary">-</span>
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-outline-danger btn-xs btn-clear-row" title="Kosongkan nilai siswa ini">
                                <i class="fas fa-eraser"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            $('#sumatifTableBody').html(html);

            // Calculate each row's Nilai Akhir
            $('#sumatifTableBody tr.student-row').each(function() {
                updateRowAverage($(this));
            });
        }

        // ── Calculate Nilai Akhir for a single row ─────────────────────
        function updateRowAverage(tr) {
            let dailyScores = [];
            for (let i = 1; i <= 10; i++) {
                let field = i === 1 ? 'value_daily' : `value_daily_${i}`;
                let val = tr.find(`input[data-field="${field}"]`).val();
                if (val !== '' && val !== null && !isNaN(val)) {
                    dailyScores.push(parseFloat(val));
                }
            }

            let stsVal = tr.find('input[data-field="value_sts"]').val();
            let sasVal = tr.find('input[data-field="value_sas"]').val();
            let examScores = [];
            if (stsVal !== '' && stsVal !== null && !isNaN(stsVal)) examScores.push(parseFloat(stsVal));
            if (sasVal !== '' && sasVal !== null && !isNaN(sasVal)) examScores.push(parseFloat(sasVal));

            let naBadge = tr.find('.na-badge');

            if (dailyScores.length === 0 && examScores.length === 0) {
                naBadge.text('-').removeClass('badge-success badge-warning badge-danger').addClass('badge-secondary');
                return null;
            }

            let avgDaily = dailyScores.length > 0 ? (dailyScores.reduce((a, b) => a + b, 0) / dailyScores.length) : null;
            let avgExam = examScores.length > 0 ? (examScores.reduce((a, b) => a + b, 0) / examScores.length) : null;

            let finalVal = 0;
            if (avgDaily !== null && avgExam !== null) {
                finalVal = (avgDaily + avgExam) / 2;
            } else if (avgDaily !== null) {
                finalVal = avgDaily;
            } else if (avgExam !== null) {
                finalVal = avgExam;
            }

            let rounded = Math.round(finalVal);
            naBadge.text(rounded);

            if (rounded >= 75) {
                naBadge.removeClass('badge-secondary badge-warning badge-danger').addClass('badge-success');
            } else if (rounded >= 60) {
                naBadge.removeClass('badge-secondary badge-success badge-danger').addClass('badge-warning');
            } else {
                naBadge.removeClass('badge-secondary badge-success badge-warning').addClass('badge-danger');
            }

            return rounded;
        }

        // ── Recalculate Class Statistics ──────────────────────────────
        function recalculateStats() {
            let allScores = [];
            $('#sumatifTableBody tr.student-row').each(function() {
                let na = $(this).find('.na-badge').text();
                if (na !== '-' && !isNaN(na)) {
                    allScores.push(parseInt(na));
                }
            });

            if (allScores.length === 0) {
                $('#statAvg').text('-');
                $('#statMax').text('-');
                $('#statMin').text('-');
                $('#statTuntas').text('-');
                return;
            }

            let sum = allScores.reduce((a, b) => a + b, 0);
            let avg = (sum / allScores.length).toFixed(1);
            let max = Math.max(...allScores);
            let min = Math.min(...allScores);
            let tuntasCount = allScores.filter(s => s >= 75).length;
            let tuntasPct = Math.round((tuntasCount / allScores.length) * 100);

            $('#statAvg').text(avg);
            $('#statMax').text(max);
            $('#statMin').text(min);
            $('#statTuntas').text(`${tuntasCount}/${allScores.length} (${tuntasPct}%)`);
        }

        // ── Input Changes & Dirty State ────────────────────────────────
        $('#sumatifTableBody').on('input', '.cell-score-input', function() {
            let val = $(this).val();
            if (val !== '') {
                let num = parseFloat(val);
                if (num > 100) $(this).val(100);
                if (num < 0) $(this).val(0);
            }

            $(this).addClass('is-changed');
            updateRowAverage($(this).closest('tr'));
            recalculateStats();

            if (!isDirty) {
                isDirty = true;
                $('#stickySaveBar').fadeIn(200);
            }
        });

        // ── Keyboard Navigation (Enter = Next Row, Tab = Next Col) ─────
        $('#sumatifTableBody').on('keydown', '.cell-score-input', function(e) {
            let currentInput = $(this);
            let currentTd = currentInput.closest('td');
            let currentTr = currentInput.closest('tr');
            let field = currentInput.data('field');

            if (e.key === 'Enter') {
                e.preventDefault();
                let nextTr = e.shiftKey ? currentTr.prev('tr.student-row') : currentTr.next('tr.student-row');
                if (nextTr.length) {
                    let targetInput = nextTr.find(`input[data-field="${field}"]`);
                    targetInput.focus().select();
                }
            } else if (e.key === 'ArrowDown') {
                let nextTr = currentTr.next('tr.student-row');
                if (nextTr.length) {
                    nextTr.find(`input[data-field="${field}"]`).focus().select();
                }
            } else if (e.key === 'ArrowUp') {
                let prevTr = currentTr.prev('tr.student-row');
                if (prevTr.length) {
                    prevTr.find(`input[data-field="${field}"]`).focus().select();
                }
            }
        });

        // ── Excel Copy-Paste Support (10 Sumatif + STS + SAS) ───────────
        $('#sumatifTableBody').on('paste', '.cell-score-input', function(e) {
            e.preventDefault();
            let clipboardData = (e.originalEvent || e).clipboardData.getData('text/plain');
            if (!clipboardData) return;

            let rows = clipboardData.split(/\r\n|\n|\r/);
            let startTd = $(this).closest('td');
            let startTr = $(this).closest('tr');

            let allRows = $('#sumatifTableBody tr.student-row');
            let startRowIdx = allRows.index(startTr);

            // Daftar 12 field berurutan persis seperti susunan kolom tabel:
            // S1 s.d S10, lalu STS, lalu SAS
            let visibleFields = [
                'value_daily', 'value_daily_2', 'value_daily_3', 'value_daily_4', 'value_daily_5',
                'value_daily_6', 'value_daily_7', 'value_daily_8', 'value_daily_9', 'value_daily_10',
                'value_sts', 'value_sas'
            ];

            let currentField = $(this).attr('data-field') || $(this).data('field');
            let startColIdx = visibleFields.indexOf(currentField);
            if (startColIdx === -1) startColIdx = 0;

            let anyPasted = false;
            let pastedRowCount = 0;

            for (let r = 0; r < rows.length; r++) {
                let rowStr = rows[r];
                if (rowStr === undefined || (rowStr.trim() === '' && r === rows.length - 1)) continue;

                let targetTr = allRows.eq(startRowIdx + r);
                if (!targetTr.length) break;

                let cols = rowStr.split(/\t/);
                for (let c = 0; c < cols.length; c++) {
                    let fieldIdx = startColIdx + c;
                    if (fieldIdx >= visibleFields.length) break;

                    let targetField = visibleFields[fieldIdx];
                    let rawVal = cols[c] !== undefined ? cols[c].trim().replace(',', '.') : '';

                    let input = targetTr.find(`input[data-field="${targetField}"]`);
                    if (input.length) {
                        if (!isNaN(rawVal) && rawVal !== '') {
                            let num = Math.min(100, Math.max(0, parseFloat(rawVal)));
                            input.val(num).addClass('is-changed');
                            anyPasted = true;
                        } else if (rawVal === '-' || rawVal === '') {
                            input.val('').addClass('is-changed');
                            anyPasted = true;
                        }
                    }
                }

                updateRowAverage(targetTr);
                pastedRowCount++;
            }

            if (anyPasted) {
                recalculateStats();
                if (!isDirty) {
                    isDirty = true;
                    $('#stickySaveBar').fadeIn(200);
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `Berhasil paste ${pastedRowCount} baris nilai dari Excel!`,
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });

        // ── Clear Row Button ───────────────────────────────────────────
        $('#sumatifTableBody').on('click', '.btn-clear-row', function() {
            let tr = $(this).closest('tr');
            let studentName = tr.find('.student-name-text').text();

            Swal.fire({
                title: 'Kosongkan Nilai?',
                text: `Apakah Anda yakin ingin menghapus seluruh isian nilai untuk ${studentName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kosongkan'
            }).then((res) => {
                if (res.isConfirmed) {
                    tr.find('.cell-score-input').val('').addClass('is-changed');
                    updateRowAverage(tr);
                    recalculateStats();
                    if (!isDirty) {
                        isDirty = true;
                        $('#stickySaveBar').fadeIn(200);
                    }
                }
            });
        });

        // ── Search Siswa Real-time ─────────────────────────────────────
        $('#studentSearchInput').on('keyup', function() {
            let query = $(this).val().toLowerCase().trim();
            let count = 0;
            $('#sumatifTableBody tr.student-row').each(function() {
                let name = $(this).find('.student-name-text').text().toLowerCase();
                if (name.includes(query)) {
                    $(this).show();
                    count++;
                } else {
                    $(this).hide();
                }
            });
            $('#studentCountText').text(`Menampilkan ${count} siswa`);
        });

        // ── Reset / Discard Changes ────────────────────────────────────
        $('#btnDiscardChanges').on('click', function() {
            Swal.fire({
                title: 'Batalkan Perubahan?',
                text: 'Semua nilai yang baru saja Anda ketik dan belum disimpan akan dikembalikan ke data awal.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Batalkan'
            }).then((res) => {
                if (res.isConfirmed) {
                    renderTable(currentStudents);
                    recalculateStats();
                    isDirty = false;
                    $('#stickySaveBar').fadeOut(200);
                }
            });
        });

        // ── Save All Bulk Action ───────────────────────────────────────
        function saveAllScores() {
            if (!class_id || !fst_id || !mapel_id) return;

            let studentsPayload = [];
            $('#sumatifTableBody tr.student-row').each(function() {
                let tr = $(this);
                let studentId = tr.data('studentid');

                let rowData = {
                    student_id: studentId,
                    value_daily: tr.find('input[data-field="value_daily"]').val() || null,
                    value_daily_2: tr.find('input[data-field="value_daily_2"]').val() || null,
                    value_daily_3: tr.find('input[data-field="value_daily_3"]').val() || null,
                    value_daily_4: tr.find('input[data-field="value_daily_4"]').val() || null,
                    value_daily_5: tr.find('input[data-field="value_daily_5"]').val() || null,
                    value_daily_6: tr.find('input[data-field="value_daily_6"]').val() || null,
                    value_daily_7: tr.find('input[data-field="value_daily_7"]').val() || null,
                    value_daily_8: tr.find('input[data-field="value_daily_8"]').val() || null,
                    value_daily_9: tr.find('input[data-field="value_daily_9"]').val() || null,
                    value_daily_10: tr.find('input[data-field="value_daily_10"]').val() || null,
                    value_sts: tr.find('input[data-field="value_sts"]').val() || null,
                    value_sas: tr.find('input[data-field="value_sas"]').val() || null,
                };
                studentsPayload.push(rowData);
            });

            $('.btn-save-main').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: "{{ route('value.storeBulk') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: {
                    class_id: class_id,
                    mapel_id: mapel_id,
                    fst_id: fst_id,
                    students: studentsPayload
                },
                success: function(response) {
                    $('.btn-save-main').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Semua Nilai');
                    isDirty = false;
                    $('.cell-score-input').removeClass('is-changed');
                    $('#stickySaveBar').fadeOut(200);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disimpan!',
                        text: response.message || 'Semua nilai asesmen sumatif berhasil disimpan.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    $('.btn-save-main').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Semua Nilai');
                    let errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data!';
                    Swal.fire('Gagal Menyimpan!', errMsg, 'error');
                }
            });
        }

        $('.btn-save-main').on('click', function() {
            saveAllScores();
        });

        // ── Keyboard Shortcut: Ctrl + S ────────────────────────────────
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                saveAllScores();
            }
        });

        // ── Import Excel Modal ─────────────────────────────────────────
        $('#upCsvBtn').click(function() {
            $('#csv').val(null);
            let currentClassText = class_name || $('#filter_class_id option:selected').text() || '{{ $className ?? "" }}';
            if (!currentClassText || currentClassText.includes('Pilih')) {
                currentClassText = '{{ $className ?? "Belum Memilih Kelas" }}';
            }
            $('#modalTargetClassBadge').text(currentClassText);
            $('#upCsvModal').modal('show');
        });

        $('#btnDownloadExcelTemplate').click(function(e) {
            e.preventDefault();
            let currentClass = class_id || $('#filter_class_id').val() || '{{ Auth::user()->class_id ?? "" }}';
            let currentMapel = mapel_id || $('#filter_mapel_id').val() || '';
            let currentFst   = fst_id   || $('#filter_fst_id').val() || '';

            let url = `{{ route('value.download') }}?class_id=${currentClass}&mapel_id=${currentMapel}&fst_id=${currentFst}`;
            window.location.href = url;
        });

        $('#csvForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let currentClass = class_id || $('#filter_class_id').val() || '{{ Auth::user()->class_id ?? "" }}';
            let currentMapel = mapel_id || $('#filter_mapel_id').val() || '';
            let currentFst   = fst_id   || $('#filter_fst_id').val() || '';

            if (currentClass) formData.append('class_id', currentClass);
            if (currentMapel) formData.append('mapel_id', currentMapel);
            if (currentFst) formData.append('fst_id', currentFst);

            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Excel...');

            $.ajax({
                url: "{{ route('value.import') }}",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);
                    $('#upCsvModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    loadSumatifData();
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || "Terjadi kesalahan saat memproses berkas Excel!",
                    });
                }
            });
        });

        @if (\App\Models\Setting::isModuleEnabled('cbt_sync', true))
        // ── CBT Sync Modal ─────────────────────────────────────────────
        function openCbtModal() {
            if (!class_id || !mapel_id || !fst_id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Silahkan pilih Kelas, Periode, dan Mata Pelajaran terlebih dahulu.'
                });
                return;
            }

            $('#cbtModalClass').text(class_name);
            $('#cbtModalMapel').text(mapel_name);
            $('#cbtModalFst').text(fst_name);
            $('#cbtSyncModal').modal('show');
        }

        $('#cbtSyncBtn').click(function() {
            openCbtModal();
        });

        $('#btnTestCbtConnection').click(function() {
            let btn = $(this);
            let badge = $('#cbtStatusBadge');
            let msg = $('#cbtConnectionMsg');

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menghubungkan...');
            badge.removeClass('badge-success badge-danger badge-secondary')
                 .addClass('badge-info').html('<i class="fas fa-spinner fa-spin mr-1"></i>Checking...');
            msg.addClass('d-none');

            $.ajax({
                url: "{{ route('value.cbtTest') }}",
                type: "GET",
                timeout: 8000,
                success: function(res) {
                    badge.removeClass('badge-info badge-danger badge-secondary')
                         .addClass('badge-success')
                         .html(`<i class="fas fa-check-circle mr-1"></i>Online (${res.latency_ms} ms)`);
                    msg.removeClass('d-none text-danger').addClass('text-success').text(res.message);
                },
                error: function(xhr) {
                    badge.removeClass('badge-info badge-success badge-secondary')
                         .addClass('badge-danger')
                         .html('<i class="fas fa-times-circle mr-1"></i>Offline / Gagal');
                    let errMsg = xhr.responseJSON?.message || 'Gagal menghubungi server CBT';
                    msg.removeClass('d-none text-success').addClass('text-danger').text(errMsg);
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-plug mr-1"></i>Tes Koneksi');
                }
            });
        });

        $('#cbtSyncForm').submit(function(e) {
            e.preventDefault();

            let targetField = $('#cbt_target_field').val();
            let targetText = $('#cbt_target_field option:selected').text();
            let overwriteVal = $('input[name="overwrite"]:checked').val();

            Swal.fire({
                title: 'Tarik Nilai dari CBT?',
                html: `Sistem akan mengambil nilai ujian dari server CBT untuk:<br>
                       <strong>Kelas:</strong> ${class_name}<br>
                       <strong>Mata Pelajaran:</strong> ${mapel_name}<br>
                       <strong>Target Kolom:</strong> <span class="badge badge-primary">${targetText}</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-cloud-download-alt"></i> Ya, Tarik Nilai',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang Menarik Nilai...',
                        html: 'Menghubungkan ke CBT dan memproses pencocokan NISN siswa.<br>Mohon tunggu sebentar...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('value.cbtSync') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        data: {
                            class_id: class_id,
                            mapel_id: mapel_id,
                            fst_id: fst_id,
                            target_field: targetField,
                            overwrite: overwriteVal
                        },
                        success: function(response) {
                            $('#cbtSyncModal').modal('hide');

                            let detailHtml = `<p class="mb-2 font-weight-bold text-success">${response.message}</p>`;
                            if (response.total_skipped > 0) {
                                detailHtml += `<p class="mb-1 text-muted small"><i class="fas fa-info-circle mr-1"></i>${response.total_skipped} siswa dilewati karena sudah memiliki nilai.</p>`;
                            }
                            if (response.total_unmatched > 0) {
                                let unmatchedList = response.unmatched.map(u => u.name).slice(0, 5).join(', ');
                                if (response.unmatched.length > 5) unmatchedList += '...';
                                detailHtml += `<p class="mb-1 text-warning small"><i class="fas fa-exclamation-triangle mr-1"></i>${response.total_unmatched} siswa CBT tidak cocok (${unmatchedList}).</p>`;
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Sinkronisasi Selesai!',
                                html: detailHtml,
                                confirmButtonText: 'OK'
                            });

                            loadSumatifData();
                        },
                        error: function(xhr) {
                            let errTitle = 'Gagal Sinkronisasi';
                            let errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menghubungi CBT.';

                            Swal.fire({
                                icon: 'error',
                                title: errTitle,
                                text: errMsg,
                                confirmButtonText: 'Tutup'
                            });
                        }
                    });
                }
            });
        });

        // ── CBT Logs Handler ───────────────────────────────────────────
        function loadCbtLogs() {
            let tbody = $('#cbtLogsTable tbody');
            tbody.html('<tr><td colspan="6" class="text-center text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat riwayat...</td></tr>');
            $('#cbtLogsModal').modal('show');

            $.ajax({
                url: "{{ route('value.cbtLogs') }}",
                type: "GET",
                data: {
                    class_id: class_id,
                    mapel_id: mapel_id,
                    fst_id: fst_id
                },
                success: function(res) {
                    let rows = res.data || [];
                    if (rows.length === 0) {
                        tbody.html('<tr><td colspan="6" class="text-center text-muted">Belum ada riwayat sinkronisasi untuk filter ini.</td></tr>');
                        return;
                    }

                    let html = '';
                    rows.forEach(function(log) {
                        let typeBadge = log.sync_type === 'push'
                            ? '<span class="badge badge-info"><i class="fas fa-arrow-down mr-1"></i>Push (CBT)</span>'
                            : '<span class="badge badge-warning"><i class="fas fa-arrow-up mr-1"></i>Pull (Guru)</span>';

                        let statusBadge = log.status === 'success'
                            ? '<span class="badge badge-success">Sukses</span>'
                            : (log.status === 'partial'
                                ? '<span class="badge badge-warning">Sebagian</span>'
                                : '<span class="badge badge-danger">Gagal</span>');

                        let targetName = log.target_field ? log.target_field.replace('value_', '').toUpperCase() : '-';

                        html += `
                            <tr>
                                <td><small>${log.created_at}</small></td>
                                <td>${typeBadge}</td>
                                <td><strong>${targetName}</strong></td>
                                <td><span class="text-success font-weight-bold">${log.total_synced}</span> / <span class="text-danger">${log.total_failed}</span></td>
                                <td>${statusBadge}</td>
                                <td><small>${log.message || '-'}</small></td>
                            </tr>
                        `;
                    });
                    tbody.html(html);
                },
                error: function() {
                    tbody.html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat riwayat log CBT.</td></tr>');
                }
            });
        }

        $('#cbtLogsBtn').click(function() {
            loadCbtLogs();
        });
        @endif
    });
</script>
@endsection
