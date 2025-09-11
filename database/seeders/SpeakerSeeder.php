<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Speaker;

class SpeakerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        Speaker::truncate();

        Speaker::create([
            'name' => 'ROSALIA BALLONA',
            'spanish_description' =>  'La Dra. Ballona es Dermatóloga Pediátrica, jefa del Servicio de Dermatología Instituto Nacional de Salud del Niño - Lima Perú y Coordinadora de la Residencia de Dermatología Pediátrica en la UPSMP, miembro titular del Círculo Dermatológico del Perú.',
            'english_description' =>  'Dr. Ballona is a Pediatric Dermatologist, Head of the Dermatology Department at the National Institute of Child Health in Lima, Peru, and Coordinator of the Pediatric Dermatology Residency Program at UPSMP. She is also a full member of the Dermatological Circle of Peru.',
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dra-rosalia-ballona.png',
            'document' => 'https://docs.google.com/presentation/d/1Lq9GxVnV6WJWLz_x8T7L5ds6xHZz35Rd/edit?usp=drive_link'
        ]);

        Speaker::create([
            'name' => 'EDWIN BENDEK',
            'spanish_description' =>  'El Dr. Bendek es médico dermatólogo de la Universidad Javeriana de Bogotá, Colombia. Especialista en antienvejecimiento de Wosiam, París, Francia. Tiene un Máster en Salud Pública de la Universidad de Granada España, un Máster en Bioética -Salud y Derecho de la Universidad de Rennes en Francia. Cursó especialización en ensayos clínicos en la escuela de Salud Pública de Harvard en Boston, USA. Actualmente cursa con estudios en la maestría de Medicina Estética y Longevidad de la Universidad de Alcalá en España. Es miembro del Comité de Investigaciones de AsoColDerma, miembro del CILAD, EADV y profesor universitario de dermatología.',
            'english_description' =>  "Dr. Bendek is a dermatologist from Universidad Javeriana in Bogotá, Colombia. He specializes in anti-aging treatments from Wosiam in Paris, France. He holds a master’s degree in public health from the University of Granada, Spain, and a master's degree in Bioethics - Health and Law from the University of Rennes in France. He completed a specialization in clinical trials at the Harvard School of Public Health in Boston, USA. Currently, he is pursuing studies in the master's program in Aesthetic Medicine and Longevity at the University of Alcalá in Spain. He is a member of the Research Committee of AsoColDerma, a member of CILAD and EADV, and a university professor of dermatology.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dr-edwin-bendek.png',
            'document' => 'https://docs.google.com/presentation/d/10Jj-GI-ib5YiHbA1CJMEw3RKFdHXh13s/edit?usp=drive_link'
        ]);

        Speaker::create([
            'name' => 'APPLE BODEMER',
            'spanish_description' =>  'La Dra. Bodemer es médica y dermatóloga graduada de la Universidad de Wisconsin. Completó estudios en Medicina Integrativa en la Universidad de Arizona. Es la primera dermatóloga que ha sido board-certified¬ tanto en Dermatología como en Medicina Integrativa. Actualmente participa de la Junta Directiva de la Asociación de Medicina Integrativa Americana. También es board-certified¬ en Medicina de Estilo de Vida. Es co-fundadora del Programa que certifica en Dermatología Integrativa y es profesora de los programas académicos de Dermatología Integrativa para médicos y estéticos en LearnSkin. Ha escrito varios capítulos de libros de Dermatología Integrativa y participado en el desarrollo del Curriculum académico de Medicina Integrativa de la Universidad de Arizona. Tiene un alto compromiso con la medicina preventiva y considera que la mejor manera de impactar la salud de forma positiva es enseñarle a los pacientes a empoderarse sobre sus propios procesos de salud. Ha participado en medios de comunicación incluyendo The Plantrician Project, The Food Revolution Network y MindBodyGreen.',
            'english_description' =>  "Dr. Bodemer is a medical doctor and dermatologist who graduated from the University of Wisconsin. She completed studies in Integrative Medicine at the University of Arizona. She is the first dermatologist to be board-certified in both Dermatology and Integrative Medicine. Currently, she serves on the Board of Directors of the American Association of Integrative Medicine. She is also board-certified in Lifestyle Medicine. She is a co-founder of the Integrative Dermatology Certificate and a professor in academic programs for Integrative Dermatology for medical and aesthetic professionals at LearnSkin. She has authored several chapters in Integrative Dermatology books and contributed to the development of the academic curriculum for Integrative Medicine at the University of Arizona. She is deeply committed to preventive medicine and believes that the best way to positively impact health is by empowering patients to take control of their own health processes. She has appeared in media outlets including The Plantrician Project, The Food Revolution Network, and MindBodyGreen.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dra-apple-bodemer.png',
            'document' => 'https://docs.google.com/presentation/d/1lv1aWu-VDPqPdjQKciivAHZqYVRw9J77/edit?usp=drive_link',
            'document2' => 'https://docs.google.com/presentation/d/1SoVwyq2mTcF7P2smVYuOzG05mcM2-tER/edit?usp=drive_link'
        ]);

        Speaker::create([
            'name' => 'DENI DAZA',
            'spanish_description' =>  'Creativo Estratégico para Colombia, Ecuador y Perú. 
            Comunicadora con 13 años de experiencia. Escritora y creativa publicitaria con experiencia en medios 360.',
            'english_description' =>  'Strategic Creative for Colombia, Ecuador, and Peru. 
            A communicator with 13 years of experience. Writer and advertising creative with expertise in 360-degree media.',
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/deni-daza.png',
            'document' => 'https://docs.google.com/presentation/d/1azbe8aifkwFAXscI98Gk40_XvKJurUak/edit?usp=drive_link'
        ]);

        Speaker::create([
            'name' => 'ANA CLAUDIA ESPÓSITO',
            'spanish_description' =>  'La Dra. Espósito es una dermatóloga brasileña con un Máster y Doctorado en Patología de la Universidad de Estadual Paulista (Unesp) con énfasis en el estudio del melasma y otras hiperpigmentaciones cutáneas, estrés oxidativo, autofagia y senescencia celular. Es docente universitaria y asesora del Programa de Posgrado en Patología (Unesp), con cerca de 50 artículos científicos publicados en dermatología.',
            'english_description' =>  "Dr. Espósito is a Brazilian dermatologist holding a master's and doctorate in Pathology from São Paulo State University (Unesp), specializing in the study of melasma and other cutaneous hyperpigmentations, oxidative stress, autophagy, and cellular senescence. She is a university professor and advisor for the Postgraduate Pathology Program (Unesp), having authored nearly 50 scientific articles in dermatology.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dra-ana-esposito.png',
            'document' => 'https://docs.google.com/presentation/d/16SH3xNRiMtFgnjX0GnG1U_57acLwIb9p/edit?usp=drive_link&ouid=111110470192833271763&rtpof=true&sd=true'
        ]);

        Speaker::create([
            'name' => 'FABIÁN FLORES',
            'spanish_description' =>  "Fabián Flores cuenta con una experiencia de más de 20 años en la industria de la cosmética. Comienza su carrera en las áreas de Ventas y Mercadotecnia del grupo L’Oréal y posteriormente trabaja en el Desarrollo de Productos en la sede de L’Oréal, en Francia.  Gracias a su formación científica, la cual incluye un Máster en Dermofarmacia y Formulación Cosmética y un MBA Farmacéutico, en el año 2009 asume la posición de Director Científico y de Asuntos Regulatorios para L’Oréal México en donde tiene la oportunidad de aplicar la estrategia regulatoria del grupo para el desarrollo, comercialización e innovación de los productos, gestionar la constitución de los expedientes regulatorios y representar al Grupo L’Oréal en el seno de los grupos técnicos de las Asociaciones Profesionales, en particular la CANIPEC y CASIC en donde actualmente preside la Comisión de Asuntos Sanitarios, en colaboración con las autoridades sanitarias y de protección al consumidor.
A partir del año 2014 ocupa la posición de Director Científico y de Asuntos Regulatorios para México y la Zona Hispanoamericana, coordinando y asesorando a una red de equipos especializados en los asuntos regulatorios, a lo largo de los países de Hispanoamérica, incluyendo aquellos que integran la Alianza del Pacífico y la Comunidad Andina. 
Ha participado en numerosos Congresos y Simposiums relacionados con los Asuntos Regulatorios, tanto en México como en el extranjero.",
            'english_description' =>  "Fabián Flores boasts over 20 years of experience in the cosmetics industry. He began his career in Sales and Marketing at L'Oréal Group, later transitioning to Product Development at L'Oréal's headquarters in France. Thanks to his scientific background, which includes a master's in Dermopharmacy and Cosmetic Formulation as well as an MBA in Pharmaceutical Sciences. He assumed the role of Scientific and Regulatory Affairs Director for L'Oréal Mexico in 2009. In this capacity, he applied the group's regulatory strategy for product development, commercialization, and innovation of products. He managed the establishment of regulatory dossiers and represented L'Oréal Group within technical committees of Professional Associations, notably CANIPEC and CASIC, where he currently leads the Sanitary Affairs Commission in collaboration with health and consumer protection authorities.

Since 2014, he has held the position of Scientific and Regulatory Affairs Director for Mexico and the Hispanic American Region. In this role, he coordinates and advises a network of specialized regulatory teams across Hispanic American countries, including those in the Pacific Alliance and Andean Community. 
He has actively participated in numerous Congresses and Symposia centered around Regulatory Affairs, both within Mexico and abroad.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/fabian-flores.png',
            'document' => ''
        ]);


        Speaker::create([
            'name' => 'CÉSAR GONZALEZ',
            'spanish_description' =>  "El Dr. César es médico de la Universidad Nacional de Colombia y Médico Dermatólogo de la Universidad del Bosque en Bogotá, Colombia. Realizó un Máster en Medicina Estética en las Islas Beleares, España y Diplomado en láser en la Universidad Nacional de Colombia. Es Miembro de la Asociación Colombiana de Dermatología, AsoColDerma, Miembro de AAD y presidente de la Sociedad Internacional de Melasma y otros trastornos pigmentarios SINMELASMA.",
            'english_description' =>  "Dr. César is a physician from the National University of Colombia and a Dermatologist from the University of Bosque in Bogotá, Colombia. He completed a master's in Aesthetic Medicine in the Balearic Islands, Spain, and a Diploma in Laser Therapy from the National University of Colombia. He is a member of the Colombian Association of Dermatology (AsoColDerma), a member of the American Academy of Dermatology (AAD), and the president of the International Society for Melasma and other Pigmentary Disorders, SINMELASMA.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dr-cesar-gonzales.png',
            'document' => ''
        ]);


        Speaker::create([
            'name' => 'FRÉDÉRIQUE LABATUT',
            'spanish_description' =>  "Comenzó su carrera trabajando para la investigación de LVMH en investigación aplicada para crear futuras innovaciones para la marca Dior. Luego se unió a L'Oreal, donde construyó la mayor parte de su carrera. Trabajó inicialmente, en París, para diferentes marcas del grupo en la División de Lujo, luego en la División de Productos de Consumo. Posteriormente, se unió a los laboratorios de investigación de L'Oreal en Asia, en Shanghái, durante casi 5 años, donde desarrolló un sólido conocimiento y comprensión de las necesidades y comportamientos de la piel de hombres y mujeres. De vuelta en Francia en 2020, en la División de Belleza Dermatológica de L'Oreal, primero asumió la dirección del laboratorio internacional de cuidado de la piel de Vichy Laboratoire y luego de La Roche Posay. Como directora del laboratorio de cuidado de la piel, dedica su tiempo a diseñar y probar innovaciones revolucionarias para 'crear belleza que mueva el mundo', el propósito común del grupo L'Oreal.",
            'english_description' =>  "She began her career working on research for LVMH in applied research to create future innovations for the Dior brand. Later, she joined L'Oréal, where she built most of her career. She started in Paris, working for various brands within the Luxury Division and then in the Consumer Products Division. Subsequently, she joined L'Oréal's research laboratories in Asia, in Shanghai, for nearly 5 years. During this time, she developed a profound understanding of the skincare needs and behaviors of both men and women. Returning to France in 2020, in L'Oréal's Dermatological Beauty Division, she initially took on the leadership of the international skincare laboratory for Vichy Laboratoire and later for La Roche-Posay. As the director of the skincare laboratory, she devotes her time to designing and testing revolutionary innovations to fulfill the shared purpose of 'create the beauty that moves the world,' the common purpose of the L'Oréal Group.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/frederique-labatut.png',
            'document' => 'https://docs.google.com/presentation/d/1OF9JKqRZvjlhyoeQZ6qUcl7ZHvZtVT5d/edit?usp=drive_link'
        ]);

        Speaker::create([
            'name' => 'KEITH LOO',
            'spanish_description' =>  "Dr. Loo es CEO y Co-Fundador de Skinopathy, una compañía médica que se enfoca en Inteligencia Artificial en dermatología y cáncer de piel. Skinopathy ha sido ganadora de varios reconocimientos: en 2022 en el AIMED Conference Abstract Competition, en 2022 INSEAD Business for Good Award y en 2023, el primer puesto en innovación en la Sociedad de Cirugía Plástica en Canadá. El Dr. Tiene más de 20 años de experiencia con uso de tecnologías y ha trabajado con firmas como Microsoft, IBM, Logitech, en temas de banca, telcos, Estrategias de Data & AI y laboratorios de innovación. También es un asesor y está muy involucrado con la comunidad de “start-ups”en Canadá. El Dr. Loo es instructor en la Schulich School of Business de la Universidad de York en temas de data & estrategias de AI tipo “start-ups”. ",
            'english_description' =>  "Dr. Loo is the CEO and Co-Founder of Skinopathy, a medical company that focuses on Artificial Intelligence in dermatology and skin cancer. Skinopathy has received several awards: in 2022 at the AIMED Conference Abstract Competition, the 2022 INSEAD Business for Good Award, and in 2023, first place in innovation from the Canadian Society of Plastic Surgery. With over 20 years of experience in technology utilization, he has worked with companies such as Microsoft, IBM, Logitech, in banking, telecommunications, Data & AI Strategies, and innovation labs. He also serves as an advisor and is deeply involved in the start-up community in Canada. Dr. Loo is an instructor at the Schulich School of Business at York University, specializing data and AI strategies for start-ups.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/keith-loo.png',
            'document' => ''
        ]);

        Speaker::create([
            'name' => 'MIGUEL MARTI',
            'spanish_description' =>  "El Dr. Marti es médico de la Universidad de la Habana, Cuba, con estudios de Medicina Interna y Dermatología de la Universidad de Buenos Aires. Tiene un máster en Tricología & Transplante Capilar de la Universidad de Alcalá en Madrid y un máster en Medicina Regenerativa de la Universidad Charite de Berlín. Su área de mayor interés es la tricología y cuenta con más de 10 años de experiencia en este campo. Es fundador de TRICOMED y miembro de la Sociedad Argentina de Dermatología, AAD, AHRS, CILAD y EADV.",
            'english_description' =>  "Dr. Marti is a physician from the University of Havana, Cuba, with studies in Internal Medicine and Dermatology from the University of Buenos Aires. He holds a master's degree in Trichology & Hair Transplant from the University of Alcalá in Madrid and a master's degree in Regenerative Medicine from the Charite University in Berlin. His primary area of interest is trichology, and he has over 10 years of experience in this field. He is the founder of TRICOMED and a member of the Argentine Society of Dermatology, AAD, AHRS, CILAD, and EADV.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dr-miguel-marti.png',
            'document' => 'https://drive.google.com/open?id=13U1DtGM1NusS3oZlfaaa6G3x3ljBv596&usp=drive_copy'
        ]);

        Speaker::create([
            'name' => 'ANA MOLINA',
            'spanish_description' =>  "Ana Molina Ruiz es Dermatóloga en el Hospital Universitario Fundación Jiménez Díaz y Profesora de Dermatología de la Universidad Autónoma de Madrid. Premio Extraordinario de Doctorado Internacional de la Universidad Autónoma de Madrid con estancias de investigación en UCSF California, MSKCC en Nueva York y Alemania. En los últimos años se ha involucrado activamente en la Divulgación de Salud en medios tradicionales como radio y televisión, así como online. Presenta el programa matutino “Cuestión de Piel” en Radio Nacional de España,  y una sección dedicada al cuidado de la piel en Televisión Española y Telemadrid. Dirige un Podcast con su hermana Rosa Molina, psiquiatra, titulado “De Piel a Cabeza”, cuyo lema es “El conocimiento es la mejor medicina” y ha publicado el libro “Piel sana, Piel Bonita” con Editorial Paidós. Puedes encontrarla en instagram como @dr.anamolina.",
            'english_description' =>  "Ana Molina Ruiz is a Dermatologist at the Jiménez Díaz Foundation University Hospital and a Dermatology Professor at the Autonomous University of Madrid. She received the Extraordinary International Doctorate Award from the Autonomous University of Madrid and conducted research stays at UCSF California, MSKCC in New York, and Germany. In recent years, she has been actively engaged in Health Communication through traditional media such as radio and television, as well as online platforms. She hosts the morning program 'Cuestión de Piel' on Radio Nacional de España and has a dedicated skincare segment on Televisión Española and Telemadrid. She co-hosts a podcast with her sister Rosa Molina, a psychiatrist, titled 'De Piel a Cabeza' (From Skin to Mind), with the motto 'Knowledge is the best medicine.' She has also authored the book 'Piel sana, Piel Bonita' (Healthy Skin, Beautiful Skin) published by Editorial Paidós. You can find her on Instagram as @dr.anamolina.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dra-ana-molina.png',
            'document' => 'https://docs.google.com/presentation/d/1AxgaUEidWekekD3ZyUKaaqhkdMYGdJ4O/edit?usp=drive_link'
        ]);

        Speaker::create([
            'name' => 'CLAUDIA MONTOYA',
            'spanish_description' =>  "La Dra. Montoya estudió Medicina en la Universidad del Valle, Cali, Colombia y se especializó en Dermatología en la Universidad ICESI en Cali Colombia. Realizó un Fellow en Tricología en la Universidad de Miami. Actualmente es Docente de pregrado y postgrado de la Universidad del Norte-Barranquilla y Presidente del Grupo Colombiano de Tricología.",
            'english_description' =>  "Dr. Montoya studied Medicine at the University of Valle, Cali, Colombia, and specialized in Dermatology at the ICESI University in Cali, Colombia. She completed a Fellowship in Trichology at the University of Miami. Currently, she serves as a professor for both undergraduate and postgraduate programs at the University of Norte-Barranquilla and is the President of the Colombian Trichology Group.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dra-claudia-montoya.png',
            'document' => 'https://docs.google.com/presentation/d/13U1DtGM1NusS3oZlfaaa6G3x3ljBv596/edit?usp=drive_link&ouid=111110470192833271763&rtpof=true&sd=true'
        ]);

        Speaker::create([
            'name' => 'MONICA NOGUERA',
            'spanish_description' =>  "La Dra. Noguera es médica de la Universidad de Buenos Aires, Argentina. Especialista en Dermatología y Medicina Interna también de la Universidad de Buenos Aires. Es Médica Staff de Buenos Aires Skin; coordinadora de la sección de Acné en el Hospital Universitario CEMIC , Buenos Aires; docente auxiliar de carrera de Medicina II, en el Instituto Universitario CEMIC. Ella es miembro titular de la Sociedad Argentina de Dermatología (SAD), directora del Curso de Acné de la SAD, coordinadora del grupo de trabajo de Acné de la SAD, investigadora en ensayos clínicos en dermocosmética, acné y Dermatología general y speaker nacional e internacional en acné.",
            'english_description' =>  "Dr. Noguera is a physician from the University of Buenos Aires, Argentina. She holds specializations in Dermatology and Internal Medicine, also from the University of Buenos Aires. She is a Medical Staff member at Buenos Aires Skin, coordinator of the Acne section at CEMIC University Hospital in Buenos Aires, and an assistant professor in the Medicine II career at CEMIC University Institute. She is a full member of the Argentine Society of Dermatology (SAD), director of the SAD's Acne Course, coordinator of the SAD's Acne working group, a researcher in clinical trials for dermocosmetics, acne, and general dermatology. She is also a national and international speaker on acne.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dra-monica-noguera.png',
            'document' => 'https://docs.google.com/presentation/d/19QdTShsIgTA3BlCbqqSCkVctG-Vmuheg/edit?usp=drive_link'
        ]);

        Speaker::create([
            'name' => 'SERGIO SCHALKA',
            'spanish_description' =>  "El Dr. Schalka es médico y dermatólogo de la Universidad de São Paulo con un máster en fotoprotección de esta misma universidad. Es investigador invitado de la Universidad de São Paulo y coordinador del Consenso Brasileño de Fotoprotección. Es jefe de Laboratorio de Fotoprotección – Medcin Skin Research Center.",
            'english_description' =>  "Dr. Schalka is a physician and dermatologist from the University of São Paulo, with a master's degree in photoprotection from the same university. He is an invited researcher at the University of São Paulo and serves as the coordinator of the Brazilian Photoprotection Consensus. He also holds the position of head of the Photoprotection Laboratory at the Medcin Skin Research Center.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dr-sergio-schalka.png',
            'document' => 'https://docs.google.com/presentation/d/1EqCXWjGWrNAqiZapVSSRebsgm2dj9cHR/edit?usp=drive_link'
        ]);

        Speaker::create([
            'name' => 'JERRY TAN',
            'spanish_description' =>  "El Dr. Tan se graduó en medicina en la Universidad de Queen en Kingston, Ontario, Canadá y se formó en medicina interna en la Universidad de Toronto y en dermatología en la Universidad de British Columbia y la Universidad de Michigan. Ejerce en Windsor, Ontario, Canadá. El enfoque de investigación incluye el acné, las cicatrices del acné y la rosácea. Su grupo ha desarrollado ayudas para la toma de decisiones del paciente en dermatología: acné, psoriasis, rosácea e hidradenitis supurativa, disponibles en www.informed-decisions.org
Es miembro del grupo de trabajo de las pautas para el acné de la AAD y ha sido editor asociado de la JAAD y la BJD. Durante las últimas 3 décadas, ha sido autor/coautor de más de 150 publicaciones revisadas por pares.",
            'english_description' =>  "Dr. Tan graduated in medicine from Queen's University in Kingston, Ontario, Canada, and underwent training in internal medicine at the University of Toronto and in dermatology at the University of British Columbia and the University of Michigan. He practices in Windsor, Ontario, Canada. His research focus includes acne, acne scarring, and rosacea. His group has developed patient decision aids in dermatology for acne, psoriasis, rosacea, and hidradenitis suppurativa, available at www.informed-decisions.org. 
He is a member of the AAD's acne guidelines working group and has served as an associate editor for JAAD and BJD. Over the past three decades, he has authored/co-authored more than 150 peer-reviewed publications.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/dr-jerry-tan.png',
            'document' => 'https://docs.google.com/presentation/d/1esMQ47OFA-4jCa5L0pfX0cymeuKrwEZG/edit?usp=drive_link',
            'document2' => 'https://docs.google.com/presentation/d/1JeFyuL0q71XPelut9pV2zNpVZkXaJo-O/edit?usp=drive_link',
            'document3' => 'https://docs.google.com/presentation/d/1UjJz4dJEvQWMoreYD7KLjmO05ZEaPO8y/edit?usp=drive_link&ouid=111110470192833271763&rtpof=true&sd=true'
        ]);

        Speaker::create([
            'name' => 'ADRIANA CRUZ (CHAIR)',
            'spanish_description' =>  "Médico y dermatóloga formada en la Universidad del Valle, Colombia, con un post doctorado en investigación en la Universidad de Connecticut. Ha sido asesora del Ministerio de Salud, docente universitario, es miembro de la AAD, CILAD y hace parte de la Junta Directiva de la Asociación Colombiana de Dermatología - AsoColDerma. Actualmente cursa el Certificado de Dermatología Integrativa LearnSkin en EE. UU. Líder de opinión y gestora de actividades académicas y de responsabilidad social en Cali. Cuenta con varias publicaciones en revistas internacionales indexadas y ha participado como conferencista en varios escenarios.",

            'english_description' =>  "Physician and dermatologist trained at Universidad del Valle, Colombia, with a post-doctoral degree in research from the University of Connecticut. She has served as an advisor to the Ministry of Health, university professor, and is a member of AAD, CILAD, and she is part of the Board of Directors of the Colombian Association of Dermatology - AsoColDerma. Currently pursuing the Integrative Dermatology LearnSkin Certificate in the USA. She is an opinion leader and manager of academic and social responsibility initiatives in Cali. She has numerous publications in indexed international journals and has participated as a speaker in various settings.",
            'image' => 'https://signme4.s3.amazonaws.com/public/loreal/images/adriana-cruz.png',
            'document' => ''
        ]);

    }
}
