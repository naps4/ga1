@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0">Detail Work Order #WO-{{ str_pad($workOrder->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-primary rounded-pill mr-2 shadow-sm" data-toggle="modal" data-target="#pdfModal">
                    <i class="fas fa-print"></i> Cetak Dokumen (PDF)
                </button>
                <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <!-- Informasi Utama WO -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-primary"><i class="fas fa-info-circle"></i> Informasi Tiket (Form A)</h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">{{ $workOrder->status }}</span>
                            @if($workOrder->status === 'Menunggu Serah Terima')
                                @php
                                    $ho = $workOrder->handover;
                                    $hoText = '';
                                    if (!$ho || !$ho->prepared_by) $hoText = 'Admin/HRGA';
                                    elseif (!$ho->known_by) $hoText = 'Oprt. Manager';
                                    elseif (!$ho->accepted_by) $hoText = 'Klien/Pemohon';
                                @endphp
                                @if($hoText)
                                    <span class="badge badge-warning ml-1"><i class="fas fa-clock"></i> {{ $hoText }}</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="35%" class="text-muted">Tanggal Pengajuan</th>
                                <td>: {{ $workOrder->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Pemohon</th>
                                <td>: {{ $workOrder->user->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Departemen</th>
                                <td>: {{ $workOrder->department ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Keluhan / Request</th>
                                <td>: {!! nl2br(e($workOrder->description)) !!}</td>
                            </tr>
                            @if($workOrder->attachment_request)
                            <tr>
                                <th class="text-muted">Lampiran Pengajuan</th>
                                <td>: <a href="{{ asset($workOrder->attachment_request) }}" target="_blank" class="btn btn-xs btn-outline-info rounded-pill"><i class="fas fa-paperclip"></i> Lihat Lampiran</a></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Modul Penugasan untuk Admin -->
                @if((auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') && $workOrder->status === 'Menunggu Penugasan')
                    <div class="card shadow-sm border-0 rounded-4 bg-light border-primary mb-4">
                        <div class="card-body">
                            <h5 class="text-primary mb-3"><i class="fas fa-user-plus"></i> Tugaskan Engineer</h5>
                            <form action="{{ route('work-orders.assign', $workOrder->id) }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <select name="assigned_engineer_id" class="form-control rounded-left-pill" required>
                                        <option value="">-- Pilih Teknisi --</option>
                                        @foreach($engineers as $engineer)
                                            <option value="{{ $engineer->id }}">{{ $engineer->name }} ({{ $engineer->role }})</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary rounded-right-pill">Tugaskan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Panel Engineer -->
            <div class="col-md-6">
                <!-- Form Pengecekan (Form B) -->
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-success"><i class="fas fa-tools"></i> Pengecekan Engineer (Form B)</h3>
                    </div>
                    <div class="card-body">
                        @if($workOrder->status === 'Pengecekan Engineer' && auth()->user()->id === $workOrder->assigned_engineer_id)
                            <form action="{{ route('work-orders.update-check', $workOrder->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label>Hasil Pengecekan Lapangan</label>
                                    <textarea name="check_result" class="form-control rounded-4" rows="3" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Kebutuhan Material/Barang</label>
                                    <textarea name="engineer_needs" class="form-control rounded-4" rows="2" placeholder="(Opsional) Tuliskan jika butuh sparepart..."></textarea>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                        <input type="checkbox" class="custom-control-input" id="needs_material" name="needs_material" value="1">
                                        <label class="custom-control-label" for="needs_material">Butuh Pengadaan Material (Buka Form C/FPU)</label>
                                    </div>
                                </div>

                                <!-- Bagian Form C (FPU) Langsung -->
                                <div id="form-c-container" style="display: none;" class="mb-4 p-3 bg-light rounded-4 border border-warning">
                                    <h5 class="text-warning font-weight-bold"><i class="fas fa-box"></i> Rincian Material (Form C)</h5>
                                    <p class="text-sm text-muted">Silakan tentukan klasifikasi dan rinci material/barang yang dibutuhkan untuk pekerjaan ini.</p>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label class="text-sm">Prioritas</label>
                                            <select name="priority" class="form-control form-control-sm">
                                                <option value="Biasa">Biasa</option>
                                                <option value="Penting">Penting</option>
                                                <option value="Sangat Penting">Sangat Penting</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="text-sm">Pelaksana</label>
                                            <select name="executor_type" class="form-control form-control-sm">
                                                <option value="Internal">Internal</option>
                                                <option value="Eksternal">Eksternal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="text-sm">Jenis</label>
                                            <select name="type" class="form-control form-control-sm">
                                                <option value="Bangunan">Bangunan</option>
                                                <option value="Kendaraan">Kendaraan</option>
                                                <option value="Lainnya">Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="text-sm">Kategori</label>
                                            <select name="category" class="form-control form-control-sm">
                                                <option value="Pembuatan">Pembuatan</option>
                                                <option value="Perbaikan">Perbaikan</option>
                                                <option value="Pengadaan">Pengadaan</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm bg-white" id="engineer-material-table">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Nama Material</th>
                                                    <th>Spesifikasi</th>
                                                    <th width="15%">Qty</th>
                                                    <th width="10%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" name="materials[0][name]" class="form-control form-control-sm" placeholder="Contoh: Lampu LED"></td>
                                                    <td><input type="text" name="materials[0][spec]" class="form-control form-control-sm" placeholder="Contoh: 18 Watt Philips"></td>
                                                    <td><input type="number" name="materials[0][qty]" class="form-control form-control-sm" value="1" min="1"></td>
                                                    <td><button type="button" class="btn btn-danger btn-sm remove-mat-row"><i class="fas fa-trash"></i></button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill" id="add-mat-row"><i class="fas fa-plus"></i> Tambah Material</button>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <label>Lampiran Bukti Pengecekan <span class="text-muted">(Opsional)</span></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="attachment" accept=".jpg,.jpeg,.png,.pdf">
                                        <label class="custom-file-label">Pilih foto hasil cek...</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success rounded-pill w-100">Simpan & Lanjutkan</button>
                            </form>
                        @else
                            <!-- View Mode Pengecekan -->
                            @if($workOrder->check_result)
                                <div class="callout callout-info border-0 bg-light rounded-4">
                                    <h5><i class="fas fa-clipboard-check text-info"></i> Hasil Cek:</h5>
                                    <p>{{ $workOrder->check_result }}</p>
                                    @if($workOrder->engineer_needs)
                                        <hr>
                                        <strong>Kebutuhan:</strong> {{ $workOrder->engineer_needs }}
                                    @endif
                                    @if($workOrder->attachment_check)
                                        <hr>
                                        <a href="{{ asset($workOrder->attachment_check) }}" target="_blank" class="btn btn-xs btn-info rounded-pill"><i class="fas fa-image"></i> Lihat Bukti Pengecekan</a>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted text-center my-4"><i class="fas fa-clock fa-2x mb-2"></i><br>Menunggu pengecekan teknisi...</p>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- FPU Request Info (Form C) jika ada -->
                @if($workOrder->fpuRequest)
                    <div class="card shadow-sm border-0 rounded-4 mb-4 border-warning">
                        <div class="card-header bg-warning text-dark rounded-top-4">
                            <h3 class="card-title"><i class="fas fa-box"></i> Pengadaan Material (Form C)</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Status FPU:</strong> <span class="badge badge-warning">{{ $workOrder->fpuRequest->status_approval }}</span></p>
                            <a href="{{ route('fpu.show', $workOrder->fpuRequest->id) }}" class="btn btn-outline-warning rounded-pill btn-block text-dark font-weight-bold">
                                <i class="fas fa-file-invoice"></i> Buka / Kelola Form C (FPU)
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Laporan Penyelesaian (Form B Lanjutan) -->
                @if(in_array($workOrder->status, ['Sedang Dikerjakan', 'Menunggu Serah Terima', 'Selesai']))
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom border-light">
                            <h3 class="card-title text-primary"><i class="fas fa-flag-checkered"></i> Laporan Penyelesaian</h3>
                        </div>
                        <div class="card-body">
                            @if($workOrder->status === 'Sedang Dikerjakan' && auth()->user()->id === $workOrder->assigned_engineer_id)
                                <form action="{{ route('work-orders.update-report', $workOrder->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label>Laporan Pekerjaan (Reporting Engineer)</label>
                                        <textarea name="engineer_report" class="form-control rounded-4" rows="3" placeholder="Deskripsikan pekerjaan yang telah selesai dilakukan..." required></textarea>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label>Lampiran Bukti Pengerjaan <span class="text-muted">(Opsional)</span></label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="attachment" accept=".jpg,.jpeg,.png,.pdf">
                                            <label class="custom-file-label">Foto hasil pengerjaan selesai...</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill w-100">Kirim Laporan Penyelesaian</button>
                                </form>
                            @else
                                <div class="callout callout-success border-0 bg-light rounded-4 mb-3">
                                    <p>{{ $workOrder->engineer_report ?? 'Belum ada laporan.' }}</p>
                                    @if($workOrder->attachment_report)
                                        <hr>
                                        <a href="{{ asset($workOrder->attachment_report) }}" target="_blank" class="btn btn-xs btn-success rounded-pill"><i class="fas fa-check-double"></i> Lihat Bukti Penyelesaian</a>
                                    @endif
                                </div>
                                @if(in_array($workOrder->status, ['Menunggu Serah Terima', 'Selesai']))
                                    <a href="{{ route('handovers.show', $workOrder->id) }}" class="btn btn-outline-success rounded-pill btn-block font-weight-bold">
                                        <i class="fas fa-handshake"></i> Buka Berita Acara (Form D)
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Modal PDF Preview -->
<div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title" id="pdfModalLabel"><i class="fas fa-file-pdf"></i> Preview Dokumen (PDF)</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe src="{{ route('work-orders.print', $workOrder->id) }}" width="100%" height="100%" style="border: none; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleMaterial = document.getElementById('needs_material');
        const formCContainer = document.getElementById('form-c-container');
        
        if (toggleMaterial && formCContainer) {
            toggleMaterial.addEventListener('change', function() {
                if (this.checked) {
                    formCContainer.style.display = 'block';
                    // Tambahkan required pada baris pertama jika dimunculkan
                    document.querySelector('input[name="materials[0][name]"]').setAttribute('required', 'required');
                } else {
                    formCContainer.style.display = 'none';
                    document.querySelector('input[name="materials[0][name]"]').removeAttribute('required');
                }
            });
        }

        let rowCount = 1;
        const tbody = document.querySelector('#engineer-material-table tbody');
        const addBtn = document.getElementById('add-mat-row');
        
        if (addBtn && tbody) {
            addBtn.addEventListener('click', function () {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="text" name="materials[${rowCount}][name]" class="form-control form-control-sm" placeholder="Nama Material" required></td>
                    <td><input type="text" name="materials[${rowCount}][spec]" class="form-control form-control-sm" placeholder="Spesifikasi"></td>
                    <td><input type="number" name="materials[${rowCount}][qty]" class="form-control form-control-sm" value="1" min="1" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-mat-row"><i class="fas fa-trash"></i></button></td>
                `;
                tbody.appendChild(tr);
                rowCount++;
            });

            tbody.addEventListener('click', function (e) {
                if (e.target.closest('.remove-mat-row')) {
                    if (tbody.children.length > 1) {
                        e.target.closest('tr').remove();
                    } else {
                        alert('Minimal harus ada 1 baris material jika opsi ini diaktifkan.');
                    }
                }
            });
        }
    });
</script>
@endpush
