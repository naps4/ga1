@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item active">Permintaan ATK</li>
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
                        <i class="fas fa-pencil-alt mr-2 text-primary"></i>Daftar Permintaan ATK (Alat Tulis Kantor)
                    </h3>
                    <p class="text-muted mb-0 d-none d-md-block" style="font-size:.78rem;margin-top:.2rem;">Pengajuan kebutuhan ATK divisi bulanan paling lambat tanggal 28</p>
                </div>
                <a href="{{ route('atk-requests.create') }}" class="btn btn-primary btn-sm shadow-sm" style="border-radius:.6rem;white-space:nowrap;">
                    <i class="fas fa-plus mr-1"></i> Permintaan Baru
                </a>
            </div>

            {{-- Table --}}
            <div class="table-responsive" style="padding:0 1.25rem 1.25rem;">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 80px;">No. Doc</th>
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
                                <strong class="text-primary">ATK-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</strong>
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
                                @elseif($req->status_approval === 'Menunggu Persetujuan')
                                    <span class="badge badge-warning">Menunggu Persetujuan</span>
                                @elseif($req->status_approval === 'Disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($req->status_approval === 'Ditolak/Revisi')
                                    <span class="badge badge-danger">Ditolak/Revisi</span>
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
                                    
                                    @if($req->status_approval === 'Draft' || $req->status_approval === 'Ditolak/Revisi')
                                        <a href="{{ route('atk-requests.edit', $req) }}" class="btn btn-sm btn-warning" style="border-radius:.45rem;" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                    @endif

                                    <a href="{{ route('atk-requests.print', $req) }}" target="_blank" class="btn btn-sm btn-info text-white" style="border-radius:.45rem;" title="Cetak"><i class="fas fa-print"></i></a>
                                    
                                    @if($req->status_approval === 'Draft')
                                        <button class="btn btn-sm btn-danger btn-delete" style="border-radius:.45rem;"
                                            data-id="{{ $req->id }}" data-name="ATK-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}" title="Hapus">
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
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-pencil-alt fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">Belum ada pengajuan ATK. <a href="{{ route('atk-requests.create') }}">Buat pengajuan baru</a>.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id   = this.dataset.id;
        const name = this.dataset.name;
        Swal.fire({
            title: 'Hapus Draft?',
            html: `Draft pengajuan <b>${name}</b> akan dihapus secara permanen.`,
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
</script>
@endpush
