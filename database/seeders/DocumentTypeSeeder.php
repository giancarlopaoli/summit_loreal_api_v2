<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DocumentType::create([
            'name' => 'RUC',
            'active' => true,
            'size' => 11
        ]);

        DocumentType::create([
            'name' => 'DNI',
            'active' => true,
            'size' => 8
        ]);

        DocumentType::create([
            'name' => 'Carné de extranjería',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'name' => 'Carné de identidad de las Fuerzas Policiales',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'name' => 'Carné de identidad de las Fuerzas Armadas',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'name' => 'Pasaporte',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'name' => 'Otros (Carta de Identidad, Cedula de identidad, Partida de Nacimiento, etc.) ',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'name' => 'No Domiciliado',
            'active' => true,
            'size' => null
        ]);

    }
}
