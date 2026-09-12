@extends('layouts.app')

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-database text-warning mr-2"></i>Backup Database
                </h1>
                <p class="text-muted small mb-0">Cadangkan seluruh basis data SiNilai ke format SQL, tersimpan aman di storage privat server.</p>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item active">Backup Database</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- Banner Info Keamanan -->
        <div class="alert alert-info border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-shield-alt fa-2x mr-3 text-info"></i>
                <div>
                    <strong class="d-block font-weight-bold">Keamanan Penyimpanan Database Privat</strong>
                    <span class="small">
                        Seluruh file backup disimpan di direktori privat (<code>storage/app/private/backups/</code>) yang tidak dapat diakses langsung melalui URL publik internet. Hanya pengguna dengan hak akses <strong>Administrator</strong> yang dapat mengunduh atau mengelola berkas cadangan ini.
                    </span>
                </div>
            </div>
        </div>

        <!-- Summary Stat Cards -->
        <div class="row mb-3">
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-file-code"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Total File Backup</span>
                        <span class="info-box-number h5 font-weight-bold" id="statTotalFiles">{{ $stats['total_files'] }} Berkas</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-hdd"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Kapasitas Penyimpanan</span>
                        <span class="info-box-number h5 font-weight-bold">{{ $stats['total_size'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-history text-white"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Backup Terakhir</span>
                        <span class="info-box-number h6 font-weight-bold mb-0 text-truncate" title="{{ $stats['last_backup'] }}">
                            {{ $stats['last_backup'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Action & Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="card-title font-weight-bold text-dark m-0">
                    <i class="fas fa-list-alt text-primary mr-1"></i> Daftar Berkas Cadangan Database (.sql)
                </h5>
                <button type="button" class="btn btn-warning font-weight-bold shadow-sm mt-2 mt-sm-0" id="btnCreateBackup">
                    <i class="fas fa-cloud-download-alt mr-1"></i> Buat Backup Sekarang
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0" id="tableBackups">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Nama Berkas (.sql)</th>
                                <th>Ukuran Berkas</th>
                                <th>Waktu Pembuatan</th>
                                <th style="width: 180px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($backupFiles as $index => $file)
                                <tr id="row-{{ md5($file['filename']) }}">
                                    <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-code text-info fa-lg mr-2"></i>
                                            <div>
                                                <span class="font-weight-bold text-dark">{{ $file['filename'] }}</span>
                                                <span class="badge badge-secondary ml-2">SQL</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border px-2 py-1">
                                            <i class="fas fa-weight-hanging text-muted mr-1"></i>{{ $file['size'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            <i class="far fa-clock mr-1"></i>{{ $file['created_at'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('backup.download', $file['filename']) }}" class="btn btn-primary" title="Download ke Komputer">
                                                <i class="fas fa-download mr-1"></i> Unduh
                                            </a>
                                            <button type="button" class="btn btn-danger btn-delete-backup" data-filename="{{ $file['filename'] }}" data-row="row-{{ md5($file['filename']) }}" title="Hapus dari Server">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-database fa-3x mb-3 text-secondary d-block"></i>
                                        Belum ada berkas backup database di server.<br>
                                        Klik tombol <strong>"Buat Backup Sekarang"</strong> untuk mencadangkan database Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light text-muted small py-2">
                <i class="fas fa-info-circle mr-1"></i> Berkas backup dapat langsung direstore menggunakan aplikasi MySQL Client (seperti phpMyAdmin, DBeaver, HeidiSQL, atau terminal MySQL).
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script type="module">
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Handler Buat Backup
        $('#btnCreateBackup').on('click', function() {
            Swal.fire({
                title: 'Buat Backup Database?',
                text: 'Sistem akan mengekspor seluruh struktur tabel dan data ke dalam file SQL di server privat.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-database mr-1"></i> Ya, Mulai Backup',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mencadangkan Database...',
                        html: 'Sedang mengekstrak skema tabel dan baris data ke file <code>.sql</code>.<br>Mohon tidak menutup halaman ini...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('backup.store') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Backup Berhasil!',
                                html: `
                                    <p class="mb-2 text-dark font-weight-bold">${res.message}</p>
                                    <div class="alert alert-light border text-left p-2 mb-3">
                                        <small class="d-block"><strong>Nama File:</strong> ${res.filename}</small>
                                        <small class="d-block"><strong>Ukuran:</strong> ${res.size}</small>
                                        <small class="d-block"><strong>Penyimpanan:</strong> Server Private Disk</small>
                                    </div>
                                    <a href="${res.download_url}" class="btn btn-success btn-block font-weight-bold">
                                        <i class="fas fa-download mr-1"></i> Unduh File Sekarang
                                    </a>
                                `,
                                showCancelButton: true,
                                cancelButtonText: 'Tutup',
                                showConfirmButton: false,
                                cancelButtonColor: '#6c757d'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat membuat backup database.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Membuat Backup',
                                text: errMsg,
                                confirmButtonText: 'Tutup'
                            });
                        }
                    });
                }
            });
        });

        // Handler Hapus Backup
        $(document).on('click', '.btn-delete-backup', function() {
            let btn = $(this);
            let filename = btn.data('filename');
            let rowId = btn.data('row');

            Swal.fire({
                title: 'Hapus File Backup?',
                html: `Apakah Anda yakin ingin menghapus file <strong>${filename}</strong> dari penyimpanan server?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    let deleteUrl = "{{ url('backup') }}/" + encodeURIComponent(filename);

                    $.ajax({
                        url: deleteUrl,
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            $('#' + rowId).fadeOut(300, function() {
                                $(this).remove();
                                if ($('#tableBackups tbody tr').length === 0) {
                                    location.reload();
                                }
                            });
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Gagal menghapus file backup.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
