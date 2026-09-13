@extends('layouts.app')

@section('title', 'Mapping Mata Pelajaran Kelas')

@section('styles')
<style>
    /* Custom Switch Enhancements */
    .custom-switch-md .custom-control-label::before {
        height: 1.5rem;
        width: 2.75rem;
        border-radius: 2rem;
    }
    .custom-switch-md .custom-control-label::after {
        width: calc(1.5rem - 4px);
        height: calc(1.5rem - 4px);
        border-radius: calc(2rem - (1.5rem / 2));
    }
    .custom-switch-md .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.25rem);
    }
    .custom-switch-md .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #28a745;
        border-color: #28a745;
    }

    .mapel-row {
        transition: background-color 0.15s ease;
    }
    .mapel-row.is-active {
        background-color: #ffffff;
    }
    .mapel-row.is-inactive {
        background-color: #fafafa;
    }
    .mapel-row.is-inactive .mapel-name {
        color: #6c757d;
    }

    .save-indicator {
        transition: opacity 0.3s ease;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-network-wired text-primary mr-2"></i>{{ __('Mapping Mata Pelajaran Kelas') }}
                </h1>
                <p class="text-muted small mb-0">Atur ketersediaan mata pelajaran yang diajarkan pada kelas dan periode semester tertentu.</p>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item">Kelola Data Master</li>
                    <li class="breadcrumb-item active">Mapping Mapel</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-filter text-primary mr-1"></i> Filter Periode & Kelas
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-outline-success font-weight-bold shadow-sm" data-toggle="modal" data-target="#copyModal">
                        <i class="fas fa-copy mr-1"></i> Salin dari Semester Lain
                    </button>
                </div>
            </div>
            <div class="card-body py-3">
                <form action="{{ route('mapel_mapping.index') }}" method="GET" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-6 col-12 mb-2">
                            <label class="font-weight-bold small text-muted text-uppercase mb-1">
                                <i class="fas fa-calendar-alt text-warning mr-1"></i> 1. Periode / Semester Aktif
                            </label>
                            <select name="fst_id" id="filter_fst_id" class="form-control font-weight-bold" onchange="document.getElementById('filterForm').submit();">
                                @include('partials.select_fst_options', ['fstList' => $fsts, 'selectedId' => $selectedFstId])
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-2">
                            <label class="font-weight-bold small text-muted text-uppercase mb-1">
                                <i class="fas fa-school text-primary mr-1"></i> 2. Kelas
                            </label>
                            <select name="class_id" id="filter_class_id" class="form-control font-weight-bold" onchange="document.getElementById('filterForm').submit();">
                                @include('partials.select_class_options', ['classList' => $classes, 'selectedId' => $selectedClassId])
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card card-outline card-secondary shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <!-- Context Badges -->
                    <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                        <span class="badge badge-light border text-dark px-2 py-2">
                            <i class="fas fa-school text-primary mr-1"></i>
                            <span id="badgeClassText">{{ $classes->where('id', $selectedClassId)->first()->class_name ?? 'Kelas' }}</span>
                        </span>
                        <span class="badge badge-success px-2 py-2 shadow-sm font-weight-bold">
                            <i class="fas fa-check-circle mr-1"></i> <span id="activeCount">0</span> Aktif
                        </span>
                        <span class="badge badge-secondary px-2 py-2 shadow-sm font-weight-bold">
                            <i class="fas fa-times-circle mr-1"></i> <span id="inactiveCount">0</span> Nonaktif
                        </span>
                        <span class="badge badge-info px-2 py-2 shadow-sm font-weight-bold">
                            <i class="fas fa-book mr-1"></i> Total <span id="totalCount">{{ count($mapels) }}</span> Mapel
                        </span>
                    </div>

                    <!-- Right Controls: Quick Search & Batch Buttons -->
                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="searchMapel" class="form-control border-left-0" placeholder="Cari mata pelajaran...">
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btnAktifkanSemua">
                            <i class="fas fa-check-double mr-1"></i> Aktifkan Semua
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold" id="btnNonaktifkanSemua">
                            <i class="fas fa-ban mr-1"></i> Nonaktifkan Semua
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-bordered mb-0" id="mappingTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>Nama Mata Pelajaran</th>
                            <th style="width: 220px;" class="text-center">Status Pembelajaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mapels as $index => $mapel)
                            @php
                                $isActive = isset($mappings[$mapel->id]) ? (int)$mappings[$mapel->id] : 0;
                            @endphp
                            <tr class="mapel-row {{ $isActive ? 'is-active' : 'is-inactive' }}" data-mapel-name="{{ strtolower($mapel->nama_mapel) }}">
                                <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                <td class="mapel-name font-weight-bold">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-book text-muted mr-2" style="font-size: 0.9rem;"></i>
                                        <span>{{ $mapel->nama_mapel }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center justify-content-center" style="gap: 10px;">
                                        <div class="custom-control custom-switch custom-switch-md">
                                            <input type="checkbox" class="custom-control-input toggle-mapel-switch" 
                                                   id="switch-{{ $mapel->id }}" 
                                                   data-mapel="{{ $mapel->id }}" 
                                                   {{ $isActive ? 'checked' : '' }}>
                                            <label class="custom-control-label cursor-pointer" for="switch-{{ $mapel->id }}"></label>
                                        </div>
                                        <span class="badge {{ $isActive ? 'badge-success' : 'badge-secondary' }} status-badge font-weight-bold px-2 py-1" style="min-width: 72px;">
                                            {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                        <span class="save-indicator text-success small font-weight-bold" style="display: none; width: 18px;" title="Tersimpan">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                    Belum ada data mata pelajaran yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light py-2 px-3 text-muted small d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <i class="fas fa-info-circle text-info mr-1"></i>
                    <strong>Petunjuk:</strong> Klik saklar (*toggle switch*) untuk mengaktifkan atau menonaktifkan mata pelajaran secara instan.
                </div>
                <div>
                    Perubahan langsung tersimpan otomatis secara real-time ke database.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Salin Semester -->
<div class="modal fade" id="copyModal" tabindex="-1" role="dialog" aria-labelledby="copyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="copyForm">
            @csrf
            <input type="hidden" name="target_fst_id" value="{{ $selectedFstId }}">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="copyModalLabel">
                        <i class="fas fa-copy mr-2"></i> Salin Konfigurasi dari Semester Lain
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-muted small mb-3">
                        Fitur ini akan menyalin seluruh pengaturan status aktif mata pelajaran untuk semua kelas dari semester sumber ke semester aktif saat ini.
                    </p>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold small text-muted text-uppercase mb-1">Pilih Semester Sumber</label>
                        <select name="source_fst_id" class="form-control font-weight-bold" required>
                            <option value="" selected disabled>-- Pilih Semester Sumber --</option>
                            @include('partials.select_fst_options', ['fstList' => $otherFsts])
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold px-3" id="btnCopy">
                        <i class="fas fa-check mr-1"></i> Salin Data Sekarang
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    const fst_id = '{{ $selectedFstId }}';
    const class_id = '{{ $selectedClassId }}';

    // Recalculate Active & Inactive Counters
    function updateCounters() {
        const total = $('.toggle-mapel-switch').length;
        const active = $('.toggle-mapel-switch:checked').length;
        const inactive = total - active;

        $('#activeCount').text(active);
        $('#inactiveCount').text(inactive);
        $('#totalCount').text(total);
    }
    updateCounters();

    // Instant Toggle Switch
    $('.toggle-mapel-switch').on('change', function() {
        const switchEl = $(this);
        const mapel_id = switchEl.data('mapel');
        const is_active = switchEl.is(':checked') ? 1 : 0;
        const row = switchEl.closest('.mapel-row');
        const badge = row.find('.status-badge');
        const saveIndicator = row.find('.save-indicator');

        if (!fst_id || !class_id) {
            SwalHelper.showError('Filter FST dan Kelas harus dipilih terlebih dahulu!');
            switchEl.prop('checked', !is_active);
            return;
        }

        // Optimistic UI update
        if (is_active) {
            badge.removeClass('badge-secondary').addClass('badge-success').text('Aktif');
            row.removeClass('is-inactive').addClass('is-active');
        } else {
            badge.removeClass('badge-success').addClass('badge-secondary').text('Nonaktif');
            row.removeClass('is-active').addClass('is-inactive');
        }
        updateCounters();

        $.ajax({
            url: '{{ route("mapel_mapping.toggle") }}',
            type: 'POST',
            data: {
                mapel_id: mapel_id,
                class_id: class_id,
                fst_id: fst_id,
                is_active: is_active
            },
            success: function(response) {
                // Subtle saved indicator flash
                saveIndicator.stop(true, true).fadeIn(150).delay(800).fadeOut(300);
            },
            error: function(xhr) {
                // Revert on failure
                switchEl.prop('checked', !is_active);
                if (!is_active) {
                    badge.removeClass('badge-secondary').addClass('badge-success').text('Aktif');
                    row.removeClass('is-inactive').addClass('is-active');
                } else {
                    badge.removeClass('badge-success').addClass('badge-secondary').text('Nonaktif');
                    row.removeClass('is-active').addClass('is-inactive');
                }
                updateCounters();
                SwalHelper.showError(xhr.responseJSON?.message || 'Gagal mengubah status mapel.');
            }
        });
    });

    // Real-time Search by Mapel Name
    $('#searchMapel').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        if (!query) {
            $('.mapel-row').show();
            return;
        }
        $('.mapel-row').each(function() {
            const name = $(this).data('mapel-name') || '';
            if (name.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Aktifkan Semua Action
    $('#btnAktifkanSemua').on('click', function() {
        if (!fst_id || !class_id) {
            SwalHelper.showError('Pilih periode semester dan kelas terlebih dahulu!');
            return;
        }

        Swal.fire({
            title: "Aktifkan Semua Mapel?",
            text: "Seluruh mata pelajaran akan diaktifkan untuk kelas dan semester ini.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Aktifkan Semua!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $('#btnAktifkanSemua');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

                $.ajax({
                    url: '{{ route("mapel_mapping.activate_all") }}',
                    type: 'POST',
                    data: {
                        class_id: class_id,
                        fst_id: fst_id
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fas fa-check-double mr-1"></i> Aktifkan Semua');
                        SwalHelper.showSuccess(response.message);
                        $('.toggle-mapel-switch').prop('checked', true);
                        $('.status-badge').removeClass('badge-secondary').addClass('badge-success').text('Aktif');
                        $('.mapel-row').removeClass('is-inactive').addClass('is-active');
                        updateCounters();
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-check-double mr-1"></i> Aktifkan Semua');
                        SwalHelper.showError(xhr.responseJSON?.message || 'Terjadi kesalahan.');
                    }
                });
            }
        });
    });

    // Nonaktifkan Semua Action
    $('#btnNonaktifkanSemua').on('click', function() {
        if (!fst_id || !class_id) {
            SwalHelper.showError('Pilih periode semester dan kelas terlebih dahulu!');
            return;
        }

        Swal.fire({
            title: "Nonaktifkan Semua Mapel?",
            text: "Seluruh mata pelajaran akan dinonaktifkan untuk kelas dan semester ini.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Nonaktifkan Semua!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $('#btnNonaktifkanSemua');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

                $.ajax({
                    url: '{{ route("mapel_mapping.deactivate_all") }}',
                    type: 'POST',
                    data: {
                        class_id: class_id,
                        fst_id: fst_id
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fas fa-ban mr-1"></i> Nonaktifkan Semua');
                        SwalHelper.showSuccess(response.message);
                        $('.toggle-mapel-switch').prop('checked', false);
                        $('.status-badge').removeClass('badge-success').addClass('badge-secondary').text('Nonaktif');
                        $('.mapel-row').removeClass('is-active').addClass('is-inactive');
                        updateCounters();
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-ban mr-1"></i> Nonaktifkan Semua');
                        SwalHelper.showError(xhr.responseJSON?.message || 'Terjadi kesalahan.');
                    }
                });
            }
        });
    });

    // Submit Copy Form
    $('#copyForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnCopy');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyalin...');

        $.ajax({
            url: '{{ route("mapel_mapping.copy") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Salin Data Sekarang');
                SwalHelper.showError(xhr.responseJSON?.message || 'Gagal menyalin konfigurasi semester.');
            }
        });
    });
});
</script>
@endsection
