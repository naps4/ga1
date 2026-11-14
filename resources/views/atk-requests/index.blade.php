@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item active">Permintaan</li>
@endsection

@push('styles')
<style>
    .atk-row-parent {
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }
    .atk-row-parent:hover {
        background-color: #f8fafc !important;
    }
    .atk-row-parent.expanded {
        background-color: #eff6ff !important;
    }
    .chevron-toggle-icon {
        display: inline-block;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), color 0.15s ease-in-out;
        font-size: 0.75rem;
        width: 16px;
        text-align: center;
    }
    .atk-row-parent.expanded .chevron-toggle-icon {
        transform: rotate(90deg);
        color: #2563eb !important;
    }
    .atk-item-details-row td {
        background-color: #f8fafc !important;
        border-top: none !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill shadow-sm mb-3">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-pill shadow-sm mb-3">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show rounded-pill shadow-sm mb-3">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-lg shadow-sm mb-3">
                <i class="fas fa-times-circle mr-1"></i> <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-1 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card support-shell-card">
            {{-- Card Header --}}
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem; padding:1rem 1.25rem .6rem;">
                <div>
                    <h3 class="card-title mb-0 font-weight-bold" style="font-size:1.05rem;">
                        <i class="fas fa-boxes mr-2 text-primary"></i>Daftar Permintaan
                    </h3>
                    <p class="text-muted mb-0 d-none d-md-block" style="font-size:.78rem;margin-top:.2rem;">Pengajuan kebutuhan ATK / RT divisi bulanan paling lambat tanggal 28</p>
                </div>
                <a href="{{ route('atk-requests.create') }}" class="btn btn-primary btn-sm shadow-sm" style="border-radius:.6rem;white-space:nowrap;">
                    <i class="fas fa-plus mr-1"></i> Permintaan Baru
                </a>
            </div>

            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            {{-- Filter Panel --}}
            <div class="px-4 py-3 bg-light border-top border-bottom">
                <form action="{{ route('atk-requests.index') }}" method="GET" id="filterForm">
                    <div class="row">
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" style="border-radius: 0.5rem;">
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" style="border-radius: 0.5rem;">
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted">Tipe</label>
                            <select name="request_type" class="form-control form-control-sm" style="border-radius: 0.5rem;">
                                <option value="all" {{ request('request_type') == 'all' ? 'selected' : '' }}>Semua Tipe</option>
                                <option value="ATK" {{ request('request_type') == 'ATK' ? 'selected' : '' }}>ATK</option>
                                <option value="Rumah Tangga" {{ request('request_type') == 'Rumah Tangga' ? 'selected' : '' }}>Sanitasi Non Produksi (SNP)</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted">Status</label>
                            <select name="status_approval" class="form-control form-control-sm" style="border-radius: 0.5rem;">
                                <option value="all" {{ request('status_approval') == 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="Draft" {{ request('status_approval') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                <option value="Menunggu Persetujuan Manager" {{ request('status_approval') == 'Menunggu Persetujuan Manager' ? 'selected' : '' }}>Review Manager</option>
                                <option value="Menunggu Persetujuan Admin GA" {{ request('status_approval') == 'Menunggu Persetujuan Admin GA' ? 'selected' : '' }}>Review Admin GA</option>
                                <option value="Disetujui" {{ request('status_approval') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="Ditolak/Revisi" {{ request('status_approval') == 'Ditolak/Revisi' ? 'selected' : '' }}>Ditolak/Revisi</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted">Nama Barang</label>
                            <input type="text" name="item_name" class="form-control form-control-sm" placeholder="Cari item..." value="{{ request('item_name') }}" style="border-radius: 0.5rem;">
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted">Divisi</label>
                            <input type="text" name="divisi" class="form-control form-control-sm" placeholder="Nama divisi..." value="{{ request('divisi') }}" style="border-radius: 0.5rem;">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap" style="gap: .5rem;">
                        <div class="d-flex align-items-center" style="gap: .5rem;">
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-3" style="border-radius: 0.5rem;">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('atk-requests.index') }}" class="btn btn-secondary btn-sm shadow-sm px-3" style="border-radius: 0.5rem;">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        </div>
                        
                        <div class="d-flex align-items-center flex-wrap" style="gap: .5rem;">
                            <!-- Modal Button 1: Rekap Total -->
                            <button type="button" class="btn btn-info btn-sm text-white shadow-sm px-3" data-toggle="modal" data-target="#modalRekapTotal" style="border-radius: 0.5rem;">
                                <i class="fas fa-boxes mr-1"></i> Rekap Total (Kategori)
                            </button>

                            <!-- Modal Button 2: Rekap Per Departemen -->
                            <button type="button" class="btn btn-teal btn-sm text-white shadow-sm px-3" data-toggle="modal" data-target="#modalRekapDepartemen" style="border-radius: 0.5rem; background-color: #0d9488;">
                                <i class="fas fa-sitemap mr-1"></i> Rekap Per Departemen
                            </button>

                            <!-- Dropdown Export Menu -->
                            <div class="dropdown">
                                <button class="btn btn-danger btn-sm dropdown-toggle shadow-sm px-3" type="button" id="dropdownExportMenu" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false" style="border-radius: 0.5rem;">
                                    <i class="fas fa-file-export mr-1"></i> Ekspor Laporan
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 mt-1" aria-labelledby="dropdownExportMenu" style="border-radius: 0.75rem; min-width: 260px; font-size: 0.85rem; z-index: 1050;">
                                    <h6 class="dropdown-header text-primary font-weight-bold py-1" style="font-size: 0.78rem;"><i class="fas fa-file-pdf mr-1 text-danger"></i> Rekap Total Kebutuhan (PDF)</h6>
                                    <a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="exportPdf('aggregation', 'ATK')">
                                        <i class="fas fa-pencil-alt text-primary mr-2"></i> Rekap Total ATK
                                    </a>
                                    <a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="exportPdf('aggregation', 'Rumah Tangga')">
                                        <i class="fas fa-pump-soap text-warning mr-2"></i> Rekap Total Sanitasi Non Produksi (SNP)
                                    </a>
                                    <a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="exportPdf('aggregation', 'all')">
                                        <i class="fas fa-boxes text-info mr-2"></i> Rekap Total Keseluruhan
                                    </a>

                                    <div class="dropdown-divider my-1"></div>
                                    <h6 class="dropdown-header text-success font-weight-bold py-1" style="font-size: 0.78rem;"><i class="fas fa-sitemap mr-1 text-teal" style="color:#0d9488;"></i> Distribusi Departemen (PDF Landscape)</h6>
                                    <a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="exportPdf('department', 'ATK')">
                                        <i class="fas fa-building text-primary mr-2"></i> Matriks Departemen ATK
                                    </a>
                                    <a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="exportPdf('department', 'Rumah Tangga')">
                                        <i class="fas fa-warehouse text-warning mr-2"></i> Matriks Dept Sanitasi Non Produksi (SNP)
                                    </a>

                                    <div class="dropdown-divider my-1"></div>
                                    <h6 class="dropdown-header text-dark font-weight-bold py-1" style="font-size: 0.78rem;"><i class="fas fa-file-alt mr-1 text-secondary"></i> Laporan Lainnya</h6>
                                    <a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="exportPdf('detail')">
                                        <i class="fas fa-list text-secondary mr-2"></i> Laporan Detail Transaksi (PDF)
                                    </a>
                                    <a class="dropdown-item py-1.5 font-weight-bold text-success" style="background-color: #f0fdf4;" href="javascript:void(0)" onclick="exportExcel()">
                                        <i class="fas fa-file-excel text-success mr-2"></i> Unduh Rekap Lengkap (Excel)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            {{-- Table --}}
            <div class="table-responsive" style="padding:0 1.25rem 1.25rem;">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 100px;">No. Doc</th>
                            <th style="width: 130px;">Tipe</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Nama Pengaju</th>
                            <th>Divisi</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Disetujui Oleh</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr class="atk-row-parent" data-target="#items-collapse-{{ $req->id }}" title="Klik baris ini untuk melihat rincian barang">
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-chevron-right text-muted chevron-toggle-icon mr-1.5" id="chevron-{{ $req->id }}"></i>
                                    <div>
                                        <strong class="text-primary font-weight-bold">
                                            {{ $req->formatted_doc_number }}
                                        </strong>
                                        @if($req->revision_number > 0)
                                            <span class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 10px;">
                                                Rev. {{ $req->revision_number }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if(($req->request_type ?? 'ATK') === 'Rumah Tangga')
                                    <span class="badge badge-warning px-2 py-1 text-dark" style="border-radius: 0.5rem; font-size: 0.75rem;">
                                        <i class="fas fa-pump-soap mr-1"></i> Sanitasi Non Produksi (SNP)
                                    </span>
                                @else
                                    <span class="badge badge-primary px-2 py-1" style="border-radius: 0.5rem; font-size: 0.75rem;">
                                        <i class="fas fa-pencil-alt mr-1"></i> ATK
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="font-weight-600" style="font-size:.85rem;">{{ $req->tanggal_pengajuan->format('d M Y') }}</span>
                            </td>
                            <td>
                                <span class="font-weight-600">{{ $req->nama }}</span>
                            </td>
                            <td>{{ $req->divisi }}</td>
                            <td>
                                <span class="badge badge-info">{{ $req->periode }}</span>
                            </td>
                            <td>
                                @if($req->status_approval === 'Draft')
                                    <span class="badge badge-secondary">Draft</span>
                                @elseif($req->status_approval === 'Menunggu Persetujuan Manager')
                                    <span class="badge badge-warning">Review Manager</span>
                                @elseif($req->status_approval === 'Menunggu Persetujuan Admin GA')
                                    <span class="badge badge-info">Review Admin GA</span>
                                @elseif($req->status_approval === 'Disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($req->status_approval === 'Ditolak/Revisi')
                                    <span class="badge badge-danger">Ditolak/Revisi</span>
                                @else
                                    <span class="badge badge-warning">{{ $req->status_approval }}</span>
                                @endif
                            </td>
                            <td>
                                @if($req->approved_by)
                                    <span class="small font-weight-bold text-muted"><i class="fas fa-user-check mr-1"></i>{{ $req->approver->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center" style="gap:.25rem;">
                                    <a href="{{ route('atk-requests.show', $req) }}" class="btn btn-sm btn-light border" style="border-radius:.45rem;" title="Detail"><i class="fas fa-eye"></i></a>
                                    
                                    @php
                                        $isOwner = ($req->user_id === auth()->id());
                                        $hasRejectedItems = $req->items->where('status_approval', 'Ditolak')->count() > 0;
                                        $canEdit = $isOwner && in_array($req->status_approval, ['Draft', 'Ditolak/Revisi', 'Menunggu Persetujuan Manager', 'Menunggu Persetujuan Admin GA']);
                                    @endphp

                                    @if($canEdit)
                                        @if($req->status_approval === 'Ditolak/Revisi' || $hasRejectedItems)
                                            <a href="{{ route('atk-requests.edit', $req) }}" class="btn btn-sm btn-warning text-dark font-weight-bold" style="border-radius:.45rem;" title="Revisi Permintaan">
                                                <i class="fas fa-edit mr-1"></i> Revisi
                                            </a>
                                        @else
                                            <a href="{{ route('atk-requests.edit', $req) }}" class="btn btn-sm btn-success" style="border-radius:.45rem;" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                        @endif
                                    @endif

                                    <a href="{{ route('atk-requests.print', $req) }}" target="_blank" class="btn btn-sm btn-info text-white" style="border-radius:.45rem;" title="Cetak"><i class="fas fa-print"></i></a>
                                    
                                    @if(in_array($req->status_approval, ['Draft', 'Ditolak/Revisi', 'Menunggu Persetujuan Manager']) && $isOwner)
                                        <button type="button" class="btn btn-sm btn-danger btn-delete" style="border-radius:.45rem;"
                                            data-id="{{ $req->id }}" 
                                            data-name="{{ $req->formatted_doc_number }}" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $req->id }}" action="{{ route('atk-requests.destroy', $req) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Dropdown Detail Row --}}
                        <tr class="collapse atk-item-details-row" id="items-collapse-{{ $req->id }}">
                            <td colspan="9" class="p-0 border-0">
                                <div class="p-3 my-2 mx-3 bg-white rounded-4 shadow-sm border" style="border-left: 4px solid #3b82f6 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom flex-wrap" style="gap: .5rem;">
                                        <div class="d-flex align-items-center flex-wrap" style="gap: .5rem;">
                                            <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                                                <i class="fas fa-boxes text-primary mr-1"></i> Rincian Barang Diminta:
                                            </span>
                                            <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                                                Total: <strong>{{ $req->items->count() }} item</strong>
                                            </span>
                                            @if($req->items->count() > 0)
                                                @php
                                                    $appCount = $req->items->where('status_approval', 'Disetujui')->count();
                                                    $rejCount = $req->items->where('status_approval', 'Ditolak')->count();
                                                    $penCount = $req->items->filter(fn($i) => $i->status_approval === 'Pending' || empty($i->status_approval))->count();
                                                @endphp
                                                <span class="badge badge-success px-2 py-1 font-weight-normal" style="font-size: 11px;"><i class="fas fa-check mr-1"></i>{{ $appCount }} Disetujui</span>
                                                <span class="badge badge-danger px-2 py-1 font-weight-normal" style="font-size: 11px;"><i class="fas fa-times mr-1"></i>{{ $rejCount }} Ditolak</span>
                                                <span class="badge badge-warning text-dark px-2 py-1 font-weight-normal" style="font-size: 11px;"><i class="fas fa-hourglass-half mr-1"></i>{{ $penCount }} Pending</span>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('atk-requests.show', $req) }}" class="btn btn-xs btn-primary font-weight-bold rounded-pill px-3 py-1 shadow-sm">
                                                <i class="fas fa-external-link-alt mr-1"></i> Buka Detail Dokumen &rarr;
                                            </a>
                                        </div>
                                    </div>

                                    @if($req->items->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.82rem;">
                                                <thead class="bg-light text-muted">
                                                    <tr>
                                                        <th style="width: 40px;" class="text-center">No</th>
                                                        <th>Nama Barang</th>
                                                        <th style="width: 90px;" class="text-center">Satuan</th>
                                                        <th style="width: 25%;">Spesifikasi</th>
                                                        <th style="width: 80px;" class="text-center">Qty</th>
                                                        <th style="width: 120px;" class="text-center">Status Item</th>
                                                        <th>Catatan Item</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($req->items as $iIndex => $item)
                                                        <tr>
                                                            <td class="text-center align-middle">{{ $iIndex + 1 }}</td>
                                                            <td class="font-weight-600 align-middle {{ $item->is_custom ? 'text-danger' : '' }}">
                                                                {{ $item->item_name }}
                                                                @if($item->is_custom)
                                                                    <span class="badge badge-danger text-white ml-1" style="font-size: 9px;">Khusus</span>
                                                                @endif
                                                                @if($item->is_revised)
                                                                    <span class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 9px;"><i class="fas fa-history"></i> Revisi</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge badge-secondary">{{ $item->unit }}</span>
                                                            </td>
                                                            <td class="align-middle text-muted">
                                                                {{ $item->description ?? '-' }}
                                                                @if($item->is_revised && $item->previous_description && $item->previous_description !== $item->description)
                                                                    <div class="small text-muted font-italic" style="font-size: 10px;">
                                                                        Spek awal: {{ $item->previous_description }}
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="text-center align-middle font-weight-bold">
                                                                {{ $item->quantity }}
                                                                @if($item->is_revised && $item->previous_quantity !== null)
                                                                    <div class="small text-muted" style="font-size: 10px;">
                                                                        <del class="text-danger">{{ $item->previous_quantity }}</del> &rarr; <span class="text-success">{{ $item->quantity }}</span>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                @if($item->status_approval === 'Disetujui')
                                                                    <span class="badge badge-success px-2 py-0.5"><i class="fas fa-check mr-1"></i>Disetujui</span>
                                                                @elseif($item->status_approval === 'Ditolak')
                                                                    <span class="badge badge-danger px-2 py-0.5"><i class="fas fa-times mr-1"></i>Ditolak</span>
                                                                @else
                                                                    <span class="badge badge-warning text-dark px-2 py-0.5"><i class="fas fa-hourglass-half mr-1"></i>Pending</span>
                                                                @endif
                                                            </td>
                                                            <td class="align-middle text-muted small">{{ $item->notes ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-2 text-muted small">
                                            <i class="fas fa-info-circle mr-1"></i> Belum ada rincian item pada dokumen pengajuan ini.
                                        </div>
                                    @endif

                                    @if($req->manager_notes || $req->notes)
                                        <div class="mt-2 pt-2 border-top small d-flex flex-wrap" style="gap: 1rem;">
                                            @if($req->manager_notes)
                                                <div class="text-muted">
                                                    <strong class="text-dark"><i class="fas fa-comment text-warning mr-1"></i> Catatan Manager:</strong> {{ $req->manager_notes }}
                                                </div>
                                            @endif
                                            @if($req->notes)
                                                <div class="text-muted">
                                                    <strong class="text-dark"><i class="fas fa-comment text-info mr-1"></i> Catatan Admin GA:</strong> {{ $req->notes }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-pencil-alt fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">Belum ada pengajuan. <a href="{{ route('atk-requests.create') }}">Buat pengajuan baru</a>.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isManager())
{{-- MODAL 1: REKAP TOTAL KEBUTUHAN BARANG (DIKELOMPOKKAN PER KATEGORI) --}}
<div class="modal fade" id="modalRekapTotal" tabindex="-1" role="dialog" aria-labelledby="modalRekapTotalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 0.8rem; overflow: hidden;">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalRekapTotalTitle">
                    <i class="fas fa-boxes text-primary mr-2"></i>Rekap Total Kebutuhan Barang (Per Kategori)
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 px-3 small rounded-pill mb-3">
                    <i class="fas fa-info-circle mr-1"></i> Data rekapitulasi di bawah ini terpisah per kategori barang dan dihitung berdasarkan filter aktif.
                </div>

                {{-- Nav Tabs --}}
                <ul class="nav nav-pills mb-3 nav-fill" id="rekapTotalTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold shadow-sm" id="atk-total-tab" data-toggle="pill" href="#pills-atk-total" role="tab" aria-controls="pills-atk-total" aria-selected="true" style="border-radius: 0.5rem;">
                            <i class="fas fa-pencil-alt mr-1"></i> ATK (Alat Tulis Kantor)
                            <span class="badge badge-primary ml-1">{{ $atkGrandTotalQty ?? 0 }} Qty</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold shadow-sm" id="household-total-tab" data-toggle="pill" href="#pills-household-total" role="tab" aria-controls="pills-household-total" aria-selected="false" style="border-radius: 0.5rem;">
                            <i class="fas fa-pump-soap mr-1"></i> Sanitasi Non Produksi (SNP)
                            <span class="badge badge-warning text-dark ml-1">{{ $householdGrandTotalQty ?? 0 }} Qty</span>
                        </a>
                    </li>
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content" id="rekapTotalTabContent">
                    {{-- Tab ATK --}}
                    <div class="tab-pane fade show active" id="pills-atk-total" role="tabpanel" aria-labelledby="atk-total-tab">
                        <div class="table-responsive style-scroll" style="max-height: 420px;">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 45px;" class="text-center">No</th>
                                        <th>Nama Barang ATK</th>
                                        <th style="width: 110px;" class="text-center">Satuan</th>
                                        <th style="width: 140px;" class="text-center">Total Kebutuhan</th>
                                        <th style="width: 130px;" class="text-center">Frekuensi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $noAtk = 1; @endphp
                                    @forelse($atkByCategory ?? [] as $catName => $catData)
                                        <tr class="bg-light font-weight-bold text-primary">
                                            <td colspan="5">
                                                <i class="fas fa-folder-open mr-1"></i> Kategori: {{ $catName }}
                                                <span class="badge badge-light border ml-1 font-weight-normal text-muted">{{ count($catData['items']) }} jenis</span>
                                            </td>
                                        </tr>
                                        @foreach($catData['items'] as $item)
                                            <tr>
                                                <td class="text-center text-muted">{{ $noAtk++ }}</td>
                                                <td class="font-weight-600">
                                                    {{ $item['name'] }}
                                                    @if($item['is_custom'])
                                                        <span class="badge badge-danger ml-1" style="font-size: 9px;">Custom</span>
                                                    @endif
                                                </td>
                                                <td class="text-center"><span class="badge badge-light border">{{ $item['unit'] }}</span></td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary px-2.5 py-1" style="font-size: 0.85rem;">{{ $item['total_qty'] }}</span>
                                                </td>
                                                <td class="text-center small text-muted">{{ $item['request_count'] }}x diajukan</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light" style="font-size: 0.85rem;">
                                            <td colspan="3" class="text-right font-weight-bold text-muted">Subtotal {{ $catName }}:</td>
                                            <td class="text-center font-weight-bold text-primary">{{ $catData['subtotal_qty'] }}</td>
                                            <td></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                Tidak ada data kebutuhan ATK pada filter saat ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(($atkGrandTotalQty ?? 0) > 0)
                                    <tfoot class="bg-primary text-white font-weight-bold">
                                        <tr>
                                            <td colspan="3" class="text-right">GRAND TOTAL KEBUTUHAN ATK:</td>
                                            <td class="text-center" style="font-size: 1.05rem;">{{ $atkGrandTotalQty }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- Tab Sanitasi Non Produksi --}}
                    <div class="tab-pane fade" id="pills-household-total" role="tabpanel" aria-labelledby="household-total-tab">
                        <div class="table-responsive style-scroll" style="max-height: 420px;">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="bg-warning text-white">
                                    <tr>
                                        <th style="width: 45px;" class="text-center">No</th>
                                        <th>Nama Barang Sanitasi Non Produksi</th>
                                        <th style="width: 110px;" class="text-center">Satuan</th>
                                        <th style="width: 140px;" class="text-center">Total Kebutuhan</th>
                                        <th style="width: 130px;" class="text-center">Frekuensi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $noRt = 1; @endphp
                                    @forelse($householdByCategory ?? [] as $catName => $catData)
                                        <tr class="bg-light font-weight-bold text-warning">
                                            <td colspan="5">
                                                <i class="fas fa-folder-open mr-1"></i> Kategori: {{ $catName }}
                                                <span class="badge badge-light border ml-1 font-weight-normal text-muted">{{ count($catData['items']) }} jenis</span>
                                            </td>
                                        </tr>
                                        @foreach($catData['items'] as $item)
                                            <tr>
                                                <td class="text-center text-muted">{{ $noRt++ }}</td>
                                                <td class="font-weight-600">
                                                    {{ $item['name'] }}
                                                    @if($item['is_custom'])
                                                        <span class="badge badge-danger ml-1" style="font-size: 9px;">Custom</span>
                                                    @endif
                                                </td>
                                                <td class="text-center"><span class="badge badge-light border">{{ $item['unit'] }}</span></td>
                                                <td class="text-center">
                                                    <span class="badge badge-warning text-dark px-2.5 py-1" style="font-size: 0.85rem;">{{ $item['total_qty'] }}</span>
                                                </td>
                                                <td class="text-center small text-muted">{{ $item['request_count'] }}x diajukan</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light" style="font-size: 0.85rem;">
                                            <td colspan="3" class="text-right font-weight-bold text-muted">Subtotal {{ $catName }}:</td>
                                            <td class="text-center font-weight-bold text-warning">{{ $catData['subtotal_qty'] }}</td>
                                            <td></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                Tidak ada data kebutuhan Sanitasi Non Produksi pada filter saat ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(($householdGrandTotalQty ?? 0) > 0)
                                    <tfoot class="bg-warning text-dark font-weight-bold">
                                        <tr>
                                            <td colspan="3" class="text-right">GRAND TOTAL KEBUTUHAN SANITASI NON PRODUKSI:</td>
                                            <td class="text-center" style="font-size: 1.05rem;">{{ $householdGrandTotalQty }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div>
                    <button type="button" onclick="exportPdf('aggregation', 'ATK')" class="btn btn-outline-primary btn-sm rounded-pill font-weight-bold mr-1">
                        <i class="fas fa-file-pdf mr-1"></i> Cetak Rekap ATK
                    </button>
                    <button type="button" onclick="exportPdf('aggregation', 'Rumah Tangga')" class="btn btn-outline-warning text-dark btn-sm rounded-pill font-weight-bold">
                        <i class="fas fa-file-pdf mr-1"></i> Cetak Rekap SNP
                    </button>
                </div>
                <div>
                    <button type="button" onclick="exportExcel()" class="btn btn-success btn-sm rounded-pill font-weight-bold mr-1">
                        <i class="fas fa-file-excel mr-1"></i> Unduh Excel
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: REKAP PER DEPARTEMEN / MATRIKS DISTRIBUSI --}}
<div class="modal fade" id="modalRekapDepartemen" tabindex="-1" role="dialog" aria-labelledby="modalRekapDepartemenTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 0.8rem; overflow: hidden;">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalRekapDepartemenTitle">
                    <i class="fas fa-sitemap mr-2" style="color: #0d9488;"></i>Rekapitulasi Kebutuhan Per Departemen (Matriks Distribusi & Purchasing)
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-teal py-2 px-3 small rounded-pill mb-3" style="background-color: #ccfbf1; color: #115e59;">
                    <i class="fas fa-info-circle mr-1"></i> Matriks ini memudahkan tim <strong>Purchasing</strong> saat memesan dan tim <strong>GA</strong> dalam mendistribusikan barang ke tiap divisi secara akurat.
                </div>

                {{-- Nav Tabs --}}
                <ul class="nav nav-pills mb-3 nav-fill" id="rekapDeptTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold shadow-sm" id="atk-dept-tab" data-toggle="pill" href="#pills-atk-dept" role="tab" aria-controls="pills-atk-dept" aria-selected="true" style="border-radius: 0.5rem;">
                            <i class="fas fa-pencil-alt mr-1"></i> Distribusi ATK Per Divisi
                            <span class="badge badge-primary ml-1">{{ count($atkDeptList ?? []) }} Divisi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold shadow-sm" id="household-dept-tab" data-toggle="pill" href="#pills-household-dept" role="tab" aria-controls="pills-household-dept" aria-selected="false" style="border-radius: 0.5rem;">
                            <i class="fas fa-pump-soap mr-1"></i> Distribusi Sanitasi Non Produksi (SNP) Per Divisi
                            <span class="badge badge-warning text-dark ml-1">{{ count($householdDeptList ?? []) }} Divisi</span>
                        </a>
                    </li>
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content" id="rekapDeptTabContent">
                    {{-- Tab 1: ATK Matrix --}}
                    <div class="tab-pane fade show active" id="pills-atk-dept" role="tabpanel" aria-labelledby="atk-dept-tab">
                        <div class="table-responsive style-scroll" style="max-height: 440px;">
                            <table class="table table-bordered table-sm table-hover mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 35px;" class="text-center" rowspan="2">No</th>
                                        <th style="min-width: 180px;" rowspan="2">Nama Barang ATK</th>
                                        <th style="width: 80px;" class="text-center" rowspan="2">Satuan</th>
                                        @if(count($atkDeptList ?? []) > 0)
                                            <th colspan="{{ count($atkDeptList) }}" class="text-center bg-dark" style="background-color: #1e3a8a !important; font-size: 0.8rem;">
                                                RINCIAN KEBUTUHAN PER DEPARTEMEN / DIVISI
                                            </th>
                                        @endif
                                        <th style="width: 110px;" class="text-center" rowspan="2">TOTAL</th>
                                    </tr>
                                    @if(count($atkDeptList ?? []) > 0)
                                        <tr>
                                            @foreach($atkDeptList as $dept)
                                                <th class="text-center text-nowrap" style="background-color: #2563eb; font-size: 0.78rem; min-width: 85px;">
                                                    {{ $dept }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @php $noDeptAtk = 1; @endphp
                                    @forelse($atkByCategory ?? [] as $catName => $catData)
                                        <tr class="bg-light font-weight-bold text-primary">
                                            <td colspan="{{ 3 + count($atkDeptList ?? []) + 1 }}">
                                                <i class="fas fa-folder-open mr-1"></i> Kategori: {{ $catName }}
                                            </td>
                                        </tr>
                                        @foreach($catData['items'] as $item)
                                            <tr>
                                                <td class="text-center text-muted">{{ $noDeptAtk++ }}</td>
                                                <td class="font-weight-600">
                                                    {{ $item['name'] }}
                                                    @if($item['is_custom'])
                                                        <span class="badge badge-danger ml-1" style="font-size: 8px;">Custom</span>
                                                    @endif
                                                </td>
                                                <td class="text-center"><span class="badge badge-light border">{{ $item['unit'] }}</span></td>
                                                
                                                @foreach($atkDeptList as $dept)
                                                    @php $qty = $item['dept_quantities'][$dept] ?? 0; @endphp
                                                    <td class="text-center {{ $qty > 0 ? 'font-weight-bold text-dark bg-white' : 'text-muted' }}" style="{{ $qty > 0 ? 'background-color: #eff6ff !important;' : '' }}">
                                                        {{ $qty > 0 ? $qty : '-' }}
                                                    </td>
                                                @endforeach

                                                <td class="text-center font-weight-bold text-primary" style="font-size: 0.9rem;">
                                                    {{ $item['total_qty'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light" style="font-size: 0.82rem;">
                                            <td colspan="3" class="text-right font-weight-bold text-muted">Subtotal {{ $catName }}:</td>
                                            @foreach($atkDeptList as $dept)
                                                <td class="text-center font-weight-bold text-primary">
                                                    {{ ($catData['dept_subtotals'][$dept] ?? 0) > 0 ? $catData['dept_subtotals'][$dept] : '-' }}
                                                </td>
                                            @endforeach
                                            <td class="text-center font-weight-bold text-primary">{{ $catData['subtotal_qty'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 3 + count($atkDeptList ?? []) + 1 }}" class="text-center py-4 text-muted">
                                                Tidak ada data permintaan ATK ditemukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(($atkGrandTotalQty ?? 0) > 0)
                                    <tfoot class="bg-primary text-white font-weight-bold">
                                        <tr>
                                            <td colspan="3" class="text-right">GRAND TOTAL:</td>
                                            @foreach($atkDeptList as $dept)
                                                <td class="text-center">{{ ($atkDeptGrandTotals[$dept] ?? 0) > 0 ? $atkDeptGrandTotals[$dept] : '-' }}</td>
                                            @endforeach
                                            <td class="text-center" style="font-size: 1rem;">{{ $atkGrandTotalQty }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- Tab 2: Household Matrix --}}
                    <div class="tab-pane fade" id="pills-household-dept" role="tabpanel" aria-labelledby="household-dept-tab">
                        <div class="table-responsive style-scroll" style="max-height: 440px;">
                            <table class="table table-bordered table-sm table-hover mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-warning text-dark">
                                    <tr>
                                        <th style="width: 35px;" class="text-center" rowspan="2">No</th>
                                        <th style="min-width: 180px;" rowspan="2">Nama Barang Sanitasi Non Produksi</th>
                                        <th style="width: 80px;" class="text-center" rowspan="2">Satuan</th>
                                        @if(count($householdDeptList ?? []) > 0)
                                            <th colspan="{{ count($householdDeptList) }}" class="text-center text-white bg-dark" style="background-color: #92400e !important; font-size: 0.8rem;">
                                                RINCIAN KEBUTUHAN PER DEPARTEMEN / DIVISI
                                            </th>
                                        @endif
                                        <th style="width: 110px;" class="text-center" rowspan="2">TOTAL</th>
                                    </tr>
                                    @if(count($householdDeptList ?? []) > 0)
                                        <tr>
                                            @foreach($householdDeptList as $dept)
                                                <th class="text-center text-nowrap text-white" style="background-color: #d97706; font-size: 0.78rem; min-width: 85px;">
                                                    {{ $dept }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @php $noDeptRt = 1; @endphp
                                    @forelse($householdByCategory ?? [] as $catName => $catData)
                                        <tr class="bg-light font-weight-bold text-warning">
                                            <td colspan="{{ 3 + count($householdDeptList ?? []) + 1 }}">
                                                <i class="fas fa-folder-open mr-1"></i> Kategori: {{ $catName }}
                                            </td>
                                        </tr>
                                        @foreach($catData['items'] as $item)
                                            <tr>
                                                <td class="text-center text-muted">{{ $noDeptRt++ }}</td>
                                                <td class="font-weight-600">
                                                    {{ $item['name'] }}
                                                    @if($item['is_custom'])
                                                        <span class="badge badge-danger ml-1" style="font-size: 8px;">Custom</span>
                                                    @endif
                                                </td>
                                                <td class="text-center"><span class="badge badge-light border">{{ $item['unit'] }}</span></td>
                                                
                                                @foreach($householdDeptList as $dept)
                                                    @php $qty = $item['dept_quantities'][$dept] ?? 0; @endphp
                                                    <td class="text-center {{ $qty > 0 ? 'font-weight-bold text-dark' : 'text-muted' }}" style="{{ $qty > 0 ? 'background-color: #fef3c7 !important;' : '' }}">
                                                        {{ $qty > 0 ? $qty : '-' }}
                                                    </td>
                                                @endforeach

                                                <td class="text-center font-weight-bold text-warning" style="font-size: 0.9rem;">
                                                    {{ $item['total_qty'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light" style="font-size: 0.82rem;">
                                            <td colspan="3" class="text-right font-weight-bold text-muted">Subtotal {{ $catName }}:</td>
                                            @foreach($householdDeptList as $dept)
                                                <td class="text-center font-weight-bold text-warning">
                                                    {{ ($catData['dept_subtotals'][$dept] ?? 0) > 0 ? $catData['dept_subtotals'][$dept] : '-' }}
                                                </td>
                                            @endforeach
                                            <td class="text-center font-weight-bold text-warning">{{ $catData['subtotal_qty'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 3 + count($householdDeptList ?? []) + 1 }}" class="text-center py-4 text-muted">
                                                Tidak ada data permintaan Sanitasi Non Produksi ditemukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(($householdGrandTotalQty ?? 0) > 0)
                                    <tfoot class="bg-warning text-dark font-weight-bold">
                                        <tr>
                                            <td colspan="3" class="text-right">GRAND TOTAL:</td>
                                            @foreach($householdDeptList as $dept)
                                                <td class="text-center">{{ ($householdDeptGrandTotals[$dept] ?? 0) > 0 ? $householdDeptGrandTotals[$dept] : '-' }}</td>
                                            @endforeach
                                            <td class="text-center" style="font-size: 1rem;">{{ $householdGrandTotalQty }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div>
                    <button type="button" onclick="exportPdf('department', 'ATK')" class="btn btn-outline-primary btn-sm rounded-pill font-weight-bold mr-1">
                        <i class="fas fa-file-pdf mr-1"></i> Cetak Matriks ATK (PDF)
                    </button>
                    <button type="button" onclick="exportPdf('department', 'Rumah Tangga')" class="btn btn-outline-warning text-dark btn-sm rounded-pill font-weight-bold">
                        <i class="fas fa-file-pdf mr-1"></i> Cetak Matriks SNP (PDF)
                    </button>
                </div>
                <div>
                    <button type="button" onclick="exportExcel()" class="btn btn-success btn-sm rounded-pill font-weight-bold mr-1">
                        <i class="fas fa-file-excel mr-1"></i> Unduh Excel
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Click handler for table row accordion collapse
$(document).on('click', '.atk-row-parent', function(e) {
    // Abaikan klik pada tombol aksi, link, form, input, atau dropdown
    if ($(e.target).closest('a, button, input, select, .dropdown, form, label').length) {
        return;
    }
    var targetId = $(this).data('target');
    var $target = $(targetId);
    $target.collapse('toggle');
});

$(document).on('show.bs.collapse', '.atk-item-details-row', function() {
    var rowId = $(this).attr('id');
    $('[data-target="#' + rowId + '"]').addClass('expanded');
});

$(document).on('hide.bs.collapse', '.atk-item-details-row', function() {
    var rowId = $(this).attr('id');
    $('[data-target="#' + rowId + '"]').removeClass('expanded');
});

document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id   = this.dataset.id;
        const name = this.dataset.name;
        Swal.fire({
            title: 'Hapus Pengajuan?',
            html: `Pengajuan <b>${name}</b> akan dihapus secara permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form-' + id);
                if (form) form.submit();
            }
        });
    });
});

function exportPdf(mode, type = null) {
    const form = document.getElementById('filterForm');
    const formData = form ? new FormData(form) : new FormData();
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value && key !== 'request_type') {
            params.append(key, value);
        }
    }
    params.append('mode', mode);
    if (type) {
        params.append('type', type);
        params.append('request_type', type);
    } else {
        const reqType = form ? form.elements['request_type']?.value : null;
        if (reqType) {
            params.append('request_type', reqType);
        }
    }
    
    window.location.href = "{{ route('atk-requests.export-pdf') }}?" + params.toString();
}

function exportExcel() {
    const form = document.getElementById('filterForm');
    const formData = form ? new FormData(form) : new FormData();
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    window.location.href = "{{ route('atk-requests.export-excel') }}?" + params.toString();
}
</script>
@endpush
