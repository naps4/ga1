<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fpu_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->string('priority')->nullable(); // Sangat Penting, Penting, Biasa
            $table->string('executor_type')->nullable(); // Internal, Eksternal
            $table->string('type')->nullable(); // Bangunan, Kendaraan, Lainnya
            $table->string('category')->nullable(); // Pembuatan, Perbaikan, Pengadaan, Lainnya
            $table->string('status_approval')->default('Menunggu Verifikasi HRGA');
            $table->foreignId('verified_by_hrga')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('known_by_manager')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_by_gm_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fpu_requests');
    }
};
