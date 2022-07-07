<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Range>
 */
class RangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "min_range" => $this->faker->randomFloat(2, 10, 100),
            "max_range" => $this->faker->randomFloat(2, 100, 500),
            "comission_open" => $this->faker->randomFloat(2, 1, 5),
            "comission_close" => $this->faker->randomFloat(2, 5, 10),
            "spread_open" => $this->faker->randomFloat(2, 1, 5),
            "spread_close" => $this->faker->randomFloat(2, 5, 10),
            "active" => $this->faker->boolean(),
            "modified_by" => User::factory()
        ];
    }
}
