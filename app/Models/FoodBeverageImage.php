<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodBeverageImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'food_beverage_id',
        'image_path',
        'alt_text',
        'order',
        'is_primary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the food/beverage that owns this image
     */
    public function foodBeverage(): BelongsTo
    {
        return $this->belongsTo(FoodBeverage::class);
    }

    /**
     * When setting this image as primary, unset any other primary images
     *
     * @param bool $value
     * @return void
     */
    public function setIsPrimaryAttribute($value): void
    {
        if ($value) {
            // Unset other primary images for this food/beverage
            self::where('food_beverage_id', $this->food_beverage_id)
                ->where('id', '!=', $this->id)
                ->update(['is_primary' => false]);
        }

        $this->attributes['is_primary'] = $value;
    }
}
