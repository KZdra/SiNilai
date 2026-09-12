@extends('layouts.portal')

@section('title', 'Capaian Projek P5')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-shapes text-warning mr-2"></i>Rapor Projek P5
            </h2>
            <p class="text-muted small mb-0">Capaian Perkembangan Profil Pelajar Pancasila (Kurikulum Merdeka).</p>
        </div>

        <div class="mt-3 mt-md-0 d-flex align-items-center flex-wrap">
            @if ($class)
                <span class="badge badge-warning text-dark py-2 px-3 shadow-sm mr-2 mb-1 mb-sm-0" title="Kelas pada semester terpilih">
                    <i class="fas fa-school mr-1"></i> {{ $class->class_name }}
                    @if (isset($currentClass) && $currentClass->id != $class->id)
                        <small class="text-muted">(Aktif: {{ $currentClass->class_name }})</small>
                    @endif
                </span>
            @endif
            <form method="GET" action="{{ route('portal.p5') }}" id="filterP5Form" class="mr-2 mb-1 mb-sm-0">
                <select name="fst_id" class="form-control font-weight-bold shadow-sm" onchange="document.getElementById('filterP5Form').submit()">
                    @include('partials.select_fst_options', ['fstList' => $fstList, 'selectedId' => $activeFst ? $activeFst->id : null])
                </select>
            </form>

            @if ($activeFst)
                <button type="button" class="btn btn-danger shadow-sm font-weight-bold btnDownloadP5"
                        data-student-id="{{ $student->id }}"
                        data-class-id="{{ $class ? $class->id : $student->class_id }}"
                        data-fst-id="{{ $activeFst->id }}">
                    <i class="fas fa-file-pdf mr-1"></i>Unduh Rapor P5 (PDF)
                </button>
            @endif
        </div>
    </div>

    <!-- Rubrik Keterangan -->
    <div class="card portal-card bg-white shadow-sm mb-4 border-0">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between">
            <span class="small font-weight-bold text-secondary mb-1 mb-md-0">
                <i class="fas fa-info-circle text-primary mr-1"></i>Skala Capaian P5:
            </span>
            <div>
                <span class="badge badge-secondary px-3 py-1 mr-1">MB = Mulai Berkembang</span>
                <span class="badge badge-info px-3 py-1 mr-1">SB = Sedang Berkembang</span>
                <span class="badge badge-success px-3 py-1 mr-1">BSH = Berkembang Sesuai Harapan</span>
                <span class="badge badge-primary px-3 py-1">SAB = Sangat Berkembang</span>
            </div>
        </div>
    </div>

    <!-- List Projek P5 -->
    @forelse ($projeks as $pIdx => $pItem)
        <div class="card portal-card bg-white shadow-sm mb-4 border-0">
            <div class="card-header bg-light py-3 border-0">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <span class="badge badge-info px-2 py-1 mb-1 font-weight-normal">{{ $pItem['projek']->tema }}</span>
                        <h4 class="font-weight-bold text-dark mb-0">
                            Projek {{ $pIdx + 1 }}: {{ $pItem['projek']->nama_projek }}
                        </h4>
                    </div>
                </div>
                @if ($pItem['projek']->deskripsi)
                    <p class="text-muted small mt-2 mb-0" style="line-height: 1.5;">
                        {{ $pItem['projek']->deskripsi }}
                    </p>
                @endif
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th style="width: 250px;">Dimensi & Elemen</th>
                                <th>Subelemen / Capaian Akhir Fase</th>
                                <th class="text-center" style="width: 180px;">Capaian Peserta Didik</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pItem['targets'] as $target)
                                @php
                                    $predikat = isset($pItem['ratings'][$target->sub_id]) ? $pItem['ratings'][$target->sub_id]->predikat : 'BSH';
                                    $badgeStyle = 'badge-success';
                                    if ($predikat === 'MB') $badgeStyle = 'badge-secondary';
                                    elseif ($predikat === 'SB') $badgeStyle = 'badge-info';
                                    elseif ($predikat === 'SAB') $badgeStyle = 'badge-primary';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $target->nama_dimensi }}</div>
                                        <small class="text-muted">&bull; {{ $target->nama_elemen }}</small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-secondary">{{ $target->nama_subelemen }}</div>
                                        <small class="text-muted">{{ $target->capaian_fase }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeStyle }} px-3 py-2 font-weight-bold" style="font-size: 0.9rem;">
                                            {{ $predikat }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Belum ada target subelemen yang ditetapkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Catatan Proses -->
            <div class="card-footer bg-white border-top p-4">
                <h6 class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-comment-alt text-primary mr-1"></i>Catatan Fasilitator Projek:
                </h6>
                <p class="text-secondary mb-0 bg-light p-3 rounded border" style="font-style: italic;">
                    "{{ $pItem['catatan'] ?: 'Peserta didik berpartisipasi aktif dalam kegiatan projek dan menunjukkan perkembangan Profil Pelajar Pancasila yang baik.' }}"
                </p>
            </div>
        </div>
    @empty
        <div class="card portal-card bg-white shadow-sm p-5 text-center text-muted border-0">
            <i class="fas fa-shapes fa-4x mb-3 opacity-50"></i>
            <h5 class="font-weight-bold">Belum Ada Projek P5 di Semester Ini</h5>
            <p class="mb-0">Data modul dan capaian projek P5 belum diinput oleh pihak sekolah.</p>
        </div>
    @endforelse

</div>
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    $('.btnDownloadP5').on('click', function() {
        let stdId = $(this).data('student-id');
        let clsId = $(this).data('class-id');
        let fstId = $(this).data('fst-id');

        Swal.fire({
            title: 'Menyiapkan Rapor P5...',
            text: 'Menghasilkan dokumen resmi PDF...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '{{ route("p5.cetak") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                student_id: stdId,
                class_id: clsId,
                fst_id: fstId
            },
            success: function(res) {
                Swal.close();
                if (res.pdf_url) {
                    window.open(res.pdf_url, '_blank');
                } else {
                    SwalHelper.showError('URL file tidak ditemukan.', 'Gagal');
                }
            },
            error: function() {
                SwalHelper.showError('Tidak dapat mengunduh rapor P5.', 'Gagal');
            }
        });
    });
});
</script>
@endsection
