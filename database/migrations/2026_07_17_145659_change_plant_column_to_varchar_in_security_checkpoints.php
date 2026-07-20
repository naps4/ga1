<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom 'plant' dari ENUM menjadi VARCHAR(100) agar mendukung nama plant/gedung bebas
        DB::statement("ALTER TABLE security_checkpoints MODIFY COLUMN plant VARCHAR(100) NOT NULL");
    }

    public function down(): void
    {
        // Rollback: kembalikan ke ENUM dengan dua nilai lama
        // Data yang tidak sesuai harus dibersihkan terlebih dahulu
        DB::statement("ALTER TABLE security_checkpoints MODIFY COLUMN plant ENUM('Plant A', 'Plant B') NOT NULL DEFAULT 'Plant A'");
    }
};
