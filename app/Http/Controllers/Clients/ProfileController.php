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

        $user = User::select('id','name','last_name','email','document_number','phone')
                ->find(Auth::user());

         return response()->json([
             'success' => true,
             'data' => [
                 'client' => $client,
                 'user' => $user
             ]
         ]);
    }
}
