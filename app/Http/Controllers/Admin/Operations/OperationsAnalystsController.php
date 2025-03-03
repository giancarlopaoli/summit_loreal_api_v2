<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operation;
use App\Models\OperationsAnalyst;
use App\Models\OperationsAnalystLog;
use App\Models\User;
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

    public function analysts_list(Request $request) {
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

    public function users_list(Request $request) {
        $analysts = User::select('id','name','last_name')
            ->whereIn('role_id',[2,3,6,7])
            ->where('status', 'Activo')
            ->whereNotIn('id', OperationsAnalyst::get()->pluck('id'))
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
            'user_id' => 'required|exists:users,id',
            'start_time' => 'required|string',
            'end_time' => 'required|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $analyst = OperationsAnalyst::create([
            'id' => $request->user_id,
            'status' => 'Activo',
            'online' => false,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'analyst' => $analyst,
            ]
        ]);
    }

    public function edit_analyst(Request $request, OperationsAnalyst $operations_analyst) {
        $val = Validator::make($request->all(), [
            'status' => 'required|in:Activo,Inactivo',
            'online' => 'required|in:1,0',
            'start_time' => 'required|string',
            'end_time' => 'required|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $operations_analyst->update([
            'status' => $request->status,
            'online' => $request->online,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
        ]);

        OperationsAnalystLog::create([
            'operations_analyst_id' => $operations_analyst->id,
            'online' => $request->online,
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'analyst' => $operations_analyst,
            ]
        ]);
    }

    public function analysts_history(Request $request) {
        $history = OperationsAnalystLog::select('id','operations_analyst_id','online','created_by','created_at')
            ->with('operations_analyst:id')
            ->with('operations_analyst.user:id,name,last_name')
            ->with('creator:id,name,last_name')
            ->whereRaw('created_at >= DATE_SUB(now(), INTERVAL 30 DAY)')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'analyst' => $history,
            ]
        ]);
    }
}
