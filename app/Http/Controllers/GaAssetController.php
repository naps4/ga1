<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Location;
use App\Models\Plant;
use App\Services\GaAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GaAssetController extends Controller
{
    protected GaAssetService $assetService;

    public function __construct(GaAssetService $assetService)
    {
        $this->assetService = $assetService;
        $this->middleware('auth')->except(['apiMaster', 'apiPlant', 'apiLampiranC', 'aiToolDefinitions', 'aiToolCall']);
    }

    // ── Endpoint 1: Master Aset (ASET ALL 2026) ──────────────────────────────

    public function index(Request $request)
    {
        $query = Asset::with(['originPlant', 'currentPlant', 'department', 'location', 'category'])
            ->active();

        if ($request->filled('group') && $request->group !== 'all') {
            $query->where('asset_group', strtoupper($request->group));
        }

        if ($request->filled('plant') && $request->plant !== 'all') {
            $query->where('current_plant_code', $request->plant);
        }

        if ($request->filled('dept') && $request->dept !== 'all') {
            $query->where('department_id', $request->dept);
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category_code', $request->category);
        }

        if ($request->filled('condition') && $request->condition !== 'all') {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('asset_code', 'like', "%{$s}%")
                  ->orWhere('item_name', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%")
                  ->orWhere('specification', 'like', "%{$s}%");
            });
        }

        $assets = $query->orderBy('id', 'asc')->paginate(25)->withQueryString();

        $plants = Plant::all();
        $departments = Department::all();
        $categories = AssetCategory::all();
        $locations = Location::with('plant')->orderBy('plant_code')->orderBy('room_name')->get();

        // Ringkasan Finansial Dinamis
        $totalActive = Asset::active()->count();
        $totalValuation = (float) Asset::active()->sum('total_price');
        $incompletePriceCount = Asset::active()->where('is_price_complete', false)->count();

        return view('assets.index', compact(
            'assets', 'plants', 'departments', 'categories', 'locations',
            'totalActive', 'totalValuation', 'incompletePriceCount'
        ));
    }

    public function apiMaster(Request $request)
    {
        $query = Asset::with(['originPlant', 'currentPlant', 'department', 'location', 'category'])
            ->active();

        if ($request->filled('group')) {
            $query->where('asset_group', strtoupper($request->group));
        }
        if ($request->filled('plant')) {
            $query->where('current_plant_code', $request->plant);
        }
        if ($request->filled('dept')) {
            $query->where('department_id', $request->dept);
        }
        if ($request->filled('category')) {
            $query->where('category_code', $request->category);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('asset_code', 'like', "%{$s}%")
                  ->orWhere('item_name', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%");
            });
        }

        $assets = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $assets,
        ]);
    }

    // ── Endpoint 2: Plant A & Plant B Views ───────────────────────────────────

    public function plantView(Request $request, string $plantCode)
    {
        $plant = Plant::where('code', $plantCode)->firstOrFail();

        $query = Asset::with(['department', 'location', 'category', 'originPlant'])
            ->active()
            ->where('current_plant_code', $plantCode);

        // Filter per Tab Departemen jika dipilih
        $selectedDeptId = $request->input('dept');
        if ($selectedDeptId) {
            $query->where('department_id', $selectedDeptId);
        }

        // Search Bar: asset_code, item_name, brand, specification
        $search = $request->input('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('specification', 'like', "%{$search}%");
            });
        }

        // Filter Lokasi / Ruangan
        $locationId = $request->input('location_id');
        if (!empty($locationId)) {
            $query->where('location_id', $locationId);
        }

        // Filter Kategori
        $categoryCode = $request->input('category');
        if (!empty($categoryCode)) {
            $query->where('category_code', $categoryCode);
        }

        // Filter Kondisi (Baik / Rusak / Perbaikan)
        $condition = $request->input('condition');
        if (!empty($condition)) {
            $query->where('condition', $condition);
        }

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 25;
        }

        $assets = $query->orderBy('department_id')->orderBy('item_name')->paginate($perPage)->withQueryString();

        // Tab Departemen yang memiliki aset di plant ini
        $departmentTabs = Department::whereHas('assets', function ($q) use ($plantCode) {
            $q->where('current_plant_code', $plantCode)->active();
        })->withCount(['assets' => function ($q) use ($plantCode) {
            $q->where('current_plant_code', $plantCode)->active();
        }])->get();

        // Hitung KPI & Ringkasan Performa Fasilitas
        $totalItems = Asset::active()->where('current_plant_code', $plantCode)->count();
        $totalValuation = (float) Asset::active()->where('current_plant_code', $plantCode)->sum('total_price');
        $baikCount = Asset::active()->where('current_plant_code', $plantCode)->where('condition', 'Baik')->count();
        $rusakCount = Asset::active()->where('current_plant_code', $plantCode)->where('condition', 'Rusak')->count();
        $percentBaik = $totalItems > 0 ? round(($baikCount / $totalItems) * 100, 1) : 100;

        // Data master untuk filter dropdown dan modal
        $locations = Location::where('plant_code', $plantCode)->orderBy('room_name')->get();
        $categories = AssetCategory::orderBy('code')->get();
        $departments = Department::orderBy('name')->get();
        $plants = Plant::all();

        return view('assets.plant', compact(
            'plant', 'assets', 'departmentTabs', 'selectedDeptId',
            'totalItems', 'totalValuation', 'baikCount', 'rusakCount', 'percentBaik',
            'search', 'locationId', 'categoryCode', 'condition', 'locations', 'categories', 'departments', 'plants', 'perPage'
        ));
    }

    public function apiPlant(Request $request, string $plantCode)
    {
        $plant = Plant::where('code', $plantCode)->firstOrFail();

        $query = Asset::with(['department', 'location', 'category'])
            ->active()
            ->where('current_plant_code', $plantCode);

        if ($request->filled('dept')) {
            $query->where('department_id', $request->dept);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('asset_code', 'like', "%{$s}%")
                  ->orWhere('item_name', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%")
                  ->orWhere('specification', 'like', "%{$s}%");
            });
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('category')) {
            $query->where('category_code', $request->category);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $totalItems = (clone $query)->count();
        $totalValuation = (float) (clone $query)->sum('total_price');
        $baikCount = (clone $query)->where('condition', 'Baik')->count();
        $rusakCount = (clone $query)->where('condition', 'Rusak')->count();

        $assets = $query->paginate(50);

        return response()->json([
            'success' => true,
            'plant'   => $plant,
            'kpi'     => [
                'total_items'     => $totalItems,
                'total_valuation' => $totalValuation,
                'baik_count'      => $baikCount,
                'rusak_count'     => $rusakCount,
                'percent_baik'    => $totalItems > 0 ? round(($baikCount / $totalItems) * 100, 1) : 100,
            ],
            'data'    => $assets,
        ]);
    }

    // ── Endpoint 3: Lampiran C (Form Daftar Aset Ruangan CM-03/GA/002.C.01) ───

    public function lampiranC(Request $request, ?int $locationId = null)
    {
        $locations = Location::with('plant', 'department')->orderBy('plant_code')->orderBy('room_name')->get();

        $selectedLocation = $locationId ? Location::with('plant', 'department')->findOrFail($locationId) : $locations->first();

        $aggregatedItems = [];
        $lastUpdated = now()->translatedFormat('d F Y');

        if ($selectedLocation) {
            // Query Agregasi SUM per Nama & Kode Kategori
            $aggregatedItems = DB::table('assets as a')
                ->join('asset_categories as c', 'a.category_code', '=', 'c.code')
                ->selectRaw('
                    a.item_name as jenis_aset,
                    c.code as kode,
                    SUM(a.qty) as jumlah,
                    MAX(a.updated_at) as last_updated
                ')
                ->where('a.location_id', $selectedLocation->id)
                ->where('a.status', 'AKTIF')
                ->groupBy('a.item_name', 'c.code')
                ->orderBy('c.code', 'asc')
                ->orderBy('a.item_name', 'asc')
                ->get();

            // Ambil tanggal update terakhir dari aset pada ruangan ini
            $latestAssetDate = Asset::where('location_id', $selectedLocation->id)->max('updated_at');
            if ($latestAssetDate) {
                $lastUpdated = \Carbon\Carbon::parse($latestAssetDate)->translatedFormat('d F Y');
            }
        }

        return view('assets.lampiran-c', compact(
            'locations', 'selectedLocation', 'aggregatedItems', 'lastUpdated'
        ));
    }

    public function printLampiranC(int $locationId)
    {
        $location = Location::with('plant', 'department')->findOrFail($locationId);

        $aggregatedItems = DB::table('assets as a')
            ->join('asset_categories as c', 'a.category_code', '=', 'c.code')
            ->selectRaw('
                a.item_name as jenis_aset,
                c.code as kode,
                SUM(a.qty) as jumlah
            ')
            ->where('a.location_id', $location->id)
            ->where('a.status', 'AKTIF')
            ->groupBy('a.item_name', 'c.code')
            ->orderBy('c.code', 'asc')
            ->orderBy('a.item_name', 'asc')
            ->get();

        $latestAssetDate = Asset::where('location_id', $location->id)->max('updated_at');
        $lastUpdated = $latestAssetDate ? \Carbon\Carbon::parse($latestAssetDate)->translatedFormat('d F Y') : now()->translatedFormat('d F Y');

        return view('assets.lampiran-c-print', compact('location', 'aggregatedItems', 'lastUpdated'));
    }

    public function apiLampiranC(int $locationId)
    {
        $location = Location::with('plant', 'department')->findOrFail($locationId);

        $aggregatedItems = DB::table('assets as a')
            ->join('asset_categories as c', 'a.category_code', '=', 'c.code')
            ->selectRaw('
                a.item_name as jenis_aset,
                c.code as kode,
                SUM(a.qty) as jumlah
            ')
            ->where('a.location_id', $location->id)
            ->where('a.status', 'AKTIF')
            ->groupBy('a.item_name', 'c.code')
            ->orderBy('c.code', 'asc')
            ->orderBy('a.item_name', 'asc')
            ->get();

        return response()->json([
            'success'       => true,
            'doc_reference' => 'CM-03/GA/002.C.01',
            'location'      => $location,
            'items'         => $aggregatedItems,
        ]);
    }

    // ── Endpoint 4: Core Actions (Create, Update, Mutate, Dispose) ───────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'          => 'required|string|max:255',
            'item_abbr'          => 'required|string|max:10',
            'brand'              => 'nullable|string|max:150',
            'specification'      => 'nullable|string',
            'current_plant_code' => 'required|exists:plants,code',
            'department_id'      => 'required|exists:departments,id',
            'location_id'        => 'nullable|exists:locations,id',
            'category_code'      => 'required|exists:asset_categories,code',
            'qty'                => 'required|integer|min:1',
            'unit_price'         => 'required|numeric|min:0',
            'purchase_date'      => 'required|date',
            'useful_life_years'  => 'nullable|integer|min:1|max:30',
            'tracking_mode'      => 'required|in:INDIVIDUAL,BULK',
            'condition'          => 'nullable|in:Baik,Rusak,Perbaikan',
        ]);

        $created = $this->assetService->createAsset($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => count($created) . ' aset berhasil dibuat.', 'data' => $created], 201);
        }

        return back()->with('success', count($created) . ' aset berhasil ditambahkan ke inventaris.');
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'item_name'         => 'required|string|max:255',
            'brand'             => 'nullable|string|max:150',
            'specification'     => 'nullable|string',
            'unit_price'        => 'required|numeric|min:0',
            'qty'               => 'required|integer|min:1',
            'useful_life_years' => 'required|integer|min:1',
            'condition'         => 'required|in:Baik,Rusak,Perbaikan',
            'condition_note'    => 'nullable|string',
            'department_id'     => 'nullable|exists:departments,id',
            'location_id'       => 'nullable|exists:locations,id',
        ]);

        $updated = $this->assetService->updateAsset($asset, $validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data aset berhasil diperbarui.', 'data' => $updated]);
        }

        return back()->with('success', "Aset {$asset->asset_code} berhasil diperbarui.");
    }

    public function mutate(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'target_plant_code'  => 'required|exists:plants,code',
            'target_location_id' => 'nullable|exists:locations,id',
            'target_dept_id'     => 'nullable|exists:departments,id',
            'pic_name'           => 'required|string|max:255',
            'reason'             => 'required|string',
            'notes'              => 'nullable|string',
            'mutation_date'      => 'nullable|date',
        ]);

        $mutation = $this->assetService->mutateAsset($asset, $validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Aset {$asset->asset_code} berhasil dimutasikan ke Plant {$validated['target_plant_code']}.", 'data' => $mutation]);
        }

        return back()->with('success', "Aset {$asset->asset_code} berhasil dipindahkan ke Plant {$validated['target_plant_code']}.");
    }

    public function updateCondition(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'condition'      => 'required|in:Baik,Rusak,Perbaikan',
            'condition_note' => 'nullable|string',
        ]);

        $updated = $this->assetService->updateCondition($asset, $validated['condition'], $validated['condition_note'] ?? null);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Kondisi aset diperbarui menjadi {$validated['condition']}.", 'data' => $updated]);
        }

        return back()->with('success', "Status kondisi aset {$asset->asset_code} berhasil diupdate.");
    }

    public function destroy(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'disposal_reason' => 'required|string|max:1000',
        ]);

        $this->assetService->disposeAsset($asset, $validated['disposal_reason']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Aset {$asset->asset_code} berhasil dihapus (disposed)."]);
        }

        return back()->with('success', "Aset {$asset->asset_code} berhasil diarsipkan sebagai disposed.");
    }

    // ── Endpoint 5: AI Agent Tool-Calling Interfaces ─────────────────────────

    public function aiToolDefinitions(\App\Services\GaAiAssetAgentService $aiService)
    {
        return response()->json([
            'system_prompt' => $aiService->getSystemPrompt(),
            'tools'         => $aiService->getToolDefinitions(),
        ]);
    }

    public function aiToolCall(Request $request, \App\Services\GaAiAssetAgentService $aiService)
    {
        $request->validate([
            'tool'      => 'required|string',
            'arguments' => 'nullable|array',
        ]);

        $result = $aiService->callTool($request->input('tool'), $request->input('arguments', []));

        return response()->json([
            'success' => !isset($result['error']),
            'result'  => $result,
        ]);
    }
}
