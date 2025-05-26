<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'table_id',
        'start_time',
        'end_time',
        'status',
        'total_price',
        'duration_hours',
        'price_per_hour',
        'discount',
        'reservation_code',
        'payment_token',
        'payment_order_id',
        'payment_expired_at',
        'payment_details',
        'rejection_reason',
        'reason',
        'notified',
        'approved_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'payment_expired_at' => 'datetime',
        'payment_details' => 'json'
    ];

    /**
     * Get the customer that owns the reservation.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the table that owns the reservation.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Calculate the total price for this reservation.
     */
    public function calculateTotalPrice()
    {
        $start = $this->start_time;
        $end = $this->end_time;
        $totalHours = $end->diffInHours($start);

        // Gunakan PriceService untuk mendapatkan harga yang tepat berdasarkan waktu
        $table = Table::find($this->table_id);
        $price = \App\Services\PriceService::getPriceForTable($table, $start);

        if (!$price) {
            // Fallback ke metode lama jika PriceService tidak menemukan harga
            // Get the price for this table at the reservation time
            $price = Price::where('table_id', $this->table_id)
                ->where('status', true)
                ->where(function($query) use ($start) {
                    $query->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $start);
                })
                ->where(function($query) use ($start) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', $start);
                })
                ->where(function($query) use ($start) {
                    $dayType = $start->isWeekend() ? 'weekend' : 'weekday';
                    $query->where('day_type', $dayType);
                })
                ->latest()
                ->first();
        }

        // Fallback to any active price if none matched specific time criteria
        if (!$price) {
            $price = Price::where('table_id', $this->table_id)
                ->where('status', true)
                ->latest()
                ->first();
            if (!$price) {
                return 0;
            }
        }

        $total = $price->price * $totalHours;

        // Record calculated values on the model for later saving
        $this->duration_hours = $totalHours;
        $this->price_per_hour = $price->price;
        $this->total_price = $total;

        return $total;
    }

    /**
     * Get the transactions for this reservation.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the admin who approved this reservation
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Payment Information Methods
    public function getPaymentDetails()
    {
        $details = [
            'price_per_hour' => $this->price_per_hour,
            'duration_hours' => $this->duration_hours,
            'subtotal' => $this->price_per_hour * $this->duration_hours,
            'discount' => $this->discount ?? 0,
            'total_price' => $this->total_price,
            'total_discount' => $this->discount ?? 0,
        ];

        return $details;
    }

    public function getFormattedPaymentDetails()
    {
        $details = $this->getPaymentDetails();
        return [
            'price_per_hour' => 'Rp ' . number_format($details['price_per_hour'], 0, ',', '.'),
            'duration_hours' => $details['duration_hours'] . ' jam',
            'subtotal' => 'Rp ' . number_format($details['subtotal'], 0, ',', '.'),
            'discount' => 'Rp ' . number_format($details['discount'], 0, ',', '.'),
            'total_discount' => 'Rp ' . number_format($details['total_discount'], 0, ',', '.'),
            'total_price' => 'Rp ' . number_format($details['total_price'], 0, ',', '.')
        ];
    }

    // Status Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    // Payment Status Methods
    public function hasPaymentToken()
    {
        return !empty($this->payment_token);
    }

    public function isPaymentExpired()
    {
        return $this->payment_expired_at && now()->gt($this->payment_expired_at);
    }

    public function getPaymentExpiryTime()
    {
        return $this->payment_expired_at ? $this->payment_expired_at->format('H:i:s') : null;
    }

    public function getRemainingPaymentTime()
    {
        if (!$this->payment_expired_at) {
            return null;
        }

        $remaining = now()->diffInSeconds($this->payment_expired_at, false);
        return $remaining > 0 ? $remaining : 0;
    }

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    // Get all available statuses
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_PAID,
            self::STATUS_REJECTED,
            self::STATUS_EXPIRED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED
        ];
    }
}
