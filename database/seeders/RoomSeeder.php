<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'Ruangan Regular A',
                'type' => 'regular',
                'description' => 'Ruangan dengan fasilitas standard untuk pemain biasa',
                'status' => true,
            ],
            [
                'name' => 'Ruangan Regular B',
                'type' => 'regular',
                'description' => 'Ruangan dengan fasilitas standard untuk pemain biasa',
                'status' => true,
            ],
            [
                'name' => 'Ruangan VIP',
                'type' => 'vip',
                'description' => 'Ruangan dengan fasilitas premium untuk member VIP',
                'status' => true,
            ],
            [
                'name' => 'Ruangan VVIP',
                'type' => 'vvip',
                'description' => 'Ruangan eksklusif dengan fasilitas terbaik dan tempat duduk yang nyaman',
                'status' => true,
            ],
            [
                'name' => 'Ruangan Latihan',
                'type' => 'regular',
                'description' => 'Ruangan untuk latihan pemain pemula',
                'status' => false,
            ],
        ];

        foreach ($rooms as $roomData) {
            Room::create($roomData);
        }
    }
}
