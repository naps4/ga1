<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_checkpoint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('checkpoint_id')->nullable()->constrained('security_checkpoints')->onDelete('set null');
            $table->string('shift');
            $table->dateTime('checked_at');
            $table->string('status'); // Aman, Pintu Belum Ditutup, Listrik Belum Dimatikan, dll.
            $table->text('notes')->nullable();
            $table->string('selfie_path')->nullable(); // Bukti selfie, opsional untuk evident report
            $table->boolean('is_evident_report')->default(false);
            $table->integer('duration_minutes')->default(0); // Durasi monitoring dalam menit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_checkpoint_logs');
    }
};
