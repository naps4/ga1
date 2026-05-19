@extends('layouts.print')

@section('title', 'Berita Acara - WO-' . str_pad($handover->workOrder->id, 4, '0', STR_PAD_LEFT))

@section('custom_header')
    <!-- Header Handover Custom -->
    <table class="wo-table" style="width: 100%; margin-bottom: 10px; table-layout: fixed; border-collapse: collapse;">
        <tr>
            <td class="wo-table-logo" style="width: 15%; padding: 2px; border: 1px solid #000; text-align: center; vertical-align: middle;">
                @if(\App\Helpers\SettingsHelper::get('app_logo'))
                    <img src="{{ asset('storage/' . \App\Helpers\SettingsHelper::get('app_logo')) }}" alt="Logo" style="width: 100%; max-height: 55px; object-fit: contain;">
                @else
                    <div style="color: #7cb342; font-weight: 900; font-size: 14px;">{{ \App\Helpers\SettingsHelper::get('company_name', 'HERBATECH') }}</div>
                @endif
            </td>
            <td class="text-center" style="width: 55%; vertical-align: middle; border: 1px solid #000;">
                <h5 style="margin: 0; font-weight: bold; font-size: 15px; text-transform: uppercase; letter-spacing: 1px;">BERITA ACARA SERAH TERIMA</h5>
            </td>
            <td style="width: 30%; text-align: left; vertical-align: middle; font-size: 11px; padding-left: 10px; border: 1px solid #000;">
                No. Doc. CM-03/GA/012D.00
            </td>
        </tr>
    </table>
@endsection

@section('content')
<style>
    body, .page-a4 { font-family: 'Arial', sans-serif !important; color: #000; }
    .table-print { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .table-print th, .table-print td { border: 1px solid #000; padding: 8px; font-size: 13px; }
    
    /* Comparison Styles */
    .comparison-section {
        width: 100%;
        margin-top: 20px;
        margin-bottom: 20px;
        page-break-inside: avoid;
    }
    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        table-layout: fixed;
    }
    .comparison-table th, .comparison-table td {
        border: 1px solid #000;
        padding: 10px;
        vertical-align: top;
        width: 50%;
    }
    .comparison-header {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: center;
        font-size: 12px;
    }
    .comparison-photo-container {
        text-align: center;
        padding: 5px;
        background-color: #fafafa;
        border: 1px dashed #ccc;
        margin-bottom: 10px;
        height: 180px;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }
    .comparison-photo {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .comparison-desc {
        font-size: 12px;
        line-height: 1.5;
        text-align: justify;
    }
    .no-photo-text {
        font-style: italic;
        color: #888;
        font-size: 12px;
    }
</style>
    <h4 class="doc-title">BERITA ACARA PENYELESAIAN PEKERJAAN</h4>
    <p class="doc-number">Terkait Tiket No: WO-{{ str_pad($handover->workOrder->id, 4, '0', STR_PAD_LEFT) }}</p>

    <div class="mt-4 mb-4" style="text-align: justify; line-height: 1.6;">
        <p>Pada hari ini, tanggal <strong>{{ $handover->created_at->translatedFormat('d F Y') }}</strong>, telah dilakukan penyelesaian dan serah terima hasil pekerjaan dengan rincian sebagai berikut:</p>
    </div>

    <table class="table table-bordered table-print mb-4">
        <tr>
            <th width="30%">Jenis Keluhan / Pekerjaan</th>
            <td>{!! nl2br(e($handover->workOrder->description)) !!}</td>
        </tr>
        <tr>
            <th>Hasil Pengecekan Teknisi</th>
            <td>{!! nl2br(e($handover->workOrder->check_result ?? '-')) !!}</td>
        </tr>
        @if($handover->workOrder->engineer_needs || ($handover->workOrder->fpuRequest && $handover->workOrder->fpuRequest->materials->count() > 0))
        <tr>
            <th>Kebutuhan Material</th>
            <td>
                @if($handover->workOrder->engineer_needs)
                    <div style="margin-bottom: 5px;">{!! nl2br(e($handover->workOrder->engineer_needs)) !!}</div>
                @endif
                
                @if($handover->workOrder->fpuRequest && $handover->workOrder->fpuRequest->materials->count() > 0)
                    <ul style="margin-top: 5px; padding-left: 15px; margin-bottom: 0;">
                        @foreach($handover->workOrder->fpuRequest->materials as $mat)
                            <li>{{ $mat->material_name }} {{ $mat->specification ? '('.$mat->specification.')' : '' }} - {{ $mat->qty }} {{ $mat->unit }}</li>
                        @endforeach
                    </ul>
                @endif
            </td>
        </tr>
        @endif
        @if($handover->workOrder->progresses && $handover->workOrder->progresses->count() > 0)
        <tr>
            <th>Log Progres Pekerjaan</th>
            <td>
                <ul style="margin-top: 5px; padding-left: 15px; margin-bottom: 0;">
                    @foreach($handover->workOrder->progresses as $progress)
                        <li style="margin-bottom: 5px;">
                            <strong>{{ $progress->created_at->format('d M Y H:i') }}:</strong><br>
                            {!! nl2br(e($progress->description)) !!}
                        </li>
                    @endforeach
                </ul>
            </td>
        </tr>
        @endif
        <tr>
            <th>Laporan Penyelesaian Teknisi</th>
            <td>{!! nl2br(e($handover->workOrder->engineer_report ?? '-')) !!}</td>
        </tr>
        <tr>
            <th>Status Serah Terima</th>
            <td>
                @if($handover->accepted_by)
                    <strong>Telah Diterima (Selesai)</strong>
                @else
                    <strong>Menunggu Konfirmasi Pemohon</strong>
                @endif
            </td>
        </tr>
        <tr>
            <th>Ulasan Pemohon</th>
            <td>{!! nl2br(e($handover->feedback ?? '-')) !!}</td>
        </tr>
        <tr>
            <th>Rating Kepuasan</th>
            <td>{{ $handover->rating ?? '-' }} @if($handover->rating)/ 5 @endif</td>
        </tr>
    </table>

    <div class="comparison-section">
        <h5 style="font-weight: bold; font-size: 13px; text-transform: uppercase; margin-bottom: 5px; border-bottom: 1px solid #000; padding-bottom: 3px;">
            DOKUMENTASI FOTO & KOMPARASI KERJA
        </h5>
        <table class="comparison-table">
            <thead>
                <tr>
                    <th class="comparison-header">SEBELUM (AWAL PELAPORAN)</th>
                    <th class="comparison-header">SESUDAH (LAPORAN PENYELESAIAN)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- Sebelum (Awal Pelaporan) -->
                    <td>
                        <div class="comparison-photo-container">
                            @if($handover->workOrder->attachment_request)
                                <img src="{{ asset($handover->workOrder->attachment_request) }}" class="comparison-photo" alt="Foto Awal Pelaporan">
                            @else
                                <span class="no-photo-text">Tidak ada lampiran foto awal</span>
                            @endif
                        </div>
                        <div class="comparison-desc">
                            <strong>Deskripsi Komplain:</strong><br>
                            {!! nl2br(e($handover->workOrder->description)) !!}
                        </div>
                    </td>
                    
                    <!-- Sesudah (Hasil Report) -->
                    <td>
                        <div class="comparison-photo-container">
                            @if($handover->workOrder->attachment_report)
                                <img src="{{ asset($handover->workOrder->attachment_report) }}" class="comparison-photo" alt="Foto Hasil Pekerjaan">
                            @else
                                <span class="no-photo-text">Tidak ada lampiran foto hasil</span>
                            @endif
                        </div>
                        <div class="comparison-desc">
                            <strong>Deskripsi Penyelesaian:</strong><br>
                            {!! nl2br(e($handover->workOrder->engineer_report ?? 'Tidak ada deskripsi laporan penyelesaian.')) !!}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4 mb-4" style="text-align: justify; line-height: 1.6;">
        <p>Dengan ditandatanganinya Berita Acara ini, pihak Pemohon menyatakan bahwa pekerjaan telah diselesaikan dengan baik oleh pihak Pelaksana (Teknisi) sesuai dengan permintaan pada tiket Work Order tersebut.</p>
    </div>

    <div class="signature-box clearfix">
        <div class="signature-col" style="width: 50%;">
            <p>Pihak Pelaksana / Yang Menyerahkan,</p>
            <div class="signature-space">
                <!-- Tanda Tangan Teknisi -->
            </div>
            <p class="signature-name">{{ $handover->workOrder->assignedEngineer->name ?? '.......................' }}</p>
            <small>Teknisi / Engineer</small>
        </div>
        <div class="signature-col" style="width: 50%;">
            <p>Pihak Pemohon / Yang Menerima,</p>
            <div class="signature-space">
                @if($handover->accepted_by)
                    <span style="display:inline-block; margin-top:30px; font-style:italic; font-size:12px; color:green;">Disetujui di Sistem</span>
                @endif
            </div>
            <p class="signature-name">{{ $handover->workOrder->user->name ?? '.......................' }}</p>
            <small>{{ $handover->workOrder->department ?? 'Departemen Pemohon' }}</small>
        </div>
    </div>
@endsection
