<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_assignments', function (Blueprint $table) {
            // Penempatan: nama plant/area yang ditugaskan ke petugas.
            // Nullable agar data lama tidak rusak, NULL berarti semua area.
            $table->string('plant', 100)->nullable()->after('shift_id');
        });
    }

    public function down(): void
    {
        Schema::table('security_assignments', function (Blueprint $table) {
            $table->dropColumn('plant');
        });
    }
};
