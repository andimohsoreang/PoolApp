<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Table;
use App\Models\Price;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all available tables
        $tables = Table::whereHas('room', function($q) {
            $q->where('status', true);
        })->where('status', 'normal')->get();

        // If there are no tables, we can't create prices
        if ($tables->isEmpty()) {
            $this->command->info('No active tables found. Please run table seeder first.');
            return;
        }

        // Delete all existing prices first to avoid duplicates
        Price::truncate();

        // Create two time ranges for each table:
        // 1. Daytime: 06:00 to 18:00 (6 AM to 6 PM)
        // 2. Nighttime: 18:00 to 06:00 (6 PM to 6 AM)
        foreach ($tables as $table) {
            // Daytime price - 6 AM to 6 PM
            Price::create([
                'table_id' => $table->id,
                'start_time' => '06:00:00',
                'end_time' => '18:00:00',
                'price' => 70000.00, // 70k IDR weekday
                'day_type' => 'weekday',
                'valid_from' => now(),
                'valid_until' => now()->addYear(),
                'status' => true,
            ]);

            Price::create([
                'table_id' => $table->id,
                'start_time' => '06:00:00',
                'end_time' => '18:00:00',
                'price' => 90000.00, // 90k IDR weekend
                'day_type' => 'weekend',
                'valid_from' => now(),
                'valid_until' => now()->addYear(),
                'status' => true,
            ]);

            // Nighttime price - 6 PM to 6 AM
            Price::create([
                'table_id' => $table->id,
                'start_time' => '18:00:00',
                'end_time' => '06:00:00',
                'price' => 100000.00, // 100k IDR weekday
                'day_type' => 'weekday',
                'valid_from' => now(),
                'valid_until' => now()->addYear(),
                'status' => true,
            ]);

            Price::create([
                'table_id' => $table->id,
                'start_time' => '18:00:00',
                'end_time' => '06:00:00',
                'price' => 120000.00, // 120k IDR weekend
                'day_type' => 'weekend',
                'valid_from' => now(),
                'valid_until' => now()->addYear(),
                'status' => true,
            ]);
        }

        $this->command->info('Price data seeded successfully!');
    }
}
