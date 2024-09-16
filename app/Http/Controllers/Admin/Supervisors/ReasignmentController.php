<?php

namespace App\Http\Controllers\Admin\Supervisors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Client;

class ReasignmentController extends Controller
{
    // Clients list
    public function clients(Request $request) {

        $clients = Client::select('id','document_type_id','document_number','executive_id','registered_at')
            ->selectRaw("if(customer_type = 'PN',concat(name,' ',last_name,' ',mothers_name),name) as client_name")
            ->selectRaw("(select concat(name,' ',last_name) from executives_comissions ec inner join users us on ec.executive_id = us.id where ec.client_id = clients.id and ec.start_date<= now() and ec.end_date >= now() limit 1) as executive_free_name")
            ->where('client_status_id',3)
            ->with('document_type:id,name')
            ->with('executive:id,type','executive.user:id,name,last_name')
            ->limit(20);

        if(isset($request->customer_type)) $clients = $clients->where('customer_type', $request->customer_type);
        
        if(isset($request->document_type_id)) $clients = $clients->where('document_type_id', $request->document_type_id);

        if($request->client_name != "") $clients = $clients->whereRaw("(CONCAT(name,' ',last_name,' ',mothers_name) like "."'%"."$request->client_name"."%'" . "or name like "."'%"."$request->client_name"."%')");

        if($request->document_number != "")  $clients = $clients->where('document_number', 'like', "%".$request->document_number."%");

        $clients = $clients->get();

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $clients
            ]
        ]);
    }

    // Client detail
    public function client_detail(Request $request, Client $client) {

        $client2 = Client::where('id', $client->id)
            ->select('id','document_type_id','document_number','executive_id','registered_at','comission_start_date','fix_comission')
            ->selectRaw("if(customer_type = 'PN',concat(name,' ',last_name,' ',mothers_name),name) as client_name")
            ->selectRaw("(select concat(name,' ',last_name,' - ',round(ec.comission*100,0),'%') from executives_comissions ec inner join users us on ec.executive_id = us.id where ec.client_id = clients.id and ec.start_date<= now() and ec.end_date >= now() limit 1) as executive_free_name")

            ->selectRaw("(select concat(start_date,' - ',end_date) from executives_comissions ec where ec.client_id = clients.id and ec.start_date<= now() and ec.end_date >= now() limit 1) as executive_free_dates")

            ->with('executive:id,type','executive.user:id,name,last_name')
            ->with('document_type:id,name')
            ->with(['operations' => function ($query) use ($client) {
                        $query->whereIn('operation_status_id', [6,7])->where('operation_date',">=", $client->comission_start_date);
                    }, 'operations:id,code,type,client_id,user_id,amount,exchange_rate,comission_spread,comission_amount,igv,operation_date']) 
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client2
            ]
        ]);
    }
}
