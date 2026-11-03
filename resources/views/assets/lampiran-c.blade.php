@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-clipboard-list text-success mr-2"></i> Form Daftar Aset Ruangan (Lampiran C)
                </h1>
                <small class="text-muted">Standar Dokumen Mutu: CM-03/GA/002.C.01 — Audit Inventaris Fisik Sub-Ruangan</small>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mr-1">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Master Aset
                </a>
                @if($selectedLocation)
                    <a href="{{ route('assets.lampiran-c.print-qr', $selectedLocation->id) }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm mr-1">
                        <i class="fas fa-qrcode mr-1"></i> Cetak Semua Stiker QR
                    </a>
                    <a href="{{ route('assets.lampiran-c.print', $selectedLocation->id) }}" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fas fa-print mr-1"></i> Cetak Dokumen Resmi
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="row">
            {{-- Daftar Ruangan / Selector --}}
            <div class="col-md-4 col-12 mb-3">
                <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Pilih Ruangan Audit</h6>
                    </div>
                    <div class="list-group list-group-flush overflow-auto" style="max-height: 600px;">
                        @foreach($locations as $loc)
                            <a href="{{ route('assets.lampiran-c', $loc->id) }}"
                               class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center {{ $selectedLocation && $selectedLocation->id == $loc->id ? 'active' : '' }}">
                                <div>
                                    <div class="font-weight-bold">{{ $loc->room_name }}</div>
                                    <small class="{{ $selectedLocation && $selectedLocation->id == $loc->id ? 'text-white-50' : 'text-muted' }}">
                                        {{ $loc->plant->name ?? 'Plant ' . $loc->plant_code }} • Dept: {{ $loc->department->code ?? '-' }}
                                    </small>
                                </div>
                                <span class="badge {{ $selectedLocation && $selectedLocation->id == $loc->id ? 'badge-light text-dark' : 'badge-primary' }} rounded-pill">
                                    {{ $loc->plant_code }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Form Preview --}}
            <div class="col-md-8 col-12 mb-3">
                <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                    @if($selectedLocation)
                        <div class="card-body p-4">
                            {{-- Header Dokumen Resmi --}}
                            <div class="border p-3 mb-4 rounded bg-white">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="font-weight-bold text-dark mb-1">FORM DAFTAR ASET</h4>
                                        <div class="text-muted small">PT Herbatech Innopharma Industry</div>
                                    </div>
                                    <div class="col-4 text-right">
                                        <table class="table-sm table-borderless text-left ml-auto font-weight-bold small" style="width: auto;">
                                            <tr><td>No. doc</td><td>:</td><td>CM-03/GA/002.C.01</td></tr>
                                            <tr><td>Revisi</td><td>:</td><td>-</td></tr>
                                            <tr><td>Tgl Berlaku</td><td>:</td><td>13 April 2026</td></tr>
                                        </table>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row small mt-2">
                                    <div class="col-6">
                                        <strong>Lokasi :</strong> <span class="badge badge-light border px-2 py-1 font-weight-bold">{{ $selectedLocation->room_name }} ({{ $selectedLocation->plant->name ?? 'Plant ' . $selectedLocation->plant_code }})</span>
                                    </div>
                                    <div class="col-6 text-right">
                                        <strong>Tanggal :</strong> {{ $lastUpdated }} (Last Update)
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <div class="small text-muted">
                                        <i class="fas fa-boxes text-primary mr-1"></i> Form ini mengagregasi aset fisik di <strong>{{ $selectedLocation->room_name }}</strong>.
                                    </div>
                                    <a href="{{ route('assets.lampiran-c.print-qr', $selectedLocation->id) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3 font-weight-bold">
                                        <i class="fas fa-qrcode mr-1"></i> Cetak Semua Label QR Ruangan Ini
                                    </a>
                                </div>
                            </div>

                            {{-- Tabel Agregasi Lampiran C --}}
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-center mb-0" style="font-size: .9rem;">
                                    <thead class="bg-light font-weight-bold">
                                        <tr>
                                            <th style="width: 50px;">NO</th>
                                            <th class="text-left">JENIS ASET</th>
                                            <th style="width: 100px;">KODE</th>
                                            <th style="width: 120px;">JUMLAH</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($aggregatedItems as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-left font-weight-bold">{{ $item->jenis_aset }}</td>
                                                <td><span class="badge badge-secondary">{{ $item->kode }}</span></td>
                                                <td class="font-weight-bold text-primary">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-4 text-muted">
                                                    Belum ada aset terpetakan di ruangan {{ $selectedLocation->room_name }}.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if(count($aggregatedItems) > 0)
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td colspan="3" class="text-right">TOTAL FISIK UNIT:</td>
                                                <td class="text-primary">{{ number_format($aggregatedItems->sum('jumlah'), 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>

                            <div class="mt-3 text-muted small font-italic">
                                LAMP. C PR-03/GA/002C.01 — Formulir Inventaris Terverifikasi General Affairs
                            </div>
                        </div>
                    @else
                        <div class="card-body p-5 text-center text-muted">
                            <i class="fas fa-building fa-3x mb-3 text-secondary"></i>
                            <p>Silakan pilih salah satu ruangan di panel kiri untuk menampilkan Form Daftar Aset.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

