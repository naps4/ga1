@extends('layouts.print')

@section('title', 'Work Order - WO-' . str_pad($workOrder->id, 4, '0', STR_PAD_LEFT))

@section('custom_header')
    <!-- Custom header within the page content -->
@endsection

@section('content')
<style>
    body, .page-a4 { font-family: 'Arial', sans-serif !important; color: #000; }
    .wo-table { width: 100%; border: 3px solid #000; border-collapse: collapse; margin-bottom: 25px; table-layout: fixed; }
    .wo-table td { border: 2px solid #000; padding: 10px; }
    .wo-table-logo { width: 15%; text-align: center; vertical-align: middle; }
    .wo-table-title { width: 60%; text-align: center; vertical-align: middle; }
    .wo-table-doc { width: 25%; text-align: center; font-weight: bold; font-size: 13px; vertical-align: middle; }
    
    .wo-body { width: 100%; font-size: 13px; margin-bottom: 15px; border: none; table-layout: fixed; }
    .wo-body td { padding: 4px 0; vertical-align: top; border: none; }
    .wo-body td:first-child { width: 150px; } /* Diperkecil sedikit agar kolom konten lebih luas */
    .wo-body td:nth-child(2) { width: 15px; text-align: left; }
    
    /* Using repeating linear gradient for dotted lines to match image */
    .dotted-line-fill {
        width: 100%;
        height: 22px;
        border-bottom: 1.5px dotted #000;
        margin-bottom: 4px;
    }
    
    .signature-row { width: 100%; margin-top: 30px; display: table; }
    .signature-col { display: table-cell; width: 50%; font-size: 13px; }
    .signature-col.left { text-align: left; padding-left: 0; }
    .signature-col.right { text-align: right; padding-right: 30px; }
    .signature-space { height: 70px; }
    
    .thick-divider { 
        width: 100%;
        border-top: 4px solid #666; 
        border-bottom: 1.5px solid #666; 
        height: 3px; 
        margin: 35px 0; 
    }
    
    .logo-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .logo-icon {
        width: 25px;
        height: 35px;
        background-color: #558b2f; /* Approximate leaf green */
        clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
    }
    .logo-text {
        color: #7cb342;
        font-weight: 900;
        letter-spacing: 1px;
        font-size: 16px;
        margin: 0;
    }
</style>

<!-- FORM A -->
<table class="wo-table">
    <tr>
        <td class="wo-table-logo" style="width: 15%; padding: 2px; border: 1px solid #000; text-align: center; vertical-align: middle;">
            @if(\App\Helpers\SettingsHelper::getReportLogo())
                <img src="{{ asset('storage/' . \App\Helpers\SettingsHelper::getReportLogo()) }}" alt="Logo" style="width: 100%; max-height: 55px; object-fit: contain;">
            @else
                <div class="logo-container">
                    <h2 class="logo-text">{{ \App\Helpers\SettingsHelper::get('company_name', 'HERBATECH') }}</h2>
                </div>
            @endif
        </td>
        <td class="wo-table-title">
            <h5 style="margin: 0 0 10px 0; font-weight: bold; font-size: 14px;">{{ \App\Helpers\SettingsHelper::get('company_name', 'PT. HERBATECH INNOPHARMA INDUSTRY') }}</h5>
            <h5 style="margin: 0; font-weight: bold; font-size: 14px;">WORK ORDER</h5>
        </td>
        <td class="wo-table-doc">
            CM-03/GA/011C.00
        </td>
    </tr>
</table>

<table class="wo-body">
    <tr>
        <td>Kepada</td>
        <td>:</td>
        <td>{{ $workOrder->assignedEngineer->name ?? '' }}</td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td>{{ $workOrder->created_at->format('d M Y') }}</td>
    </tr>
    <tr>
        <td>Dari</td>
        <td>:</td>
        <td>{{ $workOrder->user->name ?? '' }}</td>
    </tr>
    <tr>
        <td>Department</td>
        <td>:</td>
        <td>{{ $workOrder->department ?? '' }}</td>
    </tr>
    <tr>
        <td>Mengajukan</td>
        <td>:</td>
        <td>
            @if($workOrder->description)
                <div style="margin-bottom: 5px; line-height: 1.5;">{!! nl2br(e($workOrder->description)) !!}</div>
            @else
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
            @endif
        </td>
    </tr>
</table>

<div class="signature-row">
    <div class="signature-col left">
        Diajukan oleh
        <div class="signature-space"></div>
        <div style="border-bottom: 1.5px dotted #000; width: 220px; margin-bottom: 5px;"></div>
    </div>
    <div class="signature-col right">
        Penerima Tugas
        <div class="signature-space"></div>
        <strong style="margin-right: 25px;">TEKNISI</strong>
    </div>
</div>

<div class="thick-divider"></div>

<!-- FORM B -->
<table class="wo-table">
    <tr>
        <td class="wo-table-logo" style="width: 15%; padding: 2px; border: 1px solid #000; text-align: center; vertical-align: middle;">
            @if(\App\Helpers\SettingsHelper::get('app_logo'))
                <img src="{{ asset('storage/' . \App\Helpers\SettingsHelper::get('app_logo')) }}" alt="Logo" style="width: 100%; max-height: 55px; object-fit: contain;">
            @else
                <div class="logo-container">
                    <h2 class="logo-text">{{ \App\Helpers\SettingsHelper::get('company_name', 'HERBATECH') }}</h2>
                </div>
            @endif
        </td>
        <td class="wo-table-title">
            <h5 style="margin: 0 0 10px 0; font-weight: bold; font-size: 14px;">{{ \App\Helpers\SettingsHelper::get('company_name', 'PT. HERBATECH INNOPHARMA INDUSTRY') }}</h5>
            <h5 style="margin: 0; font-weight: bold; font-size: 14px;">WORK ORDER</h5>
        </td>
        <td class="wo-table-doc">
            CM-03/GA/011C.00
        </td>
    </tr>
</table>

<table class="wo-body">
    <tr>
        <td>Hasil Pengecekan Engineer</td>
        <td>:</td>
        <td>
            @if($workOrder->check_result)
                <div style="margin-bottom: 5px; line-height: 1.5;">{!! nl2br(e($workOrder->check_result)) !!}</div>
            @else
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
            @endif
        </td>
    </tr>
    @if($workOrder->engineer_needs || ($workOrder->fpuRequest && $workOrder->fpuRequest->materials->count() > 0))
    <tr>
        <td>Kebutuhan Engineer</td>
        <td>:</td>
        <td>
            @if($workOrder->engineer_needs)
                <div style="margin-bottom: 5px; line-height: 1.5;">{!! nl2br(e($workOrder->engineer_needs)) !!}</div>
            @endif

            @if($workOrder->fpuRequest && $workOrder->fpuRequest->materials->count() > 0)
                <div style="margin-top: {{ $workOrder->engineer_needs ? '10px' : '0' }};">
                    <strong>Item Pengadaan:</strong>
                    <ul style="margin-top: 5px; padding-left: 20px; margin-bottom: 0;">
                        @foreach($workOrder->fpuRequest->materials as $mat)
                            <li>{{ $mat->material_name }} {{ $mat->specification ? '('.$mat->specification.')' : '' }} - {{ $mat->qty }} {{ $mat->unit }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </td>
    </tr>
    @endif
    @if($workOrder->progresses && $workOrder->progresses->count() > 0)
    <tr>
        <td>Log Progres Pekerjaan</td>
        <td>:</td>
        <td>
            <ul style="margin-top: 5px; padding-left: 20px; margin-bottom: 5px; line-height: 1.5;">
                @foreach($workOrder->progresses as $progress)
                    <li>
                        <strong>{{ $progress->created_at->format('d M Y H:i') }}</strong><br>
                        {!! nl2br(e($progress->description)) !!}
                    </li>
                @endforeach
            </ul>
        </td>
    </tr>
    @endif
    <tr>
        <td>Reporting Engineer</td>
        <td>:</td>
        <td>
            @if($workOrder->engineer_report)
                <div style="margin-bottom: 5px; margin-top: 10px; line-height: 1.5;">{!! nl2br(e($workOrder->engineer_report)) !!}</div>
            @else
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
                <div class="dotted-line-fill"></div>
            @endif
        </td>
    </tr>
</table>

<div class="signature-row" style="margin-top: 30px;">
    <div class="signature-col left">
        Yang Menjalankan Tugas
        <div class="signature-space"></div>
        <strong>TEKNISI</strong>
    </div>
    <div class="signature-col right">
        Pengontrol Tugas
        <div class="signature-space"></div>
        <strong>HRGA SUPERVISOR</strong>
    </div>
</div>

@endsection
