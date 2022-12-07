<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configuration;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ConfigurationsController extends Controller
{
    //Ranges list
    public function list(Request $request) {

        $configurations = Configuration::select('id','shortname','description','value','updated_by')->with('updater:id,name,last_name')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'configurations' => $configurations
            ]
        ]);
    }

    //Edit Range
    public function edit(Request $request, Configuration $configuration) {
        $val = Validator::make($request->all(), [
            'value' => 'required|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $configuration->update(array_merge($request->only(['value']), ["updated_by" => auth()->id()]));

        return response()->json([
            'success' => true,
            'data' => [
                'Configuración editada exitosamente'
            ]
        ]);
    }

}
