<?php

namespace App\Http\Controllers;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\Uom;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class MasterBarangController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'barang'); // 'barang' or 'uom'
        $type = $request->query('type', 'ATK'); // default to ATK
        if (!in_array($type, ['ATK', 'Rumah Tangga'])) {
            $type = 'ATK';
        }

        $categories = AtkCategory::where('type', $type)->withCount('items')->orderBy('name')->get();
        
        $selectedCategory = null;
        $items = collect();

        if ($request->filled('category_id')) {
            $selectedCategory = AtkCategory::where('type', $type)->find($request->category_id);
            if ($selectedCategory) {
                $items = AtkItem::where('atk_category_id', $selectedCategory->id)->orderBy('name')->get();
            }
        } elseif ($categories->isNotEmpty()) {
            $selectedCategory = $categories->first();
            $items = AtkItem::where('atk_category_id', $selectedCategory->id)->orderBy('name')->get();
        }

        $uoms = Uom::withCount('atkItems')->orderBy('name')->get();

        return view('super-admin.master-barang.index', compact('categories', 'selectedCategory', 'items', 'type', 'uoms', 'tab'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:ATK,Rumah Tangga',
        ]);

        // Check if category already exists for this type
        $exists = AtkCategory::where('name', $validated['name'])->where('type', $validated['type'])->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'Kategori ini sudah terdaftar untuk tipe tersebut.'])->withInput();
        }

        $category = AtkCategory::create($validated);

        ActivityLog::log('create_atk_category', "Menambahkan kategori baru: {$category->name} ({$category->type})", null, $category->toArray());

        return redirect()->route('super-admin.master-barang.index', ['type' => $category->type, 'category_id' => $category->id])
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $category = AtkCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:ATK,Rumah Tangga',
        ]);

        // Check if other category with same name and type exists
        $exists = AtkCategory::where('name', $validated['name'])
            ->where('type', $validated['type'])
            ->where('id', '!=', $category->id)
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['name' => 'Kategori dengan nama dan tipe ini sudah terdaftar.'])->withInput();
        }

        $oldData = $category->toArray();
        $category->update($validated);

        ActivityLog::log('update_atk_category', "Mengubah kategori: {$category->name} ({$category->type})", null, [
            'old' => $oldData,
            'new' => $category->toArray()
        ]);

        return redirect()->route('super-admin.master-barang.index', ['type' => $category->type, 'category_id' => $category->id])
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $category = AtkCategory::findOrFail($id);
        $type = $category->type;
        $name = $category->name;

        ActivityLog::log('delete_atk_category', "Menghapus kategori: {$name} ({$type})", null, $category->toArray());

        $category->delete();

        return redirect()->route('super-admin.master-barang.index', ['type' => $type])
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'atk_category_id' => 'required|exists:atk_categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:100',
            'options_input' => 'nullable|string',
        ]);

        $category = AtkCategory::findOrFail($validated['atk_category_id']);

        // Parse options_input
        $options = [];
        if ($request->filled('options_input')) {
            $options = array_map('trim', explode(',', $request->options_input));
            $options = array_values(array_filter($options, fn($val) => $val !== ''));
        }

        Uom::firstOrCreate(['name' => trim($validated['unit'])]);

        $item = AtkItem::create([
            'atk_category_id' => $validated['atk_category_id'],
            'name' => $validated['name'],
            'unit' => trim($validated['unit']),
            'options' => $options,
        ]);

        ActivityLog::log('create_atk_item', "Menambahkan barang baru: {$item->name} under {$category->name}", null, $item->toArray());

        return redirect()->route('super-admin.master-barang.index', ['type' => $category->type, 'category_id' => $category->id])
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function updateItem(Request $request, $id)
    {
        $item = AtkItem::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:100',
            'options_input' => 'nullable|string',
        ]);

        $category = $item->category;

        // Parse options_input
        $options = [];
        if ($request->filled('options_input')) {
            $options = array_map('trim', explode(',', $request->options_input));
            $options = array_values(array_filter($options, fn($val) => $val !== ''));
        }

        Uom::firstOrCreate(['name' => trim($validated['unit'])]);

        $oldData = $item->toArray();
        $item->update([
            'name' => $validated['name'],
            'unit' => trim($validated['unit']),
            'options' => $options,
        ]);

        ActivityLog::log('update_atk_item', "Mengubah barang: {$item->name} under {$category->name}", null, [
            'old' => $oldData,
            'new' => $item->toArray()
        ]);

        return redirect()->route('super-admin.master-barang.index', ['type' => $category->type, 'category_id' => $category->id])
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroyItem($id)
    {
        $item = AtkItem::findOrFail($id);
        $category = $item->category;
        $name = $item->name;

        ActivityLog::log('delete_atk_item', "Menghapus barang: {$name} under {$category->name}", null, $item->toArray());

        $item->delete();

        return redirect()->route('super-admin.master-barang.index', ['type' => $category->type, 'category_id' => $category->id])
            ->with('success', 'Barang berhasil dihapus.');
    }
}
