<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Client;
use App\Models\User;
use App\Enums;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    //Users list
    public function list(Request $request) {

        $users = User::select('id','name','last_name','email','phone','tries','last_active','status');

        if(isset($request->email) && $request->email != '') $users->where('email', 'like', '%'.$request->email.'%');
        if(isset($request->name) && $request->name != '') $users->where('name', 'like', '%'.$request->name.'%');
        if(isset($request->last_name) && $request->last_name != '') $users->where('last_name', 'like', '%'.$request->last_name.'%');

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users->get()
            ]
        ]);
    }

    //User detail
    public function detail(Request $request, User $user) {

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $user->only(['id','name','last_name','email','document_type_id','document_number','phone','tries','last_active','status','created_at','role_id'])
            ]
        ]);
    }

    //Edit user
    public function edit(Request $request, User $user) {

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $user->only(['id','name','last_name','email','document_type_id','document_number','phone','tries','last_active','status','created_at','role_id'])
            ]
        ]);
    }

    //Deactivate user
    public function deactivate(Request $request, User $user) {

        if($user->status != 'Activo') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'Solo puede desactivar un usuario que se encuentre en estado Activo'
                ]
            ]);
        }

        $user->status = Enums\UserStatus::Inactivo;
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $user->only(['id','name','last_name','email','document_type_id','document_number','phone','tries','last_active','status','created_at','role_id'])
            ]
        ]);
    }

    //Activate user
    public function activate(Request $request, User $user) {

        if($user->status != 'Inactivo') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'Solo puede activar un usuario que se encuentre en estado Inactivo'
                ]
            ]);
        }

        $user->status = Enums\UserStatus::Activo;
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $user->only(['id','name','last_name','email','document_type_id','document_number','phone','tries','last_active','status','created_at','role_id'])
            ]
        ]);
    }

    //Reset Password
    public function reset_password(Request $request, User $user) {

        $password = Str::random(12);
        $user->password = Hash::make($password);
        $user->save();

        // enviar correo()

        return response()->json([
            'success' => true,
            'data' => [
                'Password reseteado exitosamente. Se envió correo a usuario',
            ]
        ]);
    }

    //Current User Clients list
    public function client_list(Request $request, User $user) {

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $user->clients
            ]
        ]);
    }

    //Avaliable clients to attach
    public function clients(Request $request, User $user) {

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => Client::select('id', 'name', 'last_name', 'mothers_name','customer_type','type')
                    ->where('type', 'Cliente')
                    ->whereNotIn('id', $user->clients
                    ->pluck('id'))
                    ->get()
            ]
        ]);
    }

    //Attach client to user
    public function attach_client(Request $request, User $user) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
        ]);
        if($val->fails()) return response()->json($val->messages());


        
        return response()->json([
            'success' => true,
            'data' => [
                'Cliente asignado.'
            ]
        ]);
    }


}
