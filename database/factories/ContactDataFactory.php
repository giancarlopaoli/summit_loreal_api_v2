<?php

namespace Database\Factories;

use App\Enums\ContactDataType;
use App\Models\ContactData;
use App\Models\LeadContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactData>
 */
class ContactDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "lead_contact_id" => LeadContact::factory(),
            "type" => ContactDataType::getRandomKey(),
            "contact" => $this->faker->sentence()
        ];
    }
}
