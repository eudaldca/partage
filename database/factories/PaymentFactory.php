<?php

namespace Database\Factories;

use App\Models\User;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_user_id' => User::inRandomOrder()->first()->id,
            'to_user_id' => User::inRandomOrder()->first()->id,
            'amount' => Money::ofMinor(fake()->numberBetween(1, 10000), 'EUR'),
            'date' => fake()->dateTimeBetween('-30 days'),
        ];
    }
}
