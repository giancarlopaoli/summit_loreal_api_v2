<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadContact;
use App\Models\TrackingForm;
use App\Models\TrackingPhase;
use App\Models\TrackingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeadTracking>
 */
class LeadTrackingFactory extends Factory
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
            "tracking_status_id" => TrackingStatus::factory(),
            "tracking_form_id" => TrackingForm::factory(),
            "tracking_phase_id" => TrackingPhase::factory(),
            "lead_contact_id" => LeadContact::factory(),
            "comments" => $this->faker->words(3, true)
        ];
    }
}
