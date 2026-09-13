@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-users-cog text-primary mr-2"></i>{{ __('Manajemen Pengguna Sistem') }}
                    </h1>
                    <p class="text-muted small mb-0">Kelola akun Administrator, Guru / Wali Kelas, serta Akun Login Portal Siswa.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item">Administrasi & Sistem</li>
                        <li class="breadcrumb-item active">Manajemen Pengguna</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline card-outline-tabs shadow-sm mb-5">
                <div class="card-header p-0 border-bottom-0 bg-light">
                    <ul class="nav nav-tabs" id="userTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold py-3 px-4" id="tab-guru-link" data-toggle="pill" href="#tab-guru" role="tab" aria-controls="tab-guru" aria-selected="true">
                                <i class="fas fa-chalkboard-teacher text-primary mr-2"></i> Guru & Tenaga Pendidik
                                <span class="badge badge-primary ml-2">{{ $countAdmin + $countGuru }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold py-3 px-4" id="tab-siswa-link" data-toggle="pill" href="#tab-siswa" role="tab" aria-controls="tab-siswa" aria-selected="false">
                                <i class="fas fa-user-graduate text-success mr-2"></i> Akun Portal Siswa & Orang Tua
                                <span class="badge badge-success ml-2" id="badgeTotalSiswaUsers">{{ $countSiswaUser }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="userTabsContent">

                        <!-- ── TAB 1: GURU & ADMINISTRATOR ── -->
                        <div class="tab-pane fade show active" id="tab-guru" role="tabpanel" aria-labelledby="tab-guru-link">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 8px;">
                                <!-- Left Badges -->
                                <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                    <span class="badge badge-primary px-2 py-2 shadow-sm font-weight-bold">
                                        <i class="fas fa-user-shield mr-1"></i> {{ $countAdmin }} Admin
                                    </span>
                                    <span class="badge badge-info px-2 py-2 shadow-sm font-weight-bold">
                                        <i class="fas fa-chalkboard-teacher mr-1"></i> {{ $countGuru }} Guru
                                    </span>
                                    <span class="badge badge-success px-2 py-2 shadow-sm font-weight-bold">
                                        <i class="fas fa-user-check mr-1"></i> {{ $countWalas }} Wali Kelas
                                    </span>
                                </div>

                                <!-- Right Button -->
                                <button class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="inputUserBtn">
                                    <i class="fas fa-plus mr-1"></i> Tambah Guru / Admin
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered w-100" id="usersTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Username</th>
                                            <th>Nama Lengkap</th>
                                            <th>NIP</th>
                                            <th>Wali Kelas</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th style="width: 140px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ── TAB 2: AKUN PORTAL SISWA ── -->
                        <div class="tab-pane fade" id="tab-siswa" role="tabpanel" aria-labelledby="tab-siswa-link">
                            <div class="alert alert-info border-0 shadow-sm py-2 px-3 mb-3 small d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Panduan Portal:</strong> Username login siswa menggunakan <strong>NISN / NIS</strong> dengan kata sandi default <code>siswa123</code>.
                                </div>
                                <div class="text-muted">
                                    Siswa dapat melihat capaian nilai dan rapor secara mandiri melalui portal.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 8px;">
                                <!-- Left Badges & Filter Kelas & Status -->
                                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                    <span class="badge badge-success px-2 py-2 shadow-sm font-weight-bold">
                                        <i class="fas fa-user-check mr-1"></i> <span id="siswaActiveBadge">{{ $countSiswaUser }}</span> Akun Aktif
                                    </span>
                                    <span class="badge badge-secondary px-2 py-2 shadow-sm font-weight-bold">
                                        <i class="fas fa-user-clock mr-1"></i> <span id="siswaInactiveBadge">{{ $totalStudents - $countSiswaUser }}</span> Belum Aktif
                                    </span>
                                    <span class="badge badge-light border text-dark px-2 py-2 shadow-sm">
                                        <i class="fas fa-users text-primary mr-1"></i> Total <span id="siswaTotalCount">{{ $totalStudents }}</span> Siswa
                                    </span>

                                    <div class="input-group input-group-sm ml-sm-2" style="width: 200px;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-school text-muted"></i></span>
                                        </div>
                                        <select id="filterClassSiswa" class="form-control border-left-0 font-weight-bold">
                                            <option value="">-- Semua Kelas --</option>
                                            @include('partials.select_class_options', ['classList' => $classList])
                                        </select>
                                    </div>

                                    <div class="input-group input-group-sm ml-sm-1" style="width: 170px;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-toggle-on text-muted"></i></span>
                                        </div>
                                        <select id="filterStatusSiswa" class="form-control border-left-0 font-weight-bold">
                                            <option value="">Semua Status</option>
                                            <option value="1">Hanya Aktif</option>
                                            <option value="0">Belum Aktif</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Right Batch Buttons -->
                                <button class="btn btn-success btn-sm font-weight-bold shadow-sm" id="btnGenerateSiswa">
                                    <i class="fas fa-magic mr-1"></i> Generate Massal
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered w-100" id="siswaUsersTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">No</th>
                                            <th>NISN / Username</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas</th>
                                            <th>Email Terdaftar</th>
                                            <th style="width: 100px;" class="text-center">Status</th>
                                            <th style="width: 150px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah & Edit Guru/Admin -->
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="userModalLabel">Input Pengguna</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="userForm">
                    <div class="modal-body py-3">
                        <input type="hidden" id="user_id">
                        
                        <div class="form-group mb-2">
                            <label for="username" class="font-weight-bold small text-muted text-uppercase mb-1">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-weight-bold" id="username" name="username" placeholder="Contoh: gurumatematika" required>
                        </div>

                        <div class="form-group mb-2">
                            <label for="nama" class="font-weight-bold small text-muted text-uppercase mb-1">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-weight-bold" id="nama" name="nama" placeholder="Contoh: Budi Santoso, M.Pd" required>
                        </div>

                        <div class="form-group mb-2">
                            <label for="nip" class="font-weight-bold small text-muted text-uppercase mb-1">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" class="form-control" id="nip" name="nip" placeholder="Masukkan 18 digit NIP atau strip (-)">
                        </div>

                        <div class="form-group mb-2">
                            <label for="role_id" class="font-weight-bold small text-muted text-uppercase mb-1">Hak Akses / Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-control font-weight-bold" required>
                                <option value="" disabled selected>-- Pilih Role --</option>
                                @foreach ($rolesList as $role)
                                    <option value="{{ $role->id }}">{{ ucfirst($role->role_name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label for="class_id" class="font-weight-bold small text-muted text-uppercase mb-1">Wali Kelas Untuk</label>
                            <select name="class_id" id="class_id" class="form-control">
                                <option value="">-- Bukan Wali Kelas (Guru Mata Pelajaran) --</option>
                                @include('partials.select_class_options', ['classList' => $classList])
                            </select>
                            <small class="form-text text-muted">Pilih kelas jika guru ini ditugaskan sebagai Wali Kelas.</small>
                        </div>

                        <div class="form-group mb-2">
                            <label for="email" class="font-weight-bold small text-muted text-uppercase mb-1">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="email@sekolah.sch.id" required>
                        </div>

                        <div class="form-group mb-1">
                            <label for="password" class="font-weight-bold small text-muted text-uppercase mb-1">
                                Password <span id="passReqText" class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 4 karakter">
                            <small class="form-text text-muted" id="passHintText">Biarkan kosong jika tidak ingin mengubah password.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reset Password Cepat -->
    <div class="modal fade" id="resetPassModal" tabindex="-1" role="dialog" aria-labelledby="resetPassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-warning text-dark py-2">
                    <h6 class="modal-title font-weight-bold" id="resetPassModalLabel">
                        <i class="fas fa-key mr-1"></i> Reset Password
                    </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="resetPassForm">
                    <div class="modal-body py-3">
                        <input type="hidden" id="reset_user_id">
                        <p class="small text-muted mb-2">
                            Reset password untuk: <strong id="reset_user_name" class="text-dark"></strong>
                        </p>
                        <div class="form-group mb-0">
                            <label for="new_password" class="font-weight-bold small text-muted text-uppercase mb-1">Password Baru</label>
                            <input type="text" class="form-control font-weight-bold" id="new_password" required>
                            <small class="form-text text-muted" id="reset_pass_hint">Default: <code>siswa123</code></small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold px-3">Reset Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // ── Datatable 1: Guru & Tenaga Pendidik ─────────────────
            let guruTable = $('#usersTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('muser.data_guru') }}",
                    type: "GET",
                    error: function() {
                        SwalHelper.showError('Gagal memuat data pengguna guru.');
                    }
                },
                columns: [
                    { 
                        data: 'username', 
                        className: 'font-weight-bold text-dark'
                    },
                    { data: 'name' },
                    { data: 'nip' },
                    {
                        data: 'class_name',
                        render: function(data) {
                            return data 
                                ? `<span class="badge badge-info px-2 py-1"><i class="fas fa-chalkboard-teacher mr-1"></i>Walas ${data}</span>` 
                                : `<span class="badge badge-light border text-muted px-2 py-1">Bukan Walas</span>`;
                        }
                    },
                    { data: 'email' },
                    {
                        data: 'role_id',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (data == 1) {
                                return `<span class="badge badge-primary font-weight-bold px-2 py-1"><i class="fas fa-shield-alt mr-1"></i>Admin</span>`;
                            }
                            return `<span class="badge badge-success font-weight-bold px-2 py-1">Guru</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            let safeName = (row.name || '').replace(/"/g, '&quot;');
                            let safeUname = (row.username || '').replace(/"/g, '&quot;');
                            return `
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info editUserBtn"
                                        data-id="${row.id}"
                                        data-class_id="${row.class_id || ''}"
                                        data-role_id="${row.role_id || ''}"
                                        data-nama="${safeName}"
                                        data-nip="${row.nip || ''}"
                                        data-username="${safeUname}"
                                        data-email="${row.email || ''}"
                                        title="Edit Profil">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-outline-warning resetPassBtn"
                                        data-id="${row.id}"
                                        data-nama="${safeName}"
                                        data-role="${row.role_id}"
                                        title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button class="btn btn-outline-danger delUserBtn" data-id="${row.id}" title="Hapus User">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            // ── Datatable 2: Akun Portal Siswa ─────────────────────
            let siswaTable = null;

            $('#tab-siswa-link').on('shown.bs.tab', function() {
                if (!siswaTable) {
                    siswaTable = $('#siswaUsersTable').DataTable({
                        responsive: true,
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('muser.data_siswa') }}",
                            type: "GET",
                            data: function(d) {
                                d.class_id = $('#filterClassSiswa').val();
                                d.status = $('#filterStatusSiswa').val();
                            },
                            error: function() {
                                SwalHelper.showError('Gagal memuat data akun siswa.');
                            }
                        },
                        drawCallback: function(settings) {
                            if (settings.json) {
                                if (settings.json.active_count !== undefined) {
                                    $('#siswaActiveBadge').text(settings.json.active_count);
                                    if (!$('#filterClassSiswa').val()) {
                                        $('#badgeTotalSiswaUsers').text(settings.json.active_count);
                                    }
                                }
                                if (settings.json.inactive_count !== undefined) {
                                    $('#siswaInactiveBadge').text(settings.json.inactive_count);
                                }
                                if (settings.json.total_students !== undefined) {
                                    $('#siswaTotalCount').text(settings.json.total_students);
                                }
                            }
                        },
                        columns: [
                            {
                                data: null,
                                orderable: false,
                                className: 'text-center font-weight-bold text-muted',
                                render: function(data, type, row, meta) {
                                    return meta.row + meta.settings._iDisplayStart + 1;
                                }
                            },
                            { 
                                data: 'username',
                                className: 'font-weight-bold text-dark',
                                render: function(data, type, row) {
                                    if (row.is_active == 1) {
                                        return `<span class="badge badge-light border font-weight-bold px-2 py-1"><i class="fas fa-id-card text-muted mr-1"></i>${data}</span>`;
                                    }
                                    let preview = (data && data !== '-') ? data : (row.nisn || row.nis || '-');
                                    return `<span class="text-muted font-italic">${preview}</span>`;
                                }
                            },
                            { 
                                data: 'student_name', 
                                className: 'font-weight-bold',
                                render: function(data, type, row) {
                                    return data || row.name || '-';
                                }
                            },
                            {
                                data: 'class_name',
                                render: function(data) {
                                    return data ? `<span class="badge badge-info px-2 py-1">${data}</span>` : '-';
                                }
                            },
                            { 
                                data: 'email',
                                render: function(data, type, row) {
                                    if (row.is_active == 1 && data && data !== '-') {
                                        return data;
                                    }
                                    return `<span class="text-muted font-italic">Belum dibuat</span>`;
                                }
                            },
                            {
                                data: 'is_active',
                                className: 'text-center',
                                render: function(data) {
                                    if (data == 1) {
                                        return `<span class="badge badge-success font-weight-bold px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Aktif</span>`;
                                    }
                                    return `<span class="badge badge-secondary font-weight-bold px-2 py-1"><i class="fas fa-clock mr-1"></i>Belum Aktif</span>`;
                                }
                            },
                            {
                                data: null,
                                orderable: false,
                                className: 'text-center',
                                render: function(data, type, row) {
                                    let safeName = (row.student_name || row.name || '').replace(/"/g, '&quot;');
                                    if (row.is_active == 1) {
                                        return `
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-warning resetPassBtn"
                                                    data-id="${row.user_id}"
                                                    data-nama="${safeName}"
                                                    data-role="3"
                                                    title="Reset Password Siswa">
                                                    <i class="fas fa-key mr-1"></i>Reset
                                                </button>
                                                <button class="btn btn-outline-danger delUserBtn" data-id="${row.user_id}" title="Hapus / Nonaktifkan Akun Siswa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        `;
                                    } else {
                                        return `
                                            <button class="btn btn-sm btn-success activateSingleBtn font-weight-bold shadow-sm"
                                                data-student-id="${row.student_id}"
                                                data-nama="${safeName}"
                                                title="Buat dan aktifkan akun login untuk siswa ini">
                                                <i class="fas fa-user-plus mr-1"></i> Aktifkan
                                            </button>
                                        `;
                                    }
                                }
                            }
                        ]
                    });
                } else {
                    siswaTable.columns.adjust().responsive.recalc();
                }
            });

            // Filter Kelas & Status Siswa
            $('#filterClassSiswa, #filterStatusSiswa').on('change', function() {
                if (siswaTable) {
                    siswaTable.ajax.reload();
                }
            });

            // ── Aktifkan Akun Siswa Tunggal (1-Klik) ───────────────
            $(document).on('click', '.activateSingleBtn', function() {
                let studentId = $(this).data('student-id');
                let studentName = $(this).data('nama');
                let btn = $(this);
                let originHtml = btn.html();

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengaktifkan...');

                apiService("{{ route('muser.activate_single_siswa') }}", 'POST', {
                    student_id: studentId,
                    _token: "{{ csrf_token() }}"
                })
                .then(response => {
                    SwalHelper.showSuccess(response.message);
                    if (siswaTable) siswaTable.ajax.reload(null, false);
                })
                .catch(err => {
                    btn.prop('disabled', false).html(originHtml);
                    SwalHelper.showError(err.responseJSON?.message || 'Gagal mengaktifkan akun siswa.');
                });
            });

            // ── Modal Tambah User ──────────────────────────────────
            $('#inputUserBtn').click(function() {
                $('#user_id').val('');
                $('#class_id').val('').trigger('change');
                $('#role_id').val('').trigger('change');
                $('#username').val('');
                $('#nama').val('');
                $('#nip').val('');
                $('#email').val('');
                $('#password').val('');
                $('#password').prop('required', true);
                $('#passReqText').show();
                $('#passHintText').hide();
                $('#userModalLabel').text('Tambah Pengguna Guru / Admin');
                $('#userModal').modal('show');
            });

            // ── Simpan / Update User ───────────────────────────────
            $('#userForm').submit(function(e) {
                e.preventDefault();
                let id = $('#user_id').val();
                let url = id ? `/muser/${id}` : "{{ route('muser.store') }}";
                let method = id ? "PUT" : "POST";

                let payload = {
                    class_id: $('#class_id').val(),
                    role_id: $('#role_id').val(),
                    username: $('#username').val().trim(),
                    nama: $('#nama').val().trim(),
                    nip: $('#nip').val().trim(),
                    email: $('#email').val().trim(),
                    password: $('#password').val(),
                    _token: "{{ csrf_token() }}"
                };

                apiService(url, method, payload)
                    .then(response => {
                        SwalHelper.showSuccess(response.message);
                        $('#userModal').modal('hide');
                        guruTable.ajax.reload(null, false);
                    })
                    .catch(err => {
                        SwalHelper.showError(err.responseJSON?.message || 'Terjadi kesalahan.');
                    });
            });

            // ── Modal Edit User ────────────────────────────────────
            $(document).on('click', '.editUserBtn', function() {
                let id = $(this).data('id');
                let role_id = $(this).data('role_id');
                let class_id = $(this).data('class_id');
                let username = $(this).data('username');
                let nama = $(this).data('nama');
                let nip = $(this).data('nip');
                let email = $(this).data('email');

                $('#user_id').val(id);
                $('#class_id').val(class_id).trigger('change');
                $('#role_id').val(role_id).trigger('change');
                $('#username').val(username);
                $('#nama').val(nama);
                $('#nip').val(nip === '-' ? '' : nip);
                $('#email').val(email);
                $('#password').val('');
                $('#password').prop('required', false);
                $('#passReqText').hide();
                $('#passHintText').show();
                $('#userModalLabel').text('Edit Pengguna');
                $('#userModal').modal('show');
            });

            // ── Reset Password Action ──────────────────────────────
            $(document).on('click', '.resetPassBtn', function() {
                let id = $(this).data('id');
                let nama = $(this).data('nama');
                let role = $(this).data('role');

                $('#reset_user_id').val(id);
                $('#reset_user_name').text(nama);

                let defaultPass = (role == 3) ? 'siswa123' : 'guru123';
                $('#new_password').val(defaultPass);
                $('#reset_pass_hint').html(`Default: <code>${defaultPass}</code>`);
                $('#resetPassModal').modal('show');
            });

            $('#resetPassForm').submit(function(e) {
                e.preventDefault();
                let id = $('#reset_user_id').val();
                let newPass = $('#new_password').val().trim();

                apiService(`/muser/reset-password/${id}`, 'POST', {
                    password: newPass,
                    _token: "{{ csrf_token() }}"
                })
                .then(response => {
                    SwalHelper.showSuccess(response.message);
                    $('#resetPassModal').modal('hide');
                })
                .catch(err => {
                    SwalHelper.showError(err.responseJSON?.message || 'Gagal mereset password.');
                });
            });

            // ── Hapus Pengguna ─────────────────────────────────────
            $(document).on('click', '.delUserBtn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: "Hapus Pengguna Ini?",
                    text: "Akun login pengguna akan dihapus dari sistem!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        apiService(`/muser/${id}`, 'DELETE', {
                            _token: "{{ csrf_token() }}"
                        })
                        .then(response => {
                            SwalHelper.showSuccess(response.message);
                            guruTable.ajax.reload(null, false);
                            if (siswaTable) siswaTable.ajax.reload(null, false);
                        })
                        .catch(err => {
                            SwalHelper.showError(err.responseJSON?.message || 'Gagal menghapus pengguna.');
                        });
                    }
                });
            });

            // ── Generate Massal Akun Siswa ─────────────────────────
            $('#btnGenerateSiswa').on('click', function() {
                let classId = $('#filterClassSiswa').val();
                let classText = classId ? $('#filterClassSiswa option:selected').text().trim() : 'Seluruh Kelas';

                Swal.fire({
                    title: "Generate Akun Siswa Massal?",
                    html: `Sistem akan membuat akun login otomatis untuk siswa di <strong>${classText}</strong> yang belum memiliki akun.<br><br>Username: <code>NISN / NIS</code><br>Password Default: <code>siswa123</code>`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Generate Sekarang!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        let btn = $('#btnGenerateSiswa');
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sedang Membuat Akun...');

                        apiService("{{ route('muser.generate_siswa') }}", 'POST', {
                            class_id: classId,
                            _token: "{{ csrf_token() }}"
                        })
                        .then(response => {
                            btn.prop('disabled', false).html('<i class="fas fa-magic mr-1"></i> Generate Akun Siswa Massal');
                            Swal.fire({
                                icon: 'success',
                                title: 'Selesai!',
                                text: response.message,
                                confirmButtonColor: '#28a745'
                            });
                            if (siswaTable) siswaTable.ajax.reload(null, false);
                        })
                        .catch(err => {
                            btn.prop('disabled', false).html('<i class="fas fa-magic mr-1"></i> Generate Akun Siswa Massal');
                            SwalHelper.showError(err.responseJSON?.message || 'Gagal membuat akun siswa.');
                        });
                    }
                });
            });
        });
    </script>
@endsection
