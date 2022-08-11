<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\DocumentType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
            'last_name' => $this->faker->firstName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'document_type_id' => DocumentType::factory(),
            'document_number' => $this->faker->asciify("********"),
            'tries' => $this->faker->numberBetween(0, 3),
            'password' => Hash::make("password"),
            'last_login' => $this->faker->dateTime(),
            'status' => UserStatus::getRandomValue()
        ];
    }

    public function active() {
        return $this->state(function (array $atributes) {
            return  [
                'status' => UserStatus::Activo
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
