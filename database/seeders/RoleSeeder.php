<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Admin GA',
            'email' => 'superadmin@ga-system.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
            'phone' => '+62812345678',
            'company' => 'General Affair',
            'bio' => 'System Super Administrator',
        ]);

        // Create Admin / SPV
        User::create([
            'name' => 'Admin HRGA',
            'email' => 'admin@ga-system.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '+62812345679',
            'company' => 'HRGA Department',
            'bio' => 'SPV / Admin HRGA',
        ]);

        // Create Engineer (Teknisi) -> Di database masih 'developer' untuk sinkronisasi ENUM
        User::create([
            'name' => 'Budi Teknisi',
            'email' => 'engineer1@ga-system.com',
            'password' => Hash::make('password'),
            'role' => 'developer', 
            'status' => 'active',
            'phone' => '+62812345680',
            'company' => 'Maintenance',
            'bio' => 'Teknisi Internal (Engineer)',
        ]);

        User::create([
            'name' => 'Joko Teknisi',
            'email' => 'engineer2@ga-system.com',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'status' => 'active',
            'phone' => '+62812345681',
            'company' => 'Maintenance',
            'bio' => 'Teknisi Bangunan',
        ]);

        // Create User / Client
        User::create([
            'name' => 'Staff Marketing',
            'email' => 'user1@company.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => 'active',
            'phone' => '+62812345682',
            'company' => 'Marketing Dept',
            'bio' => 'Staff pemohon',
        ]);

        User::create([
            'name' => 'Manager Produksi',
            'email' => 'user2@company.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => 'active',
            'phone' => '+62812345683',
            'company' => 'Production Dept',
            'bio' => 'User pemohon',
        ]);

        User::create([
            'name' => 'Oprt. Manager',
            'email' => 'oprtmanager@company.com',
            'password' => Hash::make('password'),
            'role' => 'admin', // Kita set admin agar bisa melakukan approval diketahui
            'status' => 'active',
            'phone' => '+62812345684',
            'company' => 'Management',
            'bio' => 'Operational Manager',
        ]);
        
        User::create([
            'name' => 'Direktur / GM',
            'email' => 'gm@company.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin', // Kita set super_admin agar punya otoritas approve khusus
            'status' => 'active',
            'phone' => '+62812345685',
            'company' => 'Management',
            'bio' => 'Direktur Utama',
        ]);

        // Create Security Guard
        User::create([
            'name' => 'Slamet Security',
            'email' => 'security@ga-system.com',
            'password' => Hash::make('password'),
            'role' => 'security',
            'status' => 'active',
            'phone' => '+62812345686',
            'company' => 'Security Dept',
            'bio' => 'Petugas Keamanan (Security Guard)',
        ]);
    }
}
