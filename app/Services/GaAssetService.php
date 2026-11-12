<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMutation;
use App\Models\Department;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $locationId = $this->resolveValidatedLocationId(
            !empty($data['location_id']) ? (int) $data['location_id'] : null,
            $plantCode,
            'location_id'
        );
        $purchaseDate = $data['purchase_date'] ?? now()->format('Y-m-d');
        $usefulLife = (int) ($data['useful_life_years'] ?? $cat->default_useful_life_years ?? 4);

        $createdAssets = [];

        DB::transaction(function () use ($data, $trackingMode, $qty, $unitPrice, $itemAbbr, $dept, $cat, $plantCode, $locationId, $purchaseDate, $usefulLife, &$createdAssets) {
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
                        'location_id'        => $locationId,
                        'category_code'      => $cat->code,
                        'acquisition_source' => $data['acquisition_source'] ?? 'RAB',
                        'purchase_date'      => $purchaseDate,
                        'qty'                => 1,
                        'unit_price'         => $unitPrice,
                        'total_price'        => $unitPrice,
                        'useful_life_years'  => $usefulLife,
                        'initial_condition'  => $data['initial_condition'] ?? 'Baru',
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
                    'location_id'        => $locationId,
                    'category_code'      => $cat->code,
                    'acquisition_source' => $data['acquisition_source'] ?? 'RAB',
                    'purchase_date'      => $purchaseDate,
                    'qty'                => $qty,
                    'unit_price'         => $unitPrice,
                    'total_price'        => $totalPrice,
                    'useful_life_years'  => $usefulLife,
                    'initial_condition'  => $data['initial_condition'] ?? 'Baru',
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
            'initial_condition'  => $data['initial_condition'] ?? $asset->initial_condition,
            'condition'          => $data['condition'] ?? $asset->condition,
            'condition_note'     => $data['condition_note'] ?? $asset->condition_note,
            'unit_price'         => $unitPrice,
            'qty'                => $qty,
            'total_price'        => $totalPrice,
            'useful_life_years'  => $data['useful_life_years'] ?? $asset->useful_life_years,
            'is_price_complete'  => ($unitPrice > 0),
        ];

        if (isset($data['acquisition_source'])) {
            $updateData['acquisition_source'] = $data['acquisition_source'];
        }
        if (!empty($data['purchase_date'])) {
            $updateData['purchase_date'] = $data['purchase_date'];
        }

        DB::transaction(function () use ($asset, &$updateData, $data) {
            $targetDeptId = !empty($data['department_id']) ? (int) $data['department_id'] : $asset->department_id;
            if (!empty($data['department_id'])) {
                $updateData['department_id'] = $targetDeptId;
            }

            if (!empty($data['current_plant_code'])) {
                $updateData['current_plant_code'] = $data['current_plant_code'];
                if ($data['current_plant_code'] === '00') {
                    $updateData['location_id'] = null;
                }
            }

            if (array_key_exists('location_id', $data)) {
                $requestedLocId = !empty($data['location_id']) ? (int) $data['location_id'] : null;
                $targetPlantAfterEdit = $updateData['current_plant_code'] ?? $asset->current_plant_code;

                if ($requestedLocId) {
                    $newLoc = Location::find($requestedLocId);
                    if ($newLoc && !empty($newLoc->plant_code)) {
                        $newPlantCode = !empty($data['current_plant_code']) ? $data['current_plant_code'] : $newLoc->plant_code;
                        if ($newPlantCode === '00') {
                            $requestedLocId = null;
                        } elseif ($newLoc->plant_code !== $newPlantCode) {
                            throw ValidationException::withMessages([
                                'location_id' => "Ruangan / sub-area yang dipilih tidak berada di Plant {$newPlantCode}.",
                            ]);
                        }

                        $updateData['location_id'] = $requestedLocId;
                        $oldPlantCode = $asset->current_plant_code;
                        $oldLocId = $asset->location_id;

                        if ($newPlantCode !== $oldPlantCode || (int) $oldLocId !== (int) $requestedLocId) {
                            $updateData['current_plant_code'] = $newPlantCode;

                            AssetMutation::create([
                                'asset_id'          => $asset->id,
                                'from_plant_code'   => $oldPlantCode,
                                'to_plant_code'     => $newPlantCode,
                                'from_location_id'  => $oldLocId,
                                'to_location_id'    => $requestedLocId,
                                'from_dept_id'      => $asset->department_id,
                                'to_dept_id'        => $targetDeptId,
                                'mutation_date'     => now()->format('Y-m-d'),
                                'pic_name'          => auth()->user()->name ?? 'GA Officer',
                                'reason'            => ($newPlantCode !== $oldPlantCode) ? 'Perpindahan Lintas Plant via Edit Aset' : 'Perpindahan Ruangan via Edit Aset',
                                'notes'             => 'Pindah ruangan/plant otomatis dicatat dari form Edit Data Aset',
                            ]);
                        }
                    }
                } else {
                    $updateData['location_id'] = null;
                    if ($asset->location_id !== null || $targetPlantAfterEdit !== $asset->current_plant_code) {
                        AssetMutation::create([
                            'asset_id'          => $asset->id,
                            'from_plant_code'   => $asset->current_plant_code,
                            'to_plant_code'     => $targetPlantAfterEdit,
                            'from_location_id'  => $asset->location_id,
                            'to_location_id'    => null,
                            'from_dept_id'      => $asset->department_id,
                            'to_dept_id'        => $targetDeptId,
                            'mutation_date'     => now()->format('Y-m-d'),
                            'pic_name'          => auth()->user()->name ?? 'GA Officer',
                            'reason'            => 'Pengosongan Ruangan via Edit Aset',
                            'notes'             => 'Ruangan dikosongkan dari form Edit Data Aset',
                        ]);
                    }
                }
            }

            $asset->update($updateData);
        });

        return $asset->fresh();
    }

    /**
     * Flow MUTATE (Pindah Plant / Ruangan / Departemen)
     * Catat log mutasi dan perbarui lokasi tanpa mengubah asset_code!
     */
    public function mutateAsset(Asset $asset, array $data): AssetMutation
    {
        $targetPlant = $data['target_plant_code'] ?? $asset->current_plant_code;
        $targetDeptId = !empty($data['target_dept_id']) ? (int) $data['target_dept_id'] : $asset->department_id;

        if ($targetPlant === '00') {
            $targetLocId = null;
        } elseif ($targetPlant !== $asset->current_plant_code) {
            // Saat berpindah Plant, jangan pernah mewarisi ruangan dari Plant lama jika target_location_id kosong
            $rawLocId = !empty($data['target_location_id']) ? (int) $data['target_location_id'] : null;
            $targetLocId = $this->resolveValidatedLocationId($rawLocId, $targetPlant, 'target_location_id');
        } else {
            $rawLocId = array_key_exists('target_location_id', $data)
                ? (!empty($data['target_location_id']) ? (int) $data['target_location_id'] : null)
                : $asset->location_id;
            $targetLocId = $this->resolveValidatedLocationId($rawLocId ? (int) $rawLocId : null, $targetPlant, 'target_location_id');
        }

        $plantChanged = $targetPlant !== $asset->current_plant_code;
        $locationChanged = $targetLocId !== ($asset->location_id !== null ? (int) $asset->location_id : null);
        $deptChanged = ($targetDeptId !== null ? (int) $targetDeptId : null) !== ($asset->department_id !== null ? (int) $asset->department_id : null);

        if (!$plantChanged && !$locationChanged && !$deptChanged) {
            throw ValidationException::withMessages([
                'target_plant_code' => 'Lokasi plant, ruangan, dan departemen tujuan masih sama dengan posisi aset saat ini.',
            ]);
        }

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
     * Validasi bahwa ruangan tujuan berada di Plant target.
     * Plant '00' (Umum / Mobile) tidak memiliki ruangan tetap sehingga selalu null.
     */
    protected function resolveValidatedLocationId(?int $locationId, string $plantCode, string $errorKey = 'location_id'): ?int
    {
        if ($plantCode === '00' || empty($locationId)) {
            return null;
        }

        $location = Location::find($locationId);
        if (!$location || $location->plant_code !== $plantCode) {
            throw ValidationException::withMessages([
                $errorKey => "Ruangan / sub-area yang dipilih tidak terdaftar di Plant {$plantCode}.",
            ]);
        }

        return (int) $location->id;
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

