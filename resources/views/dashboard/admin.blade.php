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

        {{-- 1. PEMBERITAHUAN WORK ORDER: MENUNGGU PENUGASAN TEKNISI --}}
        @if(isset($pendingAssignmentWOs) && $pendingAssignmentWOs->count() > 0)
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-left: 5px solid #ef4444 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fas fa-user-plus fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-danger mb-0">Tindakan Admin: {{ $pendingAssignmentWOs->count() }} Work Order Baru Menunggu Penugasan</h5>
                            <small class="text-muted">Permintaan perbaikan/pekerjaan dari pemohon telah masuk dan memerlukan penunjukan teknisi pelaksana.</small>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($pendingAssignmentWOs as $unassignedWO)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-3 bg-white shadow-none h-100" style="border-color: #fca5a5 !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-danger font-weight-bold" style="font-size: 1rem;">
                                                <i class="fas fa-tools mr-1"></i> #WO-{{ str_pad($unassignedWO->id, 4, '0', STR_PAD_LEFT) }}
                                            </strong>
                                            <span class="badge badge-danger">Belum Ditugaskan</span>
                                        </div>

                                        <p class="text-dark small mb-2 text-truncate" title="{{ $unassignedWO->description }}">
                                            <strong>Keluhan:</strong> {{ $unassignedWO->description }}
                                        </p>

                                        <div class="small text-muted mb-2">
                                            <div><i class="fas fa-user mr-1"></i> Pemohon: <strong>{{ $unassignedWO->user->name ?? '-' }}</strong> ({{ $unassignedWO->department }})</div>
                                            <div><i class="fas fa-clock mr-1"></i> Diajukan: {{ $unassignedWO->created_at->diffForHumans() }}</div>
                                        </div>

                                        <div class="d-flex justify-content-end align-items-center pt-2 border-top">
                                            <a href="{{ route('work-orders.show', $unassignedWO->id) }}" class="btn btn-danger btn-sm rounded-pill px-3 font-weight-bold shadow-sm">
                                                <i class="fas fa-user-check mr-1"></i> Tugaskan Teknisi
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

        {{-- 2. PEMBERITAHUAN WORK ORDER: HASIL PENGECEKAN TEKNISI (FORM B) MENUNGGU APPROVAL --}}
        @if(isset($pendingCheckApprovalWOs) && $pendingCheckApprovalWOs->count() > 0)
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fas fa-clipboard-check fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-dark mb-0">Tindakan Admin: {{ $pendingCheckApprovalWOs->count() }} Hasil Cek Teknisi (Form B) Menunggu Approval</h5>
                            <small class="text-muted">Teknisi telah menyelesaikan investigasi lapangan dan mengajukan analisa/kebutuhan material untuk diverifikasi.</small>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($pendingCheckApprovalWOs as $chkWO)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-3 bg-white shadow-none h-100" style="border-color: #fde68a !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-primary font-weight-bold" style="font-size: 1rem;">
                                                <i class="fas fa-clipboard-list mr-1"></i> #WO-{{ str_pad($chkWO->id, 4, '0', STR_PAD_LEFT) }}
                                            </strong>
                                            <span class="badge badge-warning text-dark">Menunggu Approval Cek</span>
                                        </div>

                                        <div class="small text-muted mb-2">
                                            <div><i class="fas fa-user-ninja mr-1"></i> Teknisi: <strong>{{ $chkWO->assignedEngineer->name ?? '-' }}</strong></div>
                                            <div><i class="fas fa-building mr-1"></i> Lokasi: {{ $chkWO->department }}</div>
                                        </div>

                                        @if($chkWO->check_result)
                                            <div class="p-2 mb-2 rounded small bg-light text-dark font-italic">
                                                <strong>Hasil Cek:</strong> "{{ $chkWO->check_result }}"
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-end align-items-center pt-2 border-top">
                                            <a href="{{ route('work-orders.show', $chkWO->id) }}" class="btn btn-warning btn-sm text-dark font-weight-bold rounded-pill px-3 shadow-sm">
                                                <i class="fas fa-check-circle mr-1"></i> Review &amp; Setujui Form B
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

        {{-- 3. PEMBERITAHUAN WORK ORDER: MENUNGGU PENYIAPAN BERITA ACARA SERAH TERIMA --}}
        @if(isset($pendingHandoverPrepWOs) && $pendingHandoverPrepWOs->count() > 0)
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-left: 5px solid #10b981 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fas fa-file-signature fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-success mb-0">Tindakan Admin: {{ $pendingHandoverPrepWOs->count() }} Pekerjaan Selesai Menunggu Berita Acara (Form D)</h5>
                            <small class="text-muted">Teknisi telah menyelesaikan perbaikan. Silakan buat Berita Acara Serah Terima untuk diserahkan ke user.</small>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($pendingHandoverPrepWOs as $hoWO)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-3 bg-white shadow-none h-100" style="border-color: #6ee7b7 !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-dark font-weight-bold">
                                                #WO-{{ str_pad($hoWO->id, 4, '0', STR_PAD_LEFT) }}
                                            </strong>
                                            <span class="badge badge-success">Siap Serah Terima</span>
                                        </div>

                                        <p class="text-dark small mb-2 text-truncate">
                                            <strong>Pekerjaan:</strong> {{ $hoWO->description }}
                                        </p>

                                        <div class="small text-muted mb-2">
                                            <div><i class="fas fa-user mr-1"></i> Pemohon: <strong>{{ $hoWO->user->name ?? '-' }}</strong></div>
                                            <div><i class="fas fa-user-ninja mr-1"></i> Teknisi: <strong>{{ $hoWO->assignedEngineer->name ?? '-' }}</strong></div>
                                        </div>

                                        <div class="d-flex justify-content-end align-items-center pt-2 border-top">
                                            <a href="{{ route('handovers.show', $hoWO->id) }}" class="btn btn-success btn-sm rounded-pill px-3 font-weight-bold shadow-sm">
                                                <i class="fas fa-edit mr-1"></i> Siapkan Berita Acara
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

        {{-- 4. PEMBERITAHUAN PENGAJUAN REVISI ATK/RT MENUNGGU VERIFIKASI ADMIN GA --}}
        @if(isset($pendingRevisionAtkGA) && $pendingRevisionAtkGA->count() > 0)
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 5px solid #2563eb !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fas fa-history fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-primary mb-0">Pemberitahuan: Ada {{ $pendingRevisionAtkGA->count() }} Dokumen Revisi ATK/RT Siap Diverifikasi GA</h5>
                            <small class="text-muted">Dokumen revisi ini telah disetujui oleh Manager Operasional dan sekarang memerlukan evaluasi akhir dari Admin GA.</small>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($pendingRevisionAtkGA as $revGA)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-3 bg-white shadow-none h-100" style="border-color: #bfdbfe !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong class="text-primary font-weight-bold" style="font-size: 1rem;">
                                                    <i class="fas fa-file-invoice mr-1"></i> {{ $revGA->formatted_doc_number }}
                                                </strong>
                                                <span class="badge badge-warning text-dark ml-1 font-weight-bold">Revisi Ke-{{ $revGA->revision_number }}</span>
                                            </div>
                                            <span class="badge badge-secondary">{{ $revGA->request_type }}</span>
                                        </div>

                                        <div class="small text-muted mb-2">
                                            <div><i class="fas fa-user mr-1"></i> Pemohon: <strong>{{ $revGA->nama }}</strong> ({{ $revGA->divisi }})</div>
                                            <div><i class="fas fa-calendar mr-1"></i> Periode: <strong>{{ $revGA->periode }}</strong></div>
                                            @if($revGA->parent)
                                                <div><i class="fas fa-link mr-1"></i> Asal Dokumen: <strong>{{ $revGA->parent->formatted_doc_number }}</strong></div>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                                            <a href="{{ route('atk-requests.show', $revGA->id) }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm font-weight-bold">
                                                <i class="fas fa-check-double mr-1"></i> Verifikasi GA
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

        <div class="row">
            {{-- Update Work Order Terbaru --}}
            <div class="col-lg-6 mb-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center py-3">
                        <h3 class="card-title text-primary font-weight-bold mb-0"><i class="fas fa-tools mr-1"></i> Update Work Order Terbaru</h3>
                        <a href="{{ route('work-orders.index') }}" class="btn btn-outline-primary btn-xs rounded-pill px-3">Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th>No. Tiket</th>
                                        <th>Pemohon</th>
                                        <th>Status</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentWO ?? [] as $wo)
                                        <tr>
                                            <td><strong>#WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                            <td>{{ $wo->user->name ?? '-' }}</td>
                                            <td><span class="badge badge-info">{{ $wo->status }}</span></td>
                                            <td class="text-right">
                                                <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3 small">Belum ada data Work Order.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Update Permintaan ATK / Rumah Tangga Terbaru --}}
            <div class="col-lg-6 mb-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center py-3">
                        <h3 class="card-title text-primary font-weight-bold mb-0"><i class="fas fa-boxes mr-1"></i> Permintaan ATK / Rumah Tangga</h3>
                        <a href="{{ route('atk-requests.index') }}" class="btn btn-outline-primary btn-xs rounded-pill px-3">Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th>No. Dokumen</th>
                                        <th>Pemohon</th>
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
                                            </td>
                                            <td>{{ $atk->nama }} <span class="text-muted small">({{ $atk->divisi }})</span></td>
                                            <td>
                                                @if($atk->status_approval === 'Disetujui')
                                                    <span class="badge badge-success">Disetujui</span>
                                                @elseif($atk->status_approval === 'Ditolak/Revisi')
                                                    <span class="badge badge-danger">Ditolak/Revisi</span>
                                                @elseif($atk->status_approval === 'Menunggu Persetujuan Manager')
                                                    <span class="badge badge-warning text-dark">Review Mgr</span>
                                                @elseif($atk->status_approval === 'Menunggu Persetujuan Admin GA')
                                                    <span class="badge badge-info">Review GA</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $atk->status_approval }}</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('atk-requests.show', $atk->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3 small">Belum ada data pengajuan ATK / RT.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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
