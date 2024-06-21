<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Area;
use App\Models\Budget;
use App\Models\Supplier;
use App\Models\SupplierContactType;

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




        DB::connection('mysql2')->statement('SET FOREIGN_KEY_CHECKS=1;');

        return response()->json([
            'success' => true,
            'data' => [
                'Datos de prueba creados exitosamente'
            ]
        ]);
    }
}
