<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bersihkan UOM duplikat/majemuk 'Pcs, Pack'
        if (Schema::hasTable('uoms')) {
            DB::table('uoms')->where('name', 'Pcs, Pack')->delete();
            DB::table('uoms')->insertOrIgnore([
                ['name' => 'Pcs', 'description' => 'Pieces / Buah', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Pack', 'description' => 'Bungkus / Pak', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. Normalisasi satuan transaksi di atk_request_items yang menyimpan 'Pcs, Pack'
        if (Schema::hasTable('atk_request_items')) {
            DB::table('atk_request_items')->where('unit', 'Pcs, Pack')->update(['unit' => 'Pcs']);
            DB::table('atk_request_items')->where('unit', 'Pcs / Pack')->update(['unit' => 'Pcs']);
        }
    }

    public function down(): void
    {
        // No-op
    }
};

