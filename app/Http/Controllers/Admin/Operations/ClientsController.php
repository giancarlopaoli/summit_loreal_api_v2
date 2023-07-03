<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Enums;
use App\Models\Client;
use App\Models\ClientStatus;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\BankAccountStatus;
use App\Models\BankAccountReceipt;
use App\Models\Currency;
use App\Models\Document;
use App\Models\Representative;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ClientsController extends Controller
{
    //Clients list
    public function list(Request $request) {
        $val = Validator::make($request->all(), [
            'type' => 'required|in:pending,approved,canceled,corfid,all'
        ]);
        if($val->fails()) return response()->json($val->messages());


        $client = Client::select('id','name','last_name','mothers_name','document_type_id','document_number','phone','email','address','birthdate','customer_type','type','client_status_id','billex_approved_at','corfid_approved_at','registered_at','updated_at as last_update')
            ->with('document_type:id,name','status:id,name')
            ->with('bank_accounts:id,client_id,bank_id,account_number,cci_number,bank_account_status_id,currency_id','bank_accounts.bank:id,shortname,image','bank_accounts.currency:id,name,sign','bank_accounts.status:id,name')
            ->with('users:id,name,last_name,phone,status');
        
        if($request->type == 'pending'){
            $client = $client->whereIn('client_status_id', ClientStatus::whereIn('name', ['Registrado','Aprobado Billex','Rechazo parcial'])->get()->pluck('id'));
        }
        elseif($request->type == 'approved'){
            $client = $client->whereIn('client_status_id', ClientStatus::whereIn('name', ['Activo','Pendiente Aprobacion'])->get()->pluck('id'));
        }
        elseif($request->type == 'canceled'){
            $client = $client->whereIn('client_status_id', ClientStatus::whereIn('name', ['Rechazado'])->get()->pluck('id'));
        }
        elseif($request->type == 'corfid'){
            $client = $client->whereIn('client_status_id', ClientStatus::whereIn('name', ['Aprobado Billex'])->get()->pluck('id'));
        }

        if(isset($request->customer_type)) $client = $client->where('customer_type', $request->customer_type);

        if($request->company_name != "") $client = $client->whereRaw("CONCAT(name,' ',last_name,' ',mothers_name) like "."'%"."$request->company_name"."%'");

        if($request->document_number != "")  $client = $client->where('document_number', 'like', "%".$request->document_number."%");

        if($request->document_type_id != "")  $client = $client->where('document_type_id', 'like', "%".$request->document_type_id."%");

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $client->get()
            ]
        ]);
    }

    //Bank Account list
    public function bank_account_list(Request $request, Client $client) {
        //$client->bank_accounts->load('bank:id,name,shortname,main','status:id,name','currency:id,name,sign')
        return response()->json([
            'success' => true,
            'data' => [
                'bank_accounts' => $client->load('document_type:id,name','bank_accounts','bank_accounts.bank:id,name,shortname,main','bank_accounts.status:id,name','bank_accounts.currency:id,name,sign','bank_accounts.receipts:id,bank_account_id,name')->only('id','name','last_name','mothers_name','document_type','document_number','phone','email','type','customer_type','bank_accounts')
            ]
        ]);
    }

    //Add Bank Account
    public function add_bank_account(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required|string|min:5',
            'cci_number' => 'required|string|min:20|max:20',
            'currency_id' => 'required|exists:currencies,id',
            'account_type_id' => 'required|exists:account_types,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $alias = Bank::where('id', $request->bank_id)->first()->shortname . " " . Currency::where('id', $request->currency_id)->first()->name;

        $insert = BankAccount::create([
            'client_id' => $client->id,
            'alias' => $alias,
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'cci_number' => $request->cci_number,
            'account_type_id' => $request->account_type_id,
            'currency_id' => $request->currency_id,
            'bank_account_status_id' => BankAccountStatus::where('name','Pendiente')->first()->id
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Cuenta bancaria creada exitosamente'
            ]
        ]);
    }

    //Edit Bank Account
    public function edit_bank_account(Request $request, BankAccount $bank_account) {
        $val = Validator::make($request->all(), [
            'account_number' => 'required|string|min:5',
            'cci_number' => 'required|string|min:20|max:20',
            'currency_id' => 'required|exists:currencies,id',
            'account_type_id' => 'required|exists:account_types,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $bank_account->update($request->only(["account_number","cci_number","currency_id", "account_type_id"]));

        return response()->json([
            'success' => true,
            'data' => [
                'Cuenta bancaria editada exitosamente'
            ]
        ]);
    }


    //Approve Bank Account
    public function approve_bank_account(Request $request, BankAccount $bank_account) {
        if($bank_account->status->name != 'Pendiente'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Solo puede aprobar una cuenta bancaria que se encuentre en estado Pendiente de Aprobación'
                ]
            ]);
        }

        $bank_account->bank_account_status_id = BankAccountStatus::where('name','Activo')->first()->id;
        $bank_account->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Cuenta bancaria activada exitosamente'
            ]
        ]);
    }

    //Reject Bank Account
    public function reject_bank_account(Request $request, BankAccount $bank_account) {

        $bank_account->bank_account_status_id = BankAccountStatus::where('name','Rechazado')->first()->id;
        $bank_account->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Cuenta bancaria rechazada'
            ]
        ]);
    }

    //Reject Bank Account
    public function delete_bank_account(Request $request, BankAccount $bank_account) {

        $bank_account->bank_account_status_id = BankAccountStatus::where('name','Inactivo')->first()->id;
        $bank_account->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Cuenta bancaria desactivada'
            ]
        ]);
    }

    //Upload bank account receipt
    public function upload_bank_account_receipt(Request $request, BankAccount $bank_account) {
        $val = Validator::make($request->all(), [
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());

        logger('Archivo adjunto: ClientsController@upload_bank_account_receipt', ["client_id" => $request->client_id]);

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/bank_accounts';

            try {
                $extension = strrpos($file->getClientOriginalName(), ".")? (Str::substr($file->getClientOriginalName(), strrpos($file->getClientOriginalName(), ".") , Str::length($file->getClientOriginalName()) -strrpos($file->getClientOriginalName(), ".") +1)): "";
                
                $now = Carbon::now();
                $filename = md5($now->toDateTimeString().$file->getClientOriginalName()).$extension;
            } catch (\Exception $e) {
                $filename = $file->getClientOriginalName();
            }


            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                $bank_account->receipts()->delete();

                $insert = BankAccountReceipt::create([
                    'bank_account_id' => $bank_account->id,
                    'name' => $filename
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: ClientsController@upload_bank_account_receipt', ["error" => $e]);
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


    //Client detail
    public function detail(Request $request, Client $client) {

        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client->load('bank_accounts','bank_accounts.bank:id,name,shortname','bank_accounts.currency:id,name','bank_accounts.account_type:id,name,shortname','users','users.document_type:id,name','document_type:id,name','documents:id,client_id,name','status:id,name')
                ->load('representatives:id,client_id,representative_type,document_type_id,document_number,names,last_name,mothers_name,pep,pep_company,pep_position','representatives.document_type:id,name')
                ->load('business_associates:id,client_id,representative_type,document_type_id,document_number,names,last_name,mothers_name,pep,pep_company,pep_position,share','business_associates.document_type:id,name')
                ->load('district:id,name,province_id,ubigeo','district.province:id,name,department_id','district.province.department:id,name')
                ->load('economic_activity:id,name,code')
                ->load('profession:id,name')
                ->load('country:id,name')
            ]
        ]);
    }

    // Downloading client document
    public function download_document(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'document_id' => 'required|exists:documents,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $document = Document::where('id', $request->document_id)->where('client_id', $client->id)->first();

        if(is_null($document)){
            return response()->json([
                'success' => false,
                'data' => [
                    'Archivo no encontrado'
                ]
            ]);
        }

        if (Storage::disk('s3')->exists(env('AWS_ENV').'/register/' . $document->name)) {
            return Storage::disk('s3')->download(env('AWS_ENV').'/register/' . $document->name);
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Archivo no encontrado'
                ]
            ]);
        }

        return Storage::disk('s3')->download(env('AWS_ENV').'/register/' . $document->name);
    }

    // Deleting client document
    public function delete_document(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'document_id' => 'required|exists:documents,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $document = Document::where('id', $request->document_id)->where('client_id', $client->id)->first();

        if(is_null($document)){
            return response()->json([
                'success' => false,
                'data' => [
                    'Archivo no encontrado'
                ]
            ]);
        }

        if (Storage::disk('s3')->exists(env('AWS_ENV').'/register/' . $document->name)) {
            Storage::disk('s3')->delete(env('AWS_ENV').'/register/' . $document->name);

            $document->delete();

            return response()->json([
                'success' => true,
                'data' => [
                    'Archivo eliminado exitosamente'
                ]
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error al eliminar archivo'
                ]
            ]);
        }

    }

    public function upload_document(Request $request, Client $client)
    {
        $val = Validator::make($request->all(), [
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());

        logger('Archivo adjunto: ClientsController@upload_document', ["client_id" => $client->id]);

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

                $insert = Document::create([
                    'client_id' => $client->id,
                    'name' => $filename
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: ClientsController@upload_document', ["error" => $e]);
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

    //Edit client
    public function edit(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'district_id' => 'required|exists:districts,id',
            'comments' => 'required|string',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $client->update($request->only(["email","phone","address", "district_id", "accountable_email","comments"]));

        return response()->json([
            'success' => true,
            'data' => [
                'Cliente actualizado exitosamente'
            ]
        ]);
    }


    ############ Gestión de Socios ############

    //Add associate
    public function add_associate(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'document_type_id' => 'required|exists:document_types,id',
            'document_number' => 'required|string',
            'names' => 'required|string',
            'last_name' => 'nullable|string',
            'mothers_name' => 'nullable|string',
            'pep' => 'required|boolean',
            'pep_company' => 'nullable|string',
            'pep_position' => 'nullable|string',
            'share' => 'required|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $representatives = Representative::create([
            'client_id' => $client->id,
            'representative_type' => Enums\RepresentativeType::Socio,
            'document_type_id' => $request->document_type_id,
            'document_number' => $request->document_number,
            'names' => $request->names,
            'last_name' => $request->last_name,
            'mothers_name' => $request->mothers_name,
            'PEP' => $request->pep,
            'pep_company' => isset($request->pep_company) ? $request->pep_company : null,
            'pep_position' => isset($request->pep_position) ? $request->pep_position : null,
            'share' => $request->share
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Socio agregado exitosamente'
            ]
        ]);
    }

    // delete associate
    public function delete_associate(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'associate_id' => 'required|exists:representatives,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $associate = Representative::where('id', $request->associate_id)->where('client_id', $client->id)->where('representative_type', Enums\RepresentativeType::Socio)->first();

        if(is_null($associate)){
            return response()->json([
                'success' => false,
                'data' => [
                    'Socio no encontrado'
                ]
            ]);
        }

        $associate->delete();

        return response()->json([
            'success' => true,
            'data' => [
                'Socio eliminado exitosamente'
            ]
        ]);

    }

    //Edit Associate
    public function edit_associate(Request $request, Representative $representative) {
        $val = Validator::make($request->all(), [
            'document_type_id' => 'required|exists:document_types,id',
            'document_number' => 'required|string',
            'names' => 'required|string',
            'last_name' => 'nullable|string',
            'mothers_name' => 'nullable|string',
            'pep' => 'required|boolean',
            'pep_company' => 'nullable|string',
            'pep_position' => 'nullable|string',
            'share' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $representative->update($request->only(["document_type_id","document_number","names", "last_name","mothers_name","pep","pep_company","pep_position","share"]));

        return response()->json([
            'success' => true,
            'data' => [
                'Datos de socio actualizados'
            ]
        ]);
    }


    ############ Gestión de Representantes ############

    //Add representative
    public function add_representative(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'document_type_id' => 'required|exists:document_types,id',
            'document_number' => 'required|string',
            'names' => 'required|string',
            'last_name' => 'nullable|string',
            'mothers_name' => 'nullable|string',
            'pep' => 'required|boolean',
            'pep_company' => 'nullable|string',
            'pep_position' => 'nullable|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $representatives = Representative::create([
            'client_id' => $client->id,
            'representative_type' => Enums\RepresentativeType::RepresentanteLegal,
            'document_type_id' => $request->document_type_id,
            'document_number' => $request->document_number,
            'names' => $request->names,
            'last_name' => $request->last_name,
            'mothers_name' => $request->mothers_name,
            'PEP' => $request->pep,
            'pep_company' => isset($request->pep_company) ? $request->pep_company : null,
            'pep_position' => isset($request->pep_position) ? $request->pep_position : null
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Representante agregado exitosamente'
            ]
        ]);
    }

    // delete representative
    public function delete_representative(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'representative_id' => 'required|exists:representatives,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $representative = Representative::where('id', $request->representative_id)->where('client_id', $client->id)->where('representative_type', Enums\RepresentativeType::RepresentanteLegal)->first();

        if(is_null($representative)){
            return response()->json([
                'success' => false,
                'data' => [
                    'Socio no encontrado'
                ]
            ]);
        }

        $representative->delete();

        return response()->json([
            'success' => true,
            'data' => [
                'Representante eliminado exitosamente'
            ]
        ]);

    }

    //Edit representative
    public function edit_representative(Request $request, Representative $representative) {
        $val = Validator::make($request->all(), [
            'document_type_id' => 'required|exists:document_types,id',
            'document_number' => 'required|string',
            'names' => 'required|string',
            'last_name' => 'nullable|string',
            'mothers_name' => 'nullable|string',
            'pep' => 'required|boolean',
            'pep_company' => 'nullable|string',
            'pep_position' => 'nullable|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $representative->update($request->only(["document_type_id","document_number","names", "last_name","mothers_name","pep","pep_company","pep_position"]));

        return response()->json([
            'success' => true,
            'data' => [
                'Datos de representante actualizados'
            ]
        ]);
    }


    ############# Gestión de Usuarios  ##################333


    //Avaliable clients to attach
    public function users(Request $request, Client $client) {

        return response()->json([
            'success' => true,
            'data' => [
                'users' => User::select('id', 'name', 'last_name')
                    ->where('status', Enums\UserStatus::Activo)
                    ->where('role_id', Role::where('name', 'Cliente')->first()->id)
                    ->whereNotIn('id', $client->users->pluck('id'))
                    ->get()
            ]
        ]);
    }

    //Attach client to user
    public function attach_user(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($client->users->pluck('id')->contains($request->user_id)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El usuario ya se encuentra asignado'
                ]
            ]);
        }

        $client->users()->attach($request->user_id, ['status' => Enums\ClientUserStatus::Activo, 'created_at' => Carbon::now(),'updated_by' => auth()->id()]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'Usuario asignado.'
            ]
        ]);
    }

    //Detach client from user
    public function detach_user(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if(is_null($client->users->find($request->user_id))){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El usuario no se encuentra asignado'
                ]
            ]);
        }

        $client->users()->syncWithoutDetaching([$request->user_id => [ 'status' => Enums\ClientUserStatus::Inactivo, 'updated_at' => Carbon::now(), 'updated_by' => auth()->id()]]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'Usuario desasignado.'
            ]
        ]);
    }

    //Evaluation
    public function evaluation(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'agent' => 'required|in:billex,corfid',
            'comments' => 'nullable|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($request->agent == 'billex'){
            if($request->action == 'approve'){
                if($client->status->name == 'Registrado' || $client->status->name == 'Rechazo parcial'){

                    // Validating if bank accounts were already approved

                    if($client->bank_accounts->where('bank_account_status_id', BankAccountStatus::where('name','Pendiente')->first()->id)->count() > 0){
                        return response()->json([
                            'success' => false,
                            'errors' => [
                                'El cliente tiene cuentas bancarias pendientes de activación.'
                            ]
                        ]);
                    }

                    if($client->bank_accounts->where('bank_account_status_id', BankAccountStatus::where('name','Activo')->first()->id)->count() == 0){
                        return response()->json([
                            'success' => false,
                            'errors' => [
                                'El cliente no tiene ninguna cuenta bancaria activada'
                            ]
                        ]);
                    }

                    $client->client_status_id = ClientStatus::where('name', 'Aprobado Billex')->first()->id;
                    $client->comments .= " - ".(!is_null($request->comments) ? $request->comments : null);
                    $client->save();


                    // Envío de correo()

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'Cliente aprobado exitosamente.'
                        ]
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'El cliente debe encontrarse en estado Registrado o Rechazo parcial para ser aprobado'
                        ]
                    ]);
                }

            }
            elseif($request->action == 'reject'){
                if($client->status->name == 'Registrado' || $client->status->name == 'Rechazo parcial'){
                    $client->client_status_id = ClientStatus::where('name', 'Rechazado')->first()->id;
                    $client->comments .= " - ".(!is_null($request->comments) ? $request->comments : null);
                    $client->save();

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'Cliente rechazado exitosamente.'
                        ]
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'El cliente debe encontrarse en estado Registrado o Rechazo parcial para ser rechazado'
                        ]
                    ]);
                }
            }
        }

        return response()->json([
            'success' => false,
            'errors' => [
                'Hubo un error en la evaluación del cliente'
            ]
        ]);
    }
    
    //Client comission list
    public function comission_list(Request $request, Client $client) {

        return response()->json([
            'success' => true,
            'data' => [
                'comissions' => $client->load('comissions:id,client_id,comission_open,comission_close,active,comments,updated_by,created_at','comissions.updater:id,name,last_name')->comissions
            ]
        ]);
    }

    //Client comission list
    public function create_comission(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'comission_open' => 'nullable|numeric',
            'comission_close' => 'nullable|numeric',
            'comments' => 'required|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $client->comissions()->where("active", true)->update(["active" => false]);

        $client->comissions()->create([
            'comission_open' => (isset($request->comission_open) ? $request->comission_open : null),
            'comission_close' => (isset($request->comission_close) ? $request->comission_close : null),
            'active' => true,
            'comments' => $request->comments,
            'updated_by' => auth()->id()

        ]);
        return response()->json([
            'success' => true,
            'data' => [
                'Comision creada exitosamente'
            ]
        ]);
    }

    //Client comission list
    public function delete_comission(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'comission_id' => 'required|exists:client_comissions,id',
        ]);
        if($val->fails()) return response()->json($val->messages());

        if(is_null($client->comissions()->find($request->comission_id))){
            return response()->json([
                'success' => false,
                'data' => [
                    'Comission no encontrada'
                ]
            ]);
        }
        $client->comissions()->find($request->comission_id)->update(["active" => false]);

        return response()->json([
            'success' => true,
            'data' => [
                'Comision desactivada exitosamente'
            ]
        ]);
    }
}
