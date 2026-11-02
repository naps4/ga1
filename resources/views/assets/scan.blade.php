<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Aset - {{ $asset->asset_code }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #334155;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .asset-card {
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .card-header-brand {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            padding: 1.5rem 1.75rem;
            text-align: center;
        }
        .qr-badge-box {
            background: #ffffff;
            border: 3px solid #e2e8f0;
            border-radius: 1rem;
            padding: 0.75rem;
            display: inline-block;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-top: -3.5rem;
        }
        .code-pill {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 0.4rem 0.85rem;
            border-radius: 0.5rem;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            font-size: 1.05rem;
            color: #1e40af;
            letter-spacing: 0.04em;
            word-break: break-all;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.55rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
        }
        .info-value {
            font-weight: 700;
            color: #1e293b;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="asset-card">
        <div class="card-header-brand">
            <div class="small text-uppercase font-weight-bold" style="letter-spacing: 0.1em; opacity: 0.85;">
                PT Herbatech Innopharma Industry
            </div>
            <h5 class="mb-0 font-weight-bold mt-1">
                <i class="fas fa-boxes mr-1"></i> Verifikasi Inventaris GA
            </h5>
            <small class="d-block mt-1 text-white-50">Sistem Informasi Manajemen Aset Terpadu</small>
        </div>

        <div class="text-center">
            <div class="qr-badge-box">
                <div id="scanQrcode"></div>
            </div>
        </div>

        <div class="p-4 pt-2">
            <div class="text-center mb-3">
                <div class="code-pill mb-2">
                    {{ $asset->asset_code }}
                </div>
                <h4 class="font-weight-bold text-dark mb-1">{{ $asset->item_name }}</h4>
                @if($asset->brand)
                    <span class="badge badge-light border text-muted px-2 py-1"><i class="fas fa-tag mr-1"></i>{{ $asset->brand }}</span>
                @endif
                <span class="badge badge-{{ $asset->status === 'AKTIF' ? 'success' : 'danger' }} px-2 py-1">
                    <i class="fas fa-check-circle mr-1"></i>Status: {{ $asset->status }}
                </span>
                <span class="badge badge-info px-2 py-1 font-weight-bold">
                    {{ $asset->tracking_mode ?? 'INDIVIDUAL' }}
                </span>
            </div>

            <div class="bg-light p-3 rounded mb-3 border">
                <div class="info-row">
                    <span class="info-label">Lokasi Plant Fisik</span>
                    <span class="info-value">
                        @if($asset->current_plant_code === '01')
                            Plant A (01)
                        @elseif($asset->current_plant_code === '02')
                            Plant B (02)
                        @else
                            Mobile (Lintas Plant)
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ruangan / Sub-Area</span>
                    <span class="info-value">{{ $asset->location->room_name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Departemen</span>
                    <span class="info-value">{{ $asset->department->name ?? '-' }} ({{ $asset->department->code ?? '-' }})</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kategori Aset</span>
                    <span class="info-value">{{ $asset->category->code ?? '-' }} - {{ $asset->category->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kondisi Fisik</span>
                    <span class="info-value">
                        <span class="badge badge-{{ $asset->condition === 'Baik' ? 'success' : ($asset->condition === 'Rusak' ? 'danger' : 'warning') }}">
                            {{ $asset->condition }}
                        </span>
                        <span class="badge badge-light border">
                            {{ $asset->initial_condition ?? 'Baru' }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jumlah (Qty)</span>
                    <span class="info-value">{{ number_format($asset->qty, 0, ',', '.') }} Unit</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Pembelian</span>
                    <span class="info-value">{{ $asset->purchase_date ? $asset->purchase_date->format('d F Y') : '-' }}</span>
                </div>
                @if($asset->specification)
                <div class="info-row">
                    <span class="info-label">Spesifikasi</span>
                    <span class="info-value" style="max-width: 60%;">{{ $asset->specification }}</span>
                </div>
                @endif
                @if(auth()->check())
                <div class="info-row">
                    <span class="info-label">Nilai Satuan (Rp)</span>
                    <span class="info-value text-success">Rp {{ number_format($asset->unit_price, 0, ',', '.') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Nilai (Rp)</span>
                    <span class="info-value text-success font-weight-bold">Rp {{ number_format($asset->total_price, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>

            <div class="text-center">
                @if(auth()->check())
                    <a href="{{ route('assets.index') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-boxes mr-1"></i> Buka Dashboard Aset
                    </a>
                @else
                    <small class="text-muted d-block">
                        <i class="fas fa-shield-alt text-success mr-1"></i> QR Code Diverifikasi oleh PT Herbatech Innopharma Industry
                    </small>
                @endif
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
    <script>
        new QRCode(document.getElementById("scanQrcode"), {
            text: "{{ url('/assets/scan/' . $asset->asset_code) }}",
            width: 100,
            height: 100,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });
    </script>
</body>
</html>

