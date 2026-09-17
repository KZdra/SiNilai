@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">
                        <i class="fas fa-book-open text-primary mr-2"></i>{{ __('Data Mata Pelajaran') }}
                    </h1>
                    <p class="text-muted small mb-0 mt-1">Kelola daftar mata pelajaran kurikulum umum, kejuruan, dan muatan lokal yang diajarkan di sekolah.</p>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item">Data Master</li>
                        <li class="breadcrumb-item active">Mata Pelajaran</li>
                    </ol>
                    <div class="clearfix"></div>
                    <button class="btn btn-primary font-weight-bold shadow-sm" id="addMapelBtn">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Mata Pelajaran
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
                                <i class="fas fa-lightbulb mr-2"></i>Tips Pengelolaan Mata Pelajaran
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
                                    <strong>Penulisan Nama Baku:</strong> Tuliskan nama lengkap sesuai struktur Kurikulum Merdeka (contoh: <code class="text-primary font-weight-bold">Pendidikan Pancasila</code>, <code class="text-primary font-weight-bold">Matematika</code>, <code class="text-primary font-weight-bold">Konsentrasi Keahlian RPL</code>) karena teks ini dicetak langsung di lembar rapor.
                                </li>
                                <li class="mb-1">
                                    <strong>Langkah Wajib Selanjutnya:</strong> Setelah menambahkan mata pelajaran, segera buka menu <a href="{{ route('mapel_mapping.index') }}" class="font-weight-bold text-primary"><i class="fas fa-link mr-1"></i>Mapping Mapel</a> untuk menugaskan mapel ini ke kelas dan guru pengampunya.
                                </li>
                                <li>
                                    <strong>Guru Pengampu:</strong> Guru baru dapat menginput nilai (formatif, sumatif, dan upload Excel) setelah kelasnya di-mapping ke mapel terkait.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 mb-3">
                    <div class="card card-outline card-danger shadow-sm h-100 mb-0" style="border-radius: 8px;">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title font-weight-bold text-danger mb-0">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Peringatan Penting
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
                                    <strong class="text-danger">Keterkaitan Nilai Siswa:</strong> Jangan menghapus mata pelajaran yang sedang aktif digunakan atau sudah memiliki data nilai siswa, karena akan menghilangkan komponen nilai tersebut dari rapor.
                                </li>
                                <li>
                                    <strong>Hindari Duplikasi Nama:</strong> Jangan membuat mata pelajaran ganda dengan singkatan berbeda agar guru dan wali kelas tidak salah memilih instrumen penilaian.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TABEL DATA MAPEL ────────────────────────────────────────────── -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0" style="border-radius: 10px;">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-list text-primary mr-2"></i>Daftar Mata Pelajaran
                                </h6>
                                <span class="badge badge-light border text-muted px-2 py-1 small">
                                    <i class="fas fa-sync-alt mr-1"></i>Realtime Server Data
                                </span>
                            </div>
                        </div>
                        <div class="card-body pt-0 px-3 pb-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered w-100" id="mapelTable">
                                    <thead class="bg-light text-dark">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">No</th>
                                            <th>Nama Mata Pelajaran</th>
                                            <th style="width: 140px;" class="text-center">Aksi</th>
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

        <!-- Modal Tambah & Edit Mapel -->
        <div class="modal fade" id="mapelModal" tabindex="-1" role="dialog" aria-labelledby="mapelModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                    <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold" id="mapelModalLabel">
                            <i class="fas fa-book-open mr-2"></i>Tambah Mata Pelajaran
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapelForm">
                        <div class="modal-body p-4">
                            <input type="hidden" name="mapel_id" id="mapel_id">
                            <div class="form-group mb-0">
                                <label for="mapel_name" class="font-weight-bold text-dark">
                                    Nama Mata Pelajaran <span class="text-danger">*</span>
                                </label>
                                <input type="text" max="100" class="form-control form-control-lg" id="mapel_name" name="mapel_name"
                                    placeholder="Contoh: Matematika, Informatika, Bahasa Indonesia" required>
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>Nama ini akan tercetak pada laporan hasil belajar resmi dan lembar leger nilai.
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
            var mapelTable = $('#mapelTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                language: {
                    search: "Cari Mapel:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ mapel",
                    infoEmpty: "Tidak ada data mata pelajaran",
                    zeroRecords: "Data mata pelajaran tidak ditemukan",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "&raquo;",
                        previous: "&laquo;"
                    }
                },
                ajax: {
                    url: "{{ route('mapel.getAll') }}",
                    type: "GET",
                    error: function(xhr, error, thrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengambil Data',
                            text: 'Terjadi gangguan saat memuat data mata pelajaran.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
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
                        data: "nama_mapel",
                        className: "align-middle font-weight-bold text-dark",
                        render: function(data) {
                            return `<i class="fas fa-book text-info mr-2"></i>${data || '-'}`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        className: "text-center align-middle",
                        render: function(data, type, row) {
                            let safeName = (row.nama_mapel || '').replace(/"/g, '&quot;');
                            return `
                                <div class="btn-group btn-group-sm shadow-sm" role="group">
                                    <button class="btn btn-outline-primary editMapelBtn" data-id="${row.id}" data-mapel_name="${safeName}" title="Edit Mapel">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button class="btn btn-outline-danger delMapelBtn" data-id="${row.id}" title="Hapus Mapel">
                                        <i class="fas fa-trash-alt mr-1"></i>Hapus
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            // Tampilkan Modal Tambah Mapel
            $("#addMapelBtn").on("click", function() {
                $('#mapel_id').val('');
                $('#mapel_name').val('');
                $('#mapelModalLabel').html('<i class="fas fa-plus-circle mr-2"></i>Tambah Mata Pelajaran');
                $('#mapelModal').modal('show');
            });

            // Simpan atau Update Mapel
            $('#mapelForm').submit(function(e) {
                e.preventDefault();
                let id = $('#mapel_id').val();
                let url = id ? `/mapel/${id}` : "{{ route('mapel.store') }}";
                let method = id ? "PUT" : "POST";
                let data = {
                    nama_mapel: $('#mapel_name').val(),
                };
                apiService(url, method, data)
                    .then(response => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#mapelModal').modal('hide');
                        $('#mapelTable').DataTable().ajax.reload(null, false);
                    })
                    .catch(err => {
                        Swal.fire('Error', err.responseJSON?.message || 'Terjadi kesalahan, coba lagi!', 'error');
                    });
            });

            // Tampilkan Modal Edit Mapel
            $("#mapelTable").on("click", ".editMapelBtn", function() {
                let id = $(this).data('id');
                let nama_mapel = $(this).data('mapel_name');
                $('#mapel_id').val(id);
                $('#mapel_name').val(nama_mapel);
                $('#mapelModalLabel').html('<i class="fas fa-edit mr-2"></i>Edit Mata Pelajaran');
                $('#mapelModal').modal('show');
            });

            // Delete Action
            $("#mapelTable").on("click", ".delMapelBtn", function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: "Hapus Mata Pelajaran Ini?",
                    text: "Mata pelajaran yang dihapus akan memutus relasi pada nilai siswa yang pernah diinput!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        apiService(`/mapel/${id}`, 'DELETE').then(response => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#mapelTable').DataTable().ajax.reload(null, false);
                        }).catch(err => {
                            Swal.fire("Gagal!", err.responseJSON?.message || "Terjadi kesalahan saat menghapus data!", "error");
                        });
                    }
                });
            });
        });
    </script>
@endsection

