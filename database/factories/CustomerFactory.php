<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'loyalty_points' => $this->faker->numberBetween(0, 10000),
            'total_spent' => $this->faker->randomFloat(2, 0, 100000),
            'last_purchase_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'membership_tier' => $this->faker->randomElement(['bronze', 'silver', 'gold', 'platinum']),
            'birth_date' => $this->faker->date(),
            'anniversary_date' => $this->faker->date(),
            'preferences' => [
                'newsletter' => $this->faker->boolean,
                'sms_notifications' => $this->faker->boolean
            ],
            'status' => 'active'
        ];
    }
} 