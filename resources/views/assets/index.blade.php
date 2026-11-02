@extends('layouts.app')

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
                        <label class="small font-weight-bold text-muted mb-1">Kondisi</label>
                        <select name="condition" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all">Semua Kondisi</option>
                            <option value="Baik" {{ request('condition') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak" {{ request('condition') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Perbaikan" {{ request('condition') == 'Perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-8 col-12 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Cari Aset</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="ID / Nama / Merk..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                @if(request()->hasAny(['group', 'plant', 'dept', 'category', 'condition', 'search']))
                                    <a href="{{ route('assets.index') }}" class="btn btn-light border"><i class="fas fa-times"></i></a>
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>ID ASET & Nama</th>
                                <th>Departemen</th>
                                <th>Posisi Plant</th>
                                <th>Kondisi</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Total Perolehan</th>
                                <th class="text-center">Umur / Bulan</th>
                                <th class="text-right">Akumulasi Penyusutan</th>
                                <th class="text-right font-weight-bold text-primary">Nilai Buku (Sisa)</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
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
                                        @if(!$asset->is_price_complete)
                                            <span class="badge badge-warning text-dark" style="font-size: 10px;">Harga Belum Lengkap</span>
                                        @endif
                                        @if($asset->asset_group !== 'UTAMA')
                                            <span class="badge badge-info" style="font-size: 10px;">{{ $asset->asset_group }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">{{ $asset->department->code ?? '-' }}</span>
                                        <div class="small text-muted">{{ $asset->department->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @if($asset->current_plant_code === '01')
                                            <span class="badge badge-primary">Plant A</span>
                                        @elseif($asset->current_plant_code === '02')
                                            <span class="badge badge-info">Plant B</span>
                                        @else
                                            <span class="badge badge-secondary">Mobile</span>
                                        @endif

                                        @if($asset->origin_plant_code !== $asset->current_plant_code)
                                            <div class="small text-danger" title="Asal lahir berbeda dengan fisik saat ini">
                                                <i class="fas fa-exchange-alt mr-1"></i>Lahir: Plant {{ $asset->origin_plant_code }}
                                            </div>
                                        @endif
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
                                    <td class="text-right">
                                        Rp {{ number_format($asset->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light">{{ $asset->useful_life_years }} Th</span>
                                        <div class="small text-muted">{{ $asset->months_elapsed }} Bln</div>
                                    </td>
                                    <td class="text-right text-muted">
                                        Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right font-weight-bold text-primary">
                                        Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" title="Pindah / Mutasi Aset"
                                                    onclick="openMutateModal({{ json_encode($asset) }})">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" title="Update Kondisi"
                                                    onclick="openConditionModal({{ json_encode($asset) }})">
                                                <i class="fas fa-stethoscope"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" title="Edit Aset"
                                                    onclick="openEditModal({{ json_encode($asset) }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" title="Disposal Aset"
                                                    onclick="openDisposeModal({{ json_encode($asset) }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
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
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form action="{{ route('assets.store') }}" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            @csrf
            <div class="modal-header bg-primary text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Tambah Aset Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-8 col-12 mb-3">
                        <label class="form-label font-weight-bold">Nama Aset <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" placeholder="Contoh: Meja Kantor, Laptop Lenovo" required>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Singkatan Kode <span class="text-danger">*</span></label>
                        <input type="text" name="item_abbr" class="form-control text-uppercase" placeholder="Contoh: MK, LP, KK" maxlength="10" required>
                        <small class="text-muted">Untuk penomoran: HII-[KODE]...</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-12 mb-3">
                        <label class="form-label font-weight-bold">Merk</label>
                        <input type="text" name="brand" class="form-control" placeholder="Contoh: Active Furniture, Epson, Samsung">
                    </div>
                    <div class="col-md-6 col-12 mb-3">
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
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-12 mb-3">
                        <label class="form-label font-weight-bold">Lokasi Plant Fisik <span class="text-danger">*</span></label>
                        <select name="current_plant_code" class="form-control" required>
                            @foreach($plants as $p)
                                <option value="{{ $p->code }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
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
                    <textarea name="specification" class="form-control" rows="2" placeholder="Catatan dimensi, nomor seri, kelengkapan..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save mr-1"></i> Simpan Aset</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL MUTASI ASET --}}
<div class="modal fade" id="modalMutateAsset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="formMutateAsset" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
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
        <form id="formConditionAsset" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
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
        <form id="formDisposeAsset" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
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

<script>
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

