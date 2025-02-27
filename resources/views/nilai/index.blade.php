@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Input Nilai') }}</h1>
                    <button class="mt-2 btn btn-primary" id="pickClassBtn">Pilih Kelas</button>
                    <button class="mt-2 btn btn-info" id="upCsvBtn">Import CSV Nilai Siswa</button>
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
                    <div class="card" id="resultTable" style="display: none">
                        <div class="card-body p-2">

                            <table class="table table-striped table-bordered" id="valueTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Nilai Harian</th>
                                        <th>Nilai STS</th>
                                        <th>Nilai SAS</th>
                                        <th>Rata Rata</th>
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
        <div class="modal fade" id="valueModal" tabindex="-1" role="dialog" aria-labelledby="valueModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="valueModalLabel">Input Nilai</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="valueForm">
                        <div class="modal-body">
                            <input type="hidden" id="value_id">
                            <input type="hidden" id="student_id">
                            <div class="form-group">
                                <label for="value_daily">Nilai Harian</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control" id="value_daily" name="value_daily" required>
                            </div>
                            <div class="form-group">
                                <label for="value_sts">Nilai STS</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control" id="value_sts" name="value_sts" required>
                            </div>
                            <div class="form-group">
                                <label for="value_sas">Nilai SAS</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control" id="value_sas" name="value_sas" required>
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
        {{-- Modal CSV --}}
        <div class="modal fade" id="upCsvModal" tabindex="-1" role="dialog" aria-labelledby="upCsvModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="upCsvModalLabel">Upload CSV Nilai Siswa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="csvForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <h5>Klik Dibawah Ini Untuk Download Template Nya</h5>
                            <a href="{{ route('value.download') }}" class="btn btn-success mt-2 mb-2" target="blank"><i
                                    class="fas fa-file-excel"></i>&nbsp;Download Template Untuk CSV</a>
                            <h5>Upload CSV:</h5>
                            <div class="form-group">
                                <label for="csv">File CSV</label>
                                <input type="file" class="form-control" id="csv" name="csv" required>
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
            $('#upCsvBtn').hide()
            // NIlai Section (Filter)

            $('#pickClassBtn').click(function() {
                $("#pickClass").slideToggle(300);
            })
            $('#filterForm').submit(function(e) {
                e.preventDefault();
                let class_id = $('#class_id').val();

                if ($.fn.DataTable.isDataTable('#valueTable')) {
                    $('#valueTable').DataTable()
                        .destroy(); // Hancurkan DataTables lama sebelum memuat ulang
                }
                $('#valueTable').DataTable({
                    "responsive": true,
                    "ajax": {
                        "url": "{{ route('value.getByClass') }}", // Ganti dengan URL API Anda
                        "type": "GET",
                        "data": {
                            class_id: class_id
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
                            "data": "student_name"
                        },
                        {
                            "data": "class_name"
                        },
                        {
                            "data": "value_daily",
                            "render": function(data) {
                                return data ? Math.round(data) :
                                    '-'; // Jika null, tampilkan "-"
                            }
                        },
                        {
                            "data": "value_sts",
                            "render": function(data) {
                                return data ? Math.round(data) : '-';
                            }
                        },
                        {
                            "data": "value_sas",
                            "render": function(data) {
                                return data ? Math.round(data) : '-';
                            }
                        },
                        {
                            "data": "average_value",
                            "render": function(data) {
                                return data ? Math.round(data) : '-';
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                let exportUrl = "{{ route('value.exportPDF', ['student_id' => '__STUDENT_ID__', 'value_id' => '__VALUE_ID__']) }}";
                                exportUrl = exportUrl.replace('__STUDENT_ID__', row.student_id).replace('__VALUE_ID__', row.value_id);
                                if (row.value_id) {
                                    return `
                        <div class="btn-group">
                                                <button type="button" class="btn btn-info dropdown-toggle"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item " href="${exportUrl}" target='blank' ><i
                                                            class="fas fa-print text-primary"></i>&nbsp;Print</a>
                                                    <button class="dropdown-item editNilaiBtn" data-id='${row.value_id}' data-student_id='${row.student_id}' data-value_daily='${Math.round(row.value_daily)}' data-value_sts='${Math.round(row.value_sts)}' data-value_sas='${Math.round(row.value_sas)}' ><i
                                                            class="fas fa-pen text-info"></i>&nbsp;Edit</button>
                                                    <div class="dropdown-divider"></div>
                                                    <button class="dropdown-item text-danger delNilaiBtn" data-id='${row.value_id}'><i
                                                            class="fas fa-trash text-danger"></i>&nbsp;Delete</button>
                                                </div>
                                            </div>
                    `;
                                } else {
                                    return `<button class="btn btn-success inputNilaiBtn" data-student_id='${row.student_id}'>Input</button>`
                                }

                            }
                        }
                    ]
                });
                $('#upCsvBtn').show()
                $("#resultTable").show(300);
                $("#pickClass").hide(300);
            })
            // Init
            ///
            // Tampilkan Modal Input Nilai
            $("#valueTable").on("click", ".inputNilaiBtn", function() {
                let student_id = $(this).data('student_id');
                $('#value_id').val('');
                $('#student_id').val(student_id);
                $('#value_daily').val('');
                $('#value_sts').val('');
                $('#value_sas').val('');
                $('#valueModalLabel').text('Input Nilai');
                $('#valueModal').modal('show');
            });

            // Simpan atau Update Input
            $('#valueForm').submit(function(e) {
                e.preventDefault();
                let id = $('#value_id').val();
                let url = id ? `/nilai/${id}` : "{{ route('value.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        student_id: $('#student_id').val(),
                        value_daily: $('#value_daily').val(),
                        value_sts: $('#value_sts').val(),
                        value_sas: $('#value_sas').val(),
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
                            $('#valueModal').modal('hide');
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
            $("#valueTable").on("click", ".editNilaiBtn", function() {
                let id = $(this).data('id');
                let student_id = $(this).data('student_id');
                let value_daily = $(this).data('value_daily');
                let value_sts = $(this).data('value_sts');
                let value_sas = $(this).data('value_sas');


                $('#value_id').val(id);
                $('#student_id').val(student_id);
                $('#value_daily').val(value_daily);
                $('#value_sts').val(value_sts);
                $('#value_sas').val(value_sas);
                $('#valueModalLabel').text('Edit Nilai');
                $('#valueModal').modal('show');
            });

            // Delete Action
            $("#valueTable").on("click", ".delNilaiBtn", function() {
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
                            url: `/nilai/${id}`,
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
            //
            $('#upCsvBtn').click(function() {
                $('#csv').val(null);
                $('#upCsvModal').modal('show');
            });
            $('#csvForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('value.import') }}",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        '_method': 'post'
                    },
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        setTimeout(function() {
                            $('#upCsvModal').modal('hide');
                            $('#valueTable').DataTable().ajax.reload(null, false);
                        }, 2000);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || "Terjadi kesalahan!",
                        });
                    }
                });
            });
        });
    </script>
@endsection
