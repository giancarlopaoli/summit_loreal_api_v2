<?php

namespace App\Http\Controllers\Admin\Vendors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Enums;
use App\Models\User;
use App\Models\Client;
use App\Models\VendorRange;
use App\Models\VendorSpread;
use App\Models\Operation;
use App\Models\OperationStatus;

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

        $operations = Operation::select('id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','operation_status_id','operation_date')
            ->where('operation_status_id',  OperationStatus::where('name', 'Disponible')->first()->id)
            ->where('post', true)
            ->where('class', Enums\OperationClass::Inmediata)
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
}
