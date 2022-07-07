<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Executive;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExecutivesComission>
 */
class ExecutivesComissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "executive_id" => Executive::factory(),
            "client_id" => Client::factory(),
            "comission" => $this->faker->randomFloat(4, 3, 10),
            "start_date" => $this->faker->date("Y-m-d", "-1 month"),
            "end_date" => $this->faker->date()

        ];
    }
}
