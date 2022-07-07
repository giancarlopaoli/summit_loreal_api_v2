<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Configuration>
 */
class ConfigurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "shortname" => $this->faker->word(),
            "description" => $this->faker->sentence(),
            "value" => $this->faker->words(),
            "active" => $this->faker->boolean(),
            "updated_by" => User::factory()
        ];
    }
}
