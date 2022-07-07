<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DniData>
 */
class DniDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "dni" => $this->faker->randomNumber(8),
            "name" => $this->faker->firstName(),
            "last_name" => $this->faker->lastName(),
            "mothers_name" => $this->faker->lastName()
        ];
    }
}
