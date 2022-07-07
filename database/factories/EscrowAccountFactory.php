<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EscrowAccount>
 */
class EscrowAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "bank_id" => Bank::factory(),
            "account_number" => $this->faker->creditCardNumber(),
            "cci_number" => $this->faker->creditCardNumber(),
            "currency_id" => Currency::factory(),
            "beneficiary_name" => $this->faker->name(),
            "beneficiary_address" => $this->faker->address(),
            "document_type_id" => DocumentType::factory(),
            "document_number" => $this->faker->numerify("########"),
            "active" => $this->faker->boolean(),
        ];
    }
}
