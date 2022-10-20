<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\BankAccount;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        

        $role = Role::create(['name' => 'cliente']);
        $role = Role::create(['name' => 'administrador']);
        $role = Role::create(['name' => 'operaciones']);
        $role = Role::create(['name' => 'proveedor']);
        $role = Role::create(['name' => 'corfid']);
        $role = Role::create(['name' => 'ejecutivos']);
        $role = Role::create(['name' => 'supervisores']);
        Permission::create(['name' => 'firmar_operaciones']);

        $user = User::create([
            'name' => 'admin',
            'last_name' => 'admin',
            'email' => 'email@email.com',
            'document_number' => '12345678',
            'document_type_id' => '2',
            'phone' => '987654321',
            'tries' => 0,
            'password' => Hash::make('password'),
            'status' => UserStatus::Activo
        ]);

        $user->assignRole('cliente');

        $user1 = User::create([
            'name' => 'giancarlopaoli',
            'last_name' => 'Paoli',
            'email' => 'giancarlopaoli@gmail.com',
            'document_number' => '42868509',
            'document_type_id' => '2',
            'phone' => '998102921',
            'tries' => 0,
            'password' => Hash::make('password'),
            'status' => UserStatus::Activo
        ]);

        $user1->assignRole('cliente');

        $user2 = User::create([
            'name' => 'Giancarlo',
            'last_name' => 'Paoli',
            'email' => 'giancarlo.paoli@billex.pe',
            'document_number' => '42868509-1',
            'document_type_id' => '2',
            'phone' => '998102921',
            'tries' => 0,
            'password' => Hash::make('password'),
            'status' => UserStatus::Activo
        ]);

        $user2->assignRole('administrador');
        $user2->assignRole('operaciones');
        $user2->assignRole('proveedor');
        $user2->assignRole('corfid');
        $user2->assignRole('ejecutivos');
        $user2->assignRole('supervisores');
        $user2->givePermissionTo('firmar_operaciones');

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

        $client2 = Client::create([
            'name' => 'Bill Financial Services SA',
            'last_name' => 'Billex',
            'mothers_name' => '',
            'document_type_id' => DocumentType::all()->random()->id,
            'document_number' => '20601788676',
            'phone' => '987654321',
            'email' => 'tenologia@billex.pe',
            'address' => 'Av Benavides 1944 - Miraflores',
            'birthdate' => Carbon::now(),
            'district_id' => District::all()->random()->id,
            'country_id' => Country::all()->random()->id,
            'economic_activity_id' => EconomicActivity::all()->random()->id,
            'profession_id' => Profession::all()->random()->id,
            'customer_type' => 'PJ',
            'type' => 'Cliente',
            'client_status_id' => ClientStatus::where('name', 'Activo')->first()->id
        ]);

        $user->clients()->attach($client, ['status' => 'Asignado']);
        $user->clients()->attach($client2, ['status' => 'Activo']);
        $user1->clients()->attach($client2, ['status' => 'Activo']);

        $operations = Operation::factory()->state([
            'client_id' => $client->id,
            'user_id' => $user->id,
        ])->count(20)
            ->create();

        $bank_accounts = BankAccount::factory()->state([
            'client_id' => $client->id
        ])->count(10)
            ->create();

        $main_bank_account = $bank_accounts->first();
        $main_bank_account->main = true;
        $main_bank_account->save();

        // Exchange Rate dummy data
        DB::table('exchange_rates')->insert([
            [
                'venta' => 3.7130,
                'compra' => 3.7110,
                'created_at' => '2021-12-03 09:01'
            ],
            [
                'venta' => 3.7130,
                'compra' => 3.7100,
                'created_at' => '2021-12-03 09:02'
            ],
            [
                'venta' => 3.7130,
                'compra' => 3.7120,
                'created_at' => '2021-12-03 09:03'
            ],
            [
                'venta' => 3.7180,
                'compra' => 3.7120,
                'created_at' => '2021-12-03 09:04'
            ],
            [
                'venta' => 3.7150,
                'compra' => 3.7100,
                'created_at' => '2021-12-03 09:05'
            ],
            [
                'venta' => 3.7150,
                'compra' => 3.7090,
                'created_at' => '2021-12-03 09:06'
            ],
            ['compra' => 3.860, 'venta' => 3.855, 'created_at' => '2021-06-20 13:30'],
            ['compra' => 3.870, 'venta' => 3.865, 'created_at' => '2021-06-30 13:30'],
            ['compra' => 3.875, 'venta' => 3.87, 'created_at' => '2021-07-10 13:30'],
            ['compra' => 3.882, 'venta' => 3.88, 'created_at' => '2021-07-20 13:30'],
            ['compra' => 3.887, 'venta' => 3.885, 'created_at' => '2021-07-30 13:30'],
            ['compra' => 3.893, 'venta' => 3.89, 'created_at' => '2021-08-10 13:30'],
            ['compra' => 3.92, 'venta' => 3.915, 'created_at' => '2021-08-20 13:30'],
            ['compra' => 3.93, 'venta' => 3.92, 'created_at' => '2021-08-30 13:30'],
            ['compra' => 3.96, 'venta' => 3.94, 'created_at' => '2021-09-10 13:30'],
            ['compra' => 3.97, 'venta' => 3.95, 'created_at' => '2021-09-20 13:30'],
            ['compra' => 3.99, 'venta' => 3.98, 'created_at' => '2021-09-30 13:30'],
            ['compra' => 4.01, 'venta' => 3.99, 'created_at' => '2021-10-10 13:30'],
            ['compra' => 4.03, 'venta' => 4.025, 'created_at' => '2021-10-20 13:30'],
            ['compra' => 4.03, 'venta' => 4.02, 'created_at' => '2021-10-30 13:30'],
            ['compra' => 4.04, 'venta' => 4.035, 'created_at' => '2021-11-01 13:30'],
            ['compra' => 4.06, 'venta' => 4.05, 'created_at' => '2021-11-10 13:30'],
            ['compra' => 4.02, 'venta' => 4.01, 'created_at' => '2021-11-20 13:30'],
            ['compra' => 3.98, 'venta' => 3.96, 'created_at' => '2021-12-01 13:30'],
            ['compra' => 4.0, 'venta' => 4.0, 'created_at' => '2021-12-15 13:30'],
            ['compra' => 4.02, 'venta' => 4.01, 'created_at' => '2021-12-20 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2021-12-21 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2021-12-22 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2021-12-23 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2021-12-24 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2021-12-27 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2021-12-28 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2021-12-29 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2021-12-30 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2021-12-31 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-03 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-04 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-05 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-06 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-07 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-10 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-11 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-12 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-13 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-14 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-17 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-18 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-19 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-20 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-21 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-24 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-25 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-26 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-27 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => '2022-01-28 13:30'],
            ['compra' => 3.95, 'venta' => 3.93, 'created_at' => '2022-01-31 13:30'],
            ['compra' => 3.94, 'venta' => 3.93, 'created_at' => '2022-02-01 13:35'],
            ['compra' => 3.93, 'venta' => 3.92, 'created_at' => '2022-02-01 13:35'],
            ['compra' => 3.93, 'venta' => 3.925, 'created_at' => '2022-02-01 13:35'],
            ['compra' => 3.92, 'venta' => 3.91, 'created_at' => '2022-02-02 13:39'],
            ['compra' => 3.90, 'venta' => 3.88, 'created_at' => '2022-03-30 13:30'],
            ['compra' => 3.90, 'venta' => 3.88, 'created_at' => '2022-04-03 13:30'],
            ['compra' => 3.90, 'venta' => 3.88, 'created_at' => '2022-04-29 13:30'],
            ['compra' => 3.78, 'venta' => 3.80, 'created_at' => '2022-05-01 13:30'],
            ['compra' => 3.79, 'venta' => 3.81, 'created_at' => '2022-05-15 13:30'],
            ['compra' => 3.80, 'venta' => 3.82, 'created_at' => '2022-05-30 13:30'],
            ['compra' => 3.80, 'venta' => 3.82, 'created_at' => '2022-05-31 13:30'],
            ['compra' => 3.78, 'venta' => 3.80, 'created_at' => '2022-06-01 13:30'],
            ['compra' => 3.75, 'venta' => 3.77, 'created_at' => '2022-06-02 13:30'],
            ['compra' => 3.76, 'venta' => 3.78, 'created_at' => '2022-06-03 13:30'],
            ['compra' => 3.75, 'venta' => 3.77, 'created_at' => '2022-06-06 13:30'],
            ['compra' => 3.73, 'venta' => 3.75, 'created_at' => '2022-06-07 13:30'],
            ['compra' => 3.72, 'venta' => 3.74, 'created_at' => '2022-06-08 13:30'],
            ['compra' => 3.72, 'venta' => 3.74, 'created_at' => '2022-06-08 13:30'],
            ['compra' => 3.69, 'venta' => 3.71, 'created_at' => '2022-06-10 13:30'],
            ['compra' => 3.71, 'venta' => 3.73, 'created_at' => '2022-06-13 13:30'],
            ['compra' => 3.74, 'venta' => 3.76, 'created_at' => '2022-06-14 13:30'],
            ['compra' => 3.75, 'venta' => 3.77, 'created_at' => '2022-06-15 13:30'],
            ['compra' => 4.085, 'venta' => 4.07, 'created_at' => Carbon::now()->toDateString() . ' 08:59'],
            ['compra' => 4.085, 'venta' => 4.065, 'created_at' => Carbon::now()->toDateString() . ' 09:06'],
            ['compra' => 4.085, 'venta' => 4.071, 'created_at' => Carbon::now()->toDateString() . ' 09:06'],
            ['compra' => 4.085, 'venta' => 4.075, 'created_at' => Carbon::now()->toDateString() . ' 09:07'],
            ['compra' => 4.08, 'venta' => 4.075, 'created_at' => Carbon::now()->toDateString() . ' 09:16'],
            ['compra' => 4.084, 'venta' => 4.08, 'created_at' => Carbon::now()->toDateString() . ' 09:28'],
            ['compra' => 4.084, 'venta' => 4.077, 'created_at' => Carbon::now()->toDateString() . ' 09:28'],
            ['compra' => 4.083, 'venta' => 4.077, 'created_at' => Carbon::now()->toDateString() . ' 09:28'],
            ['compra' => 4.083, 'venta' => 4.08, 'created_at' => Carbon::now()->toDateString() . ' 09:29'],
            ['compra' => 4.085, 'venta' => 4.08, 'created_at' => Carbon::now()->toDateString() . ' 09:29'],
            ['compra' => 4.083, 'venta' => 4.08, 'created_at' => Carbon::now()->toDateString() . ' 09:30'],
            ['compra' => 4.084, 'venta' => 4.08, 'created_at' => Carbon::now()->toDateString() . ' 09:34'],
            ['compra' => 4.085, 'venta' => 4.08, 'created_at' => Carbon::now()->toDateString() . ' 09:37'],
            ['compra' => 4.085, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:38'],
            ['compra' => 4.085, 'venta' => 4.08, 'created_at' => Carbon::now()->toDateString() . ' 09:38'],
            ['compra' => 4.085, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:39'],
            ['compra' => 4.084, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:43'],
            ['compra' => 4.085, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:43'],
            ['compra' => 4.083, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:44'],
            ['compra' => 4.084, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:44'],
            ['compra' => 4.083, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:46'],
            ['compra' => 4.084, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:47'],
            ['compra' => 4.085, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 09:47'],
            ['compra' => 4.084, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 09:51'],
            ['compra' => 4.085, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 09:51'],
            ['compra' => 4.084, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 09:51'],
            ['compra' => 4.083, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:52'],
            ['compra' => 4.084, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 09:54'],
            ['compra' => 4.084, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 09:55'],
            ['compra' => 4.084, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 10:01'],
            ['compra' => 4.084, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 10:03'],
            ['compra' => 4.083, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 10:03'],
            ['compra' => 4.084, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 10:03'],
            ['compra' => 4.083, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 10:05'],
            ['compra' => 4.084, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 10:08'],
            ['compra' => 4.085, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 10:19'],
            ['compra' => 4.085, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 10:20'],
            ['compra' => 4.086, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 10:20'],
            ['compra' => 4.086, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 10:24'],
            ['compra' => 4.085, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 10:24'],
            ['compra' => 4.085, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 10:24'],
            ['compra' => 4.085, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 10:25'],
            ['compra' => 4.084, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 10:26'],
            ['compra' => 4.085, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 10:30'],
            ['compra' => 4.085, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 10:37'],
            ['compra' => 4.086, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 10:43'],
            ['compra' => 4.085, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 10:44'],
            ['compra' => 4.086, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 10:52'],
            ['compra' => 4.086, 'venta' => 4.085, 'created_at' => Carbon::now()->toDateString() . ' 10:56'],
            ['compra' => 4.086, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 10:57'],
            ['compra' => 4.086, 'venta' => 4.085, 'created_at' => Carbon::now()->toDateString() . ' 10:58'],
            ['compra' => 4.086, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 11:01'],
            ['compra' => 4.085, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 11:02'],
            ['compra' => 4.086, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 11:05'],
            ['compra' => 4.085, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 11:05'],
            ['compra' => 4.086, 'venta' => 4.085, 'created_at' => Carbon::now()->toDateString() . ' 11:14'],
            ['compra' => 4.086, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 11:32'],
            ['compra' => 4.085, 'venta' => 4.084, 'created_at' => Carbon::now()->toDateString() . ' 11:44'],
            ['compra' => 4.084, 'venta' => 4.083, 'created_at' => Carbon::now()->toDateString() . ' 11:46'],
            ['compra' => 4.083, 'venta' => 4.082, 'created_at' => Carbon::now()->toDateString() . ' 11:51'],
            ['compra' => 4.082, 'venta' => 4.081, 'created_at' => Carbon::now()->toDateString() . ' 11:58'],
            ['compra' => 4.081, 'venta' => 4.080, 'created_at' => Carbon::now()->toDateString() . ' 11:59'],
            ['compra' => 4.080, 'venta' => 4.078, 'created_at' => Carbon::now()->toDateString() . ' 12:04'],
            ['compra' => 4.080, 'venta' => 4.077, 'created_at' => Carbon::now()->toDateString() . ' 12:14'],
            ['compra' => 4.080, 'venta' => 4.078, 'created_at' => Carbon::now()->toDateString() . ' 12:24'],
            ['compra' => 4.081, 'venta' => 4.079, 'created_at' => Carbon::now()->toDateString() . ' 12:44'],
            ['compra' => 4.082, 'venta' => 4.079, 'created_at' => Carbon::now()->toDateString() . ' 12:54'],
            ['compra' => 4.081, 'venta' => 4.078, 'created_at' => Carbon::now()->toDateString() . ' 13:07'],
            ['compra' => 4.080, 'venta' => 4.077, 'created_at' => Carbon::now()->toDateString() . ' 13:14'],
            ['compra' => 4.079, 'venta' => 4.075, 'created_at' => Carbon::now()->toDateString() . ' 13:17'],
            ['compra' => 4.078, 'venta' => 4.074, 'created_at' => Carbon::now()->toDateString() . ' 13:22'],
            ['compra' => 4.075, 'venta' => 4.072, 'created_at' => Carbon::now()->toDateString() . ' 13:26'],
            ['compra' => 4.075, 'venta' => 4.072, 'created_at' => Carbon::now()->toDateString() . ' 13:27'],
            ['compra' => 4.073, 'venta' => 4.070, 'created_at' => Carbon::now()->toDateString() . ' 13:28'],
            ['compra' => 4.074, 'venta' => 4.071, 'created_at' => Carbon::now()->toDateString() . ' 13:29'],
            ['compra' => 4.078, 'venta' => 4.073, 'created_at' => Carbon::now()->toDateString() . ' 13:30'],
            ['compra' => 4.078, 'venta' => 4.072, 'created_at' => Carbon::now()->toDateString() . ' 13:31']
        ]);
    }
}
