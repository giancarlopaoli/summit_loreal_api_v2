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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

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

        } 
        elseif ((User::where('email', $request->email)->where('status', 'Bloqueado')->count()>0) && Auth::user()->hasAnyRole('administrador','operaciones','proveedor','corfid','ejecutivos','supervisores')) {
            
            return response()->json([
                'errors' => 'Su usuario se encuentra bloqueado. Por favor contacte al administrador.',
            ], 403);
        }
        else {
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

    public function pase_a_produccion(Request $request) {
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();

        $users = User::where('role_id', 1);
        $role = Role::findByName('cliente');
        $role->users()->attach($users->pluck('id'));

        $users = User::where('role_id', 3);
        $role = Role::findByName('operaciones');
        $role->users()->attach($users->pluck('id'));

        $users = User::where('role_id', 4);
        $role = Role::findByName('proveedor');
        $role->users()->attach($users->pluck('id'));

        $users = User::where('role_id', 5);
        $role = Role::findByName('corfid');
        $role->users()->attach($users->pluck('id'));

        $user = User::where('id',483)->first()->assignRole('administrador');
        $user = User::where('id',483)->first()->assignRole('operaciones');
        $user = User::where('id',483)->first()->assignRole('proveedor');
        $user = User::where('id',483)->first()->assignRole('corfid');
        $user = User::where('id',483)->first()->assignRole('ejecutivos');


        return response()->json([
            'success' => true,
            $role
        ]);
    }

    public function instruction(Request $request, Operation $operation) {
        if($operation->type == 'Compra' || $operation->type == 'Venta'){

            $pen_amount = $operation->type == 'Compra' ? ($operation->amount*$operation->exchange_rate + $operation->comission_amount + $operation->igv) :  ($operation->amount*$operation->exchange_rate - $operation->comission_amount - $operation->igv);
            $final_exchange_rate = $operation->type == 'Compra' ? number_format($operation->exchange_rate + $operation->comission_spread/10000 ,4) : number_format($operation->exchange_rate - $operation->comission_spread/10000 ,4);

            $data = [
                    'username' => Str::of($operation->user->name)->ucfirst() . " " . Str::of($operation->user->last_name)->ucfirst(),
                    'client_name' => $operation->client->customer_type == 'PJ' ? $operation->client->name : $operation->client->name ." " . $operation->client->last_name . " " . $operation->client->mothers_name,
                    'code' => $operation->code,
                    'use_escrow_account' => $operation->use_escrow_account,
                    'operation_date' => date('d F Y', strtotime($operation->operation_date)),
                    'operation_time' => date('H:i', strtotime($operation->operation_date)),
                    'type' => $operation->type == 'Compra' ? 'comprar': 'vender',
                    'currency_sign' => $operation->currency->sign,
                    'amount' => number_format($operation->amount,2),
                    'exchange_rate' => number_format($operation->exchange_rate,4),
                    'comission_amount' => $operation->comission_amount,
                    'igv' => $operation->igv,
                    'final_exchange_rate' => $final_exchange_rate,
                    'pen_type' => $operation->type == 'Compra' ? 'depositar': 'recibir',
                    'pen_amount' => number_format($pen_amount,2),
                    'deposit_sign' => $operation->type == 'Compra' ? 'S/' : '$',
                    'deposit_amount' => $operation->type == 'Compra' ? number_format($pen_amount,2) : number_format($operation->amount,2),
                    'receive_sign' => $operation->type == 'Compra' ? '$' : 'S/',
                    'receive_amount' => $operation->type == 'Compra' ? number_format($operation->amount,2) : number_format($pen_amount,2),
                    'escrow_accounts' => $operation->escrow_accounts->load('bank','currency'),
                    'bank_accounts' => $operation->bank_accounts->load('bank','currency'),
                    'vendor_bank_accounts' => $operation->vendor_bank_accounts->load('bank','currency','client')
                ];

            $alto = 1720 + ($operation->bank_accounts->count() + $operation->escrow_accounts->count() + $operation->vendor_bank_accounts->count()) * 65 ;

            $pdf = PDF::loadView('pdf.instructionsbuyingselling', $data);

            $pdf->setPaper(array(0,0,595,$alto), 'portrait');
            $pdf->setOption('isFontSubsettingEnabled', true);
            $pdf->setOption('defaultMediaType', 'all');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'Poppins');
            $pdf->render();
            
            return $pdf->download('Instructivo_' . $operation->code .'.pdf');
        }
        else{  

            $financial_expenses = round($operation->amount * $operation->spread/10000,2);
            $spread = round(($operation->spread + 1)*$operation->exchange_rate - $operation->exchange_rate,2);
            $exchange_rate_selling = round($operation->exchange_rate + $spread/10000, 4) ;
            $counter_value = $operation->amount + $financial_expenses;

            $data = [
                'username' => Str::of($operation->user->name)->ucfirst() . " " . Str::of($operation->user->last_name)->ucfirst(),
                'client_name' => $operation->client->customer_type == 'PJ' ? $operation->client->name : $operation->client->name ." " . $operation->client->last_name . " " . $operation->client->mothers_name,
                'code' => $operation->code,
                'use_escrow_account' => $operation->use_escrow_account,
                'operation_date' => date('d F Y', strtotime($operation->operation_date)),
                'operation_time' => date('H:i', strtotime($operation->operation_date)),
                'currency_sign' => $operation->currency->sign,
                'amount' => number_format($operation->amount,2),
                'exchange_rate' => round($operation->exchange_rate,4),
                'exchange_rate_selling' => round($exchange_rate_selling,4),
                'comission_amount' => $operation->comission_amount,
                'igv' => $operation->igv,
                'counter_value' => number_format($counter_value,2),
                'deposit_amount' => $operation->client->type == 'PL' ? number_format($operation->amount,2) : number_format($counter_value + $operation->comission_amount + $operation->igv,2),
                'receive_amount' => $operation->client->type == 'PL' ? number_format($counter_value + $operation->comission_amount + $operation->igv,2) : number_format($operation->amount,2),
                'escrow_accounts' => $operation->escrow_accounts->load('bank','currency'),
                'bank_accounts' => $operation->bank_accounts->load('bank','currency'),
                'vendor_bank_accounts' => $operation->vendor_bank_accounts->load('bank','currency','client'),

            ];

            $alto = 1760 + ($operation->bank_accounts->count() + $operation->escrow_accounts->count() + $operation->vendor_bank_accounts->count()) * 65;

            $pdf = PDF::loadView('pdf.instructionsinterbank', $data);

            $pdf->setPaper(array(0,0,595,$alto), 'portrait');
            $pdf->setOption('isFontSubsettingEnabled', true);
            $pdf->setOption('defaultMediaType', 'all');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'Poppins');
            $pdf->render();
            
            return $pdf->download('Instructivo_' . $operation->code .'.pdf');
        }
    }
}
