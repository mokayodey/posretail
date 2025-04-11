<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'amount',
        'description',
        'payment_method',
        'status',
        'payment_reference',
        'failure_reason',
        'completed_at',
        'cart_id',
        'user_id',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 