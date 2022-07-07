<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrackingPhase>
 */
class TrackingPhaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "name" => $this->faker->words(2, true),
            "min_days" => $this->faker->numberBetween(1, 3),
            "max_days" => $this->faker->numberBetween(1, 3),
        ];
    }
}
