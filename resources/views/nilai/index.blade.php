@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Input Nilai') }}</h1>
                    <button class="mt-2 btn btn-primary" id="pickClassBtn">Pilih Kelas Dan Mata Pelajaran</button>
                    <button class="mt-2 btn btn-info" id="upCsvBtn">Import CSV Nilai Siswa</button>
                    <button class="mt-2 btn btn-success" id="saveAllBtn" style="display: none">Simpan Semua Nilai</button>
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
                                    <label for="fst_id">Fase/Semester/Tahun Ajaran</label>
                                    <select name="fst_id" id="fst_id" class="form-control">
                                        <option value="" selected disabled> Pilih Fase/Semester/Tahun Ajaran</option>
                                        @foreach ($fstList as $index => $fst)
                                            <option value="{{ $fst->id }}">
                                                {{ ucwords($fst->fase) . '/' . $fst->semester . '/' . $fst->tahun_ajaran . '/' . ucfirst($fst->ta) }}
                                            </option>
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
                                        <option value="" selected disabled>Loading Mata Pelajaran...</option>
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
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Sumatif 1</th>
                                        <th>Sumatif 2</th>
                                        <th>Sumatif 3</th>
                                        <th>Sumatif 4</th>
                                        <th>Sumatif 5</th>
                                        <th>Sumatif 6</th>
                                        <th>Sumatif 7</th>
                                        <th>Sumatif 8</th>
                                        <th>Sumatif 9</th>
                                        <th>Sumatif 10</th>
                                        <th>Nilai STS</th>
                                        <th>Nilai SAS</th>
                                        <th>Nilai Akhir</th>
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
                            <input type="hidden" id="mapel_id">
                            <input type="hidden" id="fst_id">
                            <div class="form-group">
                                <label for="value_daily">Sumatif 1</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily" name="value_daily">
                            </div>
                            <div class="form-group">
                                <label for="value_daily_2">Sumatif 2</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily_2" name="value_daily_2">
                            </div>

                            <div class="form-group">
                                <label for="value_daily_3">Sumatif3</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily_3" name="value_daily_3">
                            </div>

                            <div class="form-group">
                                <label for="value_daily_4">Sumatif 4</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily_4" name="value_daily_4">
                            </div>

                            <div class="form-group">
                                <label for="value_daily_5">Sumatif 5</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily_5" name="value_daily_5">
                            </div>

                            <div class="form-group">
                                <label for="value_daily_6">Sumatif 6</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily_6" name="value_daily_6">
                            </div>

                            <div class="form-group">
                                <label for="value_daily_7">Sumatif 7</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily_7" name="value_daily_7">
                            </div>

                            <div class="form-group">
                                <label for="value_daily_8">Sumatif 8</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily_8" name="value_daily_8">
                            </div>

                            <div class="form-group">
                                <label for="value_daily_9">Sumatif 9</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily_9" name="value_daily_9">
                            </div>

                            <div class="form-group">
                                <label for="value_daily_10">Sumatif 10</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_daily_10" name="value_daily_10">
                            </div>
                            <div class="form-group">
                                <label for="value_sts">Nilai STS</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_sts" name="value_sts">
                            </div>
                            <div class="form-group">
                                <label for="value_sas">Nilai SAS</label>
                                <input type="number" max="100" inputmode="numeric" class="form-control"
                                    id="value_sas" name="value_sas">
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
                                <input type="hidden" id="mapel_id">
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
            // State
            let class_id = null;
            let mapel_id = null;
            let fst_id = null;
            let class_name = '';
            let mapel_name = '';
            let fst_name = '';
            // End Of State
            $('#upCsvBtn').hide()
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

                // Ambil daftar Mapel yang aktif via AJAX
                $.ajax({
                    url: "{{ route('value.getMapel') }}",
                    type: "GET",
                    data: {
                        class_id: class_id,
                        fst_id: fst_id
                    },
                    success: function(response) {
                        let html = '<option value="" selected disabled>Pilih Mata Pelajaran</option>';
                        if(response.length === 0) {
                            html = '<option value="" selected disabled>Belum ada mapel aktif untuk kelas ini di semester ini.</option>';
                        } else {
                            response.forEach(function(item) {
                                html += `<option value="${item.id}">${item.nama_mapel}</option>`;
                            });
                        }
                        $('#mapel_id').html(html);
                        $("#pickFst").hide(300);
                        $("#pickMapel").show(300);
                    },
                    error: function() {
                        SwalHelper.showError('Gagal mengambil data Mata Pelajaran.');
                    }
                });
            })

            $('#mapelForm').submit(function(e) {
                e.preventDefault();
                mapel_id = $('#mapel_id').val();
                mapel_name = $('#mapel_id option:selected').text();

                if (!mapel_id) {
                    SwalHelper.showError('Silahkan Pilih Mata Pelajaran Terlebih Dahulu');
                    return;
                }

                if ($.fn.DataTable.isDataTable('#valueTable')) {
                    $('#valueTable').DataTable()
                        .destroy(); // Hancurkan DataTables lama sebelum memuat ulang
                }
                $("#MapelSel").text(mapel_name);
                $("#FstSel").text(fst_name);
                $("#ClassSel").text(class_name);

                let isAllNew = false;
                
                function renderInput(data, fieldName, row) {
                    let val = data !== null && data !== undefined ? Math.round(data) : '';
                    let textVal = val !== '' ? val : '-';
                    let textClass = isAllNew ? 'd-none' : '';
                    let inputClass = isAllNew ? '' : 'd-none';
                    return `
                        <span class="val-text ${textClass}">${textVal}</span>
                        <input type="number" max="100" class="form-control form-control-sm val-input ${inputClass}" data-field="${fieldName}" value="${val}" style="min-width: 70px;">
                    `;
                }

                $('#valueTable').DataTable({
                    "paging": false,
                    "responsive": false,
                    "scrollX": true,
                    "ajax": {
                        "url": "{{ route('value.getByClass') }}",
                        "type": "GET",
                        "data": {
                            class_id: class_id,
                            mapel_id: mapel_id,
                            fst_id: fst_id,
                        },
                        "dataSrc": function(json) {
                            let allNew = true;
                            let anyNew = false;
                            
                            if (json.data && json.data.length > 0) {
                                json.data.forEach(row => {
                                    if (row.value_id) allNew = false;
                                    else anyNew = true;
                                });
                                isAllNew = allNew && anyNew;
                            } else {
                                isAllNew = false;
                            }
                            
                            if (isAllNew) {
                                $('#saveAllBtn').show();
                            } else {
                                $('#saveAllBtn').hide();
                            }
                            
                            return json.data;
                        }
                    },
                    "createdRow": function(row, data, dataIndex) {
                        $(row).attr('data-student_id', data.student_id);
                    },
                    "columns": [{
                            "data": null,
                            "render": function(data, type, row, meta) {
                                return meta.row + 1; // Index + 1
                            }
                        },
                        { "data": "student_name" },
                        { "data": "class_name" },
                        { "data": "value_daily", "render": function(data, type, row) { return renderInput(data, 'value_daily', row); } },
                        { "data": "value_daily_2", "render": function(data, type, row) { return renderInput(data, 'value_daily_2', row); } },
                        { "data": "value_daily_3", "render": function(data, type, row) { return renderInput(data, 'value_daily_3', row); } },
                        { "data": "value_daily_4", "render": function(data, type, row) { return renderInput(data, 'value_daily_4', row); } },
                        { "data": "value_daily_5", "render": function(data, type, row) { return renderInput(data, 'value_daily_5', row); } },
                        { "data": "value_daily_6", "render": function(data, type, row) { return renderInput(data, 'value_daily_6', row); } },
                        { "data": "value_daily_7", "render": function(data, type, row) { return renderInput(data, 'value_daily_7', row); } },
                        { "data": "value_daily_8", "render": function(data, type, row) { return renderInput(data, 'value_daily_8', row); } },
                        { "data": "value_daily_9", "render": function(data, type, row) { return renderInput(data, 'value_daily_9', row); } },
                        { "data": "value_daily_10", "render": function(data, type, row) { return renderInput(data, 'value_daily_10', row); } },
                        { "data": "value_sts", "render": function(data, type, row) { return renderInput(data, 'value_sts', row); } },
                        { "data": "value_sas", "render": function(data, type, row) { return renderInput(data, 'value_sas', row); } },
                        {
                            "data": "average_value",
                            "render": function(data) {
                                return data ? Math.round(data) : '-';
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                if (isAllNew) {
                                    return `<span class="text-muted"><i class="fas fa-edit"></i> Edit Semua</span>`;
                                }

                                if (row.value_id) {
                                    return `
                                        <div class="d-flex" style="gap: 5px;">
                                            <button type="button" class="btn btn-sm btn-info editRowBtn" data-id='${row.value_id}' data-student_id='${row.student_id}'><i class="fas fa-pen"></i> Edit</button>
                                            <button type="button" class="btn btn-sm btn-success saveRowBtn d-none" data-id='${row.value_id}' data-student_id='${row.student_id}'><i class="fas fa-save"></i> Save</button>
                                            <button type="button" class="btn btn-sm btn-secondary cancelRowBtn d-none"><i class="fas fa-times"></i></button>
                                            <button type="button" class="btn btn-sm btn-danger delNilaiBtn" data-id='${row.value_id}'><i class="fas fa-trash"></i></button>
                                        </div>
                                    `;
                                } else {
                                    return `
                                        <div class="d-flex" style="gap: 5px;">
                                            <button type="button" class="btn btn-sm btn-primary inputRowBtn" data-student_id='${row.student_id}'><i class="fas fa-plus"></i> Input</button>
                                            <button type="button" class="btn btn-sm btn-success saveRowBtn d-none" data-id='' data-student_id='${row.student_id}'><i class="fas fa-save"></i> Save</button>
                                            <button type="button" class="btn btn-sm btn-secondary cancelRowBtn d-none"><i class="fas fa-times"></i></button>
                                        </div>
                                    `;
                                }
                            }
                        }
                    ]
                });
                $('#upCsvBtn').show();
                $('#pickFst').hide();
                $("#resultTable").show(300);
            })
            // Handle paste from Excel
            $('#valueTable').on('paste', '.val-input', function(e) {
                e.preventDefault();
                let text = (e.originalEvent || e).clipboardData.getData('text/plain');
                if (!text) return;
                
                let rows = text.split(/\r\n|\n|\r/);
                let currentInput = $(this);
                let currentTd = currentInput.closest('td');
                let currentTr = currentTd.closest('tr');
                
                let startRowIndex = currentTr.index();
                let startColIndex = currentTd.index();
                
                let tableRows = $('#valueTable tbody tr');
                
                for (let i = 0; i < rows.length; i++) {
                    let rowData = rows[i].trim();
                    if (!rowData && i === rows.length - 1) continue; // Skip empty last row
                    
                    let cols = rowData.split(/\t/);
                    let targetTr = tableRows.eq(startRowIndex + i);
                    if (!targetTr.length) break;
                    
                    for (let j = 0; j < cols.length; j++) {
                        let targetTd = targetTr.find('td').eq(startColIndex + j);
                        if (!targetTd.length) continue;
                        
                        let targetInput = targetTd.find('.val-input');
                        if (targetInput.length && !targetInput.prop('readonly') && !targetInput.prop('disabled')) {
                            let val = cols[j].trim().replace(',', '.');
                            if (!isNaN(val) && val !== '') {
                                targetInput.val(val);
                                targetInput.trigger('input'); 
                            }
                        }
                    }
                }
            });
            // Save All Actions
            $("#saveAllBtn").click(function() {
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                
                let requests = [];
                
                $('#valueTable tbody tr').each(function() {
                    let tr = $(this);
                    let student_id = tr.attr('data-student_id');
                    if (!student_id) return;
                    
                    let data = {
                        mapel_id: mapel_id,
                        student_id: student_id,
                        fst_id: fst_id,
                        class_id: class_id,
                        _token: "{{ csrf_token() }}"
                    };
                    
                    let hasValue = false;
                    tr.find('.val-input').each(function() {
                        let field = $(this).data('field');
                        let val = $(this).val();
                        if (val !== '') {
                            hasValue = true;
                        }
                        data[field] = val;
                    });
                    
                    if (hasValue) {
                        requests.push($.ajax({
                            url: "{{ route('value.store') }}",
                            method: "POST",
                            data: data
                        }));
                    }
                });
                
                if (requests.length === 0) {
                    Swal.fire('Info', 'Tidak ada nilai yang diisi.', 'info');
                    btn.prop('disabled', false).html('Simpan Semua Nilai');
                    return;
                }
                
                Promise.all(requests).then(function(responses) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil menyimpan semua nilai!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#valueTable').DataTable().ajax.reload(null, false);
                }).catch(function(err) {
                    console.log(err);
                    Swal.fire('Error', 'Terjadi kesalahan saat menyimpan sebagian/semua nilai.', 'error');
                    $('#valueTable').DataTable().ajax.reload(null, false);
                }).finally(function() {
                    btn.prop('disabled', false).html('Simpan Semua Nilai');
                });
            });
            ///
            // Inline Edit Actions
            $("#valueTable").on("click", ".editRowBtn, .inputRowBtn", function() {
                let tr = $(this).closest('tr');
                tr.find('.val-text').addClass('d-none');
                tr.find('.val-input').removeClass('d-none');
                
                tr.find('.editRowBtn, .inputRowBtn, .delNilaiBtn').addClass('d-none');
                tr.find('.saveRowBtn, .cancelRowBtn').removeClass('d-none');
            });

            $("#valueTable").on("click", ".cancelRowBtn", function() {
                let tr = $(this).closest('tr');
                tr.find('.val-input').addClass('d-none');
                tr.find('.val-text').removeClass('d-none');
                
                // reset values
                tr.find('.val-input').each(function() {
                    let textVal = $(this).siblings('.val-text').text();
                    $(this).val(textVal === '-' ? '' : textVal);
                });

                tr.find('.editRowBtn, .inputRowBtn, .delNilaiBtn').removeClass('d-none');
                tr.find('.saveRowBtn, .cancelRowBtn').addClass('d-none');
            });

            $("#valueTable").on("click", ".saveRowBtn", function() {
                let tr = $(this).closest('tr');
                let btn = $(this);
                let id = btn.data('id');
                let student_id = btn.data('student_id');
                
                let url = id ? `/nilai/${id}` : "{{ route('value.store') }}";
                let method = id ? "PUT" : "POST";
                
                let data = {
                    mapel_id: mapel_id,
                    student_id: student_id,
                    fst_id: fst_id,
                    class_id: class_id,
                    _token: "{{ csrf_token() }}"
                };
                
                tr.find('.val-input').each(function() {
                    let field = $(this).data('field');
                    let val = $(this).val();
                    data[field] = val;
                });
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                
                $.ajax({
                    url: url,
                    method: method,
                    data: data,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#valueTable').DataTable().ajax.reload(null, false);
                    },
                    error: function(res) {
                        console.log(res);
                        Swal.fire('Error', 'Terjadi kesalahan, coba lagi!', 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
                    }
                });
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
                $('#mapel_id').val(mapel_id);
                $('#fst_id').val(fst_id);
                $('#upCsvModal').modal('show');
            });
            $('#csvForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append('mapel_id', mapel_id);
                formData.append('fst_id', fst_id);


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
