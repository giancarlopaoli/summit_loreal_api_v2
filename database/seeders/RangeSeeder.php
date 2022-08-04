<?php

namespace Database\Seeders;

use App\Models\Range;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Range::create([
            'min_range' => 1000.0,
            'max_range' => 9999.99,
            'comission_open' => 100,
            'comission_close' => 120,
            'spread_open' => 200,
            'spread_close' => 200
        ]);

        Range::create([
            'min_range' => 10000.0,
            'max_range' => 49999.99,
            'comission_open' => 120,
            'comission_close' => 120,
            'spread_open' => 150,
            'spread_close' => 200
        ]);

        Range::create([
            'min_range' => 50000.0,
            'max_range' => 99999.99,
            'comission_open' => 100,
            'comission_close' => 120,
            'spread_open' => 150,
            'spread_close' => 200
        ]);

        Range::create([
            'min_range' => 100000.0,
            'max_range' => 249999.99,
            'comission_open' => 80,
            'comission_close' => 120,
            'spread_open' => 120,
            'spread_close' => 200
        ]);

        Range::create([
            'min_range' => 250000.0,
            'max_range' => 20000000.0,
            'comission_open' => 60,
            'comission_close' => 120,
            'spread_open' => 100,
            'spread_close' => 200
        ]);

    }
}
