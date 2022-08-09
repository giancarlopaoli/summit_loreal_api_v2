<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function profile_detail(Request $request) {

        $client = Client::select('name','last_name','mothers_name','document_type_id','email','address','accountable_email','customer_type', 'created_at as registered_at')
            ->with(['document_type:id,name'])
            ->find($request->client_id);

        $user = Auth::user()->only(['id','name','last_name','email','document_number','phone']);

        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client,
                'user' => $user
            ]
        ]);
    }

    public function clients_list(Request $request) {

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => Auth::user()->clients
            ]
        ]);
    }

    public function users_list(Request $request) {

        $users = Client::find($request->client_id)->users;
         
        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ]);
    }
}
