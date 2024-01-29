<?php

namespace App\Http\Controllers\Admin\Supervisors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Client;

class ReportsController extends Controller
{
    //
    public function new_clients(Request $request) {
        $month = (isset($request->month)) ? $request->month : Carbon::now()->month;
        $year = (isset($request->year)) ? $request->year : Carbon::now()->year;
        $executive = (isset($request->executive_id)) ? " executive_id = " . $request->executive_id : '1';

        $new_clients = Client::select('id','executive_id','registered_at','customer_type')
            ->whereRaw($executive)
            ->selectRaw("if(customer_type='PN', CONCAT(name, ' ', last_name, ' ', mothers_name), name) as client_name")
            ->selectRaw("(select sum(ov.amount) from operations_view ov where ov.client_id = clients.id) as total_volume")
            ->selectRaw("(select count(ov.amount) from operations_view ov where ov.client_id = clients.id) as total_operations")
            ->selectRaw("(select ov.operation_date from operations_view ov where ov.client_id = clients.id order by ov.operation_date desc limit 1) as last_operation")
            ->whereRaw("(month(registered_at) = $month and year(registered_at) = $year)")
            ->with("executive:id,type","executive.user:id,name,last_name")
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'new_clients' => $new_clients
            ]
        ]);
    }
}
