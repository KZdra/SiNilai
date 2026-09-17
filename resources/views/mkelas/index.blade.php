@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">
                        <i class="fas fa-chalkboard text-primary mr-2"></i>{{ __('Master Data Kelas') }}
                    </h1>
                    <p class="text-muted small mb-0 mt-1">Kelola rombongan belajar (rombel) siswa untuk pembagian kelas, pengisian nilai, dan rekapitulasi rapor.</p>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item">Data Master</li>
                        <li class="breadcrumb-item active">Data Kelas</li>
                    </ol>
                    <div class="clearfix"></div>
                    <button class="btn btn-primary font-weight-bold shadow-sm" id="addClassBtn">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Kelas Baru
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
                                <i class="fas fa-lightbulb mr-2"></i>Tips Pengelolaan Rombongan Belajar
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
                                    <strong>Standar Penamaan Kurikulum Merdeka:</strong> Gunakan format tingkat dan konsentrasi keahlian yang konsisten (contoh: <code class="text-primary font-weight-bold">X RPL</code>, <code class="text-primary font-weight-bold">XI TKJ 1</code>, <code class="text-primary font-weight-bold">XII DKV 2</code>).
                                </li>
                                <li class="mb-1">
                                    <strong>Sinkronisasi Template:</strong> Nama kelas di sini digunakan langsung pada lembar cover rapor, template Excel impor siswa, dan penugasan wali kelas.
                                </li>
                                <li>
                                    <strong>Urutan Pengisian:</strong> Pastikan kelas telah dibuat sebelum Anda mengimpor data siswa atau melakukan pemetaan (mapping) mata pelajaran.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 mb-3">
                    <div class="card card-outline card-danger shadow-sm h-100 mb-0" style="border-radius: 8px;">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title font-weight-bold text-danger mb-0">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Peringatan & Keamanan Data
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
                                    <strong class="text-danger">Keterikatan Nilai & Siswa:</strong> Jangan menghapus kelas yang sudah memiliki siswa terdaftar atau memiliki riwayat nilai rapor semester lampau.
                                </li>
                                <li>
                                    <strong>Pergantian Tahun Ajaran:</strong> Saat siswa naik tingkat atau lulus, jangan hapus kelas lama. Rekam jejak kelas diperlukan untuk mencetak kembali rapor arsip/alumni.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TABEL DATA KELAS ────────────────────────────────────────────── -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0" style="border-radius: 10px;">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-list text-primary mr-2"></i>Daftar Kelas Terdaftar
                                </h6>
                                <span class="badge badge-light border text-muted px-2 py-1 small">
                                    <i class="fas fa-sync-alt mr-1"></i>Realtime Server Data
                                </span>
                            </div>
                        </div>
                        <div class="card-body pt-0 px-3 pb-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered w-100" id="classTable">
                                    <thead class="bg-light text-dark">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">No</th>
                                            <th>Nama Kelas / Rombongan Belajar</th>
                                            <th style="width: 160px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->

        <!-- Modal Tambah & Edit Kelas -->
        <div class="modal fade" id="classModal" tabindex="-1" role="dialog" aria-labelledby="classModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                    <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold" id="classModalLabel">
                            <i class="fas fa-chalkboard mr-2"></i>Tambah Kelas
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="classForm">
                        <div class="modal-body p-4">
                            <input type="hidden" id="class_id">
                            <div class="form-group mb-0">
                                <label for="class_name" class="font-weight-bold text-dark">
                                    Nama Kelas <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="class_name" name="class_name"
                                    placeholder="Contoh: X RPL, XI TKJ 1, XII DKV 2" required>
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>Tuliskan tingkatan kelas (X, XI, XII) dan nama program/konsentrasi keahlian secara jelas.
                                </small>
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

    </div>
    <!-- /.content -->
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // Init DataTable with Server-Side AJAX
            let table = $('#classTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                language: {
                    search: "Cari Kelas:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ kelas",
                    infoEmpty: "Tidak ada data kelas",
                    zeroRecords: "Data kelas tidak ditemukan",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "&raquo;",
                        previous: "&laquo;"
                    }
                },
                ajax: {
                    url: "{{ route('class.getData') }}",
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
                        data: 'class_name', 
                        className: "align-middle font-weight-bold text-dark",
                        render: function(data) {
                            return `<i class="fas fa-door-open text-primary mr-2"></i>${data || '-'}`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        className: "text-center align-middle",
                        render: function(data, type, row) {
                            let safeName = (row.class_name || '').replace(/"/g, '&quot;');
                            return `
                                <div class="btn-group btn-group-sm shadow-sm" role="group">
                                    <button class="btn btn-outline-primary editClassBtn" data-id="${row.id}" data-name="${safeName}" title="Edit Nama Kelas">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button class="btn btn-outline-danger delBtn" data-id="${row.id}" title="Hapus Kelas">
                                        <i class="fas fa-trash-alt mr-1"></i>Hapus
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            // Tampilkan Modal Tambah Kelas
            $('#addClassBtn').click(function() {
                $('#class_id').val('');
                $('#class_name').val('');
                $('#classModalLabel').html('<i class="fas fa-plus-circle mr-2"></i>Tambah Kelas Baru');
                $('#classModal').modal('show');
            });

            // Simpan atau Update Kelas
            $('#classForm').submit(function(e) {
                e.preventDefault();
                let id = $('#class_id').val();
                let url = id ? `/kelas/${id}` : "{{ route('class.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        class_name: $('#class_name').val(),
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
                        $('#classModal').modal('hide');
                        table.ajax.reload(null, false);
                    },
                    error: function(res) {
                        console.log(res);
                        Swal.fire('Error', res.responseJSON?.message || 'Terjadi kesalahan, coba lagi!', 'error');
                    }
                });
            });

            // Tampilkan Modal Edit Kelas
            $(document).on('click', '.editClassBtn', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');

                $('#class_id').val(id);
                $('#class_name').val(name);
                $('#classModalLabel').html('<i class="fas fa-edit mr-2"></i>Edit Nama Kelas');
                $('#classModal').modal('show');
            });

            // Delete Action
            $(document).on('click', '.delBtn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: "Hapus Kelas Ini?",
                    text: "Pastikan tidak ada siswa atau data nilai yang terhubung dengan kelas ini!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/kelas/${id}`,
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
                                table.ajax.reload(null, false);
                            },
                            error: function(r) {
                                console.log(r);
                                Swal.fire("Gagal!", r.responseJSON?.message || "Terjadi kesalahan saat menghapus kelas!", "error");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

