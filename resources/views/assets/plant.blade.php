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

    /* ── Horizontal Scrollable Table Container & Strict Single-Line Styling ──────── */
    .table-responsive-scroll {
        display: block;
        width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.45) rgba(148, 163, 184, 0.08);
    }
    .table-responsive-scroll::-webkit-scrollbar {
        height: 8px;
    }
    .table-responsive-scroll::-webkit-scrollbar-track {
        background: rgba(148, 163, 184, 0.08);
        border-radius: 9999px;
    }
    .table-responsive-scroll::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.4);
        border-radius: 9999px;
    }
    .table-responsive-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.7);
    }

    body.dark-mode .table-responsive-scroll {
        scrollbar-color: rgba(255, 255, 255, 0.25) rgba(255, 255, 255, 0.05);
    }
    body.dark-mode .table-responsive-scroll::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }
    body.dark-mode .table-responsive-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.25);
    }
    body.dark-mode .table-responsive-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.45);
    }

    /* Force Table Columns strictly single-line with generous width */
    .table-asset-nowrap {
        min-width: 1450px !important;
        width: 100%;
        margin-bottom: 0 !important;
    }

    .table-asset-nowrap th,
    .table-asset-nowrap td {
        white-space: nowrap !important;
        word-break: keep-all !important;
        vertical-align: middle !important;
        padding: 0.75rem 0.85rem !important;
    }

    .table-asset-nowrap thead th {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap !important;
        word-break: keep-all !important;
    }

    .table-asset-nowrap code {
        white-space: nowrap !important;
        word-break: keep-all !important;
        display: inline-block;
    }

    .table-asset-nowrap .badge {
        white-space: nowrap !important;
        display: inline-flex;
        align-items: center;
    }

    .table-asset-nowrap .cell-single-line {
        white-space: nowrap !important;
        word-break: keep-all !important;
    }

    /* ── Form Section & UX Improvements for Create / Edit Modal ──────── */
    .modal-form-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.15rem;
    }
    body.dark-mode .modal-form-section {
        background: rgba(255, 255, 255, 0.035);
        border-color: rgba(255, 255, 255, 0.08);
    }
    .modal-form-section-title {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #2563eb;
        margin-bottom: 0.95rem;
        display: flex;
        align-items: center;
    }
    body.dark-mode .modal-form-section-title {
        color: #60a5fa;
    }

    /* Tracking mode selection cards */
    .tracking-mode-card {
        display: block;
        position: relative;
        padding: 0.85rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: #ffffff;
        height: 100%;
        user-select: none;
    }
    body.dark-mode .tracking-mode-card {
        background: #252b32;
        border-color: #3b424a;
    }
    .tracking-mode-card:hover {
        border-color: #93c5fd;
        transform: translateY(-1px);
    }
    .tracking-mode-radio:checked + .tracking-mode-card {
        border-color: #2563eb !important;
        background: rgba(37, 99, 235, 0.06) !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }
    body.dark-mode .tracking-mode-radio:checked + .tracking-mode-card {
        background: rgba(37, 99, 235, 0.2) !important;
        border-color: #3b82f6 !important;
    }

    /* Live total preview badge box */
    .total-preview-box {
        background: rgba(16, 185, 129, 0.1);
        border: 1px dashed rgba(16, 185, 129, 0.4);
        border-radius: 0.5rem;
        padding: 0.45rem 0.85rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 38px;
    }
    body.dark-mode .total-preview-box {
        background: rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.35);
    }

    /* Fix Chrome/Edge/Safari autofill turning inputs white in dark mode */
    body.dark-mode input:-webkit-autofill,
    body.dark-mode input:-webkit-autofill:hover, 
    body.dark-mode input:-webkit-autofill:focus,
    body.dark-mode input:-webkit-autofill:active,
    body.dark-mode textarea:-webkit-autofill,
    body.dark-mode textarea:-webkit-autofill:hover,
    body.dark-mode textarea:-webkit-autofill:focus,
    body.dark-mode select:-webkit-autofill {
        -webkit-box-shadow: 0 0 0 1000px #212529 inset !important;
        -webkit-text-fill-color: #f8f9fa !important;
        box-shadow: 0 0 0 1000px #212529 inset !important;
        color: #f8f9fa !important;
        caret-color: #ffffff !important;
        transition: background-color 5000s ease-in-out 0s !important;
    }

    body.dark-mode .modal-content {
        background-color: #1a1e24 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .modal .form-control,
    body.dark-mode .modal .custom-select {
        background-color: #212529 !important;
        border-color: #3b424a !important;
        color: #f8f9fa !important;
    }

    body.dark-mode .modal .form-control:focus,
    body.dark-mode .modal .custom-select:focus {
        background-color: #2a3036 !important;
        border-color: #3b82f6 !important;
        color: #ffffff !important;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
    }

    body.dark-mode .modal .input-group-text {
        background-color: #2c3238 !important;
        border-color: #3b424a !important;
        color: #cbd5e1 !important;
    }

    body.dark-mode .modal-footer {
        background-color: #1e242c !important;
        border-top-color: #2d3748 !important;
    }

    /* ── Physical Inventory QR Sticker Label Styling ──────── */
    .asset-physical-sticker {
        background: #ffffff;
        border: 2px solid #0f172a;
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
        color: #0f172a;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
    }
    .sticker-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 0.35rem;
        margin-bottom: 0.65rem;
    }
    .sticker-company {
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #0f172a;
    }
    .sticker-system-badge {
        font-size: 0.68rem;
        font-weight: 800;
        background: #0f172a;
        color: #ffffff;
        padding: 0.15rem 0.45rem;
        border-radius: 0.25rem;
        letter-spacing: 0.05em;
    }
    .sticker-body {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .sticker-qr-wrap {
        background: #ffffff;
        padding: 0.35rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.45rem;
        width: 110px;
        height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .sticker-meta {
        flex-grow: 1;
        min-width: 0;
    }
    .sticker-code {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.95rem;
        font-weight: 800;
        color: #1e40af;
        letter-spacing: 0.02em;
        word-break: break-all;
        line-height: 1.2;
        margin-bottom: 0.2rem;
    }
    .sticker-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.35rem;
        line-height: 1.25;
    }
    .sticker-details {
        font-size: 0.75rem;
        color: #334155;
        line-height: 1.35;
    }
    .sticker-lbl {
        color: #64748b;
        font-weight: 600;
    }
    .sticker-foot {
        border-top: 1px dashed #94a3b8;
        margin-top: 0.55rem;
        padding-top: 0.35rem;
        text-align: center;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #64748b;
    }

    body.dark-mode .asset-physical-sticker {
        background: #ffffff !important;
        color: #0f172a !important;
        border-color: #0f172a !important;
    }
    body.dark-mode .asset-physical-sticker * {
        color: #0f172a;
    }
    body.dark-mode .asset-physical-sticker .sticker-code {
        color: #1e40af !important;
    }
    body.dark-mode .asset-physical-sticker .sticker-system-badge {
        background: #0f172a !important;
        color: #ffffff !important;
    }
    body.dark-mode .asset-physical-sticker .sticker-lbl {
        color: #64748b !important;
    }
    body.dark-mode .asset-physical-sticker .sticker-foot {
        color: #64748b !important;
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
                <div class="table-responsive table-responsive-scroll">
                    <table class="table table-hover align-middle mb-0 table-asset-nowrap" style="font-size: .88rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
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
                                <th class="text-center" style="width: 70px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $index => $asset)
                                <tr>
                                    <td class="text-center font-weight-bold text-muted">{{ $assets->firstItem() + $index }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $asset->item_name }}</div>
                                        <div class="d-inline-flex align-items-center flex-nowrap mt-0.5" style="gap: .35rem;">
                                            <code class="text-primary font-weight-bold">{{ $asset->asset_code }}</code>
                                            <button type="button" class="btn btn-xs btn-outline-primary border-0 p-0 px-1" title="Buka Detail & QR Code" onclick="openDetailModal({{ json_encode($asset) }})">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                            @if($asset->origin_plant_code !== $plant->code)
                                                <span class="badge badge-warning text-dark" style="font-size: 10px;" title="Aset lahir di Plant lain">
                                                    <i class="fas fa-exchange-alt mr-1"></i>Mutasi dari Plant {{ $asset->origin_plant_code }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($asset->specification)
                                            <div class="small text-muted" style="max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $asset->specification }}">
                                                <i class="fas fa-info-circle mr-1 text-secondary"></i>{{ $asset->specification }}
                                            </div>
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
                                        <div class="d-inline-flex align-items-center flex-nowrap" style="gap: .4rem;">
                                            <span class="badge badge-light border">{{ $asset->department->code ?? '-' }}</span>
                                            <span class="small text-muted">{{ $asset->department->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted cell-single-line"><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $asset->location->room_name ?? 'Area Umum' }}</span>
                                    </td>
                                    <td class="text-center cell-single-line">
                                        @if($asset->acquisition_source === 'Hibah')
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-gift mr-1"></i>Hibah</span>
                                        @elseif($asset->acquisition_source === 'Pengadaan Baru')
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-plus-circle mr-1"></i>Baru</span>
                                        @else
                                            <span class="badge badge-info px-2 py-1"><i class="fas fa-file-invoice mr-1"></i>{{ $asset->acquisition_source ?? 'RAB' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold text-dark cell-single-line">
                                        {{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        <div class="d-inline-flex align-items-center flex-nowrap" style="gap: .35rem;">
                                            @if($asset->condition === 'Baik')
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Baik</span>
                                            @elseif($asset->condition === 'Rusak')
                                                <span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Rusak</span>
                                            @else
                                                <span class="badge badge-warning px-2 py-1">{{ $asset->condition }}</span>
                                            @endif

                                            @if($asset->initial_condition === 'Bekas')
                                                <span class="badge badge-secondary px-2 py-1" title="Kondisi Fisik Awal: Bekas"><i class="fas fa-history mr-1"></i>Bekas</span>
                                            @else
                                                <span class="badge badge-light border px-2 py-1" title="Kondisi Fisik Awal: Baru">Baru</span>
                                            @endif

                                            @if($asset->condition_note)
                                                <span class="small text-muted font-italic">({{ $asset->condition_note }})</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-right font-weight-bold cell-single-line">{{ number_format($asset->qty, 0, ',', '.') }}</td>
                                    <td class="text-right text-muted cell-single-line">
                                        Rp {{ number_format($asset->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right font-weight-bold cell-single-line">
                                        Rp {{ number_format($asset->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center cell-single-line">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button"
                                                    class="btn btn-outline-primary"
                                                    onclick="openDetailModal({{ json_encode($asset) }})"
                                                    title="Lihat Detail & QR Code">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-outline-secondary"
                                                    onclick="openEditAssetModal({{ json_encode($asset) }})"
                                                    title="Edit Data & Pindah Ruangan">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5 text-muted">
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
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 980px;" role="document">
        <form action="{{ route('assets.store') }}" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;" autocomplete="off">
            @csrf
            <input type="hidden" name="current_plant_code" value="{{ $plant->code }}">
            <div class="modal-header bg-primary text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem; padding: 1.15rem 1.5rem;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Aset di {{ $plant->name }} (Kode: {{ $plant->code }})
                    </h5>
                    <small class="text-white-50">Registrasi inventaris operasional fisik langsung ke {{ $plant->name }}</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-4" style="max-height: calc(85vh - 120px); overflow-y: auto;">

                {{-- 1. IDENTITAS & KLASIFIKASI BARANG --}}
                <div class="modal-form-section">
                    <div class="modal-form-section-title">
                        <i class="fas fa-boxes mr-2"></i> 1. Identitas & Klasifikasi Aset
                    </div>
                    <div class="row">
                        <div class="col-md-7 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Nama Aset / Barang <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="item_name" class="form-control" placeholder="Contoh: Meja Kantor, Timbangan Digital, Mesin Mixer" autocomplete="off" required>
                        </div>
                        <div class="col-md-5 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Singkatan Kode <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold text-muted bg-light border-right-0">HII-</span>
                                </div>
                                <input type="text" name="item_abbr" class="form-control text-uppercase font-weight-bold" placeholder="TD / MK / MX" maxlength="10" autocomplete="off" required>
                            </div>
                            <small class="text-muted">Awalan penomoran: <code>HII-[KODE].[N]-[DEPT]...</code></small>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Kategori Aset <span class="text-danger">*</span>
                            </label>
                            <select name="category_code" id="plantCategoryCode" class="form-control custom-select" required>
                                @foreach($categories as $c)
                                    <option value="{{ $c->code }}" data-useful-life="{{ $c->default_useful_life_years }}">
                                        {{ $c->code }} - {{ $c->name }} (Standar: {{ $c->default_useful_life_years }} Tahun)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Merk / Brand
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light text-muted border-right-0"><i class="fas fa-tag"></i></span>
                                </div>
                                <input type="text" name="brand" class="form-control" placeholder="Contoh: Great Scale, Brother, Sharp, Active" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Spesifikasi Teknis / Dimensi / Nomor Seri
                            </label>
                            <textarea name="specification" class="form-control" rows="2" placeholder="Catatan dimensi, tipe teknis, kapasitas, kelengkapan aksesoris..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- 2. PENEMPATAN & LOKASI FISIK --}}
                <div class="modal-form-section">
                    <div class="modal-form-section-title">
                        <i class="fas fa-map-marker-alt mr-2"></i> 2. Penempatan & Ruangan Fisik ({{ $plant->name }})
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Departemen Pemilik <span class="text-danger">*</span>
                            </label>
                            <select name="department_id" class="form-control custom-select" required>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" {{ ($selectedDeptId == $d->id) ? 'selected' : '' }}>
                                        {{ $d->name }} ({{ $d->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Ruangan / Sub-Area (Lampiran C)
                            </label>
                            <select name="location_id" class="form-control custom-select">
                                <option value="">-- Pilih Ruangan / Area --</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">
                                        {{ $loc->room_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Otomatis masuk ke audit fisik Lampiran C</small>
                        </div>
                    </div>
                </div>

                {{-- 3. NILAI FINANSIAL & PENCATATAN --}}
                <div class="modal-form-section">
                    <div class="modal-form-section-title">
                        <i class="fas fa-money-bill-wave mr-2"></i> 3. Nilai Finansial & Perolehan
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Sumber Perolehan <span class="text-danger">*</span>
                            </label>
                            <select name="acquisition_source" class="form-control custom-select" required>
                                <option value="RAB">RAB (Rencana Anggaran Biaya)</option>
                                <option value="Hibah">Hibah</option>
                                <option value="Pengadaan Baru">Pengadaan Baru</option>
                                <option value="Sumbangan">Sumbangan</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Tanggal Pembelian <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Masa Manfaat Ekonomis
                            </label>
                            <div class="input-group">
                                <input type="number" name="useful_life_years" id="plantUsefulLifeYears" class="form-control" placeholder="4" min="1" max="30" autocomplete="off">
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold text-muted bg-light">Tahun</span>
                                </div>
                            </div>
                            <small class="text-muted">Kosongkan jika mengikuti kategori</small>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-md-3 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Jumlah (Qty) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="qty" id="plantQtyInput" class="form-control font-weight-bold" value="1" min="1" autocomplete="off" required>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Harga Satuan (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold text-muted bg-light">Rp</span>
                                </div>
                                <input type="text" inputmode="numeric" name="unit_price" id="plantPriceInput" class="form-control font-weight-bold" value="0" placeholder="0" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-5 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Total Estimasi Nilai
                            </label>
                            <div class="total-preview-box">
                                <span class="small font-weight-bold text-muted">Total (Qty × Harga):</span>
                                <span class="font-weight-bold text-success" id="plantTotalPreview" style="font-size: 1.15rem;">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tracking Mode Selection Cards --}}
                    <div class="mt-2">
                        <label class="form-label font-weight-bold small text-muted text-uppercase mb-2">
                            Metode Penomoran Stiker (Tracking Mode) <span class="text-danger">*</span>
                        </label>
                        <div class="row">
                            <div class="col-md-6 col-12 mb-2">
                                <label class="d-block mb-0 h-100">
                                    <input type="radio" name="tracking_mode" value="INDIVIDUAL" class="tracking-mode-radio d-none" checked>
                                    <div class="tracking-mode-card">
                                        <div class="d-flex align-items-start">
                                            <div class="mr-3 text-primary mt-1" style="font-size: 1.3rem;">
                                                <i class="fas fa-barcode"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold text-dark">INDIVIDUAL (Stiker Terpisah)</div>
                                                <small class="text-muted d-block mt-0.5">
                                                    Jika Qty > 1, sistem otomatis membuat stiker kode unik berurutan per unit barang (contoh: <code>TD.1</code>, <code>TD.2</code>).
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6 col-12 mb-2">
                                <label class="d-block mb-0 h-100">
                                    <input type="radio" name="tracking_mode" value="BULK" class="tracking-mode-radio d-none">
                                    <div class="tracking-mode-card">
                                        <div class="d-flex align-items-start">
                                            <div class="mr-3 text-info mt-1" style="font-size: 1.3rem;">
                                                <i class="fas fa-cubes"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold text-dark">BULK (Kuantitas Gabungan)</div>
                                                <small class="text-muted d-block mt-0.5">
                                                    1 stiker kode aset mewakili seluruh jumlah barang (contoh: 50 Box File, 100 Palet).
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. KONDISI FISIK AWAL --}}
                <div class="modal-form-section mb-0">
                    <div class="modal-form-section-title">
                        <i class="fas fa-heartbeat mr-2"></i> 4. Kondisi Fisik Awal
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-12 mb-3 mb-md-0">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Kondisi Fisik Awal <span class="text-danger">*</span>
                            </label>
                            <select name="initial_condition" class="form-control custom-select" required>
                                <option value="Baru" selected>✨ Baru (Barang Baru)</option>
                                <option value="Bekas">🔄 Bekas (Second / Hibah)</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-12 mb-3 mb-md-0">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Status Kelayakan <span class="text-danger">*</span>
                            </label>
                            <select name="condition" class="form-control custom-select" required>
                                <option value="Baik" selected>🟢 Baik (Siap Pakai)</option>
                                <option value="Rusak">🔴 Rusak</option>
                                <option value="Perbaikan">🟡 Perbaikan / Servis</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Catatan Kondisi Fisik Awal
                            </label>
                            <input type="text" name="condition_note" class="form-control" placeholder="Contoh: Barang baru operasional, fungsi prima, kelengkapan utuh..." autocomplete="off">
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer bg-light border-top" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Aset Baru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL DETAIL & STIKER QR CODE ASET --}}
<div class="modal fade" id="modalDetailAsset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 820px;" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header bg-primary text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem; padding: 1.15rem 1.5rem;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-qrcode mr-2"></i> Detail Aset & Stiker QR Code
                    </h5>
                    <small class="text-white-50">PT Herbatech Innopharma Industry — Inventaris Operasional {{ $plant->name }}</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-4" style="max-height: calc(85vh - 120px); overflow-y: auto;">
                
                {{-- STIKER FISIK PREVIEW (Printable Area) --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small font-weight-bold text-uppercase text-muted" style="letter-spacing: .05em;">
                            <i class="fas fa-barcode mr-1"></i> Format Kartu Stiker Label Fisik
                        </span>
                        <div class="d-flex" style="gap: .4rem;">
                            <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2" onclick="downloadQRCode()">
                                <i class="fas fa-download mr-1"></i> Unduh QR (PNG)
                            </button>
                            <button type="button" class="btn btn-xs btn-primary rounded-pill px-3 shadow-sm font-weight-bold" onclick="printAssetSticker()">
                                <i class="fas fa-print mr-1"></i> Cetak Stiker Label
                            </button>
                        </div>
                    </div>

                    <div id="stickerPrintArea">
                        <div class="asset-physical-sticker">
                            <div class="sticker-head">
                                <div class="sticker-company">PT HERBATECH INNOPHARMA INDUSTRY</div>
                                <div class="sticker-system-badge">INVENTARIS GA</div>
                            </div>
                            <div class="sticker-body">
                                <div class="sticker-qr-wrap" id="detailQrContainer">
                                    <!-- QR Code Canvas/SVG injected here -->
                                </div>
                                <div class="sticker-meta">
                                    <div class="sticker-code" id="detailStickerCode">HII-...</div>
                                    <div class="sticker-title" id="detailStickerName">Nama Aset</div>
                                    <div class="sticker-details">
                                        <div><span class="sticker-lbl">Lokasi:</span> <strong id="detailStickerLocation">{{ $plant->name }}</strong></div>
                                        <div><span class="sticker-lbl">Departemen:</span> <strong id="detailStickerDept">-</strong></div>
                                        <div><span class="sticker-lbl">Tgl Beli:</span> <strong id="detailStickerDate">-</strong> | <strong id="detailStickerSource">-</strong></div>
                                        <div><span class="sticker-lbl">Tracking:</span> <span class="badge badge-dark" id="detailStickerTracking">INDIVIDUAL</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="sticker-foot">
                                <span>* SCAN QR UNTUK VERIFIKASI ASLI INVENTARIS PERUSAHAAN *</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- INFORMASI LENGKAP PROFIL ASET --}}
                <div class="modal-form-section mb-0">
                    <div class="modal-form-section-title">
                        <i class="fas fa-info-circle mr-2"></i> Informasi Detail Spesifikasi & Finansial
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4 col-6 mb-2">
                            <small class="text-muted d-block">Kategori Aset</small>
                            <strong class="text-dark" id="detailInfoCategory">-</strong>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <small class="text-muted d-block">Merk / Brand</small>
                            <strong class="text-dark" id="detailInfoBrand">-</strong>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <small class="text-muted d-block">Kondisi Fisik</small>
                            <div id="detailInfoCondition">-</div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <small class="text-muted d-block">Kuantitas (Qty)</small>
                            <strong class="text-dark" id="detailInfoQty">1 Unit</strong>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <small class="text-muted d-block">Harga Satuan (Rp)</small>
                            <strong class="text-dark text-success" id="detailInfoUnitPrice">Rp 0</strong>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <small class="text-muted d-block">Total Nilai Perolehan</small>
                            <strong class="text-success font-weight-bold" id="detailInfoTotalPrice" style="font-size: 1.1rem;">Rp 0</strong>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <small class="text-muted d-block">Masa Manfaat</small>
                            <strong class="text-dark" id="detailInfoUsefulLife">-</strong>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <small class="text-muted d-block">Sumber Perolehan</small>
                            <strong class="text-dark" id="detailInfoSource">-</strong>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <small class="text-muted d-block">Status Aset</small>
                            <span class="badge badge-success" id="detailInfoStatus">AKTIF</span>
                        </div>
                        <div class="col-12 mt-2">
                            <small class="text-muted d-block">Spesifikasi / Dimensi / Catatan</small>
                            <div class="p-2 bg-light rounded text-dark font-italic small border" id="detailInfoSpec">
                                Tidak ada catatan spesifikasi khusus.
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer bg-light border-top" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">
                    Tutup
                </button>
                <button type="button" class="btn btn-info rounded-pill px-4 shadow-sm font-weight-bold" onclick="openEditFromDetail()">
                    <i class="fas fa-edit mr-1"></i> Edit Aset Ini
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT ASET & PINDAH RUANGAN --}}
<div class="modal fade" id="modalEditAssetPlant" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="formEditAsset" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;" autocomplete="off">
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
                <div class="card bg-light border mb-3 shadow-none p-3" style="border-radius: 0.75rem;">
                    <div class="d-flex align-items-center flex-wrap" style="gap: 1rem;">
                        <div id="editAssetQrContainerPlant" class="bg-white p-1 rounded border shadow-sm d-flex align-items-center justify-content-center" style="width: 78px; height: 78px; flex-shrink: 0;">
                            <!-- QR Code auto-generated -->
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1 flex-wrap" style="gap: .35rem;">
                                <span class="badge badge-primary px-2 py-0.5" id="editAssetBadgeModePlant">INDIVIDUAL</span>
                                <span class="badge badge-info px-2 py-0.5">{{ $plant->name }}</span>
                                <span class="badge badge-secondary px-2 py-0.5" id="editAssetBadgeDeptPlant">Dept</span>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-0 font-monospace" id="editAssetCode">HII-...</h6>
                            <small class="text-muted d-block mt-0.5">Kode QR fisik otomatis sinkron dengan stiker barcode aset.</small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" onclick="openDetailFromEdit()">
                                <i class="fas fa-print mr-1"></i> Cetak Stiker
                            </button>
                        </div>
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
                    <div class="col-md-3 col-12 mb-3">
                        <label class="form-label font-weight-bold">Kondisi Awal <span class="text-danger">*</span></label>
                        <select name="initial_condition" id="editInitialCondition" class="form-control" required>
                            <option value="Baru">Baru</option>
                            <option value="Bekas">Bekas</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-12 mb-3">
                        <label class="form-label font-weight-bold">Status Kelayakan <span class="text-danger">*</span></label>
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
                        <input type="text" inputmode="numeric" name="unit_price" id="editUnitPrice" class="form-control font-weight-bold" placeholder="0" autocomplete="off" required>
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
<script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
<script>
    let currentDetailAsset = null;

    function formatRupiahNumber(val) {
        if (!val && val !== 0) return '';
        let clean = val.toString().replace(/[^0-9]/g, '');
        if (!clean) return '';
        let num = parseInt(clean, 10);
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseRupiahNumber(val) {
        if (!val) return 0;
        let clean = val.toString().replace(/[^0-9]/g, '');
        return clean ? parseInt(clean, 10) : 0;
    }

    function attachRupiahInput(input, onUpdate) {
        if (!input) return;
        input.addEventListener('input', function() {
            let clean = this.value.replace(/[^0-9]/g, '');
            if (clean === '') {
                this.value = '';
            } else {
                let num = parseInt(clean, 10);
                this.value = num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            if (typeof onUpdate === 'function') onUpdate();
        });

        input.addEventListener('focus', function() {
            if (this.value === '0') this.value = '';
        });

        input.addEventListener('blur', function() {
            if (this.value.trim() === '') this.value = '0';
        });
    }

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

        // Live Total Estimasi & Sinkronisasi Kategori di Modal Tambah Aset Plant
        const plantQty = document.getElementById('plantQtyInput');
        const plantPrice = document.getElementById('plantPriceInput');
        const plantTotal = document.getElementById('plantTotalPreview');
        const plantCategory = document.getElementById('plantCategoryCode');
        const plantUsefulLife = document.getElementById('plantUsefulLifeYears');
        const editUnitPrice = document.getElementById('editUnitPrice');

        function calculatePlantTotal() {
            if (!plantQty || !plantPrice || !plantTotal) return;
            const q = parseFloat(plantQty.value) || 0;
            const p = parseRupiahNumber(plantPrice.value);
            const total = q * p;
            plantTotal.innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
        }

        if (plantQty) plantQty.addEventListener('input', calculatePlantTotal);
        if (plantPrice) attachRupiahInput(plantPrice, calculatePlantTotal);
        if (editUnitPrice) attachRupiahInput(editUnitPrice);

        if (plantCategory && plantUsefulLife) {
            function syncPlantCategoryUsefulLife() {
                const opt = plantCategory.options[plantCategory.selectedIndex];
                const years = opt ? opt.getAttribute('data-useful-life') : '4';
                if (years) {
                    plantUsefulLife.placeholder = years + ' Th (Default Kategori)';
                }
            }
            plantCategory.addEventListener('change', syncPlantCategoryUsefulLife);
            syncPlantCategoryUsefulLife();
        }

        calculatePlantTotal();
    });

    // ── Detail & Cetak Stiker QR Code ──────────────────────────────────────────
    function openDetailModal(asset) {
        currentDetailAsset = asset;

        // 1. Informasi Stiker Fisik
        document.getElementById('detailStickerCode').innerText = asset.asset_code;
        document.getElementById('detailStickerName').innerText = asset.item_name || '-';

        let plantName = (asset.current_plant_code === '01') ? 'Plant A' : (asset.current_plant_code === '02' ? 'Plant B' : 'Mobile');
        let roomName = asset.location ? asset.location.room_name : '-';
        document.getElementById('detailStickerLocation').innerText = plantName + (roomName && roomName !== '-' ? ' — ' + roomName : '');

        let deptName = asset.department ? (asset.department.code || asset.department.name) : '-';
        document.getElementById('detailStickerDept').innerText = deptName;

        let purchaseDate = asset.purchase_date ? asset.purchase_date.substring(0, 10) : '-';
        document.getElementById('detailStickerDate').innerText = purchaseDate;
        document.getElementById('detailStickerSource').innerText = asset.acquisition_source || 'RAB';

        let trackingMode = asset.tracking_mode || 'INDIVIDUAL';
        const trackingBadge = document.getElementById('detailStickerTracking');
        if (trackingMode === 'BULK') {
            trackingBadge.innerText = 'BULK (Qty: ' + (asset.qty || 1) + ')';
            trackingBadge.className = 'badge badge-info';
        } else {
            trackingBadge.innerText = 'INDIVIDUAL';
            trackingBadge.className = 'badge badge-dark';
        }

        // 2. Informasi Detail Profil
        document.getElementById('detailInfoCategory').innerText = (asset.category ? (asset.category.code + ' - ' + asset.category.name) : (asset.category_code || '-'));
        document.getElementById('detailInfoBrand').innerText = asset.brand || '-';

        let condHtml = `<span class="badge badge-${asset.condition === 'Baik' ? 'success' : (asset.condition === 'Rusak' ? 'danger' : 'warning')}">${asset.condition || 'Baik'}</span>`;
        condHtml += ` <span class="badge badge-light border ml-1">${asset.initial_condition || 'Baru'}</span>`;
        if (asset.condition_note) condHtml += ` <small class="text-muted font-italic">(${asset.condition_note})</small>`;
        document.getElementById('detailInfoCondition').innerHTML = condHtml;

        document.getElementById('detailInfoQty').innerText = (asset.qty || 1) + ' Unit';
        document.getElementById('detailInfoUnitPrice').innerText = 'Rp ' + Math.round(asset.unit_price || 0).toLocaleString('id-ID');
        document.getElementById('detailInfoTotalPrice').innerText = 'Rp ' + Math.round(asset.total_price || 0).toLocaleString('id-ID');
        document.getElementById('detailInfoUsefulLife').innerText = (asset.useful_life_years || 4) + ' Tahun';
        document.getElementById('detailInfoSource').innerText = asset.acquisition_source || 'RAB';
        document.getElementById('detailInfoStatus').innerText = asset.status || 'AKTIF';
        document.getElementById('detailInfoStatus').className = 'badge badge-' + (asset.status === 'AKTIF' ? 'success' : 'secondary');
        document.getElementById('detailInfoSpec').innerText = asset.specification || 'Tidak ada catatan spesifikasi khusus.';

        // 3. Render QR Code (Dynamic Canvas/SVG)
        const qrContainer = document.getElementById('detailQrContainer');
        qrContainer.innerHTML = '';
        const scanUrl = window.location.origin + '/assets/scan/' + encodeURIComponent(asset.asset_code);
        new QRCode(qrContainer, {
            text: scanUrl,
            width: 100,
            height: 100,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });

        $('#modalDetailAsset').modal('show');
    }

    function printAssetSticker() {
        if (!currentDetailAsset) return;
        const printContent = document.getElementById('stickerPrintArea').innerHTML;
        const printWindow = window.open('', '_blank', 'width=620,height=420');
        if (!printWindow) {
            alert('Mohon izinkan popup window di browser untuk mencetak stiker.');
            return;
        }
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Cetak Stiker - ${currentDetailAsset.asset_code}</title>
                    <style>
                        @page { size: auto; margin: 4mm; }
                        * { box-sizing: border-box; }
                        body {
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
                            margin: 0;
                            padding: 0;
                            background: #ffffff;
                        }
                        .asset-physical-sticker {
                            background: #ffffff;
                            border: 2px solid #000000;
                            border-radius: 6px;
                            padding: 8px 10px;
                            width: 360px;
                            color: #000000;
                        }
                        .sticker-head {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            border-bottom: 1.5px solid #000000;
                            padding-bottom: 3px;
                            margin-bottom: 6px;
                        }
                        .sticker-company {
                            font-size: 8.5px;
                            font-weight: 800;
                            text-transform: uppercase;
                        }
                        .sticker-system-badge {
                            font-size: 7.5px;
                            font-weight: 800;
                            background: #000000;
                            color: #ffffff;
                            padding: 1px 4px;
                            border-radius: 3px;
                        }
                        .sticker-body {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                        }
                        .sticker-qr-wrap {
                            width: 95px;
                            height: 95px;
                            flex-shrink: 0;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .sticker-qr-wrap img, .sticker-qr-wrap canvas {
                            width: 95px !important;
                            height: 95px !important;
                        }
                        .sticker-meta {
                            flex-grow: 1;
                            font-size: 8.5px;
                            line-height: 1.35;
                        }
                        .sticker-code {
                            font-size: 10px;
                            font-weight: 800;
                            font-family: monospace;
                        }
                        .sticker-title {
                            font-size: 10px;
                            font-weight: 700;
                            margin: 2px 0;
                        }
                        .sticker-foot {
                            border-top: 1px dashed #666;
                            margin-top: 5px;
                            padding-top: 3px;
                            text-align: center;
                            font-size: 7px;
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <script>
                        window.onload = function() {
                            setTimeout(function() {
                                window.print();
                                window.close();
                            }, 250);
                        };
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    }

    function downloadQRCode() {
        if (!currentDetailAsset) return;
        const canvas = document.querySelector('#detailQrContainer canvas') || document.querySelector('#detailQrContainer img');
        if (!canvas) return;
        const link = document.createElement('a');
        link.download = 'QR_' + currentDetailAsset.asset_code + '.png';
        link.href = canvas.src || canvas.toDataURL('image/png');
        link.click();
    }

    function openDetailFromEdit() {
        $('#modalEditAssetPlant').modal('hide');
        setTimeout(() => {
            if (currentDetailAsset) openDetailModal(currentDetailAsset);
        }, 350);
    }

    function openEditFromDetail() {
        $('#modalDetailAsset').modal('hide');
        setTimeout(() => {
            if (currentDetailAsset) openEditAssetModal(currentDetailAsset);
        }, 350);
    }

    // ── Edit Aset Modal ────────────────────────────────────────────────────────
    function openEditAssetModal(asset) {
        currentDetailAsset = asset;
        document.getElementById('formEditAsset').action = '/assets/' + asset.id;
        document.getElementById('editAssetCode').innerText = asset.asset_code + ' — ' + (asset.item_name || '');

        // Header Badges
        const modeBadge = document.getElementById('editAssetBadgeModePlant');
        if (modeBadge) {
            modeBadge.innerText = asset.tracking_mode || 'INDIVIDUAL';
            modeBadge.className = 'badge ' + ((asset.tracking_mode === 'BULK') ? 'badge-info' : 'badge-primary') + ' px-2 py-0.5';
        }
        const deptBadge = document.getElementById('editAssetBadgeDeptPlant');
        if (deptBadge) {
            deptBadge.innerText = asset.department ? (asset.department.code || asset.department.name) : '-';
        }

        // Header QR Code
        const editQrContainer = document.getElementById('editAssetQrContainerPlant');
        if (editQrContainer) {
            editQrContainer.innerHTML = '';
            const scanUrl = window.location.origin + '/assets/scan/' + encodeURIComponent(asset.asset_code);
            new QRCode(editQrContainer, {
                text: scanUrl,
                width: 70,
                height: 70,
                colorDark : "#0f172a",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
        }

        document.getElementById('editItemName').value = asset.item_name || '';
        document.getElementById('editBrand').value = asset.brand || '';
        document.getElementById('editAcquisitionSource').value = asset.acquisition_source || 'RAB';
        document.getElementById('editPurchaseDate').value = asset.purchase_date ? asset.purchase_date.substring(0, 10) : '';
        document.getElementById('editDept').value = asset.department_id || '';
        document.getElementById('editLocation').value = asset.location_id || '';
        document.getElementById('editInitialCondition').value = asset.initial_condition || 'Baru';
        document.getElementById('editCondition').value = asset.condition || 'Baik';
        document.getElementById('editConditionNote').value = asset.condition_note || '';
        document.getElementById('editUnitPrice').value = formatRupiahNumber(Math.round(asset.unit_price) || 0);
        document.getElementById('editQty').value = asset.qty || 1;
        document.getElementById('editUsefulLife').value = asset.useful_life_years || 4;
        document.getElementById('editSpecification').value = asset.specification || '';
        $('#modalEditAssetPlant').modal('show');
    }
</script>
@endpush
