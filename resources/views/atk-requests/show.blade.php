@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item"><a href="{{ route('atk-requests.index') }}">Permintaan ATK</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill shadow-sm mb-3">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Details Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-primary font-weight-bold mb-0">
                    <i class="fas fa-file-alt mr-2"></i>Detail Permintaan ATK - ATK-{{ str_pad($atkRequest->id, 4, '0', STR_PAD_LEFT) }}
                </h3>
                <div>
                    <a href="{{ route('atk-requests.print', $atkRequest->id) }}" target="_blank" class="btn btn-primary rounded-pill btn-sm shadow-sm mr-2">
                        <i class="fas fa-print"></i> Cetak Formulir
                    </a>
                    @if(($atkRequest->status_approval === 'Draft' || $atkRequest->status_approval === 'Ditolak/Revisi') && $atkRequest->user_id === auth()->id())
                        <a href="{{ route('atk-requests.edit', $atkRequest->id) }}" class="btn btn-warning rounded-pill btn-sm shadow-sm">
                            <i class="fas fa-pencil-alt"></i> Edit Permintaan
                        </a>
                    @endif
                </div>
            </div>
            
            <div class="card-body">
                <!-- Metadata table -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th style="width: 150px;" class="text-muted">Nama Pengaju</th>
                                <td>: <strong>{{ $atkRequest->nama }}</strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Divisi / Unit Kerja</th>
                                <td>: {{ $atkRequest->divisi }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th style="width: 150px;" class="text-muted">Periode Permintaan</th>
                                <td>: <span class="badge badge-info">{{ $atkRequest->periode }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Tanggal Pengajuan</th>
                                <td>: {{ $atkRequest->tanggal_pengajuan->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <h5 class="text-primary font-weight-bold mb-3"><i class="fas fa-list-ol mr-2"></i>Daftar Barang yang Diminta</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 45%;">Nama Barang</th>
                                <th style="width: 15%;" class="text-center">Satuan</th>
                                <th style="width: 25%;">Spesifikasi / Keterangan</th>
                                <th style="width: 10%;" class="text-center">Total (Qty)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            
                            <!-- Standard Items -->
                            @foreach($atkRequest->items->where('is_custom', false) as $item)
                                <tr>
                                    <td class="text-center align-middle">{{ $no++ }}</td>
                                    <td class="font-weight-600 align-middle">{{ $item->item_name }}</td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-secondary">{{ $item->unit }}</span>
                                    </td>
                                    <td class="align-middle text-muted">{{ $item->description ?? '-' }}</td>
                                    <td class="text-center align-middle font-weight-bold">{{ $item->quantity }}</td>
                                </tr>
                            @endforeach

                            <!-- Custom Items -->
                            @if($atkRequest->items->where('is_custom', true)->count() > 0)
                                <tr class="bg-light">
                                    <td colspan="5" class="font-weight-bold text-primary small py-2"><i class="fas fa-edit mr-1"></i> Barang Kustom (Tidak Terdaftar)</td>
                                </tr>
                                @foreach($atkRequest->items->where('is_custom', true) as $item)
                                    <tr>
                                        <td class="text-center align-middle">{{ $no++ }}</td>
                                        <td class="font-weight-600 align-middle text-danger">{{ $item->item_name }}</td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-secondary">{{ $item->unit }}</span>
                                        </td>
                                        <td class="align-middle text-muted">{{ $item->description ?? '-' }}</td>
                                        <td class="text-center align-middle font-weight-bold">{{ $item->quantity }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($atkRequest->status_approval === 'Ditolak/Revisi' && $atkRequest->notes)
                    <div class="alert alert-danger rounded-4 border-0 p-3 mt-4">
                        <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Alasan Penolakan / Revisi:</h6>
                        <span>{{ $atkRequest->notes }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action / Sidebar Panel -->
    <div class="col-md-4">
        <!-- Status Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white border-bottom border-light">
                <h3 class="card-title text-success font-weight-bold mb-0">
                    <i class="fas fa-check-double mr-2"></i>Status Approval
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <h6 class="font-weight-bold text-muted">Status Saat Ini:</h6>
                    @if($atkRequest->status_approval === 'Draft')
                        <span class="badge badge-secondary text-lg px-4 py-2 rounded-pill">Draft</span>
                    @elseif($atkRequest->status_approval === 'Menunggu Persetujuan')
                        <span class="badge badge-warning text-lg px-4 py-2 rounded-pill">Menunggu Persetujuan</span>
                    @elseif($atkRequest->status_approval === 'Disetujui')
                        <span class="badge badge-success text-lg px-4 py-2 rounded-pill">Disetujui</span>
                    @elseif($atkRequest->status_approval === 'Ditolak/Revisi')
                        <span class="badge badge-danger text-lg px-4 py-2 rounded-pill">Ditolak/Revisi</span>
                    @endif
                </div>

                <div class="timeline timeline-inverse small">
                    <!-- Stage 1: Pengajuan -->
                    <div>
                        <i class="fas fa-paper-plane bg-primary"></i>
                        <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                            <span class="time"><i class="fas fa-clock"></i> {{ $atkRequest->created_at->format('d/m H:i') }}</span>
                            <h3 class="timeline-header border-0 font-weight-bold text-sm">Diajukan</h3>
                            <div class="timeline-body p-2 text-muted">
                                Oleh: {{ $atkRequest->user->name }}
                            </div>
                        </div>
                    </div>

                    <!-- Stage 2: Verifikasi Admin -->
                    @if($atkRequest->status_approval !== 'Draft')
                        <div>
                            @if($atkRequest->approved_by)
                                <i class="fas fa-check bg-success"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $atkRequest->approved_at->format('d/m H:i') }}</span>
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Disetujui Admin</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Disetujui oleh: <strong>{{ $atkRequest->approver->name }}</strong>
                                    </div>
                                </div>
                            @elseif($atkRequest->status_approval === 'Ditolak/Revisi')
                                <i class="fas fa-times bg-danger"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $atkRequest->updated_at->format('d/m H:i') }}</span>
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Ditolak / Perlu Revisi</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Catatan: {{ $atkRequest->notes }}
                                    </div>
                                </div>
                            @else
                                <i class="fas fa-clock bg-warning"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Menunggu Review</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Menunggu verifikasi dan persetujuan dari Admin HRGA.
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Stage 3: Serah Terima Barang -->
                    @if($atkRequest->status_approval === 'Disetujui')
                        <div>
                            @if($atkRequest->diberikan_pada)
                                <i class="fas fa-handshake bg-success"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $atkRequest->diberikan_pada->format('d/m/Y') }}</span>
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Barang Diserahkan</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Penerima: <strong>{{ $atkRequest->receiver_name }}</strong><br>
                                        Tanggal: {{ $atkRequest->diberikan_pada->format('d M Y') }}
                                    </div>
                                </div>
                            @else
                                <i class="fas fa-hourglass-half bg-secondary"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Menunggu Serah Terima</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Barang disiapkan oleh GA dan menunggu diserahkan kepada pengaju.
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Admin Action Buttons -->
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <hr>
                    @if($atkRequest->status_approval === 'Menunggu Persetujuan')
                        <div class="d-flex flex-column" style="gap: 8px;">
                            <form action="{{ route('atk-requests.approve', $atkRequest->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block rounded-pill">
                                    <i class="fas fa-check mr-1"></i> Setujui Pengajuan
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger btn-block rounded-pill" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times mr-1"></i> Tolak / Minta Revisi
                            </button>
                        </div>
                    @elseif($atkRequest->status_approval === 'Disetujui' && !$atkRequest->diberikan_pada)
                        <button type="button" class="btn btn-primary btn-block rounded-pill" data-toggle="modal" data-target="#handoverModal">
                            <i class="fas fa-handshake mr-1"></i> Konfirmasi Penyerahan
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('atk-requests.reject', $atkRequest->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title font-weight-bold text-danger" id="rejectModalLabel"><i class="fas fa-exclamation-triangle mr-1"></i> Tolak / Minta Revisi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="notes" class="font-weight-600">Alasan Penolakan atau Catatan Revisi:</label>
                        <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Tulis alasan penolakan atau instruksi revisi barang..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Handover Modal -->
<div class="modal fade" id="handoverModal" tabindex="-1" aria-labelledby="handoverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('atk-requests.handover', $atkRequest->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title font-weight-bold text-primary" id="handoverModalLabel"><i class="fas fa-handshake mr-1"></i> Konfirmasi Penyerahan Barang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="diberikan_pada" class="font-weight-600">Tanggal Penyerahan:</label>
                        <input type="date" name="diberikan_pada" id="diberikan_pada" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="receiver_name" class="font-weight-600">Nama Penerima Barang:</label>
                        <input type="text" name="receiver_name" id="receiver_name" class="form-control" value="{{ $atkRequest->nama }}" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
