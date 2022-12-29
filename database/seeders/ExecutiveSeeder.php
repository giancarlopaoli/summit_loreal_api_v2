<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Executive;

class ExecutiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Executive::create([
            'id' => 1,
            'type'=> 'Tiempo Completo',
            'comission' => 0.15
        ]);

        Executive::create([
            'id' => 2,
            'type'=> 'Freelance',
            'comission' => 0.25,
            'years' => 3
        ]);

        Executive::create([
            'id' => 3,
            'type'=> 'Tiempo Completo',
            'comission' => 0
        ]);

        Executive::create([
            'id' => 7,
            'type'=> 'Tiempo Completo',
            'comission' => 0.15
        ]);

        Executive::create([
            'id' => 8,
            'type'=> 'Tiempo Completo',
            'comission' => 0.15
        ]);
    }
}
