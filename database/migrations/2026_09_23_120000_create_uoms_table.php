<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uoms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 1. Initial Standard UOMs
        $initialUoms = [
            ['name' => 'Pcs', 'description' => 'Pieces / Buah / Satuan Tunggal'],
            ['name' => 'Rim', 'description' => 'Kertas Rim (500 Lembar)'],
            ['name' => 'Box', 'description' => 'Kotak / Box'],
            ['name' => 'Box Kecil', 'description' => 'Kotak Ukuran Kecil (Isi staples, clips, dll)'],
            ['name' => 'Pack', 'description' => 'Bungkus / Pak'],
            ['name' => 'Roll', 'description' => 'Gulung / Roll'],
            ['name' => 'Botol', 'description' => 'Botol / Botolan'],
            ['name' => 'Dus', 'description' => 'Karton / Dus Besar'],
            ['name' => 'Lusin', 'description' => '12 Pieces (Lusin)'],
            ['name' => 'Set', 'description' => 'Perangkat / Set'],
            ['name' => 'Plastik', 'description' => 'Kantong / Bungkus Plastik'],
            ['name' => 'Meter', 'description' => 'Satuan Panjang (Meter)'],
            ['name' => 'Lembar', 'description' => 'Satuan Lembar'],
            ['name' => 'Pasang', 'description' => 'Sepasang (Pair)'],
            ['name' => 'Kaleng', 'description' => 'Satuan Kaleng'],
        ];

        foreach ($initialUoms as $uom) {
            DB::table('uoms')->insertOrIgnore([
                'name' => $uom['name'],
                'description' => $uom['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Normalisasi Satuan Ambigu pada atk_items & atk_request_items
        if (Schema::hasTable('atk_items')) {
            DB::table('atk_items')->where('unit', 'Pcs / Pack')->update(['unit' => 'Pcs']);
            DB::table('atk_items')->where('unit', 'Pcs/Botol')->update(['unit' => 'Botol']);
            
            // Masukkan juga unit lain yang sudah ada di atk_items ke master uoms jika belum terdaftar
            $existingUnits = DB::table('atk_items')->distinct()->pluck('unit');
            foreach ($existingUnits as $unit) {
                $trimmed = trim($unit);
                if (!empty($trimmed) && !str_contains($trimmed, '/')) {
                    DB::table('uoms')->insertOrIgnore([
                        'name' => $trimmed,
                        'description' => 'Satuan dari Master Barang',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if (Schema::hasTable('atk_request_items')) {
            DB::table('atk_request_items')->where('unit', 'Pcs / Pack')->update(['unit' => 'Pcs']);
            DB::table('atk_request_items')->where('unit', 'Pcs/Botol')->update(['unit' => 'Botol']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('uoms');
    }
};
