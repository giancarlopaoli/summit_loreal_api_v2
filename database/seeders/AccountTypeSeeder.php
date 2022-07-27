<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AccountType::create([
            'name' => 'Cuenta Corriente',
            'shortname' => 'Cta. Cte.',
            'size' => 20,
            'active' => true
        ]);

        AccountType::create([
            'name' => 'Cuenta de Ahorros',
            'shortname' => 'Ahorro',
            'size' => 20,
            'active' => true
        ]);
    }
}
