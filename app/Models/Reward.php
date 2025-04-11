<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'description',
        'points_cost',
        'status',
        'expires_at',
        'redeemed_at'
    ];

    protected $casts = [
        'points_cost' => 'integer',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
} 