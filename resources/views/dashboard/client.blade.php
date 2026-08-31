<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 text-dark font-weight-bold">Dashboard User / Staff</h1>
                <p class="text-muted mb-0 small">Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong></p>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <a href="{{ route('atk-requests.create') }}" class="btn btn-primary btn-sm rounded-pill shadow-sm">
                    <i class="fas fa-boxes mr-1"></i> Ajukan ATK / RT
                </a>
                <a href="{{ route('work-orders.create') }}" class="btn btn-outline-primary btn-sm rounded-pill shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Buat Work Order
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        {{-- 1. STATS WIDGETS (4 KOTAK METRIK UTAMA DI ATAS) --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info rounded-4 shadow-sm border-0">
                    <div class="inner p-3">
                        <h3 class="font-weight-bold">{{ $activeTickets ?? 0 }}</h3>
                        <p class="mb-0">Work Order Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <a href="{{ route('work-orders.index') }}" class="small-box-footer bg-dark-50 py-1">Lihat WO <i class="fas fa-arrow-circle-right ml-1"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary rounded-4 shadow-sm border-0">
                    <div class="inner p-3">
                        <h3 class="font-weight-bold">{{ $myTickets ?? 0 }}</h3>
                        <p class="mb-0">Total Work Order</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <a href="{{ route('work-orders.index') }}" class="small-box-footer bg-dark-50 py-1">Semua WO <i class="fas fa-arrow-circle-right ml-1"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning rounded-4 shadow-sm border-0 text-dark">
                    <div class="inner p-3">
                        <h3 class="font-weight-bold text-dark">{{ $myAtkActive ?? 0 }}</h3>
                        <p class="mb-0 text-dark">Permintaan ATK/RT Diproses</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <a href="{{ route('atk-requests.index') }}" class="small-box-footer text-dark py-1" style="background: rgba(0,0,0,0.08);">Lihat Permintaan <i class="fas fa-arrow-circle-right ml-1"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success rounded-4 shadow-sm border-0">
                    <div class="inner p-3">
                        <h3 class="font-weight-bold">{{ $myAtkTotal ?? 0 }}</h3>
                        <p class="mb-0">Total Permintaan ATK/RT</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <a href="{{ route('atk-requests.index') }}" class="small-box-footer bg-dark-50 py-1">Semua Pengajuan <i class="fas fa-arrow-circle-right ml-1"></i></a>
                </div>
            </div>
        </div>

        {{-- PEMBERITAHUAN WORK ORDER SELESAI - KONFIRMASI SERAH TERIMA & RATING --}}
        @if(isset($pendingHandoverWOs) && $pendingHandoverWOs->count() > 0)
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-left: 5px solid #10b981 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fas fa-clipboard-check fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-success mb-0">Pekerjaan Selesai: Konfirmasi Serah Terima &amp; Penilaian</h5>
                            <small class="text-muted">Teknisi telah menyelesaikan pekerjaan untuk <strong>{{ $pendingHandoverWOs->count() }}</strong> Work Order Anda. Silakan periksa hasil pekerjaan dan berikan penilaian.</small>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($pendingHandoverWOs as $woItem)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-3 bg-white shadow-none h-100" style="border-color: #6ee7b7 !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-dark font-weight-bold" style="font-size: 1rem;">
                                                <i class="fas fa-tools text-primary mr-1"></i> #WO-{{ str_pad($woItem->id, 4, '0', STR_PAD_LEFT) }}
                                            </strong>
                                            <span class="badge badge-success">Menunggu Konfirmasi Anda</span>
                                        </div>

                                        <p class="text-dark small mb-2 text-truncate" title="{{ $woItem->description }}">
                                            <strong>Keluhan/Pekerjaan:</strong> {{ $woItem->description }}
                                        </p>

                                        <div class="small text-muted mb-3">
                                            <div><i class="fas fa-user-ninja mr-1"></i> Teknisi Pelaksana: <strong>{{ $woItem->assignedEngineer->name ?? '-' }}</strong></div>
                                            @if($woItem->engineer_report)
                                                <div class="mt-1 font-italic text-dark bg-light p-2 rounded small">
                                                    <strong>Laporan Teknisi:</strong> "{{ $woItem->engineer_report }}"
                                                </div>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-end align-items-center pt-2 border-top">
                                            <a href="{{ route('handovers.show', $woItem->id) }}" class="btn btn-success btn-sm rounded-pill px-3 font-weight-bold shadow-sm">
                                                <i class="fas fa-star mr-1"></i> Konfirmasi &amp; Beri Rating
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- 2. ALERT PEMBERITAHUAN REVISI ATK / RUMAH TANGGA --}}
        @if(isset($rejectedAtkList) && $rejectedAtkList->count() > 0)
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef2f2 100%); border-left: 5px solid #ef4444 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-danger mb-0">Pemberitahuan Revisi Permintaan Barang</h5>
                            <small class="text-muted">Terdapat <strong>{{ $rejectedAtkList->count() }}</strong> pengajuan yang ditolak atau membutuhkan revisi sebelum dapat diproses lebih lanjut.</small>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($rejectedAtkList as $rejReq)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-3 bg-white shadow-none h-100" style="border-color: #fca5a5 !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong class="text-danger font-weight-bold" style="font-size: 1rem;">
                                                    <i class="fas fa-file-invoice mr-1"></i> {{ $rejReq->formatted_doc_number }}
                                                </strong>
                                                <span class="badge badge-secondary ml-1 small">{{ $rejReq->request_type }}</span>
                                            </div>
                                            <span class="badge badge-danger">Ditolak / Perlu Revisi</span>
                                        </div>

                                        @php
                                            $evalNotes = $rejReq->manager_notes ?? $rejReq->notes;
                                            $rejectedItems = $rejReq->items->where('status_approval', 'Ditolak');
                                        @endphp

                                        @if($evalNotes)
                                            <div class="p-2 mb-2 rounded small text-danger font-italic" style="background-color: #fff1f2; border-left: 3px solid #f87171;">
                                                <i class="fas fa-comment-dots mr-1"></i> "{{ $evalNotes }}"
                                            </div>
                                        @endif

                                        @if($rejectedItems->count() > 0)
                                            <div class="small text-muted mb-2">
                                                <strong>Item yang ditolak:</strong>
                                                <ul class="pl-3 mb-0 mt-1">
                                                    @foreach($rejectedItems as $rItem)
                                                        <li>
                                                            <strong>{{ $rItem->item_name }}</strong> (Qty: {{ $rItem->quantity }} {{ $rItem->unit }})
                                                            @if($rItem->notes)
                                                                <span class="text-danger font-italic">- {{ $rItem->notes }}</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                            <small class="text-muted"><i class="fas fa-calendar-alt mr-1"></i> Periode: {{ $rejReq->periode }}</small>
                                            <a href="{{ route('atk-requests.show', $rejReq->id) }}" class="btn btn-warning btn-sm text-dark font-weight-bold rounded-pill px-3 shadow-sm">
                                                <i class="fas fa-edit mr-1"></i> Buka &amp; Revisi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- 3. ALERT STATUS REVISI SEDANG BERJALAN --}}
        @if(isset($inProgressRevisions) && $inProgressRevisions->count() > 0)
            <div class="alert alert-info border-0 rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between shadow-sm" style="background: #eff6ff; border-left: 4px solid #3b82f6 !important;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-primary fa-2x mr-3"></i>
                    <div>
                        <h6 class="font-weight-bold text-primary mb-1">Status Pengajuan Revisi Sedang Diproses</h6>
                        <span class="text-muted small">
                            Anda memiliki <strong>{{ $inProgressRevisions->count() }}</strong> pengajuan revisi yang sedang ditinjau ulang:
                            @foreach($inProgressRevisions as $ipRev)
                                <a href="{{ route('atk-requests.show', $ipRev->id) }}" class="badge badge-primary font-weight-bold mx-1">{{ $ipRev->formatted_doc_number }} ({{ $ipRev->status_approval }})</a>
                            @endforeach
                        </span>
                    </div>
                </div>
                <a href="{{ route('atk-requests.index') }}" class="btn btn-outline-primary btn-sm rounded-pill font-weight-bold">
                    Lihat Semua Permintaan
                </a>
            </div>
        @endif

        {{-- 4. TABLES: ATK REQUESTS & WORK ORDERS --}}
        <div class="row">
            {{-- Tabel Permintaan ATK & Rumah Tangga --}}
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center py-3">
                        <h3 class="card-title text-primary font-weight-bold mb-0">
                            <i class="fas fa-boxes mr-1"></i> Pengajuan ATK / Rumah Tangga
                        </h3>
                        <a href="{{ route('atk-requests.index') }}" class="btn btn-outline-primary btn-xs rounded-pill px-3">
                            Semua
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th>No. Dokumen</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAtk ?? [] as $atk)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $atk->formatted_doc_number }}</strong>
                                                @if($atk->revision_number > 0)
                                                    <span class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 9px;">Rev. {{ $atk->revision_number }}</span>
                                                @endif
                                                <div class="small text-muted">{{ $atk->request_type }}</div>
                                            </td>
                                            <td><span class="badge badge-info">{{ $atk->periode }}</span></td>
                                            <td>
                                                @if($atk->status_approval === 'Disetujui')
                                                    <span class="badge badge-success">Disetujui</span>
                                                @elseif($atk->status_approval === 'Ditolak/Revisi')
                                                    <span class="badge badge-danger">Ditolak/Revisi</span>
                                                @elseif($atk->status_approval === 'Menunggu Persetujuan Manager')
                                                    <span class="badge badge-warning text-dark">Review Manager</span>
                                                @elseif($atk->status_approval === 'Menunggu Persetujuan Admin GA')
                                                    <span class="badge badge-info">Review GA</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $atk->status_approval }}</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('atk-requests.show', $atk->id) }}" class="btn btn-sm btn-light border rounded-pill px-2" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($atk->status_approval === 'Ditolak/Revisi' || $atk->items->where('status_approval', 'Ditolak')->count() > 0)
                                                    <a href="{{ route('atk-requests.show', $atk->id) }}" class="btn btn-sm btn-warning text-dark font-weight-bold rounded-pill px-2" title="Revisi">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4 small">Belum ada pengajuan ATK / Rumah Tangga.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Work Orders --}}
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center py-3">
                        <h3 class="card-title text-primary font-weight-bold mb-0">
                            <i class="fas fa-tools mr-1"></i> Riwayat Work Order
                        </h3>
                        <a href="{{ route('work-orders.index') }}" class="btn btn-outline-primary btn-xs rounded-pill px-3">
                            Semua
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th>No. Tiket</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentWO ?? [] as $wo)
                                        <tr>
                                            <td><strong>#WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                            <td>{{ $wo->created_at->format('d/m/Y H:i') }}</td>
                                            <td><span class="badge badge-primary">{{ $wo->status }}</span></td>
                                            <td class="text-right">
                                                <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-3">Detail</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4 small">Belum ada pengajuan Work Order.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
