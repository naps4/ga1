<?php

namespace App\Http\Controllers;

use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AtkRequestController extends Controller
{
    public static $standardItems = [
        ['name' => 'Kertas 70 Gr', 'unit' => 'Rim', 'options' => ['A3', 'A4', 'F4'], 'category' => 'Kertas & Buku'],
        ['name' => 'Kertas 80 Gr', 'unit' => 'Rim', 'options' => ['A3', 'A4', 'F4'], 'category' => 'Kertas & Buku'],
        ['name' => 'Kertas Continuos Form 9 1/2 x 11', 'unit' => 'Dus', 'options' => ['1 Ply', '2 Ply', '3 Ply'], 'category' => 'Kertas & Buku'],
        ['name' => 'Kertas Continuos Form 9 1/2 x 11 (Bagi 2)', 'unit' => 'Dus', 'options' => ['1 Ply', '2 Ply', '3 Ply'], 'category' => 'Kertas & Buku'],
        ['name' => 'Kertas Foto/sticker', 'unit' => 'Pack', 'options' => ['A4', 'F4'], 'category' => 'Kertas & Buku'],
        ['name' => 'Buku', 'unit' => 'Pcs', 'options' => ['Folio', 'Kuarto', 'Tulis'], 'category' => 'Kertas & Buku'],
        ['name' => 'Sticker label', 'unit' => 'Plastik', 'options' => ['112', '120', '121'], 'category' => 'Kertas & Buku'],
        
        ['name' => 'Pulpen', 'unit' => 'Pcs', 'options' => ['Biru', 'Hitam', 'Merah'], 'category' => 'Alat Tulis & Koreksi'],
        ['name' => 'Isi Pulpen', 'unit' => 'Pcs', 'options' => ['Biru', 'Hitam', 'Merah'], 'category' => 'Alat Tulis & Koreksi'],
        ['name' => 'Pensil', 'unit' => 'Pcs', 'options' => ['HB', '2B', 'Rangka', 'Body'], 'category' => 'Alat Tulis & Koreksi'],
        ['name' => 'Penghapus', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat Tulis & Koreksi'],
        ['name' => 'Spidol Kecil/OPM', 'unit' => 'Pcs', 'options' => ['Biru', 'Hitam', 'Merah'], 'category' => 'Alat Tulis & Koreksi'],
        ['name' => 'Spidol Papan Tulis', 'unit' => 'Pcs', 'options' => ['Biru', 'Hitam', 'Merah'], 'category' => 'Alat Tulis & Koreksi'],
        ['name' => 'Stabilo', 'unit' => 'Pcs', 'options' => ['Hijau', 'Kuning', 'Orange'], 'category' => 'Alat Tulis & Koreksi'],
        ['name' => 'Tip ex', 'unit' => 'Pcs', 'options' => ['Cair', 'Kertas'], 'category' => 'Alat Tulis & Koreksi'],
        
        ['name' => 'Box File', 'unit' => 'Pcs', 'options' => [], 'category' => 'Penyimpanan Dokumen'],
        ['name' => 'Business File', 'unit' => 'Pack', 'options' => ['A4', 'F4'], 'category' => 'Penyimpanan Dokumen'],
        ['name' => 'Clear Holder Document', 'unit' => 'Pcs', 'options' => ['20', '40', '60', '80'], 'category' => 'Penyimpanan Dokumen'],
        ['name' => 'Clear Sleeves', 'unit' => 'Pcs', 'options' => ['A4', 'F4'], 'category' => 'Penyimpanan Dokumen'],
        ['name' => 'Map Dengan Pengait', 'unit' => 'Pcs', 'options' => ['Biru', 'Hijau', 'Merah'], 'category' => 'Penyimpanan Dokumen'],
        ['name' => 'Map Tanpa Pengait', 'unit' => 'Pcs', 'options' => ['Biru', 'Hijau', 'Merah'], 'category' => 'Penyimpanan Dokumen'],
        ['name' => 'Ordner', 'unit' => 'Pcs', 'options' => ['Kwitansi', 'Folio'], 'category' => 'Penyimpanan Dokumen'],
        ['name' => 'Pembatas File', 'unit' => 'Pack', 'options' => ['A4', 'A4 (A-Z)', 'F4'], 'category' => 'Penyimpanan Dokumen'],
        ['name' => 'Sheet Protector', 'unit' => 'Pcs', 'options' => [], 'category' => 'Penyimpanan Dokumen'],
        
        ['name' => 'Cutter', 'unit' => 'Pcs / Pack', 'options' => ['Kecil', 'Besar'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Isi Cutter', 'unit' => 'Pack', 'options' => ['Kecil', 'Besar'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Double Tape', 'unit' => 'Pcs', 'options' => ['Kecil', 'Besar'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Gunting', 'unit' => 'Pcs', 'options' => ['Kecil', 'Besar'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Lem Kertas', 'unit' => 'Pcs', 'options' => ['Cair', 'Stick'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Lakban ( Coklat / Hitam / Bening )*', 'unit' => 'Pcs', 'options' => ['Kecil', 'Sedang', 'Besar', 'Listrik'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Sellotape', 'unit' => 'Pcs', 'options' => ['Kecil', 'Sedang', 'Besar', 'Listrik'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Tape Dispenser', 'unit' => 'Pcs', 'options' => ['Kecil', 'Besar', 'Cutter'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Binder Clips', 'unit' => 'Box Kecil', 'options' => ['105', '107', '111'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Paper Clips', 'unit' => 'Box Kecil', 'options' => ['No. 3', 'No. 5'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Pembolong Kertas', 'unit' => 'Pcs', 'options' => ['Kecil', 'Besar'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Pencabut Staples', 'unit' => 'Pcs', 'options' => [], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Staples', 'unit' => 'Pcs', 'options' => ['Kecil', 'Besar'], 'category' => 'Perekat & Pemotong'],
        ['name' => 'Isi Staples', 'unit' => 'Box Kecil', 'options' => ['Kecil', 'Besar'], 'category' => 'Perekat & Pemotong'],
        
        ['name' => 'Cartridge tinta pita dot matrix', 'unit' => 'Pcs', 'options' => ['LQ 2190'], 'category' => 'Tinta & Printer'],
        ['name' => 'Pita Printer', 'unit' => 'Pcs', 'options' => ['LX 300', 'LX 310', 'LQ 2190'], 'category' => 'Tinta & Printer'],
        ['name' => 'Tinta ( 678 / 703 / 802 )', 'unit' => 'Pcs', 'options' => ['Warna', 'Hitam'], 'category' => 'Tinta & Printer'],
        ['name' => 'Tinta PG740', 'unit' => 'Pcs', 'options' => ['Biru', 'Hitam', 'Kuning', 'Merah'], 'category' => 'Tinta & Printer'],
        ['name' => 'Tinta CL741', 'unit' => 'Pcs', 'options' => ['Biru', 'Hitam', 'Kuning', 'Merah'], 'category' => 'Tinta & Printer'],
        ['name' => 'Tinta Stempel Tanpa Bantalan (Simanis)', 'unit' => 'Pcs', 'options' => ['Biru', 'Ungu', 'Merah'], 'category' => 'Tinta & Printer'],
        
        ['name' => 'Amplop Coklat', 'unit' => 'Pcs', 'options' => ['A3', 'A4', '1/2 Folio', 'Folio'], 'category' => 'Lain-lain'],
        ['name' => 'Amplop Putih', 'unit' => 'Pcs', 'options' => ['Kecil', 'Panjang'], 'category' => 'Lain-lain'],
        ['name' => 'Baterai', 'unit' => 'Pcs', 'options' => ['AA', 'AAA', 'C', 'D'], 'category' => 'Lain-lain'],
        ['name' => 'Baterai AA', 'unit' => 'Pack', 'options' => ['Kecil', 'Besar'], 'category' => 'Lain-lain'],
        ['name' => 'Baterai AAA', 'unit' => 'Pack', 'options' => ['Kecil', 'Besar'], 'category' => 'Lain-lain'],
        ['name' => 'Kalkulator', 'unit' => 'Pcs', 'options' => [], 'category' => 'Lain-lain'],
        ['name' => 'Penggaris', 'unit' => 'Pcs', 'options' => ['Besi', 'Plastik'], 'category' => 'Lain-lain'],
        ['name' => 'Post IT', 'unit' => 'Pcs', 'options' => ['Kecil', 'Besar', 'Page Marker', 'Sign Here'], 'category' => 'Lain-lain'],
        ['name' => 'Rautan Pensil', 'unit' => 'Pcs', 'options' => ['Kecil', 'Meja'], 'category' => 'Lain-lain']
    ];

    public static $householdItems = [
        ['name' => 'Hand Soap', 'unit' => 'Pcs', 'options' => [], 'category' => 'Sabun & Pembersih'],
        ['name' => 'Sabun Cuci Piring', 'unit' => 'Pcs', 'options' => [], 'category' => 'Sabun & Pembersih'],
        ['name' => 'Wipol', 'unit' => 'Pcs/Botol', 'options' => [], 'category' => 'Sabun & Pembersih'],
        ['name' => 'Soklin lantai', 'unit' => 'Pack', 'options' => [], 'category' => 'Sabun & Pembersih'],
        ['name' => 'Soklin Liquid', 'unit' => 'Pack', 'options' => [], 'category' => 'Sabun & Pembersih'],
        ['name' => 'Pewangi Pakaian/molto', 'unit' => 'Pack', 'options' => [], 'category' => 'Sabun & Pembersih'],
        ['name' => 'Hand Sanitizer', 'unit' => 'Pcs/Botol', 'options' => [], 'category' => 'Sabun & Pembersih'],
        ['name' => 'Karbol WC', 'unit' => 'Pack', 'options' => [], 'category' => 'Sabun & Pembersih'],
        ['name' => 'Clink Pembersih Kaca', 'unit' => 'Pcs', 'options' => [], 'category' => 'Sabun & Pembersih'],
        
        ['name' => 'Pewangi ruangan gantung', 'unit' => 'Pcs', 'options' => [], 'category' => 'Pewangi Ruangan'],
        ['name' => 'Pewangi Ruangan Spray', 'unit' => 'Pcs', 'options' => [], 'category' => 'Pewangi Ruangan'],
        ['name' => 'Alat Pewangi Ruangan automantic', 'unit' => 'Pcs', 'options' => [], 'category' => 'Pewangi Ruangan'],
        ['name' => 'Isi Pewangi Ruangan automantic', 'unit' => 'Pcs', 'options' => [], 'category' => 'Pewangi Ruangan'],
        ['name' => 'Karbol Lemari', 'unit' => 'Pack', 'options' => [], 'category' => 'Pewangi Ruangan'],
        
        ['name' => 'Tissu Kotak Besar', 'unit' => 'Pack', 'options' => [], 'category' => 'Tisu & Kebersihan Diri'],
        ['name' => 'Tissu Kotak Kecil', 'unit' => 'Pack', 'options' => [], 'category' => 'Tisu & Kebersihan Diri'],
        ['name' => 'Tissu Toilet', 'unit' => 'Roll', 'options' => [], 'category' => 'Tisu & Kebersihan Diri'],
        ['name' => 'Hairnet', 'unit' => 'Pack', 'options' => [], 'category' => 'Tisu & Kebersihan Diri'],
        ['name' => 'Sarung tangan latex', 'unit' => 'Pack', 'options' => [], 'category' => 'Tisu & Kebersihan Diri'],
        
        ['name' => 'Trash Bag besar', 'unit' => 'Pack', 'options' => [], 'category' => 'Kantong & Tempat Sampah'],
        ['name' => 'Trash Bag Kecil', 'unit' => 'Pack', 'options' => [], 'category' => 'Kantong & Tempat Sampah'],
        ['name' => 'Tempat sampah', 'unit' => 'Pcs', 'options' => [], 'category' => 'Kantong & Tempat Sampah'],
        ['name' => 'Tempat Sampah Kecil', 'unit' => 'Pcs', 'options' => [], 'category' => 'Kantong & Tempat Sampah'],
        ['name' => 'Tempat Sampah Sedang', 'unit' => 'Pcs', 'options' => [], 'category' => 'Kantong & Tempat Sampah'],
        ['name' => 'Tempat Sampah Besar', 'unit' => 'Pcs', 'options' => [], 'category' => 'Kantong & Tempat Sampah'],
        
        ['name' => 'Alat Pel Gagang Panjang', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Gayung', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Sikat lantai Gagang Panjang', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Sikat WC', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Kanebo', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Lap Microfiber (lap meja)', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Spon Cuci Piring', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Scotch Brite (sabut besi)', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Kemoceng', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Sapu Lantai', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Sapu Halaman', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Sapu Lidi', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Sapu Temit', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        ['name' => 'Wipper kaca', 'unit' => 'Pcs', 'options' => [], 'category' => 'Alat & Perlengkapan Kebersihan'],
        
        ['name' => 'Kran Air', 'unit' => 'Pcs', 'options' => [], 'category' => 'Lain-lain'],
        ['name' => 'Hanger', 'unit' => 'Pack', 'options' => [], 'category' => 'Lain-lain'],
        ['name' => 'Pengki', 'unit' => 'Pcs', 'options' => [], 'category' => 'Lain-lain']
    ];

    private function getItemsForType(string $type)
    {
        return \App\Models\AtkItem::whereHas('category', function($q) use ($type) {
            $q->where('type', $type);
        })
        ->with('category')
        ->orderBy('atk_category_id', 'asc')
        ->orderBy('id', 'asc')
        ->get()
        ->map(function($item) {
            return [
                'name' => $item->name,
                'unit' => $item->unit,
                'options' => $item->options ?? [],
                'category' => $item->category->name
            ];
        })
        ->toArray();
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'security') {
            return redirect()->route('security.scan')->with('error', 'Role Security tidak memiliki akses ke menu Permintaan.');
        }

        $query = AtkRequest::with(['user', 'approver', 'items']);

        if (!$user->isManager() && !$user->isAdmin() && !$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Apply filters if Admin/Super Admin/Manager
        if ($user->isAdmin() || $user->isSuperAdmin() || $user->isManager()) {
            // 1. Filter Tanggal Pengajuan
            if ($request->filled('start_date')) {
                $query->whereDate('tanggal_pengajuan', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('tanggal_pengajuan', '<=', $request->end_date);
            }

            // 2. Filter Tipe Permintaan
            if ($request->filled('request_type') && $request->request_type !== 'all') {
                $query->where('request_type', $request->request_type);
            }

            // 3. Filter Status Approval
            if ($request->filled('status_approval') && $request->status_approval !== 'all') {
                $query->where('status_approval', $request->status_approval);
            }

            // 4. Filter Divisi
            if ($request->filled('divisi')) {
                $query->where('divisi', 'like', '%' . $request->divisi . '%');
            }

            // 5. Filter Item Tertentu
            if ($request->filled('item_name')) {
                $query->whereHas('items', function($q) use ($request) {
                    $q->where('item_name', 'like', '%' . $request->item_name . '%');
                });
            }
        }

        $requests = $query->orderBy('tanggal_pengajuan', 'desc')->get();

        return view('atk-requests.index', compact('requests'));
    }

    public function create()
    {
        $user = auth()->user();
        $standardItems = $this->getItemsForType('ATK');
        $householdItems = $this->getItemsForType('Rumah Tangga');
        
        $defaultType = request()->get('type') === 'household' ? 'Rumah Tangga' : 'ATK';
        
        // Setup default values
        $nama = $user->name;
        $divisi = $user->company ?? $user->department ?? '-';
        $tanggal_pengajuan = now()->format('Y-m-d');
        
        // Generate months for period selection
        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $date = now()->addMonths($i);
            $val = $date->format('m/Y');
            $text = $date->translatedFormat('F Y');
            $months[$val] = $text;
        }

        return view('atk-requests.create', compact('standardItems', 'householdItems', 'nama', 'divisi', 'tanggal_pengajuan', 'months', 'defaultType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'request_type' => 'required|string|in:ATK,Rumah Tangga',
            'nama' => 'required|string',
            'divisi' => 'required|string',
            'periode' => 'required|string',
            'tanggal_pengajuan' => 'required|date',
            'items' => 'nullable|array',
            'custom_items' => 'nullable|array',
        ]);

        $action = $request->input('action', 'draft');
        $status = ($action === 'submit') ? 'Menunggu Persetujuan Manager' : 'Draft';

        DB::transaction(function () use ($request, $status) {
            $atk = AtkRequest::create([
                'user_id' => auth()->id(),
                'request_type' => $request->request_type,
                'nama' => $request->nama,
                'divisi' => $request->divisi,
                'periode' => $request->periode,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'status_approval' => $status,
            ]);

            // Save Standard Items
            if ($request->has('items')) {
                foreach ($request->items as $itemName => $itemData) {
                    $qty = intval($itemData['qty'] ?? 0);
                    if ($qty > 0) {
                        $desc = isset($itemData['options']) ? implode(', ', $itemData['options']) : ($itemData['custom_option'] ?? null);
                        AtkRequestItem::create([
                            'atk_request_id' => $atk->id,
                            'item_name' => $itemName,
                            'unit' => $itemData['unit'] ?? 'Pcs',
                            'description' => $desc,
                            'quantity' => $qty,
                            'is_custom' => false,
                        ]);
                    }
                }
            }

            // Save Custom Items
            if ($request->has('custom_items')) {
                foreach ($request->custom_items as $index => $cItem) {
                    $qty = intval($cItem['qty'] ?? 0);
                    if (!empty($cItem['name']) && $qty > 0) {
                        $attachmentPath = null;
                        if ($request->hasFile("custom_items.{$index}.attachment")) {
                            $file = $request->file("custom_items.{$index}.attachment");
                            if ($file->isValid()) {
                                $fileName = time() . '_' . $file->getClientOriginalName();
                                $attachmentPath = $file->storeAs('atk-attachments', $fileName, 'public');
                            }
                        }

                        AtkRequestItem::create([
                            'atk_request_id' => $atk->id,
                            'item_name' => $cItem['name'],
                            'unit' => $cItem['unit'] ?? 'Pcs',
                            'description' => $cItem['desc'] ?? null,
                            'quantity' => $qty,
                            'is_custom' => true,
                            'attachment' => $attachmentPath,
                        ]);
                    }
                }
            }
        });

        $typeName = ($request->request_type === 'Rumah Tangga') ? 'Kebutuhan Rumah Tangga' : 'ATK';
        $msg = ($status === 'Draft') ? "Draft permintaan {$typeName} berhasil disimpan." : "Permintaan {$typeName} berhasil diajukan.";
        return redirect()->route('atk-requests.index')->with('success', $msg);
    }

    public function show(AtkRequest $atkRequest)
    {
        $atkRequest->load('user', 'approver', 'items');
        return view('atk-requests.show', compact('atkRequest'));
    }

    public function edit(AtkRequest $atkRequest)
    {
        if ($atkRequest->user_id !== auth()->id() && !auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        if (!in_array($atkRequest->status_approval, ['Draft', 'Ditolak/Revisi', 'Menunggu Persetujuan Manager', 'Menunggu Persetujuan Admin GA'])) {
            return redirect()->route('atk-requests.show', $atkRequest->id)->with('error', 'Permintaan yang sudah disetujui secara penuh tidak dapat diubah.');
        }

        $standardItems = $this->getItemsForType('ATK');
        $householdItems = $this->getItemsForType('Rumah Tangga');
        
        // Generate months for period selection
        $months = [];
        for ($i = -2; $i < 6; $i++) {
            $date = now()->addMonths($i);
            $val = $date->format('m/Y');
            $text = $date->translatedFormat('F Y');
            $months[$val] = $text;
        }

        // Prepare existing items mapped by name
        $existingItems = $atkRequest->items->where('is_custom', false)->keyBy('item_name')->toArray();
        $customItems = $atkRequest->items->where('is_custom', true)->values()->toArray();

        return view('atk-requests.edit', compact('atkRequest', 'standardItems', 'householdItems', 'months', 'existingItems', 'customItems'));
    }

    public function update(Request $request, AtkRequest $atkRequest)
    {
        if ($atkRequest->user_id !== auth()->id() && !auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        if (!in_array($atkRequest->status_approval, ['Draft', 'Ditolak/Revisi', 'Menunggu Persetujuan Manager', 'Menunggu Persetujuan Admin GA'])) {
            return redirect()->route('atk-requests.show', $atkRequest->id)->withErrors(['error' => 'Permintaan yang sudah disetujui secara penuh tidak dapat diubah.']);
        }

        $request->validate([
            'request_type' => 'required|string|in:ATK,Rumah Tangga',
            'nama' => 'required|string',
            'divisi' => 'required|string',
            'periode' => 'required|string',
            'tanggal_pengajuan' => 'required|date',
            'items' => 'nullable|array',
            'custom_items' => 'nullable|array',
        ]);

        $action         = $request->input('action', 'draft');
        $status         = ($action === 'submit') ? 'Menunggu Persetujuan Manager' : 'Draft';
        $wasRejected    = ($atkRequest->status_approval === 'Ditolak/Revisi');

        DB::transaction(function () use ($request, $atkRequest, $status, $wasRejected) {
            $updateData = [
                'request_type'     => $request->request_type,
                'nama'             => $request->nama,
                'divisi'           => $request->divisi,
                'periode'          => $request->periode,
                'tanggal_pengajuan'=> $request->tanggal_pengajuan,
                'status_approval'  => $status,
            ];

            // Jika diajukan ulang ke Manager, bersihkan semua jejak persetujuan sebelumnya
            // agar Manager & Admin GA bisa mereview ulang seluruh item.
            if ($status === 'Menunggu Persetujuan Manager') {
                $updateData['manager_approved_by'] = null;
                $updateData['manager_approved_at'] = null;
                $updateData['approved_by']         = null;
                $updateData['approved_at']         = null;
                $updateData['manager_notes']       = null;
                $updateData['notes']               = null;
            }

            $atkRequest->update($updateData);

            // Clear old items (selalu hapus dan buat ulang agar status_approval = Pending)
            $atkRequest->items()->delete();

            // Save Standard Items
            if ($request->has('items')) {
                foreach ($request->items as $itemName => $itemData) {
                    $qty = intval($itemData['qty'] ?? 0);
                    if ($qty > 0) {
                        $desc = isset($itemData['options']) ? implode(', ', $itemData['options']) : ($itemData['custom_option'] ?? null);
                        AtkRequestItem::create([
                            'atk_request_id'  => $atkRequest->id,
                            'item_name'       => $itemName,
                            'unit'            => $itemData['unit'] ?? 'Pcs',
                            'description'     => $desc,
                            'quantity'        => $qty,
                            'is_custom'       => false,
                            'status_approval' => 'Pending',
                        ]);
                    }
                }
            }

            // Save Custom Items
            if ($request->has('custom_items')) {
                foreach ($request->custom_items as $index => $cItem) {
                    $qty = intval($cItem['qty'] ?? 0);
                    if (!empty($cItem['name']) && $qty > 0) {
                        $attachmentPath = $cItem['existing_attachment'] ?? null;
                        if ($request->hasFile("custom_items.{$index}.attachment")) {
                            $file = $request->file("custom_items.{$index}.attachment");
                            if ($file->isValid()) {
                                $fileName = time() . '_' . $file->getClientOriginalName();
                                $attachmentPath = $file->storeAs('atk-attachments', $fileName, 'public');
                            }
                        }

                        AtkRequestItem::create([
                            'atk_request_id'  => $atkRequest->id,
                            'item_name'       => $cItem['name'],
                            'unit'            => $cItem['unit'] ?? 'Pcs',
                            'description'     => $cItem['desc'] ?? null,
                            'quantity'        => $qty,
                            'is_custom'       => true,
                            'attachment'      => $attachmentPath,
                            'status_approval' => 'Pending',
                        ]);
                    }
                }
            }
        });

        $typeName = ($request->request_type === 'Rumah Tangga') ? 'Kebutuhan Rumah Tangga' : 'ATK';
        if ($wasRejected && $status === 'Menunggu Persetujuan Manager') {
            $msg = "Permintaan {$typeName} berhasil direvisi dan diajukan ulang.";
        } else {
            $msg = ($status === 'Draft') ? "Draft permintaan {$typeName} berhasil diperbarui." : "Permintaan {$typeName} berhasil diajukan.";
        }
        return redirect()->route('atk-requests.index')->with('success', $msg);
    }

    public function destroy(AtkRequest $atkRequest)
    {
        if ($atkRequest->user_id !== auth()->id() && !auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        if (!in_array($atkRequest->status_approval, ['Draft', 'Ditolak/Revisi', 'Menunggu Persetujuan'])) {
            return back()->withErrors(['error' => 'Hanya permintaan draft, menunggu persetujuan, atau ditolak/revisi yang dapat dihapus.']);
        }

        $typeName = ($atkRequest->request_type === 'Rumah Tangga') ? 'Kebutuhan Rumah Tangga' : 'ATK';
        $atkRequest->delete();

        return redirect()->route('atk-requests.index')->with('success', "Permintaan {$typeName} berhasil dihapus.");
    }

    public function approve(Request $request, AtkRequest $atkRequest)
    {
        $user = auth()->user();

        // 1. Manager Approval Step
        if ($atkRequest->status_approval === 'Menunggu Persetujuan Manager') {
            if (!$user->isManager() && !$user->isAdmin() && !$user->isSuperAdmin()) {
                abort(403, 'Aksi ini hanya untuk Operational Manager.');
            }

            $request->validate([
                'items' => 'required|array',
                'items.*.status' => 'required|in:Disetujui,Ditolak,Pending',
                'items.*.notes' => 'nullable|string',
                'manager_notes' => 'nullable|string',
            ]);

            $pendingCount = 0;
            $approvedCount = 0;

            DB::transaction(function () use ($request, $atkRequest, $user, &$pendingCount, &$approvedCount) {
                foreach ($request->items as $itemId => $itemData) {
                    $item = AtkRequestItem::where('atk_request_id', $atkRequest->id)->findOrFail($itemId);
                    $itemStatus = $itemData['status'];
                    $itemNote = $itemData['notes'] ?? null;
                    
                    $item->update([
                        'status_approval' => $itemStatus,
                        'notes' => $itemNote,
                    ]);
                }

                // Check remaining item statuses
                $allItems = AtkRequestItem::where('atk_request_id', $atkRequest->id)->get();
                $pendingCount = $allItems->filter(function($i) {
                    return $i->status_approval === 'Pending' || empty($i->status_approval);
                })->count();

                $approvedCount = $allItems->filter(function($i) {
                    return $i->status_approval === 'Disetujui';
                })->count();

                if ($pendingCount > 0) {
                    // Still has items waiting for review -> Keep status as 'Menunggu Persetujuan Manager'
                    $atkRequest->update([
                        'manager_notes' => $request->manager_notes,
                    ]);
                } else {
                    // All items reviewed -> Move to Admin GA if any approved, or Reject if all rejected
                    $newStatus = $approvedCount > 0 ? 'Menunggu Persetujuan Admin GA' : 'Ditolak/Revisi';
                    
                    $atkRequest->update([
                        'status_approval' => $newStatus,
                        'manager_approved_by' => $user->id,
                        'manager_approved_at' => now(),
                        'manager_notes' => $request->manager_notes ?? ($approvedCount > 0 ? null : 'Semua item ditolak oleh manager.'),
                    ]);
                }
            });

            if ($pendingCount > 0) {
                return redirect()->route('atk-requests.show', $atkRequest->id)
                    ->with('success', 'Persetujuan parsial item berhasil disimpan. Terdapat ' . $pendingCount . ' item lagi yang masih menunggu review.');
            }

            return redirect()->route('atk-requests.show', $atkRequest->id)
                ->with('success', 'Semua item telah selesai diproses oleh Manager.');
        }

        // 2. Admin GA Approval Step
        if ($atkRequest->status_approval === 'Menunggu Persetujuan Admin GA') {
            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                abort(403, 'Aksi ini hanya untuk Admin GA.');
            }

            $request->validate([
                'items' => 'required|array',
                'items.*.status' => 'required|in:Disetujui,Ditolak,Pending',
                'items.*.notes' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            $pendingCount = 0;

            DB::transaction(function () use ($request, $atkRequest, $user, &$pendingCount) {
                foreach ($request->items as $itemId => $itemData) {
                    $item = AtkRequestItem::where('atk_request_id', $atkRequest->id)->findOrFail($itemId);
                    $itemStatus = $itemData['status'];
                    $itemNote = $itemData['notes'] ?? null;

                    $item->update([
                        'status_approval' => $itemStatus,
                        'notes' => $itemNote,
                    ]);
                }

                $allItems = AtkRequestItem::where('atk_request_id', $atkRequest->id)->get();
                $pendingCount = $allItems->filter(function($i) {
                    return $i->status_approval === 'Pending' || empty($i->status_approval);
                })->count();

                $approvedCount = $allItems->filter(function($i) {
                    return $i->status_approval === 'Disetujui';
                })->count();

                if ($pendingCount > 0) {
                    $atkRequest->update([
                        'notes' => $request->notes,
                    ]);
                } else {
                    $newStatus = $approvedCount > 0 ? 'Disetujui' : 'Ditolak/Revisi';

                    $atkRequest->update([
                        'status_approval' => $newStatus,
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                        'notes' => $request->notes ?? ($approvedCount > 0 ? null : 'Semua item ditolak oleh Admin GA.'),
                    ]);
                }
            });

            if ($pendingCount > 0) {
                return redirect()->route('atk-requests.show', $atkRequest->id)
                    ->with('success', 'Persetujuan parsial item berhasil disimpan oleh Admin GA. Terdapat ' . $pendingCount . ' item lagi yang masih menunggu review.');
            }

            return redirect()->route('atk-requests.show', $atkRequest->id)
                ->with('success', 'Semua item telah selesai diproses oleh Admin GA.');
        }

        abort(400, 'Permintaan tidak dalam status yang dapat disetujui.');
    }

    public function approveItem(Request $request, AtkRequest $atkRequest)
    {
        $user = auth()->user();

        $request->validate([
            'item_id' => 'required|exists:atk_request_items,id',
            'status' => 'required|in:Disetujui,Ditolak,Pending',
            'notes' => 'nullable|string',
        ]);

        $item = AtkRequestItem::where('atk_request_id', $atkRequest->id)->findOrFail($request->item_id);

        $isManagerStep = ($atkRequest->status_approval === 'Menunggu Persetujuan Manager');
        $isAdminStep = ($atkRequest->status_approval === 'Menunggu Persetujuan Admin GA');

        if ($isManagerStep) {
            if (!$user->isManager() && !$user->isAdmin() && !$user->isSuperAdmin()) {
                return response()->json(['success' => false, 'message' => 'Aksi ini hanya untuk Operational Manager.'], 403);
            }
        } elseif ($isAdminStep) {
            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                return response()->json(['success' => false, 'message' => 'Aksi ini hanya untuk Admin GA.'], 403);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Permintaan tidak dalam status yang dapat di-review.'], 400);
        }

        $allCompleted = false;
        $newRequestStatus = $atkRequest->status_approval;

        DB::transaction(function () use ($request, $atkRequest, $item, $user, $isManagerStep, $isAdminStep, &$allCompleted, &$newRequestStatus) {
            $item->update([
                'status_approval' => $request->status,
                'notes' => $request->has('notes') ? $request->notes : $item->notes,
            ]);

            $allItems = AtkRequestItem::where('atk_request_id', $atkRequest->id)->get();
            $pendingCount = $allItems->filter(function ($i) {
                return $i->status_approval === 'Pending' || empty($i->status_approval);
            })->count();

            $approvedCount = $allItems->filter(function ($i) {
                return $i->status_approval === 'Disetujui';
            })->count();

            if ($pendingCount === 0) {
                $allCompleted = true;
                if ($isManagerStep) {
                    $newRequestStatus = $approvedCount > 0 ? 'Menunggu Persetujuan Admin GA' : 'Ditolak/Revisi';
                    $atkRequest->update([
                        'status_approval' => $newRequestStatus,
                        'manager_approved_by' => $user->id,
                        'manager_approved_at' => now(),
                    ]);
                } elseif ($isAdminStep) {
                    $newRequestStatus = $approvedCount > 0 ? 'Disetujui' : 'Ditolak/Revisi';
                    $atkRequest->update([
                        'status_approval' => $newRequestStatus,
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                    ]);
                }
            }
        });

        $statusLabel = [
            'Disetujui' => 'disetujui',
            'Ditolak' => 'ditolak',
            'Pending' => 'diset ke Pending'
        ][$request->status] ?? 'diperbarui';

        return response()->json([
            'success' => true,
            'message' => "Item \"{$item->item_name}\" berhasil {$statusLabel}.",
            'item_id' => $item->id,
            'item_status' => $item->status_approval,
            'all_completed' => $allCompleted,
            'new_request_status' => $newRequestStatus,
        ]);
    }

    public function reviseItem(Request $request, AtkRequest $atkRequest)
    {
        $user = auth()->user();

        if ($atkRequest->user_id !== $user->id && !$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk merevisi item ini.'], 403);
        }

        $request->validate([
            'item_id' => 'required|exists:atk_request_items,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $item = AtkRequestItem::where('atk_request_id', $atkRequest->id)->findOrFail($request->item_id);

        DB::transaction(function () use ($atkRequest, $item, $request) {
            $item->update([
                'quantity' => $request->quantity,
                'description' => $request->description ?? $item->description,
                'status_approval' => 'Pending',
            ]);

            // Reset request status back to Menunggu Persetujuan Manager so Manager can re-review
            $atkRequest->update([
                'status_approval' => 'Menunggu Persetujuan Manager',
                'manager_approved_by' => null,
                'manager_approved_at' => null,
                'approved_by' => null,
                'approved_at' => null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Item \"{$item->item_name}\" berhasil direvisi menjadi Qty {$request->quantity} dan diajukan ulang ke Manager.",
        ]);
    }

    public function reviseRejectedItems(Request $request, AtkRequest $atkRequest)
    {
        $user = auth()->user();

        if ($atkRequest->user_id !== $user->id && !$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:atk_request_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($atkRequest, $request) {
            foreach ($request->items as $itemData) {
                $item = AtkRequestItem::where('atk_request_id', $atkRequest->id)->find($itemData['id']);
                if ($item) {
                    $item->update([
                        'quantity' => $itemData['quantity'],
                        'description' => $itemData['description'] ?? $item->description,
                        'status_approval' => 'Pending',
                    ]);
                }
            }

            // Reset request status back to Menunggu Persetujuan Manager
            $atkRequest->update([
                'status_approval' => 'Menunggu Persetujuan Manager',
                'manager_approved_by' => null,
                'manager_approved_at' => null,
                'approved_by' => null,
                'approved_at' => null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Seluruh item yang ditolak berhasil direvisi dan diajukan ulang ke Manager.',
        ]);
    }

    public function updateNotes(Request $request, AtkRequest $atkRequest)
    {
        $user = auth()->user();
        $notes = $request->input('notes');

        if ($atkRequest->status_approval === 'Menunggu Persetujuan Manager' && ($user->isManager() || $user->isAdmin() || $user->isSuperAdmin())) {
            $atkRequest->update(['manager_notes' => $notes]);
            return response()->json(['success' => true, 'message' => 'Catatan umum Manager berhasil disimpan.']);
        } elseif ($atkRequest->status_approval === 'Menunggu Persetujuan Admin GA' && ($user->isAdmin() || $user->isSuperAdmin())) {
            $atkRequest->update(['notes' => $notes]);
            return response()->json(['success' => true, 'message' => 'Catatan umum Admin GA berhasil disimpan.']);
        }

        return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk memperbarui catatan.'], 403);
    }

    public function reject(Request $request, AtkRequest $atkRequest)
    {
        $user = auth()->user();
        
        $request->validate([
            'notes' => 'required|string',
        ]);

        if ($atkRequest->status_approval === 'Menunggu Persetujuan Manager') {
            if (!$user->isManager() && !$user->isAdmin() && !$user->isSuperAdmin()) {
                abort(403);
            }

            DB::transaction(function() use ($atkRequest, $request, $user) {
                $atkRequest->items()->update([
                    'status_approval' => 'Ditolak',
                    'notes' => 'Ditolak bersama pengajuan utama.'
                ]);

                $atkRequest->update([
                    'status_approval' => 'Ditolak/Revisi',
                    'manager_approved_by' => $user->id,
                    'manager_approved_at' => now(),
                    'manager_notes' => $request->notes,
                ]);
            });

            return back()->with('success', 'Permintaan telah ditolak oleh Manager.');
        }

        if ($atkRequest->status_approval === 'Menunggu Persetujuan Admin GA') {
            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                abort(403);
            }

            DB::transaction(function() use ($atkRequest, $request, $user) {
                $atkRequest->items()->update([
                    'status_approval' => 'Ditolak',
                    'notes' => 'Ditolak bersama pengajuan utama.'
                ]);

                $atkRequest->update([
                    'status_approval' => 'Ditolak/Revisi',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'notes' => $request->notes,
                ]);
            });

            return back()->with('success', 'Permintaan telah ditolak oleh Admin GA.');
        }

        abort(400, 'Permintaan tidak dalam status yang dapat ditolak.');
    }

    public function handover(Request $request, AtkRequest $atkRequest)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'diberikan_pada' => 'required|date',
            'receiver_name' => 'required|string',
        ]);

        $atkRequest->update([
            'diberikan_pada' => $request->diberikan_pada,
            'receiver_name' => $request->receiver_name,
        ]);

        return back()->with('success', 'Informasi penyerahan barang berhasil disimpan.');
    }

    public function print(AtkRequest $atkRequest)
    {
        $atkRequest->load(['user', 'approver', 'managerApprover']);
        
        // Formulir HANYA mencetak item yang sudah berstatus 'Disetujui'
        $items = $atkRequest->items()->where('status_approval', 'Disetujui')->get();

        if ($items->count() === 0) {
            return back()->with('error', 'Formulir belum dapat dicetak karena belum ada barang yang disetujui.');
        }
        
        $itemsMap = $items->where('is_custom', false)->keyBy('item_name')->toArray();
        $customItems = $items->where('is_custom', true)->values()->all();

        $standardItems = $this->getItemsForType('ATK');
        $householdItems = $this->getItemsForType('Rumah Tangga');

        return view('print.atk', compact('atkRequest', 'standardItems', 'householdItems', 'itemsMap', 'customItems'));
    }

    public function exportPdf(Request $request)
    {
        // Only Admin and Super Admin can export PDF
        if (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $query = AtkRequest::with(['items', 'user', 'approver']);

        // 1. Filter Tanggal Pengajuan
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_pengajuan', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_pengajuan', '<=', $request->end_date);
        }

        // 2. Filter Tipe Permintaan
        if ($request->filled('request_type') && $request->request_type !== 'all') {
            $query->where('request_type', $request->request_type);
        }

        // 3. Filter Status Approval
        if ($request->filled('status_approval') && $request->status_approval !== 'all') {
            $query->where('status_approval', $request->status_approval);
        }

        // 4. Filter Divisi
        if ($request->filled('divisi')) {
            $query->where('divisi', 'like', '%' . $request->divisi . '%');
        }

        // 5. Filter Item Tertentu
        if ($request->filled('item_name')) {
            $query->whereHas('items', function($q) use ($request) {
                $q->where('item_name', 'like', '%' . $request->item_name . '%');
            });
        }

        $requests = $query->orderBy('tanggal_pengajuan', 'desc')->get();

        // Check the export mode: aggregated/grouped items vs detailed transaction list
        if ($request->get('mode') === 'aggregation') {
            $aggregatedItems = [];
            foreach ($requests as $req) {
                foreach ($req->items as $item) {
                    $key = trim(strtolower($item->item_name)) . '_' . trim(strtolower($item->unit));
                    if (!isset($aggregatedItems[$key])) {
                        $aggregatedItems[$key] = [
                            'name' => $item->item_name,
                            'unit' => $item->unit,
                            'total_qty' => 0,
                            'request_count' => 0,
                        ];
                    }
                    $aggregatedItems[$key]['total_qty'] += $item->quantity;
                    $aggregatedItems[$key]['request_count'] += 1;
                }
            }

            // Sort by quantity descending
            usort($aggregatedItems, function($a, $b) {
                return $b['total_qty'] <=> $a['total_qty'];
            });

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('print.pdf-aggregation', compact('aggregatedItems', 'request'))
                      ->setPaper('a4', 'portrait');
            return $pdf->download('Rekap-Kebutuhan-Barang-' . now()->format('Y-m-d') . '.pdf');
        }

        // Default: Detailed list
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('print.pdf-detail', compact('requests', 'request'))
                  ->setPaper('a4', 'landscape');
        return $pdf->download('Laporan-Permintaan-Barang-' . now()->format('Y-m-d') . '.pdf');
    }
}
