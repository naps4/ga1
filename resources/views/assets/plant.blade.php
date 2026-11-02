@extends('layouts.app')

@push('styles')
<style>
    /* ── Single-Line Horizontal Scrolling Tab Bar ────────────────────────── */
    .dept-tabs-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }
    .dept-tabs-track {
        display: flex;
        flex-wrap: nowrap !important;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
        gap: 0.5rem;
        padding: 0.4rem 0.25rem;
        width: 100%;
        /* Sleek modern scrollbar */
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
    }
    .dept-tabs-track::-webkit-scrollbar {
        height: 5px;
    }
    .dept-tabs-track::-webkit-scrollbar-track {
        background: transparent;
    }
    .dept-tabs-track::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.35);
        border-radius: 9999px;
    }
    .dept-tabs-track::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.6);
    }
    .dept-tab-item {
        flex: 0 0 auto !important; /* strictly prevent wrapping & shrinkage */
    }
    .dept-tab-btn {
        display: inline-flex;
        align-items: center;
        white-space: nowrap !important; /* strictly keep on one single line */
        word-break: keep-all !important;
        text-decoration: none !important;
        padding: 0.5rem 1.05rem;
        border-radius: 9999px;
        font-size: 0.86rem;
        font-weight: 600;
        line-height: 1.25;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        user-select: none;
    }
    .dept-tab-btn:hover {
        background-color: #e2e8f0;
        color: #1e293b;
        transform: translateY(-1px);
    }
    .dept-tab-btn.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }
    .dept-tab-btn .tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 0.5rem;
        padding: 0.15rem 0.55rem;
        border-radius: 9999px;
        font-size: 0.74rem;
        font-weight: 700;
        background-color: rgba(148, 163, 184, 0.22);
        color: inherit;
        line-height: 1;
    }
    .dept-tab-btn.active .tab-badge {
        background-color: rgba(255, 255, 255, 0.28);
        color: #ffffff;
    }

    /* Dark Mode Overrides for Tabs */
    body.dark-mode .dept-tab-btn {
        background-color: #2c3238;
        color: #cbd5e1;
        border-color: #3b424a;
    }
    body.dark-mode .dept-tab-btn:hover {
        background-color: #373f47;
        color: #ffffff;
    }
    body.dark-mode .dept-tab-btn.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }
    body.dark-mode .dept-tab-btn .tab-badge {
        background-color: rgba(255, 255, 255, 0.14);
        color: #e2e8f0;
    }
    body.dark-mode .dept-tab-btn.active .tab-badge {
        background-color: rgba(255, 255, 255, 0.28);
        color: #ffffff;
    }

    /* Arrow scroll buttons */
    .dept-scroll-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: rgba(148, 163, 184, 0.15);
        color: inherit;
        border: 1px solid rgba(148, 163, 184, 0.2);
        cursor: pointer;
        flex-shrink: 0;
        margin: 0 3px;
        transition: all 0.2s ease;
        font-size: 0.8rem;
    }
    .dept-scroll-btn:hover {
        background-color: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    body.dark-mode .dept-scroll-btn {
        background-color: #2c3238;
        color: #cbd5e1;
        border-color: #3b424a;
    }
    body.dark-mode .dept-scroll-btn:hover {
        background-color: #2563eb;
        color: #ffffff;
    }

    /* KPI interactive cards */
    .kpi-link-card {
        text-decoration: none !important;
        display: block;
    }
    .kpi-interactive {
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s;
    }
    .kpi-interactive:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1) !important;
    }
    .kpi-active-filter {
        outline: 2px solid #2563eb;
        outline-offset: -2px;
    }
</style>
@endpush

@section('content')
@php
    $filterParams = request()->only(['search', 'location_id', 'category', 'condition', 'source', 'per_page']);
    $activeDeptName = $selectedDeptId 
        ? ($departmentTabs->firstWhere('id', $selectedDeptId)->name ?? 'Departemen')
        : 'Semua Departemen';
    $hasActiveFilters = !empty($search) || !empty($locationId) || !empty($categoryCode) || !empty($condition) || !empty($source);
@endphp

<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-industry text-primary mr-2"></i> Inventaris Operasional {{ $plant->name }} (Kode: {{ $plant->code }})
                </h1>
                <small class="text-muted">PT Herbatech Innopharma Industry — Rekapitulasi Lokasi Fisik Terkini</small>
            </div>
            <div class="mt-2 mt-md-0 d-flex align-items-center flex-wrap" style="gap: .4rem;">
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#modalCreateAssetPlant">
                    <i class="fas fa-plus-circle mr-1"></i> Tambah Aset di {{ $plant->name }}
                </button>
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
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

        {{-- KPI Cards Per Plant (Clickable untuk Filter Cepat Kondisi) --}}
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
                <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except(['condition', 'page']))) }}"
                   class="kpi-link-card"
                   title="Klik untuk tampilkan semua kondisi">
                    <div class="info-box shadow-sm border-0 kpi-interactive {{ empty($condition) ? 'kpi-active-filter' : '' }}" style="border-radius: 1rem;">
                        <span class="info-box-icon bg-primary elevation-1" style="border-radius: .75rem;"><i class="fas fa-cubes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Total Aset di {{ $plant->name }}</span>
                            <span class="info-box-number text-dark" style="font-size: 1.4rem;">{{ number_format($totalItems, 0, ',', '.') }} <small>Item</small></span>
                        </div>
                    </div>
                </a>
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
                <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except('page'), ['condition' => 'Baik'])) }}"
                   class="kpi-link-card"
                   title="Klik untuk memfilter aset kondisi Baik">
                    <div class="info-box shadow-sm border-0 kpi-interactive {{ ($condition ?? '') === 'Baik' ? 'kpi-active-filter' : '' }}" style="border-radius: 1rem;">
                        <span class="info-box-icon bg-info elevation-1" style="border-radius: .75rem;"><i class="fas fa-heartbeat"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Kondisi Baik</span>
                            <span class="info-box-number text-info font-weight-bold" style="font-size: 1.4rem;">{{ $percentBaik }}% <small>({{ $baikCount }} item)</small></span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except('page'), ['condition' => 'Rusak'])) }}"
                   class="kpi-link-card"
                   title="Klik untuk memfilter aset Perlu Perbaikan / Rusak">
                    <div class="info-box shadow-sm border-0 kpi-interactive {{ ($condition ?? '') === 'Rusak' ? 'kpi-active-filter' : '' }}" style="border-radius: 1rem;">
                        <span class="info-box-icon bg-danger elevation-1" style="border-radius: .75rem;"><i class="fas fa-tools"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Perlu Perbaikan / Rusak</span>
                            <span class="info-box-number text-danger font-weight-bold" style="font-size: 1.4rem;">{{ $rusakCount }} <small>Item</small></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Sub-Tabs Departemen: Single-Line Horizontal Scrolling --}}
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 1rem;">
            <div class="card-body p-2">
                <div class="dept-tabs-wrapper">
                    <button type="button" class="dept-scroll-btn d-none d-md-inline-flex" id="tabScrollLeft" title="Scroll ke kiri">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <div class="dept-tabs-track" id="deptTabsTrack">
                        {{-- Tab: Semua Departemen --}}
                        <div class="dept-tab-item">
                            <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code], $filterParams)) }}"
                               class="dept-tab-btn {{ empty($selectedDeptId) ? 'active' : '' }}">
                                <i class="fas fa-th-large mr-1.5"></i>
                                <span>Semua Departemen</span>
                                <span class="tab-badge">{{ $totalItems }}</span>
                            </a>
                        </div>

                        {{-- Tab: Tiap Departemen --}}
                        @foreach($departmentTabs as $tabDept)
                            <div class="dept-tab-item">
                                <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $tabDept->id], $filterParams)) }}"
                                   class="dept-tab-btn {{ $selectedDeptId == $tabDept->id ? 'active' : '' }}">
                                    <span>{{ $tabDept->name }}</span>
                                    <span class="tab-badge">{{ $tabDept->assets_count }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="dept-scroll-btn d-none d-md-inline-flex" id="tabScrollRight" title="Scroll ke kanan">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Search Bar & Filter Toolbar --}}
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 1rem;">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('assets.plant', $plant->code) }}" id="assetFilterForm" class="mb-0">
                    {{-- Pertahankan tab departemen yang sedang dibuka --}}
                    @if(!empty($selectedDeptId))
                        <input type="hidden" name="dept" value="{{ $selectedDeptId }}">
                    @endif

                    <div class="row align-items-center" style="row-gap: .6rem;">
                        {{-- Search Input (Nama, Kode, Merk, Spek, Sumber) --}}
                        <div class="col-lg-3 col-md-12">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-right-0 text-muted">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input type="text"
                                       name="search"
                                       class="form-control border-left-0"
                                       placeholder="Cari ID, Nama, Merk, Spek..."
                                       value="{{ $search ?? '' }}"
                                       autocomplete="off">
                                @if(!empty($search))
                                    <div class="input-group-append">
                                        <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except(['search', 'page']))) }}"
                                           class="input-group-text bg-transparent border-left-0 text-muted"
                                           title="Hapus pencarian">
                                            <i class="fas fa-times-circle"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Dropdown Ruangan / Area --}}
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <select name="location_id" class="form-control form-control-sm custom-select" onchange="this.form.submit()">
                                <option value="">Semua Ruangan ({{ $locations->count() }})</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ ($locationId ?? '') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->room_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dropdown Sumber Perolehan (RAB / Hibah) --}}
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <select name="source" class="form-control form-control-sm custom-select" onchange="this.form.submit()">
                                <option value="">Semua Sumber Perolehan</option>
                                <option value="RAB" {{ ($source ?? '') === 'RAB' ? 'selected' : '' }}>Sumber: RAB</option>
                                <option value="Hibah" {{ ($source ?? '') === 'Hibah' ? 'selected' : '' }}>Sumber: Hibah</option>
                                <option value="Pengadaan Baru" {{ ($source ?? '') === 'Pengadaan Baru' ? 'selected' : '' }}>Sumber: Pengadaan Baru</option>
                            </select>
                        </div>

                        {{-- Dropdown Kategori --}}
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <select name="category" class="form-control form-control-sm custom-select" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->code }}" {{ ($categoryCode ?? '') == $cat->code ? 'selected' : '' }}>
                                        {{ $cat->code }} - {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dropdown Kondisi --}}
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <select name="condition" class="form-control form-control-sm custom-select" onchange="this.form.submit()">
                                <option value="">Semua Kondisi</option>
                                <option value="Baik" {{ ($condition ?? '') === 'Baik' ? 'selected' : '' }}>Kondisi: Baik</option>
                                <option value="Rusak" {{ ($condition ?? '') === 'Rusak' ? 'selected' : '' }}>Kondisi: Rusak</option>
                                <option value="Perbaikan" {{ ($condition ?? '') === 'Perbaikan' ? 'selected' : '' }}>Kondisi: Perbaikan</option>
                            </select>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="col-lg-1 col-md-12 col-sm-6 d-flex" style="gap: .3rem;">
                            <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold" title="Terapkan Pencarian">
                                <i class="fas fa-filter"></i>
                            </button>
                            @if($hasActiveFilters)
                                <a href="{{ route('assets.plant', ['plantCode' => $plant->code, 'dept' => $selectedDeptId]) }}"
                                   class="btn btn-outline-secondary btn-sm"
                                   title="Reset Pencarian & Filter">
                                    <i class="fas fa-undo"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Baris Filter Chips Aktif --}}
                    @if($hasActiveFilters)
                        <div class="d-flex align-items-center flex-wrap mt-2 pt-2 border-top" style="gap: .4rem; font-size: .83rem;">
                            <span class="text-muted font-weight-bold mr-1">
                                <i class="fas fa-filter text-primary mr-1"></i>Filter Aktif ({{ $assets->total() }} hasil):
                            </span>

                            @if(!empty($search))
                                <span class="badge badge-primary px-2.5 py-1 font-weight-normal">
                                    Pencarian: "<strong>{{ $search }}</strong>"
                                    <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except(['search', 'page']))) }}" class="text-white ml-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif

                            @if(!empty($source))
                                <span class="badge badge-warning text-dark px-2.5 py-1 font-weight-normal">
                                    Sumber: <strong>{{ $source }}</strong>
                                    <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except(['source', 'page']))) }}" class="text-dark ml-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif

                            @if(!empty($locationId))
                                @php $activeLoc = $locations->firstWhere('id', $locationId); @endphp
                                <span class="badge badge-info px-2.5 py-1 font-weight-normal">
                                    Ruangan: <strong>{{ $activeLoc->room_name ?? $locationId }}</strong>
                                    <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except(['location_id', 'page']))) }}" class="text-white ml-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif

                            @if(!empty($categoryCode))
                                @php $activeCat = $categories->firstWhere('code', $categoryCode); @endphp
                                <span class="badge badge-secondary px-2.5 py-1 font-weight-normal">
                                    Kategori: <strong>{{ $activeCat->name ?? $categoryCode }}</strong>
                                    <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except(['category', 'page']))) }}" class="text-white ml-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif

                            @if(!empty($condition))
                                <span class="badge {{ $condition === 'Baik' ? 'badge-success' : 'badge-danger' }} px-2.5 py-1 font-weight-normal">
                                    Kondisi: <strong>{{ $condition }}</strong>
                                    <a href="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except(['condition', 'page']))) }}" class="text-white ml-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif

                            <a href="{{ route('assets.plant', ['plantCode' => $plant->code, 'dept' => $selectedDeptId]) }}"
                               class="text-danger ml-2 font-weight-bold" style="text-decoration: underline;">
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Plant Assets Table Card --}}
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center flex-wrap pt-3 px-3 pb-2">
                <div>
                    <h5 class="mb-0 font-weight-bold text-dark">
                        <i class="fas fa-table text-primary mr-1"></i>
                        Daftar Aset {{ $activeDeptName }}
                    </h5>
                    <small class="text-muted">
                        Ditemukan {{ $assets->total() }} aset
                        @if(!empty($search))
                            yang cocok dengan "<strong>{{ $search }}</strong>"
                        @endif
                    </small>
                </div>
                <div class="mt-2 mt-md-0 d-flex align-items-center" style="gap: .5rem;">
                    <span class="small text-muted font-weight-bold">Baris per halaman:</span>
                    <select class="custom-select custom-select-sm" style="width: 80px;" onchange="window.location.href = this.value">
                        @foreach([15, 25, 50, 100] as $size)
                            <option value="{{ route('assets.plant', array_merge(['plantCode' => $plant->code, 'dept' => $selectedDeptId], request()->except('page'), ['per_page' => $size])) }}" {{ ($perPage ?? 25) == $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th style="width: 45px;">No</th>
                                <th>ID ASET & Nama</th>
                                <th>Merek</th>
                                <th>Departemen</th>
                                <th>Ruangan / Area</th>
                                <th class="text-center">Sumber</th>
                                <th class="text-center">Tgl Beli</th>
                                <th>Kondisi</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-right">Total Nilai (Rp)</th>
                                <th class="text-right text-primary font-weight-bold">Nilai Buku Sisa</th>
                                <th class="text-center" style="width: 70px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $index => $asset)
                                <tr>
                                    <td>{{ $assets->firstItem() + $index }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $asset->item_name }}</div>
                                        <code class="text-primary font-weight-bold">{{ $asset->asset_code }}</code>
                                        @if($asset->specification)
                                            <div class="small text-muted" style="max-width: 250px; white-space: normal; line-height: 1.25;">
                                                <i class="fas fa-info-circle mr-1 text-secondary"></i>{{ Str::limit($asset->specification, 45) }}
                                            </div>
                                        @endif
                                        @if($asset->origin_plant_code !== $plant->code)
                                            <span class="badge badge-warning text-dark mt-1" style="font-size: 10px;" title="Aset lahir di Plant lain">
                                                <i class="fas fa-exchange-alt mr-1"></i>Mutasi dari Plant {{ $asset->origin_plant_code }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($asset->brand)
                                            <span class="badge badge-light border"><i class="fas fa-tag mr-1 text-secondary"></i>{{ $asset->brand }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">{{ $asset->department->code ?? '-' }}</span>
                                        <div class="small text-muted">{{ $asset->department->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $asset->location->room_name ?? 'Area Umum' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($asset->acquisition_source === 'Hibah')
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-gift mr-1"></i>Hibah</span>
                                        @elseif($asset->acquisition_source === 'Pengadaan Baru')
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-plus-circle mr-1"></i>Baru</span>
                                        @else
                                            <span class="badge badge-info px-2 py-1"><i class="fas fa-file-invoice mr-1"></i>{{ $asset->acquisition_source ?? 'RAB' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="small font-weight-bold text-dark">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</span>
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
                                    <td class="text-right font-weight-bold">
                                        Rp {{ number_format($asset->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right font-weight-bold text-primary">
                                        Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary rounded-circle"
                                                style="width: 32px; height: 32px; padding: 0;"
                                                onclick="openEditAssetModal({{ json_encode($asset) }})"
                                                title="Edit Data & Pindah Ruangan">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-3x mb-3 text-secondary"></i>
                                        <h6 class="font-weight-bold">Tidak ada aset yang sesuai kriteria</h6>
                                        <p class="mb-3 text-muted">Coba ubah kata kunci pencarian atau sesuaikan filter ruangan / kondisi.</p>
                                        @if($hasActiveFilters)
                                            <a href="{{ route('assets.plant', ['plantCode' => $plant->code, 'dept' => $selectedDeptId]) }}"
                                               class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                <i class="fas fa-undo mr-1"></i> Reset Filter
                                            </a>
                                        @endif
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

{{-- MODAL TAMBAH ASET DI PLANT INI --}}
<div class="modal fade" id="modalCreateAssetPlant" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form action="{{ route('assets.store') }}" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            @csrf
            <input type="hidden" name="current_plant_code" value="{{ $plant->code }}">
            <div class="modal-header bg-primary text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Aset di {{ $plant->name }} (Kode: {{ $plant->code }})
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-8 col-12 mb-3">
                        <label class="form-label font-weight-bold">Nama Aset <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" placeholder="Contoh: Meja Kantor, Timbangan Digital, Mixer" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Singkatan Kode <span class="text-danger">*</span></label>
                        <input type="text" name="item_abbr" class="form-control text-uppercase" placeholder="Contoh: MK, TD, MX" maxlength="10" required>
                        <small class="text-muted">Untuk kode HII-[KODE]...</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Merk / Brand</label>
                        <input type="text" name="brand" class="form-control" placeholder="Contoh: Great Scale, Brother, Sharp">
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Sumber Perolehan <span class="text-danger">*</span></label>
                        <select name="acquisition_source" class="form-control" required>
                            <option value="RAB">RAB (Rencana Anggaran Biaya)</option>
                            <option value="Hibah">Hibah</option>
                            <option value="Pengadaan Baru">Pengadaan Baru</option>
                            <option value="Sumbangan">Sumbangan</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Kategori Aset <span class="text-danger">*</span></label>
                        <select name="category_code" class="form-control" required>
                            @foreach($categories as $c)
                                <option value="{{ $c->code }}">{{ $c->name }} (Kode: {{ $c->code }} - Manfaat {{ $c->default_useful_life_years }} Th)</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Departemen <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-control" required>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ ($selectedDeptId == $d->id) ? 'selected' : '' }}>
                                    {{ $d->name }} ({{ $d->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Ruangan / Sub-Area (Lampiran C)</label>
                        <select name="location_id" class="form-control">
                            <option value="">-- Pilih Ruangan / Area --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">
                                    {{ $loc->room_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Untuk lembar audit Lampiran C</small>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Tanggal Pembelian <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Jumlah (Qty) <span class="text-danger">*</span></label>
                        <input type="number" name="qty" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="unit_price" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Tracking Mode <span class="text-danger">*</span></label>
                        <select name="tracking_mode" class="form-control" required>
                            <option value="INDIVIDUAL">INDIVIDUAL (Loop pisah kode otomatis)</option>
                            <option value="BULK">BULK (1 Baris untuk seluruh Qty)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Spesifikasi / Catatan Teknis</label>
                    <textarea name="specification" class="form-control" rows="2" placeholder="Catatan dimensi, tipe spesifik, kelengkapan..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                    <i class="fas fa-save mr-1"></i> Simpan Aset Baru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT ASET & PINDAH RUANGAN --}}
<div class="modal fade" id="modalEditAssetPlant" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="formEditAsset" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            @csrf
            @method('PUT')
            <div class="modal-header bg-info text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-edit mr-2"></i> Edit Data & Ruangan Aset
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border d-flex align-items-center mb-3 py-2">
                    <i class="fas fa-barcode text-primary fa-2x mr-3"></i>
                    <div>
                        <div class="font-weight-bold text-dark" id="editAssetCode"></div>
                        <small class="text-muted">Kode ID Aset fisik tidak berubah saat pengeditan ruangan / data teknis.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 col-12 mb-3">
                        <label class="form-label font-weight-bold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" id="editItemName" class="form-control" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Merk / Brand</label>
                        <input type="text" name="brand" id="editBrand" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-12 mb-3">
                        <label class="form-label font-weight-bold">Sumber Perolehan</label>
                        <select name="acquisition_source" id="editAcquisitionSource" class="form-control">
                            <option value="RAB">RAB (Rencana Anggaran Biaya)</option>
                            <option value="Hibah">Hibah</option>
                            <option value="Pengadaan Baru">Pengadaan Baru</option>
                            <option value="Sumbangan">Sumbangan</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-12 mb-3">
                        <label class="form-label font-weight-bold">Tanggal Pembelian</label>
                        <input type="date" name="purchase_date" id="editPurchaseDate" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-12 mb-3">
                        <label class="form-label font-weight-bold">Departemen</label>
                        <select name="department_id" id="editDept" class="form-control">
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-12 mb-3">
                        <label class="form-label font-weight-bold">Ruangan / Sub-Area (Lampiran C)</label>
                        <select name="location_id" id="editLocation" class="form-control">
                            <option value="">-- Pilih Ruangan / Area --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->room_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Ubah ruangan ini agar aset tampil di lembar Lampiran C terkait.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-12 mb-3">
                        <label class="form-label font-weight-bold">Kondisi Fisik <span class="text-danger">*</span></label>
                        <select name="condition" id="editCondition" class="form-control" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Perbaikan">Perbaikan</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-12 mb-3">
                        <label class="form-label font-weight-bold">Catatan Kondisi</label>
                        <input type="text" name="condition_note" id="editConditionNote" class="form-control" placeholder="Contoh: Layar bergaris, tombol macet...">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Qty <span class="text-danger">*</span></label>
                        <input type="number" name="qty" id="editQty" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="unit_price" id="editUnitPrice" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Masa Manfaat (Tahun) <span class="text-danger">*</span></label>
                        <input type="number" name="useful_life_years" id="editUsefulLife" class="form-control" min="1" max="30" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Spesifikasi / Keterangan</label>
                    <textarea name="specification" id="editSpecification" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info rounded-pill px-4 font-weight-bold">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.getElementById('deptTabsTrack');
        const scrollLeftBtn = document.getElementById('tabScrollLeft');
        const scrollRightBtn = document.getElementById('tabScrollRight');

        if (track) {
            // Scroll kiri & kanan via tombol chevron
            if (scrollLeftBtn) {
                scrollLeftBtn.addEventListener('click', function() {
                    track.scrollBy({ left: -260, behavior: 'smooth' });
                });
            }
            if (scrollRightBtn) {
                scrollRightBtn.addEventListener('click', function() {
                    track.scrollBy({ left: 260, behavior: 'smooth' });
                });
            }

            // Otomatis scroll tab aktif ke tengah pandangan saat halaman dimuat
            const activeTab = track.querySelector('.dept-tab-btn.active');
            if (activeTab) {
                setTimeout(function() {
                    activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }, 120);
            }
        }
    });

    function openEditAssetModal(asset) {
        document.getElementById('formEditAsset').action = '/assets/' + asset.id;
        document.getElementById('editAssetCode').innerText = asset.asset_code + ' — ' + (asset.item_name || '');
        document.getElementById('editItemName').value = asset.item_name || '';
        document.getElementById('editBrand').value = asset.brand || '';
        document.getElementById('editAcquisitionSource').value = asset.acquisition_source || 'RAB';
        document.getElementById('editPurchaseDate').value = asset.purchase_date ? asset.purchase_date.substring(0, 10) : '';
        document.getElementById('editDept').value = asset.department_id || '';
        document.getElementById('editLocation').value = asset.location_id || '';
        document.getElementById('editCondition').value = asset.condition || 'Baik';
        document.getElementById('editConditionNote').value = asset.condition_note || '';
        document.getElementById('editUnitPrice').value = Math.round(asset.unit_price) || 0;
        document.getElementById('editQty').value = asset.qty || 1;
        document.getElementById('editUsefulLife').value = asset.useful_life_years || 4;
        document.getElementById('editSpecification').value = asset.specification || '';
        $('#modalEditAssetPlant').modal('show');
    }
</script>
@endpush
