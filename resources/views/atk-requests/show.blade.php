@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item"><a href="{{ route('atk-requests.index') }}">Permintaan</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
@php
    $user = auth()->user();
    $isManagerApproval = ($atkRequest->status_approval === 'Menunggu Persetujuan Manager' && ($user->isManager() || $user->isAdmin() || $user->isSuperAdmin()));
    $isAdminApproval = ($atkRequest->status_approval === 'Menunggu Persetujuan Admin GA' && ($user->isAdmin() || $user->isSuperAdmin()));
    $isApprovalMode = $isManagerApproval || $isAdminApproval;
    $showItemStatus = !in_array($atkRequest->status_approval, ['Draft']);
@endphp

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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-pill shadow-sm mb-3">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Details Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-primary font-weight-bold mb-0">
                    <i class="fas fa-file-alt mr-2"></i>Detail Permintaan {{ ($atkRequest->request_type ?? 'ATK') === 'Rumah Tangga' ? 'Rumah Tangga' : 'ATK' }} - {{ ($atkRequest->request_type ?? 'ATK') === 'Rumah Tangga' ? 'RT' : 'ATK' }}-{{ str_pad($atkRequest->id, 4, '0', STR_PAD_LEFT) }}
                </h3>
                <div>
                    @php
                        $hasApprovedItems = $atkRequest->items->where('status_approval', 'Disetujui')->count() > 0;
                        $hasReviewedItems = $atkRequest->items->whereIn('status_approval', ['Disetujui', 'Ditolak'])->count() > 0;
                    @endphp

                    @if($hasApprovedItems)
                        <a href="{{ route('atk-requests.print', $atkRequest->id) }}" target="_blank" class="btn btn-primary rounded-pill btn-sm shadow-sm mr-2">
                            <i class="fas fa-print"></i> Cetak Formulir
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm shadow-sm mr-2" disabled title="Formulir hanya dapat dicetak jika sudah ada barang yang disetujui">
                            <i class="fas fa-print"></i> Cetak Formulir
                        </button>
                    @endif

                    @if(in_array($atkRequest->status_approval, ['Draft', 'Ditolak/Revisi', 'Menunggu Persetujuan Manager']) && $atkRequest->user_id === auth()->id() && !$hasReviewedItems)
                        <a href="{{ route('atk-requests.edit', $atkRequest->id) }}" class="btn btn-success rounded-pill btn-sm shadow-sm">
                            <i class="fas fa-pencil-alt"></i> Edit Permintaan
                        </a>
                    @endif
                </div>
            </div>
            
            <div class="card-body">
                <!-- Metadata table -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="font-weight-bold" style="width: 40%;">Nama Pengaju</td>
                                <td>: {{ $atkRequest->nama }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Divisi / Unit Kerja</td>
                                <td>: {{ $atkRequest->divisi }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="font-weight-bold" style="width: 40%;">Periode Permintaan</td>
                                <td>: <span class="badge badge-info">{{ $atkRequest->bulan_permintaan }}/{{ $atkRequest->tahun_permintaan }}</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Tanggal Pengajuan</td>
                                <td>: {{ $atkRequest->created_at->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <h5 class="text-primary font-weight-bold mb-3"><i class="fas fa-list-ol mr-2"></i>Daftar Barang yang Diminta</h5>
                
                @if($isApprovalMode)
                <form action="{{ route('atk-requests.approve', $atkRequest->id) }}" method="POST">
                    @csrf
                    <div class="alert alert-info rounded-3 border-0 py-2 px-3 mb-3 d-flex flex-wrap align-items-center justify-content-between" style="border-radius: .75rem; background-color: #e0f2fe; color: #0369a1;">
                        <span><i class="fas fa-info-circle mr-1"></i> Klik tombol <strong>Setuju</strong> atau <strong>Tolak</strong> pada setiap item untuk langsung menyimpan keputusan secara instan.</span>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th>Nama Barang</th>
                                <th style="width: 10%;" class="text-center">Satuan</th>
                                <th style="width: 18%;">Spesifikasi</th>
                                <th style="width: 8%;" class="text-center">Qty</th>
                                @if($isApprovalMode)
                                    <th style="width: 27%;" class="text-center">Tindakan</th>
                                    <th style="width: 20%;">Catatan Item</th>
                                @elseif($showItemStatus)
                                    <th style="width: 15%;" class="text-center">Status Item</th>
                                    <th style="width: 18%;">Catatan Item</th>
                                @endif
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
                                    
                                    @if($isApprovalMode)
                                        <td class="text-center align-middle">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-outline-success btn-xs px-2 {{ $item->status_approval === 'Disetujui' ? 'active' : '' }}"
                                                       onclick="submitSingleItemApproval({{ $item->id }}, 'Disetujui', this)" title="Setujui Barang Ini">
                                                    <input type="radio" name="items[{{ $item->id }}][status]" value="Disetujui" autocomplete="off" {{ $item->status_approval === 'Disetujui' ? 'checked' : '' }}> <i class="fas fa-check"></i> Setuju
                                                </label>
                                                <label class="btn btn-outline-danger btn-xs px-2 {{ $item->status_approval === 'Ditolak' ? 'active' : '' }}"
                                                       onclick="submitSingleItemApproval({{ $item->id }}, 'Ditolak', this)" title="Tolak Barang Ini">
                                                    <input type="radio" name="items[{{ $item->id }}][status]" value="Ditolak" autocomplete="off" {{ $item->status_approval === 'Ditolak' ? 'checked' : '' }}> <i class="fas fa-times"></i> Tolak
                                                </label>
                                                <label class="btn btn-outline-warning btn-xs px-2 {{ ($item->status_approval === 'Pending' || !$item->status_approval) ? 'active' : '' }}"
                                                       onclick="submitSingleItemApproval({{ $item->id }}, 'Pending', this)" title="Belum Ada Keputusan (Pending)">
                                                    <input type="radio" name="items[{{ $item->id }}][status]" value="Pending" autocomplete="off" {{ ($item->status_approval === 'Pending' || !$item->status_approval) ? 'checked' : '' }}> <i class="fas fa-hourglass-half"></i> Pending
                                                </label>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <input type="text" name="items[{{ $item->id }}][notes]" class="form-control form-control-sm" placeholder="Catatan..." value="{{ $item->notes }}" onchange="saveItemNote({{ $item->id }}, this)">
                                        </td>
                                    @elseif($showItemStatus)
                                        <td class="text-center align-middle">
                                            @if($item->status_approval === 'Disetujui')
                                                <span class="badge badge-success"><i class="fas fa-check"></i> Disetujui</span>
                                            @elseif($item->status_approval === 'Ditolak')
                                                <span class="badge badge-danger"><i class="fas fa-times"></i> Ditolak</span>
                                            @else
                                                <span class="badge badge-warning text-dark"><i class="fas fa-hourglass-half"></i> Pending</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-muted small">{{ $item->notes ?? '-' }}</td>
                                    @endif
                                </tr>
                            @endforeach

                            <!-- Custom Items -->
                            @if($atkRequest->items->where('is_custom', true)->count() > 0)
                                <tr class="bg-light">
                                    <td colspan="{{ $isApprovalMode ? 7 : ($showItemStatus ? 7 : 5) }}" class="font-weight-bold text-primary small py-2">
                                        <i class="fas fa-edit mr-1"></i> Barang Kustom (Tidak Terdaftar)
                                    </td>
                                </tr>
                                @foreach($atkRequest->items->where('is_custom', true) as $item)
                                    <tr>
                                        <td class="text-center align-middle">{{ $no++ }}</td>
                                        <td class="font-weight-600 align-middle text-danger">{{ $item->item_name }}</td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-secondary">{{ $item->unit }}</span>
                                        </td>
                                        <td class="align-middle text-muted">
                                            {{ $item->description ?? '-' }}
                                            @if($item->attachment)
                                                <br>
                                                <a href="{{ asset('storage/' . $item->attachment) }}" target="_blank" class="badge badge-success mt-1">
                                                    <i class="fas fa-paperclip mr-1 text-white"></i> Lihat Lampiran
                                                </a>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle font-weight-bold">{{ $item->quantity }}</td>
                                        
                                        @if($isApprovalMode)
                                            <td class="text-center align-middle">
                                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                    <label class="btn btn-outline-success btn-xs px-2 {{ $item->status_approval === 'Disetujui' ? 'active' : '' }}"
                                                           onclick="submitSingleItemApproval({{ $item->id }}, 'Disetujui', this)" title="Setujui Barang Ini">
                                                        <input type="radio" name="items[{{ $item->id }}][status]" value="Disetujui" autocomplete="off" {{ $item->status_approval === 'Disetujui' ? 'checked' : '' }}> <i class="fas fa-check"></i> Setuju
                                                    </label>
                                                    <label class="btn btn-outline-danger btn-xs px-2 {{ $item->status_approval === 'Ditolak' ? 'active' : '' }}"
                                                           onclick="submitSingleItemApproval({{ $item->id }}, 'Ditolak', this)" title="Tolak Barang Ini">
                                                        <input type="radio" name="items[{{ $item->id }}][status]" value="Ditolak" autocomplete="off" {{ $item->status_approval === 'Ditolak' ? 'checked' : '' }}> <i class="fas fa-times"></i> Tolak
                                                    </label>
                                                    <label class="btn btn-outline-warning btn-xs px-2 {{ ($item->status_approval === 'Pending' || !$item->status_approval) ? 'active' : '' }}"
                                                           onclick="submitSingleItemApproval({{ $item->id }}, 'Pending', this)" title="Belum Ada Keputusan (Pending)">
                                                        <input type="radio" name="items[{{ $item->id }}][status]" value="Pending" autocomplete="off" {{ ($item->status_approval === 'Pending' || !$item->status_approval) ? 'checked' : '' }}> <i class="fas fa-hourglass-half"></i> Pending
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <input type="text" name="items[{{ $item->id }}][notes]" class="form-control form-control-sm" placeholder="Catatan..." value="{{ $item->notes }}" onchange="saveItemNote({{ $item->id }}, this)">
                                            </td>
                                        @elseif($showItemStatus)
                                            <td class="text-center align-middle">
                                                @if($item->status_approval === 'Disetujui')
                                                    <span class="badge badge-success"><i class="fas fa-check"></i> Disetujui</span>
                                                @elseif($item->status_approval === 'Ditolak')
                                                    <span class="badge badge-danger"><i class="fas fa-times"></i> Ditolak</span>
                                                @else
                                                    <span class="badge badge-warning text-dark"><i class="fas fa-hourglass-half"></i> Pending</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-muted small">{{ $item->notes ?? '-' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($isApprovalMode)
                    <div class="card p-4 bg-light rounded-4 mt-4 border-0 shadow-sm">
                        <div class="form-group mb-3">
                            <label for="general_notes" class="font-weight-bold text-dark">
                                Catatan Umum {{ $isManagerApproval ? 'Manager' : 'Admin GA' }} (Opsional):
                            </label>
                            <textarea name="{{ $isManagerApproval ? 'manager_notes' : 'notes' }}" id="general_notes" class="form-control rounded-3" rows="3" placeholder="Tulis catatan persetujuan keseluruhan di sini..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end align-items-center" style="gap: 12px;">
                            <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times mr-1"></i> Tolak Semua
                            </button>
                            <button type="button" class="btn btn-outline-success rounded-pill px-4" onclick="approveAllItems()">
                                <i class="fas fa-check-double mr-1"></i> Setuju Semua
                            </button>
                            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                                <i class="fas fa-check-circle mr-1"></i> Proses Persetujuan
                            </button>
                        </div>
                    </div>
                </form>
                @endif

                @if($atkRequest->status_approval === 'Ditolak/Revisi')
                    @if($atkRequest->manager_notes)
                        <div class="alert alert-danger rounded-4 border-0 p-3 mt-4 mb-2">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Penolakan Manager:</h6>
                            <span>{{ $atkRequest->manager_notes }}</span>
                        </div>
                    @endif
                    @if($atkRequest->notes)
                        <div class="alert alert-danger rounded-4 border-0 p-3 mt-2">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Penolakan Admin GA:</h6>
                            <span>{{ $atkRequest->notes }}</span>
                        </div>
                    @endif
                @else
                    @if($atkRequest->manager_notes)
                        <div class="alert alert-info rounded-4 border-0 p-3 mt-4 mb-2">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-comment-dots mr-1"></i> Catatan Manager:</h6>
                            <span>{{ $atkRequest->manager_notes }}</span>
                        </div>
                    @endif
                    @if($atkRequest->notes)
                        <div class="alert alert-info rounded-4 border-0 p-3 mt-2">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-comment-dots mr-1"></i> Catatan Admin GA:</h6>
                            <span>{{ $atkRequest->notes }}</span>
                        </div>
                    @endif
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
                <div class="text-center mb-3">
                    <h6 class="font-weight-bold text-muted">Status Saat Ini:</h6>
                    @if($atkRequest->status_approval === 'Draft')
                        <span class="badge badge-secondary text-lg px-4 py-2 rounded-pill">Draft</span>
                    @elseif($atkRequest->status_approval === 'Menunggu Persetujuan Manager')
                        <span class="badge badge-warning text-lg px-4 py-2 rounded-pill">Review Manager</span>
                    @elseif($atkRequest->status_approval === 'Menunggu Persetujuan Admin GA')
                        <span class="badge badge-info text-lg px-4 py-2 rounded-pill">Review Admin GA</span>
                    @elseif($atkRequest->status_approval === 'Disetujui')
                        <span class="badge badge-success text-lg px-4 py-2 rounded-pill">Disetujui</span>
                    @elseif($atkRequest->status_approval === 'Ditolak/Revisi')
                        <span class="badge badge-danger text-lg px-4 py-2 rounded-pill">Ditolak/Revisi</span>
                    @endif
                </div>

                <!-- Ringkasan Stat Item Approval -->
                <div class="p-3 bg-light rounded-4 mb-4 border text-center">
                    <div class="font-weight-bold text-xs text-muted mb-2 text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="fas fa-boxes mr-1"></i> Ringkasan Status Barang
                    </div>
                    <div class="d-flex justify-content-around align-items-center">
                        <div>
                            <span class="d-block font-weight-bold text-success text-md">{{ $atkRequest->items->where('status_approval', 'Disetujui')->count() }}</span>
                            <small class="text-muted">Disetujui</small>
                        </div>
                        <div>
                            <span class="d-block font-weight-bold text-danger text-md">{{ $atkRequest->items->where('status_approval', 'Ditolak')->count() }}</span>
                            <small class="text-muted">Ditolak</small>
                        </div>
                        <div>
                            <span class="d-block font-weight-bold text-warning text-md">{{ $atkRequest->items->filter(function($i){ return $i->status_approval === 'Pending' || empty($i->status_approval); })->count() }}</span>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>

                @if($isApprovalMode)
                    <div class="p-3 bg-light rounded-4 mb-3 border">
                        <label for="sidebar_general_notes" class="font-weight-bold text-sm text-primary mb-1">
                            <i class="fas fa-comment-alt mr-1"></i> Catatan Umum {{ $isManagerApproval ? 'Manager' : 'Admin GA' }}:
                        </label>
                        <textarea id="sidebar_general_notes" class="form-control form-control-sm" rows="3" placeholder="Tulis catatan persetujuan keseluruhan di sini..." onchange="saveGeneralNotes(this)">{{ $isManagerApproval ? $atkRequest->manager_notes : $atkRequest->notes }}</textarea>
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Catatan ini tersimpan otomatis &amp; dapat dilihat oleh pengaju.</small>
                    </div>
                @endif

                <div class="timeline timeline-inverse small">
                    <!-- Stage 1: Pengajuan -->
                    <div>
                        <i class="fas fa-paper-plane bg-primary"></i>
                        <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                            <span class="time"><i class="fas fa-clock"></i> {{ $atkRequest->created_at->format('d/m H:i') }}</span>
                            <h3 class="timeline-header border-0 font-weight-bold text-sm">Diajukan</h3>
                            <div class="timeline-body p-2 text-muted">
                                Oleh: <strong>{{ $atkRequest->user->name }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Stage 2: Persetujuan Manager -->
                    @if($atkRequest->status_approval !== 'Draft')
                        <div>
                            @if($atkRequest->manager_approved_by)
                                <i class="fas fa-check bg-success"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($atkRequest->manager_approved_at)->format('d/m H:i') }}</span>
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Persetujuan Manager</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Disetujui oleh: <strong>{{ $atkRequest->managerApprover->name ?? 'Manager' }}</strong>
                                        @if($atkRequest->manager_notes)
                                            <div class="mt-2 text-dark font-italic bg-white p-2 rounded border-left border-success">
                                                <i class="fas fa-quote-left text-muted mr-1"></i> {{ $atkRequest->manager_notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @elseif($atkRequest->status_approval === 'Menunggu Persetujuan Manager')
                                <i class="fas fa-clock bg-warning"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Review Manager</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Menunggu persetujuan dari Operational Manager.
                                        @if($atkRequest->manager_notes)
                                            <div class="mt-2 text-dark font-italic bg-white p-2 rounded border-left border-info">
                                                <i class="fas fa-comment-dots text-info mr-1"></i> Catatan Sementara: {{ $atkRequest->manager_notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @elseif($atkRequest->status_approval === 'Ditolak/Revisi' && !$atkRequest->manager_approved_by)
                                <i class="fas fa-times bg-danger"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $atkRequest->updated_at->format('d/m H:i') }}</span>
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Ditolak Manager</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Catatan: {{ $atkRequest->manager_notes ?? $atkRequest->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Stage 3: Persetujuan Admin GA -->
                    @if($atkRequest->status_approval !== 'Draft' && $atkRequest->status_approval !== 'Menunggu Persetujuan Manager' && ($atkRequest->manager_approved_by || $atkRequest->status_approval === 'Ditolak/Revisi'))
                        <div>
                            @if($atkRequest->approved_by)
                                <i class="fas fa-check-double bg-success"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $atkRequest->approved_at ? $atkRequest->approved_at->format('d/m H:i') : '' }}</span>
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Persetujuan Admin GA</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Disetujui oleh: <strong>{{ $atkRequest->approver->name ?? 'Admin GA' }}</strong>
                                        @if($atkRequest->notes)
                                            <div class="mt-2 text-dark font-italic bg-white p-2 rounded border-left border-success">
                                                <i class="fas fa-quote-left text-muted mr-1"></i> {{ $atkRequest->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @elseif($atkRequest->status_approval === 'Menunggu Persetujuan Admin GA')
                                <i class="fas fa-clock bg-info"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Review Admin GA</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Menunggu verifikasi akhir dari Admin GA.
                                        @if($atkRequest->notes)
                                            <div class="mt-2 text-dark font-italic bg-white p-2 rounded border-left border-info">
                                                <i class="fas fa-comment-dots text-info mr-1"></i> Catatan Sementara: {{ $atkRequest->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @elseif($atkRequest->status_approval === 'Ditolak/Revisi' && $atkRequest->manager_approved_by)
                                <i class="fas fa-times bg-danger"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $atkRequest->updated_at->format('d/m H:i') }}</span>
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Ditolak Admin GA</h3>
                                    <div class="timeline-body p-2 text-muted">
                                        Catatan: {{ $atkRequest->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Stage 4: Serah Terima Barang -->
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

                @if($isApprovalMode)
                    <hr>
                    <div class="alert alert-info rounded-4 border-0 p-3 mb-0 small">
                        <h6 class="font-weight-bold mb-1"><i class="fas fa-info-circle mr-1"></i> Mode Persetujuan</h6>
                        <span>Tentukan setuju/tolak pada setiap item barang di tabel sebelah kiri, lalu klik <strong>Proses Persetujuan</strong>.</span>
                    </div>
                @elseif($atkRequest->status_approval === 'Disetujui' && !$atkRequest->diberikan_pada && ($user->isAdmin() || $user->isSuperAdmin()))
                    <hr>
                    <button type="button" class="btn btn-primary btn-block rounded-pill shadow-sm" data-toggle="modal" data-target="#handoverModal">
                        <i class="fas fa-handshake mr-1"></i> Konfirmasi Penyerahan
                    </button>
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
                    <h5 class="modal-title font-weight-bold text-danger" id="rejectModalLabel"><i class="fas fa-exclamation-triangle mr-1"></i> Tolak Seluruh Pengajuan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-2 pt-0">
                    <div class="form-group">
                        <label for="notes" class="font-weight-600">Alasan Penolakan:</label>
                        <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Tulis alasan penolakan seluruh barang secara lengkap..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Tolak Seluruhnya</button>
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
                <div class="modal-body pb-2 pt-0">
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
@endsection

@push('scripts')
<script>
    function submitSingleItemApproval(itemId, status, labelElement) {
        var $label = $(labelElement);
        var $group = $label.closest('.btn-group');
        var $noteInput = $group.closest('tr').find('input[name="items[' + itemId + '][notes]"]');
        var notes = $noteInput.val() || '';

        // UI Optimistic Highlight
        $group.find('.btn').removeClass('active');
        $label.addClass('active');
        $group.find('input[type="radio"]').prop('checked', false);
        $label.find('input[type="radio"]').prop('checked', true);

        // Toast feedback
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });

        $.ajax({
            url: "{{ route('atk-requests.approve-item', $atkRequest->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                item_id: itemId,
                status: status,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    if (window.Swal) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                    }
                    if (response.all_completed) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    }
                }
            },
            error: function(xhr) {
                var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan status item.';
                alert(errorMsg);
            }
        });
    }

    function saveItemNote(itemId, inputElement) {
        var notes = $(inputElement).val();
        var $group = $(inputElement).closest('tr').find('.btn-group');
        var status = $group.find('input[type="radio"]:checked').val() || 'Pending';

        $.ajax({
            url: "{{ route('atk-requests.approve-item', $atkRequest->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                item_id: itemId,
                status: status,
                notes: notes
            }
        });
    }

    function saveGeneralNotes(inputElement) {
        var notes = $(inputElement).val();
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });

        $.ajax({
            url: "{{ route('atk-requests.update-notes', $atkRequest->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                notes: notes
            },
            success: function(response) {
                if (response.success && window.Swal) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                }
            }
        });
    }

    function approveAllItems() {
        $('.btn-group-toggle').each(function() {
            var $group = $(this);
            var $setujuLabel = $group.find('label.btn-outline-success');
            if ($setujuLabel.length) {
                $group.find('.btn').removeClass('active');
                $setujuLabel.addClass('active');
                $group.find('input[type="radio"]').prop('checked', false);
                $setujuLabel.find('input[type="radio"]').prop('checked', true);
                
                var inputName = $setujuLabel.find('input[type="radio"]').attr('name');
                if (inputName) {
                    var match = inputName.match(/\d+/);
                    if (match) {
                        submitSingleItemApproval(match[0], 'Disetujui', $setujuLabel[0]);
                    }
                }
            }
        });
    }
</script>
@endpush
