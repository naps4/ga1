@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0 text-dark">Master Area Checkpoint Kebersihan</h1>
            <button class="btn btn-teal rounded-pill shadow-sm text-white" style="background-color: #0d9488; border-color: #0d9488;" data-toggle="modal" data-target="#addCheckpointModal">
                <i class="fas fa-plus mr-1"></i> Tambah Area Baru
            </button>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            @forelse($existingPlants as $plantName)
                @php
                    $plantColorClass = $loop->iteration % 2 === 0 ? 'teal' : 'info';
                    $plantIcon = $loop->iteration % 2 === 0 ? 'fa-broom' : 'fa-building';
                    $plantCheckpoints = $checkpoints->where('plant', $plantName);
                    $subAreas  = $plantCheckpoints->whereNotNull('sub_area')->pluck('sub_area')->unique()->values();
                    $noSubArea = $plantCheckpoints->whereNull('sub_area');
                @endphp
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        {{-- Header Area + Tombol + Sub Area --}}
                        <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold text-dark mb-0" style="color: #0d9488 !important;">
                                <i class="fas {{ $plantIcon }} mr-1"></i> Area {{ $plantName }}
                            </h3>
                            <button class="btn btn-xs btn-outline-info rounded-pill px-3"
                                    onclick="openAddSubAreaModal('{{ addslashes($plantName) }}')"
                                    title="Tambah Sub Area / Checkpoint di area ini">
                                <i class="fas fa-folder-plus mr-1"></i> + Sub Area
                            </button>
                        </div>

                        <div class="card-body p-0">

                            {{-- Checkpoints DENGAN sub_area --}}
                            @foreach($subAreas as $subAreaName)
                                <div class="border-bottom border-light">
                                    <div class="d-flex align-items-center px-3 py-2 bg-light">
                                        <small class="font-weight-bold text-secondary">
                                            <i class="fas fa-folder-open mr-1 text-warning"></i>
                                            {{ $subAreaName }}
                                        </small>
                                    </div>
                                    @foreach($plantCheckpoints->where('sub_area', $subAreaName) as $cp)
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 pl-4 border-0 border-bottom border-light">
                                            <div>
                                                <h6 class="mb-0 font-weight-bold text-dark" style="font-size:.88rem;">{{ $cp->area_name }}</h6>
                                                <small class="text-muted">
                                                    <code class="bg-light px-2 rounded">{{ $cp->code }}</code>
                                                    &bull; Durasi: {{ $cp->target_duration }} mnt
                                                </small>
                                            </div>
                                            <div class="d-flex align-items-center" style="gap:.4rem;">
                                                <button class="btn btn-xs btn-outline-info rounded-pill px-2"
                                                        onclick="showQrModal('{{ $cp->code }}', '{{ $cp->area_name }}', '{{ $cp->plant }}')">
                                                    <i class="fas fa-qrcode"></i> QR
                                                </button>
                                                <button class="btn btn-xs btn-outline-warning rounded-pill px-2"
                                                        onclick="openEditModal({{ json_encode($cp) }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('cleaning.checkpoints.destroy', $cp->id) }}" method="POST"
                                                      class="d-inline" onsubmit="return confirm('Hapus area kebersihan ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            {{-- Checkpoints TANPA sub_area --}}
                            @if($noSubArea->count() > 0)
                                @if($subAreas->count() > 0)
                                    <div class="px-3 py-1 bg-light border-bottom border-light">
                                        <small class="font-weight-bold text-secondary">
                                            <i class="fas fa-map-marker-alt mr-1 text-muted"></i> Tanpa Sub Area
                                        </small>
                                    </div>
                                @endif
                                @foreach($noSubArea as $cp)
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 border-0 border-bottom border-light">
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark" style="font-size:.88rem;">{{ $cp->area_name }}</h6>
                                            <small class="text-muted">
                                                <code class="bg-light px-2 rounded">{{ $cp->code }}</code>
                                                &bull; Durasi: {{ $cp->target_duration }} mnt
                                            </small>
                                        </div>
                                        <div class="d-flex align-items-center" style="gap:.4rem;">
                                            <button class="btn btn-xs btn-outline-info rounded-pill px-2"
                                                    onclick="showQrModal('{{ $cp->code }}', '{{ $cp->area_name }}', '{{ $cp->plant }}')">
                                                <i class="fas fa-qrcode"></i> QR
                                            </button>
                                            <button class="btn btn-xs btn-outline-warning rounded-pill px-2"
                                                    onclick="openEditModal({{ json_encode($cp) }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('cleaning.checkpoints.destroy', $cp->id) }}" method="POST"
                                                  class="d-inline" onsubmit="return confirm('Hapus area kebersihan ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if($plantCheckpoints->count() === 0)
                                <div class="p-4 text-center text-muted">Belum ada checkpoint kebersihan di {{ $plantName }}.</div>
                            @endif

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-8">
                    <div class="alert alert-info rounded-4 shadow-sm">
                        <i class="fas fa-info-circle mr-1"></i> Belum ada area checkpoint kebersihan terdaftar. Klik <strong>Tambah Area Baru</strong> untuk memulainya.
                    </div>
                </div>
            @endforelse

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 rounded-4 text-white h-100" style="background: linear-gradient(135deg, #0d9488, #0f766e);">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-3"><i class="fas fa-info-circle mr-1"></i> Panduan QR Code Kebersihan</h5>
                        <p class="small mb-3">Setiap lokasi membutuhkan QR Code unik yang ditempel secara fisik di area kebersihan agar petugas *Cleaning Service* dapat memindai saat melakukan pembersihan & pengecekan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Modal Tambah Checkpoint Baru --}}
<div class="modal fade" id="addCheckpointModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header text-white" style="background-color: #0d9488;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-1"></i> Tambah Area Checkpoint Kebersihan</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cleaning.checkpoints.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Plant / Lokasi Utama <span class="text-danger">*</span></label>
                        <input type="text" name="plant" id="add_plant" class="form-control rounded-pill" placeholder="Contoh: Plant A, Office Floor 2" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Sub Area / Zona <small class="text-muted">(Opsional)</small></label>
                        <input type="text" name="sub_area" id="add_sub_area" class="form-control rounded-pill" placeholder="Contoh: Area Toilet, Ruang Rapat">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Checkpoint / Titik Kebersihan <span class="text-danger">*</span></label>
                        <input type="text" name="area_name" class="form-control rounded-pill" placeholder="Contoh: Washtafel & Cermin, Pantry Pria" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Target Durasi Pengecekan (Menit) <span class="text-danger">*</span></label>
                        <input type="number" name="target_duration" class="form-control rounded-pill" value="5" min="1" max="120" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #0d9488;">Simpan Area</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Checkpoint --}}
<div class="modal fade" id="editCheckpointModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-1"></i> Edit Area Checkpoint Kebersihan</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCheckpointForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Plant / Lokasi Utama</label>
                        <input type="text" name="plant" id="edit_plant" class="form-control rounded-pill" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Sub Area / Zona</label>
                        <input type="text" name="sub_area" id="edit_sub_area" class="form-control rounded-pill">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Checkpoint</label>
                        <input type="text" name="area_name" id="edit_area_name" class="form-control rounded-pill" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Target Durasi (Menit)</label>
                        <input type="number" name="target_duration" id="edit_target_duration" class="form-control rounded-pill" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 font-weight-bold">Update Area</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Preview QR Code --}}
<div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content border-0 shadow rounded-4 text-center">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title font-weight-bold w-100" id="qrModalTitle">QR Code Area</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div id="qrCodeContainer" class="d-flex justify-content-center p-3 bg-white rounded-3 border border-dashed mb-3"></div>
                <code id="qrModalCode" class="d-block font-weight-bold bg-light p-2 rounded mb-1" style="font-size:1.1rem; letter-spacing: 0.05em;"></code>
                <small id="qrModalPlant" class="text-muted d-block font-weight-600"></small>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0 pb-4 flex-wrap" style="gap: .4rem;">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-sm text-white rounded-pill px-3" style="background-color: #0d9488;" onclick="downloadCleaningQr()">
                    <i class="fas fa-download mr-1"></i> Download PNG
                </button>
                <button type="button" class="btn btn-sm btn-info rounded-pill px-3" onclick="printCleaningQr()">
                    <i class="fas fa-print mr-1"></i> Cetak Label
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    let currentQrCode = '';
    let currentAreaName = '';
    let currentPlantName = '';

    function openAddSubAreaModal(plantName) {
        document.getElementById('add_plant').value = plantName;
        document.getElementById('add_sub_area').value = '';
        $('#addCheckpointModal').modal('show');
    }

    function openEditModal(cp) {
        let form = document.getElementById('editCheckpointForm');
        form.action = '/cleaning/checkpoints/' + cp.id;
        document.getElementById('edit_plant').value = cp.plant;
        document.getElementById('edit_sub_area').value = cp.sub_area || '';
        document.getElementById('edit_area_name').value = cp.area_name;
        document.getElementById('edit_target_duration').value = cp.target_duration;
        $('#editCheckpointModal').modal('show');
    }

    function showQrModal(code, areaName, plant) {
        currentQrCode = code;
        currentAreaName = areaName;
        currentPlantName = plant;

        document.getElementById('qrModalTitle').innerText = areaName;
        document.getElementById('qrModalCode').innerText = code;
        document.getElementById('qrModalPlant').innerText = 'Plant: ' + plant;
        
        let container = document.getElementById('qrCodeContainer');
        container.innerHTML = '';
        new QRCode(container, {
            text: code,
            width: 190,
            height: 190
        });

        $('#qrModal').modal('show');
    }

    function downloadCleaningQr() {
        let container = document.getElementById('qrCodeContainer');
        let img = container.querySelector('img');
        let canvas = container.querySelector('canvas');
        let dataUrl = '';
        
        if (canvas) {
            dataUrl = canvas.toDataURL("image/png");
        } else if (img) {
            dataUrl = img.src;
        }

        if (dataUrl) {
            let link = document.createElement('a');
            link.download = 'QR-' + (currentQrCode || 'checkpoint') + '.png';
            link.href = dataUrl;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert('Gagal mengunduh QR Code. Silakan coba lagi.');
        }
    }

    function printCleaningQr() {
        let container = document.getElementById('qrCodeContainer');
        let img = container.querySelector('img') || container.querySelector('canvas');
        let imgSrc = img ? (img.src || img.toDataURL('image/png')) : '';
        let title = currentAreaName;
        let code = currentQrCode;
        let plant = currentPlantName;

        let printWindow = window.open('', '_blank', 'width=600,height=700');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cetak QR - ${code}</title>
                <style>
                    body { font-family: 'Segoe UI', Arial, sans-serif; text-align: center; padding: 40px; margin: 0; background-color: #f8fafc; }
                    .qr-card { border: 3px solid #0d9488; border-radius: 16px; padding: 30px; display: inline-block; background: #ffffff; max-width: 360px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
                    .header-title { font-size: 13px; font-weight: 800; color: #0d9488; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
                    .area-title { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 18px; word-break: break-word; }
                    .qr-img { width: 220px; height: 220px; margin: 0 auto 15px auto; display: block; }
                    .code-box { background: #f1f5f9; padding: 8px 16px; border-radius: 8px; font-family: monospace; font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 10px; display: inline-block; }
                    .plant-name { font-size: 14px; color: #64748b; font-weight: 600; }
                </style>
            </head>
            <body>
                <div class="qr-card">
                    <div class="header-title">📌 CHECKPOINT KEBERSIHAN</div>
                    <div class="area-title">${title}</div>
                    <img class="qr-img" src="${imgSrc}" alt="QR Code" />
                    <div class="code-box">${code}</div>
                    <div class="plant-name">Plant: ${plant}</div>
                </div>
                <script>
                    setTimeout(function() { window.print(); window.close(); }, 400);
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>
@endsection
