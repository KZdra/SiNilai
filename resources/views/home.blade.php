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
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-row justify-content-between w-100">
                                @if (Auth::user()->role_id == 1)
                                    <div class="small-box bg-info mx-2 flex-fill">
                                        <div class="inner">
                                            <h3>{{ $classNames->count() }}</h3>
                                            <p>Jumlah Kelas</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-school"></i>
                                        </div>
                                        <a href="#" class="small-box-footer">
                                            Lihat Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                @endif

                                <div class="small-box bg-gradient-success mx-2 flex-fill">
                                    <div class="inner">
                                        <h3>{{ $allstudentCounts }}</h3>
                                        <p>Jumlah Siswa</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <a href="#" class="small-box-footer">
                                        Lihat Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                </div>
                                @if (Auth::user()->role_id == 1)
                                    <div class="small-box bg-gradient-primary mx-2 flex-fill">
                                        <div class="inner">
                                            <h3>{{ $allMapelCounts }}</h3>
                                            <p>Jumlah Mata Pelajaran</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <a href="#" class="small-box-footer">
                                            Lihat Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Chart Column -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-text text-center font-weight-bold mb-4">
                                        Statistik Murid/Kelas
                                    </h5>
                                    <div class="d-flex justify-content-center" style="position: relative; height:300px; width:100%">
                                        <canvas id="myChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top 5 Students Column -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-text text-center font-weight-bold mb-4">
                                        Top 5 Siswa (Rata-Rata Tertinggi)
                                    </h5>
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
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection
@section('scripts')
    <script type="module">
        // Chart Example Helper
        var konmt = @json($classNames);
        var siswapk = @json($studentCounts);

        function getRandomRGBColors(count) {
            const colors = new Set();
            const baseColors = ["red", "green", "blue", "yellow", "cyan", "magenta", "orange"];

            while (colors.size < count) {
                const base = baseColors[Math.floor(Math.random() * baseColors.length)];

                let r = 50,
                    g = 50,
                    b = 50; // Set warna dasar gelap dulu

                if (base === "red") r = 255;
                if (base === "green") g = 255;
                if (base === "blue") b = 255;
                if (base === "yellow") {
                    r = 255;
                    g = 255;
                }
                if (base === "cyan") {
                    g = 255;
                    b = 255;
                }
                if (base === "magenta") {
                    r = 255;
                    b = 255;
                }
                if (base === "orange") {
                    r = 255;
                    g = Math.floor(Math.random() * 150) + 50;
                }

                const rgb = `rgb(${r}, ${g}, ${b})`;
                colors.add(rgb); // Set hanya menyimpan nilai unik
            }

            return Array.from(colors);
        }

        const chart = {
            data: {
                labels: konmt,
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: siswapk,
                    backgroundColor: getRandomRGBColors(siswapk.length),
                    hoverOffset: 4
                }]
            },
            config: {
                chartId: 'myChart',
                type: 'doughnut',
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Agar chart menyesuaikan dengan ukuran kontainer
                }
            }
        };


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
            MakeChart(chart);

        });
    </script>
@endsection
