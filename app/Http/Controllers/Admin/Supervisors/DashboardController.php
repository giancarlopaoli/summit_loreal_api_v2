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

        $operations = Operation::where('operation_date', '>=', '2024-01-01')
            ->whereIn('operation_status_id', OperationStatus::whereIn('name', ['Contravalor recaudado','Facturado', 'Finalizado sin factura'])->get()->pluck('id'))
            ->where('detraction_paid', false)
            ->whereHas('client', function (Builder $query) {
                    $query->where('type', 'Cliente');
                })
            ->where(function ($query) {
                    $query->where('corfid_id', null)
                        ->orwhere('corfid_id', '!=' , 1);
                })
            ->get();

        return response()->json([
            'success' => true,
            'data' => [ 
                $operations

            ]
        ]);
    }
}
