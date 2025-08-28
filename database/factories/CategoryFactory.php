<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
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
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['fas-car', 'fas-home', 'fas-shopping-cart', 'fas-dumbbell', 'fas-utensils']),
            'color' => fake()->hexColor(),
        ];
    }
}
