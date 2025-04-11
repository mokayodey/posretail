<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'loyalty_points',
        'total_spent',
        'last_purchase_at',
        'membership_tier',
        'birth_date',
        'anniversary_date',
        'preferences',
        'status'
    ];

    protected $casts = [
        'preferences' => 'array',
        'last_purchase_at' => 'datetime',
        'birth_date' => 'date',
        'anniversary_date' => 'date'
    ];

    // Relationships
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    // Methods
    public function addPoints(int $points, string $source, string $description = null): void
    {
        $this->loyalty_points += $points;
        $this->save();

        $this->loyaltyTransactions()->create([
            'points' => $points,
            'type' => 'earn',
            'source' => $source,
            'description' => $description
        ]);

        $this->updateMembershipTier();
    }

    public function redeemPoints(int $points, string $description = null): bool
    {
        if ($this->loyalty_points < $points) {
            return false;
        }

        $this->loyalty_points -= $points;
        $this->save();

        $this->loyaltyTransactions()->create([
            'points' => $points,
            'type' => 'redeem',
            'description' => $description
        ]);

        return true;
    }

    public function updateMembershipTier(): void
    {
        $tiers = [
            'bronze' => 0,
            'silver' => 1000,
            'gold' => 5000,
            'platinum' => 10000
        ];

        $newTier = 'bronze';
        foreach ($tiers as $tier => $points) {
            if ($this->loyalty_points >= $points) {
                $newTier = $tier;
            }
        }

        if ($this->membership_tier !== $newTier) {
            $this->membership_tier = $newTier;
            $this->save();
        }
    }

    public function getDiscountRate(): float
    {
        return match($this->membership_tier) {
            'bronze' => 0.05,  // 5% discount
            'silver' => 0.10,  // 10% discount
            'gold' => 0.15,    // 15% discount
            'platinum' => 0.20,// 20% discount
            default => 0.00
        };
    }

    public function getBirthdayReward(): int
    {
        return match($this->membership_tier) {
            'bronze' => 100,
            'silver' => 250,
            'gold' => 500,
            'platinum' => 1000,
            default => 0
        };
    }
} 