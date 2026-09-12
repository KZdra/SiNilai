@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Master Kelas') }}</h1>
                    <button class="mt-2 btn btn-success" id="addClassBtn">Tambah Kelas</button>
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

                            <table class="table table-striped table-bordered w-100" id="classTable">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">No</th>
                                        <th>Kelas</th>
                                        <th style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
        <!-- Modal Tambah & Edit Kelas -->
        <div class="modal fade" id="classModal" tabindex="-1" role="dialog" aria-labelledby="classModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="classModalLabel">Tambah Kelas</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="classForm">
                        <div class="modal-body">
                            <input type="hidden" id="class_id">
                            <div class="form-group">
                                <label for="class_name">Nama Kelas</label>
                                <input type="text" class="form-control" id="class_name" name="class_name" required>
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
            // Init DataTable with Server-Side AJAX
            let table = $('#classTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('class.getData') }}",
                    type: "GET"
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'class_name', defaultContent: '-' },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            let safeName = (row.class_name || '').replace(/"/g, '&quot;');
                            return `
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary editClassBtn" data-id="${row.id}" data-name="${safeName}">Edit</button>
                                    <button class="btn btn-sm btn-danger delBtn" data-id="${row.id}">Delete</button>
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
                $('#classModalLabel').text('Tambah Kelas');
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
                            title: response.message,
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
                $('#classModalLabel').text('Edit Kelas');
                $('#classModal').modal('show');
            });

            // Delete Action
            $(document).on('click', '.delBtn', function() {
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
                                Swal.fire("Gagal!", "Terjadi kesalahan, coba lagi!", "error");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
