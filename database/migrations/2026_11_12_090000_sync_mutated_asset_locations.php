<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assets') || !Schema::hasTable('locations')) {
            return;
        }

        // 1. Aset berstatus Umum / Mobile (00) tidak boleh terikat pada ruangan gedung tetap
        DB::table('assets')
            ->where('current_plant_code', '00')
            ->whereNotNull('location_id')
            ->update([
                'location_id' => null,
                'updated_at'  => now(),
            ]);

        // 2. Lepaskan location_id pada aset yang plant_code ruangannya tidak cocok dengan current_plant_code aset
        $mismatchedIds = DB::table('assets as a')
            ->join('locations as l', 'a.location_id', '=', 'l.id')
            ->whereColumn('a.current_plant_code', '!=', 'l.plant_code')
            ->pluck('a.id');

        if ($mismatchedIds->isNotEmpty()) {
            DB::table('assets')
                ->whereIn('id', $mismatchedIds)
                ->update([
                    'location_id' => null,
                    'updated_at'  => now(),
                ]);
        }
    }

    public function down(): void
    {
        // No-op: koreksi konsistensi data lokasi aset bersifat satu arah
    }
};

