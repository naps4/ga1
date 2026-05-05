<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fpu_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fpu_request_id')->constrained('fpu_requests')->onDelete('cascade');
            $table->string('material_name');
            $table->text('specification')->nullable();
            $table->integer('qty')->default(1);
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fpu_materials');
    }
};
