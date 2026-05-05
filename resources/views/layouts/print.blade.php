<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cetak Dokumen') - Work Order GA</title>
    <!-- Include Bootstrap CSS for layouting -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #525659; /* Latar belakang abu-abu seperti PDF viewer */
            font-family: 'Times New Roman', Times, serif; /* Font resmi dokumen */
            color: #000;
        }
        .page-a4 {
            width: 21cm;
            min-height: 29.7cm;
            padding: 2cm;
            margin: 1cm auto;
            border: 1px #D3D3D3 solid;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .kop-surat {
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-surat h2 {
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .kop-surat p {
            margin: 0;
            font-size: 14px;
        }
        .doc-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .doc-number {
            text-align: center;
            margin-bottom: 30px;
        }
        .table-print {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .table-print th, .table-print td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 14px;
        }
        .table-print th {
            background-color: #f2f2f2;
        }
        .table-borderless th, .table-borderless td {
            border: none;
            padding: 5px;
        }
        .signature-box {
            margin-top: 50px;
            width: 100%;
        }
        .signature-col {
            text-align: center;
            width: 25%;
            float: left;
        }
        .signature-space {
            height: 80px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        
        @media print {
            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }
            .page-a4 {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
                padding: 0; /* Margin diatur dari browser */
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Tombol untuk trigger print manual jika onload gagal/diblokir -->
    <div class="text-center mt-3 mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary shadow">🖨️ Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn btn-secondary shadow">Tutup</button>
    </div>

    <div class="page-a4">
        @hasSection('custom_header')
            @yield('custom_header')
        @else
            <div class="kop-surat text-center">
                <h2>{{ \App\Helpers\SettingsHelper::get('company_name', 'PT. GENERAL AFFAIR SYSTEM') }}</h2>
                <p>{{ \App\Helpers\SettingsHelper::get('company_address', 'Jl. Contoh Perusahaan No. 123, Gedung Perkantoran Lt. 4, Jakarta Pusat') }}</p>
                <p>Telp: {{ \App\Helpers\SettingsHelper::get('company_phone', '(021) 1234567') }} | Email: {{ \App\Helpers\SettingsHelper::get('company_email', 'info@gasystem.com') }}</p>
            </div>
        @endif

        @yield('content')

    </div>

</body>
</html>
