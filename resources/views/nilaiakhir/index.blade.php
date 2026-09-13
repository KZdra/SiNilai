@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">
                        <i class="fas fa-file-signature text-primary mr-2"></i>{{ __('Nilai Akhir & Rapor') }}
                    </h1>
                    <p class="text-muted small mb-0 mt-1">Rekapitulasi capaian nilai akhir semester, pengaturan rapor resmi, cetak dokumen, dan ekspor leger nilai.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item">Penilaian & Rapor</li>
                        <li class="breadcrumb-item active">Nilai Akhir</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- ── 1. UNIFIED FILTER CARD ──────────────────────────────── -->
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
                <div class="card-body p-3">
                    <div class="row align-items-end">
                        <!-- Filter Kelas -->
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label for="class_id" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-chalkboard text-primary mr-1"></i> Pilih Kelas
                            </label>
                            @if ($className)
                                <input type="hidden" id="class_id" value="{{ Auth::user()->class_id }}">
                                <input type="text" class="form-control font-weight-bold bg-light" value="{{ $className }}" readonly>
                            @else
                                <select name="class_id" id="class_id" class="form-control font-weight-bold">
                                    <option value="" selected disabled>-- Pilih Kelas --</option>
                                    @include('partials.select_class_options')
                                </select>
                            @endif
                        </div>

                        <!-- Filter Periode -->
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label for="fst_id" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-calendar-alt text-success mr-1"></i> Fase / Semester / Tahun Ajaran
                            </label>
                            <select name="fst_id" id="fst_id" class="form-control font-weight-bold">
                                <option value="" selected disabled>-- Pilih Periode Semester --</option>
                                @include('partials.select_fst_options')
                            </select>
                        </div>

                        <!-- Tombol Aksi Filter -->
                        <div class="col-md-2 text-right">
                            <button type="button" class="btn btn-primary btn-block font-weight-bold shadow-sm" id="btnLoadData">
                                <i class="fas fa-sync-alt mr-1"></i> Muat Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── 2. EXECUTIVE SUMMARY METRICS ────────────────────────── -->
            <div id="summaryCards" style="display: none;">
                <div class="row mb-3">
                    <!-- Total Siswa -->
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card border-0 shadow-sm mb-0 h-100" style="border-radius: 10px; border-left: 4px solid #007bff !important;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small text-uppercase font-weight-bold">Total Siswa</div>
                                    <div class="h4 mb-0 font-weight-bold text-dark" id="statTotalStudents">0 Siswa</div>
                                </div>
                                <div class="rounded-circle bg-light p-3 text-primary">
                                    <i class="fas fa-users fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rata-rata Kelas -->
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card border-0 shadow-sm mb-0 h-100" style="border-radius: 10px; border-left: 4px solid #28a745 !important;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small text-uppercase font-weight-bold">Rata-rata Kelas</div>
                                    <div class="h4 mb-0 font-weight-bold text-dark" id="statClassAvg">0.00</div>
                                </div>
                                <div class="rounded-circle bg-light p-3 text-success">
                                    <i class="fas fa-chart-line fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nilai Tertinggi -->
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card border-0 shadow-sm mb-0 h-100" style="border-radius: 10px; border-left: 4px solid #ffc107 !important;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small text-uppercase font-weight-bold">Nilai Tertinggi</div>
                                    <div class="h4 mb-0 font-weight-bold text-dark" id="statMaxScore">0.00</div>
                                    <div class="small text-truncate text-muted" id="statMaxStudent" style="max-width: 130px;">-</div>
                                </div>
                                <div class="rounded-circle bg-light p-3 text-warning">
                                    <i class="fas fa-trophy fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tgl Cetak Rapor -->
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card border-0 shadow-sm mb-0 h-100" style="border-radius: 10px; border-left: 4px solid #17a2b8 !important;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small text-uppercase font-weight-bold">Tgl Cetak Rapor</div>
                                    <div class="small font-weight-bold text-dark mb-1" id="statPrintDate">Belum diatur</div>
                                    <a href="#" class="small text-info font-weight-bold" data-toggle="modal" data-target="#SettingRaportCenter">
                                        <i class="fas fa-edit mr-1"></i>Ubah Tanggal
                                    </a>
                                </div>
                                <div class="rounded-circle bg-light p-3 text-info">
                                    <i class="fas fa-calendar-check fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── 3. DATA TABLE CARD & ACTION TOOLBAR ──────────────────── -->
            <div class="card shadow-sm border-0 mb-4" id="resultTableCard" style="display: none; border-radius: 10px;">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
                    <div>
                        <span class="badge badge-primary px-3 py-2 mr-1 font-weight-bold shadow-sm" id="ClassSelBadge">-</span>
                        <span class="badge badge-light border text-dark px-3 py-2 font-weight-bold shadow-sm" id="FstSelBadge">-</span>
                    </div>

                    <!-- Action Toolbar -->
                    <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                        <button class="btn btn-outline-dark btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#SettingRaportCenter">
                            <i class="fas fa-cog mr-1"></i> Pengaturan Rapor
                        </button>

                        <button class="btn btn-warning btn-sm font-weight-bold shadow-sm text-dark" id="btnExportServer">
                            <i class="fas fa-server mr-1"></i> Export Rapor ke Server
                        </button>

                        <button class="btn btn-info btn-sm font-weight-bold shadow-sm" id="expBtn4">
                            <i class="fas fa-print mr-1"></i> Print Rapor Berurutan
                        </button>

                        <!-- Dropdown Export Excel -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle font-weight-bold shadow-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 8px;">
                                <a class="dropdown-item py-2" id="expBtnLeger" href="#">
                                    <i class="fas fa-table text-success mr-2"></i> Download Leger Lengkap (.xlsx)
                                </a>
                                <a class="dropdown-item py-2" id="expBtn1" href="#">
                                    <i class="fas fa-file-alt text-primary mr-2"></i> Rekap Nilai Akhir Siswa (.xlsx)
                                </a>
                                <a class="dropdown-item py-2" id="expBtn2" href="#">
                                    <i class="fas fa-medal text-warning mr-2"></i> Rekap Ranking Siswa (.xlsx)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered w-100" id="valueTable">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th>Siswa</th>
                                    <th style="width: 120px;" class="text-center">Kelas</th>
                                    <th style="width: 200px;" class="text-center">Rata-rata Nilai</th>
                                    <th style="width: 170px;" class="text-center">Aksi Dokumen</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── MODAL: PENGATURAN RAPORT ─────────────────────────────── -->
            <div class="modal fade" id="SettingRaportCenter" tabindex="-1" role="dialog" aria-labelledby="SettingRaportCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                        <div class="modal-header bg-primary text-white py-3">
                            <h5 class="modal-title font-weight-bold" id="SettingRaportLongTitle">
                                <i class="fas fa-sliders-h mr-2"></i>Pengaturan Titimangsa Rapor
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="SetRaportForm">
                            <div class="modal-body p-4">
                                <div class="form-group mb-3">
                                    <label for="tanggalPrint" class="font-weight-bold text-dark">
                                        <i class="fas fa-calendar-alt text-primary mr-1"></i> Tanggal Cetak Rapor (Titimangsa)
                                    </label>
                                    <input type="date" class="form-control" id="tanggalPrint" required />
                                    <small class="form-text text-muted">Tanggal ini akan dicetak pada lembar tanda tangan rapor & identitas siswa.</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="boxKeputusan" class="font-weight-bold text-dark">
                                        <i class="fas fa-clipboard-check text-success mr-1"></i> Catatan Keputusan / Kenaikan
                                    </label>
                                    <textarea class="form-control" id="boxKeputusan" rows="3" placeholder="Contoh: Naik ke kelas XI / Lulus. (Bila tidak ada keputusan khusus, boleh dikosongkan)"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer bg-light py-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3">
                                    <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ── MODAL: PRINT RAPORT BERURUTAN ───────────────────────── -->
            <div class="modal fade" id="BulkPrintModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                        <div class="modal-header bg-info text-white py-3">
                            <h5 class="modal-title font-weight-bold">
                                <i class="fas fa-print mr-2"></i>Print Raport Berurutan
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <h6 id="bpProgress" class="text-secondary font-weight-bold mb-1">Siswa 1 dari X</h6>
                            <h3 id="bpStudentName" class="text-primary font-weight-bold my-2">-</h3>

                            <div class="my-3 p-3 bg-light rounded border text-left">
                                <label class="font-weight-bold text-dark mb-2 d-block small text-uppercase">Pilihan Dokumen Yang Dicetak:</label>
                                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                    <label class="btn btn-outline-primary btn-sm active" style="flex: 1;">
                                        <input type="radio" name="bp_doc_type" value="nilai" checked> Nilai Saja
                                    </label>
                                    <label class="btn btn-outline-primary btn-sm" style="flex: 1;">
                                        <input type="radio" name="bp_doc_type" value="cover"> Cover Saja
                                    </label>
                                    <label class="btn btn-outline-primary btn-sm" style="flex: 1;">
                                        <input type="radio" name="bp_doc_type" value="all"> Lengkap (Semua)
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center mt-4" style="gap: 12px;">
                                <button class="btn btn-secondary px-3" id="bpPrevBtn"><i class="fas fa-chevron-left mr-1"></i> Sebelumnya</button>
                                <button class="btn btn-success px-3 font-weight-bold shadow-sm" id="bpPrintBtn"><i class="fas fa-print mr-1"></i> Print Sekarang</button>
                                <button class="btn btn-primary px-3" id="bpNextBtn">Berikutnya <i class="fas fa-chevron-right ml-1"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── MODAL: EXPORT RAPOR KE SERVER (ARSIP RAPORT) ────────── -->
            <div class="modal fade" id="ExportServerModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                        <div class="modal-header bg-warning text-dark py-3">
                            <h5 class="modal-title font-weight-bold">
                                <i class="fas fa-server mr-2"></i>Export Rapor ke Server Arsip
                            </h5>
                            <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body py-4">
                            <div class="alert alert-info border-0 shadow-sm small mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Fitur ini akan meng-generate seluruh PDF rapor siswa pada kelas terpilih dan menyimpannya langsung ke server storage (<code>storage/app/public/raport/...</code>). Administrator dapat melihat dan mengunduhnya kapan saja melalui menu <strong>Arsip Raport</strong>.
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-2 d-block small text-uppercase">Pilihan Dokumen Yang Disimpan:</label>
                                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                    <label class="btn btn-outline-primary btn-sm active" style="flex: 1;">
                                        <input type="radio" name="server_doc_type" value="all" checked> Lengkap (Semua)
                                    </label>
                                    <label class="btn btn-outline-primary btn-sm" style="flex: 1;">
                                        <input type="radio" name="server_doc_type" value="nilai"> Nilai Saja
                                    </label>
                                    <label class="btn btn-outline-primary btn-sm" style="flex: 1;">
                                        <input type="radio" name="server_doc_type" value="cover"> Cover Saja
                                    </label>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded border small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Kelas:</span>
                                    <strong id="modalServerClass" class="text-dark">-</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Periode:</span>
                                    <strong id="modalServerFst" class="text-dark">-</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Siswa:</span>
                                    <strong id="modalServerTotal" class="text-primary">-</strong>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm text-dark" id="btnConfirmExportServer">
                                <i class="fas fa-cloud-upload-alt mr-1"></i> Mulai Export ke Server
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            let class_id = null;
            let class_name = '';
            let fst_id = null;
            let fst_name = '';

            // Inisialisasi tanggal & keputusan dari sessionStorage
            let tgl_print = sessionStorage.getItem('tgl_print') || '';
            let keputusan = sessionStorage.getItem('keputusan') || '';

            if (tgl_print) {
                $('#tanggalPrint').val(tgl_print);
                $('#statPrintDate').text(formatDateIndo(tgl_print));
            } else {
                let today = new Date().toISOString().split('T')[0];
                $('#tanggalPrint').val(today);
                sessionStorage.setItem('tgl_print', today);
                $('#statPrintDate').text(formatDateIndo(today));
            }
            if (keputusan) {
                $('#boxKeputusan').val(keputusan);
            }

            function formatDateIndo(dateStr) {
                if (!dateStr) return '-';
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                let parts = dateStr.split('-');
                if (parts.length === 3) {
                    let day = parseInt(parts[2], 10);
                    let month = months[parseInt(parts[1], 10) - 1] || parts[1];
                    let year = parts[0];
                    return `${day} ${month} ${year}`;
                }
                return dateStr;
            }

            // Trigger load data saat tombol Muat Data diklik atau saat dropdown dipilih
            $('#btnLoadData').click(function() {
                loadReportData();
            });

            $('#class_id, #fst_id').on('change', function() {
                if ($('#class_id').val() && $('#fst_id').val()) {
                    loadReportData();
                }
            });

            function loadReportData() {
                class_id = $('#class_id').val();
                class_name = $('#class_id option:selected').text().trim() || $('#class_id').val();
                fst_id = $('#fst_id').val();
                fst_name = $('#fst_id option:selected').text().trim();

                if (!class_id) {
                    SwalHelper.showError('Silakan pilih Kelas terlebih dahulu.');
                    return;
                }
                if (!fst_id) {
                    SwalHelper.showError('Silakan pilih Periode Semester / Tahun Ajaran terlebih dahulu.');
                    return;
                }

                $('#ClassSelBadge').html(`<i class="fas fa-chalkboard mr-1"></i> Kelas: ${class_name}`);
                $('#FstSelBadge').html(`<i class="fas fa-calendar-alt mr-1"></i> ${fst_name}`);

                if ($.fn.DataTable.isDataTable('#valueTable')) {
                    $('#valueTable').DataTable().destroy();
                }

                $('#valueTable').DataTable({
                    paging: true,
                    pageLength: 25,
                    responsive: true,
                    ordering: false,
                    ajax: {
                        url: "{{ route('nilaiakhir.getAllStudentAVG') }}",
                        type: "GET",
                        data: {
                            class_id: class_id,
                            fst_id: fst_id
                        },
                        dataSrc: function(json) {
                            let students = json.data || [];
                            updateSummaryMetrics(students);
                            return students;
                        },
                        error: function() {
                            SwalHelper.showError('Gagal memuat data nilai akhir.');
                        }
                    },
                    columns: [
                        {
                            data: null,
                            className: 'text-center font-weight-bold text-muted',
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        },
                        {
                            data: "student_name",
                            render: function(data, type, row) {
                                let name = data || 'Siswa';
                                let initials = name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
                                let nis = row.student_nis ? `NIS: ${row.student_nis}` : '';
                                let nisn = row.student_nisn ? `NISN: ${row.student_nisn}` : '';
                                let sub = [nis, nisn].filter(Boolean).join(' • ');

                                return `
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle mr-2 font-weight-bold text-white shadow-sm" style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #06b6d4); display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                                            ${initials}
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark">${name}</div>
                                            ${sub ? `<div class="small text-muted font-italic">${sub}</div>` : ''}
                                        </div>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: "class_name",
                            className: 'text-center',
                            render: function(data) {
                                return `<span class="badge badge-info px-2 py-1">${data || class_name}</span>`;
                            }
                        },
                        {
                            data: "avg_nilai_semua_mapel",
                            className: 'text-center',
                            render: function(data) {
                                let num = parseFloat(data) || 0;
                                let badgeClass = 'badge-danger';
                                let icon = 'fa-times-circle';
                                let label = 'Perlu Bimbingan';

                                if (num >= 75) {
                                    badgeClass = 'badge-success';
                                    icon = 'fa-check-circle';
                                    label = 'Tuntas';
                                } else if (num >= 60) {
                                    badgeClass = 'badge-warning text-dark';
                                    icon = 'fa-exclamation-circle';
                                    label = 'Cukup';
                                }

                                return `
                                    <div class="text-center">
                                        <span class="badge ${badgeClass} px-3 py-2 shadow-sm font-weight-bold" style="font-size: 13px; border-radius: 20px;">
                                            <i class="fas ${icon} mr-1"></i> ${num.toFixed(2)}
                                            <span class="badge badge-light ml-1 font-weight-normal text-dark">${label}</span>
                                        </span>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            className: 'text-center',
                            render: function(data, type, row) {
                                let exportUrl = "{!! route('nilaiakhir.detailNilaiAkhir', ['student_id' => '__STUDENT_ID__', 'class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']) !!}"
                                    .replace('__STUDENT_ID__', row.student_id).replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);

                                let exportUrl2 = "{!! route('nilaiakhir.print', ['student_id' => '__STUDENT_ID__', 'class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']) !!}"
                                    .replace('__STUDENT_ID__', row.student_id).replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);

                                return `
                                    <div class="btn-group btn-group-sm w-100">
                                        <button type="button" class="btn btn-outline-primary dropdown-toggle font-weight-bold shadow-sm"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-print mr-1"></i> Cetak / Aksi
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 8px; font-size: 13px;">
                                            <a class="dropdown-item btn-detail py-2" data-url="${exportUrl}" href="#">
                                                <i class="fas fa-eye text-primary mr-2"></i> Detail Capaian Nilai
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <div class="dropdown-header font-weight-bold text-uppercase small text-muted">Format Cetak Dokumen</div>
                                            <a class="dropdown-item btn-print py-2" data-url="${exportUrl2}" data-type="nilai" href="#">
                                                <i class="fas fa-file-alt text-success mr-2"></i> Cetak Nilai Raport Saja
                                            </a>
                                            <a class="dropdown-item btn-print py-2" data-url="${exportUrl2}" data-type="cover" href="#">
                                                <i class="fas fa-id-card text-info mr-2"></i> Cetak Cover & Identitas Saja
                                            </a>
                                            <a class="dropdown-item btn-print py-2 font-weight-bold text-dark" data-url="${exportUrl2}" data-type="all" href="#">
                                                <i class="fas fa-book text-warning mr-2"></i> Cetak Raport Lengkap (Cover + Nilai)
                                            </a>
                                        </div>
                                    </div>
                                `;
                            }
                        }
                    ]
                });

                $('#summaryCards').slideDown(300);
                $('#resultTableCard').slideDown(300);
            }

            function updateSummaryMetrics(students) {
                let total = students.length;
                let sum = 0;
                let maxScore = 0;
                let maxStudent = '-';

                students.forEach(s => {
                    let score = parseFloat(s.avg_nilai_semua_mapel) || 0;
                    sum += score;
                    if (score > maxScore) {
                        maxScore = score;
                        maxStudent = s.student_name;
                    }
                });

                let avg = total > 0 ? (sum / total).toFixed(2) : '0.00';
                $('#statTotalStudents').text(`${total} Siswa`);
                $('#statClassAvg').text(avg);
                $('#statMaxScore').text(maxScore > 0 ? maxScore.toFixed(2) : '0.00');
                $('#statMaxStudent').text(maxStudent !== '-' ? maxStudent : 'Belum ada nilai');

                let curTgl = sessionStorage.getItem('tgl_print') || '';
                $('#statPrintDate').text(formatDateIndo(curTgl));
            }

            // Export PDF Single Student Action
            $(document).on("click", ".btn-print", function(e) {
                e.preventDefault();
                let url = $(this).data("url");
                let type = $(this).data("type") || 'nilai';
                let tgl = sessionStorage.getItem('tgl_print') ?? '';
                let kpt = sessionStorage.getItem('keputusan') ?? '';

                url += `&type=${encodeURIComponent(type)}&tgl_print=${encodeURIComponent(tgl)}&keputusan=${encodeURIComponent(kpt)}`;
                exportpdf(url);
            });

            // Detail Nilai Single Student Action
            $(document).on("click", ".btn-detail", function(e) {
                e.preventDefault();
                let url = $(this).data("url");
                let tgl = sessionStorage.getItem('tgl_print') ?? '';
                url += `&tgl_print=${encodeURIComponent(tgl)}`;
                window.location.href = url;
            });

            // Form Pengaturan Raport Submit
            $('#SetRaportForm').submit(function(e) {
                e.preventDefault();
                let newTgl = $('#tanggalPrint').val();
                let newKpt = $('#boxKeputusan').val();
                sessionStorage.setItem('tgl_print', newTgl);
                sessionStorage.setItem('keputusan', newKpt);

                $('#statPrintDate').text(formatDateIndo(newTgl));
                SwalHelper.showSuccess('Pengaturan Titimangsa Raport Berhasil Disimpan!');
                $('#SettingRaportCenter').modal('hide');
            });

            // Export Excel Rekap Nilai
            $('#expBtn1').on('click', function(e) {
                e.preventDefault();
                if (!class_id || !fst_id) {
                    SwalHelper.showError('Pilih Kelas dan Periode terlebih dahulu.');
                    return;
                }

                let excelUrl = @json(route('nilaiakhir.exportexcel', ['class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']))
                    .replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);

                downloadBlobFile(excelUrl, `Nilai_Akhir_${class_name}.xlsx`);
            });

            // Export Excel Ranking
            $('#expBtn2').on('click', function(e) {
                e.preventDefault();
                if (!class_id || !fst_id) {
                    SwalHelper.showError('Pilih Kelas dan Periode terlebih dahulu.');
                    return;
                }

                let excelUrl = @json(route('nilaiakhir.exportranking', ['class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']))
                    .replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);

                downloadBlobFile(excelUrl, `Ranking_Siswa_${class_name}.xlsx`);
            });

            // Export Excel Leger Lengkap
            $('#expBtnLeger').on('click', function(e) {
                e.preventDefault();
                if (!class_id || !fst_id) {
                    SwalHelper.showError('Pilih Kelas dan Periode terlebih dahulu.');
                    return;
                }

                let legerUrl = @json(route('nilaiakhir.exportleger', ['class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']))
                    .replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);

                Swal.fire({
                    title: 'Membuat Leger Nilai...',
                    html: 'Sedang mengolah seluruh komponen nilai siswa sekelas.<br>Mohon tunggu sebentar...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: legerUrl,
                    method: 'GET',
                    xhrFields: { responseType: 'blob' },
                    success: function(data, _, xhr) {
                        let contentDisposition = xhr.getResponseHeader('Content-Disposition');
                        let fileName = `Leger_Nilai_${class_name}.xlsx`;

                        if (contentDisposition) {
                            let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
                            if (matches != null && matches[1]) {
                                fileName = matches[1].replace(/['"]/g, '');
                            }
                        }

                        let a = document.createElement('a');
                        let blobUrl = window.URL.createObjectURL(data);
                        a.href = blobUrl;
                        a.download = fileName;
                        document.body.append(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(blobUrl);
                        Swal.close();
                        SwalHelper.showSuccess('Leger Nilai Berhasil Diunduh!');
                    },
                    error: function() {
                        Swal.close();
                        SwalHelper.showError('Gagal mendownload Leger Nilai.');
                    }
                });
            });

            function downloadBlobFile(url, defaultFileName) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    xhrFields: { responseType: 'blob' },
                    success: function(data, _, xhr) {
                        let contentDisposition = xhr.getResponseHeader('Content-Disposition');
                        let fileName = defaultFileName;

                        if (contentDisposition) {
                            let matches = /filename="([^"]*)"/.exec(contentDisposition);
                            if (matches != null && matches[1]) {
                                fileName = matches[1];
                            }
                        }

                        let a = document.createElement('a');
                        let blobUrl = window.URL.createObjectURL(data);
                        a.href = blobUrl;
                        a.download = fileName;
                        document.body.append(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(blobUrl);
                        SwalHelper.showSuccess('Berkas berhasil diunduh!');
                    },
                    error: function() {
                        SwalHelper.showError('Gagal mengunduh berkas Excel.');
                    }
                });
            }

            // Export PDF Helper
            function exportpdf(url) {
                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    let newWindow = window.open(data.pdf_url, '_blank');
                    if (newWindow) {
                        setTimeout(() => newWindow.print(), 1000);
                    }
                })
                .catch(err => {
                    SwalHelper.showError('Gagal memuat dokumen PDF.');
                });
            }

            // ── BULK PRINT BERURUTAN ──────────────────────────────────
            let currentPrintIndex = 0;
            let tableData = [];

            $('#expBtn4').click(function() {
                let dt = $('#valueTable').DataTable();
                tableData = dt.rows().data().toArray();
                if (tableData.length === 0) {
                    SwalHelper.showError('Tidak ada data siswa untuk dicetak.');
                    return;
                }
                currentPrintIndex = 0;
                updateBulkPrintUI();
                $('#BulkPrintModal').modal('show');
            });

            function updateBulkPrintUI() {
                if (currentPrintIndex < 0) currentPrintIndex = 0;
                if (currentPrintIndex >= tableData.length) currentPrintIndex = tableData.length - 1;

                let student = tableData[currentPrintIndex];
                $('#bpProgress').text(`Siswa ${currentPrintIndex + 1} dari ${tableData.length}`);
                $('#bpStudentName').text(student.student_name);

                $('#bpPrevBtn').prop('disabled', currentPrintIndex === 0);
                $('#bpNextBtn').prop('disabled', currentPrintIndex === tableData.length - 1);
            }

            $('#bpPrevBtn').click(function() {
                currentPrintIndex--;
                updateBulkPrintUI();
            });

            $('#bpNextBtn').click(function() {
                currentPrintIndex++;
                updateBulkPrintUI();
            });

            $('#bpPrintBtn').click(function() {
                let student = tableData[currentPrintIndex];
                let tgl = sessionStorage.getItem('tgl_print') ?? '';
                let kpt = sessionStorage.getItem('keputusan') ?? '';
                let docType = $('input[name="bp_doc_type"]:checked').val() || 'nilai';

                let exportUrl2 = "{!! route('nilaiakhir.print', ['student_id' => '__STUDENT_ID__', 'class_id' => '__VALUE_ID__', 'fst_id' => '__FST_ID__']) !!}"
                    .replace('__STUDENT_ID__', student.student_id).replace('__VALUE_ID__', class_id).replace('__FST_ID__', fst_id);
                exportUrl2 += `&type=${encodeURIComponent(docType)}&tgl_print=${encodeURIComponent(tgl)}&keputusan=${encodeURIComponent(kpt)}`;

                let btn = $(this);
                let originalHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyiapkan PDF...');

                fetch(exportUrl2, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    btn.prop('disabled', false).html(originalHtml);
                    let newWindow = window.open(data.pdf_url, '_blank');
                    if (newWindow) {
                        setTimeout(() => newWindow.print(), 1000);
                    }

                    // Otomatis lompat ke urutan siswa berikutnya
                    if (currentPrintIndex < tableData.length - 1) {
                        currentPrintIndex++;
                        updateBulkPrintUI();
                    }
                })
                .catch(err => {
                    btn.prop('disabled', false).html(originalHtml);
                    SwalHelper.showError('Gagal menyiapkan PDF.');
                });
            });

            // ── EXPORT RAPOR KE SERVER (ARSIP RAPORT) ─────────────────
            $('#btnExportServer').click(function() {
                if (!class_id || !fst_id) {
                    SwalHelper.showError('Pilih Kelas dan Periode terlebih dahulu.');
                    return;
                }

                let dt = $('#valueTable').DataTable();
                let total = dt.rows().data().length;
                if (total === 0) {
                    SwalHelper.showError('Tidak ada data siswa untuk diekspor.');
                    return;
                }

                $('#modalServerClass').text(class_name);
                $('#modalServerFst').text(fst_name);
                $('#modalServerTotal').text(`${total} Siswa`);
                $('#ExportServerModal').modal('show');
            });

            $('#btnConfirmExportServer').click(function() {
                let docType = $('input[name="server_doc_type"]:checked').val() || 'all';
                let tgl = sessionStorage.getItem('tgl_print') ?? '';
                let kpt = sessionStorage.getItem('keputusan') ?? '';

                $('#ExportServerModal').modal('hide');

                Swal.fire({
                    title: 'Meng-export Rapor ke Server...',
                    html: 'Sedang membuat PDF rapor seluruh siswa dan menyimpannya ke arsip server.<br>Mohon tunggu sebentar...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('nilaiakhir.exportserver') }}",
                    method: "POST",
                    data: {
                        class_id: class_id,
                        fst_id: fst_id,
                        type: docType,
                        tgl_print: tgl,
                        keputusan: kpt,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: `${res.message}<br><small class="text-muted">Berkas tersimpan di folder: <code>${res.folder}</code></small>`,
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-archive mr-1"></i> Buka Arsip Raport',
                            cancelButtonText: 'Tutup',
                            confirmButtonColor: '#28a745'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open("{{ route('raport_explorer.index') }}", '_blank');
                            }
                        });
                    },
                    error: function(xhr) {
                        let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat mengekspor ke server.';
                        SwalHelper.showError(msg);
                    }
                });
            });

        });
    </script>
@endsection
