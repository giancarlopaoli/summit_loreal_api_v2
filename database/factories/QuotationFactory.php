<?php

namespace Database\Factories;

use App\Enums\QuotationType;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "user_id" => User::factory(),
            "client_id" => Client::factory(),
            "type" => QuotationType::getRandomValue(),
            "ammount" => $this->faker->randomFloat(2, 2000, 99999),
            "exchange_rate" => $this->faker->randomFloat(6, 2.5, 4.5),
            "comission_spread" => $this->faker->randomFloat(2, 3, 10),
            "igv" => $this->faker->randomFloat(2, 100, 1000),
            "spread" => $this->faker->randomFloat(2, 1, 5)
        ];
    }
}
