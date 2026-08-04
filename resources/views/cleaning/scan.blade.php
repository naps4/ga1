@extends('layouts.app')

@section('content')
<div class="content-header px-2">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.5rem;"><i class="fas fa-broom mr-1" style="color: #0d9488;"></i> Scan Checkpoint Kebersihan</h1>
        <p class="text-muted small mb-0">
            Lakukan pemindaian barcode area untuk memverifikasi kehadiran dan mencatat laporan kebersihan.
            @if($assignedPlant)
                &nbsp;<span class="badge badge-success rounded-pill px-3 py-1">
                    <i class="fas fa-building mr-1"></i> Penempatan: {{ $assignedPlant }}
                </span>
            @endif
        </p>
    </div>
</div>

<section class="content px-2">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <!-- Left Side: Scanner & Form -->
            <div class="col-lg-7 mb-4">
                <!-- Scanner Card -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom border-light py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="font-weight-bold mb-0 text-dark" style="font-size:.9rem;">
                                <i class="fas fa-camera mr-1" style="color: #0d9488;"></i> Pemindai Barcode / QR Code Kebersihan
                            </h6>
                            <button class="btn btn-sm rounded-pill px-3 py-1" id="toggleScannerBtn"
                                    style="font-size:.78rem; background:#fef2f2; color:#dc2626; border:1px solid #fecaca;">
                                <i class="fas fa-stop mr-1"></i> Matikan Kamera
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 bg-dark position-relative"
                         style="min-height: 220px; display: flex; align-items: center; justify-content: center;">
                        <!-- HTML5 QR Scanner target container -->
                        <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto; background: #1f2d3d;"></div>

                        {{-- Placeholder saat scan berhasil --}}
                        <div id="scannerPlaceholder" class="d-none"
                             style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; background:rgba(15,23,42,0.92); z-index:10;">
                            <div id="scanSuccessBadge" class="d-none mb-3 text-center">
                                <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px">
                                    <i class="fas fa-check fa-lg text-white"></i>
                                </div>
                                <p class="text-success font-weight-bold mt-2 mb-0 small" id="scannedCodeLabel"></p>
                            </div>
                            <button type="button" class="btn text-white rounded-pill px-4 py-2" id="nextScanBtn"
                                    style="font-size:.85rem; background-color: #0d9488;">
                                <i class="fas fa-camera mr-1"></i> Scan Area Berikutnya
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Placeholder 'belum scan' --}}
                <div id="waitingScanCard" class="card shadow-sm border-0 rounded-4 mb-4"
                     style="border: 2px dashed rgba(13,148,136,0.3) !important; background: rgba(13,148,136,0.04);">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-qrcode fa-4x" style="color: rgba(13,148,136,0.35);"></i>
                        </div>
                        <h6 class="font-weight-bold text-muted mb-1">Belum ada area kebersihan yang dipindai</h6>
                        <p class="text-muted small mb-0">Arahkan kamera ke QR Code area kebersihan untuk mencatat laporan kondisi area.</p>
                    </div>
                </div>

                <!-- Form Checkpoint Log -->
                <div class="card shadow-sm border-0 rounded-4 d-none" id="formLaporanCard">
                    <div class="card-header bg-white border-bottom border-light pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0 text-dark">
                                <i class="fas fa-file-signature text-teal mr-1" style="color:#0d9488;"></i> Form Laporan Kebersihan Area
                            </h5>
                            <span class="badge text-white rounded-pill px-3 py-2" id="formAreaBadge" style="font-size: .85rem; background-color:#0d9488;">
                                <i class="fas fa-map-marker-alt mr-1"></i> <span id="formAreaName">-</span>
                            </span>
                        </div>
                    </div>
                    <form action="{{ route('cleaning.submit') }}" method="POST" enctype="multipart/form-data" id="checkpointForm">
                        @csrf
                        <div class="card-body p-4">
                            
                            <!-- Timer & Duration -->
                            <div class="alert alert-info rounded-pill shadow-none d-flex justify-content-between align-items-center mb-4 py-2 px-3" style="background-color: rgba(13, 148, 136, 0.08); border-color: rgba(13, 148, 136, 0.12);">
                                <span class="small font-weight-bold" style="color:#0d9488;"><i class="fas fa-stopwatch mr-1"></i> Waktu Pembersihan Aktif:</span>
                                <span class="badge text-white rounded-pill font-weight-bold px-3 py-1" id="timerDisplay" style="font-size: .95rem; background-color:#0d9488;">00:00</span>
                                <input type="hidden" name="duration_minutes" id="durationMinutes" value="0">
                            </div>

                            <!-- Opsi Eviden Report -->
                            <div class="form-group mb-4 bg-light p-3 rounded-4">
                                <div class="custom-control custom-switch custom-switch-md">
                                    <input type="checkbox" class="custom-control-input" id="is_evident_report" name="is_evident_report" value="1">
                                    <label class="custom-control-label font-weight-bold text-dark" for="is_evident_report">Kirim Laporan Temuan Kebersihan Khusus (Eviden Report)</label>
                                </div>
                                <small class="form-text text-muted ml-5 mt-1">Gunakan ini jika Anda menemukan temuan kotor/sampah di luar area checkpoint biasa. <strong>Foto selfie tidak diwajibkan</strong>.</small>
                            </div>

                            <!-- Area Code -->
                            <div class="form-group mb-3">
                                <label class="font-weight-600 text-dark">Kode Area Kebersihan</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text border-light bg-light"><i class="fas fa-qrcode text-muted"></i></span>
                                    </div>
                                    <input type="text" name="barcode_code" id="barcode_code"
                                           class="form-control border-light bg-light font-weight-bold"
                                           placeholder="Arahkan kamera ke QR Code..."
                                           required readonly>
                                </div>
                            </div>

                            <!-- Shift -->
                            <div class="form-group mb-3">
                                <label class="font-weight-600 text-dark">Jadwal Shift</label>
                                <select name="shift" class="form-control border-light bg-light" required>
                                    @foreach($shifts as $s)
                                        <option value="{{ $s->name }}" {{ $currentShift === $s->name ? 'selected' : '' }}>
                                            {{ $s->name }} ({{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Kebersihan -->
                            <div class="form-group mb-3">
                                <label class="font-weight-600 text-dark">Status Hasil Pengecekan Area <span class="text-danger">*</span></label>
                                <select name="status" id="statusSelect" class="form-control border-light" required>
                                    <option value="Bersih & Rapi">Bersih & Rapi (Default)</option>
                                    <option value="Sangat Bersih">Sangat Bersih & Kinclong</option>
                                    <option value="Kotor / Perlu Pembersihan">Kotor / Perlu Pembersihan Ekstra</option>
                                    <option value="Bau Tidak Sedap">Bau Tidak Sedap</option>
                                    <option value="Fasilitas / Sanitari Rusak">Fasilitas / Sanitari Rusak</option>
                                    <option value="Tempat Sampah Penuh">Tempat Sampah Penuh</option>
                                </select>
                            </div>

                            <!-- Upload Selfie Area -->
                            <div class="form-group mb-3" id="selfieGroup">
                                <label class="font-weight-600 text-dark">
                                    <i class="fas fa-portrait text-info mr-1"></i> Foto Selfie di Area <span class="text-danger">*</span>
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="selfieInput" name="selfie" accept="image/*" capture="user">
                                    <label class="custom-file-label border-light" for="selfieInput">Ambil foto selfie / bukti kehadiran...</label>
                                </div>
                                <small class="form-text text-muted mt-1">Wajib ambil selfie langsung di titik area kebersihan.</small>
                            </div>

                            <!-- Upload Foto Temuan Kondisi Kotor -->
                            <div class="form-group mb-3" id="attachmentGroup">
                                <label class="font-weight-600 text-dark">
                                    <i class="fas fa-camera text-warning mr-1"></i> Foto Temuan / Kondisi Area
                                    <span class="text-danger" id="attRequiredLabel" style="display:none;">*</span>
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="attachmentInput" name="attachment" accept="image/*" capture="environment">
                                    <label class="custom-file-label border-light" for="attachmentInput">Upload foto bukti kondisi area...</label>
                                </div>
                                <small class="form-text text-muted mt-1">Wajib jika status area kotor atau bermasalah.</small>
                            </div>

                            <!-- Catatan -->
                            <div class="form-group mb-4">
                                <label class="font-weight-600 text-dark">Catatan / Laporan Temuan</label>
                                <textarea name="notes" class="form-control border-light" rows="3" placeholder="Tuliskan catatan kondisi kebersihan atau temuan barang/kerusakan jika ada..."></textarea>
                            </div>

                            <button type="submit" class="btn text-white btn-block rounded-pill py-2.5 font-weight-bold shadow-sm" style="background-color: #0d9488;">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Laporan Kebersihan
                            </button>

                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Side: Checkpoint Status List -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-white border-bottom border-light pt-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-list-check text-success mr-1"></i> Area Pengecekan Hari Ini
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                            @forelse($checkpoints as $cp)
                                @php
                                    $isChecked = in_array($cp->id, $todayCheckedCodes);
                                @endphp
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-light">
                                    <div>
                                        <h6 class="mb-0 font-weight-bold {{ $isChecked ? 'text-success' : 'text-dark' }}" style="font-size:.9rem;">
                                            @if($isChecked)
                                                <i class="fas fa-check-circle text-success mr-1"></i>
                                            @else
                                                <i class="far fa-circle text-muted mr-1"></i>
                                            @endif
                                            {{ $cp->area_name }}
                                        </h6>
                                        <small class="text-muted">
                                            Plant {{ $cp->plant }} @if($cp->sub_area) &bull; {{ $cp->sub_area }} @endif
                                            &bull; Target: {{ $cp->target_duration }} menit
                                        </small>
                                    </div>
                                    <div>
                                        <button class="btn btn-xs rounded-pill px-3 font-weight-bold btn-select-area {{ $isChecked ? 'btn-outline-success' : 'btn-outline-teal' }}"
                                                style="{{ !$isChecked ? 'color:#0d9488; border-color:#0d9488;' : '' }}"
                                                data-code="{{ $cp->code }}"
                                                data-name="{{ $cp->area_name }}"
                                                data-target="{{ $cp->target_duration }}">
                                            {{ $isChecked ? 'Sudah Check' : 'Pilih Area' }}
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted small">Belum ada area checkpoint kebersihan terdaftar.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scripts untuk QR Scanner & Live Timer -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrCode = null;
    let isScannerRunning = false;
    let timerInterval = null;
    let secondsElapsed = 0;
    let lastScannedCode = null;
    let isScanningPaused = false;

    const allCheckpoints = @json($checkpoints);

    document.addEventListener("DOMContentLoaded", function() {
        initScanner();

        document.querySelectorAll('.custom-file-input').forEach(input => {
            input.addEventListener('change', function(e) {
                let fileName = e.target.files[0]?.name || 'Pilih file...';
                let label = e.target.nextElementSibling;
                if (label) label.innerText = fileName;
            });
        });

        document.getElementById('statusSelect').addEventListener('change', toggleAttachmentRequired);
        document.getElementById('is_evident_report').addEventListener('change', toggleEvidentReport);

        document.querySelectorAll('.btn-select-area').forEach(btn => {
            btn.addEventListener('click', function() {
                let code = this.getAttribute('data-code');
                let name = this.getAttribute('data-name');
                let target = this.getAttribute('data-target');
                onScanSuccess(code, name, target, true);
            });
        });
    });

    function initScanner() {
        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start({ facingMode: "environment" }, config, (decodedText, decodedResult) => {
            if (isScanningPaused || lastScannedCode === decodedText) {
                return; // Mencegah pemicuan ulang beruntun setiap frame (10fps) yang membuat form bergetar/bergerak sendiri
            }
            let cp = allCheckpoints.find(c => c.code === decodedText);
            let name = cp ? cp.area_name : decodedText;
            let target = cp ? cp.target_duration : 5;
            onScanSuccess(decodedText, name, target, false);
        }).catch(err => {
            console.log("Scanner start failed or fallback:", err);
        });

        isScannerRunning = true;

        document.getElementById('toggleScannerBtn').addEventListener('click', function() {
            if (isScannerRunning) {
                html5QrCode.stop().then(() => {
                    this.innerHTML = '<i class="fas fa-play mr-1"></i> Nyalakan Kamera';
                    this.style.background = '#f0fdf4';
                    this.style.color = '#16a34a';
                    this.style.borderColor = '#bbf7d0';
                    isScannerRunning = false;
                });
            } else {
                isScanningPaused = false;
                lastScannedCode = null;
                html5QrCode.start({ facingMode: "environment" }, config, (decodedText, decodedResult) => {
                    if (isScanningPaused || lastScannedCode === decodedText) {
                        return;
                    }
                    let cp = allCheckpoints.find(c => c.code === decodedText);
                    let name = cp ? cp.area_name : decodedText;
                    let target = cp ? cp.target_duration : 5;
                    onScanSuccess(decodedText, name, target, false);
                });
                this.innerHTML = '<i class="fas fa-stop mr-1"></i> Matikan Kamera';
                this.style.background = '#fef2f2';
                this.style.color = '#dc2626';
                this.style.borderColor = '#fecaca';
                isScannerRunning = true;
            }
        });

        document.getElementById('nextScanBtn').addEventListener('click', function() {
            isScanningPaused = false;
            lastScannedCode = null;
            document.getElementById('scannerPlaceholder').classList.add('d-none');
            document.getElementById('scanSuccessBadge').classList.add('d-none');
        });
    }

    function onScanSuccess(code, areaName, targetDuration, isManual = false) {
        if (!isManual && isScanningPaused && lastScannedCode === code) {
            return;
        }

        isScanningPaused = true;
        lastScannedCode = code;

        document.getElementById('barcode_code').value = code;
        document.getElementById('formAreaName').innerText = areaName;
        
        document.getElementById('waitingScanCard').classList.add('d-none');
        document.getElementById('formLaporanCard').classList.remove('d-none');

        document.getElementById('scannerPlaceholder').classList.remove('d-none');
        document.getElementById('scanSuccessBadge').classList.remove('d-none');
        document.getElementById('scannedCodeLabel').innerText = areaName + ' (' + code + ')';

        startTimer();

        // Scroll 1 kali saja dengan mulus ke posisi form laporan
        const formElement = document.getElementById('formLaporanCard');
        if (formElement) {
            const yOffset = -20;
            const y = formElement.getBoundingClientRect().top + window.pageYOffset + yOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    }

    function startTimer() {
        if (timerInterval) clearInterval(timerInterval);
        secondsElapsed = 0;
        updateTimerDisplay();

        timerInterval = setInterval(() => {
            secondsElapsed++;
            updateTimerDisplay();
        }, 1000);
    }

    function updateTimerDisplay() {
        let mins = Math.floor(secondsElapsed / 60);
        let secs = secondsElapsed % 60;
        let formatted = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        
        document.getElementById('timerDisplay').innerText = formatted;
        document.getElementById('durationMinutes').value = Math.max(1, mins);
    }

    function toggleAttachmentRequired() {
        let val = document.getElementById('statusSelect').value;
        let isClean = (val === 'Bersih & Rapi' || val === 'Sangat Bersih');
        let reqLabel = document.getElementById('attRequiredLabel');

        if (!isClean) {
            reqLabel.style.display = 'inline';
        } else {
            reqLabel.style.display = 'none';
        }
    }

    function toggleEvidentReport() {
        let isEvident = document.getElementById('is_evident_report').checked;
        let selfieGroup = document.getElementById('selfieGroup');

        if (isEvident) {
            selfieGroup.style.display = 'none';
            document.getElementById('barcode_code').value = 'EVIDENT-CLEAN';
            document.getElementById('waitingScanCard').classList.add('d-none');
            document.getElementById('formLaporanCard').classList.remove('d-none');
        } else {
            selfieGroup.style.display = 'block';
        }
    }
</script>
@endsection
