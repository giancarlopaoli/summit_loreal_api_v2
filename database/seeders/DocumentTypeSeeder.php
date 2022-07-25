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
            'name' => 'DNI',
            'active' => true,
            'size' => 8
        ]);

        DocumentType::create([
            'name' => 'RUC',
            'active' => true,
            'size' => 15
        ]);
    }
}
