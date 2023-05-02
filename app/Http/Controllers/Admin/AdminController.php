<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserStatus;
use App\Models\AccessLog;
use App\Models\User;
use App\Models\Operation;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
 
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

    public function instruction(Request $request, Operation $operation) {

        //$pdf = PDF::loadHtml('hello world');

        //$html = file_get_contents("https://instructivo.romacperu.com/"); 
        //$pdf = PDF::loadHtml($html);

        $pen_amount = $operation->type == 'Compra' ? ($operation->amount*$operation->exchange_rate + $operation->comission_amount + $operation->igv) :  ($operation->amount*$operation->exchange_rate - $operation->comission_amount - $operation->igv);
        $final_exchange_rate = $operation->type == 'Compra' ? round($operation->exchange_rate + $operation->comission_spread/10000 ,4) : round($operation->exchange_rate - $operation->comission_spread/10000 ,4);


        $data = [
                'username' => Str::of($operation->user->name)->ucfirst(),
                'client_name' => $operation->client->cutomer_type == 'PJ' ? $operation->client->name : $operation->client->name ." " . $operation->client->last_name . " " . $operation->client->mothers_name,
                'code' => $operation->code,
                'operation_date' => date('d F Y', strtotime($operation->operation_date)),
                'operation_time' => date('H:i', strtotime($operation->operation_date)),
                'type' => $operation->type == 'Compra' ? 'comprar': 'vender',
                'currency_sign' => $operation->currency->sign,
                'amount' => number_format($operation->amount),
                'exchange_rate' => round($operation->exchange_rate,4),
                'comission_amount' => $operation->comission_amount,
                'igv' => $operation->igv,
                'final_exchange_rate' => $final_exchange_rate,
                'pen_type' => $operation->type == 'Compra' ? 'depositar': 'recibir',
                'pen_amount' => number_format($pen_amount),
                'deposit_sign' => $operation->type == 'Compra' ? 'S/' : '$',
                'deposit_amount' => $operation->type == 'Compra' ? number_format($pen_amount) : number_format($operation->amount),
                'receive_sign' => $operation->type == 'Compra' ? '$' : 'S/',
                'receive_amount' => $operation->type == 'Compra' ? number_format($operation->amount) : number_format($pen_amount),
                'escrow_accounts' => $operation->escrow_accounts->load('bank','currency'),
                'bank_accounts' => $operation->bank_accounts->load('bank','currency')
            ];

/*        return response()->json([
            'success' => true,
            'data' => $data
        ]);*/

        $alto = 1720 + ($operation->bank_accounts->count() + $operation->escrow_accounts->count()) * 65;
        //$alto = 1850; op 51
        //$alto = 1980; 

        $pdf = PDF::loadView('pdf.instructionsbuyingselling', $data);

        $pdf->setPaper(array(0,0,595,$alto), 'portrait');
        $pdf->setOption('isFontSubsettingEnabled', true);
        $pdf->setOption('defaultMediaType', 'all');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'Poppins');

        $pdf->render();
        //$pdf->stream();
        
        return $pdf->download('Instructivo_' . $operation->code .'.pdf');
    }
}
