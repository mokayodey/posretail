<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'cart_id',
        'amount',
        'payment_method',
        'status',
        'reference',
        'payment_details'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array'
    ];

    /**
     * Get the cart that owns the payment
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the user that processed the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment processor (Moniepoint, Suregifts, etc.)
     */
    public function processor(): MorphTo
    {
        return $this->morphTo();
    }
} 