<?php

namespace App\Services;

use App\Models\Price;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PriceService
{
    public static function getPriceForTable($table, $dateTime)
    {
        // 1. Input data
        $dayOfWeek = strtolower($dateTime->format('l'));
        $timeOfDay = $dateTime->format('H:i:s');
        $hour = (int)$dateTime->format('H');
        $minute = (int)$dateTime->format('i');

        // Weekend/weekday
        $isWeekend = in_array($dayOfWeek, ['saturday', 'sunday']);
        $dayType = $isWeekend ? 'weekend' : 'weekday';

        // 2. Log input data
        Log::info('🔍 PriceService: Input parameters', [
            'table_id' => $table->id,
            'table_number' => $table->table_number ?? 'Unknown',
            'date_time' => $dateTime->format('Y-m-d H:i:s'),
            'day_type' => $dayType,
            'hour' => $hour,
            'minute' => $minute
        ]);

        // 3. Get all active prices for this table and day type
        $prices = Price::where('table_id', $table->id)
            ->where('day_type', $dayType)
            ->where('status', true)
            ->get();

        if ($prices->isEmpty()) {
            Log::warning('❌ PriceService: No prices found for table', [
                'table_id' => $table->id,
                'day_type' => $dayType
            ]);
            return null;
        }

        // 4. Debug: Log available prices
        $pricesLog = [];
        foreach ($prices as $price) {
            $startHour = (int)date('H', strtotime($price->start_time));
            $startMinute = (int)date('i', strtotime($price->start_time));
            $endHour = (int)date('H', strtotime($price->end_time));
            $endMinute = (int)date('i', strtotime($price->end_time));

            $isOvernight = ($startHour > $endHour) ||
                          ($startHour == $endHour && $startMinute > $endMinute);

            $pricesLog[] = [
                'id' => $price->id,
                'price' => $price->price,
                'start_time' => date('H:i', strtotime($price->start_time)),
                'end_time' => date('H:i', strtotime($price->end_time)),
                'start_hour' => $startHour,
                'end_hour' => $endHour,
                'is_overnight' => $isOvernight ? 'YES' : 'NO'
            ];
        }

        Log::info('📋 PriceService: Available prices', ['prices' => $pricesLog]);

        // 5. Find matching price with SIMPLIFIED LOGIC
        $normalPrice = null;
        $overnightPrice = null;

        foreach ($prices as $price) {
            $startHour = (int)date('H', strtotime($price->start_time));
            $startMinute = (int)date('i', strtotime($price->start_time));
            $endHour = (int)date('H', strtotime($price->end_time));
            $endMinute = (int)date('i', strtotime($price->end_time));

            // Convert to total minutes for easier comparison
            $startTotalMinutes = $startHour * 60 + $startMinute;
            $endTotalMinutes = $endHour * 60 + $endMinute;
            $currentTotalMinutes = $hour * 60 + $minute;

            // Check if this is a normal range (start <= end)
            if ($startTotalMinutes <= $endTotalMinutes) {
                // Normal range logic: currentTime must be BETWEEN start and end
                if ($currentTotalMinutes >= $startTotalMinutes && $currentTotalMinutes < $endTotalMinutes) {
                    $normalPrice = $price;
                    Log::info('✅ PriceService: Found NORMAL range match', [
                        'price_id' => $price->id,
                        'price' => $price->price,
                        'current_time' => "$hour:$minute",
                        'start_time' => date('H:i', strtotime($price->start_time)),
                        'end_time' => date('H:i', strtotime($price->end_time))
                    ]);
                }
            } else {
                // Overnight range logic: currentTime must be AFTER start OR BEFORE end
                if ($currentTotalMinutes >= $startTotalMinutes || $currentTotalMinutes < $endTotalMinutes) {
                    $overnightPrice = $price;
                    Log::info('✅ PriceService: Found OVERNIGHT range match', [
                        'price_id' => $price->id,
                        'price' => $price->price,
                        'current_time' => "$hour:$minute",
                        'start_time' => date('H:i', strtotime($price->start_time)),
                        'end_time' => date('H:i', strtotime($price->end_time)),
                        'match_condition' => $currentTotalMinutes >= $startTotalMinutes ?
                                          'After start time' : 'Before end time'
                    ]);
                }
            }
        }

        // 6. Return price based on priority (normal first, then overnight)
        $selectedPrice = null;
        $priceType = '';

        if ($normalPrice) {
            $selectedPrice = $normalPrice;
            $priceType = 'NORMAL';
        } else if ($overnightPrice) {
            $selectedPrice = $overnightPrice;
            $priceType = 'OVERNIGHT';
        } else {
            $selectedPrice = $prices->sortByDesc('id')->first();
            $priceType = 'FALLBACK';
            Log::warning('⚠️ PriceService: No direct match found, using fallback price', [
                'price_id' => $selectedPrice->id,
                'price' => $selectedPrice->price
            ]);
        }

        // 7. Add price_per_hour and log result
        if ($selectedPrice) {
            $selectedPrice->price_per_hour = $selectedPrice->price;

            Log::info('💲 PriceService: Final price selected', [
                'price_id' => $selectedPrice->id,
                'price' => $selectedPrice->price,
                'price_type' => $priceType,
                'table_id' => $table->id,
                'current_time' => "$hour:$minute"
            ]);

            return $selectedPrice;
        }

        Log::error('❌ PriceService: No price could be determined');
        return null;
    }
}
