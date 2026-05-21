@extends('layouts.app')

@section('content')
<style>
/* Star Rating Interaktif (RTL flow untuk seleksi hover terbalik) */
.star-rating {
    direction: rtl;
    font-size: 2.5rem;
    unicode-bidi: bidi-override;
}
.star-rating input {
    display: none;
}
.star-rating label {
    color: #e4e5e9;
    cursor: pointer;
    margin-right: 5px;
    transition: color 0.15s ease-in-out, transform 0.15s ease-in-out;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #ffc107;
}
.star-rating label:active {
    transform: scale(0.9);
}

.star-static {
    font-size: 1.5rem;
    color: #e4e5e9;
}
.star-static .filled {
    color: #ffc107;
}
</style>
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0">Penyerahan Hasil (Form D)</h1>
            <div class="d-flex align-items-center">
                <a href="{{ route('handovers.print', $handover->work_order_id) }}" class="btn btn-primary rounded-pill mr-2 shadow-sm">
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

            <!-- Approval & Rating Panel -->
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-primary"><i class="fas fa-check-double text-success"></i> Status & Persetujuan Serah Terima</h3>
                    </div>
                    <div class="card-body">
                        @if(!$handover->accepted_by)
                            @if(auth()->user()->role === 'client')
                                <!-- Form Persetujuan & Rating untuk Client -->
                                <form action="{{ route('handovers.accept', $handover->id) }}" method="POST" id="handover-accept-form">
                                    @csrf
                                    <div class="alert alert-info rounded-4 border-0 mb-4 text-sm" style="background-color: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                                        <i class="fas fa-info-circle mr-1"></i> Silakan periksa hasil pengerjaan di lapangan. Jika sudah sesuai dan memuaskan, berikan penilaian Anda di bawah untuk menyelesaikan tiket.
                                    </div>

                                    <!-- Status Serah Terima -->
                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold text-muted d-block text-xs uppercase-tracking">Status Serah Terima</label>
                                        <span class="badge badge-warning py-2 px-3 rounded-pill text-sm shadow-sm">
                                            <i class="fas fa-clock mr-1 animate-pulse"></i> Menunggu Konfirmasi Pemohon
                                        </span>
                                    </div>

                                    <!-- Rating Kepuasan -->
                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold text-muted d-block mb-1 text-xs uppercase-tracking">Rating Kepuasan <span class="text-danger">*</span></label>
                                        <div class="star-rating d-flex align-items-center">
                                            <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="Sangat Puas"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="Puas"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="Cukup"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="Kurang Puas"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star1" name="rating" value="1" required /><label for="star1" title="Sangat Kurang"><i class="fas fa-star"></i></label>
                                        </div>
                                        <small class="text-muted d-block mt-1">Pilih bintang 1 sampai 5 untuk menilai performa layanan kami.</small>
                                    </div>

                                    <!-- Ulasan Pemohon -->
                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold text-muted text-xs uppercase-tracking">Ulasan Pemohon (Kritik & Saran)</label>
                                        <textarea name="feedback" rows="3" class="form-control rounded-4 shadow-sm" placeholder="Tuliskan ulasan, masukan, atau kritik dan saran mengenai hasil pekerjaan di sini..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-block py-2 rounded-pill font-weight-bold shadow-lg transition-transform hover-scale">
                                        <i class="fas fa-handshake mr-1"></i> Terima & Selesaikan Pekerjaan
                                    </button>
                                </form>
                            @else
                                <!-- Tampilan Menunggu untuk Admin / Teknisi -->
                                <div class="text-center py-4">
                                    <i class="fas fa-clock text-warning fa-3x mb-3 animate-pulse"></i>
                                    <h5 class="font-weight-bold">Menunggu Konfirmasi Pemohon</h5>
                                    <p class="text-muted text-sm px-3">Tiket ini telah diselesaikan oleh teknisi dan saat ini sedang menunggu tinjauan, ulasan, serta persetujuan dari Client/Pemohon.</p>
                                </div>

                                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin')
                                    <hr class="my-4 border-light">
                                    <div class="p-3 bg-light rounded-4 border border-light-gray">
                                        <p class="text-xs text-muted mb-2 font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> OPSI ALTERNATIF (ADMIN / SPV)</p>
                                        <p class="text-xs text-muted mb-3">Jika pemohon berhalangan secara fisik untuk melakukan persetujuan melalui sistem, Admin/SPV dapat menutup tiket ini secara sepihak.</p>
                                        <form action="{{ route('handovers.approve-all', $handover->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-block btn-sm rounded-pill shadow-sm">
                                                <i class="fas fa-check-double mr-1"></i> Verifikasi & Selesaikan Tiket
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                        @else
                            <!-- Sudah Diterima / Selesai (View Mode) -->
                            <div class="p-2">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="mr-3 rounded-circle p-2 d-inline-flex justify-content-center align-items-center" style="width: 50px; height: 50px; background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-weight-bold mb-0 text-success">Persetujuan Selesai</h5>
                                        <small class="text-muted">Hasil pekerjaan telah diterima</small>
                                    </div>
                                </div>

                                <table class="table table-sm table-borderless mb-4">
                                    <tr>
                                        <th width="35%" class="text-muted text-xs uppercase-tracking">Status</th>
                                        <td>: <span class="badge badge-success rounded-pill px-3 py-1">Diterima Pemohon</span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted text-xs uppercase-tracking">Disetujui Oleh</th>
                                        <td>: <strong>{{ $handover->acceptedBy->name ?? 'Admin (Mewakili)' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted text-xs uppercase-tracking">Tanggal Selesai</th>
                                        <td>: {{ \Carbon\Carbon::parse($handover->accepted_by_user_at)->translatedFormat('d F Y H:i') }}</td>
                                    </tr>
                                </table>

                                @if($handover->rating)
                                    <hr class="my-3 border-light">
                                    <div class="mb-4">
                                        <label class="font-weight-bold text-muted d-block text-xs uppercase-tracking mb-1">Rating Kepuasan</label>
                                        <div class="star-static text-warning d-flex align-items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa-star {{ $i <= $handover->rating ? 'fas text-warning' : 'far text-muted' }}" style="font-size: 1.5rem; margin-right: 4px;"></i>
                                            @endfor
                                            <span class="text-dark font-weight-bold text-sm ml-2">({{ $handover->rating }} / 5)</span>
                                        </div>
                                    </div>
                                @endif

                                @if($handover->feedback)
                                    <div class="mb-0">
                                        <label class="font-weight-bold text-muted d-block text-xs uppercase-tracking mb-1">Ulasan Pemohon</label>
                                        <div class="bg-light p-3 rounded-4 text-dark font-italic border-left border-success border-width-3 text-sm">
                                            "{!! nl2br(e($handover->feedback)) !!}"
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
