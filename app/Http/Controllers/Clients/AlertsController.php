<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\ExchangeRateAlert;
use App\Enums;

class AlertsController extends Controller
{
    //
    public function alerts_list(Request $request)
    {

        $alerts = ExchangeRateAlert::where('user_id', auth()->id())
            ->where('status', Enums\AlertStatus::Activo)
            ->get();


        return response()->json([
            'success' => true,
            'data' => [
                'alerts' => $alerts
            ]
        ]);
    }

    public function delete_alert(Request $request, ExchangeRateAlert $exchange_rate_alert)
    {  
        
        if($exchange_rate_alert->status != Enums\AlertStatus::Activo){
             return response()->json([
                'success' => false,
                'errors' => 'La alerta debe estar activa para poder eliminarla'
            ]);
        }

        if($exchange_rate_alert->user_id != auth()->id()){
             return response()->json([
                'success' => false,
                'errors' => 'La alerta no pertenece al usuario'
            ]);
        }

        $exchange_rate_alert->status = Enums\AlertStatus::Eliminado;
        $exchange_rate_alert->save();

        $alerts = ExchangeRateAlert::where('user_id', auth()->id())
            ->where('status', Enums\AlertStatus::Activo)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'alerts' => $alerts,
            ]
        ]);
    }

    public function new_alert(Request $request)
    {  
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:Compra,Venta',
            'exchange_rate' => 'required|numeric',
            'email' => 'nullable|email',
            'phone' => 'nullable|string'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        if(is_null($request->email) && is_null($request->phone)){
             return response()->json([
                'success' => false,
                'errors' => 'Debe ingresar por lo menos un email o teléfono'
            ]);
        }

        $exchange_rate_alert = auth()->user()->alerts()->create([
            'type' => $request->type,
            'user_id' => auth()->id(),
            'exchange_rate' => $request->exchange_rate,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => Enums\AlertStatus::Activo
        ]);

        $alerts = ExchangeRateAlert::where('user_id', auth()->id())
            ->where('status', Enums\AlertStatus::Activo)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'alerts' => $alerts,
            ]
        ]);
    }
}
