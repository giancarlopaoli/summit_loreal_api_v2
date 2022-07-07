<?php

namespace Database\Factories;

use App\Enums\RepresentativeType;
use App\Models\Client;
use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Representative>
 */
class RepresentativeFactory extends Factory
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
            "representative_type" => RepresentativeType::getRandomValue(),
            "document_type_id" => DocumentType::factory(),
            "document_number" => $this->faker->numerify("########"),
            "names" => $this->faker->firstName(),
            "last_name" => $this->faker->lastName(),
            "mothers_name" => $this->faker->lastName(),
            "pep" => $this->faker->boolean(),
            "pep_company" => $this->faker->company(),
            "pep_position" => $this->faker->words(3, true)
        ];
    }
}
