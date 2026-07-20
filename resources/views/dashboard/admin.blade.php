<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 text-dark">Dashboard Admin HRGA & Management</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger rounded-4 shadow-sm">
                    <div class="inner">
                        <h3>{{ $pendingAssignment ?? 0 }}</h3>
                        <p>Menunggu Penugasan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <a href="{{ route('work-orders.index') }}" class="small-box-footer">Lihat Tiket <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning rounded-4 shadow-sm">
                    <div class="inner">
                        <h3>{{ $pendingFpu ?? 0 }}</h3>
                        <p>FPU Menunggu Approval</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <a href="{{ route('work-orders.index') }}" class="small-box-footer">Kelola FPU <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info rounded-4 shadow-sm">
                    <div class="inner">
                        <h3>{{ $activeWO ?? 0 }}</h3>
                        <p>Work Order Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <a href="{{ route('work-orders.index') }}" class="small-box-footer">Semua WO Aktif <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success rounded-4 shadow-sm">
                    <div class="inner">
                        <h3>{{ $totalWO ?? 0 }}</h3>
                        <p>Total Work Order</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <a href="{{ route('super-admin.reports') }}" class="small-box-footer">Lihat Laporan <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Security Checkpoint Summary Row -->
        <div class="row">
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
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success-light text-success d-flex align-items-center justify-content-center mr-3" style="width: 42px; height: 42px; background-color: rgba(16, 185, 129, 0.1);">
                                    <i class="fas fa-user-clock fa-md"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0 small font-weight-bold" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">On Duty Hari Ini</h6>
                                    <h5 class="mb-0 font-weight-bold text-success">{{ count($todayAssignments ?? []) }} Petugas Aktif</h5>
                                </div>
                            </div>
                            <a href="{{ route('security.assignments.index') }}" class="btn btn-xs btn-success rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                Detail <i class="fas fa-chevron-right ml-0.5" style="font-size: 0.65rem;"></i>
                            </a>
                        </div>
                        
                        <div class="mt-2 border-top pt-2" style="max-height: 120px; overflow-y: auto;">
                            @forelse($todayAssignments ?? [] as $assignment)
                                <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 0.8rem; line-height: 1.4;">
                                    <div class="d-flex align-items-center" style="max-width: 60%; min-width: 0;">
                                        <i class="fas fa-user-shield text-success mr-2" style="font-size: 0.75rem; flex-shrink: 0;"></i>
                                        <span class="font-weight-bold text-dark text-truncate" title="{{ $assignment->user->name ?? '-' }}">{{ $assignment->user->name ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-end" style="max-width: 40%; min-width: 0; flex-shrink: 0;">
                                        @if($assignment->plant)
                                            <span class="badge badge-light border text-muted py-0.5 px-1.5 font-weight-normal text-truncate mr-1" style="font-size: 0.65rem;" title="{{ $assignment->plant }}">
                                                <i class="fas fa-industry mr-0.5" style="font-size: 0.6rem;"></i>{{ $assignment->plant }}
                                            </span>
                                        @endif
                                        @if($assignment->shift)
                                            <span class="badge badge-primary py-0.5 px-1.5 font-weight-normal text-truncate" style="font-size: 0.65rem;" title="{{ $assignment->shift->name }}">
                                                {{ $assignment->shift->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-2 small" style="font-size: 0.78rem;">
                                    Tidak ada petugas aktif hari ini
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mt-2">
            <div class="card-header bg-white border-bottom border-light">
                <h3 class="card-title text-primary"><i class="fas fa-list"></i> Update Work Order Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No. Tiket</th>
                                <th class="d-none d-sm-table-cell">Pemohon</th>
                                <th class="d-none d-md-table-cell">Teknisi</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentWO ?? [] as $wo)
                                <tr>
                                    <td><strong>#WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                    <td class="d-none d-sm-table-cell">{{ $wo->user->name ?? '-' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $wo->assignedEngineer->name ?? '-' }}</td>
                                    <td><span class="badge badge-info" style="white-space: normal; text-align: center; display: inline-block;">{{ $wo->status }}</span></td>
                                    <td class="text-right">
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-eye"></i> <span class="d-none d-sm-inline">Detail</span></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Belum ada data Work Order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Security Shift & Penugasan Keseluruhan -->
        <div class="card shadow-sm border-0 rounded-4 mt-4">
            <div class="card-header bg-white border-bottom border-light py-3 d-flex align-items-center justify-content-between">
                <h3 class="card-title text-info m-0 font-weight-bold">
                    <i class="fas fa-calendar-check mr-1"></i> Jadwal & Penugasan Security Keseluruhan
                </h3>
                <a href="{{ route('security.assignments.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                    Kelola Penjadwalan
                </a>
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
