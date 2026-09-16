@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Dashboard') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            @if (Auth::user()->role_id != 1 && Auth::user()->class_id === null)
                <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
                    <div class="col-md-8 text-center">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-5">
                                <div class="mb-4 text-success">
                                    <i class="fas fa-chalkboard-teacher fa-4x"></i>
                                </div>
                                <h3 class="font-weight-bold text-dark mb-2">Selamat Datang, {{ Auth::user()->name }}!</h3>
                                <p class="text-muted leading-relaxed mb-4" style="font-size: 16px;">
                                    Akun Anda terdaftar sebagai <strong>Guru Mata Pelajaran</strong>.<br>
                                    Anda dapat langsung mengunduh format Excel siswa dan mengunggah nilai mata pelajaran Anda melalui tombol di bawah:
                                </p>
                                <a href="{{ route('nilai_import.index') }}" class="btn btn-success btn-lg font-weight-bold px-4 shadow-sm">
                                    <i class="fas fa-file-excel mr-2"></i> Buka Menu Upload Nilai (Excel)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex flex-row justify-content-between w-100 flex-wrap">
                                    @if (Auth::user()->role_id == 1)
                                        <div class="small-box bg-info mx-1 flex-fill">
                                            <div class="inner">
                                                <h3>{{ $classNames->count() }}</h3>
                                                <p>Jumlah Kelas</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-school"></i>
                                            </div>
                                            <a href="{{ route('class.index') }}" class="small-box-footer">
                                                Lihat Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                                            </a>
                                        </div>

                                        <div class="small-box bg-gradient-success mx-1 flex-fill">
                                            <div class="inner">
                                                <h3>{{ $allstudentCounts }}</h3>
                                                <p>Jumlah Siswa</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                            <a href="{{ route('student.index') }}" class="small-box-footer">
                                                Lihat Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                                            </a>
                                        </div>

                                        <div class="small-box bg-gradient-primary mx-1 flex-fill">
                                            <div class="inner">
                                                <h3>{{ $allMapelCounts }}</h3>
                                                <p>Jumlah Mata Pelajaran</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <a href="{{ route('mapel.index') }}" class="small-box-footer">
                                                Lihat Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                                            </a>
                                        </div>
                                    @else
                                        {{-- Khusus Tampilan Wali Kelas --}}
                                        <div class="small-box bg-info mx-1 flex-fill">
                                            <div class="inner">
                                                <h3 style="font-size: 1.8rem;">{{ $walasClassName ?? 'Kelas' }}</h3>
                                                <p>Kelas Binaan Anda</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-chalkboard"></i>
                                            </div>
                                            <a href="{{ route('student.index') }}" class="small-box-footer">
                                                Data Siswa <i class="fas fa-arrow-circle-right"></i>
                                            </a>
                                        </div>

                                        <div class="small-box bg-gradient-success mx-1 flex-fill">
                                            <div class="inner">
                                                <h3>{{ $allstudentCounts }}</h3>
                                                <p>Jumlah Siswa Kelas</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                            <a href="{{ route('student.index') }}" class="small-box-footer">
                                                Lihat Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                                            </a>
                                        </div>

                                        <div class="small-box bg-gradient-primary mx-1 flex-fill">
                                            <div class="inner">
                                                <h3>Rapor</h3>
                                                <p>Nilai Akhir & Leger</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-file-invoice"></i>
                                            </div>
                                            <a href="{{ route('nilaiakhir.index') }}" class="small-box-footer">
                                                Buka Rapor <i class="fas fa-arrow-circle-right"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @if (Auth::user()->role_id == 1)
                                <!-- Master Data Setup Readiness Column (KHUSUS ADMIN) -->
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title font-weight-bold mb-0 text-dark">
                                                    <i class="fas fa-clipboard-check text-primary mr-2"></i> Kesiapan Master Data
                                                </h5>
                                                <small class="text-muted d-block mt-1">Checklist data awal sebelum pengisian nilai & cetak rapor</small>
                                            </div>
                                            <div>
                                                @if($readinessPercent >= 100)
                                                    <span class="badge badge-success px-2 py-1 font-weight-bold shadow-sm">
                                                        <i class="fas fa-check-circle mr-1"></i> 100% Lengkap
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning px-2 py-1 font-weight-bold shadow-sm text-dark">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i> {{ $unfilledCount }} Perlu Diisi
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            <!-- Progress Bar -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                                    <span class="text-secondary">Kelengkapan Setup:</span>
                                                    <span class="{{ $readinessPercent >= 100 ? 'text-success' : 'text-primary' }}">
                                                        {{ $filledCount }} dari {{ $totalMasterItems }} Siap ({{ $readinessPercent }}%)
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 10px; border-radius: 5px;">
                                                    <div class="progress-bar progress-bar-striped {{ $readinessPercent >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                                         role="progressbar" style="width: {{ $readinessPercent }}%;" 
                                                         aria-valuenow="{{ $readinessPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>

                                            <!-- List Checklist Master Data -->
                                            <div class="list-group list-group-flush" style="max-height: 330px; overflow-y: auto;">
                                                @foreach($masterChecklist as $item)
                                                    <div class="list-group-item px-2 py-2 d-flex justify-content-between align-items-center border-bottom">
                                                        <div class="d-flex align-items-center" style="gap: 12px; min-width: 0;">
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                                                 style="width: 34px; height: 34px; background-color: {{ $item['is_filled'] ? '#e8f5e9' : '#fff3e0' }};">
                                                                <i class="{{ $item['icon'] }}" style="font-size: 14px;"></i>
                                                            </div>
                                                            <div class="text-truncate">
                                                                <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.9rem;">
                                                                    {{ $item['title'] }}
                                                                </div>
                                                                <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $item['desc'] }}</small>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex align-items-center flex-shrink-0 ml-2" style="gap: 6px;">
                                                            @if($item['is_filled'])
                                                                <span class="badge badge-light border border-success text-success px-2 py-1 font-weight-bold" style="font-size: 0.78rem;">
                                                                    <i class="fas fa-check mr-1"></i>{{ $item['count_label'] }}
                                                                </span>
                                                                <a href="{{ $item['route'] }}" class="btn btn-xs btn-outline-secondary" title="Kelola data ini">
                                                                    <i class="fas fa-cog"></i>
                                                                </a>
                                                            @else
                                                                <span class="badge badge-light border border-danger text-danger px-2 py-1 font-weight-bold" style="font-size: 0.78rem;">
                                                                    <i class="fas fa-times mr-1"></i>Belum Diisi
                                                                </span>
                                                                <a href="{{ $item['route'] }}" class="btn btn-xs btn-primary font-weight-bold px-2 py-1 shadow-sm">
                                                                    Isi <i class="fas fa-arrow-right ml-1"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Menu Tugas Wali Kelas (KHUSUS WALI KELAS) -->
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title font-weight-bold mb-0 text-dark">
                                                    <i class="fas fa-chalkboard-teacher text-primary mr-2"></i> Menu Tugas Wali Kelas
                                                </h5>
                                                <small class="text-muted d-block mt-1">Akses cepat administrasi rapor kelas {{ $walasClassName ?? '' }}</small>
                                            </div>
                                            <span class="badge badge-primary px-3 py-2 font-weight-bold shadow-sm">
                                                <i class="fas fa-user-tie mr-1"></i> Wali Kelas
                                            </span>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="list-group list-group-flush">
                                                <a href="{{ route('walas.index') }}" class="list-group-item list-group-item-action px-2 py-3 d-flex justify-content-between align-items-center border-bottom">
                                                    <div class="d-flex align-items-center" style="gap: 14px;">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-primary flex-shrink-0" style="width: 42px; height: 42px;">
                                                            <i class="fas fa-clipboard-list fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 font-weight-bold text-dark">Presensi & Catatan Wali Kelas</h6>
                                                            <small class="text-muted">Input rekap absensi sakit, izin, alpa, catatan perkembangan, & keputusan kenaikan</small>
                                                        </div>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-muted ml-2"></i>
                                                </a>
                                                <a href="{{ route('nilaiakhir.index') }}" class="list-group-item list-group-item-action px-2 py-3 d-flex justify-content-between align-items-center border-bottom">
                                                    <div class="d-flex align-items-center" style="gap: 14px;">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-success flex-shrink-0" style="width: 42px; height: 42px;">
                                                            <i class="fas fa-file-invoice fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 font-weight-bold text-dark">Nilai Akhir & Rapor Siswa</h6>
                                                            <small class="text-muted">Pantau rekap capaian semester, kunci alur status, cetak rapor, & unduh ZIP kelas</small>
                                                        </div>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-muted ml-2"></i>
                                                </a>
                                                <a href="{{ route('student.index') }}" class="list-group-item list-group-item-action px-2 py-3 d-flex justify-content-between align-items-center border-bottom">
                                                    <div class="d-flex align-items-center" style="gap: 14px;">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-info flex-shrink-0" style="width: 42px; height: 42px;">
                                                            <i class="fas fa-user-graduate fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 font-weight-bold text-dark">Daftar Siswa Kelas</h6>
                                                            <small class="text-muted">Lihat data biodata, NIS/NISN, dan identitas siswa pada kelas binaan Anda</small>
                                                        </div>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-muted ml-2"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Top 5 Students Column -->
                            <div class="col-md-6 mb-3">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-header bg-white py-3 border-bottom">
                                        <h5 class="card-title font-weight-bold mb-0 text-dark">
                                            <i class="fas fa-trophy text-warning mr-2"></i> Top 5 Siswa (Rata-Rata Tertinggi)
                                        </h5>
                                        <small class="text-muted d-block mt-1">Peringkat 5 siswa dengan rata-rata nilai tertinggi</small>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped text-center">
                                                <thead class="bg-primary text-white">
                                                    <tr>
                                                        <th>Peringkat</th>
                                                        <th>Nama Siswa</th>
                                                        <th>Kelas</th>
                                                        <th>Rata-Rata</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($topStudents as $index => $student)
                                                        <tr>
                                                            <td>
                                                                @if($index == 0) <i class="fas fa-medal text-warning"></i> 1
                                                                @elseif($index == 1) <i class="fas fa-medal text-secondary"></i> 2
                                                                @elseif($index == 2) <i class="fas fa-medal" style="color: #cd7f32;"></i> 3
                                                                @else {{ $index + 1 }}
                                                                @endif
                                                            </td>
                                                            <td class="text-left">{{ $student->student_name }}</td>
                                                            <td>{{ $student->class_name }}</td>
                                                            <td class="font-weight-bold text-success">{{ $student->average_score }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted">Belum ada data nilai.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection
@section('scripts')
    <script type="module">
        @if (session('success'))
            Swal.fire({
                icon: "success",
                title: "Selamat Datang, {{ Auth::user()->name }}!",
                showConfirmButton: false,
                timer: 1500
            });
        @endif
        $(document).ready(function() {
            $('#myTable').DataTable();
        });
    </script>
@endsection
