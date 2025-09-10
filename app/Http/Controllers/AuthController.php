<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPassword;

class AuthController extends Controller
{
    public function login(Request $request) {
        $val = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($val->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $val->errors()->toJson()
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt(array_merge($credentials, ['type' => function ($query) {
               $query->where('type', '!=', 'Administrador');
          }]))) {
            $request->session()->regenerate();

            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $user->createToken("basic")->plainTextToken,
                    'user' => $user->only(['id','name','email','document_type','document_number','phone','image','type','confirmed']),
                ]
            ]);

        } else {
            return response()->json([
                'errors' => 'Usuario o contraseña incorrectos',
            ], 403);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
        ]);

    }

    public function me(Request $request) {
        return response()->json([
            'success' => true,
            'data' => \auth()->user()
        ]);
    }

    public function forgot_password(Request $request) {
        $val = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($val->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $val->errors()->toJson()
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if($user != null) {
            $new_password = Str::random(10);

            $user->password = Hash::make($new_password);
            $user->save();

            //enviando mail
            $rpta_mail = Mail::to($request->email)->send(new ForgotPassword($user->id,$new_password));
        }

        return response()->json([
            'success' => true,
            'data' => [
                'Te enviamos un mail con tu nueva contraseña'
            ]
        ]);
    }
}
