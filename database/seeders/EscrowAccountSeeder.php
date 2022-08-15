<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EscrowAccount;

class EscrowAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EscrowAccount::create([
            'bank_id' => 1,
            'account_number' => '193-2437800-0-41',
            'cci_number' => '002-193-002437800-041-18',
            'currency_id' => '1',
            'beneficiary_name' => 'Intercambio Corfid – Fideicomiso Bill',
            'beneficiary_address' => 'Calle Monterosa 256, piso 5, oficina 501 Urb. Chacarilla del Estanque, Santiago de Surco',
            'document_type_id' => '2',
            'document_number' => '09446799',
            'active' => true,
            'corfid_id' => '3'
        ]);

        EscrowAccount::create([
            'bank_id' => 1,
            'account_number' => '193-2445594-1-78',
            'cci_number' => '002-193-002445594-178-11',
            'currency_id' => '2',
            'beneficiary_name' => 'Intercambio Corfid – Fideicomiso Bill',
            'beneficiary_address' => 'Calle Monterosa 256, piso 5, oficina 501 Urb. Chacarilla del Estanque, Santiago de Surco',
            'document_type_id' => '2',
            'document_number' => '09446799',
            'active' => true,
            'corfid_id' => '2'
        ]);

        EscrowAccount::create([
            'bank_id' => 2,
            'account_number' => '2003001519970',
            'cci_number' => '00320000300151997033',
            'currency_id' => '1',
            'beneficiary_name' => 'Intercambio CORFID – Fideicomiso Bill MN',
            'beneficiary_address' => 'Calle Monterosa 256, piso 5, oficina 501 Urb. Chacarilla del Estanque, Santiago de Surco',
            'document_type_id' => '2',
            'document_number' => '0015846762',
            'active' => true,
            'corfid_id' => '5'
        ]);

        EscrowAccount::create([
            'bank_id' => 2,
            'account_number' => '2003001519961',
            'cci_number' => '00320000300151996132',
            'currency_id' => '2',
            'beneficiary_name' => 'Intercambio CORFID – Fideicomiso Bill ME',
            'beneficiary_address' => 'Calle Monterosa 256, piso 5, oficina 501 Urb. Chacarilla del Estanque, Santiago de Surco',
            'document_type_id' => '2',
            'document_number' => '0015846762',
            'active' => true,
            'corfid_id' => '6'
        ]);

        EscrowAccount::create([
            'bank_id' => 3,
            'account_number' => '0011-0436-01-00002710',
            'cci_number' => '011-436-000100002710-30',
            'currency_id' => '1',
            'beneficiary_name' => 'Intercambio CORFID – Fideicomiso Bill MN',
            'beneficiary_address' => 'Calle Monterosa 256, piso 5, oficina 501 Urb. Chacarilla del Estanque, Santiago de Surco',
            'document_type_id' => '2',
            'document_number' => '0015846762',
            'active' => true,
            'corfid_id' => '7'
        ]);

        EscrowAccount::create([
            'bank_id' => 3,
            'account_number' => '0011-0436-01-00002729',
            'cci_number' => '011-436-000100002729-30',
            'currency_id' => '2',
            'beneficiary_name' => 'Intercambio CORFID – Fideicomiso Bill ME',
            'beneficiary_address' => 'Calle Monterosa 256, piso 5, oficina 501 Urb. Chacarilla del Estanque, Santiago de Surco',
            'document_type_id' => '2',
            'document_number' => '0015846762',
            'active' => true,
            'corfid_id' => '8'
        ]);
    }
}
