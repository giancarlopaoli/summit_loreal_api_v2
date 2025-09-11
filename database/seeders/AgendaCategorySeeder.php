<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Agenda;
use App\Models\AgendaCategory;
use App\Models\AgendaSpeaker;
use Illuminate\Support\Facades\DB;

class AgendaCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AgendaSpeaker::truncate();
        Agenda::truncate();
        AgendaCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $category = AgendaCategory::create([
            'name_es' => '',
            'name_en' => '',
            'start_date' => '2023-09-22'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 08:30:00',
            'subject_pen' => 'Registro',
            'subject_usd' => 'Register'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 09:00:00',
            'subject_pen' => 'Bienvenida',
            'subject_usd' => 'Welcome'
        ]);

        $agenda->speakers()->create([
            'name' => 'Elisa Rozalén (España)',
            'specialty_pen' => 'Gerente Médica & Entrenamiento L´Oréal CERAN',
            'specialty_usd' => 'Medical Manager & Training L´Oréal CERAN'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 09:05:00',
            'subject_pen' => 'Presentación L´Oréal Dermatological Beauty',
            'subject_usd' => 'L´Oréal Dermatological Beauty Presentation'
        ]);

        $agenda->speakers()->create([
            'name' => 'Melanie Cooper (Perú)',
            'specialty_pen' => 'Directora General L´Oréal Dermatological Beauty CERAN',
            'specialty_usd' => 'General Director, L´Oréal Dermatological Beauty CERAN'
        ]);


        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 09:10:00',
            'subject_pen' => 'Presentación L´Oréal Groupe',
            'subject_usd' => 'L´Oréal Group Presentation'
        ]);

        $agenda->speakers()->create([
            'name' => 'Alberto Mario Rincón (Colombia)',
            'specialty_pen' => 'Director General L´Oréal CERAN',
            'specialty_usd' => 'General Director, L´Oréal CERAN'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 09:25:00',
            'subject_pen' => 'Bienvenida',
            'subject_usd' => 'Welcome'
        ]);

        $agenda->speakers()->create([
            'name' => 'Dra. Adriana Cruz - Chair (Colombia)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);


        ###########################################

        $category = AgendaCategory::create([
            'name_es' => 'Dermatología Integrativa',
            'name_en' => 'Integrative Dermatology',
            'start_date' => '2023-09-22'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 09:30:00',
            'subject_pen' => 'El exposoma revelado',
            'subject_usd' => 'The exposome unveiled'
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. Jerry Tan (Canadá)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 09:50:00',
            'subject_pen' => 'Comprendiendo el papel del estrés oxidativo en el envejecimiento cutáneo',
            'subject_usd' => 'Understanding the role of oxidative stress in skin aging'
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. Edwin Bendek (Colombia)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 10:10:00',
            'subject_pen' => "Los AGE y la Piel",
            'subject_usd' => "AGE's and the Skin"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. Apple Bodemer (EEUU)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 10:30:00',
            'subject_pen' => "Preguntas y Respuestas",
            'subject_usd' => "Q&A"
        ]); 

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 10:40:00',
            'subject_pen' => "Receso",
            'subject_usd' => "Break"
        ]); 

        ##############################

        $category = AgendaCategory::create([
            'name_es' => 'Hiperpigmentación & Fotoprotección',
            'name_en' => 'Hyperpigmentation & Photoprotection',
            'start_date' => '2023-09-22'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 11:10:00',
            'subject_pen' => "Descifrando el misterio de la hiperpigmentación: Desde las ciencias básicas",
            'subject_usd' => "Unraveling the mystery of hyperpigmentation: From basic sciences"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. Ana Espósito (Brasil)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 11:30:00',
            'subject_pen' => "Descifrando el misterio de la hiperpigmentación: Desde la práctica clínica",
            'subject_usd' => "Unraveling the mystery of hyperpigmentation: From clinical practice"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. César González (Colombia)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);


        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 11:50:00',
            'subject_pen' => "Fotoprotección: Más allá del FPS",
            'subject_usd' => "Photoprotection: Beyond SPF"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. Sergio Schalka (Brasil)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 12:20:00',
            'subject_pen' => "Preguntas y Respuestas",
            'subject_usd' => "Q&A"
        ]); 

        #################################
        $category = AgendaCategory::create([
            'name_es' => '',
            'name_en' => '',
            'start_date' => '2023-09-22'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 12:30:00',
            'subject_pen' => "Almuerzo",
            'subject_usd' => "Lunch"
        ]); 

        #################################

        $category = AgendaCategory::create([
            'name_es' => 'Tricología',
            'name_en' => 'Trichology',
            'start_date' => '2023-09-22'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 14:00:00',
            'subject_pen' => "Microbioma y Dermatitis Seborréica",
            'subject_usd' => "Microbiome and Seborrheic Dermatitis"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dra. Claudia Montoya (Colombia)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 14:20:00',
            'subject_pen' => "Novedades terapéuticas en alopecias no cicatriciales",
            'subject_usd' => "Therapeutic Innovations in Non-Scarring Alopecias"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. Miguel Marti (Argentina)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 14:40:00',
            'subject_pen' => "¿Cómo lo tratarías tú?",
            'subject_usd' => "How would you treat it?"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dra. Claudia Montoya',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda->speakers()->create([
            'name' => 'Dr. Miguel Marti (Argentina)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 15:00:00',
            'subject_pen' => "Preguntas y Respuestas",
            'subject_usd' => "Q&A"
        ]); 

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 15:10:00',
            'subject_pen' => "Receso",
            'subject_usd' => "Break"
        ]); 

        #################################

        $category = AgendaCategory::create([
            'name_es' => 'Digital para no Digitales',
            'name_en' => 'Digital for non-digital',
            'start_date' => '2023-09-22'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 15:40:00',
            'subject_pen' => "Cómo crear contenido en Tik Tok",
            'subject_usd' => "How to create content on Tik Tok"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Deni Daza (Venezuela)',
            'specialty_pen' => 'Creativa Estratégica en Tik Tok Colombia, Ecuador & Perú',
            'specialty_usd' => 'Strategic Creative at TikTok Colombia, Ecuador & Peru'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 16:10:00',
            'subject_pen' => "Influ-cienciers: La importancia de los dermatólogos en la era de la infoxicación online",
            'subject_usd' => "Influ-cienciers: The importance of Dermatologists in the Age of Online Infoxication"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dra. Ana Molina (España)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 16:30:00',
            'subject_pen' => "Ciencia e innovación avanzada al servicio de la dermocosmética",
            'subject_usd' => "Science and Advanced Innovation at the Service of Dermocosmetics"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Fabian Flores (México)',
            'specialty_pen' => 'Director Científico L´Oréal Zona América Latina',
            'specialty_usd' => 'Scientific Director, L´Oréal Latin America Region'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 16:50:00',
            'subject_pen' => "Consciencia en el entorno clínico",
            'subject_usd' => "Mindfulness in the clinical setting"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. Apple Bodemer (EEUU)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        #################################
        $category = AgendaCategory::create([
            'name_es' => '',
            'name_en' => '',
            'start_date' => '2023-09-22'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 17:20:00',
            'subject_pen' => "Preguntas y Respuestas",
            'subject_usd' => "Q&A"
        ]); 

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-22 17:30:00',
            'subject_pen' => "Cierre Primer día ",
            'subject_usd' => "First day closing "
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dra. Adriana Cruz - Chair (Colombia)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);


        ######### SABADO ################
        #################################
        $category = AgendaCategory::create([
            'name_es' => '',
            'name_en' => '',
            'start_date' => '2023-09-23'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 08:30:00',
            'subject_pen' => 'Registro',
            'subject_usd' => 'Register'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 09:00:00',
            'subject_pen' => 'Bienvenida',
            'subject_usd' => 'Welcome'
        ]);

        $agenda->speakers()->create([
            'name' => 'Elisa Rozalén (España)',
            'specialty_pen' => 'Gerente Médica & Entrenamiento L´Oréal CERAN',
            'specialty_usd' => 'Medical Manager & Training, L´Oréal CERAN'
        ]);

        #################################
        $category = AgendaCategory::create([
            'name_es' => 'Dermatitis Atópica & Acné',
            'name_en' => 'Atopic Dermatitis  & Acne',
            'start_date' => '2023-09-23'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 09:10:00',
            'subject_pen' => "¿Piel fina y sensible?",
            'subject_usd' => "Thin skinned and sensitive?"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. Jerry Tan (Canadá))',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 09:30:00',
            'subject_pen' => "Dermatitis Atópica, Microbioma y Probióticos",
            'subject_usd' => "Atopic Dermatitis, Microbiome and Probiotics"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dra. Rosalía Ballona (Perú)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 09:50:00',
            'subject_pen' => "Acné: La revolución del microbioma",
            'subject_usd' => "Acne: The Microbiome Revolution"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dra. Mónica Noguera (Argentina)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 10:10:00',
            'subject_pen' => "Microbioma",
            'subject_usd' => "Red microbiome"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dr. Jerry Tan (Canadá))',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 10:30:00',
            'subject_pen' => "Preguntas y Respuestas",
            'subject_usd' => "Q&A"
        ]); 

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 10:40:00',
            'subject_pen' => "Receso",
            'subject_usd' => "Break"
        ]); 


        #################################
        $category = AgendaCategory::create([
            'name_es' => 'Dermatología del hoy hacia el mañana',
            'name_en' => 'Dermatology from Today to Tomorrow',
            'start_date' => '2023-09-23'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 11:10:00',
            'subject_pen' => "Cómo dar vida a nuestras innovaciones para nuestros pacientes en todo el mundo",
            'subject_usd' => "How to bring our innovations alive for our patients all around the world"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Frédérique Labatut (Francia)',
            'specialty_pen' => 'Directora Laboratorios La Roche-Posay Skincare',
            'specialty_usd' => 'Director of Laboratories, La Roche-Posay Skincare'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 11:30:00',
            'subject_pen' => "Preguntas y Respuestas",
            'subject_usd' => "Q&A"
        ]); 

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 11:40:00',
            'subject_pen' => "Pixel Perfect",
            'subject_usd' => "Pixel Perfect"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Keith Loo (Canadá)',
            'specialty_pen' => 'CEO en Skinopathy (AI)',
            'specialty_usd' => 'CEO at Skinopathy (AI)'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 12:00:00',
            'subject_pen' => "De las erupciones a la luminosidad: Cómo la IA está revolucionando la dermatología",
            'subject_usd' => "From Rashes to Radiance: How AI is Revolutionizing Dermatology"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Keith Loo (Canadá)',
            'specialty_pen' => 'CEO en Skinopathy (AI)',
            'specialty_usd' => 'CEO at Skinopathy (AI)'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 12:20:00',
            'subject_pen' => "IA: ¡Vamos!",
            'subject_usd' => "AI: LET'S GO!"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Keith Loo (Canadá)',
            'specialty_pen' => 'CEO en Skinopathy (AI)',
            'specialty_usd' => 'CEO en Skinopathy (AI)'
        ]);

        $agenda = $category->agenda()->create([
            'start_date' => '2023-09-23 13:20:00',
            'subject_pen' => "Cierre Congreso",
            'subject_usd' => "Conference Closing"
        ]); 

        $agenda->speakers()->create([
            'name' => 'Dra. Adriana Cruz - Chair (Colombia)',
            'specialty_pen' => 'Médico Dermatólogo',
            'specialty_usd' => 'Dermatologist'
        ]);
    }
}
