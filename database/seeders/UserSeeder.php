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
        $users = [
            [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin'
            ],
            [
                'name' => 'petani',
                'email' => 'petani@gmail.com',
                'password' => Hash::make('petani123'),
                'role' => 'petani'
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
