@extends('layouts.portal')

@section('title', 'Transkrip Nilai Akademik')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-chart-line text-primary mr-2"></i>Transkrip Nilai Akademik
            </h2>
            <p class="text-muted small mb-0">Rincian capaian pembelajaran sumatif dan ekstrakurikuler peserta didik.</p>
        </div>

        <div class="mt-3 mt-md-0 d-flex align-items-center flex-wrap">
            @if ($class)
                <span class="badge badge-primary py-2 px-3 shadow-sm mr-2 mb-1 mb-sm-0" title="Kelas pada semester terpilih">
                    <i class="fas fa-school mr-1"></i> {{ $class->class_name }}
                    @if (isset($currentClass) && $currentClass->id != $class->id)
                        <small class="text-white-50">(Aktif: {{ $currentClass->class_name }})</small>
                    @endif
                </span>
            @endif
            <form method="GET" action="{{ route('portal.nilai') }}" id="filterSemesterForm" class="mr-2 mb-1 mb-sm-0">
                <select name="fst_id" class="form-control font-weight-bold shadow-sm" onchange="document.getElementById('filterSemesterForm').submit()">
                    @include('partials.select_fst_options', ['fstList' => $fstList, 'selectedId' => $activeFst ? $activeFst->id : null])
                </select>
            </form>

            @if ($activeFst)
                <a href="{{ route('portal.raport.download', ['fst_id' => $activeFst->id]) }}" class="btn btn-primary shadow-sm font-weight-bold btnDownloadRaport">
                    <i class="fas fa-file-download mr-1"></i>Unduh E-Raport (PDF)
                </a>
            @endif
        </div>
    </div>

    <!-- Tabel Nilai Sumatif Mata Pelajaran -->
    <div class="card portal-card bg-white shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title font-weight-bold text-dark mb-0">
                <i class="fas fa-award text-warning mr-2"></i>Daftar Capaian Sumatif Mata Pelajaran
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Mata Pelajaran</th>
                            <th class="text-center">Nilai Harian (Rata-Rata)</th>
                            <th class="text-center">STS (Tengah Semester)</th>
                            <th class="text-center">SAS (Akhir Semester)</th>
                            <th class="text-center" style="width: 140px;">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scores as $idx => $sc)
                            @php
                                $dailyArr = array_filter([
                                    $sc->value_daily, $sc->value_daily_2, $sc->value_daily_3, $sc->value_daily_4, $sc->value_daily_5
                                ], function($v) { return !is_null($v); });
                                $avgDaily = count($dailyArr) > 0 ? round(array_sum($dailyArr) / count($dailyArr), 1) : '-';

                                $dailyVal = is_numeric($avgDaily) ? $avgDaily : 0;
                                $stsVal = $sc->value_sts ?: 0;
                                $sasVal = $sc->value_sas ?: 0;

                                $parts = array_filter([is_numeric($avgDaily) ? $avgDaily : null, $sc->value_sts, $sc->value_sas], function($v) { return !is_null($v); });
                                $finalVal = count($parts) > 0 ? round(array_sum($parts) / count($parts), 1) : '-';
                            @endphp
                            <tr>
                                <td class="text-center font-weight-bold text-secondary">{{ $idx + 1 }}</td>
                                <td class="font-weight-bold text-dark">{{ $sc->nama_mapel }}</td>
                                <td class="text-center">{{ $avgDaily }}</td>
                                <td class="text-center">{{ $sc->value_sts ?: '-' }}</td>
                                <td class="text-center">{{ $sc->value_sas ?: '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-primary px-3 py-2 font-weight-bold" style="font-size: 0.95rem;">
                                        {{ $finalVal }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-book-reader fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0">Belum ada nilai yang tercatat pada semester ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabel Ekstrakurikuler -->
    <div class="card portal-card bg-white shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title font-weight-bold text-dark mb-0">
                <i class="fas fa-running text-success mr-2"></i>Kegiatan Ekstrakurikuler
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Nama Ekstrakurikuler</th>
                            <th class="text-center" style="width: 200px;">Predikat / Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($eskulScores as $eIdx => $esk)
                            <tr>
                                <td class="text-center font-weight-bold text-secondary">{{ $eIdx + 1 }}</td>
                                <td class="font-weight-bold text-dark">{{ $esk->nama_eskul }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success px-3 py-2 font-weight-bold">{{ $esk->nilai_eskul ?: 'Sangat Baik' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    Tidak ada data kegiatan ekstrakurikuler pada semester ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script type="module">
    $('.btnDownloadRaport').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Mengunduh E-Raport...',
            text: 'Berkas PDF sedang disiapkan dan diunduh ke perangkat Anda.',
            timer: 3500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    });
</script>
@endsection

