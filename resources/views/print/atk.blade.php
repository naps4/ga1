@extends('layouts.print')

@section('title', 'ATK-' . str_pad($atkRequest->id, 4, '0', STR_PAD_LEFT))

@section('custom_header')
    <!-- Override the default kop surat to match the scanned paper form -->
@endsection

@section('content')
<style>
    body, .page-a4 {
        font-family: 'Arial', sans-serif !important;
        color: #000 !important;
        line-height: 1.25 !important;
    }
    .page-a4 {
        padding: 0.8cm !important;
    }
    
    .atk-print-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid #000;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .atk-print-table th, .atk-print-table td {
        border: 1px solid #000;
        padding: 5px 8px;
        vertical-align: middle;
    }
    .atk-print-table th {
        background-color: #f2f2f2 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        font-size: 10.5px;
        font-weight: bold;
        text-align: center;
    }
    .atk-print-table td {
        font-size: 10px;
    }
    
    .checkbox-box {
        font-family: monospace;
        font-size: 11px;
        font-weight: bold;
        margin-right: 2px;
    }
    
    .dotted-underline {
        border-bottom: 1px dotted #000;
        min-width: 60px;
        display: inline-block;
        font-size: 9.5px;
    }
    
    @media print {
        body {
            background-color: white !important;
        }
        .page-a4 {
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<!-- Header Section -->
<table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 10px;">
    <tr>
        <td style="width: 50%; border: none; vertical-align: bottom; padding-bottom: 5px;">
            <div style="font-weight: 900; font-size: 15px; color: #000; letter-spacing: 0.5px;">
                {{ \App\Helpers\SettingsHelper::get('company_name', 'HERBATECH') }}
            </div>
        </td>
        <td style="width: 50%; border: 1.5px solid #000; padding: 6px; font-family: Arial, sans-serif;">
            <table style="width: 100%; border-collapse: collapse; border: none;">
                <tr style="font-size: 10px; height: 14px;">
                    <td style="width: 50%; padding: 0; border: none;">Tanggal Pengajuan Barang (DD/MM/YY)</td>
                    <td style="width: 50%; padding: 0; border: none;">: {{ $atkRequest->tanggal_pengajuan->format('d/m/Y') }}</td>
                </tr>
                <tr style="font-size: 10px; height: 14px;">
                    <td style="padding: 0; border: none;">Nama</td>
                    <td style="padding: 0; border: none;">: <strong>{{ $atkRequest->nama }}</strong></td>
                </tr>
                <tr style="font-size: 10px; height: 14px;">
                    <td style="padding: 0; border: none;">Divisi / Unit Kerja</td>
                    <td style="padding: 0; border: none;">: {{ $atkRequest->divisi }}</td>
                </tr>
                <tr style="font-size: 10px; height: 14px;">
                    <td style="padding: 0; border: none;">Periode</td>
                    <td style="padding: 0; border: none;">: <span style="font-weight: bold;">{{ $atkRequest->periode }}</span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Title -->
<div style="text-align: center; margin-top: 15px; margin-bottom: 15px;">
    <h5 style="margin: 0; font-weight: bold; font-size: 13px; text-decoration: underline; letter-spacing: 0.5px;">FORM PERMINTAAN ATK (ALAT TULIS KANTOR)</h5>
</div>

<!-- Main Selected Items Table -->
@php
    $orderedNames = [
        'Amplop Coklat', 'Amplop Putih', 'Baterai', 'Binder Clips', 'Box File', 'Buku', 'Business File',
        'Cartridge tinta pita dot matrix', 'Clear Holder Document', 'Clear Sleeves', 'Cutter', 'Isi Cutter',
        'Double Tape', 'Gunting', 'Isi Pulpen', 'Isi Staples', 'Kalkulator', 'Kertas 70 Gr', 'Kertas 80 Gr',
        'Kertas Continuos Form 9 1/2 x 11', 'Kertas Continuos Form 9 1/2 x 11 (Bagi 2)', 'Kertas Foto/sticker',
        'Lakban ( Coklat / Hitam / Bening )*', 'Lem Kertas', 'Map Dengan Pengait', 'Map Tanpa Pengait',
        'Ordner', 'Paper Clips', 'Pembatas File', 'Pembolong Kertas', 'Pencabut Staples', 'Penggaris',
        'Penghapus', 'Pensil', 'Pita Printer', 'Post IT', 'Pulpen', 'Rautan Pensil', 'Sellotape',
        'Sheet Protector', 'Spidol Kecil/OPM', 'Spidol Papan Tulis', 'Stabilo', 'Staples', 'Tape Dispenser',
        'Tinta ( 678 / 703 / 802 )', 'Tinta PG740', 'Tinta CL741', 'Tinta Stempel Tanpa Bantalan (Simanis)',
        'Tip ex', 'Sticker label', 'Baterai AA', 'Baterai AAA'
    ];
    $standardItemsByName = collect($standardItems)->keyBy('name');
@endphp

<table class="atk-print-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 35%;">Nama Barang</th>
            <th style="width: 12%;">Satuan</th>
            <th style="width: 38%;">Spesifikasi / Keterangan</th>
            <th style="width: 10%;">Total</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        
        <!-- Render Standard Items with Quantity > 0 -->
        @foreach($orderedNames as $name)
            @php
                $dbItem = $itemsMap[$name] ?? null;
                $qty = $dbItem ? $dbItem['quantity'] : 0;
            @endphp
            @if($qty > 0)
                @php
                    $itemConfig = $standardItemsByName->get($name);
                    $unit = $itemConfig ? $itemConfig['unit'] : 'Pcs';
                    $options = $itemConfig ? $itemConfig['options'] : [];
                    
                    // Parse options
                    $checkedOpts = [];
                    $customOpt = '';
                    $dbDesc = $dbItem['description'] ?? '';
                    if (!empty($options)) {
                        $descParts = array_map('trim', explode(',', $dbDesc));
                        foreach ($options as $opt) {
                            if (in_array($opt, $descParts)) {
                                $checkedOpts[] = $opt;
                            }
                        }
                        $remainingParts = array_diff($descParts, $options);
                        $customOpt = implode(', ', $remainingParts);
                    } else {
                        $customOpt = $dbDesc;
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td style="font-weight: bold;">{{ $name }}</td>
                    <td style="text-align: center;">{{ $unit }}</td>
                    <td style="font-size: 9.5px;">
                        @if(!empty($options))
                            @foreach($options as $opt)
                                @php $isChecked = in_array($opt, $checkedOpts); @endphp
                                <span style="margin-right: 10px; white-space: nowrap; display: inline-block;">
                                    <span class="checkbox-box">[{{ $isChecked ? 'X' : ' ' }}]</span> {{ $opt }}
                                </span>
                            @endforeach
                            @if($customOpt)
                                <span class="dotted-underline">{{ $customOpt }}</span>
                            @endif
                        @else
                            @if($customOpt)
                                <span class="dotted-underline" style="width: 90%;">{{ $customOpt }}</span>
                            @else
                                -
                            @endif
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold; font-size: 11px;">{{ $qty }}</td>
                </tr>
            @endif
        @endforeach
        
        <!-- Render Custom / Unlisted Items with Quantity > 0 -->
        @if(count($customItems) > 0)
            <tr style="background-color: #f9f9f9;">
                <td colspan="5" style="font-weight: bold; font-size: 9.5px; color: #333; height: 22px;">
                    <i class="fas fa-edit mr-1"></i> Barang Kustom (Tidak Terdaftar di Form Standar)
                </td>
            </tr>
            @foreach($customItems as $cItem)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td style="font-weight: bold; color: #dc3545;">{{ $cItem->item_name }}</td>
                    <td style="text-align: center;">{{ $cItem->unit }}</td>
                    <td style="font-size: 9.5px;">{{ $cItem->description ?? '-' }}</td>
                    <td style="text-align: center; font-weight: bold; font-size: 11px;">{{ $cItem->quantity }}</td>
                </tr>
            @endforeach
        @endif

        @if($no === 1)
            <tr>
                <td colspan="5" class="text-center text-muted" style="padding: 20px;">
                    Tidak ada barang yang terpilih.
                </td>
            </tr>
        @endif
    </tbody>
</table>

<div style="font-size: 8.5px; font-style: italic; margin-top: 2px;">
    * coret yang tidak diperlukan
</div>

<!-- Footer Signatures -->
<table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-family: Arial, sans-serif; font-size: 9.5px;">
    <tr>
        <!-- Diminta Oleh -->
        <td style="width: 33.3%; border: 1.5px solid #000; padding: 4px; height: 65px; vertical-align: top; text-align: left;">
            Diminta Oleh :
            <div style="height: 30px; text-align: center; vertical-align: middle; margin-top: 5px; font-size: 8.5px; color: #555;">
                [TANDA TANGAN]
            </div>
            <div style="border-top: 1.5px solid #000; font-size: 9px; padding-top: 2px; margin-top: 2px;">
                Nama: {{ $atkRequest->nama }}
            </div>
        </td>
        
        <!-- Disetujui Oleh -->
        <td style="width: 33.3%; border: 1.5px solid #000; padding: 4px; height: 65px; vertical-align: top; text-align: left;">
            Disetujui Oleh :
            <div style="height: 30px; text-align: center; vertical-align: middle; margin-top: 5px; font-size: 9px; font-weight: bold; color: green;">
                @if($atkRequest->approved_by)
                    ✓ APPROVED DIGITAL
                @endif
            </div>
            <div style="border-top: 1.5px solid #000; font-size: 9px; padding-top: 2px; margin-top: 2px;">
                Nama: {{ $atkRequest->approver->name ?? '.......................' }}
            </div>
        </td>
        
        <!-- Diberikan Pada Tanggal -->
        <td style="width: 33.3%; border: 1.5px solid #000; padding: 4px; height: 65px; vertical-align: top; text-align: left;">
            Diberikan Pada Tanggal : {{ $atkRequest->diberikan_pada ? $atkRequest->diberikan_pada->format('d/m/Y') : '/  /' }}
            <div style="height: 30px; text-align: center; vertical-align: middle; margin-top: 5px; font-size: 8.5px; color: #555;">
                @if($atkRequest->diberikan_pada)
                    [DITERIMA]
                @endif
            </div>
            <div style="border-top: 1.5px solid #000; font-size: 9px; padding-top: 2px; margin-top: 2px;">
                Nama: {{ $atkRequest->receiver_name ?? '.......................' }}
            </div>
        </td>
    </tr>
</table>

<!-- Ketentuan Box -->
<div style="border: 1.5px solid #000; margin-top: 15px; padding: 6px; font-family: Arial, sans-serif; font-size: 9px; line-height: 1.35;">
    <strong>Ketentuan :</strong>
    <ol style="margin: 0; padding-left: 15px;">
        <li>Mohon diisi yang diperlukan selama 1 bulan (estimasi) Jika kurang dari 1 bulan ATK sudah habis dan atau ada keperluan mendadak (urgent) mengenai ATK mohon agar bisa menunggu priode bulan berikutnya.</li>
        <li>Pengisian Permintaan & Penyerahan Form Permintaan ATK paling lambat tanggal 28 disetiap bulannya.</li>
    </ol>
</div>
@endsection
