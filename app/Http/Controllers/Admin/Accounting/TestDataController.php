<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Area;
use App\Models\Budget;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\SupplierBankAccount;
use App\Models\SupplierContactType;
use App\Models\BusinessBankAccount;

class TestDataController extends Controller
{
    ////Adding data
    public function test_data(Request $request) {

        DB::connection('mysql2')->statement('SET FOREIGN_KEY_CHECKS=0;');
        Area::truncate();

        Area::create(['name' => 'Operaciones', 'code' => 'A001']);
        Area::create(['name' => 'Comercial', 'code' => 'A002']);
        Area::create(['name' => 'Administración', 'code' => 'A003']);
        Area::create(['name' => 'RRHH', 'code' => 'A004']);
        Area::create(['name' => 'Tecnología', 'code' => 'A005']);

        Budget::truncate();

        Budget::create([
            'area_id' => 1,
            'code' => "B001",
            'description' => "Presupuesto de personal",
            'period' => 2024,
            'initial_budget' => 45780
        ]); 

        Budget::create([
            'area_id' => 1,
            'code' => "B002",
            'description' => "Servicios necesarios para operación",
            'period' => 2024,
            'initial_budget' => 62600
        ]);

        Budget::create([
            'area_id' => 2,
            'code' => "B003",
            'description' => "Gastos Comerciales",
            'period' => 2024,
            'initial_budget' => 62600
        ]);

        Supplier::truncate();

        Supplier::create([
            'name' => "DATATEC S.A.",
            'document_type_id' => 1,
            'document_number' => "20336260702",
            'email' => null,
            'phone' => "613 4444",
            'detraction_account' => "00006001173",
            'address' => 'Av. Jorge Basadre nro 347 int 801 Urb Orrantia',
            'district_id' => 1278,
            'country_id' => 375
        ]); 

        Supplier::create([
            'name' => "CORFID S.A.",
            'document_type_id' => 1,
            'document_number' => "20336260123",
            'email' => null,
            'phone' => "613 2222",
            'detraction_account' => "00006003345",
            'address' => 'Av. Jorge Basadre nro 347 int 801 Urb Orrantia',
            'district_id' => 1278,
            'country_id' => 375
        ]); 

        Supplier::create([
            'name' => "GRUPO EQUILIBRIO S.A.C.",
            'document_type_id' => 1,
            'document_number' => "20605969730",
            'email' => null,
            'phone' => "6101111",
            'detraction_account' => null,
            'address' => 'Av. rebagliate 123',
            'district_id' => 1278,
            'country_id' => 375
        ]); 


        SupplierContactType::truncate();

        SupplierContactType::create(['name' => "Administrativo"]);
        SupplierContactType::create(['name' => "Comercial"]);
        SupplierContactType::create(['name' => "Técnico"]);
        SupplierContactType::create(['name' => "Legal"]);
        SupplierContactType::create(['name' => "Gerencia"]);

        SupplierBankAccount::truncate();
        SupplierBankAccount::create([
            'supplier_id' => 1,
            'bank_id' => 1,
            'account_number' => '193232314143',
            'cci_number' => "00123456789012345678",
            'currency_id' => '1',
            'account_type_id' => "1",
            'main' => "0"
        ]);

        SupplierBankAccount::create([
            'supplier_id' => 1,
            'bank_id' => 1,
            'account_number' => '194848845845',
            'cci_number' => "00123456789087654321",
            'currency_id' => '2',
            'account_type_id' => "1",
            'main' => "0"
        ]);

        Service::truncate();
        Service::create([
            'budget_id' => 2,
            'supplier_id' => 1,
            'name' => 'Tipo de Cambio Datatec',
            'description' => 'Servicio lectura de Tipo de cambio',
            'amount' => '12000',
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 3,
            'supplier_id' => 3,
            'name' => 'Administración RRSS',
            'description' => 'Servicio de Administración de Redes Sociales',
            'amount' => 10800,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        BusinessBankAccount::truncate();
        BusinessBankAccount::create([
            'bank_id' => 1,
            'alias' => 'BCP Soles',
            'account_number' => '1942385690077',
            'cci_number' => "00219400238569007796",
            'currency_id' => '1',
            'account_type_id' => "1",
            'status' => 'Activo'
        ]);

        BusinessBankAccount::create([
            'bank_id' => 1,
            'alias' => 'BCP Dólares',
            'account_number' => '1942366597128',
            'cci_number' => "00219400236659712891",
            'currency_id' => '2',
            'account_type_id' => "1",
            'status' => 'Activo'
        ]);

        DB::connection('mysql2')->statement('SET FOREIGN_KEY_CHECKS=1;');

        return response()->json([
            'success' => true,
            'data' => [
                'Datos de prueba creados exitosamente'
            ]
        ]);
    }
}
