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
        const chart = {
            data: {
                labels: konmt,
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: siswapk,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ],
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
