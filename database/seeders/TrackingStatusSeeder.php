<?php

namespace Database\Seeders;

use App\Models\TrackingStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrackingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TrackingStatus::create([
            'name' => 'Registrado'
        ]);

        TrackingStatus::create([
            'name' => 'Interesado'
        ]);

        TrackingStatus::create([
            'name' => 'No interesado'
        ]);

        TrackingStatus::create([
            'name' => 'No contesta'
        ]);

        TrackingStatus::create([
            'name' => 'Datos incorrectos'
        ]);

        TrackingStatus::create([
            'name' => 'Seguimiento incumplido'
        ]);

        TrackingStatus::create([
            'name' => 'Pendiente Respuesta'
        ]);

    }
}
