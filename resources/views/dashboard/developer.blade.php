<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 text-dark">Dashboard Teknisi (Engineer)</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6 col-6">
                <div class="small-box bg-warning rounded-4 shadow-sm">
                    <div class="inner">
                        <h3>{{ $pendingChecks ?? 0 }}</h3>
                        <p>Menunggu Pengecekan Saya</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-6">
                <div class="small-box bg-info rounded-4 shadow-sm">
                    <div class="inner">
                        <h3>{{ $myTasks ?? 0 }}</h3>
                        <p>Total Tugas Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mt-3">
            <div class="card-header bg-white border-bottom border-light">
                <h3 class="card-title text-primary"><i class="fas fa-hard-hat"></i> Daftar Tugas Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No. Tiket</th>
                                <th>Lokasi/Dept</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTasks ?? [] as $wo)
                                <tr>
                                    <td>#WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $wo->department }}</td>
                                    <td><span class="badge badge-warning">{{ $wo->status }}</span></td>
                                    <td>
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">Kerjakan</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Belum ada tugas saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
