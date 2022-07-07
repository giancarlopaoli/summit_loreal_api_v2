<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientComission>
 */
class ClientComissionFactory extends Factory
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
            "comission_open" => $this->faker->randomFloat(2, 0, 5),
            "comission_close" => $this->faker->randomFloat(2, 0, 5),
            "active" => $this->faker->boolean(),
            "updated_by" => User::factory()
        ];
    }
}
