@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-industry text-primary mr-2"></i> Inventaris Operasional {{ $plant->name }} (Kode: {{ $plant->code }})
                </h1>
                <small class="text-muted">PT Herbatech Innopharma Industry — Rekapitulasi Lokasi Fisik Terkini</small>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mr-1">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Master
                </a>
                <a href="{{ route('assets.plant', $plant->code === '01' ? '02' : '01') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-exchange-alt mr-1"></i> Buka Plant {{ $plant->code === '01' ? 'B' : 'A' }}
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- KPI Cards Per Plant --}}
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0" style="border-radius: 1rem;">
                    <span class="info-box-icon bg-primary elevation-1" style="border-radius: .75rem;"><i class="fas fa-cubes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted font-weight-bold">Total Aset di {{ $plant->name }}</span>
                        <span class="info-box-number text-dark" style="font-size: 1.4rem;">{{ number_format($totalItems, 0, ',', '.') }} <small>Item</small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0" style="border-radius: 1rem;">
                    <span class="info-box-icon bg-success elevation-1" style="border-radius: .75rem;"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted font-weight-bold">Total Nilai Perolehan</span>
                        <span class="info-box-number text-success font-weight-bold" style="font-size: 1.25rem;">Rp {{ number_format($totalValuation, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0" style="border-radius: 1rem;">
                    <span class="info-box-icon bg-info elevation-1" style="border-radius: .75rem;"><i class="fas fa-heartbeat"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted font-weight-bold">Kondisi Baik</span>
                        <span class="info-box-number text-info font-weight-bold" style="font-size: 1.4rem;">{{ $percentBaik }}% <small>({{ $baikCount }} item)</small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0" style="border-radius: 1rem;">
                    <span class="info-box-icon bg-danger elevation-1" style="border-radius: .75rem;"><i class="fas fa-tools"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted font-weight-bold">Perlu Perbaikan / Rusak</span>
                        <span class="info-box-number text-danger font-weight-bold" style="font-size: 1.4rem;">{{ $rusakCount }} <small>Item</small></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sub-Tabs Departemen --}}
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 1rem;">
            <div class="card-body p-2">
                <ul class="nav nav-pills flex-nowrap overflow-auto py-1" style="gap: .4rem;">
                    <li class="nav-item">
                        <a href="{{ route('assets.plant', $plant->code) }}"
                           class="nav-link rounded-pill font-weight-bold px-3 py-2 {{ empty($selectedDeptId) ? 'active' : 'bg-light text-dark' }}">
                            <i class="fas fa-th-large mr-1"></i> Semua Departemen ({{ $totalItems }})
                        </a>
                    </li>
                    @foreach($departmentTabs as $tabDept)
                        <li class="nav-item">
                            <a href="{{ route('assets.plant', ['plantCode' => $plant->code, 'dept' => $tabDept->id]) }}"
                               class="nav-link rounded-pill font-weight-bold px-3 py-2 {{ $selectedDeptId == $tabDept->id ? 'active' : 'bg-light text-dark' }}">
                                {{ $tabDept->name }} ({{ $tabDept->assets_count }})
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Plant Assets Table --}}
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>ID ASET & Nama</th>
                                <th>Departemen</th>
                                <th>Ruangan / Area</th>
                                <th>Kondisi</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-right">Total Perolehan</th>
                                <th class="text-right text-primary font-weight-bold">Nilai Buku Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $index => $asset)
                                <tr>
                                    <td>{{ $assets->firstItem() + $index }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $asset->item_name }}</div>
                                        <code class="text-primary font-weight-bold">{{ $asset->asset_code }}</code>
                                        @if($asset->brand)
                                            <div class="small text-muted"><i class="fas fa-tag mr-1"></i>{{ $asset->brand }}</div>
                                        @endif
                                        @if($asset->origin_plant_code !== $plant->code)
                                            <span class="badge badge-warning text-dark" style="font-size: 10px;" title="Aset lahir di Plant lain">
                                                <i class="fas fa-exchange-alt mr-1"></i>Mutasi dari Plant {{ $asset->origin_plant_code }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">{{ $asset->department->code ?? '-' }}</span>
                                        <div class="small text-muted">{{ $asset->department->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $asset->location->room_name ?? 'Area Umum' }}</span>
                                    </td>
                                    <td>
                                        @if($asset->condition === 'Baik')
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Baik</span>
                                        @elseif($asset->condition === 'Rusak')
                                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Rusak</span>
                                        @else
                                            <span class="badge badge-warning px-2 py-1">{{ $asset->condition }}</span>
                                        @endif

                                        @if($asset->condition_note)
                                            <div class="small text-muted font-italic">{{ $asset->condition_note }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold">{{ number_format($asset->qty, 0, ',', '.') }}</td>
                                    <td class="text-right text-muted">
                                        Rp {{ number_format($asset->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        Rp {{ number_format($asset->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right font-weight-bold text-primary">
                                        Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-boxes fa-3x mb-3 text-secondary"></i>
                                        <p class="mb-0">Tidak ada aset pada kriteria ini di {{ $plant->name }}.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap py-3">
                <div class="text-muted small mb-2 mb-md-0">
                    Menampilkan {{ $assets->firstItem() ?? 0 }} s/d {{ $assets->lastItem() ?? 0 }} dari total {{ $assets->total() }} aset di {{ $plant->name }}
                </div>
                <div>
                    {{ $assets->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

