@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Super Admin</a></li>
    <li class="breadcrumb-item active">Laporan</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row mb-4 pt-2">
    <div>
        <h3 class="mb-1 font-weight-bold text-dark">Laporan Bulanan Kumulatif GA</h3>
        <p class="text-muted mb-0 font-weight-500">Analitik Work Order GA tahunan — tren bulanan masuk, selesai, dan ditutup via Handover.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center mt-3 mt-md-0 bg-white p-2 rounded shadow-sm border border-light w-100 w-md-auto">
        <form action="{{ route('super-admin.reports') }}" method="GET" class="d-flex align-items-center flex-grow-1 mr-md-2 mb-2 mb-md-0">
            <div class="input-group input-group-sm w-100">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-0"><i class="far fa-calendar-alt text-muted"></i></span>
                </div>
                <select name="year" class="form-control form-control-sm border-0 bg-light font-weight-600" onchange="this.form.submit()" style="border-radius: 0 4px 4px 0; min-width: 90px;">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ (int) $selectedYear === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div class="d-flex w-100 w-md-auto justify-content-center" style="gap: 8px;">
            <a href="{{ route('super-admin.reports.export', ['year' => $selectedYear]) }}"
               class="btn btn-success btn-sm flex-grow-1 d-flex align-items-center justify-content-center shadow-sm px-3"
               style="border-radius: 0.5rem; font-weight: 500;">
                <i class="fas fa-file-excel mr-2"></i> Excel
            </a>
            <a href="{{ route('super-admin.reports.export.pdf', ['year' => $selectedYear]) }}"
               class="btn btn-danger btn-sm flex-grow-1 d-flex align-items-center justify-content-center shadow-sm px-3"
               style="border-radius: 0.5rem; font-weight: 500;">
                <i class="fas fa-file-pdf mr-2"></i> PDF
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="support-stat-card">
            <small class="text-muted d-block">Total Work Order {{ $selectedYear }}</small>
            <div class="support-stat-value">{{ number_format($reportSummary['total_tiket_tahun']) }}</div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="support-stat-card">
            <small class="text-muted d-block">WO Selesai</small>
            <div class="support-stat-value text-success">{{ number_format($reportSummary['total_selesai_tahun']) }}</div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="support-stat-card">
            <small class="text-muted d-block">WO Ditutup (Handover)</small>
            <div class="support-stat-value text-info">{{ number_format($reportSummary['total_ditutup_tahun']) }}</div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="support-stat-card">
            <small class="text-muted d-block">Kumulatif Akhir Tahun</small>
            <div class="support-stat-value text-primary">{{ number_format($reportSummary['kumulatif_akhir_tahun']) }}</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card support-shell-card mb-4">
            <div class="card-header border-0 bg-white pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.15rem;">Tren Work Order GA ({{ $selectedYear }})</h3>
                    <small class="text-muted d-block mt-1">WO Masuk vs Selesai vs Ditutup (Handover) vs Kumulatif</small>
                </div>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="position-relative w-100" style="height: 340px;">
                    <canvas id="monthlyInsightChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card support-shell-card h-100">
            <div class="card-header border-0 bg-white pt-4 px-4 pb-2">
                <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.15rem;">Performa Teknisi (Work Orders)</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4 border-bottom-0">Nama Teknisi</th>
                                <th class="border-bottom-0 text-center">Total Tugas</th>
                                <th class="border-bottom-0 text-center">Selesai</th>
                                <th class="pr-4 border-bottom-0 text-right">Rasio Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($developerPerformance as $dev)
                                <tr>
                                    <td class="pl-4 font-weight-600 text-dark">{{ $dev->name }}</td>
                                    <td class="text-center">{{ $dev->total_projects }}</td>
                                    <td class="text-center font-weight-500 text-success">{{ $dev->completed_projects }}</td>
                                    <td class="pr-4 text-right">
                                        @if($dev->total_projects > 0)
                                            @php $ratio = ($dev->completed_projects / $dev->total_projects) * 100; @endphp
                                            <span class="badge {{ $ratio >= 80 ? 'badge-success' : ($ratio >= 50 ? 'badge-warning' : 'badge-danger') }} px-2 py-1">
                                                {{ number_format($ratio, 1) }}%
                                            </span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1">0%</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card support-shell-card h-100">
            <div class="card-header border-0 bg-white pt-4 px-4 pb-2">
                <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.15rem;">Klien Teraktif</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4 border-bottom-0">Klien</th>
                                <th class="pr-4 border-bottom-0 text-right">Total Permintaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clientActivity as $client)
                                <tr>
                                    <td class="pl-4 font-weight-600 text-dark">{{ $client->name }}</td>
                                    <td class="pr-4 text-right font-weight-500 text-primary">{{ $client->project_requests_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted">Belum ada data klien.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('monthlyInsightChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthLabels->values()) !!},
            datasets: [
                {
                    label: 'WO Masuk',
                    data: {!! json_encode($monthlyIncomingSeries->values()) !!},
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                },
                {
                    label: 'WO Selesai',
                    data: {!! json_encode($monthlyResolvedSeries->values()) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false,
                },
                {
                    label: 'WO Ditutup (Handover)',
                    data: {!! json_encode($monthlyClosedSeries->values()) !!},
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.08)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false,
                },
                {
                    label: 'Kumulatif WO Masuk',
                    data: {!! json_encode($monthlyCumulativeSeries->values()) !!},
                    borderColor: '#1f2d3d', /* Theme Dark */
                    backgroundColor: 'rgba(31, 45, 61, 0.08)',
                    borderWidth: 2,
                    borderDash: [6, 4],
                    tension: 0.3,
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
