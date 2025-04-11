<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyRewardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'points_cost' => $this->faker->numberBetween(100, 1000),
            'description' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+1 month', '+1 year'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now')
        ];
    }
} 