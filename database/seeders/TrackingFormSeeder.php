<?php

namespace Database\Seeders;

use App\Models\TrackingForm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrackingFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TrackingForm::create([
            'name' => 'Llamada'
        ]);

        TrackingForm::create([
            'name' => 'Mail'
        ]);

        TrackingForm::create([
            'name' => 'LinkedIn'
        ]);

        TrackingForm::create([
            'name' => 'WhatsApp'
        ]);

        TrackingForm::create([
            'name' => 'Facebook'
        ]);

        TrackingForm::create([
            'name' => 'Telegram'
        ]);

        TrackingForm::create([
            'name' => 'Reunion'
        ]);

    }
}
