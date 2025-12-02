<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. SUPER ADMIN (Mengelola Semua)
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'super@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 2. ADMIN (Approve, Cek Stok, Export)
        DB::table('users')->insert([
            'name' => 'Admin Approval',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 3. USER (Inputter / Sales)
        DB::table('users')->insert([
            'name' => 'Sales Budi',
            'email' => 'user@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}