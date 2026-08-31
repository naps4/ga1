<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>
        @if(($targetType ?? 'all') === 'ATK')
            Rekap Kebutuhan Alat Tulis Kantor (ATK)
        @elseif(($targetType ?? 'all') === 'Rumah Tangga')
            Rekap Kebutuhan Rumah Tangga (RT)
        @else
            Rekap Total Kebutuhan Barang
        @endif
    </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            color: #1a1a1a;
        }
        .header-meta {
            font-size: 8.5px;
            color: #666;
            margin-top: 4px;
        }
        .filter-summary {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 6px 10px;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .filter-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .filter-summary td {
            padding: 2px 4px;
            font-size: 9.5px;
        }
        .section-header {
            margin-top: 14px;
            margin-bottom: 6px;
            padding: 5px 8px;
            font-size: 11px;
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
        .category-header-row {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            font-size: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            text-align: left;
            font-size: 9.5px;
        }
        .table-subtotal-row {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 9.5px;
        }
        .table-grandtotal-row {
            font-weight: bold;
            font-size: 11px;
        }
        .page-break {
            page-break-before: always;
        }
        .signatures-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 9.5px;
            padding: 0 10px;
        }
        .sign-space {
            height: 50px;
        }
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 8.5px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 4px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    @php
        $target = $targetType ?? ($request->request_type ?? 'all');
        $showAtk = ($target === 'all' || empty($target) || $target === 'ATK');
        $showHousehold = ($target === 'all' || empty($target) || $target === 'Rumah Tangga');
    @endphp

    <!-- HEADER BLOCK -->
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 class="header-title">
                        @if($target === 'ATK')
                            Rekapitulasi Kebutuhan Alat Tulis Kantor (ATK)
                        @elseif($target === 'Rumah Tangga')
                            Rekapitulasi Kebutuhan Rumah Tangga (RT)
                        @else
                            Rekapitulasi Total Kebutuhan Barang
                        @endif
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
                        <img src="{{ $logoPath }}" style="max-height: 32px; max-width: 140px; display: inline-block; margin-bottom: 3px; vertical-align: middle;">
                        <br>
                    @endif
                    <strong style="font-size: 11px; vertical-align: middle;">{{ \App\Helpers\SettingsHelper::get('company_name', 'HERBATECH') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- FILTER INFO -->
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
                <td style="width: 15%;"><strong>Tipe Rekap</strong></td>
                <td style="width: 35%;">: 
                    @if($target === 'ATK')
                        <strong style="color: #1d4ed8;">Alat Tulis Kantor (ATK)</strong>
                    @elseif($target === 'Rumah Tangga')
                        <strong style="color: #d97706;">Kebutuhan Rumah Tangga (RT)</strong>
                    @else
                        Semua Tipe (Dipisah Per Kategori)
                    @endif
                </td>
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

    <!-- SECTION 1: REKAP ATK -->
    @if($showAtk)
        <div class="section-header section-atk">
            {{ $showHousehold && $target === 'all' ? '1. ' : '' }}Rekapitulasi Kebutuhan Alat Tulis Kantor (ATK)
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%;" class="text-center">No</th>
                    <th>Nama Barang ATK</th>
                    <th style="width: 15%;" class="text-center">Satuan</th>
                    <th style="width: 20%;" class="text-center">Total Kebutuhan</th>
                    <th style="width: 18%;" class="text-center">Frekuensi</th>
                </tr>
            </thead>
            <tbody>
                @php $globalNoAtk = 1; @endphp
                @forelse($atkByCategory ?? [] as $catName => $catData)
                    <tr class="category-header-row">
                        <td colspan="5">
                            <strong style="color: #1e3a8a;">📁 Kategori: {{ $catName }}</strong>
                            <span style="font-size: 8.5px; font-weight: normal; color: #64748b;">({{ count($catData['items']) }} jenis barang)</span>
                        </td>
                    </tr>
                    @foreach($catData['items'] as $item)
                        <tr>
                            <td class="text-center">{{ $globalNoAtk++ }}</td>
                            <td style="font-weight: 600;">
                                {{ $item['name'] }}
                                @if($item['is_custom'])
                                    <span style="color: #ef4444; font-size: 8px;">[Custom]</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item['unit'] }}</td>
                            <td class="text-center" style="font-weight: bold; color: #1d4ed8; font-size: 10.5px;">{{ $item['total_qty'] }}</td>
                            <td class="text-center" style="color: #64748b;">{{ $item['request_count'] }}x diajukan</td>
                        </tr>
                    @endforeach
                    <tr class="table-subtotal-row">
                        <td colspan="3" class="text-right" style="color: #1e40af;">Subtotal Kategori {{ $catName }}:</td>
                        <td class="text-center" style="font-weight: bold; color: #1e40af;">{{ $catData['subtotal_qty'] }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 15px; color: #999;">
                            Tidak ada data kebutuhan ATK ditemukan sesuai kriteria filter.
                        </td>
                    </tr>
                @endforelse

                @if(($atkGrandTotalQty ?? 0) > 0)
                    <tr class="table-grandtotal-row" style="background-color: #dbeafe; color: #1e3a8a;">
                        <td colspan="3" class="text-right">GRAND TOTAL KEBUTUHAN ATK:</td>
                        <td class="text-center" style="font-size: 12px; color: #1d4ed8;">{{ $atkGrandTotalQty }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    <!-- PAGE BREAK IF BOTH ATK AND HOUSEHOLD ARE SHOWN -->
    @if($showAtk && $showHousehold && $target === 'all' && count($householdByCategory ?? []) > 0)
        <div class="page-break"></div>
        
        <!-- Repeat minimal header on page 2 -->
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <h1 class="header-title">Rekapitulasi Total Kebutuhan Barang (Lanjutan)</h1>
                        <div class="header-meta">
                            Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} | Dicetak Oleh: {{ auth()->user()?->name ?? 'Admin / Purchasing' }}
                        </div>
                    </td>
                    <td style="text-align: right; vertical-align: bottom;">
                        <strong style="font-size: 11px;">{{ \App\Helpers\SettingsHelper::get('company_name', 'HERBATECH') }}</strong>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <!-- SECTION 2: REKAP RUMAH TANGGA -->
    @if($showHousehold)
        <div class="section-header section-household">
            {{ $showAtk && $target === 'all' ? '2. ' : '' }}Rekapitulasi Kebutuhan Rumah Tangga (RT)
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%;" class="text-center">No</th>
                    <th>Nama Barang Rumah Tangga</th>
                    <th style="width: 15%;" class="text-center">Satuan</th>
                    <th style="width: 20%;" class="text-center">Total Kebutuhan</th>
                    <th style="width: 18%;" class="text-center">Frekuensi</th>
                </tr>
            </thead>
            <tbody>
                @php $globalNoRt = 1; @endphp
                @forelse($householdByCategory ?? [] as $catName => $catData)
                    <tr class="category-header-row">
                        <td colspan="5">
                            <strong style="color: #92400e;">📁 Kategori: {{ $catName }}</strong>
                            <span style="font-size: 8.5px; font-weight: normal; color: #64748b;">({{ count($catData['items']) }} jenis barang)</span>
                        </td>
                    </tr>
                    @foreach($catData['items'] as $item)
                        <tr>
                            <td class="text-center">{{ $globalNoRt++ }}</td>
                            <td style="font-weight: 600;">
                                {{ $item['name'] }}
                                @if($item['is_custom'])
                                    <span style="color: #ef4444; font-size: 8px;">[Custom]</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item['unit'] }}</td>
                            <td class="text-center" style="font-weight: bold; color: #d97706; font-size: 10.5px;">{{ $item['total_qty'] }}</td>
                            <td class="text-center" style="color: #64748b;">{{ $item['request_count'] }}x diajukan</td>
                        </tr>
                    @endforeach
                    <tr class="table-subtotal-row">
                        <td colspan="3" class="text-right" style="color: #b45309;">Subtotal Kategori {{ $catName }}:</td>
                        <td class="text-center" style="font-weight: bold; color: #b45309;">{{ $catData['subtotal_qty'] }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 15px; color: #999;">
                            Tidak ada data kebutuhan Rumah Tangga ditemukan sesuai kriteria filter.
                        </td>
                    </tr>
                @endforelse

                @if(($householdGrandTotalQty ?? 0) > 0)
                    <tr class="table-grandtotal-row" style="background-color: #fef3c7; color: #92400e;">
                        <td colspan="3" class="text-right">GRAND TOTAL KEBUTUHAN RUMAH TANGGA:</td>
                        <td class="text-center" style="font-size: 12px; color: #d97706;">{{ $householdGrandTotalQty }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    <!-- SIGNATURE BLOCK -->
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
        Rekap Total Kebutuhan Barang - {{ \App\Helpers\SettingsHelper::get('app_name', 'HelpDesk Pro') }}
    </div>
</body>
</html>
