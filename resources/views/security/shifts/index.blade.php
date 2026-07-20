@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0 text-dark">Master Shift Kerja Security</h1>
            <button class="btn btn-primary rounded-pill shadow-sm" data-toggle="modal" data-target="#addShiftModal">
                <i class="fas fa-plus mr-1"></i> Tambah Shift Baru
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

        <div class="row">
            <!-- Table list of Shifts -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-business-time text-info mr-1"></i> Daftar Shift Aktif</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: .9rem;">
                                <thead class="bg-light text-muted uppercase">
                                    <tr>
                                        <th class="py-3 text-center" style="width: 80px;">No</th>
                                        <th class="py-3">Nama Shift</th>
                                        <th class="py-3 text-center">Jam Mulai</th>
                                        <th class="py-3 text-center">Jam Selesai</th>
                                        <th class="py-3 text-center" style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($shifts as $index => $shift)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                            <td><strong>{{ $shift->name }}</strong></td>
                                            <td class="text-center"><span class="badge badge-success px-3 py-1.5 rounded-pill">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</span></td>
                                            <td class="text-center"><span class="badge badge-danger px-3 py-1.5 rounded-pill">{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</span></td>
                                            <td class="text-center">
                                                <button class="btn btn-xs btn-outline-warning rounded-pill px-2.5 mr-1" onclick="openEditModal({{ json_encode($shift) }})">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </button>
                                                <form action="{{ route('security.shifts.destroy', $shift->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus shift ini?')">
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
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data shift kerja security.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick info panel -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4 bg-gradient-light">
                    <div class="card-body p-4 text-secondary">
                        <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-info-circle text-info mr-1"></i> Ketentuan Shift</h5>
                        <p class="small">Master shift ini akan digunakan untuk mendeteksi waktu tugas patroli security secara otomatis pada halaman mobile scanner.</p>
                        <hr class="my-3 border-light">
                        <ul class="small pl-3 mb-0" style="line-height: 1.6;">
                            <li class="mb-2"><strong>Cross-day Shift</strong>: Shift malam yang melewati jam 00:00 (misal: 23:00 - 07:00) didukung secara penuh oleh sistem.</li>
                            <li><strong>Jadwal Tugas</strong>: Setiap shift dapat dikaitkan dengan petugas keamanan pada menu Penjadwalan Security.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Shift Modal -->
<div class="modal fade" id="addShiftModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-plus text-primary mr-1"></i> Tambah Shift Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('security.shifts.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Nama Shift <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Shift 1 - Pagi" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-600 text-dark">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-600 text-dark">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save mr-1"></i> Simpan Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Shift Modal -->
<div class="modal fade" id="editShiftModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-edit text-warning mr-1"></i> Edit Shift Kerja</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editShiftForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Nama Shift <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-600 text-dark">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-600 text-dark">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 text-white">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(shift) {
        $('#editShiftForm').attr('action', `/security/shifts/${shift.id}`);
        $('#edit_name').val(shift.name);
        
        // Memformat format time (H:i:s -> H:i)
        const formatTime = (timeStr) => {
            if (!timeStr) return '';
            return timeStr.substring(0, 5);
        };

        $('#edit_start_time').val(formatTime(shift.start_time));
        $('#edit_end_time').val(formatTime(shift.end_time));
        
        $('#editShiftModal').modal('show');
    }
</script>
@endpush
