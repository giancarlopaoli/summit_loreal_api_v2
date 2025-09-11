<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TriviaQuestion;
use App\Models\TriviaOption;
use Illuminate\Support\Facades\DB;

class TriviaQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TriviaOption::truncate();
        TriviaQuestion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $question = TriviaQuestion::create([
            'question_es' => '¿Qué es el exposoma?',
            'question_en' => '¿Qué es el exposoma?',
            'subject_es' => 'Exposome',
            'subject_en' => 'Exposome',
            'speaker_id' => 15,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Es lo mismo que exosoma, sólo que mal escrito',
            'question_en' => 'Es lo mismo que exosoma, sólo que mal escrito',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'La totalidad de exposiciones del cuerpo humano ajenas a la genética',
            'question_en' => 'La totalidad de exposiciones del cuerpo humano ajenas a la genética',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Un ejemplo de exposoma es la trisomía 21',
            'question_en' => 'Un ejemplo de exposoma es la trisomía 21',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Es algo de EXPO, similar a un souvenir',
            'question_en' => 'Es algo de EXPO, similar a un souvenir',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Ninguna de las anteriores',
            'question_en' => 'Ninguna de las anteriores',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cómo escoges una marca de un dermocosmético? Escoger una o varias.',
            'question_en' => '¿Cómo escoges una marca de un dermocosmético? Escoger una o varias.',
            'subject_es' => 'Exposome',
            'subject_en' => 'Exposome',
            'speaker_id' => 15,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Recomendaciones de colegas',
            'question_en' => 'Recomendaciones de colegas',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Recomendaciones de las guías clínicas de manejo',
            'question_en' => 'Recomendaciones de las guías clínicas de manejo',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Recomendaciones de influencers',
            'question_en' => 'Recomendaciones de influencers',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Si son amigables con el ambiente y la ecología',
            'question_en' => 'Si son amigables con el ambiente y la ecología',
            'correct' => '1'
        ]);


        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => 'Son ejemplos de antioxidantes enzimáticos:',
            'question_en' => 'Son ejemplos de antioxidantes enzimáticos:',
            'subject_es' => 'Antioxidantes',
            'subject_en' => 'Antioxidantes',
            'speaker_id' => 2,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Glutatión peroxidasa',
            'question_en' => 'Glutatión peroxidasa',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Superóxido dismutasa',
            'question_en' => 'Superóxido dismutasa',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Catalasa',
            'question_en' => 'Catalasa',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Todas las anteriores son ciertas',
            'question_en' => 'Todas las anteriores son ciertas',
            'correct' => '1'
        ]);


        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => 'La técnica de preparación de alimentos que minimiza la formación de AGEs incluye:',
            'question_en' => 'La técnica de preparación de alimentos que minimiza la formación de AGEs incluye:',
            'subject_es' => 'Diet and AGEs',
            'subject_en' => 'Diet and AGEs',
            'speaker_id' => 3,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Asado a la parrilla (grilling)',
            'question_en' => 'Asado a la parrilla (grilling)',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Hervido (poaching)',
            'question_en' => 'Hervido (poaching)',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Asado-dorado al horno (broiling)',
            'question_en' => 'Asado-dorado al horno (broiling)',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Frito (frying)',
            'question_en' => 'Frito (frying)',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => 'Los pasos iniciales en la Reacción de Maillard son:',
            'question_en' => 'Los pasos iniciales en la Reacción de Maillard son:',
            'subject_es' => 'Diet and AGEs',
            'subject_en' => 'Diet and AGEs',
            'speaker_id' => 3,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Reversible y no depende de la concentración de azúcar disponible',
            'question_en' => 'Reversible y no depende de la concentración de azúcar disponible',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Irreversible y no depende de la concentración de azúcar disponible',
            'question_en' => 'Irreversible y no depende de la concentración de azúcar disponible',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Reversible y depende de la concentración de azúcar disponible',
            'question_en' => 'Reversible y depende de la concentración de azúcar disponible',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Irreversible y depende de la concentración de azúcar disponible',
            'question_en' => 'Irreversible y depende de la concentración de azúcar disponible',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => 'El melasma es:',
            'question_en' => 'El melasma es:',
            'subject_es' => 'Fisiopatología de melasma',
            'subject_en' => 'Fisiopatología de melasma',
            'speaker_id' => 5,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Una dermatosis con cambios exclusivos de los melanocitos.',
            'question_en' => 'Una dermatosis con cambios exclusivos de los melanocitos.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Una dermatosis con aumento de melanocitos.',
            'question_en' => 'Una dermatosis con aumento de melanocitos.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Resulta de un cambio en la comunicación entre queratinocitos y melanocitos.',
            'question_en' => 'Resulta de un cambio en la comunicación entre queratinocitos y melanocitos.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Una dermatosis compleja con cambios en la epidermis, el área de la membrana basal y la dermis.',
            'question_en' => 'Una dermatosis compleja con cambios en la epidermis, el área de la membrana basal y la dermis.',
            'correct' => '1'
        ]);


        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => 'Sobre el melasma:',
            'question_en' => 'Sobre el melasma:',
            'subject_es' => 'Fisiopatología de melasma',
            'subject_en' => 'Fisiopatología de melasma',
            'speaker_id' => 5,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Existe igual prevalencia entre hombres y mujeres.',
            'question_en' => 'Existe igual prevalencia entre hombres y mujeres.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Hay cambios exclusivamente en los receptores de estrógenos.',
            'question_en' => 'Hay cambios exclusivamente en los receptores de estrógenos.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Ha habido un aumento reciente en la incidencia del melasma, especialmente en los grandes centros urbanos.',
            'question_en' => 'Ha habido un aumento reciente en la incidencia del melasma, especialmente en los grandes centros urbanos.',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'No existe contribución de mediadores inflamatorios en su fisiopatología.',
            'question_en' => 'No existe contribución de mediadores inflamatorios en su fisiopatología.',
            'correct' => '0'
        ]);


        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuando te llega una paciente con melasma cual sería tu primer pensamiento?',
            'question_en' => '¿Cuando te llega una paciente con melasma cual sería tu primer pensamiento?',
            'subject_es' => 'Melasma',
            'subject_en' => 'Melasma',
            'speaker_id' => 7,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Lo remito con un enemigo.',
            'question_en' => 'Lo remito con un enemigo.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Le digo que eso no se cura y lo abrazo',
            'question_en' => 'Le digo que eso no se cura y lo abrazo',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Le receto la fórmula de Kligman',
            'question_en' => 'Le receto la fórmula de Kligman',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Le ofrezco 4 sesiones de laser de C02',
            'question_en' => 'Le ofrezco 4 sesiones de laser de C02',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Ninguna de la anteriore',
            'question_en' => 'Ninguna de la anteriore',
            'correct' => '1'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuál de los siguientes ha mostrado ser efectivo en el melasma?',
            'question_en' => '¿Cuál de los siguientes ha mostrado ser efectivo en el melasma?',
            'subject_es' => 'Melasma',
            'subject_en' => 'Melasma',
            'speaker_id' => 7,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Tranexámico',
            'question_en' => 'Tranexámico',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => ' Niacinamida',
            'question_en' => ' Niacinamida',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => ' Hidroquinona',
            'question_en' => ' Hidroquinona',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Metformina',
            'question_en' => 'Metformina',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Todos los anteriores',
            'question_en' => 'Todos los anteriores',
            'correct' => '1'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuál de estas frases no es correcta?',
            'question_en' => '¿Cuál de estas frases no es correcta?',
            'subject_es' => 'Fotoprotección',
            'subject_en' => 'Fotoprotección',
            'speaker_id' => 14,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'La radiación UVA prolongada es tres veces más efectiva que la radiación UVB para generar inmunosupresión',
            'question_en' => 'La radiación UVA prolongada es tres veces más efectiva que la radiación UVB para generar inmunosupresión',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'La luz visible es capaz de pigmentar la piel.',
            'question_en' => 'La luz visible es capaz de pigmentar la piel.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'El fotoenvejecimiento tiene como principal mecanismo etiopatogénico el estrés oxidativo resultante de la radiación solar.',
            'question_en' => 'El fotoenvejecimiento tiene como principal mecanismo etiopatogénico el estrés oxidativo resultante de la radiación solar.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'La radiación UVB es la principal fracción de la radiación solar responsable del estrés oxidativo.',
            'question_en' => 'La radiación UVB es la principal fracción de la radiación solar responsable del estrés oxidativo.',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'El daño oxidativo indirecto al ADN es uno de los mecanismos por los que actúa la radiación UVA',
            'question_en' => 'El daño oxidativo indirecto al ADN es uno de los mecanismos por los que actúa la radiación UVA',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿En qué condiciones clínicas es importante prescribir un protector solar con alta protección UVA a nuestro paciente?',
            'question_en' => '¿En qué condiciones clínicas es importante prescribir un protector solar con alta protección UVA a nuestro paciente?',
            'subject_es' => 'Fotoprotección',
            'subject_en' => 'Fotoprotección',
            'speaker_id' => 14,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Prevención del fotoenvejecimiento',
            'question_en' => 'Prevención del fotoenvejecimiento',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Prevención del melanoma',
            'question_en' => 'Prevención del melanoma',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Prevención y Tratamiento del Melasma',
            'question_en' => 'Prevención y Tratamiento del Melasma',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Prevención y tratamiento de la erupción luminosa polimorfa',
            'question_en' => 'Prevención y tratamiento de la erupción luminosa polimorfa',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Todas las alternativas anteriores son correctas',
            'question_en' => 'Todas las alternativas anteriores son correctas',
            'correct' => '1'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Qué cambios en la microbiota podemos encontrar en la Dermatitis Seborreica?',
            'question_en' => '¿Qué cambios en la microbiota podemos encontrar en la Dermatitis Seborreica?',
            'subject_es' => 'Dermatitis Seborreica',
            'subject_en' => 'Dermatitis Seborreica',
            'speaker_id' => 12,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Aumento en Staphylococcus aureus',
            'question_en' => 'Aumento en Staphylococcus aureus',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Cambio de proporción entre Malassezia restricta y globosa',
            'question_en' => 'Cambio de proporción entre Malassezia restricta y globosa',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Aumento del Cutibacterium acnés',
            'question_en' => 'Aumento del Cutibacterium acnés',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'A y B',
            'question_en' => 'A y B',
            'correct' => '1'
        ]);


        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => 'Dentro de la fisiopatología de la dermatitis seborreica que podemos decir que hay:',
            'question_en' => 'Dentro de la fisiopatología de la dermatitis seborreica que podemos decir que hay:',
            'subject_es' => 'Dermatitis Seborreica',
            'subject_en' => 'Dermatitis Seborreica',
            'speaker_id' => 12,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Susceptibilidad genétic',
            'question_en' => 'Susceptibilidad genétic',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Disbiosis',
            'question_en' => 'Disbiosis',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Disbalance de glándula sebácea',
            'question_en' => 'Disbalance de glándula sebácea',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Respuesta inmune alterada',
            'question_en' => 'Respuesta inmune alterada',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Todas las anteriores',
            'question_en' => 'Todas las anteriores',
            'correct' => '1'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuál sería el orden a la hora de seleccionar un tratamiento en la FAGA?  ¿Prioridades?',
            'question_en' => '¿Cuál sería el orden a la hora de seleccionar un tratamiento en la FAGA?  ¿Prioridades?',
            'subject_es' => 'Alopecia',
            'subject_en' => 'Alopecia',
            'speaker_id' => 10,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Minoxidil – anti-andrógeno',
            'question_en' => 'Minoxidil – anti-andrógeno',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Minoxidil - espironolactona',
            'question_en' => 'Minoxidil - espironolactona',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Minoxidil y PRP',
            'question_en' => 'Minoxidil y PRP',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Dutasteride y trasplante capilar',
            'question_en' => 'Dutasteride y trasplante capilar',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => 'Al seleccionar un anticonceptivo oral para la FAGA, el ideal sería :',
            'question_en' => 'Al seleccionar un anticonceptivo oral para la FAGA, el ideal sería :',
            'subject_es' => 'Alopecia',
            'subject_en' => 'Alopecia',
            'speaker_id' => 10,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Etinilestradiol ',
            'question_en' => 'Etinilestradiol ',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Ciproterona y etilestradiol',
            'question_en' => 'Ciproterona y etilestradiol',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Dinogest',
            'question_en' => 'Dinogest',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Drosperinona',
            'question_en' => 'Drosperinona',
            'correct' => '1'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Qué se considera como regla nativa de Tiktok?',
            'question_en' => '¿Qué se considera como regla nativa de Tiktok?',
            'subject_es' => 'TIK TOK',
            'subject_en' => 'TIK TOK',
            'speaker_id' => 4,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Creadores',
            'question_en' => 'Creadores',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Emojis',
            'question_en' => 'Emojis',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Asmr',
            'question_en' => 'Asmr',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'El sonido',
            'question_en' => 'El sonido',
            'correct' => '1'
        ]);


        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuáles son las voces con las que se identifica nuestra comunidad?',
            'question_en' => '¿Cuáles son las voces con las que se identifica nuestra comunidad?',
            'subject_es' => 'TIK TOK',
            'subject_en' => 'TIK TOK',
            'speaker_id' => 4,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Relatable + Aspiracional + informativa + Inspiracional.',
            'question_en' => 'Relatable + Aspiracional + informativa + Inspiracional.',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => ' Relatable + graciosa + viral + inspiracional.',
            'question_en' => ' Relatable + graciosa + viral + inspiracional.',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuál de los siguientes no es un principio de influencia de Cialdini?',
            'question_en' => '¿Cuál de los siguientes no es un principio de influencia de Cialdini?',
            'subject_es' => 'Social Media Influencers',
            'subject_en' => 'Social Media Influencers',
            'speaker_id' => 11,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Principio de autoridad.',
            'question_en' => 'Principio de autoridad.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Principio de escasez.',
            'question_en' => 'Principio de escasez.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Principio de persuasión.',
            'question_en' => 'Principio de persuasión.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Principio de reciprocidad.',
            'question_en' => 'Principio de reciprocidad.',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuál es el porcentaje de cuentas que publican contenido sobre cuidado de la piel online cuyo responsable es un Dermatólogo?',
            'question_en' => '¿Cuál es el porcentaje de cuentas que publican contenido sobre cuidado de la piel online cuyo responsable es un Dermatólogo?',
            'subject_es' => 'Social Media Influencers',
            'subject_en' => 'Social Media Influencers',
            'speaker_id' => 11,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '4%',
            'question_en' => '4%',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '10%',
            'question_en' => '10%',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '12%',
            'question_en' => '12%',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '20%',
            'question_en' => '20%',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Qué es la Medicina Narrativa?',
            'question_en' => '¿Qué es la Medicina Narrativa?',
            'subject_es' => 'Social Media Influencers',
            'subject_en' => 'Social Media Influencers',
            'speaker_id' => 11,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Una rama de la medicina dedicada a acercar la evidencia científica a la población general.',
            'question_en' => 'Una rama de la medicina dedicada a acercar la evidencia científica a la población general.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Una forma de ayudar a los pacientes a través del conocimiento científico.',
            'question_en' => 'Una forma de ayudar a los pacientes a través del conocimiento científico.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Una rama de la medicina especializada en la divulgación de la salud',
            'question_en' => 'Una rama de la medicina especializada en la divulgación de la salud',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Todas las anteriores son ciertas',
            'question_en' => 'Todas las anteriores son ciertas',
            'correct' => '1'
        ]);


        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿En qué consiste el Skill-Stacking?',
            'question_en' => '¿En qué consiste el Skill-Stacking?',
            'subject_es' => 'Social Media Influencers',
            'subject_en' => 'Social Media Influencers',
            'speaker_id' => 11,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Es un sinónimo de Soft-skill.',
            'question_en' => 'Es un sinónimo de Soft-skill.',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Es una forma de llamar a la geometría del talento',
            'question_en' => 'Es una forma de llamar a la geometría del talento',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Formarse en disciplinas o áreas de conocimiento diferentes a la habitual.',
            'question_en' => 'Formarse en disciplinas o áreas de conocimiento diferentes a la habitual.',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Formarse en varias especialidades médicas diferentes.',
            'question_en' => 'Formarse en varias especialidades médicas diferentes.',
            'correct' => '0'
        ]);


        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuántos científicos y de cuántas nacionales trabajan en L’Oréal?',
            'question_en' => '¿Cuántos científicos y de cuántas nacionales trabajan en L’Oréal?',
            'subject_es' => 'Ciencia e Innovación',
            'subject_en' => 'Ciencia e Innovación',
            'speaker_id' => 6,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '2500 científicos, 40 nacionalidades',
            'question_en' => '2500 científicos, 40 nacionalidades',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '3800 científicos, 60 nacionalidades',
            'question_en' => '3800 científicos, 60 nacionalidades',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '4000 científicos, 85 nacionalidades',
            'question_en' => '4000 científicos, 85 nacionalidades',
            'correct' => '1'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuántos millones de euros invierte L’Oréal anualmente en R&I?',
            'question_en' => '¿Cuántos millones de euros invierte L’Oréal anualmente en R&I?',
            'subject_es' => 'Ciencia e Innovación',
            'subject_en' => 'Ciencia e Innovación',
            'speaker_id' => 6,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Más de 100 millones de euros',
            'question_en' => 'Más de 100 millones de euros',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Más de 150 millones de euros',
            'question_en' => 'Más de 150 millones de euros',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Más de 900 millones de euros',
            'question_en' => 'Más de 900 millones de euros',
            'correct' => '1'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuál fue la primera planta 100% neutra en carbono del Grupo L’Oréal?',
            'question_en' => '¿Cuál fue la primera planta 100% neutra en carbono del Grupo L’Oréal?',
            'subject_es' => 'Ciencia e Innovación',
            'subject_en' => 'Ciencia e Innovación',
            'speaker_id' => 6,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Planta de Burgos, España',
            'question_en' => 'Planta de Burgos, España',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Planta de Alcalá de Henares, España',
            'question_en' => 'Planta de Alcalá de Henares, España',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Planta de Aulnay, Francia',
            'question_en' => 'Planta de Aulnay, Francia',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿Cuál es el nombre del modelo de piel reconstruida del Grupo L’Oréal?',
            'question_en' => '¿Cuál es el nombre del modelo de piel reconstruida del Grupo L’Oréal?',
            'subject_es' => 'Ciencia e Innovación',
            'subject_en' => 'Ciencia e Innovación',
            'speaker_id' => 6,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Ereskin',
            'question_en' => 'Ereskin',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Episkin',
            'question_en' => 'Episkin',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Evoskin',
            'question_en' => 'Evoskin',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => '¿En qué año L’Oréal introdujo los métodos alternativos para el testeo de la seguridad?',
            'question_en' => '¿En qué año L’Oréal introdujo los métodos alternativos para el testeo de la seguridad?',
            'subject_es' => 'Ciencia e Innovación',
            'subject_en' => 'Ciencia e Innovación',
            'speaker_id' => 6,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '1979',
            'question_en' => '1979',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '1985',
            'question_en' => '1985',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => '1987',
            'question_en' => '1987',
            'correct' => '0'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => 'Los beneficios del Mindfulness incluyen:',
            'question_en' => 'Los beneficios del Mindfulness incluyen:',
            'subject_es' => 'Mindfulness in the Clinical Settin',
            'subject_en' => 'Mindfulness in the Clinical Settin',
            'speaker_id' => 3,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Disminución de estrés y ansiedad',
            'question_en' => 'Disminución de estrés y ansiedad',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Mejoría del estado de ánimo',
            'question_en' => 'Mejoría del estado de ánimo',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Incremento de habilidades cognitivas',
            'question_en' => 'Incremento de habilidades cognitivas',
            'correct' => '0'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Todas las anteriores',
            'question_en' => 'Todas las anteriores',
            'correct' => '1'
        ]);

        ########################################################################################
        $question = TriviaQuestion::create([
            'question_es' => 'El rezar u orar es una forma de meditación',
            'question_en' => 'El rezar u orar es una forma de meditación',
            'subject_es' => 'Mindfulness in the Clinical Settin',
            'subject_en' => 'Mindfulness in the Clinical Settin',
            'speaker_id' => 3,
            'status' => 'Pendiente'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Verdadero',
            'question_en' => 'Verdadero',
            'correct' => '1'
        ]);

        $option = $question->trivia_options()->create([
            'question_es' => 'Falso',
            'question_en' => 'Falso',
            'correct' => '0'
        ]);



########################################################################################

    $question = TriviaQuestion::create([
        'question_es' => '¿Qué es piel sensible?',
        'question_en' => '¿Qué es piel sensible?',
        'subject_es' => 'Sensitive Skin',
        'subject_en' => 'Sensitive Skin',
        'speaker_id' => 15,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'La piel de las personas bravas',
        'question_en' => 'La piel de las personas bravas',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Piel con disestesia sin una dermatosis subyacente',
        'question_en' => 'Piel con disestesia sin una dermatosis subyacente',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Piel que ha sufrido quemadura solar',
        'question_en' => 'Piel que ha sufrido quemadura solar',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Piel con síntomas de intoxicación por pescado en mal estado',
        'question_en' => 'Piel con síntomas de intoxicación por pescado en mal estado',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Resultado de usar mucha fragancia',
        'question_en' => 'Resultado de usar mucha fragancia',
        'correct' => '0'
    ]);



    $question = TriviaQuestion::create([
        'question_es' => '¿Cómo tratar la piel sensible? Escoger una o varias',
        'question_en' => '¿Cómo tratar la piel sensible? Escoger una o varias',
        'subject_es' => 'Sensitive Skin',
        'subject_en' => 'Sensitive Skin',
        'speaker_id' => 15,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Láser fraccionado',
        'question_en' => 'Láser fraccionado',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Inyecciones de ácido hialurónico',
        'question_en' => 'Inyecciones de ácido hialurónico',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Limpiadores suaves, hidratantes',
        'question_en' => 'Limpiadores suaves, hidratantes',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'PRP',
        'question_en' => 'PRP',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Fracciones probióticas',
        'question_en' => 'Fracciones probióticas',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Baba de caracol',
        'question_en' => 'Baba de caracol',
        'correct' => '1'
    ]);



########################################################################################

    $question = TriviaQuestion::create([
        'question_es' => 'La Dermatitis atópica es una dermatosis predominantemente infantil, inflamatoria, crónica. Marque lo falso:',
        'question_en' => 'La Dermatitis atópica es una dermatosis predominantemente infantil, inflamatoria, crónica. Marque lo falso:',
        'subject_es' => '',
        'subject_en' => '',
        'speaker_id' => 1,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'El inicio temprano se asocia a cronicidad y severidad del cuadro',
        'question_en' => 'El inicio temprano se asocia a cronicidad y severidad del cuadro',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Es la iniciadora de la marcha atópica en la mayoría de los casos',
        'question_en' => 'Es la iniciadora de la marcha atópica en la mayoría de los casos',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Los hallazgos clínicos son de ayuda diagnóstica',
        'question_en' => 'Los hallazgos clínicos son de ayuda diagnóstica',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'La predisposición genética no es factor de riesgo. ',
        'question_en' => 'La predisposición genética no es factor de riesgo. ',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Considerada como Neurodermatitis en los adolescentes',
        'question_en' => 'Considerada como Neurodermatitis en los adolescentes',
        'correct' => '0'
    ]);



    $question = TriviaQuestion::create([
        'question_es' => 'En la fisiopatología de la Dermatitis Atópica, marque lo verdadero:',
        'question_en' => 'En la fisiopatología de la Dermatitis Atópica, marque lo verdadero:',
        'subject_es' => '',
        'subject_en' => '',
        'speaker_id' => 1,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'En la alteración de la barrera cutánea no hay déficit de filagrina',
        'question_en' => 'En la alteración de la barrera cutánea no hay déficit de filagrina',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'La uniones estrechas frenan la pérdida transepidérmica de agua',
        'question_en' => 'La uniones estrechas frenan la pérdida transepidérmica de agua',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'La TSLP, es la citocina coordinadora de la reparación de barrera',
        'question_en' => 'La TSLP, es la citocina coordinadora de la reparación de barrera',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'No hay relación entre daño de barrera e intensidad de prurito',
        'question_en' => 'No hay relación entre daño de barrera e intensidad de prurito',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'IL4, IL13 liberadas por células TH1, activan mayor inflamación',
        'question_en' => 'IL4, IL13 liberadas por células TH1, activan mayor inflamación',
        'correct' => '0'
    ]);


########################################################################################

    $question = TriviaQuestion::create([
        'question_es' => 'Acerca del microbioma en pacientes con acné: ¿cuál es correcta? : ',
        'question_en' => 'Acerca del microbioma en pacientes con acné: ¿cuál es correcta? : ',
        'subject_es' => 'Acné & Microbiota',
        'subject_en' => 'Acné & Microbiota',
        'speaker_id' => 13,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'La disbiosis ocurre por la hiperproliferación de C. acnes en forma aislada.',
        'question_en' => 'La disbiosis ocurre por la hiperproliferación de C. acnes en forma aislada.',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'La disfunción de la barrera epidérmica permite el desarrollo de filotipos de C. acnes más inflamatórios',
        'question_en' => 'La disfunción de la barrera epidérmica permite el desarrollo de filotipos de C. acnes más inflamatórios',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'La dieta Occidental impacta positivamente en la microbiota intestinal y sebogénesis.',
        'question_en' => 'La dieta Occidental impacta positivamente en la microbiota intestinal y sebogénesis.',
        'correct' => '0'
    ]);



    $question = TriviaQuestion::create([
        'question_es' => 'Las terapias en acné: ¿cuál es correcta?',
        'question_en' => 'Las terapias en acné: ¿cuál es correcta?',
        'subject_es' => 'Acné & Microbiota',
        'subject_en' => 'Acné & Microbiota',
        'speaker_id' => 13,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'La isotretinoina siempre se administra hasta lograr una dosis alta acumulada fija',
        'question_en' => 'La isotretinoina siempre se administra hasta lograr una dosis alta acumulada fija',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'No se debe utilizar hidratantes en pacientes con hiperseborrea',
        'question_en' => 'No se debe utilizar hidratantes en pacientes con hiperseborrea',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Los antibióticos sistémicos producen efectos negativos por tiempos variables en microbiota cutánea y extra cutánea, por eso limitar su uso a un máximo de 3 meses',
        'question_en' => 'Los antibióticos sistémicos producen efectos negativos por tiempos variables en microbiota cutánea y extra cutánea, por eso limitar su uso a un máximo de 3 meses',
        'correct' => '1'
    ]);



########################################################################################

    $question = TriviaQuestion::create([
        'question_es' => '¿Cómo diagnostica rosácea en piel oscura? Marcar una o varias.',
        'question_en' => '¿Cómo diagnostica rosácea en piel oscura? Marcar una o varias.',
        'subject_es' => 'Red Microbiome - Rosacea',
        'subject_en' => 'Red Microbiome - Rosacea',
        'speaker_id' => 15,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'No es necesario, la rosácea no da en pieles oscuras',
        'question_en' => 'No es necesario, la rosácea no da en pieles oscuras',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Preguntar por sensación de picor, quemazón, hipersensibilidad',
        'question_en' => 'Preguntar por sensación de picor, quemazón, hipersensibilidad',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Preguntar por otras situaciones además de las rojeces',
        'question_en' => 'Preguntar por otras situaciones además de las rojeces',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Evaluar la respuesta a tratamientos de rosácea',
        'question_en' => 'Evaluar la respuesta a tratamientos de rosácea',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Uso de imagenología digital para rojeces',
        'question_en' => 'Uso de imagenología digital para rojeces',
        'correct' => '1'
    ]);


    $question = TriviaQuestion::create([
        'question_es' => '¿Qué es el microbioma cutáneo? Marcar una o varias. ',
        'question_en' => '¿Qué es el microbioma cutáneo? Marcar una o varias. ',
        'subject_es' => 'Red Microbiome - Rosacea',
        'subject_en' => 'Red Microbiome - Rosacea',
        'speaker_id' => 15,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Las características microscópicas de las células',
        'question_en' => 'Las características microscópicas de las células',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Sólo las bacterias de la piel, no se incluyen hongos u otros microorganismos',
        'question_en' => 'Sólo las bacterias de la piel, no se incluyen hongos u otros microorganismos',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'La totalidad de bacterias, hongos, virus y parásitos de la piel',
        'question_en' => 'La totalidad de bacterias, hongos, virus y parásitos de la piel',
        'correct' => '1'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Una condición invariable y estática de la piel',
        'question_en' => 'Una condición invariable y estática de la piel',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => 'Algo que se relaciona con las funciones de barrera e inmunológica de la piel',
        'question_en' => 'Algo que se relaciona con las funciones de barrera e inmunológica de la piel',
        'correct' => '0'
    ]);

    
    ########################################################################################

    $question = TriviaQuestion::create([
        'question_es' => '¿Cuántos investigadores están trabajando en LOREAL R&I alrededor del mundo?',
        'question_en' => '¿Cuántos investigadores están trabajando en LOREAL R&I alrededor del mundo?',
        'subject_es' => 'How to bring our innovations alive for our patients all around the world',
        'subject_en' => 'How to bring our innovations alive for our patients all around the world',
        'speaker_id' => 8,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => '500',
        'question_en' => '500',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => '2000',
        'question_en' => '2000',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => '4000',
        'question_en' => '4000',
        'correct' => '1'
    ]);


    $question = TriviaQuestion::create([
        'question_es' => '¿Cuántos tests de calidad y seguridad se hacen antes de lanzar una fórmula al mercado?',
        'question_en' => '¿Cuántos tests de calidad y seguridad se hacen antes de lanzar una fórmula al mercado?',
        'subject_es' => 'How to bring our innovations alive for our patients all around the world',
        'subject_en' => 'How to bring our innovations alive for our patients all around the world',
        'speaker_id' => 8,
        'status' => 'Pendiente'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => '20',
        'question_en' => '20',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => '80',
        'question_en' => '80',
        'correct' => '0'
    ]);
    $option = $question->trivia_options()->create([
        'question_es' => '+100',
        'question_en' => '+100',
        'correct' => '1'
    ]);


    }
}
