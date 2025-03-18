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
                    <div class="card" id="pickMapel" style="display: none;">
                        <div class="card-body p-2">
                            <form id="mapelForm">
                                <div class="form-group">
                                    <label for="eskul_id">Ekstrakulikuler</label>
                                    <select name="eskul_id" id="eskul_id" class="form-control">
                                        <option value="" selected disabled>Pilih Ekstrakulikuler</option>
                                        @foreach ($eskulList as $index => $eskul)
                                            <option value="{{ $eskul->id }}">{{ $eskul->nama_eskul }}</option>
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
                            <h5>Ekstrakulikuler: <span id="EskulSel"></span></h5>
                            <h5>Fase/Semester/Tahun Ajaran: <span id="FstSel"></span></h5>
                        </div>
                        <div class="card-body p-2">

                            <table class="table table-striped table-bordered" id="valueTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Siswa</th>
                                        <th>Nilai Eskul</th>
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
                        <h5 class="modal-title" id="valueModalLabel">Input Penilaian Ekstrakulikuler</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="valueForm">
                        <div class="modal-body">
                            <input type="hidden" id="nilai_eskul_Id" name="nilai_eskul_id">
                            <input type="hidden" id="eskul_Id" name="nilai_eskul_id">
                            <input type="hidden" id="student_Id" name="student_id">
                            <input type="hidden" id="class_Id" name="class_id">
                            <input type="hidden" id="fst_Id" name="fst_id">
                            <div class="form-group">
                                <label for="penilaian_eskul">Nilai Eskul</label>
                                <textarea name="penilaian_eskul" id="penilaian_eskul" placeholder="Contoh: Cukup Baik Dalam bermain Alat musik" class="form-control"></textarea>
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
            let eskul_id = null;
            let fst_id = null;
            let class_name = '';
            let eskul_name = '';
            let fst_name = '';
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
                $("#pickMapel").show(300);
            })
            $('#mapelForm').submit(function(e) {
                e.preventDefault();
                eskul_id = $('#eskul_id').val();
                eskul_name = $('#eskul_id option:selected').text();

                if (!eskul_id) {
                    SwalHelper.showError('Silahkan Pilih Ekstrakulikuler Terlebih Dahulu');
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
                    $('#valueTable').DataTable().destroy(); // Hancurkan DataTables lama sebelum memuat ulang
                }
                $("#EskulSel").text(eskul_name);
                $("#FstSel").text(fst_name);
                $("#ClassSel").text(class_name);

                $('#valueTable').DataTable({
                    "responsive": true,
                    "ajax": {
                        "url": "{{ route('peskul.getdata') }}", // Ganti dengan URL API Anda
                        "type": "GET",
                        "data": {
                            class_id: class_id,
                            eskul_id: eskul_id,
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
                            "data": "nilai_eskul"
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                if (row.nilai_eskul_id) {
                                    return `
                        <div class="btn-group">
                                                <button type="button" class="btn btn-info dropdown-toggle"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <button class="dropdown-item editNilaiBtn" data-nilai_eskul_id="${row.nilai_eskul_id}" data-student_id='${row.student_id}' data-penilaian_eskul="${row.nilai_eskul}"><i
                                                            class="fas fa-pen text-info"></i>&nbsp;Edit</button>
                                                    <div class="dropdown-divider"></div>
                                                    <button class="dropdown-item text-danger delNilaiBtn" data-nilai_eskul_id='${row.nilai_eskul_id}' data-student_id="${row.student_id}"><i
                                                            class="fas fa-trash text-danger"></i>&nbsp;Delete</button>
                                                </div>
                                            </div>
                    `;
                                } else {
                                    return `<button class="btn btn-success inputNilaiBtn" data-student_id='${row.student_id}'>input Nilai</button>`
                                }

                            }
                        }
                    ]
                });
                $('#pickFst').hide();
                $("#resultTable").show(300);
            })


            ///
            // Tampilkan Modal Input Nilai
            $("#valueTable").on("click", ".inputNilaiBtn", function() {
                $('#eskul_Id').val(eskul_id);
                $('#fst_Id').val(fst_id);
                $('#class_Id').val(class_id);
                let student_id = $(this).data('student_id');
                $('#student_Id').val(student_id);
                $('#penilaian_eskul').val('');
                $('#valueModalLabel').text('Input Nilai Ekstrakulikuler');
                $('#valueModal').modal('show');
            });

            // Simpan atau Update Input
            $('#valueForm').submit(function(e) {
                e.preventDefault();
                let id = $('#nilai_eskul_Id').val();
                let url = id ? `/peskul/${id}` : "{{ route('peskul.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    data: {
                        student_id: $('#student_Id').val(),
                        eskul_id:eskul_id,
                        fst_id:fst_id,
                        class_id:class_id,
                        nilai:$('#penilaian_eskul').val()
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
                let student_id = $(this).data('student_id');
                let penilaian_eskul = $(this).data('penilaian_eskul');
                let nilai_eskul_id = $(this).data('nilai_eskul_id');
                $('#eskul_Id').val(eskul_id);
                $('#fst_Id').val(fst_id);
                $('#class_Id').val(class_id);
                $('#student_Id').val(student_id);
                $('#nilai_eskul_Id').val(nilai_eskul_id);
                $('#penilaian_eskul').val(penilaian_eskul);
                $('#valueModalLabel').text('Edit Nilai Ekstrakulikuler');
                $('#valueModal').modal('show');
            });

            // Delete Action
            $("#valueTable").on("click", ".delNilaiBtn", function() {
                let id = $(this).data('nilai_eskul_id');
                let sid = $(this).data('student_id');
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
                            url: `/peskul/${id}`,
                            method: "DELETE",
                            data: {
                                class_id: class_id,
                                eskul_id: eskul_id,
                                fst_id: fst_id,
                                student_id: sid,
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
