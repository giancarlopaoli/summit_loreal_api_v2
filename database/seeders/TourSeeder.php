<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tour;

class TourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Tour::truncate();

        Tour::create([
            'title_es' => 'MUSEO DE ARTE DE LIMA (MALI)',
            'title_en'    =>  'LIMA ART MUSEUM (MALI)',
            'description_es' => 'En 1954, se constituyó el Patronato de las Artes, una asociación civil que tenía como objetivo promover el arte y la cultura en el Perú, por lo que solicitaron al Consejo de Lima un lugar para realizar actividades que la incentivaran.<br>Es así como en 1956 fue cedido el Palacio de la Exposición construido en 1872. En 1959, se inaugura el Museo de Arte de Lima, abriendo sus puertas al público el 10 de marzo de 1961. En 1979, el Museo de Arte de Lima conocido por sus siglas como MALI, fue declarado Patrimonio Cultural de la Nación.<br><br><strong>QUE VER</strong><br><br> Expone arte prehispánico, virreinal, republicano y actual. Con más de 17.000 obras de arte peruano entre ellas salas de arte precolombino, colonial, además, de textil, platería, dibujo y fotografía. También cuenta con exposiciones de artistas nacionales e internacionales en salas temporales.<br>El MALI también ofrece acceso a sus colecciones de manera virtual. Por ejemplo, hallaremos, al menos, 3.077 fotografías de arte prehispánico; 1.880 obras de arte moderno y contemporáneo; entre otras. ¡Una base de datos impresionante! Les dejo el link: <br><br> https://coleccion.mali.pe/collections<br><br> Descubre la historia del Perú contada desde el periodo precolombino hasta el actual a través de la mirada de nuestros artistas. ¡Un lugar que no podemos dejar de conocer!',
            'description_en' => 'In 1954, the Board of the Arts was established, a civil association aimed at promoting art and culture in Peru. They requested the Lima Council for a place to carry out activities that would foster these goals.<br> This is how, in 1956, the Exposition Palace, built in 1872, was granted to them. In 1959, the Lima Art Museum was inaugurated, opening its doors to the public on March 10, 1961. In 1979, the Lima Art Museum, known by its acronym MALI, was declared a Cultural Heritage of the Nation.<br><br> <strong>WHAT TO SEE</strong><br><br> The MALI exhibits pre-Hispanic, colonial, republican, and contemporary art. With over 17,000 pieces of Peruvian art, it includes galleries dedicated to pre-Columbian and colonial art, as well as textiles, silverware, drawings, and photography. Additionally, it regularly hosts exhibitions featuring both national and international artists in its temporary galleries.<br><br>  The MALI also offers virtual access to its collections. For instance, you can find at least 3,077 photographs of pre-Hispanic art, 1,880 pieces of modern and contemporary art, and more. It´s an impressive database! Here´s the link: <br><br>https://coleccion.mali.pe/collections<br><br>  Discover the history of Peru told through the eyes of our artists, spanning from the pre-Columbian period to the present. It´s a place you definitely shouldn´t miss!'
        ]);
    }
}
