<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('security_shifts')->onDelete('cascade');
            $table->date('assigned_date');
            $table->text('notes')->nullable();
            $table->unique(['user_id', 'assigned_date']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_assignments');
    }
};
