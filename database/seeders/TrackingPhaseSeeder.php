<?php

namespace Database\Seeders;

use App\Models\TrackingPhase;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrackingPhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        TrackingPhase::create([
            'name' => 'Primer seguimiento',
            'min_days' => 0,
            'max_days' => 3
        ]);

        TrackingPhase::create([
            'name' => 'Segundo seguimiento',
            'min_days' => 3,
            'max_days' => 7
        ]);

        TrackingPhase::create([
            'name' => 'Tercer seguimiento',
            'min_days' => 7,
            'max_days' => 21
        ]);

        TrackingPhase::create([
            'name' => 'Cuarto seguimiento',
            'min_days' => 21,
            'max_days' => null
        ]);
    }
}
