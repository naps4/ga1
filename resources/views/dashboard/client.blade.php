<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 text-dark">Dashboard User / Staff</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6 col-6">
                <div class="small-box bg-info rounded-4 shadow-sm">
                    <div class="inner">
                        <h3>{{ $activeTickets ?? 0 }}</h3>
                        <p>Tiket Sedang Diproses</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-spinner"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-6">
                <div class="small-box bg-success rounded-4 shadow-sm">
                    <div class="inner">
                        <h3>{{ $myTickets ?? 0 }}</h3>
                        <p>Total Tiket Saya</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mt-3">
            <div class="card-header bg-white border-bottom border-light">
                <h3 class="card-title text-primary"><i class="fas fa-history"></i> Riwayat Pengajuan Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No. Tiket</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentWO ?? [] as $wo)
                                <tr>
                                    <td>#WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $wo->created_at->format('d M Y H:i') }}</td>
                                    <td><span class="badge badge-primary">{{ $wo->status }}</span></td>
                                    <td>
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-outline-info rounded-pill">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Belum ada pengajuan Work Order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
