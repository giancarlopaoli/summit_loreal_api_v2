<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RecomendationCategory;
use App\Models\Recomendation;
use Illuminate\Support\Facades\DB;

class RecomendationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Recomendation::truncate();
        RecomendationCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $category = RecomendationCategory::create([
            'category_es' => 'Agencias de viajes',
            'category_en' => 'Travel agencies',
            'image' => ''
        ]);

        $category->recomendations()->create([
            'name' => 'AGENCIA NUEVO MUNDO',
            'address' => 'Av. José Pardo 801, Miraflores - Lima, Perú.',
            'phone' => '(511) 610- 9200',
            'web' => 'https://www.nmviajes.com/'
        ]);
        $category->recomendations()->create([
            'name' => 'TRAVEL GROUP PERÚ',
            'address' => 'Av. 28 de Julio 674, Miraflores - Lima, Perú.',
            'phone' => '(511) 6250- 4444',
            'web' => 'http://www.travelgroup.com.pe/'
        ]);
        $category->recomendations()->create([
            'name' => 'PERUVIAN TRAVEL SERVICE',
            'address' => 'Av.Pardo 610, Miraflores - Lima, Perú.',
            'phone' => '(511) 242-1741 | 241-7642',
            'web' => 'www.peruviantravelservice.com'
        ]);



        $category = RecomendationCategory::create([
            'category_es' => 'Casas de cambio',
            'category_en' => 'Exchange Offices',
            'image' => ''
        ]);

        $category->recomendations()->create([
            'name' => 'JET PERÚ',
            'address' => 'Camino Real 395, San Isidro - Lima, Perú.',
            'phone' => '(511) 611-5050',
            'web' => 'http://www.jetperu.com.pe/'
        ]);
        $category->recomendations()->create([
            'name' => 'EXPRESS MONEY EXCHANGE',
            'address' => 'Camino Real 492, San Isidro - Lima, Perú.',
            'phone' => '(511) 441-7114',
            'web' => ''
        ]);
        $category->recomendations()->create([
            'name' => 'CAPITAL EXCHANGE',
            'address' => 'Av. Canaval y Moreyra 233, San Isidro - Lima, Perú.',
            'phone' => '(511) 221-6683',
            'web' => ''
        ]);



        $category = RecomendationCategory::create([
            'category_es' => 'Restaurantes',
            'category_en' => 'Restaurants',
            'image' => ''
        ]);

        $category->recomendations()->create([
            'name' => 'CENTRAL RESTAURANTE',
            'address' => 'Av. Pedro de Osma 301, Barranco, Lima, Perú.',
            'phone' => '(511) 242-8515',
            'web' => 'https://centralrestaurante.com.pe/'
        ]);
        $category->recomendations()->create([
            'name' => 'MAIDO',
            'address' => 'Calle San Martín 399, Miraflores - Lima, Perú.',
            'phone' => '(511) 313-5100',
            'web' => 'https://www.maido.pe/'
        ]);
        $category->recomendations()->create([
            'name' => 'MARAS',
            'address' => 'Amador Merino Reyna 589, San Isidro - Lima, Perú.',
            'phone' => '(51) 962 382 609',
            'web' => 'http://www.marasrestaurante.com.pe/'
        ]);
        $category->recomendations()->create([
            'name' => 'MAYTA',
            'address' => 'Av. La Mar 1285, Miraflores - Lima, Perú.',
            'phone' => '(51) 937 220 734',
            'web' => 'https://www.maytalima.com/'
        ]);
        $category->recomendations()->create([
            'name' => 'ASTRID &amp; GASTÓN',
            'address' => 'Av. Paz Soldán 290, San Isidro - Lima, Perú.',
            'phone' => '(511) 442-2777',
            'web' => 'https://www.astridygaston.com/'
        ]);
        $category->recomendations()->create([
            'name' => 'COSME',
            'address' => 'Av. Tudela y Varela 162, San Isidro - Lima, Perú.',
            'phone' => '(511) 421-5228',
            'web' => 'https://cosme.com.pe/'
        ]);
        $category->recomendations()->create([
            'name' => 'OSAKA',
            'address' => 'Av. Felipe Pardo y Aliaga 660, San Isidro - Lima, Perú.',
            'phone' => '(511) 222-0405 / (51) 958 798 721',
            'web' => 'https://www.osakanikkei.com/'
        ]);



        $category = RecomendationCategory::create([
            'category_es' => 'Agencias de taxis',
            'category_en' => 'Taxi Agencies',
            'image' => ''
        ]);

        $category->recomendations()->create([
            'name' => 'TAXI SEGURO',
            'address' => '',
            'phone' => '(51) 924 101 670',
            'web' => 'https://taxisegurooficial.net.pe/'
        ]);
        $category->recomendations()->create([
            'name' => 'TAXI SATELITAL',
            'address' => '',
            'phone' => '(51) 986 173 880',
            'web' => 'https://satelitalexpressairport.com/'
        ]);
        $category->recomendations()->create([
            'name' => 'ALÓ TAXI',
            'address' => '',
            'phone' => '(511) 217-7777 | 644-7777',
            'web' => 'https://www.alotaxis.com/'
        ]);
    }
}
