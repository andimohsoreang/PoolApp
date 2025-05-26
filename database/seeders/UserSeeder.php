<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pool.com',
            'username' => 'superadmin',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'phone' => '081234567890',
            'whatsapp' => '081234567890',
            'gender' => 'male',
            'age' => 35,
            'address' => 'Jl. Admin Utama No. 1',
            'status' => true,
        ]);

        // Create Owner
        User::create([
            'name' => 'Owner',
            'email' => 'owner@pool.com',
            'username' => 'owner',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'phone' => '082234567891',
            'whatsapp' => '082234567891',
            'gender' => 'male',
            'age' => 40,
            'address' => 'Jl. Pemilik No. 2',
            'status' => true,
        ]);

        // Create Admin Pool
        User::create([
            'name' => 'Admin Pool',
            'email' => 'admin@pool.com',
            'username' => 'adminpool',
            'password' => Hash::make('password123'),
            'role' => 'admin_pool',
            'phone' => '083234567892',
            'whatsapp' => '083234567892',
            'gender' => 'female',
            'age' => 28,
            'address' => 'Jl. Admin Billiard No. 3',
            'status' => true,
        ]);

        // Create Customers
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '084234567893',
                'whatsapp' => '084234567893',
                'gender' => 'male',
                'age' => 25,
                'address' => 'Jl. Pelanggan 1 No. 4',
                'status' => true,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'username' => 'janesmith',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '085234567894',
                'whatsapp' => '085234567894',
                'gender' => 'female',
                'age' => 22,
                'address' => 'Jl. Pelanggan 2 No. 5',
                'status' => true,
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'username' => 'bobjohnson',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '086234567895',
                'whatsapp' => '086234567895',
                'gender' => 'male',
                'age' => 30,
                'address' => 'Jl. Pelanggan 3 No. 6',
                'status' => true,
            ],
        ];

        foreach ($customers as $customer) {
            User::create($customer);
        }
    }
}
