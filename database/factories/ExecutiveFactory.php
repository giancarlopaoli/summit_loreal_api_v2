<?php

namespace Database\Factories;

use App\Enums\ExecutiveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Executive>
 */
class ExecutiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "type" => ExecutiveType::getRandomValue(),
            "comission" => $this->faker->randomFloat(2, 3, 10),
            "years" => $this->faker->numberBetween(0, 10),
            "comments" => $this->faker->sentences(2, true),
        ];
    }
}
