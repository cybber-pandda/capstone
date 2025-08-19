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
            'name' => 'John Superadmin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        // Delivery Rider
        User::create([
            'name' => 'John DeliveryRider',
            'username' => 'deliveryrider',
            'email' => 'deliveryrider@example.com',
            'password' => Hash::make('password'),
            'role' => 'deliveryrider',
        ]);

        // Assistant Sales Officer
        User::create([
            'name' => 'John SalesOfficer',
            'username' => 'salesofficer',
            'email' => 'assistantsales@example.com',
            'password' => Hash::make('password'),
            'role' => 'salesofficer',
        ]);

        // B2B
        User::create([
            'name' => 'John B2B',
            'username' => 'b2b',
            'email' => 'b2b@example.com',
            'password' => Hash::make('password'),
            'role' => 'b2b',
            'credit_limit' => 300000
        ]);
    }
}
