<?php

namespace Database\Factories;

use App\Models\Association;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssociationComission>
 */
class AssociationComissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "association_id" => Association::factory(),
            "comission_open" => $this->faker->randomFloat(2, 1, 10),
            "comission_close" => $this->faker->randomFloat(2, 1, 10),
            "active" => $this->faker->boolean(),
            "updated_by" => User::factory()
        ];
    }
}
