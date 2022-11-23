<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;

class ClientsController extends Controller
{
    //Clients list
    public function list(Request $request) {
        $val = Validator::make($request->all(), [
            'type' => 'required|in:pending,approved,cenceled'
        ]);
        if($val->fails()) return response()->json($val->messages());


        $client = Client::get();
/*        $users = User::select('id','name','last_name','email','phone','tries','last_active','status','role_id')->with('role:id,name')->with('roles:id,name');

        if(isset($request->email) && $request->email != '') $users->where('email', 'like', '%'.$request->email.'%');
        if(isset($request->name) && $request->name != '') $users->where('name', 'like', '%'.$request->name.'%');
        if(isset($request->last_name) && $request->last_name != '') $users->where('last_name', 'like', '%'.$request->last_name.'%');*/

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $client
            ]
        ]);
    }
}
