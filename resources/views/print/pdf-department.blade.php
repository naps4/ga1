<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>
        @if(($targetType ?? 'ATK') === 'Rumah Tangga')
            Rekap Distribusi Kebutuhan Sanitasi Non Produksi (SNP) Per Departemen
        @else
            Rekap Distribusi Kebutuhan ATK Per Departemen
        @endif
    </title>
    <style>
        @page {
            margin: 20px 20px 25px 20px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8.5px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
        }
        .header-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            color: #1a1a1a;
        }
        .header-meta {
            font-size: 8px;
            color: #666;
            margin-top: 3px;
        }
        .filter-summary {
            background-color: #f8f9fa;
            border: 1px solid #e2e8f0;
            padding: 5px 8px;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .filter-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .filter-summary td {
            padding: 1px 4px;
            font-size: 8.5px;
        }
        .section-header {
            margin-top: 10px;
            margin-bottom: 5px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
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
            margin-bottom: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
        }
        .category-header-row {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #1e293b;
            font-size: 8.5px;
        }
        .table-subtotal-row {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 8px;
        }
        .table-grandtotal-row {
            font-weight: bold;
            font-size: 9px;
        }
        .signatures-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 8.5px;
            padding: 0 10px;
        }
        .sign-space {
            height: 40px;
        }
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0px;
            right: 0px;
            height: 20px;
            text-align: center;
            font-size: 7.5px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 3px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    @php
        $isHousehold = (($targetType ?? 'ATK') === 'Rumah Tangga');
        $deptList = $isHousehold ? ($householdDeptList ?? []) : ($atkDeptList ?? []);
        $byCategory = $isHousehold ? ($householdByCategory ?? []) : ($atkByCategory ?? []);
        $grandTotal = $isHousehold ? ($householdGrandTotalQty ?? 0) : ($atkGrandTotalQty ?? 0);
        $deptGrandTotals = $isHousehold ? ($householdDeptGrandTotals ?? []) : ($atkDeptGrandTotals ?? []);
        $deptColCount = count($deptList);
        $totalColSpan = 4 + $deptColCount + 2; // No, Kategori, Nama, Satuan + Depts + Total + Checklist
    @endphp

    <!-- HEADER -->
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 class="header-title">
                        MATRIKS REKAPITULASI DISTRIBUSI {{ $isHousehold ? 'KEBUTUHAN SANITASI NON PRODUKSI (SNP)' : 'ALAT TULIS KANTOR (ATK)' }} PER DEPARTEMEN
                    </h1>
                    <div class="header-meta">
                        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} | Dicetak Oleh: {{ auth()->user()?->name ?? 'Admin / Purchasing' }}
                    </div>
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    @php
                        $logo = \App\Helpers\SettingsHelper::getReportLogo();
                        $logoPath = $logo ? storage_path('app/public/' . $logo) : null;
                    @endphp
                    @if($logoPath && file_exists($logoPath))
                        <img src="{{ $logoPath }}" style="max-height: 28px; max-width: 130px; display: inline-block; margin-bottom: 2px; vertical-align: middle;">
                        <br>
                    @endif
                    <strong style="font-size: 10px; vertical-align: middle;">{{ \App\Helpers\SettingsHelper::get('company_name', 'HERBATECH') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- FILTER SUMMARY -->
    <div class="filter-summary">
        <table>
            <tr>
                <td style="width: 12%;"><strong>Periode</strong></td>
                <td style="width: 38%;">: 
                    @if($request->filled('start_date') || $request->filled('end_date'))
                        {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Awal' }}
                        s.d
                        {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Akhir' }}
                    @else
                        Semua Periode
                    @endif
                </td>
                <td style="width: 15%;"><strong>Tipe Kategori</strong></td>
                <td style="width: 35%;">: 
                    <strong style="color: {{ $isHousehold ? '#d97706' : '#1d4ed8' }};">
                        {{ $isHousehold ? 'Kebutuhan Sanitasi Non Produksi (SNP)' : 'Alat Tulis Kantor (ATK)' }}
                    </strong>
                </td>
            </tr>
            <tr>
                <td><strong>Status Approval</strong></td>
                <td>: {{ $request->status_approval && $request->status_approval !== 'all' ? $request->status_approval : 'Semua Status' }}</td>
                <td><strong>Jumlah Divisi Terdaftar</strong></td>
                <td>: {{ count($deptList) }} Divisi / Departemen</td>
            </tr>
        </table>
    </div>

    <!-- MAIN MATRIX TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" rowspan="2">No</th>
                <th style="width: 180px;" class="text-left" rowspan="2">Nama Barang {{ $isHousehold ? 'Sanitasi Non Produksi' : 'ATK' }}</th>
                <th style="width: 45px;" rowspan="2">Satuan</th>
                @if($deptColCount > 0)
                    <th colspan="{{ $deptColCount }}" style="background-color: {{ $isHousehold ? '#fef3c7' : '#dbeafe' }}; color: {{ $isHousehold ? '#92400e' : '#1e3a8a' }}; font-size: 8.5px;">
                        RINCIAN KEBUTUHAN PER DEPARTEMEN / DIVISI
                    </th>
                @endif
                <th style="width: 60px;" rowspan="2">TOTAL KEBUTUHAN</th>
                <th style="width: 65px;" rowspan="2">CEK DISTRIBUSI</th>
            </tr>
            @if($deptColCount > 0)
                <tr>
                    @foreach($deptList as $dept)
                        <th style="min-width: 40px; font-size: 7.5px; padding: 3px 2px;" title="{{ $dept }}">
                            {{ Str::limit($dept, 15) }}
                        </th>
                    @endforeach
                </tr>
            @endif
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($byCategory as $catName => $catData)
                <!-- Category Section Header -->
                <tr class="category-header-row">
                    <td colspan="{{ $totalColSpan }}" class="text-left" style="background-color: {{ $isHousehold ? '#fffbeb' : '#f0f9ff' }}; color: {{ $isHousehold ? '#b45309' : '#0369a1' }};">
                        <strong>📁 Kategori: {{ $catName }}</strong>
                        <span style="font-size: 7.5px; font-weight: normal; color: #64748b;">({{ count($catData['items']) }} item)</span>
                    </td>
                </tr>

                @foreach($catData['items'] as $item)
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="text-left" style="font-weight: 600;">
                            {{ $item['name'] }}
                            @if($item['is_custom'])
                                <span style="color: #ef4444; font-size: 7px;">[Custom]</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item['unit'] }}</td>

                        <!-- Department breakdown -->
                        @foreach($deptList as $dept)
                            @php $qty = $item['dept_quantities'][$dept] ?? 0; @endphp
                            <td class="text-center" style="{{ $qty > 0 ? 'font-weight: bold; color: #1e293b;' : 'color: #cbd5e1;' }}">
                                {{ $qty > 0 ? $qty : '-' }}
                            </td>
                        @endforeach

                        <!-- Total Qty Column -->
                        <td class="text-center" style="font-weight: bold; font-size: 9.5px; color: {{ $isHousehold ? '#d97706' : '#1d4ed8' }};">
                            {{ $item['total_qty'] }}
                        </td>

                        <!-- Checklist Column -->
                        <td class="text-center" style="color: #cbd5e1; font-size: 7px;">
                            [ &nbsp; ]
                        </td>
                    </tr>
                @endforeach

                <!-- Subtotal Row per Category -->
                <tr class="table-subtotal-row" style="background-color: {{ $isHousehold ? '#fef3c7' : '#eff6ff' }};">
                    <td colspan="3" class="text-right" style="color: {{ $isHousehold ? '#92400e' : '#1e40af' }}; font-weight: bold;">
                        Subtotal {{ $catName }}:
                    </td>
                    @foreach($deptList as $dept)
                        <td class="text-center" style="font-weight: bold; color: {{ $isHousehold ? '#92400e' : '#1e40af' }};">
                            {{ ($catData['dept_subtotals'][$dept] ?? 0) > 0 ? $catData['dept_subtotals'][$dept] : '-' }}
                        </td>
                    @endforeach
                    <td class="text-center" style="font-weight: bold; color: {{ $isHousehold ? '#b45309' : '#1d4ed8' }}; font-size: 9px;">
                        {{ $catData['subtotal_qty'] }}
                    </td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $totalColSpan }}" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data permintaan ditemukan sesuai kriteria filter.
                    </td>
                </tr>
            @endforelse

            <!-- GRAND TOTAL ROW -->
            @if($grandTotal > 0)
                <tr class="table-grandtotal-row" style="background-color: {{ $isHousehold ? '#fde68a' : '#bfdbfe' }}; color: {{ $isHousehold ? '#78350f' : '#172554' }};">
                    <td colspan="3" class="text-right" style="font-size: 9px;">
                        GRAND TOTAL KEBUTUHAN:
                    </td>
                    @foreach($deptList as $dept)
                        <td class="text-center" style="font-size: 9px;">
                            {{ ($deptGrandTotals[$dept] ?? 0) > 0 ? $deptGrandTotals[$dept] : '-' }}
                        </td>
                    @endforeach
                    <td class="text-center" style="font-size: 11px; font-weight: bold; color: {{ $isHousehold ? '#b45309' : '#1d4ed8' }};">
                        {{ $grandTotal }}
                    </td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- SIGNATURES -->
    <table class="signatures-table">
        <tr>
            <td>
                Dibuat Oleh,<br>
                <strong>Admin GA / Logistik</strong>
                <div class="sign-space"></div>
                <u>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</u>
            </td>
            <td>
                Diperiksa / Pembelian,<br>
                <strong>Purchasing</strong>
                <div class="sign-space"></div>
                <u>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</u>
            </td>
            <td>
                Disetujui Oleh,<br>
                <strong>Manager General Affairs</strong>
                <div class="sign-space"></div>
                <u>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</u>
            </td>
        </tr>
    </table>

    <div class="footer">
        Matriks Rekapitulasi Kebutuhan Per Departemen - {{ \App\Helpers\SettingsHelper::get('app_name', 'HelpDesk Pro') }}
    </div>
</body>
</html>
