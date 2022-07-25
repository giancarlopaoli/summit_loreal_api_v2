<?php

namespace Database\Seeders;

use App\Models\EconomicActivity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EconomicActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EconomicActivity::create([
           'name' => 'Test activity',
           'code' => 'TEST'
        ]);
    }
}
