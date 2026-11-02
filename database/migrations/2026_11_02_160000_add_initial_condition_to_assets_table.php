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
        if (Schema::hasTable('assets') && !Schema::hasColumn('assets', 'initial_condition')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->string('initial_condition', 20)->default('Baru')->after('useful_life_years');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assets') && Schema::hasColumn('assets', 'initial_condition')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropColumn('initial_condition');
            });
        }
    }
};

