<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyCardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'card_number' => $this->faker->unique()->numerify('##########'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'blocked']),
            'points_balance' => $this->faker->numberBetween(0, 5000),
            'tier_progress' => $this->faker->numberBetween(0, 1000),
            'last_activity_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now')
        ];
    }
} 