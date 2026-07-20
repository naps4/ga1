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
        User::firstOrCreate(
            ['email' => 'superadmin@ga-system.com'],
            [
                'name' => 'Super Admin GA',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'phone' => '+62812345678',
                'company' => 'General Affair',
                'bio' => 'System Super Administrator',
            ]
        );

        // Create Admin / SPV
        User::firstOrCreate(
            ['email' => 'admin@ga-system.com'],
            [
                'name' => 'Admin HRGA',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '+62812345679',
                'company' => 'HRGA Department',
                'bio' => 'SPV / Admin HRGA',
            ]
        );

        // Create Engineer (Teknisi) -> Di database masih 'developer' untuk sinkronisasi ENUM
        User::firstOrCreate(
            ['email' => 'engineer1@ga-system.com'],
            [
                'name' => 'Budi Teknisi',
                'password' => Hash::make('password'),
                'role' => 'developer',
                'status' => 'active',
                'phone' => '+62812345680',
                'company' => 'Maintenance',
                'bio' => 'Teknisi Internal (Engineer)',
            ]
        );

        User::firstOrCreate(
            ['email' => 'engineer2@ga-system.com'],
            [
                'name' => 'Joko Teknisi',
                'password' => Hash::make('password'),
                'role' => 'developer',
                'status' => 'active',
                'phone' => '+62812345681',
                'company' => 'Maintenance',
                'bio' => 'Teknisi Bangunan',
            ]
        );

        // Create User / Client
        User::firstOrCreate(
            ['email' => 'user1@company.com'],
            [
                'name' => 'Staff Marketing',
                'password' => Hash::make('password'),
                'role' => 'client',
                'status' => 'active',
                'phone' => '+62812345682',
                'company' => 'Marketing Dept',
                'bio' => 'Staff pemohon',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user2@company.com'],
            [
                'name' => 'Manager Produksi',
                'password' => Hash::make('password'),
                'role' => 'client',
                'status' => 'active',
                'phone' => '+62812345683',
                'company' => 'Production Dept',
                'bio' => 'User pemohon',
            ]
        );

        User::firstOrCreate(
            ['email' => 'oprtmanager@company.com'],
            [
                'name' => 'Oprt. Manager',
                'password' => Hash::make('password'),
                'role' => 'admin', // Kita set admin agar bisa melakukan approval diketahui
                'status' => 'active',
                'phone' => '+62812345684',
                'company' => 'Management',
                'bio' => 'Operational Manager',
            ]
        );

        User::firstOrCreate(
            ['email' => 'gm@company.com'],
            [
                'name' => 'Direktur / GM',
                'password' => Hash::make('password'),
                'role' => 'super_admin', // Kita set super_admin agar punya otoritas approve khusus
                'status' => 'active',
                'phone' => '+62812345685',
                'company' => 'Management',
                'bio' => 'Direktur Utama',
            ]
        );

        // Create Security Guard
        User::firstOrCreate(
            ['email' => 'security@ga-system.com'],
            [
                'name' => 'Slamet Security',
                'password' => Hash::make('password'),
                'role' => 'security',
                'status' => 'active',
                'phone' => '+62812345686',
                'company' => 'Security Dept',
                'bio' => 'Petugas Keamanan (Security Guard)',
            ]
        );

        User::firstOrCreate(
            ['email' => 'security1@ga-system.com'],
            [
                'name' => 'Agus Security',
                'password' => Hash::make('password'),
                'role' => 'security',
                'status' => 'active',
                'phone' => '+62812345687',
                'company' => 'Security Dept',
                'bio' => 'Petugas Keamanan (Security Guard)',
            ]
        );

        User::firstOrCreate(
            ['email' => 'security2@ga-system.com'],
            [
                'name' => 'Budi Security',
                'password' => Hash::make('password'),
                'role' => 'security',
                'status' => 'active',
                'phone' => '+62812345688',
                'company' => 'Security Dept',
                'bio' => 'Petugas Keamanan (Security Guard)',
            ]
        );

        User::firstOrCreate(
            ['email' => 'security3@ga-system.com'],
            [
                'name' => 'Cecep Security',
                'password' => Hash::make('password'),
                'role' => 'security',
                'status' => 'active',
                'phone' => '+62812345689',
                'company' => 'Security Dept',
                'bio' => 'Petugas Keamanan (Security Guard)',
            ]
        );

        User::firstOrCreate(
            ['email' => 'security4@ga-system.com'],
            [
                'name' => 'Dedi Security',
                'password' => Hash::make('password'),
                'role' => 'security',
                'status' => 'active',
                'phone' => '+62812345690',
                'company' => 'Security Dept',
                'bio' => 'Petugas Keamanan (Security Guard)',
            ]
        );
    }
}
