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
        {{-- PEMBERITAHUAN PENGAJUAN REVISI BARU MENUNGGU PERSETUJUAN --}}
        @if(isset($pendingRevisionAtkList) && $pendingRevisionAtkList->count() > 0)
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fas fa-history fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-dark mb-0">Pemberitahuan: Ada {{ $pendingRevisionAtkList->count() }} Pengajuan Revisi Baru Menunggu Persetujuan Anda</h5>
                            <small class="text-muted">Staf pemohon telah memperbaiki item pengajuan yang sebelumnya ditolak dan mengajukan ulang untuk dievaluasi kembali.</small>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($pendingRevisionAtkList as $revReq)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-3 bg-white shadow-none h-100" style="border-color: #fde68a !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong class="text-primary font-weight-bold" style="font-size: 1rem;">
                                                    <i class="fas fa-file-alt mr-1"></i> {{ $revReq->formatted_doc_number }}
                                                </strong>
                                                <span class="badge badge-warning text-dark ml-1 font-weight-bold">Revisi Ke-{{ $revReq->revision_number }}</span>
                                            </div>
                                            <span class="badge badge-secondary">{{ $revReq->request_type }}</span>
                                        </div>

                                        <div class="small text-muted mb-2">
                                            <div><i class="fas fa-user mr-1"></i> Pemohon: <strong>{{ $revReq->nama }}</strong> ({{ $revReq->divisi }})</div>
                                            <div><i class="fas fa-calendar mr-1"></i> Periode: <strong>{{ $revReq->periode }}</strong></div>
                                            @if($revReq->parent)
                                                <div><i class="fas fa-link mr-1"></i> Dokumen Asal: <strong>{{ $revReq->parent->formatted_doc_number }}</strong></div>
                                            @endif
                                        </div>

                                        @php
                                            $revisedItems = $revReq->items->where('is_revised', true);
                                        @endphp
                                        @if($revisedItems->count() > 0)
                                            <div class="p-2 mb-2 rounded small" style="background-color: #fffbeb; border: 1px dashed #fcd34d;">
                                                <strong>Item yang disesuaikan pemohon:</strong>
                                                <ul class="pl-3 mb-0 mt-1">
                                                    @foreach($revisedItems as $revItem)
                                                        <li>
                                                            <strong>{{ $revItem->item_name }}</strong>: 
                                                            @if($revItem->previous_quantity)
                                                                <del class="text-danger">{{ $revItem->previous_quantity }}</del> &rarr;
                                                            @endif
                                                            <span class="text-success font-weight-bold">{{ $revItem->quantity }} {{ $revItem->unit }}</span>
                                                            @if($revItem->description)
                                                                <span class="text-muted font-italic">({{ $revItem->description }})</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                                            <a href="{{ route('atk-requests.show', $revReq->id) }}" class="btn btn-warning btn-sm text-dark font-weight-bold rounded-pill px-4 shadow-sm">
                                                <i class="fas fa-check-circle mr-1"></i> Evaluasi &amp; Setujui
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
                            <span class="badge badge-success rounded-pill px-2 py-1 small" style="font-size: 0.7rem;">Aktif</span>
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

        <!-- Recent ATK Requests Summary Card -->
        <div class="card shadow-sm border-0 rounded-4 mt-3">
            <div class="card-header bg-white border-bottom border-light py-3 d-flex align-items-center justify-content-between">
                <h3 class="card-title text-primary m-0 font-weight-bold">
                    <i class="fas fa-clipboard-list mr-1"></i> Kesimpulan Permintaan ATK / Rumah Tangga
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
                                        {{ $req->formatted_doc_number }}
                                        @if($req->revision_number > 0)
                                            <span class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 10px;">
                                                Rev. {{ $req->revision_number }}
                                            </span>
                                        @endif
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
                                            <a href="{{ route('atk-requests.show', $req->id) }}" class="btn btn-sm btn-warning rounded-pill px-3 shadow-xs font-weight-bold">
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
