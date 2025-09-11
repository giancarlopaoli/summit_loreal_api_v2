<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        Country::truncate();

        Country::create([
            'name' => 'Afganistán',
            'prefix'    =>  'AF',
            'phone_code' => '+93'
        ]);
        Country::create([
            'name' => 'Alemania',
            'prefix'    =>  'AL',
            'phone_code' => '+49'
        ]);
        Country::create([
            'name' => 'Andorra',
            'prefix'    =>  'AD',
            'phone_code' => '+376'
        ]);
        Country::create([
            'name' => 'Anguilla',
            'prefix'    =>  'AN',
            'phone_code' => '+1 264'
        ]);
        Country::create([
            'name' => 'Argelia',
            'prefix'    =>  'DZ',
            'phone_code' => '+213'
        ]);
        Country::create([
            'name' => 'Argentina',
            'prefix'    =>  'AR',
            'phone_code' => '+54'
        ]);
        Country::create([
            'name' => 'Aruba',
            'prefix'    =>  'AB',
            'phone_code' => '+297'
        ]);
        Country::create([
            'name' => 'Australia',
            'prefix'    =>  'AU',
            'phone_code' => '+61'
        ]);
        Country::create([
            'name' => 'Austria',
            'prefix'    =>  'AT',
            'phone_code' => '+43'
        ]);
        Country::create([
            'name' => 'Bahamas',
            'prefix'    =>  'BH',
            'phone_code' => '+1 242'
        ]);
        Country::create([
            'name' => 'Barbados',
            'prefix'    =>  'BA',
            'phone_code' => '+1 246'
        ]);
        Country::create([
            'name' => 'Bélgica',
            'prefix'    =>  'BG',
            'phone_code' => '+32'
        ]);
        Country::create([
            'name' => 'Belice',
            'prefix'    =>  'BE',
            'phone_code' => '+501'
        ]);
        Country::create([
            'name' => 'Bermudas',
            'prefix'    =>  'BM',
            'phone_code' => '+1 441'
        ]);
        Country::create([
            'name' => 'Bolivia',
            'prefix'    =>  'BO',
            'phone_code' => '+591'
        ]);
        Country::create([
            'name' => 'Brasil',
            'prefix'    =>  'BR',
            'phone_code' => '+55'
        ]);
        Country::create([
            'name' => 'Islas Caimán',
            'prefix'    =>  'CH',
            'phone_code' => '+1 345'
        ]);
        Country::create([
            'name' => 'Canadá',
            'prefix'    =>  'CA',
            'phone_code' => '+1'
        ]);
        Country::create([
            'name' => 'Chile',
            'prefix'    =>  'CP',
            'phone_code' => '+56'
        ]);
        Country::create([
            'name' => 'China',
            'prefix'    =>  'CN',
            'phone_code' => '+86'
        ]);
        Country::create([
            'name' => 'Chipre',
            'prefix'    =>  'NC',
            'phone_code' => '+357'
        ]);
        Country::create([
            'name' => 'Colombia',
            'prefix'    =>  'CO',
            'phone_code' => '+57'
        ]);
        Country::create([
            'name' => 'Corea del Norte',
            'prefix'    =>  'DM',
            'phone_code' => '+850'
        ]);
        Country::create([
            'name' => 'Corea del Sur',
            'prefix'    =>  'KR',
            'phone_code' => '+82'
        ]);
        Country::create([
            'name' => 'Costa Rica',
            'prefix'    =>  'CR',
            'phone_code' => '+506'
        ]);
        Country::create([
            'name' => 'Cuba',
            'prefix'    =>  'CU',
            'phone_code' => '+53'
        ]);
        Country::create([
            'name' => 'Dinamarca',
            'prefix'    =>  'ES',
            'phone_code' => '+45'
        ]);
        Country::create([
            'name' => 'República Dominicana',
            'prefix'    =>  'EA',
            'phone_code' => '+1 809'
        ]);
        Country::create([
            'name' => 'Ecuador',
            'prefix'    =>  'EC',
            'phone_code' => '+593'
        ]);
        Country::create([
            'name' => 'Egipto',
            'prefix'    =>  'EG',
            'phone_code' => '+20'
        ]);
        Country::create([
            'name' => 'El Salvador',
            'prefix'    =>  'SP',
            'phone_code' => '+503'
        ]);
        Country::create([
            'name' => 'Emiratos Árabes Unidos',
            'prefix'    =>  'FP',
            'phone_code' => '+971'
        ]);
        Country::create([
            'name' => 'España',
            'prefix'    =>  'GM',
            'phone_code' => '+34'
        ]);
        Country::create([
            'name' => 'Estados Unidos',
            'prefix'    =>  'US',
            'phone_code' => '+1'
        ]);
        Country::create([
            'name' => 'Filipinas',
            'prefix'    =>  'HI',
            'phone_code' => '+63'
        ]);
        Country::create([
            'name' => 'Francia',
            'prefix'    =>  'FR',
            'phone_code' => '+33'
        ]);
        Country::create([
            'name' => 'Grecia',
            'prefix'    =>  'GR',
            'phone_code' => '+30'
        ]);
        Country::create([
            'name' => 'Guatemala',
            'prefix'    =>  'HD',
            'phone_code' => '+502'
        ]);
        Country::create([
            'name' => 'Guyana',
            'prefix'    =>  'GY',
            'phone_code' => '+592'
        ]);
        Country::create([
            'name' => 'Haití',
            'prefix'    =>  'ID',
            'phone_code' => '+509'
        ]);
        Country::create([
            'name' => 'Honduras',
            'prefix'    =>  'IK',
            'phone_code' => '+504'
        ]);
        Country::create([
            'name' => 'India',
            'prefix'    =>  'IL',
            'phone_code' => '+91'
        ]);
        Country::create([
            'name' => 'Irán',
            'prefix'    =>  'IR',
            'phone_code' => '+98'
        ]);
        Country::create([
            'name' => 'Iraq',
            'prefix'    =>  'IC',
            'phone_code' => '+964'
        ]);
        Country::create([
            'name' => 'Irlanda',
            'prefix'    =>  'IV',
            'phone_code' => '+353'
        ]);
        Country::create([
            'name' => 'Israel',
            'prefix'    =>  'IS',
            'phone_code' => '+972'
        ]);
        Country::create([
            'name' => 'Italia',
            'prefix'    =>  'IT',
            'phone_code' => '+39'
        ]);
        Country::create([
            'name' => 'Japón',
            'prefix'    =>  'JA',
            'phone_code' => '+81'
        ]);
        Country::create([
            'name' => 'Laos',
            'prefix'    =>  'LO',
            'phone_code' => '+856'
        ]);
        Country::create([
            'name' => 'Letonia',
            'prefix'    =>  'LT',
            'phone_code' => '+371'
        ]);
        Country::create([
            'name' => 'Líbano',
            'prefix'    =>  'LB',
            'phone_code' => '+961'
        ]);
        Country::create([
            'name' => 'Luxemburgo',
            'prefix'    =>  'LX',
            'phone_code' => '+352'
        ]);
        Country::create([
            'name' => 'Macao',
            'prefix'    =>  'MC',
            'phone_code' => '+853'
        ]);
        Country::create([
            'name' => 'Malasia',
            'prefix'    =>  'ML',
            'phone_code' => '+60'
        ]);
        Country::create([
            'name' => 'México',
            'prefix'    =>  'MX',
            'phone_code' => '+52'
        ]);
        Country::create([
            'name' => 'Myanmar',
            'prefix'    =>  'MY',
            'phone_code' => '+95'
        ]);
        Country::create([
            'name' => 'Nicaragua',
            'prefix'    =>  'NI',
            'phone_code' => '+505'
        ]);
        Country::create([
            'name' => 'Nueva Zelanda',
            'prefix'    =>  'NZ',
            'phone_code' => '+64'
        ]);
        Country::create([
            'name' => 'Panamá',
            'prefix'    =>  'PA',
            'phone_code' => '+507'
        ]);
        Country::create([
            'name' => 'Papúa Nueva Guinea',
            'prefix'    =>  'NG',
            'phone_code' => '+675'
        ]);
        Country::create([
            'name' => 'Paraguay',
            'prefix'    =>  'PG',
            'phone_code' => '+595'
        ]);
        Country::create([
            'name' => 'Perú',
            'prefix'    =>  'PE',
            'phone_code' => '+51'
        ]);
        Country::create([
            'name' => 'Portugal',
            'prefix'    =>  'PT',
            'phone_code' => '+351'
        ]);
        Country::create([
            'name' => 'Puerto Rico',
            'prefix'    =>  'PR',
            'phone_code' => '+1'
        ]);
        Country::create([
            'name' => 'Rumania',
            'prefix'    =>  'RD',
            'phone_code' => '+40'
        ]);
        Country::create([
            'name' => 'Rusia',
            'prefix'    =>  'RU',
            'phone_code' => '+7'
        ]);
        Country::create([
            'name' => 'Samoa',
            'prefix'    =>  'RS',
            'phone_code' => '+685'
        ]);
        Country::create([
            'name' => 'Serbia',
            'prefix'    =>  'SM',
            'phone_code' => '+381'
        ]);
        Country::create([
            'name' => 'Singapur',
            'prefix'    =>  'SG',
            'phone_code' => '+65'
        ]);
        Country::create([
            'name' => 'Siria',
            'prefix'    =>  'SR',
            'phone_code' => '+963'
        ]);
        Country::create([
            'name' => 'Suecia',
            'prefix'    =>  'SI',
            'phone_code' => '+46'
        ]);
        Country::create([
            'name' => 'Suiza',
            'prefix'    =>  'SC',
            'phone_code' => '+41'
        ]);
        Country::create([
            'name' => 'Turquía',
            'prefix'    =>  'SZ',
            'phone_code' => '+90'
        ]);
        Country::create([
            'name' => 'Uganda',
            'prefix'    =>  'UG',
            'phone_code' => '+256'
        ]);
        Country::create([
            'name' => 'Uruguay',
            'prefix'    =>  'TU',
            'phone_code' => '+598'
        ]);
        Country::create([
            'name' => 'Vanuatu',
            'prefix'    =>  'UR',
            'phone_code' => '+678'
        ]);
        Country::create([
            'name' => 'Venezuela',
            'prefix'    =>  'VE',
            'phone_code' => '+58'
        ]);
        Country::create([
            'name' => 'Islas Vírgenes Británicas',
            'prefix'    =>  'VN',
            'phone_code' => '+1 284'
        ]);
        Country::create([
            'name' => 'Yemen',
            'prefix'    =>  'YM',
            'phone_code' => '+967'
        ]);
        Country::create([
            'name' => 'Channel Islands',
            'prefix'    =>  'CI',
            'phone_code' => ''
        ]);
        Country::create([
            'name' => 'República Checa',
            'prefix'    =>  'CZ',
            'phone_code' => '+420'
        ]);
        Country::create([
            'name' => 'Islas Caimán',
            'prefix'    =>  'GC',
            'phone_code' => '+1 345'
        ]);
        Country::create([
            'name' => 'Guernsey ',
            'prefix'    =>  'GU',
            'phone_code' => '+44'
        ]);
        Country::create([
            'name' => 'Países Bajos',
            'prefix'    =>  'HO',
            'phone_code' => '+31'
        ]);
        Country::create([
            'name' => 'Inglaterra',
            'prefix'    =>  'IN',
            'phone_code' => '+44'
        ]);
        Country::create([
            'name' => 'Reino Unido',
            'prefix'    =>  'RN',
            'phone_code' => '+44'
        ]);
        Country::create([
            'name' => 'Eslovenia',
            'prefix'    =>  'SI',
            'phone_code' => '+386'
        ]);
    }
}
