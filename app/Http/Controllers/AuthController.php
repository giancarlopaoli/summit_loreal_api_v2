<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt(array_merge($credentials, ['status' => UserStatus::Activo]))) {

            $request->session()->regenerate();

            $user = Auth::user();
            //$user->load("assigned_client");

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'assigned_client' => $user->assigned_client
                ]
            ], 200);

        } else
        {
            return response()->json([
                'errors' => 'No cuenta con sufientes permisos',
            ], 403);
        }
    }

    public function logout(Request $request)
    {
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
}
