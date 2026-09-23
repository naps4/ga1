<?php

namespace App\Http\Controllers;

use App\Models\Uom;
use App\Models\AtkItem;
use App\Models\AtkRequestItem;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UomController extends Controller
{
    /**
     * Display a listing of UOMs (API or redirect to Master Barang)
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $uoms = Uom::withCount('atkItems')->orderBy('name')->get();
            return response()->json($uoms);
        }

        return redirect()->route('super-admin.master-barang.index', ['tab' => 'uom']);
    }

    /**
     * Store a newly created UOM in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:50|unique:uoms,name',
            'description' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Nama satuan ini sudah terdaftar.',
            'name.required' => 'Nama satuan wajib diisi.',
        ]);

        $validated['name'] = trim($validated['name']);

        $uom = Uom::create($validated);

        ActivityLog::log('create_uom', "Menambahkan satuan baru: {$uom->name}", null, $uom->toArray());

        return redirect()->route('super-admin.master-barang.index', ['tab' => 'uom'])
            ->with('success', "Satuan '{$uom->name}' berhasil ditambahkan.");
    }

    /**
     * Update the specified UOM in storage.
     */
    public function update(Request $request, Uom $uom)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:50|unique:uoms,name,' . $uom->id,
            'description' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Nama satuan ini sudah terdaftar.',
            'name.required' => 'Nama satuan wajib diisi.',
        ]);

        $oldName = $uom->name;
        $newName = trim($validated['name']);
        $oldData = $uom->toArray();

        DB::transaction(function () use ($uom, $oldName, $newName, $validated) {
            $uom->update([
                'name'        => $newName,
                'description' => $validated['description'],
            ]);

            // Jika nama satuan berubah, sinkronkan ke AtkItem dan AtkRequestItem
            if (strcasecmp($oldName, $newName) !== 0) {
                AtkItem::where('unit', $oldName)->update(['unit' => $newName]);
                AtkRequestItem::where('unit', $oldName)->update(['unit' => $newName]);
            }
        });

        ActivityLog::log('update_uom', "Mengubah satuan: {$oldName} -> {$newName}", null, [
            'old' => $oldData,
            'new' => $uom->toArray()
        ]);

        return redirect()->route('super-admin.master-barang.index', ['tab' => 'uom'])
            ->with('success', "Satuan '{$newName}' berhasil diperbarui dan disinkronkan.");
    }

    /**
     * Remove the specified UOM from storage.
     */
    public function destroy(Uom $uom)
    {
        $usageCount = AtkItem::where('unit', $uom->name)->count();

        if ($usageCount > 0) {
            return redirect()->route('super-admin.master-barang.index', ['tab' => 'uom'])
                ->withErrors(['error' => "Satuan '{$uom->name}' tidak dapat dihapus karena sedang digunakan oleh {$usageCount} barang di Master Barang. Silakan ganti satuan barang tersebut terlebih dahulu."]);
        }

        $name = $uom->name;
        ActivityLog::log('delete_uom', "Menghapus satuan: {$name}", null, $uom->toArray());

        $uom->delete();

        return redirect()->route('super-admin.master-barang.index', ['tab' => 'uom'])
            ->with('success', "Satuan '{$name}' berhasil dihapus.");
    }
}
