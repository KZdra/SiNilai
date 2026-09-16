@extends('layouts.app')

@section('styles')
<style>
    .class-checkbox-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }
    .class-checkbox-card:hover {
        border-color: #3b82f6;
        background: #f8fafc;
    }
    .class-checkbox-card.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .console-log-box {
        background-color: #0f172a;
        color: #f8fafc;
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 0.82rem;
        border-radius: 8px;
        padding: 12px 16px;
        max-height: 280px;
        overflow-y: auto;
        white-space: pre-wrap;
    }
    .console-log-box .log-time { color: #94a3b8; }
    .console-log-box .log-success { color: #4ade80; }
    .console-log-box .log-info { color: #38bdf8; }
    .console-log-box .log-warn { color: #facc15; }
    .console-log-box .log-error { color: #f87171; }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-cloud-upload-alt text-warning mr-2"></i>Export Massal Rapor ke Server
                </h1>
                <p class="text-muted small mb-0">Hasilkan dan simpan seluruh berkas PDF rapor siswa ke server storage secara bertahap tanpa risiko batas waktu (timeout).</p>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('raport_explorer.index') }}">Arsip Raport</a></li>
                    <li class="breadcrumb-item active">Export Massal</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <!-- Banner Panduan Admin -->
        <div class="alert alert-info border-0 shadow-sm mb-3" style="border-radius: 8px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-server fa-2x mr-3 text-info"></i>
                <div class="small">
                    <strong>Fungsi Khusus Administrator:</strong>
                    Fitur ini mempersiapkan (pre-render) berkas PDF rapor ke direktori arsip server (<code>storage/app/public/raport/...</code>) sebelum hari pembagian rapor. Berkas yang tersimpan di server dapat langsung dibuka seketika di <strong>Penjelajah Arsip (jsTree)</strong> dan memudahkan proses cetak tanpa membebani server saat puncak pembagian rapor.
                </div>
            </div>
        </div>

        <div class="row">
            <!-- ── FORM KONFIGURASI EXPORT ─────────────────────────────── -->
            <div class="col-lg-5 col-md-12 mb-3">
                <div class="card card-outline card-warning shadow-sm" style="border-radius: 10px;">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-sliders-h text-warning mr-1"></i> Parameter Export Rapor
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <form id="bulkExportForm">
                            <!-- Periode FST -->
                            <div class="form-group mb-3">
                                <label for="bulk_fst_id" class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-calendar-alt text-success mr-1"></i> 1. Periode / Semester
                                </label>
                                <select id="bulk_fst_id" class="form-control font-weight-bold" required>
                                    <option value="" selected disabled>-- Pilih Periode Semester --</option>
                                    @foreach ($fstList as $fst)
                                        <option value="{{ $fst->id }}">
                                            TA {{ $fst->tahun_ajaran }} - Semester {{ $fst->semester }} (Fase {{ $fst->fase }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pilihan Lembar Dokumen -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-file-pdf text-danger mr-1"></i> 2. Pilihan Lembar Dokumen
                                </label>
                                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                    <label class="btn btn-outline-primary btn-sm active" style="flex: 1;">
                                        <input type="radio" name="bulk_doc_type" value="all" checked> Lengkap (Semua)
                                    </label>
                                    <label class="btn btn-outline-primary btn-sm" style="flex: 1;">
                                        <input type="radio" name="bulk_doc_type" value="nilai"> Nilai Saja
                                    </label>
                                    <label class="btn btn-outline-primary btn-sm" style="flex: 1;">
                                        <input type="radio" name="bulk_doc_type" value="cover"> Cover Saja
                                    </label>
                                </div>
                            </div>

                            <!-- Tanggal Cetak Titimangsa -->
                            <div class="form-group mb-3">
                                <label for="bulk_tgl_print" class="font-weight-bold small text-muted text-uppercase mb-1">
                                    <i class="fas fa-clock text-primary mr-1"></i> 3. Tanggal Cetak (Titimangsa)
                                </label>
                                <input type="date" class="form-control" id="bulk_tgl_print" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- Cakupan Kelas -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase mb-2 d-block">
                                    <i class="fas fa-chalkboard text-info mr-1"></i> 4. Cakupan Kelas / Rombel
                                </label>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="scope_all" name="bulk_scope" value="all" class="custom-control-input" checked onchange="toggleScope(this.value)">
                                    <label class="custom-control-label font-weight-bold text-dark" for="scope_all" style="cursor: pointer;">
                                        Seluruh Kelas (Semua Rombel Sekolah - {{ count($classes) }} Kelas)
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="scope_custom" name="bulk_scope" value="custom" class="custom-control-input" onchange="toggleScope(this.value)">
                                    <label class="custom-control-label font-weight-bold text-dark" for="scope_custom" style="cursor: pointer;">
                                        Pilih Kelas Tertentu
                                    </label>
                                </div>
                            </div>

                            <!-- Container Checklist Kelas (Custom) -->
                            <div id="customClassesContainer" class="d-none border rounded p-2 mb-3 bg-light" style="max-height: 220px; overflow-y: auto;">
                                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                    <span class="small font-weight-bold text-muted">Daftar Rombel (Centang Kelas):</span>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-outline-primary" id="btnSelectAllClasses" onclick="selectAllClasses(true)">Pilih Semua</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ml-1" id="btnDeselectAllClasses" onclick="selectAllClasses(false)">Kosongkan</button>
                                    </div>
                                </div>
                                <div class="row no-gutters">
                                    @foreach ($classes as $c)
                                        <div class="col-6 p-1">
                                            <div class="class-checkbox-card" onclick="toggleCheckboxCard(this, event)">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input class-checkbox" id="chk_class_{{ $c->id }}" value="{{ $c->id }}" data-name="{{ $c->class_name }}" onchange="this.closest('.class-checkbox-card').classList.toggle('selected', this.checked)">
                                                    <label class="custom-control-label font-weight-bold small text-dark" for="chk_class_{{ $c->id }}" style="cursor: pointer;">
                                                        {{ $c->class_name }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <button type="button" class="btn btn-warning btn-block font-weight-bold shadow-sm py-2" id="btnStartBulkExport">
                                <i class="fas fa-play mr-1"></i> Mulai Export Rapor ke Server
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ── PANEL MONITORING PROSES REAL-TIME ──────────────────── -->
            <div class="col-lg-7 col-md-12 mb-3">
                <div class="card card-outline card-primary shadow-sm h-100" style="border-radius: 10px;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-tasks text-primary mr-1"></i> Monitoring & Log Pemrosesan
                        </h5>
                        <span class="badge badge-light border" id="exportStatusBadge">Siap Dimulai</span>
                    </div>
                    <div class="card-body p-3">

                        <!-- Progress Bar Keseluruhan (Kelas) -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small font-weight-bold mb-1">
                                <span class="text-dark">
                                    <i class="fas fa-school text-primary mr-1"></i>
                                    Progress Rombel: <span id="labelClassProgress">0 / 0 Kelas</span>
                                </span>
                                <span id="percentClassProgress" class="text-primary font-weight-bold">0%</span>
                            </div>
                            <div class="progress" style="height: 14px; border-radius: 7px;">
                                <div class="progress-bar bg-primary progress-bar-striped" id="barClassProgress" style="width: 0%;"></div>
                            </div>
                        </div>

                        <!-- Progress Bar Kelas Aktif (Siswa) -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small font-weight-bold mb-1">
                                <span class="text-dark">
                                    <i class="fas fa-user-graduate text-success mr-1"></i>
                                    Siswa Kelas Aktif: <strong id="currentActiveClassName">-</strong> (<span id="labelStudentProgress">0 / 0</span>)
                                </span>
                                <span id="percentStudentProgress" class="text-success font-weight-bold">0%</span>
                            </div>
                            <div class="progress" style="height: 14px; border-radius: 7px;">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="barStudentProgress" style="width: 0%;"></div>
                            </div>
                        </div>

                        <!-- Execution Log Console -->
                        <label class="font-weight-bold small text-muted text-uppercase mb-1">
                            <i class="fas fa-terminal text-dark mr-1"></i> Log Eksekusi Server
                        </label>
                        <div class="console-log-box" id="consoleLog">
<span class="log-time">[SIAP]</span> Menunggu parameter konfigurasi diisi lalu klik tombol 'Mulai Export Rapor ke Server'.
                        </div>

                        <!-- Box Selesai -->
                        <div id="exportSummaryBox" class="alert alert-success mt-3 py-3 px-3 d-none shadow-sm" style="border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-success">
                                        <i class="fas fa-check-circle mr-1"></i> Export Massal Rapor Selesai!
                                    </h6>
                                    <span class="small text-dark" id="summaryText">-</span>
                                </div>
                                <div>
                                    <a href="{{ route('raport_explorer.index') }}" class="btn btn-primary btn-sm font-weight-bold px-3 shadow-sm">
                                        <i class="fas fa-folder-open mr-1"></i> Buka Penjelajah Arsip (jsTree)
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Global helper functions callable before module resolution
    window.toggleScope = function(val) {
        var box = document.getElementById('customClassesContainer');
        if (box) {
            if (val === 'custom') {
                box.classList.remove('d-none');
            } else {
                box.classList.add('d-none');
            }
        }
    };

    window.selectAllClasses = function(check) {
        document.querySelectorAll('.class-checkbox').forEach(function(el) {
            el.checked = check;
            var card = el.closest('.class-checkbox-card');
            if (card) {
                if (check) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }
        });
    };

    window.toggleCheckboxCard = function(card, event) {
        if (event && event.target && (event.target.tagName.toLowerCase() === 'input' || event.target.tagName.toLowerCase() === 'label')) {
            return;
        }
        var chk = card.querySelector('input[type="checkbox"]');
        if (chk) {
            chk.checked = !chk.checked;
            card.classList.toggle('selected', chk.checked);
        }
    };
</script>

<script type="module">
$(document).ready(function() {
    const allClasses = @json($classes);

    // Also support jQuery change listener
    $(document).on('change', 'input[name="bulk_scope"]', function() {
        window.toggleScope($(this).val());
    });

    // Checkbox styling
    $(document).on('change', '.class-checkbox', function() {
        $(this).closest('.class-checkbox-card').toggleClass('selected', $(this).is(':checked'));
    });

    function addLog(msg, type = 'info') {
        const time = new Date().toLocaleTimeString();
        let cls = 'log-info';
        if (type === 'success') cls = 'log-success';
        if (type === 'warn') cls = 'log-warn';
        if (type === 'error') cls = 'log-error';

        $('#consoleLog').append(`\n<span class="log-time">[${time}]</span> <span class="${cls}">${msg}</span>`);
        const el = document.getElementById('consoleLog');
        el.scrollTop = el.scrollHeight;
    }

    let isProcessing = false;

    $('#btnStartBulkExport').click(async function() {
        if (isProcessing) return;

        const fstId = $('#bulk_fst_id').val();
        if (!fstId) {
            SwalHelper.showError('Silakan pilih Periode / Semester terlebih dahulu.');
            return;
        }

        const docType = $('input[name="bulk_doc_type"]:checked').val() || 'all';
        const tglPrint = $('#bulk_tgl_print').val() || '';
        const scope = $('input[name="bulk_scope"]:checked').val();

        let targetClasses = [];
        if (scope === 'all') {
            targetClasses = allClasses.map(c => ({ id: c.id, name: c.class_name }));
        } else {
            $('.class-checkbox:checked').each(function() {
                targetClasses.push({
                    id: parseInt($(this).val()),
                    name: $(this).data('name')
                });
            });
        }

        if (targetClasses.length === 0) {
            SwalHelper.showError('Pilih minimal satu kelas untuk diekspor.');
            return;
        }

        const confirmRes = await Swal.fire({
            title: 'Konfirmasi Export Massal',
            html: `Akan meng-export rapor untuk <strong>${targetClasses.length} kelas</strong> terpilih ke server storage.<br>Proses ini akan berjalan otomatis tanpa membebani browser.<br><br>Lanjutkan?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Mulai Export',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ffc107'
        });

        if (!confirmRes.isConfirmed) return;

        // Start execution
        isProcessing = true;
        $('#btnStartBulkExport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sedang Memproses...');
        $('#exportStatusBadge').removeClass('badge-light badge-success badge-danger').addClass('badge-warning').text('Sedang Berjalan');
        $('#exportSummaryBox').addClass('d-none');
        $('#consoleLog').empty();

        addLog(`Memulai Export Massal Rapor: ${targetClasses.length} kelas terpilih.`, 'info');

        let totalClassesProcessed = 0;
        let grandTotalPdfGenerated = 0;
        const startTime = Date.now();

        for (let i = 0; i < targetClasses.length; i++) {
            const currentClass = targetClasses[i];
            $('#currentActiveClassName').text(currentClass.name);
            $('#labelClassProgress').text(`${i + 1} dari ${targetClasses.length} Kelas`);
            const classPercent = Math.round(((i) / targetClasses.length) * 100);
            $('#barClassProgress').css('width', classPercent + '%');
            $('#percentClassProgress').text(classPercent + '%');

            addLog(`>> [KELAS ${i + 1}/${targetClasses.length}] Memproses Kelas: ${currentClass.name}...`, 'info');

            // Chunk loop for active class
            let offset = 0;
            let limit = 4;
            let classDone = false;
            let classPdfCount = 0;

            while (!classDone) {
                try {
                    const response = await $.ajax({
                        url: "{{ route('raport_explorer.bulk_export_chunk') }}",
                        method: "POST",
                        data: {
                            class_id: currentClass.id,
                            fst_id: fstId,
                            type: docType,
                            tgl_print: tglPrint,
                            offset: offset,
                            limit: limit,
                            _token: "{{ csrf_token() }}"
                        }
                    });

                    if (response.status === 'empty') {
                        addLog(`Kelas ${currentClass.name} tidak memiliki siswa aktif (dilewati).`, 'warn');
                        classDone = true;
                        break;
                    }

                    classPdfCount += (response.processed || 0);
                    grandTotalPdfGenerated += (response.processed || 0);

                    // Update Student Progress
                    const nextOffset = response.next_offset || 0;
                    const totalStudents = response.total || 0;
                    const studentPct = response.percent || 0;

                    $('#labelStudentProgress').text(`${nextOffset} / ${totalStudents} Siswa`);
                    $('#barStudentProgress').css('width', studentPct + '%');
                    $('#percentStudentProgress').text(studentPct + '%');

                    if (response.items && response.items.length > 0) {
                        response.items.forEach(item => {
                            addLog(`  ✔ PDF Siswa '${item.nama}' tersimpan ke server.`, 'success');
                        });
                    }

                    if (response.is_complete) {
                        classDone = true;
                        addLog(`✔ Kelas ${currentClass.name} selesai tuntas (${classPdfCount} PDF rapor).`, 'success');
                    } else {
                        offset = nextOffset;
                    }
                } catch (xhr) {
                    const errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengekspor batch.';
                    addLog(`[ERROR] Gagal memproses batch kelas ${currentClass.name}: ${errMsg}`, 'error');
                    classDone = true; // Continue to next class
                }
            }

            totalClassesProcessed++;
        }

        // Complete overall progress
        $('#barClassProgress').css('width', '100%');
        $('#percentClassProgress').text('100%');
        $('#barStudentProgress').css('width', '100%').removeClass('progress-bar-animated');
        $('#percentStudentProgress').text('100%');

        const elapsedSec = Math.round((Date.now() - startTime) / 1000);
        addLog(`=== SEMUA SELESAI: ${targetClasses.length} kelas tuntas dalam ${elapsedSec} detik. Total ${grandTotalPdfGenerated} berkas PDF tersimpan di storage server. ===`, 'success');

        $('#exportStatusBadge').removeClass('badge-warning').addClass('badge-success').text('Selesai');
        $('#btnStartBulkExport').prop('disabled', false).html('<i class="fas fa-redo mr-1"></i> Mulai Export Ulang');
        $('#summaryText').html(`Berhasil meng-export <strong>${grandTotalPdfGenerated} berkas rapor</strong> dari <strong>${targetClasses.length} kelas</strong> dalam waktu ${elapsedSec} detik.`);
        $('#exportSummaryBox').removeClass('d-none');
        isProcessing = false;

        Swal.fire({
            icon: 'success',
            title: 'Export Massal Selesai!',
            html: `Berhasil memproses <strong>${targetClasses.length} kelas</strong> (${grandTotalPdfGenerated} berkas PDF rapor).<br>Seluruh dokumen siap diakses kapan saja.`,
            confirmButtonText: 'Buka Penjelajah Arsip',
            showCancelButton: true,
            cancelButtonText: 'Tutup'
        }).then((res) => {
            if (res.isConfirmed) {
                window.location.href = "{{ route('raport_explorer.index') }}";
            }
        });
    });
});
</script>
@endsection
