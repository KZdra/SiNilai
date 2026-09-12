@extends('layouts.portal')

@section('title', 'Beranda Siswa')

@section('content')
<div class="container">

    <!-- Profile Header Card -->
    <div class="card portal-card shadow-sm mb-4 overflow-hidden border-0">
        <div class="card-body p-4 bg-white">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if ($student->foto_siswa_path)
                        <img src="{{ asset('storage/' . $student->foto_siswa_path) }}" alt="Foto Siswa" class="rounded-circle border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary shadow-sm" style="width: 80px; height: 80px; font-size: 2.2rem; font-weight: bold;">
                            {{ strtoupper(substr($student->nama, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="col mt-3 mt-md-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h3 class="font-weight-bold text-dark mb-1">{{ $student->nama }}</h3>
                            <p class="text-muted mb-0">
                                <span><i class="fas fa-id-card text-secondary mr-1"></i>NIS: <strong>{{ $student->nis }}</strong></span>
                                <span class="mx-2">&bull;</span>
                                <span>NISN: <strong>{{ $student->nisn ?: '-' }}</strong></span>
                                <span class="mx-2">&bull;</span>
                                <span>Kelas: <strong class="badge badge-primary">{{ $class ? $class->class_name : '-' }}</strong>
                                @if (isset($currentClass) && $class && $currentClass->id != $class->id)
                                    <small class="text-muted ml-1" title="Kelas aktif saat ini">(Kelas Sekarang: {{ $currentClass->class_name }})</small>
                                @endif
                                </span>
                            </p>
                        </div>

                        <!-- Filter Semester & Quick Actions -->
                        <div class="mt-3 mt-md-0 d-flex align-items-center flex-wrap">
                            <form method="GET" action="{{ route('portal.dashboard') }}" id="semesterForm" class="mr-2 mb-1 mb-sm-0">
                                <select name="fst_id" class="form-control form-control-sm font-weight-bold shadow-sm" onchange="document.getElementById('semesterForm').submit()">
                                    @include('partials.select_fst_options', ['fstList' => $fstList, 'selectedId' => $activeFst ? $activeFst->id : null])
                                </select>
                            </form>
                            <a href="{{ route('portal.password') }}" class="btn btn-sm btn-outline-secondary font-weight-bold shadow-sm" title="Ubah kata sandi akun portal Anda">
                                <i class="fas fa-key text-warning mr-1"></i> Ganti Password
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card portal-card bg-white h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 mr-3 text-primary" style="background-color: #e8f0fe;">
                        <i class="fas fa-star fa-2x"></i>
                    </div>
                    <div>
                        <small class="text-muted font-weight-bold text-uppercase">Rata-Rata Nilai</small>
                        <h3 class="font-weight-bold text-dark mb-0">{{ $avgScore ?: '-' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card portal-card bg-white h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 mr-3 text-success" style="background-color: #e6f4ea;">
                        <i class="fas fa-user-check fa-2x"></i>
                    </div>
                    <div>
                        <small class="text-muted font-weight-bold text-uppercase">Sakit (S)</small>
                        <h3 class="font-weight-bold text-dark mb-0">{{ $catatanWalas ? $catatanWalas->sakit : ($student->sakit ?? 0) }} Hari</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card portal-card bg-white h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 mr-3 text-info" style="background-color: #e8f0fe;">
                        <i class="fas fa-envelope-open-text fa-2x"></i>
                    </div>
                    <div>
                        <small class="text-muted font-weight-bold text-uppercase">Izin (I)</small>
                        <h3 class="font-weight-bold text-dark mb-0">{{ $catatanWalas ? $catatanWalas->izin : ($student->izin ?? 0) }} Hari</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card portal-card bg-white h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 mr-3 text-danger" style="background-color: #fce8e6;">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                    <div>
                        <small class="text-muted font-weight-bold text-uppercase">Tanpa Keterangan</small>
                        <h3 class="font-weight-bold text-dark mb-0">{{ $catatanWalas ? $catatanWalas->alpa : ($student->alpa ?? 0) }} Hari</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Catatan Walas & Download Center -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card portal-card bg-white h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-comment-dots text-primary mr-2"></i>Catatan Perkembangan dari Wali Kelas
                    </h5>
                    @if ($walas)
                        <small class="text-muted">Wali Kelas: <strong>{{ $walas->name }}</strong></small>
                    @endif
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="p-3 rounded bg-light border">
                        <p class="mb-0 text-secondary" style="font-style: italic; line-height: 1.6;">
                            "{{ $catatanWalas && $catatanWalas->catatan ? $catatanWalas->catatan : 'Pertahankan semangat belajarmu dan terus tingkatkan kedisiplinan serta keaktifan di dalam kelas maupun kegiatan projek sekolah!' }}"
                        </p>
                    </div>

                    @if ($catatanWalas && $catatanWalas->status_kenaikan)
                        <div class="mt-3">
                            <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 0.95rem;">
                                <i class="fas fa-check-circle mr-1"></i>Status Keputusan: {{ $catatanWalas->status_kenaikan }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card portal-card bg-white h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-download text-success mr-2"></i>Unduh Dokumen Raport
                    </h5>
                </div>
                <div class="card-body px-4 pb-4 d-flex flex-column justify-content-around">
                    <p class="text-muted small mb-3">
                        Orang tua dan peserta didik dapat mengunduh salinan resmi E-Raport Akademik dan Rapor Projek P5 langsung ke perangkat HP atau laptop.
                    </p>

                    @if ($activeFst)
                        <a href="{{ route('nilaiakhir.print', ['student_id' => $student->id, 'class_id' => ($class ? $class->id : $student->class_id), 'fst_id' => $activeFst->id]) }}" target="_blank" class="btn btn-outline-primary btn-block mb-2 font-weight-bold py-2 btnDownloadRaport"
                           data-student-id="{{ $student->id }}"
                           data-class-id="{{ $class ? $class->id : $student->class_id }}"
                           data-fst-id="{{ $activeFst->id }}">
                            <i class="fas fa-file-pdf mr-2 text-danger"></i>Unduh E-Raport Akademik (PDF)
                        </a>

                        <button type="button" class="btn btn-outline-info btn-block font-weight-bold py-2 btnDownloadP5"
                                data-student-id="{{ $student->id }}"
                                data-class-id="{{ $class ? $class->id : $student->class_id }}"
                                data-fst-id="{{ $activeFst->id }}">
                            <i class="fas fa-shapes mr-2 text-warning"></i>Unduh Rapor Projek P5 (PDF)
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Nilai Mata Pelajaran -->
    <div class="card portal-card bg-white shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold text-dark mb-0">
                <i class="fas fa-book-open text-primary mr-2"></i>Ringkasan Nilai Mata Pelajaran
            </h5>
            <a href="{{ route('portal.nilai', ['fst_id' => $activeFst ? $activeFst->id : null]) }}" class="small font-weight-bold text-primary">
                Lihat Transkrip Detail <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="card-body px-4 pb-4">
            @if(count($mapelScores) == 0)
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-2 opacity-50"></i>
                    <p class="mb-0">Belum ada nilai yang diinput untuk semester ini.</p>
                </div>
            @else
                <div class="row">
                    @foreach ($mapelScores as $sc)
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold text-dark">{{ $sc['nama_mapel'] }}</span>
                                    <span class="badge badge-pill badge-primary px-3 py-1 font-weight-bold" style="font-size: 0.9rem;">
                                        {{ $sc['final'] }}
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    @php
                                        $progClass = 'bg-success';
                                        if ($sc['final'] < 70) $progClass = 'bg-danger';
                                        elseif ($sc['final'] < 80) $progClass = 'bg-warning';
                                    @endphp
                                    <div class="progress-bar {{ $progClass }}" role="progressbar" style="width: {{ min($sc['final'], 100) }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2 small text-muted">
                                    <span>Harian: <strong>{{ $sc['daily'] ?: '-' }}</strong></span>
                                    <span>STS: <strong>{{ $sc['sts'] ?: '-' }}</strong></span>
                                    <span>SAS: <strong>{{ $sc['sas'] ?: '-' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script type="module">
    $('.btnDownloadRaport').on('click', function(e) {
        e.preventDefault();
        let stdId = $(this).data('student-id');
        let clsId = $(this).data('class-id');
        let fstId = $(this).data('fst-id');
        let fallbackUrl = $(this).attr('href');

        Swal.fire({
            title: 'Menyiapkan E-Raport...',
            text: 'Menghasilkan dokumen resmi PDF...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '{{ route("nilaiakhir.print") }}',
            method: 'GET',
            data: {
                student_id: stdId,
                class_id: clsId,
                fst_id: fstId
            },
            success: function(res) {
                Swal.close();
                if (res.pdf_url) {
                    window.open(res.pdf_url, '_blank');
                } else if (fallbackUrl) {
                    window.open(fallbackUrl, '_blank');
                } else {
                    SwalHelper.showError('URL file tidak ditemukan.', 'Gagal');
                }
            },
            error: function() {
                Swal.close();
                if (fallbackUrl) {
                    window.open(fallbackUrl, '_blank');
                } else {
                    SwalHelper.showError('Tidak dapat mengunduh E-Raport.', 'Gagal');
                }
            }
        });
    });

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
