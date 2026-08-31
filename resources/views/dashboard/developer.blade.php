<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 text-dark font-weight-bold">Dashboard Teknisi (Engineer)</h1>
                <p class="text-muted mb-0 small">Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong></p>
            </div>
            <span class="badge badge-warning text-dark px-3 py-2 rounded-pill font-weight-bold">
                <i class="fas fa-hard-hat mr-1"></i> Teknisi / Maintenance
            </span>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        {{-- 1. STATS WIDGETS --}}
        <div class="row">
            <div class="col-lg-4 col-12">
                <div class="small-box bg-warning rounded-4 shadow-sm border-0 text-dark">
                    <div class="inner p-3">
                        <h3 class="font-weight-bold text-dark">{{ $pendingChecks ?? 0 }}</h3>
                        <p class="mb-0 text-dark">Menunggu Pengecekan Lapangan (Form B)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <a href="{{ route('work-orders.index') }}" class="small-box-footer text-dark py-1" style="background: rgba(0,0,0,0.08);">
                        Tinjau Tugas <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info rounded-4 shadow-sm border-0">
                    <div class="inner p-3">
                        <h3 class="font-weight-bold">{{ $activeWork ?? 0 }}</h3>
                        <p class="mb-0">Pekerjaan Dalam Pengerjaan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <a href="{{ route('work-orders.index') }}" class="small-box-footer bg-dark-50 py-1">
                        Lihat Pengerjaan <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-primary rounded-4 shadow-sm border-0">
                    <div class="inner p-3">
                        <h3 class="font-weight-bold">{{ $myTasks ?? 0 }}</h3>
                        <p class="mb-0">Total Tugas Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <a href="{{ route('work-orders.index') }}" class="small-box-footer bg-dark-50 py-1">
                        Semua Tugas <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- 2. ACTION SECTION: TUGAS PENGECEKAN LAPANGAN (FORM B) --}}
        @if(isset($pendingCheckTasks) && $pendingCheckTasks->count() > 0)
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fas fa-clipboard-list fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-dark mb-0">Tugas Baru: Pengecekan Lapangan &amp; Form B Menunggu Anda</h5>
                            <small class="text-muted">Ada <strong>{{ $pendingCheckTasks->count() }}</strong> tiket Work Order yang ditugaskan kepada Anda dan memerlukan investigasi/input hasil pengecekan.</small>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($pendingCheckTasks as $chkTask)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-3 bg-white shadow-none h-100" style="border-color: #fde68a !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-primary font-weight-bold" style="font-size: 1rem;">
                                                <i class="fas fa-wrench mr-1"></i> #WO-{{ str_pad($chkTask->id, 4, '0', STR_PAD_LEFT) }}
                                            </strong>
                                            @if($chkTask->rejection_note)
                                                <span class="badge badge-danger">Perlu Revisi Pengecekan</span>
                                            @else
                                                <span class="badge badge-warning text-dark">Belum Dicek</span>
                                            @endif
                                        </div>

                                        <p class="text-dark small mb-2 font-weight-600">
                                            {{ $chkTask->description }}
                                        </p>

                                        <div class="small text-muted mb-2">
                                            <div><i class="fas fa-user mr-1"></i> Pemohon: <strong>{{ $chkTask->user->name ?? '-' }}</strong> ({{ $chkTask->department }})</div>
                                            <div><i class="fas fa-clock mr-1"></i> Masuk: {{ $chkTask->created_at->diffForHumans() }}</div>
                                        </div>

                                        @if($chkTask->rejection_note)
                                            <div class="p-2 mb-2 rounded small text-danger font-italic" style="background-color: #fef2f2; border: 1px dashed #fca5a5;">
                                                <strong>Catatan Revisi Admin:</strong> "{{ $chkTask->rejection_note }}"
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-end align-items-center pt-2 border-top">
                                            <a href="{{ route('work-orders.show', $chkTask->id) }}" class="btn btn-warning btn-sm text-dark font-weight-bold rounded-pill px-3 shadow-sm">
                                                <i class="fas fa-edit mr-1"></i> {{ $chkTask->rejection_note ? 'Perbaiki Form B' : 'Input Hasil Cek (Form B)' }}
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

        {{-- 3. ACTION SECTION: PEKERJAAN YANG SEDANG BERJALAN --}}
        @if(isset($workingTasks) && $workingTasks->count() > 0)
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 5px solid #2563eb !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fas fa-cogs fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-primary mb-0">Pekerjaan Siap Dikerjakan &amp; Pelaporan</h5>
                            <small class="text-muted">Terdapat <strong>{{ $workingTasks->count() }}</strong> pekerjaan yang sedang berjalan. Tambahkan progress pekerjaan atau kirim laporan penyelesaian.</small>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($workingTasks as $workTask)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-3 bg-white shadow-none h-100" style="border-color: #bfdbfe !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-dark font-weight-bold">
                                                #WO-{{ str_pad($workTask->id, 4, '0', STR_PAD_LEFT) }}
                                            </strong>
                                            <span class="badge badge-info">{{ $workTask->status }}</span>
                                        </div>

                                        <p class="text-dark small mb-2 text-truncate" title="{{ $workTask->description }}">
                                            <strong>Pekerjaan:</strong> {{ $workTask->description }}
                                        </p>

                                        <div class="small text-muted mb-2">
                                            <div><i class="fas fa-building mr-1"></i> Dept: {{ $workTask->department }}</div>
                                            @if($workTask->fpuRequest)
                                                <div><i class="fas fa-file-invoice mr-1"></i> Status FPU: <span class="badge badge-light border">{{ $workTask->fpuRequest->status_approval }}</span></div>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-end align-items-center pt-2 border-top">
                                            <a href="{{ route('work-orders.show', $workTask->id) }}" class="btn btn-primary btn-sm rounded-pill px-3 font-weight-bold shadow-sm">
                                                <i class="fas fa-tasks mr-1"></i> Update Progress / Laporan
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

        {{-- 4. TABLE: RIWAYAT DAFTAR TUGAS TERBARU --}}
        <div class="card shadow-sm border-0 rounded-4 mt-3">
            <div class="card-header bg-white border-bottom border-light py-3 d-flex justify-content-between align-items-center">
                <h3 class="card-title text-primary font-weight-bold m-0"><i class="fas fa-hard-hat mr-1"></i> Daftar Semua Tugas Saya</h3>
                <a href="{{ route('work-orders.index') }}" class="btn btn-outline-primary btn-xs rounded-pill px-3">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-light text-muted small">
                            <tr>
                                <th>No. Tiket</th>
                                <th>Pemohon</th>
                                <th>Lokasi/Dept</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTasks ?? [] as $wo)
                                <tr>
                                    <td><strong>#WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                    <td>{{ $wo->user->name ?? '-' }}</td>
                                    <td>{{ $wo->department }}</td>
                                    <td>
                                        @if($wo->status === 'Pengecekan Engineer')
                                            <span class="badge badge-warning text-dark">Pengecekan</span>
                                        @elseif($wo->status === 'Sedang Dikerjakan')
                                            <span class="badge badge-info">Dikerjakan</span>
                                        @elseif($wo->status === 'Selesai')
                                            <span class="badge badge-success">Selesai</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $wo->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Buka Tiket</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4 small">Belum ada riwayat tugas saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
