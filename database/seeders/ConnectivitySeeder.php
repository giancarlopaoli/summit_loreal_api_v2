<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Connectivity;

class ConnectivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Connectivity::truncate();

        Connectivity::create([
            'ssid' => 'Salón principal',
            'password'    =>  'abcdefg'
        ]);

        Connectivity::create([
            'ssid' => 'Comedor',
            'password'    =>  '12345678'
        ]);

        Connectivity::create([
            'ssid' => 'Terraza',
            'password'    =>  '87654321'
        ]);
    }
}
