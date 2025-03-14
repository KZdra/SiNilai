@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Manajemen User') }}</h1>
                    <button class="btn btn-success mt-2" id="inputUserBtn">Tambah User</button>
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

                    {{-- <div class="alert alert-info">
                        Sample table page
                    </div> --}}

                    <div class="card">
                        <div class="card-body p-2">

                            <table class="table" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Nip</th>
                                        <th>Mengajar Di</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{ $user->username }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->nip }}</td>
                                            <td>{{ $user->class_name ?? 'Belum Diatur' }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ ucwords($user->role_name) }}</td>
                                            <td><button class="btn btn-primary editUserBtn" data-id="{{ $user->id }}"
                                                    data-class_id="{{ $user->class_id }}"
                                                    data-role_id="{{ $user->role_id }}" data-nama="{{ $user->name }}"
                                                    data-nip="{{ $user->nip }}" data-username="{{ $user->username }}"
                                                    data-email="{{ $user->email }}">
                                                    Edit</button>
                                                <button class="btn btn-danger delUserBtn" data-id="{{$user->id}}">Delete</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="userModalLabel">Tambah User</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form id="userForm">
                                    <div class="modal-body">
                                        <input type="hidden" id="user_id">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="nama">Nama</label>
                                            <input type="text" class="form-control" id="nama" name="nama"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="nip">Nip</label>
                                            <input type="text" inputmode="numeric" class="form-control" id="nip"
                                                name="nip" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="class_id">Mengajar Di</label>
                                            <select name="class_id" id="class_id" class="form-control">
                                                <option value="" disabled selected> Pilih Kelas Mengajar</option>
                                                @foreach ($classList as $class)
                                                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="role_id">Role</label>
                                            <select name="role_id" id="role_id" class="form-control">
                                                <option value="" disabled selected> Pilih Role</option>
                                                @foreach ($rolesList as $role)
                                                    <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" id="password" name="password">
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
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection
@section('scripts')
    <script type="module">
        $(document).ready(function() {
            let table = $('#usersTable').DataTable({
                responsive: true
            });
            // Tampilkan Modal Tambah Kelas
            $('#inputUserBtn').click(function() {
                $('#user_id').val('');
                $('#class_id').val('');
                $('#role_id').val('');
                $('#username').val('');
                $('#nama').val('');
                $('#nip').val('');
                $('#email').val('');
                $('#password').val('');
                $('#userModalLabel').text('Tambah User');
                $('#userModal').modal('show');
            });

            // Simpan atau Update Kelas
            $('#userForm').submit(function(e) {
                e.preventDefault();
                let id = $('#user_id').val();
                let url = id ? `/muser/${id}` : "{{ route('muser.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        class_id: $('#class_id').val(),
                        role_id: $('#role_id').val(),
                        username: $('#username').val(),
                        nama: $('#nama').val(),
                        nip: $('#nip').val(),
                        email: $('#email').val(),
                        password: $('#password').val(),
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
                            $('#userModal').modal('hide');
                            location.reload(); // Refresh halaman setelah berhasil
                        }, 2000);

                    },
                    error: function(res) {
                        console.log(res)
                        Swal.fire('Error', 'Terjadi kesalahan, coba lagi!', 'error');
                    }
                });
            });
            //
            // Tampilkan Modal Edit Kelas
            $(document).on('click', '.editUserBtn', function() {
                let id = $(this).data('id');
                let role_id = $(this).data('role_id');
                let class_id = $(this).data('class_id');
                let username = $(this).data('username');
                let nama = $(this).data('nama');
                let nip = $(this).data('nip');
                let email = $(this).data('email');

                $('#user_id').val(id);
                $('#class_id').val(class_id);
                $('#role_id').val(role_id);
                $('#username').val(username);
                $('#nama').val(nama);
                $('#nip').val(nip);
                $('#email').val(email);
                $('#password').val('');
                $('#userModalLabel').text('Edit User');
                $('#userModal').modal('show');
            });

            // Delete Action
            $(document).on('click', '.delUserBtn', function() {
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
                            url: `/muser/${id}`,
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
        })
    </script>
@endsection
