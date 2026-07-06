<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atk_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nama');
            $table->string('divisi');
            $table->string('periode');
            $table->date('tanggal_pengajuan');
            $table->string('status_approval')->default('Draft');
            
            // Approval & Handover
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->date('diberikan_pada')->nullable();
            $table->string('receiver_name')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });

        Schema::create('atk_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atk_request_id')->constrained('atk_requests')->onDelete('cascade');
            $table->string('item_name');
            $table->string('unit');
            $table->string('description')->nullable(); // For specifications/checkbox selections
            $table->integer('quantity');
            $table->boolean('is_custom')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atk_request_items');
        Schema::dropIfExists('atk_requests');
    }
};
