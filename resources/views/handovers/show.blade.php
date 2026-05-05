@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0">Penyerahan Hasil (Form D)</h1>
            <div class="d-flex align-items-center">
                <a href="{{ route('handovers.print', $handover->work_order_id) }}" target="_blank" class="btn btn-primary rounded-pill mr-2 shadow-sm">
                    <i class="fas fa-print"></i> Cetak Berita Acara (PDF)
                </a>
                <a href="{{ route('work-orders.show', $handover->work_order_id) }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fas fa-arrow-left"></i> Kembali ke Tiket
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill shadow-sm">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <!-- Form Input Handover -->
            <div class="col-md-7">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-success"><i class="fas fa-file-contract"></i> Detail Penyerahan Hasil</h3>
                    </div>
                    
                    @if((auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') && !$handover->accepted_by)
                        <form action="{{ route('handovers.update', $handover->id) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label>Nama Pelayanan</label>
                                    <input type="text" name="service_name" class="form-control rounded-pill" value="{{ $handover->service_name }}" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Jumlah (Qty)</label>
                                            <input type="number" name="qty" class="form-control rounded-pill" value="{{ $handover->qty }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Satuan</label>
                                            <input type="text" name="unit" class="form-control rounded-pill" value="{{ $handover->unit }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Spesifikasi</label>
                                    <textarea name="specification" rows="3" class="form-control rounded-4">{{ $handover->specification }}</textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Catatan Tambahan</label>
                                    <textarea name="notes" rows="2" class="form-control rounded-4">{{ $handover->notes }}</textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Tanggal Penyerahan</label>
                                    <input type="date" name="handover_date" class="form-control rounded-pill" value="{{ $handover->handover_date ? \Carbon\Carbon::parse($handover->handover_date)->format('Y-m-d') : date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top border-light text-right">
                                <button type="submit" class="btn btn-success rounded-pill"><i class="fas fa-save"></i> Simpan Form D</button>
                            </div>
                        </form>
                    @else
                        <!-- View Only Mode -->
                        <div class="card-body">
                            <table class="table table-borderless table-sm">
                                <tr><th width="35%">Nama Pelayanan</th><td>: {{ $handover->service_name ?? '-' }}</td></tr>
                                <tr><th>Jumlah / Satuan</th><td>: {{ $handover->qty ?? '-' }} {{ $handover->unit ?? '' }}</td></tr>
                                <tr><th>Spesifikasi</th><td>: {{ $handover->specification ?? '-' }}</td></tr>
                                <tr><th>Catatan</th><td>: {{ $handover->notes ?? '-' }}</td></tr>
                                <tr><th>Tanggal Penyerahan</th><td>: {{ $handover->handover_date ? \Carbon\Carbon::parse($handover->handover_date)->format('d F Y') : '-' }}</td></tr>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval Panel -->
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-primary"><i class="fas fa-check-double"></i> Tanda Tangan Digital</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline timeline-inverse">
                            <!-- Disiapkan HRGA -->
                            <div>
                                <i class="fas fa-user-edit bg-{{ $handover->prepared_by ? 'success' : 'secondary' }}"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Disiapkan (HRGA)</h3>
                                    <div class="timeline-body p-2 text-sm">
                                        @if($handover->prepared_by)
                                            Oleh: {{ $handover->preparedBy->name ?? '-' }}
                                        @else
                                            <span class="text-muted">Menunggu form dilengkapi Admin.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Diketahui Oprt. Manager -->
                            <div>
                                <i class="fas fa-eye bg-{{ $handover->known_by ? 'success' : 'secondary' }}"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Diketahui (Oprt. Manager)</h3>
                                    <div class="timeline-body p-2 text-sm">
                                        @if($handover->known_by)
                                            Oleh: {{ $handover->knownBy->name ?? '-' }}
                                        @else
                                            @if((auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') && $handover->prepared_by)
                                                <form action="{{ route('handovers.know-manager', $handover->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill btn-block">Tandai Diketahui</button>
                                                </form>
                                            @else
                                                <span class="text-muted">Menunggu proses sebelumnya.</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Diterima User/Client -->
                            <div>
                                <i class="fas fa-handshake bg-{{ $handover->accepted_by ? 'success' : 'secondary' }}"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Diterima (User/Client)</h3>
                                    <div class="timeline-body p-2 text-sm">
                                        @if($handover->accepted_by)
                                            Oleh: {{ $handover->acceptedBy->name ?? '-' }}
                                            <br><small class="text-success"><i class="fas fa-check-circle"></i> Tiket Selesai pada {{ \Carbon\Carbon::parse($handover->accepted_by_user_at)->format('d M Y H:i') }}</small>
                                        @else
                                            @if((auth()->user()->id === $handover->workOrder->user_id || auth()->user()->role === 'super_admin') && $handover->known_by)
                                                <form action="{{ route('handovers.accept-user', $handover->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill btn-block">Terima Hasil & Tutup Tiket</button>
                                                </form>
                                            @else
                                                <span class="text-muted">Menunggu User pemohon konfirmasi.</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div>
                                <i class="fas fa-flag-checkered bg-gray"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
