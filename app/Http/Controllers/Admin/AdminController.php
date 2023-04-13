<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserStatus;
use App\Models\AccessLog;
use App\Models\User;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use PDF;

class AdminController extends Controller
{
    //
    public function login(Request $request) {
        $val = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $credentials = $request->only('email', 'password');

        if (Auth::attempt(array_merge($credentials, ['status' => UserStatus::Activo])) && Auth::user()->hasAnyRole('administrador','operaciones','proveedor','corfid','ejecutivos','supervisores')) {

            $request->session()->regenerate();

            $user = Auth::user();
            $user->tries = 0;
            $user->last_active = Carbon::now();
            $user->save();

            AccessLog::create([
                'ip' => $request->ip(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $user->createToken("basic")->plainTextToken,
                    'user' => $user->only(['id','name','last_name','email','phone','role']),
                    'roles' => $user->getRoleNames()
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

    public function has_permission(Request $request) {
        $val = Validator::make($request->all(), [
            'permission' => 'required|exists:permissions,name',
        ]);
        if($val->fails()) return response()->json($val->messages());

        return response()->json([
            'success' => true,
            'data' => [
                'permission' => Auth::user()->hasPermissionTo($request->permission)
            ]
        ]);
    }

    public function has_role(Request $request) {
        $val = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name',
        ]);
        if($val->fails()) return response()->json($val->messages());

        return response()->json([
            'success' => true,
            'data' => [
                'role' => Auth::user()->hasRole($request->role)
            ]
        ]);
    }

    public function test_pdf(Request $request) {

        //$pdf = PDF::loadHtml('hello world');

        /*$html = file_get_contents("https://billex.pe"); 
        $pdf = PDF::loadHtml($html);*/


        $pdf = PDF::loadView('pdf.sample');

        $pdf->render();
        $pdf->stream();
        
        return $pdf->download('pdf_file.pdf');
    }
}
