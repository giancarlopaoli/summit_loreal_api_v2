<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\DocumentType;
use App\Models\EconomicActivity;
use App\Models\Profession;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            AccountTypeSeeder::class,
            DocumentTypeSeeder::class,
            BankSeeder::class,
            BankAccountStatusSeeder::class,
            ConfigurationSeeder::class,
            OperationStatusSeeder::class,
            ClientStatusSeeder::class,
            CurrencySeeder::class,
            IbopsRangeSeeder::class,
            DepartmentSeeder::class,
            ProvinceSeeder::class,
            DistrictSeeder::class,
            //EconomicActivitySeeder::class,
            LeadStatusSeeder::class,
            //ProfessionSeeder::class,
            RegionSeeder::class,
            RangeSeeder::class,
            SectorSeeder::class,
            LeadContactTypeSeeder::class,
            //EscrowAccountSeeder::class,
            ExecutiveSeeder::class,
            TrackingFormSeeder::class,
            TrackingStatusSeeder::class,
            TrackingPhaseSeeder::class
        ]);

        $this->call(PRDSeeder::class);
        /*if(env('APP_ENV') == 'prod') {
        }
        else{
            $this->call(TestingSeeder::class);
        }*/


        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
