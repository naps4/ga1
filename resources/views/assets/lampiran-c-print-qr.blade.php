<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Stiker QR - {{ $location->room_name }} ({{ $location->plant->name ?? 'Plant ' . $location->plant_code }})</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        /* Top Action Bar (hidden when printing) */
        .top-toolbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        /* Container & Grid Layout */
        .print-container {
            max-width: 1040px;
            margin: 1.5rem auto;
            padding: 0 1rem;
        }
        .sticker-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        /* Physical Inventory Sticker Card */
        .asset-physical-sticker {
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 10px 12px;
            color: #0f172a;
            page-break-inside: avoid;
            break-inside: avoid;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 155px;
        }
        .sticker-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .sticker-company {
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #0f172a;
        }
        .sticker-system-badge {
            font-size: 7.5px;
            font-weight: 800;
            background: #0f172a;
            color: #ffffff;
            padding: 1.5px 5px;
            border-radius: 3px;
            letter-spacing: 0.04em;
        }
        .sticker-body {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-grow: 1;
        }
        .sticker-qr-wrap {
            background: #ffffff;
            padding: 2px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            width: 86px;
            height: 86px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sticker-qr-wrap canvas,
        .sticker-qr-wrap img {
            width: 82px !important;
            height: 82px !important;
            display: block;
        }
        .sticker-meta {
            flex-grow: 1;
            min-width: 0;
        }
        .sticker-code {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 10px;
            font-weight: 800;
            color: #1e40af;
            letter-spacing: 0.01em;
            word-break: break-all;
            line-height: 1.2;
            margin-bottom: 2px;
        }
        .sticker-title {
            font-size: 9.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sticker-details {
            font-size: 8px;
            color: #334155;
            line-height: 1.35;
        }
        .sticker-lbl {
            color: #64748b;
            font-weight: 600;
        }
        .sticker-foot {
            border-top: 1px dashed #94a3b8;
            margin-top: 6px;
            padding-top: 3px;
            text-align: center;
            font-size: 6.5px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #64748b;
        }

        /* Print Settings */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff;
                color: #000000;
            }
            .print-container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
            .sticker-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 5mm;
            }
            .asset-physical-sticker {
                border-color: #000000 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .sticker-code {
                color: #000000 !important;
            }
            .sticker-system-badge {
                background: #000000 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: A4;
                margin: 8mm 6mm;
            }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar (tidak ikut tercetak) --}}
    <div class="top-toolbar no-print">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <a href="{{ route('assets.lampiran-c', $location->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mr-3">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Lampiran C
                </a>
                <div>
                    <h5 class="mb-0 font-weight-bold text-dark">
                        <i class="fas fa-qrcode text-primary mr-1"></i> Cetak Label QR Ruangan: {{ $location->room_name }}
                    </h5>
                    <small class="text-muted">
                        {{ $location->plant->name ?? 'Plant ' . $location->plant_code }} • Dept: {{ $location->department->name ?? '-' }} ({{ $location->department->code ?? '-' }}) — 
                        <strong>Total: {{ $assets->count() }} Label Stiker Aset Fisik</strong>
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap: .5rem;">
                <span class="badge badge-success px-3 py-2 font-weight-bold" id="qrStatusBadge">
                    <i class="fas fa-check-circle mr-1"></i> <span id="qrCountLoaded">0</span>/{{ $assets->count() }} QR Siap
                </span>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm font-weight-bold" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Cetak Semua Stiker (Print)
                </button>
            </div>
        </div>
    </div>

    {{-- Container Grid Stiker --}}
    <div class="print-container">
        @if($assets->isEmpty())
            <div class="alert alert-warning text-center py-5 no-print" style="border-radius: 1rem;">
                <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                <h5 class="font-weight-bold">Tidak Ada Aset Fisik di Ruangan Ini</h5>
                <p class="mb-0 text-muted">Belum ada aset inventaris yang ditempatkan pada ruangan <strong>{{ $location->room_name }}</strong>.</p>
                <a href="{{ route('assets.lampiran-c', $location->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-3">
                    Kembali ke Lampiran C
                </a>
            </div>
        @else
            <div class="sticker-grid" id="stickerGrid">
                @foreach($assets as $index => $asset)
                    <div class="asset-physical-sticker">
                        <div class="sticker-head">
                            <div class="sticker-company">PT HERBATECH INNOPHARMA INDUSTRY</div>
                            <div class="sticker-system-badge">INVENTARIS GA</div>
                        </div>
                        <div class="sticker-body">
                            <div class="sticker-qr-wrap qr-bulk-item" data-code="{{ $asset->asset_code }}" id="qr-container-{{ $index }}">
                                {{-- QR Code SVG/Canvas rendered via JS --}}
                            </div>
                            <div class="sticker-meta">
                                <div class="sticker-code">{{ $asset->asset_code }}</div>
                                <div class="sticker-title" title="{{ $asset->item_name }}">{{ $asset->item_name }}</div>
                                <div class="sticker-details">
                                    <div><span class="sticker-lbl">Lokasi:</span> <strong>{{ $location->room_name }} ({{ $location->plant->name ?? 'Plant ' . $location->plant_code }})</strong></div>
                                    <div><span class="sticker-lbl">Departemen:</span> <strong>{{ $asset->department->code ?? ($location->department->code ?? '-') }}</strong></div>
                                    <div><span class="sticker-lbl">Tgl Beli:</span> <strong>{{ $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-' }}</strong> | <strong>{{ $asset->acquisition_source ?? 'RAB' }}</strong></div>
                                    <div>
                                        <span class="sticker-lbl">Tracking:</span> 
                                        @if($asset->tracking_mode === 'BULK')
                                            <span class="badge badge-info" style="font-size: 7.5px;">BULK (Qty: {{ $asset->qty }})</span>
                                        @else
                                            <span class="badge badge-dark" style="font-size: 7.5px;">INDIVIDUAL</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sticker-foot">
                            <span>* SCAN QR UNTUK VERIFIKASI ASLI INVENTARIS PERUSAHAAN *</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrItems = document.querySelectorAll('.qr-bulk-item');
            const baseUrl = "{{ url('/assets/scan') }}";
            let loadedCount = 0;

            qrItems.forEach((item, idx) => {
                const code = item.getAttribute('data-code');
                const targetUrl = baseUrl + '/' + encodeURIComponent(code);

                new QRCode(item, {
                    text: targetUrl,
                    width: 82,
                    height: 82,
                    colorDark: "#0f172a",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });

                loadedCount++;
            });

            const countBadge = document.getElementById('qrCountLoaded');
            if (countBadge) {
                countBadge.innerText = loadedCount;
            }
        });
    </script>
</body>
</html>

