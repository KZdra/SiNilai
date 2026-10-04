<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="shortcut icon" href="{{asset('images/icb.png')}}" type="image/x-icon">
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('styles')
    <style>
        html, body {
            min-height: 100% !important;
        }
        .wrapper {
            min-height: 100vh !important;
            display: flex;
            flex-direction: column;
        }
        .content-wrapper {
            flex: 1 0 auto;
            min-height: calc(100vh - 57px - 57px) !important;
        }
        .main-footer {
            flex-shrink: 0;
            background-color: #ffffff;
            border-top: 1px solid #dee2e6;
        }
        .main-sidebar {
            background-color: #ffffff !important;
            border-right: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .main-sidebar .brand-link {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e2e8f0 !important;
            color: #0f172a !important;
            padding: 0.8125rem 1rem !important;
        }
        .main-sidebar .brand-link .brand-text {
            color: #0f172a !important;
            font-size: 1.15rem;
            letter-spacing: -0.02em;
        }
        .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25) !important;
            border-radius: 6px;
        }
        .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link.active .nav-icon,
        .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link.active p {
            color: #ffffff !important;
        }
        .nav-sidebar .nav-link {
            color: #334155 !important;
            font-weight: 500;
            border-radius: 6px;
            margin-bottom: 3px;
            padding: 0.55rem 0.85rem !important;
            transition: all 0.15s ease-in-out;
        }
        .nav-sidebar .nav-link:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
        .nav-sidebar .nav-header {
            color: #94a3b8 !important;
            font-size: 0.72rem !important;
            letter-spacing: 0.06em;
            padding: 0.85rem 0.85rem 0.35rem 0.85rem !important;
        }
        .nav-treeview > .nav-item > .nav-link {
            padding-left: 1.75rem !important;
            font-size: 0.9rem;
        }
        /* Fix AdminLTE clearfix pseudo-element interfering with flexbox card-header */
        .card-header.d-flex::after,
        .card-header.d-flex::before {
            display: none !important;
        }
        .card-header.d-flex > .card-tools {
            margin-right: 0 !important;
            margin-left: auto !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link font-weight-bold" data-toggle="dropdown" href="#" aria-expanded="false">
                        <i class="fas fa-user-circle mr-1 text-primary"></i> {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" style="left: inherit; right: 0px; min-width: 220px;">
                        <div class="px-3 py-2 border-bottom bg-light">
                            <span class="d-block font-weight-bold text-dark text-truncate">{{ Auth::user()->name }}</span>
                            <span class="d-block small text-muted text-truncate"><i class="fas fa-at text-muted mr-1"></i>{{ Auth::user()->username }}</span>
                        </div>
                        <a href="{{ route('profile.show') }}" class="dropdown-item py-2 font-weight-bold text-dark">
                            <i class="mr-2 fas fa-user-cog text-primary"></i>
                            {{ __('Pengaturan Akun') }}
                        </a>
                        <div class="dropdown-divider my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" class="dropdown-item py-2 text-danger font-weight-bold"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="mr-2 fas fa-sign-out-alt"></i>
                                {{ __('Log Out') }}
                            </a>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-light-primary elevation-1">
            <a href="/" class="brand-link">

                <span class="brand-text font-weight-bold">{{ config('app.name', 'Laravel') }}</span>
            </a>

            @include('layouts.navigation')
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
            <div class="p-3">
                <h5>Title</h5>
                <p>Sidebar content</p>
            </div>
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- To the right -->
            <div class="float-right d-none d-sm-inline text-muted small">
                SiNilai &bull; Sistem Informasi Penilaian
            </div>
            <!-- Default to the left -->
            <strong>Copyright &copy; {{ date('Y') }} <a href="https://kzdra.github.io" target="_blank" rel="noopener noreferrer" class="font-weight-bold">InDev.</a></strong> All rights reserved.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- AdminLTE App -->
    <script src="{{ asset('js/adminlte.min.js') }}" defer></script>

    @yield('scripts')
</body>

</html>