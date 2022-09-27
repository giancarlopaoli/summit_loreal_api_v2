<?php

namespace App\Http\Controllers\Register;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentType;
use App\Models\Client;
use App\Models\Bank;
use App\Models\EconomicActivity;
use App\Models\AccountType;
use App\Models\DniData;
use App\Models\RucData;
use App\Models\Department;
use App\Models\Province;
use App\Models\District;
use App\Models\Country;
use App\Models\Profession;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    //Document Types for Person clients
    public function document_types(Request $request) {

        $document_types = DocumentType::wherein('id', [2,3])
            ->select('id','name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'document_types' => $document_types
            ]
        ]);
    }

    //Document Types for Person
    public function representatives_document_types(Request $request) {

        $document_types = DocumentType::wherein('id', [1,2,3,6,8])
            ->select('id','name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'document_types' => $document_types
            ]
        ]);
    }

    public function bank_list() {

        $banks = Bank::where('active', true)
            ->select('id','name','shortname')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'document_types' => $banks
            ]
        ]);
    }

    public function economic_activities() {

        $economic_activities = EconomicActivity::select('id','name','code')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'document_types' => $economic_activities
            ]
        ]);
    }    

    public function account_types() {

        $account_types = AccountType::where('active', true)
            ->select('id','name','shortname','size')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'document_types' => $account_types
            ]
        ]);
    }

    public function departments() {

        $departments = Department::select('id','name')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'departments' => $departments
            ]
        ]);
    }

    public function provinces(Request $request) {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}
        

        $provinces = Province::select('id','name','department_id')->where('department_id', $request->department_id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'provinces' => $provinces
            ]
        ]);
    }

    public function districts(Request $request) {
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|exists:provinces,id',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}
        

        $districts = District::select('id','name','province_id','ubigeo')->where('province_id', $request->province_id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'districts' => $districts
            ]
        ]);
    }

    public function countries(Request $request) {

        $countries = Country::select('id','name','prefix')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'countries' => $countries
            ]
        ]);
    }

    public function professions(Request $request) {

        $professions = Profession::select('id','name')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'professions' => $professions
            ]
        ]);
    }

    public function validate_dni(Request $request) {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|size:8',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}
  
        $consulta = DniData::where('dni', $request->dni)->first();

        if(!is_null($consulta)){
            $dni = array(
                "dni" => (string) $request->dni,
                "name" => $consulta->name,
                "last_name" => $consulta->last_name,
                "mothers_name" => $consulta->mothers_name,
                "birthdate" => null
            );

            return response()->json([
                'success' => true,
                'source' => '1',
                'data' => [
                    'dni' => $dni
                ]
            ]);
        }

        // Utilizando apiperu.dev
        $consulta = Http::withToken(env('APIPERUDEV_TOKEN'))->get(env('APIPERUDEV_URL') . "api/dni/" . $request->dni);

        $rpta_json = json_decode($consulta);

        if(is_object($rpta_json)){
            if($rpta_json->success){
                $dni = array(
                    "dni" => $rpta_json->data->numero,
                    "name" => $rpta_json->data->nombres,
                    "last_name" => $rpta_json->data->apellido_paterno,
                    "mothers_name" => $rpta_json->data->apellido_materno,
                    "birthdate" => isset($rpta_json->data->fecha_nacimiento) ? $rpta_json->data->fecha_nacimiento : null
                );

                try {
                    $save_response = RegisterController::save_dni_db($dni);
                } catch (\Exception $e) {
                    logger('Guardando persona en BDD de validación de identidad: RegisterController@save_dni_db', ["error" => $e]);
                }

                return response()->json([
                    'success' => true,
                    'source' => '2',
                    'data' => [
                        'dni' => $dni
                    ]
                ]);
            }
        }

        // Utilizando https://apis.net.pe/
        $consulta = Http::withToken(env('APISNET_TOKEN'))->get(env('APISNET_URL') . "v1/dni?numero=". $request->dni);

        $rpta_json = json_decode($consulta);

        if(is_object($rpta_json)){
            if(isset($rpta_json->nombre)){
                $dni = array(
                    "dni" => $rpta_json->numeroDocumento,
                    "name" => $rpta_json->nombres,
                    "last_name" => $rpta_json->apellidoPaterno,
                    "mothers_name" => $rpta_json->apellidoMaterno,
                    "birthdate" => isset($rpta_json->fecha_nacimiento) ? $rpta_json->fecha_nacimiento : null
                );

                try {
                    $save_response = RegisterController::save_dni_db($dni);
                } catch (\Exception $e) {
                    logger('Guardando persona en BDD de validación de identidad: RegisterController@save_dni_db', ["error" => $e]);
                }

                return response()->json([
                    'success' => true,
                    'source' => '3',
                    'data' => $dni
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'errors' => [
                'Error en el servicio de validación de identidad'
            ]
        ]);
    }

     public function save_dni_db($persona) {

        $consulta = DniData::where('dni', $persona['dni'])->first();

        if(is_null($consulta)){

            $insert = DniData::create([
                'dni' => Str::substr("0".$persona['dni'], -8 ),
                'name' => $persona['name'],
                'last_name' => $persona['last_name'],
                'mothers_name' => $persona['mothers_name']
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }


    public function validate_ruc(Request $request) {
        $val = Validator::make($request->all(), [
            'ruc' => 'required|string|size:11',
        ]);
        if($val->fails()) return response()->json($val->messages());


        $tipoempresa = Str::substr($request->ruc, 0, 2);

        if ($tipoempresa != 10 and $tipoempresa != 20 and $tipoempresa != 15) {
            return response()->json([
                'ruc' => array('El nro de ruc debe empezar con 10, 15 o 20')
            ]);
        }

        $consulta = RucData::where('ruc', $request->ruc)->first();

        if(!is_null($consulta)){
            $ruc = array(
                "ruc" => $consulta->ruc,
                "business" => $consulta->business,
                "tradename" => $consulta->tradename,
                "address" => $consulta->address,
                "ubigeo" => null
            );

            try {
                $distrito = trim(Str::substr($ruc['direccion'], strrpos($ruc['direccion'], "-") + 1, Str::length($ruc['direccion']) - strrpos($ruc['direccion'], "-")));
                $nuevaDir = Str::substr($ruc['direccion'],0, strrpos($ruc['direccion'], "-") -1 );

                $provincia = trim(Str::substr($nuevaDir, strrpos($nuevaDir, "-") + 1, Str::length($nuevaDir) - strrpos($nuevaDir, "-")));

                $provinciaDB = DB::table('Provincia')->where('Descripcion', $provincia)->first();
                
                if ( !is_null($provinciaDB)){
                    $provinciaId = $provinciaDB->ProvinciaId;
                    $ubigeo = DB::table('Distrito')->where('ProvinciaId', $provinciaId)->where('Descripcion', $distrito)->first()->UbigeoId;

                    $ubigeoarr = array(
                        Str::substr($ubigeo,0,2),
                        Str::substr($ubigeo,0,4),
                        $ubigeo
                    );
                    $ruc['ubigeo'] = $ubigeoarr;
                }
                    
            } catch (\Exception $e) {
                logger('Extrayendo Ubigeo: RegisterController@validaRUC', ["error" => $e]);
            }

            return response()->json([
                'success' => true,
                'source' => '1',
                'data' => [
                    'ruc' => $ruc
                ]
            ]);
        }

        
        // Utilizando peruapis.net.pe
        $consulta = Http::withToken(env('PERUAPISTOKEN'))->post(env('PERUAPISURL') . "/ruc", ['document' => $request->ruc]);

        $rpta_json = json_decode($consulta);

        if(is_object($rpta_json)){
            if($rpta_json->success){
                $ruc = array(
                    "ruc" => $rpta_json->data->ruc,
                    "business" => $rpta_json->data->name,
                    "tradename" => isset($rpta_json->data->commercial_name) ? $rpta_json->data->commercial_name : "",
                    "address" => isset($rpta_json->data->address) ? $rpta_json->data->address : "",
                    "ubigeo" => isset($rpta_json->data->location) ? $rpta_json->data->location : null
                );

                try {
                    $save_response = RegisterController::save_ruc_db($ruc);
                } catch (\Exception $e) {
                    logger('Guardando empresa en BDD de validación de identidad: RegisterController@save_ruc_db', ["error" => $e]);
                }

                return response()->json([
                    'success' => true,
                    'source' => '2',
                    'data' => [
                        'ruc' => $ruc
                    ]
                ]);
            }
        }


        // Utilizando apiperu.dev
        $consulta = Http::withToken(env('APIPERUDEV_TOKEN'))->get(env('APIPERUDEV_URL') . "api/ruc/" . $request->ruc);

        $rpta_json = json_decode($consulta);

        if(is_object($rpta_json)){
            if($rpta_json->success){
                $ruc = array(
                    "ruc" => $rpta_json->data->ruc,
                    "business" => $rpta_json->data->nombre_o_razon_social,
                    "tradename" => "",
                    "address" => isset($rpta_json->data->direccion) ? $rpta_json->data->direccion : "",
                    "ubigeo" => isset($rpta_json->data->ubigeo) ? $rpta_json->data->ubigeo : null
                );

                try {
                    $save_response = RegisterController::save_ruc_db($ruc);
                } catch (\Exception $e) {
                    logger('Guardando empresa en BDD de validación de identidad: RegisterController@save_ruc_db', ["error" => $e]);
                }

                return response()->json([
                    'success' => true,
                    'source' => '3',
                    'data' => [
                        'ruc' => $ruc
                    ]
                ]);
            }
        }

        // Utilizando https://apis.net.pe/
        $consulta = Http::withToken(env('APISNET_TOKEN'))->get(env('APISNET_URL') . "v1/ruc?numero=". $request->ruc);

        $rpta_json = json_decode($consulta);


        if(is_object($rpta_json)){
            if(isset($rpta_json->nombre)){
                $ruc = array(
                    "ruc" => $rpta_json->numeroDocumento,
                    "business" => $rpta_json->nombre,
                    "tradename" => "",
                    "address" => isset($rpta_json->direccion) ? $rpta_json->direccion : "",
                    "ubigeo" => isset($rpta_json->ubigeo) ? $rpta_json->ubigeo : null
                );

                try {
                    $save_response = RegisterController::save_ruc_db($ruc);
                } catch (\Exception $e) {
                    logger('Guardando empresa en BDD de validación de identidad: RegisterController@save_ruc_db', ["error" => $e]);
                }

                return response()->json([
                    'success' => true,
                    'source' => '4',
                    'data' => [
                        'ruc' => $ruc
                    ]
                ]);
            }
        }


        return response()->json([
            'success' => false,
            'message' => 'Error en el servicio de validación de identidad'
        ]);
    }

    public function save_ruc_db($empresa) {

        $consulta = RucData::where('ruc', $empresa['ruc'])->first();

        $insert = RucData::create([
            'ruc' => $empresa['ruc'],
            'business' => $empresa['business'],
            'tradename' => $empresa['tradename'],
            'address' => $empresa['address']
        ]);

        return response()->json([
            'success' => $insert
        ]);
    }

    public function exists_person(Request $request) {
        $validator = Validator::make($request->all(), [
            'document_number' => 'required|string',
            'document_type' => 'required|exists:document_types,id',
        ]);
        if($validator->fails()) return response()->json($val->messages());

        $client = Client::where('document_type_id', $request->document_type)->where('document_number', $request->document_number)->get();

        if($client->count() > 0){
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => 'El cliente ya se encuentra registrado'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'El cliente no se encuentra registrado'
            ]
        ]);
    }

    public function exists_company(Request $request) {
        $validator = Validator::make($request->all(), [
            'ruc' => 'required|string'
        ]);
        if($validator->fails()) return response()->json($val->messages());

        $client = Client::where('document_type_id', 1)->where('document_number', $request->ruc)->get();

        if($client->count() > 0){
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => 'El cliente ya se encuentra registrado'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'El cliente no se encuentra registrado'
            ]
        ]);
    }
}
