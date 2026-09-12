@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <a href="{{ route('p5.index') }}" class="btn btn-sm btn-outline-secondary mr-2">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <i class="fas fa-pen-nib mr-2 text-primary"></i>Penilaian Projek P5
                    </h1>
                    <p class="text-muted small mb-0 mt-1">
                        <strong>{{ $projek->nama_projek }}</strong> &bull; Tema: <span class="badge badge-info">{{ $projek->tema }}</span> &bull; Kelas: {{ $projek->class_name }} &bull; Semester: {{ $projek->semester }}
                    </p>
                </div>
                <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                    <button class="btn btn-success shadow-sm font-weight-bold" id="btnSavePenilaian">
                        <i class="fas fa-save mr-1"></i>Simpan Semua Penilaian
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            @if ($projek->is_locked)
                <div class="alert alert-warning border-0 shadow-sm mb-4">
                    <i class="fas fa-lock mr-2"></i>
                    <strong>Semester Ini Terkunci!</strong> Penilaian P5 berada dalam mode <em>Read-Only</em> (hanya lihat) karena telah dikunci oleh Kurikulum.
                </div>
            @endif

            <!-- Info Petunjuk Predikat -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-2 bg-light rounded d-flex flex-wrap align-items-center justify-content-between">
                    <div class="small font-weight-bold text-secondary mr-3 mb-1 mb-md-0">
                        <i class="fas fa-info-circle text-primary mr-1"></i>Rubrik Capaian Profil Pelajar Pancasila:
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-secondary px-2 py-1 mr-2"><strong class="text-warning">MB</strong> = Mulai Berkembang</span>
                        <span class="badge badge-secondary px-2 py-1 mr-2"><strong class="text-info">SB</strong> = Sedang Berkembang</span>
                        <span class="badge badge-secondary px-2 py-1 mr-2"><strong class="text-success">BSH</strong> = Berkembang Sesuai Harapan</span>
                        <span class="badge badge-secondary px-2 py-1"><strong class="text-primary">SAB</strong> = Sangat Berkembang</span>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-primary ml-auto" id="btnSetDefaultBSH" {{ $projek->is_locked ? 'disabled' : '' }}>
                        <i class="fas fa-magic mr-1"></i>Isi Semua Siswa dengan BSH
                    </button>
                </div>
            </div>

            <form id="formPenilaianP5">
                @csrf
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" id="matrixTable">
                                <thead class="bg-dark text-white text-center small">
                                    <tr>
                                        <th rowspan="2" style="width: 40px; vertical-align: middle;">No</th>
                                        <th rowspan="2" style="width: 200px; vertical-align: middle;" class="text-left">Nama Siswa</th>
                                        @foreach ($targetSubelemen as $idx => $target)
                                            <th style="min-width: 170px;">
                                                <span class="badge badge-primary mb-1">{{ $target->dimensi_kode }}</span>
                                                <div class="font-weight-bold" title="{{ $target->capaian_fase }}">{{ $target->nama_subelemen }}</div>
                                                <small class="text-muted d-block text-truncate" style="max-width: 180px;">{{ $target->nama_elemen }}</small>
                                            </th>
                                        @endforeach
                                        <th rowspan="2" style="min-width: 260px; vertical-align: middle;">Catatan Proses Fasilitator</th>
                                    </tr>
                                    <tr>
                                        @foreach ($targetSubelemen as $target)
                                            <th class="bg-secondary text-white font-weight-normal py-1" style="font-size: 11px;">
                                                MB &bull; SB &bull; BSH &bull; SAB
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($students as $sIdx => $student)
                                        @php
                                            $studentRatings = isset($penilaian[$student->id]) ? $penilaian[$student->id]->keyBy('subelemen_id') : collect();
                                            $existingCatatan = $studentRatings->first() ? $studentRatings->first()->catatan_proses : '';
                                        @endphp
                                        <tr>
                                            <td class="text-center font-weight-bold text-secondary">{{ $sIdx + 1 }}</td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $student->nama }}</div>
                                                <small class="text-muted">NIS: {{ $student->nis }}</small>
                                            </td>
                                            @foreach ($targetSubelemen as $target)
                                                @php
                                                    $val = isset($studentRatings[$target->subelemen_id]) ? $studentRatings[$target->subelemen_id]->predikat : 'BSH';
                                                @endphp
                                                <td class="text-center bg-white">
                                                    <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                                        <label class="btn btn-xs btn-outline-secondary {{ $val == 'MB' ? 'active font-weight-bold' : '' }}">
                                                            <input type="radio" name="ratings[{{ $student->id }}][{{ $target->subelemen_id }}]" value="MB" {{ $val == 'MB' ? 'checked' : '' }} {{ $projek->is_locked ? 'disabled' : '' }}> MB
                                                        </label>
                                                        <label class="btn btn-xs btn-outline-secondary {{ $val == 'SB' ? 'active font-weight-bold' : '' }}">
                                                            <input type="radio" name="ratings[{{ $student->id }}][{{ $target->subelemen_id }}]" value="SB" {{ $val == 'SB' ? 'checked' : '' }} {{ $projek->is_locked ? 'disabled' : '' }}> SB
                                                        </label>
                                                        <label class="btn btn-xs btn-outline-success {{ $val == 'BSH' ? 'active font-weight-bold' : '' }}">
                                                            <input type="radio" name="ratings[{{ $student->id }}][{{ $target->subelemen_id }}]" value="BSH" {{ $val == 'BSH' ? 'checked' : '' }} {{ $projek->is_locked ? 'disabled' : '' }}> BSH
                                                        </label>
                                                        <label class="btn btn-xs btn-outline-primary {{ $val == 'SAB' ? 'active font-weight-bold' : '' }}">
                                                            <input type="radio" name="ratings[{{ $student->id }}][{{ $target->subelemen_id }}]" value="SAB" {{ $val == 'SAB' ? 'checked' : '' }} {{ $projek->is_locked ? 'disabled' : '' }}> SAB
                                                        </label>
                                                    </div>
                                                </td>
                                            @endforeach
                                            <td>
                                                <textarea name="catatan[{{ $student->id }}]" 
                                                          class="form-control form-control-sm" 
                                                          rows="2" 
                                                          placeholder="Catatan keaktifan & perkembangan siswa..." 
                                                          {{ $projek->is_locked ? 'readonly' : '' }}>{{ $existingCatatan }}</textarea>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($targetSubelemen) + 3 }}" class="text-center py-4 text-muted">
                                                Tidak ada data siswa pada kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                        <a href="{{ route('p5.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar Projek
                        </a>
                        <button type="button" class="btn btn-success font-weight-bold px-4" id="btnSavePenilaianBottom" {{ $projek->is_locked ? 'disabled' : '' }}>
                            <i class="fas fa-save mr-1"></i>Simpan Semua Penilaian
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
    // Quick helper: Set all radio buttons to BSH
    $('#btnSetDefaultBSH').on('click', function() {
        $('input[type="radio"][value="BSH"]').each(function() {
            $(this).prop('checked', true);
            $(this).closest('label').addClass('active font-weight-bold').siblings().removeClass('active font-weight-bold');
        });
        SwalHelper.showInfo('Semua capaian telah disetel ke BSH (Berkembang Sesuai Harapan)', 'Info');
    });

    // Update active label styles on radio click
    $('input[type="radio"]').on('change', function() {
        $(this).closest('label').addClass('active font-weight-bold').siblings().removeClass('active font-weight-bold');
    });

    // Simpan Penilaian
    function submitPenilaian() {
        Swal.fire({
            title: 'Menyimpan Penilaian P5...',
            text: 'Sedang memproses seluruh capaian siswa...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '{{ route("p5.penilaian.store", $projek->id) }}',
            method: 'POST',
            data: $('#formPenilaianP5').serialize(),
            success: function(res) {
                SwalHelper.showSuccess(res.message, 'Tersimpan!').then(() => {
                    location.reload();
                });
            },
            error: function(err) {
                let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Gagal menyimpan penilaian.';
                SwalHelper.showError(msg, 'Error');
            }
        });
    }

    $('#btnSavePenilaian, #btnSavePenilaianBottom').on('click', function() {
        submitPenilaian();
    });
});
</script>
@endsection
