@extends('layouts.app')

@section('content')
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-bullseye text-primary mr-2"></i>{{ __('Master Tujuan Pembelajaran (TP)') }}
                    </h1>
                    <p class="text-muted small mb-0">Kelola dan rumuskan Tujuan Pembelajaran (TP) Kurikulum Merdeka per mata pelajaran & periode semester (berlaku untuk semua kelas paralel).</p>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active">Master TP</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- Panel Filter Terpadu (Periode & Mata Pelajaran) -->
            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-filter text-primary mr-1"></i> Filter Periode Semester & Mata Pelajaran
                    </h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body py-3">
                    <div class="alert alert-light border py-2 px-3 mb-3 small d-flex align-items-center">
                        <i class="fas fa-info-circle text-primary fa-lg mr-2"></i>
                        <div>
                            <strong>Konsep Kurikulum Merdeka:</strong> Tujuan Pembelajaran (TP) dirumuskan untuk <strong>1 Mata Pelajaran</strong> dan <strong>1 Periode Semester (Fase/TA)</strong>. TP yang Anda buat atau unggah di sini otomatis berlaku sebagai acuan penilaian di <strong>seluruh kelas paralel</strong>.
                        </div>
                    </div>

                    <form id="unifiedFilterForm">
                        <div class="row align-items-end">
                            <!-- Dropdown 1: Fase / Semester / Tahun Ajaran -->
                            <div class="col-md-6 col-12 mb-2">
                                <label for="filter_fst_id" class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-calendar-alt mr-1 text-warning"></i> 1. Periode / Semester (Fase & TA)
                                </label>
                                <select name="fst_id" id="filter_fst_id" class="form-control font-weight-bold" required>
                                    <option value="" selected disabled>-- Pilih Periode Semester --</option>
                                    @include('partials.select_fst_options', ['fstList' => $fstList])
                                </select>
                            </div>

                            <!-- Dropdown 2: Mata Pelajaran -->
                            <div class="col-md-6 col-12 mb-2">
                                <label for="filter_mapel_id" class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-book mr-1 text-success"></i> 2. Mata Pelajaran
                                </label>
                                <select name="mapel_id" id="filter_mapel_id" class="form-control font-weight-bold" required>
                                    <option value="" selected disabled>-- Pilih Mata Pelajaran --</option>
                                    @foreach ($mapelList as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->nama_mapel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Empty State Awal (Sebelum Memilih Filter) -->
            <div id="initialPlaceholder" class="card shadow-sm border-0 py-5 text-center">
                <div class="card-body">
                    <i class="fas fa-layer-group fa-4x text-muted mb-3"></i>
                    <h5 class="font-weight-bold text-secondary">Silakan Pilih Periode Semester dan Mata Pelajaran</h5>
                    <p class="text-muted small mb-0">Gunakan filter di atas untuk memuat daftar Tujuan Pembelajaran yang tersimpan atau menambah TP baru.</p>
                </div>
            </div>

            <!-- Panel Data Table Tujuan Pembelajaran (Muncul Setelah Filter Dipilih) -->
            <div class="card card-outline card-secondary shadow-sm mb-4" id="tpDataCard" style="display: none;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                    <!-- Left: Active Context Badges -->
                    <div class="mb-2 mb-md-0">
                        <span class="badge badge-light border text-dark px-3 py-2 mr-1">
                            <i class="fas fa-book text-success mr-1"></i> <span id="badgeMapelText">-</span>
                        </span>
                        <span class="badge badge-light border text-dark px-3 py-2 mr-1">
                            <i class="fas fa-calendar-alt text-warning mr-1"></i> <span id="badgeFstText">-</span>
                        </span>
                        <span class="badge badge-success px-3 py-2 mr-1" title="TP ini digunakan bersama untuk semua kelas rombel yang mengambil mapel ini">
                            <i class="fas fa-users mr-1"></i> Berlaku Seluruh Kelas Paralel
                        </span>
                        <span class="badge badge-primary px-3 py-2">
                            <i class="fas fa-list-ol mr-1"></i> Total: <b id="badgeTotalTp">0</b> TP
                        </span>
                    </div>

                    <!-- Right: Action Buttons -->
                    <div class="d-flex align-items-center flex-wrap">
                        <a href="javascript:void(0)" id="btnDownloadTemplate" class="btn btn-outline-success btn-sm mr-2 mb-1 font-weight-bold">
                            <i class="fas fa-file-download mr-1"></i> Unduh Format Excel
                        </a>
                        <button type="button" id="btnOpenImportModal" class="btn btn-success btn-sm mr-2 mb-1 font-weight-bold">
                            <i class="fas fa-file-excel mr-1"></i> Import Excel TP
                        </button>
                        <button type="button" id="btnOpenAddModal" class="btn btn-primary btn-sm mb-1 font-weight-bold">
                            <i class="fas fa-plus mr-1"></i> + Tambah TP Manual
                        </button>
                    </div>
                </div>

                <div class="card-body p-3">
                    <table class="table table-hover table-striped table-bordered w-100" id="tpTable">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 110px;" class="text-center">Kode</th>
                                <th>Deskripsi Tujuan Pembelajaran (Capaian Kompetensi)</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div><!-- /.container-fluid -->
    </div><!-- /.content -->

    <!-- ============================================================== -->
    <!-- MODAL 1: INPUT TUJUAN PEMBELAJARAN (MANUAL)                     -->
    <!-- ============================================================== -->
    <div class="modal fade" id="addTpModal" tabindex="-1" role="dialog" aria-labelledby="addTpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="addTpModalLabel">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Tujuan Pembelajaran (TP)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addTpForm">
                    <div class="modal-body py-3">
                        <!-- Banner Info Konteks -->
                        <div class="alert alert-info py-2 px-3 mb-3 small d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Target Mapel:</strong> <span id="modalAddMapel">-</span> |
                                <strong>Periode:</strong> <span id="modalAddFst">-</span>
                            </div>
                            <span class="badge badge-light border text-primary">
                                <i class="fas fa-globe mr-1"></i> Berlaku Semua Kelas
                            </span>
                        </div>

                        <p class="text-muted small mb-2">
                            <i class="fas fa-info-circle text-primary mr-1"></i>
                            Tuliskan rumusan deskripsi Tujuan Pembelajaran secara ringkas dan spesifik. Anda dapat menambahkan beberapa baris TP sekaligus.
                        </p>

                        <!-- Dynamic TP Inputs Container -->
                        <div id="tpInputsContainer">
                            <!-- Default Row 1 -->
                            <div class="card card-body bg-light p-3 mb-2 border tp-input-row" data-index="1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold text-dark small text-uppercase">
                                        <i class="fas fa-tag text-primary mr-1"></i> Tujuan Pembelajaran #1
                                    </span>
                                </div>
                                <textarea class="form-control tp_deskripsi_input" name="tp_deskripsi[]" rows="2" placeholder="Contoh: Memahami struktur teks laporan hasil observasi dan kaidah kebahasaannya..." required></textarea>
                            </div>
                        </div>

                        <!-- Button Add Row -->
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2 font-weight-bold" id="btnAddRowBtn">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris TP Lainnya
                        </button>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold" id="btnSubmitAddTp">
                            <i class="fas fa-save mr-1"></i> Simpan Semua TP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: EDIT TUJUAN PEMBELAJARAN (MANUAL)                      -->
    <!-- ============================================================== -->
    <div class="modal fade" id="editTpModal" tabindex="-1" role="dialog" aria-labelledby="editTpModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-info text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="editTpModalLabel">
                        <i class="fas fa-edit mr-2"></i>Edit Tujuan Pembelajaran
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editTpForm">
                    <div class="modal-body py-3">
                        <input type="hidden" id="edit_tp_id">
                        <div class="form-group mb-2">
                            <label for="edit_tp_deskripsi" class="font-weight-bold text-muted small text-uppercase">
                                Deskripsi Tujuan Pembelajaran (TP):
                            </label>
                            <textarea class="form-control" id="edit_tp_deskripsi" name="tp_deskripsi[]" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info font-weight-bold text-white" id="btnSubmitEditTp">
                            <i class="fas fa-check mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: IMPORT EXCEL TUJUAN PEMBELAJARAN                       -->
    <!-- ============================================================== -->
    <div class="modal fade" id="importTpModal" tabindex="-1" role="dialog" aria-labelledby="importTpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="importTpModalLabel">
                        <i class="fas fa-file-excel mr-2"></i>Import Tujuan Pembelajaran dari Excel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="importTpForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body py-3">
                        <!-- Step 1: Download Template -->
                        <div class="alert alert-light border mb-3 py-2 px-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <strong class="d-block text-dark small font-weight-bold">Format Excel (.xlsx):</strong>
                                    <span class="text-muted small">Unduh template yang sudah sesuai kolom sistem:</span>
                                </div>
                                <button type="button" id="btnDownloadTemplateInModal" class="btn btn-sm btn-outline-success font-weight-bold">
                                    <i class="fas fa-download mr-1"></i> Unduh Template
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: File Input -->
                        <div class="form-group mb-3">
                            <label for="import_file" class="font-weight-bold text-dark">Pilih Berkas Excel (.xlsx / .xls):</label>
                            <input type="file" class="form-control-file border p-2 rounded w-100 bg-light" id="import_file" name="file" accept=".xlsx, .xls, .csv" required>
                            <small class="form-text text-muted">Maksimal ukuran file: 10 MB.</small>
                        </div>

                        <!-- Step 3: Opsi Mode Import -->
                        <div class="form-group mb-2">
                            <label class="font-weight-bold text-dark d-block">Metode Import TP:</label>
                            <div class="custom-control custom-radio mb-1">
                                <input type="radio" id="modeReplace" name="import_mode" class="custom-control-input" value="replace" checked>
                                <label class="custom-control-label font-weight-normal" for="modeReplace">
                                    <strong>Ganti Seluruh TP (Replace)</strong>
                                    <span class="d-block text-muted small">Menghapus TP lama di mapel dan periode semester ini, lalu menggantinya dengan TP dari Excel (berlaku ke semua kelas).</span>
                                </label>
                            </div>
                            <div class="custom-control custom-radio mt-2">
                                <input type="radio" id="modeAppend" name="import_mode" class="custom-control-input" value="append">
                                <label class="custom-control-label font-weight-normal" for="modeAppend">
                                    <strong>Tambahkan (Append)</strong>
                                    <span class="d-block text-muted small">Menambahkan baris TP baru tanpa menghapus TP yang sudah tersimpan sebelumnya.</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success font-weight-bold" id="btnSubmitImport">
                            <i class="fas fa-upload mr-1"></i> Proses Import Excel
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
            let activeFstId   = "";
            let activeMapelId = "";

            let activeFstName   = "";
            let activeMapelName = "";

            let tpTable = null;
            let tpRowCount = 1;

            // ── Auto-select FST Semester pertama / default jika tersedia ───
            if ($('#filter_fst_id option').length > 1) {
                let firstValidFst = $('#filter_fst_id option:not(:disabled)').first().val();
                if (firstValidFst) {
                    $('#filter_fst_id').val(firstValidFst);
                    activeFstId = firstValidFst;
                    activeFstName = $('#filter_fst_id option:selected').text();
                }
            }

            // ── Event Handlers Filter ────────────────────────────────────
            $('#filter_fst_id').on('change', function() {
                activeFstId   = $(this).val();
                activeFstName = $('#filter_fst_id option:selected').text();
                checkAndLoad();
            });

            $('#filter_mapel_id').on('change', function() {
                activeMapelId   = $(this).val();
                activeMapelName = $('#filter_mapel_id option:selected').text();
                checkAndLoad();
            });

            function checkAndLoad() {
                if (activeFstId && activeMapelId) {
                    loadTpDataTable();
                } else {
                    $('#tpDataCard').hide();
                    $('#initialPlaceholder').show();
                }
            }

            // ── Load / Reload DataTable Tujuan Pembelajaran ──────────────
            function loadTpDataTable() {
                if (!activeFstId || !activeMapelId) {
                    return;
                }

                // Update Badges
                $('#badgeFstText').text(activeFstName);
                $('#badgeMapelText').text(activeMapelName);

                $('#initialPlaceholder').hide();
                $('#tpDataCard').fadeIn(200);

                if ($.fn.DataTable.isDataTable('#tpTable')) {
                    $('#tpTable').DataTable().destroy();
                }

                tpTable = $('#tpTable').DataTable({
                    responsive: true,
                    ajax: {
                        url: "{{ route('mastertp.getdata') }}",
                        type: "GET",
                        data: {
                            fst_id: activeFstId,
                            mapel_id: activeMapelId
                        },
                        dataSrc: function(json) {
                            let list = json.data || [];
                            $('#badgeTotalTp').text(list.length);
                            return list;
                        }
                    },
                    columns: [
                        {
                            data: null,
                            className: "text-center align-middle font-weight-bold",
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        },
                        {
                            data: null,
                            className: "text-center align-middle",
                            render: function(data, type, row, meta) {
                                return `<span class="badge badge-primary px-2 py-1 font-weight-bold">TP ${meta.row + 1}</span>`;
                            }
                        },
                        {
                            data: "tp_deskripsi",
                            className: "align-middle",
                            render: function(data) {
                                return data ? `<div style="font-size: 0.95rem; line-height: 1.45;">${data}</div>` : '<span class="text-muted italic">-</span>';
                            }
                        },
                        {
                            data: null,
                            className: "text-center align-middle",
                            render: function(data, type, row) {
                                let safeDesc = (row.tp_deskripsi || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                return `
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-info editTpBtn" data-id="${row.id}" data-tp_deskripsi="${safeDesc}" title="Edit TP">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delTpBtn" data-id="${row.id}" title="Hapus TP">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ],
                    language: {
                        emptyTable: `
                            <div class="py-4 text-center">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-2"></i>
                                <p class="text-muted font-weight-bold mb-2">Belum ada Tujuan Pembelajaran yang tersimpan untuk mata pelajaran ini.</p>
                                <button type="button" class="btn btn-sm btn-primary mr-1" onclick="$('#btnOpenAddModal').click()">
                                    <i class="fas fa-plus mr-1"></i> Tambah TP Manual
                                </button>
                                <button type="button" class="btn btn-sm btn-success" onclick="$('#btnOpenImportModal').click()">
                                    <i class="fas fa-file-excel mr-1"></i> Import dari Excel
                                </button>
                            </div>
                        `
                    }
                });
            }

            // ── Download Template Excel Handler ──────────────────────────
            function triggerDownloadTemplate() {
                if (!activeMapelId || !activeFstId) {
                    Swal.fire('Perhatian', 'Silahkan pilih Periode Semester dan Mata Pelajaran terlebih dahulu.', 'warning');
                    return;
                }
                let url = `{{ route('mastertp.template') }}?mapel_id=${activeMapelId}&fst_id=${activeFstId}`;
                window.location.href = url;
            }

            $('#btnDownloadTemplate').click(triggerDownloadTemplate);
            $('#btnDownloadTemplateInModal').click(triggerDownloadTemplate);

            // ── Modal Tambah TP Manual Handlers ──────────────────────────
            $('#btnOpenAddModal').click(function() {
                if (!activeMapelId || !activeFstId) {
                    Swal.fire('Perhatian', 'Silahkan pilih Periode Semester dan Mata Pelajaran terlebih dahulu.', 'warning');
                    return;
                }

                $('#modalAddMapel').text(activeMapelName);
                $('#modalAddFst').text(activeFstName);

                tpRowCount = 1;
                $('#tpInputsContainer').html(`
                    <div class="card card-body bg-light p-3 mb-2 border tp-input-row" data-index="1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark small text-uppercase">
                                <i class="fas fa-tag text-primary mr-1"></i> Tujuan Pembelajaran #1
                            </span>
                        </div>
                        <textarea class="form-control tp_deskripsi_input" name="tp_deskripsi[]" rows="2" placeholder="Contoh: Memahami struktur teks laporan hasil observasi dan kaidah kebahasaannya..." required></textarea>
                    </div>
                `);

                $('#addTpModal').modal('show');
            });

            $('#btnAddRowBtn').click(function() {
                tpRowCount++;
                $('#tpInputsContainer').append(`
                    <div class="card card-body bg-light p-3 mb-2 border tp-input-row" data-index="${tpRowCount}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark small text-uppercase">
                                <i class="fas fa-tag text-primary mr-1"></i> Tujuan Pembelajaran #${tpRowCount}
                            </span>
                            <button type="button" class="btn btn-outline-danger btn-xs removeTpRowBtn" title="Hapus baris ini">
                                <i class="fas fa-times mr-1"></i> Hapus
                            </button>
                        </div>
                        <textarea class="form-control tp_deskripsi_input" name="tp_deskripsi[]" rows="2" placeholder="Tuliskan rumusan kompetensi tujuan pembelajaran..." required></textarea>
                    </div>
                `);
            });

            $(document).on('click', '.removeTpRowBtn', function() {
                $(this).closest('.tp-input-row').remove();
                // Re-index display numbers
                $('.tp-input-row').each(function(idx) {
                    $(this).find('span.text-uppercase').html(`<i class="fas fa-tag text-primary mr-1"></i> Tujuan Pembelajaran #${idx + 1}`);
                });
                tpRowCount = $('.tp-input-row').length;
            });

            // Submit Form Tambah TP Manual
            $('#addTpForm').on('submit', function(e) {
                e.preventDefault();
                let descriptions = [];
                $('.tp_deskripsi_input').each(function() {
                    let val = $(this).val().trim();
                    if (val) descriptions.push(val);
                });

                if (descriptions.length === 0) {
                    Swal.fire('Peringatan', 'Silakan isi setidaknya 1 Tujuan Pembelajaran.', 'warning');
                    return;
                }

                let submitBtn = $('#btnSubmitAddTp');
                let originalHtml = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: "{{ route('mastertp.store') }}",
                    method: "POST",
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: {
                        fst_id: activeFstId,
                        mapel_id: activeMapelId,
                        tp_deskripsi: descriptions
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Tujuan Pembelajaran berhasil disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#addTpModal').modal('hide');
                        if (tpTable) tpTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // ── Modal Edit TP Manual Handlers ────────────────────────────
            $(document).on('click', '.editTpBtn', function() {
                let id   = $(this).data('id');
                let desc = $(this).data('tp_deskripsi');

                $('#edit_tp_id').val(id);
                $('#edit_tp_deskripsi').val(desc);
                $('#editTpModal').modal('show');
            });

            $('#editTpForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#edit_tp_id').val();
                let desc = $('#edit_tp_deskripsi').val().trim();

                if (!desc) {
                    Swal.fire('Peringatan', 'Deskripsi Tujuan Pembelajaran tidak boleh kosong.', 'warning');
                    return;
                }

                let submitBtn = $('#btnSubmitEditTp');
                let originalHtml = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: `/mastertp/${id}`,
                    method: "PUT",
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: {
                        mapel_id: activeMapelId,
                        tp_deskripsi: [desc]
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Tujuan Pembelajaran berhasil diperbarui.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#editTpModal').modal('hide');
                        if (tpTable) tpTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal memperbarui Tujuan Pembelajaran.', 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // ── Delete TP Action Handler ─────────────────────────────────
            $(document).on('click', '.delTpBtn', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Hapus Tujuan Pembelajaran?',
                    text: 'Data TP yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/mastertp/${id}`,
                            method: "DELETE",
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: res.message || 'Tujuan Pembelajaran berhasil dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                if (tpTable) tpTable.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus Tujuan Pembelajaran.', 'error');
                            }
                        });
                    }
                });
            });

            // ── Modal Import Excel Handlers ──────────────────────────────
            $('#btnOpenImportModal').click(function() {
                if (!activeMapelId || !activeFstId) {
                    Swal.fire('Perhatian', 'Silahkan pilih Periode Semester dan Mata Pelajaran terlebih dahulu.', 'warning');
                    return;
                }
                $('#import_file').val(null);
                $('#importTpModal').modal('show');
            });

            $('#importTpForm').on('submit', function(e) {
                e.preventDefault();
                let file = $('#import_file')[0].files[0];
                if (!file) {
                    Swal.fire('Peringatan', 'Silakan pilih berkas Excel terlebih dahulu.', 'warning');
                    return;
                }

                let formData = new FormData(this);
                formData.append('mapel_id', activeMapelId);
                formData.append('fst_id', activeFstId);

                let submitBtn = $('#btnSubmitImport');
                let originalHtml = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Import...');

                $.ajax({
                    url: "{{ route('mastertp.import') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Berhasil!',
                            text: res.message,
                            confirmButtonColor: '#28a745'
                        });
                        $('#importTpModal').modal('hide');
                        if (tpTable) tpTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal Import!', xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses file Excel.', 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

        });
    </script>
@endsection
