@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0 text-dark">Master Shift Kerja Kebersihan</h1>
            <div class="d-flex align-items-center" style="gap: .5rem;">
                <a href="{{ route('cleaning.shifts.template') }}" class="btn btn-outline-secondary rounded-pill shadow-sm">
                    <i class="fas fa-download mr-1"></i> Unduh Template
                </a>
                <button class="btn btn-outline-success rounded-pill shadow-sm" data-toggle="modal" data-target="#importShiftsModal">
                    <i class="fas fa-file-import mr-1"></i> Import Excel
                </button>
                <button class="btn text-white rounded-pill shadow-sm" style="background-color: #0d9488;" data-toggle="modal" data-target="#addShiftModal">
                    <i class="fas fa-plus mr-1"></i> Tambah Shift Baru
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

        <div class="row">
            <!-- Table list of Shifts -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-clock text-teal mr-1" style="color: #0d9488;"></i> Daftar Shift Aktif Kebersihan</h3>
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
                                                <form action="{{ route('cleaning.shifts.destroy', $shift->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus shift ini?')">
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
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data shift kerja kebersihan.</td>
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
                        <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-info-circle text-teal mr-1" style="color: #0d9488;"></i> Ketentuan Shift Kebersihan</h5>
                        <p class="small">Master shift ini digunakan untuk menentukan jadwal kerja petugas kebersihan secara otomatis pada saat scan QR Code area.</p>
                        <hr class="my-3 border-light">
                        <ul class="small pl-3 mb-0" style="line-height: 1.6;">
                            <li class="mb-2"><strong>Fleksibel & Presisi</strong>: Sesuaikan jam mulai dan selesai sesuai penugasan Cleaning Service.</li>
                            <li><strong>Import Massal</strong>: Gunakan fitur import Excel jika memiliki daftar shift kerja yang banyak.</li>
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
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-plus text-teal mr-1" style="color: #0d9488;"></i> Tambah Shift Kebersihan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cleaning.shifts.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Nama Shift <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-pill" placeholder="Contoh: Shift 1 - Pagi Kebersihan" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-600 text-dark">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" class="form-control rounded-pill" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-600 text-dark">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" class="form-control rounded-pill" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #0d9488;">Simpan Shift</button>
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
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-edit text-warning mr-1"></i> Edit Shift Kebersihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editShiftForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Nama Shift <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-pill" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-600 text-dark">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" id="edit_start_time" class="form-control rounded-pill" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-600 text-dark">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" id="edit_end_time" class="form-control rounded-pill" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 font-weight-bold">Update Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Shifts Modal -->
<div class="modal fade" id="importShiftsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-file-import text-success mr-1"></i> Import Shift Kebersihan via Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cleaning.shifts.import') }}" method="POST" enctype="multipart/form-data">
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

<script>
    function openEditModal(shift) {
        document.getElementById('editShiftForm').action = '/cleaning/shifts/' + shift.id;
        document.getElementById('edit_name').value = shift.name;
        document.getElementById('edit_start_time').value = shift.start_time;
        document.getElementById('edit_end_time').value = shift.end_time;
        $('#editShiftModal').modal('show');
    }
</script>
@endsection
