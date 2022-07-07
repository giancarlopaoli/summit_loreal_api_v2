<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RucData>
 */
class RucDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "ruc" => $this->faker->numerify("############"),
            "business" => $this->faker->company(),
            "tradename" => $this->faker->company(),
            "address" => $this->faker->address()
        ];
    }
}
