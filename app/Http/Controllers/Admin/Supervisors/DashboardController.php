<?php

namespace App\Http\Controllers\Admin\Supervisors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Operation;
use App\Models\OperationStatus;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    // Dashboard
    public function dashboard(Request $request) {

        $indicators = DB::table('monthly_operations_view')
            ->selectRaw("sum(amount) as total_volume, sum(operations_number) as num_operations, round(sum(comission_amount),2) as total_comissions")
            ->selectRaw("(select count(distinct client_id) from operations_view where type in ('Compra','Venta')) as unique_clients")
            ->whereIn("type", ['Compra','Venta'])
            ->first();

        $graphs = DB::table('monthly_operations_view')
            ->selectRaw("year, sum(amount) as volume, sum(operations_number) as num_operations, round(sum(comission_amount),2) as comissions")
            ->selectRaw("(select count(distinct client_id) from operations_view where type in ('Compra','Venta') and year(operation_date) = year) as unique_clients")
            ->whereIn("type", ['Compra','Venta'])
            ->groupByRaw("year")
            ->orderByRaw('year desc')
            ->limit(7)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [ 
                'global_indicators' => [
                    $indicators
                ],
                'ghaphs' => [
                    'year' => $graphs->pluck('year'),
                    'volume' => $graphs->pluck('volume'),
                    'comissions' => $graphs->pluck('comissions'),
                    'num_operations' => $graphs->pluck('num_operations'),
                    'unique_clients' => $graphs->pluck('unique_clients'),
                ]
            ]
        ]);
    }
}
