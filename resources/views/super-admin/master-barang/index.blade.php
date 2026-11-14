@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item active">Master Barang ATK / RT & Satuan</li>
@endsection

@section('content')
<!-- Top Tab Navigation Card -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
    <div class="card-body p-3">
        <div class="nav nav-pills nav-fill" id="type-tabs" role="tablist">
            <a class="nav-link font-weight-bold rounded-pill {{ ($tab ?? 'barang') !== 'uom' && $type === 'ATK' ? 'active bg-primary shadow-sm' : 'text-secondary' }}" 
               href="{{ route('super-admin.master-barang.index', ['type' => 'ATK']) }}">
                <i class="fas fa-pencil-alt mr-1"></i> Alat Tulis Kantor (ATK)
            </a>
            <a class="nav-link font-weight-bold rounded-pill {{ ($tab ?? 'barang') !== 'uom' && $type === 'Rumah Tangga' ? 'active bg-warning text-white shadow-sm' : 'text-secondary' }}" 
               href="{{ route('super-admin.master-barang.index', ['type' => 'Rumah Tangga']) }}">
                <i class="fas fa-pump-soap mr-1"></i> Sanitasi Non Produksi (SNP)
            </a>
            <a class="nav-link font-weight-bold rounded-pill {{ ($tab ?? 'barang') === 'uom' ? 'active bg-info text-white shadow-sm' : 'text-secondary' }}" 
               href="{{ route('super-admin.master-barang.index', ['tab' => 'uom']) }}">
                <i class="fas fa-tags mr-1"></i> Kelola Satuan (UOM)
                <span class="badge {{ ($tab ?? 'barang') === 'uom' ? 'badge-light text-info' : 'badge-secondary' }} ml-1">{{ $uoms->count() }}</span>
            </a>
        </div>
    </div>
</div>

@if(($tab ?? 'barang') === 'uom')
    <!-- ========================================== -->
    <!-- TAB 3: MANAGEMENT MASTER SATUAN (UOM)     -->
    <!-- ========================================== -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 1.25rem;">
        <div class="card-header border-0 bg-white pt-4 pb-3 px-4 d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="font-weight-bold text-dark mb-0">
                    <i class="fas fa-tags text-info mr-2"></i>Daftar Satuan Barang (UOM)
                </h4>
                <p class="text-muted mb-0 small">Kelola master satuan baku untuk barang ATK, Sanitasi Non Produksi (SNP), dan Form Permintaan User.</p>
            </div>
            <button class="btn btn-info text-white btn-sm rounded-pill shadow-sm px-3 mt-2 mt-sm-0" 
                    data-toggle="modal" 
                    data-target="#addUomModal">
                <i class="fas fa-plus mr-1"></i> Tambah Satuan Baru
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="uom-table">
                    <thead class="bg-light border-0">
                        <tr>
                            <th class="pl-4 py-3 text-center" style="width: 8%; border: 0;">No</th>
                            <th class="py-3" style="width: 25%; border: 0;">Nama Satuan (UOM)</th>
                            <th class="py-3" style="width: 40%; border: 0;">Keterangan / Deskripsi</th>
                            <th class="py-3 text-center" style="width: 15%; border: 0;">Digunakan Pada</th>
                            <th class="pr-4 py-3 text-center" style="width: 12%; border: 0;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($uoms->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-tags fa-3x mb-3 text-light"></i>
                                    <p class="mb-0 font-weight-500">Belum ada satuan terdaftar.</p>
                                    <small class="text-muted">Klik tombol "Tambah Satuan Baru" untuk menambahkan.</small>
                                </td>
                            </tr>
                        @else
                            @foreach($uoms as $index => $uom)
                                <tr>
                                    <td class="pl-4 py-3 text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="py-3">
                                        <span class="badge badge-primary font-weight-bold py-1.5 px-3 rounded-pill" style="font-size: 0.9rem;">
                                            {{ $uom->name }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-muted">
                                        {{ $uom->description ?: '-' }}
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($uom->atk_items_count > 0)
                                            <span class="badge badge-light border text-dark font-weight-bold py-1 px-2.5 rounded-pill">
                                                <i class="fas fa-box text-primary mr-1"></i>{{ $uom->atk_items_count }} Barang
                                            </span>
                                        @else
                                            <span class="badge badge-light text-muted py-1 px-2 rounded-pill">
                                                0 Barang
                                            </span>
                                        @endif
                                    </td>
                                    <td class="pr-4 py-3 text-center">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-info rounded-circle mr-1 btn-edit-uom" 
                                                    data-id="{{ $uom->id }}" 
                                                    data-name="{{ $uom->name }}" 
                                                    data-description="{{ $uom->description }}" 
                                                    style="width: 32px; height: 32px; padding: 0;"
                                                    title="Edit Satuan">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger rounded-circle btn-delete-uom" 
                                                    data-id="{{ $uom->id }}" 
                                                    data-name="{{ $uom->name }}" 
                                                    data-count="{{ $uom->atk_items_count }}"
                                                    style="width: 32px; height: 32px; padding: 0;"
                                                    title="Hapus Satuan">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@else
    <!-- ========================================== -->
    <!-- TAB 1 & 2: MASTER BARANG (ATK & RT)        -->
    <!-- ========================================== -->
    <div class="row">
        <!-- Left Column: Categories List -->
        <div class="col-lg-4 mb-4">
            <!-- Categories Card -->
            <div class="card shadow-sm border-0" style="border-radius: 1.25rem;">
                <div class="card-header border-0 bg-white pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="font-weight-bold text-dark mb-0">Daftar Kategori</h4>
                        <p class="text-muted mb-0 small">Kategori untuk Tipe: <span class="font-weight-bold text-primary">{{ $type }}</span></p>
                    </div>
                    <button class="btn btn-outline-primary btn-sm rounded-pill" data-toggle="modal" data-target="#addCategoryModal">
                        <i class="fas fa-plus"></i> Kategori
                    </button>
                </div>
                
                <div class="card-body px-3 py-3" style="max-height: 550px; overflow-y: auto;">
                    @if($categories->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 text-light"></i>
                            <p class="mb-0">Belum ada kategori ditambahkan.</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($categories as $category)
                                @php
                                    $isActive = $selectedCategory && $selectedCategory->id === $category->id;
                                @endphp
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 mb-2 py-3 px-3 rounded-4 {{ $isActive ? 'bg-primary-light text-primary active-category' : '' }}" 
                                     style="border-radius: 0.75rem; cursor: pointer; transition: all 0.2s;"
                                     onclick="window.location.href='{{ route('super-admin.master-barang.index', ['type' => $type, 'category_id' => $category->id]) }}'">
                                    <div class="d-flex align-items-center flex-grow-1 mr-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" 
                                             style="width: 38px; height: 38px; background-color: {{ $isActive ? 'rgba(37, 99, 235, 0.15)' : 'rgba(100, 116, 139, 0.1)' }}; color: {{ $isActive ? '#2563eb' : '#64748b' }}">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                        <div class="text-truncate">
                                            <h6 class="mb-0 font-weight-bold text-dark text-truncate">{{ $category->name }}</h6>
                                            <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light text-muted' }} small mt-1 font-weight-bold">
                                                {{ $category->items_count }} Barang
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex" onclick="event.stopPropagation();">
                                        <button class="btn btn-sm btn-link text-info p-1 mr-1 btn-edit-category" 
                                                data-id="{{ $category->id }}" 
                                                data-name="{{ $category->name }}" 
                                                data-type="{{ $category->type }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-link text-danger p-1 btn-delete-category" 
                                                data-id="{{ $category->id }}" 
                                                data-name="{{ $category->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Items inside Category -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 1.25rem; min-height: 500px;">
                @if(!$selectedCategory)
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5">
                        <div class="bg-light rounded-circle p-4 mb-4" style="color: #94a3b8;">
                            <i class="fas fa-boxes fa-4x"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark">Kelola Daftar Barang</h5>
                        <p class="text-muted max-width-350">Silakan pilih kategori di sebelah kiri untuk melihat dan mengelola daftar barang standar di dalamnya.</p>
                    </div>
                @else
                    <div class="card-header border-0 bg-white pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="font-weight-bold text-dark mb-0">
                                <i class="fas fa-folder-open text-warning mr-2"></i>{{ $selectedCategory->name }}
                            </h4>
                            <p class="text-muted mb-0 small">Kategori untuk: <span class="badge bg-secondary text-white font-weight-bold">{{ $selectedCategory->type }}</span></p>
                        </div>
                        <button class="btn btn-primary btn-sm rounded-pill shadow-sm px-3 mt-2 mt-sm-0" 
                                data-toggle="modal" 
                                data-target="#addItemModal">
                            <i class="fas fa-plus mr-1"></i> Tambah Barang
                        </button>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="items-table">
                                <thead class="bg-light border-0">
                                    <tr>
                                        <th class="pl-4 py-3 text-center" style="width: 8%; border: 0;">No</th>
                                        <th class="py-3" style="width: 35%; border: 0;">Nama Barang</th>
                                        <th class="py-3 text-center" style="width: 15%; border: 0;">Satuan</th>
                                        <th class="py-3" style="width: 30%; border: 0;">Opsi / Spesifikasi</th>
                                        <th class="pr-4 py-3 text-center" style="width: 12%; border: 0;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($items->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
                                                <p class="mb-0 font-weight-500">Belum ada barang di kategori ini.</p>
                                                <small class="text-muted">Klik tombol "Tambah Barang" untuk menambahkan.</small>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($items as $index => $item)
                                            <tr>
                                                <td class="pl-4 py-3 text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                                <td class="py-3 font-weight-bold text-dark">{{ $item->name }}</td>
                                                <td class="py-3 text-center">
                                                    <span class="badge badge-secondary py-1 px-2.5 rounded-pill">{{ $item->unit }}</span>
                                                </td>
                                                <td class="py-3">
                                                    @if(empty($item->options))
                                                        <span class="text-muted small">-</span>
                                                    @else
                                                        @foreach($item->options as $opt)
                                                            <span class="badge badge-info mr-1 mb-1 py-1 px-2 font-weight-600 rounded">{{ $opt }}</span>
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td class="pr-4 py-3 text-center">
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-info rounded-circle mr-1 btn-edit-item" 
                                                                data-id="{{ $item->id }}" 
                                                                data-name="{{ $item->name }}" 
                                                                data-unit="{{ $item->unit }}" 
                                                                data-options="{{ implode(', ', $item->options ?? []) }}"
                                                                style="width: 32px; height: 32px; padding: 0;">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger rounded-circle btn-delete-item" 
                                                                data-id="{{ $item->id }}" 
                                                                data-name="{{ $item->name }}"
                                                                style="width: 32px; height: 32px; padding: 0;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

<!-- ========================================== -->
<!-- MODALS SECTION                             -->
<!-- ========================================== -->

<!-- Modal Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-folder-plus text-primary mr-2"></i>Tambah Kategori Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('super-admin.master-barang.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4">
                    <div class="form-group">
                        <label class="font-weight-600 text-dark">Nama Kategori</label>
                        <input type="text" name="name" class="form-control rounded-pill border-light bg-light shadow-none" placeholder="Masukkan nama kategori (contoh: Kertas & Buku)" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark">Tipe Form Permintaan</label>
                        <select name="type" class="form-control rounded-pill border-light bg-light shadow-none" required>
                            <option value="ATK" {{ $type === 'ATK' ? 'selected' : '' }}>Alat Tulis Kantor (ATK)</option>
                            <option value="Rumah Tangga" {{ $type === 'Rumah Tangga' ? 'selected' : '' }}>Sanitasi Non Produksi (SNP)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-edit text-info mr-2"></i>Edit Kategori</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body px-4">
                    <div class="form-group">
                        <label class="font-weight-600 text-dark">Nama Kategori</label>
                        <input type="text" name="name" id="edit-cat-name" class="form-control rounded-pill border-light bg-light shadow-none" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark">Tipe</label>
                        <select name="type" id="edit-cat-type" class="form-control rounded-pill border-light bg-light shadow-none" required>
                            <option value="ATK">Alat Tulis Kantor (ATK)</option>
                            <option value="Rumah Tangga">Sanitasi Non Produksi (SNP)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Category -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Hapus Kategori</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="deleteCategoryForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body px-4">
                    <p>Apakah Anda yakin ingin menghapus kategori <strong id="delete-cat-name-span"></strong>?</p>
                    <p class="text-danger small mb-0"><i class="fas fa-info-circle mr-1"></i>Semua barang di dalam kategori ini juga akan terhapus secara permanen.</p>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Item -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-box-open text-primary mr-2"></i>Tambah Barang Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('super-admin.master-barang.items.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4">
                    <input type="hidden" name="atk_category_id" value="{{ $selectedCategory ? $selectedCategory->id : '' }}">
                    
                    <div class="form-group">
                        <label class="font-weight-600 text-dark">Nama Barang</label>
                        <input type="text" name="name" class="form-control rounded-pill border-light bg-light shadow-none" placeholder="Masukkan nama barang (contoh: Penggaris Besi)" required>
                    </div>
                    
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-600 text-dark mb-0">Satuan (UOM)</label>
                            <a href="{{ route('super-admin.master-barang.index', ['tab' => 'uom']) }}" class="small text-info font-weight-bold" target="_blank">
                                <i class="fas fa-cog mr-1"></i>Kelola Satuan
                            </a>
                        </div>
                        <select name="unit" class="form-control rounded-pill border-light bg-light shadow-none" required>
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($uoms as $u)
                                <option value="{{ $u->name }}">{{ $u->name }} {{ $u->description ? '(' . $u->description . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark font-size-14">Opsi / Spesifikasi (Opsional)</label>
                        <input type="text" name="options_input" class="form-control rounded-pill border-light bg-light shadow-none" placeholder="Pisahkan dengan koma (contoh: 30cm, 50cm)">
                        <small class="form-text text-muted mt-1">Gunakan pemisah koma untuk mendefinisikan beberapa pilihan spesifikasi.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Item -->
<div class="modal fade" id="editItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-edit text-info mr-2"></i>Edit Detail Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editItemForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body px-4">
                    <div class="form-group">
                        <label class="font-weight-600 text-dark">Nama Barang</label>
                        <input type="text" name="name" id="edit-item-name" class="form-control rounded-pill border-light bg-light shadow-none" required>
                    </div>
                    
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-600 text-dark mb-0">Satuan (UOM)</label>
                            <a href="{{ route('super-admin.master-barang.index', ['tab' => 'uom']) }}" class="small text-info font-weight-bold" target="_blank">
                                <i class="fas fa-cog mr-1"></i>Kelola Satuan
                            </a>
                        </div>
                        <select name="unit" id="edit-item-unit" class="form-control rounded-pill border-light bg-light shadow-none" required>
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($uoms as $u)
                                <option value="{{ $u->name }}">{{ $u->name }} {{ $u->description ? '(' . $u->description . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark font-size-14">Opsi / Spesifikasi (Opsional)</label>
                        <input type="text" name="options_input" id="edit-item-options" class="form-control rounded-pill border-light bg-light shadow-none" placeholder="contoh: A3, A4, F4">
                        <small class="form-text text-muted mt-1">Pisahkan setiap opsi spesifikasi dengan tanda koma.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Item -->
<div class="modal fade" id="deleteItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Hapus Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="deleteItemForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body px-4">
                    <p>Apakah Anda yakin ingin menghapus barang <strong id="delete-item-name-span"></strong>?</p>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add UOM -->
<div class="modal fade" id="addUomModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-tag text-info mr-2"></i>Tambah Satuan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('super-admin.uoms.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4">
                    <div class="form-group">
                        <label class="font-weight-600 text-dark">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-pill border-light bg-light shadow-none" placeholder="Contoh: Pcs, Box, Rim, Pack, dll." required>
                        <small class="form-text text-muted mt-1">Nama satuan harus unik dan singkat.</small>
                    </div>
                    
                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark">Keterangan / Deskripsi (Opsional)</label>
                        <input type="text" name="description" class="form-control rounded-pill border-light bg-light shadow-none" placeholder="Contoh: Pieces / Buah / Satuan Tunggal">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4">Simpan Satuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit UOM -->
<div class="modal fade" id="editUomModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-edit text-info mr-2"></i>Edit Detail Satuan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUomForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body px-4">
                    <div class="alert alert-info py-2 px-3 rounded-4 small mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Jika nama satuan diubah, semua barang di Master Barang yang memakai satuan lama akan otomatis diperbarui.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600 text-dark">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-uom-name" class="form-control rounded-pill border-light bg-light shadow-none" required>
                    </div>
                    
                    <div class="form-group mb-0">
                        <label class="font-weight-600 text-dark">Keterangan / Deskripsi (Opsional)</label>
                        <input type="text" name="description" id="edit-uom-description" class="form-control rounded-pill border-light bg-light shadow-none">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4">Perbarui Satuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete UOM -->
<div class="modal fade" id="deleteUomModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Hapus Satuan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="deleteUomForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body px-4">
                    <p>Apakah Anda yakin ingin menghapus satuan <strong id="delete-uom-name-span"></strong>?</p>
                    <div id="delete-uom-warning" class="d-none text-danger small font-weight-bold">
                        <i class="fas fa-exclamation-circle mr-1"></i> Perhatian: Satuan ini sedang digunakan oleh <span id="delete-uom-count-span"></span> barang. Satuan tidak dapat dihapus jika masih digunakan.
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-confirm-delete-uom" class="btn btn-danger rounded-pill px-4">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .bg-primary-light {
        background-color: rgba(37, 99, 235, 0.08) !important;
    }
    .active-category {
        border-left: 4px solid #2563eb !important;
    }
    .active-category:hover {
        background-color: rgba(37, 99, 235, 0.12) !important;
    }
    .rounded-4 {
        border-radius: 0.75rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Edit Category ---
        $('.btn-edit-category').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const type = $(this).data('type');

            $('#edit-cat-name').val(name);
            $('#edit-cat-type').val(type);
            $('#editCategoryForm').attr('action', `/super-admin/master-barang/${id}`);
            $('#editCategoryModal').modal('show');
        });

        // --- Delete Category ---
        $('.btn-delete-category').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#delete-cat-name-span').text(name);
            $('#deleteCategoryForm').attr('action', `/super-admin/master-barang/${id}`);
            $('#deleteCategoryModal').modal('show');
        });

        // --- Edit Item ---
        $('.btn-edit-item').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const unit = $(this).data('unit');
            const options = $(this).data('options');

            $('#edit-item-name').val(name);
            $('#edit-item-unit').val(unit);
            $('#edit-item-options').val(options);
            
            // Set action for form update item
            $('#editItemForm').attr('action', `/super-admin/master-barang/items/${id}`);
            $('#editItemModal').modal('show');
        });

        // --- Delete Item ---
        $('.btn-delete-item').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#delete-item-name-span').text(name);
            
            // Set action for form delete item
            $('#deleteItemForm').attr('action', `/super-admin/master-barang/items/${id}`);
            $('#deleteItemModal').modal('show');
        });

        // --- Edit UOM ---
        $('.btn-edit-uom').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const description = $(this).data('description');

            $('#edit-uom-name').val(name);
            $('#edit-uom-description').val(description);
            
            $('#editUomForm').attr('action', `/super-admin/uoms/${id}`);
            $('#editUomModal').modal('show');
        });

        // --- Delete UOM ---
        $('.btn-delete-uom').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const count = parseInt($(this).data('count')) || 0;

            $('#delete-uom-name-span').text(name);
            $('#deleteUomForm').attr('action', `/super-admin/uoms/${id}`);

            if (count > 0) {
                $('#delete-uom-warning').removeClass('d-none');
                $('#delete-uom-count-span').text(count);
                $('#btn-confirm-delete-uom').prop('disabled', true).addClass('disabled');
            } else {
                $('#delete-uom-warning').addClass('d-none');
                $('#btn-confirm-delete-uom').prop('disabled', false).removeClass('disabled');
            }

            $('#deleteUomModal').modal('show');
        });
    });
</script>
@endpush
