@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0 text-dark">Laporan Monitoring Security Checkpoint</h1>
            <div class="d-flex align-items-center" style="gap: .75rem;">
                <a href="{{ route('security.export', request()->query()) }}" class="btn btn-primary rounded-pill shadow-sm">
                    <i class="fas fa-file-excel mr-1"></i> Ekspor ke Excel
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show rounded-pill shadow-sm" role="alert">
                <i class="fas fa-info-circle mr-1"></i> {{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Card Filter -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-3 bg-light rounded-4">
                <form action="{{ route('security.logs.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from" class="form-control border-light" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" class="form-control border-light" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2 col-sm-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Plant</label>
                            <select name="plant" class="form-control border-light">
                                <option value="">Semua Plant</option>
                                @foreach($existingPlants as $p)
                                    <option value="{{ $p }}" {{ request('plant') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Shift</label>
                            <select name="shift" class="form-control border-light">
                                <option value="">Semua Shift</option>
                                <option value="Shift 1 - Pagi" {{ request('shift') === 'Shift 1 - Pagi' ? 'selected' : '' }}>Shift 1 - Pagi</option>
                                <option value="Shift 2 - Sore" {{ request('shift') === 'Shift 2 - Sore' ? 'selected' : '' }}>Shift 2 - Sore</option>
                                <option value="Shift 3 - Malam" {{ request('shift') === 'Shift 3 - Malam' ? 'selected' : '' }}>Shift 3 - Malam</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Status Laporan</label>
                            <select name="status" class="form-control border-light">
                                <option value="">Semua Status</option>
                                <option value="Aman" {{ request('status') === 'Aman' ? 'selected' : '' }}>Aman</option>
                                <option value="Pintu Belum Ditutup" {{ request('status') === 'Pintu Belum Ditutup' ? 'selected' : '' }}>Pintu Belum Ditutup</option>
                                <option value="Listrik Belum Dimatikan" {{ request('status') === 'Listrik Belum Dimatikan' ? 'selected' : '' }}>Listrik Belum Dimatikan</option>
                                <option value="Temuan Lainnya" {{ request('status') === 'Temuan Lainnya' ? 'selected' : '' }}>Temuan Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-right">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm w-100">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Google Sheet style data log -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-clipboard-list text-info mr-1"></i> Log History & Recap Sheets</h3>
                <span class="badge badge-light border">{{ $logs->total() }} Log Ditemukan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: .88rem; min-width: 1000px;">
                        <thead class="bg-light text-muted uppercase">
                            <tr>
                                <th class="py-3 text-center" style="width: 50px;">No</th>
                                <th class="py-3">Nama Petugas</th>
                                <th class="py-3 text-center">Jadwal Shift</th>
                                <th class="py-3 text-center">Tanggal & Jam Check</th>
                                <th class="py-3">Plant</th>
                                <th class="py-3">Check Point Area</th>
                                <th class="py-3 text-center">Kode Barcode</th>
                                <th class="py-3 text-center">Durasi</th>
                                <th class="py-3 text-center">Status Area</th>
                                <th class="py-3">Laporan / Catatan Temuan</th>
                                <th class="py-3 text-center">Bukti Selfie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $index => $log)
                                <tr class="{{ $log->status !== 'Aman' ? 'table-warning' : '' }}">
                                    <td class="text-center font-weight-bold text-muted">{{ $logs->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $log->user->avatar_url }}" alt="avatar" class="rounded-circle mr-2" style="width: 28px; height: 28px; object-fit: cover;">
                                            <strong>{{ $log->user->name ?? '-' }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge badge-secondary px-2 py-1 rounded">{{ $log->shift }}</span></td>
                                    <td class="text-center">{{ $log->checked_at->format('d M Y H:i:s') }}</td>
                                    <td>
                                        @if($log->checkpoint)
                                            <span class="font-weight-600 text-dark">{{ $log->checkpoint->plant }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->is_evident_report)
                                            <span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Eviden Report</span>
                                        @elseif($log->checkpoint)
                                            <span class="font-weight-bold text-dark">{{ $log->checkpoint->area_name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($log->checkpoint)
                                            <code class="bg-light px-2 py-0.5 rounded" style="font-size: .8rem;">{{ $log->checkpoint->code }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold text-primary">{{ $log->duration_minutes }} Min</td>
                                    <td class="text-center">
                                        @php
                                            $badgeClass = 'success';
                                            if ($log->status === 'Pintu Belum Ditutup') $badgeClass = 'warning';
                                            elseif ($log->status === 'Listrik Belum Dimatikan') $badgeClass = 'warning';
                                            elseif ($log->status === 'Temuan Lainnya') $badgeClass = 'danger';
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }} px-2 py-1" style="font-size: 0.78rem;">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td>{{ $log->notes ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($log->selfie_path)
                                            <a href="{{ asset('storage/' . $log->selfie_path) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2">
                                                <i class="fas fa-image mr-1"></i> Lihat Foto
                                            </a>
                                        @else
                                            <span class="text-muted small">Tanpa Selfie</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-light"></i>
                                        <p class="mb-0">Belum ada log checkpoint security untuk periode filter ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="float-right">
                        {{ $logs->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
