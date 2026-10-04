@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i>{{ __('Pengaturan Periode & FST') }}
                    </h1>
                    <p class="text-muted small mb-0 mt-1">Konfigurasi Fase (E/F), Semester, Tahun Ajaran, dan kontrol Kunci Nilai (Locking) untuk masa tutup semester.</p>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item">Data Master</li>
                        <li class="breadcrumb-item active">FST / Periode Ajaran</li>
                    </ol>
                    <div class="clearfix"></div>
                    <button class="btn btn-outline-primary font-weight-bold shadow-sm mr-2" id="presetFstBtn">
                        <i class="fas fa-magic mr-1"></i> Generate Preset Otomatis
                    </button>
                    <button class="btn btn-primary font-weight-bold shadow-sm" id="inputFstBtn">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Periode Baru
                    </button>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- ── PANDUAN, TIPS & PERINGATAN (UX HELPER) ────────────────────────── -->
            <div class="row">
                <div class="col-md-7 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100 mb-0" style="border-radius: 8px;">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title font-weight-bold text-info mb-0">
                                <i class="fas fa-lightbulb mr-2"></i>Panduan Fase, Semester & Tahun Ajaran
                            </h6>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-2 px-3 small text-secondary">
                            <ul class="pl-3 mb-0">
                                <li class="mb-1">
                                    <strong>Ketentuan Fase Kurikulum Merdeka:</strong>
                                    <span class="badge badge-primary px-1">Fase E</span> diperuntukkan bagi Kelas X (10), sedangkan <span class="badge badge-info px-1">Fase F</span> untuk jenjang Kelas XI (11) dan Kelas XII (12).
                                </li>
                                <li class="mb-1">
                                    <strong>Format Tahun Ajaran:</strong> Gunakan format standar 4 digit garis miring, contoh: <code class="text-primary font-weight-bold">2024/2025</code> atau <code class="text-primary font-weight-bold">2025/2026</code>.
                                </li>
                                <li>
                                    <strong>Semester Tengah vs Akhir:</strong> Pilih <strong>Tengah</strong> untuk penerbitan rapor STS/PTS (Tengah Semester) atau <strong>Akhir</strong> untuk penerbitan rapor SAS/PAS (Rapor Akhir Semester/Kenaikan Kelas).
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 mb-3">
                    <div class="card card-outline card-warning shadow-sm h-100 mb-0" style="border-radius: 8px;">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title font-weight-bold text-warning mb-0">
                                <i class="fas fa-lock mr-2"></i>Peringatan & Fungsi Kunci Nilai (Lock)
                            </h6>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-2 px-3 small text-secondary">
                            <ul class="pl-3 mb-0">
                                <li class="mb-1">
                                    <strong>Kunci Nilai (Tutup Semester):</strong> Gunakan tombol <span class="badge badge-secondary px-1">Aksi &rarr; Kunci Nilai</span> jika masa pengisian nilai telah berakhir. Saat terkunci (<span class="badge badge-danger px-1"><i class="fas fa-lock mr-1"></i>Terkunci</span>), guru tidak dapat mengubah atau menambah nilai lagi (Read-Only).
                                </li>
                                <li>
                                    <strong class="text-danger">Peringatan Hapus:</strong> Jangan menghapus periode yang telah memiliki riwayat nilai siswa karena akan merusak perhitungan capaian rapor pada semester tersebut.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TABEL DATA FST ──────────────────────────────────────────────── -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0" id="resultTable" style="border-radius: 10px;">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-list text-primary mr-2"></i>Daftar Periode & FST Terdaftar
                                </h6>
                                <span class="badge badge-light border text-muted px-2 py-1 small">
                                    <i class="fas fa-sync-alt mr-1"></i>Realtime Server Data
                                </span>
                            </div>
                        </div>
                        <div class="card-body pt-0 px-3 pb-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered w-100" id="valueTable">
                                    <thead class="bg-light text-dark">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">No</th>
                                            <th style="width: 80px;" class="text-center">Fase</th>
                                            <th>Semester</th>
                                            <th>Tahun Ajaran</th>
                                            <th style="width: 100px;" class="text-center">Jenis Sem</th>
                                            <th style="width: 140px;" class="text-center">Status Kunci</th>
                                            <th style="width: 120px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->

        <!-- Modal Tambah & Edit Periode FST -->
        <div class="modal fade" id="tpModal" tabindex="-1" role="dialog" aria-labelledby="tpModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                    <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold" id="tpModalLabel">
                            <i class="fas fa-calendar-alt mr-2"></i>Tambah Periode & FST
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="valueForm">
                        <div class="modal-body p-4">
                            <input type="hidden" id="fst_id">
                            
                            <div class="form-group">
                                <label for="fase" class="font-weight-bold text-dark">
                                    Fase Pembelajaran <span class="text-danger">*</span>
                                </label>
                                <select name="fase" id="fase" class="form-control" required>
                                    <option value="" selected disabled>-- Pilih Fase Pembelajaran --</option>
                                    <option value="E">Fase E (Tingkat Kelas X / 10)</option>
                                    <option value="F">Fase F (Tingkat Kelas XI & XII / 11 & 12)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="semester" class="font-weight-bold text-dark">
                                    Semester <span class="text-danger">*</span>
                                </label>
                                <select name="semester" id="semester" class="form-control" required>
                                    <option value="" selected disabled>-- Pilih Semester Pembelajaran --</option>
                                    <option value="I (Satu)">I (Satu) - Ganjil</option>
                                    <option value="II (Dua)">II (Dua) - Genap</option>
                                    <option value="III (Tiga)">III (Tiga) - Ganjil</option>
                                    <option value="IV (Empat)">IV (Empat) - Genap</option>
                                    <option value="V (Lima)">V (Lima) - Ganjil</option>
                                    <option value="VI (Enam)">VI (Enam) - Genap</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="tahun_ajaran" class="font-weight-bold text-dark">
                                    Tahun Ajaran <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran"
                                    placeholder="Contoh: 2024/2025 atau 2025/2026" required>
                                <small class="form-text text-muted">
                                    Gunakan format 4 digit garis miring (contoh: 2024/2025).
                                </small>
                            </div>

                            <div class="form-group mb-0">
                                <label for="ta" class="font-weight-bold text-dark">
                                    Jenis Pelaporan Semester (Sem) <span class="text-danger">*</span>
                                </label>
                                <select name="ta" id="ta" class="form-control" required>
                                    <option value="" selected disabled>-- Pilih Jenis Pelaporan --</option>
                                    <option value="tengah">Tengah (Penilaian Tengah Semester / STS / PTS)</option>
                                    <option value="akhir">Akhir (Penilaian Akhir Semester / SAS / PAS / Kenaikan)</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                            <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Generate Preset FST & Auto Map -->
        <div class="modal fade" id="presetModal" tabindex="-1" role="dialog" aria-labelledby="presetModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                    <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold" id="presetModalLabel">
                            <i class="fas fa-magic mr-2"></i>Generate Paket Periode FST & Pemetaan Kelas
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="presetForm">
                        <div class="modal-body p-4">
                            <div class="alert alert-info py-2 px-3 small mb-3 border-0" style="border-radius: 8px;">
                                <i class="fas fa-info-circle mr-1"></i> <strong>Solusi Cepat:</strong> Fitur ini langsung membuat paket periode standar (Fase E & F, Tengah & Akhir) tanpa perlu input satu per satu.
                            </div>

                            <div class="form-group">
                                <label for="preset_tahun_ajaran" class="font-weight-bold text-dark">
                                    Tahun Ajaran <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control font-weight-bold" id="preset_tahun_ajaran" name="tahun_ajaran"
                                    placeholder="Contoh: 2024/2025 atau 2025/2026" required>
                                <small class="form-text text-muted">Format 4 digit garis miring (contoh: 2024/2025).</small>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold text-dark d-block mb-2">
                                    Pilihan Paket Semester <span class="text-danger">*</span>
                                </label>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="paket_ganjil" name="paket" value="ganjil" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="paket_ganjil">
                                        <strong>Semester Ganjil</strong> (4 Periode: Fase E & F &bull; STS & SAS)
                                    </label>
                                </div>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="paket_genap" name="paket" value="genap" class="custom-control-input">
                                    <label class="custom-control-label" for="paket_genap">
                                        <strong>Semester Genap</strong> (4 Periode: Fase E & F &bull; STS & SAS)
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="paket_full" name="paket" value="full" class="custom-control-input">
                                    <label class="custom-control-label" for="paket_full">
                                        <strong>1 Tahun Penuh</strong> (8 Periode: Ganjil + Genap Lengkap)
                                    </label>
                                </div>
                            </div>

                            <div class="custom-control custom-checkbox mt-3 pt-3 border-top">
                                <input type="checkbox" class="custom-control-input" id="preset_auto_map" name="auto_map_classes" value="1" checked>
                                <label class="custom-control-label font-weight-bold text-dark" for="preset_auto_map">
                                    Otomatis petakan seluruh mata pelajaran aktif ke rombel kelas yang sesuai
                                </label>
                                <small class="form-text text-muted">
                                    Fase E dipetakan ke rombel Kelas X, Fase F ke rombel Kelas XI & XII. Guru dapat langsung mengisi nilai tanpa perlu mapping manual satu per satu.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                            <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary px-4 font-weight-bold" id="btnSubmitPreset">
                                <i class="fas fa-bolt mr-1"></i> Generate Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <!-- /.content -->
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            let table = $('#valueTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                language: {
                    search: "Cari Periode:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ periode",
                    infoEmpty: "Tidak ada data periode",
                    zeroRecords: "Data periode tidak ditemukan",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "&raquo;",
                        previous: "&laquo;"
                    }
                },
                ajax: {
                    url: "{{ route('mfst.getData') }}",
                    type: "GET"
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        className: "text-center align-middle",
                        render: function(data, type, row, meta) {
                            return `<span class="badge badge-light border text-muted">${meta.row + meta.settings._iDisplayStart + 1}</span>`;
                        }
                    },
                    {
                        data: "fase",
                        className: "text-center align-middle",
                        render: function(data) {
                            if (!data) return '-';
                            let faseUpper = data.toUpperCase();
                            let badgeClass = (faseUpper === 'E') ? 'badge-primary' : 'badge-info';
                            return `<span class="badge ${badgeClass} px-2 py-1 font-weight-bold">Fase ${faseUpper}</span>`;
                        }
                    },
                    {
                        data: "semester",
                        className: "align-middle font-weight-bold text-dark",
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: "tahun_ajaran",
                        className: "align-middle font-weight-bold text-dark",
                        render: function(data) {
                            return data ? `<i class="fas fa-calendar mr-1 text-secondary"></i>${data}` : '-';
                        }
                    },
                    {
                        data: "ta",
                        className: "text-center align-middle",
                        render: function(data) {
                            if (!data) return '-';
                            let isAkhir = data.toLowerCase() === 'akhir';
                            return `<span class="badge ${isAkhir ? 'badge-dark' : 'badge-light border'} px-2 py-1 text-capitalize">${data}</span>`;
                        }
                    },
                    {
                        data: "is_locked",
                        className: "text-center align-middle",
                        render: function(data) {
                            if (data == 1) {
                                return '<span class="badge badge-pill badge-danger px-2 py-1 font-weight-normal shadow-sm" title="Nilai dikunci (Read-Only)"><i class="fas fa-lock mr-1"></i>Terkunci</span>';
                            }
                            return '<span class="badge badge-pill badge-success px-2 py-1 font-weight-normal shadow-sm" title="Nilai dapat diinput guru"><i class="fas fa-lock-open mr-1"></i>Terbuka</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        className: "text-center align-middle",
                        render: function(data, type, row) {
                            if (row.id) {
                                let isLocked = (row.is_locked == 1);
                                return `
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info dropdown-toggle shadow-sm"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                            <button class="dropdown-item toggleLockBtn font-weight-bold ${isLocked ? 'text-success' : 'text-warning'}" 
                                                data-id='${row.id}' data-locked='${row.is_locked}'>
                                                <i class="fas ${isLocked ? 'fa-unlock mr-2' : 'fa-lock mr-2'}"></i>
                                                ${isLocked ? 'Buka Kunci Nilai' : 'Kunci Nilai (Lock)'}
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <button class="dropdown-item editFstBtn text-primary" 
                                                data-id='${row.id}' data-fase='${row.fase}' data-semester='${row.semester}' data-tahun_ajaran='${row.tahun_ajaran}' data-ta='${row.ta}'>
                                                <i class="fas fa-pen mr-2"></i>Edit Periode
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <button class="dropdown-item text-danger delFstBtn" data-id='${row.id}'>
                                                <i class="fas fa-trash mr-2"></i>Hapus Periode
                                            </button>
                                        </div>
                                    </div>
                                `;
                            } else {
                                return `<span class="text-muted small">-</span>`;
                            }
                        }
                    }
                ]
            });

            // Tampilkan Modal Generate Preset FST
            $(document).on("click", "#presetFstBtn", function() {
                let currentYear = new Date().getFullYear();
                let currentMonth = new Date().getMonth(); // 0-indexed (6 = Juli)
                let defaultTa = (currentMonth >= 6)
                    ? `${currentYear}/${currentYear + 1}`
                    : `${currentYear - 1}/${currentYear}`;

                if (!$('#preset_tahun_ajaran').val()) {
                    $('#preset_tahun_ajaran').val(defaultTa);
                }
                $('#preset_auto_map').prop('checked', true);
                $('#presetModal').modal('show');
            });

            // Submit Form Generate Preset FST
            $('#presetForm').submit(function(e) {
                e.preventDefault();
                let $btn = $('#btnSubmitPreset');
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

                $.ajax({
                    url: "{{ route('mfst.generatePreset') }}",
                    method: "POST",
                    data: {
                        tahun_ajaran: $('#preset_tahun_ajaran').val(),
                        paket: $('input[name="paket"]:checked').val(),
                        auto_map_classes: $('#preset_auto_map').is(':checked') ? 1 : 0,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            confirmButtonColor: '#007bff'
                        });
                        $('#presetModal').modal('hide');
                        $('#valueTable').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat generate preset!';
                        if (xhr.responseJSON?.errors) {
                            let errList = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            msg += `<br><small class="text-danger mt-1 d-block">${errList}</small>`;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: msg
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Tampilkan Modal Input FST
            $(document).on("click", "#inputFstBtn", function() {
                $('#fst_id').val('');
                $('#fase').val('').trigger('change');
                $('#semester').val('').trigger('change');
                $('#tahun_ajaran').val('');
                $('#ta').val('').trigger('change');
                $('#tpModalLabel').html('<i class="fas fa-plus-circle mr-2"></i>Tambah Periode & FST');
                $('#tpModal').modal('show');
            });

            // Simpan atau Update Input
            $('#valueForm').submit(function(e) {
                e.preventDefault();
                let id = $('#fst_id').val();
                let url = id ? `/mfst/${id}` : "{{ route('mfst.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        fase: $('#fase').val(),
                        semester: $('#semester').val(),
                        tahun_ajaran: $('#tahun_ajaran').val(),
                        ta: $('#ta').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#tpModal').modal('hide');
                        $('#valueTable').DataTable().ajax.reload(null, false);
                    },
                    error: function(res) {
                        console.log(res);
                        Swal.fire('Error', res.responseJSON?.message || 'Terjadi kesalahan, coba lagi!', 'error');
                    }
                });
            });

            // Tampilkan Modal Edit FST
            $("#valueTable").on("click", ".editFstBtn", function() {
                let id = $(this).data('id');
                let fase = $(this).data('fase');
                let semester = $(this).data('semester');
                let tahun_ajaran = $(this).data('tahun_ajaran');
                let ta = $(this).data('ta');

                $('#fst_id').val(id);
                $('#fase').val(fase).trigger('change');
                $('#semester').val(semester).trigger('change');
                $('#tahun_ajaran').val(tahun_ajaran);
                $('#ta').val(ta).trigger('change');
                $('#tpModalLabel').html('<i class="fas fa-edit mr-2"></i>Edit Periode & FST');
                $('#tpModal').modal('show');
            });

            // Delete Action
            $("#valueTable").on("click", ".delFstBtn", function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: "Hapus Periode Ini?",
                    text: "Menghapus periode FST akan memutus seluruh relasi nilai rapor pada semester ini!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/mfst/${id}`,
                            method: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#valueTable').DataTable().ajax.reload(null, false);
                            },
                            error: function(r) {
                                console.log(r);
                                Swal.fire("Gagal!", r.responseJSON?.message || "Terjadi kesalahan saat menghapus data!", "error");
                            }
                        });
                    }
                });
            });

            // Toggle Lock Action
            $(document).on('click', '.toggleLockBtn', function() {
                let id = $(this).data('id');
                let isLocked = $(this).data('locked') == 1;
                let actionText = isLocked ? 'Buka Kunci Nilai' : 'Kunci Nilai (Lock)';
                let confirmText = isLocked
                    ? 'Guru mata pelajaran dan wali kelas dapat kembali menginput atau mengedit nilai pada semester ini.'
                    : 'Seluruh input dan perubahan nilai pada semester ini akan dibekukan (Read-Only). Guru tidak dapat lagi mengubah data nilai.';

                Swal.fire({
                    title: `${actionText}?`,
                    text: confirmText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isLocked ? '#28a745' : '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Ya, ${actionText}!`,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/mfst/${id}/toggle-lock`,
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                $('#valueTable').DataTable().ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Swal.fire("Gagal!", xhr.responseJSON?.message || "Terjadi kesalahan!", "error");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

