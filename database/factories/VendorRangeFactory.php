<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VendorRange>
 */
class VendorRangeFactory extends Factory
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
            "min_range" => $this->faker->randomFloat(2, 2000, 50000),
            "max_range" => $this->faker->randomFloat(2, 10000, 99999),
            "active" => $this->faker->boolean(),
            "updated_by" => User::factory()
        ];
    }
}
