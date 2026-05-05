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

        <div class="card shadow-sm border-0 rounded-4 mt-3">
            <div class="card-header bg-white border-bottom border-light">
                <h3 class="card-title text-primary"><i class="fas fa-list"></i> Update Work Order Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No. Tiket</th>
                                <th>Pemohon</th>
                                <th>Teknisi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentWO ?? [] as $wo)
                                <tr>
                                    <td><strong>#WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                    <td>{{ $wo->user->name ?? '-' }}</td>
                                    <td>{{ $wo->assignedEngineer->name ?? '-' }}</td>
                                    <td><span class="badge badge-info">{{ $wo->status }}</span></td>
                                    <td>
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>
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
