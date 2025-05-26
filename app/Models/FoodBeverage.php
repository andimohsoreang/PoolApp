<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodBeverage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'thumbnail',
        'is_available',
        'is_featured',
        'order',
        'average_rating',
        'rating_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'average_rating' => 'decimal:2',
        'rating_count' => 'integer',
    ];

    /**
     * Get all images for this food/beverage
     */
    public function images(): HasMany
    {
        return $this->hasMany(FoodBeverageImage::class);
    }

    /**
     * Get the primary image for this food/beverage
     */
    public function primaryImage()
    {
        return $this->images()->where('is_primary', true)->first();
    }

    /**
     * Get all ratings for this food/beverage
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(FoodBeverageRating::class);
    }

    /**
     * Update the average rating based on all ratings
     */
    public function updateAverageRating(): void
    {
        $avg = $this->ratings()->avg('rating') ?? 0;
        $count = $this->ratings()->count() ?? 0;

        $this->update([
            'average_rating' => round($avg, 2),
            'rating_count' => $count
        ]);
    }
}
