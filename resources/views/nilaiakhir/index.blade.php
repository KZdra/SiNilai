@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Nilai Akhir') }}</h1>
                    <button class="mt-2 btn btn-primary" id="pickClassBtn">Pilih Kelas</button>
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
                        <div class="card-header">
                            <h5>Kelas: <span id="ClassSel"></span></h5>
                        </div>
                        <div class="card-body p-2">

                            <table class="table table-striped table-bordered" id="valueTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Nilai Rata Rata Semua Mapel</th>
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
    </div>
    <!-- /.content -->
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // State
            let class_id = null;
            let class_name = ''; // Simpan nama kelas
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

                if ($.fn.DataTable.isDataTable('#valueTable')) {
                    $('#valueTable').DataTable()
                        .destroy(); // Hancurkan DataTables lama sebelum memuat ulang
                }
                $("#ClassSel").text(class_name);

                $('#valueTable').DataTable({
                    "responsive": true,
                    "ajax": {
                        "url": "{{ route('nilaiakhir.getAllStudentAVG') }}", // Ganti dengan URL API Anda
                        "type": "GET",
                        "data": {
                            class_id: class_id,
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
                            "data": "avg_nilai_semua_mapel",
                            "render": function(data) {
                                if (data) {
                                    return `<input type="text" value="${Math.round(data)}" class="form-control" disabled></input>`
                                } else {
                                    return '-';
                                }
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                let exportUrl =
                                    "{{ route('nilaiakhir.detailNilaiAkhir', ['student_id' => '__STUDENT_ID__', 'class_id' => '__VALUE_ID__']) }}";
                                exportUrl = exportUrl.replace('__STUDENT_ID__', row
                                    .student_id).replace('__VALUE_ID__', row.class_id);
                                let exportUrl2 =
                                    "{{ route('value.exportPDF', ['student_id' => '__STUDENT_ID__', 'class_id' => '__VALUE_ID__']) }}";
                                exportUrl2 = exportUrl.replace('__STUDENT_ID__', row
                                    .student_id).replace('__VALUE_ID__', row.class_id);
                                return `
                        <div class="btn-group">
                                                <button type="button" class="btn btn-info dropdown-toggle"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item " href="${exportUrl}"><i
                                                            class="fas fa-info-circle text-primary"></i>&nbsp;Detail</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item "><i
                                                            class="fas fa-print text-success"></i>&nbsp;Print</a>

                                                </div>
                                            </div>
                    `;


                            }
                        }
                    ]
                });
                $("#pickClass").hide(300);
                $("#resultTable").show(300);

            })


        });
    </script>
@endsection
