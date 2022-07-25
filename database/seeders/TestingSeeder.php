<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Client;
use App\Models\ClientStatus;
use App\Models\Country;
use App\Models\District;
use App\Models\DocumentType;
use App\Models\EconomicActivity;
use App\Models\Operation;
use App\Models\Profession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'admin',
            'last_name' => 'admin',
            'email' => 'email@email.com',
            'document_number' => '12345678',
            'phone' => '987654321',
            'tries' => 0,
            'password' => Hash::make('password'),
            'status' => UserStatus::Activo
        ]);

        $client = Client::create([
            'name' => 'admin',
            'last_name' => 'admin',
            'mothers_name' => 'admin',
            'document_type_id' => DocumentType::all()->random()->id,
            'document_number' => '12345678',
            'phone' => '987654321',
            'email' => 'email@email.com',
            'address' => 'Calle Calle',
            'birthdate' => Carbon::now(),
            'district_id' => District::all()->random()->id,
            'country_id' => Country::all()->random()->id,
            'economic_activity_id' => EconomicActivity::all()->random()->id,
            'profession_id' => Profession::all()->random()->id,
            'customer_type' => 'PN',
            'type' => 'Cliente',
            'client_status_id' => ClientStatus::where('name', 'Activo')->first()->id
        ]);

        $operations = Operation::factory()->state([
            'client_id' => $client->id,
            'user_id' => $user->id,
        ])->count(10);
    }
}
