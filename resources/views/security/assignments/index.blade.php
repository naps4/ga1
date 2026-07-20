@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0 text-dark">Penjadwalan & Penugasan Security</h1>
            <button class="btn btn-primary rounded-pill shadow-sm" data-toggle="modal" data-target="#addAssignmentModal">
                <i class="fas fa-calendar-plus mr-1"></i> Buat Jadwal Baru
            </button>
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
                <form action="{{ route('security.assignments.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted uppercase">Filter Petugas</label>
                            <select name="user_id" class="form-control">
                                <option value="">Semua Security</option>
                                @foreach($guards as $guard)
                                    <option value="{{ $guard->id }}" {{ request('user_id') == $guard->id ? 'selected' : '' }}>{{ $guard->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted uppercase">Filter Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                        </div>
                        <div class="col-md-4 d-flex" style="gap: .5rem;">
                            <button type="submit" class="btn btn-info rounded-pill px-4 flex-fill shadow-none">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            @if(request()->anyFilled(['user_id', 'date']))
                                <a href="{{ route('security.assignments.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
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
                        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-calendar-alt text-info mr-1"></i> Jadwal Tugas Security</h3>
                        <span class="badge badge-light border ml-2">{{ $assignments->total() }} Jadwal Terdaftar</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: .88rem;">
                                <thead class="bg-light text-muted uppercase">
                                    <tr>
                                        <th class="py-3 text-center" style="width: 60px;">No</th>
                                        <th class="py-3">Petugas Security</th>
                                        <th class="py-3 text-center">Tanggal Tugas</th>
                                        <th class="py-3 text-center">Shift Kerja</th>
                                        <th class="py-3 text-center">Jam Tugas</th>
                                        <th class="py-3">Catatan / Keterangan</th>
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
                                                {{ $item->assigned_date->format('d M Y') }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary px-2.5 py-1 rounded">{{ $item->shift->name }}</span>
                                            </td>
                                            <td class="text-center text-muted small">
                                                {{ \Carbon\Carbon::parse($item->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->shift->end_time)->format('H:i') }}
                                            </td>
                                            <td>{{ $item->notes ?? '-' }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('security.assignments.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal penugasan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2.5">
                                                        <i class="fas fa-trash-alt mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada penugasan security yang terjadwal.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($assignments->hasPages())
                            <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
                                {{ $assignments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Side guidelines -->
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 bg-gradient-info text-white">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-3"><i class="fas fa-info-circle mr-1"></i> Aturan Penjadwalan</h5>
                        <p class="small mb-3">Sistem menerapkan validasi ketat agar jadwal tidak bertabrakan:</p>
                        <ol class="small pl-3 mb-0" style="line-height: 1.6;">
                            <li class="mb-2">Satu petugas security hanya bisa ditugaskan pada <strong>satu shift</strong> untuk satu tanggal yang sama.</li>
                            <li class="mb-2">Jika petugas login di halaman scan pada tanggal yang bersangkutan, sistem otomatis mengisi shift sesuai jadwal.</li>
                            <li>Jadwal masa lalu tetap disimpan sebagai arsip log historis.</li>
                        </ol>
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
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-calendar-plus text-primary mr-1"></i> Buat Jadwal Penugasan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('security.assignments.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Petugas Security <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Pilih Petugas...</option>
                            @foreach($guards as $guard)
                                <option value="{{ $guard->id }}" {{ old('user_id') == $guard->id ? 'selected' : '' }}>{{ $guard->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Shift Kerja <span class="text-danger">*</span></label>
                        <select name="shift_id" class="form-control" required>
                            <option value="">Pilih Shift...</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Tanggal Tugas <span class="text-danger">*</span></label>
                        <input type="date" name="assigned_date" class="form-control" value="{{ old('assigned_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark">Catatan Tambahan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Keterangan opsional">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save mr-1"></i> Simpan Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
