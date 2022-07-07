<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IbopsClientComission>
 */
class IbopsClientComissionFactory extends Factory
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
            "comission_spread" => $this->faker->randomFloat(2, 0, 4),
            "spread" => $this->faker->randomFloat(2, 0, 4)
        ];
    }
}
