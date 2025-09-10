<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
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

        if (Auth::attempt(array_merge($credentials, ['type' => 'Administrador']))) {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $user->createToken("basic")->plainTextToken,
                    'user' => $user->only(['id','name','email','document_type','document_number','phone','type']),
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

        return response()->json([
            'success' => true,
        ]);

    }
}
