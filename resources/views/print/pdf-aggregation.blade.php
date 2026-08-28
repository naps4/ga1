<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Total Kebutuhan Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            color: #1a1a1a;
        }
        .header-meta {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }
        .filter-summary {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .filter-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .filter-summary td {
            padding: 2px 5px;
            font-size: 10px;
        }
        .section-header {
            margin-top: 20px;
            margin-bottom: 8px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .section-atk {
            background-color: #1d4ed8;
            color: #ffffff;
        }
        .section-household {
            background-color: #d97706;
            color: #ffffff;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #dee2e6;
            padding: 7px 10px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f1f3f5;
            font-weight: bold;
            text-align: left;
            font-size: 10px;
        }
        .table-summary-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 class="header-title">Rekap Total Kebutuhan Barang</h1>
                    <div class="header-meta">
                        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} | Oleh: {{ auth()->user()->name }}
                    </div>
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    @php
                        $logo = \App\Helpers\SettingsHelper::getReportLogo();
                        $logoPath = $logo ? storage_path('app/public/' . $logo) : null;
                    @endphp
                    @if($logoPath && file_exists($logoPath))
                        <img src="{{ $logoPath }}" style="max-height: 35px; max-width: 150px; display: inline-block; margin-bottom: 4px; vertical-align: middle;">
                        <br>
                    @endif
                    <strong style="font-size: 12px; vertical-align: middle;">{{ \App\Helpers\SettingsHelper::get('company_name', 'HERBATECH') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- Filter Info -->
    <div class="filter-summary">
        <table>
            <tr>
                <td style="width: 15%;"><strong>Periode</strong></td>
                <td style="width: 35%;">: 
                    @if($request->filled('start_date') || $request->filled('end_date'))
                        {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Awal' }}
                        s.d
                        {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Akhir' }}
                    @else
                        Semua Periode
                    @endif
                </td>
                <td style="width: 15%;"><strong>Tipe Permintaan</strong></td>
                <td style="width: 35%;">: {{ $request->request_type === 'ATK' ? 'Alat Tulis Kantor (ATK)' : (($request->request_type === 'Rumah Tangga') ? 'Kebutuhan Rumah Tangga (RT)' : 'Semua Tipe (Dipisah)') }}</td>
            </tr>
            <tr>
                <td><strong>Status Approval</strong></td>
                <td>: {{ $request->status_approval && $request->status_approval !== 'all' ? $request->status_approval : 'Semua Status' }}</td>
                <td><strong>Divisi</strong></td>
                <td>: {{ $request->divisi ?: 'Semua Divisi' }}</td>
            </tr>
            @if($request->filled('item_name'))
            <tr>
                <td><strong>Filter Item</strong></td>
                <td colspan="3">: Menampilkan permintaan yang berisi item: <u>{{ $request->item_name }}</u></td>
            </tr>
            @endif
        </table>
    </div>

    @php
        $showAtk = ($request->request_type === 'all' || empty($request->request_type) || $request->request_type === 'ATK');
        $showHousehold = ($request->request_type === 'all' || empty($request->request_type) || $request->request_type === 'Rumah Tangga');
    @endphp

    <!-- SECTION 1: REKAP ATK -->
    @if($showAtk)
        <div class="section-header section-atk">
            1. Rekap Total Kebutuhan Alat Tulis Kantor (ATK)
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;" class="text-center">No</th>
                    <th>Nama Barang ATK</th>
                    <th style="width: 18%;" class="text-center">Satuan</th>
                    <th style="width: 22%;" class="text-center">Total Kebutuhan (Qty)</th>
                    <th style="width: 20%;" class="text-center">Frekuensi Pengajuan</th>
                </tr>
            </thead>
            <tbody>
                @php $totalAtkQty = 0; @endphp
                @forelse($atkAggregatedItems as $index => $item)
                    @php $totalAtkQty += $item['total_qty']; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td style="font-weight: bold;">{{ $item['name'] }}</td>
                        <td class="text-center">{{ $item['unit'] }}</td>
                        <td class="text-center" style="font-size: 11px; font-weight: bold; color: #1d4ed8;">{{ $item['total_qty'] }}</td>
                        <td class="text-center">{{ $item['request_count'] }}x pengajuan</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 15px; color: #999;">
                            Tidak ada data kebutuhan ATK ditemukan sesuai kriteria filter.
                        </td>
                    </tr>
                @endforelse
                @if(count($atkAggregatedItems) > 0)
                    <tr class="table-summary-row">
                        <td colspan="3" class="text-right">TOTAL KEBUTUHAN ATK ({{ count($atkAggregatedItems) }} Jenis Barang):</td>
                        <td class="text-center" style="font-size: 12px; color: #1d4ed8;">{{ $totalAtkQty }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    <!-- SECTION 2: REKAP RUMAH TANGGA -->
    @if($showHousehold)
        <div class="section-header section-household">
            {{ $showAtk ? '2.' : '1.' }} Rekap Total Kebutuhan Rumah Tangga (RT)
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;" class="text-center">No</th>
                    <th>Nama Barang Rumah Tangga</th>
                    <th style="width: 18%;" class="text-center">Satuan</th>
                    <th style="width: 22%;" class="text-center">Total Kebutuhan (Qty)</th>
                    <th style="width: 20%;" class="text-center">Frekuensi Pengajuan</th>
                </tr>
            </thead>
            <tbody>
                @php $totalHouseholdQty = 0; @endphp
                @forelse($householdAggregatedItems as $index => $item)
                    @php $totalHouseholdQty += $item['total_qty']; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td style="font-weight: bold;">{{ $item['name'] }}</td>
                        <td class="text-center">{{ $item['unit'] }}</td>
                        <td class="text-center" style="font-size: 11px; font-weight: bold; color: #d97706;">{{ $item['total_qty'] }}</td>
                        <td class="text-center">{{ $item['request_count'] }}x pengajuan</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 15px; color: #999;">
                            Tidak ada data kebutuhan Rumah Tangga ditemukan sesuai kriteria filter.
                        </td>
                    </tr>
                @endforelse
                @if(count($householdAggregatedItems) > 0)
                    <tr class="table-summary-row">
                        <td colspan="3" class="text-right">TOTAL KEBUTUHAN RUMAH TANGGA ({{ count($householdAggregatedItems) }} Jenis Barang):</td>
                        <td class="text-center" style="font-size: 12px; color: #d97706;">{{ $totalHouseholdQty }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    <div class="footer">
        Rekap Total Kebutuhan Barang - {{ \App\Helpers\SettingsHelper::get('app_name', 'HelpDesk Pro') }}
    </div>
</body>
</html>
