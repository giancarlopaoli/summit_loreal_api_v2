<?php

namespace App\Http\Controllers\Admin\Executives;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientStatus;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Clients\InmediateOperationController;

class ClientsController extends Controller
{
    //
    public function list(Request $request) {
/*        $val = Validator::make($request->all(), [
            'contact_type' => 'nullable|in:Natural,Juridica',
            'lead_contact_type_id' => 'nullable|exists:lead_contact_types,id',
            'document_type_id' => 'nullable|exists:document_types,id',
            'region_id' => 'nullable|exists:regions,id',
            "sector_id" => 'nullable|exists:sectors,id',
            "lead_status_id" => 'nullable|exists:lead_statuses,id',
            "company_name" => 'nullable|string'
        ]);
        if($val->fails()) return response()->json($val->messages());
*/
        $clients = Client::select('id','customer_type','document_number','client_status_id','registered_at','executive_id')
            ->selectRaw("if(customer_type ='PN',CONCAT(name,' ',last_name, ' ',mothers_name),name) as client_name")
            ->with('status:id,name')
            ->with('executive:id,type')
            ->with('executive.user:id,name,last_name,email,phone')
            ->where('executive_id', auth()->id());

        if(isset($request->customer_type)) $clients = $clients->where('customer_type', $request->customer_type);

        if($request->company_name != "") $clients = $clients->whereRaw("CONCAT(name,' ',last_name,' ',mothers_name) like "."'%"."$request->company_name"."%'");

        if($request->document_number != "")  $clients = $clients->where('document_number', 'like', "%".$request->document_number."%");

        $clients = $clients->get();

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $clients
            ]
        ]);
    }

    public function client_detail(Request $request, Client $client) {

        $client->load('operations.bank_accounts','operations.escrow_accounts')
            ->only(['id','name','last_name','mothers_name','document_type_id','document_number','phone','email','address','birthdate','district_id','economic_activity_id','client_status_id','accountable_email','comments','association_id','registered_at','executive_id','tracking_phase_id','tracking_date','comission_start_date','comission','accepts_publicity','users']);


        return response()->json([
            'success' => true,
            'data' => [
                "client" => $client
            ]
        ]);
    }

    public function client_follows(Request $request, Client $client) {
        $tracking = $client->trackings;
        

        return response()->json([
            'success' => true,
            'data' => [
                "tracking" => $tracking
            ]
        ]);
    }

    public function register_follow(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'tracking_status_id' => 'required',
            'tracking_form_id' => 'nullable|numeric',
            'comments' => 'nullable|string'
        ]);

        if($val->fails()) return response()->json($val->messages());

        $tracking = $client->trackings()->create([
            'tracking_status_id' => $request->tracking_status_id,
            'tracking_form_id' => $request->tracking_form_id,
            'comments' => $request->comments,
            'created_by' => auth()->id()
        ]);


        return response()->json([
            'success' => true,
            'data' => [
                "tracking" => $tracking
            ]
        ]);
    }

    //Edit user
    public function update_user(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'phone' => 'required|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        /*if(is_null($client->users()->find($request->user_id))){
            return response()->json([
            'success' => false,
                'errors' => [
                    'No tiene permisos para modificar datos de este usuario'
                ]
            ]);
        }*/

        $client->users()->find($request->user_id)->update($request->only(["phone"]));

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $client->users()->find($request->user_id)->only(['id','name','last_name','email','document_type_id','document_number','phone','tries','last_active','status','created_at','role_id'])
            ]
        ]);
    }

    public function vendors(Request $request) {
        
        $vendors = Client::select('id','name','last_name')
            ->where('type', 'PL')
            ->where('client_status_id', ClientStatus::where('name','Activo')->first()->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                "vendors" => $vendors
            ]
        ]);
    }

    public function escrow_accounts(Request $request) {
        
        $vendors = Client::select('id','name','last_name')
            ->where('type', 'PL')
            ->where('client_status_id', ClientStatus::where('name','Activo')->first()->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                "vendors" => $vendors
            ]
        ]);
    }

    public function quote_inmediate_operation(Request $request) {
        
        $consult = new InmediateOperationController();
        $result = $consult->quote_operation($request)->getData();

        return response()->json($result);
    }

    public function create_inmediate_operation(Request $request) {
        
        $consult = new InmediateOperationController();
        $result = $consult->create_operation($request)->getData();

        if(!$result->success){
            return response()->json(
                $result
            );
        }
        else{
            $consult2 = new InmediateOperationController();
            $result2 = $consult->match_operation_vendor($result->data->id, $request->vendor_id, $request->bank_accounts)->getData();

            return response()->json([
                'success' => true,
                'data' => [
                    "operation" => $result->data
                ]
            ]);
        }
    }

    // Guardando configuración especial de cliente
    public function interbank_parameters(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|numeric',
            'comission' => 'nullable|numeric',
            'spread' => 'nullable|numeric',
            'exchange_rate' => 'nullable|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $client = Client::find($request->client_id);

        $client->ibops_client_comissions()->where('active', true)->update([
            'active' => false
        ]);


        $client->ibops_client_comissions()->create([
            'client_id' => $request->client_id,
            'comission_spread' => $request->comission_spread,
            'spread' => $request->spread,
            'exchange_rate' => $request->exchange_rate,
            'active' => true,
            //'created_at' => Carbon::now(),
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'data' => $client->ibops_client_comissions->where('active', true)->first()
        ]);
    }
}
