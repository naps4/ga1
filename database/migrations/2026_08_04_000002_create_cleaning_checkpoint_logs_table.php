<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_checkpoint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('checkpoint_id')->nullable()->constrained('cleaning_checkpoints')->onDelete('set null');
            $table->string('shift');
            $table->dateTime('checked_at');
            $table->string('status'); // Bersih & Rapi, Kotor / Perlu Pembersihan, Bau Tidak Sedap, Fasilitas / Sanitari Rusak, Tempat Sampah Penuh, dll.
            $table->text('notes')->nullable();
            $table->string('selfie_path')->nullable(); // Bukti selfie di area
            $table->string('attachment_path')->nullable(); // Foto temuan / kondisi kotor
            $table->boolean('is_evident_report')->default(false);
            $table->integer('duration_minutes')->default(0); // Durasi pembersihan dalam menit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_checkpoint_logs');
    }
};
