<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Budget;

class BudgetsController extends Controller
{
    //Areas list
    public function list_areas(Request $request) {

        $areas = Area::select('id','name','code')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'areas' => $areas
            ]
        ]);
    }

    //Add Area
    public function new_area(Request $request) {
        $val = Validator::make($request->all(), [
            'name' => 'required|string',
            'code' => 'required|string|min:3'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $insert = Area::create([
            'name' => $request->name,
            'code' => $request->code
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Área creada exitosamente'
            ]
        ]);
    }

    //Add Budget
    public function new_budget(Request $request) {
        $val = Validator::make($request->all(), [
            'area_id' => 'required|exists:mysql2.areas,id',
            'code' => 'required|string|min:3',
            'description' => 'required|string',
            'period' => 'required|integer',
            'initial_budget' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $insert = Budget::create([
            'area_id' => $request->area_id,
            'code' => $request->code,
            'description' => $request->description,
            'period' => $request->period,
            'initial_budget' => $request->initial_budget,
            'status' => 'Activo'
        ]); 

        return response()->json([
            'success' => true,
            'data' => [
                'Presupuesto creado exitosamente'
            ]
        ]);
    }

    //Budgets list
    public function list_budgets(Request $request) {

        $budgets = Area::select('id','name','code')
            ->with("budgets:id,area_id,code,description,period,initial_budget,final_budget,status")
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'budgets' => $budgets
            ]
        ]);
    }
}
