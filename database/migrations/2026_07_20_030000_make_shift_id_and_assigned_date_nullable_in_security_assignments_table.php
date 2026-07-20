<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_assignments', function (Blueprint $table) {
            // Di Laravel/MySQL untuk mengubah column menjadi nullable, kita bisa gunakan change()
            $table->foreignId('shift_id')->nullable()->change();
            $table->date('assigned_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('security_assignments', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable(false)->change();
            $table->date('assigned_date')->nullable(false)->change();
        });
    }
};
