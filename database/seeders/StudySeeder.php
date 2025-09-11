<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Study;

class StudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Study::truncate();

        Study::create([
            'name' => 'CERAVÉ',
            'url' =>  'https://drive.google.com/file/d/10q51YxxpRFJ13OlpsIStn6hY35iFhLEK/view?usp=drive_link'
        ]);

        Study::create([
            'name' => 'LA ROCHE POSAY',
            'url' =>  'https://drive.google.com/file/d/15n6QIHfbbP_lp3KNiqYHNYUnw2MAFfsg/view?usp=drive_link'
        ]);

        Study::create([
            'name' => 'SKINCEUTICALS',
            'url' =>  'https://drive.google.com/drive/folders/1zoGT0SNdB30pWTiBQ9nCBmuc1fh-_wR0?usp=drive_link'
        ]);

        Study::create([
            'name' => 'VICHY',
            'url' =>  'https://drive.google.com/file/d/1MW_py4PD8xtNAc9MdBHqMjG6ydRXWBD3/view?usp=drive_link'
        ]);
    }
}
