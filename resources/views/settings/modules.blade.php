@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-toggle-on text-success mr-2"></i>Pengaturan Modul Sistem
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pengaturan Modul</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-9 col-md-11">
                    <form action="{{ route('settings.modules.update') }}" method="POST">
                        @csrf

                        <div class="card card-outline card-success shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title font-weight-bold mb-0">
                                    <i class="fas fa-cubes text-primary mr-1"></i> Kendali Status Fitur & Navigasi Modul
                                </h5>
                                <div class="card-tools">
                                    <span class="badge badge-light border text-muted">Akses Khusus Administrator</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="p-3 bg-light border-bottom text-muted small">
                                    <i class="fas fa-info-circle mr-1 text-info"></i>
                                    Aktifkan atau nonaktifkan modul di bawah ini sesuai kebutuhan operasional sekolah.
                                    Modul yang dinonaktifkan akan disembunyikan dari menu navigasi guru/wali kelas secara otomatis.
                                </div>

                                <ul class="list-group list-group-flush">
                                    @foreach($modules as $key => $mod)
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <div class="d-flex align-items-center mr-3">
                                                <div class="rounded p-3 bg-light border mr-3 text-center" style="width: 54px; height: 54px;">
                                                    <i class="{{ $mod['icon'] }} fa-lg"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center mb-1">
                                                        <h6 class="font-weight-bold mb-0 text-dark">{{ $mod['name'] }}</h6>
                                                        <span class="badge badge-light border ml-2 text-secondary">{{ $mod['badge'] }}</span>
                                                    </div>
                                                    <p class="text-muted small mb-0">{{ $mod['description'] }}</p>
                                                </div>
                                            </div>
                                            <div class="custom-control custom-switch custom-switch-lg text-right" style="cursor: pointer;">
                                                <input type="checkbox" class="custom-control-input" id="switch_{{ $key }}" name="module_{{ $key }}" value="1" {{ $mod['enabled'] ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold" for="switch_{{ $key }}">
                                                    <span class="status-label {{ $mod['enabled'] ? 'text-success' : 'text-muted' }}">
                                                        {{ $mod['enabled'] ? 'Aktif' : 'Non-aktif' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
                                <span class="text-muted small">
                                    <i class="fas fa-shield-alt text-secondary mr-1"></i> Perubahan akan langsung berdampak pada seluruh pengguna.
                                </span>
                                <button type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Simpan Status Modul
                                </button>
                            </div>
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
    $('.custom-control-input').on('change', function() {
        let label = $(this).siblings('label').find('.status-label');
        if ($(this).is(':checked')) {
            label.text('Aktif').removeClass('text-muted').addClass('text-success');
        } else {
            label.text('Non-aktif').removeClass('text-success').addClass('text-muted');
        }
    });
});
</script>
@endsection
