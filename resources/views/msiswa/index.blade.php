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
                    @if (Auth::user()->role_id == 1)
                        <button class="mt-2 btn btn-primary" id="btnGenAccounts"><i class="fas fa-users-cog mr-1"></i> Generate Akun Portal Siswa</button>
                    @endif
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
                            @if ($className)
                            <input type="hidden" name="class_filter" value="{{$className}}" id="class_filter">
                            @else
                            <div class="form-group">
                                <label for="class_filter"> Filter Kelas</label>
                                <select class="form-control" id="class_filter" name="class_filter">
                                    <option value="" selected>Semua Kelas</option>
                                    @include('partials.select_class_options', ['useNameAsValue' => true])
                                </select>
                            </div>
                            @endif
                            <table class="table table-striped table-bordered w-100" id="studentTable">
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
                                        <th>Sakit</th>
                                        <th>Izin</th>
                                        <th>Alpa</th>
                                        <th>Foto</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
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
                                    @include('partials.select_class_options')
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin">
                                    <option value="" selected disabled>Pilih Jenis Kelamin</option>
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
                                <label for="sakit">Sakit</label>
                                <input type="number" inputmode="numeric" class="form-control" id="sakit"
                                    name="sakit">
                            </div>
                            <div class="form-group">
                                <label for="izin">Izin</label>
                                <input type="number" inputmode="numeric" class="form-control" id="izin"
                                    name="izin" required>
                            </div>
                            <div class="form-group">
                                <label for="alpa">Alpa</label>
                                <input type="number" inputmode="numeric" class="form-control" id="alpa"
                                    name="alpa" required>
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
            // Init DataTable with Server-Side AJAX
            let table = $('#studentTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('student.getData') }}",
                    type: "GET",
                    data: function(d) {
                        d.class_name = $('#class_filter').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'nisn', defaultContent: '-' },
                    { data: 'nis', defaultContent: '-' },
                    { data: 'nama', defaultContent: '-' },
                    { data: 'class_name', defaultContent: '-' },
                    { data: 'jenis_kelamin', defaultContent: '-' },
                    { data: 'tempat_lahir', defaultContent: '-' },
                    { data: 'tanggal_lahir', defaultContent: '-' },
                    {
                        data: 'agama',
                        render: function(data) {
                            return data ? data.charAt(0).toUpperCase() + data.slice(1) : '-';
                        }
                    },
                    { data: 'pendidikan_sebelumnya', defaultContent: '-' },
                    { data: 'alamat', defaultContent: '-' },
                    { data: 'nama_ayah', defaultContent: '-' },
                    { data: 'nama_ibu', defaultContent: '-' },
                    { data: 'pekerjaan_ayah', defaultContent: '-' },
                    { data: 'pekerjaan_ibu', defaultContent: '-' },
                    { data: 'alamat_orang_tua', defaultContent: '-' },
                    { data: 'sakit', defaultContent: '0' },
                    { data: 'izin', defaultContent: '0' },
                    { data: 'alpa', defaultContent: '0' },
                    {
                        data: 'foto_siswa_path',
                        render: function(data) {
                            if (data) {
                                return `<img src="/storage/${data}" alt="" class="img-fluid img-thumbnail" style="width: 70px; max-height: 90px; object-fit: cover;">`;
                            }
                            return 'Belum Ada Foto';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            let safeNama = (row.nama || '').replace(/"/g, '&quot;');
                            let safeTl = (row.tempat_lahir || '').replace(/"/g, '&quot;');
                            let safePendidikan = (row.pendidikan_sebelumnya || '').replace(/"/g, '&quot;');
                            let safeAlamat = (row.alamat || '').replace(/"/g, '&quot;');
                            let safeAyah = (row.nama_ayah || '').replace(/"/g, '&quot;');
                            let safeIbu = (row.nama_ibu || '').replace(/"/g, '&quot;');
                            let safePekerjaanAyah = (row.pekerjaan_ayah || '').replace(/"/g, '&quot;');
                            let safePekerjaanIbu = (row.pekerjaan_ibu || '').replace(/"/g, '&quot;');
                            let safeAlamatOrtu = (row.alamat_orang_tua || '').replace(/"/g, '&quot;');

                            return `
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary editStudentBtn"
                                        data-id="${row.id}"
                                        data-nis="${row.nis || ''}"
                                        data-nisn="${row.nisn || ''}"
                                        data-student_name="${safeNama}"
                                        data-class_id="${row.class_id || ''}"
                                        data-jenis_kelamin="${row.jenis_kelamin || ''}"
                                        data-tempat_lahir="${safeTl}"
                                        data-tanggal_lahir="${row.tanggal_lahir || ''}"
                                        data-agama="${row.agama || ''}"
                                        data-pendidikan_sebelumnya="${safePendidikan}"
                                        data-alamat="${safeAlamat}"
                                        data-nama_ayah="${safeAyah}"
                                        data-nama_ibu="${safeIbu}"
                                        data-pekerjaan_ayah="${safePekerjaanAyah}"
                                        data-pekerjaan_ibu="${safePekerjaanIbu}"
                                        data-alamat_orang_tua="${safeAlamatOrtu}"
                                        data-sakit="${row.sakit || 0}"
                                        data-izin="${row.izin || 0}"
                                        data-alpa="${row.alpa || 0}">Edit</button>
                                    <button class="btn btn-sm btn-danger delBtn" data-id="${row.id}">Delete</button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            // Tampilkan Modal Tambah Siswa
            $('#addStudentBtn').click(function() {
                $('#student_id').val('');
                $('#nisn').val('');
                $('#nis').val('');
                $('#student_name').val('');
                $('#class_id').val('').trigger('change');
                $('#jenis_kelamin').val('').trigger('change');
                $('#tempat_lahir').val('');
                $('#tanggal_lahir').val('');
                $('#agama').val('').trigger('change');
                $('#pendidikan_sebelumnya').val('');
                $('#alamat').val('');
                $('#nama_ayah').val('');
                $('#nama_ibu').val('');
                $('#pekerjaan_ayah').val('');
                $('#pekerjaan_ibu').val('');
                $('#alamat_orang_tua').val('');
                $('#foto_siswa').val(null);
                $('#sakit').val('');
                $('#izin').val('');
                $('#alpa').val('');
                $('#studentModalLabel').text('Tambah Siswa');
                $('#studentModal').modal('show');
            });

            // Simpan atau Update Siswa
            $('#studentForm').submit(function(e) {
                e.preventDefault();
                let id = $('#student_id').val();
                let url = id ? `/siswa/${id}` : "{{ route('student.store') }}";

                let formData = new FormData(this);
                formData.append('nis', $('#nis').val());
                formData.append('student_name', $('#student_name').val());
                formData.append('class_id', $('#class_id').val() || '');
                formData.append('jenis_kelamin', $('#jenis_kelamin').val() || '');
                formData.append('tempat_lahir', $('#tempat_lahir').val());
                formData.append('tanggal_lahir', $('#tanggal_lahir').val());
                formData.append('agama', $('#agama').val() || '');
                formData.append('pendidikan_sebelumnya', $('#pendidikan_sebelumnya').val());
                formData.append('alamat', $('#alamat').val());
                formData.append('nama_ayah', $('#nama_ayah').val());
                formData.append('nama_ibu', $('#nama_ibu').val());
                formData.append('pekerjaan_ayah', $('#pekerjaan_ayah').val());
                formData.append('pekerjaan_ibu', $('#pekerjaan_ibu').val());
                formData.append('alamat_orang_tua', $('#alamat_orang_tua').val());
                formData.append('sakit', $('#sakit').val() || 0);
                formData.append('izin', $('#izin').val() || 0);
                formData.append('alpa', $('#alpa').val() || 0);
                if (id) {
                    formData.append('_method', 'PUT');
                }
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
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#studentModal').modal('hide');
                        table.ajax.reload(null, false);
                    },
                    error: function(res) {
                        console.log(res);
                        Swal.fire('Error', res.responseJSON?.message || 'Terjadi kesalahan, coba lagi!', 'error');
                    }
                });
            });

            // Tampilkan Modal Edit Siswa
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
                let sakit = $(this).data('sakit');
                let izin = $(this).data('izin');
                let alpa = $(this).data('alpa');

                $('#student_id').val(id);
                $('#nisn').val(nisn);
                $('#nis').val(nis);
                $('#student_name').val(student_name);
                $('#class_id').val(class_id).trigger('change');
                $('#jenis_kelamin').val(jenis_kelamin).trigger('change');
                $('#tempat_lahir').val(tempat_lahir);
                $('#tanggal_lahir').val(tanggal_lahir);
                $('#agama').val(agama ? agama.toLowerCase() : '').trigger('change');
                $('#pendidikan_sebelumnya').val(pendidikan_sebelumnya);
                $('#alamat').val(alamat);
                $('#nama_ayah').val(nama_ayah);
                $('#nama_ibu').val(nama_ibu);
                $('#pekerjaan_ayah').val(pekerjaan_ayah);
                $('#pekerjaan_ibu').val(pekerjaan_ibu);
                $('#alamat_orang_tua').val(alamat_orang_tua);
                $('#foto_siswa').val(null);
                $('#sakit').val(sakit);
                $('#izin').val(izin);
                $('#alpa').val(alpa);
                $('#studentModalLabel').text('Edit Siswa');
                $('#studentModal').modal('show');
            });

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
                                table.ajax.reload(null, false);
                            },
                            error: function(r) {
                                console.log(r);
                                Swal.fire("Gagal!", "Terjadi kesalahan, coba lagi!", "error");
                            }
                        });
                    }
                });
            });

            // Upload CSV
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
                        $('#upCsvModal').modal('hide');
                        table.ajax.reload();
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
                table.ajax.reload();
            });

            $('#btnGenAccounts').on('click', function() {
                Swal.fire({
                    title: 'Generate Akun Portal Siswa?',
                    text: 'Sistem akan membuat akun login untuk seluruh siswa yang belum memiliki akun (Username: NISN/NIS, Password default: siswa123).',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Buat Akun!',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        Swal.fire({
                            title: 'Membuat Akun Siswa...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.post('{{ route("portal.generate_accounts") }}', { _token: '{{ csrf_token() }}' }, function(resp) {
                            Swal.fire('Berhasil!', resp.message, 'success');
                            table.ajax.reload(null, false);
                        }).fail(function(xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                        });
                    }
                });
            });
        });
    </script>
@endsection
