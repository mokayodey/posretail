<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyTierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'points_required' => $this->faker->numberBetween(1000, 10000),
            'discount_percentage' => $this->faker->numberBetween(5, 20),
            'description' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now')
        ];
    }
} 