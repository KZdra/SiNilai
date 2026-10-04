@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('About us') }}</h1>
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
                            <h5 class="card-title font-weight-bold text-primary">SiNilai &bull; Sistem Informasi Penilaian & Raport</h5>

                            <p class="card-text mt-3">
                                SiNilai dikembangkan oleh <a href="https://kzdra.github.io" target="_blank" rel="noopener noreferrer"><strong>InDev.</strong></a> sebagai solusi sistem penilaian dan pelaporan akademik modern berstandar Kurikulum Merdeka.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection