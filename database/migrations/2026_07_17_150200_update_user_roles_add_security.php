<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter enum column role in users table to include 'security' and 'manager'
        // Using raw SQL statement for MySQL compatibility
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('client', 'developer', 'admin', 'super_admin', 'security', 'manager') DEFAULT 'client'");
    }

    public function down(): void
    {
        // Revert enum column in users table
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('client', 'developer', 'admin', 'super_admin') DEFAULT 'client'");
    }
};
