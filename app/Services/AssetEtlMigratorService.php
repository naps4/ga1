<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMutation;
use App\Models\Department;
use App\Models\Location;
use App\Models\Plant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AssetEtlMigratorService
{
    protected array $departmentCache = [];
    protected array $categoryCache = [];
    protected array $locationCache = [];

    public function __construct()
    {
        $this->loadCaches();
    }

    protected function loadCaches(): void
    {
        $this->departmentCache = Department::all()->keyBy('code')->all();
        $this->categoryCache = AssetCategory::all()->keyBy('code')->all();
        $this->locationCache = Location::all()->all();
    }

    /**
     * Jalankan seluruh alur ETL 4 File Excel secara berurutan.
     */
    public function migrateAll(): array
    {
        $stats = [
            'utama_count'        => 0,
            'terpisah_count'     => 0,
            'bergerak_count'     => 0,
            'plant_a_enriched'   => 0,
            'plant_b_enriched'   => 0,
            'overlap_resolved'   => 0,
            'orphan_plant_a'     => 0,
            'orphan_plant_b'     => 0,
            'locations_mapped'   => 0,
            'total_assets'       => 0,
            'total_valuation'    => 0,
        ];

        DB::beginTransaction();
        try {
            // Kosongkan data aset & mutasi sebelum re-migrasi bersih
            AssetMutation::query()->delete();
            Asset::query()->delete();

            // Step 1: Ekstraksi ASET ALL 2026.xlsx
            $allStats = $this->importAsetAll2026();
            $stats['utama_count']    = $allStats['utama'];
            $stats['terpisah_count'] = $allStats['terpisah'];
            $stats['bergerak_count'] = $allStats['bergerak'];

            // Step 2: Rekonsiliasi & Enrich PLANT A.xlsx
            $paStats = $this->reconcilePlantA();
            $stats['plant_a_enriched'] = $paStats['enriched'];
            $stats['orphan_plant_a']   = $paStats['orphans'];

            // Step 3: Rekonsiliasi, Enrich & Resolusi Overlap PLANT B.xlsx
            $pbStats = $this->reconcilePlantB();
            $stats['plant_b_enriched'] = $pbStats['enriched'];
            $stats['overlap_resolved'] = $pbStats['overlap_resolved'];
            $stats['orphan_plant_b']   = $pbStats['orphans'];

            // Step 4: Pemetaan Sub-Ruangan Lampiran C
            $stats['locations_mapped'] = $this->mapLampiranC();

            // Total kalkulasi akhir
            $stats['total_assets']    = Asset::count();
            $stats['total_valuation'] = (float) Asset::active()->sum('total_price');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Asset Migration Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }

        return $stats;
    }

    /**
     * Ambil nilai terhitung dari sel spreadsheet dengan aman dari error formula.
     */
    protected function getCellCalculatedValue($sheet, string $cellCoordinate)
    {
        try {
            $cell = $sheet->getCell($cellCoordinate);
            if (!$cell) return null;
            $val = $cell->getCalculatedValue();
            if (is_string($val) && (str_starts_with($val, '#') || str_starts_with($val, '='))) {
                return null;
            }
            return $val;
        } catch (\Throwable) {
            try {
                return $sheet->getCell($cellCoordinate)->getValue();
            } catch (\Throwable) {
                return null;
            }
        }
    }

    /**
     * Step 1: Ekstraksi 3 sheet ASET ALL 2026.xlsx
     */
    public function importAsetAll2026(): array
    {
        $filePath = storage_path('app/assets_migration/ASET_ALL_2026.xlsx');
        if (!file_exists($filePath)) {
            throw new \Exception("File ASET_ALL_2026.xlsx tidak ditemukan di {$filePath}");
        }

        $spreadsheet = IOFactory::load($filePath);
        $counts = ['utama' => 0, 'terpisah' => 0, 'bergerak' => 0];

        // 1.1 Sheet 'Data All Aset' -> UTAMA
        $sheetAll = $spreadsheet->getSheetByName('Data All Aset');
        if ($sheetAll) {
            $highestRow = $sheetAll->getHighestRow();
            for ($r = 7; $r <= $highestRow; $r++) {
                $rawId   = trim((string) $sheetAll->getCell("C{$r}")->getValue());
                $name    = trim((string) $sheetAll->getCell("D{$r}")->getValue());
                if (empty($rawId) || empty($name)) {
                    continue;
                }

                $code = $this->cleanAssetCode($rawId);
                $brand = trim((string) $sheetAll->getCell("E{$r}")->getValue()) ?: null;
                $source = trim((string) $sheetAll->getCell("F{$r}")->getValue()) ?: 'RAB';
                $rawCat = trim((string) $sheetAll->getCell("G{$r}")->getValue());
                $rawDept = trim((string) $sheetAll->getCell("H{$r}")->getValue());
                $rawDate = $sheetAll->getCell("I{$r}")->getValue();
                $qty = (int) $this->getCellCalculatedValue($sheetAll, "J{$r}") ?: 1;
                $unitPrice = $this->cleanNumber($this->getCellCalculatedValue($sheetAll, "K{$r}"));
                $totalPrice = $this->cleanNumber($this->getCellCalculatedValue($sheetAll, "L{$r}"));
                $usefulLife = (int) $this->getCellCalculatedValue($sheetAll, "M{$r}") ?: 4;

                if ($totalPrice <= 0 && $unitPrice > 0) {
                    $totalPrice = $unitPrice * $qty;
                }
                if ($unitPrice <= 0 && $totalPrice > 0 && $qty > 0) {
                    $unitPrice = $totalPrice / $qty;
                }

                $plantCode = $this->detectPlantFromCode($code, '01');
                $deptId = $this->resolveDepartmentId($rawDept, $code);
                $catCode = $this->resolveCategoryCode($rawCat, $code);
                $purchaseDate = $this->normalizeDate($rawDate, $code);

                Asset::updateOrCreate(
                    ['asset_code' => $code],
                    [
                        'asset_group'        => 'UTAMA',
                        'item_name'          => $name,
                        'brand'              => $brand,
                        'origin_plant_code'  => $plantCode,
                        'current_plant_code' => $plantCode,
                        'department_id'      => $deptId,
                        'category_code'      => $catCode,
                        'acquisition_source' => $source,
                        'purchase_date'      => $purchaseDate,
                        'qty'                => $qty,
                        'unit_price'         => $unitPrice,
                        'total_price'        => $totalPrice,
                        'useful_life_years'  => $usefulLife,
                        'condition'          => 'Baik',
                        'tracking_mode'      => 'INDIVIDUAL',
                        'is_price_complete'  => ($unitPrice > 0),
                        'status'             => 'AKTIF',
                        'raw_data_source'    => 'ASET_ALL_DATA_ALL',
                    ]
                );
                $counts['utama']++;
            }
        }

        // 1.2 Sheet 'Aset Tepisah' -> TERPISAH (BULK)
        $sheetTerpisah = $spreadsheet->getSheetByName('Aset Tepisah');
        if ($sheetTerpisah) {
            $highestRow = $sheetTerpisah->getHighestRow();
            for ($r = 6; $r <= $highestRow; $r++) {
                $rawId = trim((string) $sheetTerpisah->getCell("C{$r}")->getValue());
                $name  = trim((string) $sheetTerpisah->getCell("D{$r}")->getValue());
                if (empty($rawId) || empty($name)) {
                    continue;
                }

                $code = $this->cleanAssetCode($rawId);
                $brand = trim((string) $sheetTerpisah->getCell("E{$r}")->getValue()) ?: null;
                $source = trim((string) $sheetTerpisah->getCell("F{$r}")->getValue()) ?: 'RAB';
                $rawCat = trim((string) $sheetTerpisah->getCell("G{$r}")->getValue());
                $rawDept = trim((string) $sheetTerpisah->getCell("H{$r}")->getValue());
                $rawDate = $sheetTerpisah->getCell("I{$r}")->getValue();
                $usefulLife = (int) $this->getCellCalculatedValue($sheetTerpisah, "J{$r}") ?: 4;
                $unitPrice = $this->cleanNumber($this->getCellCalculatedValue($sheetTerpisah, "K{$r}"));
                $qty = (int) $this->getCellCalculatedValue($sheetTerpisah, "L{$r}") ?: 1;
                $totalPrice = $this->cleanNumber($this->getCellCalculatedValue($sheetTerpisah, "M{$r}"));

                if ($totalPrice <= 0 && $unitPrice > 0) {
                    $totalPrice = $unitPrice * $qty;
                }
                if ($unitPrice <= 0 && $totalPrice > 0 && $qty > 0) {
                    $unitPrice = $totalPrice / $qty;
                }

                $plantCode = $this->detectPlantFromCode($code, '01');
                $deptId = $this->resolveDepartmentId($rawDept, $code);
                $catCode = $this->resolveCategoryCode($rawCat, $code);
                $purchaseDate = $this->normalizeDate($rawDate, $code);

                $existing = Asset::where('asset_code', $code)->first();
                if ($existing) {
                    if ($qty > 1 && $existing->qty === 1) {
                        $existing->update([
                            'asset_group'   => 'TERPISAH',
                            'qty'           => $qty,
                            'total_price'   => $totalPrice ?: ($unitPrice * $qty),
                            'tracking_mode' => 'BULK',
                        ]);
                    }
                } else {
                    Asset::create([
                        'asset_code'         => $code,
                        'asset_group'        => 'TERPISAH',
                        'item_name'          => $name,
                        'brand'              => $brand,
                        'origin_plant_code'  => $plantCode,
                        'current_plant_code' => $plantCode,
                        'department_id'      => $deptId,
                        'category_code'      => $catCode,
                        'acquisition_source' => $source,
                        'purchase_date'      => $purchaseDate,
                        'qty'                => $qty,
                        'unit_price'         => $unitPrice,
                        'total_price'        => $totalPrice ?: ($unitPrice * $qty),
                        'useful_life_years'  => $usefulLife,
                        'condition'          => 'Baik',
                        'tracking_mode'      => 'BULK',
                        'is_price_complete'  => ($unitPrice > 0),
                        'status'             => 'AKTIF',
                        'raw_data_source'    => 'ASET_ALL_TERPISAH',
                    ]);
                }
                $counts['terpisah']++;
            }
        }

        // 1.3 Sheet 'Aset Bergerak' -> BERGERAK (Kendaraan)
        $sheetBergerak = $spreadsheet->getSheetByName('Aset Bergerak');
        if ($sheetBergerak) {
            $highestRow = $sheetBergerak->getHighestRow();
            for ($r = 6; $r <= $highestRow; $r++) {
                $rawId = trim((string) $sheetBergerak->getCell("C{$r}")->getValue());
                $name  = trim((string) $sheetBergerak->getCell("D{$r}")->getValue());
                if (empty($rawId) || empty($name)) {
                    continue;
                }

                $code = $this->cleanAssetCode($rawId);
                $brand = trim((string) $sheetBergerak->getCell("E{$r}")->getValue()) ?: null;
                $source = trim((string) $sheetBergerak->getCell("F{$r}")->getValue()) ?: 'RAB';
                $rawDept = trim((string) $sheetBergerak->getCell("H{$r}")->getValue());
                $rawDate = $sheetBergerak->getCell("I{$r}")->getValue();
                $usefulLife = (int) $this->getCellCalculatedValue($sheetBergerak, "J{$r}") ?: 5;
                $unitPrice = $this->cleanNumber($this->getCellCalculatedValue($sheetBergerak, "K{$r}"));
                $qty = (int) $this->getCellCalculatedValue($sheetBergerak, "L{$r}") ?: 1;
                $totalPrice = $this->cleanNumber($this->getCellCalculatedValue($sheetBergerak, "M{$r}"));

                if ($totalPrice <= 0 && $unitPrice > 0) {
                    $totalPrice = $unitPrice * $qty;
                }

                $deptId = $this->resolveDepartmentId($rawDept, $code);
                $purchaseDate = $this->normalizeDate($rawDate, $code);

                Asset::updateOrCreate(
                    ['asset_code' => $code],
                    [
                        'asset_group'        => 'BERGERAK',
                        'item_name'          => $name,
                        'brand'              => $brand,
                        'origin_plant_code'  => '00',
                        'current_plant_code' => '00',
                        'department_id'      => $deptId,
                        'category_code'      => '03', // Kendaraan
                        'acquisition_source' => $source,
                        'purchase_date'      => $purchaseDate,
                        'qty'                => $qty,
                        'unit_price'         => $unitPrice,
                        'total_price'        => $totalPrice ?: ($unitPrice * $qty),
                        'useful_life_years'  => $usefulLife,
                        'condition'          => 'Baik',
                        'tracking_mode'      => 'INDIVIDUAL',
                        'is_price_complete'  => ($unitPrice > 0),
                        'status'             => 'AKTIF',
                        'raw_data_source'    => 'ASET_ALL_BERGERAK',
                    ]
                );
                $counts['bergerak']++;
            }
        }

        return $counts;
    }

    /**
     * Step 2: Rekonsiliasi & Enrich dengan PLANT A.xlsx
     */
    public function reconcilePlantA(): array
    {
        $filePath = storage_path('app/assets_migration/PLANT_A.xlsx');
        if (!file_exists($filePath)) {
            return ['enriched' => 0, 'orphans' => 0];
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('ALL DEPT') ?: $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        $enriched = 0;
        $orphans = 0;
        $currentDivisi = 'RnD';

        for ($r = 5; $r <= $highestRow; $r++) {
            $divisiVal = trim((string) $sheet->getCell("B{$r}")->getValue());
            if (!empty($divisiVal)) {
                $currentDivisi = $divisiVal;
            }

            $rawId = trim((string) $sheet->getCell("F{$r}")->getValue());
            $name  = trim((string) $sheet->getCell("D{$r}")->getValue());
            if (empty($rawId) && empty($name)) {
                continue;
            }
            if (empty($rawId)) {
                continue;
            }

            $code = $this->cleanAssetCode($rawId);
            $brand = trim((string) $sheet->getCell("E{$r}")->getValue()) ?: null;
            $rawCond = trim((string) $sheet->getCell("I{$r}")->getValue());
            [$condition, $condNote] = $this->parseCondition($rawCond);

            $asset = Asset::where('asset_code', $code)->first();
            if ($asset) {
                $asset->update([
                    'brand'          => $asset->brand ?: $brand,
                    'condition'      => $condition,
                    'condition_note' => $condNote ?: $asset->condition_note,
                ]);
                $enriched++;
            } else {
                $source = trim((string) $sheet->getCell("G{$r}")->getValue()) ?: 'RAB';
                $rawDate = $sheet->getCell("H{$r}")->getValue();
                $qty = (int) $this->getCellCalculatedValue($sheet, "J{$r}") ?: 1;
                $unitPrice = $this->cleanNumber($this->getCellCalculatedValue($sheet, "K{$r}"));
                $totalPrice = $this->cleanNumber($this->getCellCalculatedValue($sheet, "L{$r}"));
                $rawManfaat = $sheet->getCell("M{$r}")->getValue();
                $usefulLife = $this->parseUsefulLife($rawManfaat, 4);

                if ($totalPrice <= 0 && $unitPrice > 0) {
                    $totalPrice = $unitPrice * $qty;
                }
                if ($unitPrice <= 0 && $totalPrice > 0 && $qty > 0) {
                    $unitPrice = $totalPrice / $qty;
                }

                $deptId = $this->resolveDepartmentId($currentDivisi, $code);
                $catCode = $this->resolveCategoryCode(null, $code);
                $purchaseDate = $this->normalizeDate($rawDate, $code);

                Asset::create([
                    'asset_code'         => $code,
                    'asset_group'        => 'UTAMA',
                    'item_name'          => $name,
                    'brand'              => $brand,
                    'origin_plant_code'  => '01',
                    'current_plant_code' => '01',
                    'department_id'      => $deptId,
                    'category_code'      => $catCode,
                    'acquisition_source' => $source,
                    'purchase_date'      => $purchaseDate,
                    'qty'                => $qty,
                    'unit_price'         => $unitPrice,
                    'total_price'        => $totalPrice ?: ($unitPrice * $qty),
                    'useful_life_years'  => $usefulLife,
                    'condition'          => $condition,
                    'condition_note'     => $condNote,
                    'tracking_mode'      => ($qty > 1) ? 'BULK' : 'INDIVIDUAL',
                    'is_price_complete'  => ($unitPrice > 0),
                    'status'             => 'AKTIF',
                    'raw_data_source'    => 'PLANT_A_ORPHAN',
                ]);
                $orphans++;
            }
        }

        return ['enriched' => $enriched, 'orphans' => $orphans];
    }

    /**
     * Step 3: Rekonsiliasi, Enrich & Resolusi Overlap PLANT B.xlsx
     */
    public function reconcilePlantB(): array
    {
        $filePath = storage_path('app/assets_migration/PLANT_B.xlsx');
        if (!file_exists($filePath)) {
            return ['enriched' => 0, 'overlap_resolved' => 0, 'orphans' => 0];
        }

        $spreadsheet = IOFactory::load($filePath);
        $enriched = 0;
        $overlapResolved = 0;
        $orphans = 0;

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $sheet->getHighestRow();

            $startRow = 3;
            for ($r = 1; $r <= 6; $r++) {
                $val = strtolower(trim((string) $sheet->getCell("A{$r}")->getValue()));
                if ($val === 'item id' || str_contains($val, 'id')) {
                    $startRow = $r + 1;
                    break;
                }
            }

            for ($r = $startRow; $r <= $highestRow; $r++) {
                $rawId = trim((string) $sheet->getCell("A{$r}")->getValue());
                $name  = trim((string) $sheet->getCell("B{$r}")->getValue());
                if (empty($rawId) && empty($name)) {
                    continue;
                }
                if (empty($rawId)) {
                    continue;
                }

                $code = $this->cleanAssetCode($rawId);
                $spec = trim((string) $sheet->getCell("C{$r}")->getValue()) ?: null;
                $rawCond = trim((string) $sheet->getCell("D{$r}")->getValue());
                [$condition, $condNote] = $this->parseCondition($rawCond);

                $asset = Asset::where('asset_code', $code)->first();

                if ($asset) {
                    $asset->update([
                        'specification'  => $asset->specification ?: $spec,
                        'condition'      => $condition,
                        'condition_note' => $condNote ?: $asset->condition_note,
                    ]);
                    $enriched++;

                    // Resolusi 66 Data Tumpang Tindih (Overlap Plant A & B):
                    // Jika kode aset lahir di Plant A (-01.xx) namun tercatat aktif di Plant B:
                    // Tetapkan origin_plant = '01', current_plant = '02'
                    if (str_ends_with($code, '-01.01') || str_ends_with($code, '-01.02') || str_ends_with($code, '-01.04') || str_ends_with($code, '-01.03') || $asset->origin_plant_code === '01') {
                        if ($asset->current_plant_code !== '02') {
                            $asset->update([
                                'origin_plant_code'  => '01',
                                'current_plant_code' => '02',
                            ]);

                            AssetMutation::firstOrCreate(
                                [
                                    'asset_id'        => $asset->id,
                                    'from_plant_code' => '01',
                                    'to_plant_code'   => '02',
                                ],
                                [
                                    'mutation_date'   => Carbon::create(2025, 1, 1),
                                    'pic_name'        => 'Sistem Migrasi GA',
                                    'reason'          => 'Migrasi Awal: Aset berkode Plant A tercatat aktif di Plant B',
                                    'notes'           => "Ditemukan pada sheet Plant B ({$sheetName})",
                                ]
                            );
                            $overlapResolved++;
                        }
                    }
                } else {
                    $source = trim((string) $sheet->getCell("E{$r}")->getValue()) ?: 'RAB';
                    $rawDate = $sheet->getCell("F{$r}")->getValue();
                    $rawCat = trim((string) $sheet->getCell("G{$r}")->getValue());
                    $qty = (int) $this->getCellCalculatedValue($sheet, "H{$r}") ?: 1;
                    $unitPrice = $this->cleanNumber($this->getCellCalculatedValue($sheet, "I{$r}"));
                    $totalPrice = $this->cleanNumber($this->getCellCalculatedValue($sheet, "J{$r}"));
                    $usefulLife = (int) $this->getCellCalculatedValue($sheet, "K{$r}") ?: 4;

                    if ($totalPrice <= 0 && $unitPrice > 0) {
                        $totalPrice = $unitPrice * $qty;
                    }
                    if ($unitPrice <= 0 && $totalPrice > 0 && $qty > 0) {
                        $unitPrice = $totalPrice / $qty;
                    }

                    $originPlant = $this->detectPlantFromCode($code, '02');
                    $deptId = $this->resolveDepartmentId($sheetName, $code);
                    $catCode = $this->resolveCategoryCode($rawCat, $code);
                    $purchaseDate = $this->normalizeDate($rawDate, $code);

                    Asset::create([
                        'asset_code'         => $code,
                        'asset_group'        => 'UTAMA',
                        'item_name'          => $name,
                        'specification'      => $spec,
                        'origin_plant_code'  => $originPlant,
                        'current_plant_code' => '02',
                        'department_id'      => $deptId,
                        'category_code'      => $catCode,
                        'acquisition_source' => $source,
                        'purchase_date'      => $purchaseDate,
                        'qty'                => $qty,
                        'unit_price'         => $unitPrice,
                        'total_price'        => $totalPrice ?: ($unitPrice * $qty),
                        'useful_life_years'  => $usefulLife,
                        'condition'          => $condition,
                        'condition_note'     => $condNote,
                        'tracking_mode'      => ($qty > 1) ? 'BULK' : 'INDIVIDUAL',
                        'is_price_complete'  => ($unitPrice > 0),
                        'status'             => 'AKTIF',
                        'raw_data_source'    => 'PLANT_B_ORPHAN',
                    ]);
                    $orphans++;
                }
            }
        }

        return [
            'enriched'         => $enriched,
            'overlap_resolved' => $overlapResolved,
            'orphans'          => $orphans,
        ];
    }

    /**
     * Step 4: Pemetaan Sub-Ruangan Lampiran C (Audit Form)
     */
    public function mapLampiranC(): int
    {
        $filePath = storage_path('app/assets_migration/LAMPIRAN_C.xlsx');
        if (!file_exists($filePath)) {
            return 0;
        }

        $spreadsheet = IOFactory::load($filePath);
        $mapped = 0;

        $sheetToLocation = [
            'QA A'          => ['01', 'QA'],
            'QC A'          => ['01', 'QC LAB'],
            'OFFICE A '     => ['01', 'OFFICE Plant A'],
            'PRODUKSI A '   => ['01', 'Produksi Plant A - Ruang SPV'],
            'WAREHOUSE A'   => ['01', 'PPIC-WH Plant A'],
            'OFFICE B'      => ['02', 'OFFICE Plant B - Umum'],
            'WAREHOUSE B'   => ['02', 'Ruang Admin PPIC-WH Plant B'],
            'Area WH B'     => ['02', 'Area PPIC-WH Plant B'],
            'FAT'           => ['02', 'OFFICE Plant B - Dep FAT'],
            'R&D B'         => ['01', 'R & D'],
            'Ruang Meeting' => ['01', 'Ruang Meeting'],
            'PRODUKSI B'    => ['02', 'Produksi Plant B - Ruang SPV'],
        ];

        foreach ($sheetToLocation as $sheetTitle => [$plantCode, $roomName]) {
            $sheet = $spreadsheet->getSheetByName($sheetTitle);
            if (!$sheet) {
                continue;
            }

            $location = Location::where('plant_code', $plantCode)
                ->where('room_name', 'like', "%{$roomName}%")
                ->first();

            if (!$location) {
                continue;
            }

            $highestRow = $sheet->getHighestRow();
            for ($r = 8; $r <= $highestRow; $r++) {
                $itemName = trim((string) $sheet->getCell("B{$r}")->getValue());
                if (empty($itemName) || str_starts_with(strtolower($itemName), 'ruang') || str_starts_with(strtolower($itemName), 'dep ')) {
                    continue;
                }

                $matchedAssets = Asset::where('current_plant_code', $plantCode)
                    ->whereNull('location_id')
                    ->where(function ($q) use ($itemName) {
                        $q->where('item_name', 'like', "%{$itemName}%")
                          ->orWhereRaw('SOUNDEX(item_name) = SOUNDEX(?)', [$itemName]);
                    })
                    ->get();

                foreach ($matchedAssets as $asset) {
                    $asset->update(['location_id' => $location->id]);
                    $mapped++;
                }
            }
        }

        return $mapped;
    }

    // ── Helper Resolvers & Normalizers ───────────────────────────────────────

    public function cleanAssetCode(string $raw): string
    {
        $code = trim(preg_replace('/\s+/', '', $raw));
        $code = str_replace(',', '.', $code);

        // Fix missing dashes in WH0724, QC0324, etc.
        if (preg_match('/(WH|QC|QA|GA|RnD|PROD|PC|FAT)(\d{4,6})/', $code, $m)) {
            $code = str_replace($m[0], $m[1] . '-' . $m[2], $code);
        }

        // Fix suffix non-standar: 08.02, 10.02, 03.04, 07.02 -> norm to 01/02
        if (preg_match('/-(\d{2})\.(\d{2})$/', $code, $m)) {
            $plantPart = $m[1];
            $catPart   = $m[2];
            if (!in_array($plantPart, ['01', '02', '00'])) {
                $correctPlant = (str_contains(strtoupper($code), 'GA.B') || str_contains(strtoupper($code), 'WH.B') || str_contains(strtoupper($code), 'PROD.B')) ? '02' : '01';
                $code = preg_replace('/-\d{2}\.\d{2}$/', "-{$correctPlant}.{$catPart}", $code);
            }
        }

        return strtoupper($code);
    }

    public function detectPlantFromCode(string $code, string $default = '01'): string
    {
        if (str_ends_with($code, '-01.01') || str_ends_with($code, '-01.02') || str_ends_with($code, '-01.03') || str_ends_with($code, '-01.04')) {
            return '01';
        }
        if (str_ends_with($code, '-02.01') || str_ends_with($code, '-02.02') || str_ends_with($code, '-02.03') || str_ends_with($code, '-02.04')) {
            return '02';
        }
        if (str_ends_with($code, '-00.01') || str_ends_with($code, '-00.02') || str_ends_with($code, '-00.03') || str_ends_with($code, '-00.04')) {
            return '00';
        }

        if (str_contains(strtoupper($code), '.B-') || str_contains(strtoupper($code), '-B-')) {
            return '02';
        }

        return $default;
    }

    public function resolveDepartmentId(?string $rawDept, string $assetCode = ''): int
    {
        $clean = strtoupper(trim((string) $rawDept));

        if (!empty($clean)) {
            foreach ($this->departmentCache as $dept) {
                if (strtoupper($dept->code) === $clean || strtoupper($dept->name) === $clean) {
                    return $dept->id;
                }
                if (!empty($dept->aliases)) {
                    foreach ($dept->aliases as $alias) {
                        if (strtoupper(trim($alias)) === $clean) {
                            return $dept->id;
                        }
                    }
                }
            }
        }

        $parts = explode('-', $assetCode);
        if (count($parts) >= 3) {
            $deptPart = strtoupper(explode('.', $parts[2])[0]);
            foreach ($this->departmentCache as $dept) {
                if ($dept->code === $deptPart) {
                    return $dept->id;
                }
            }
        }

        return $this->departmentCache['GA']->id ?? 1;
    }

    public function resolveCategoryCode(?string $rawCat, string $assetCode = ''): string
    {
        if (preg_match('/\.(\d{2})$/', $assetCode, $m)) {
            if (isset($this->categoryCache[$m[1]])) {
                return $m[1];
            }
        }

        $catText = strtolower(trim((string) $rawCat));
        if (str_contains($catText, 'elektronik') || str_contains($catText, 'electronic')) {
            return '01';
        }
        if (str_contains($catText, 'peralatan') || str_contains($catText, 'furniture')) {
            return '02';
        }
        if (str_contains($catText, 'kendaraan')) {
            return '03';
        }
        if (str_contains($catText, 'mesin')) {
            return '04';
        }

        return '02';
    }

    public function normalizeDate($rawDate, string $assetCode = ''): ?string
    {
        if (empty($rawDate)) {
            return $this->extractDateFromCode($assetCode) ?: '2024-07-01';
        }

        if (is_numeric($rawDate)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject($rawDate);
                $year = (int) $dt->format('Y');
                if ($year < 2020) {
                    return $this->extractDateFromCode($assetCode) ?: '2024-07-01';
                }
                return $dt->format('Y-m-d');
            } catch (\Throwable) {
                // fallback
            }
        }

        $dateStr = trim((string) $rawDate);

        if (str_starts_with($dateStr, '1905-')) {
            return $this->extractDateFromCode($assetCode) ?: '2025-01-01';
        }

        $indoMonths = [
            'januari' => 'January', 'februari' => 'February', 'maret' => 'March',
            'april' => 'April', 'mei' => 'May', 'juni' => 'June', 'juli' => 'July',
            'agustus' => 'August', 'september' => 'September', 'oktober' => 'October',
            'november' => 'November', 'desember' => 'December', 'des' => 'December', 'okt' => 'October',
        ];

        $lower = strtolower($dateStr);
        foreach ($indoMonths as $id => $en) {
            $lower = str_replace($id, $en, $lower);
        }

        try {
            if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/', $lower, $m)) {
                return Carbon::parse("{$m[1]} {$m[2]} {$m[3]}")->format('Y-m-d');
            }

            if (preg_match('/([A-Za-z]+)\s+(\d{4})/', $lower, $m)) {
                return Carbon::parse("1 {$m[1]} {$m[2]}")->format('Y-m-d');
            }

            if (preg_match('/^\d{4}$/', $lower)) {
                return "{$lower}-01-01";
            }

            return Carbon::parse($lower)->format('Y-m-d');
        } catch (\Throwable) {
            return $this->extractDateFromCode($assetCode) ?: '2024-07-01';
        }
    }

    public function extractDateFromCode(string $code): ?string
    {
        $parts = explode('-', $code);
        foreach ($parts as $p) {
            if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $p, $m)) {
                $d = $m[1];
                $mon = $m[2];
                $y = '20' . $m[3];
                if ((int) $d <= 31 && (int) $mon <= 12) {
                    return "{$y}-{$mon}-{$d}";
                }
            } elseif (preg_match('/^(\d{2})(\d{2})$/', $p, $m)) {
                $mon = $m[1];
                $y = '20' . $m[2];
                if ((int) $mon <= 12) {
                    return "{$y}-{$mon}-01";
                }
            }
        }
        return null;
    }

    public function parseCondition(string $raw): array
    {
        $trim = trim($raw);
        if (empty($trim) || str_starts_with(strtolower($trim), 'baik')) {
            return ['Baik', null];
        }

        if (str_starts_with(strtolower($trim), 'rusak')) {
            return ['Rusak', $trim];
        }

        return ['Baik', $trim];
    }

    public function parseUsefulLife($val, int $default = 4): int
    {
        if (is_numeric($val) && (int) $val > 0) {
            return (int) $val;
        }

        $str = strtolower(trim((string) $val));
        if (str_contains($str, 'tahun')) {
            preg_match('/(\d+)/', $str, $m);
            return isset($m[1]) ? (int) $m[1] : $default;
        }

        return $default;
    }

    public function cleanNumber($val): float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }
        if (empty($val) || !is_string($val)) {
            return 0.0;
        }

        $str = trim($val);
        if (str_starts_with($str, '=') || str_starts_with($str, '#')) {
            return 0.0;
        }

        $str = str_ireplace(['rp', ' '], '', $str);

        // Multiple dots e.g. 1.999.000
        if (substr_count($str, '.') > 1 && !str_contains($str, ',')) {
            $str = str_replace('.', '', $str);
        } elseif (substr_count($str, ',') > 1 && !str_contains($str, '.')) {
            $str = str_replace(',', '', $str);
        } elseif (str_contains($str, '.') && str_contains($str, ',')) {
            if (strrpos($str, '.') > strrpos($str, ',')) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            }
        } else {
            if (preg_match('/^\d+,\d{3}$/', $str)) {
                $str = str_replace(',', '', $str);
            } elseif (preg_match('/^\d+\.\d{3}$/', $str)) {
                $str = str_replace('.', '', $str);
            } else {
                $str = str_replace(',', '.', $str);
            }
        }

        return (float) preg_replace('/[^-0-9.]/', '', $str);
    }
}
