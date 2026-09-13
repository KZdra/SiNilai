<!-- Sidebar -->
<div class="sidebar">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">

            <!-- ── MENU UTAMA ────────────────────────────────────── -->
            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt text-primary"></i>
                    <p>{{ __('Dashboard') }}</p>
                </a>
            </li>

            @if (Auth::user()->role_id == 1 || Auth::user()->class_id !== null)

            <!-- ── SEKSI 1: AKADEMIK & PENILAIAN ─────────────────── -->
            <li class="nav-header text-uppercase font-weight-bold text-muted small mt-2">
                {{ __('Penilaian & Rapor') }}
            </li>

            @if (\App\Models\Setting::isModuleEnabled('formatif', true))
            <li class="nav-item">
                <a href="{{ route('formatif.index') }}" class="nav-link {{ request()->is('formatif*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tasks text-info"></i>
                    <p>{{ __('Asesmen Formatif') }}</p>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('value.index') }}" class="nav-link {{ request()->is('nilai*') && !request()->is('nilai/audit*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-pen-nib text-warning"></i>
                    <p>{{ __('Asesmen Sumatif') }}</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('nilai_import.index') }}" class="nav-link {{ request()->is('upload-nilai-excel*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-excel text-success"></i>
                    <p class="font-weight-bold">{{ __('Upload Nilai (Excel)') }}</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('mastertp.index') }}" class="nav-link {{ request()->is('mastertp*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-bullseye text-success"></i>
                    <p>{{ __('Tujuan Pembelajaran') }}</p>
                </a>
            </li>

            @if (\App\Models\Setting::isModuleEnabled('p5', true))
            <li class="nav-item">
                <a href="{{ route('p5.index') }}" class="nav-link {{ request()->is('p5*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-shapes text-danger"></i>
                    <p>{{ __('Projek P5') }}</p>
                </a>
            </li>
            @endif

            @if (\App\Models\Setting::isModuleEnabled('eskul', true))
            <li class="nav-item">
                <a href="{{ route('peskul.index') }}" class="nav-link {{ request()->is('peskul*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-award text-purple"></i>
                    <p>{{ __('Penilaian Eskul') }}</p>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('walas.index') }}" class="nav-link {{ request()->is('walas*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-clipboard-check text-indigo"></i>
                    <p>{{ __('Presensi & Catatan Walas') }}</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('nilaiakhir.index') }}" class="nav-link {{ request()->is('akhir*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-graduation-cap text-teal"></i>
                    <p class="font-weight-bold">{{ __('Nilai Akhir & Rapor') }}</p>
                </a>
            </li>


            <!-- ── SEKSI 2: DATA MASTER ──────────────────────────── -->
            <li class="nav-header text-uppercase font-weight-bold text-muted small mt-2">
                {{ __('Data Master') }}
            </li>

            @if (Auth::user()->role_id == 1)
                @php
                    $isMasterActive = request()->is('siswa*') || request()->is('kelas*') || request()->is('mapel*') || request()->is('meskul*') || request()->is('mfst*') || request()->is('datasekolah*');
                @endphp
                <li class="nav-item has-treeview {{ $isMasterActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $isMasterActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-database text-cyan"></i>
                        <p>
                            {{ __('Kelola Data Master') }}
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('student.index') }}" class="nav-link {{ request()->is('siswa*') ? 'active' : '' }}">
                                <i class="fas fa-user-graduate nav-icon"></i>
                                <p>{{ __('Data Siswa') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('class.index') }}" class="nav-link {{ request()->is('kelas*') ? 'active' : '' }}">
                                <i class="fas fa-school nav-icon"></i>
                                <p>{{ __('Data Kelas') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('mapel.index') }}" class="nav-link {{ request()->is('mapel') ? 'active' : '' }}">
                                <i class="fas fa-book nav-icon"></i>
                                <p>{{ __('Mata Pelajaran') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('mapel_mapping.index') }}" class="nav-link {{ request()->is('mapel-mapping*') ? 'active' : '' }}">
                                <i class="fas fa-network-wired nav-icon"></i>
                                <p>{{ __('Mapping Mapel') }}</p>
                            </a>
                        </li>
                        @if (\App\Models\Setting::isModuleEnabled('eskul', true))
                        <li class="nav-item">
                            <a href="{{ route('meskul.index') }}" class="nav-link {{ request()->is('meskul*') ? 'active' : '' }}">
                                <i class="fas fa-trophy nav-icon"></i>
                                <p>{{ __('Data Ekstrakurikuler') }}</p>
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('mfst.index') }}" class="nav-link {{ request()->is('mfst*') ? 'active' : '' }}">
                                <i class="fas fa-calendar-alt nav-icon"></i>
                                <p>{{ __('FST / Periode Ajaran') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('datasekolah.index') }}" class="nav-link {{ request()->is('datasekolah*') ? 'active' : '' }}">
                                <i class="fas fa-cogs nav-icon"></i>
                                <p>{{ __('Profil Sekolah') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
            @else
                {{-- Untuk Wali Kelas / Guru yang bukan admin --}}
                <li class="nav-item">
                    <a href="{{ route('student.index') }}" class="nav-link {{ request()->is('siswa*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-graduate text-cyan"></i>
                        <p>{{ __('Data Siswa Kelas') }}</p>
                    </a>
                </li>
            @endif

            <!-- ── SEKSI 3: ADMINISTRASI & SISTEM (KHUSUS ADMIN) ─── -->
            @if (Auth::user()->role_id == 1)
                <li class="nav-header text-uppercase font-weight-bold text-muted small mt-2">
                    {{ __('Administrasi & Sistem') }}
                </li>

                <li class="nav-item">
                    <a href="{{ route('kenaikan_kelas.index') }}" class="nav-link {{ request()->is('kenaikan-kelas*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-level-up-alt text-success"></i>
                        <p class="font-weight-bold">{{ __('Kenaikan Kelas & Tutup TA') }}</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('audit.index') }}" class="nav-link {{ request()->is('audit*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history text-warning"></i>
                        <p>{{ __('Audit Mutasi Nilai') }}</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('raport_explorer.index') }}" class="nav-link {{ request()->is('raport-explorer*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-folder-open text-warning"></i>
                        <p class="font-weight-bold">{{ __('Arsip Raport (Storage)') }}</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('muser.index') }}" class="nav-link {{ request()->is('muser*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-cog text-primary"></i>
                        <p>{{ __('Manajemen Pengguna') }}</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('backup.index') }}" class="nav-link {{ request()->is('backup*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-database text-info"></i>
                        <p class="font-weight-bold">{{ __('Backup Database') }}</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('settings.modules') }}" class="nav-link {{ request()->is('settings/modules*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-toggle-on text-success"></i>
                        <p class="font-weight-bold">{{ __('Pengaturan Modul') }}</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('settings.auth') }}" class="nav-link {{ request()->is('settings/auth*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-shield-alt text-danger"></i>
                        <p>{{ __('Pengaturan SSO / Auth') }}</p>
                    </a>
                </li>
            @endif

            @else
                <!-- ── SEKSI GURU MAPEL (NON-WALAS) ─────────────────── -->
                <li class="nav-header text-uppercase font-weight-bold text-muted small mt-2">
                    {{ __('Penilaian (Guru Mapel)') }}
                </li>
                <li class="nav-item">
                    <a href="{{ route('nilai_import.index') }}" class="nav-link {{ request()->is('upload-nilai-excel*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-excel text-success"></i>
                        <p class="font-weight-bold">{{ __('Upload Nilai (Excel)') }}</p>
                    </a>
                </li>
            @endif

        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>
<!-- /.sidebar -->