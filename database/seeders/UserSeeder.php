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
        // Sales Officer
        User::create([
            'username' => 'salesofficer',
            'email' => 'salesofficer@example.com',
            'password' => Hash::make('password'),
            'role' => 'salesofficer/superadmin',
        ]);

        // Delivery Rider
        User::create([
            'username' => 'deliveryrider',
            'email' => 'deliveryrider@example.com',
            'password' => Hash::make('password'),
            'role' => 'deliveryrider/admin',
        ]);

        // Assistant Sales Officer
        User::create([
            'username' => 'assistantsales',
            'email' => 'assistantsales@example.com',
            'password' => Hash::make('password'),
            'role' => 'assistantsales/admin',
        ]);

        // B2B
        User::create([
            'username' => 'b2b',
            'email' => 'b2b@example.com',
            'password' => Hash::make('password'),
            'role' => 'b2b',
        ]);
    }
}
