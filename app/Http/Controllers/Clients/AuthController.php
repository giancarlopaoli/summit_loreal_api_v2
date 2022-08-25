<?php

namespace App\Http\Controllers\Clients;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\User;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        if (Auth::attempt(array_merge($credentials, ['status' => UserStatus::Activo]))) {

            $request->session()->regenerate();

            $user = Auth::user();
            $user->tries = 0;
            $user->last_login = Carbon::now();
            $user->save();

            AccessLog::create([
                'ip' => $request->ip(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'token' => $user->createToken("basic")->plainTextToken,
                    'assigned_client' => $user->assigned_client
                ]
            ]);

        } else {
            // Add login attempt
            $user = User::where('email', $credentials['email'])->first();
            if($user != null) {
                $user->tries++;

                // Check login attempts exceeds 5
                if($user->tries >= 5) {
                    $user->status = UserStatus::Bloqueado;
                }

                $user->save();
            }

            return response()->json([
                'errors' => 'Usuario o contraseña incorrectos',
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
