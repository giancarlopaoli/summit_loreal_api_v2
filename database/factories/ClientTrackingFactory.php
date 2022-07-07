<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\TrackingForm;
use App\Models\TrackingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientTracking>
 */
class ClientTrackingFactory extends Factory
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
            "tracking_status_id" => TrackingStatus::factory(),
            "tracking_form_id" => TrackingForm::factory(),
            "comments" => $this->faker->sentence(),
            "created_by" => User::factory()
        ];
    }
}
