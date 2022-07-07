<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VendorRange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VendorSpread>
 */
class VendorSpreadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "vendor_range_id" => VendorRange::factory(),
            "buying_spread" => $this->faker->randomFloat(2, 2.5, 4.5),
            "selling_spread" => $this->faker->randomFloat(2, 2.5, 4.5),
            "active" => $this->faker->boolean(),
            "user_id" => User::factory()
        ];
    }
}
