@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Data Ekstrakulikuler') }}</h1>
                    <button class="mt-2 btn btn-success" id="addMapelBtn">Tambah Ekstrakulikuler</button>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body p-2">

                            <table class="table table-striped table-bordered" id="mapelTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Eskul</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>


                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
        <!-- Modal Tambah & Edit Mapel -->
        <div class="modal fade" id="mapelModal" tabindex="-1" role="dialog" aria-labelledby="mapelModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapelModalLabel">Input Eskul</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapelForm">
                        <div class="modal-body">
                            <input type="hidden" name="eskul_id" id="eskul_id">
                            <div class="form-group">
                                <label for="nama_eskul">Nama Eskul:</label>
                                <input type="text" max="100" class="form-control" id="nama_eskul" name="nama_eskul"
                                    required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
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
            // INIz
            var mapelTable = $('#mapelTable').DataTable({
                "responsive": true,
                "ajax": {
                    "url": "{{ route('meskul.getdata') }}",
                    "type": "GET",
                    "dataSrc": 'data',
                    "error": function(xhr, error, thrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'gagal Mengambil Data',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                },
                "columns": [{
                        "data": null,
                        "render": function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        "data": "nama_eskul"
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return `<div class="btn-group">
                                                <button type="button" class="btn btn-info dropdown-toggle"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <button class="dropdown-item editMapelBtn" data-id='${row.id}' data-nama_eskul='${row.nama_eskul}' ><i
                                                            class="fas fa-pen text-info"></i>&nbsp;Edit</button>
                                                    <div class="dropdown-divider"></div>
                                                    <button class="dropdown-item text-danger delMapelBtn" data-id='${row.id}'><i
                                                            class="fas fa-trash text-danger"></i>&nbsp;Delete</button>
                                                </div>
                                            </div>`;
                        }
                    }
                ]
            });
            // INizs
            $("#addMapelBtn").on("click", function() {
                $('#eskul_id').val('');
                $('#nama_eskul').val('');
                $('#mapelModalLabel').text('Input Ekstrakulikuler');
                $('#mapelModal').modal('show');
            });
            $('#mapelForm').submit(function(e) {
                e.preventDefault();
                let id = $('#eskul_id').val();
                let url = id ? `/meskul/${id}` : "{{ route('meskul.store') }}";
                let method = id ? "PUT" : "POST";
                let data = {
                    nama_eskul: $('#nama_eskul').val(),
                }
                apiService(url, method, data)
                    .then(response => {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });

                        setTimeout(function() {
                            $('#mapelModal').modal('hide'); // Menutup modal
                            $('#mapelTable').DataTable().ajax.reload(null,
                                false); // Reload tabel
                        }, 2000);
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Terjadi kesalahan, coba lagi!', 'error');
                    });
            });
            // Tampilkan Modal Edit Nilai
            $("#mapelTable").on("click", ".editMapelBtn", function() {
                let id = $(this).data('id');
                let nama_eskul = $(this).data('nama_eskul');
                $('#eskul_id').val(id);
                $('#nama_eskul').val(nama_eskul);
                $('#mapelModalLabel').text('Edit Ekstrakulikuler');
                $('#mapelModal').modal('show');
            });

            // Delete Action
            $("#mapelTable").on("click", ".delMapelBtn", function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Hapus!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        apiService(`/meskul/${id}`, 'DELETE').then(response => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            setTimeout(function() {
                                $('#mapelTable').DataTable().ajax.reload(
                                    null, false);
                            }, 2000);
                        }).catch(err => {
                            Swal.fire("Gagal!", "Terjadi kesalahan, coba lagi!",
                                "error");
                        })
                    }
                });
            })
        });
    </script>
@endsection
