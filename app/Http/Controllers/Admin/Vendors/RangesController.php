<?php

namespace App\Http\Controllers\Admin\Vendors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VendorRange;

class RangesController extends Controller
{
    //Disable Range
    public function delete_range(Request $request, VendorRange $vendor_range) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($vendor_range->vendor_id != $request->client_id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Rango no encontrado'
                ]
            ]);
        }

        $vendor_range->update([
            "active" => false,
            "updated_by" => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Rango eliminado exitosamente'
            ]
        ]);
    }

    //create Range
    public function register_range(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
            'min_range' => 'required|numeric',
            'max_range' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $vendor_ranges = VendorRange::where('vendor_id', $request->client_id)
            ->where('active', true)
            ->get();

        foreach($vendor_ranges as $range) {
            if( ($range->min_range >= $request->min_range && $range->min_range <= $request->max_range) || ($range->max_range >= $request->min_range && $range->max_range <= $request->max_range) || ($request->min_range >= $range->min_range && $request->min_range <= $range->max_range) || ($request->max_range >= $range->min_range && $request->max_range <= $range->max_range)){

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'El rango ingresado se superpone con otro rango existente'
                    ]
                ]);
            }
        };

        VendorRange::create([
            "vendor_id" => $request->client_id,
            "min_range" => $request->min_range,
            "max_range" => $request->max_range,
            "active" => true,
            "updated_by" => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Rango creado exitosamente'
            ]
        ]);
    }
}
