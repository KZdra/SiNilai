@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Master Tujuan Pembelajaran') }}</h1>
                    <button class="mt-2 btn btn-primary" id="pickClassBtn">Pilih Kelas Dan Mata Pelajaran</button>
                    <button class=" mt-2 btn btn-success " id="inputTpBtn">Input</button>
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
                    <div class="card" id="pickClass" style="display: none">
                        <div class="card-body p-2">
                            <form id="filterForm">
                                <div class="form-group">
                                    <label for="class_id">Kelas</label>
                                    <select name="class_id" id="class_id" class="form-control">
                                        <option value="" selected disabled>Pilih Kelas</option>
                                        @foreach ($classList as $index => $class)
                                            <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">Submit</button>
                            </form>
                        </div>
                    </div>
                    <div class="card" id="pickMapel" style="display: none;">
                        <div class="card-body p-2">
                            <form id="mapelForm">
                                <div class="form-group">
                                    <label for="mapel_id">Mata Pelajaran</label>
                                    <select name="mapel_id" id="mapel_id" class="form-control">
                                        <option value="" selected disabled>Pilih Mata Pelajaran</option>
                                        @foreach ($mapelList as $index => $mapel)
                                            <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">Submit</button>
                            </form>
                        </div>
                    </div>
                    <div class="card" id="pickFst" style="display: none;">
                        <div class="card-body p-2">
                            <form id="fstForm">
                                <div class="form-group">
                                    <label for="fst_id">Fase/Semester/Tahun Ajaran</label>
                                    <select name="fst_id" id="fst_id" class="form-control">
                                        <option value="" selected disabled> Pilih Fase/Semester/Tahun Ajaran</option>
                                        @foreach ($fstList as $index => $fst)
                                            <option value="{{ $fst->id }}">
                                                {{ ucwords($fst->fase) . '/' . $fst->semester . '/' . $fst->tahun_ajaran }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">Submit</button>
                            </form>
                        </div>
                    </div>
                    <div class="card" id="resultTable" style="display: none">
                        <div class="card-header">
                            <h5>Kelas: <span id="ClassSel"></span></h5>
                            <h5>Mata Pelajaran: <span id="MapelSel"></span></h5>
                            <h5>Fase/Semester/Tahun: <span id="FstSel"></span></h5>
                        </div>
                        <div class="card-body p-2">

                            <table class="table table-striped table-bordered" id="valueTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tujuan Pembelajaran</th>
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
                            <input type="hidden" id="tp_id">
                            <input type="hidden" id="class_id">
                            <input type="hidden" id="mapel_id">
                            <div class="form-group">
                                <label for="tp_deskripsi">Tujuan Pembelajaran</label>
                                <input type="textarea" class="form-control" id="tp_deskripsi" name="tp_deskripsi" required>
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
            // State
            $('#inputTpBtn').hide();
            let class_id = null;
            let mapel_id = null;
            let fst_id = null;
            let class_name = ''; // Simpan nama kelas
            let mapel_name = ''; // Simpan nama mapel
            let fst_name = '';
            // End Of State
            // NIlai Section (Filter)
            $('#pickClassBtn').click(function() {
                $("#pickClass").slideToggle(300);
            })
            $('#filterForm').submit(function(e) {
                e.preventDefault();
                class_id = $('#class_id').val();
                class_name = $('#class_id option:selected').text();
                if (!class_id) {
                    SwalHelper.showError('Silahkan Pilih Kelas Terlebih Dahulu');
                    return;
                }
                $("#pickClass").hide(300);
                $("#pickMapel").show(300);
            })
            $('#mapelForm').submit(function(e) {
                e.preventDefault();
                mapel_id = $('#mapel_id').val();
                mapel_name = $('#mapel_id option:selected').text();

                if (!mapel_id) {
                    SwalHelper.showError('Silahkan Pilih Mata Pelajaran Terlebih Dahulu');
                    return;
                }
                $("#pickMapel").hide(300);
                $("#pickFst").show();

            })
            $('#fstForm').submit(function(e) {
                e.preventDefault();
                fst_id = $('#fst_id').val();
                fst_name = $('#fst_id option:selected').text();

                if (!fst_id) {
                    SwalHelper.showError('Silahkan Pilih Fase/Semester/Tahun Ajaran Terlebih Dahulu');
                    return;
                }

                if ($.fn.DataTable.isDataTable('#valueTable')) {
                    $('#valueTable').DataTable()
                        .destroy(); // Hancurkan DataTables lama sebelum memuat ulang
                }
                $("#MapelSel").text(mapel_name);
                $("#FstSel").text(fst_name);
                $("#ClassSel").text(class_name);
                $('#valueTable').DataTable({
                    "responsive": true,
                    "ajax": {
                        "url": "{{ route('mastertp.getdata') }}",
                        "type": "GET",
                        "data": {
                            mapel_id: mapel_id,
                            class_id: class_id,
                            fst_id: fst_id
                        },
                        "dataSrc": 'data'
                    },
                    "columns": [{
                            "data": null,
                            "render": function(data, type, row, meta) {
                                return meta.row + 1; // Index + 1
                            }
                        },
                        {
                            "data": "tp_deskripsi",
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
                                                    <button class="dropdown-item editTpBtn" data-id='${row.id}' data-tp_deskripsi="${row.tp_deskripsi}" ><i
                                                            class="fas fa-pen text-info"></i>&nbsp;Edit</button>
                                                    <div class="dropdown-divider"></div>
                                                    <button class="dropdown-item text-danger delTpBtn" data-id='${row.id}'><i
                                                            class="fas fa-trash text-danger"></i>&nbsp;Delete</button>
                                                </div>
                                            </div>
                    `;
                                } else {
                                    return `<button class="btn btn-success inputTpBtn btn-disabled">No Data</button>`
                                }

                            }
                        }
                    ]
                });
                $('#pickFst').hide();
                $('#inputTpBtn').show();
                $("#resultTable").show(300);
            })
            // Init
            ///
            // Tampilkan Modal Input Nilai
            $(document).on("click", "#inputTpBtn", function() {
                $('#tp_id').val('');
                $('#fst_id').val(fst_id);
                $('#mapel_id').val(mapel_id);
                $('#class_id').val(class_id);
                $('#tp_deskripsi').val('');
                $('#tpModalLabel').text('Input Tujuan Pembelajaran');
                $('#tpModal').modal('show');
            });

            // Simpan atau Update Input
            $('#valueForm').submit(function(e) {
                e.preventDefault();
                let id = $('#tp_id').val();
                let url = id ? `/mastertp/${id}` : "{{ route('mastertp.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        mapel_id: $('#mapel_id').val(),
                        fst_id: $('#fst_id').val(),
                        class_id: $('#class_id').val(),
                        tp_deskripsi: $('#tp_deskripsi').val(),
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
            $("#valueTable").on("click", ".editTpBtn", function() {
                let id = $(this).data('id');
                let tp_deskripsi = $(this).data('tp_deskripsi');


                $('#tp_id').val(id);
                $('#fst_id').val(fst_id);
                $('#mapel_id').val(mapel_id);
                $('#class_id').val(class_id);
                $('#tp_deskripsi').val(tp_deskripsi);
                $('#tpModalLabel').text('Edit Tujuan Pembelajaran');
                $('#tpModal').modal('show');
            });

            // Delete Action
            $("#valueTable").on("click", ".delTpBtn", function() {
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
                            url: `/mastertp/${id}`,
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
