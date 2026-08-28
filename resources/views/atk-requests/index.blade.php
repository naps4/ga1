@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item active">Permintaan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill shadow-sm mb-3">
                {{ session('success') }}
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
                                <option value="Rumah Tangga" {{ request('request_type') == 'Rumah Tangga' ? 'selected' : '' }}>Rumah Tangga</option>
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
                    
                    <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap" style="gap: .5rem;">
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-3" style="border-radius: 0.5rem;">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('atk-requests.index') }}" class="btn btn-secondary btn-sm shadow-sm px-3" style="border-radius: 0.5rem;">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        </div>
                        
                        <div class="d-flex" style="gap: .5rem;">
                            <button type="button" class="btn btn-info btn-sm text-white shadow-sm px-3" data-toggle="modal" data-target="#modalRekapTotal" style="border-radius: 0.5rem;">
                                <i class="fas fa-list-alt mr-1"></i> Lihat Rekap Total
                            </button>

                            <button type="button" onclick="exportPdf('detail')" class="btn btn-danger btn-sm shadow-sm px-3" style="border-radius: 0.5rem;">
                                <i class="fas fa-file-pdf mr-1"></i> Ekspor PDF (Detail)
                            </button>
                            
                            <button type="button" onclick="exportPdf('aggregation')" class="btn btn-warning btn-sm text-dark shadow-sm px-3" style="border-radius: 0.5rem;">
                                <i class="fas fa-boxes mr-1"></i> Ekspor PDF (Total Kebutuhan)
                            </button>
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
                        <tr>
                            <td>
                                <strong class="text-primary">
                                    {{ ($req->request_type ?? 'ATK') === 'Rumah Tangga' ? 'RT' : 'ATK' }}-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}
                                </strong>
                            </td>
                            <td>
                                @if(($req->request_type ?? 'ATK') === 'Rumah Tangga')
                                    <span class="badge badge-warning px-2 py-1 text-dark" style="border-radius: 0.5rem; font-size: 0.75rem;">
                                        <i class="fas fa-home mr-1"></i> Rumah Tangga
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
                                <div class="d-flex" style="gap:.25rem;">
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
                                        <button class="btn btn-sm btn-danger btn-delete" style="border-radius:.45rem;"
                                            data-id="{{ $req->id }}" 
                                            data-name="{{ ($req->request_type ?? 'ATK') === 'Rumah Tangga' ? 'RT' : 'ATK' }}-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $req->id }}" action="{{ route('atk-requests.destroy', $req) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
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
{{-- Modal Rekap Total Kebutuhan Barang --}}
<div class="modal fade" id="modalRekapTotal" tabindex="-1" role="dialog" aria-labelledby="modalRekapTotalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 0.8rem; overflow: hidden;">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalRekapTotalTitle">
                    <i class="fas fa-boxes text-primary mr-2"></i>Rekap Total Kebutuhan Barang
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 px-3 small rounded-pill mb-3">
                    <i class="fas fa-info-circle mr-1"></i> Rekap ini dihitung berdasarkan filter pencarian yang aktif pada daftar permintaan di atas.
                </div>

                {{-- Nav Tabs --}}
                <ul class="nav nav-pills mb-3 nav-fill" id="rekapTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold shadow-sm" id="atk-rekap-tab" data-toggle="pill" href="#pills-atk-rekap" role="tab" aria-controls="pills-atk-rekap" aria-selected="true" style="border-radius: 0.5rem;">
                            <i class="fas fa-pencil-alt mr-1"></i> ATK (Alat Tulis Kantor)
                            <span class="badge badge-primary ml-1">{{ count($atkAggregatedItems ?? []) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold shadow-sm" id="household-rekap-tab" data-toggle="pill" href="#pills-household-rekap" role="tab" aria-controls="pills-household-rekap" aria-selected="false" style="border-radius: 0.5rem;">
                            <i class="fas fa-home mr-1"></i> Rumah Tangga (RT)
                            <span class="badge badge-warning text-dark ml-1">{{ count($householdAggregatedItems ?? []) }}</span>
                        </a>
                    </li>
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content" id="rekapTabContent">
                    {{-- Tab ATK --}}
                    <div class="tab-pane fade show active" id="pills-atk-rekap" role="tabpanel" aria-labelledby="atk-rekap-tab">
                        <div class="table-responsive style-scroll" style="max-height: 400px;">
                            <table class="table table-bordered table-striped table-hover mb-0">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">No</th>
                                        <th>Nama Barang ATK</th>
                                        <th style="width: 120px;" class="text-center">Satuan</th>
                                        <th style="width: 150px;" class="text-center">Total Qty</th>
                                        <th style="width: 140px;" class="text-center">Pengajuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalAtkModalQty = 0; @endphp
                                    @forelse($atkAggregatedItems ?? [] as $index => $item)
                                        @php $totalAtkModalQty += $item['total_qty']; @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="font-weight-bold">{{ $item['name'] }}</td>
                                            <td class="text-center"><span class="badge badge-light border">{{ $item['unit'] }}</span></td>
                                            <td class="text-center">
                                                <span class="badge badge-primary px-2 py-1" style="font-size: 0.85rem;">{{ $item['total_qty'] }}</span>
                                            </td>
                                            <td class="text-center small text-muted">{{ $item['request_count'] }}x diajukan</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                Tidak ada data barang ATK pada filter saat ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(!empty($atkAggregatedItems) && count($atkAggregatedItems) > 0)
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td colspan="3" class="text-right">Total Kebutuhan ATK:</td>
                                            <td class="text-center text-primary" style="font-size: 1rem;">{{ $totalAtkModalQty }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- Tab Rumah Tangga --}}
                    <div class="tab-pane fade" id="pills-household-rekap" role="tabpanel" aria-labelledby="household-rekap-tab">
                        <div class="table-responsive style-scroll" style="max-height: 400px;">
                            <table class="table table-bordered table-striped table-hover mb-0">
                                <thead class="bg-warning text-white">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">No</th>
                                        <th>Nama Barang Rumah Tangga</th>
                                        <th style="width: 120px;" class="text-center">Satuan</th>
                                        <th style="width: 150px;" class="text-center">Total Qty</th>
                                        <th style="width: 140px;" class="text-center">Pengajuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalRtModalQty = 0; @endphp
                                    @forelse($householdAggregatedItems ?? [] as $index => $item)
                                        @php $totalRtModalQty += $item['total_qty']; @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="font-weight-bold">{{ $item['name'] }}</td>
                                            <td class="text-center"><span class="badge badge-light border">{{ $item['unit'] }}</span></td>
                                            <td class="text-center">
                                                <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 0.85rem;">{{ $item['total_qty'] }}</span>
                                            </td>
                                            <td class="text-center small text-muted">{{ $item['request_count'] }}x diajukan</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                Tidak ada data barang Rumah Tangga pada filter saat ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(!empty($householdAggregatedItems) && count($householdAggregatedItems) > 0)
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td colspan="3" class="text-right">Total Kebutuhan Rumah Tangga:</td>
                                            <td class="text-center text-warning" style="font-size: 1rem;">{{ $totalRtModalQty }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" onclick="exportPdf('aggregation')" class="btn btn-warning btn-sm text-dark font-weight-bold" style="border-radius: 0.5rem;">
                    <i class="fas fa-file-pdf mr-1"></i> Cetak Rekap (PDF)
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius: 0.5rem;">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
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

function exportPdf(mode) {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    params.append('mode', mode);
    
    window.location.href = "{{ route('atk-requests.export-pdf') }}?" + params.toString();
}
</script>
@endpush
