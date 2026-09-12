@extends('layouts.portal')

@section('title', 'Data Siswa Belum Terhubung')

@section('content')
<div class="container py-5">
    <div class="card portal-card bg-white shadow-sm p-5 text-center border-0 mx-auto" style="max-width: 600px;">
        <i class="fas fa-user-slash fa-4x text-warning mb-3"></i>
        <h4 class="font-weight-bold text-dark">Akun Belum Terhubung ke Profil Siswa</h4>
        <p class="text-muted mb-4">
            Akun Anda belum dikaitkan dengan data peserta didik aktif. Silakan hubungi Wali Kelas atau Admin Kurikulum sekolah untuk mengaitkan akun Anda dengan NISN yang valid.
        </p>
        <div>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt mr-1"></i>Keluar dari Sistem
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
