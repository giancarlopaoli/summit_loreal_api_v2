<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IbopsRange>
 */
class IbopsRangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "min_range" => $this->faker->randomFloat(2, 0, 5),
            "max_range" => $this->faker->randomFloat(2, 5, 10),
            "comission_spread" => $this->faker->randomFloat(2, 0, 4),
            "spread" => $this->faker->randomFloat(2, 0, 4),
            "user_id" => User::factory()
        ];
    }
}
