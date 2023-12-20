<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use App\Models\ClientUser;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Enums;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function profile_detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $client = Client::select('name', 'last_name', 'mothers_name', 'document_type_id', 'document_number', 'email', 'address', 'phone', 'accountable_email', 'customer_type', 'created_at as registered_at')
            ->with(['document_type:id,name'])
            ->find($request->client_id);

        $user = Auth::user()->only(['id', 'name', 'last_name', 'email', 'document_number', 'phone']);

        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client,
                'user' => $user
            ]
        ]);
    }

    public function edit_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            //'email' => 'required|email'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $user = Auth::user();
        $user->phone = $request->phone;

/*        // validating if email have changed
        if ($user->email != $request->email) {
            $emailexists = User::where('email', $request->email)->get();

            if (count($emailexists) > 0) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'El email ingresado ya se encuentra registrado'
                    ]
                ]);
            } else $user->email = $request->email;
        }*/
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'last_name', 'email', 'document_number', 'phone','document_type','document_type_id'])
            ]
        ]);
    }

    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $user = Auth::user();

        if(!Hash::check($request->old_password, auth()->user()->password)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La contraseña anterior es incorrecta.'
                ]
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Contraseña actualizada correctamente'
            ]
        ]);
    }

    public function edit_client(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'accountable_email' => 'string',
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $client = Client::find($request->client_id);

        $client->update(
            $request->except(['client_id'])
        );


        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client->only(['id','name', 'last_name', 'mothers_name', 'document_type_id', 'document_number', 'email', 'address', 'phone', 'accountable_email', 'customer_type', 'document_type'])
            ]
        ]);
    }

    public function clients_list(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'clients' => Auth::user()->clients
            ]
        ]);
    }

    public function users_list(Request $request)
    {
        $users = Client::find($request->client_id)->users;

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ]);
    }

    public function change(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $client = Auth::user()->clients->find($request->client_id);


        if (!is_null($client)) {
            $desactivando = ClientUser::where('user_id', Auth::user()->id)
                ->where('status', Enums\ClientUserStatus::Asignado)
                ->update([
                    'status' => Enums\ClientUserStatus::Activo
                ]);


            $activando = ClientUser::where('user_id', Auth::user()->id)
                ->where('client_id', $client->id)
                ->update([
                    'status' => Enums\ClientUserStatus::Asignado
                ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'client' => $client->only(['id', 'name', 'last_name', 'mothers_name', 'document_type_id', 'document_number', 'customer_type', 'email','address','created_at as registered_at','document_type'])
                ]
            ]);

        } else {
            return response()->json([
                'success' => false,
                'errors' => [
                    'El Perfil seleccionado es incorrecto.'
                ]
            ]);
        }
    }

    public function bank_accounts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'currency_id' => 'required|exists:currencies,id',
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $client = Client::find($request->client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => 'Cliente no encontrado'
            ], 404);
        }

        $bank_accounts = $client->bank_accounts()
            ->select('id','client_id','alias','account_number','cci_number','main','bank_account_status_id','currency_id','bank_id')
            ->selectRaw("(if( (select count(*) from escrow_accounts ea where ea.bank_id = bank_accounts.bank_id and ea.currency_id = bank_accounts.currency_id and ea.active = 1) > 0,1,0)) as has_escrow_account")
            ->where('currency_id', $request->currency_id)
            ->whereRelation('status', 'name', 'Activo')
            ->with([
            'bank:id,name,shortname,image',
            'currency:id,name,sign'
        ])->get();


        return response()->json([
            'success' => true,
            'data' => [
                'bank_accounts' => $bank_accounts
            ]
        ]);
    }

    public function add_user(Request $request)
    {  
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string',
            'document_type_id' => 'required|exists:document_types,id',
            'document_number' => 'required|string',
            'phone' => 'required|string',
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $user = User::where('email', $request->email)->get();

        /*return response()->json([
                'success' => false,
                'errors' => $user->count()
            ], 404);*/

        if($user->count()){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El email ingresado ya se encuentra registrado'
                ]
            ]);
        }

        $password = Str::random(10);

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'document_type_id' => $request->document_type_id,
            'document_number' => $request->document_number,
            'phone' => $request->phone,
            'password' => Hash::make($password),
            'role_id' => Role::where('name', 'cliente')->first()->id,
            'status' => Enums\UserStatus::Activo,
        ]);

        // Envio correo creación usuario con contraseña

        $user->assignRole('cliente');

        $client = Client::find($request->client_id);

        $client->users()->attach($user->id, ['status' => Enums\ClientUserStatus::Asignado,]);

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $client->users
            ]
        ]);
    }

    public function delete_user(Request $request)
    {

        if($request->user_id == auth()->id()){
            return response()->json([
                'success' => false,
                'errors' => [
                    'No puedes desasociar tu propio usuario. Por favor comunicate con tu ejecutivo para realizar esta acción.'
                ]
            ]);
        }

        $users = Client::find($request->client_id)->users()->detach($request->user_id);

        return response()->json([
            'success' => true,
            'data' => [
                'users' => Client::find($request->client_id)->users
            ]
        ]);
    }
}
