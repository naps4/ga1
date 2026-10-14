@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0">Work Order (General Affair)</h1>
            @if(auth()->user()->role === 'client' || auth()->user()->role === 'user')
                <a href="{{ route('work-orders.create') }}" class="btn btn-primary shadow-sm rounded-pill">
                    <i class="fas fa-plus"></i> Buat Tiket Baru
                </a>
            @endif
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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" style="border-radius: 1rem;" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>No. Tiket</th>
                                <th class="d-none d-md-table-cell">Tanggal</th>
                                <th class="d-none d-sm-table-cell">Pemohon</th>
                                <th class="d-none d-lg-table-cell">Departemen</th>
                                <th class="d-none d-md-table-cell">Teknisi</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workOrders as $wo)
                                <tr>
                                    <td><strong>#WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                    <td class="d-none d-md-table-cell">{{ $wo->created_at->format('d M Y H:i') }}</td>
                                    <td class="d-none d-sm-table-cell">{{ $wo->user->name ?? '-' }}</td>
                                    <td class="d-none d-lg-table-cell">{{ $wo->department ?? '-' }}</td>
                                    <td class="d-none d-md-table-cell">
                                        @if($wo->assignedEngineer)
                                            <span class="badge badge-info"><i class="fas fa-user-cog"></i> {{ $wo->assignedEngineer->name }}</span>
                                        @else
                                            <span class="badge badge-secondary">Belum Ditugaskan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = 'secondary';
                                            if ($wo->status == 'Pengecekan Engineer') $badgeClass = 'info';
                                            elseif ($wo->status == 'Sedang Dikerjakan') $badgeClass = 'primary';
                                            elseif ($wo->status == 'Menunggu Pengadaan Material') $badgeClass = 'warning';
                                            elseif ($wo->status == 'Menunggu Serah Terima') $badgeClass = 'primary';
                                            elseif ($wo->status == 'Selesai') $badgeClass = 'success';
                                            elseif ($wo->status == 'Ditolak') $badgeClass = 'danger';
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}" style="white-space: normal; text-align: center; display: inline-block;">{{ $wo->status }}</span>
                                        @if($wo->status === 'Menunggu Serah Terima')
                                            @php
                                                $ho = $wo->handover;
                                                $hoText = '';
                                                if (!$ho || !$ho->prepared_by) $hoText = 'Admin/HRGA';
                                                elseif ($ho->known_by && !$ho->accepted_by) $hoText = 'Klien/Pemohon';
                                            @endphp
                                            @if($hoText)
                                                <div class="mt-1"><small class="text-muted font-weight-bold" style="font-size: 0.75rem;"><i class="fas fa-clock"></i> {{ $hoText }}</small></div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group">
                                            <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-outline-primary rounded-pill mr-1">
                                                <i class="fas fa-eye"></i> <span class="d-none d-sm-inline">Detail</span>
                                            </a>
                                            @if((auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isManager()) && $wo->status === 'Menunggu Penugasan')
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill mr-1" data-toggle="modal" data-target="#modalRejectIndex{{ $wo->id }}" title="Tolak Tiket">
                                                    <i class="fas fa-times-circle"></i> <span class="d-none d-lg-inline">Tolak</span>
                                                </button>
                                            @endif
                                            @if($wo->user_id === auth()->id() && in_array($wo->status, ['Menunggu Penugasan', 'Ditolak']))
                                                @if($wo->status === 'Menunggu Penugasan')
                                                    <a href="{{ route('work-orders.edit', $wo->id) }}" class="btn btn-sm btn-outline-warning rounded-pill mr-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('work-orders.destroy', $wo->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tiket ini?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        @if((auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isManager()) && $wo->status === 'Menunggu Penugasan')
                                            <!-- Modal Tolak WO {{ $wo->id }} -->
                                            <div class="modal fade text-left" id="modalRejectIndex{{ $wo->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <div class="modal-header bg-danger text-white rounded-top-4">
                                                            <h5 class="modal-title font-weight-bold">
                                                                <i class="fas fa-exclamation-triangle mr-1"></i> Tolak Tiket #WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}
                                                            </h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="{{ route('work-orders.reject', $wo->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <p class="text-muted">Yakin ingin menolak tiket <strong>#WO-{{ str_pad($wo->id, 4, '0', STR_PAD_LEFT) }}</strong> dari <strong>{{ $wo->user->name ?? 'Pemohon' }}</strong>?</p>
                                                                <div class="form-group">
                                                                    <label class="font-weight-bold text-dark">Alasan Penolakan <span class="text-danger">*</span></label>
                                                                    <textarea name="rejection_note" class="form-control rounded-3" rows="3" placeholder="Tuliskan alasan penolakan tiket secara jelas..." required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light rounded-bottom-4">
                                                                <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger rounded-pill font-weight-bold">
                                                                    <i class="fas fa-times-circle mr-1"></i> Ya, Tolak Tiket
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                        <p class="mb-0">Belum ada data Work Order.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
