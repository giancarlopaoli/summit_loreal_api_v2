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
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/bcp.png'
        ]);

        Bank::create([
            'name' => 'Banco Internacional del Perú',
            'shortname' => 'INTERBANK',
            'corfid_id' => 3,
            'main' => false,
            'active' => true,
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/interbank.png'
        ]);

        Bank::create([
            'name' => 'BBVA Continental',
            'shortname' => 'BBVA',
            'corfid_id' => 11,
            'main' => false,
            'active' => true,
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/bbva.png'
        ]);

        Bank::create([
            'id' => 5,
            'name' => 'Scotiabank Perú',
            'shortname' => 'SCOTIABANK',
            'corfid_id' => 41,
            'main' => false,
            'active' => true,
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/scotia.png'
        ]);

        Bank::create([
            'id' => 6,
            'name' => 'Banco Interamericano de Finanzas',
            'shortname' => 'BANBIF',
            'corfid_id' => 38,
            'main' => false,
            'active' => true,
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/banbif.png'
        ]);

        Bank::create([
            'id' => 8,
            'name' => 'Banco Pichincha',
            'shortname' => 'Pichincha',
            'corfid_id' => 35,
            'main' => false,
            'active' => true,
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/pichincha.png'
        ]);

        Bank::create([
            'id' => 10,
            'name' => 'Banco de Comercio',
            'shortname' => 'Banco de Comercio',
            'corfid_id' => 23,
            'main' => false,
            'active' => true,
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/bancocomercio.png'
        ]);

        Bank::create([
            'id' => 11,
            'name' => 'CITIBANK N.A LIMA',
            'shortname' => 'Citibank',
            'corfid_id' => 7,
            'main' => false,
            'active' => true,
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/citibank.png'
        ]);

        Bank::create([
            'id' => 12,
            'name' => 'Banco de la Nación',
            'shortname' => 'Nación',
            'corfid_id' => 0,
            'main' => false,
            'active' => true,
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/bn.png'
        ]);

        Bank::create([
            'id' => 13,
            'name' => 'Banco Santander',
            'shortname' => 'Santander',
            'corfid_id' => 22,
            'main' => false,
            'active' => true,
            'image' => 'https://bill-upload.s3.amazonaws.com/static/img/santander.png'
        ]);

    }
}
