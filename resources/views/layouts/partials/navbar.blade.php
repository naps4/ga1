<nav class="main-header navbar navbar-expand navbar-light elevation-0">
    <!-- Left area -->
    <ul class="navbar-nav">
        {{-- Hamburger toggle — mobile only, opens the side drawer --}}
        <li class="nav-item d-flex d-md-none align-items-center">
            <button class="btn top-icon-btn p-1 ml-1" id="mobileMenuToggle"
                    aria-label="Buka menu navigasi" style="font-size:1.15rem;line-height:1;border:0;background:none;">
                <i class="fas fa-bars" id="mobileMenuIcon"></i>
            </button>
        </li>
        <li class="nav-item d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="brand-head ml-1 {{ \App\Helpers\SettingsHelper::get('app_logo') ? 'has-logo' : '' }}">
                @if(\App\Helpers\SettingsHelper::get('app_logo'))
                    <span class="brand-icon"><img src="{{ asset('storage/' . \App\Helpers\SettingsHelper::get('app_logo')) }}" alt="Logo" style="height: 32px; width: auto; max-width: 160px; object-fit: contain;"></span>
                @else
                    <span class="brand-icon"><i class="fas fa-life-ring"></i></span>
                    <span class="d-none d-md-inline">{{ \App\Helpers\SettingsHelper::get('app_name', config('app.name', 'Antrian Project')) }}</span>
                @endif
            </a>
        </li>
    </ul>


    {{-- Desktop horizontal list --}}
    <div class="top-menu-scroll d-none d-md-flex flex-grow-1 ml-4 mr-auto">
        <ul class="top-menu-list" style="margin-bottom: 0; padding-top: 0; align-items: center;">
            <li>
                <a href="{{ route('dashboard') }}" class="top-menu-link {{ request()->routeIs('dashboard') || request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                    <span>Dasbor</span>
                </a>
            </li>

            <li>
                <a href="{{ route('atk-requests.index') }}" class="top-menu-link {{ request()->routeIs('atk-requests.*') ? 'active' : '' }}">
                    <span>Permintaan</span>
                </a>
            </li>

            @if(auth()->user()->isSuperAdmin())
                <li><a href="{{ route('work-orders.index') }}" class="top-menu-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}"><span>Work Order GA</span></a></li>
                <li class="dropdown">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('security.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Security Checkpoint</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('security.logs.index') }}"><i class="fas fa-file-invoice-dollar mr-2 text-info"></i> Laporan & Rekap</a>
                        <a class="dropdown-item py-2" href="{{ route('security.checkpoints.index') }}"><i class="fas fa-map-marker-alt mr-2 text-primary"></i> Master Area</a>
                        <a class="dropdown-item py-2" href="{{ route('security.shifts.index') }}"><i class="fas fa-clock mr-2 text-success"></i> Master Shift Kerja</a>
                        <a class="dropdown-item py-2" href="{{ route('security.assignments.index') }}"><i class="fas fa-calendar-alt mr-2 text-warning"></i> Penjadwalan Security</a>
                    </div>
                </li>
                <li><a href="{{ route('super-admin.reports') }}" class="top-menu-link {{ request()->routeIs('super-admin.reports*') ? 'active' : '' }}"><span>Laporan GA</span></a></li>
                <li><a href="{{ route('super-admin.users.index') }}" class="top-menu-link {{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}"><span>Data Karyawan</span></a></li>
                <li><a href="{{ route('super-admin.master-barang.index') }}" class="top-menu-link {{ request()->routeIs('super-admin.master-barang.*') ? 'active' : '' }}"><span>Master Barang</span></a></li>
                <li><a href="{{ route('super-admin.settings') }}" class="top-menu-link {{ request()->routeIs('super-admin.settings') ? 'active' : '' }}"><span>Pengaturan</span></a></li>

            @elseif(auth()->user()->canApproveProjects())
                <li><a href="{{ route('work-orders.index') }}" class="top-menu-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}"><span>Work Order GA</span></a></li>
                <li class="dropdown">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('security.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Security Checkpoint</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('security.logs.index') }}"><i class="fas fa-file-invoice-dollar mr-2 text-info"></i> Laporan & Rekap</a>
                        <a class="dropdown-item py-2" href="{{ route('security.checkpoints.index') }}"><i class="fas fa-map-marker-alt mr-2 text-primary"></i> Master Area</a>
                        <a class="dropdown-item py-2" href="{{ route('security.shifts.index') }}"><i class="fas fa-clock mr-2 text-success"></i> Master Shift Kerja</a>
                        <a class="dropdown-item py-2" href="{{ route('security.assignments.index') }}"><i class="fas fa-calendar-alt mr-2 text-warning"></i> Penjadwalan Security</a>
                    </div>
                </li>
                <li><a href="{{ route('chat.index') }}" class="top-menu-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"><span>Chat</span></a></li>
                <li><a href="{{ route('super-admin.users.index') }}" class="top-menu-link {{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}"><span>Data Karyawan</span></a></li>

            @elseif(auth()->user()->isDeveloper())
                <li><a href="{{ route('work-orders.index') }}" class="top-menu-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}"><span>Tugas Teknisi (WO)</span></a></li>
                <li><a href="{{ route('chat.index') }}" class="top-menu-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"><span>Chat</span></a></li>

            @elseif(auth()->user()->role === 'security')
                <li><a href="{{ route('security.scan') }}" class="top-menu-link {{ request()->routeIs('security.scan') ? 'active' : '' }}"><span>Scan Checkpoint</span></a></li>

            @elseif(auth()->user()->isClient())
                <li><a href="{{ route('work-orders.create') }}" class="top-menu-link {{ request()->routeIs('work-orders.create') ? 'active' : '' }}"><span>Buat Pengajuan</span></a></li>
                <li><a href="{{ route('work-orders.index') }}" class="top-menu-link {{ request()->routeIs('work-orders.index') || request()->routeIs('work-orders.show') ? 'active' : '' }}"><span>Riwayat Pengajuan</span></a></li>
                <li><a href="{{ route('chat.index') }}" class="top-menu-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"><span>Chat</span></a></li>
            @endif

            <li><a href="{{ route('profile.edit') }}" class="top-menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><span>Profil</span></a></li>
        </ul>
    </div>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link top-icon-btn" data-toggle="dropdown" href="#" id="notification-bell">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge" id="notification-count" style="display: none;">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right notification-dropdown" id="notification-dropdown">
                <span class="dropdown-item dropdown-header" id="notification-header">0 Notifikasi</span>
                <div class="dropdown-divider"></div>
                <div id="notification-list">
                    <div class="text-center p-3 text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Memuat...
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">Lihat Semua Notifikasi</a>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link top-icon-btn" href="#" id="dark-mode-toggle" title="Toggle Dark Mode">
                <i class="far fa-moon" id="dark-mode-icon"></i>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link top-icon-btn" href="{{ route('chat.index') }}" title="Chat">
                <i class="far fa-comment-alt"></i>
            </a>
        </li>

        <li class="nav-item d-none d-md-flex align-items-center">
            <span class="top-divider"></span>
        </li>

        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link text-muted" data-toggle="dropdown" href="#">
                <span class="d-none d-md-inline user-meta">
                    <span class="user-meta-name">{{ auth()->user()->name }}</span>
                    <span class="user-meta-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                </span>
                <img src="{{ auth()->user()->avatar_url }}" alt="User" class="user-avatar-pill">
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow-sm border-0 mt-2" style="border-radius: 1rem; overflow: hidden;">
                <span class="dropdown-item dropdown-header bg-light">
                    <strong class="text-dark">{{ auth()->user()->name }}</strong><br>
                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</small>
                </span>
                <div class="dropdown-divider"></div>
                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                    <i class="fas fa-user mr-2"></i> Profil
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
