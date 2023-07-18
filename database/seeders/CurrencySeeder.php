<?php

namespace Database\Seeders;

use App\Models\Currency;
use Composer\Util\Http\CurlResponse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Currency::create([
            'name' => 'Soles',
            'iso_code' => 'PEN',
            'sign' => 'S/',
            'sbs_code' => '',
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/pen.png',
            'active' => true
        ]);

        Currency::create([
            'name' => 'Dolares',
            'iso_code' => 'USD',
            'sign' => '$',
            'sbs_code' => '',
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/usd.png',
            'active' => true
        ]);
    }
}
