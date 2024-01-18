<?php

namespace App\Http\Controllers\Admin\Executives;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientStatus;
use App\Models\ClientTracking;
use App\Models\IbopsClientComission;
use App\Models\User;
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
            ->with('executive:id,type','executive.user:id,name,last_name,email,phone')
            ->whereIn('client_status_id', [2,3])
            ->where('executive_id', auth()->id());

        if(isset($request->customer_type)) $clients = $clients->where('customer_type', $request->customer_type);

        if($request->company_name != "") $clients = $clients->whereRaw("(CONCAT(name,' ',last_name,' ',mothers_name) like "."'%"."$request->company_name"."%'" . "or name like "."'%"."$request->company_name"."%')");

        if($request->document_number != "")  $clients = $clients->where('document_number', 'like', "%".$request->document_number."%");

        $clients = $clients->get();

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $clients
            ]
        ]);
    }

    public function clients_base(Request $request) {
        $val = Validator::make($request->all(), [
            'customer_type' => 'required|in:PN,PJ'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $clients = Client::select('id','customer_type','document_number','client_status_id','registered_at','executive_id')
            ->selectRaw("if(customer_type ='PN',CONCAT(name,' ',last_name, ' ',mothers_name),name) as client_name")
            ->selectRaw(" (select operation_date from operations op where op.client_id = clients.id and op.operation_status_id in (6,7,8) order by operation_date desc limit 1) as last_operation") 
            ->with('status:id,name')
            ->with('executive:id,type','executive.user:id,name,last_name,email,phone')
            ->whereIn('client_status_id', [2,3]);

        if(isset($request->customer_type)) $clients = $clients->where('customer_type', $request->customer_type);

        if($request->company_name != "") $clients = $clients->whereRaw("(CONCAT(name,' ',last_name,' ',mothers_name) like "."'%"."$request->company_name"."%'" . "or name like "."'%"."$request->company_name"."%')");

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

        $client->load('operations:id,code,class,type,client_id,user_id,use_escrow_account,operation_date,amount,currency_id,exchange_rate,comission_spread,comission_amount,igv,spread,operation_status_id,invoice_url','operations.status:id,name','operations.bank_accounts','operations.escrow_accounts','operations.escrow_accounts.bank','tracking_phase:id,name','status:id,name','document_type:id,name','operations.bank_accounts.bank','operations.vendor_bank_accounts','operations.vendor_bank_accounts.bank')
            ->only(['id','name','last_name','mothers_name','document_type_id','document_number','phone','email','address','birthdate','district_id','economic_activity_id','client_status_id','accountable_email','comments','association_id','registered_at','executive_id','tracking_phase_id','tracking_date','comission_start_date','comission','accepts_publicity','users']);

        $client->tracking_status = !is_null(ClientTracking::where('client_id', $client->id)->orderByDesc('id')->with('status:id,name')->first()) ? ClientTracking::where('client_id', $client->id)->orderByDesc('id')->with('status:id,name')->first()->status : null;

        return response()->json([
            'success' => true,
            'data' => [
                "client" => $client
            ]
        ]);
    }

    public function client_follows(Request $request, Client $client) {
        $tracking = $client->trackings->load('status:id,name','form:id,name','creator:id,name,last_name');
        
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

        $user = User::find($request->user_id);
        $user->update($request->only(["phone"]));

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $user->only(['id','name','last_name','email','document_type_id','document_number','phone','tries','last_active','status','created_at','role_id'])
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

    public function no_escrow_vendors(Request $request) {
        
        $vendors = Client::select('id','name','last_name')
            ->where('type', 'PL')
            ->where('use_bank_accounts', true)
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

    // List of Interbank Operations parameters
    public function get_interbank_parameters(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

       $parameters = IbopsClientComission::select('id','client_id','comission_spread','spread','exchange_rate')
            ->where('client_id', $request->client_id)
            ->where('active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ibops_client_comissions' => $parameters
            ]
        ]);
    }

    // Saving special configuration
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
            'data' => [
                'ibops_client_comissions' => $client->ibops_client_comissions->where('active', true)->first()
            ]
        ]);
    }

    // Eliminar Comision cliente
    public function delete_interbank_parameter(Request $request, IbopsClientComission $ibops_client_comissions) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $ibops_client_comissions->active = false;
        $ibops_client_comissions->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Parametros eliminados exitosamente'
            ]
        ]);
    }
}
