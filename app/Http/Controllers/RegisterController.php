<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterNotification;

class RegisterController extends Controller
{
    //
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|unique:users,email',
            'phone' => 'required|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'type' => 'required|in:Participante,Expositor,Administrador',
            'document_type' => 'required|string',
            'document_number' => 'required|string',
            'preferences' => 'required|string',
            'password' => 'required|string',
            'accepts_publicity' => 'required|string'
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'city' => $request->city,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'preferences' => $request->preferences,
            'type' => $request->type,
            'password' =>  Hash::make($request->password),
            'accepts_publicity' => $request->accepts_publicity
        ]);

        $rpta_mail = Mail::to($request->email)->bcc('giancarlopaoli@gmail.com')->send(new RegisterNotification());

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);

        /*return response()->json([
            'success' => false,
            'data' => [
                'El evento ha finalizado'
            ]
        ]);*/
    }

    public function countries(Request $request) {
        return response()->json([
            'success' => true,
            'data' => Country::select('id','name','prefix','phone_code')->get()
        ]);
    }
}