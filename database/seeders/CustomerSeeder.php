<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'email' => 'john@example.com',
                'name' => 'John Doe',
                'phone' => '084234567893',
                'whatsapp' => '084234567893',
                'gender' => 'male',
                'age' => 25,
                'origin_address' => 'Jl. Pelanggan 1 No. 4',
                'current_address' => 'Jl. Pelanggan 1 No. 4',
                'category' => 'regular',
                'visit_count' => 5,
                'status' => true,
            ],
            [
                'email' => 'jane@example.com',
                'name' => 'Jane Smith',
                'phone' => '085234567894',
                'whatsapp' => '085234567894',
                'gender' => 'female',
                'age' => 22,
                'origin_address' => 'Jl. Pelanggan 2 No. 5',
                'current_address' => 'Jl. Pelanggan 2 No. 5',
                'category' => 'vip',
                'visit_count' => 12,
                'status' => true,
            ],
            [
                'email' => 'bob@example.com',
                'name' => 'Bob Johnson',
                'phone' => '086234567895',
                'whatsapp' => '086234567895',
                'gender' => 'male',
                'age' => 30,
                'origin_address' => 'Jl. Pelanggan 3 No. 6',
                'current_address' => 'Jl. Pelanggan 3 No. 6 Blok C',
                'category' => 'regular',
                'visit_count' => 3,
                'status' => true,
            ],
            [
                'email' => 'sarah@example.com',
                'name' => 'Sarah Williams',
                'phone' => '087234567896',
                'whatsapp' => '087234567896',
                'gender' => 'female',
                'age' => 27,
                'origin_address' => 'Jl. Pelanggan 4 No. 7',
                'current_address' => 'Jl. Pelanggan 4 No. 7',
                'category' => 'vvip',
                'visit_count' => 20,
                'status' => true,
            ],
            [
                'email' => 'mike@example.com',
                'name' => 'Mike Brown',
                'phone' => '088234567897',
                'whatsapp' => '088234567897',
                'gender' => 'male',
                'age' => 35,
                'origin_address' => 'Jl. Pelanggan 5 No. 8',
                'current_address' => 'Jl. Pelanggan 5 No. 8',
                'category' => 'regular',
                'visit_count' => 8,
                'status' => true,
            ],
        ];

        foreach ($customers as $customerData) {
            // Mencari user berdasarkan email
            $user = User::where('email', $customerData['email'])->first();

            if ($user) {
                // Jika user ditemukan, gunakan ID-nya untuk customer
                $customerData['user_id'] = $user->id;
                Customer::create($customerData);
            }
        }
    }
}
