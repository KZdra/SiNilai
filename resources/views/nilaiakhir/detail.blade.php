@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Nilai Akhir') }}</h1>
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
                        <div class="card-header d-flex align-items-center flex-wrap">
                            <div class="mr-3 mb-3 mb-md-0">
                                <img src="{{ asset('images/user.jpg') }}" alt="" class="img-fluid"
                                    style="max-width: 100px; height: auto;">
                            </div>
                            <div>
                                <h5 class="mb-1">Nama Siswa: <span
                                        class="font-weight-bold">{{ $formattedStudents[0]['student_name'] }}</span> </h5>
                                <h5 class="mb-1">Kelas: <span
                                        class="font-weight-bold">{{ $formattedStudents[0]['class_name'] }}</span> </h5>
                                <h5 class="mb-1">Rata Rata Nilai Semua Pelajaran: <span
                                        class="font-weight-bold">{{ $formattedStudents[0]['avg_nilai_semua_mapel'] }}</span>
                                </h5>
                            </div>
                        </div>

                        <div class="card-body p-2">
                            {{-- <table class="table table-striped table-bordered table-auto" id="nilaiTable">
                                <thead>
                                    <tr>
                                        @foreach ($formattedStudents[0]['nilai_per_mapel'] as $subject => $s)
                                            <th>{{ $subject }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach ($formattedStudents[0]['nilai_per_mapel'] as $subject => $s)
                                            <td>{{ round($s) }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table> --}}
                            <table class="table table-striped table-bordered table-auto" id="nilaiTable">
                                <thead>
                                    <tr>
                                        <th>Mata Pelajaran</th>
                                        <th>Nilai Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($formattedStudents[0]['nilai_per_mapel'] as $subject => $s)
                                        <tr>
                                            <td>{{ $subject }}</td>
                                            <td>{{ round($s) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
        $(document).ready(function() {
            // let table = $('#nilaiTable').DataTable({
            //     "responsive": true,
            //     searching: false,
            //     paging: false,
            //     info: false,
            //     ordering: false,
            //     "autoWidth": false
            // });
            let table = $('#nilaiTable').DataTable({
                "responsive": true,
                searching: false,
                paging: false,
                info: false,
                ordering: false,
                columnDefs: [{
                    targets: '_all',
                    className: 'dt-left'
                }]
            });
        })
    </script>
@endsection
