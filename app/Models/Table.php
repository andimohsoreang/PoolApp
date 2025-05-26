<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'table_number',
        'room_id',
        'status',
        'capacity',
        'description',
    ];

    /**
     * Get the room that owns the table
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get prices for the table
     */
    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Get transactions for the table
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get promos for the table
     */
    public function promos()
    {
        return $this->belongsToMany(Promo::class, 'promo_tables');
    }

    /**
     * Get the reservations for the table.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
