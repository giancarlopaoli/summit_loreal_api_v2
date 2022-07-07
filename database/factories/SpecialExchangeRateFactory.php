<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpecialExchangeRate>
 */
class SpecialExchangeRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "client_id" => Client::factory(),
            "vendor_id" => Client::factory(),
            "buying" => $this->faker->randomFloat(4, 2.5, 4.5),
            "selling" => $this->faker->randomFloat(4, 2.5, 4.5),
            "duration_time" => $this->faker->numberBetween(10, 30),
            "active" => $this->faker->boolean()
        ];
    }
}
