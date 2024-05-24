<?php

namespace App\Http\Controllers\Admin\Vendors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Enums;
use App\Models\User;
use App\Models\Client;
use App\Models\VendorRange;
use App\Models\VendorSpread;
use App\Models\Operation;
use App\Models\OperationStatus;
use App\Models\EscrowAccount;
use App\Models\Configuration;
use App\Models\BankAccount;
use App\Events\AvailableOperations;

class DashboardController extends Controller
{
    //Vendors list
    public function vendors(Request $request) {

        $vendors = User::where('id', auth()->id())
            ->with('active_clients', function ($query) {
                $query->where('type','=','PL');
            })
            ->first()->only('active_clients');

        return response()->json([
            'success' => true,
            'data' => [
                'vendors' => $vendors
            ]
        ]);
    }

    //Ranges list
    public function indicators(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        // Tablero de indicadores globales
        $main_indicators = Operation::selectRaw('sum(amount) as monthly_amount, count(amount) as monthly_num_operations')
            ->selectRaw('(select sum(amount) from operations op1 where op1.client_id = '. $request->client_id . ' and operation_status_id = ' . OperationStatus::where('name', 'Finalizado sin factura')->first()->id . ' and DATE(operation_date) = DATE(current_timestamp) ) as today_amount')
            ->selectRaw('(select count(amount) from operations op1 where op1.client_id = '. $request->client_id . ' and operation_status_id = ' . OperationStatus::where('name', 'Finalizado sin factura')->first()->id . ' and DATE(operation_date) = DATE(current_timestamp) ) as today_num_operations')
            ->where('client_id', $request->client_id)
            ->where('operation_status_id', OperationStatus::where('name', 'Finalizado sin factura')->first()->id)
            ->whereIn('type', ['Compra', 'Venta'])
            ->whereRaw('(MONTH(operation_date) = MONTH(CURRENT_TIMESTAMP))')
            ->first();

        $month_operations = Operation::selectRaw("day(operation_date) as dia, sum(amount) as amount, count(amount) as num_operations")
            ->where('operation_status_id', OperationStatus::where('name', 'Finalizado sin factura')->first()->id)
            ->where('client_id', $request->client_id)
            ->whereIn('type', ['Compra', 'Venta'])
            ->whereRaw('(MONTH(operation_date) = MONTH(CURRENT_TIMESTAMP) and YEAR(operation_date) = YEAR(CURRENT_TIMESTAMP))')
            ->groupByRaw("day(operation_date)")
            ->orderByRaw('day(operation_date)')
            ->get();

        $today_operations = Operation::selectRaw("hour(operation_date) as hora, sum(amount) as amount, count(amount) as num_operations")
            ->where('operation_status_id', OperationStatus::where('name', 'Finalizado sin factura')->first()->id)
            ->where('client_id', $request->client_id)
            ->whereIn('type', ['Compra', 'Venta'])
            ->whereRaw('(date(operation_date) = date(current_timestamp))')
            ->groupByRaw("hour(operation_date)")
            ->orderByRaw('hour(operation_date)')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'monthly_indicators' => [
                    "day" => $month_operations->pluck('dia'),
                    "amount" => $month_operations->pluck('amount'),
                    "num_operations" => $month_operations->pluck('num_operations')

                ],
                'today_operations' => [
                    "hour" => $today_operations->pluck('hora'),
                    "amount" => $today_operations->pluck('amount'),
                    "num_operations" => $today_operations->pluck('num_operations')
                ],
                "main_indicators" => $main_indicators
            ]
        ]);
    }

    //Spread list
    public function spreads(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $spreads = VendorSpread::select('vendor_spreads.id','vendor_spreads.vendor_range_id','vendor_spreads.buying_spread','vendor_spreads.selling_spread')
            ->where('vendor_spreads.active', true)
            ->join('vendor_ranges', 'vendor_ranges.id', '=', 'vendor_spreads.vendor_range_id')
            ->whereRaw("vendor_ranges.vendor_id =  $request->client_id ")
            ->with('vendor_range:id,vendor_id,min_range,max_range')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'spreads' => $spreads
            ]
        ]);
    }

    //Edit Vendor Spread
    public function edit_spread(Request $request, VendorSpread $vendor_spread) {
        $val = Validator::make($request->all(), [
            'buying_spread' => 'required|numeric',
            'selling_spread' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $vendor_spread->update($request->only(["selling_spread","buying_spread"]));

        return response()->json([
            'success' => true,
            'data' => [
                'Spread actualizado exitosamente'
            ]
        ]);
    }
    
    //Delete Vendor Spread
    public function delete_spread(Request $request, VendorSpread $vendor_spread) {
        $vendor_spread->update(['active' => false]);

        return response()->json([
            'success' => true,
            'data' => [
                'Spread eliminado exitosamente'
            ]
        ]);
    }

    public function delete_all_spreads(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $client = Client::find($request->client_id)->vendor_ranges;
        $vendor_spread = VendorSpread::wherein('vendor_range_id', $client->pluck('id'))->where('active', true);

        $vendor_spread->update([
            'active' => false,
            "user_id" => auth()->id(),
            "updated_at" => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Spreads eliminados exitosamente'
            ]
        ]);
    }

    //Register Vendor Spread
    public function register_spreads(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        VendorSpread::join('vendor_ranges', 'vendor_ranges.id', '=', 'vendor_spreads.vendor_range_id')
            ->whereRaw("vendor_ranges.vendor_id =  $request->client_id ")
            ->where("vendor_spreads.active", true)
            ->update(["vendor_spreads.active" => false]);

        foreach($request->ranges as $range) {
            VendorSpread::create([
                "vendor_range_id" => $range['vendor_range_id'],
                "buying_spread" => $range['buying_spread'],
                "selling_spread" => $range['selling_spread'],
                "active" => true,
                "user_id" => auth()->id()
            ]);
        };

        return response()->json([
            'success' => true,
            'data' => [
                'Spreads registrados exitosamente',
            ]
        ]);
    }

    //Ranges list
    public function ranges(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $ranges = VendorRange::select('id','min_range','max_range')->where('vendor_id', $request->client_id)->where('active', true)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ranges' => $ranges
            ]
        ]);
    }

    //Edit user
    public function edit_price(Request $request, VendorRange $vendor_range) {
        $val = Validator::make($request->all(), [
            'compra' => 'required|numeric',
            'venta' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $vendor_range->update($request->only(["compra","venta"]));

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $user->only(['id','name','last_name','email','document_type_id','document_number','phone','tries','last_active','status','created_at','role_id'])
            ]
        ]);
    }
    
    //Ranges list
    public function avaliable_operations(Request $request) {
        /*$val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());*/

        $date = Carbon::now()->format('Y-m-d');

        $operations = Operation::select('id','code','class','client_id','user_id','amount','currency_id','exchange_rate','operation_status_id','operation_date')
            ->selectRaw("if(type ='Compra', 'Venta','Compra') as type")
            ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
            ->where('operation_status_id',  OperationStatus::where('name', 'Disponible')->first()->id)
            ->where('post', true)
            ->where('class', Enums\OperationClass::Inmediata)
            ->whereRaw("date(operation_date) = '$date'")
            ->with('currency:id,name,sign')
            ->with('bank_accounts:id,bank_id,currency_id','bank_accounts.bank:id,shortname','bank_accounts.currency:id,name,sign')
            ->with('escrow_accounts:id,bank_id,currency_id','escrow_accounts.bank:id,shortname','escrow_accounts.currency:id,name,sign')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'operations' => $operations
            ]
        ]);
    }

    //Ranges list
    public function test(Request $request) {

        AvailableOperations::dispatch();

        return response()->json([
            'success' => true,
            'data' => [
                'operations'
            ]
        ]);
    }

    //Ranges list
    public function operation_detail(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $operation->conversion_amount = round($operation->amount * $operation->exchange_rate,2);
        $operation = $operation
            ->load('currency:id,name,sign')
            ->load('bank_accounts:id,bank_id,currency_id','bank_accounts.bank:id,shortname','bank_accounts.currency:id,name,sign')
            ->load('escrow_accounts:id,bank_id,currency_id','escrow_accounts.bank:id,shortname','escrow_accounts.currency:id,name,sign')
            ->only('id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','operation_status_id','operation_date','conversion_amount','currency','bank_accounts','escrow_accounts');

        $vendor = Client::select('id','name')
            ->where('id', $request->client_id)
            ->get();

        $escrow_account_list = array();
        $bank_account_list = array();

        foreach ($operation['bank_accounts'] as $bank_account_data) {

            $escrow_account = EscrowAccount::select('id','bank_id','account_number','cci_number','currency_id')
                ->where('active',true)
                ->where('bank_id',$bank_account_data->bank_id)
                ->where('currency_id', $bank_account_data->currency_id)
                ->with('currency:id,name,sign')
                ->with('bank:id,shortname,image')
                ->first();

            $vendor_escrow = BankAccount::where('bank_id', $bank_account_data->bank_id)
                ->where('client_id', $request->client_id)
                ->where('currency_id', $bank_account_data->currency_id)
                ->get();

            if(!is_null($escrow_account) && $vendor_escrow->count() > 0){
                $escrow_account->amount = $bank_account_data->pivot->amount + $bank_account_data->pivot->comission_amount;
                array_push($escrow_account_list, $escrow_account);
            }
            else{

                // Si es banco Pichincha que devuelva error porque solo lo debe tomar coril (banbif tb por si es mi banco)
                if($bank_account_data->bank_id == 8 || $bank_account_data->bank_id == 6){
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en cuenta fideicomiso'
                        ]
                    ], 200);
                }

                $escrow_account = EscrowAccount::select('id','bank_id','account_number','cci_number','currency_id')
                ->where('bank_id', Configuration::where('shortname', 'DEFAULTBANK')->first()->value)
                ->where('currency_id', $bank_account_data->currency_id)
                ->with('currency:id,name,sign')
                ->with('bank:id,shortname,image')
                ->first();

                if(!is_null($escrow_account)){
                    $escrow_account->amount = $bank_account_data->pivot->amount + $bank_account_data->pivot->comission_amount;
                    array_push($escrow_account_list, $escrow_account);
                }
                else{

                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en cuenta fideicomiso'
                        ]
                    ], 200);
                }
            }
        }

        foreach ($operation['escrow_accounts'] as $escrow_account_data) {
            
            $bank_account = BankAccount::select('id','client_id','bank_id','account_number','cci_number','currency_id')
                ->where('bank_id', $escrow_account_data->bank_id)
                ->where('client_id', $request->client_id)
                ->where('currency_id', $escrow_account_data->currency_id)
                ->with('currency:id,name,sign')
                ->with('bank:id,shortname,image')
                ->first();

            if(!is_null($bank_account)){
                $bank_account->amount = $escrow_account_data->pivot->amount - $escrow_account_data->pivot->comission_amount;
                array_push($bank_account_list, $bank_account);
            }
            else{
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en cuenta bancaria'
                    ]
                ], 200);
            }

        }

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation,
                'vendor_escrow_accounts' => $escrow_account_list,
                'vendor_bank_accounts' => $bank_account_list
            ]
        ]);
    }

    //Operations in progress list
    public function operations_in_progress(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $pendientes = OperationStatus::wherein('name', ['Disponible','Pendiente envio fondos','Pendiente fondos contraparte','Contravalor recaudado','Fondos enviados'])->get()->pluck('id');

        $operations = Operation::select('id','code','class','type','amount','exchange_rate','operation_status_id','currency_id')
            ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
            ->where('client_id', $request->client_id)
            ->whereRaw("operation_status_id in (" . substr($pendientes, 1, Str::length($pendientes)-2) . ")")
            ->with('status:id,name')
            ->with('currency:id,name,sign')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'operations' => $operations
            ]
        ]);
    }

    //Report
    public function report(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $operations = Operation::selectRaw("month(operation_date) as mes, sum(amount) as amount, count(amount) as num_operations")
            ->where('operation_status_id', OperationStatus::where('name', 'Finalizado sin factura')->first()->id)
            ->where('client_id', $request->client_id)
            ->whereIn('type', ['Compra', 'Venta'])
            ->whereRaw('((year(operation_date)-2000)*12 + MONTH(operation_date)) >= ((year(CURRENT_TIMESTAMP)-2000)*12 + MONTH(CURRENT_TIMESTAMP)-12)')
            ->groupByRaw("month(operation_date)")
            ->orderByRaw('month(operation_date)')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'operations' => [
                    "month" => $operations->pluck('mes'),
                    "amount" => $operations->pluck('amount'),
                    "num_operations" => $operations->pluck('num_operations')

                ]
            ]
        ]);
    }
}
