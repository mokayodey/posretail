<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_branch_id',
        'destination_branch_id',
        'transfer_code',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'completed_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function sourceBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function approve(User $user): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        return $this->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now()
        ]);
    }

    public function complete(): bool
    {
        if ($this->status !== 'in_transit') {
            return false;
        }

        return $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    public function cancel(): bool
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }

        return $this->update([
            'status' => 'cancelled'
        ]);
    }
} 