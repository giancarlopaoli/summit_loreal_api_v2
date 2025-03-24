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
use App\Models\RefundBankAccount;
use App\Models\DetractionType;

class TestDataController extends Controller
{
    ////Adding data
    public function test_data(Request $request) {

        DB::connection('mysql2')->statement('SET FOREIGN_KEY_CHECKS=0;');
        Area::truncate();

        Area::create(['name' => 'Ventas', 'code' => 'VT01']);
        Area::create(['name' => 'Costo de Ventas', 'code' => 'VT02']);
        Area::create(['name' => 'Gastos Administrativos', 'code' => 'ADM01']);
        Area::create(['name' => 'Gastos de Ventas', 'code' => 'VT03']);
        Area::create(['name' => 'Otros Ingresos', 'code' => 'IN01']);
        Area::create(['name' => 'Ingresos Financieros', 'code' => 'IN02']);
        Area::create(['name' => 'Gastos Financieros', 'code' => 'GF01']);
        Area::create(['name' => 'Diferencia de Cambio', 'code' => 'TC01']);

        Budget::truncate();
        Budget::create([
            'area_id' => 2,
            'code' => "7032100",
            'description' => "Servicios-Local-Terceros",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 2,
            'code' => "6329100",
            'description' => "Otros Servicios de Asesoria",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 2,
            'code' => "6391100",
            'description' => "Gastos bancarios",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 2,
            'code' => "6393100",
            'description' => "Otros servicios prestados por Terceros",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 2,
            'code' => "6412101",
            'description' => "ITF FIDEICOMISO",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 2,
            'code' => "6861900",
            'description' => "Amort. Costo-Otros activos intangibles",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6211100",
            'description' => "Sueldos y salarios",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6211101",
            'description' => "Sueldos y salarios practicante",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6214100",
            'description' => "Gratificaciones",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6215100",
            'description' => "Vacaciones",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6221100",
            'description' => "Otras remuneraciones",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6271100",
            'description' => "Régimen de prestaciones de salud",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6291100",
            'description' => "Compensación por tiempo de servicio",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6314100",
            'description' => "Alimentación",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6315100",
            'description' => "Otros gastos de viaje",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6322100",
            'description' => "Asesoría y consult. Legal y tributaria",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6323100",
            'description' => "Auditoría y contable",
            'period' => 2025,
            'status' => 'Activo'
        ]);

        Budget::create([
            'area_id' => 3,
            'code' => "6356100",
            'description' => "Alquileres Equipos diversos",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6364100",
            'description' => "Teléfono",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6393101",
            'description' => "Otros servicios no domiciliados",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6412100",
            'description' => "Impuesto a las transacciones financieras",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6443100",
            'description' => "Otros Tributos",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6452100",
            'description' => "intereses - fraccion. deuda tributaria",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6511100",
            'description' => "Seguros",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6531100",
            'description' => "Suscripciones",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6541100",
            'description' => "Licencias y derechos de vigencia",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6561102",
            'description' => "Utiles de Oficina",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6561104",
            'description' => "Estacionamiento / Combustible",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6592100",
            'description' => "Sanciones administrativas",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6593100",
            'description' => "Otros Gastos",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6593102",
            'description' => "Ajuste por Redondeo",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 3,
            'code' => "6841500",
            'description' => "Depr. Prop. Planta-Costo-Equipos diversos",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 4,
            'code' => "6212100",
            'description' => "Comisiones",
            'period' => 2025,
            'status' => 'Activo'
        ]); 

        Budget::create([
            'area_id' => 4,
            'code' => "6371100",
            'description' => "Publicidad",
            'period' => 2025,
            'status' => 'Activo'
        ]);

        Supplier::truncate();

        Supplier::create([
            'name' => "Amazon Web Services, Inc.",
            'document_type_id' => 11,
            'document_number' => "91-1646860",
            'email' => "",
            'phone' => "",
            'address' => "410 Terry Ave North Seattle, WA 98109-5210, US",
            'district_id' => NULL,
            'country_id' => 277,
            'apply_detraction' => "No",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "ASOCIACION DE EMPRESAS INMOBILIARIAS DEL PERU - ASEI",
            'document_type_id' => 2,
            'document_number' => "20552213832",
            'email' => "facturaelectronica@asei.com.pe",
            'phone' => "989133571",
            'address' => "Av. Alberto del Campo Nro. 411 Urb. Campo de Polo",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00005266297",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "ASOCIACION DE EXPORTADORES - ADEX",
            'document_type_id' => 2,
            'document_number' => "20100365341",
            'email' => "",
            'phone' => "01 618 3333",
            'address' => "AV. JAVIER PRADO ESTE NRO. 2875, SAN BORJA LIMA LIMA",
            'district_id' => 1277,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00005017939",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "BIZ PERU E.I.R.L.",
            'document_type_id' => 2,
            'document_number' => "20525544894",
            'email' => "",
            'phone' => "",
            'address' => "CAL. MALDONADO ALFREDO 654 URB. CERCADO DE LIMA DPTO. 602B",
            'district_id' => 1268,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "CAMARA DE COMERCIO DE LIMA",
            'document_type_id' => 2,
            'document_number' => "20101266819",
            'email' => "",
            'phone' => "219-1600",
            'address' => "Av. Giuseppe Garibaldi N° 396-Jesús Maria-Lima-Lima",
            'district_id' => 1260,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "CAPECO",
            'document_type_id' => 2,
            'document_number' => "20100084334",
            'email' => "informes@capeco.org",
            'phone' => "01 2302700",
            'address' => "AV. VICTOR ANDRES BELAUNDE NRO. 147 INT. 401 (VIA PRINCIPAL NRO 155 - CUARTO PISO)",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "CONCERTUM GESTION PROFESIONAL DE INTERESES S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20513486139",
            'email' => "",
            'phone' => "",
            'address' => "CAL. LOS ANGELES 340 FRTE AV AREQUIPA Y AV SANTA CRUZ, MIRAFLORES - LIMA - LIMA",
            'district_id' => 1269,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00005039134",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "CORFID CORP.FIDUCIARIA S.A.",
            'document_type_id' => 2,
            'document_number' => "20556216089",
            'email' => "facturacion.electronica@data.com.pe",
            'phone' => "",
            'address' => "Calle Monte Rosa 256 Piso 5, Santiago de Surco",
            'district_id' => 1287,
            'country_id' => 375,
            'apply_detraction' => "No",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "DATOS TECNICOS S.A.",
            'document_type_id' => 2,
            'document_number' => "20336260702",
            'email' => "administracion@datatec.com.pe",
            'phone' => "",
            'address' => "Av. Jorge Basadre 347 int 801 Urb. Orrantia,  San Isidro",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00006001173",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "ECOS & FLORES CONSULTING PARTNER E.I.R.L",
            'document_type_id' => 2,
            'document_number' => "20601032164",
            'email' => "paul.ecos@efconsultores.com",
            'phone' => "01 3036411",
            'address' => "Av. Alfredo Mendiola 7987 Urb. Pro, Los Olivos",
            'district_id' => 1264,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00048008887",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "FORTA FINANCE S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20604822255",
            'email' => "javier.pineda@forta.com.pe",
            'phone' => "",
            'address' => "AV. SAN BORJA SUR 362 URB. SAN BORJA SUR DPTO. 402, San Borja",
            'district_id' => 1277,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00048103804",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "GAMA LEGAL S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20611207086",
            'email' => "marycielo@gama.pe",
            'phone' => "919479042",
            'address' => "AV. JAVIER PRADO ESTE 560 INT. 2302, SAN ISIDRO - LIMA - LIMA",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00061200347",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "Google LLC",
            'document_type_id' => 11,
            'document_number' => "77-0493581",
            'email' => "",
            'phone' => "",
            'address' => "1600 Amphitheatre Pkwy Mountain View, CA 94043, US",
            'district_id' => NULL,
            'country_id' => 277,
            'apply_detraction' => "No",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "GRUPO EQUILIBRIO S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20605969730",
            'email' => "",
            'phone' => "",
            'address' => "AV. DE LOS PRECURSORES 331 URB. VALLE HERMOSO DPTO. 402",
            'district_id' => 1287,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00058376396",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "INMOBILIARIA SWISS CAPITALS S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20547720254",
            'email' => "felectronica@swisscapitals.com",
            'phone' => "01 683-2867",
            'address' => "AV. ALFREDO BENAVIDES N°1944 INT 1001 (PISO 10 OFICINA 1001), Miraflores",
            'district_id' => 1269,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "LA TIENDA BC S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20548285357",
            'email' => "",
            'phone' => "",
            'address' => "AV. 2 DE MAYO 961 URB. ORRANTIA, DPTO. 202 LIMA-LIMA-SAN ISIDRO",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00046045254",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "M Y G ASESORES SOCIEDAD ANONIMA CERRADA",
            'document_type_id' => 2,
            'document_number' => "20516643375",
            'email' => "mdelmar@mygasesores.com.pe",
            'phone' => "",
            'address' => "AV. SALAVERRY 2900",
            'district_id' => 1267,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00006002455",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "Mailchimp - The Rocket Science Group, LLC",
            'document_type_id' => 11,
            'document_number' => "58-2554149",
            'email' => "",
            'phone' => "",
            'address' => "LLC 675 Ponce de Leon Ave NE, Atlanta, GA 30308",
            'district_id' => NULL,
            'country_id' => 277,
            'apply_detraction' => "No",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "NOTARIA ROSALES SEPULVEDA FERMIN",
            'document_type_id' => 2,
            'document_number' => "10095386828",
            'email' => "",
            'phone' => "01 2003700",
            'address' => "AV. JUAN DE ARONA N° 707, San Isidro",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "NUBEFACT SA",
            'document_type_id' => 2,
            'document_number' => "20600695771",
            'email' => "sistemas@nubefact.com",
            'phone' => "",
            'address' => "CALLE LIBERTAD 176 OF 211, Lima - Lima - Miraflores",
            'district_id' => 1269,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "OLVA COURIER S.A.C",
            'document_type_id' => 2,
            'document_number' => "20100686814",
            'email' => "",
            'phone' => "",
            'address' => "AV GENERAL ALVAREZ DE ARENALES NRO 1775 LINCE-LIMA",
            'district_id' => 1263,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "PACÍFICO COMPAÑÍA DE SEGUROS Y REASEGUROS",
            'document_type_id' => 2,
            'document_number' => "20332970411",
            'email' => "",
            'phone' => "",
            'address' => "AV JUAN DE ARONA NRO 830 - LIMA - LIMA - SAN ISIDRO",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "PAOLI ROSAS GIANCARLO VITTORIO",
            'document_type_id' => 2,
            'document_number' => "10428675091",
            'email' => "",
            'phone' => "",
            'address' => "CAL. JOAQUIN CAPELO 366 URB. SANTA CRUZ DPTO. 406, MIRAFLORES - LIMA - LIMA",
            'district_id' => 1269,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00003378160",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "PHASE CONSULTORES S.A.C. - PHASE S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20566018641",
            'email' => "",
            'phone' => "",
            'address' => "CAL. LOS LIBERTADORES 105 DPTO. 82, San Isidro",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00046260570",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "RISK GLOBAL GROUP PERU S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20609785421",
            'email' => "",
            'phone' => "",
            'address' => "URB. MANTELLINI MZA. C1 LOTE. 3 DPTO. 504, CHORRILLOS - LIMA - LIMA",
            'district_id' => 1255,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00046349857",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "SOCIEDAD NACIONAL DE INDUSTRIAS",
            'document_type_id' => 2,
            'document_number' => "20113439964",
            'email' => "",
            'phone' => "",
            'address' => "Calle Los Laureles 365 San Isidro",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "TELEFONICA DEL PERU SAA",
            'document_type_id' => 2,
            'document_number' => "20100017491",
            'email' => "",
            'phone' => "",
            'address' => "Jirón Domingo Martínez Luján 1130 - LIMA-LIMA-SURQUILLO",
            'district_id' => 1288,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00000334499",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "VSD INVERSIONES S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20517030571",
            'email' => "administracion@123venta.com",
            'phone' => "",
            'address' => "AV. CAMINO REAL 111 URB. EL ROSARIO INT. 1002 ALT. CDRA. 2 AV.JORGE BASADRE",
            'district_id' => 1278,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00026004764",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "WWIN PLANNERS SOCIEDAD ANONIMA CERRADA",
            'document_type_id' => 2,
            'document_number' => "20602103499",
            'email' => "",
            'phone' => "",
            'address' => "CAL. BREA Y PARIÑAS 102 URB. TAMBO DE MONTERRICO DPTO. 1301 SANTIAGO DE SURCO - LIMA - LIMA",
            'district_id' => 1287,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00093017749",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "Zoom Video Communications Inc.",
            'document_type_id' => 11,
            'document_number' => "61-1648780",
            'email' => "",
            'phone' => "",
            'address' => "55 Almaden Blvd, 6th Floor San Jose, CA 95113",
            'district_id' => NULL,
            'country_id' => 277,
            'apply_detraction' => "No",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "AMERICA MOVIL PERU S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20505203152",
            'email' => "",
            'phone' => "",
            'address' => "Av. Nicolás Arriola 480 Urb. Santa Catalina",
            'district_id' => 1262,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

        Supplier::create([
            'name' => "SUPERINTENDENCIA NACIONAL DE REGISTROS PÚBLICOS - SUNARP",
            'document_type_id' => 2,
            'document_number' => "20267073580",
            'email' => "",
            'phone' => "",
            'address' => "AV. PRIMAVERA NRO. 1878",
            'district_id' => 1287,
            'country_id' => 375,
            'apply_detraction' => "No",
            'detraction_account' => "",
            'status' => 'Activo'
        ]); 

         Supplier::create([
            'name' => "CAP IP CONSULTING S.A.C.",
            'document_type_id' => 2,
            'document_number' => "20604352119",
            'email' => "",
            'phone' => "",
            'address' => "CAL. LUIS LARCO 373 DPTO. C4",
            'district_id' => 681,
            'country_id' => 375,
            'apply_detraction' => "Si",
            'detraction_account' => "00060133131",
            'status' => 'Activo'
        ]);

        
        SupplierContactType::truncate();

        SupplierContactType::create(['name' => "Administrativo"]);
        SupplierContactType::create(['name' => "Comercial"]);
        SupplierContactType::create(['name' => "Técnico"]);
        SupplierContactType::create(['name' => "Legal"]);
        SupplierContactType::create(['name' => "Gerencia"]);

        /*
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
        ]);*/

        Service::truncate();
        Service::create([
            'budget_id' => 20,
            'supplier_id' => 1,
            'name' => "Servicio Amazon Web Services",
            'description' => "Servicio de alquiler de infraestructura en la nube de Amazon",
            'amount' => 60,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 4,
            'supplier_id' => 4,
            'name' => "Servicio asesoramiento comercial",
            'description' => "Pago comisiones como ejecutivo free lance",
            'amount' => 3000,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 25,
            'supplier_id' => 5,
            'name' => "Membresía anual CCL",
            'description' => "Membresía a la Cámara de Comercio de Lima",
            'amount' => 2484,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Anual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 3,
            'supplier_id' => 8,
            'name' => "Servicio ADMINISTRACION DEL FIDEICOMISO",
            'description' => "Servicio de administración de las cuentas de fideicomiso Bill",
            'amount' => 14160,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 2,
            'supplier_id' => 9,
            'name' => "SERVICIO SMF DATATEC",
            'description' => "Servicio de provisión de TC Datatec",
            'amount' => 16992,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 17,
            'supplier_id' => 10,
            'name' => "Servicios contables",
            'description' => "SERVICIOS PROFESIONALES DE OUTSORCING CONTABLE",
            'amount' => 15340,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 4,
            'supplier_id' => 11,
            'name' => "Servicio asesoramiento comercial",
            'description' => "Pago comisiones como ejecutivo free lance",
            'amount' => 14160,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 2,
            'supplier_id' => 11,
            'name' => "Servicio Gestión Operativa",
            'description' => "Servicio Gerente de Operaciones",
            'amount' => 48000,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 16,
            'supplier_id' => 12,
            'name' => "Servicios Legales",
            'description' => "Servicios legales",
            'amount' => 4248,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Otro',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 20,
            'supplier_id' => 13,
            'name' => "Google Workspace",
            'description' => "Servicio de correos electrónicos de Google",
            'amount' => 1320,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 2,
            'supplier_id' => 14,
            'name' => "Servicio de gestión de redes sociales",
            'description' => "",
            'amount' => 12744,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 2,
            'supplier_id' => 14,
            'name' => "Servicio inversión pauta",
            'description' => "Inversión en pauta en redes sociales",
            'amount' => 6000,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 25,
            'supplier_id' => 15,
            'name' => "Oficina Virtual",
            'description' => "Membresía SeedSpace - Alquiler oficina virtual",
            'amount' => 7080,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 4,
            'supplier_id' => 17,
            'name' => "Servicio asesoramiento comercial",
            'description' => "Pago comisiones como ejecutivo free lance",
            'amount' => 36000,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 20,
            'supplier_id' => 18,
            'name' => "Mailing",
            'description' => "Servicio envío Mailing",
            'amount' => 907.5,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 24,
            'supplier_id' => 22,
            'name' => "SEGURO FOLA",
            'description' => "Seguro para practicantes",
            'amount' => 1680,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 2,
            'supplier_id' => 24,
            'name' => "Elaboración reportes económicos",
            'description' => "Servicio elaboración de reportes económicos que se envian a clientes",
            'amount' => 38940,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 2,
            'supplier_id' => 25,
            'name' => "Servicio Listas LAFTP",
            'description' => "Servicios para Due Dilegence y conoce a tu cliente",
            'amount' => 3067.92,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 25,
            'supplier_id' => 26,
            'name' => "Membresía SIN",
            'description' => "Membresía a la Sociedad Nacional de Industrias",
            'amount' => 138,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 25,
            'supplier_id' => 27,
            'name' => "Servicios Cloud - Huawei",
            'description' => "Servicios en la Nube Huawei - Telefonica",
            'amount' => 6000,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 20,
            'supplier_id' => 30,
            'name' => "Membresía Zoom",
            'description' => "Servicio de conferencias premium",
            'amount' => 180,
            'currency_id' => 2,
            'exchange_rate' => 3.85,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 4,
            'supplier_id' => 20,
            'name' => "Facturación electrónica",
            'description' => "Servicio de generación de facturas electrónicas",
            'amount' => 1800,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Mensual',
            'status' => 'Activo'
        ]); 

        Service::create([
            'budget_id' => 4,
            'supplier_id' => 32,
            'name' => "Solicitudes Registrales",
            'description' => "Solicitudes de Vigencias de poderes, partidas registrales, copia literal",
            'amount' => 500,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Otro',
            'status' => 'Activo'
        ]);

        Service::create([
            'budget_id' => 4,
            'supplier_id' => 32,
            'name' => "Asesoría en temas de Marca - Indecopi",
            'description' => "Asesoría en temas de MArcoa - Indecopi",
            'amount' => 2000,
            'currency_id' => 1,
            'exchange_rate' => null,
            'frequency' => 'Otro',
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


        RefundBankAccount::truncate();
        RefundBankAccount::create([
            'user_id' => 483,
            'bank_id' => 1,
            'account_number' => '19319941567095',
            'cci_number' => "00219311994156709518",
            'currency_id' => '1',
            'account_type_id' => "1",
            'status' => 'Activo'
        ]);

        RefundBankAccount::create([
            'user_id' => 483,
            'bank_id' => 2,
            'account_number' => '19432363638142',
            'cci_number' => "00219413236363814293",
            'currency_id' => '2',
            'account_type_id' => "1",
            'status' => 'Activo'
        ]);


        DetractionType::truncate();

        DetractionType::create([
            "code" => "001",
            "name" => "Azúcar"
        ]);
        DetractionType::create([
            "code" => "003",
            "name" => "Alcohol etílico"
        ]);
        DetractionType::create([
            "code" => "004",
            "name" => "Recursos hidrobiológicos"
        ]);
        DetractionType::create([
            "code" => "005",
            "name" => "Maíz amarillo duro"
        ]);
        DetractionType::create([
            "code" => "006",
            "name" => "Algodón"
        ]);
        DetractionType::create([
            "code" => "007",
            "name" => "Caña de azúcar"
        ]);
        DetractionType::create([
            "code" => "008",
            "name" => "Madera"
        ]);
        DetractionType::create([
            "code" => "009",
            "name" => "Arena y piedra."
        ]);
        DetractionType::create([
            "code" => "010",
            "name" => "Residuos, subproductos, desechos, recortes y desperdicios"
        ]);
        DetractionType::create([
            "code" => "011",
            "name" => "Bienes del inciso A) del Apéndice I de la Ley del IGV"
        ]);
        DetractionType::create([
            "code" => "012",
            "name" => "Intermediación laboral y tercerización"
        ]);
        DetractionType::create([
            "code" => "013",
            "name" => "Animales vivos"
        ]);
        DetractionType::create([
            "code" => "014",
            "name" => "Carnes y despojos comestibles"
        ]);
        DetractionType::create([
            "code" => "015",
            "name" => "Abonos, cueros y pieles de origen animal"
        ]);
        DetractionType::create([
            "code" => "016",
            "name" => "Aceite de pescado."
        ]);
        DetractionType::create([
            "code" => "017",
            "name" => "Harina, polvo y “pellets” de pescado, crustáceos, moluscos y demás invertebrados acuáticos"
        ]);
        DetractionType::create([
            "code" => "018",
            "name" => "Embarcaciones pesqueras"
        ]);
        DetractionType::create([
            "code" => "019",
            "name" => "Arrendamiento de bienes mueble"
        ]);
        DetractionType::create([
            "code" => "020",
            "name" => "Mantenimiento y reparación de bienes muebles"
        ]);
        DetractionType::create([
            "code" => "021",
            "name" => "Movimiento de carga"
        ]);
        DetractionType::create([
            "code" => "022",
            "name" => "Otros servicios empresariales"
        ]);
        DetractionType::create([
            "code" => "023",
            "name" => "Leche"
        ]);
        DetractionType::create([
            "code" => "024",
            "name" => "Comisión mercantil"
        ]);
        DetractionType::create([
            "code" => "025",
            "name" => "Fabricación de bienes por encargo"
        ]);
        DetractionType::create([
            "code" => "026",
            "name" => "Servicio de transporte de personas"
        ]);
        DetractionType::create([
            "code" => "029",
            "name" => "Algodòn en rama sin desmontar"
        ]);
        DetractionType::create([
            "code" => "030",
            "name" => "Contratos de construcción"
        ]);
        DetractionType::create([
            "code" => "031",
            "name" => "Oro gravado con el IGV"
        ]);
        DetractionType::create([
            "code" => "032",
            "name" => "Páprika y otros frutos de los géneros capsicum o pimienta"
        ]);
        DetractionType::create([
            "code" => "033",
            "name" => "Espárragos"
        ]);
        DetractionType::create([
            "code" => "034",
            "name" => "Minerales metálicos no auríferos"
        ]);
        DetractionType::create([
            "code" => "035",
            "name" => "Bienes exonerados del IGV"
        ]);
        DetractionType::create([
            "code" => "036",
            "name" => "Oro y demás minerales metálicos exonerados del IGV"
        ]);
        DetractionType::create([
            "code" => "037",
            "name" => "Demás servicios gravados con el IGV"
        ]);
        DetractionType::create([
            "code" => "039",
            "name" => "Minerales no metálicos"
        ]);
        DetractionType::create([
            "code" => "040",
            "name" => "Bien inmueble gravado con IGV"
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
