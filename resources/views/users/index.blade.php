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

                            <table class="table table-striped table-bordered w-100" id="usersTable">
                                <thead>
                                     <tr>
                                         <th>Username</th>
                                         <th>Nama</th>
                                         <th>Nip</th>
                                         <th>Wali Kelas</th>
                                         <th>Email</th>
                                         <th>Role</th>
                                         <th>Aksi</th>
                                     </tr>
                                 </thead>
                                 <tbody>
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
                                            <label for="class_id">Wali Kelas Untuk</label>
                                            <select name="class_id" id="class_id" class="form-control">
                                                <option value="">-- Bukan Wali Kelas (Guru Mapel Murni) --</option>
                                                @include('partials.select_class_options')
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
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.getData') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'username', defaultContent: '-' },
                    { data: 'name', defaultContent: '-' },
                    { data: 'nip', defaultContent: '-' },
                    {
                        data: 'class_name',
                        render: function(data) {
                            return data ? `<span class="badge badge-info"><i class="fas fa-chalkboard-teacher mr-1"></i>${data}</span>` : `<span class="badge badge-light text-muted border">Bukan Walas</span>`;
                        }
                    },
                    { data: 'email', defaultContent: '-' },
                    {
                        data: 'role_name',
                        render: function(data) {
                            return data ? data.charAt(0).toUpperCase() + data.slice(1) : '-';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            let safeName = (row.name || '').replace(/"/g, '&quot;');
                            let safeUname = (row.username || '').replace(/"/g, '&quot;');
                            return `
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary editUserBtn"
                                        data-id="${row.id}"
                                        data-class_id="${row.class_id || ''}"
                                        data-role_id="${row.role_id || ''}"
                                        data-nama="${safeName}"
                                        data-nip="${row.nip || ''}"
                                        data-username="${safeUname}"
                                        data-email="${row.email || ''}">Edit</button>
                                    <button class="btn btn-sm btn-danger delUserBtn" data-id="${row.id}">Delete</button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            // Tampilkan Modal Tambah User
            $('#inputUserBtn').click(function() {
                $('#user_id').val('');
                $('#class_id').val('').trigger('change');
                $('#role_id').val('').trigger('change');
                $('#username').val('');
                $('#nama').val('');
                $('#nip').val('');
                $('#email').val('');
                $('#password').val('');
                $('#userModalLabel').text('Tambah User');
                $('#userModal').modal('show');
            });

            // Simpan atau Update User
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
                        $('#userModal').modal('hide');
                        table.ajax.reload(null, false);
                    },
                    error: function(res) {
                        console.log(res);
                        Swal.fire('Error', res.responseJSON?.message || 'Terjadi kesalahan, coba lagi!', 'error');
                    }
                });
            });

            // Tampilkan Modal Edit User
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
        });
    </script>
@endsection
