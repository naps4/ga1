@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0 text-dark">Penjadwalan & Penugasan Kebersihan</h1>
            <div class="d-flex align-items-center" style="gap: .5rem;">
                <a href="{{ route('cleaning.assignments.template') }}" class="btn btn-outline-secondary rounded-pill shadow-sm">
                    <i class="fas fa-download mr-1"></i> Unduh Template
                </a>
                <button class="btn btn-outline-success rounded-pill shadow-sm" data-toggle="modal" data-target="#importAssignmentsModal">
                    <i class="fas fa-file-import mr-1"></i> Import Excel
                </button>
                <button class="btn text-white rounded-pill shadow-sm" style="background-color: #0d9488;" data-toggle="modal" data-target="#addAssignmentModal">
                    <i class="fas fa-calendar-plus mr-1"></i> Buat Jadwal Baru
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill shadow-sm mb-4" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <i class="fas fa-info-circle mr-1"></i> {!! session('info') !!}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-pill shadow-sm mb-4" role="alert">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Filter bar -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('cleaning.assignments.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted uppercase">Filter Petugas Kebersihan</label>
                            <select name="user_id" class="form-control border-light">
                                <option value="">Semua Petugas Kebersihan</option>
                                @foreach($cleaners as $cleaner)
                                    <option value="{{ $cleaner->id }}" {{ request('user_id') == $cleaner->id ? 'selected' : '' }}>{{ $cleaner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted uppercase">Filter Tanggal</label>
                            <input type="date" name="date" class="form-control border-light" value="{{ request('date') }}">
                        </div>
                        <div class="col-md-4 d-flex" style="gap: .5rem;">
                            <button type="submit" class="btn text-white rounded-pill px-4 flex-fill shadow-none" style="background-color: #0d9488;">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            @if(request()->anyFilled(['user_id', 'date']))
                                <a href="{{ route('cleaning.assignments.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Table list of Assignments -->
            <div class="col-md-9">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-calendar-alt text-teal mr-1" style="color: #0d9488;"></i> Jadwal Tugas Kebersihan</h3>
                        <span class="badge badge-light border ml-2">{{ $assignments->total() }} Jadwal Terdaftar</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: .88rem;">
                                <thead class="bg-light text-muted uppercase">
                                    <tr>
                                        <th class="py-3 text-center" style="width: 60px;">No</th>
                                        <th class="py-3">Petugas Kebersihan</th>
                                        <th class="py-3 text-center">Tanggal Tugas</th>
                                        <th class="py-3 text-center">Shift Kerja</th>
                                        <th class="py-3">Penempatan Area</th>
                                        <th class="py-3 text-center">Jam Tugas</th>
                                        <th class="py-3">Catatan</th>
                                        <th class="py-3 text-center" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assignments as $index => $item)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $assignments->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $item->user->avatar_url }}" alt="avatar" class="rounded-circle mr-2" style="width: 28px; height: 28px; object-fit: cover;">
                                                    <strong>{{ $item->user->name ?? '-' }}</strong>
                                                </div>
                                            </td>
                                            <td class="text-center font-weight-bold">
                                                @if($item->assigned_date)
                                                    {{ $item->assigned_date->format('d M Y') }}
                                                @else
                                                    <span class="badge badge-secondary px-2.5 py-1 rounded">Permanen</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item->shift)
                                                    <span class="badge text-white px-2.5 py-1 rounded" style="background-color: #0d9488;">{{ $item->shift->name }}</span>
                                                @else
                                                    <span class="text-muted small">Fleksibel</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->plant)
                                                    <span class="badge badge-success rounded-pill px-2">
                                                        <i class="fas fa-building mr-1"></i>{{ $item->plant }}
                                                    </span>
                                                @else
                                                    <span class="text-muted small">Semua Area</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-muted small">
                                                @if($item->shift)
                                                    {{ \Carbon\Carbon::parse($item->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->shift->end_time)->format('H:i') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $item->notes ?? '-' }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('cleaning.assignments.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal penugasan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2.5">
                                                        <i class="fas fa-trash mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">Belum ada penugasan kebersihan terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($assignments->hasPages())
                        <div class="card-footer bg-white border-top border-light d-flex justify-content-end py-3">
                            {{ $assignments->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick info panel -->
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 bg-gradient-light">
                    <div class="card-body p-4 text-secondary">
                        <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-info-circle text-teal mr-1" style="color:#0d9488;"></i> Informasi Penugasan</h5>
                        <p class="small">Sistem mengutamakan **Jadwal Harian Khusus** (berdasarkan tanggal). Jika tidak ada, sistem akan menggunakan **Penempatan Permanen** (tanpa tanggal).</p>
                        <hr class="my-3 border-light">
                        <ul class="small pl-3 mb-0" style="line-height: 1.6;">
                            <li class="mb-2"><strong>Penempatan Area</strong>: Petugas hanya melihat QR Code area kebersihan sesuai lokasi plant penugasannya.</li>
                            <li><strong>Auto Shift</strong>: Apabila shift tidak dipilih, sistem menyesuaikan secara otomatis berdasarkan jam saat melakukan scan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Assignment Modal -->
<div class="modal fade" id="addAssignmentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-calendar-plus text-teal mr-1" style="color:#0d9488;"></i> Tambah Penugasan Kebersihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cleaning.assignments.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Petugas Kebersihan <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control rounded-pill" required>
                            <option value="">-- Pilih Petugas Kebersihan --</option>
                            @foreach($cleaners as $cleaner)
                                <option value="{{ $cleaner->id }}">{{ $cleaner->name }} ({{ $cleaner->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-600 text-dark">Shift Kerja <small class="text-muted">(Opsional)</small></label>
                                <select name="shift_id" class="form-control rounded-pill">
                                    <option value="">-- Otomatis berdasarkan jam --</option>
                                    @foreach($shifts as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }} ({{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-600 text-dark">Penempatan Plant <small class="text-muted">(Opsional)</small></label>
                                <input type="text" name="plant" class="form-control rounded-pill" list="plantsData" placeholder="Contoh: Plant A">
                                <datalist id="plantsData">
                                    @foreach($plantsList as $pl)
                                        <option value="{{ $pl }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Tanggal Penugasan <small class="text-muted">(Kosongkan untuk penempatan permanen)</small></label>
                        <input type="date" name="assigned_date" class="form-control rounded-pill">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark">Catatan / Instruksi Khusus</label>
                        <textarea name="notes" class="form-control rounded-4" rows="2" placeholder="Instruksi khusus pembersihan area..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #0d9488;">Simpan Penugasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Assignments Modal -->
<div class="modal fade" id="importAssignmentsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-file-import text-success mr-1"></i> Import Penjadwalan Kebersihan via Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cleaning.assignments.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Upload File Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control-file" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 font-weight-bold">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
