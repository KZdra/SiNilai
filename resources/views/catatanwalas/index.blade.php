@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-clipboard-list mr-2 text-primary"></i>Presensi & Catatan Wali Kelas
                    </h1>
                    <p class="text-muted small mb-0">Input rekap absensi semester, pesan perkembangan siswa, dan keputusan kenaikan kelas.</p>
                </div>
                <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                    <button class="btn btn-primary" id="btnFilterShow">
                        <i class="fas fa-filter mr-1"></i>Pilih Kelas & Semester
                    </button>
                    <button class="btn btn-success" id="btnSaveAll" style="display: none;">
                        <i class="fas fa-save mr-1"></i>Simpan Semua Presensi & Catatan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Filter Box -->
            <div class="card shadow-sm" id="filterCard">
                <div class="card-header bg-light">
                    <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-sliders-h mr-2"></i>Filter Kelas & Semester</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_id" class="font-weight-bold">Kelas</label>
                                    @if ($className)
                                        <select name="class_id" id="class_id" class="form-control" disabled>
                                            <option value="{{ Auth::user()->class_id }}" selected>{{ $className }}</option>
                                        </select>
                                    @else
                                        <select name="class_id" id="class_id" class="form-control" required>
                                            <option value="" selected disabled>-- Pilih Kelas --</option>
                                            @include('partials.select_class_options')
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fst_id" class="font-weight-bold">Fase / Semester / Tahun Ajaran</label>
                                    <select name="fst_id" id="fst_id" class="form-control" required>
                                        <option value="" selected disabled>-- Pilih Fase & Semester --</option>
                                        @include('partials.select_fst_options')
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                            <i class="fas fa-search mr-1"></i>Tampilkan Siswa
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lock Banner (Hidden by default) -->
            <div class="alert alert-warning shadow-sm d-none" id="lockBanner">
                <i class="fas fa-lock mr-2"></i>
                <strong>Perhatian:</strong> Semester ini telah <strong>dikunci</strong> oleh kurikulum. Input presensi dan catatan dinonaktifkan (mode baca saja).
            </div>

            <!-- Student Attendance & Notes Table Card -->
            <div class="card shadow-sm" id="tableCard" style="display: none;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <span class="badge badge-primary px-3 py-2 mr-2" id="badgeKelas">-</span>
                        <span class="badge badge-info px-3 py-2" id="badgeSemester">-</span>
                    </div>
                    <div class="card-tools mt-2 mt-sm-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnReloadData">
                            <i class="fas fa-sync-alt mr-1"></i>Muat Ulang
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-bordered mb-0" id="walasTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 45px;" class="text-center">No</th>
                                <th style="min-width: 180px;">Nama Siswa</th>
                                <th style="width: 85px;" class="text-center">Sakit (S)</th>
                                <th style="width: 85px;" class="text-center">Izin (I)</th>
                                <th style="width: 85px;" class="text-center">Alpa (A)</th>
                                <th style="min-width: 280px;">Catatan Perkembangan Wali Kelas</th>
                                <th style="min-width: 180px;">Keputusan Akhir Tahun</th>
                                <th style="width: 90px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="walasTableBody">
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Memuat data siswa...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1 text-primary"></i>
                        Tekan tombol Simpan pada baris atau tombol <strong>Simpan Semua</strong> di pojok kanan atas untuk menyimpan perubahan.
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    let currentClassId = null;
    let currentFstId = null;
    let currentClassName = '';
    let currentFstName = '';
    let isLocked = false;

    $('#btnFilterShow').click(function() {
        $('#filterCard').slideToggle(300);
    });

    $('#filterForm').submit(function(e) {
        e.preventDefault();
        currentClassId = $('#class_id').val() || "{{ Auth::user()->class_id }}";
        currentFstId = $('#fst_id').val();
        currentClassName = $('#class_id option:selected').text();
        currentFstName = $('#fst_id option:selected').text();

        if (!currentClassId || !currentFstId) {
            SwalHelper.showError('Silahkan pilih Kelas dan Semester terlebih dahulu.');
            return;
        }

        $('#filterCard').slideUp(300);
        loadStudentsData();
    });

    $('#btnReloadData').click(function() {
        if (currentClassId && currentFstId) {
            loadStudentsData();
        }
    });

    function loadStudentsData() {
        $('#badgeKelas').text('Kelas: ' + currentClassName.trim());
        $('#badgeSemester').text(currentFstName.trim());

        let tbody = $('#walasTableBody');
        tbody.html('<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Mengambil data siswa...</td></tr>');
        $('#tableCard').show();

        $.ajax({
            url: "{{ route('walas.getdata') }}",
            type: "GET",
            data: {
                class_id: currentClassId,
                fst_id: currentFstId
            },
            success: function(res) {
                isLocked = res.is_locked;
                if (isLocked) {
                    $('#lockBanner').removeClass('d-none');
                    $('#btnSaveAll').hide();
                } else {
                    $('#lockBanner').addClass('d-none');
                    $('#btnSaveAll').show();
                }

                let students = res.data || [];
                if (students.length === 0) {
                    tbody.html('<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data siswa untuk kelas ini.</td></tr>');
                    return;
                }

                let html = '';
                students.forEach(function(s, index) {
                    let disabledAttr = isLocked ? 'disabled' : '';
                    let catatanVal = s.catatan || '';
                    let statusVal = s.status_kenaikan || '';

                    html += `
                        <tr data-student-id="${s.student_id}">
                            <td class="text-center font-weight-bold text-muted">${index + 1}</td>
                            <td>
                                <span class="font-weight-bold text-dark">${s.student_name}</span>
                                <div class="text-muted small">NIS: ${s.nis || '-'}</div>
                            </td>
                            <td>
                                <input type="number" min="0" max="365" class="form-control form-control-sm text-center input-sakit" value="${s.sakit || 0}" ${disabledAttr}>
                            </td>
                            <td>
                                <input type="number" min="0" max="365" class="form-control form-control-sm text-center input-izin" value="${s.izin || 0}" ${disabledAttr}>
                            </td>
                            <td>
                                <input type="number" min="0" max="365" class="form-control form-control-sm text-center input-alpa" value="${s.alpa || 0}" ${disabledAttr}>
                            </td>
                            <td>
                                <textarea class="form-control form-control-sm input-catatan" rows="2" placeholder="Tuliskan motivasi / capaian siswa..." ${disabledAttr}>${catatanVal}</textarea>
                            </td>
                            <td>
                                <select class="form-control form-control-sm input-status" ${disabledAttr}>
                                    <option value="" ${statusVal === '' ? 'selected' : ''}>-- Belum Diputuskan --</option>
                                    <option value="Naik ke Kelas XI" ${statusVal === 'Naik ke Kelas XI' ? 'selected' : ''}>Naik ke Kelas XI</option>
                                    <option value="Naik ke Kelas XII" ${statusVal === 'Naik ke Kelas XII' ? 'selected' : ''}>Naik ke Kelas XII</option>
                                    <option value="Lulus" ${statusVal === 'Lulus' ? 'selected' : ''}>Lulus</option>
                                    <option value="Tinggal di Kelas" ${statusVal === 'Tinggal di Kelas' ? 'selected' : ''}>Tinggal di Kelas</option>
                                </select>
                            </td>
                            <td class="text-center">
                                ${isLocked ? '<span class="badge badge-secondary"><i class="fas fa-lock"></i></span>' :
                                `<button type="button" class="btn btn-sm btn-primary btnSaveRow" title="Simpan Baris Ini">
                                    <i class="fas fa-save"></i>
                                </button>`}
                            </td>
                        </tr>
                    `;
                });

                tbody.html(html);
            },
            error: function() {
                tbody.html('<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data presensi dan catatan.</td></tr>');
            }
        });
    }

    // Save individual row
    $('#walasTable').on('click', '.btnSaveRow', function() {
        if (isLocked) return;

        let btn = $(this);
        let tr = btn.closest('tr');
        let studentId = tr.data('student-id');
        let sakit = tr.find('.input-sakit').val();
        let izin = tr.find('.input-izin').val();
        let alpa = tr.find('.input-alpa').val();
        let catatan = tr.find('.input-catatan').val();
        let statusKenaikan = tr.find('.input-status').val();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('walas.store') }}",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: {
                student_id: studentId,
                class_id: currentClassId,
                fst_id: currentFstId,
                sakit: sakit,
                izin: izin,
                alpa: alpa,
                catatan: catatan,
                status_kenaikan: statusKenaikan
            },
            success: function(res) {
                btn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i>');
                setTimeout(() => {
                    btn.removeClass('btn-success').addClass('btn-primary').html('<i class="fas fa-save"></i>').prop('disabled', false);
                }, 1500);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i>');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message || 'Gagal menyimpan data.'
                });
            }
        });
    });

    // Save All Bulk
    $('#btnSaveAll').click(function() {
        if (isLocked || !currentClassId || !currentFstId) return;

        let items = [];
        $('#walasTableBody tr').each(function() {
            let tr = $(this);
            let studentId = tr.data('student-id');
            if (studentId) {
                items.push({
                    student_id: studentId,
                    sakit: tr.find('.input-sakit').val(),
                    izin: tr.find('.input-izin').val(),
                    alpa: tr.find('.input-alpa').val(),
                    catatan: tr.find('.input-catatan').val(),
                    status_kenaikan: tr.find('.input-status').val()
                });
            }
        });

        if (items.length === 0) return;

        Swal.fire({
            title: 'Simpan Semua Data?',
            text: `Akan memperbarui presensi dan catatan untuk ${items.length} siswa di kelas ini.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save mr-1"></i> Simpan Semua',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu sebentar...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('walas.storeBulk') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    data: {
                        class_id: currentClassId,
                        fst_id: currentFstId,
                        items: items
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endsection
