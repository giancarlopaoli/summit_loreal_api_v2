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
use App\Models\Document;
use App\Models\Currency;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Executive;
use App\Models\ExecutivesComission;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Enums;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\NewClientNotification;

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

    // Registro cliente PJ WS Corfid
    public function validate_dni(Request $request) {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|size:8',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $registro = RegisterController::function_validate_dni($request->dni);

        return response()->json(
            $registro->getData()
        );
    }

    public function function_validate_dni($dni_value) {
    
        $consulta = DniData::where('dni', $dni_value)->first();

        if(!is_null($consulta)){
            $dni = array(
                "dni" => (string) $dni_value,
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
        $consulta = Http::withToken(env('APIPERUDEV_TOKEN'))->get(env('APIPERUDEV_URL') . "api/dni/" . $dni_value);

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
        $consulta = Http::withToken(env('APISNET_TOKEN'))->get(env('APISNET_URL') . "v1/dni?numero=". $dni_value);

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

    // Consulta servicio RUC
    public function validate_ruc(Request $request) {
        $validator = Validator::make($request->all(), [
            'ruc' => 'required|string|size:11',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $registro = RegisterController::function_validate_ruc($request->ruc);

        return response()->json(
            $registro->getData()
        );
    }

    public function function_validate_ruc($ruc_value) {
        $tipoempresa = Str::substr($ruc_value, 0, 2);

        if ($tipoempresa != 10 and $tipoempresa != 20 and $tipoempresa != 15) {
            return response()->json([
                'ruc' => array('El nro de ruc debe empezar con 10, 15 o 20')
            ]);
        }

        $consulta = RucData::where('ruc', $ruc_value)->first();

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
        $consulta = Http::withToken(env('PERUAPISTOKEN'))->post(env('PERUAPISURL') . "/ruc", ['document' => $ruc_value]);

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
        $consulta = Http::withToken(env('APIPERUDEV_TOKEN'))->get(env('APIPERUDEV_URL') . "api/ruc/" . $ruc_value);

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
        $consulta = Http::withToken(env('APISNET_TOKEN'))->get(env('APISNET_URL') . "v1/ruc?numero=". $ruc_value);

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
            $rpta_mail = Mail::send(new Welcome($request->client['name'], $request->client['email'], null));
        } catch (\Exception $e) {
            $error = true;
            logger('ERROR: Register Person: RegisterController@register_person', ["mensaje" => "No se pudo enviar el correo de bienvenida", "error" => $e]);  
        }

        $client_id = null;

        try {
            // Creating Client

            $executive_id = User::where('email', env('PERSONS_EXECUTIVE'))->first()->id;
            $comission = 0;
            
            // Looking for a existing lead       
            $lead = Lead::where('contact_type', 'Natural')->where('document_number', $request->client['document_number'])->get();

            // Retrieving executive
            if($lead->count() > 0){
                $executive = Executive::where('id', $lead[0]->executive_id)->first();

                // Si es freelance, se agrega ejecutivo en tabla executives_comission
                if($executive->type == 'Freelance'){
                    $comision = 0;
                }
                else{
                    $comission = $executive->comission;
                    $executive_id = $executive->id;
                }
            }

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
                'registered_at' => $now,
                'accepted_tyc_at' => $now,
                'accepts_publicity' => isset($request->client['accepts_publicity']) ? $request->client['accepts_publicity'] : 'false',
                'executive_id' => $executive_id,
                'tracking_phase_id' => null,
                'tracking_date' => $now,
                'comission' => $comission
            ]);

            
            if ($client) {
                $client_id = $client->id;

                try {
                    // Insertando cuentas bancarias
                    foreach ($request->accounts as $account) {
                        $alias = Bank::where('id', $account['bank_id'])->first()->shortname .  ($account['currency_id'] == 1) ? " SOLES" : " USD";

                        $insert = BankAccount::create([
                            'client_id' => $client_id,
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
                            $client->users()->attach($user->id, ['status' => Enums\ClientUserStatus::Activo]);
                        } catch (\Exception $e) {
                            logger('Error: Register Person - attaching user: RegisterController@register_person', ["error" => $e]);
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
                            'role_id' => Role::where('name', 'cliente')->first()->id,
                            'status' => Enums\UserStatus::Activo,
                        ]);

                        $user->assignRole('cliente');

                        $client->users()->attach($user->id, ['status' => Enums\ClientUserStatus::Activo,]);

                    } catch (\Exception $e) {
                        logger('Register Person Usuario: RegisterController@register_person', ["error" => $e]);
                    }
                }

                // Actualizando lead
                try {
                    // Actualizando Lead
                    if($lead->count() > 0){
                        $actualiza = lead::where('contact_type', 'Natural')
                            ->where('document_number', $request->client['document_number'])
                            ->update([
                                'lead_status_id' => LeadStatus::where('name', 'Cliente')->first()->id,
                                'tracking_status' => 'Completado',
                                'client_id' => $client_id,
                            ]);
                    }

                    return response()->json([
                    'success' => true,
                        'executive' => [ 
                            $executive
                        ]
                    ]);

                    // Si es freelance, se agrega ejecutivo en tabla executives_comission
                    if(isset($executive)){
                        if($executive->type == 'Freelance'){

                            // agregando en tabla de cliente
                            $executive_comission = ExecutivesComission::create([
                                'executive_id' => $executive->id,
                                'client_id' => $client_id,
                                'comission' => $executive->comission
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

                $rpta_mail = Mail::send(new NewClientNotification($mensaje, $client_id));

                return response()->json([
                    'success' => true,
                    'client_id' => $client_id,
                    'data' => [ 
                        $mensaje
                    ]
                ]);
            }
            

        } catch (\Exception $e) {
            // Registrando el el log los datos ingresados
            logger('ERROR: Register Person: RegisterController@register_person', ["error" => $e]);
            

            //Enviando confirmación de Registro al equipo Billex
            $mensaje = "El registro no se realizó o se realizó de manera parcial.";
            $rpta_mail = Mail::send(new NewClientNotification($mensaje, $client_id));

            return response()->json([
                'success' => true,
                'client_id' => $client_id,
                'data' => [
                    $mensaje
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'client_id' => $client_id,
            'data' => [
                $mensaje
            ]
        ]);
    }

    public function register_company(Request $request) {
        $val = Validator::make($request->all(), [
            'client' => 'required',
            'client.document_type_id' => 'required|in:2,3',
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

        try {
            //Enviando mensaje de bienvenida al cliente
            $rpta_mail = Mail::send(new Welcome($request->client['name'], $request->client['email'], $request->client['company_name']));
        } catch (\Exception $e) {
            $error = true;
            logger('ERROR: Register Company: RegisterController@register_company', ["mensaje" => "No se pudo enviar el correo de bienvenida", "error" => $e]);  
        }

        $client_id = null;

        try {
            // Creating Client
            $executive_id = User::where('email', env('COMPANIES_EXECUTIVE'))->first()->id;
            $comission = 0.05;

            // Looking for a existing lead       
            $lead = Lead::where('contact_type', 'Juridica')->where('document_number', $request->client['ruc'])->get();

            // Retrieving executive
            if($lead->count() > 0){                
                $executive = Executive::where('id', $lead[0]->executive_id)->first();

                // Si es freelance, se agrega ejecutivo en tabla executives_comission
                /*if($executive->type == 'Freelance'){
                    $comision = 0;
                }
                else{
                    $comission = $executive->comission;
                    $executive_id = $executive->id;
                }*/
            }

            $client = Client::create([
                'name' => $request->client['company_name'],
                'last_name' => isset($request->client['brand_name']) ? $request->client['brand_name']: "-",
                'mothers_name' => "",
                'document_type_id' => DocumentType::where('name', 'RUC')->first()->id,
                'document_number' => $request->client['ruc'],
                'phone' => $request->client['phone'],
                'address' => $request->client['address'],
                'email' => $request->client['email'],
                'birthdate' => isset($request->client['constitution_date']) ? $request->client['constitution_date'] : $now,
                'district_id' => $request->client['district_id'],
                'economic_activity_id' => $request->client['economic_activity_id'],
                'customer_type' => 'PJ',
                'type' => 'Cliente',
                'client_status_id' => ClientStatus::where('name','Registrado')->first()->id,
                'funds_source' => ($request->client['funds_source'] != "" )  ? $request->client['funds_source'] : " ",
                'funds_comments' => isset($request->client['funds_comments']) ? $request->client['funds_comments']: null,
                'other_funds_comments' => isset($request->client['other_funds_comments']) ? $request->client['other_funds_comments']: null,
                'accepted_tyc_at' => $now,
                'accepts_publicity' => isset($request->client['accepts_publicity']) ? $request->client['accepts_publicity'] : 'false',
                'executive_id' => $executive_id,
                'tracking_phase_id' => null,
                'tracking_date' => Carbon::now(),
                'comission' => $comission
            ]);

            $mensaje = 'El registro se realizó de manera exitosa.';
            
            if ($client) {
                $client_id = $client->id;

                // Insertando representantes Legales
                try {
                    foreach ($request->representatives as $representative) {
                        $representatives = Representative::create([
                            'client_id' => $client_id,
                            'representative_type' => Enums\RepresentativeType::RepresentanteLegal,
                            'document_type_id' => isset($representative['document_type_id']) ? $representative['document_type_id'] : null,
                            'document_number' => isset($representative['document_number']) ? $representative['document_number'] : "",
                            'names' => ($representative['names'] != "") ? $representative['names'] : (isset($representative['company_name']) ? $representative['company_name'] : " " ),
                            'last_name' => $representative['last_name'],
                            'mothers_name' => $representative['mothers_name'],
                            'pep' => $representative['pep'],
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
                    if(isset($request->business_associates)){
                        foreach ($request->business_associates as $socio) {
                            $representatives = Representative::create([
                                'client_id' => $client_id,
                                'representative_type' => Enums\RepresentativeType::Socio,
                                'document_type_id' => isset($socio['document_type_id']) ? $socio['document_type_id'] : null,
                                'document_number' => isset($socio['document_number']) ? $socio['document_number'] : "",
                                'names' => ($socio['names'] != "") ? $socio['names'] : (isset($socio['company_name']) ? $socio['company_name'] : " " ),
                                'last_name' => isset($socio['last_name']) ? $socio['last_name'] : null,
                                'mothers_name' => isset($socio['mothers_name']) ? $socio['mothers_name'] : null,
                                'pep' => $socio['pep'],
                                'pep_company' => isset($socio['pep_company']) ? $socio['pep_company'] : null,
                                'pep_position' => isset($socio['pep_position']) ? $socio['pep_position'] : null,
                                'share' => $socio['share']
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    $error = true;
                    logger('Registro Empresa Socios: RegisterController@register-company', ["error" => $e]);
                }

                // Insertando cuentas bancarias
                try {

                    foreach ($request->accounts as $account) {
                        $alias = Bank::where('id', $account['bank_id'])->first()->shortname . " " . Currency::where('id', $account['currency_id'])->first()->name;

                        $insert = BankAccount::create([
                            'client_id' => $client_id,
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
                    logger('Registro Empresa Cuentas banc: RegisterController@register-company', ["error" => $e]);
                }

                // Validando que Email no existe, sino no se crea usuario
                $user = User::where('email', $request->client['email'])->first();
                        

                if(!is_null($user)){
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
                            'role_id' => Role::where('name', 'cliente')->first()->id,
                            'status' => Enums\UserStatus::Activo
                        ]);

                        $user->assignRole('cliente');

                        $client->users()->attach($user->id, ['status' => Enums\ClientUserStatus::Asignado,]);

                    } catch (\Exception $e) {
                        logger('Register Person Usuario: RegisterController@register_person', ["error" => $e]);
                    }
                }

                // Updating Lead
                try {
                    if($lead->count() > 0){
                        $actualiza = lead::where('contact_type', 'Juridica')
                            ->where('document_number', $request->client['ruc'])
                            ->update([
                                'lead_status_id' => LeadStatus::where('name', 'Cliente')->first()->id,
                                'tracking_status' => 'Completado',
                                'client_id' => $client_id,
                            ]);
                    }

                    // Si es freelance, se agrega ejecutivo en tabla executives_comission
                    if(isset($executive)){
                        if($executive->type == 'Freelance'){

                            // agregando en tabla de cliente
                            $executive_comission = ExecutivesComission::create([
                                'executive_id' => $executive->id,
                                'client_id' => $client_id,
                                'comission' => $executive->comission
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
                
                $rpta_mail = Mail::send(new NewClientNotification($mensaje, $client_id));

                return response()->json([
                    'success' => true,
                    'client_id' => $client_id,
                    'data' => [
                        $mensaje
                    ]
                ]);
            }


        } catch (\Exception $e) {
            // Registrando el el log los datos ingresados
            logger('ERROR: Registro Empresa: RegisterController@register-company', ["error" => $e]);    

            //Enviando confirmación de Registro al equipo Billex
            $mensaje = "El registro no se realizó o se realizó de manera parcial.";
            $rpta_mail = Mail::send(new NewClientNotification($mensaje, $client_id));

            return response()->json([
                'success' => true,
                'client_id' => $client_id,
                'data' => [
                    $mensaje
                ]
            ]);
        }
    }

    public function upload_file(Request $request)
    {
        $val = Validator::make($request->all(), [
            'client_id' => 'required',
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());

        logger('Archivo adjunto: RegisterController@uploadFile', ["client_id" => $request->client_id]);

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/register';

            try {
                $extension = strrpos($file->getClientOriginalName(), ".")? (Str::substr($file->getClientOriginalName(), strrpos($file->getClientOriginalName(), ".") , Str::length($file->getClientOriginalName()) -strrpos($file->getClientOriginalName(), ".") +1)): "";
                
                $now = Carbon::now();
                $filename = md5($now->toDateTimeString().$file->getClientOriginalName()).$extension;
            } catch (\Exception $e) {
                $filename = $file->getClientOriginalName();
            }


            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);
                $cliente = ($request->client_id) ? ( ($request->client_id <> "null") ? $request->client_id : null) : null;

                $insert = Document::create([
                    'client_id' => $cliente,
                    'name' => $filename
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: RegisterController@uploadFile', ["error" => $e]);
            }


            return response()->json([
                'success' => true,
                'data' => [
                    'Archivo agregado'
                ]
            ]);

        } else{
            return response()->json([
                'success' => false,
                'errors' => 'Error en el archivo adjunto',
            ]);
        }

    }
}
