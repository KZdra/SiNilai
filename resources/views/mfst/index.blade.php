@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Pengaturan Fase/Semester/Tahun Ajaran') }}</h1>
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
                    <div class="card" id="resultTable" >
                        <div class="card-header">
                            <button class=" mt-2 btn btn-success " id="inputFstBtn">Input</button>
                        </div>
                        <div class="card-body p-2">

                            <table class="table table-striped table-bordered" id="valueTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Fase</th>
                                        <th>Semester</th>
                                        <th>Tahun Ajaran</th>
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
        <!-- Modal Tambah & Edit Nilai -->
        <div class="modal fade" id="tpModal" tabindex="-1" role="dialog" aria-labelledby="tpModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tpModalLabel">Input Tujuan Pembelajaran</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="valueForm">
                        <div class="modal-body">
                            <input type="hidden" id="fst_id">
                            <div class="form-group">
                                <label for="fase">Fase</label>
                                <select name="fase" id="fase" class="form-control">
                                    <option value="" selected disabled>Pilih Fase Pembelajaran</option>
                                    <option value="e">E</option>
                                    <option value="f">F</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="semester">Semester</label>
                                <select name="semester" id="semester" class="form-control">
                                    <option value="" selected disabled>Pilih Semester Pembelajaran</option>
                                    <option value="I (Satu)">I (Satu)</option>
                                    <option value="II (Dua)">II (Dua)</option>
                                    <option value="III (Tiga)">III (Tiga)</option>
                                    <option value="IV (Empat)">IV (Empat)</option>
                                    <option value="V (Lima)">V(Lima)</option>
                                    <option value="VI (Enam)">VI(Enam)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tahun_ajaran">Tahun Ajaran</label>
                                <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" placeholder="contoh pengisian 2022/2025" required>
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
                let table = $('#valueTable').DataTable({
                    "responsive": true,
                    "ajax": {
                        "url": "{{route('mfst.getData')}}",
                        "type": "GET",
                        "dataSrc": 'data'
                    },
                    "columns": [{
                            "data": null,
                            "render": function(data, type, row, meta) {
                                return meta.row + 1; // Index + 1
                            }
                        },
                        {
                            "data": "fase",
                            "render": function(data, type, row) {
                                return data ? data.toUpperCase() : '-'
                            }
                        },
                        {
                            "data": "semester",
                            "render": function(data, type, row) {
                                return data ? data : '-'
                            }
                        },
                        {
                            "data": "tahun_ajaran",
                            "render": function(data, type, row) {
                                return data ? data : '-'
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                if (row.id) {
                                    return `
                        <div class="btn-group">
                                                <button type="button" class="btn btn-info dropdown-toggle"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <button class="dropdown-item editFstBtn" data-id='${row.id}' data-fase='${row.fase}' data-semester='${row.semester}' data-tahun_ajaran='${row.tahun_ajaran}' ><i
                                                            class="fas fa-pen text-info"></i>&nbsp;Edit</button>
                                                    <div class="dropdown-divider"></div>
                                                    <button class="dropdown-item text-danger delFstBtn" data-id='${row.id}'><i
                                                            class="fas fa-trash text-danger"></i>&nbsp;Delete</button>
                                                </div>
                                            </div>
                    `;
                                } else {
                                    return `<button class="btn btn-success inputFstBtn btn-disabled">No Data</button>`
                                }

                            }
                        }
                    ]
                });

            // Init
            ///
            // Tampilkan Modal Input Nilai
            $(document).on("click", "#inputFstBtn", function() {
                $('#fst_id').val('');
                $('#fase').val('');
                $('#semester').val('');
                $('#tahun_ajaran').val('');
                $('#tpModalLabel').text('Input Fase/Semester/Tahun Ajaran');
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
                        fase:$('#fase').val(),
                        semester:$('#semester').val(),
                        tahun_ajaran:$('#tahun_ajaran').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        setTimeout(function() {
                            $('#tpModal').modal('hide');
                            $('#valueTable').DataTable().ajax.reload(null, false);
                        }, 2000);

                    },
                    error: function(res) {
                        console.log(res)
                        Swal.fire('Error', 'Terjadi kesalahan, coba lagi!', 'error');
                    }
                });
            });

            // Tampilkan Modal Edit Nilai
            $("#valueTable").on("click", ".editFstBtn", function() {
                let id = $(this).data('id');
                let fase = $(this).data('fase');
                let semester = $(this).data('semester');
                let tahun_ajaran = $(this).data('tahun_ajaran');


                $('#fst_id').val(id);
                $('#fase').val(fase);
                $('#semester').val(semester);
                $('#tahun_ajaran').val(tahun_ajaran);
                $('#tpModalLabel').text('Edit Fase/Semester/Tahun Ajaran');
                $('#tpModal').modal('show');
            });

            // Delete Action
            $("#valueTable").on("click", ".delFstBtn", function() {
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

                                setTimeout(function() {
                                    $('#valueTable').DataTable().ajax.reload(
                                        null, false);
                                }, 2000);
                            },
                            error: function(r) {
                                console.log(r)
                                Swal.fire("Gagal!", "Terjadi kesalahan, coba lagi!",
                                    "error");
                            }
                        });
                    }
                });
            })
        });
    </script>
@endsection
