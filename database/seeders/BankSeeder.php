<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bank::create([
            'name' => 'Banco de Crédito del Perú',
            'shortname' => 'BCP',
            'corfid_id' => 2,
            'main' => true,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);

        Bank::create([
            'name' => 'Banco Internacional del Perú',
            'shortname' => 'INTERBANK',
            'corfid_id' => 3,
            'main' => false,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);

        Bank::create([
            'name' => 'BBVA Continental',
            'shortname' => 'BBVA',
            'corfid_id' => 11,
            'main' => false,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);

        Bank::create([
            'name' => 'Scotiabank Perú',
            'shortname' => 'SCOTIABANK',
            'corfid_id' => 41,
            'main' => false,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);

        Bank::create([
            'name' => 'Banco Interamericano de Finanzas',
            'shortname' => 'BANBIF',
            'corfid_id' => 38,
            'main' => false,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);

        Bank::create([
            'name' => 'Banco Pichincha',
            'shortname' => 'Pichincha',
            'corfid_id' => 35,
            'main' => false,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);

        Bank::create([
            'name' => 'Banco de Comercio',
            'shortname' => 'Banco de Comercio',
            'corfid_id' => 23,
            'main' => false,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);

        Bank::create([
            'name' => 'CITIBANK N.A LIMA',
            'shortname' => 'CITIBANK',
            'corfid_id' => 7,
            'main' => false,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);

        Bank::create([
            'name' => 'Banco de la Nación',
            'shortname' => 'Nación',
            'corfid_id' => 0,
            'main' => false,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);

    }
}
