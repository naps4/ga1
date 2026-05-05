<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wo_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->string('service_name')->nullable();
            $table->integer('qty')->nullable();
            $table->string('unit')->nullable();
            $table->text('specification')->nullable();
            $table->text('notes')->nullable();
            $table->date('handover_date')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('known_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('accepted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('accepted_by_user_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wo_handovers');
    }
};
