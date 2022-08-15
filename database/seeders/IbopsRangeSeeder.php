<?php

namespace Database\Seeders;

use App\Models\IbopsRange;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IbopsRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        IbopsRange::create([
            'min_range' => 10000.0,
            'max_range' => 29999.99,
            'currency_id' => 1,
            'comission_spread' => 30,
            'spread' => 100
        ]);

        IbopsRange::create([
            'min_range' => 30000.0,
            'max_range' => 49999.99,
            'currency_id' => 1,
            'comission_spread' => 25,
            'spread' => 80
        ]);

        IbopsRange::create([
            'min_range' => 50000.0,
            'max_range' => 99999.99,
            'currency_id' => 1,
            'comission_spread' => 20,
            'spread' => 50
        ]);

        IbopsRange::create([
            'min_range' => 100000.0,
            'max_range' => 1000000,
            'currency_id' => 1,
            'comission_spread' => 15,
            'spread' => 30
        ]);

        IbopsRange::create([
            'min_range' => 10000.0,
            'max_range' => 29999.99,
            'currency_id' => 2,
            'comission_spread' => 25,
            'spread' => 80
        ]);

        IbopsRange::create([
            'min_range' => 30000.0,
            'max_range' => 49999.99,
            'currency_id' => 2,
            'comission_spread' => 20,
            'spread' => 60
        ]);

        IbopsRange::create([
            'min_range' => 50000.0,
            'max_range' => 99999.99,
            'currency_id' => 2,
            'comission_spread' => 15,
            'spread' => 40
        ]);

        IbopsRange::create([
            'min_range' => 100000.0,
            'max_range' => 1000000,
            'currency_id' => 2,
            'comission_spread' => 10,
            'spread' => 20
        ]);
    }
}
