<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "compra" => $this->faker->randomFloat(4, 2.5, 4.5),
            "venta" => $this->faker->randomFloat(4, 2.5, 4.5)
        ];
    }
}
