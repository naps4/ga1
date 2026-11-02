<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Plants table
        Schema::create('plants', function (Blueprint $table) {
            $table->string('code', 10)->primary(); // 01, 02, 00
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // GA, PROD, WH, QC, QA, RnD, FAT, PC
            $table->string('name', 150);
            $table->json('aliases')->nullable();
            $table->timestamps();
        });

        // 3. Asset Categories table
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->string('code', 10)->primary(); // 01, 02, 03, 04
            $table->string('name', 150);
            $table->unsignedInteger('default_useful_life_years')->default(4);
            $table->timestamps();
        });

        // 4. Locations table (Sub-ruangan Lampiran C)
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('plant_code', 10);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('room_name', 150);
            $table->string('doc_reference', 50)->default('CM-03/GA/002.C.01');
            $table->timestamps();

            $table->foreign('plant_code')->references('code')->on('plants')->cascadeOnUpdate()->cascadeOnDelete();
        });

        
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_mutations');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('plants');
    }
};

