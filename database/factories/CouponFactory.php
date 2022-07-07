<?php

namespace Database\Factories;

use App\Enums\CouponClass;
use App\Enums\CouponType;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "campaign_id" => Campaign::factory(),
            "code" => $this->faker->asciify("*****"),
            "description" => $this->faker->words(3, true),
            "type" => CouponType::getRandomValue(),
            "class" => CouponClass::getRandomValue(),
            "value" => $this->faker->randomFloat(2, 3, 10),
            "limit_total" => $this->faker->randomNumber(5),
            "limit_individual" => $this->faker->randomNumber(3),
            "start_month" => $this->faker->date("Y-m-d", "-1 month"),
            "end_date" => $this->faker->date()
        ];
    }
}
