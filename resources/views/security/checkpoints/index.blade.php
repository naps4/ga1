@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="page-title-wrap">
            <h1 class="m-0 text-dark">Master Area Checkpoint Security</h1>
            <button class="btn btn-primary rounded-pill shadow-sm" data-toggle="modal" data-target="#addCheckpointModal">
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
                    $plantColorClass = $loop->iteration % 2 === 0 ? 'success' : 'primary';
                    $plantIcon = $loop->iteration % 2 === 0 ? 'fa-warehouse' : 'fa-industry';
                    $plantCheckpoints = $checkpoints->where('plant', $plantName);
                    $subAreas  = $plantCheckpoints->whereNotNull('sub_area')->pluck('sub_area')->unique()->values();
                    $noSubArea = $plantCheckpoints->whereNull('sub_area');
                @endphp
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        {{-- Header Area + Tombol + Sub Area --}}
                        <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold text-{{ $plantColorClass }} mb-0">
                                <i class="fas {{ $plantIcon }} mr-1"></i> Area {{ $plantName }}
                            </h3>
                            <button class="btn btn-xs btn-outline-{{ $plantColorClass }} rounded-pill px-3"
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
                                                <form action="{{ route('security.checkpoints.destroy', $cp->id) }}" method="POST"
                                                      class="d-inline" onsubmit="return confirm('Hapus area ini?')">
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
                                            <form action="{{ route('security.checkpoints.destroy', $cp->id) }}" method="POST"
                                                  class="d-inline" onsubmit="return confirm('Hapus area ini?')">
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
                                <div class="p-4 text-center text-muted">Belum ada checkpoint di {{ $plantName }}.</div>
                            @endif

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-8">
                    <div class="alert alert-info rounded-4 shadow-sm">
                        <i class="fas fa-info-circle mr-1"></i> Belum ada area checkpoint yang terdaftar. Klik <strong>Tambah Area Baru</strong> untuk memulainya.
                    </div>
                </div>
            @endforelse

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 rounded-4 bg-gradient-info text-white h-100">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-3"><i class="fas fa-info-circle mr-1"></i> Panduan Cetak QR Code</h5>
                        <p class="small mb-3">Setiap area membutuhkan QR Code/Barcode unik yang terpasang secara fisik di lokasi agar dapat dipindai oleh Security Guard selama melakukan patroli.</p>
                        <ol class="small pl-3 mb-0">
                            <li class="mb-2">Klik tombol <strong>QR</strong> di samping nama area.</li>
                            <li class="mb-2">Gunakan tombol <strong>Cetak Label</strong> untuk mencetak QR Code pada printer stiker/kertas.</li>
                            <li class="mb-2">Tempelkan kode tersebut pada titik checkpoint fisik di area bersangkutan.</li>
                            <li>Security Guard akan memindai QR Code tersebut menggunakan kamera HP.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Modal: Tambah Area / Checkpoint Baru --}}
<div class="modal fade" id="addCheckpointModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-map-marker-alt text-primary mr-1"></i> Tambah Area Checkpoint
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('security.checkpoints.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Plant / Gedung <span class="text-danger">*</span></label>
                        <input type="text" name="plant" id="add_plant" list="plantsList"
                               class="form-control border-light bg-light"
                               placeholder="Ketik atau pilih Plant/Gedung..." required autocomplete="off">
                        <datalist id="plantsList">
                            @foreach($existingPlants as $p)
                                <option value="{{ $p }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">
                            Sub Area <span class="text-muted font-weight-normal small">(Opsional)</span>
                        </label>
                        <input type="text" name="sub_area" id="add_sub_area" list="subAreaList"
                               class="form-control border-light bg-light"
                               placeholder="Contoh: Lantai 1, Gedung A, Zona B..." autocomplete="off">
                        <datalist id="subAreaList">
                            @foreach($existingSubAreas ?? [] as $sa)
                                <option value="{{ $sa }}">
                            @endforeach
                        </datalist>
                        <small class="form-text text-muted">Kosongkan jika tidak perlu sub-pengelompokan.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Nama Area / Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="area_name"
                               class="form-control border-light bg-light"
                               placeholder="Contoh: Office, Warehouse, QC" required>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark">Target Durasi Pengecekan (Menit)</label>
                        <input type="number" name="target_duration"
                               class="form-control border-light bg-light"
                               value="5" min="1" max="120" required>
                        <small class="form-text text-muted">Estimasi waktu patroli di area ini.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light text-right">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 mr-2" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Area</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: QR Code --}}
<div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-qrcode text-info mr-1"></i> QR Code Checkpoint</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5 text-center" id="printArea">
                <div class="p-3 border border-dashed rounded-4 d-inline-block bg-white shadow-sm mb-3">
                    <div id="qrcode-container" style="width:200px;height:200px;margin:0 auto;"></div>
                </div>
                <h4 class="font-weight-bold text-dark mt-2 mb-0" id="qrAreaName">Nama Area</h4>
                <p class="text-primary font-weight-bold" id="qrPlantName">Plant X</p>
                <div class="badge badge-light border px-3 py-2 mt-2">
                    <code class="font-weight-bold" id="qrCodeText" style="font-size:1.05rem;">CODE</code>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light text-center justify-content-center flex-wrap" style="gap:.4rem;">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary rounded-pill px-3" onclick="downloadSecurityQr()">
                    <i class="fas fa-download mr-1"></i> Download PNG
                </button>
                <button type="button" class="btn btn-info rounded-pill px-3" onclick="printQrLabel()">
                    <i class="fas fa-print mr-1"></i> Cetak Label QR
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Edit Checkpoint --}}
<div class="modal fade" id="editCheckpointModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-edit text-warning mr-1"></i> Edit Area Checkpoint
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editCheckpointForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Plant / Gedung <span class="text-danger">*</span></label>
                        <input type="text" name="plant" id="edit_plant" list="editPlantsList"
                               class="form-control" placeholder="Ketik atau pilih Plant/Gedung..." required autocomplete="off">
                        <datalist id="editPlantsList">
                            @foreach($existingPlants as $p)
                                <option value="{{ $p }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">
                            Sub Area <span class="text-muted font-weight-normal small">(Opsional)</span>
                        </label>
                        <input type="text" name="sub_area" id="edit_sub_area" list="editSubAreaList"
                               class="form-control" placeholder="Contoh: Lantai 1, Gedung A..." autocomplete="off">
                        <datalist id="editSubAreaList">
                            @foreach($existingSubAreas ?? [] as $sa)
                                <option value="{{ $sa }}">
                            @endforeach
                        </datalist>
                        <small class="form-text text-muted">Kosongkan jika tidak ada sub area.</small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-600 text-dark">Nama Area <span class="text-danger">*</span></label>
                        <input type="text" name="area_name" id="edit_area_name"
                               class="form-control" placeholder="Contoh: R&D, Ruang Supervisor, dll." required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark">Target Durasi (Menit) <span class="text-danger">*</span></label>
                        <input type="number" name="target_duration" id="edit_target_duration"
                               class="form-control" min="1" max="120" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 text-white">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    let currentQrCode = null;

    function showQrModal(code, name, plant) {
        $('#qrAreaName').text(name);
        $('#qrPlantName').text(plant);
        $('#qrCodeText').text(code);

        const container = document.getElementById('qrcode-container');
        container.innerHTML = '';
        currentQrCode = new QRCode(container, {
            text: code, width: 200, height: 200,
            colorDark: '#000000', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
        $('#qrCodeModal').modal('show');
    }

    function downloadSecurityQr() {
        const container = document.getElementById('qrcode-container');
        const canvas = container.querySelector('canvas');
        const img    = container.querySelector('img');
        let dataUrl = canvas ? canvas.toDataURL('image/png') : (img ? img.src : '');
        let codeText = document.getElementById('qrCodeText').innerText;

        if (dataUrl) {
            let link = document.createElement('a');
            link.download = 'QR-' + (codeText || 'checkpoint') + '.png';
            link.href = dataUrl;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert('Gagal mengunduh QR Code. Silakan coba lagi.');
        }
    }

    function printQrLabel() {
        const container = document.getElementById('qrcode-container');
        const canvas = container.querySelector('canvas');
        const img    = container.querySelector('img');
        let qrDataUrl = canvas ? canvas.toDataURL('image/png') : (img ? img.src : '');

        const areaName  = document.getElementById('qrAreaName').innerText;
        const plantName = document.getElementById('qrPlantName').innerText;
        const codeText  = document.getElementById('qrCodeText').innerText;

        const printWindow = window.open('', '_blank', 'width=500,height=600');
        printWindow.document.write(`
            <html><head><title>Cetak Label QR - ${areaName}</title>
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body { font-family:Arial,sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; background:#fff; }
                .label { text-align:center; border:2px dashed #ccc; border-radius:16px; padding:32px 40px; width:320px; }
                .label img { width:200px; height:200px; display:block; margin:0 auto 16px; }
                .label h2 { font-size:18px; font-weight:bold; color:#111; margin-bottom:4px; }
                .label p.plant { font-size:13px; color:#2563eb; font-weight:600; margin-bottom:12px; }
                .label .code { display:inline-block; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:8px; padding:6px 14px; font-family:monospace; font-size:15px; font-weight:bold; letter-spacing:1px; color:#334155; margin-top:4px; }
                @media print { body { min-height:auto; } .label { border-style:solid; } }
            </style></head>
            <body><div class="label">
                <img src="${qrDataUrl}" alt="QR ${codeText}">
                <h2>${areaName}</h2>
                <p class="plant">${plantName}</p>
                <div class="code">${codeText}</div>
            </div>
            <script>window.onload=function(){setTimeout(function(){window.print();window.close();},400);};<\/script>
            </body></html>`);
        printWindow.document.close();
    }

    function openEditModal(cp) {
        $('#editCheckpointForm').attr('action', `/security/checkpoints/${cp.id}`);
        $('#edit_plant').val(cp.plant);
        $('#edit_sub_area').val(cp.sub_area ?? '');
        $('#edit_area_name').val(cp.area_name);
        $('#edit_target_duration').val(cp.target_duration);
        $('#editCheckpointModal').modal('show');
    }

    // Pre-fill plant saat tombol "+ Sub Area" diklik dari card area
    function openAddSubAreaModal(plantName) {
        $('#add_plant').val(plantName);
        $('#add_sub_area').val('');
        $('#addCheckpointModal').modal('show');
        $('#addCheckpointModal').one('shown.bs.modal', function () {
            $('#add_sub_area').focus();
        });
    }
</script>
@endpush
