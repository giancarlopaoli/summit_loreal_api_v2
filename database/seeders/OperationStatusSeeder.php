<?php

namespace Database\Seeders;

use App\Models\OperationStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OperationStatus::create([
            'name' => 'Disponible'
        ]);

        OperationStatus::create([
            'name' => 'Pendiente envio fondos'
        ]);

        OperationStatus::create([
            'name' => 'Pendiente fondos contraparte'
        ]);

        OperationStatus::create([
            'name' => 'Contravalor recaudado'
        ]);

        OperationStatus::create([
            'name' => 'Fondos enviados'
        ]);

        OperationStatus::create([
            'name' => 'Facturado'
        ]);

        OperationStatus::create([
            'name' => 'Finalizado sin factura'
        ]);

        OperationStatus::create([
            'name' => 'Pendiente facturar'
        ]);

        OperationStatus::create([
            'name' => 'Cancelado'
        ]);

        OperationStatus::create([
            'name' => 'Expirado'
        ]);

    }
}
