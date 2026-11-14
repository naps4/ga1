<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Permintaan Barang</title>
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
            margin-bottom: 20px;
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
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            vertical-align: top;
        }
        .data-table th {
            background-color: #f1f3f5;
            font-weight: bold;
            text-align: left;
            font-size: 10.5px;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-primary { background-color: #007bff; color: white; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .item-list {
            margin: 0;
            padding-left: 15px;
            font-size: 9.5px;
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
                    <h1 class="header-title">Laporan Rekapitulasi Permintaan Barang</h1>
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
                <td style="width: 35%;">: {{ $request->request_type === 'ATK' ? 'Alat Tulis Kantor (ATK)' : (($request->request_type === 'Rumah Tangga') ? 'Kebutuhan Sanitasi Non Produksi (SNP)' : 'Semua Tipe') }}</td>
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

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 12%;">No. Doc</th>
                <th style="width: 12%;">Tipe</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 15%;">Pengaju</th>
                <th style="width: 13%;">Divisi</th>
                <th style="width: 23%;">Daftar Item & Qty</th>
                <th style="width: 8%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $index => $req)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $req->formatted_doc_number }}</strong>
                        @if($req->revision_number > 0)
                            <br><small style="color: #d97706; font-size: 8px;">(Rev. {{ $req->revision_number }})</small>
                        @endif
                    </td>
                    <td>
                        @if($req->request_type === 'Rumah Tangga')
                            Sanitasi Non Produksi (SNP)
                        @else
                            ATK
                        @endif
                    </td>
                    <td>{{ $req->tanggal_pengajuan->format('d/m/Y') }}</td>
                    <td>{{ $req->nama }}</td>
                    <td>{{ $req->divisi }}</td>
                    <td>
                        <ul class="item-list">
                            @foreach($req->items as $item)
                                <li>
                                    {{ $item->item_name }} 
                                    <strong>({{ $item->quantity }} {{ $item->unit }})</strong>
                                    @if($item->is_revised)
                                        <span style="color: #d97706; font-weight: bold; font-size: 8.5px;">[Direvisi]</span>
                                    @endif
                                    @if($item->description)
                                        <span style="color: #666; font-style: italic;"> - {{ $item->description }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </td>

                    <td class="text-center">
                        <span class="badge 
                            @if($req->status_approval === 'Disetujui') badge-success
                            @elseif($req->status_approval === 'Menunggu Persetujuan') badge-warning
                            @elseif($req->status_approval === 'Ditolak/Revisi') badge-danger
                            @else badge-secondary @endif">
                            {{ $req->status_approval }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #999;">
                        Tidak ada data permintaan ditemukan sesuai kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan Rekapitulasi Permintaan Barang - {{ \App\Helpers\SettingsHelper::get('app_name', 'HelpDesk Pro') }}
    </div>
</body>
</html>
