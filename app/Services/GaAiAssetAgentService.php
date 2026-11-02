<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Location;
use App\Models\Plant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GaAiAssetAgentService
{
    protected GaAssetService $assetService;

    public function __construct(GaAssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * System Prompt resmi untuk AI Asset Controller
     */
    public function getSystemPrompt(): string
    {
        return <<<PROMPT
Anda adalah AI Asset Controller untuk divisi General Affairs (GA) PT Herbatech Innopharma Industry. Tugas Anda adalah mengelola siklus hidup aset di Master Data (ASET ALL), memantau distribusi aset antara Plant A (Kode 01) dan Plant B (Kode 02), menghitung penyusutan nilai buku, serta menghasilkan rekapitulasi audit ruangan sesuai standar dokumen CM-03/GA/002.C.01 (Lampiran C). Setiap perubahan lokasi lintas Plant wajib melalui fungsi mutasi (mutate_asset) tanpa mengubah ID Aset asli.
PROMPT;
    }

    /**
     * Definisi 5 Function Tools untuk LLM Tool-Calling
     */
    public function getToolDefinitions(): array
    {
        return [
            [
                'name' => 'search_assets',
                'description' => 'Mencari dan memfilter aset di Master, Plant A, atau Plant B beserta nilai depresiasinya.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'plant_code'      => ['type' => 'string', 'enum' => ['01', '02', '00', 'all'], 'description' => 'Kode plant fisik saat ini'],
                        'department_code' => ['type' => 'string', 'description' => 'Kode departemen (misal: GA, PROD, WH, QC, QA, RnD, FAT, PC)'],
                        'category_code'   => ['type' => 'string', 'enum' => ['01', '02', '03', '04']],
                        'condition'       => ['type' => 'string', 'enum' => ['Baik', 'Rusak', 'Perbaikan']],
                        'keyword'         => ['type' => 'string', 'description' => 'Kata kunci nama barang atau kode aset HII-...'],
                    ],
                ],
            ],
            [
                'name' => 'register_new_asset',
                'description' => 'Menambah aset baru dan men-generate kode HII-... otomatis sesuai format perusahaan.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'item_name'          => ['type' => 'string'],
                        'item_abbr'          => ['type' => 'string', 'description' => 'Singkatan nama aset (contoh: MK untuk Meja Kantor, LP untuk Laptop)'],
                        'brand'              => ['type' => 'string'],
                        'plant_code'         => ['type' => 'string', 'enum' => ['01', '02', '00']],
                        'department_code'    => ['type' => 'string'],
                        'location_id'        => ['type' => 'integer'],
                        'category_code'      => ['type' => 'string', 'enum' => ['01', '02', '03', '04']],
                        'qty'                => ['type' => 'integer'],
                        'unit_price'         => ['type' => 'number'],
                        'useful_life_years'  => ['type' => 'integer'],
                        'tracking_mode'      => ['type' => 'string', 'enum' => ['INDIVIDUAL', 'BULK']],
                        'specification'      => ['type' => 'string'],
                    ],
                    'required' => ['item_name', 'item_abbr', 'plant_code', 'department_code', 'category_code', 'qty', 'unit_price'],
                ],
            ],
            [
                'name' => 'mutate_asset',
                'description' => 'Memindahkan aset dari Plant A ke Plant B (atau antar ruangan) dan mencatat log mutasi tanpa mengubah kode stiker fisik.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'asset_code'         => ['type' => 'string', 'description' => 'Kode aset asli (misal: HII-MK.1-RnD-0724-01.02)'],
                        'target_plant_code'  => ['type' => 'string', 'enum' => ['01', '02', '00']],
                        'target_location_id' => ['type' => 'integer'],
                        'pic_name'           => ['type' => 'string'],
                        'reason'             => ['type' => 'string'],
                    ],
                    'required' => ['asset_code', 'target_plant_code', 'pic_name', 'reason'],
                ],
            ],
            [
                'name' => 'update_asset_condition',
                'description' => 'Mengubah status kondisi barang (Baik / Rusak / Perbaikan) saat inspeksi atau stock opname berkala.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'asset_code' => ['type' => 'string'],
                        'condition'  => ['type' => 'string', 'enum' => ['Baik', 'Rusak', 'Perbaikan']],
                        'notes'      => ['type' => 'string', 'description' => 'Rincian kondisi abnormal'],
                    ],
                    'required' => ['asset_code', 'condition'],
                ],
            ],
            [
                'name' => 'generate_lampiran_c_report',
                'description' => 'Menghitung agregasi barang di ruangan tertentu dan menghasilkan tabel siap cetak berformat dokumen CM-03/GA/002.C.01.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location_id' => ['type' => 'integer', 'description' => 'ID ruangan pada tabel locations'],
                        'room_name'   => ['type' => 'string', 'description' => 'Atau nama ruangan (misal: QC LAB, Ruang Meeting, PPIC-WH)'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Dispatcher eksekusi tool panggilan AI
     */
    public function callTool(string $toolName, array $args): array
    {
        return match ($toolName) {
            'search_assets'             => $this->toolSearchAssets($args),
            'register_new_asset'        => $this->toolRegisterNewAsset($args),
            'mutate_asset'              => $this->toolMutateAsset($args),
            'update_asset_condition'    => $this->toolUpdateAssetCondition($args),
            'generate_lampiran_c_report'=> $this->toolGenerateLampiranC($args),
            default                     => ['error' => "Tool '{$toolName}' tidak dikenal."],
        };
    }

    protected function toolSearchAssets(array $args): array
    {
        $query = Asset::with(['department', 'location', 'category'])->active();

        if (!empty($args['plant_code']) && $args['plant_code'] !== 'all') {
            $query->where('current_plant_code', $args['plant_code']);
        }
        if (!empty($args['department_code'])) {
            $dept = Department::where('code', strtoupper($args['department_code']))->first();
            if ($dept) {
                $query->where('department_id', $dept->id);
            }
        }
        if (!empty($args['category_code'])) {
            $query->where('category_code', $args['category_code']);
        }
        if (!empty($args['condition'])) {
            $query->where('condition', $args['condition']);
        }
        if (!empty($args['keyword'])) {
            $k = trim($args['keyword']);
            $query->where(function ($q) use ($k) {
                $q->where('asset_code', 'like', "%{$k}%")
                  ->orWhere('item_name', 'like', "%{$k}%")
                  ->orWhere('brand', 'like', "%{$k}%");
            });
        }

        $totalFound = (clone $query)->count();
        $items = $query->take(20)->get()->map(function ($a) {
            return [
                'id'                       => $a->id,
                'asset_code'               => $a->asset_code,
                'item_name'                => $a->item_name,
                'brand'                    => $a->brand,
                'plant'                    => $a->current_plant_code,
                'dept'                     => $a->department->code ?? '-',
                'location'                 => $a->location->room_name ?? 'Area Umum',
                'condition'                => $a->condition,
                'condition_note'           => $a->condition_note,
                'qty'                      => $a->qty,
                'total_price'              => (float) $a->total_price,
                'book_value'               => $a->book_value,
                'months_elapsed'           => $a->months_elapsed,
                'accumulated_depreciation' => $a->accumulated_depreciation,
            ];
        });

        return [
            'total_matches' => $totalFound,
            'limit_shown'   => 20,
            'assets'        => $items,
        ];
    }

    protected function toolRegisterNewAsset(array $args): array
    {
        $dept = Department::where('code', strtoupper($args['department_code']))->firstOrFail();

        $data = [
            'item_name'          => $args['item_name'],
            'item_abbr'          => $args['item_abbr'] ?? 'BRG',
            'brand'              => $args['brand'] ?? null,
            'specification'      => $args['specification'] ?? null,
            'current_plant_code' => $args['plant_code'],
            'department_id'      => $dept->id,
            'location_id'        => $args['location_id'] ?? null,
            'category_code'      => $args['category_code'],
            'qty'                => (int) ($args['qty'] ?? 1),
            'unit_price'         => (float) ($args['unit_price'] ?? 0),
            'purchase_date'      => $args['purchase_date'] ?? now()->format('Y-m-d'),
            'useful_life_years'  => $args['useful_life_years'] ?? 4,
            'tracking_mode'      => $args['tracking_mode'] ?? 'INDIVIDUAL',
            'condition'          => 'Baik',
        ];

        $created = $this->assetService->createAsset($data);

        return [
            'success'        => true,
            'created_count'  => count($created),
            'created_assets' => collect($created)->map(fn ($a) => [
                'id'         => $a->id,
                'asset_code' => $a->asset_code,
                'item_name'  => $a->item_name,
                'total_price'=> $a->total_price,
            ]),
        ];
    }

    protected function toolMutateAsset(array $args): array
    {
        $asset = Asset::where('asset_code', strtoupper(trim($args['asset_code'])))->firstOrFail();

        $mutation = $this->assetService->mutateAsset($asset, [
            'target_plant_code'  => $args['target_plant_code'],
            'target_location_id' => $args['target_location_id'] ?? null,
            'pic_name'           => $args['pic_name'],
            'reason'             => $args['reason'],
        ]);

        return [
            'success'           => true,
            'message'           => "Aset {$asset->asset_code} ({$asset->item_name}) berhasil dimutasikan ke Plant {$args['target_plant_code']}.",
            'asset_code'        => $asset->asset_code,
            'from_plant'        => $mutation->from_plant_code,
            'to_plant'          => $mutation->to_plant_code,
            'mutation_id'       => $mutation->id,
        ];
    }

    protected function toolUpdateAssetCondition(array $args): array
    {
        $asset = Asset::where('asset_code', strtoupper(trim($args['asset_code'])))->firstOrFail();

        $updated = $this->assetService->updateCondition($asset, $args['condition'], $args['notes'] ?? null);

        return [
            'success'        => true,
            'asset_code'     => $updated->asset_code,
            'new_condition'  => $updated->condition,
            'condition_note' => $updated->condition_note,
        ];
    }

    protected function toolGenerateLampiranC(array $args): array
    {
        $location = null;
        if (!empty($args['location_id'])) {
            $location = Location::find($args['location_id']);
        } elseif (!empty($args['room_name'])) {
            $location = Location::where('room_name', 'like', "%{$args['room_name']}%")->first();
        }

        if (!$location) {
            return ['error' => 'Ruangan tidak ditemukan. Silakan tentukan location_id atau room_name yang valid.'];
        }

        $items = DB::table('assets as a')
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

        $latestDate = Asset::where('location_id', $location->id)->max('updated_at');

        return [
            'document_name'    => 'FORM DAFTAR ASET',
            'doc_reference'    => 'CM-03/GA/002.C.01',
            'room_name'        => $location->room_name,
            'plant_code'       => $location->plant_code,
            'last_update'      => $latestDate ? Carbon::parse($latestDate)->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
            'total_physical'   => (int) $items->sum('jumlah'),
            'items'            => $items,
            'print_url'        => route('assets.lampiran-c.print', $location->id),
        ];
    }
}
