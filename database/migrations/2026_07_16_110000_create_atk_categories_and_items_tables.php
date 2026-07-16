<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AtkRequestController;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atk_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'ATK' or 'Rumah Tangga'
            $table->timestamps();
        });

        Schema::create('atk_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atk_category_id')->constrained('atk_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('unit');
            $table->json('options')->nullable();
            $table->timestamps();
        });

        // Seed initial data from AtkRequestController
        $standardItems = AtkRequestController::$standardItems;
        $householdItems = AtkRequestController::$householdItems;

        foreach ($standardItems as $itemData) {
            $categoryName = $itemData['category'];
            $category = DB::table('atk_categories')
                ->where('name', $categoryName)
                ->where('type', 'ATK')
                ->first();

            if (!$category) {
                $categoryId = DB::table('atk_categories')->insertGetId([
                    'name' => $categoryName,
                    'type' => 'ATK',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $categoryId = $category->id;
            }

            DB::table('atk_items')->insert([
                'atk_category_id' => $categoryId,
                'name' => $itemData['name'],
                'unit' => $itemData['unit'],
                'options' => json_encode($itemData['options'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($householdItems as $itemData) {
            $categoryName = $itemData['category'];
            $category = DB::table('atk_categories')
                ->where('name', $categoryName)
                ->where('type', 'Rumah Tangga')
                ->first();

            if (!$category) {
                $categoryId = DB::table('atk_categories')->insertGetId([
                    'name' => $categoryName,
                    'type' => 'Rumah Tangga',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $categoryId = $category->id;
            }

            DB::table('atk_items')->insert([
                'atk_category_id' => $categoryId,
                'name' => $itemData['name'],
                'unit' => $itemData['unit'],
                'options' => json_encode($itemData['options'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('atk_items');
        Schema::dropIfExists('atk_categories');
    }
};
