<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\BankAccount;
use App\Models\EscrowAccount;
use App\Models\Operation;
use App\Models\OperationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Enums;
use Illuminate\Support\Facades\Storage;

class DailyOperationsController extends Controller
{
    public function daily_operations(Request $request) {
        $val = Validator::make($request->all(), [
            'date' => 'date',
            'status' => 'required|in:Todas,Pendientes,Finalizadas'
        ]);
        if($val->fails()) return response()->json($val->messages());

        ########### Configuration ##################

        $date = isset($request->date) ? $request->date : Carbon::now()->format('Y-m-d');
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $pendientes = OperationStatus::wherein('name', ['Disponible','Pendiente envio fondos','Pendiente fondos contraparte','Contravalor recaudado','Fondos enviados'])->get()->pluck('id');
        $finalizadas = OperationStatus::wherein('name', ['Facturado','Finalizado sin factura', 'Pendiente facturar'])->get()->pluck('id');
        $todas = OperationStatus::get()->pluck('id');


        if($request->status == 'Pendientes'){
            $status = $pendientes;
            $status_str = "(op1.operation_status_id in (" . substr($pendientes, 1, Str::length($pendientes)-2) . ") or op2.operation_status_id in (" . substr($pendientes, 1, Str::length($pendientes)-2) . ") )";
        }
        elseif($request->status == 'Finalizadas'){
            $status = $finalizadas;
            $status_str = "(op1.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and op2.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") )";
        }
        else{
            $status = $todas;
            $status_str = "(1)";
        }

        #############################################

        $indicators = Operation::selectRaw("coalesce(sum(amount),0) as total_amount, count(id) as num_operations")
            ->selectRaw("(select sum(op1.amount) from operations op1 where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ")) as monthly_amount")
            ->selectRaw("(select count(op1.amount) from operations op1 where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ")) as monthly_operations")
            ->whereRaw("date(operation_date) = '$date'")
            ->whereIn('operation_status_id', $finalizadas)
            ->get();

        $graphs = Operation::
            selectRaw("day(operation_date) as dia, sum(amount) as amount, count(amount) as num_operations")
            ->selectRaw("(select sum(amount) from operations as op2 where month(op2.operation_date) = month(CURRENT_TIMESTAMP) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= day(operations.operation_date) and operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and type in ('Compra','Venta')) as accumulated_amount")

            ->selectRaw("(select sum(amount) from operations as op2 where month(op2.operation_date) = month(CURRENT_TIMESTAMP) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= day(operations.operation_date) and operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and type in ('Compra','Venta')) as accumulated_num_operations")

            ->whereIn('operation_status_id', $finalizadas)
            ->whereIn('type', ['Compra', 'Venta'])
            ->whereRaw('month(operation_date) = month(CURRENT_TIMESTAMP) and year(operation_date) = year(CURRENT_TIMESTAMP) ')
            ->groupByRaw("day(operation_date)")
            ->orderByRaw('day(operation_date)')
            ->get();

        $pending_operations = Operation::select('id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date')
            ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
            ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate * (1 + spread/10000),4))) as final_exchange_rate")
            ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(amount + round(amount * spread/10000, 2 ) + comission_amount + igv, 2)) ) as counter_value")
            ->selectRaw("if(type = 'Interbancaria', round(amount * spread/10000, 2 ) , null ) as financial_expenses")
            ->whereIn('operation_status_id', OperationStatus::wherein('name', ['Disponible','Cancelado'])->get()->pluck('id'))
            ->whereRaw("date(operation_date) = '$date'")
            ->with('client:id,name,last_name,mothers_name,customer_type,type')
            ->with('currency:id,name:sign')
            ->with('status:id,name')
            ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
            ->with('bank_accounts.currency:id,name,sign')
            ->with('bank_accounts.bank:id,name,shortname,image')
            ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
            ->with('escrow_accounts.currency:id,name,sign')
            ->with('escrow_accounts.bank:id,name,shortname,image')
            ->get();

        $matched_operations = DB::table('operation_matches')
            ->select('operation_id', 'matched_id')
            ->join('operations as op1', 'op1.id', "=", "operation_matches.operation_id")
            ->join('operations as op2', 'op2.id', "=", "operation_matches.matched_id")
            ->whereRaw("date(operation_matches.created_at) = '$date'")
            ->whereRaw("$status_str")
            ->get();


        $matched_operations->each(function ($item, $key) {

            $item->created_operation = Operation::where('id',$item->operation_id)
                ->select('operations.id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date')
                ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate * (1 + spread/10000),4))) as final_exchange_rate")
                ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(amount + round(amount * spread/10000, 2 ) + comission_amount + igv, 2)) ) as counter_value")
                ->selectRaw("if(type = 'Interbancaria', round(amount * spread/10000, 2 ) , null ) as financial_expenses")
                ->with('status:id,name')
                ->with('client:id,name,last_name,mothers_name,customer_type,type')
                ->with('currency:id,name:sign')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('bank_accounts.bank:id,name,shortname,image')
                ->with('bank_accounts.currency:id,name,sign')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('escrow_accounts.currency:id,name,sign')
                ->with('escrow_accounts.bank:id,name,shortname,image')
                ->first();

            $item->matched_operation = Operation::where('id',$item->matched_id)
                ->select('operations.id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date')
                ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate * (1 + spread/10000),4))) as final_exchange_rate")
                ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(amount + round(amount * spread/10000, 2 ) + comission_amount + igv, 2)) ) as counter_value")
                ->selectRaw("if(type = 'Interbancaria', round(amount * spread/10000, 2 ) , null ) as financial_expenses")
                ->with('status:id,name')
                ->with('client:id,name,last_name,mothers_name,customer_type,type')
                ->with('currency:id,name:sign')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('bank_accounts.currency:id,name,sign')
                ->with('bank_accounts.bank:id,name,shortname,image')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('escrow_accounts.currency:id,name,sign')
                ->with('escrow_accounts.bank:id,name,shortname,image')
                ->first();
        });


        return response()->json([
            'success' => true,
            'data' => [
                'status' =>  $status,
                'indicators' => $indicators,
                'graphs' => $graphs,
                'pending_operations' => $pending_operations,
                'matched_operations' => $matched_operations,
            ]
        ]);

    }


    public function vendor_list(Request $request) {

        return response()->json([
            'success' => true,
            'data' => [
                'vendors' => Client::select('id','name','last_name','type')->where('type', 'PL')->get()
            ]
        ]);
    }

    public function match_operation(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        ####### Validating operation is not previusly matched ##########
        $operation_match = DB::table('operation_matches')
            ->where("operation_id", $operation->id)
            ->get();

        if($operation_match->count() > 0) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación ya se encuentra emparejada'
                ]
            ], 404);
        }

        ######### Creating vendor operation #############

        $op_code = Carbon::now()->format('YmdHisv') . rand(0,9);
        $status_id = OperationStatus::where('name', 'Pendiente envio fondos')->first()->id;

        $matched_operation = Operation::create([
            'code' => $op_code,
            'class' => Enums\OperationClass::Inmediata,
            'type' => ($operation->type == "Compra") ? 'Venta' : ($operation->type == "Venta" ? 'Compra' : 'Interbancaria'),
            'client_id' => $request->client_id,
            'user_id' => auth()->id(),
            'amount' => $operation->amount,
            'currency_id' => $operation->currency_id,
            'exchange_rate' => $operation->exchange_rate,
            'comission_spread' => 0,
            'comission_amount' => 0,
            'igv' => 0,
            'spread' => ($operation->type == "Interbancaria") ? $operation->spread : 0,
            'operation_status_id' => $status_id,
            'operation_date' => Carbon::now(),
            'post' => false
        ]);

        if($matched_operation){
            foreach ($operation->bank_accounts as $bank_account_data) {
                
                $escrow_account = EscrowAccount::where('bank_id',$bank_account_data->bank_id)
                    ->where('currency_id', $bank_account_data->currency_id)
                    ->first();

                if(!is_null($escrow_account)){
                    $matched_operation->escrow_accounts()->attach($escrow_account->id, [
                        'amount' => $bank_account_data->pivot->amount + $bank_account_data->pivot->comission_amount,
                        'comission_amount' => 0,
                        'created_at' => Carbon::now()
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en cuenta bancaria'
                        ]
                    ], 404);
                }
            }

            foreach ($operation->escrow_accounts as $escrow_account_data) {
                
                $bank_account = BankAccount::where('bank_id',$escrow_account_data->bank_id)
                    ->where('client_id', $request->client_id)
                    ->where('currency_id', $escrow_account_data->currency_id)
                    ->first();

                if(!is_null($bank_account)){
                    $matched_operation->bank_accounts()->attach($bank_account->id, [
                        'amount' => $escrow_account_data->pivot->amount - $escrow_account_data->pivot->comission_amount,
                        'comission_amount' => 0,
                        'created_at' => Carbon::now()
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en cuenta bancaria'
                        ]
                    ], 404);
                }

            }

            $operations_matches = $operation->matches()->attach($matched_operation->id, ['created_at' => Carbon::now()]);

            $operation->operation_status_id = $status_id;
            $operation->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'Operación emparejada exitosamente'
            ]
        ]);
    }

    public function cancel(Request $request, Operation $operation) {

        $operation->operation_status_id = OperationStatus::where('name', 'Cancelado')->first()->id;
        $operation->canceled_at = Carbon::now();
        $operation->save();

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function confirm_funds(Request $request, Operation $operation) {

        if( $operation->operation_status_id != OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación no se encuentra en estado Pendiente envio fondos'
                ]
            ], 404);
        }

        if($operation->matches->count() > 0) {
            if($operation->matches[0]->operation_status_id == OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){
                $operation->operation_status_id = OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id;
                $operation->funds_confirmation_date = Carbon::now();
                $operation->save();
            }
            elseif($operation->matches[0]->operation_status_id == OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id){
                $operation->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                $operation->funds_confirmation_date = Carbon::now();
                $operation->save();

                $operation->matches[0]->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                $operation->matches[0]->save();
            }
            else{
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el estado de la operación emparejadora'
                    ]
                ], 404);
            }
        }
        elseif ($operation->matched_operation->count() > 0) {
            if($operation->matched_operation[0]->operation_status_id == OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){
                $operation->operation_status_id = OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id;
                $operation->funds_confirmation_date = Carbon::now();
                $operation->save();
            }
            elseif($operation->matched_operation[0]->operation_status_id == OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id){
                $operation->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                $operation->funds_confirmation_date = Carbon::now();
                $operation->save();

                $operation->matched_operation[0]->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                $operation->matched_operation[0]->save();
            }
            else{
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el estado de la operación emparejadora'
                    ]
                ], 404);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function upload_voucher(Request $request) {
        /*$val = Validator::make($request->all(), [
            'operation_id' => 'required|exists:operations,id',
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());


        logger('Archivo adjunto: DailyOperationsController@upload_voucher', ["operation_id" => $request->client_id]);
        
        
        if (Storage::disk('s3')->exists('test/' . $request->name)) {
            return Storage::disk('s3')->download('test/' . $request->name);
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Archivo no encontrado'
                ]
            ]);
        }

        return Storage::disk('s3')->download('test/' . $request->name);*/
    }
    
    public function download_file(Request $request) {

        /*return response()->json([
            'success' => true,
            'data' => [
                'operation' => Storage::disk('s3')->download('test/' . $request->name)
            ]
        ]);*/

        if (Storage::disk('s3')->exists('test/' . $request->name)) {
            return Storage::disk('s3')->download('test/' . $request->name);
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Archivo no encontrado'
                ]
            ]);
        }

        return Storage::disk('s3')->download('test/' . $request->name);
    }
    
}
