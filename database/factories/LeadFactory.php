<?php

namespace Database\Factories;

use App\Models\DocumentType;
use App\Models\Executive;
use App\Models\LeadContactType;
use App\Models\LeadStatus;
use App\Models\Region;
use App\Models\Sector;
use App\Models\TrackingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "contact_type" => $this->faker->randomElement(["Natural", "Juridica"]),
            "document_type_id" => DocumentType::factory(),
            "region_id" => Region::factory(),
            "sector_id" => Sector::factory(),
            "company_name" => $this->faker->company(),
            "document_number" => $this->faker->numerify("########"),
            "lead_contact_type_id" => LeadContactType::factory(),
            "lead_status_id" => LeadStatus::factory(),
            "comments" => $this->faker->sentences(2, true),
            "executive_id" => Executive::factory(),
            "tracking_status" => TrackingStatus::factory(),
            "tracking_date" => $this->faker->date()
        ];
    }
}
