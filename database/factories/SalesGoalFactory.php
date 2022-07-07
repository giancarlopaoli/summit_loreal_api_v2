<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesGoal>
 */
class SalesGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "month" => $this->faker->monthName(),
            "year" => $this->faker->year(),
            "goal" => $this->faker->randomFloat(2, 100000, 50000),
            "daily_goal" => $this->faker->randomFloat(2, 5000, 10000)
        ];
    }
}
