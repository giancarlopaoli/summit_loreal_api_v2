<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeadContact>
 */
class LeadContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "lead_id" => Lead::factory(),
            "names" => $this->faker->firstName(),
            "last_names" => $this->faker->lastName(),
            "area" => $this->faker->city(),
            "job_tittle" => $this->faker->words(2, true),
            "main_contact" => $this->faker->boolean
        ];
    }
}
