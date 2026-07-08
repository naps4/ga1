<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('client', 'manager', 'developer', 'admin', 'super_admin') DEFAULT 'client'");
        }

        Schema::table('atk_requests', function (Blueprint $table) {
            $table->foreignId('manager_approved_by')->nullable()->after('status_approval')->constrained('users')->onDelete('set null');
            $table->timestamp('manager_approved_at')->nullable()->after('manager_approved_by');
            $table->text('manager_notes')->nullable()->after('manager_approved_at');
        });

        Schema::table('atk_request_items', function (Blueprint $table) {
            $table->string('status_approval')->default('Pending')->after('is_custom');
            $table->text('notes')->nullable()->after('status_approval');
        });
    }

    public function down(): void
    {
        Schema::table('atk_request_items', function (Blueprint $table) {
            $table->dropColumn(['status_approval', 'notes']);
        });

        Schema::table('atk_requests', function (Blueprint $table) {
            $table->dropForeign(['manager_approved_by']);
            $table->dropColumn(['manager_approved_by', 'manager_approved_at', 'manager_notes']);
        });

        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('client', 'developer', 'admin', 'super_admin') DEFAULT 'client'");
        }
    }
};
