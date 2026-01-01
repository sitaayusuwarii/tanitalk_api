<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Akun Super Admin (Hanya 1)
        User::create([
            'name' => 'Super Admin',
            'email' => 'super_admin@gmail.com',
            'phone' => '081234567890', 
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
        ]);

        // 2. Buat Akun Admin Biasa
        User::create([
            'name' => 'Admin Staff',
            'email' => 'admin@gmail.com',
            'phone' => '08987654321',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // 3. Buat Akun Petani (Contoh)
        User::create([
            'name' => 'Petani',
            'email' => 'petani@gmail.com',
            'phone' => '08111222333',
            'password' => Hash::make('petani123'),
            'role' => 'petani',
        ]);
    }
}
