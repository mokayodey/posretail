<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyRedemptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'reward_id' => \App\Models\LoyaltyReward::factory(),
            'points_used' => $this->faker->numberBetween(100, 5000),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
            'redemption_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'branch_id' => \App\Models\Branch::factory(),
            'notes' => $this->faker->sentence
        ];
    }
} 