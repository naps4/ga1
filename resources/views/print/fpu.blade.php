@extends('layouts.print')

@section('title', 'FPU - ' . str_pad($fpu->id, 4, '0', STR_PAD_LEFT))

@section('custom_header')
    <!-- Header FPU Custom -->
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
                <h5 style="margin: 0; font-weight: bold; font-size: 15px; text-transform: uppercase; letter-spacing: 1px;">FORMULIR PELAYANAN UMUM</h5>
            </td>
            <td style="width: 30%; text-align: left; vertical-align: middle; font-size: 11px; padding-left: 10px; border: 1px solid #000;">
                No. Doc. CM-03/GA/002B.01
            </td>
        </tr>
    </table>
@endsection

@section('content')
<style>
    body, .page-a4 { font-family: 'Arial', sans-serif !important; color: #000; font-size: 12px; line-height: 1.2; }
    .fpu-box-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
    .fpu-box-table td { border: 1px solid #000; padding: 5px; height: 25px; vertical-align: middle; }
    .fpu-label { width: 120px; font-weight: normal; }
    
    .checkbox-container { display: flex; align-items: center; margin-right: 15px; font-size: 11px; }
    .checkbox-box { width: 14px; height: 14px; border: 1px solid #000; margin-right: 5px; display: inline-block; position: relative; vertical-align: middle; flex-shrink: 0; }
    .checkbox-box.checked::after { content: 'X'; position: absolute; top: -1px; left: 2px; font-size: 10px; font-weight: bold; }
    
    .section-title { font-weight: bold; text-decoration: underline; margin-bottom: 5px; font-size: 12px; margin-top: 15px; }
    
    .mat-table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
    .mat-table th, .mat-table td { border: 1px solid #000; padding: 4px; font-size: 11px; }
    .mat-table th { text-align: center; background-color: #f2f2f2; }
    
    .approval-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
    .approval-table td { border: 1px solid #000; text-align: center; vertical-align: bottom; height: 75px; padding-bottom: 5px; }
    .approval-header { height: 25px !important; vertical-align: middle !important; background-color: #f2f2f2; font-weight: bold; font-size: 11px; }
    
    .penyerahan-box { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
    .penyerahan-box td { border: 1px solid #000; padding: 5px; }
    
    .dotted-underline { border-bottom: 1px dotted #000; min-width: 100px; display: inline-block; }
</style>

<!-- Section 1: Info PPU -->
<table class="fpu-box-table">
    <tr>
        <td class="fpu-label">Tanggal PPU</td>
        <td>{{ $fpu->created_at->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td class="fpu-label">Nama PPU</td>
        <td>{{ $fpu->workOrder->user->name }} ({{ $fpu->workOrder->department }})</td>
    </tr>
    <tr>
        <td class="fpu-label">Spesifikasi</td>
        <td>{{ $fpu->workOrder->check_result ?? '-' }}</td>
    </tr>
    <tr>
        <td class="fpu-label" style="height: 50px;">Tujuan PPU</td>
        <td>{{ $fpu->workOrder->description }}</td>
    </tr>
</table>

<!-- Section 2: Checkboxes -->
<div style="margin-top: 5px; border: 1px solid #000; padding: 5px;">
    <table style="width: 100%; border-collapse: collapse;">
        <!-- Prioritas -->
        <tr>
            <td style="width: 100px; padding: 2px 0;">Prioritas</td>
            <td style="display: flex; padding: 2px 0; flex-wrap: wrap;">
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->priority == 'Sangat Penting' ? 'checked' : '' }}"></div> Sangat Penting</div>
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->priority == 'Penting' ? 'checked' : '' }}"></div> Penting</div>
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->priority == 'Biasa' ? 'checked' : '' }}"></div> Biasa</div>
            </td>
        </tr>
        <!-- Pelaksana -->
        <tr>
            <td style="padding: 2px 0;">Pelaksana</td>
            <td style="display: flex; padding: 2px 0; flex-wrap: wrap;">
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->executor_type == 'Internal' ? 'checked' : '' }}"></div> Internal</div>
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->executor_type == 'Eksternal' ? 'checked' : '' }}"></div> Eksternal</div>
            </td>
        </tr>
        <!-- Jenis -->
        <tr>
            <td style="padding: 2px 0;">Jenis</td>
            <td style="display: flex; padding: 2px 0; flex-wrap: wrap;">
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->type == 'Bangunan' ? 'checked' : '' }}"></div> Bangunan</div>
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->type == 'Kendaraan' ? 'checked' : '' }}"></div> Kendaraan</div>
                <div class="checkbox-container">
                    <div class="checkbox-box {{ (!in_array($fpu->type, ['Bangunan', 'Kendaraan']) && $fpu->type) ? 'checked' : '' }}"></div> 
                    Lainnya <span class="dotted-underline" style="margin-left: 5px;">{{ !in_array($fpu->type, ['Bangunan', 'Kendaraan']) ? $fpu->type : '' }}</span>
                </div>
            </td>
        </tr>
        <!-- Item -->
        <tr>
            <td style="padding: 2px 0;">Item</td>
            <td style="display: flex; padding: 2px 0; flex-wrap: wrap;">
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->category == 'Pembuatan' ? 'checked' : '' }}"></div> Pembuatan</div>
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->category == 'Perbaikan' ? 'checked' : '' }}"></div> Perbaikan</div>
                <div class="checkbox-container"><div class="checkbox-box {{ $fpu->category == 'Pengadaan' ? 'checked' : '' }}"></div> Pengadaan</div>
                <div class="checkbox-container">
                    <div class="checkbox-box {{ (!in_array($fpu->category, ['Pembuatan', 'Perbaikan', 'Pengadaan']) && $fpu->category) ? 'checked' : '' }}"></div> 
                    Lainnya <span class="dotted-underline" style="margin-left: 5px;">{{ !in_array($fpu->category, ['Pembuatan', 'Perbaikan', 'Pengadaan']) ? $fpu->category : '' }}</span>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- Section 3: Pemakaian Material -->
<div class="section-title">PEMAKAIAN MATERIAL</div>
<table class="mat-table">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="35%">Nama Material</th>
            <th width="20%">spesifikasi</th>
            <th width="8%">Qty</th>
            <th width="10%">Satuan</th>
            <th width="22%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @php $maxRows = 10; @endphp
        @foreach($fpu->materials as $index => $mat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $mat->material_name }}</td>
                <td>{{ $mat->specification ?? '-' }}</td>
                <td class="text-center">{{ $mat->qty }}</td>
                <td class="text-center">{{ $mat->unit }}</td>
                <td>{{ $mat->notes ?? '-' }}</td>
            </tr>
        @endforeach
        @for($i = count($fpu->materials); $i < $maxRows; $i++)
            <tr>
                <td style="height: 18px;"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
    </tbody>
</table>

<!-- Section 4: Approval 1 -->
<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
    <tr>
        <td style="width: 100%; padding: 0; vertical-align: top;">
            <table class="approval-table" style="margin-top: 0;">
                <tr>
                    <td class="approval-header">Diajukan oleh</td>
                    <td class="approval-header">Diketahui</td>
                    <td class="approval-header">Disetujui</td>
                </tr>
                <tr>
                    <td>
                        <div style="height: 35px;"></div>
                        <strong style="text-decoration: underline;">{{ $fpu->verifiedByHrga->name ?? (auth()->user()->name ?? '.......................') }}</strong><br>
                        <span style="font-size: 10px;">Supervisor General Affair</span>
                    </td>
                    <td>
                        <div style="height: 35px;"></div>
                        <strong style="text-decoration: underline;">{{ $fpu->knownByManager->name ?? '.......................' }}</strong><br>
                        <span style="font-size: 10px;">Oprt. Manager</span>
                    </td>
                    <td>
                        <div style="height: 35px;"></div>
                        <strong style="text-decoration: underline;">{{ $fpu->approvedByGm->name ?? '.......................' }}</strong><br>
                        <span style="font-size: 10px;">Direktur/GM</span>
                    </td>
                </tr>
            </table>
            <div style="font-size: 9px; font-style: italic; margin-top: 3px;">
                Persetujuan direktur hanya untuk FPU uang, melibatkan pihak Eksternal
            </div>
        </td>
    </tr>
</table>

<!-- Section 5: Penyerahan Hasil -->
<div class="section-title">PENYERAHAN HASIL</div>
@php
    $handover = $fpu->workOrder->handover;
@endphp
<table class="penyerahan-box">
    <tr>
        <td style="width: 40%; font-size: 11px;">Nama Pelayanan: {{ $handover ? $fpu->workOrder->description : '' }}</td>
        <td style="width: 30%; font-size: 11px;">Jumlah: </td>
        <td style="width: 30%; font-size: 11px;">Satuan: </td>
    </tr>
    <tr>
        <td colspan="3" style="font-size: 11px;">
            Spesifikasi: 
            <div style="height: 40px; margin-top: 5px; padding: 5px;">
                {{ $handover ? $fpu->workOrder->engineer_report : '' }}
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="font-size: 11px;">
            Catatan: 
            <div style="height: 25px; margin-top: 5px; padding: 5px;">
                {{ $handover ? $handover->user_review : '' }}
            </div>
        </td>
    </tr>
</table>

<!-- Section 6: Info Penyerahan & Approval 2 -->
<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
    <tr>
        <td style="width: 35%; border: 1px solid #000; padding: 5px; vertical-align: top;">
            <span style="font-size: 11px;">Tanggal penyerahan Hasil :</span><br>
            <strong style="font-size: 12px;">{{ $handover ? $handover->created_at->format('d/m/Y') : '..../..../....' }}</strong>
            <div style="margin-top: 15px; border-top: 1px solid #000; padding-top: 5px;">
                <span style="font-size: 11px;">Keterangan :</span><br>
                <div style="height: 45px;"></div>
            </div>
        </td>
        <td style="width: 65%; padding-left: 10px; vertical-align: top;">
            <table class="approval-table" style="margin-top: 0;">
                <tr>
                    <td class="approval-header">Disiapkan</td>
                    <td class="approval-header">Diketahui</td>
                    <td class="approval-header">Diterima</td>
                </tr>
                <tr>
                    <td>
                        <div style="height: 40px;"></div>
                        <strong style="text-decoration: underline;">HRGA</strong>
                    </td>
                    <td>
                        <div style="height: 40px;"></div>
                        <strong style="text-decoration: underline;">Oprt. Manager</strong>
                    </td>
                    <td>
                        <div style="height: 40px;"></div>
                        <strong style="text-decoration: underline;">.......................</strong>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div style="margin-top: 10px; font-size: 10px; font-weight: bold;">
    LAMP. B PR-03/GA/002B.01
</div>

@endsection
