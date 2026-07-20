<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0 text-dark">Dashboard Operational Manager</h1>
            <span class="badge badge-primary px-3 py-2 rounded-pill font-weight-bold">
                <i class="fas fa-user-tie mr-1"></i> Manager
            </span>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Quick Stats Widgets -->
        <div class="row">
            <!-- Pending Manager Approvals -->
            <div class="col-lg-6 col-md-6 col-12">
                <div class="small-box bg-warning rounded-4 shadow-sm border-0 position-relative overflow-hidden mb-4" style="transition: transform .2s;">
                    <div class="inner p-4 text-white">
                        <h3 style="font-size: 2.2rem; font-weight: 700;">{{ $pendingManagerAtk ?? 0 }}</h3>
                        <p style="font-size: 1rem; opacity: 0.9;">Menunggu Persetujuan Anda</p>
                    </div>
                    <div class="icon" style="position: absolute; right: 15px; top: 15px; font-size: 4rem; opacity: 0.15; color: #fff;">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <a href="{{ route('atk-requests.index', ['status_approval' => 'Menunggu Persetujuan Manager']) }}" class="small-box-footer bg-dark-50 text-white py-2" style="background: rgba(0,0,0,0.1); display: block; text-align: center; text-decoration: none;">
                        Proses Sekarang <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>

            <!-- Approved by Manager -->
            <div class="col-lg-6 col-md-6 col-12">
                <div class="small-box bg-success rounded-4 shadow-sm border-0 position-relative overflow-hidden mb-4" style="transition: transform .2s;">
                    <div class="inner p-4 text-white">
                        <h3 style="font-size: 2.2rem; font-weight: 700;">{{ $totalManagerAtkApproved ?? 0 }}</h3>
                        <p style="font-size: 1rem; opacity: 0.9;">Telah Anda Setujui / Proses</p>
                    </div>
                    <div class="icon" style="position: absolute; right: 15px; top: 15px; font-size: 4rem; opacity: 0.15; color: #fff;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <a href="{{ route('atk-requests.index') }}" class="small-box-footer bg-dark-50 text-white py-2" style="background: rgba(0,0,0,0.1); display: block; text-align: center; text-decoration: none;">
                        Lihat Semua Riwayat <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Security Checkpoint Summary Row -->
        <div class="row mt-3">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 rounded-4 bg-white mb-0 h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary-light text-primary d-flex align-items-center justify-content-center mr-3" style="width: 54px; height: 54px; background-color: rgba(37, 99, 235, 0.1);">
                                <i class="fas fa-shield-alt fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small font-weight-bold">Patroli Security Hari Ini</h6>
                                <h4 class="mb-0 font-weight-bold text-dark">{{ $checkedToday ?? 0 }} / {{ $totalAreas ?? 0 }} Area Terpantau</h4>
                            </div>
                        </div>
                        <a href="{{ route('security.checkpoints.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                            Detail <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 rounded-4 bg-white mb-0 h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 {{ ($anomaliesToday ?? 0) > 0 ? 'bg-danger-light text-danger' : 'bg-success-light text-success' }}" style="width: 54px; height: 54px; background-color: {{ ($anomaliesToday ?? 0) > 0 ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)' }};">
                                <i class="fas {{ ($anomaliesToday ?? 0) > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle' }} fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small font-weight-bold">Temuan / Anomali Keamanan</h6>
                                <h4 class="mb-0 font-weight-bold {{ ($anomaliesToday ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $anomaliesToday ?? 0 }} Temuan Hari Ini
                                </h4>
                            </div>
                        </div>
                        <a href="{{ route('security.checkpoints.index') }}?status=anomaly" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            Lihat <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 rounded-4 bg-white mb-0 h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success-light text-success d-flex align-items-center justify-content-center mr-3" style="width: 54px; height: 54px; background-color: rgba(16, 185, 129, 0.1);">
                                <i class="fas fa-user-clock fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small font-weight-bold">Security On Duty Hari Ini</h6>
                                <h4 class="mb-0 font-weight-bold text-success">{{ count($todayAssignments ?? []) }} Petugas Aktif</h4>
                            </div>
                        </div>
                        <span class="btn btn-sm btn-success rounded-pill px-3 disabled">
                            Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent ATK Requests Summary Card -->
        <div class="card shadow-sm border-0 rounded-4 mt-3">
            <div class="card-header bg-white border-bottom border-light py-3 d-flex align-items-center justify-content-between">
                <h3 class="card-title text-primary m-0 font-weight-bold">
                    <i class="fas fa-clipboard-list mr-1"></i> Kesimpulan Permintaan ATK
                </h3>
                <a href="{{ route('atk-requests.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="py-3 px-4" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">No. Pengajuan</th>
                                <th class="py-3" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Pemohon</th>
                                <th class="py-3" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Divisi</th>
                                <th class="py-3" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Periode</th>
                                <th class="py-3" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Status</th>
                                <th class="py-3 px-4 text-right" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAtkRequests ?? [] as $req)
                                <tr>
                                    <td class="py-3 px-4 font-weight-bold">
                                        {{ (($req->request_type ?? 'ATK') === 'Rumah Tangga' ? 'RT-' : 'ATK-') . str_pad($req->id, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3">{{ $req->nama }}</td>
                                    <td class="py-3"><span class="badge badge-secondary rounded-pill px-2 py-1">{{ $req->divisi }}</span></td>
                                    <td class="py-3">{{ $req->periode }}</td>
                                    <td class="py-3">
                                        @if($req->status_approval === 'Draft')
                                            <span class="badge badge-warning rounded-pill px-2 py-1">Draft</span>
                                        @elseif($req->status_approval === 'Menunggu Persetujuan Manager')
                                            <span class="badge badge-info rounded-pill px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i> Menunggu Persetujuan Anda</span>
                                        @elseif($req->status_approval === 'Menunggu Persetujuan Admin GA')
                                            <span class="badge badge-primary rounded-pill px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i> Menunggu GA</span>
                                        @elseif($req->status_approval === 'Disetujui')
                                            <span class="badge badge-success rounded-pill px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                                        @else
                                            <span class="badge badge-danger rounded-pill px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if($req->status_approval === 'Menunggu Persetujuan Manager')
                                            <a href="{{ route('atk-requests.show', $req->id) }}" class="btn btn-sm btn-warning rounded-pill px-3 shadow-xs">
                                                <i class="fas fa-edit mr-1"></i> Tinjau
                                            </a>
                                        @else
                                            <a href="{{ route('atk-requests.show', $req->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="fas fa-eye mr-1"></i> Detail
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-folder-open fa-2x mb-3 d-block text-gray-300"></i>
                                        Belum ada riwayat pengajuan ATK yang perlu Anda proses.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        <!-- Security Shift & Penugasan Keseluruhan -->
        <div class="card shadow-sm border-0 rounded-4 mt-4">
            <div class="card-header bg-white border-bottom border-light py-3 d-flex align-items-center justify-content-between">
                <h3 class="card-title text-info m-0 font-weight-bold">
                    <i class="fas fa-calendar-check mr-1"></i> Jadwal & Penugasan Security Keseluruhan
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle" style="font-size: .88rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="py-3 px-4">Petugas Security</th>
                                <th class="py-3 text-center">Tipe Tugas / Tanggal</th>
                                <th class="py-3 text-center">Shift</th>
                                <th class="py-3 text-center">Jam Tugas</th>
                                <th class="py-3">Penempatan Area</th>
                                <th class="py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allAssignments ?? [] as $assignment)
                                <tr>
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $assignment->user->avatar_url }}" alt="avatar" class="rounded-circle mr-2" style="width: 28px; height: 28px; object-fit: cover;">
                                            <strong>{{ $assignment->user->name ?? '-' }}</strong>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($assignment->assigned_date)
                                            <span class="badge badge-light border text-muted">Harian ({{ $assignment->assigned_date->format('d M Y') }})</span>
                                        @else
                                            <span class="badge badge-success-light text-success font-weight-bold" style="background: rgba(16, 185, 129, 0.1);">Permanen / Default</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($assignment->shift)
                                            <span class="badge badge-primary px-2.5 py-1 rounded">{{ $assignment->shift->name }}</span>
                                        @else
                                            <span class="text-muted small">Fleksibel</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center text-muted small">
                                        @if($assignment->shift)
                                            {{ \Carbon\Carbon::parse($assignment->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($assignment->shift->end_time)->format('H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if($assignment->plant)
                                            <span class="badge badge-info rounded-pill px-2.5 py-1">
                                                <i class="fas fa-industry mr-1"></i>{{ $assignment->plant }}
                                            </span>
                                        @else
                                            <span class="text-muted small">Semua Area</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-muted">{{ $assignment->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-calendar-times fa-2x mb-2 text-light"></i>
                                        <p class="mb-0">Belum ada jadwal penugasan security yang terdaftar.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>
