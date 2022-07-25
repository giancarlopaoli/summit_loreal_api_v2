<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bank::create([
            'name' => 'Test Bank',
            'shortname' => 'TEST',
            'corfid_id' => 1,
            'main' => true,
            'active' => true,
            'image' => 'https://cdn.pixabay.com/photo/2014/06/03/19/38/road-sign-361514_960_720.png'
        ]);
    }
}
