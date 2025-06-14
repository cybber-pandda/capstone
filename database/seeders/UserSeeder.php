<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        // Admin user
        User::create([
            'username' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        // Staff user
        User::create([
            'username' => 'Shelter User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'role' => 'shelterowner/admin',
        ]);

        // Adopter user
        User::create([
            'username' => 'Adopter User',
            'email' => 'adopter@example.com',
            'password' => Hash::make('password123'),
            'role' => 'adopter',
        ]);
    }
}
