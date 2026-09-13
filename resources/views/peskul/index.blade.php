@extends('layouts.app')

@section('styles')
<style>
    /* ─── Penilaian Ekstrakurikuler Styles ─── */
    #gridCard {
        max-width: 100%;
        overflow: hidden;
    }

    .eskul-table-wrapper {
        position: relative;
        display: block;
        width: 100%;
        max-width: 100%;
        max-height: calc(100vh - 250px);
        min-height: 400px;
        overflow-x: auto !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .eskul-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 900px;
    }

    .eskul-table thead th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        color: #1e293b;
        font-weight: 700;
        font-size: 0.84rem;
        padding: 10px 12px;
        border-bottom: 2px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
        vertical-align: middle;
        text-align: center;
        z-index: 20;
    }

    /* Sticky columns desktop */
    @media (min-width: 768px) {
        .eskul-table .col-sticky-no {
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
        .eskul-table thead th.col-sticky-no {
            position: sticky;
            top: 0;
            left: 0;
            z-index: 30;
            background-color: #f8fafc !important;
        }

        .eskul-table .col-sticky-name {
            position: sticky;
            left: 48px;
            min-width: 220px;
            max-width: 260px;
            background-color: #ffffff !important;
            border-right: 2px solid #cbd5e1 !important;
            box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.08);
            z-index: 15;
        }
        .eskul-table thead th.col-sticky-name {
            position: sticky;
            top: 0;
            left: 48px;
            z-index: 30;
            background-color: #f8fafc !important;
        }
    }

    @media (max-width: 767.98px) {
        .eskul-table .col-sticky-no {
            position: static !important;
            width: 40px;
            min-width: 40px;
            text-align: center;
        }
        .eskul-table .col-sticky-name {
            position: static !important;
            min-width: 170px;
            max-width: 220px;
            box-shadow: none !important;
        }
        .eskul-table thead th.col-sticky-no,
        .eskul-table thead th.col-sticky-name {
            position: sticky;
            top: 0;
            left: auto !important;
            z-index: 20 !important;
            box-shadow: none !important;
        }
    }

    /* Table cells & controls */
    .eskul-table tbody td {
        padding: 10px 12px;
        vertical-align: top;
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
    }

    .eskul-table tbody tr:hover td {
        background-color: #f8fafc;
    }
    .eskul-table tbody tr:hover .col-sticky-no,
    .eskul-table tbody tr:hover .col-sticky-name {
        background-color: #f1f5f9 !important;
    }

    .eskul-card-cell {
        background: #fafafa;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 10px;
        transition: border-color 0.2s, background-color 0.2s;
    }

    .eskul-card-cell:focus-within {
        background: #ffffff;
        border-color: #93c5fd;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
    }

    .preset-badge-btn {
        cursor: pointer;
        padding: 3px 8px;
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 4px;
        transition: all 0.15s ease;
        user-select: none;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        border: 1px solid transparent;
    }

    .preset-badge-btn:hover {
        transform: translateY(-1px);
        filter: brightness(0.95);
    }

    .preset-badge-btn:active {
        transform: translateY(0);
    }

    /* Dynamic Preset Colors */
    .preset-success {
        background-color: #dcfce7;
        color: #15803d;
        border-color: #bbf7d0;
    }
    .preset-success:hover {
        background-color: #bbf7d0;
        color: #14532d;
    }

    .preset-primary {
        background-color: #e0f2fe;
        color: #0369a1;
        border-color: #bae6fd;
    }
    .preset-primary:hover {
        background-color: #bae6fd;
        color: #0c4a6e;
    }

    .preset-warning {
        background-color: #fef3c7;
        color: #b45309;
        border-color: #fde68a;
    }
    .preset-warning:hover {
        background-color: #fde68a;
        color: #78350f;
    }

    .preset-secondary {
        background-color: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }
    .preset-secondary:hover {
        background-color: #e2e8f0;
        color: #1e293b;
    }

    .preset-info {
        background-color: #e0e7ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }
    .preset-info:hover {
        background-color: #c7d2fe;
        color: #312e81;
    }

    .txt-eskul {
        font-size: 0.83rem;
        resize: vertical;
        min-height: 54px;
        line-height: 1.35;
        border-color: #d1d5db;
        border-radius: 5px;
    }

    .txt-eskul:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .highlight-flash {
        animation: flashGreen 0.7s ease-out;
    }

    @keyframes flashGreen {
        0% { background-color: #bbf7d0; }
        100% { background-color: #ffffff; }
    }

    /* Floating Save Bar */
    .sticky-floating-bar {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1040;
        min-width: 320px;
        max-width: 760px;
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
                        <i class="fas fa-medal text-warning mr-2"></i>{{ __('Input Penilaian Ekstrakurikuler') }}
                    </h1>
                    <p class="text-muted small mb-0">Kelola catatan keikutsertaan kegiatan ekstrakurikuler siswa beserta predikat & narasi capaian rapor.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active">Penilaian Ekstrakurikuler</li>
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
                        <i class="fas fa-filter text-primary mr-1"></i> Filter Kelas & Periode Semester
                    </h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body py-3">
                    <form id="filterForm">
                        <div class="row align-items-end">
                            <!-- Dropdown 1: Kelas -->
                            <div class="col-md-6 col-12 mb-2">
                                <label for="class_id" class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-school text-primary mr-1"></i> 1. Kelas
                                </label>
                                @if ($className)
                                    <select name="class_id" id="class_id" class="form-control font-weight-bold" disabled>
                                        <option value="{{ Auth::user()->class_id }}" selected>{{ $className }}</option>
                                    </select>
                                @else
                                    <select name="class_id" id="class_id" class="form-control font-weight-bold" required>
                                        <option value="" selected disabled>-- Pilih Kelas --</option>
                                        @include('partials.select_class_options', ['classList' => $classList])
                                    </select>
                                @endif
                            </div>

                            <!-- Dropdown 2: Periode / Semester -->
                            <div class="col-md-6 col-12 mb-2">
                                <label for="fst_id" class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-calendar-alt text-warning mr-1"></i> 2. Periode / Semester
                                </label>
                                <select name="fst_id" id="fst_id" class="form-control font-weight-bold" required>
                                    <option value="" selected disabled>-- Pilih Periode Semester --</option>
                                    @include('partials.select_fst_options', ['fstList' => $fstList])
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Empty State Awal -->
            <div id="initialPlaceholder" class="card shadow-sm border-0 py-5 text-center">
                <div class="card-body">
                    <i class="fas fa-award fa-4x text-muted mb-3" style="opacity: 0.6;"></i>
                    <h5 class="font-weight-bold text-secondary">Silakan Pilih Kelas dan Periode Semester</h5>
                    <p class="text-muted small mb-0">Pilih filter di atas untuk menampilkan lembar penilaian ekstrakurikuler siswa.</p>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingGridPlaceholder" class="card shadow-sm border-0 py-5 text-center" style="display: none;">
                <div class="card-body">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                    <h5 class="font-weight-bold text-dark">Memuat Data Ekstrakurikuler Siswa...</h5>
                    <p class="text-muted small mb-0">Menyiapkan daftar siswa dan riwayat penilaian ekstrakurikuler.</p>
                </div>
            </div>

            <!-- Panel Lembar Penilaian Ekstrakurikuler -->
            <div class="card card-outline card-secondary shadow-sm mb-5" id="gridCard" style="display: none;">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                        <!-- Left: Badges Konteks -->
                        <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                            <span class="badge badge-light border text-dark px-2 py-2">
                                <i class="fas fa-school text-primary mr-1"></i> <span id="badgeClassText">-</span>
                            </span>
                            <span class="badge badge-light border text-dark px-2 py-2">
                                <i class="fas fa-calendar-alt text-warning mr-1"></i> <span id="badgeFstText">-</span>
                            </span>
                            <span class="badge badge-info px-2 py-2">
                                <i class="fas fa-user-graduate mr-1"></i> <span id="badgeTotalStudent">0</span> Siswa
                            </span>
                        </div>

                        <!-- Right: Actions Buttons & Filter Siswa -->
                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="searchStudent" class="form-control border-left-0" placeholder="Cari nama siswa...">
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" id="btnOpenBatchModal">
                                <i class="fas fa-layer-group mr-1"></i> Terapkan Eskul Massal
                            </button>

                            @if (Auth::user()->role_id == 1)
                                <a href="{{ route('meskul.index') }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Kelola Template Master">
                                    <i class="fas fa-cog"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="eskul-table-wrapper">
                        <table class="table eskul-table" id="eskulTable">
                            <thead>
                                <tr>
                                    <th class="col-sticky-no">No</th>
                                    <th class="col-sticky-name text-left">Nama Siswa</th>
                                    <th style="min-width: 360px;">
                                        <i class="fas fa-star text-warning mr-1"></i> Ekstrakurikuler 1 (Utama / Wajib)
                                    </th>
                                    <th style="min-width: 360px;">
                                        <i class="fas fa-plus-circle text-info mr-1"></i> Ekstrakurikuler 2 (Tambahan / Pilihan)
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="eskulTableBody">
                                <!-- Data rows injected via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 px-3 text-muted small d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <i class="fas fa-info-circle text-info mr-1"></i>
                        <strong>Tips Efisien:</strong> Pilih eskul lalu klik tombol preset predikat untuk membuat narasi capaian rapor secara instan.
                    </div>
                    <div>
                        Tekan <kbd class="bg-secondary text-white">Ctrl + S</kbd> untuk menyimpan cepat kapan saja.
                    </div>
                </div>
            </div>

        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

    <!-- Floating Save Bar -->
    <div class="sticky-floating-bar" id="stickyFloatingBar">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center text-white" style="gap: 10px;">
                <span id="unsavedChangesBadge" class="badge badge-warning font-weight-bold px-2 py-1" style="display: none;">
                    <i class="fas fa-exclamation-circle mr-1"></i> Perubahan belum disimpan
                </span>
                <span id="saveStatusText" class="small text-white-50">
                    <i class="fas fa-check-circle text-success mr-1"></i> Data siap disimpan
                </span>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <span class="text-white-50 small d-none d-md-inline mr-1">
                    <kbd class="bg-secondary text-white">Ctrl + S</kbd>
                </span>
                <button type="button" class="btn btn-success font-weight-bold px-3 shadow" id="btnSaveBulk">
                    <i class="fas fa-save mr-1"></i> Simpan Penilaian
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Terapkan Eskul Massal -->
    <div class="modal fade" id="batchAssignModal" tabindex="-1" role="dialog" aria-labelledby="batchAssignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="batchAssignModalLabel">
                        <i class="fas fa-layer-group mr-2"></i> Terapkan Eskul Massal ke Seluruh Siswa
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-muted small mb-3">
                        Gunakan fitur ini untuk mengisi kegiatan eskul wajib (misal: Pramuka) ke seluruh siswa dalam satu kelas sekaligus.
                    </p>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase mb-1">Target Kolom</label>
                        <select id="batchSlot" class="form-control font-weight-bold">
                            <option value="0">Ekstrakurikuler 1 (Utama / Wajib)</option>
                            <option value="1">Ekstrakurikuler 2 (Tambahan / Pilihan)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase mb-1">Pilih Ekstrakurikuler</label>
                        <select id="batchEskulId" class="form-control font-weight-bold">
                            <option value="" selected disabled>-- Pilih Ekstrakurikuler --</option>
                            @foreach ($eskulList as $es)
                                <option value="{{ $es->id }}">{{ $es->nama_eskul }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-bold small text-muted text-uppercase mb-0">Pilihan Predikat Otomatis</label>
                            @if (Auth::user()->role_id == 1)
                                <a href="{{ route('meskul.index') }}" target="_blank" class="small font-weight-bold text-primary">
                                    <i class="fas fa-cog mr-1"></i> Kelola Template
                                </a>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap" style="gap: 6px;">
                            @forelse ($predikatList as $pred)
                                <button type="button" class="btn btn-sm btn-outline-{{ $pred->badge_color ?: 'primary' }} font-weight-bold flex-fill btn-batch-preset" data-kode="{{ $pred->kode }}" title="{{ $pred->nama }}">
                                    <i class="fas fa-check mr-1"></i> {{ $pred->kode }} - {{ $pred->nama }}
                                </button>
                            @empty
                                <small class="text-muted">Belum ada template predikat di master data.</small>
                            @endforelse
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase mb-1">Deskripsi / Catatan Rapor</label>
                        <textarea id="batchDesc" class="form-control font-weight-normal" rows="3" placeholder="Pilih predikat di atas atau ketik deskripsi khusus..."></textarea>
                    </div>

                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="batchOverwrite" checked>
                        <label class="custom-control-label small font-weight-bold text-dark" for="batchOverwrite">
                            Timpa nilai siswa yang sudah terisi sebelumnya
                        </label>
                        <small class="form-text text-muted">Jika tidak dicentang, hanya baris siswa yang masih kosong yang akan diisi.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" id="btnExecuteBatch">
                        <i class="fas fa-check mr-1"></i> Terapkan Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // State
            let class_id = null;
            let fst_id = null;
            let class_name = '';
            let fst_name = '';
            let eskulList = @json($eskulList);
            let predikatList = @json($predikatList);
            let rawStudentData = [];
            let isDirty = false;

            // Pre-selected class check
            const initialClassId = $('#class_id').val();
            if (initialClassId) {
                class_id = initialClassId;
                class_name = $('#class_id option:selected').text().trim();
            }

            function generateEskulOptions(selectedId) {
                let html = '<option value="">-- Tidak Ada / Kosong --</option>';
                eskulList.forEach(e => {
                    let sel = (selectedId && selectedId == e.id) ? 'selected' : '';
                    html += `<option value="${e.id}" ${sel}>${e.nama_eskul}</option>`;
                });
                return html;
            }

            function generatePresetButtonsHtml() {
                if (!predikatList || predikatList.length === 0) return '';
                let html = '<div class="d-flex align-items-center mb-1 flex-wrap" style="gap: 4px;">';
                html += '<span class="text-xs text-muted mr-1"><i class="fas fa-magic"></i> Preset:</span>';
                predikatList.forEach(p => {
                    let badge = p.badge_color || 'primary';
                    html += `<span class="preset-badge-btn preset-${badge} btn-preset" data-kode="${p.kode}" title="${p.nama}">${p.kode} - ${p.nama}</span>`;
                });
                html += '</div>';
                return html;
            }

            function setDirty(state) {
                isDirty = state;
                if (state) {
                    $('#unsavedChangesBadge').show();
                    $('#saveStatusText').html('<i class="fas fa-edit text-warning mr-1"></i> Perubahan belum disimpan');
                    $('#stickyFloatingBar').fadeIn(200);
                } else {
                    $('#unsavedChangesBadge').hide();
                    $('#saveStatusText').html('<i class="fas fa-check-circle text-success mr-1"></i> Semua data tersimpan');
                }
            }

            // Unsaved prompt warning before leaving
            window.addEventListener('beforeunload', function(e) {
                if (isDirty) {
                    e.preventDefault();
                    e.returnValue = 'Perubahan belum disimpan. Yakin ingin keluar?';
                }
            });

            // Handle Filter Selection
            $('#class_id').on('change', function() {
                class_id = $(this).val();
                class_name = $(this).find('option:selected').text().trim();
                triggerAutoLoad();
            });

            $('#fst_id').on('change', function() {
                fst_id = $(this).val();
                fst_name = $(this).find('option:selected').text().trim();
                triggerAutoLoad();
            });

            function triggerAutoLoad() {
                if (class_id && fst_id) {
                    loadPeskulData();
                }
            }

            // Load Data via Ajax
            function loadPeskulData() {
                if (isDirty) {
                    if (!confirm('Terdapat perubahan data yang belum disimpan. Tetap ganti filter dan buang perubahan?')) {
                        return;
                    }
                }

                $('#initialPlaceholder').hide();
                $('#gridCard').hide();
                $('#stickyFloatingBar').hide();
                $('#loadingGridPlaceholder').show();

                $.ajax({
                    url: "{{ route('peskul.getdata') }}",
                    type: "GET",
                    data: {
                        class_id: class_id,
                        fst_id: fst_id
                    },
                    success: function(response) {
                        $('#loadingGridPlaceholder').hide();
                        rawStudentData = response.data || [];

                        $('#badgeClassText').text(class_name);
                        $('#badgeFstText').text(fst_name);
                        $('#badgeTotalStudent').text(rawStudentData.length);

                        renderStudentRows(rawStudentData);
                        setDirty(false);
                        $('#gridCard').fadeIn(250);
                    },
                    error: function(xhr) {
                        $('#loadingGridPlaceholder').hide();
                        $('#initialPlaceholder').show();
                        SwalHelper.showError('Gagal memuat data ekstrakurikuler siswa.');
                    }
                });
            }

            // Render Table Rows
            function renderStudentRows(students) {
                const tbody = $('#eskulTableBody');
                tbody.empty();

                if (!students || students.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-user-slash fa-2x mb-2 d-block text-secondary"></i>
                                Belum ada data siswa terdaftar di kelas ini.
                            </td>
                        </tr>
                    `);
                    return;
                }

                const presetsHtml = generatePresetButtonsHtml();

                students.forEach((student, index) => {
                    const eskul1 = (student.eskuls && student.eskuls[0]) ? student.eskuls[0] : null;
                    const eskul2 = (student.eskuls && student.eskuls[1]) ? student.eskuls[1] : null;

                    const rowHtml = `
                        <tr class="student-row" data-student-id="${student.student_id}" data-student-name="${student.student_name.toLowerCase()}">
                            <td class="col-sticky-no text-center font-weight-bold text-muted">${index + 1}</td>
                            <td class="col-sticky-name">
                                <div class="font-weight-bold text-dark text-truncate" title="${student.student_name}">
                                    ${student.student_name}
                                </div>
                                <span class="text-xs text-muted">ID: ${student.student_id}</span>
                            </td>

                            <!-- Slot Eskul 1 (Utama) -->
                            <td>
                                <div class="eskul-card-cell" data-slot="0">
                                    <div class="d-flex align-items-center mb-1" style="gap: 5px;">
                                        <select class="form-control form-control-sm font-weight-bold sel-eskul" data-student="${student.student_id}" data-slot="0">
                                            ${generateEskulOptions(eskul1 ? eskul1.eskul_id : '')}
                                        </select>
                                        <button type="button" class="btn btn-xs btn-outline-danger btn-clear-slot" title="Kosongkan Slot">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    ${presetsHtml}
                                    <textarea class="form-control form-control-sm txt-eskul" data-student="${student.student_id}" data-slot="0" placeholder="Deskripsi narasi capaian rapor...">${eskul1 ? (eskul1.nilai_eskul || '') : ''}</textarea>
                                </div>
                            </td>

                            <!-- Slot Eskul 2 (Pilihan) -->
                            <td>
                                <div class="eskul-card-cell" data-slot="1">
                                    <div class="d-flex align-items-center mb-1" style="gap: 5px;">
                                        <select class="form-control form-control-sm font-weight-bold sel-eskul" data-student="${student.student_id}" data-slot="1">
                                            ${generateEskulOptions(eskul2 ? eskul2.eskul_id : '')}
                                        </select>
                                        <button type="button" class="btn btn-xs btn-outline-danger btn-clear-slot" title="Kosongkan Slot">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    ${presetsHtml}
                                    <textarea class="form-control form-control-sm txt-eskul" data-student="${student.student_id}" data-slot="1" placeholder="Deskripsi narasi capaian rapor...">${eskul2 ? (eskul2.nilai_eskul || '') : ''}</textarea>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.append(rowHtml);
                });
            }

            // Quick Preset Click Handler (Dynamic from master data)
            $(document).on('click', '.btn-preset', function() {
                const kode = $(this).data('kode');
                const cell = $(this).closest('.eskul-card-cell');
                const select = cell.find('.sel-eskul');
                const textarea = cell.find('.txt-eskul');
                const eskulId = select.val();
                const eskulName = select.find('option:selected').text().trim();

                if (!eskulId) {
                    SwalHelper.showError('Pilih Ekstrakurikuler terlebih dahulu sebelum memilih predikat.');
                    select.focus();
                    return;
                }

                const matched = predikatList.find(p => p.kode === kode);
                const rawTemplate = matched ? matched.template_narasi : "Aktif mengikuti kegiatan {eskul}.";
                const generated = rawTemplate.replace(/\{eskul\}/g, eskulName);

                textarea.val(generated);
                textarea.addClass('highlight-flash');
                setTimeout(() => textarea.removeClass('highlight-flash'), 700);

                setDirty(true);
            });

            // Clear Slot Handler
            $(document).on('click', '.btn-clear-slot', function() {
                const cell = $(this).closest('.eskul-card-cell');
                cell.find('.sel-eskul').val('');
                cell.find('.txt-eskul').val('');
                setDirty(true);
            });

            // Change listener on inputs
            $(document).on('change', '.sel-eskul', function() {
                setDirty(true);
            });

            $(document).on('input', '.txt-eskul', function() {
                setDirty(true);
            });

            // Real-time Search by Student Name
            $('#searchStudent').on('input', function() {
                const query = $(this).val().toLowerCase().trim();
                if (!query) {
                    $('.student-row').show();
                    return;
                }
                $('.student-row').each(function() {
                    const name = $(this).data('student-name');
                    if (name.includes(query)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Batch Modal Controls
            $('#btnOpenBatchModal').on('click', function() {
                $('#batchAssignModal').modal('show');
            });

            $('.btn-batch-preset').on('click', function() {
                const kode = $(this).data('kode');
                const eskulId = $('#batchEskulId').val();
                const eskulName = eskulId ? $('#batchEskulId option:selected').text().trim() : '[Nama Eskul]';

                const matched = predikatList.find(p => p.kode === kode);
                const rawTemplate = matched ? matched.template_narasi : "Aktif mengikuti kegiatan {eskul}.";
                $('#batchDesc').val(rawTemplate.replace(/\{eskul\}/g, eskulName));
            });

            $('#batchEskulId').on('change', function() {
                const eskulName = $(this).find('option:selected').text().trim();
                const currentDesc = $('#batchDesc').val();
                if (!currentDesc || currentDesc.includes('[Nama Eskul]')) {
                    const defaultPred = (predikatList && predikatList.length > 0) ? predikatList[0] : null;
                    if (defaultPred) {
                        $('#batchDesc').val(defaultPred.template_narasi.replace(/\{eskul\}/g, eskulName));
                    }
                }
            });

            $('#btnExecuteBatch').on('click', function() {
                const targetSlot = $('#batchSlot').val();
                const eskulId = $('#batchEskulId').val();
                const eskulName = $('#batchEskulId option:selected').text().trim();
                const desc = $('#batchDesc').val().trim();
                const overwrite = $('#batchOverwrite').is(':checked');

                if (!eskulId) {
                    SwalHelper.showError('Silakan pilih ekstrakurikuler terlebih dahulu.');
                    return;
                }
                if (!desc) {
                    SwalHelper.showError('Silakan masukkan atau pilih deskripsi predikat.');
                    return;
                }

                let updatedCount = 0;
                $('.student-row').each(function() {
                    const cell = $(this).find(`.eskul-card-cell[data-slot="${targetSlot}"]`);
                    const select = cell.find('.sel-eskul');
                    const textarea = cell.find('.txt-eskul');

                    if (overwrite || (!select.val() && !textarea.val())) {
                        select.val(eskulId);
                        textarea.val(desc);
                        updatedCount++;
                    }
                });

                $('#batchAssignModal').modal('hide');
                setDirty(true);
                SwalHelper.showSuccess(`Berhasil menerapkan ${eskulName} ke ${updatedCount} siswa!`);
            });

            // Save Function (Bulk)
            function executeSave() {
                if (!class_id || !fst_id) {
                    SwalHelper.showError('Pilih kelas dan semester terlebih dahulu.');
                    return;
                }

                const payload = {};
                $('.student-row').each(function() {
                    const studentId = $(this).data('student-id');
                    payload[studentId] = [];

                    $(this).find('.eskul-card-cell').each(function() {
                        const eskul_id = $(this).find('.sel-eskul').val();
                        const desc = $(this).find('.txt-eskul').val().trim();

                        if (eskul_id && desc) {
                            payload[studentId].push({
                                eskul_id: eskul_id,
                                nilai_eskul: desc
                            });
                        }
                    });
                });

                const btn = $('#btnSaveBulk');
                const originHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: "{{ route('peskul.storeBulk') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        class_id: class_id,
                        fst_id: fst_id,
                        students: payload
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html(originHtml);
                        setDirty(false);
                        SwalHelper.showSuccess(res.message || 'Penilaian ekstrakurikuler berhasil disimpan!');
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html(originHtml);
                        const msg = err.responseJSON?.message || 'Gagal menyimpan penilaian ekstrakurikuler.';
                        SwalHelper.showError(msg);
                    }
                });
            }

            $('#btnSaveBulk').on('click', function() {
                executeSave();
            });

            // Keyboard shortcut Ctrl + S / Cmd + S
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                    e.preventDefault();
                    if ($('#gridCard').is(':visible')) {
                        executeSave();
                    }
                }
            });
        });
    </script>
@endsection
