<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use App\Models\ClientUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\ClientUserStatus;

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
            'email' => 'required|email'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $user = Auth::user();
        $user->phone = $request->phone;


        // validating if email have changed
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
        }
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'last_name', 'email', 'document_number', 'phone'])
            ]
        ]);
    }

    public function edit_client(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'phone' => 'required|string',
            'email' => 'required|email',
            'address' => 'required|string',
            'accountable_email' => 'string',
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $client = Client::find($request->client_id)->first()->update(
            $request->all()
        );


        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client
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
                ->where('status', ClientUserStatus::Asignado)
                ->update([
                    'status' => ClientUserStatus::Activo
                ]);


            $activando = ClientUser::where('user_id', Auth::user()->id)
                ->where('client_id', $client->id)
                ->update([
                    'status' => ClientUserStatus::Asignado
                ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'client' => $client->only(['id', 'name', 'last_name', 'mothers_name', 'document_type_id', 'document_number', 'customer_type', 'created_at as registered_at'])
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
        $users = Client::find($request->client_id)->users;

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ]);
    }

    public function delete_user(Request $request)
    {
        $users = Client::find($request->client_id)->users;

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ]);
    }
}
