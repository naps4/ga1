@extends('layouts.print')

@section('title', 'Berita Acara - WO-' . str_pad($handover->workOrder->id, 4, '0', STR_PAD_LEFT))

@section('content')
    <h4 class="doc-title">BERITA ACARA PENYELESAIAN PEKERJAAN (FORM D)</h4>
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
            <th>Laporan Penyelesaian Teknisi</th>
            <td>{!! nl2br(e($handover->workOrder->engineer_report ?? '-')) !!}</td>
        </tr>
        <tr>
            <th>Status Serah Terima</th>
            <td>
                @if($handover->status === 'Diterima')
                    <strong>Telah Diterima & Selesai</strong>
                @else
                    {{ $handover->status }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Ulasan Pemohon</th>
            <td>{!! nl2br(e($handover->user_review ?? '-')) !!}</td>
        </tr>
        <tr>
            <th>Rating Kepuasan</th>
            <td>{{ $handover->rating ?? '-' }} / 5</td>
        </tr>
    </table>

    <div class="mt-4 mb-5" style="text-align: justify; line-height: 1.6;">
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
                @if($handover->status === 'Diterima')
                    <span style="display:inline-block; margin-top:30px; font-style:italic; font-size:12px; color:green;">Disetujui di Sistem</span>
                @endif
            </div>
            <p class="signature-name">{{ $handover->workOrder->user->name ?? '.......................' }}</p>
            <small>{{ $handover->workOrder->department ?? 'Departemen Pemohon' }}</small>
        </div>
    </div>
@endsection
