<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'points' => $this->faker->numberBetween(10, 1000),
            'type' => $this->faker->randomElement(['earn', 'redeem']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
            'description' => $this->faker->sentence,
            'reference_type' => $this->faker->randomElement(['sale', 'reward', 'adjustment']),
            'reference_id' => $this->faker->numberBetween(1, 1000),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+1 month', '+1 year'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now')
        ];
    }
} 