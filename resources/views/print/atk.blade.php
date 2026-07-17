@extends('layouts.print')

@section('title', (($atkRequest->request_type ?? 'ATK') === 'Rumah Tangga' ? 'RT-' : 'ATK-') . str_pad($atkRequest->id, 4, '0', STR_PAD_LEFT))

@section('custom_header')
    <!-- Override the default kop surat to match the scanned paper form -->
@endsection

@section('content')
    <style>
        body,
        .page-a4 {
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

        .atk-print-table th,
        .atk-print-table td {
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
    <table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 10px; table-layout: fixed;">
        <tr>
            <td style="width: 58%; border: none; vertical-align: bottom; padding-bottom: 5px;">
                @php
                    $logo = \App\Helpers\SettingsHelper::get('app_logo');
                    $logoPath = $logo ? storage_path('app/public/' . $logo) : null;
                @endphp
                @if($logo && $logoPath && file_exists($logoPath))
                    <img src="{{ asset('storage/' . $logo) }}"
                        style="max-height: 55px; max-width: 220px; display: block; margin-bottom: 5px;">
                @else
                    <div style="font-weight: 900; font-size: 15px; color: #000; letter-spacing: 0.5px;">
                        {{ \App\Helpers\SettingsHelper::get('company_name', 'HERBATECH') }}
                    </div>
                @endif
            </td>
            <td style="width: 38%; border: 1.5px solid #000; padding: 6px; font-family: Arial, sans-serif;">
                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                    <tr style="font-size: 10px; height: 14px;">
                        <td style="width: 55%; padding: 0; border: none;">Tanggal Pengajuan Barang</td>
                        <td style="width: 45%; padding: 0; border: none;">:
                            {{ $atkRequest->tanggal_pengajuan->format('d/m/Y') }}</td>
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
                        <td style="padding: 0; border: none;">: <span
                                style="font-weight: bold;">{{ $atkRequest->periode }}</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Title -->
    <div style="text-align: center; margin-top: 15px; margin-bottom: 15px;">
        <h5 style="margin: 0; font-weight: bold; font-size: 13px; text-decoration: underline; letter-spacing: 0.5px;">
            FORM PERMINTAAN
            {{ ($atkRequest->request_type ?? 'ATK') === 'Rumah Tangga' ? 'KEBUTUHAN RUMAH TANGGA' : 'ATK (ALAT TULIS KANTOR)' }}
        </h5>
    </div>

    <!-- Main Selected Items Table -->
    @php
        $isHousehold = ($atkRequest->request_type ?? 'ATK') === 'Rumah Tangga';
        if ($isHousehold) {
            $orderedNames = collect($householdItems)->pluck('name')->toArray();
            $itemsByName = collect($householdItems)->keyBy('name');
        } else {
            // Let's use the full list of ATK standard items
            $orderedNames = collect($standardItems)->pluck('name')->toArray();
            $itemsByName = collect($standardItems)->keyBy('name');
        }
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

            <!-- Render Standard Items with Quantity > 0 (that are not rejected) -->
            @foreach($orderedNames as $name)
                @php
                    $dbItem = $itemsMap[$name] ?? null;
                    $qty = $dbItem ? $dbItem['quantity'] : 0;
                @endphp
                @if($qty > 0)
                    @php
                        $itemConfig = $itemsByName->get($name);
                        $unit = $itemConfig ? $itemConfig['unit'] : 'Pcs';
                        $options = $itemConfig && isset($itemConfig['options']) ? $itemConfig['options'] : [];

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

            <!-- Render Custom / Unlisted Items (that are not rejected) -->
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
                        Tidak ada barang yang disetujui untuk dicetak.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div style="font-size: 8.5px; font-style: italic; margin-top: 2px;">
        * coret yang tidak diperlukan
    </div>

    <!-- Footer Signatures -->
    <table
        style="width: 100%; border-collapse: collapse; margin-top: 20px; font-family: Arial, sans-serif; font-size: 9.5px;">
        <tr>
            <!-- Diminta Oleh -->
            <td
                style="width: 25%; border: 1.5px solid #000; padding: 4px; height: 65px; vertical-align: top; text-align: left;">
                Diminta Oleh :
                <div style="height: 32px; text-align: center; vertical-align: middle; margin-top: 3px;">
                    @if($atkRequest->user && $atkRequest->user->signature)
                        <img src="{{ $atkRequest->user->signature_url }}" alt="TTD Pemohon"
                            style="max-height: 30px; display: block; margin: 0 auto;">
                    @else
                        <div
                            style="height: 25px; text-align: center; vertical-align: middle; margin-top: 5px; font-size: 8px; color: #777;">
                            [TANDA TANGAN]
                        </div>
                    @endif
                </div>
                <div style="border-top: 1.5px solid #000; font-size: 9px; padding-top: 2px; margin-top: 2px;">
                    Nama: {{ $atkRequest->nama }}
                </div>
            </td>

            <!-- Diketahui Oleh (Manager) -->
            <td
                style="width: 25%; border: 1.5px solid #000; padding: 4px; height: 65px; vertical-align: top; text-align: left;">
                Diketahui Oleh (Manager) :
                <div style="height: 32px; text-align: center; vertical-align: middle; margin-top: 3px;">
                    @if($atkRequest->manager_approved_by)
                        @if($atkRequest->managerApprover && $atkRequest->managerApprover->signature)
                            <img src="{{ $atkRequest->managerApprover->signature_url }}" alt="TTD Manager"
                                style="max-height: 30px; display: block; margin: 0 auto;">
                        @else
                            <div
                                style="border: 1px dashed #0284c7; color: #0284c7; padding: 2px; display: inline-block; font-size: 7.5px; border-radius: 3px; font-weight: bold; transform: rotate(-1deg); background-color: rgba(2, 132, 199, 0.05); font-family: monospace; line-height: 1.1; text-align: center; width: 95%;">
                                ✓ APPROVED DIGITAL
                            </div>
                        @endif
                    @endif
                </div>
                <div style="border-top: 1.5px solid #000; font-size: 9px; padding-top: 2px; margin-top: 2px;">
                    Nama: {{ $atkRequest->managerApprover->name ?? '.......................' }}
                </div>
            </td>

            <!-- Disetujui Oleh (GA) -->
            <td
                style="width: 25%; border: 1.5px solid #000; padding: 4px; height: 65px; vertical-align: top; text-align: left;">
                Disetujui Oleh (GA) :
                <div style="height: 32px; text-align: center; vertical-align: middle; margin-top: 3px;">
                    @if($atkRequest->approved_by)
                        @if($atkRequest->approver && $atkRequest->approver->signature)
                            <img src="{{ $atkRequest->approver->signature_url }}" alt="TTD Admin GA"
                                style="max-height: 30px; display: block; margin: 0 auto;">
                        @else
                            <div
                                style="border: 1px dashed green; color: green; padding: 2px; display: inline-block; font-size: 7.5px; border-radius: 3px; font-weight: bold; transform: rotate(-1deg); background-color: rgba(0, 128, 0, 0.05); font-family: monospace; line-height: 1.1; text-align: center; width: 95%;">
                                ✓ APPROVED DIGITAL
                            </div>
                        @endif
                    @endif
                </div>
                <div style="border-top: 1.5px solid #000; font-size: 9px; padding-top: 2px; margin-top: 2px;">
                    Nama: {{ $atkRequest->approver->name ?? '.......................' }}
                </div>
            </td>

            <!-- Diberikan Pada Tanggal / Penerima -->
            <td
                style="width: 25%; border: 1.5px solid #000; padding: 4px; height: 65px; vertical-align: top; text-align: left;">
                Diterima Oleh :
                <div
                    style="height: 30px; text-align: center; vertical-align: middle; margin-top: 5px; font-size: 8.5px; color: #555;">
                    @if($atkRequest->diberikan_pada)
                        Tanggal: {{ $atkRequest->diberikan_pada->format('d/m/Y') }}
                    @endif
                </div>
                <div style="border-top: 1.5px solid #000; font-size: 9px; padding-top: 2px; margin-top: 2px;">
                    Nama: {{ $atkRequest->receiver_name ?? '.......................' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Ketentuan Box -->
    <div
        style="border: 1.5px solid #000; margin-top: 15px; padding: 6px; font-family: Arial, sans-serif; font-size: 9px; line-height: 1.35;">
        <strong>Ketentuan :</strong>
        @if(($atkRequest->request_type ?? 'ATK') === 'Rumah Tangga')
            <ol style="margin: 0; padding-left: 15px;">
                <li>Mohon diisi kebutuhan selama 1 bulan (estimasi).</li>
                <li>Pengisian Permintaan & Penyerahan Form paling lambat tanggal 28 disetiap bulannya.</li>
            </ol>
        @else
            <ol style="margin: 0; padding-left: 15px;">
                <li>Mohon diisi yang diperlukan selama 1 bulan (estimasi) Jika kurang dari 1 bulan ATK sudah habis dan atau ada
                    keperluan mendadak (urgent) mengenai ATK mohon agar bisa menunggu priode bulan berikutnya.</li>
                <li>Pengisian Permintaan & Penyerahan Form Permintaan ATK paling lambat tanggal 28 disetiap bulannya.</li>
            </ol>
        @endif
    </div>
@endsection