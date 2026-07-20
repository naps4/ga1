<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->string('plant', 100);
            $table->string('area_name');
            $table->string('code')->unique();
            $table->integer('target_duration')->default(5); // target durasi dalam menit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_checkpoints');
    }
};
