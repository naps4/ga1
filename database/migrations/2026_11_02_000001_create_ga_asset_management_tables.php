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

        // 5. Assets master table
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 120)->unique();
            $table->enum('asset_group', ['UTAMA', 'TERPISAH', 'BERGERAK'])->default('UTAMA');
            $table->string('item_name', 255);
            $table->string('brand', 150)->nullable();
            $table->text('specification')->nullable();
            $table->string('origin_plant_code', 10)->default('01');
            $table->string('current_plant_code', 10)->default('01');
            $table->foreignId('department_id')->constrained('departments')->cascadeOnUpdate();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('category_code', 10);
            $table->string('acquisition_source', 50)->default('RAB');
            $table->date('purchase_date')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->unsignedInteger('useful_life_years')->default(4);
            $table->enum('condition', ['Baik', 'Rusak', 'Perbaikan'])->default('Baik');
            $table->text('condition_note')->nullable();
            $table->enum('tracking_mode', ['INDIVIDUAL', 'BULK'])->default('INDIVIDUAL');
            $table->boolean('is_price_complete')->default(true);
            $table->enum('status', ['AKTIF', 'DISPOSED'])->default('AKTIF');
            $table->dateTime('disposed_at')->nullable();
            $table->text('disposal_reason')->nullable();
            $table->string('raw_data_source', 50)->nullable();
            $table->timestamps();

            $table->foreign('origin_plant_code')->references('code')->on('plants')->cascadeOnUpdate();
            $table->foreign('current_plant_code')->references('code')->on('plants')->cascadeOnUpdate();
            $table->foreign('category_code')->references('code')->on('asset_categories')->cascadeOnUpdate();

            $table->index(['current_plant_code', 'department_id']);
            $table->index(['status', 'asset_group']);
        });

        // 6. Asset Mutations table
        Schema::create('asset_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('from_plant_code', 10);
            $table->string('to_plant_code', 10);
            $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('from_dept_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_dept_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->date('mutation_date');
            $table->string('pic_name', 255);
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('from_plant_code')->references('code')->on('plants')->cascadeOnUpdate();
            $table->foreign('to_plant_code')->references('code')->on('plants')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
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

