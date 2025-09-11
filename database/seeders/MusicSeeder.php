<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Music;
use App\Models\MusicVote;
use Illuminate\Support\Facades\DB;

class MusicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Music::truncate();
        MusicVote::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Music::create([
            'song_name' => 'Tienes la magia - Silvio y el vega',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Amor de mis amores - Versión Alberto barros',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Mentirosa - Ráfaga',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Felices los 4 - Versión Orquesta Karibe',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'El ritmo de mi corazón - Grupo 5',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Las cajas - Joe arroyo',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Ya te olvide - Vernis Hernández',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'La rebelión - Joe arroyo',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'La dueña del swing - Hnos. rosario',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Abusadora - Wilfrido Vargas',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Juana la cubana - Chicas del can',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Isla para dos - Nano cabrera',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Ráfaga de amor - Ráfaga',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Desde esa noche - Versión Orquesta Karibe',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Bomba - Vico C',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'El tao tao - Grupo 5',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Mayonesa - Chocolate 2000',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Pollera colora - Alberto Barros',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Cali pachanguero - Niche',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Tú de que vas - Los 4 de Cuba',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Cuéntame - Charanga Habanera',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Talento de TV - Willy colon',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Bomba para fincar - Vico C',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Mal bicho - Fabulosos Cadillacs',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Ella me levanto - Daddy Yankee',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Pa’ que me invitan - Los 5 de oro',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Báilame (Versión Cumbia) - GLM Super Kumbia',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Yo quiero chupar - Super Lamas',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Punto y aparte - Tego Calderon',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Equis - Nicky Jam x J Balvin',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Sin Pijama - Becky G, Natti Natasha',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Ven Báilalo - Angel & Khriz',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Cuéntale - Don Omar',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Agachadita - Mayonesa 2000',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Ay mi Dios - Pitbull',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Loquita - Márama',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'La bicicleta - Shakira',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Bronceada - Márama',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Mayores Becky G',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Dura - Daddy Janckee',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Mentirosa - Azul Azul',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'El boricua - El general',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Taqui taqui - Proyecto 1',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Ritmo de la noche - The sacados',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Esta pegao - Proyecto 1',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => '1, 2, 3 - El símbolo',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Carrapicho - Tic tic tac',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Macarena    - Los del Rio',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Meneito - Natusha',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Cachete pechito y ombligo - Sonora Colorada',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Sopa de caracol - Banda blanca',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'El tiburón - Proyecto 1',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'La guitarra - Auténticos decadentes',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Me enamore de ti y que - Grupo 5',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Que levante la mano - Grupo 5',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Te vas te vas - Grupo 5',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Alimaña - Grupo 5',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'Morir de amor - Grupo 5',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'El teléfono - Grupo 5',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'MIX GRUPO 5 – 1 (Me enamore de ti y que - Que levante la mano - Te vas te vas)',
            'status' =>  'Pendiente'
        ]);

        Music::create([
            'song_name' => 'MIX GRUPO 5 – 2 (Alimaña - Morir de amor - El teléfono)',
            'status' =>  'Pendiente'
        ]);
    }
}
