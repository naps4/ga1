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
                @if(auth()->user()->role === 'security')
                    <a href="{{ route('security.scan') }}" class="top-menu-link {{ request()->routeIs('security.scan') ? 'active' : '' }}">
                        <span>Scan Checkpoint</span>
                    </a>
                @elseif(auth()->user()->role === 'cleaning')
                    <a href="{{ route('cleaning.scan') }}" class="top-menu-link {{ request()->routeIs('cleaning.scan') ? 'active' : '' }}">
                        <span>Scan Kebersihan</span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="top-menu-link {{ request()->routeIs('dashboard') || request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                        <span>Dasbor</span>
                    </a>
                @endif
            </li>

            @if(auth()->user()->isSuperAdmin())
                <li class="dropdown position-relative">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('work-orders.*') || request()->routeIs('atk-requests.*') || request()->routeIs('assets.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Work Order & Aset</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('work-orders.index') }}"><i class="fas fa-hard-hat mr-2 text-primary"></i> Work Order GA</a>
                        <a class="dropdown-item py-2" href="{{ route('atk-requests.index') }}"><i class="fas fa-pencil-alt mr-2 text-info"></i> Permintaan ATK</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item py-2" href="{{ route('assets.index') }}"><i class="fas fa-boxes mr-2 text-warning"></i> Master Aset GA (All)</a>
                        <a class="dropdown-item py-2" href="{{ route('assets.plant', '01') }}"><i class="fas fa-industry mr-2 text-primary"></i> Aset Plant A</a>
                        <a class="dropdown-item py-2" href="{{ route('assets.plant', '02') }}"><i class="fas fa-building mr-2 text-info"></i> Aset Plant B</a>
                        <a class="dropdown-item py-2" href="{{ route('assets.lampiran-c') }}"><i class="fas fa-clipboard-list mr-2 text-success"></i> Form Lampiran C</a>
                    </div>
                </li>
                <li class="dropdown position-relative">
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
                <li class="dropdown position-relative">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('cleaning.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Cleaning Checkpoint</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('cleaning.logs.index') }}"><i class="fas fa-file-invoice-dollar mr-2 text-info"></i> Laporan & Rekap</a>
                        <a class="dropdown-item py-2" href="{{ route('cleaning.checkpoints.index') }}"><i class="fas fa-broom mr-2" style="color: #0d9488;"></i> Master Area</a>
                        <a class="dropdown-item py-2" href="{{ route('cleaning.shifts.index') }}"><i class="fas fa-clock mr-2 text-success"></i> Master Shift Kerja</a>
                        <a class="dropdown-item py-2" href="{{ route('cleaning.assignments.index') }}"><i class="fas fa-calendar-alt mr-2 text-warning"></i> Penjadwalan Kebersihan</a>
                    </div>
                </li>
                <li class="dropdown position-relative">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('super-admin.reports*') || request()->routeIs('super-admin.users.*') || request()->routeIs('super-admin.master-barang.*') || request()->routeIs('super-admin.settings') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Kelola GA</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('super-admin.reports') }}"><i class="fas fa-chart-bar mr-2 text-info"></i> Laporan GA</a>
                        <a class="dropdown-item py-2" href="{{ route('super-admin.users.index') }}"><i class="fas fa-users mr-2 text-primary"></i> Data Karyawan</a>
                        <a class="dropdown-item py-2" href="{{ route('super-admin.master-barang.index') }}"><i class="fas fa-boxes mr-2 text-warning"></i> Master Barang</a>
                        <a class="dropdown-item py-2" href="{{ route('super-admin.settings') }}"><i class="fas fa-cog mr-2 text-secondary"></i> Pengaturan Aplikasi</a>
                    </div>
                </li>

            @elseif(auth()->user()->canApproveProjects())
                <li class="dropdown position-relative">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('work-orders.*') || request()->routeIs('atk-requests.*') || request()->routeIs('assets.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Work Order & Aset</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('work-orders.index') }}"><i class="fas fa-hard-hat mr-2 text-primary"></i> Work Order GA</a>
                        <a class="dropdown-item py-2" href="{{ route('atk-requests.index') }}"><i class="fas fa-pencil-alt mr-2 text-info"></i> Permintaan ATK</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item py-2" href="{{ route('assets.index') }}"><i class="fas fa-boxes mr-2 text-warning"></i> Master Aset GA (All)</a>
                        <a class="dropdown-item py-2" href="{{ route('assets.plant', '01') }}"><i class="fas fa-industry mr-2 text-primary"></i> Aset Plant A</a>
                        <a class="dropdown-item py-2" href="{{ route('assets.plant', '02') }}"><i class="fas fa-building mr-2 text-info"></i> Aset Plant B</a>
                        <a class="dropdown-item py-2" href="{{ route('assets.lampiran-c') }}"><i class="fas fa-clipboard-list mr-2 text-success"></i> Form Lampiran C</a>
                    </div>
                </li>
                <li class="dropdown position-relative">
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
                <li class="dropdown position-relative">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('cleaning.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Cleaning Checkpoint</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('cleaning.logs.index') }}"><i class="fas fa-file-invoice-dollar mr-2 text-info"></i> Laporan & Rekap</a>
                        <a class="dropdown-item py-2" href="{{ route('cleaning.checkpoints.index') }}"><i class="fas fa-broom mr-2" style="color: #0d9488;"></i> Master Area</a>
                        <a class="dropdown-item py-2" href="{{ route('cleaning.shifts.index') }}"><i class="fas fa-clock mr-2 text-success"></i> Master Shift Kerja</a>
                        <a class="dropdown-item py-2" href="{{ route('cleaning.assignments.index') }}"><i class="fas fa-calendar-alt mr-2 text-warning"></i> Penjadwalan Kebersihan</a>
                    </div>
                </li>
                <li><a href="{{ route('chat.index') }}" class="top-menu-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"><span>Chat</span></a></li>
                <li><a href="{{ route('super-admin.users.index') }}" class="top-menu-link {{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}"><span>Data Karyawan</span></a></li>

            @elseif(auth()->user()->isManager())
                <li class="dropdown position-relative">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('work-orders.*') || request()->routeIs('atk-requests.*') || request()->routeIs('assets.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Work Order & Aset</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('work-orders.index') }}"><i class="fas fa-hard-hat mr-2 text-primary"></i> Work Order GA</a>
                        <a class="dropdown-item py-2" href="{{ route('atk-requests.index') }}"><i class="fas fa-pencil-alt mr-2 text-info"></i> Permintaan ATK</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item py-2" href="{{ route('assets.index') }}"><i class="fas fa-boxes mr-2 text-warning"></i> Master Aset GA (All)</a>
                        <a class="dropdown-item py-2" href="{{ route('assets.plant', '01') }}"><i class="fas fa-industry mr-2 text-primary"></i> Aset Plant A</a>
                        <a class="dropdown-item py-2" href="{{ route('assets.plant', '02') }}"><i class="fas fa-building mr-2 text-info"></i> Aset Plant B</a>
                        <a class="dropdown-item py-2" href="{{ route('assets.lampiran-c') }}"><i class="fas fa-clipboard-list mr-2 text-success"></i> Form Lampiran C</a>
                    </div>
                </li>
                <li class="dropdown position-relative">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('security.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Security Checkpoint</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('security.logs.index') }}"><i class="fas fa-file-invoice-dollar mr-2 text-info"></i> Laporan & Rekap</a>
                    </div>
                </li>
                <li class="dropdown position-relative">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('cleaning.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Cleaning Checkpoint</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('cleaning.logs.index') }}"><i class="fas fa-file-invoice-dollar mr-2 text-info"></i> Laporan & Rekap</a>
                    </div>
                </li>

            @elseif(auth()->user()->isDeveloper())
                <li><a href="{{ route('work-orders.index') }}" class="top-menu-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}"><span>Tugas Teknisi (WO)</span></a></li>
                <li><a href="{{ route('chat.index') }}" class="top-menu-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"><span>Chat</span></a></li>

            @elseif(auth()->user()->role === 'security')
                <li><a href="{{ route('security.scan') }}" class="top-menu-link {{ request()->routeIs('security.scan') ? 'active' : '' }}"><span>Scan Checkpoint</span></a></li>

            @elseif(auth()->user()->role === 'cleaning')
                <li><a href="{{ route('cleaning.scan') }}" class="top-menu-link {{ request()->routeIs('cleaning.scan') ? 'active' : '' }}"><span>Scan Kebersihan</span></a></li>

            @elseif(auth()->user()->isClient())
                <li class="dropdown position-relative">
                    <a class="top-menu-link dropdown-toggle {{ request()->routeIs('work-orders.*') || request()->routeIs('atk-requests.*') ? 'active' : '' }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>Work Order & Permintaan</span>
                    </a>
                    <div class="dropdown-menu border-0 shadow-sm mt-2" style="border-radius: .75rem;">
                        <a class="dropdown-item py-2" href="{{ route('work-orders.create') }}"><i class="fas fa-plus-circle mr-2 text-success"></i> Buat Pengajuan WO</a>
                        <a class="dropdown-item py-2" href="{{ route('work-orders.index') }}"><i class="fas fa-hard-hat mr-2 text-primary"></i> Riwayat Work Order</a>
                        <a class="dropdown-item py-2" href="{{ route('atk-requests.index') }}"><i class="fas fa-pencil-alt mr-2 text-info"></i> Permintaan ATK</a>
                    </div>
                </li>
                <li><a href="{{ route('chat.index') }}" class="top-menu-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"><span>Chat</span></a></li>
            @endif

            <li><a href="{{ route('profile.edit') }}" class="top-menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><span>Profil</span></a></li>
        </ul>
    </div>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto flex-shrink-0 align-items-center">
        {{-- Tombol Scan cepat untuk Security & Cleaning Guard (mobile only) --}}
        @if(auth()->user()->role === 'security')
        <li class="nav-item d-flex d-md-none align-items-center mr-1">
            <a href="{{ route('security.scan') }}"
               class="btn btn-sm btn-primary rounded-pill px-3 py-1 font-weight-bold"
               style="font-size:.8rem; letter-spacing:.02em;">
                <i class="fas fa-qrcode mr-1"></i> Scan
            </a>
        </li>
        @elseif(auth()->user()->role === 'cleaning')
        <li class="nav-item d-flex d-md-none align-items-center mr-1">
            <a href="{{ route('cleaning.scan') }}"
               class="btn btn-sm text-white rounded-pill px-3 py-1 font-weight-bold"
               style="font-size:.8rem; letter-spacing:.02em; background-color: #0d9488;">
                <i class="fas fa-qrcode mr-1"></i> Scan
            </a>
        </li>
        @endif

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
            <a class="nav-link text-muted d-flex align-items-center px-2 py-0" data-toggle="dropdown" href="#">
                <span class="d-none d-lg-inline-block user-meta text-right mr-2" style="white-space: nowrap; line-height: 1.2;">
                    <span class="user-meta-name d-block text-truncate" style="max-width: 130px;">{{ auth()->user()->name }}</span>
                    <span class="user-meta-role d-block text-truncate" style="max-width: 130px;">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                </span>
                <img src="{{ auth()->user()->avatar_url }}" alt="User" class="user-avatar-pill flex-shrink-0">
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
