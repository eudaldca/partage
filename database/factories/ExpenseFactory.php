<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'amount' => fake()->numberBetween(1, 100),
            'date' => fake()->dateTimeBetween('-30 days'),
            'description' => fake()->sentence(),
            'owner_id' => self::factoryForModel(User::class),
            'category_id' => self::factoryForModel(Category::class),
        ];
    }
}
