<?php

namespace Database\Factories;

use App\Models\Association;
use App\Models\ClientStatus;
use App\Models\ClientTracking;
use App\Models\Country;
use App\Models\District;
use App\Models\DocumentType;
use App\Models\EconomicActivity;
use App\Models\Executive;
use App\Models\Profession;
use App\Models\TrackingPhase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "name" => $this->faker->firstName(),
            "last_name" => $this->faker->lastName(),
            "mothers_name" => $this->faker->lastName(),
            "document_type_id" => DocumentType::factory(),
            "document_number" => $this->faker->numberBetween(10000000, 99999999),
            "phone" => $this->faker->phoneNumber(),
            "email" => $this->faker->email(),
            "address" => $this->faker->address(),
            "birthdate" => $this->faker->date("Y-m-d", "2000-01-01"),
            "district_id" => District::factory(),
            "country_id" => Country::factory(),
            "economic_activity_id" => EconomicActivity::factory(),
            "profession_id" => Profession::factory(),
            "customer_type" => $this->faker->randomElement(["PN", "PJ"]),
            "type" => $this->faker->randomElement(["Cliente", "PL"]),
            "client_status_id" => ClientStatus::factory(),
            "accountable_email" => $this->faker->companyEmail(),
            "comments" => $this->faker->sentence(),
            "funds_source" => $this->faker->sentence(),
            "funds_comments" => $this->faker->sentence(),
            "other_funds_comments" => $this->faker->sentence(),
            "pep" => $this->faker->boolean,
            "pep_company" => $this->faker->company(),
            "pep_position" => $this->faker->words(2, true),
            "corfid_id" => $this->faker->randomElement([null, 1, 2]),
            "corfid_message" => $this->faker->sentence(),
            "association_id" => Association::factory(),
            "billex_approved_at" => $this->faker->dateTime("-1 hour"),
            "corfid_approved_at" => $this->faker->dateTime("-1 hour"),
            "updated_by" => null,
            "executive_id" => Executive::factory(),
            "tracking_phase_id" => TrackingPhase::factory(),
            "tracking_date" => $this->faker->dateTime(),
            "comission_start_date" => $this->faker->dateTime(),
            "comission" => $this->faker->randomFloat(4, 0, 5),
            "invoice_to" => null
        ];
    }
}
