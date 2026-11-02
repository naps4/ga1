<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMutation;
use App\Models\Department;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GaAssetService
{
    /**
     * Generate kode aset baru berformat HII-[ITEM].[SEQ]-[DEPT]-[MMYY]-[PLANT].[CAT]
     */
    public function generateNextCode(
        string $itemAbbr,
        string $deptCode,
        string $purchaseDate,
        string $plantCode,
        string $catCode
    ): string {
        $dateObj = Carbon::parse($purchaseDate);
        $datePart = $dateObj->format('my'); // e.g. 1126 for Nov 2026

        $prefix = "HII-" . strtoupper($itemAbbr) . ".";
        $deptUpper = strtoupper($deptCode);

        // Cari sequence terakhir untuk item abbreviation dan departemen ini
        $lastAsset = Asset::where('asset_code', 'like', "{$prefix}%-{$deptUpper}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastAsset && preg_match('/\.(\d+)-/', $lastAsset->asset_code, $m)) {
            $nextSeq = ((int) $m[1]) + 1;
        }

        return "HII-" . strtoupper($itemAbbr) . ".{$nextSeq}-{$deptUpper}-{$datePart}-{$plantCode}.{$catCode}";
    }

    /**
     * Flow CREATE (Tambah Aset Baru)
     */
    public function createAsset(array $data): array
    {
        $trackingMode = $data['tracking_mode'] ?? 'INDIVIDUAL';
        $qty = (int) ($data['qty'] ?? 1);
        $unitPrice = (float) ($data['unit_price'] ?? 0);
        $itemAbbr = trim($data['item_abbr'] ?? 'BRG');
        $dept = Department::findOrFail($data['department_id']);
        $cat = AssetCategory::findOrFail($data['category_code']);
        $plantCode = $data['current_plant_code'] ?? '01';
        $purchaseDate = $data['purchase_date'] ?? now()->format('Y-m-d');
        $usefulLife = (int) ($data['useful_life_years'] ?? $cat->default_useful_life_years ?? 4);

        $createdAssets = [];

        DB::transaction(function () use ($data, $trackingMode, $qty, $unitPrice, $itemAbbr, $dept, $cat, $plantCode, $purchaseDate, $usefulLife, &$createdAssets) {
            if ($trackingMode === 'INDIVIDUAL' && $qty > 1) {
                // Loop buat baris terpisah masing-masing qty = 1
                $dateObj = Carbon::parse($purchaseDate);
                $datePart = $dateObj->format('my');
                $prefix = "HII-" . strtoupper($itemAbbr) . ".";
                $deptUpper = strtoupper($dept->code);

                $lastAsset = Asset::where('asset_code', 'like', "{$prefix}%-{$deptUpper}-%")
                    ->orderBy('id', 'desc')
                    ->first();

                $currSeq = 0;
                if ($lastAsset && preg_match('/\.(\d+)-/', $lastAsset->asset_code, $m)) {
                    $currSeq = (int) $m[1];
                }

                for ($i = 1; $i <= $qty; $i++) {
                    $currSeq++;
                    $generatedCode = "HII-" . strtoupper($itemAbbr) . ".{$currSeq}-{$deptUpper}-{$datePart}-{$plantCode}.{$cat->code}";

                    $asset = Asset::create([
                        'asset_code'         => $generatedCode,
                        'asset_group'        => $data['asset_group'] ?? 'UTAMA',
                        'item_name'          => $data['item_name'],
                        'brand'              => $data['brand'] ?? null,
                        'specification'      => $data['specification'] ?? null,
                        'origin_plant_code'  => $plantCode,
                        'current_plant_code' => $plantCode,
                        'department_id'      => $dept->id,
                        'location_id'        => $data['location_id'] ?? null,
                        'category_code'      => $cat->code,
                        'acquisition_source' => $data['acquisition_source'] ?? 'RAB',
                        'purchase_date'      => $purchaseDate,
                        'qty'                => 1,
                        'unit_price'         => $unitPrice,
                        'total_price'        => $unitPrice,
                        'useful_life_years'  => $usefulLife,
                        'condition'          => $data['condition'] ?? 'Baik',
                        'condition_note'     => $data['condition_note'] ?? null,
                        'tracking_mode'      => 'INDIVIDUAL',
                        'is_price_complete'  => ($unitPrice > 0),
                        'status'             => 'AKTIF',
                    ]);

                    $createdAssets[] = $asset;
                }
            } else {
                // Buat 1 baris (BULK atau qty = 1)
                $code = $data['asset_code'] ?? null;
                if (!$code) {
                    $dateObj = Carbon::parse($purchaseDate);
                    $datePart = $dateObj->format('my');
                    $code = "HII-" . strtoupper($itemAbbr) . "-{$dept->code}-{$datePart}-{$plantCode}.{$cat->code}";
                    // Check uniqueness
                    if (Asset::where('asset_code', $code)->exists()) {
                        $code .= '-' . rand(10, 99);
                    }
                }

                $totalPrice = ($trackingMode === 'BULK') ? ($unitPrice * $qty) : $unitPrice;

                $asset = Asset::create([
                    'asset_code'         => $code,
                    'asset_group'        => $data['asset_group'] ?? 'UTAMA',
                    'item_name'          => $data['item_name'],
                    'brand'              => $data['brand'] ?? null,
                    'specification'      => $data['specification'] ?? null,
                    'origin_plant_code'  => $plantCode,
                    'current_plant_code' => $plantCode,
                    'department_id'      => $dept->id,
                    'location_id'        => $data['location_id'] ?? null,
                    'category_code'      => $cat->code,
                    'acquisition_source' => $data['acquisition_source'] ?? 'RAB',
                    'purchase_date'      => $purchaseDate,
                    'qty'                => $qty,
                    'unit_price'         => $unitPrice,
                    'total_price'        => $totalPrice,
                    'useful_life_years'  => $usefulLife,
                    'condition'          => $data['condition'] ?? 'Baik',
                    'condition_note'     => $data['condition_note'] ?? null,
                    'tracking_mode'      => $trackingMode,
                    'is_price_complete'  => ($unitPrice > 0),
                    'status'             => 'AKTIF',
                ]);

                $createdAssets[] = $asset;
            }
        });

        return $createdAssets;
    }

    /**
     * Flow UPDATE (Edit Biasa / In-Place Update)
     */
    public function updateAsset(Asset $asset, array $data): Asset
    {
        $unitPrice = isset($data['unit_price']) ? (float) $data['unit_price'] : (float) $asset->unit_price;
        $qty = isset($data['qty']) ? (int) $data['qty'] : (int) $asset->qty;
        $totalPrice = $unitPrice * $qty;

        $updateData = [
            'item_name'          => $data['item_name'] ?? $asset->item_name,
            'brand'              => $data['brand'] ?? $asset->brand,
            'specification'      => $data['specification'] ?? $asset->specification,
            'condition'          => $data['condition'] ?? $asset->condition,
            'condition_note'     => $data['condition_note'] ?? $asset->condition_note,
            'unit_price'         => $unitPrice,
            'qty'                => $qty,
            'total_price'        => $totalPrice,
            'useful_life_years'  => $data['useful_life_years'] ?? $asset->useful_life_years,
            'is_price_complete'  => ($unitPrice > 0),
        ];

        if (!empty($data['department_id'])) {
            $updateData['department_id'] = $data['department_id'];
        }
        if (!empty($data['location_id'])) {
            $updateData['location_id'] = $data['location_id'];
        }

        $asset->update($updateData);

        return $asset->fresh();
    }

    /**
     * Flow MUTATE (Pindah Plant / Ruangan)
     * Catat log mutasi dan perbarui lokasi tanpa mengubah asset_code!
     */
    public function mutateAsset(Asset $asset, array $data): AssetMutation
    {
        $targetPlant = $data['target_plant_code'] ?? $asset->current_plant_code;
        $targetLocId = $data['target_location_id'] ?? $asset->location_id;
        $targetDeptId = $data['target_dept_id'] ?? $asset->department_id;

        return DB::transaction(function () use ($asset, $data, $targetPlant, $targetLocId, $targetDeptId) {
            $mutation = AssetMutation::create([
                'asset_id'          => $asset->id,
                'from_plant_code'   => $asset->current_plant_code,
                'to_plant_code'     => $targetPlant,
                'from_location_id'  => $asset->location_id,
                'to_location_id'    => $targetLocId,
                'from_dept_id'      => $asset->department_id,
                'to_dept_id'        => $targetDeptId,
                'mutation_date'     => $data['mutation_date'] ?? now()->format('Y-m-d'),
                'pic_name'          => $data['pic_name'] ?? auth()->user()->name ?? 'GA Officer',
                'reason'            => $data['reason'] ?? 'Mutasi Lokasi Operasional',
                'notes'             => $data['notes'] ?? null,
            ]);

            // Update aset tanpa mengubah asset_code!
            $asset->update([
                'current_plant_code' => $targetPlant,
                'location_id'        => $targetLocId,
                'department_id'      => $targetDeptId,
            ]);

            return $mutation;
        });
    }

    /**
     * Flow DISPOSE (Soft Delete / Penghapusan Aset)
     */
    public function disposeAsset(Asset $asset, string $reason): Asset
    {
        $asset->update([
            'status'          => 'DISPOSED',
            'disposed_at'     => now(),
            'disposal_reason' => $reason,
        ]);

        return $asset;
    }

    /**
     * Update kondisi fisik saat Stock Opname
     */
    public function updateCondition(Asset $asset, string $condition, ?string $note = null): Asset
    {
        $asset->update([
            'condition'      => $condition,
            'condition_note' => $note ?: $asset->condition_note,
        ]);

        return $asset;
    }
}

