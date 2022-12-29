<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Region::create([
            'name' => 'AMAZONAS'
        ]);

        Region::create([
            'name' => 'ANCASH'
        ]);

        Region::create([
            'name' => 'APURIMAC'
        ]);

        Region::create([
            'name' => 'AREQUIPA'
        ]);

        Region::create([
            'name' => 'AYACUCHO'
        ]);

        Region::create([
            'name' => 'CAJAMARCA'
        ]);

        Region::create([
            'name' => 'CALLAO'
        ]);

        Region::create([
            'name' => 'CUSCO'
        ]);

        Region::create([
            'name' => 'HUANCAVELICA'
        ]);

        Region::create([
            'name' => 'HUANUCO'
        ]);

        Region::create([
            'name' => 'ICA'
        ]);

        Region::create([
            'name' => 'JUNIN'
        ]);

        Region::create([
            'name' => 'LA LIBERTAD'
        ]);

        Region::create([
            'name' => 'LAMBAYEQUE'
        ]);

        Region::create([
            'name' => 'LIMA'
        ]);

        Region::create([
            'name' => 'LORETO'
        ]);

        Region::create([
            'name' => 'MADRE DE DIOS'
        ]);

        Region::create([
            'name' => 'MOQUEGUA'
        ]);

        Region::create([
            'name' => 'PASCO'
        ]);

        Region::create([
            'name' => 'PIURA'
        ]);

        Region::create([
            'name' => 'PUNO'
        ]);

        Region::create([
            'name' => 'SAN MARTIN'
        ]);

        Region::create([
            'name' => 'TACNA'
        ]);

        Region::create([
            'name' => 'TUMBES'
        ]);

        Region::create([
            'name' => 'UCAYALI'
        ]);
    }
}
