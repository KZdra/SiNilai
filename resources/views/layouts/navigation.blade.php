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

            <li class="nav-item">
                <a href="{{ route('panduan.index') }}" class="nav-link {{ request()->is('panduan*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-book-reader text-warning"></i>
                    <p>
                        {{ __('Panduan & SOP') }}
                        <span class="right badge badge-info">Panduan</span>
                    </p>
                </a>
            </li>

            @if (Auth::user()->role_id == 1 || Auth::user()->class_id !== null)

            <!-- ── SEKSI 1: AKADEMIK & PENILAIAN (ALUR KURIKULUM MERDEKA) ── -->
            <li class="nav-header text-uppercase font-weight-bold text-muted small mt-2">
                {{ __('Penilaian & Rapor') }}
            </li>

            {{-- 1. Perumusan Tujuan Pembelajaran (Fondasi Pembelajaran) --}}
            <li class="nav-item">
                <a href="{{ route('mastertp.index') }}" class="nav-link {{ request()->is('mastertp*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-bullseye text-success"></i>
                    <p>{{ __('Tujuan Pembelajaran') }}</p>
                </a>
            </li>

            {{-- 2. Asesmen Formatif (Ketercapaian TP / KKTP) --}}
            @if (\App\Models\Setting::isModuleEnabled('formatif', true))
            <li class="nav-item">
                <a href="{{ route('formatif.index') }}" class="nav-link {{ request()->is('formatif*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tasks text-info"></i>
                    <p>{{ __('Asesmen Formatif (KKTP)') }}</p>
                </a>
            </li>
            @endif

            {{-- 3. Asesmen Sumatif (Harian 1-10, STS, SAS) --}}
            <li class="nav-item">
                <a href="{{ route('value.index') }}" class="nav-link {{ request()->is('nilai*') && !request()->is('nilai/audit*') && !request()->is('upload-nilai-excel*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-pen-nib text-warning"></i>
                    <p>{{ __('Asesmen Sumatif') }}</p>
                </a>
            </li>

            {{-- 4. Upload Nilai Masal Guru Mapel --}}
            <li class="nav-item">
                <a href="{{ route('nilai_import.index') }}" class="nav-link {{ request()->is('upload-nilai-excel*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-excel text-success"></i>
                    <p>
                        {{ __('Upload Nilai Excel') }}
                        <span class="right badge badge-success">Excel</span>
                    </p>
                </a>
            </li>

            {{-- 5. Presensi & Catatan Wali Kelas --}}
            <li class="nav-item">
                <a href="{{ route('walas.index') }}" class="nav-link {{ request()->is('walas*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-clipboard-check text-indigo"></i>
                    <p>{{ __('Presensi & Catatan Walas') }}</p>
                </a>
            </li>

            {{-- 6. Penilaian Ekstrakurikuler --}}
            @if (\App\Models\Setting::isModuleEnabled('eskul', true))
            <li class="nav-item">
                <a href="{{ route('peskul.index') }}" class="nav-link {{ request()->is('peskul*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-award text-purple"></i>
                    <p>{{ __('Penilaian Eskul') }}</p>
                </a>
            </li>
            @endif

            {{-- 7. Projek Penguatan Profil Pelajar Pancasila (P5) --}}
            @if (\App\Models\Setting::isModuleEnabled('p5', true))
            <li class="nav-item">
                <a href="{{ route('p5.index') }}" class="nav-link {{ request()->is('p5*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-shapes text-danger"></i>
                    <p>
                        {{ __('Projek P5') }}
                        <span class="right badge badge-danger">P5</span>
                    </p>
                </a>
            </li>
            @endif

            {{-- 8. Muara Akhir: Nilai Akhir, Ranking, Cetak Rapor & Leger --}}
            <li class="nav-item">
                <a href="{{ route('nilaiakhir.index') }}" class="nav-link {{ request()->is('akhir*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-graduation-cap text-teal"></i>
                    <p>
                        {{ __('Nilai Akhir & Rapor') }}
                        <span class="right badge badge-primary">Rapor</span>
                    </p>
                </a>
            </li>


            <!-- ── SEKSI 2: DATA MASTER ──────────────────────────── -->
            <li class="nav-header text-uppercase font-weight-bold text-muted small mt-2">
                {{ __('Data Master') }}
            </li>

            @if (Auth::user()->role_id == 1)
                @php
                    $isMasterActive = request()->is('siswa*') || request()->is('kelas*') || request()->is('mapel*') || request()->is('mapel-mapping*') || request()->is('meskul*') || request()->is('mfst*') || request()->is('datasekolah*');
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
                        {{-- 1. Fondasi Instansi --}}
                        <li class="nav-item">
                            <a href="{{ route('datasekolah.index') }}" class="nav-link {{ request()->is('datasekolah*') ? 'active' : '' }}">
                                <i class="fas fa-school nav-icon text-primary"></i>
                                <p>{{ __('Profil Sekolah') }}</p>
                            </a>
                        </li>
                        {{-- 2. Periode Akademik --}}
                        <li class="nav-item">
                            <a href="{{ route('mfst.index') }}" class="nav-link {{ request()->is('mfst*') ? 'active' : '' }}">
                                <i class="fas fa-calendar-alt nav-icon text-warning"></i>
                                <p>{{ __('FST / Periode Ajaran') }}</p>
                            </a>
                        </li>
                        {{-- 3. Rombel Kelas --}}
                        <li class="nav-item">
                            <a href="{{ route('class.index') }}" class="nav-link {{ request()->is('kelas*') ? 'active' : '' }}">
                                <i class="fas fa-chalkboard nav-icon text-info"></i>
                                <p>{{ __('Data Kelas') }}</p>
                            </a>
                        </li>
                        {{-- 4. Peserta Didik --}}
                        <li class="nav-item">
                            <a href="{{ route('student.index') }}" class="nav-link {{ request()->is('siswa*') ? 'active' : '' }}">
                                <i class="fas fa-user-graduate nav-icon text-success"></i>
                                <p>{{ __('Data Siswa') }}</p>
                            </a>
                        </li>
                        {{-- 5. Mata Pelajaran --}}
                        <li class="nav-item">
                            <a href="{{ route('mapel.index') }}" class="nav-link {{ request()->is('mapel') ? 'active' : '' }}">
                                <i class="fas fa-book nav-icon text-indigo"></i>
                                <p>{{ __('Mata Pelajaran') }}</p>
                            </a>
                        </li>
                        {{-- 6. Pemetaan Mapel ke Kelas --}}
                        <li class="nav-item">
                            <a href="{{ route('mapel_mapping.index') }}" class="nav-link {{ request()->is('mapel-mapping*') ? 'active' : '' }}">
                                <i class="fas fa-network-wired nav-icon text-cyan"></i>
                                <p>{{ __('Mapping Mapel') }}</p>
                            </a>
                        </li>
                        {{-- 7. Ekstrakurikuler --}}
                        @if (\App\Models\Setting::isModuleEnabled('eskul', true))
                        <li class="nav-item">
                            <a href="{{ route('meskul.index') }}" class="nav-link {{ request()->is('meskul*') ? 'active' : '' }}">
                                <i class="fas fa-trophy nav-icon text-purple"></i>
                                <p>{{ __('Data Ekstrakurikuler') }}</p>
                            </a>
                        </li>
                        @endif
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

                {{-- 1. Pengelolaan Akun Pengguna --}}
                <li class="nav-item">
                    <a href="{{ route('muser.index') }}" class="nav-link {{ request()->is('muser*') || request()->is('users*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users-cog text-primary"></i>
                        <p>{{ __('Manajemen Pengguna') }}</p>
                    </a>
                </li>

                {{-- 2. Kenaikan Kelas & Tutup Tahun Ajaran --}}
                <li class="nav-item">
                    <a href="{{ route('kenaikan_kelas.index') }}" class="nav-link {{ request()->is('kenaikan-kelas*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-level-up-alt text-success"></i>
                        <p>
                            {{ __('Kenaikan Kelas & Tutup TA') }}
                            <span class="right badge badge-warning text-dark">Akhir TA</span>
                        </p>
                    </a>
                </li>

                {{-- 3. Audit Trail Perubahan Nilai --}}
                <li class="nav-item">
                    <a href="{{ route('audit.index') }}" class="nav-link {{ request()->is('audit*') || request()->is('audit-nilai*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history text-warning"></i>
                        <p>{{ __('Audit Mutasi Nilai') }}</p>
                    </a>
                </li>

                {{-- 4. Arsip Rapor Digital (Storage Explorer) --}}
                <li class="nav-item">
                    <a href="{{ route('raport_explorer.index') }}" class="nav-link {{ request()->is('raport-explorer') || request()->is('raport-explorer/tree*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-folder-open text-warning"></i>
                        <p>{{ __('Arsip Raport (Storage)') }}</p>
                    </a>
                </li>

                {{-- 5. Export Massal Rapor ke Server --}}
                <li class="nav-item">
                    <a href="{{ route('raport_explorer.bulk_export') }}" class="nav-link {{ request()->is('raport-explorer/bulk-export*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cloud-upload-alt text-teal"></i>
                        <p>{{ __('Export Massal Server') }}</p>
                    </a>
                </li>

                {{-- 6. Backup & Dump Basis Data --}}
                <li class="nav-item">
                    <a href="{{ route('backup.index') }}" class="nav-link {{ request()->is('backup*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-database text-info"></i>
                        <p>
                            {{ __('Backup Database') }}
                            <span class="right badge badge-secondary">.sql</span>
                        </p>
                    </a>
                </li>

                {{-- 7. Kendali Toggle Modul Sistem --}}
                <li class="nav-item">
                    <a href="{{ route('settings.modules') }}" class="nav-link {{ request()->is('settings/modules*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-toggle-on text-success"></i>
                        <p>{{ __('Pengaturan Modul') }}</p>
                    </a>
                </li>

                {{-- 8. Pengaturan SSO & Protokol Auth --}}
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
                        <p>
                            {{ __('Upload Nilai Excel') }}
                            <span class="right badge badge-success">Excel</span>
                        </p>
                    </a>
                </li>
            @endif

            <!-- ── AKUN PENGGUNA ─────────────────────────────────── -->
            <li class="nav-header text-uppercase font-weight-bold text-muted small mt-2">
                {{ __('Akun & Profil') }}
            </li>
            <li class="nav-item">
                <a href="{{ route('profile.show') }}" class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-circle text-info"></i>
                    <p>{{ __('Pengaturan Akun') }}</p>
                </a>
            </li>

        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>
<!-- /.sidebar -->