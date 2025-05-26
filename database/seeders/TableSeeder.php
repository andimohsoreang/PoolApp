<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Table;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active rooms
        $rooms = Room::where('status', true)->get();

        if ($rooms->isEmpty()) {
            $this->command->info('No active rooms found. Please run room seeder first.');
            return;
        }

        // Create tables for each room
        foreach ($rooms as $room) {
            // Create different number of tables based on room type
            $tableCount = 2; // Default for regular rooms

            if ($room->type === 'vip') {
                $tableCount = 3;
            } elseif ($room->type === 'vvip') {
                $tableCount = 4;
            }

            for ($i = 1; $i <= $tableCount; $i++) {
                // Set capacity based on room type
                $capacity = 4; // Default capacity

                if ($room->type === 'vip') {
                    $capacity = 6;
                } elseif ($room->type === 'vvip') {
                    $capacity = 8;
                }

                Table::create([
                    'room_id' => $room->id,
                    'table_number' => $this->generateTableNumber($room, $i),
                    'status' => 'normal',
                    'capacity' => $capacity,
                    'description' => 'Meja biliar ' . $room->type . ' nomor ' . $i,
                ]);
            }
        }

        $this->command->info('Table data seeded successfully!');
    }

    /**
     * Generate a unique table number based on room type and index
     */
    private function generateTableNumber(Room $room, int $index): string
    {
        $prefix = '';

        switch ($room->type) {
            case 'regular':
                $prefix = 'REG';
                break;
            case 'vip':
                $prefix = 'VIP';
                break;
            case 'vvip':
                $prefix = 'VVIP';
                break;
            default:
                $prefix = 'TBL';
        }

        return $prefix . '-' . str_pad($index, 2, '0', STR_PAD_LEFT);
    }
}