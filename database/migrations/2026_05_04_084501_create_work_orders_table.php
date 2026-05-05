<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('department')->nullable();
            $table->text('description');
            $table->string('attachment_request')->nullable(); // Lampiran saat pengajuan
            $table->foreignId('assigned_engineer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('check_result')->nullable();
            $table->string('attachment_check')->nullable(); // Lampiran hasil cek
            $table->text('engineer_needs')->nullable();
            $table->text('engineer_report')->nullable();
            $table->string('attachment_report')->nullable(); // Lampiran laporan selesai
            $table->string('status')->default('Menunggu Penugasan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
