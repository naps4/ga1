<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atk_requests', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('atk_requests')->onDelete('set null');
            $table->unsignedSmallInteger('revision_number')->default(0)->after('parent_id');
            $table->text('revision_note')->nullable()->after('status_approval');
        });

        Schema::table('atk_request_items', function (Blueprint $table) {
            $table->boolean('is_revised')->default(false)->after('is_custom');
            $table->integer('previous_quantity')->nullable()->after('is_revised');
            $table->text('previous_description')->nullable()->after('previous_quantity');
            $table->foreignId('original_item_id')->nullable()->after('previous_description')->constrained('atk_request_items')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('atk_request_items', function (Blueprint $table) {
            $table->dropForeign(['original_item_id']);
            $table->dropColumn(['is_revised', 'previous_quantity', 'previous_description', 'original_item_id']);
        });

        Schema::table('atk_requests', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'revision_number', 'revision_note']);
        });
    }
};
