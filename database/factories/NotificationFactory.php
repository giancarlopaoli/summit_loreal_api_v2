<?php

namespace Database\Factories;

use App\Models\Operation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "tittle" => $this->faker->words(3, true),
            "description" => $this->faker->sentences(2, true),
            "operation_id" => Operation::factory(),
            "seen" => $this->faker->boolean()
        ];
    }
}
