<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORM DAFTAR ASET - {{ $location->room_name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        .header-box {
            border: 2px solid #000;
            padding: 12px 18px;
            margin-bottom: 15px;
        }
        .table-bordered th, .table-bordered td {
            border: 1.5px solid #000 !important;
            padding: 6px 10px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 15mm; size: A4 portrait; }
        }
    </style>
</head>
<body>

<div class="no-print mb-3 text-right">
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4 font-weight-bold">
        🖨️ Cetak Dokumen (Print)
    </button>
</div>

<div class="header-box">
    <div class="row align-items-center">
        <div class="col-8">
            <h4 class="font-weight-bold mb-1" style="letter-spacing: .5px;">FORM DAFTAR ASET</h4>
            <div class="small font-weight-bold text-uppercase">PT Herbatech Innopharma Industry</div>
        </div>
        <div class="col-4">
            <table class="table-sm table-borderless font-weight-bold float-right mb-0" style="font-size: 11px;">
                <tr><td>No. doc</td><td>:</td><td>CM-03/GA/002.C.01</td></tr>
                <tr><td>Revisi</td><td>:</td><td>-</td></tr>
                <tr><td>Tgl Berlaku</td><td>:</td><td>13 April 2026</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="row font-weight-bold mb-2" style="font-size: 13px;">
    <div class="col-6">
        Tanggal : {{ $lastUpdated }} (Last Update)
    </div>
    <div class="col-6 text-right">
        Lokasi : {{ $location->room_name }} ({{ $location->plant->name ?? 'Plant ' . $location->plant_code }})
    </div>
</div>

<table class="table table-bordered mb-2" style="font-size: 12px;">
    <thead class="text-center font-weight-bold bg-light">
        <tr>
            <th style="width: 50px;">NO</th>
            <th class="text-left">JENIS ASET</th>
            <th style="width: 90px;">KODE</th>
            <th style="width: 100px;">JUMLAH</th>
        </tr>
    </thead>
    <tbody>
        @forelse($aggregatedItems as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->jenis_aset }}</td>
                <td class="text-center font-weight-bold">{{ $item->kode }}</td>
                <td class="text-center font-weight-bold">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center py-4 text-muted">Belum ada aset terdaftar di ruangan ini.</td>
            </tr>
        @endforelse
    </tbody>
    @if(count($aggregatedItems) > 0)
        <tfoot class="font-weight-bold bg-light">
            <tr>
                <td colspan="3" class="text-right">TOTAL UNIT FISIK:</td>
                <td class="text-center">{{ number_format($aggregatedItems->sum('jumlah'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    @endif
</table>

<div class="mt-2 font-weight-bold text-muted" style="font-size: 10px;">
    LAMP. C PR-03/GA/002C.01
</div>

<div class="row mt-5 pt-3 text-center font-weight-bold" style="font-size: 12px;">
    <div class="col-4">
        <div>Dibuat Oleh,</div>
        <div style="height: 60px;"></div>
        <div>( Petugas GA )</div>
    </div>
    <div class="col-4">
        <div>Mengetahui,</div>
        <div style="height: 60px;"></div>
        <div>( SPV / Koordinator )</div>
    </div>
    <div class="col-4">
        <div>Disetujui Oleh,</div>
        <div style="height: 60px;"></div>
        <div>( Operational Manager )</div>
    </div>
</div>

</body>
</html>

