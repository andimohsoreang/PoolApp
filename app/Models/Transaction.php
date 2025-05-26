<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'table_id',
        'user_id',
        'transaction_type',
        'start_time',
        'end_time',
        'status',
        'total_price',
        'payment_method',
        'transaction_code',
        'reservation_id',
        'payment_details',
        'duration_hours',
        'price_per_hour',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price_per_hour' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    /**
     * Get the customer that owns the transaction
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the table that owns the transaction
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get the user that created the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the details for the transaction
     */
    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Get the payment for the transaction
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the reservation associated with the transaction
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function getDurationHoursAttribute()
    {
        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::parse($this->start_time);
            $end = \Carbon\Carbon::parse($this->end_time);
            return $end->diffInHours($start);
        }
        return null;
    }

    public function getDurationTextAttribute()
    {
        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::parse($this->start_time);
            $end = \Carbon\Carbon::parse($this->end_time);
            $diff = $start->diff($end);
            $parts = [];
            if ($diff->h > 0) $parts[] = $diff->h . ' jam';
            if ($diff->i > 0) $parts[] = $diff->i . ' menit';
            return implode(' ', $parts);
        }
        return '-';
    }

    /**
     * Get the formatted total price
     */
    public function getFormattedTotalPriceAttribute()
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    /**
     * Get the formatted price per hour
     */
    public function getFormattedPricePerHourAttribute()
    {
        return 'Rp ' . number_format($this->price_per_hour, 0, ',', '.');
    }

    protected static function booted()
    {
        static::retrieved(function ($transaction) {
            if (($transaction->price_per_hour <= 0 || $transaction->price_per_hour === null) && $transaction->reservation_id) {
                // Auto-fix price_per_hour from reservation
                try {
                    $reservation = Reservation::find($transaction->reservation_id);
                    if ($reservation && $reservation->price_per_hour > 0) {
                        $transaction->price_per_hour = $reservation->price_per_hour;
                        $transaction->saveQuietly(); // saveQuietly agar tidak trigger events lain

                        Log::info('Auto-corrected transaction price_per_hour', [
                            'transaction_id' => $transaction->id,
                            'reservation_id' => $transaction->reservation_id,
                            'new_price' => $reservation->price_per_hour
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to auto-correct price_per_hour', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }
}
