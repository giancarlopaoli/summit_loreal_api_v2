<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailFormat>
 */
class EmailFormatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "subject" => $this->faker->sentence(),
            "body" => $this->faker->paragraphs(2, true),
            "from_email" => $this->faker->companyEmail()
        ];
    }
}
