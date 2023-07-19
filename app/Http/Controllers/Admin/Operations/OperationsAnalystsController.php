<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operation;
use App\Models\OperationsAnalyst;
use App\Models\OperationsAnalystLog;
use Carbon\Carbon;
use App\Enums;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class OperationsAnalystsController extends Controller
{
    //
    public function assign_analyst_to_operation(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'operations_analyst_id' => 'required|exists:operations_analysts,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $operation->operations_analyst_id = $request->operations_analyst_id;
        $operation->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Analista asignado exitosamente'
            ]
        ]);
    }

    public function analyst_list(Request $request) {
        $analysts = OperationsAnalyst::select('id','status','online','start_time','end_time')
            ->with('user:id,name,last_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'analyst' => $analysts,
            ]
        ]);
    }

    public function add_analyst(Request $request) {
        $val = Validator::make($request->all(), [
            'operations_analyst_id' => 'required|exists:operations_analysts,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        return response()->json([
            'success' => true,
            'data' => [
                'analyst' => $analysts,
            ]
        ]);
    }
}
