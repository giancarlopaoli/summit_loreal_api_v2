<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function get_indicators(Request $request) {
        $client = Client::find($request->client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'El id de client enviado no existe'
                ]
            ]);
        }

        $latest_operations = $client->operations()->latest()->take(5)->get();

        $total_amount = $client->operations()->whereIn("operation_status_id", [4, 5])->selectRaw('SUM(amount) as total')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'operations' => $latest_operations,
                'total_operated_amount' => (float) $total_amount[0]->total
            ]
        ]);
    }
}
