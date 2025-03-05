<!-- Sidebar -->
<div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <img src="{{ asset('images/user.jpg') }}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
            <a href="{{ route('profile.show') }}" class="d-block">{{ Auth::user()->name }}</a>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-th"></i>
                    <p>
                        {{ __('Dashboard') }}
                    </p>
                </a>
            </li>

            <li class="nav-item ">
                <a href="{{ route('nilaiakhir.index') }}" class="nav-link {{ request()->is('akhir*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-graduation-cap"></i>
                    <p>
                        {{ __('Nilai Akhir') }}
                    </p>
                </a>
            </li>
            <li class="nav-item ">
                <a href="{{ route('value.index') }}" class="nav-link {{ request()->is('nilai*') ? 'active' : '' }} ">
                    <i class="nav-icon fas fa-pen"></i>
                    <p>
                        {{ __('Input Nilai Per Mapel') }}
                    </p>
                </a>
            </li>



            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-laptop-code nav-icon"></i>
                    <p>
                        Data Master
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview" style="display: none;">
                    <li class="nav-item ">
                        <a href="{{ route('class.index') }}"
                            class="nav-link {{ request()->is('kelas*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-school"></i>
                            <p>Data Kelas</p>
                        </a>
                    </li>

                    <li class="nav-item ">
                        <a href="{{ route('student.index') }}"
                            class="nav-link {{ request()->is('siswa*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>Data Siswa</p>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a href="{{ route('mapel.index') }}"
                            class="nav-link {{ request()->is('mapel*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Data Mata Pelajaran</p>
                        </a>
                    </li>

                </ul>
            </li>
        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>
<!-- /.sidebar -->
