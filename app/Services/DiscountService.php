<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DiscountService
{
    /**
     * Calculate discount for a given amount (stub implementation after promo removal)
     *
     * @param float $amount The original amount
     * @param string|null $promoCode (not used anymore)
     * @return array
     */
    public function calculateDiscount($amount, $promoCode = null)
    {
        // Since promos have been removed, no discount is applied
        Log::info('Discount calculation called with amount', [
            'amount' => $amount,
            'promo_code_provided' => !empty($promoCode)
        ]);

        return [
            'original_amount' => $amount,
            'discount' => 0,
            'total_after_discount' => $amount,
            'promo' => null
        ];
    }

    /**
     * Get discount details (stub implementation after promo removal)
     *
     * @param string|null $promoCode (not used anymore)
     * @return array
     */
    public function getDiscountDetails($promoCode = null)
    {
        return [
            'valid' => false,
            'message' => 'Promo feature has been removed',
            'promo' => null
        ];
    }
}