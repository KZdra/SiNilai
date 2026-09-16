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
                        </div>

                        <!-- Card Engine Pembuatan Berkas ZIP Rapor -->
                        <div class="card card-outline card-danger shadow-sm mt-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title font-weight-bold mb-0 text-dark">
                                    <i class="fas fa-file-archive text-danger mr-1"></i> Engine Pembuatan Berkas ZIP Rapor
                                </h5>
                                <div class="card-tools">
                                    <span class="badge badge-danger">Konfigurasi Global Server</span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="alert alert-light border mb-3 small text-muted">
                                    <i class="fas fa-info-circle text-info mr-1"></i>
                                    Pengaturan ini menentukan bagaimana sistem memproses pembentukan berkas ZIP rapor saat wali kelas mengunduh rapor 1 kelas. Wali kelas tidak perlu lagi memilih opsi teknis rumit.
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <div class="border rounded p-3 h-100 {{ ($zipEngine ?? 'chunk') === 'chunk' ? 'bg-light border-primary' : 'bg-white' }}" style="cursor: pointer;" onclick="document.getElementById('engine_chunk').checked = true;">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="engine_chunk" name="raport_zip_engine" value="chunk" class="custom-control-input" {{ ($zipEngine ?? 'chunk') === 'chunk' ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="engine_chunk">
                                                    <i class="fas fa-desktop text-success mr-1"></i> Mode Langsung di Layar (Chunking)
                                                </label>
                                            </div>
                                            <div class="mt-2 ml-4 small text-muted">
                                                <span class="badge badge-success px-2 py-1 mb-1">Rekomendasi Shared Hosting / cPanel</span>
                                                <p class="mb-0">
                                                    Merender PDF secara bertahap (4 siswa per batch) langsung di browser. <strong>Tidak memerlukan daemon supervisor / queue worker server</strong>, bebas risiko script timeout, dan file ZIP otomatis terunduh.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100 {{ ($zipEngine ?? 'chunk') === 'queue' ? 'bg-light border-primary' : 'bg-white' }}" style="cursor: pointer;" onclick="document.getElementById('engine_queue').checked = true;">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="engine_queue" name="raport_zip_engine" value="queue" class="custom-control-input" {{ ($zipEngine ?? 'chunk') === 'queue' ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="engine_queue">
                                                    <i class="fas fa-cogs text-info mr-1"></i> Mode Antrean Latar Belakang (Queue Worker)
                                                </label>
                                            </div>
                                            <div class="mt-2 ml-4 small text-muted">
                                                <span class="badge badge-info px-2 py-1 mb-1">VPS / Server Dedicated</span>
                                                <p class="mb-0">
                                                    Tugas dimasukkan ke antrean database/redis dan dieksekusi oleh worker di background (<code>php artisan queue:work</code>). Cocok jika server memiliki worker daemon tersendiri.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
                                <span class="text-muted small">
                                    <i class="fas fa-shield-alt text-secondary mr-1"></i> Perubahan akan langsung berdampak pada fitur unduh ZIP seluruh wali kelas.
                                </span>
                                <button type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Simpan Semua Pengaturan
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
