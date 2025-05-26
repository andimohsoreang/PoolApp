<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodBeverageRating extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'food_beverage_id',
        'user_id',
        'rating',
        'review',
        'is_approved',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the food/beverage that this rating belongs to
     */
    public function foodBeverage(): BelongsTo
    {
        return $this->belongsTo(FoodBeverage::class);
    }

    /**
     * Get the user who left this rating
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * When creating or updating a rating, update the average rating on the food/beverage
     */
    protected static function booted()
    {
        static::saved(function ($rating) {
            $rating->foodBeverage->updateAverageRating();
        });

        static::deleted(function ($rating) {
            $rating->foodBeverage->updateAverageRating();
        });
    }
}
