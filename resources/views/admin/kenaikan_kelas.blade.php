@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-level-up-alt text-primary mr-2"></i>Kenaikan Kelas & Kelulusan
                </h1>
                <p class="text-muted small mb-0">Manajemen rombongan belajar: proses kenaikan tingkat atau penetapan alumni massal.</p>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('class.index') }}">Data Kelas</a></li>
                    <li class="breadcrumb-item active">Kenaikan Kelas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- SOP & Panduan Kenaikan Tingkat Sekolah (Top-Down) -->
        <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #4f46e5 !important; background: #f8fafc;">
            <div class="card-body p-4">
                <div class="d-flex align-items-start">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 mt-1" style="width: 44px; height: 44px; min-width: 44px; background-color: #e0e7ff;">
                        <i class="fas fa-clipboard-list fa-lg text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <h5 class="font-weight-bold text-dark mb-0">SOP Standar Kenaikan Kelas & Kelulusan (Metode Top-Down)</h5>
                            <span class="badge badge-primary px-3 py-1 font-weight-bold">Panduan Resmi SOP</span>
                        </div>
                        <p class="text-secondary small mb-3">
                            Untuk mencegah terjadinya penumpukan siswa di rombel tujuan, proses kenaikan tingkat dianjurkan mengikuti urutan <strong>Top-Down (dari tingkat tertinggi ke terendah)</strong>:
                        </p>
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-2">
                                <div class="p-3 rounded border bg-white h-100 shadow-xs">
                                    <span class="badge badge-danger font-weight-bold mb-1">Langkah 1</span>
                                    <h6 class="font-weight-bold text-dark mb-1 small"><i class="fas fa-graduation-cap text-danger mr-1"></i>Luluskan Tingkat XII</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">Siswa kelas 12 dialihkan ke status <strong>Alumni / Lulus</strong> sehingga rombel XII menjadi bersih dan kosong.</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-2">
                                <div class="p-3 rounded border bg-white h-100 shadow-xs">
                                    <span class="badge badge-warning font-weight-bold mb-1 text-dark">Langkah 2</span>
                                    <h6 class="font-weight-bold text-dark mb-1 small"><i class="fas fa-level-up-alt text-warning mr-1"></i>Naikkan Tingkat XI ➔ XII</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">Siswa kelas 11 dipromosikan mengisi rombel XII yang sudah kosong dan siap ditempati.</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-2">
                                <div class="p-3 rounded border bg-white h-100 shadow-xs">
                                    <span class="badge badge-success font-weight-bold mb-1">Langkah 3</span>
                                    <h6 class="font-weight-bold text-dark mb-1 small"><i class="fas fa-level-up-alt text-success mr-1"></i>Naikkan Tingkat X ➔ XI</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">Siswa kelas 10 dipromosikan mengisi rombel XI yang sudah kosong dan siap ditempati.</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-2">
                                <div class="p-3 rounded border bg-white h-100 shadow-xs">
                                    <span class="badge badge-info font-weight-bold mb-1">Langkah 4</span>
                                    <h6 class="font-weight-bold text-dark mb-1 small"><i class="fas fa-user-plus text-info mr-1"></i>Rombel X Siap (PPDB)</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">Rombel tingkat X kini bersih dan siap menerima siswa baru tahun ajaran berikutnya.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigasi Mode Kenaikan Kelas -->
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm" id="promotionTabs" role="tablist">
            <li class="nav-item mr-2">
                <a class="nav-link font-weight-bold active py-2 px-3" id="tab-manual-btn" data-toggle="pill" href="#tab-manual" role="tab">
                    <i class="fas fa-users-cog mr-2"></i>Kenaikan Per Kelas (Manual / Fleksibel)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold py-2 px-3 text-danger" id="tab-auto-btn" data-toggle="pill" href="#tab-auto" role="tab">
                    <i class="fas fa-calendar-check mr-2"></i>Tutup Tahun Ajaran (Kenaikan Otomatis Berjenjang)
                </a>
            </li>
        </ul>

        <div class="tab-content" id="promotionTabsContent">
            <!-- TAB 1: Kenaikan Per Kelas (Manual) -->
            <div class="tab-pane fade show active" id="tab-manual" role="tabpanel">

                <!-- Form Konfigurasi Kenaikan Kelas -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-sliders-h text-primary mr-2"></i>1. Tentukan Kelas Asal & Tujuan
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('kenaikan_kelas.index') }}">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-3">
                                    <label for="source_class_id" class="font-weight-bold text-secondary small text-uppercase">
                                        <i class="fas fa-sign-out-alt text-danger mr-1"></i> Kelas Asal Siswa:
                                    </label>
                                    <select name="source_class_id" id="source_class_id" class="form-control font-weight-bold" onchange="document.getElementById('filterForm').submit()">
                                        <option value="">-- Pilih Kelas Asal --</option>
                                        @include('partials.select_class_options', ['selected' => $sourceClassId])
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="action_type" class="font-weight-bold text-secondary small text-uppercase">
                                        <i class="fas fa-exchange-alt text-info mr-1"></i> Mode Aksi:
                                    </label>
                                    <select name="action_type" id="action_type" class="form-control font-weight-bold">
                                        <option value="promote" {{ ($actionType ?? 'promote') === 'promote' ? 'selected' : '' }}>Naik ke Kelas Baru</option>
                                        <option value="graduate" {{ ($actionType ?? '') === 'graduate' ? 'selected' : '' }}>Lulus / Alumni (Tingkat Akhir)</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3" id="targetClassContainer" style="{{ ($actionType ?? 'promote') === 'graduate' ? 'display: none;' : '' }}">
                                    <label for="target_class_id" class="font-weight-bold text-secondary small text-uppercase">
                                        <i class="fas fa-sign-in-alt text-success mr-1"></i> Kelas Tujuan:
                                    </label>
                                    <select name="target_class_id" id="target_class_id" class="form-control font-weight-bold">
                                        <option value="">-- Pilih Kelas Tujuan --</option>
                                        @include('partials.select_class_options', ['excludeId' => $sourceClassId])
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

        <!-- Daftar Siswa -->
        @if ($sourceClassId)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-users text-primary mr-2"></i>2. Pilih Siswa yang Naik / Lulus
                        </h5>
                        <small class="text-muted">
                            Total: <strong>{{ count($students) }}</strong> siswa di kelas <strong>{{ $sourceClass ? $sourceClass->class_name : '' }}</strong>
                        </small>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span class="badge badge-info py-2 px-3 mr-2" id="selectedCountBadge">
                            Dipilih: <strong>{{ count($students) }}</strong> siswa
                        </span>
                        <button type="button" class="btn btn-success font-weight-bold shadow-sm" id="btnSubmitPromotion" {{ count($students) == 0 ? 'disabled' : '' }}>
                            <i class="fas fa-check-circle mr-1"></i> Proses Kenaikan Kelas
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if (count($students) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="studentsTable">
                                <thead class="bg-light text-secondary small text-uppercase">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">
                                            <input type="checkbox" id="checkAll" checked style="width: 18px; height: 18px; cursor: pointer;">
                                        </th>
                                        <th style="width: 60px;" class="text-center">No</th>
                                        <th>Nama Siswa</th>
                                        <th>NIS / NISN</th>
                                        <th class="text-center">L/P</th>
                                        <th>Status Rencana</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $idx => $std)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="student-checkbox" value="{{ $std->id }}" checked style="width: 18px; height: 18px; cursor: pointer;">
                                            </td>
                                            <td class="text-center text-muted font-weight-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if ($std->foto_siswa_path)
                                                        <img src="{{ asset('storage/' . $std->foto_siswa_path) }}" alt="Foto" class="rounded-circle mr-2" style="width: 34px; height: 34px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary font-weight-bold d-flex align-items-center justify-content-center mr-2" style="width: 34px; height: 34px; background-color: #e8f0fe;">
                                                            {{ strtoupper(substr($std->nama, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <span class="font-weight-bold text-dark">{{ $std->nama }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-light border text-dark">{{ $std->nis }}</span>
                                                @if ($std->nisn)
                                                    <span class="badge badge-light border text-muted ml-1">{{ $std->nisn }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $std->jenis_kelamin == 'L' ? 'badge-primary' : 'badge-pink text-danger border' }}">
                                                    {{ $std->jenis_kelamin }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success px-2 py-1 targetPreviewBadge">
                                                    <i class="fas fa-arrow-right mr-1"></i> Siap Dinaikkan
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted font-weight-bold">Belum ada siswa di kelas ini.</h5>
                            <p class="text-secondary small">Silakan pilih kelas lain atau tambahkan siswa baru di Data Siswa.</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card shadow-sm border-0 text-center py-5">
                <div class="card-body">
                    <i class="fas fa-chalkboard-teacher fa-4x text-muted mb-3"></i>
                    <h5 class="font-weight-bold text-secondary">Silakan Pilih Kelas Asal Terlebih Dahulu</h5>
                    <p class="text-muted small mb-0">Pilih salah satu kelas pada dropdown di atas untuk melihat daftar siswa yang akan diproses.</p>
                </div>
            </div>
        @endif
            </div>
            <!-- /TAB 1: Kenaikan Per Kelas -->

            <!-- TAB 2: Tutup Tahun Ajaran (Kenaikan Otomatis Berjenjang) -->
            <div class="tab-pane fade" id="tab-auto" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-magic text-danger mr-2"></i>Pemetaan Kenaikan Otomatis Satu Sekolah
                            </h5>
                            <small class="text-muted">
                                Sistem secara otomatis memetakan Kelas 12 ➔ Alumni, Kelas 11 ➔ 12, dan Kelas 10 ➔ 11 berdasarkan program keahlian yang sesuai.
                            </small>
                        </div>
                        <div class="mt-2 mt-sm-0">
                            <span class="badge badge-danger py-2 px-3 font-weight-bold">
                                Total Rombel: <strong>{{ count($autoMap ?? []) }}</strong> Kelas
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small text-uppercase">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">No</th>
                                        <th class="text-center" style="width: 90px;">Tingkat</th>
                                        <th>Kelas Asal</th>
                                        <th class="text-center" style="width: 50px;">Arah</th>
                                        <th>Kelas Tujuan</th>
                                        <th class="text-center" style="width: 130px;">Jumlah Siswa</th>
                                        <th>Rencana Aksi Sistem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($autoMap ?? [] as $mIdx => $map)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $mIdx + 1 }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $map['level'] == 12 ? 'badge-danger' : ($map['level'] == 11 ? 'badge-warning text-dark' : 'badge-success') }} px-2 py-1 font-weight-bold">
                                                    Kelas {{ $map['level'] }}
                                                </span>
                                            </td>
                                            <td class="font-weight-bold text-dark">
                                                <i class="fas fa-school text-muted mr-1"></i>{{ $map['source_class_name'] }}
                                            </td>
                                            <td class="text-center text-muted">
                                                <i class="fas fa-arrow-right"></i>
                                            </td>
                                            <td>
                                                @if ($map['action_type'] === 'graduate')
                                                    <span class="badge badge-secondary py-2 px-3 font-weight-bold">
                                                        <i class="fas fa-graduation-cap mr-1 text-warning"></i>Alumni / Lulus
                                                    </span>
                                                @elseif ($map['action_type'] === 'promote')
                                                    <span class="badge badge-primary py-2 px-3 font-weight-bold">
                                                        <i class="fas fa-level-up-alt mr-1"></i>{{ $map['target_class_name'] }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger py-2 px-3 font-weight-bold">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>{{ $map['target_class_name'] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center font-weight-bold {{ $map['student_count'] > 0 ? 'text-primary' : 'text-muted' }}">
                                                {{ $map['student_count'] }} Siswa
                                            </td>
                                            <td>
                                                @if ($map['action_type'] === 'graduate')
                                                    <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Diluluskan & diarsipkan</small>
                                                @elseif ($map['action_type'] === 'promote')
                                                    <small class="text-primary font-weight-bold"><i class="fas fa-sync mr-1"></i>Dinaikkan ke tingkat berikutnya</small>
                                                @else
                                                    <small class="text-danger font-weight-bold"><i class="fas fa-times-circle mr-1"></i>Perlu dicek manual</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data kelas yang terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-4">
                        <div class="row align-items-center">
                            <div class="col-md-7 mb-3 mb-md-0">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="skip_tinggal_kelas" checked>
                                    <label class="custom-control-label font-weight-bold text-dark" for="skip_tinggal_kelas">
                                        Lewati siswa yang berstatus "Tinggal Kelas" / "Tidak Naik"
                                    </label>
                                </div>
                                <small class="text-muted d-block">
                                    Siswa dengan catatan status tinggal kelas pada catatan wali kelas tidak akan dipromosikan dan tetap berada di kelas asalnya.
                                </small>
                            </div>
                            <div class="col-md-5 text-md-right">
                                <button type="button" class="btn btn-danger btn-lg font-weight-bold shadow-sm px-4" id="btnTutupTahun">
                                    <i class="fas fa-rocket mr-2"></i>Jalankan Tutup Tahun Ajaran
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /TAB 2: Tutup Tahun Ajaran -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    const sourceClassId = '{{ $sourceClassId }}';
    const sourceClassName = '{{ $sourceClass ? $sourceClass->class_name : "" }}';

    // Toggle target class container based on action mode
    $('#action_type').on('change', function() {
        if ($(this).val() === 'graduate') {
            $('#targetClassContainer').slideUp();
            $('.targetPreviewBadge').html('<i class="fas fa-graduation-cap mr-1"></i> Siap Ditetapkan Alumni');
            $('.targetPreviewBadge').removeClass('badge-success').addClass('badge-secondary');
        } else {
            $('#targetClassContainer').slideDown();
            updateTargetPreview();
        }
    });

    if ($('#action_type').val() === 'graduate') {
        $('.targetPreviewBadge').html('<i class="fas fa-graduation-cap mr-1"></i> Siap Ditetapkan Alumni').removeClass('badge-success').addClass('badge-secondary');
    }

    $('#target_class_id').on('change', function() {
        updateTargetPreview();
    });

    function updateTargetPreview() {
        let targetText = $('#target_class_id option:selected').text();
        if ($('#target_class_id').val()) {
            $('.targetPreviewBadge').html('<i class="fas fa-arrow-right mr-1"></i> Pindah ke: ' + targetText);
            $('.targetPreviewBadge').removeClass('badge-secondary').addClass('badge-success');
        } else {
            $('.targetPreviewBadge').html('<i class="fas fa-arrow-right mr-1"></i> Siap Dinaikkan');
            $('.targetPreviewBadge').removeClass('badge-secondary').addClass('badge-success');
        }
    }

    // Select all / Deselect all checkboxes
    $('#checkAll').on('change', function() {
        $('.student-checkbox').prop('checked', $(this).is(':checked'));
        updateSelectedCount();
    });

    $('.student-checkbox').on('change', function() {
        updateSelectedCount();
        let total = $('.student-checkbox').length;
        let checked = $('.student-checkbox:checked').length;
        $('#checkAll').prop('checked', total === checked);
    });

    function updateSelectedCount() {
        let checked = $('.student-checkbox:checked').length;
        let total = $('.student-checkbox').length;
        $('#selectedCountBadge').html(`Dipilih: <strong>${checked}</strong> dari ${total} siswa`);
        $('#btnSubmitPromotion').prop('disabled', checked === 0);
    }

    // Submit batch promotion
    $('#btnSubmitPromotion').on('click', function() {
        let actionType = $('#action_type').val();
        let targetClassId = $('#target_class_id').val();
        let targetClassName = $('#target_class_id option:selected').text();
        let selectedStudentIds = [];

        $('.student-checkbox:checked').each(function() {
            selectedStudentIds.push($(this).val());
        });

        if (selectedStudentIds.length === 0) {
            SwalHelper.showWarning('Pilih minimal satu siswa untuk diproses.', 'Peringatan');
            return;
        }

        if (actionType === 'promote' && !targetClassId) {
            SwalHelper.showWarning('Silakan pilih Kelas Tujuan terlebih dahulu.', 'Kelas Tujuan Kosong');
            $('#target_class_id').focus();
            return;
        }

        let confirmTitle = actionType === 'promote'
            ? 'Konfirmasi Kenaikan Kelas'
            : 'Konfirmasi Kelulusan Siswa';

        let confirmHtml = actionType === 'promote'
            ? `Anda akan memindahkan <strong>${selectedStudentIds.length} siswa</strong> dari kelas <strong>${sourceClassName}</strong> ke kelas <strong>${targetClassName}</strong>.<br><br><small class="text-muted">Data nilai semester lalu tetap tersimpan secara historis.</small>`
            : `Anda akan menetapkan <strong>${selectedStudentIds.length} siswa</strong> dari kelas <strong>${sourceClassName}</strong> sebagai <strong>Alumni/Lulus</strong>.`;

        Swal.fire({
            title: confirmTitle,
            html: confirmHtml,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Proses Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Kenaikan Kelas...',
                    text: 'Mohon tunggu sejenak...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '{{ route("kenaikan_kelas.promote") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        source_class_id: sourceClassId,
                        action_type: actionType,
                        target_class_id: targetClassId,
                        student_ids: selectedStudentIds
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(err) {
                        let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Terjadi kegagalan saat memproses kenaikan kelas.';
                        SwalHelper.showError(msg, 'Gagal');
                    }
                });
            }
        });
    });

    // Submit Batch Tutup Tahun Ajaran (Otomatis Berjenjang)
    $('#btnTutupTahun').on('click', function() {
        let skipTinggal = $('#skip_tinggal_kelas').is(':checked');

        Swal.fire({
            title: 'Tutup Tahun Ajaran & Promosi Berjenjang',
            html: `
                <div class="text-left small mb-3">
                    <p class="text-danger font-weight-bold mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>PERINGATAN PENTING:</p>
                    <p class="mb-2">Sistem akan mengeksekusi kenaikan kelas massal untuk seluruh rombel secara <strong>Top-Down (Berjenjang)</strong>:</p>
                    <ul class="pl-3 mb-2">
                        <li><strong>Tingkat XII</strong>: Dialihkan ke status <em>Alumni / Lulus</em>.</li>
                        <li><strong>Tingkat XI</strong>: Naik mengisi rombel <em>Tingkat XII</em>.</li>
                        <li><strong>Tingkat X</strong>: Naik mengisi rombel <em>Tingkat XI</em>.</li>
                    </ul>
                    ${skipTinggal ? '<p class="text-success font-weight-bold mb-0"><i class="fas fa-check-circle mr-1"></i>Siswa tinggal kelas akan dilewati & tetap tinggal di kelas asalnya.</p>' : ''}
                </div>
                <div class="alert alert-warning py-2 small mb-0 text-left">
                    Apakah Anda yakin ingin menjalankan proses tutup tahun ajaran sekarang?
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-rocket mr-1"></i> Ya, Jalankan Tutup Tahun!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Tutup Tahun Ajaran...',
                    text: 'Sedang mengeksekusi promosi berjenjang seluruh rombel...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '{{ route("kenaikan_kelas.tutup_tahun") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        skip_tinggal_kelas: skipTinggal ? 1 : 0
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tutup Tahun Ajaran Selesai!',
                            html: `
                                <p class="mb-2">${res.message}</p>
                                <div class="badge badge-success px-3 py-2 mr-1">Lulus: ${res.total_graduated} siswa</div>
                                <div class="badge badge-primary px-3 py-2 mr-1">Naik Kelas: ${res.total_promoted} siswa</div>
                                <div class="badge badge-secondary px-3 py-2">Tinggal Kelas: ${res.total_retained} siswa</div>
                            `,
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(err) {
                        let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Terjadi kegagalan saat memproses tutup tahun ajaran.';
                        SwalHelper.showError(msg, 'Gagal');
                    }
                });
            }
        });
    });
});
</script>
@endsection
