<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'table_id',
        'start_time',
        'end_time',
        'price',
        'day_type',
        'valid_from',
        'valid_until',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'status' => 'boolean',
    ];

    /**
     * Get the table that owns the price
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
