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

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AtkRequest::with('user', 'approver');

        if ($user->isClient()) {
            $query->where('user_id', $user->id);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return view('atk-requests.index', compact('requests'));
    }

    public function create()
    {
        $user = auth()->user();
        $standardItems = self::$standardItems;
        
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

        return view('atk-requests.create', compact('standardItems', 'nama', 'divisi', 'tanggal_pengajuan', 'months'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'divisi' => 'required|string',
            'periode' => 'required|string',
            'tanggal_pengajuan' => 'required|date',
            'items' => 'nullable|array',
            'custom_items' => 'nullable|array',
        ]);

        $action = $request->input('action', 'draft');
        $status = ($action === 'submit') ? 'Menunggu Persetujuan' : 'Draft';

        DB::transaction(function () use ($request, $status) {
            $atk = AtkRequest::create([
                'user_id' => auth()->id(),
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
                foreach ($request->custom_items as $cItem) {
                    $qty = intval($cItem['qty'] ?? 0);
                    if (!empty($cItem['name']) && $qty > 0) {
                        AtkRequestItem::create([
                            'atk_request_id' => $atk->id,
                            'item_name' => $cItem['name'],
                            'unit' => $cItem['unit'] ?? 'Pcs',
                            'description' => $cItem['desc'] ?? null,
                            'quantity' => $qty,
                            'is_custom' => true,
                        ]);
                    }
                }
            }
        });

        $msg = ($status === 'Draft') ? 'Draft permintaan ATK berhasil disimpan.' : 'Permintaan ATK berhasil diajukan.';
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

        if ($atkRequest->status_approval !== 'Draft' && $atkRequest->status_approval !== 'Ditolak/Revisi') {
            return redirect()->route('atk-requests.show', $atkRequest->id)->withErrors(['error' => 'Permintaan yang sudah dikirim tidak dapat diubah.']);
        }

        $standardItems = self::$standardItems;
        
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

        return view('atk-requests.edit', compact('atkRequest', 'standardItems', 'months', 'existingItems', 'customItems'));
    }

    public function update(Request $request, AtkRequest $atkRequest)
    {
        if ($atkRequest->user_id !== auth()->id() && !auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        if ($atkRequest->status_approval !== 'Draft' && $atkRequest->status_approval !== 'Ditolak/Revisi') {
            return redirect()->route('atk-requests.show', $atkRequest->id)->withErrors(['error' => 'Permintaan yang sudah dikirim tidak dapat diubah.']);
        }

        $request->validate([
            'nama' => 'required|string',
            'divisi' => 'required|string',
            'periode' => 'required|string',
            'tanggal_pengajuan' => 'required|date',
            'items' => 'nullable|array',
            'custom_items' => 'nullable|array',
        ]);

        $action = $request->input('action', 'draft');
        $status = ($action === 'submit') ? 'Menunggu Persetujuan' : 'Draft';

        DB::transaction(function () use ($request, $atkRequest, $status) {
            $atkRequest->update([
                'nama' => $request->nama,
                'divisi' => $request->divisi,
                'periode' => $request->periode,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'status_approval' => $status,
            ]);

            // Clear old items
            $atkRequest->items()->delete();

            // Save Standard Items
            if ($request->has('items')) {
                foreach ($request->items as $itemName => $itemData) {
                    $qty = intval($itemData['qty'] ?? 0);
                    if ($qty > 0) {
                        $desc = isset($itemData['options']) ? implode(', ', $itemData['options']) : ($itemData['custom_option'] ?? null);
                        AtkRequestItem::create([
                            'atk_request_id' => $atkRequest->id,
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
                foreach ($request->custom_items as $cItem) {
                    $qty = intval($cItem['qty'] ?? 0);
                    if (!empty($cItem['name']) && $qty > 0) {
                        AtkRequestItem::create([
                            'atk_request_id' => $atkRequest->id,
                            'item_name' => $cItem['name'],
                            'unit' => $cItem['unit'] ?? 'Pcs',
                            'description' => $cItem['desc'] ?? null,
                            'quantity' => $qty,
                            'is_custom' => true,
                        ]);
                    }
                }
            }
        });

        $msg = ($status === 'Draft') ? 'Draft permintaan ATK berhasil diperbarui.' : 'Permintaan ATK berhasil diajukan.';
        return redirect()->route('atk-requests.index')->with('success', $msg);
    }

    public function destroy(AtkRequest $atkRequest)
    {
        if ($atkRequest->user_id !== auth()->id() && !auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        if ($atkRequest->status_approval !== 'Draft') {
            return back()->withErrors(['error' => 'Hanya draft permintaan yang dapat dihapus.']);
        }

        $atkRequest->delete();

        return redirect()->route('atk-requests.index')->with('success', 'Permintaan ATK berhasil dihapus.');
    }

    public function approve(AtkRequest $atkRequest)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $atkRequest->update([
            'status_approval' => 'Disetujui',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Permintaan ATK berhasil disetujui.');
    }

    public function reject(Request $request, AtkRequest $atkRequest)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'notes' => 'required|string',
        ]);

        $atkRequest->update([
            'status_approval' => 'Ditolak/Revisi',
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Permintaan ATK telah ditolak / dikembalikan untuk revisi.');
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
        $atkRequest->load('user', 'approver', 'items');
        
        // Map items for rendering in the 53 template
        $itemsMap = $atkRequest->items->where('is_custom', false)->keyBy('item_name')->toArray();
        $customItems = $atkRequest->items->where('is_custom', true)->values()->all();

        $standardItems = self::$standardItems;

        return view('print.atk', compact('atkRequest', 'standardItems', 'itemsMap', 'customItems'));
    }
}
