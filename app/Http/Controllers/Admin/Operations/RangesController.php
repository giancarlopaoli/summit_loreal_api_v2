<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Range;
use App\Models\IbopsRange;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RangesController extends Controller
{
    //Ranges list
    public function list(Request $request) {

        $ranges = Range::select('id','min_range','max_range','comission_open','comission_close','spread_open','spread_close','active')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ranges' => $ranges
            ]
        ]);
    }

    //Edit Range
    public function edit(Request $request, Range $range) {
        $val = Validator::make($request->all(), [
            'min_range' => 'required|numeric',
            'max_range' => 'required|numeric',
            'comission_open' => 'required|numeric',
            'comission_close' => 'required|numeric',
            'spread_open' => 'required|numeric',
            'spread_close' => 'required|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $range->update(array_merge($request->only(['min_range','max_range','comission_open','comission_close','spread_open','spread_close']), ["modified_by" => auth()->id()]));

        return response()->json([
            'success' => true,
            'data' => [
                'Rango editado exitosamente'
            ]
        ]);
    }

    //Activar / Desactivar Rango
    public function active(Request $request, Range $range) {
        $val = Validator::make($request->all(), [
            'active' => 'required|boolean'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($range->active == $request->active){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error al actualizar el estado del Rango'
                ]
            ]);
        }

        $range->update(["active" => $request->active, "modified_by" => auth()->id()]);

        return response()->json([
            'success' => true,
            'data' => [
                'Estado de Rango actualizado exitosamente'
            ]
        ]);
    }

    //ITBC Ranges list
    public function list_itbc(Request $request) {

        $itbc_ranges = IbopsRange::select('id','min_range','max_range','currency_id','comission_spread','spread','modified_by')->with('currency:id,name,sign','updater:id,name,last_name')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'itbc_ranges' => $itbc_ranges
            ]
        ]);
    }

    //Edit Range
    public function edit_itbc(Request $request, IbopsRange $itbc_range) {
        $val = Validator::make($request->all(), [
            'min_range' => 'required|numeric',
            'max_range' => 'required|numeric',
            'comission_spread' => 'required|numeric',
            'spread' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $itbc_range->update(array_merge($request->only(['min_range','max_range','comission_spread','spread']), ["modified_by" => auth()->id()]));

        return response()->json([
            'success' => true,
            'data' => [
                'Rango Interbancario editado exitosamente'
            ]
        ]);
    }
}
