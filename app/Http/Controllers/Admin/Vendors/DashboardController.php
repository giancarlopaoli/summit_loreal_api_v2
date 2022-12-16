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

/*        $ranges = Range::select('id','min_range','max_range','comission_open','comission_close','spread_open','spread_close','active')->get();
*/
        return response()->json([
            'success' => true,
            'data' => [
                'ranges' => 'test'
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

        $operations = Operation::select('id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','operation_status_id','operation_date')
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

        $operation = $operation
            ->load('currency:id,name,sign')
            ->load('bank_accounts:id,bank_id,currency_id','bank_accounts.bank:id,shortname','bank_accounts.currency:id,name,sign')
            ->load('escrow_accounts:id,bank_id,currency_id','escrow_accounts.bank:id,shortname','escrow_accounts.currency:id,name,sign')
            ->only('id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','operation_status_id','operation_date','currency','bank_accounts','escrow_accounts');

        $vendor = Client::select('id','name')
            ->where('id', $request->client_id)
            ->get();

        $escrow_account_list = array();
        $bank_account_list = array();

        foreach ($operation['bank_accounts'] as $bank_account_data) {

            $escrow_account = EscrowAccount::select('id','bank_id','account_number','cci_number','currency_id')
                ->where('bank_id',$bank_account_data->bank_id)
                ->where('currency_id', $bank_account_data->currency_id)
                ->with('currency:id,name,sign')
                ->with('bank:id,shortname')
                ->first();

            if(!is_null($escrow_account)){
                array_push($escrow_account_list, $escrow_account);
            }
            else{
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en cuenta fideicomiso'
                    ]
                ], 404);
            }
        }

        foreach ($operation['escrow_accounts'] as $escrow_account_data) {
            
            $bank_account = BankAccount::select('id','client_id','bank_id','account_number','cci_number','currency_id')
                ->where('bank_id', $escrow_account_data->bank_id)
                ->where('client_id', $request->client_id)
                ->where('currency_id', $escrow_account_data->currency_id)
                ->with('currency:id,name,sign')
                ->with('bank:id,shortname')
                ->first();

            if(!is_null($bank_account)){
                array_push($bank_account_list, $bank_account);
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

        $operations = Operation::select('id','code','class','type','amount','exchange_rate','operation_status_id')
            ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
            ->where('client_id', $request->client_id)
            ->whereIn('operation_status_id', $pendientes)
            ->whereRaw("operation_status_id in (" . substr($pendientes, 1, Str::length($pendientes)-2) . ")")
            ->with('status:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'operations' => $operations
            ]
        ]);
    }
}
