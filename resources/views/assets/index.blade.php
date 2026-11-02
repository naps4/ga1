@extends('layouts.app')

@push('styles')
<style>
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
        min-width: 1350px !important;
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
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-boxes text-primary mr-2"></i> Master Aset GA (ASET ALL)
                </h1>
                <small class="text-muted">PT Herbatech Innopharma Industry — Inventaris Plant A, Plant B & Aset Bergerak</small>
            </div>
            <div class="mt-2 mt-md-0 d-flex gap-2">
                <a href="{{ route('assets.plant', '01') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 mr-1">
                    <i class="fas fa-industry mr-1"></i> View Plant A
                </a>
                <a href="{{ route('assets.plant', '02') }}" class="btn btn-outline-info btn-sm rounded-pill px-3 mr-1">
                    <i class="fas fa-building mr-1"></i> View Plant B
                </a>
                <a href="{{ route('assets.lampiran-c') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 mr-1">
                    <i class="fas fa-clipboard-list mr-1"></i> Lampiran C (Audit)
                </a>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#modalCreateAsset">
                    <i class="fas fa-plus mr-1"></i> Tambah Aset
                </button>
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

        {{-- KPI Cards --}}
        <div class="row">
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0" style="border-radius: 1rem;">
                    <span class="info-box-icon bg-primary elevation-1" style="border-radius: .75rem;"><i class="fas fa-cubes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted font-weight-bold">Total Aset Aktif</span>
                        <span class="info-box-number text-dark" style="font-size: 1.5rem;">{{ number_format($totalActive, 0, ',', '.') }} <small>Item</small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0" style="border-radius: 1rem;">
                    <span class="info-box-icon bg-success elevation-1" style="border-radius: .75rem;"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted font-weight-bold">Total Valuasi Perolehan</span>
                        <span class="info-box-number text-success font-weight-bold" style="font-size: 1.4rem;">Rp {{ number_format($totalValuation, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0" style="border-radius: 1rem;">
                    <span class="info-box-icon bg-warning elevation-1" style="border-radius: .75rem;"><i class="fas fa-exclamation-triangle text-white"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted font-weight-bold">Aset Belum Lengkap Harga</span>
                        <span class="info-box-number text-warning font-weight-bold" style="font-size: 1.5rem;">{{ number_format($incompletePriceCount, 0, ',', '.') }} <small>Item</small></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Box --}}
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 1rem;">
            <div class="card-body p-3">
                <form action="{{ route('assets.index') }}" method="GET" class="row align-items-center g-2">
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Grup Aset</label>
                        <select name="group" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all">Semua Grup</option>
                            <option value="UTAMA" {{ request('group') == 'UTAMA' ? 'selected' : '' }}>UTAMA</option>
                            <option value="TERPISAH" {{ request('group') == 'TERPISAH' ? 'selected' : '' }}>TERPISAH (Bulk)</option>
                            <option value="BERGERAK" {{ request('group') == 'BERGERAK' ? 'selected' : '' }}>BERGERAK</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Lokasi Plant Fisik</label>
                        <select name="plant" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all">Semua Plant</option>
                            @foreach($plants as $p)
                                <option value="{{ $p->code }}" {{ request('plant') == $p->code ? 'selected' : '' }}>{{ $p->name }} ({{ $p->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Departemen</label>
                        <select name="dept" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all">Semua Dept</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ request('dept') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Kategori</label>
                        <select name="category" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all">Semua Kategori</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->code }}" {{ request('category') == $c->code ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Sumber Perolehan</label>
                        <select name="source" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all">Semua Sumber</option>
                            <option value="RAB" {{ request('source') == 'RAB' ? 'selected' : '' }}>RAB</option>
                            <option value="Hibah" {{ request('source') == 'Hibah' ? 'selected' : '' }}>Hibah</option>
                            <option value="Pengadaan Baru" {{ request('source') == 'Pengadaan Baru' ? 'selected' : '' }}>Pengadaan Baru</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Kondisi</label>
                        <select name="condition" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all">Semua Kondisi</option>
                            <option value="Baik" {{ request('condition') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak" {{ request('condition') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Perbaikan" {{ request('condition') == 'Perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                        </select>
                    </div>
                    <div class="col-12 mt-1">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control" placeholder="Cari Kode Aset, Nama Barang, Merek, atau Sumber Perolehan..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary px-3 font-weight-bold"><i class="fas fa-search mr-1"></i> Cari Aset</button>
                                @if(request()->hasAny(['group', 'plant', 'dept', 'category', 'condition', 'source', 'search']))
                                    <a href="{{ route('assets.index') }}" class="btn btn-light border px-3" title="Reset Semua Filter"><i class="fas fa-undo mr-1"></i> Reset</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Assets Table --}}
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-0">
                <div class="table-responsive table-responsive-scroll">
                    <table class="table table-hover align-middle mb-0 table-asset-nowrap" style="font-size: .88rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>ID ASET & Nama</th>
                                <th>Merek</th>
                                <th>Departemen</th>
                                <th>Posisi Plant</th>
                                <th class="text-center">Sumber</th>
                                <th class="text-center">Tgl Beli</th>
                                <th>Kondisi</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Total Nilai (Rp)</th>
                                <th class="text-center">Umur / Bulan</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
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
                                            @if(!$asset->is_price_complete)
                                                <span class="badge badge-warning text-dark" style="font-size: 10px;">Harga Belum Lengkap</span>
                                            @endif
                                            @if($asset->asset_group !== 'UTAMA')
                                                <span class="badge badge-info" style="font-size: 10px;">{{ $asset->asset_group }}</span>
                                            @endif
                                        </div>
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
                                        @if($asset->current_plant_code === '01')
                                            <span class="badge badge-primary">Plant A</span>
                                        @elseif($asset->current_plant_code === '02')
                                            <span class="badge badge-info">Plant B</span>
                                        @else
                                            <span class="badge badge-secondary">Mobile</span>
                                        @endif
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
                                    <td class="text-right font-weight-bold cell-single-line">
                                        Rp {{ number_format($asset->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center cell-single-line">
                                        <span class="badge badge-light border mr-1">{{ $asset->useful_life_years }} Th</span>
                                        <span class="small text-muted font-weight-bold">{{ $asset->months_elapsed }} Bln</span>
                                    </td>
                                    <td class="text-center cell-single-line">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary" title="Edit Data Aset"
                                                    onclick="openEditModal({{ json_encode($asset) }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info" title="Pindah / Mutasi Aset"
                                                    onclick="openMutateModal({{ json_encode($asset) }})">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" title="Update Kondisi"
                                                    onclick="openConditionModal({{ json_encode($asset) }})">
                                                <i class="fas fa-stethoscope"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" title="Disposal Aset"
                                                    onclick="openDisposeModal({{ json_encode($asset) }})">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i>
                                        <p class="mb-0">Tidak ada aset yang sesuai dengan kriteria pencarian.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap py-3">
                <div class="text-muted small mb-2 mb-md-0">
                    Menampilkan {{ $assets->firstItem() ?? 0 }} s/d {{ $assets->lastItem() ?? 0 }} dari total {{ $assets->total() }} aset
                </div>
                <div>
                    {{ $assets->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

    </div>
</section>

{{-- MODAL TAMBAH ASET --}}
<div class="modal fade" id="modalCreateAsset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 980px;" role="document">
        <form action="{{ route('assets.store') }}" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;" autocomplete="off">
            @csrf
            <div class="modal-header bg-primary text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem; padding: 1.15rem 1.5rem;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Aset Baru
                    </h5>
                    <small class="text-white-50">Registrasi aset baru ke Master Inventaris PT Herbatech Innopharma Industry</small>
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
                            <input type="text" name="item_name" id="createItemName" class="form-control" placeholder="Contoh: Meja Kantor Kayu Jati, Laptop Lenovo ThinkPad" autocomplete="off" required>
                        </div>
                        <div class="col-md-5 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Singkatan Kode <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold text-muted bg-light border-right-0">HII-</span>
                                </div>
                                <input type="text" name="item_abbr" id="createItemAbbr" class="form-control text-uppercase font-weight-bold" placeholder="MK / LP / KK" maxlength="10" autocomplete="off" required>
                            </div>
                            <small class="text-muted">Awalan penomoran: <code>HII-[KODE].[N]-[DEPT]...</code></small>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Kategori Aset <span class="text-danger">*</span>
                            </label>
                            <select name="category_code" id="createCategoryCode" class="form-control custom-select" required>
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
                                <input type="text" name="brand" class="form-control" placeholder="Contoh: Active Furniture, Epson, Samsung, Brother" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Spesifikasi Teknis / Dimensi / Nomor Seri
                            </label>
                            <textarea name="specification" class="form-control" rows="2" placeholder="Catatan ukuran dimensi (P x L x T), spesifikasi RAM/CPU, serial number, aksesoris..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- 2. PENEMPATAN & LOKASI FISIK --}}
                <div class="modal-form-section">
                    <div class="modal-form-section-title">
                        <i class="fas fa-map-marker-alt mr-2"></i> 2. Penempatan & Lokasi Fisik
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Departemen Pemilik <span class="text-danger">*</span>
                            </label>
                            <select name="department_id" class="form-control custom-select" required>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Lokasi Plant Fisik <span class="text-danger">*</span>
                            </label>
                            <select name="current_plant_code" id="modalPlantSelect" class="form-control custom-select" required>
                                @foreach($plants as $p)
                                    <option value="{{ $p->code }}">{{ $p->name }} (Kode: {{ $p->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Ruangan / Sub-Area (Lampiran C)
                            </label>
                            <select name="location_id" id="modalLocationSelect" class="form-control custom-select">
                                <option value="">-- Pilih Ruangan / Area --</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" data-plant="{{ $loc->plant_code }}">
                                        {{ $loc->room_name }} ({{ $loc->plant->name ?? 'Plant ' . $loc->plant_code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Otomatis sinkron ke audit Lampiran C</small>
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
                                <input type="number" name="useful_life_years" id="createUsefulLifeYears" class="form-control" placeholder="4" min="1" max="30" autocomplete="off">
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
                            <input type="number" name="qty" id="createQtyInput" class="form-control font-weight-bold" value="1" min="1" autocomplete="off" required>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Harga Satuan (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold text-muted bg-light">Rp</span>
                                </div>
                                <input type="number" name="unit_price" id="createPriceInput" class="form-control font-weight-bold" value="0" min="0" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-5 col-12 mb-3">
                            <label class="form-label font-weight-bold small text-muted text-uppercase mb-1">
                                Total Estimasi Nilai
                            </label>
                            <div class="total-preview-box">
                                <span class="small font-weight-bold text-muted">Total (Qty × Harga):</span>
                                <span class="font-weight-bold text-success" id="createTotalPreview" style="font-size: 1.15rem;">Rp 0</span>
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
                                                    Jika Qty > 1, sistem otomatis membuat stiker kode unik berurutan per unit barang (contoh: <code>MK.1</code>, <code>MK.2</code>).
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
                                                    1 stiker kode aset mewakili seluruh jumlah barang (contoh: 50 Box File, 100 Buku Panduan).
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
                            <input type="text" name="condition_note" class="form-control" placeholder="Contoh: Barang baru dari vendor, fungsi prima, kemasan utuh..." autocomplete="off">
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

{{-- MODAL MUTASI ASET --}}
<div class="modal fade" id="modalMutateAsset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="formMutateAsset" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;" autocomplete="off">
            @csrf
            <div class="modal-header bg-info text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-exchange-alt mr-2"></i> Mutasi Lokasi Aset</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <strong id="mutateAssetName" class="d-block text-dark">Nama Aset</strong>
                    <code id="mutateAssetCode" class="text-primary font-weight-bold">KODE ASET</code>
                    <div class="small text-muted mt-1">Stiker kode aset fisik tidak akan diubah selama mutasi.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Plant Tujuan <span class="text-danger">*</span></label>
                    <select name="target_plant_code" class="form-control" required>
                        @foreach($plants as $p)
                            <option value="{{ $p->code }}">{{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Nama PIC / Penanggung Jawab <span class="text-danger">*</span></label>
                    <input type="text" name="pic_name" class="form-control" value="{{ auth()->user()->name }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Alasan Pemindahan <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Contoh: Kebutuhan ekspansi fasilitas, mutasi staf..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info rounded-pill px-4"><i class="fas fa-check mr-1"></i> Eksekusi Mutasi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL UPDATE KONDISI --}}
<div class="modal fade" id="modalConditionAsset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="formConditionAsset" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;" autocomplete="off">
            @csrf
            <div class="modal-header bg-warning text-dark" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-stethoscope mr-2"></i> Update Kondisi Aset</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <strong id="conditionAssetName" class="d-block text-dark">Nama Aset</strong>
                    <code id="conditionAssetCode" class="text-primary font-weight-bold">KODE ASET</code>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Kondisi Fisik Saat Ini <span class="text-danger">*</span></label>
                    <select name="condition" id="conditionSelect" class="form-control" required>
                        <option value="Baik">Baik</option>
                        <option value="Rusak">Rusak</option>
                        <option value="Perbaikan">Perbaikan</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Catatan Kondisi / Kerusakan</label>
                    <textarea name="condition_note" id="conditionNoteInput" class="form-control" rows="2" placeholder="Contoh: Layar bergaris, 2 unit bocor, perlu ganti kabel..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning rounded-pill px-4"><i class="fas fa-save mr-1"></i> Simpan Status</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL DISPOSAL ASET --}}
<div class="modal fade" id="modalDisposeAsset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="formDisposeAsset" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;" autocomplete="off">
            @csrf
            @method('DELETE')
            <div class="modal-header bg-danger text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-trash-alt mr-2"></i> Arsipkan / Hapus Aset (Disposal)</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <strong id="disposeAssetName" class="d-block text-dark">Nama Aset</strong>
                    <code id="disposeAssetCode" class="text-primary font-weight-bold">KODE ASET</code>
                </div>

                <p class="text-muted small">
                    Aset tidak akan dihapus permanen dari basis data, melainkan dialihkan statusnya menjadi <strong>DISPOSED</strong> untuk menjaga integritas pelaporan audit masa lalu.
                </p>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Alasan Penghapusan (Disposal) <span class="text-danger">*</span></label>
                    <textarea name="disposal_reason" class="form-control" rows="3" placeholder="Contoh: Rusak permanen tidak dapat diperbaiki, Dijual, Hilang..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="fas fa-archive mr-1"></i> Konfirmasi Disposal</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT ASET --}}
<div class="modal fade" id="modalEditAsset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="formEditAssetMaster" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;" autocomplete="off">
            @csrf
            @method('PUT')
            <div class="modal-header bg-info text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-edit mr-2"></i> Edit Data Aset
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border d-flex align-items-center mb-3 py-2">
                    <i class="fas fa-barcode text-primary fa-2x mr-3"></i>
                    <div>
                        <div class="font-weight-bold text-dark" id="editAssetMasterCode"></div>
                        <small class="text-muted">Kode ID Aset fisik tidak berubah saat pengeditan data teknis / perolehan.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 col-12 mb-3">
                        <label class="form-label font-weight-bold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" id="editMasterItemName" class="form-control" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Merk / Brand</label>
                        <input type="text" name="brand" id="editMasterBrand" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Sumber Perolehan</label>
                        <select name="acquisition_source" id="editMasterSource" class="form-control">
                            <option value="RAB">RAB (Rencana Anggaran Biaya)</option>
                            <option value="Hibah">Hibah</option>
                            <option value="Pengadaan Baru">Pengadaan Baru</option>
                            <option value="Sumbangan">Sumbangan</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Tanggal Pembelian</label>
                        <input type="date" name="purchase_date" id="editMasterPurchaseDate" class="form-control">
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Departemen</label>
                        <select name="department_id" id="editMasterDept" class="form-control">
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 col-12 mb-3">
                        <label class="form-label font-weight-bold">Ruangan / Sub-Area</label>
                        <select name="location_id" id="editMasterLocation" class="form-control">
                            <option value="">-- Pilih Ruangan / Area --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" data-plant="{{ $loc->plant_code }}">
                                    {{ $loc->room_name }} ({{ $loc->plant->name ?? 'Plant ' . $loc->plant_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-12 mb-3">
                        <label class="form-label font-weight-bold">Kondisi Awal <span class="text-danger">*</span></label>
                        <select name="initial_condition" id="editMasterInitialCondition" class="form-control" required>
                            <option value="Baru">Baru</option>
                            <option value="Bekas">Bekas</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-12 mb-3">
                        <label class="form-label font-weight-bold">Status Kelayakan <span class="text-danger">*</span></label>
                        <select name="condition" id="editMasterCondition" class="form-control" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Perbaikan">Perbaikan</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-12 mb-3">
                        <label class="form-label font-weight-bold">Catatan Kondisi</label>
                        <input type="text" name="condition_note" id="editMasterConditionNote" class="form-control" placeholder="Catatan kondisi...">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Qty <span class="text-danger">*</span></label>
                        <input type="number" name="qty" id="editMasterQty" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="unit_price" id="editMasterUnitPrice" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Masa Manfaat (Tahun) <span class="text-danger">*</span></label>
                        <input type="number" name="useful_life_years" id="editMasterUsefulLife" class="form-control" min="1" max="30" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Spesifikasi / Keterangan</label>
                    <textarea name="specification" id="editMasterSpecification" class="form-control" rows="2"></textarea>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const plantSelect = document.getElementById('modalPlantSelect');
    const locSelect = document.getElementById('modalLocationSelect');
    if (plantSelect && locSelect) {
        function filterLocations() {
            const selectedPlant = plantSelect.value;
            const options = locSelect.querySelectorAll('option');
            options.forEach(opt => {
                const plant = opt.getAttribute('data-plant');
                if (!plant || plant === selectedPlant) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) {
                        opt.selected = false;
                    }
                }
            });
        }
        plantSelect.addEventListener('change', filterLocations);
        filterLocations();
    }

    // Live Total Estimasi & Sinkronisasi Kategori di Modal Tambah Aset
    const createQty = document.getElementById('createQtyInput');
    const createPrice = document.getElementById('createPriceInput');
    const createTotal = document.getElementById('createTotalPreview');
    const createCategory = document.getElementById('createCategoryCode');
    const createUsefulLife = document.getElementById('createUsefulLifeYears');

    function calculateCreateTotal() {
        if (!createQty || !createPrice || !createTotal) return;
        const q = parseFloat(createQty.value) || 0;
        const p = parseFloat(createPrice.value) || 0;
        const total = q * p;
        createTotal.innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
    }

    if (createQty) createQty.addEventListener('input', calculateCreateTotal);
    if (createPrice) createPrice.addEventListener('input', calculateCreateTotal);

    if (createCategory && createUsefulLife) {
        function syncCategoryUsefulLife() {
            const opt = createCategory.options[createCategory.selectedIndex];
            const years = opt ? opt.getAttribute('data-useful-life') : '4';
            if (years) {
                createUsefulLife.placeholder = years + ' Th (Default Kategori)';
            }
        }
        createCategory.addEventListener('change', syncCategoryUsefulLife);
        syncCategoryUsefulLife();
    }

    calculateCreateTotal();
});

function openEditModal(asset) {
    document.getElementById('formEditAssetMaster').action = '/assets/' + asset.id;
    document.getElementById('editAssetMasterCode').innerText = asset.asset_code + ' — ' + (asset.item_name || '');
    document.getElementById('editMasterItemName').value = asset.item_name || '';
    document.getElementById('editMasterBrand').value = asset.brand || '';
    document.getElementById('editMasterSource').value = asset.acquisition_source || 'RAB';
    document.getElementById('editMasterPurchaseDate').value = asset.purchase_date ? asset.purchase_date.substring(0, 10) : '';
    document.getElementById('editMasterDept').value = asset.department_id || '';
    document.getElementById('editMasterLocation').value = asset.location_id || '';
    document.getElementById('editMasterInitialCondition').value = asset.initial_condition || 'Baru';
    document.getElementById('editMasterCondition').value = asset.condition || 'Baik';
    document.getElementById('editMasterConditionNote').value = asset.condition_note || '';
    document.getElementById('editMasterUnitPrice').value = Math.round(asset.unit_price) || 0;
    document.getElementById('editMasterQty').value = asset.qty || 1;
    document.getElementById('editMasterUsefulLife').value = asset.useful_life_years || 4;
    document.getElementById('editMasterSpecification').value = asset.specification || '';
    $('#modalEditAsset').modal('show');
}

function openMutateModal(asset) {
    document.getElementById('formMutateAsset').action = '/assets/' + asset.id + '/mutate';
    document.getElementById('mutateAssetName').innerText = asset.item_name;
    document.getElementById('mutateAssetCode').innerText = asset.asset_code;
    $('#modalMutateAsset').modal('show');
}

function openConditionModal(asset) {
    document.getElementById('formConditionAsset').action = '/assets/' + asset.id + '/condition';
    document.getElementById('conditionAssetName').innerText = asset.item_name;
    document.getElementById('conditionAssetCode').innerText = asset.asset_code;
    document.getElementById('conditionSelect').value = asset.condition || 'Baik';
    document.getElementById('conditionNoteInput').value = asset.condition_note || '';
    $('#modalConditionAsset').modal('show');
}

function openDisposeModal(asset) {
    document.getElementById('formDisposeAsset').action = '/assets/' + asset.id;
    document.getElementById('disposeAssetName').innerText = asset.item_name;
    document.getElementById('disposeAssetCode').innerText = asset.asset_code;
    $('#modalDisposeAsset').modal('show');
}
</script>
@endsection

