@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Input Asesmen Formatif') }}</h1>
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
                            <h5>Fase/Semester/Tahun Ajaran: <span id="FstSel"></span></h5>
                        </div>
                        <div class="card-body p-2">

                            <table class="table table-striped table-bordered" id="valueTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Siswa</th>
                                        <th>Deskripsi Capaian Tertinggi dalam Rapor</th>
                                        <th>Deskripsi Capaian Terendah dalam Rapor</th>
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
                        <h5 class="modal-title" id="valueModalLabel">Input Formatif</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="valueForm">
                        <div class="modal-body">
                            <input type="hidden" id="student_Id" name="student_id">
                            <input type="hidden" id="class_Id" name="class_id">
                            <input type="hidden" id="mapel_Id" name="mapel_id">
                            <input type="hidden" id="fst_Id" name="fst_id">
                            <div id="tpFormList">

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
                        <h5 class="modal-title" id="upCsvModalLabel">Upload Excel TP Siswa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="csvForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <h5>Klik Dibawah Ini Untuk Download Template Nya</h5>
                            <a href="{{ route('value.download') }}" class="btn btn-success mt-2 mb-2" target="blank"><i
                                    class="fas fa-file-excel"></i>&nbsp;Download Template Untuk Excel</a>
                            <h5>Upload Excel:</h5>
                            <div class="form-group">
                                <input type="hidden" id="mapel_Id">
                                <label for="csv">File Excel</label>
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
            let tp_list = [];
            // End Of State
            // $('#upCsvBtn').hide()
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

                // Fetch Mapel via AJAX
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

                $('#valueTable').DataTable({
                    "responsive": true,
                    "ajax": {
                        "url": "{{ route('formatif.getdata') }}", // Ganti dengan URL API Anda
                        "type": "GET",
                        "data": {
                            class_id: class_id,
                            mapel_id: mapel_id,
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
                            "data": "student_name"
                        },

                        {
                            "data": "Hasil_Tp_tinggi"
                        },
                        {
                            "data": "Hasil_Tp_kurang"
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                if (row.tp_isFill) {
                                    return `
                        <div class="btn-group">
                                                <button type="button" class="btn btn-info dropdown-toggle"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <button class="dropdown-item editNilaiBtn" data-student_id='${row.student_id}'><i
                                                            class="fas fa-pen text-info"></i>&nbsp;Edit</button>
                                                    <div class="dropdown-divider"></div>
                                                    <button class="dropdown-item text-danger delNilaiBtn" data-student_id='${row.student_id}'><i
                                                            class="fas fa-trash text-danger"></i>&nbsp;Delete</button>
                                                </div>
                                            </div>
                    `;
                                } else {
                                    return `<button class="btn btn-success inputNilaiBtn" data-student_id='${row.student_id}'>Atur TP</button>`
                                }

                            }
                        }
                    ]
                });
                // $('#upCsvBtn').show();
                $('#pickFst').hide();
                $("#resultTable").show(300);
            })
            // Init
            function fetchTPList(student_id) {

                $.ajax({
                    url: "{{ route('formatif.gettplist') }}",
                    type: "GET",
                    data: {
                        student_id: student_id,
                        class_id: class_id,
                        mapel_id: mapel_id,
                        fst_id: fst_id
                    },
                    success: function(response) {
                        tp_list = response.data
                        renderTPinput(tp_list)
                    },
                    error: function(error) {
                        console.error("Error fetching TP List:", error);
                    }
                });
            }

            function renderTPinput(tpList) {
                let container = $("#tpFormList");
                container.empty(); // Kosongkan sebelum render ulang

                tpList.forEach((tp,idx) => {
                    let inputHtml = `
            <div class="tp-item form-group" id="tp-${tp.id}">
                <input type="hidden" name="tp_list[${tp.id}][id]" value="${tp.id}">
                <input type="hidden" name="tp_list[${tp.id}][tps_id]" id=tps_id value="${tp.tps_id}">
                <h5>TP-${idx+1} :</h5>
                <textarea class="form-control" disabled>${tp.tp_deskripsi}</textarea>
                <label>KKTP:</label>
                <select name="tp_list[${tp.id}][kktp]" class="form-control">
                    <option value="1" ${tp.kktp == 1 ? "selected" : ""}>Cukup</option>
                    <option value="0" ${tp.kktp == 0 ? "selected" : ""}>Kurang</option>
                </select>

                <label>Tampilkan:</label>
                <select name="tp_list[${tp.id}][tampilkan]" class="form-control">
                    <option value="1" ${tp.tampilkan == 1 ? "selected" : ""}>Ya</option>
                    <option value="0" ${tp.tampilkan == 0 ? "selected" : ""}>Tidak</option>
                </select>
            </div>
        `;
                    container.append(inputHtml);
                });
            }
            ///
            // Tampilkan Modal Input Nilai
            $("#valueTable").on("click", ".inputNilaiBtn", function() {
                $('#mapel_Id').val(mapel_id);
                $('#fst_Id').val(fst_id);
                $('#class_Id').val(class_id);
                let student_id = $(this).data('student_id');
                $('#student_Id').val(student_id);
                fetchTPList(student_id)
                $('#valueModalLabel').text('Input Formatif');
                $('#valueModal').modal('show');
            });

            // Simpan atau Update Input
            $('#valueForm').submit(function(e) {
                e.preventDefault();
                let url = "{{ route('formatif.store') }}";
                let method = "POST";
                let formData = $('#valueForm').serialize();
                $.ajax({
                    url: url,
                    method: method,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    data: formData,
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
                $('#mapel_Id').val(mapel_id);
                $('#fst_Id').val(fst_id);
                $('#class_Id').val(class_id);
                let student_id = $(this).data('student_id');
                $('#student_Id').val(student_id);
                fetchTPList(student_id)
                $('#valueModalLabel').text('Edit Nilai Formatif');
                $('#valueModal').modal('show');
            });

            // Delete Action
            $("#valueTable").on("click", ".delNilaiBtn", function() {
                let id = $(this).data('student_id');
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
                            url: `/formatif/${id}`,
                            method: "DELETE",
                            data: {
                                class_id: class_id,
                                mapel_id: mapel_id,
                                fst_id: fst_id,
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
