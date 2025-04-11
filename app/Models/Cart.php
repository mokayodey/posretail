<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'discount_type',
        'discount_value'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2'
    ];

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in the cart
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate the subtotal of the cart
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    /**
     * Calculate the discount amount
     */
    public function getDiscountAmountAttribute(): float
    {
        if (!$this->discount_type || !$this->discount_value) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            return $this->subtotal * ($this->discount_value / 100);
        }

        return $this->discount_value;
    }

    /**
     * Calculate the total amount after discount
     */
    public function getTotalAttribute(): float
    {
        return $this->subtotal - $this->discount_amount;
    }
} 