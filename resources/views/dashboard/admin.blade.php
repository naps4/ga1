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
            <div class="col-md-6 mb-3">
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
            <div class="col-md-6 mb-3">
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
    </div>
</section>
