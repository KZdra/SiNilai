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
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-text text-center">
                                Statistik Murid/Kelas
                            </h5>
                            <div class="d-flex justify-content-center">
                                <canvas id="myChart"></canvas>
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

                // Acak nilai yang bukan 255 untuk memberikan variasi
                if (r !== 255) r = Math.floor(Math.random() * 150) + 50;
                if (g !== 255) g = Math.floor(Math.random() * 150) + 50;
                if (b !== 255) b = Math.floor(Math.random() * 150) + 50;

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
