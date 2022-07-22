<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "name" => $this->faker->words(3, true),
            "started_at" => $this->faker->dateTimeInInterval("-1 month"),
            "finished_at" => $this->faker->dateTimeInInterval("+1 month"),
            "active" => $this->faker->boolean(),
            "user_id" => User::factory()
        ];
    }
}
