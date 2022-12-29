<?php

namespace Database\Seeders;

use App\Models\LeadContactType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeadContactTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        LeadContactType::create([
            'name' => 'Web'
        ]);
        
        LeadContactType::create([
            'name' => 'Linkedin'
        ]);
                
        LeadContactType::create([
            'name' => 'Google Ads'
        ]);
                
        LeadContactType::create([
            'name' => 'Facebook'
        ]);
                
        LeadContactType::create([
            'name' => 'Base de Datos'
        ]);
                
        LeadContactType::create([
            'name' => 'Referidos'
        ]);
                
        LeadContactType::create([
            'name' => 'Base BCP'
        ]);
    }
}
