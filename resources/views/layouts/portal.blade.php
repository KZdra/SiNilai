<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal Siswa & Orang Tua') - SiNilai</title>

    <!-- Google Font: Inter -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome (Local + CDN Fallback) -->
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
        }
        .portal-navbar {
            background: linear-gradient(135deg, #102C57 0%, #1e3c72 50%, #2a5298 100%);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            position: sticky;
            top: 0;
            z-index: 1030;
            padding-top: 10px;
            padding-bottom: 10px;
        }
        .navbar-brand {
            font-size: 1.15rem;
            letter-spacing: -0.2px;
        }
        .portal-brand-pill {
            background: rgba(255, 255, 255, 0.15);
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .portal-card {
            border-radius: 14px;
            border: none;
            box-shadow: 0 4px 14px rgba(0,0,0,0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .portal-card:hover {
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        }
        .badge-pill-custom {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .nav-portal-link {
            color: rgba(255,255,255,0.85) !important;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
        }
        .nav-portal-link:hover {
            color: #ffffff !important;
            background-color: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }
        .nav-portal-link.active {
            color: #ffffff !important;
            background-color: rgba(255,255,255,0.22);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .nav-portal-link i {
            margin-right: 8px;
        }

        /* Mobile specific styles */
        .portal-toggler {
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            padding: 6px 10px;
            border-radius: 8px;
            outline: none !important;
            background: rgba(255, 255, 255, 0.08);
            transition: background 0.2s;
        }
        .portal-toggler:hover, .portal-toggler:focus {
            background: rgba(255, 255, 255, 0.2);
        }
        .portal-toggler .navbar-toggler-icon {
            filter: brightness(0) invert(1);
        }

        @media (max-width: 767.98px) {
            .portal-navbar .container {
                position: relative;
            }
            #navbarCollapse {
                background: linear-gradient(180deg, #102C57 0%, #173b6f 100%);
                margin: 12px -15px -10px -15px;
                padding: 18px 20px 22px 20px;
                border-radius: 0 0 20px 20px;
                box-shadow: 0 16px 32px rgba(0,0,0,0.35);
                border-top: 1px solid rgba(255, 255, 255, 0.12);
            }
            .nav-portal-link {
                display: flex !important;
                width: 100%;
                padding: 12px 16px !important;
                margin-bottom: 8px;
                font-size: 0.95rem;
                background: rgba(255, 255, 255, 0.05);
            }
            .nav-portal-link i {
                width: 26px;
                font-size: 1.15rem;
            }
            .desktop-user-menu {
                display: none !important;
            }
            .mobile-user-card {
                display: block !important;
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 14px;
                padding: 14px 16px;
                margin-top: 14px;
            }
        }

        @media (min-width: 768px) {
            .mobile-user-card {
                display: none !important;
            }
            .desktop-user-menu {
                display: flex !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="layout-top-nav">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand-md navbar-dark portal-navbar border-0">
        <div class="container">
            <a href="{{ route('portal.dashboard') }}" class="navbar-brand d-flex align-items-center">
                <i class="fas fa-graduation-cap fa-lg mr-2 text-warning"></i>
                <span class="brand-text font-weight-bold mr-2">SiNilai</span>
                <span class="portal-brand-pill font-weight-bold text-white d-none d-sm-inline">Portal Siswa</span>
            </a>

            <!-- Mobile Hamburger Toggle -->
            <button class="navbar-toggler portal-toggler order-1 ml-auto" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Menu Navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse order-3" id="navbarCollapse">
                <!-- Navigation Links -->
                <ul class="navbar-nav ml-md-3">
                    <li class="nav-item">
                        <a href="{{ route('portal.dashboard') }}" class="nav-portal-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home text-info"></i>
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.nilai') }}" class="nav-portal-link {{ request()->routeIs('portal.nilai') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar text-warning"></i>
                            <span>Transkrip Nilai</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.p5') }}" class="nav-portal-link {{ request()->routeIs('portal.p5') ? 'active' : '' }}">
                            <i class="fas fa-shapes text-success"></i>
                            <span>Projek P5</span>
                        </a>
                    </li>
                </ul>

                <!-- Desktop User Dropdown -->
                <ul class="navbar-nav ml-auto align-items-center desktop-user-menu">
                    <li class="nav-item dropdown">
                        <a class="nav-link text-white d-flex align-items-center px-3 py-2 rounded" data-toggle="dropdown" href="#" style="background: rgba(255,255,255,0.1);">
                            <i class="fas fa-user-circle fa-lg mr-2 text-warning"></i>
                            <span class="font-weight-bold mr-1">{{ Auth::user()->name }}</span>
                            <i class="fas fa-chevron-down ml-1 small opacity-75"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 mt-2 p-2" style="border-radius: 12px; min-width: 220px;">
                            <div class="px-3 py-2 border-bottom mb-2">
                                <span class="d-block font-weight-bold text-dark text-truncate">{{ Auth::user()->name }}</span>
                                <small class="text-muted d-block"><i class="fas fa-id-badge mr-1"></i>{{ Auth::user()->username }}</small>
                                <span class="badge badge-primary px-2 py-1 mt-1 small">Siswa</span>
                            </div>
                            <a href="{{ route('portal.password') }}" class="dropdown-item rounded py-2 {{ request()->routeIs('portal.password') ? 'active' : '' }}">
                                <i class="fas fa-key mr-2 text-warning"></i> Ganti Password
                            </a>
                            <div class="dropdown-divider my-1"></div>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item rounded py-2 text-danger">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>

                <!-- Mobile User Card (Inside Collapsed Menu) -->
                <div class="mobile-user-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-warning text-dark font-weight-bold d-flex align-items-center justify-content-center mr-3" style="width: 42px; height: 42px; font-size: 1.2rem;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="text-truncate">
                            <span class="d-block text-white font-weight-bold text-truncate">{{ Auth::user()->name }}</span>
                            <small class="text-white-50"><i class="fas fa-id-card mr-1"></i>{{ Auth::user()->username }} &bull; Siswa</small>
                        </div>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('portal.password') }}" class="btn btn-sm btn-warning font-weight-bold flex-fill mr-2 py-2">
                            <i class="fas fa-key mr-1"></i> Ganti Password
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold text-white border-danger py-2 px-3">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </nav>
    <!-- /.navbar -->

    <!-- Content Wrapper -->
    <div class="content-wrapper py-4">
        @yield('content')
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer bg-white border-top text-center py-3">
        <div class="container small text-muted">
            &copy; {{ date('Y') }} <strong>SiNilai</strong> - Portal Mandiri Akademik Siswa & Orang Tua. Dokumen Sah Berstandar Kurikulum Merdeka.
        </div>
    </footer>
</div>

<!-- Scripts -->
<!-- @vite('resources/js/app.js') -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])

<script src="{{ asset('js/adminlte.min.js') }}" defer></script>
@yield('scripts')
@stack('scripts')
</body>
</html>
