@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0">Formulir Pelayanan Umum (FPU)</h1>
            <div class="d-flex align-items-center">
                <a href="{{ route('fpu.print', $fpu->id) }}" target="_blank" class="btn btn-primary rounded-pill mr-2 shadow-sm">
                    <i class="fas fa-print"></i> Cetak FPU (PDF)
                </a>
                <a href="{{ route('work-orders.show', $fpu->work_order_id) }}" class="btn btn-outline-secondary rounded-pill">
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
            <!-- Form Input FPU -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-primary"><i class="fas fa-edit"></i> Detail Form C (FPU)</h3>
                    </div>
                    <!-- Form hanya bisa diedit oleh Admin (HRGA) dan saat status masih draft / belum verifikasi -->
                    @if((auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') && $fpu->status_approval === 'Menunggu Verifikasi HRGA')
                        <form action="{{ route('fpu.update', $fpu->id) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Prioritas</label>
                                            <select name="priority" class="form-control" required>
                                                <option value="Biasa" {{ $fpu->priority == 'Biasa' ? 'selected' : '' }}>Biasa</option>
                                                <option value="Penting" {{ $fpu->priority == 'Penting' ? 'selected' : '' }}>Penting</option>
                                                <option value="Sangat Penting" {{ $fpu->priority == 'Sangat Penting' ? 'selected' : '' }}>Sangat Penting</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Pelaksana</label>
                                            <select name="executor_type" class="form-control" required>
                                                <option value="Internal" {{ $fpu->executor_type == 'Internal' ? 'selected' : '' }}>Internal</option>
                                                <option value="Eksternal" {{ $fpu->executor_type == 'Eksternal' ? 'selected' : '' }}>Eksternal</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Jenis</label>
                                            <select name="type" class="form-control" required>
                                                <option value="Bangunan" {{ $fpu->type == 'Bangunan' ? 'selected' : '' }}>Bangunan</option>
                                                <option value="Kendaraan" {{ $fpu->type == 'Kendaraan' ? 'selected' : '' }}>Kendaraan</option>
                                                <option value="Lainnya" {{ $fpu->type == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Kategori</label>
                                            <select name="category" class="form-control" required>
                                                <option value="Pembuatan" {{ $fpu->category == 'Pembuatan' ? 'selected' : '' }}>Pembuatan</option>
                                                <option value="Perbaikan" {{ $fpu->category == 'Perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                                                <option value="Pengadaan" {{ $fpu->category == 'Pengadaan' ? 'selected' : '' }}>Pengadaan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h5 class="text-primary mb-3"><i class="fas fa-boxes"></i> Daftar Material (Pemakaian Material)</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" id="material-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="min-width: 140px;">Nama Material</th>
                                                <th style="min-width: 140px;">Spesifikasi</th>
                                                <th width="10%" style="min-width: 80px;">Qty</th>
                                                <th width="15%" style="min-width: 80px;">Satuan</th>
                                                <th style="min-width: 140px;">Keterangan</th>
                                                <th width="5%" style="min-width: 60px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($fpu->materials as $index => $mat)
                                                <tr>
                                                    <td><input type="text" name="materials[{{ $index }}][name]" value="{{ $mat->material_name }}" class="form-control form-control-sm" required></td>
                                                    <td><input type="text" name="materials[{{ $index }}][spec]" value="{{ $mat->specification }}" class="form-control form-control-sm"></td>
                                                    <td><input type="number" name="materials[{{ $index }}][qty]" value="{{ $mat->qty }}" class="form-control form-control-sm" required></td>
                                                    <td><input type="text" name="materials[{{ $index }}][unit]" value="{{ $mat->unit }}" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="materials[{{ $index }}][notes]" value="{{ $mat->notes }}" class="form-control form-control-sm"></td>
                                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td><input type="text" name="materials[0][name]" class="form-control form-control-sm" required></td>
                                                    <td><input type="text" name="materials[0][spec]" class="form-control form-control-sm"></td>
                                                    <td><input type="number" name="materials[0][qty]" class="form-control form-control-sm" value="1" required></td>
                                                    <td><input type="text" name="materials[0][unit]" class="form-control form-control-sm" value="Pcs"></td>
                                                    <td><input type="text" name="materials[0][notes]" class="form-control form-control-sm"></td>
                                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mb-3" id="add-row"><i class="fas fa-plus"></i> Tambah Baris Material</button>

                            </div>
                            <div class="card-footer bg-white border-top border-light text-right">
                                <button type="submit" name="action" value="draft" class="btn btn-outline-primary rounded-pill"><i class="fas fa-save"></i> Simpan Draft</button>
                                <button type="submit" name="action" value="verify" class="btn btn-success rounded-pill ml-2"><i class="fas fa-check-circle"></i> Simpan & Verifikasi HRGA</button>
                            </div>
                        </form>
                    @else
                        <!-- View Only Mode -->
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-sm-3"><strong>Prioritas:</strong><br>{{ $fpu->priority ?? '-' }}</div>
                                <div class="col-sm-3"><strong>Pelaksana:</strong><br>{{ $fpu->executor_type ?? '-' }}</div>
                                <div class="col-sm-3"><strong>Jenis:</strong><br>{{ $fpu->type ?? '-' }}</div>
                                <div class="col-sm-3"><strong>Kategori:</strong><br>{{ $fpu->category ?? '-' }}</div>
                            </div>
                            <h5 class="text-primary mb-3"><i class="fas fa-boxes"></i> Daftar Material</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="min-width: 140px;">Nama Material</th>
                                            <th style="min-width: 140px;">Spesifikasi</th>
                                            <th style="min-width: 80px;">Qty</th>
                                            <th style="min-width: 80px;">Satuan</th>
                                            <th style="min-width: 140px;">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($fpu->materials as $mat)
                                            <tr>
                                                <td>{{ $mat->material_name }}</td>
                                                <td>{{ $mat->specification }}</td>
                                                <td>{{ $mat->qty }}</td>
                                                <td>{{ $mat->unit }}</td>
                                                <td>{{ $mat->notes }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">Belum ada material</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval Panel -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-success"><i class="fas fa-check-circle"></i> Status & Approval</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h5 class="font-weight-bold">Status Saat Ini:</h5>
                            <span class="badge badge-warning text-lg px-3 py-2 rounded-pill">{{ $fpu->status_approval }}</span>
                        </div>

                        <div class="timeline timeline-inverse">
                            <!-- Approval Admin (Single Step) -->
                            <div>
                                <i class="fas fa-check-circle bg-{{ $fpu->approved_by_gm ? 'success' : 'secondary' }}"></i>
                                <div class="timeline-item border-0 shadow-none bg-light rounded-4">
                                    <h3 class="timeline-header border-0 font-weight-bold text-sm">Persetujuan Admin</h3>
                                    <div class="timeline-body p-2 text-sm">
                                        @if($fpu->approved_by_gm)
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-check text-success mr-2"></i>
                                                <span>Disetujui oleh: <strong>{{ $fpu->approvedByGm->name ?? '-' }}</strong></span>
                                            </div>
                                            <small class="text-muted"><i class="fas fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($fpu->approved_by_gm_at)->translatedFormat('d F Y H:i') }}</small>
                                        @else
                                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin')
                                                <p class="text-muted mb-3">Klik tombol di bawah untuk menyetujui FPU ini dan melanjutkan pekerjaan.</p>
                                                <form action="{{ route('fpu.approve-all', $fpu->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-block rounded-pill shadow-sm">
                                                        <i class="fas fa-check-double mr-1"></i> Approve FPU (Selesai)
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">Menunggu persetujuan Admin/HRGA.</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rowCount = {{ count($fpu->materials) > 0 ? count($fpu->materials) : 1 }};
        const tbody = document.querySelector('#material-table tbody');
        
        document.getElementById('add-row').addEventListener('click', function () {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="materials[${rowCount}][name]" class="form-control form-control-sm" required></td>
                <td><input type="text" name="materials[${rowCount}][spec]" class="form-control form-control-sm"></td>
                <td><input type="number" name="materials[${rowCount}][qty]" class="form-control form-control-sm" value="1" required></td>
                <td><input type="text" name="materials[${rowCount}][unit]" class="form-control form-control-sm" value="Pcs"></td>
                <td><input type="text" name="materials[${rowCount}][notes]" class="form-control form-control-sm"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
            rowCount++;
        });

        tbody.addEventListener('click', function (e) {
            if (e.target.closest('.remove-row')) {
                if (tbody.children.length > 1) {
                    e.target.closest('tr').remove();
                } else {
                    alert('Minimal harus ada 1 material.');
                }
            }
        });
    });
</script>
@endpush
@endsection
