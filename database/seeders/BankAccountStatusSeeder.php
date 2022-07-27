<?php

namespace Database\Seeders;

use App\Models\BankAccountStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankAccountStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BankAccountStatus::create([
            'name' => 'Activo'
        ]);

        BankAccountStatus::create([
            'name' => 'Inactivo'
        ]);

        BankAccountStatus::create([
            'name' => 'Pendiente'
        ]);

        BankAccountStatus::create([
            'name' => 'Rechazado'
        ]);
    }
}
