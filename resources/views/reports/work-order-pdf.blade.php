<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Work Order GA {{ $selectedYear }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10.5px;
            color: #1f2937;
            line-height: 1.45;
        }

        /* ── HEADER ── */
        .header {
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header h1 {
            font-size: 15px;
            color: #1e3a8a;
            font-weight: bold;
        }
        .header .sub {
            font-size: 10px;
            color: #475569;
            margin-top: 2px;
        }
        .header .meta {
            font-size: 9px;
            color: #64748b;
            text-align: right;
        }

        /* ── KPI CARDS ── */
        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .kpi-table td {
            width: 25%;
            padding: 8px 10px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            text-align: center;
        }
        .kpi-label {
            font-size: 8.5px;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: block;
            margin-bottom: 3px;
        }
        .kpi-value { font-size: 17px; font-weight: bold; color: #1e3a8a; }
        .kpi-value.green  { color: #15803d; }
        .kpi-value.orange { color: #b45309; }
        .kpi-value.purple { color: #7c3aed; }

        /* ── SECTION HEADERS ── */
        .section-head {
            background: #1d4ed8;
            color: #fff;
            font-size: 10.5px;
            font-weight: bold;
            padding: 5px 8px;
            margin-bottom: 0;
            letter-spacing: 0.3px;
        }
        .section-head.green  { background: #15803d; }
        .section-head.slate  { background: #334155; }

        /* ── GENERIC DATA TABLE ── */
        table.dt {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.dt th {
            background: #dbeafe;
            color: #1e3a8a;
            font-weight: bold;
            padding: 4px 6px;
            border: 1px solid #bfdbfe;
            font-size: 9.5px;
        }
        table.dt td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 9.5px;
        }
        table.dt tbody tr:nth-child(even) td { background: #f8fafc; }

        /* ── WO CARD (per WO block) ── */
        .wo-card {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .wo-card-head {
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            padding: 5px 8px;
            display: table;
            width: 100%;
        }
        .wo-card-head .wo-id {
            display: table-cell;
            font-weight: bold;
            font-size: 10.5px;
            color: #1e3a8a;
            vertical-align: middle;
        }
        .wo-card-head .wo-status {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }
        .wo-card-body { padding: 7px 8px; }

        /* ── INFO GRID ── */
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .info-grid td { padding: 2px 6px; font-size: 9.5px; border: none; vertical-align: top; }
        .info-grid .lbl { color: #6b7280; width: 110px; font-weight: bold; white-space: nowrap; }

        /* ── SUB-TABLE (material list) ── */
        table.mat {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.mat th {
            background: #fef3c7;
            color: #92400e;
            font-size: 9px;
            padding: 3px 5px;
            border: 1px solid #fde68a;
        }
        table.mat td {
            font-size: 9px;
            padding: 3px 5px;
            border: 1px solid #fef3c7;
            vertical-align: top;
        }
        table.mat tbody tr:nth-child(even) td { background: #fffbeb; }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .badge-success   { background: #d1fae5; color: #065f46; }
        .badge-warning   { background: #fef3c7; color: #92400e; }
        .badge-info      { background: #dbeafe; color: #1e40af; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        .badge-orange    { background: #ffedd5; color: #9a3412; }

        /* ── UTILS ── */
        .tc  { text-align: center; }
        .tr  { text-align: right; }
        .bold { font-weight: bold; }
        .muted { color: #94a3b8; font-style: italic; }

        .page-break { page-break-before: always; }

        /* ── FOOTER ── */
        .footer {
            margin-top: 14px;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            font-size: 8.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

{{-- ═══════════ HEADER ═══════════ --}}
<div class="header">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="vertical-align:middle; width:75%;">
                @if(!empty($logoPath) && file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="height:40px; width:auto; margin-right:8px; vertical-align:middle;">
                @endif
                <span class="bold" style="font-size:15px; color:#1e3a8a; vertical-align:middle;">
                    LAPORAN WORK ORDER — GENERAL AFFAIR
                </span>
                <div class="sub" style="margin-left:2px; margin-top:3px;">
                    {{ $companyName ?? 'PT. FPU' }} &bull; Periode Tahun {{ $selectedYear }}
                </div>
            </td>
            <td class="meta" style="vertical-align:top;">
                Digenerate oleh: <strong>{{ $generator }}</strong><br>
                {{ $generatedAt }}<br>
                Total WO: <strong>{{ $workOrders->count() }}</strong>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════ KPI ═══════════ --}}
<table class="kpi-table">
    <tr>
        <td>
            <span class="kpi-label">Total Work Order</span>
            <span class="kpi-value">{{ $workOrders->count() }}</span>
        </td>
        <td>
            <span class="kpi-label">WO Selesai</span>
            <span class="kpi-value green">{{ $workOrders->where('status','Selesai')->count() }}</span>
        </td>
        <td>
            <span class="kpi-label">WO Ditutup (Handover)</span>
            <span class="kpi-value orange">{{ $totalDitutup }}</span>
        </td>
        <td>
            <span class="kpi-label">WO Butuh Material (FPU)</span>
            <span class="kpi-value purple">{{ $workOrders->filter(fn($wo) => $wo->fpuRequest)->count() }}</span>
        </td>
    </tr>
</table>

{{-- ═══════════ BAGIAN 1: RINGKASAN BULANAN ═══════════ --}}
<div class="section-head">Ringkasan Bulanan — Tahun {{ $selectedYear }}</div>
<table class="dt">
    <thead>
        <tr>
            <th width="28%">Periode</th>
            <th class="tc" width="18%">WO Masuk</th>
            <th class="tc" width="18%">WO Selesai</th>
            <th class="tc" width="18%">WO Ditutup (Handover)</th>
            <th class="tc" width="18%">Kumulatif Masuk</th>
        </tr>
    </thead>
    <tbody>
        @php $running = 0; $totalMasuk = 0; $totalSelesai = 0; $totalDitutupBulan = 0; @endphp
        @foreach($monthlySummary as $row)
            @php
                $running += $row['masuk'];
                $totalMasuk      += $row['masuk'];
                $totalSelesai    += $row['selesai'];
                $totalDitutupBulan += $row['ditutup'];
            @endphp
            <tr>
                <td>{{ $row['label'] }}</td>
                <td class="tc">{{ $row['masuk'] > 0 ? $row['masuk'] : '-' }}</td>
                <td class="tc">{{ $row['selesai'] > 0 ? $row['selesai'] : '-' }}</td>
                <td class="tc">{{ $row['ditutup'] > 0 ? $row['ditutup'] : '-' }}</td>
                <td class="tc bold">{{ $running }}</td>
            </tr>
        @endforeach
        <tr style="background:#dbeafe; font-weight:bold;">
            <td>TOTAL</td>
            <td class="tc">{{ $totalMasuk }}</td>
            <td class="tc">{{ $totalSelesai }}</td>
            <td class="tc">{{ $totalDitutupBulan }}</td>
            <td class="tc">{{ $running }}</td>
        </tr>
    </tbody>
</table>

{{-- ═══════════ BAGIAN 2: PERFORMA TEKNISI ═══════════ --}}
<div class="section-head green">Performa Teknisi</div>
<table class="dt">
    <thead>
        <tr>
            <th class="tc" width="5%">No</th>
            <th width="40%">Nama Teknisi</th>
            <th class="tc" width="18%">Total Tugas</th>
            <th class="tc" width="18%">Selesai</th>
            <th class="tc" width="19%">Rasio Selesai</th>
        </tr>
    </thead>
    <tbody>
        @forelse($techPerformance as $i => $tech)
            @php
                $ratio = $tech->total_projects > 0
                    ? round(($tech->completed_projects / $tech->total_projects) * 100, 1) : 0;
                $badgeClass = $ratio >= 80 ? 'badge-success' : ($ratio >= 50 ? 'badge-warning' : 'badge-secondary');
            @endphp
            <tr>
                <td class="tc">{{ $i + 1 }}</td>
                <td class="bold">{{ $tech->name }}</td>
                <td class="tc">{{ $tech->total_projects }}</td>
                <td class="tc">{{ $tech->completed_projects }}</td>
                <td class="tc"><span class="badge {{ $badgeClass }}">{{ $ratio }}%</span></td>
            </tr>
        @empty
            <tr><td colspan="5" class="tc muted">Belum ada data teknisi.</td></tr>
        @endforelse
    </tbody>
</table>

{{-- ═══════════ BAGIAN 3: DETAIL PER WO ═══════════ --}}
<div class="page-break"></div>

<div class="section-head slate" style="margin-bottom:12px;">
    Detail Work Order — Lengkap dengan Kebutuhan Material ({{ $workOrders->count() }} WO)
</div>

@forelse($workOrders as $idx => $wo)
    @php
        $statusClass = match($wo->status) {
            'Selesai'                       => 'badge-success',
            'Sedang Dikerjakan'             => 'badge-info',
            'Menunggu Kebutuhan',
            'Menunggu Pengadaan Material',
            'Pengecekan Engineer'           => 'badge-warning',
            default                         => 'badge-secondary',
        };
        $tanggalSelesai = $wo->status === 'Selesai'
            ? $wo->updated_at->format('d/m/Y H:i') : '-';
        $tanggalDitutup = ($wo->handover && $wo->handover->accepted_by_user_at)
            ? \Carbon\Carbon::parse($wo->handover->accepted_by_user_at)->format('d/m/Y H:i') : '-';
        $fpu = $wo->fpuRequest;
    @endphp

    <div class="wo-card">

        {{-- Card Header --}}
        <div class="wo-card-head">
            <span class="wo-id">
                #{{ $idx + 1 }} &nbsp; WO-{{ str_pad($wo->id, 5, '0', STR_PAD_LEFT) }}
                &nbsp;|&nbsp; {{ $wo->created_at->format('d M Y') }}
                &nbsp;|&nbsp; <span style="color:#475569; font-weight:normal;">{{ $wo->user->name ?? '-' }}</span>
                @if($wo->department)
                    &nbsp;&mdash;&nbsp; {{ $wo->department }}
                @endif
            </span>
            <span class="wo-status">
                <span class="badge {{ $statusClass }}">{{ $wo->status }}</span>
                @if($fpu)
                    &nbsp;<span class="badge badge-orange">Ada FPU</span>
                @endif
            </span>
        </div>

        {{-- Card Body --}}
        <div class="wo-card-body">

            {{-- Row 1: Info utama (2 kolom) --}}
            <table style="width:100%; border-collapse:collapse; margin-bottom:6px;">
                <tr>
                    {{-- Kiri: Deskripsi, Teknisi, Status pengerjaan --}}
                    <td style="width:50%; vertical-align:top; padding-right:8px;">
                        <table class="info-grid">
                            <tr>
                                <td class="lbl">Deskripsi</td>
                                <td>{{ $wo->description }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Teknisi</td>
                                <td>{{ $wo->assignedEngineer->name ?? '<span class="muted">Belum ditugaskan</span>' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Tgl. Selesai</td>
                                <td>{{ $tanggalSelesai }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Tgl. Handover</td>
                                <td>{{ $tanggalDitutup }}</td>
                            </tr>
                        </table>
                    </td>
                    {{-- Kanan: Hasil pengecekan & laporan --}}
                    <td style="width:50%; vertical-align:top; border-left:1px solid #e2e8f0; padding-left:8px;">
                        <table class="info-grid">
                            <tr>
                                <td class="lbl">Hasil Pengecekan</td>
                                <td>{{ $wo->check_result ?? '<span class="muted">Belum ada</span>' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Kebutuhan Teknisi</td>
                                <td>{{ $wo->engineer_needs ?? '<span class="muted">-</span>' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Laporan Akhir</td>
                                <td>{{ $wo->engineer_report ?? '<span class="muted">Belum ada</span>' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- Row 2: Detail FPU / Material (jika ada) --}}
            @if($fpu)
                <div style="border-top: 1px dashed #fde68a; padding-top:6px; margin-top:2px;">
                    <div style="font-size:9px; font-weight:bold; color:#92400e; margin-bottom:4px;">
                        &#9654; Formulir Pengajuan Pengadaan Material (FPU)
                        &nbsp;&mdash;&nbsp;
                        Prioritas: {{ $fpu->priority ?? '-' }}
                        &nbsp;|&nbsp; Jenis: {{ $fpu->type ?? '-' }}
                        &nbsp;|&nbsp; Kategori: {{ $fpu->category ?? '-' }}
                        &nbsp;|&nbsp; Pelaksana: {{ $fpu->executor_type ?? '-' }}
                        &nbsp;|&nbsp; Status: <strong>{{ $fpu->status_approval ?? '-' }}</strong>
                    </div>

                    @if($fpu->materials && $fpu->materials->count())
                        <table class="mat">
                            <thead>
                                <tr>
                                    <th class="tc" width="4%">No</th>
                                    <th width="30%">Nama Material</th>
                                    <th width="28%">Spesifikasi</th>
                                    <th class="tc" width="10%">Qty</th>
                                    <th class="tc" width="10%">Satuan</th>
                                    <th width="18%">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fpu->materials as $mi => $mat)
                                    <tr>
                                        <td class="tc">{{ $mi + 1 }}</td>
                                        <td class="bold">{{ $mat->material_name }}</td>
                                        <td>{{ $mat->specification ?? '-' }}</td>
                                        <td class="tc">{{ $mat->qty }}</td>
                                        <td class="tc">{{ $mat->unit ?? '-' }}</td>
                                        <td>{{ $mat->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <span class="muted" style="font-size:9px;">Belum ada item material yang ditambahkan.</span>
                    @endif
                </div>
            @endif

        </div>{{-- end wo-card-body --}}
    </div>{{-- end wo-card --}}

@empty
    <p class="muted tc" style="padding:20px 0;">Tidak ada Work Order pada tahun {{ $selectedYear }}.</p>
@endforelse

{{-- ═══════════ FOOTER ═══════════ --}}
<div class="footer">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td>{{ $companyName ?? 'PT. FPU' }} &bull; Laporan Work Order GA &bull; Tahun {{ $selectedYear }}</td>
            <td style="text-align:right;">
                Digenerate oleh <strong>{{ $generator }}</strong> pada {{ $generatedAt }}
            </td>
        </tr>
    </table>
</div>

</body>
</html>
