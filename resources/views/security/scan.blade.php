@extends('layouts.app')

@section('content')
<div class="content-header px-2">
    <div class="container-fluid">
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.5rem;"><i class="fas fa-barcode text-primary mr-1"></i> Scan Checkpoint Patroli</h1>
        <p class="text-muted small mb-0">Lakukan pemindaian barcode area untuk memverifikasi kehadiran dan mencatat laporan patroli.</p>
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
                    <div class="card-header bg-white border-bottom border-light pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0 text-dark">
                                <i class="fas fa-camera text-primary mr-1"></i> Pemindai Barcode / QR Code
                            </h5>
                            <button class="btn btn-sm btn-danger rounded-pill px-3" id="toggleScannerBtn">
                                <i class="fas fa-stop mr-1"></i> Matikan Kamera
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 bg-dark position-relative" style="min-height: 250px; display: flex; align-items: center; justify-content: center;">
                        <!-- HTML5 QR Scanner target container -->
                        <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto; background: #1f2d3d;"></div>

                        {{-- Placeholder saat scan berhasil (kamera mati, siap scan berikutnya) --}}
                        <div id="scannerPlaceholder" class="text-center text-white-50 p-4 d-none">
                            <div id="scanSuccessBadge" class="d-none mb-3">
                                <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center" style="width:64px;height:64px">
                                    <i class="fas fa-check fa-2x text-white"></i>
                                </div>
                                <p class="text-success font-weight-bold mt-2 mb-0" id="scannedCodeLabel"></p>
                            </div>
                            <button type="button" class="btn btn-primary rounded-pill px-4 py-2" id="nextScanBtn">
                                <i class="fas fa-camera mr-1"></i> Scan Area Berikutnya
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Placeholder 'belum scan' - tampil sebelum pemindaian --}}
                <div id="waitingScanCard" class="card shadow-sm border-0 rounded-4 mb-4" style="border: 2px dashed rgba(99,102,241,0.3) !important; background: rgba(99,102,241,0.04);">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-qrcode fa-4x" style="color: rgba(99,102,241,0.35);"></i>
                        </div>
                        <h6 class="font-weight-bold text-muted mb-1">Belum ada area yang dipilih</h6>
                        <p class="text-muted small mb-0">Arahkan kamera ke QR Code area checkpoint, atau pilih area secara manual menggunakan tombol <strong>Pilih Area</strong> di atas.</p>
                    </div>
                </div>

                <!-- Form Checkpoint Log - tersembunyi sebelum scan -->

                <div class="card shadow-sm border-0 rounded-4 d-none" id="formLaporanCard">
                    <div class="card-header bg-white border-bottom border-light pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0 text-dark">
                                <i class="fas fa-file-signature text-success mr-1"></i> Form Laporan Area
                            </h5>
                            <span class="badge badge-success rounded-pill px-3 py-2" id="formAreaBadge" style="font-size: .85rem;">
                                <i class="fas fa-map-marker-alt mr-1"></i> <span id="formAreaName">-</span>
                            </span>
                        </div>
                    </div>
                    <form action="{{ route('security.submit') }}" method="POST" enctype="multipart/form-data" id="checkpointForm">
                        @csrf
                        <div class="card-body p-4">
                            
                            <!-- Timer & Duration -->
                            <div class="alert alert-info rounded-pill shadow-none d-flex justify-content-between align-items-center mb-4 py-2 px-3" style="background-color: rgba(37, 99, 235, 0.08); border-color: rgba(37, 99, 235, 0.12);">
                                <span class="small text-primary font-weight-bold"><i class="fas fa-stopwatch mr-1"></i> Waktu Pengecekan Aktif:</span>
                                <span class="badge badge-primary rounded-pill font-weight-bold px-3 py-1" id="timerDisplay" style="font-size: .95rem;">00:00</span>
                                <input type="hidden" name="duration_minutes" id="durationMinutes" value="0">
                            </div>

                            <!-- Opsi Laporan Khusus / Eviden Report -->
                            <div class="form-group mb-4 bg-light p-3 rounded-4">
                                <div class="custom-control custom-switch custom-switch-md">
                                    <input type="checkbox" class="custom-control-input" id="is_evident_report" name="is_evident_report" value="1">
                                    <label class="custom-control-label font-weight-bold text-dark" for="is_evident_report">Kirim Laporan Temuan Khusus (Eviden Report)</label>
                                </div>
                                <small class="form-text text-muted ml-5 mt-1">Gunakan ini jika Anda ingin melaporkan temuan darurat/anomali di luar area checkpoint standar. <strong>Foto selfie tidak diwajibkan</strong>.</small>
                            </div>

                            <!-- Area Code -->
                            <div class="form-group mb-3">
                                <label class="font-weight-600 text-dark">Kode Area Checkpoint</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text border-light bg-light"><i class="fas fa-key text-muted"></i></span>
                                    </div>
                                    <input type="text" name="barcode_code" id="barcode_code" class="form-control border-light bg-light" placeholder="Scan barcode atau masukkan kode manual..." required readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="manualSelectBtn" style="border-color: #f1f5f9; background-color: #f1f5f9;">
                                            Pilih Area <i class="fas fa-chevron-down ml-1"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 d-none" id="manualAreaContainer">
                                    <select id="areaDropdown" class="form-control border-light bg-light">
                                        <option value="">-- Pilih Area Secara Manual --</option>
                                        @foreach($checkpoints as $cp)
                                            <option value="{{ $cp->code }}">{{ $cp->plant }} - {{ $cp->area_name }} ({{ $cp->code }})</option>
                                        @endforeach
                                    </select>
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

                            <!-- Status / Laporan -->
                            <div class="form-group mb-3">
                                <label class="font-weight-600 text-dark">Status Area</label>
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-radio custom-control-inline w-100 p-3 bg-light rounded-4 border border-light">
                                            <input type="radio" id="status_aman" name="status" value="Aman" class="custom-control-input" checked>
                                            <label class="custom-control-label font-weight-bold text-success w-100" for="status_aman">
                                                <i class="fas fa-check-circle mr-1"></i> Aman / Normal
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-radio custom-control-inline w-100 p-3 bg-light rounded-4 border border-light">
                                            <input type="radio" id="status_pintu" name="status" value="Pintu Belum Ditutup" class="custom-control-input">
                                            <label class="custom-control-label font-weight-bold text-warning w-100" for="status_pintu">
                                                <i class="fas fa-door-open mr-1"></i> Pintu Belum Ditutup
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-radio custom-control-inline w-100 p-3 bg-light rounded-4 border border-light">
                                            <input type="radio" id="status_listrik" name="status" value="Listrik Belum Dimatikan" class="custom-control-input">
                                            <label class="custom-control-label font-weight-bold text-warning w-100" for="status_listrik">
                                                <i class="fas fa-lightbulb mr-1"></i> Listrik Belum Dimatikan
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-radio custom-control-inline w-100 p-3 bg-light rounded-4 border border-light">
                                            <input type="radio" id="status_temuan" name="status" value="Temuan Lainnya" class="custom-control-input">
                                            <label class="custom-control-label font-weight-bold text-danger w-100" for="status_temuan">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Temuan Lainnya
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Laporan/Catatan Detail -->
                            <div class="form-group mb-3">
                                <label class="font-weight-600 text-dark">Laporan / Keterangan Detail</label>
                                <textarea name="notes" class="form-control border-light bg-light" rows="3" placeholder="Tulis rincian kondisi area atau detail temuan jika ada..."></textarea>
                            </div>

                            <!-- Upload Selfie -->
                            <div class="form-group mb-0" id="selfieUploadGroup">
                                <label class="font-weight-600 text-dark">Bukti Selfie Area <span class="text-danger">*Wajib</span></label>
                                <div class="custom-file mb-2">
                                    <!-- capture="camera" instructs mobile devices to open camera directly -->
                                    <input type="file" name="selfie" id="selfieInput" class="custom-file-input" accept="image/*" capture="camera">
                                    <label class="custom-file-label border-light bg-light" for="selfieInput">Ambil Foto / Pilih File...</label>
                                </div>
                                <small class="form-text text-muted">Ambil foto selfie Anda dengan latar belakang area checkpoint untuk verifikasi kehadiran fisik.</small>
                            </div>

                        </div>
                        <div class="card-footer bg-white border-0 p-4 pt-0">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm btn-block">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Laporan Monitoring
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Side: Checklist Area -->
            <div class="col-lg-5">
                <!-- Visual Checklist for Patrol status -->
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom border-light pt-3">
                        <h5 class="card-title font-weight-bold mb-0 text-dark">
                            <i class="fas fa-check-double text-info mr-1"></i> Checklist Patroli Hari Ini
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 bg-light d-flex justify-content-between align-items-center">
                            <span class="small font-weight-bold text-muted">Status Progress:</span>
                            <span class="badge badge-info rounded-pill px-3 py-1 font-weight-bold" id="progressPercentage">0%</span>
                        </div>
                        
                        <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                            @foreach($checkpoints as $cp)
                                @php
                                    $isChecked = in_array($cp->id, $todayCheckedCodes);
                                @endphp
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-light checklist-item" data-id="{{ $cp->id }}" data-checked="{{ $isChecked ? '1' : '0' }}">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            @if($isChecked)
                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                    <i class="fas fa-check" style="font-size: .85rem;"></i>
                                                </div>
                                            @else
                                                <div class="border rounded-circle d-flex align-items-center justify-content-center text-muted" style="width: 28px; height: 28px; background: #fff;">
                                                    <i class="far fa-circle" style="font-size: .85rem;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold {{ $isChecked ? 'text-muted text-decoration-line-through' : 'text-dark' }}">{{ $cp->area_name }}</h6>
                                            <small class="text-muted">{{ $cp->plant }} &bull; <code>{{ $cp->code }}</code></small>
                                        </div>
                                    </div>
                                    <div>
                                        @if($isChecked)
                                            <span class="badge badge-success px-2.5 py-1 rounded-pill small">Selesai</span>
                                        @else
                                            <span class="badge badge-warning px-2.5 py-1 rounded-pill small">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<!-- Load html5-qrcode scan library via CDN -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrScanner = null;
    let isScannerRunning = false;

    // Timer Logic for monitoring duration
    let startTime = Date.now();
    setInterval(updateTimer, 1000);

    function updateTimer() {
        const elapsedSeconds = Math.floor((Date.now() - startTime) / 1000);
        const minutes = Math.floor(elapsedSeconds / 60);
        const seconds = elapsedSeconds % 60;
        
        // Display
        const timeString = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        document.getElementById('timerDisplay').innerText = timeString;
        
        // Save duration in hidden field (at least 1 minute)
        document.getElementById('durationMinutes').value = Math.max(1, minutes);
    }

    // Toggle Manual Dropdown Selection
    $('#manualSelectBtn').on('click', function() {
        $('#manualAreaContainer').toggleClass('d-none');
    });

    $('#areaDropdown').on('change', function() {
        const val = $(this).val();
        if (val) {
            const label = $(this).find('option:selected').text();
            $('#barcode_code').val(val);
            $('#manualAreaContainer').addClass('d-none');
            startTime = Date.now();
            showLaporanForm(label); // Tampilkan form dengan nama area terpilih
        }
    });

    // Tampilkan form laporan setelah area dipilih / dipindai
    function showLaporanForm(areaLabel) {
        $('#waitingScanCard').addClass('d-none');   // Sembunyikan placeholder
        $('#formAreaName').text(areaLabel);          // Isi badge nama area
        const $form = $('#formLaporanCard');
        if ($form.hasClass('d-none')) {
            $form.removeClass('d-none').hide().fadeIn(350); // Animasi fade-in
        } else {
            // Sudah tampil, hanya update badge
            $form.find('.card-header').addClass('bg-success-light');
        }
    }

    // Handle File Input label change
    $('#selfieInput').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).next('.custom-file-label').html(fileName);
        } else {
            $(this).next('.custom-file-label').html('Ambil Foto / Pilih File...');
        }
    });

    // Toggle Eviden Report (Selfie not required)
    $('#is_evident_report').on('change', function() {
        const isEvident = $(this).is(':checked');
        if (isEvident) {
            $('#selfieUploadGroup').find('.text-danger').addClass('d-none');
            $('#selfieInput').removeAttr('required');
            $('#barcode_code').removeAttr('readonly');
            $('#barcode_code').attr('placeholder', 'Masukkan judul temuan atau kode area...');
        } else {
            $('#selfieUploadGroup').find('.text-danger').removeClass('d-none');
            $('#selfieInput').attr('required', 'required');
            $('#barcode_code').attr('readonly', 'readonly');
            $('#barcode_code').attr('placeholder', 'Scan barcode atau masukkan kode manual...');
        }
    });

    // Toggle QR Code Scanner Camera
    $('#toggleScannerBtn').on('click', function() {
        if (isScannerRunning) {
            stopScanner(false);
        } else {
            startScanner();
        }
    });

    // Tombol Scan Berikutnya — aktifkan kamera lagi
    $('#nextScanBtn').on('click', function() {
        startScanner();
    });

    function startScanner() {
        $('#scannerPlaceholder').addClass('d-none');
        $('#toggleScannerBtn')
            .html('<i class="fas fa-stop mr-1"></i> Matikan Kamera')
            .removeClass('btn-outline-primary')
            .addClass('btn-danger');
        isScannerRunning = true;

        html5QrScanner = new Html5Qrcode("reader");
        html5QrScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Kamera Gagal Diaktifkan',
                text: 'Pastikan aplikasi memiliki izin mengakses kamera.'
            });
            stopScanner(false);
        });
    }

    function stopScanner(showSuccessState = true) {
        if (html5QrScanner) {
            html5QrScanner.stop().then(() => {
                // Tampilkan placeholder
                $('#scannerPlaceholder').removeClass('d-none');
                if (showSuccessState) {
                    $('#scanSuccessBadge').removeClass('d-none');
                } else {
                    $('#scanSuccessBadge').addClass('d-none');
                }
                $('#toggleScannerBtn')
                    .html('<i class="fas fa-play mr-1"></i> Aktifkan Kamera')
                    .removeClass('btn-danger')
                    .addClass('btn-outline-primary');
                isScannerRunning = false;
                $('#reader').empty();
            }).catch(err => {
                console.error(err);
            });
        } else {
            // Scanner belum dimulai
            $('#scannerPlaceholder').addClass('d-none');
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        $('#barcode_code').val(decodedText);

        // Label kode yang berhasil di-scan
        $('#scannedCodeLabel').text('\u2713 ' + decodedText);

        // Cari nama area dari dropdown (jika ada)
        let areaLabel = decodedText;
        const $option = $('#areaDropdown option[value="' + decodedText + '"]');
        if ($option.length) {
            areaLabel = $option.text();
        }

        // Tampilkan form laporan
        showLaporanForm(areaLabel);

        // Bip audio
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audioCtx.createOscillator();
            osc.connect(audioCtx.destination);
            osc.type = 'sine';
            osc.frequency.value = 880;
            osc.start();
            osc.stop(audioCtx.currentTime + 0.12);
        } catch(e) {}

        Swal.fire({
            icon: 'success',
            title: 'Barcode Terbaca!',
            text: 'Area: ' + areaLabel,
            timer: 1500,
            showConfirmButton: false
        });

        startTime = Date.now();
        stopScanner(true); // tampilkan state sukses + tombol Scan Berikutnya
    }

    function onScanFailure(error) {
        // Abaikan kegagalan scan per frame
    }

    // Progress checklist
    function calculateProgress() {
        const items = $('.checklist-item');
        if (items.length === 0) return;
        let checkedCount = 0;
        items.each(function() {
            if ($(this).attr('data-checked') === '1') checkedCount++;
        });
        const percentage = Math.round((checkedCount / items.length) * 100);
        $('#progressPercentage').text(percentage + '%');
    }

    $(document).ready(function() {
        calculateProgress();
        // Auto-start kamera saat halaman pertama dibuka
        startScanner();
    });
</script>
@endpush
