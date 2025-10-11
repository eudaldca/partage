<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Brick\Money\Money;
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
        $money = Money::ofMinor(fake()->numberBetween(1, 10000), 'EUR');
        $users = User::inRandomOrder()->limit(fake()->numberBetween(2, User::count()))->get();
        $split = $money->split($users->count());

        $splitWith = [];
        for ($i = 0; $i < count($users); $i++) {
            $user = $users[$i];
            $splitWith[$user->id] = $split[$i]->getMinorAmount()->toInt();
        }

        return [
            'name' => fake()->word(),
            'amount' => $money,
            'date' => fake()->dateTimeBetween('-30 days'),
            'description' => fake()->sentence(),
            'owner_id' => User::inRandomOrder()->first()->id,
            'category_id' => self::factoryForModel(Category::class),
            'split_with' => $splitWith,
        ];
    }
}
