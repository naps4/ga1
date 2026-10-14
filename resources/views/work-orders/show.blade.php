@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0">Detail Work Order #WO-{{ str_pad($workOrder->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <div class="d-flex align-items-center">
                <a href="{{ route('work-orders.print', $workOrder->id) }}" class="btn btn-primary rounded-pill mr-2 shadow-sm">
                    <i class="fas fa-print"></i> Cetak Dokumen (PDF)
                </a>
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

        <div class="row">
            <!-- Informasi Utama WO -->
            <div class="col-md-6">
                @if($workOrder->status === 'Ditolak')
                    <div class="alert alert-danger rounded-4 shadow-sm mb-4 border-0">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-times-circle fa-2x mr-3 text-danger"></i>
                            <div>
                                <h5 class="font-weight-bold mb-0 text-danger">Tiket Work Order Ditolak</h5>
                                <small class="text-muted">Pengajuan tiket ini telah ditolak dan tidak diproses lebih lanjut.</small>
                            </div>
                        </div>
                        <hr class="my-2 border-danger">
                        <strong>Alasan Penolakan:</strong>
                        <div class="p-3 bg-white rounded-3 mt-1 text-dark border border-danger">
                            {{ $workOrder->rejection_note ?? 'Tidak ada catatan penolakan.' }}
                        </div>
                    </div>
                @endif

                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-primary"><i class="fas fa-info-circle"></i> Informasi Tiket (Form A)</h3>
                        <div class="card-tools">
                            @php
                                $badgeClass = 'secondary';
                                if ($workOrder->status == 'Pengecekan Engineer') $badgeClass = 'info';
                                elseif ($workOrder->status == 'Sedang Dikerjakan') $badgeClass = 'primary';
                                elseif ($workOrder->status == 'Menunggu Pengadaan Material') $badgeClass = 'warning';
                                elseif ($workOrder->status == 'Menunggu Serah Terima') $badgeClass = 'primary';
                                elseif ($workOrder->status == 'Selesai') $badgeClass = 'success';
                                elseif ($workOrder->status == 'Ditolak') $badgeClass = 'danger';
                            @endphp
                            <span class="badge badge-{{ $badgeClass }}">{{ $workOrder->status }}</span>
                            @if($workOrder->status === 'Menunggu Serah Terima')
                                @php
                                    $ho = $workOrder->handover;
                                    $hoText = '';
                                    if (!$ho || !$ho->prepared_by) $hoText = 'Admin/HRGA';
                                    elseif ($ho->known_by && !$ho->accepted_by) $hoText = 'Klien/Pemohon';
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
                                <td>: <a href="{{ asset('storage/' . $workOrder->attachment_request) }}" class="btn btn-xs btn-outline-info rounded-pill"><i class="fas fa-paperclip"></i> Lihat Lampiran</a></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Modul Penugasan & Aksi Tiket untuk Admin / Operational Manager -->
                @php
                    $canActionWO = (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isManager()) && $workOrder->status === 'Menunggu Penugasan';
                @endphp
                @if($canActionWO)
                    <div class="card shadow-sm border-0 rounded-4 bg-light border-primary mb-4">
                        <div class="card-body">
                            <h5 class="text-primary mb-3"><i class="fas fa-tasks"></i> Tindakan Pengajuan Tiket</h5>
                            
                            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                <label class="text-sm font-weight-bold text-dark mb-1"><i class="fas fa-user-plus mr-1"></i> Tugaskan Engineer</label>
                                <form action="{{ route('work-orders.assign', $workOrder->id) }}" method="POST" class="mb-3">
                                    @csrf
                                    <div class="input-group">
                                        <select name="assigned_engineer_id" class="form-control rounded-left-pill" required>
                                            <option value="">-- Pilih Teknisi --</option>
                                            @foreach($engineers as $engineer)
                                                <option value="{{ $engineer->id }}">{{ $engineer->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary rounded-right-pill font-weight-bold">
                                                Tugaskan
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif

                            <div class="d-flex align-items-center justify-content-between pt-2 {{ (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()) ? 'border-top' : '' }}">
                                <div>
                                    <span class="text-sm font-weight-bold text-danger"><i class="fas fa-ban mr-1"></i> Tolak Pengajuan Tiket</span>
                                    <p class="text-xs text-muted mb-0">Tolak tiket jika tidak valid atau dibatalkan.</p>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 font-weight-bold ml-2 shadow-sm" data-toggle="modal" data-target="#modalRejectWO">
                                    <i class="fas fa-times-circle mr-1"></i> Tolak Tiket
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Status Pengerjaan (Tracking) -->
                @if($workOrder->status === 'Ditolak')
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-danger font-weight-bold"><i class="fas fa-times-circle text-danger"></i> Riwayat Penolakan</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline timeline-inverse mb-3">
                            <div>
                                <i class="fas fa-file-alt bg-primary"></i>
                                <div class="timeline-item border-0 shadow-none bg-transparent">
                                    <h3 class="timeline-header border-0 pb-1 text-dark">Pengajuan Tiket Masuk</h3>
                                    <div class="timeline-body pt-0 text-muted text-sm pb-0">
                                        Diajukan oleh: <strong>{{ $workOrder->user->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $workOrder->created_at->format('d M Y H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="fas fa-times bg-danger"></i>
                                <div class="timeline-item border-0 shadow-none bg-transparent">
                                    <h3 class="timeline-header border-0 pb-1 text-danger font-weight-bold">Tiket Ditolak</h3>
                                    <div class="timeline-body pt-0 text-muted text-sm pb-0">
                                        <strong>Alasan:</strong> {{ $workOrder->rejection_note }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="far fa-clock bg-gray"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($workOrder->status !== 'Menunggu Penugasan')
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-dark font-weight-bold"><i class="fas fa-stream text-primary"></i> Timeline Pengerjaan</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline timeline-inverse mb-3">
                            <!-- Step 1: Pengecekan Engineer -->
                            <div>
                                <i class="fas fa-search {{ in_array($workOrder->status, ['Pengecekan Engineer', 'Menunggu Pengadaan Material', 'Sedang Dikerjakan', 'Menunggu Serah Terima', 'Selesai']) ? 'bg-primary' : 'bg-secondary' }}"></i>
                                <div class="timeline-item border-0 shadow-none bg-transparent">
                                    <h3 class="timeline-header border-0 pb-1 {{ $workOrder->status == 'Pengecekan Engineer' ? 'text-primary font-weight-bold' : 'text-dark' }}">Pengecekan Lapangan</h3>
                                    @if($workOrder->check_result)
                                        <div class="timeline-body pt-0 text-muted text-sm pb-0">
                                            <strong>Detail Pengecekan:</strong><br>
                                            {{ $workOrder->check_result }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Step 2: Menunggu Kebutuhan (Only if FPU requested) -->
                            @if($workOrder->fpuRequest || $workOrder->status === 'Menunggu Pengadaan Material')
                            <div>
                                <i class="fas fa-box {{ in_array($workOrder->status, ['Menunggu Pengadaan Material', 'Sedang Dikerjakan', 'Menunggu Serah Terima', 'Selesai']) ? 'bg-warning text-dark' : 'bg-secondary' }}"></i>
                                <div class="timeline-item border-0 shadow-none bg-transparent">
                                    <h3 class="timeline-header border-0 pb-1 {{ $workOrder->status == 'Menunggu Pengadaan Material' ? 'text-warning font-weight-bold' : 'text-dark' }}">Menunggu Kebutuhan</h3>
                                    @if($workOrder->engineer_needs)
                                        <div class="timeline-body pt-0 text-muted text-sm pb-1">
                                            <strong>Kebutuhan Diajukan:</strong><br>
                                            {{ $workOrder->engineer_needs }}
                                        </div>
                                    @endif
                                    @if($workOrder->status === 'Menunggu Pengadaan Material' && $workOrder->is_check_approved && $workOrder->fpuRequest && in_array($workOrder->fpuRequest->status_approval, ['Menunggu Admin GA', 'Disetujui (Selesai)']) && auth()->user()->id === $workOrder->assigned_engineer_id)
                                    <div class="timeline-body pt-1 pb-0">
                                        <form action="{{ route('work-orders.start', $workOrder->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill shadow-sm"><i class="fas fa-play"></i> Mulai Pengerjaan</button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Step 3: Sedang Dikerjakan -->
                            <div>
                                <i class="fas fa-tools {{ in_array($workOrder->status, ['Sedang Dikerjakan', 'Menunggu Serah Terima', 'Selesai']) ? 'bg-info' : 'bg-secondary' }}"></i>
                                <div class="timeline-item border-0 shadow-none bg-transparent">
                                    <h3 class="timeline-header border-0 pb-1 {{ $workOrder->status == 'Sedang Dikerjakan' ? 'text-info font-weight-bold' : 'text-dark' }}">Sedang Dikerjakan</h3>
                                    @if($workOrder->progresses && $workOrder->progresses->count() > 0)
                                        <div class="timeline-body pt-0 text-muted text-sm pb-0">
                                            <strong>Progres Terakhir:</strong><br>
                                            {{ $workOrder->progresses->last()->description }}
                                            <br><small class="text-info">{{ $workOrder->progresses->last()->created_at->diffForHumans() }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Step 4: Selesai / Serah Terima -->
                            <div>
                                <i class="fas fa-check {{ $workOrder->status === 'Selesai' ? 'bg-success' : 'bg-secondary' }}"></i>
                                <div class="timeline-item border-0 shadow-none bg-transparent">
                                    <h3 class="timeline-header border-0 pb-1 {{ $workOrder->status == 'Selesai' ? 'text-success font-weight-bold' : 'text-dark' }}">Selesai</h3>
                                    @if($workOrder->engineer_report)
                                        <div class="timeline-body pt-0 text-muted text-sm pb-0">
                                            <strong>Laporan:</strong><br>
                                            {{ $workOrder->engineer_report }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <i class="far fa-clock bg-gray"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Panel Engineer -->
            <div class="col-md-6">
                @if($workOrder->status === 'Ditolak')
                    <div class="card shadow-sm border-0 rounded-4 mb-4 border-danger">
                        <div class="card-body text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-ban fa-4x text-danger" style="opacity: 0.5;"></i>
                            </div>
                            <h5 class="font-weight-bold text-dark">Pengajuan Work Order Ditolak</h5>
                            <p class="text-muted text-sm mb-0">Tiket ini telah ditolak oleh Admin / Operational Manager. Tidak ada tahapan pengecekan maupun pengerjaan teknisi untuk tiket ini.</p>
                        </div>
                    </div>
                @else
                    <!-- Form Pengecekan (Form B) -->
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-success"><i class="fas fa-tools"></i> Pengecekan Engineer (Form B)</h3>
                    </div>
                    <div class="card-body">
                        @if($workOrder->status === 'Pengecekan Engineer' && auth()->user()->id === $workOrder->assigned_engineer_id)
                            @if($workOrder->rejection_note)
                                <div class="alert alert-warning rounded-4 shadow-sm mb-3">
                                    <strong><i class="fas fa-exclamation-triangle"></i> Revisi Diperlukan:</strong><br>
                                    {{ $workOrder->rejection_note }}
                                </div>
                            @endif
                            <form action="{{ route('work-orders.update-check', $workOrder->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label>Hasil Pengecekan Lapangan</label>
                                    <textarea name="check_result" class="form-control rounded-4" rows="3" required>{{ old('check_result', $workOrder->check_result) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Kebutuhan Material/Barang</label>
                                    <textarea name="engineer_needs" class="form-control rounded-4" rows="2" placeholder="(Opsional) Tuliskan jika butuh sparepart...">{{ old('engineer_needs', $workOrder->engineer_needs) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                        <input type="checkbox" class="custom-control-input" id="needs_material" name="needs_material" value="1"
                                            {{ old('needs_material', $workOrder->fpuRequest ? '1' : '') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="needs_material">Butuh Pengadaan Material (Buka Form C/FPU)</label>
                                    </div>
                                </div>

                                <!-- Bagian Form C (FPU) Langsung -->
                                <div id="form-c-container" style="display: {{ old('needs_material', $workOrder->fpuRequest ? '1' : '') == '1' ? 'block' : 'none' }};" class="mb-4 p-3 bg-light rounded-4 border border-warning">
                                    <h5 class="text-warning font-weight-bold"><i class="fas fa-box"></i> Rincian Material (Form C)</h5>
                                    <p class="text-sm text-muted">Silakan tentukan klasifikasi dan rinci material/barang yang dibutuhkan untuk pekerjaan ini.</p>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label class="text-sm">Prioritas</label>
                                            <select name="priority" class="form-control form-control-sm">
                                                <option value="Biasa" {{ old('priority', $workOrder->fpuRequest->priority ?? '') == 'Biasa' ? 'selected' : '' }}>Biasa</option>
                                                <option value="Penting" {{ old('priority', $workOrder->fpuRequest->priority ?? '') == 'Penting' ? 'selected' : '' }}>Penting</option>
                                                <option value="Sangat Penting" {{ old('priority', $workOrder->fpuRequest->priority ?? '') == 'Sangat Penting' ? 'selected' : '' }}>Sangat Penting</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="text-sm">Pelaksana</label>
                                            <select name="executor_type" class="form-control form-control-sm">
                                                <option value="Internal" {{ old('executor_type', $workOrder->fpuRequest->executor_type ?? '') == 'Internal' ? 'selected' : '' }}>Internal</option>
                                                <option value="Eksternal" {{ old('executor_type', $workOrder->fpuRequest->executor_type ?? '') == 'Eksternal' ? 'selected' : '' }}>Eksternal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="text-sm">Jenis</label>
                                            <select name="type" class="form-control form-control-sm">
                                                <option value="Bangunan" {{ old('type', $workOrder->fpuRequest->type ?? '') == 'Bangunan' ? 'selected' : '' }}>Bangunan</option>
                                                <option value="Kendaraan" {{ old('type', $workOrder->fpuRequest->type ?? '') == 'Kendaraan' ? 'selected' : '' }}>Kendaraan</option>
                                                <option value="Lainnya" {{ old('type', $workOrder->fpuRequest->type ?? '') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="text-sm">Kategori</label>
                                            <select name="category" class="form-control form-control-sm">
                                                <option value="Pembuatan" {{ old('category', $workOrder->fpuRequest->category ?? '') == 'Pembuatan' ? 'selected' : '' }}>Pembuatan</option>
                                                <option value="Perbaikan" {{ old('category', $workOrder->fpuRequest->category ?? '') == 'Perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                                                <option value="Pengadaan" {{ old('category', $workOrder->fpuRequest->category ?? '') == 'Pengadaan' ? 'selected' : '' }}>Pengadaan</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm bg-white" id="engineer-material-table">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="min-width: 140px;">Nama Material</th>
                                                    <th style="min-width: 140px;">Spesifikasi</th>
                                                    <th width="15%" style="min-width: 80px;">Qty</th>
                                                    <th width="10%" style="min-width: 60px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($workOrder->fpuRequest && $workOrder->fpuRequest->materials->count() > 0)
                                                    @foreach($workOrder->fpuRequest->materials as $index => $mat)
                                                        <tr>
                                                            <td><input type="text" name="materials[{{ $index }}][name]" value="{{ old('materials.'.$index.'.name', $mat->material_name) }}" class="form-control form-control-sm" required placeholder="Contoh: Lampu LED"></td>
                                                            <td><input type="text" name="materials[{{ $index }}][spec]" value="{{ old('materials.'.$index.'.spec', $mat->specification) }}" class="form-control form-control-sm" placeholder="Contoh: 18 Watt Philips"></td>
                                                            <td><input type="number" name="materials[{{ $index }}][qty]" value="{{ old('materials.'.$index.'.qty', $mat->qty) }}" class="form-control form-control-sm" min="1" required></td>
                                                            <td><button type="button" class="btn btn-danger btn-sm remove-mat-row"><i class="fas fa-trash"></i></button></td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td><input type="text" name="materials[0][name]" class="form-control form-control-sm" placeholder="Contoh: Lampu LED"></td>
                                                        <td><input type="text" name="materials[0][spec]" class="form-control form-control-sm" placeholder="Contoh: 18 Watt Philips"></td>
                                                        <td><input type="number" name="materials[0][qty]" class="form-control form-control-sm" value="1" min="1"></td>
                                                        <td><button type="button" class="btn btn-danger btn-sm remove-mat-row"><i class="fas fa-trash"></i></button></td>
                                                    </tr>
                                                @endif
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
                                        <a href="{{ asset('storage/' . $workOrder->attachment_check) }}" class="btn btn-xs btn-info rounded-pill"><i class="fas fa-image"></i> Lihat Bukti Pengecekan</a>
                                    @endif
                                </div>

                                 @if((auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') && in_array($workOrder->status, ['Menunggu Pengadaan Material', 'Sedang Dikerjakan']))
                                    @if(!$workOrder->is_check_approved)
                                        <div class="mt-3 p-3 bg-light rounded-4 border border-danger">
                                            <h5 class="text-danger font-weight-bold"><i class="fas fa-times-circle"></i> Tindakan Hasil Cek</h5>
                                            <p class="text-sm text-muted">Jika ada kesalahan pada hasil pengecekan atau pengajuan material, Anda dapat menolak hasil cek ini. Jika sesuai, silakan setujui untuk melanjutkan pengerjaan.</p>
                                            
                                            <!-- Form Reject -->
                                            <form action="{{ route('work-orders.reject-check', $workOrder->id) }}" method="POST" id="reject-form">
                                                @csrf
                                                <div class="form-group">
                                                    <label>Alasan Penolakan / Revisi</label>
                                                    <textarea name="rejection_note" class="form-control rounded-4" rows="2" placeholder="Tuliskan catatan revisi untuk teknisi..." required></textarea>
                                                </div>
                                            </form>

                                            <!-- Form Approve -->
                                            <form action="{{ route('work-orders.approve-check', $workOrder->id) }}" method="POST" id="approve-form">
                                                @csrf
                                            </form>

                                             <div class="row">
                                                <div class="col-md-6 mb-2 mb-md-0">
                                                    <button type="submit" form="reject-form" class="btn btn-danger rounded-pill btn-block font-weight-bold">
                                                        <i class="fas fa-times-circle mr-1"></i> Reject & Kembalikan
                                                    </button>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="submit" form="approve-form" class="btn btn-success rounded-pill btn-block font-weight-bold">
                                                        <i class="fas fa-check-circle mr-1"></i> Approve / Setujui
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-3 alert alert-success rounded-4 shadow-sm border-0 d-flex align-items-center">
                                            <i class="fas fa-check-circle fa-2x mr-3 text-success"></i>
                                            <div>
                                                <h6 class="font-weight-bold mb-1 text-success">Hasil Cek Disetujui</h6>
                                                <p class="text-xs mb-0 text-dark">Hasil pengecekan lapangan ini telah disetujui oleh Admin dan terkunci dari revisi.</p>
                                            </div>
                                        </div>
                                    @endif
                                @endif
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
                            <p><strong>Status FPU:</strong> <span class="badge badge-warning">{{ $workOrder->fpuRequest->status_approval === 'Menunggu Oprt. Manager' ? 'Menunggu Admin GA' : $workOrder->fpuRequest->status_approval }}</span></p>
                            <a href="{{ route('fpu.show', $workOrder->fpuRequest->id) }}" class="btn btn-outline-warning rounded-pill btn-block text-dark font-weight-bold">
                                <i class="fas fa-file-invoice"></i> Buka / Kelola Form C (FPU)
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Log Progres Pekerjaan -->
                @if(in_array($workOrder->status, ['Sedang Dikerjakan', 'Menunggu Serah Terima', 'Selesai']))
                    <div class="card shadow-sm border-0 rounded-4 mb-4 border-info">
                        <div class="card-header bg-info text-white rounded-top-4">
                            <h3 class="card-title"><i class="fas fa-tasks"></i> Log Progres Pekerjaan</h3>
                        </div>
                        <div class="card-body">
                            @if($workOrder->progresses && $workOrder->progresses->count() > 0)
                                <div class="timeline timeline-inverse mb-4">
                                    @foreach($workOrder->progresses as $progress)
                                        <div>
                                            <i class="fas fa-check bg-info"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="far fa-clock"></i> {{ $progress->created_at->format('d M Y H:i') }}</span>
                                                <h3 class="timeline-header border-0"><strong>Progres Update</strong></h3>
                                                <div class="timeline-body">
                                                    {!! nl2br(e($progress->description)) !!}
                                                    @if($progress->attachment)
                                                        <br><br>
                                                        <a href="{{ asset('storage/' . $progress->attachment) }}" class="btn btn-sm btn-outline-info rounded-pill"><i class="fas fa-image"></i> Lihat Lampiran</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div>
                                        <i class="far fa-clock bg-gray"></i>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted text-center my-3"><i class="fas fa-history fa-2x mb-2"></i><br>Belum ada log progres.</p>
                            @endif

                            @if($workOrder->status === 'Sedang Dikerjakan' && auth()->user()->id === $workOrder->assigned_engineer_id)
                                <hr>
                                <h5>Tambah Progres Baru</h5>
                                <form action="{{ route('work-orders.store-progress', $workOrder->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <textarea name="description" class="form-control rounded-4" rows="2" placeholder="Tuliskan progres pekerjaan saat ini..." required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="attachment" accept=".jpg,.jpeg,.png,.pdf">
                                            <label class="custom-file-label">Lampiran progres (Opsional)...</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-info rounded-pill w-100"><i class="fas fa-paper-plane"></i> Kirim Progres</button>
                                </form>
                            @endif
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
                                        <a href="{{ asset('storage/' . $workOrder->attachment_report) }}" class="btn btn-xs btn-success rounded-pill"><i class="fas fa-check-double"></i> Lihat Bukti Penyelesaian</a>
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
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Modal Tolak WO -->
<div class="modal fade" id="modalRejectWO" tabindex="-1" role="dialog" aria-labelledby="modalRejectWOLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title font-weight-bold" id="modalRejectWOLabel">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Konfirmasi Penolakan Tiket
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('work-orders.reject', $workOrder->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Apakah Anda yakin ingin menolak tiket <strong>#WO-{{ str_pad($workOrder->id, 4, '0', STR_PAD_LEFT) }}</strong>?</p>
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

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleMaterial = document.getElementById('needs_material');
        const formCContainer = document.getElementById('form-c-container');
        
        if (toggleMaterial && formCContainer) {
            toggleMaterial.addEventListener('change', function() {
                const firstInput = document.querySelector('#engineer-material-table tbody tr:first-child input[name^="materials"][name$="[name]"]');
                if (this.checked) {
                    formCContainer.style.display = 'block';
                    if (firstInput) firstInput.setAttribute('required', 'required');
                } else {
                    formCContainer.style.display = 'none';
                    if (firstInput) firstInput.removeAttribute('required');
                }
            });
        }

        let rowCount = {{ $workOrder->fpuRequest && $workOrder->fpuRequest->materials->count() > 0 ? $workOrder->fpuRequest->materials->count() : 1 }};
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
