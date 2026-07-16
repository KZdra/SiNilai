@extends('layouts.app')

@section('title', 'Mapping Mata Pelajaran')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Mapping Mata Pelajaran Kelas</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Filter Data</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#copyModal">
                        <i class="fas fa-copy"></i> Salin dari Semester Lain
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('mapel_mapping.index') }}" method="GET" id="filterForm">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Fase / Semester / Tahun Ajaran Aktif</label>
                                <select name="fst_id" class="form-control select2" onchange="document.getElementById('filterForm').submit();">
                                    @foreach($fsts as $fst)
                                        <option value="{{ $fst->id }}" {{ $selectedFstId == $fst->id ? 'selected' : '' }}>
                                            Fase {{ $fst->fase }} - Semester {{ $fst->semester }} ({{ $fst->tahun_ajaran }} {{ $fst->ta }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Kelas</label>
                                <select name="class_id" class="form-control select2" onchange="document.getElementById('filterForm').submit();">
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                            {{ $c->class_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Mata Pelajaran</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-info" id="btnAktifkanSemua">
                        <i class="fas fa-check-double"></i> Aktifkan Semua
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-striped">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Mata Pelajaran</th>
                            <th width="150" class="text-center">Status Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mapels as $index => $mapel)
                            @php
                                $isActive = isset($mappings[$mapel->id]) ? $mappings[$mapel->id] : 0;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $mapel->nama_mapel }}</td>
                                <td class="text-center">
                                    <select class="form-control toggle-mapel" data-mapel="{{ $mapel->id }}">
                                        <option value="1" {{ $isActive ? 'selected' : '' }}>ON</option>
                                        <option value="0" {{ !$isActive ? 'selected' : '' }}>OFF</option>
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada mata pelajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Copy -->
<div class="modal fade" id="copyModal" tabindex="-1" role="dialog" aria-labelledby="copyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="copyForm">
            @csrf
            <input type="hidden" name="target_fst_id" value="{{ $selectedFstId }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="copyModalLabel">Salin Konfigurasi Semester Lain</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Sistem akan menyalin pengaturan ON/OFF seluruh mata pelajaran untuk semua kelas dari semester yang Anda pilih ke semester aktif saat ini.</p>
                    <div class="form-group">
                        <label>Pilih Semester Sumber</label>
                        <select name="source_fst_id" class="form-control" required>
                            <option value="">-- Pilih Semester --</option>
                            @foreach($otherFsts as $fst)
                                <option value="{{ $fst->id }}">
                                    Fase {{ $fst->fase }} - Semester {{ $fst->semester }} ({{ $fst->tahun_ajaran }} {{ $fst->ta }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnCopy"><i class="fas fa-copy"></i> Salin Data</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    console.log('ready');
    // Ajax setup for CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    // Toggle Switch
    $('.toggle-mapel').on('change', function() {
        var mapel_id = $(this).data('mapel');
        var is_active = $(this).val();
        var fst_id = '{{ $selectedFstId }}';
        var class_id = '{{ $selectedClassId }}';
        var self = $(this);

        if (!fst_id || !class_id) {
            SwalHelper.showError('Filter FST dan Kelas harus dipilih terlebih dahulu!');
            self.val(is_active == 1 ? 0 : 1); // revert
            return;
        }

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
                SwalHelper.showSuccess(response.message);
            },
            error: function(xhr) {
                SwalHelper.showError(xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan');
                self.val(is_active == 1 ? 0 : 1); // revert
            }
        });
    });

    // Aktifkan Semua Action
    $('#btnAktifkanSemua').on('click', function() {
        console.log('activated');
        var fst_id = '{{ $selectedFstId }}';
        var class_id = '{{ $selectedClassId }}';

        if (!fst_id || !class_id) {
            SwalHelper.showError('Filter FST dan Kelas harus dipilih terlebih dahulu!');
            return;
        }

        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Ingin mengaktifkan SEMUA mata pelajaran untuk kelas dan semester ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Aktifkan Semua!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $('#btnAktifkanSemua').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                
                $.ajax({
                    url: '{{ route("mapel_mapping.activate_all") }}',
                    type: 'POST',
                    data: {
                        class_id: class_id,
                        fst_id: fst_id
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
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        SwalHelper.showError(xhr.responseJSON ? xhr.responseJSON.message : 'Error');
                        $('#btnAktifkanSemua').prop('disabled', false).html('<i class="fas fa-check-double"></i> Aktifkan Semua');
                    }
                });
            }
        });
    });

    // Submit Copy Form
    $('#copyForm').on('submit', function(e) {
        e.preventDefault();
        $('#btnCopy').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyalin...');

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
                SwalHelper.showError(xhr.responseJSON ? xhr.responseJSON.message : 'Error');
                $('#btnCopy').prop('disabled', false).html('<i class="fas fa-copy"></i> Salin Data');
            }
        });
    });
});
</script>
@endsection
