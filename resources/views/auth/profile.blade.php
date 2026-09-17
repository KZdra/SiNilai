@extends('layouts.app')

@section('title', 'Pengaturan Akun & Profil')

@section('content')
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-user-cog text-primary mr-2"></i>{{ __('Pengaturan Akun & Profil') }}
                    </h1>
                    <p class="text-muted small mb-0">Kelola identitas akun login, NIP, serta perbarui kata sandi Anda.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active">Profil Pengguna</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <strong><i class="fas fa-exclamation-triangle mr-2"></i> Periksa kembali inputan Anda:</strong>
                    <ul class="mb-0 mt-1 pl-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">

                <!-- ── KOLOM KIRI: KARTU IDENTITAS & PENUGASAN (READ-ONLY) ── -->
                <div class="col-lg-4 col-md-5 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center p-4">
                            <!-- Avatar Circle -->
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border shadow-sm mb-3"
                                 style="width: 88px; height: 88px;">
                                <i class="fas fa-user text-primary" style="font-size: 40px;"></i>
                            </div>

                            <h5 class="font-weight-bold text-dark mb-1">{{ $user->name }}</h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-at text-muted mr-1"></i>{{ $user->username }}
                            </p>

                            <!-- Badge Role -->
                            <div class="mb-3">
                                @if($user->role_id == 1)
                                    <span class="badge badge-primary px-3 py-1 font-weight-bold shadow-sm">
                                        <i class="fas fa-shield-alt mr-1"></i> Administrator Sekolah
                                    </span>
                                @elseif($user->role_id == 2)
                                    <span class="badge badge-success px-3 py-1 font-weight-bold shadow-sm">
                                        <i class="fas fa-chalkboard-teacher mr-1"></i> Guru / Tenaga Pendidik
                                    </span>
                                @else
                                    <span class="badge badge-info px-3 py-1 font-weight-bold shadow-sm">
                                        <i class="fas fa-user-graduate mr-1"></i> Siswa
                                    </span>
                                @endif
                            </div>

                            <!-- List Detail Penugasan & Akun -->
                            <ul class="list-group list-group-unbordered text-left small mt-4">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span class="text-muted"><i class="fas fa-chalkboard text-info mr-2"></i>Penugasan Kelas:</span>
                                    <span>
                                        @if(!empty($className))
                                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                                <i class="fas fa-star mr-1 text-danger"></i>Wali Kelas {{ $className }}
                                            </span>
                                        @else
                                            <span class="badge badge-light border text-muted px-2 py-1">
                                                Bukan Walas (Guru Mapel)
                                            </span>
                                        @endif
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span class="text-muted"><i class="fas fa-id-badge text-secondary mr-2"></i>NIP:</span>
                                    <strong class="text-dark">{{ $user->nip ?? '-' }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span class="text-muted"><i class="fas fa-envelope text-secondary mr-2"></i>Email:</span>
                                    <span class="text-muted">{{ $user->email ?? '-' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span class="text-muted"><i class="fas fa-calendar-alt text-secondary mr-2"></i>Terdaftar Sejak:</span>
                                    <span class="text-muted">{{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d F Y') }}</span>
                                </li>
                            </ul>

                            <!-- Keterangan Kebijakan Keamanan -->
                            <div class="alert alert-light border small text-muted text-left mt-4 mb-0" style="font-size: 0.82rem; line-height: 1.4;">
                                <i class="fas fa-lock text-warning mr-1"></i>
                                <strong>Catatan Penugasan:</strong> Hak akses sistem (Role) dan penetapan penugasan Wali Kelas diatur secara terpusat oleh <strong>Administrator Sekolah</strong>.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── KOLOM KANAN: FORM EDIT PROFIL & GANTI PASSWORD ── -->
                <div class="col-lg-8 col-md-7 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-user-edit text-primary mr-2"></i>Perbarui Data Akun
                            </h5>
                            <span class="badge badge-light border text-muted px-2 py-1 small">
                                <i class="fas fa-info-circle mr-1"></i>Data Pribadi
                            </span>
                        </div>

                        <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
                            @csrf
                            @method('PUT')

                            <div class="card-body p-4">

                                <!-- SEKSI 1: IDENTITAS PENGGUNA -->
                                <h6 class="text-uppercase font-weight-bold text-primary small mb-3 pb-1 border-bottom" style="letter-spacing: 0.5px;">
                                    <i class="fas fa-id-card mr-1"></i> 1. Identitas Akun & Login
                                </h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="username" class="font-weight-bold small text-muted text-uppercase mb-1">
                                                Username Login <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-at text-muted"></i></span>
                                                </div>
                                                <input type="text" name="username" id="username"
                                                       class="form-control font-weight-bold @error('username') is-invalid @enderror"
                                                       value="{{ old('username', $user->username) }}"
                                                       placeholder="Username untuk login" required>
                                            </div>
                                            <small class="form-text text-muted">Username ini digunakan setiap kali Anda login ke sistem.</small>
                                            @error('username')
                                                <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name" class="font-weight-bold small text-muted text-uppercase mb-1">
                                                Nama Lengkap & Gelar <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                                </div>
                                                <input type="text" name="name" id="name"
                                                       class="form-control font-weight-bold @error('name') is-invalid @enderror"
                                                       value="{{ old('name', $user->name) }}"
                                                       placeholder="Contoh: Budi Santoso, S.Pd" required>
                                            </div>
                                            <small class="form-text text-muted">Nama ini akan tercantum pada tanda tangan dan lembar cetak rapor.</small>
                                            @error('name')
                                                <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="nip" class="font-weight-bold small text-muted text-uppercase mb-1">
                                                NIP (Nomor Induk Pegawai)
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-barcode text-muted"></i></span>
                                                </div>
                                                <input type="text" name="nip" id="nip"
                                                       class="form-control @error('nip') is-invalid @enderror"
                                                       value="{{ old('nip', $user->nip) }}"
                                                       placeholder="18 digit NIP atau strip (-)">
                                            </div>
                                            <small class="form-text text-muted">Opsional. Kosongkan jika belum memiliki NIP.</small>
                                            @error('nip')
                                                <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="email" class="font-weight-bold small text-muted text-uppercase mb-1">
                                                Alamat Email (Opsional)
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                                </div>
                                                <input type="email" name="email" id="email"
                                                       class="form-control @error('email') is-invalid @enderror"
                                                       value="{{ old('email', $user->email) }}"
                                                       placeholder="alamat@email.com">
                                            </div>
                                            <small class="form-text text-muted">Opsional, boleh dikosongkan.</small>
                                            @error('email')
                                                <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- SEKSI 2: GANTI KATA SANDI -->
                                <h6 class="text-uppercase font-weight-bold text-primary small mt-4 mb-3 pb-1 border-bottom" style="letter-spacing: 0.5px;">
                                    <i class="fas fa-shield-alt mr-1"></i> 2. Keamanan & Perubahan Kata Sandi
                                </h6>

                                <div class="alert alert-info border-0 shadow-sm py-2 px-3 mb-3 small">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Biarkan kolom kata sandi kosong di bawah ini jika Anda <strong>tidak ingin</strong> mengubah kata sandi akun Anda.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="password" class="font-weight-bold small text-muted text-uppercase mb-1">
                                                Kata Sandi Baru
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                                </div>
                                                <input type="password" name="password" id="password"
                                                       class="form-control @error('password') is-invalid @enderror"
                                                       placeholder="Minimal 4 karakter"
                                                       autocomplete="new-password">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-toggle-pass" type="button" data-target="password">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @error('password')
                                                <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="password_confirmation" class="font-weight-bold small text-muted text-uppercase mb-1">
                                                Ulangi Kata Sandi Baru
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                                </div>
                                                <input type="password" name="password_confirmation" id="password_confirmation"
                                                       class="form-control"
                                                       placeholder="Ketik ulang kata sandi baru"
                                                       autocomplete="new-password">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-toggle-pass" type="button" data-target="password_confirmation">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm font-weight-bold px-3">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-4 shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan Profil
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
<script>
    $(document).ready(function() {
        // Toggle view password
        $('.btn-toggle-pass').click(function() {
            let targetId = $(this).data('target');
            let input = $('#' + targetId);
            let icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
</script>
@endsection