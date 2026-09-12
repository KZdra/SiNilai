@extends('layouts.portal')

@section('title', 'Ganti Password Akun')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <!-- Breadcrumb / Back button -->
            <div class="mb-3">
                <a href="{{ route('portal.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Beranda
                </a>
            </div>

            <!-- Main Card -->
            <div class="card portal-card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 mr-3 text-primary" style="background-color: #e8f0fe;">
                            <i class="fas fa-key fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="font-weight-bold text-dark mb-1">Ganti Password</h4>
                            <p class="text-muted small mb-0">Ubah kata sandi akun portal mandiri Anda untuk keamanan data.</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">

                    <!-- Info Siswa Box -->
                    <div class="alert alert-light border py-2 px-3 mb-4 rounded">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <small class="text-muted d-block">Nama Siswa:</small>
                                <strong>{{ $student->nama }}</strong>
                            </div>
                            <div class="mt-2 mt-sm-0 text-sm-right">
                                <small class="text-muted d-block">Username / NISN:</small>
                                <span class="badge badge-secondary px-2 py-1">{{ Auth::user()->username }}</span>
                            </div>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Gagal Memperbarui:</strong>
                            <ul class="mb-0 mt-1 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('portal.password.update') }}" method="POST" id="formGantiPassword">
                        @csrf
                        @method('PUT')

                        <!-- Current Password -->
                        <div class="form-group mb-3">
                            <label for="current_password" class="font-weight-bold text-dark small">
                                Password Saat Ini <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       placeholder="Masukkan password yang saat ini digunakan"
                                       required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-toggle-pwd" type="button" data-target="#current_password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('current_password')
                                <small class="text-danger font-weight-bold mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <hr class="my-3">

                        <!-- New Password -->
                        <div class="form-group mb-3">
                            <label for="password" class="font-weight-bold text-dark small">
                                Password Baru <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Minimal 6 karakter"
                                       minlength="6"
                                       required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-toggle-pwd" type="button" data-target="#password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Gunakan kombinasi huruf dan angka agar password lebih aman.</small>
                            @error('password')
                                <small class="text-danger font-weight-bold mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Confirm New Password -->
                        <div class="form-group mb-4">
                            <label for="password_confirmation" class="font-weight-bold text-dark small">
                                Konfirmasi Password Baru <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       name="password_confirmation" 
                                       id="password_confirmation" 
                                       class="form-control" 
                                       placeholder="Ulangi password baru di atas"
                                       minlength="6"
                                       required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-toggle-pwd" type="button" data-target="#password_confirmation">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="{{ route('portal.dashboard') }}" class="btn btn-light text-muted font-weight-bold">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary font-weight-bold px-4 shadow-sm" id="btnSimpanPassword">
                                <i class="fas fa-save mr-1"></i> Simpan Password Baru
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    // Toggle show / hide password
    $('.btn-toggle-pwd').on('click', function() {
        let targetSelector = $(this).data('target');
        let input = $(targetSelector);
        let icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Validasi sederhana sebelum submit
    $('#formGantiPassword').on('submit', function(e) {
        let newPwd = $('#password').val();
        let confirmPwd = $('#password_confirmation').val();

        if (newPwd.length < 6) {
            e.preventDefault();
            SwalHelper.showWarning('Password baru minimal harus 6 karakter!', 'Perhatian');
            return false;
        }

        if (newPwd !== confirmPwd) {
            e.preventDefault();
            SwalHelper.showWarning('Konfirmasi password baru tidak cocok dengan password baru!', 'Perhatian');
            return false;
        }
    });
});
</script>
@endsection
