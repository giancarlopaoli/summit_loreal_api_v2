<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Department::create([
            'name' => 'AMAZONAS'
        ]);

        Department::create([
            'name' => 'ANCASH'
        ]);

        Department::create([
            'name' => 'APURIMAC'
        ]);

        Department::create([
            'name' => 'AREQUIPA'
        ]);

        Department::create([
            'name' => 'AYACUCHO'
        ]);

        Department::create([
            'name' => 'CAJAMARCA'
        ]);

        Department::create([
            'name' => 'CALLAO'
        ]);

        Department::create([
            'name' => 'CUSCO'
        ]);

        Department::create([
            'name' => 'HUANCAVELICA'
        ]);

        Department::create([
            'name' => 'HUANUCO'
        ]);

        Department::create([
            'name' => 'ICA'
        ]);

        Department::create([
            'name' => 'JUNIN'
        ]);

        Department::create([
            'name' => 'LA LIBERTAD'
        ]);

        Department::create([
            'name' => 'LAMBAYEQUE'
        ]);

        Department::create([
            'name' => 'LIMA'
        ]);

        Department::create([
            'name' => 'LORETO'
        ]);

        Department::create([
            'name' => 'MADRE DE DIOS'
        ]);

        Department::create([
            'name' => 'MOQUEGUA'
        ]);

        Department::create([
            'name' => 'PASCO'
        ]);

        Department::create([
            'name' => 'PIURA'
        ]);

        Department::create([
            'name' => 'PUNO'
        ]);

        Department::create([
            'name' => 'SAN MARTIN'
        ]);

        Department::create([
            'name' => 'TACNA'
        ]);

        Department::create([
            'name' => 'TUMBES'
        ]);

        Department::create([
            'name' => 'UCAYALI'
        ]);

    }
}
