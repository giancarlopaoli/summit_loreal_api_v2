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
            'id' => 1,
            'name' => 'RUC',
            'active' => true,
            'size' => 11
        ]);

        DocumentType::create([
            'id' => 2,
            'name' => 'DNI',
            'active' => true,
            'size' => 8
        ]);

        DocumentType::create([
            'id' => 3,
            'name' => 'Carné de extranjería',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'id' => 4,
            'name' => 'Carné de identidad de las Fuerzas Policiales',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'id' => 8,
            'name' => 'Carné de identidad de las Fuerzas Armadas',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'id' => 9,
            'name' => 'Pasaporte',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'id' => 10,
            'name' => 'Otros (Carta de Identidad, Cedula de identidad, Partida de Nacimiento, etc.) ',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'id' => 11,
            'name' => 'No Domiciliado',
            'active' => true,
            'size' => null
        ]);

        DocumentType::create([
            'id' => 12,
            'name' => 'Ficticio',
            'active' => true,
            'size' => null
        ]);

    }
}
