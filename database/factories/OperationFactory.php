<?php

namespace Database\Factories;

use App\Enums\OperationClass;
use App\Enums\OperationType;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\OperationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operation>
 */
class OperationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "code" => $this->faker->asciify("********"),
            "class" => OperationClass::getRandomValue(),
            "type" => OperationType::getRandomValue(),
            "client_id" => Client::factory(),
            "user_id" => User::factory(),
            "amount" => $this->faker->randomFloat(2, 2000, 100000),
            "currency_id" => Currency::factory(),
            "exchange_rate" => $this->faker->randomFloat(6, 2.5, 4.5),
            "comission_spread" => $this->faker->randomFloat(2, 3, 10),
            "spread" => $this->faker->randomFloat(2, 1, 5),
            "igv" => $this->faker->randomFloat(2, 100, 1000),
            "operation_status_id" => OperationStatus::factory(),
            "transfer_number" => $this->faker->randomNumber(3),
            "base_exchange_rate" => $this->faker->randomFloat(6, 2.5, 4.5),
            "coupon_id" => $this->faker->boolean() ? Coupon::factory() : null,
            "coupon_code" => $this->faker->optional()->asciify("******"),
            "coupon_value" => $this->faker->optional()->randomFloat(2, 5, 20),
            "operation_date" => $this->faker->date()
        ];
    }
}
