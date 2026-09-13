@extends('layouts.app')

@section('styles')
<style>
    /* ─── Formatif Grid Responsive & Custom Styles ─── */
    #gridCard {
        max-width: 100%;
        overflow: hidden;
    }

    .formatif-table-wrapper {
        position: relative;
        display: block;
        width: 100%;
        max-width: 100%;
        max-height: calc(100vh - 250px);
        min-height: 380px;
        overflow-x: auto !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .formatif-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: max-content;
        min-width: 100%;
    }

    .formatif-table thead th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        color: #1e293b;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 10px 8px;
        border-bottom: 2px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
        vertical-align: top;
        z-index: 20;
    }

    /* ─── Sticky Columns Behavior (Desktop & Tablet vs Mobile) ─── */
    @media (min-width: 768px) {
        .formatif-table .col-sticky-no {
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
        .formatif-table thead th.col-sticky-no {
            position: sticky;
            top: 0;
            left: 0;
            z-index: 30;
            background-color: #f8fafc !important;
        }

        .formatif-table .col-sticky-name {
            position: sticky;
            left: 48px;
            min-width: 220px;
            max-width: 260px;
            background-color: #ffffff !important;
            border-right: 2px solid #cbd5e1 !important;
            box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.08);
            z-index: 15;
        }
        .formatif-table thead th.col-sticky-name {
            position: sticky;
            top: 0;
            left: 48px;
            z-index: 30;
            background-color: #f8fafc !important;
        }
    }

    /* Di layar smartphone (< 768px): Kolom tidak sticky agar tidak menutupi 80% layar saat di-swipe */
    @media (max-width: 767.98px) {
        .formatif-table .col-sticky-no {
            position: static !important;
            width: 40px;
            min-width: 40px;
            max-width: 40px;
            text-align: center;
        }
        .formatif-table .col-sticky-name {
            position: static !important;
            min-width: 170px;
            max-width: 220px;
            box-shadow: none !important;
        }
        .formatif-table thead th.col-sticky-no,
        .formatif-table thead th.col-sticky-name {
            position: sticky;
            top: 0;
            left: auto !important;
            z-index: 20 !important;
            box-shadow: none !important;
        }
    }

    /* TP Column Styling */
    .formatif-table .tp-col-header {
        min-width: 150px;
        max-width: 175px;
        text-align: center;
    }

    .formatif-table .tp-cell {
        padding: 6px 8px;
        vertical-align: middle;
        text-align: center;
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #f1f5f9;
    }

    .formatif-table .col-summary {
        min-width: 110px;
        text-align: center;
        vertical-align: middle;
        background-color: #fafafa;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Row Hover */
    .formatif-table tbody tr:hover td {
        background-color: #f8fafc !important;
    }
    .formatif-table tbody tr:hover .col-sticky-no,
    .formatif-table tbody tr:hover .col-sticky-name {
        background-color: #f1f5f9 !important;
    }

    /* Toggle Buttons */
    .btn-kktp-toggle {
        transition: all 0.15s ease-in-out;
        font-size: 0.8rem;
        border-radius: 6px;
        letter-spacing: 0.02em;
    }
    .btn-kktp-toggle:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-tampil-toggle {
        transition: all 0.15s ease-in-out;
        font-size: 0.72rem;
        border-radius: 4px;
        line-height: 1.4;
    }

    /* Text Clamping for TP description in headers */
    .tp-desc-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 0.75rem;
        line-height: 1.25;
        color: #64748b;
        margin-top: 4px;
        cursor: pointer;
    }

    /* Floating Save Bar Responsive */
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
                        <i class="fas fa-check-circle text-success mr-2"></i>{{ __('Input Asesmen Formatif') }}
                    </h1>
                    <p class="text-muted small mb-0">Kelola ketercapaian Tujuan Pembelajaran (KKTP) dan pengaturan deskripsi rapor Kurikulum Merdeka secara interaktif.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active">Asesmen Formatif</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- Panel Filter Terpadu (Unified Filter Bar) -->
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
                    <p class="text-muted small mb-0">Pilih filter di atas untuk menampilkan matriks lembar penilaian formatif siswa.</p>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingGridPlaceholder" class="card shadow-sm border-0 py-5 text-center" style="display: none;">
                <div class="card-body">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                    <h5 class="font-weight-bold text-dark">Memuat Data Asesmen Formatif...</h5>
                    <p class="text-muted small mb-0">Menyiapkan daftar siswa dan matriks Tujuan Pembelajaran.</p>
                </div>
            </div>

            <!-- Panel Matriks Penilaian Formatif -->
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
                            <span class="badge badge-primary px-2 py-2">
                                <i class="fas fa-bullseye mr-1"></i> <span id="badgeTotalTp">0</span> TP
                            </span>
                        </div>

                        <!-- Right: Actions Buttons -->
                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                            <button type="button" class="btn btn-outline-success btn-sm font-weight-bold" id="btnSetAllClassAchieved" title="Tandai seluruh siswa dan seluruh TP menjadi Tercapai (1)">
                                <i class="fas fa-check-double mr-1"></i> <span class="d-none d-sm-inline">Set Semua </span>Tercapai
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm font-weight-bold" id="btnOpenCsvModal" title="Upload format Excel">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </button>
                            <button type="button" class="btn btn-success btn-sm font-weight-bold btn-save-main px-3" id="saveBulkBtn">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                        </div>
                    </div>

                    <!-- Search Bar & Petunjuk Singkat -->
                    <div class="row align-items-center mt-3 pt-3 border-top">
                        <div class="col-md-5 col-12 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="studentSearchInput" class="form-control border-left-0" placeholder="Ketik untuk mencari nama siswa...">
                            </div>
                        </div>
                        <div class="col-md-7 col-12 text-md-right text-muted small">
                            <span class="mr-3"><i class="fas fa-mouse-pointer text-primary mr-1"></i> <b>Klik 1x:</b> Toggle <i>Tercapai / Bimbingan</i></span>
                            <span class="d-none d-sm-inline"><i class="fas fa-keyboard text-secondary mr-1"></i> Tekan <b>Ctrl + S</b> untuk simpan</span>
                        </div>
                    </div>
                </div>

                <!-- Touch Scroll Hint for Mobile Devices -->
                <div class="d-md-none bg-light px-3 py-2 border-bottom text-muted small d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-arrows-alt-h text-primary mr-1"></i> Geser tabel ke kanan/kiri untuk melihat seluruh TP</span>
                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-hand-point-right mr-1"></i> Geser</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive formatif-table-wrapper mb-0">
                        <table class="table formatif-table" id="valueTable">
                            <thead id="valueTableHead"></thead>
                            <tbody id="valueTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light py-2 text-muted small d-flex justify-content-between align-items-center">
                    <div>
                        <span id="studentCountText">Menampilkan 0 siswa</span>
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
                    <div class="font-weight-bold text-white small" style="letter-spacing: 0.02em;">LEMBAR FORMATIF</div>
                    <div class="text-light text-xs" style="opacity: 0.85;">
                        <span id="unsavedCountText">Ada perubahan belum disimpan</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center flex-shrink-0">
                <button type="button" class="btn btn-sm btn-outline-light mr-2 font-weight-bold" id="btnDiscardChanges">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
                <button type="button" class="btn btn-sm btn-success font-weight-bold px-2 px-sm-3 btn-save-main" id="btnFloatingSave">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Upload Excel TP Siswa -->
    <div class="modal fade" id="upCsvModal" tabindex="-1" role="dialog" aria-labelledby="upCsvModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="upCsvModalLabel">
                        <i class="fas fa-file-excel mr-2"></i>Upload Nilai Formatif dari Excel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="csvForm" enctype="multipart/form-data">
                    <div class="modal-body py-3">
                        <div class="alert alert-light border mb-3 py-2 px-3">
                            <strong class="d-block text-dark small font-weight-bold">Format Excel Formatif:</strong>
                            <span class="text-muted small">Unduh berkas template untuk kelas dan mapel ini:</span>
                            <div class="mt-2">
                                <a href="{{ route('value.download') }}" class="btn btn-sm btn-outline-success font-weight-bold" target="_blank">
                                    <i class="fas fa-download mr-1"></i> Unduh Format Excel
                                </a>
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label for="csv" class="font-weight-bold text-dark">Pilih Berkas Excel:</label>
                            <input type="file" class="form-control-file border p-2 rounded w-100 bg-light" id="csv" name="csv" accept=".xlsx, .xls, .csv" required>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success font-weight-bold">
                            <i class="fas fa-upload mr-1"></i> Unggah File
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            const csrfToken = "{{ csrf_token() }}";

            // State
            let class_id = "{{ Auth::user()->class_id ?? '' }}";
            let fst_id = "";
            let mapel_id = "";

            let class_name = "{{ $className ?? '' }}";
            let fst_name = "";
            let mapel_name = "";

            let currentTps = [];
            let currentStudents = [];
            let isDirty = false;

            // Auto-select first FST if available
            if ($('#filter_fst_id option').length > 1) {
                let firstValidFst = $('#filter_fst_id option:not(:disabled)').first().val();
                if (firstValidFst) {
                    $('#filter_fst_id').val(firstValidFst);
                    fst_id = firstValidFst;
                    fst_name = $('#filter_fst_id option:selected').text();
                }
            }

            // Jika role wali kelas, ambil mapel langsung
            if (class_id && fst_id) {
                fetchMapels();
            }

            // ── Filter Change Handlers ─────────────────────────────────────
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
                    loadFormatifGrid();
                }
            });

            function resetMapelDropdown() {
                $('#filter_mapel_id').html('<option value="" selected disabled>Loading Mata Pelajaran...</option>').prop('disabled', true);
                $('#gridCard').hide();
                $('#stickySaveBar').hide();
                $('#initialPlaceholder').show();
                mapel_id = "";
            }

            // ── Fetch Mapel via AJAX ───────────────────────────────────────
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
                    data: {
                        class_id: cVal,
                        fst_id: fVal
                    },
                    beforeSend: function() {
                        $('#filter_mapel_id').html('<option value="" selected disabled><i class="fas fa-spinner fa-spin"></i> Memuat mapel...</option>').prop('disabled', true);
                    },
                    success: function(response) {
                        let html = '<option value="" selected disabled>-- Pilih Mata Pelajaran --</option>';
                        if (response.length === 0) {
                            html = '<option value="" selected disabled>Belum ada mapel aktif untuk kelas & semester ini.</option>';
                            $('#filter_mapel_id').html(html).prop('disabled', true);
                        } else {
                            response.forEach(function(item) {
                                html += `<option value="${item.id}">${item.nama_mapel}</option>`;
                            });
                            $('#filter_mapel_id').html(html).prop('disabled', false);

                            if (mapel_id) {
                                $('#filter_mapel_id').val(mapel_id);
                            }
                        }
                    },
                    error: function() {
                        $('#filter_mapel_id').html('<option value="" selected disabled>Gagal mengambil data mapel.</option>').prop('disabled', true);
                    }
                });
            }

            // ── Load Grid Matrix Formatif ──────────────────────────────────
            function loadFormatifGrid() {
                if (!class_id || !fst_id || !mapel_id) return;

                // Update Badges
                $('#badgeClassText').text(class_name);
                $('#badgeFstText').text(fst_name);
                $('#badgeMapelText').text(mapel_name);

                $('#initialPlaceholder').hide();
                $('#gridCard').hide();
                $('#loadingGridPlaceholder').show();
                $('#stickySaveBar').hide();
                isDirty = false;

                $.ajax({
                    url: "{{ route('formatif.grid') }}",
                    type: "GET",
                    data: {
                        class_id: class_id,
                        mapel_id: mapel_id,
                        fst_id: fst_id
                    },
                    success: function(res) {
                        $('#loadingGridPlaceholder').hide();
                        currentTps = res.tps || [];
                        currentStudents = res.students || [];

                        $('#badgeTotalStudent').text(currentStudents.length);
                        $('#badgeTotalTp').text(currentTps.length);
                        $('#studentCountText').text(`Total ${currentStudents.length} siswa`);

                        if (currentTps.length === 0) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Belum Ada Tujuan Pembelajaran (TP)',
                                html: `Mata pelajaran <b>${mapel_name}</b> belum memiliki Tujuan Pembelajaran di semester ini.<br>Silakan tambahkan TP di menu <b>Master Tujuan Pembelajaran</b> terlebih dahulu.`,
                                confirmButtonText: 'Buka Master TP'
                            }).then(() => {
                                window.location.href = "{{ route('mastertp.index') }}";
                            });
                            return;
                        }

                        renderMatrixTable(currentTps, currentStudents);
                        $('#gridCard').fadeIn(200);
                    },
                    error: function() {
                        $('#loadingGridPlaceholder').hide();
                        $('#initialPlaceholder').show();
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat memuat data asesmen formatif.', 'error');
                    }
                });
            }

            // ── Render HTML Matrix Table ───────────────────────────────────
            function renderMatrixTable(tps, students) {
                // 1. Render Header
                let headHtml = '<tr>';
                headHtml += '<th class="col-sticky-no">No</th>';
                headHtml += '<th class="col-sticky-name">Nama Siswa</th>';

                tps.forEach(function(tp, idx) {
                    let safeDesc = (tp.tp_deskripsi || '').replace(/"/g, '&quot;');
                    headHtml += `
                        <th class="tp-col-header" data-tpid="${tp.id}">
                            <span class="badge badge-primary px-2 py-1 font-weight-bold">TP ${idx + 1}</span>
                            <div class="tp-desc-clamp" title="${safeDesc}">${tp.tp_deskripsi}</div>
                            <div class="btn-group btn-group-xs mt-2 w-100">
                                <button type="button" class="btn btn-outline-success btn-xs col-set-all-reach" data-tpid="${tp.id}" title="Set semua siswa di kolom TP ini menjadi Tercapai">
                                    <i class="fas fa-check-double"></i> Semua
                                </button>
                                <button type="button" class="btn btn-outline-info btn-xs col-copy-first" data-tpid="${tp.id}" title="Salin nilai siswa baris pertama ke seluruh siswa di bawahnya">
                                    <i class="fas fa-arrow-down"></i> Salin
                                </button>
                            </div>
                        </th>
                    `;
                });

                headHtml += '<th class="col-summary">Capaian TP</th>';
                headHtml += '</tr>';
                $('#valueTableHead').html(headHtml);

                // 2. Render Body
                let bodyHtml = '';
                students.forEach(function(student, sIdx) {
                    bodyHtml += `<tr class="student-row" data-studentid="${student.student_id}">`;
                    bodyHtml += `<td class="col-sticky-no font-weight-bold text-muted">${sIdx + 1}</td>`;
                    bodyHtml += `
                        <td class="col-sticky-name">
                            <div class="font-weight-bold text-dark student-name-text">${student.student_name}</div>
                            <small class="text-muted"><i class="fas fa-id-card-alt mr-1"></i>ID: ${student.student_id}</small>
                        </td>
                    `;

                    let achievedCount = 0;
                    tps.forEach(function(tp) {
                        let tpData = (student.tps && student.tps[tp.id]) ? student.tps[tp.id] : { kktp: 0, tampilkan: 0 };
                        let isKktp = parseInt(tpData.kktp) === 1;
                        let isTampil = parseInt(tpData.tampilkan) === 1;

                        if (isKktp) achievedCount++;

                        let kktpBtnClass = isKktp ? 'btn-success' : 'btn-outline-warning text-dark';
                        let kktpIcon = isKktp ? 'fa-check-circle' : 'fa-exclamation-circle';
                        let kktpText = isKktp ? 'Tercapai' : 'Bimbingan';

                        let tampilBtnClass = isTampil ? 'text-primary font-weight-bold' : 'text-muted';
                        let tampilIcon = isTampil ? 'fa-eye' : 'fa-eye-slash';
                        let tampilText = isTampil ? 'Rapor: Ya' : 'Rapor: Tidak';

                        bodyHtml += `
                            <td class="tp-cell" data-tpid="${tp.id}">
                                <button type="button" class="btn btn-sm btn-kktp-toggle w-100 font-weight-bold py-1 ${kktpBtnClass}" data-val="${isKktp ? 1 : 0}">
                                    <i class="fas ${kktpIcon} mr-1"></i> <span class="kktp-label">${kktpText}</span>
                                </button>
                                <input type="hidden" class="kktp-input" value="${isKktp ? 1 : 0}">

                                <button type="button" class="btn btn-xs btn-tampil-toggle w-100 btn-light border mt-1 py-0 ${tampilBtnClass}" data-val="${isTampil ? 1 : 0}">
                                    <i class="fas ${tampilIcon} mr-1"></i> <span class="tampil-label">${tampilText}</span>
                                </button>
                                <input type="hidden" class="tampilkan-input" value="${isTampil ? 1 : 0}">
                            </td>
                        `;
                    });

                    let totalTp = tps.length;
                    let pct = totalTp > 0 ? Math.round((achievedCount / totalTp) * 100) : 0;
                    let badgeClass = (achievedCount === totalTp) ? 'badge-success' : (achievedCount > 0 ? 'badge-info' : 'badge-warning');

                    bodyHtml += `
                        <td class="col-summary">
                            <span class="badge ${badgeClass} px-2 py-1 student-progress-badge" data-achieved="${achievedCount}" data-total="${totalTp}">
                                ${achievedCount}/${totalTp} (${pct}%)
                            </span>
                        </td>
                    `;
                    bodyHtml += '</tr>';
                });

                $('#valueTableBody').html(bodyHtml);
            }

            // ── Live Instant Search Siswa ──────────────────────────────────
            $('#studentSearchInput').on('keyup', function() {
                let query = $(this).val().toLowerCase().trim();
                let visibleCount = 0;

                $('#valueTableBody tr.student-row').each(function() {
                    let name = $(this).find('.student-name-text').text().toLowerCase();
                    let match = name.indexOf(query) > -1;
                    $(this).toggle(match);
                    if (match) visibleCount++;
                });

                $('#studentCountText').text(`Menampilkan ${visibleCount} dari ${currentStudents.length} siswa`);
            });

            // ── 1-Click Toggle: KKTP Ketercapaian ───────────────────────────
            $(document).on('click', '.btn-kktp-toggle', function() {
                let btn = $(this);
                let currentVal = parseInt(btn.attr('data-val')) || 0;
                let cell = btn.closest('.tp-cell');
                let input = cell.find('.kktp-input');

                if (currentVal === 1) {
                    // Ubah jadi Bimbingan (0)
                    btn.attr('data-val', 0);
                    btn.removeClass('btn-success').addClass('btn-outline-warning text-dark');
                    btn.html('<i class="fas fa-exclamation-circle mr-1"></i> <span class="kktp-label">Bimbingan</span>');
                    input.val(0);
                } else {
                    // Ubah jadi Tercapai (1)
                    btn.attr('data-val', 1);
                    btn.removeClass('btn-outline-warning text-dark').addClass('btn-success');
                    btn.html('<i class="fas fa-check-circle mr-1"></i> <span class="kktp-label">Tercapai</span>');
                    input.val(1);
                }

                updateRowSummary(btn.closest('tr'));
                markDirty();
            });

            // ── 1-Click Toggle: Tampilkan di Rapor ──────────────────────────
            $(document).on('click', '.btn-tampil-toggle', function() {
                let btn = $(this);
                let currentVal = parseInt(btn.attr('data-val')) || 0;
                let cell = btn.closest('.tp-cell');
                let input = cell.find('.tampilkan-input');

                if (currentVal === 1) {
                    // Ubah jadi Sembunyi (0)
                    btn.attr('data-val', 0);
                    btn.removeClass('text-primary font-weight-bold').addClass('text-muted');
                    btn.html('<i class="fas fa-eye-slash mr-1"></i> <span class="tampil-label">Rapor: Tidak</span>');
                    input.val(0);
                } else {
                    // Ubah jadi Tampil (1)
                    btn.attr('data-val', 1);
                    btn.removeClass('text-muted').addClass('text-primary font-weight-bold');
                    btn.html('<i class="fas fa-eye mr-1"></i> <span class="tampil-label">Rapor: Ya</span>');
                    input.val(1);
                }

                markDirty();
            });

            // ── Helper: Update Progress Badge Baris Siswa ──────────────────
            function updateRowSummary(row) {
                let total = currentTps.length;
                let reached = 0;
                row.find('.kktp-input').each(function() {
                    if (parseInt($(this).val()) === 1) reached++;
                });

                let pct = total > 0 ? Math.round((reached / total) * 100) : 0;
                let badgeClass = (reached === total) ? 'badge-success' : (reached > 0 ? 'badge-info' : 'badge-warning');

                let badge = row.find('.student-progress-badge');
                badge.removeClass('badge-success badge-info badge-warning')
                     .addClass(badgeClass)
                     .text(`${reached}/${total} (${pct}%)`);
            }

            // ── Header Action: Set Semua Siswa di Kolom TP Menjadi Tercapai ─
            $(document).on('click', '.col-set-all-reach', function() {
                let tpId = $(this).data('tpid');
                let cells = $(`#valueTableBody td.tp-cell[data-tpid='${tpId}']`);

                cells.each(function() {
                    let cell = $(this);
                    let btn = cell.find('.btn-kktp-toggle');
                    btn.attr('data-val', 1);
                    btn.removeClass('btn-outline-warning text-dark').addClass('btn-success');
                    btn.html('<i class="fas fa-check-circle mr-1"></i> <span class="kktp-label">Tercapai</span>');
                    cell.find('.kktp-input').val(1);

                    updateRowSummary(cell.closest('tr'));
                });

                markDirty();
                Swal.fire({
                    icon: 'success',
                    title: 'Kolom ini diset Tercapai untuk seluruh siswa!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            });

            // ── Header Action: Salin Baris 1 ke Seluruh Baris di Kolom TP ───
            $(document).on('click', '.col-copy-first', function() {
                let tpId = $(this).data('tpid');
                let firstRow = $('#valueTableBody tr:first');
                if (!firstRow.length) return;

                let firstCell = firstRow.find(`td.tp-cell[data-tpid='${tpId}']`);
                let targetKktp = parseInt(firstCell.find('.kktp-input').val()) || 0;
                let targetTampil = parseInt(firstCell.find('.tampilkan-input').val()) || 0;

                $(`#valueTableBody td.tp-cell[data-tpid='${tpId}']`).each(function() {
                    let cell = $(this);
                    // KKTP
                    let kBtn = cell.find('.btn-kktp-toggle');
                    kBtn.attr('data-val', targetKktp);
                    if (targetKktp === 1) {
                        kBtn.removeClass('btn-outline-warning text-dark').addClass('btn-success');
                        kBtn.html('<i class="fas fa-check-circle mr-1"></i> <span class="kktp-label">Tercapai</span>');
                    } else {
                        kBtn.removeClass('btn-success').addClass('btn-outline-warning text-dark');
                        kBtn.html('<i class="fas fa-exclamation-circle mr-1"></i> <span class="kktp-label">Bimbingan</span>');
                    }
                    cell.find('.kktp-input').val(targetKktp);

                    // Tampilkan
                    let tBtn = cell.find('.btn-tampil-toggle');
                    tBtn.attr('data-val', targetTampil);
                    if (targetTampil === 1) {
                        tBtn.removeClass('text-muted').addClass('text-primary font-weight-bold');
                        tBtn.html('<i class="fas fa-eye mr-1"></i> <span class="tampil-label">Rapor: Ya</span>');
                    } else {
                        tBtn.removeClass('text-primary font-weight-bold').addClass('text-muted');
                        tBtn.html('<i class="fas fa-eye-slash mr-1"></i> <span class="tampil-label">Rapor: Tidak</span>');
                    }
                    cell.find('.tampilkan-input').val(targetTampil);

                    updateRowSummary(cell.closest('tr'));
                });

                markDirty();
                Swal.fire({
                    icon: 'success',
                    title: 'Nilai baris pertama berhasil disalin ke seluruh siswa!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            });

            // ── Global Action: Set Seluruh Siswa & Seluruh TP Tercapai ─────
            $('#btnSetAllClassAchieved').click(function() {
                Swal.fire({
                    title: 'Set Semua Siswa Tercapai?',
                    text: 'Semua siswa di kelas ini akan ditandai Tercapai (1) untuk seluruh TP.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: '<i class="fas fa-check-double mr-1"></i> Ya, Set Semua!',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $('#valueTableBody td.tp-cell').each(function() {
                            let cell = $(this);
                            let kBtn = cell.find('.btn-kktp-toggle');
                            kBtn.attr('data-val', 1);
                            kBtn.removeClass('btn-outline-warning text-dark').addClass('btn-success');
                            kBtn.html('<i class="fas fa-check-circle mr-1"></i> <span class="kktp-label">Tercapai</span>');
                            cell.find('.kktp-input').val(1);
                        });

                        $('#valueTableBody tr.student-row').each(function() {
                            updateRowSummary($(this));
                        });

                        markDirty();
                        Swal.fire({
                            icon: 'success',
                            title: 'Seluruh nilai berhasil diset Tercapai!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });

            // ── Dirty State & Floating Save Bar Management ─────────────────
            function markDirty() {
                isDirty = true;
                $('#stickySaveBar').fadeIn(200);
            }

            function clearDirty() {
                isDirty = false;
                $('#stickySaveBar').fadeOut(200);
            }

            $('#btnDiscardChanges').click(function() {
                Swal.fire({
                    title: 'Batalkan Perubahan?',
                    text: 'Data yang belum disimpan akan dimuat ulang dari database.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Lanjutkan Edit'
                }).then((r) => {
                    if (r.isConfirmed) {
                        loadFormatifGrid();
                    }
                });
            });

            // ── Save Bulk Action ───────────────────────────────────────────
            function saveBulkFormatif() {
                if (!class_id || !mapel_id || !fst_id) {
                    Swal.fire('Peringatan', 'Silakan pilih Kelas, Periode, dan Mata Pelajaran terlebih dahulu.', 'warning');
                    return;
                }

                let saveButtons = $('.btn-save-main');
                saveButtons.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                let payload = {};
                $('#valueTableBody tr.student-row').each(function() {
                    let tr = $(this);
                    let studentId = tr.attr('data-studentid');
                    if (studentId) {
                        payload[studentId] = {};
                        tr.find('td.tp-cell').each(function() {
                            let cell = $(this);
                            let tpId = cell.attr('data-tpid');
                            let kktp = parseInt(cell.find('.kktp-input').val()) || 0;
                            let tampilkan = parseInt(cell.find('.tampilkan-input').val()) || 0;

                            payload[studentId][tpId] = {
                                kktp: kktp,
                                tampilkan: tampilkan
                            };
                        });
                    }
                });

                $.ajax({
                    url: "{{ route('formatif.storeBulk') }}",
                    method: "POST",
                    data: {
                        class_id: class_id,
                        mapel_id: mapel_id,
                        fst_id: fst_id,
                        data: payload,
                        _token: csrfToken
                    },
                    success: function(res) {
                        clearDirty();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Disimpan!',
                            text: res.message || 'Seluruh Nilai Formatif Berhasil Disimpan!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(err) {
                        Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan saat menyimpan nilai.', 'error');
                    },
                    complete: function() {
                        saveButtons.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Semua Nilai');
                    }
                });
            }

            $('.btn-save-main').click(saveBulkFormatif);

            // Shortcut Ctrl + S
            $(document).keydown(function(e) {
                if ((e.ctrlKey || e.metaKey) && e.which === 83) {
                    e.preventDefault();
                    saveBulkFormatif();
                }
            });

            // ── Modal Excel Upload ─────────────────────────────────────────
            $('#btnOpenCsvModal').click(function() {
                if (!class_id || !mapel_id || !fst_id) {
                    Swal.fire('Perhatian', 'Silakan pilih Kelas, Periode, dan Mata Pelajaran terlebih dahulu.', 'warning');
                    return;
                }
                $('#csv').val(null);
                $('#upCsvModal').modal('show');
            });

            $('#csvForm').on('submit', function(e) {
                e.preventDefault();
                let file = $('#csv')[0].files[0];
                if (!file) {
                    Swal.fire('Peringatan', 'Silakan pilih file Excel terlebih dahulu.', 'warning');
                    return;
                }

                let formData = new FormData(this);
                formData.append('class_id', class_id);
                formData.append('mapel_id', mapel_id);
                formData.append('fst_id', fst_id);

                $.ajax({
                    url: "{{ route('value.import') }}",
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#upCsvModal').modal('hide');
                        loadFormatifGrid();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal Import!', xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses file Excel.', 'error');
                    }
                });
            });

        });
    </script>
@endsection
