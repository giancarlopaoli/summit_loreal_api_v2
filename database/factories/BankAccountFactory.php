<?php

namespace Database\Factories;

use App\Enums\BankAccountStatus;
use App\Models\AccountType;
use App\Models\Bank;
use App\Models\Client;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankAccount>
 */
class BankAccountFactory extends Factory
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
            "alias" => $this->faker->words(2, true),
            "bank_id" => Bank::all()->random()->id,
            "account_number" => $this->faker->creditCardNumber(),
            "cci_number" => $this->faker->creditCardNumber(),
            "bank_account_status_id" => \App\Models\BankAccountStatus::all()->random()->id,
            "comments" => $this->faker->words(4, true),
            "account_type_id" => AccountType::all()->random()->id,
            "currency_id" => Currency::all()->random()->id,
            "updated_by" => null,
        ];
    }
}
