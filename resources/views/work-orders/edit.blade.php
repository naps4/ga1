@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0">Edit Work Order #WO-{{ str_pad($workOrder->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom border-light">
                        <h3 class="card-title text-primary"><i class="fas fa-edit"></i> Form Edit Tiket (Form A)</h3>
                    </div>
                    <form action="{{ route('work-orders.update', $workOrder->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label text-muted">Dari (Pemohon)</label>
                                <input type="text" class="form-control bg-light border-0 rounded-pill" value="{{ $workOrder->user->name }}" readonly>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label text-muted">Departemen / Company</label>
                                <input type="text" class="form-control bg-light border-0 rounded-pill" value="{{ $workOrder->department }}" readonly>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label">Deskripsi Kendala / Pengajuan <span class="text-danger">*</span></label>
                                <textarea name="description" rows="5" class="form-control rounded-4 @error('description') is-invalid @enderror" placeholder="Jelaskan secara detail masalah atau layanan yang Anda butuhkan..." required>{{ old('description', $workOrder->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label">Lampiran Foto/Dokumen <span class="text-muted">(Opsional)</span></label>
                                @if($workOrder->attachment_request)
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/' . $workOrder->attachment_request) }}" class="btn btn-sm btn-info rounded-pill"><i class="fas fa-paperclip"></i> Lihat Lampiran Saat Ini</a>
                                        <small class="text-muted ml-2">Upload file baru untuk menggantikan lampiran lama.</small>
                                    </div>
                                @endif
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf">
                                    <label class="custom-file-label" for="attachment">Pilih file baru...</label>
                                </div>
                                <small class="form-text text-muted">Format yang didukung: JPG, PNG, PDF. Maksimal 5MB.</small>
                                @error('attachment')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top border-light text-right">
                            <button type="submit" class="btn btn-primary rounded-pill shadow-sm px-4">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
