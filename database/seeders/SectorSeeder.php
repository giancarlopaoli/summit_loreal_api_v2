<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Sector::create([
            'name' => 'Agropecuario'
        ]);

        Sector::create([
            'name' => 'Pesca'
        ]);

        Sector::create([
            'name' => 'Minería'
        ]);

        Sector::create([
            'name' => 'Hidrocarburos'
        ]);

        Sector::create([
            'name' => 'Manufactura'
        ]);

        Sector::create([
            'name' => 'Electricidad, Gas y Agua'
        ]);

        Sector::create([
            'name' => 'Construcción'
        ]);

        Sector::create([
            'name' => 'Comercio'
        ]);

        Sector::create([
            'name' => 'Transporte, almacenamiento, correo y mensajería'
        ]);

        Sector::create([
            'name' => 'Telecomunicaciones y otros servicios de información'
        ]);

        Sector::create([
            'name' => 'Turismo'
        ]);

        Sector::create([
            'name' => 'Servicios Empresariales'
        ]);

        Sector::create([
            'name' => 'Otro'
        ]);
    }
}
