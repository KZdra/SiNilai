@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Input Penilaian Ekstrakulikuler') }}</h1>
                    <button class="mt-2 btn btn-primary" id="pickClassBtn">Pilih Kelas Dan Mata Pelajaran</button>
                    {{-- <button class="mt-2 btn btn-info" id="upCsvBtn">Import Excel Asesmen Formatif Siswa</button> --}}
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
                                    @if ($className)
                                        <select name="class_id" id="class_id" class="form-control" disabled>
                                            <option value="{{ Auth::user()->class_id }}" selected>{{ $className }}
                                            </option>
                                        </select>
                                    @else
                                        <select name="class_id" id="class_id" class="form-control">
                                            <option value="" selected disabled>Pilih Kelas</option>
                                            @foreach ($classList as $index => $class)
                                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-success">Submit</button>
                            </form>
                        </div>
                    </div>

                    <div class="card" id="pickFst" style="display: none;">
                        <div class="card-body p-2">
                            <form id="fstForm">
                                <div class="form-group">
                                    <label for="fst_id">Fase/Semester/Tahun Ajaran dan faseSemester</label>
                                    <select name="fst_id" id="fst_id" class="form-control">
                                        <option value="" selected disabled> Pilih Fase/Semester/Tahun Ajaran dan faseSemester</option>
                                        @foreach ($fstList as $index => $fst)
                                            <option value="{{ $fst->id }}">
                                                {{ ucwords($fst->fase) . '/' . $fst->semester . '/' . $fst->tahun_ajaran.'/'.ucfirst($fst->ta) }}
                                            </option>
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
                            <h5>Fase/Semester/Tahun Ajaran: <span id="FstSel"></span></h5>
                        </div>
                        <div class="card-body p-2">
                            <button id="btnSaveBulk" class="btn btn-primary mb-3"><i class="fas fa-save"></i> Simpan Semua Penilaian</button>
                            <table class="table table-striped table-bordered" id="valueTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Siswa</th>
                                        <th>Pilihan Eskul 1</th>
                                        <th>Deskripsi 1</th>
                                        <th>Pilihan Eskul 2</th>
                                        <th>Deskripsi 2</th>
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

    </div>
    <!-- /.content -->
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // State
            let class_id = null;
            let fst_id = null;
            let class_name = '';
            let fst_name = '';
            let eskulList = @json($eskulList);

            function generateEskulOptions(selectedId) {
                let html = '<option value="">Pilih Ekstrakurikuler</option>';
                eskulList.forEach(e => {
                    let sel = selectedId == e.id ? 'selected' : '';
                    html += `<option value="${e.id}" ${sel}>${e.nama_eskul}</option>`;
                });
                return html;
            }

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
                $("#pickFst").show(300);
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
                    $('#valueTable').DataTable().destroy(); 
                }
                $("#FstSel").text(fst_name);
                $("#ClassSel").text(class_name);

                $('#valueTable').DataTable({
                    "responsive": true,
                    "paging": false,
                    "ajax": {
                        "url": "{{ route('peskul.getdata') }}", 
                        "type": "GET",
                        "data": function(d) {
                            d.class_id = class_id;
                            d.fst_id = fst_id;
                        },
                        "dataSrc": 'data'
                    },
                    "columns": [{
                            "data": null,
                            "render": function(data, type, row, meta) {
                                return meta.row + 1; 
                            }
                        },
                        {
                            "data": "student_name"
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                let eskul = row.eskuls && row.eskuls[0] ? row.eskuls[0] : null;
                                let valId = eskul ? eskul.eskul_id : '';
                                return `<select class="form-control sel-eskul" data-student="${row.student_id}" data-index="0">${generateEskulOptions(valId)}</select>`;
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                let eskul = row.eskuls && row.eskuls[0] ? row.eskuls[0] : null;
                                let valDesc = eskul ? eskul.nilai_eskul : '';
                                return `<textarea class="form-control txt-eskul" data-student="${row.student_id}" data-index="0" rows="2">${valDesc}</textarea>`;
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                let eskul = row.eskuls && row.eskuls[1] ? row.eskuls[1] : null;
                                let valId = eskul ? eskul.eskul_id : '';
                                return `<select class="form-control sel-eskul" data-student="${row.student_id}" data-index="1">${generateEskulOptions(valId)}</select>`;
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                let eskul = row.eskuls && row.eskuls[1] ? row.eskuls[1] : null;
                                let valDesc = eskul ? eskul.nilai_eskul : '';
                                return `<textarea class="form-control txt-eskul" data-student="${row.student_id}" data-index="1" rows="2">${valDesc}</textarea>`;
                            }
                        }
                    ]
                });
                $('#pickFst').hide();
                $("#resultTable").show(300);
            })


            // Simpan Secara Massal (Bulk)
            $('#btnSaveBulk').click(function() {
                let payload = {};
                let table = $('#valueTable').DataTable();
                let rows = table.rows().nodes(); 
                
                $(rows).each(function() {
                    let selects = $(this).find('.sel-eskul');
                    
                    selects.each(function() {
                        let stId = $(this).data('student');
                        let idx = $(this).data('index');
                        let eskul_id = $(this).val();
                        let desc = $(this).closest('tr').find(`.txt-eskul[data-index="${idx}"]`).val();
                        
                        if (!payload[stId]) payload[stId] = [];
                        
                        if (eskul_id && desc) {
                            payload[stId].push({
                                eskul_id: eskul_id,
                                nilai_eskul: desc
                            });
                        }
                    });
                });
                
                let btn = $(this);
                let originHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                
                $.ajax({
                    url: "{{ route('peskul.storeBulk') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        class_id: class_id,
                        fst_id: fst_id,
                        students: payload
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html(originHtml);
                        SwalHelper.showSuccess(res.message);
                        $('#valueTable').DataTable().ajax.reload(null, false);
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html(originHtml);
                        SwalHelper.showError('Gagal menyimpan nilai');
                    }
                });
            });
            $('#upCsvBtn').click(function() {
                $('#csv').val(null);
                $('#mapel_id').val(mapel_id);
                $('#upCsvModal').modal('show');
            });
            $('#csvForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append('mapel_id', mapel_id);


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
