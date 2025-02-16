@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Master Siswa') }}</h1>
                    <button class="mt-2 btn btn-success" id="addStudentBtn">Tambah Siswa</button>
                    <button class="mt-2 btn btn-info" id="upCsvBtn">Import CSV SISWA</button>
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

                            <table class="table table-striped table-bordered" id="studentTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nis</th>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $index => $student)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->nis }}</td>
                                            <td>{{ $student->nama }}</td>
                                            <td>{{ $student->class_name ?? 'Belum Di Set' }}</td>
                                            <td> <button class="btn btn-primary editStudentBtn"
                                                    data-id="{{ $student->id }}" data-nis="{{ $student->nis }}"
                                                    data-student_name="{{ $student->nama }}"
                                                    data-class_id="{{ $student->class_id }}">Edit</button> <button
                                                    class="btn btn-danger delBtn"
                                                    data-id="{{ $student->id }}">Delete</button></td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
        <!-- Modal Tambah & Edit Siswa -->
        <div class="modal fade" id="studentModal" tabindex="-1" role="dialog" aria-labelledby="studentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="studentModalLabel">Tambah Siswa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="studentForm">
                        <div class="modal-body">
                            <input type="hidden" id="student_id">
                            <div class="form-group">
                                <label for="nis">Nis</label>
                                <input type="text" class="form-control" id="nis" name="nis" required>
                            </div>
                            <div class="form-group">
                                <label for="student_name">Nama Siswa</label>
                                <input type="text" class="form-control" id="student_name" name="student_name" required>
                            </div>
                            <div class="form-group">
                                <label for="class_id">Kelas</label>
                                <select class="form-control" id="class_id" name="class_id">
                                    <option value="" selected disabled>Pilih Kelas</option>
                                    @foreach ($classList as $index => $class)
                                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
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
                        <h5 class="modal-title" id="upCsvModalLabel">Upload CSV Siswa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="csvForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <h5>Klik Dibawah Ini Untuk Download Template Nya</h5>
                            <a href="{{route('student.download')}}" class="btn btn-success mt-2 mb-2" target="blank"><i class="fas fa-file-excel"></i>&nbsp;Download Template Untuk CSV</a>
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
            // Init
            $('#studentTable').DataTable();
            ///
            // Tampilkan Modal Tambah Kelas
            $('#addStudentBtn').click(function() {
                $('#student_id').val('');
                $('#nis').val('');
                $('#student_name').val('');
                $('#class_id').val('');
                $('#studentModalLabel').text('Tambah Siswa');
                $('#studentModal').modal('show');
            });

            // Simpan atau Update Kelas
            $('#studentForm').submit(function(e) {
                e.preventDefault();
                let id = $('#student_id').val();
                let url = id ? `/siswa/${id}` : "{{ route('student.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        nis: $('#nis').val(),
                        student_name: $('#student_name').val(),
                        class_id: $('#class_id').val(),
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
                            $('#studentModal').modal('hide');
                            location.reload(); // Refresh halaman setelah berhasil
                        }, 2000);

                    },
                    error: function(res) {
                        console.log(res)
                        Swal.fire('Error', 'Terjadi kesalahan, coba lagi!', 'error');
                    }
                });
            });

            // Tampilkan Modal Edit Kelas
            $('.editStudentBtn').click(function() {
                let id = $(this).data('id');
                let nis = $(this).data('nis');
                let student_name = $(this).data('student_name');
                let class_id = $(this).data('class_id');

                $('#student_id').val(id);
                $('#nis').val(nis);
                $('#student_name').val(student_name);
                $('#class_id').val(class_id);
                $('#studentModalLabel').text('Edit Siswa');
                $('#studentModal').modal('show');
            });

            // Delete Action
            $('.delBtn').click(function() {
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
                            url: `/siswa/${id}`,
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
                                    location
                                        .reload(); // Refresh halaman setelah berhasil
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
                    url: "{{ route('student.import') }}",
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
                            location.reload(); // Refresh halaman setelah berhasil
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
