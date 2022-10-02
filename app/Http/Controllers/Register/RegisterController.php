<?php

namespace App\Http\Controllers\Register;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentType;
use App\Models\Client;
use App\Models\User;
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
use App\Models\ClientStatus;
use App\Models\BankAccount;
use App\Models\BankAccountStatus;
use App\Models\Representative;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Enums;

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

    public function register_person(Request $request) {
        $val = Validator::make($request->all(), [
            'client' => 'required',
            'client.document_type_id' => 'required|in:2,3',
            'client.document_number' => 'required|string',
            'client.phone' => 'required|string',
            'client.birthdate' => 'required|date',
            'client.email' => 'required|string',
            'client.name' => 'required|string',
            'client.last_name' => 'required|string',
            'client.mothers_name' => 'required|string',
            'client.password' => 'required|string',
            'client.accepts_publicity' => 'boolean',
            'client.address' => 'required|string',
            'client.district_id' => 'required|exists:districts,id',
            'client.country_id' => 'required|exists:countries,id',
            'client.profession_id' => 'required|exists:professions,id',
            'client.pep' => 'required|in:0,1',
            'client.funds_source' => 'required|string',

            'accounts' => 'required'
        ]);

        if($val->fails()) return response()->json($val->messages());

        // Registrando el el log los datos ingresados
        logger('Register Person: RegisterController@register_person', ["data" => $request->all()]);    

        $now = Carbon::now();

        $error = false;

        try {
            //Enviando mensaje de bienvenida al cliente
            //$rpta_mail = Mail::send(new MailBienvenida($request->client['nombres'], $request->client['email'], null));

        } catch (\Exception $e) {
            $error = true;
            logger('ERROR: Register Person: RegisterController@register_person', ["mensaje" => "No se pudo enviar el correo de bienvenida", "error" => $e]);  
        }

        $cliente_id = null;

    

        //try {
            // Creando Cliente
            $client = Client::create([
                'name' => $request->client['name'],
                'last_name' => $request->client['last_name'],
                'mothers_name' => $request->client['mothers_name'],
                'document_type_id' => $request->client['document_type_id'],
                'document_number' => $request->client['document_number'],
                'phone' => $request->client['phone'],
                'email' => $request->client['email'],
                'address' => $request->client['address'],
                'birthdate' => $request->client['birthdate'],
                'district_id' => $request->client['district_id'],
                'country_id' => $request->client['country_id'],
                'profession_id' => $request->client['profession_id'],
                'customer_type' => 'PN',
                'type' => 'Cliente',
                'client_status_id' => ClientStatus::where('name','Registrado')->first()->id,
                'funds_source' => ($request->client['funds_source'] != "" )  ? $request->client['funds_source'] : " ",
                'funds_comments' => isset($request->client['funds_comments']) ? $request->client['funds_comments']: null,
                'other_funds_comments' => isset($request->client['other_funds_comments']) ? $request->client['other_funds_comments']: null,
                'pep' => $request->client['pep'],
                'pep_company' => isset($request->client['pep_company']) ? $request->client['pep_company'] : null,
                'pep_position' => isset($request->client['pep_position']) ? $request->client['pep_position'] : null,
                'accepted_tyc_at' => $now,
                'accepts_publicity' => isset($request->client['accepts_publicity']) ? $request->client['accepts_publicity'] : 'false'
            ]);

             

            if ($client) {
                $cliente_id = $client->id;

                try {
                    // Insertando cuentas bancarias
                    foreach ($request->accounts as $account) {
                        $alias = Bank::where('id', $account['bank_id'])->first()->shortname .  ($account['currency_id'] == 1) ? " SOLES" : " USD";

                        $insert = BankAccount::create([
                            'client_id' => $cliente_id,
                            'alias' => $alias,
                            'bank_id' => $account['bank_id'],
                            'account_number' => $account['account_number'],
                            'account_type_id' => $account['account_type_id'],
                            'currency_id' => $account['currency_id'],
                            'bank_account_status_id' => BankAccountStatus::where('name','Pendiente')->first()->id
                        ]);
                    }
                } catch (\Exception $e) {
                    $error = true;
                    logger('Error: Register Person - bank accounts: RegisterController@register_person', ["error" => $e]);
                }
                
                // Validando que Email no existe, sino no se crea usuario
                $user = User::where('email', $request->client['email'])->first();
                        
                if(!is_null($user)){

                    $user_id = $user->id;

                    if ($user) {
                        // Creando Cliente/Usuario
                        try {
                            $client->users()->attach($user->id, ['status' => Enums\ClientUserStatus::Activo,]);
                        } catch (\Exception $e) {
                            $error = true;
                        }
                    }
                    else{
                        $error = true;
                    }
                }
                else{
                    
                    try {
                        // Creando Usuario
                        $user = User::create([
                            'name' => $request->client['name'],
                            'last_name' => $request->client['last_name'] . " " . $request->client['mothers_name'],
                            'email' => $request->client['email'],
                            'document_type_id' => $request->client['document_type_id'],
                            'document_number' => $request->client['document_number'],
                            'phone' => $request->client['phone'],
                            'password' => Hash::make($request->client['password']),
                            'status' => Enums\UserStatus::Activo,
                        ]);

                        $client->users()->attach($user->id, ['status' => Enums\ClientUserStatus::Activo,]);

                    } catch (\Exception $e) {
                        logger('Register Person Usuario: RegisterController@register_person', ["error" => $e]);
                    }
                }

                // Registrando cliente en CRM
                /*try {
                    $executive_id = 2196;
                    $comision = 0.05;
                    // Buscando si existe en prospecto
                    $prospecto = DB::connection('mysql')->table('leads')
                        ->where('ruc', $request->client['nro_documento'])
                        ->get();

                    if($prospecto->count() > 0){
                        $actualiza = DB::connection('mysql')->table('leads')->where('ruc', $request->client['nro_documento'])
                            ->update([
                              'status' => 'Cliente',
                              'tracking_status' => 'Completado',
                              'client_id' => $cliente_id,
                        ]);
                        
                        $ejecutivo = DB::connection('mysql')->table('executives')->where('user_id', $prospecto[0]->executive_id)->first();

                        // Si es freelance, se agrega ejecutivo en tabla executives_comission
                        if($ejecutivo->type == 'Freelance'){
                            $comision = 0;
                        }
                        else{
                            $comision = $ejecutivo->comission;
                            $executive_id = $ejecutivo->user_id;
                        }
                    }

                    // agregando en tabla de cliente
                    $clienteCRM = DB::connection('mysql')->table('clients')->insertGetId([
                        'id' => $cliente_id,
                        'executive_id' => $executive_id,
                        'status' => 'No contactado',
                        'tracking_phase_id' => 1,
                        'tracking_status' => 'Pendiente',
                        'tracking_date' => Carbon::now(),
                        'comission' => $comision,
                        'comission_start_date' => Carbon::now(),
                        'created_at' => Carbon::now(),
                    ]);

                    // Si es freelance, se agrega ejecutivo en tabla executives_comission
                    if(isset($ejecutivo)){
                        if($ejecutivo->type == 'Freelance'){

                            // agregando en tabla de cliente
                            $executive_comission = DB::connection('mysql')->table('executives_comission')->insertGetId([
                                'executive_id' => $ejecutivo->user_id,
                                'client_id' => $cliente_id,
                                'comission' => $ejecutivo->comission
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    logger('Register Person en CRM: RegisterController@register_person', ["error" => $e]);
                }

                //Enviando confirmación de Registro al equipo Billex

                if($error){
                    $mensaje ='Se presentó un problema al guardar el registro.';
                }
                else{
                    $mensaje = 'El registro se realizó de manera exitosa.';
                }

                $rpta_mail = Mail::send(new InfoRegistro($request->client['nombres']." ".$request->client['apellido_paterno']." ".$request->client['apellido_materno'], $request->cliente, $mensaje, $cliente_id));*/

                return response()->json([
                    'success' => true,
                    'cliente_id' => $cliente_id,
                    //'mensaje' => $mensaje
                ]);
                //}
            }
            

        /*} catch (\Exception $e) {
            // Registrando el el log los datos ingresados
            logger('ERROR: Register Person: RegisterController@register_person', ["error" => $e]);
            

            //Enviando confirmación de Registro al equipo Billex
            $mensaje = "El registro no se realizó o se realizó de manera parcial.";
            $rpta_mail = Mail::send(new InfoRegistro($request->client['nombres']." ".$request->client['apellido_paterno']." ".$request->client['apellido_materno'], $request->cliente, $mensaje,$cliente_id));

            return response()->json([
                'success' => true,
                'cliente_id' => $cliente_id,
                'mensaje' => 'Se presentó un problema al guardar el registro.'
            ]);
        }*/

        return response()->json([
            'success' => true,
            'data' => $cliente_id
        ]);
    }

    public function register_company(Request $request) {
        $val = Validator::make($request->all(), [
            'client' => 'required',
            'client.document_type_id' => 'required|in:1',
            'client.document_number' => 'required|string',
            'client.phone' => 'required|string',
            'client.email' => 'required|string',
            'client.name' => 'required|string',
            'client.last_name' => 'required|string',
            'client.mothers_name' => 'required|string',
            'client.password' => 'required|string',

            'client.ruc' => 'required',
            'client.company_name' => 'required|string',
            'client.economic_activity_id' => 'required|exists:economic_activities,id',
            'client.address' => 'required|string',
            'client.district_id' => 'required|exists:districts,id',
            'client.funds_source' => 'required|string',

            'accounts' => 'required',

            'representatives' => 'required'
        ]);


        if($val->fails()) return response()->json($val->messages());

        // Registrando el el log los datos ingresados
        logger('Register Company: RegisterController@register-company', ["datos" => $request->all()]);    

        $now = Carbon::now();
        $error = false;

        

        /*try {
            //Enviando mensaje de bienvenida al cliente
            $rpta_mail = Mail::send(new MailBienvenida($request->client['nombres'], $request->client['email'], $request->client['razon_social']));

        } catch (\Exception $e) {
            $error = true;
            logger('ERROR: Registro Persona Natural: RegisterController@register-company', ["mensaje" => "No se pudo enviar el correo de bienvenida", "error" => $e]);  
        }*/

        $cliente_id = null;

        //try {
            // Creando Cliente
            $client = Client::create([
                'name' => $request->client['company_name'],
                'last_name' => isset($request->client['brand_name']) ? $request->client['brand_name']: "-",
                'mothers_name' => "",
                'document_type_id' => $request->client['document_type_id'],
                'document_number' => $request->client['document_number'],
                'phone' => $request->client['phone'],
                'address' => $request->client['address'],
                'email' => $request->client['email'],
                'birthdate' => isset($request->client['constitution_date']) ? $request->client['constitution_date'] : $now,
                'district_id' => $request->client['district_id'],
                'economic_activity_id' => $request->client['economic_activity_id'],
                'customer_type' => 'PN',
                'type' => 'Cliente',
                'client_status_id' => ClientStatus::where('name','Registrado')->first()->id,
                'funds_source' => ($request->client['funds_source'] != "" )  ? $request->client['funds_source'] : " ",
                'funds_comments' => isset($request->client['funds_comments']) ? $request->client['funds_comments']: null,
                'other_funds_comments' => isset($request->client['other_funds_comments']) ? $request->client['other_funds_comments']: null,
                'accepted_tyc_at' => $now,
                'accepts_publicity' => isset($request->client['accepts_publicity']) ? $request->client['accepts_publicity'] : 'false'
            ]);

            $mensaje = 'El registro se realizó de manera exitosa.';

            return response()->json([
                'success' => true,
                'cliente_id' => $client->id,
                'mensaje' => $mensaje
            ]);
            
            if ($client) {
                $cliente_id = $client->id;            

                // Insertando representantes Legales
                try {
                    foreach ($request->representatives as $representative) {
                        $representatives = Representative::create([
                            'client_id' => $cliente_id,
                            'representative_type' => Enums\RepresentativeType::RepresentanteLegal,
                            'document_type_id' => isset($representative['id_tipo_documento']) ? $representative['id_tipo_documento'] : 2,
                            'document_number' => isset($representative['document_number']) ? $representative['document_number'] : "",
                            'names' => ($representative['names'] != "") ? $representative['names'] : (isset($representative['company_name']) ? $representative['company_name'] : " " ),
                            'last_name' => $representative['last_name'],
                            'mothers_name' => $representative['mothers_name'],
                            'PEP' => $representative['pep'],
                            'pep_company' => isset($representative['pep_company']) ? $representative['pep_company'] : null,
                            'pep_position' => isset($representative['pep_position']) ? $representative['pep_position'] : null
                        ]);
                    }
                } catch (\Exception $e) {
                    $error = true;
                    logger('Registro Empresa Rep Legales: RegisterController@register-company', ["error" => $e]);
                }

                // Insertando Socios
                try {
                    if(isset($request->socios)){
                        foreach ($request->socios as $socio) {
                            $representatives = Representative::create([
                                'client_id' => $cliente_id,
                                'TipoEjecutivoId' => 1,
                                'TipopersonaId' => ($socio['id_tipo_documento'] == 1 || $socio['id_tipo_documento'] == 4) ? 4 : 1,
                                'TipodocumentoId' => isset($socio['id_tipo_documento']) ? $socio['id_tipo_documento'] : "",
                                'Numerodocumento' => isset($socio['nro_documento']) ? $socio['nro_documento'] : "",
                                'Nombres' =>  ($socio['nombres'] != "") ? $socio['nombres'] : (isset($socio['razon_social']) ? $socio['razon_social'] : " " ),
                                'Apellidopaterno' => isset($socio['apellido_paterno']) ? $socio['apellido_paterno'] : null,
                                'Apellidomaterno' => isset($socio['apellido_materno']) ? $socio['apellido_materno'] : null,
                                'PaisNacionalidadId' => 375,
                                'Telefono' => null,
                                'ProfesionId' => 4,
                                'CargoEmpresa' => "",
                                'PEP' => $socio['pep'],
                                'EntidadPublica' => isset($socio['entidad']) ? $socio['entidad'] : null,
                                'CargoPublica' => isset($socio['cargo']) ? $socio['cargo'] : null,
                                'Participacion' => $socio['participacion']
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    $error = true;
                    logger('Registro Empresa Socios: RegisterController@register-company', ["error" => $e]);
                }


                // Insertando cuentas bancarias
                try {
                    foreach ($request->cuentas as $cuenta) {
                        $alias = ($cuenta['divisa'] == 1) ? "SOLES" : "USD";

                        $insert = DB::table('CuentasBancarias')->insert([
                            'ClienteId' => $cliente,
                            'Razon' => $request->client['razon_social'],
                            'Alias' => $alias,
                            'BancoId' => $cuenta['banco_id'],
                            'TipoCuentaId' => $cuenta['tipo_cuenta'],
                            'NroCuenta' => $cuenta['nro_cuenta'],
                            'DivisaId' => $cuenta['divisa'],
                            'EstadoId' => 'COF',
                            'FechaModificacion' => $now->toDateTimeString(),

                        ]);
                    }
                } catch (\Exception $e) {
                    $error = true;
                    logger('Registro Empresa Cuentas banc: RegisterController@register-company', ["error" => $e]);
                }

                $usuario = User::where('Email', $request->client['email'])->first();
                        

                if(!is_null($usuario)){

                    $usuario_id = $usuario->UsuarioId;

                    if ($usuario_id) {
                        // Creando Cliente/Usuario
                        try {
                            $insert = DB::table('ClienteUsuario')->insertGetId([
                                'ClienteId' => $cliente,
                                'UsuarioId' => $usuario_id,
                                'Estado' => 'ACT',
                                'FechaCreacion' => $now->toDateTimeString(),
                                'UsuarioCreacion' => $cliente
                            ]);
                        } catch (\Exception $e) {
                            $error = true;
                        }
                    }
                    else{
                        $error = true;
                    }


                    /*
                    //Enviando confirmación de Registro al equipo Billex
                    if($error){
                        $mensaje ='Se presentó un problema al guardar el registro.';
                    }
                    else{
                        $mensaje = 'El registro se realizó de manera exitosa.';
                    }
                    $rpta_mail = Mail::send(new InfoRegistro($request->client['razon_social'], $request->cliente, $mensaje));

                    // Registrando cliente en CRM
                    try {
                        $lead_id = 2339;
                        // Buscando si existe en prospecto
                        $prospecto = DB::connection('mysql')->table('leads')
                            ->where('ruc', $request->client['ruc'])
                            ->get();

                        if($prospecto->count() > 0){
                            $actualiza = DB::connection('mysql')->table('leads')->where('ruc', $request->client['ruc'])
                                ->update([
                                  'status' => 'Cliente',
                                  'tracking_status' => 'Completado',
                                  'client_id' => $cliente_id,
                            ]);

                            $lead_id = $prospecto[0]->executive_id;
                        }

                        // agregando en tabla de cliente
                        $clienteCRM = DB::connection('mysql')->table('clients')->insertGetId([
                            'id' => $cliente_id,
                            'executive_id' => $lead_id,
                            'status' => 'No contactado',
                            'tracking_phase_id' => 1,
                            'tracking_status' => 'Pendiente',
                            'tracking_date' => Carbon::now(),
                            'sum_comission' => 0,
                            'created_at' => Carbon::now(),
                        ]);

                    } catch (\Exception $e) {
                        logger('Registro Persona Juridica CRM: RegisterController@register-company', ["error" => $e]);
                    }

                    return response()->json([
                        'success' => true,
                        'cliente_id' => $cliente_id,
                        'mensaje' => $mensaje
                    ]);*/
                }
                else{

                    $params = [
                        'Correo' => $request->client['email'], 
                        'Password' => $request->client['password'],
                        'Nombres' => $request->client['nombres'],
                        'Apellidos' => $request->client['apellido_paterno'] . " " . $request->client['apellido_materno'],
                        'TipoDocumentoId' => $request->client['tipo_documento'],
                        'NroDocumento' => $request->client['nro_documento'],
                        'ClienteId' => $cliente,
                        'Telefono' => $request->client['telefono']
                    ];

                    
                    try {
                        // Creando Usuario
                        $creausuario = Http::post(env('APIBILLEX_URL').'/API/RegistrarUsuario', $params);
                        $rpta_json = json_decode($creausuario);


                        if(is_object($rpta_json)){
                            if($rpta_json->result){
                                $usuario = $rpta_json->usuarioId;

                                USER::where('UsuarioId', $usuario)->update(['Estado' => 'ACT']);
                            }
                            else{
                                logger('ERROR Registro Empresa Usuario: RegisterController@register-company - Crear usuario', ["error" => $rpta_json]);
                            }
                        }

                    } catch (\Exception $e) {
                        $error = true;
                        logger('Registro Empresa Usuario: RegisterController@register-company', ["error" => $e]);
                    }
                }

                // Registrando cliente en CRM
                try {
                    $executive_id = 2339;
                    $comision = 0.05;
                    // Buscando si existe en prospecto
                    $prospecto = DB::connection('mysql')->table('leads')
                        ->where('ruc', $request->client['ruc'])
                        ->get();

                    if($prospecto->count() > 0){
                        $actualiza = DB::connection('mysql')->table('leads')->where('ruc', $request->client['ruc'])
                            ->update([
                              'status' => 'Cliente',
                              'tracking_status' => 'Completado',
                              'client_id' => $cliente_id,
                        ]);
                        
                        $ejecutivo = DB::connection('mysql')->table('executives')->where('user_id', $prospecto[0]->executive_id)->first();

                        // Si es freelance, se agrega ejecutivo en tabla executives_comission
                        if(isset($ejecutivo)){
                            if($ejecutivo->type == 'Freelance'){
                                $comision = 0;
                            }
                            else{
                                $comision = $ejecutivo->comission;
                                $executive_id = $ejecutivo->user_id;
                            }
                        }
                    }

                    // agregando en tabla de cliente
                    $clienteCRM = DB::connection('mysql')->table('clients')->insertGetId([
                        'id' => $cliente_id,
                        'executive_id' => $executive_id,
                        'status' => 'No contactado',
                        'tracking_phase_id' => 1,
                        'tracking_status' => 'Pendiente',
                        'tracking_date' => Carbon::now(),
                        'comission' => $comision,
                        'comission_start_date' => Carbon::now(),
                        'created_at' => Carbon::now(),
                    ]);

                    // Si es freelance, se agrega ejecutivo en tabla executives_comission
                    if(isset($ejecutivo)){
                        if($ejecutivo->type == 'Freelance'){

                            // agregando en tabla de cliente
                            $executive_comission = DB::connection('mysql')->table('executives_comission')->insertGetId([
                                'executive_id' => $ejecutivo->user_id,
                                'client_id' => $cliente_id,
                                'comission' => $ejecutivo->comission,
                                'start_date' => Carbon::now()
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    logger('Registro Persona Juridica CRM: RegisterController@register-company', ["error" => $e]);
                }

                //Enviando confirmación de Registro al equipo Billex
                if($error){
                    $mensaje ='Se presentó un problema al guardar el registro.';
                }
                else{
                    $mensaje = 'El registro se realizó de manera exitosa.';
                }
                $rpta_mail = Mail::send(new InfoRegistro($request->client['razon_social'], $request->cliente, $mensaje,$cliente_id));

                return response()->json([
                    'success' => true,
                    'cliente_id' => $cliente_id,
                    'mensaje' => $mensaje
                ]);
                //}
            }

            

        /*} catch (\Exception $e) {
            // Registrando el el log los datos ingresados
            logger('ERROR: Registro Empresa: RegisterController@register-company', ["error" => $e]);
            

            //Enviando confirmación de Registro al equipo Billex
            $mensaje = "El registro no se realizó o se realizó de manera parcial.";
            $rpta_mail = Mail::send(new InfoRegistro($request->client['razon_social'], $request->cliente, $mensaje,$cliente_id));

            return response()->json([
                'success' => true,
                'cliente_id' => $cliente_id,
                'mensaje' => 'Se presentó un problema al guardar el registro.'
            ]);
        }*/
    }
}
