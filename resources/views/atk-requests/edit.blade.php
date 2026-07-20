@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item"><a href="{{ route('atk-requests.index') }}">Permintaan</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('atk-requests.update', $atkRequest->id) }}" method="POST" id="atkForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div id="hidden-attachments" class="d-none"></div>
            
            <!-- Metadata Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom border-light">
                    <h3 class="card-title text-primary font-weight-bold" id="form-title">
                        <i class="fas fa-edit mr-2"></i>Edit Pengajuan Permintaan
                    </h3>
                </div>
                <div class="card-body">
                    @if($atkRequest->manager_notes || $atkRequest->notes || $atkRequest->items->where('status_approval', 'Ditolak')->count() > 0)
                        <div class="alert alert-warning rounded-4 border-0 p-3 mb-4 shadow-sm" style="background-color: #fffbeb; border-left: 4px solid #f59e0b !important;">
                            <h6 class="font-weight-bold mb-2" style="color: #d97706 !important;">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Catatan Evaluasi &amp; Revisi Barang:
                            </h6>
                            @if($atkRequest->manager_notes)
                                <div class="mb-1 text-dark"><strong>Catatan Manager:</strong> {{ $atkRequest->manager_notes }}</div>
                            @endif
                            @if($atkRequest->notes)
                                <div class="mb-1 text-dark"><strong>Catatan Admin GA:</strong> {{ $atkRequest->notes }}</div>
                            @endif
                            @foreach($atkRequest->items->where('status_approval', 'Ditolak') as $rejItem)
                                <div class="text-sm text-dark mt-1">
                                    <i class="fas fa-arrow-right text-danger mr-1"></i> <strong>{{ $rejItem->item_name }}</strong>: 
                                    <span class="badge badge-danger">Ditolak</span> 
                                    @if($rejItem->notes)
                                        <span class="font-italic text-muted">({{ $rejItem->notes }})</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="request_type" class="font-weight-600">Tipe Permintaan</label>
                                <select name="request_type" id="request_type" class="form-control font-weight-bold text-primary" required>
                                    <option value="ATK" {{ ($atkRequest->request_type ?? 'ATK') === 'ATK' ? 'selected' : '' }}>Alat Tulis Kantor (ATK)</option>
                                    <option value="Rumah Tangga" {{ ($atkRequest->request_type ?? 'ATK') === 'Rumah Tangga' ? 'selected' : '' }}>Kebutuhan Rumah Tangga (RT)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nama" class="font-weight-600">Nama Pengaju</label>
                                <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama', $atkRequest->nama) }}" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="divisi" class="font-weight-600">Divisi / Unit Kerja</label>
                                <input type="text" name="divisi" id="divisi" class="form-control" value="{{ old('divisi', $atkRequest->divisi) }}" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="periode" class="font-weight-600">Periode Permintaan</label>
                                <select name="periode" id="periode" class="form-control" required>
                                    @foreach($months as $val => $text)
                                        <option value="{{ $val }}" {{ $atkRequest->periode == $val ? 'selected' : '' }}>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="tanggal_pengajuan" class="font-weight-600">Tanggal Pengajuan</label>
                                <input type="date" name="tanggal_pengajuan" id="tanggal_pengajuan" class="form-control" value="{{ old('tanggal_pengajuan', $atkRequest->tanggal_pengajuan->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workspace: Selection Panel (Left) and Summary Table (Right) -->
            <div class="row">
                <!-- Selection Panel -->
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 rounded-4 mb-4 sticky-top" style="top: 90px; z-index: 100;">
                        <div class="card-header bg-white border-bottom border-light p-3">
                            <h4 class="card-title text-primary font-weight-bold mb-0">
                                <i class="fas fa-plus-circle mr-2"></i>Tambah Barang
                            </h4>
                        </div>
                        
                        <!-- Toggle Tab Mode Buttons -->
                        <div class="p-3 bg-light border-bottom border-light">
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-primary active rounded-left px-4 font-weight-bold" id="tab-standard-btn">
                                    <input type="radio" name="selection_mode" id="mode-standard" checked>
                                    <i class="fas fa-clipboard-list mr-1"></i> <span id="mode-standard-text">Item Standar</span>
                                </label>
                                <label class="btn btn-outline-primary rounded-right px-4 font-weight-bold" id="tab-custom-btn">
                                    <input type="radio" name="selection_mode" id="mode-custom">
                                    <i class="fas fa-edit mr-1"></i> Barang Lainnya
                                </label>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <!-- Standard Item Form Block -->
                            <div id="standard-form-block">
                                <div class="form-group">
                                    <label for="select-category" class="font-weight-600">1. Pilih Kategori</label>
                                    <select id="select-category" class="form-control rounded-pill">
                                        <option value="">-- Pilih Kategori --</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="select-item" class="font-weight-600">2. Pilih Nama Barang</label>
                                    <select id="select-item" class="form-control rounded-pill" disabled>
                                        <option value="">-- Pilih Barang --</option>
                                    </select>
                                </div>
                                
                                <div id="item-options-container">
                                    <!-- Dynamic Options Checkboxes go here -->
                                </div>
                                
                                <div class="form-group mt-3">
                                    <label for="input-qty" class="font-weight-600">3. Jumlah (Qty)</label>
                                    <div class="input-group" style="max-width: 160px;">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary btn-minus" type="button">-</button>
                                        </div>
                                        <input type="number" id="input-qty" class="form-control text-center font-weight-bold" min="1" value="1">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary btn-plus" type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="button" id="btn-add-standard" class="btn btn-primary btn-block rounded-pill mt-4 shadow-sm">
                                    <i class="fas fa-plus mr-1"></i> Tambahkan ke Daftar
                                </button>
                            </div>
                            
                            <!-- Custom Item Form Block (Hidden by default) -->
                            <div id="custom-form-block" class="d-none">
                                <div class="form-group">
                                    <label for="custom-name" class="font-weight-600">Nama Barang</label>
                                    <input type="text" id="custom-name" class="form-control rounded-pill" placeholder="Ketik nama barang...">
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="custom-unit" class="font-weight-600">Satuan</label>
                                            <input type="text" id="custom-unit" class="form-control rounded-pill" placeholder="Pcs, Box, Rim, dll...">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="custom-qty" class="font-weight-600">Jumlah (Qty)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button class="btn btn-outline-secondary btn-minus" type="button">-</button>
                                                </div>
                                                <input type="number" id="custom-qty" class="form-control text-center font-weight-bold" min="1" value="1">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-plus" type="button">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="custom-desc" class="font-weight-600">Keterangan Spesifikasi</label>
                                            <input type="text" id="custom-desc" class="form-control rounded-pill" placeholder="Keterangan spesifikasi...">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label class="font-weight-600">Lampiran / File (Opsional)</label>
                                            <div id="custom-attachment-wrapper">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="custom-attachment" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                                                    <label class="custom-file-label rounded-pill" for="custom-attachment" id="custom-attachment-label">Pilih file...</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="button" id="btn-add-custom" class="btn btn-warning btn-block rounded-pill mt-4 shadow-sm">
                                    <i class="fas fa-plus mr-1"></i> Tambahkan ke Daftar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Table -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom border-light p-3 d-flex justify-content-between align-items-center">
                            <h4 class="card-title text-primary font-weight-bold mb-0" id="summary-title">
                                <i class="fas fa-list-ul mr-2"></i>Daftar Permintaan Anda
                            </h4>
                            <span class="badge badge-info py-2 px-3 rounded-pill" id="totalItemsSelected">0 Item Dipilih</span>
                        </div>
                        
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0 align-middle" id="summary-table">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th style="width: 8%;">No</th>
                                            <th style="width: 35%;">Nama Barang</th>
                                            <th style="width: 15%;">Satuan</th>
                                            <th style="width: 30%;">Spesifikasi / Keterangan</th>
                                            <th style="width: 12%;">Qty</th>
                                            <th style="width: 8%;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="summary-table-body">
                                        <!-- Dynamic items populate here -->
                                    </tbody>
                                </table>
                                <div id="empty-state" class="text-center p-5 text-muted">
                                    <i class="fas fa-boxes fa-3x mb-3 text-light"></i>
                                    <h5>Belum ada barang dipilih</h5>
                                    <p class="small mb-0">Silakan pilih barang pada panel sebelah kiri dan klik tombol "Tambahkan ke Daftar".</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions Card -->
            <div class="alert alert-info rounded-4 border-0 p-3 mb-4 shadow-sm" id="rules-card" style="background-color: #e3f2fd; border-left: 5px solid #2196f3 !important; color: #0d47a1;">
                <!-- Dynamically updated -->
            </div>

            <!-- Form Actions -->
            <div class="card shadow-sm border-0 rounded-4 mb-5">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <a href="{{ route('atk-requests.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <div>
                        <button type="submit" name="action" value="draft" class="btn btn-outline-primary rounded-pill px-4 mr-2">
                            <i class="fas fa-save mr-1"></i> Simpan Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-paper-plane mr-1"></i> Ajukan Permintaan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .qty-input:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    .sticky-top {
        transition: top 0.3s ease;
    }
    .gap-2 {
        gap: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const standardItems = @json($standardItems);
        const householdItems = @json($householdItems);
        
        const typeSelect = document.getElementById('request_type');
        typeSelect.dataset.lastValue = typeSelect.value;
        
        let currentItems = typeSelect.value === 'Rumah Tangga' ? householdItems : standardItems;

        // Populate Categories Function
        const categorySelect = document.getElementById('select-category');
        const itemSelect = document.getElementById('select-item');
        const optionsContainer = document.getElementById('item-options-container');

        function populateCategories() {
            const categories = [...new Set(currentItems.map(item => item.category))];
            
            categorySelect.innerHTML = '<option value="">-- Pilih Kategori --</option>';
            itemSelect.innerHTML = '<option value="">-- Pilih Barang --</option>';
            itemSelect.disabled = true;
            optionsContainer.innerHTML = '';
            
            categories.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat;
                opt.textContent = cat;
                categorySelect.appendChild(opt);
            });
        }

        // Handle Request Type Change
        typeSelect.addEventListener('change', function(e) {
            const rows = document.querySelectorAll('#summary-table-body tr');
            if (rows.length > 0) {
                Swal.fire({
                    title: 'Ganti Tipe Permintaan?',
                    text: 'Mengubah tipe permintaan akan mengosongkan semua barang yang sudah Anda masukkan ke tabel ringkasan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ganti',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('summary-table-body').innerHTML = '';
                        updateUIState();
                        switchType();
                    } else {
                        typeSelect.value = typeSelect.dataset.lastValue;
                    }
                });
            } else {
                switchType();
            }
        });

        function switchType() {
            const val = typeSelect.value;
            typeSelect.dataset.lastValue = val;
            
            const formTitle = document.getElementById('form-title');
            const summaryTitle = document.getElementById('summary-title');
            const modeStandardText = document.getElementById('mode-standard-text');
            const rulesCard = document.getElementById('rules-card');
            
            if (val === 'Rumah Tangga') {
                currentItems = householdItems;
                formTitle.innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Pengajuan Kebutuhan Rumah Tangga (RT)';
                summaryTitle.innerHTML = '<i class="fas fa-list-ul mr-2"></i>Daftar Kebutuhan Rumah Tangga Anda';
                modeStandardText.textContent = 'Barang Standar RT';
                rulesCard.innerHTML = `
                    <h5 class="alert-heading font-weight-bold mb-2"><i class="fas fa-info-circle mr-2"></i>Ketentuan Permintaan Kebutuhan Rumah Tangga</h5>
                    <ul class="mb-0 small" style="padding-left: 20px;">
                        <li>Mohon diisi kebutuhan selama <strong>1 bulan (estimasi)</strong>.</li>
                        <li>Pengisian Permintaan & Penyerahan Form paling lambat <strong>tanggal 28</strong> disetiap bulannya.</li>
                    </ul>
                `;
            } else {
                currentItems = standardItems;
                formTitle.innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Pengajuan Permintaan ATK';
                summaryTitle.innerHTML = '<i class="fas fa-list-ul mr-2"></i>Daftar Permintaan ATK Anda';
                modeStandardText.textContent = 'Item Standar ATK';
                rulesCard.innerHTML = `
                    <h5 class="alert-heading font-weight-bold mb-2"><i class="fas fa-info-circle mr-2"></i>Ketentuan Permintaan ATK</h5>
                    <ul class="mb-0 small" style="padding-left: 20px;">
                        <li>Mohon diisi yang diperlukan selama <strong>1 bulan (estimasi)</strong>.</li>
                        <li>Jika kurang dari 1 bulan ATK sudah habis dan atau ada keperluan mendadak (urgent) mengenai ATK, mohon agar bisa menunggu periode bulan berikutnya.</li>
                        <li>Pengisian Permintaan & Penyerahan Form Permintaan ATK paling lambat <strong>tanggal 28</strong> disetiap bulannya.</li>
                    </ul>
                `;
            }
            populateCategories();
        }
        
        // Initial configuration
        switchType();
        
        // Handle Category Change
        categorySelect.addEventListener('change', function() {
            const category = this.value;
            itemSelect.innerHTML = '<option value="">-- Pilih Barang --</option>';
            optionsContainer.innerHTML = '';
            
            if (!category) {
                itemSelect.disabled = true;
                return;
            }
            
            const filtered = currentItems.filter(item => item.category === category);
            filtered.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.name;
                opt.textContent = item.name;
                itemSelect.appendChild(opt);
            });
            itemSelect.disabled = false;
        });
        
        // Handle Item Change
        itemSelect.addEventListener('change', function() {
            const itemName = this.value;
            optionsContainer.innerHTML = '';
            
            if (!itemName) return;
            
            const item = currentItems.find(i => i.name === itemName);
            if (item && item.options && item.options.length > 0) {
                const label = document.createElement('label');
                label.className = 'font-weight-600 d-block mb-1 mt-2';
                label.innerHTML = '<i class="fas fa-cog mr-1 text-muted"></i> Spesifikasi / Opsi:';
                optionsContainer.appendChild(label);
                
                const wrapper = document.createElement('div');
                wrapper.className = 'd-flex flex-wrap mb-2';
                wrapper.style.gap = '8px';
                
                item.options.forEach(opt => {
                    const div = document.createElement('div');
                    div.className = 'form-check form-check-inline bg-light p-2 rounded border';
                    div.style.cursor = 'pointer';
                    div.innerHTML = `
                        <input class="form-check-input spec-opt-cb" type="checkbox" value="${opt}" id="spec_${opt}">
                        <label class="form-check-label small font-weight-bold" for="spec_${opt}" style="cursor: pointer;">${opt}</label>
                    `;
                    wrapper.appendChild(div);
                });
                optionsContainer.appendChild(wrapper);
            }
            
            // Add custom description text field
            const customDesc = document.createElement('div');
            customDesc.className = 'form-group mt-2';
            customDesc.innerHTML = `
                <label class="font-weight-600 small">Keterangan Tambahan / Custom:</label>
                <input type="text" id="item-custom-opt" class="form-control form-control-sm rounded-pill" placeholder="Keterangan tambahan...">
            `;
            optionsContainer.appendChild(customDesc);
        });

        // Mode Switching
        const tabStandardBtn = document.getElementById('tab-standard-btn');
        const tabCustomBtn = document.getElementById('tab-custom-btn');
        const standardFormBlock = document.getElementById('standard-form-block');
        const customFormBlock = document.getElementById('custom-form-block');
        
        tabStandardBtn.addEventListener('click', function() {
            standardFormBlock.classList.remove('d-none');
            customFormBlock.classList.add('d-none');
        });
        
        tabCustomBtn.addEventListener('click', function() {
            customFormBlock.classList.remove('d-none');
            standardFormBlock.classList.add('d-none');
        });
        
        // Minus Plus Qty Buttons
        document.querySelectorAll('.btn-minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.closest('.input-group').querySelector('input[type="number"]');
                let val = parseInt(input.value) || 1;
                if (val > 1) {
                    input.value = val - 1;
                }
            });
        });
        
        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.closest('.input-group').querySelector('input[type="number"]');
                let val = parseInt(input.value) || 1;
                input.value = val + 1;
            });
        });

        // Add Standard Item to Summary List
        const btnAddStandard = document.getElementById('btn-add-standard');
        btnAddStandard.addEventListener('click', function() {
            const itemName = itemSelect.value;
            if (!itemName) {
                Swal.fire('Perhatian', 'Silakan pilih barang terlebih dahulu.', 'warning');
                return;
            }
            
            const qty = parseInt(document.getElementById('input-qty').value) || 1;
            const item = currentItems.find(i => i.name === itemName);
            const unit = item ? item.unit : 'Pcs';
            
            // Get selected options
            const options = [];
            document.querySelectorAll('.spec-opt-cb:checked').forEach(cb => {
                options.push(cb.value);
            });
            
            const customOptionInput = document.getElementById('item-custom-opt');
            const customOption = customOptionInput ? customOptionInput.value.trim() : '';
            
            addStandardRow(itemName, unit, qty, options, customOption);
            
            // Reset input values
            document.getElementById('input-qty').value = 1;
            if (customOptionInput) customOptionInput.value = '';
            document.querySelectorAll('.spec-opt-cb').forEach(cb => cb.checked = false);
        });

        // Add Custom Item to Summary List
        let customItemIndex = 0;
        const btnAddCustom = document.getElementById('btn-add-custom');
        
        function bindAttachmentChangeListener() {
            const fileInput = document.getElementById('custom-attachment');
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const label = document.getElementById('custom-attachment-label');
                    if (label && e.target.files.length > 0) {
                        label.textContent = e.target.files[0].name;
                    } else if (label) {
                        label.textContent = 'Pilih file...';
                    }
                });
            }
        }
        
        bindAttachmentChangeListener();

        btnAddCustom.addEventListener('click', function() {
            const name = document.getElementById('custom-name').value.trim();
            const unit = document.getElementById('custom-unit').value.trim() || 'Pcs';
            const qty = parseInt(document.getElementById('custom-qty').value) || 1;
            const desc = document.getElementById('custom-desc').value.trim();
            
            if (!name) {
                Swal.fire('Perhatian', 'Nama barang kustom tidak boleh kosong.', 'warning');
                return;
            }

            // Handle optional file attachment
            const fileInput = document.getElementById('custom-attachment');
            let fileName = '';
            const currentIndex = customItemIndex;
            
            if (fileInput && fileInput.files.length > 0) {
                fileName = fileInput.files[0].name;
                
                // Set name and id
                fileInput.name = `custom_items[${currentIndex}][attachment]`;
                fileInput.id = `attachment-${currentIndex}`;
                
                // Move to hidden attachments container
                document.getElementById('hidden-attachments').appendChild(fileInput);
                
                // Create a new file input in wrapper
                const wrapper = document.getElementById('custom-attachment-wrapper');
                wrapper.innerHTML = `
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="custom-attachment" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                        <label class="custom-file-label rounded-pill" for="custom-attachment" id="custom-attachment-label">Pilih file...</label>
                    </div>
                `;
                bindAttachmentChangeListener();
            }
            
            addCustomRow(name, unit, desc, qty, fileName, '', currentIndex);
            
            // Reset custom form
            document.getElementById('custom-name').value = '';
            document.getElementById('custom-unit').value = '';
            document.getElementById('custom-qty').value = 1;
            document.getElementById('custom-desc').value = '';
        });

        // Add Standard Row Logic
        function addStandardRow(name, unit, qty, options, customOption) {
            const tableBody = document.getElementById('summary-table-body');
            const slug = name.replace(/[^a-zA-Z0-9]/g, '_');
            const rowId = `row-standard-${slug}`;
            
            // Check if standard item already exists
            const existingRow = document.getElementById(rowId);
            if (existingRow) {
                const qtyInput = existingRow.querySelector('.qty-input');
                qtyInput.value = (parseInt(qtyInput.value) || 0) + qty;
                
                // Animate row background highlight
                existingRow.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
                setTimeout(() => { existingRow.style.backgroundColor = ''; }, 1000);
                return;
            }
            
            const tr = document.createElement('tr');
            tr.id = rowId;
            
            const optionsText = options.join(', ');
            let specText = optionsText;
            if (customOption) {
                specText = specText ? `${specText}, ${customOption}` : customOption;
            }
            if (!specText) specText = '-';
            
            let optionsInputsHtml = '';
            options.forEach(opt => {
                optionsInputsHtml += `<input type="hidden" name="items[${name}][options][]" value="${opt}">`;
            });
            
            tr.innerHTML = `
                <td class="text-center align-middle row-no"></td>
                <td class="align-middle font-weight-bold">
                    ${name}
                    <span class="badge badge-primary ml-1 small">Standar</span>
                </td>
                <td class="text-center align-middle">
                    <span class="badge badge-secondary">${unit}</span>
                    <input type="hidden" name="items[${name}][unit]" value="${unit}">
                </td>
                <td class="align-middle">
                    <span>${specText}</span>
                    ${optionsInputsHtml}
                    <input type="hidden" name="items[${name}][custom_option]" value="${customOption}">
                </td>
                <td class="align-middle text-center">
                    <input type="number" name="items[${name}][qty]" class="form-control form-control-sm text-center qty-input mx-auto" min="1" value="${qty}" style="width: 75px;">
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill remove-row-btn"><i class="fas fa-trash"></i></button>
                </td>
            `;
            
            tableBody.appendChild(tr);
            updateUIState();
        }

        // Add Custom Row Logic
        function addCustomRow(name, unit, desc, qty, fileName = '', existingAttachment = '', index = '') {
            const tableBody = document.getElementById('summary-table-body');
            const tr = document.createElement('tr');
            tr.className = 'custom-item-row';
            tr.dataset.index = index;
            
            let specText = desc ? desc : '-';
            if (fileName) {
                specText += `<br><span class="badge badge-success mt-1"><i class="fas fa-paperclip mr-1"></i> ${fileName}</span>`;
            } else if (existingAttachment) {
                specText += `<br><a href="/storage/${existingAttachment}" target="_blank" class="badge badge-success mt-1"><i class="fas fa-paperclip mr-1"></i> Lampiran Terunggah</a>`;
            }
            
            let existingAttachmentHtml = '';
            if (existingAttachment) {
                existingAttachmentHtml = `<input type="hidden" name="custom_items[${index}][existing_attachment]" value="${existingAttachment}">`;
            }
            
            tr.innerHTML = `
                <td class="text-center align-middle row-no"></td>
                <td class="align-middle font-weight-bold text-danger">
                    ${name}
                    <span class="badge badge-warning ml-1 small">Kustom</span>
                    <input type="hidden" name="custom_items[${index}][name]" value="${name}">
                </td>
                <td class="text-center align-middle">
                    <span class="badge badge-secondary">${unit}</span>
                    <input type="hidden" name="custom_items[${index}][unit]" value="${unit}">
                </td>
                <td class="align-middle">
                    <span>${specText}</span>
                    <input type="hidden" name="custom_items[${index}][desc]" value="${desc}">
                    ${existingAttachmentHtml}
                </td>
                <td class="align-middle text-center">
                    <input type="number" name="custom_items[${index}][qty]" class="form-control form-control-sm text-center qty-input mx-auto" min="1" value="${qty}" style="width: 75px;">
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill remove-row-btn"><i class="fas fa-trash"></i></button>
                </td>
            `;
            
            tableBody.appendChild(tr);
            customItemIndex++;
            updateUIState();
        }

        // Remove row logic
        document.getElementById('summary-table-body').addEventListener('click', function(e) {
            if (e.target.closest('.remove-row-btn')) {
                const tr = e.target.closest('tr');
                if (tr.classList.contains('custom-item-row')) {
                    const idx = tr.dataset.index;
                    const attachment = document.getElementById('attachment-' + idx);
                    if (attachment) attachment.remove();
                }
                tr.remove();
                updateUIState();
            }
        });

        // Update Row Numbers, Badge, and Empty State
        function updateUIState() {
            const rows = document.querySelectorAll('#summary-table-body tr');
            
            // Update sequential numbering
            rows.forEach((row, idx) => {
                row.querySelector('.row-no').textContent = idx + 1;
            });
            
            // Badge count
            document.getElementById('totalItemsSelected').innerText = `${rows.length} Item Dipilih`;
            
            // Empty state display
            const emptyState = document.getElementById('empty-state');
            if (rows.length > 0) {
                emptyState.classList.add('d-none');
            } else {
                emptyState.classList.remove('d-none');
            }
        }

        // Form Submit Validation
        document.getElementById('atkForm').addEventListener('submit', function(e) {
            const actionButton = document.activeElement;
            const action = actionButton ? actionButton.value : 'draft';
            
            if (action === 'submit') {
                const rows = document.querySelectorAll('#summary-table-body tr');
                if (rows.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Perhatian!',
                        text: 'Anda harus memilih minimal 1 item untuk mengajukan permintaan.',
                        icon: 'warning',
                        confirmButtonColor: '#007bff'
                    });
                }
            }
        });

        // ══════════════════════════════════════════════════
        // PRE-POPULATE DATA FROM DATABASE FOR EDITING MODE
        // ══════════════════════════════════════════════════
        const existingItems = @json($existingItems);
        const customItems = @json($customItems);

        // Populate Standard Items
        Object.keys(existingItems).forEach(name => {
            const dbItem = existingItems[name];
            const qty = dbItem.quantity;
            const unit = dbItem.unit;
            
            const itemConfig = currentItems.find(i => i.name === name);
            const standardOptions = itemConfig ? itemConfig.options : [];
            
            const desc = dbItem.description || '';
            const descParts = desc.split(',').map(s => s.trim());
            
            const checkedOpts = [];
            standardOptions.forEach(opt => {
                if (descParts.includes(opt)) {
                    checkedOpts.push(opt);
                }
            });
            const remainingParts = descParts.filter(p => !standardOptions.includes(p));
            const customOption = remainingParts.join(', ');
            
            addStandardRow(name, unit, qty, checkedOpts, customOption);
        });

        // Populate Custom Items
        customItems.forEach(cItem => {
            const name = cItem.item_name;
            const unit = cItem.unit;
            const desc = cItem.description || '';
            const qty = cItem.quantity;
            const existingAttachment = cItem.attachment || '';
            
            addCustomRow(name, unit, desc, qty, '', existingAttachment, customItemIndex);
        });
    });
</script>
@endpush
