<?php

namespace Database\Seeders;

use App\Models\ClientStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        ClientStatus::create([
            'name' => 'Registrado'
        ]);

        ClientStatus::create([
            'name' => 'Aprobado Billex'
        ]);

        ClientStatus::create([
            'name' => 'Activo'
        ]);

        ClientStatus::create([
            'name' => 'Rechazo parcial'
        ]);

        ClientStatus::create([
            'name' => 'Rechazado'
        ]);
    }
}
