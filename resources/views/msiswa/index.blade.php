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
                            <div class="form-group">
                                <label for="class_filter"> Filter Kelas</label>
                                <select class="form-control" id="class_filter" name="class_filter">
                                    <option value="" selected>Semua Kelas</option>
                                    @foreach ($classList as $index => $class)
                                        <option value="{{ $class->class_name }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <table class="table table-striped table-bordered" id="studentTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nisn</th>
                                        <th>Nis</th>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tempat Lahir</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Agama</th>
                                        <th>Pendidikan Sebelumnya</th>
                                        <th>Alamat Siswa</th>
                                        <th>Nama Ayah</th>
                                        <th>Nama Ibu</th>
                                        <th>Pekerjaan Ayah</th>
                                        <th>Pekerjaan Ibu</th>
                                        <th>Alamat Orang Tua</th>
                                        <th>Foto</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $index => $student)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->nisn }}</td>
                                            <td>{{ $student->nis }}</td>
                                            <td>{{ $student->nama }}</td>
                                            <td>{{ $student->class_name ?? 'Belum Di Set' }}</td>
                                            <td>{{ $student->jenis_kelamin }}</td>
                                            <td>{{ $student->tempat_lahir }}</td>
                                            <td>{{ $student->tanggal_lahir }}</td>
                                            <td>{{ ucwords($student->agama) }}</td>
                                            <td>{{ $student->pendidikan_sebelumnya }}</td>
                                            <td>{{ $student->alamat }}</td>
                                            <td>{{ $student->nama_ayah }}</td>
                                            <td>{{ $student->nama_ibu }}</td>
                                            <td>{{ $student->pekerjaan_ayah }}</td>
                                            <td>{{ $student->pekerjaan_ibu }}</td>
                                            <td>{{ $student->alamat_orang_tua }}</td>
                                            <td>
                                                @if ($student->foto_siswa_path)
                                                    <img src="{{ asset('storage/' . $student->foto_siswa_path) }}"
                                                        alt="" class="img-fluid img-thumbnail"
                                                        style="width: 200px;height:300px;">
                                                @else
                                                    Belum Ada Foto
                                                @endif
                                            </td>
                                            <td> <button class="btn btn-primary editStudentBtn"
                                                    data-id="{{ $student->id }}" data-nis="{{ $student->nis }}"
                                                    data-nisn="{{ $student->nisn }}"
                                                    data-student_name="{{ $student->nama }}"
                                                    data-class_id="{{ $student->class_id }}"
                                                    data-jenis_kelamin="{{ $student->jenis_kelamin }}"
                                                    data-tempat_lahir="{{ $student->tempat_lahir }}"
                                                    data-tanggal_lahir="{{ $student->tanggal_lahir }}"
                                                    data-agama="{{ $student->agama }}"
                                                    data-pendidikan_sebelumnya="{{ $student->pendidikan_sebelumnya }}"
                                                    data-alamat="{{ $student->alamat }}"
                                                    data-nama_ayah="{{ $student->nama_ayah }}"
                                                    data-nama_ibu="{{ $student->nama_ibu }}"
                                                    data-pekerjaan_ayah="{{ $student->pekerjaan_ayah }}"
                                                    data-pekerjaan_ibu="{{ $student->pekerjaan_ibu }}"
                                                    data-alamat_orang_tua="{{ $student->alamat_orang_tua }}">Edit</button>
                                                <button class="btn btn-danger delBtn"
                                                    data-id="{{ $student->id }}">Delete</button>
                                            </td>
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
                                <label for="nis">Nisn</label>
                                <input type="text" class="form-control" id="nisn" name="nisn" required>
                            </div>
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
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin">
                                    <option value="" selected disabled>Pilih Kelas</option>
                                    <option value="L">Laki-Laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tempat_lahir">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="agama">Agama</label>
                                <select class="form-control" id="agama" name="agama">
                                    <option value="" selected disabled>Pilih Agama</option>
                                    <option value="islam">Islam</option>
                                    <option value="kristen">Kristen</option>
                                    <option value="katolik">Katolik</option>
                                    <option value="buddha">Buddha</option>
                                    <option value="hindu">Hindu</option>
                                    <option value="konghucu">Konghucu</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="pendidikan_sebelumnya">Pendidikan Sebelumnya</label>
                                <input type="text" class="form-control" id="pendidikan_sebelumnya"
                                    name="pendidikan_sebelumnya" required>
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat Peserta Didik</label>
                                <input type="text" class="form-control" id="alamat" name="alamat" required>
                            </div>
                            <div class="form-group">
                                <label for="nama_ayah">Nama Ayah</label>
                                <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" required>
                            </div>
                            <div class="form-group">
                                <label for="nama_ibu">Nama Ibu</label>
                                <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" required>
                            </div>
                            <div class="form-group">
                                <label for="pekerjaan_ayah">Pekerjaan Ayah</label>
                                <input type="text" class="form-control" id="pekerjaan_ayah" name="pekerjaan_ayah"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="pekerjaan_ibu">Pekerjaan Ibu</label>
                                <input type="text" class="form-control" id="pekerjaan_ibu" name="pekerjaan_ibu"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="alamat_orang_tua">Alamat Orang Tua</label>
                                <input type="textarea" class="form-control" id="alamat_orang_tua"
                                    name="alamat_orang_tua" required>
                            </div>
                            <div class="form-group">
                                <label for="foto_siswa" class="form-label">Upload Foto</label>
                                <input class="form-control" type="file" id="foto_siswa">
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
                            <a href="{{ route('student.download') }}" class="btn btn-success mt-2 mb-2"
                                target="blank"><i class="fas fa-file-excel"></i>&nbsp;Download Template Untuk CSV</a>
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
            let table = $('#studentTable').DataTable({
                responsive: true
            });
            ///
            // Tampilkan Modal Tambah Kelas
            $('#addStudentBtn').click(function() {
                $('#student_id').val('');
                $('#nisn').val('');
                $('#nis').val('');
                $('#student_name').val('');
                $('#class_id').val('');
                $('#jenis_kelamin').val('');
                $('#tempat_lahir').val('');
                $('#tanggal_lahir').val('');
                $('#agama').val('');
                $('#pendidikan_sebelumnya').val('');
                $('#alamat').val('');
                $('#nama_ayah').val('');
                $('#nama_ibu').val('');
                $('#pekerjaan_ayah').val('');
                $('#pekerjaan_ibu').val('');
                $('#alamat_orang_tua').val('');
                $('#foto_siswa').val(null);
                $('#studentModalLabel').text('Tambah Siswa');
                $('#studentModal').modal('show');
            });

            // Simpan atau Update Kelas
            $('#studentForm').submit(function(e) {
                e.preventDefault();
                let id = $('#student_id').val();
                let url = id ? `/siswa/${id}` : "{{ route('student.store') }}";

                let formData = new FormData(this);
                formData.append('nis', $('#nis').val())
                formData.append('student_name', $('#student_name').val())
                formData.append('class_id', $('#class_id').val())
                formData.append('student_name', $('#student_name').val());
                formData.append('jenis_kelamin', $('#jenis_kelamin').val());
                formData.append('tempat_lahir', $('#tempat_lahir').val());
                formData.append('tanggal_lahir', $('#tanggal_lahir').val());
                formData.append('agama', $('#agama').val());
                formData.append('pendidikan_sebelumnya', $('#pendidikan_sebelumnya').val());
                formData.append('alamat', $('#alamat').val());
                formData.append('nama_ayah', $('#nama_ayah').val());
                formData.append('nama_ibu', $('#nama_ibu').val());
                formData.append('pekerjaan_ayah', $('#pekerjaan_ayah').val());
                formData.append('pekerjaan_ibu', $('#pekerjaan_ibu').val());
                formData.append('alamat_orang_tua', $('#alamat_orang_tua').val());
                if (id) {
                    formData.append('_method', 'PUT');
                }
                // Menambahkan file gambar jika ada
                let fotoSiswa = $('#foto_siswa')[0].files[0];
                if (fotoSiswa) {
                    formData.append('foto_siswa', fotoSiswa);
                }
                $.ajax({
                    url: url,
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    data: formData,
                    processData: false, // Penting untuk FormData
                    contentType: false, // Penting untuk FormData
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
            $(document).on('click', '.editStudentBtn', function() {
                let id = $(this).data('id');
                let nisn = $(this).data('nisn');
                let nis = $(this).data('nis');
                let student_name = $(this).data('student_name');
                let class_id = $(this).data('class_id');
                let jenis_kelamin = $(this).data('jenis_kelamin');
                let tempat_lahir = $(this).data('tempat_lahir');
                let tanggal_lahir = $(this).data('tanggal_lahir');
                let agama = $(this).data('agama');
                let pendidikan_sebelumnya = $(this).data('pendidikan_sebelumnya');
                let alamat = $(this).data('alamat');
                let nama_ayah = $(this).data('nama_ayah');
                let nama_ibu = $(this).data('nama_ibu');
                let pekerjaan_ayah = $(this).data('pekerjaan_ayah');
                let pekerjaan_ibu = $(this).data('pekerjaan_ibu');
                let alamat_orang_tua = $(this).data('alamat_orang_tua');
                $('#student_id').val(id);
                $('#nisn').val(nisn);
                $('#nis').val(nis);
                $('#student_name').val(student_name);
                $('#class_id').val(class_id);
                $('#jenis_kelamin').val(jenis_kelamin);
                $('#tempat_lahir').val(tempat_lahir);
                $('#tanggal_lahir').val(tanggal_lahir);
                $('#agama').val(agama?.toLowerCase());
                $('#pendidikan_sebelumnya').val(pendidikan_sebelumnya);
                $('#alamat').val(alamat);
                $('#nama_ayah').val(nama_ayah);
                $('#nama_ibu').val(nama_ibu);
                $('#pekerjaan_ayah').val(pekerjaan_ayah);
                $('#pekerjaan_ibu').val(pekerjaan_ibu);
                $('#alamat_orang_tua').val(alamat_orang_tua);
                $('#foto_siswa').val(null);
                $('#studentModalLabel').text('Edit Siswa');
                $('#studentModal').modal('show');
            })


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
            $('#class_filter').on('change', function() {
                var val = this.value;
                table.column(3).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true,
                    false).draw(); // Ganti angka 2 dengan index kolom kelas
            });
        });
    </script>
@endsection
