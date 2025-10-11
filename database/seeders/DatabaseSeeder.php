<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test',
            'email' => 'test@mail.com',
            'password' => bcrypt('123456789'),
        ]);

        User::factory(3)->create();
        Category::factory(5)->create();
        Expense::factory(100)->create();
        Payment::factory(10)->create();
    }
}
