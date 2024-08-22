<?php

namespace App\Http\Controllers\Admin\Executives;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientStatus;
use App\Models\ClientTracking;
use App\Models\Executive;
use App\Models\IbopsClientComission;
use App\Models\User;
use App\Models\SpecialExchangeRate;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Clients\InmediateOperationController;
use App\Http\Controllers\Admin\Operations\ExecutivesController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        if(is_null($request->company_name) && is_null($request->document_number)){
            return response()->json([
                'success' => false,
                'data' => [
                    'Debe ingresar por lo menos un parámetro de búsqueda'
                ]
            ]);
        }

        if(!is_null($request->document_number)){

            if($request->customer_type == 'PJ' && strlen($request->document_number) < 11){
                return response()->json([
                    'success' => false,
                    'data' => [
                        'Nro documento debe tener 11 números'
                    ]
                ]);
            }

            if($request->customer_type == 'PN' && strlen($request->document_number) < 8){
                return response()->json([
                    'success' => false,
                    'data' => [
                        'Nro documento debe tener mínimo 8 números'
                    ]
                ]);
            }
        }

        if(!is_null($request->company_name) && strlen($request->company_name) < 5){
            return response()->json([
                'success' => false,
                'data' => [
                    'Debe ingresar mínimo 5 caracteres en búsqueda por Noombre/Razón Social'
                ]
            ]);
        }


        $clients = Client::select('id','customer_type','document_number','client_status_id','registered_at','executive_id')
            ->selectRaw("if(customer_type ='PN',CONCAT(name,' ',last_name, ' ',mothers_name),name) as client_name")
            ->selectRaw(" (select operation_date from operations op where op.client_id = clients.id and op.operation_status_id in (6,7,8) order by operation_date desc limit 1) as last_operation") 
            ->with('status:id,name')
            ->with('executive:id,type','executive.user:id,name,last_name,email,phone')
            ->whereIn('client_status_id', [2,3]);

        if(isset($request->customer_type)) $clients = $clients->where('customer_type', $request->customer_type);

        if($request->company_name != "") $clients = $clients->whereRaw("(CONCAT(name,' ',last_name,' ',mothers_name) like "."'%"."$request->company_name"."%'" . "or name like "."'%"."$request->company_name"."%')");

        if($request->document_number != "")  $clients = $clients->where('document_number', 'like', "%".$request->document_number."%");

        $clients = $clients->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $clients
            ]
        ]);
    }

    public function client_detail(Request $request, Client $client) {

        $client->load('operations:id,code,class,type,client_id,user_id,use_escrow_account,operation_date,amount,currency_id,exchange_rate,comission_spread,comission_amount,igv,spread,operation_status_id,invoice_url,unaffected_invoice_url','operations.status:id,name','operations.bank_accounts','operations.escrow_accounts','operations.escrow_accounts.bank','tracking_phase:id,name','status:id,name','document_type:id,name','operations.bank_accounts.bank','operations.vendor_bank_accounts','operations.vendor_bank_accounts.bank','operations.documents','sector:id,name','executive:id,type','executive.user:id,name,last_name')
            ->load(['users' => function ($query) {
                        $query->wherePivot('status', '!=', 'Inactivo');
                    },'users:id,name,last_name,email,document_type_id,document_number,phone,last_active'])
            ->only(['id','name','last_name','mothers_name','document_type_id','document_number','phone','email','address','birthdate','district_id','economic_activity_id','client_status_id','accountable_email','comments','association_id','registered_at','executive_id','tracking_phase_id','tracking_date','comission_start_date','comission','accepts_publicity','users','sector']);

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

    public function edit_client(Request $request, Client $client) {
        
        $client->update($request->only(["sector_id","comments2"]));

        return response()->json([
            'success' => true,
            'data' => [
                "client" => $client
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
        
        $request->executive_request = true;

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

    public function comissions(Request $request) {

        $executive = Executive::find(auth()->id());

        $consult = new ExecutivesController();
        $result = $consult->comission_detail($request, $executive)->getData();

        return response()->json($result);
    }
    
    // Eliminar Comision cliente
    public function comissions_indicators(Request $request) {
        $executive_id = (isset($request->executive_id)) ? $request->executive_id : auth()->id();

        $indicators = DB::table('operations_view')
            ->selectRaw("sum(if(type = 'Interbancaria', if(currency_id = 1, round(amount/exchange_rate,2) ,amount), amount)) as total_amount, count(amount) as num_operations")
            ->selectRaw("sum(round(if(type = 'Interbancaria', if(currency_id = 2, round(comission_amount * exchange_rate,2) ,comission_amount), comission_amount) * (if(executive_id = $executive_id,executive_comission,0) + if(executive2_id = $executive_id,executive2_comission,0)),2)) as total_comissions")
            ->selectRaw("count(distinct client_id) as unique_clients")
            ->whereRaw(" (executive_id = $executive_id or executive2_id = $executive_id)")

            ->first();

        $indicators_graphs = DB::table('operations_view')
            ->selectRaw("year(operations_view.operation_date) as year, count(amount) as num_operations, sum(if(type = 'Interbancaria', if(currency_id = 1, round(amount/exchange_rate,2) ,amount), amount)) as total_amount")
            ->selectRaw("sum(round(if(type = 'Interbancaria', if(currency_id = 2, round(comission_amount * exchange_rate,2) ,comission_amount), comission_amount) * (if(executive_id = $executive_id,executive_comission,0) + if(executive2_id = $executive_id,executive2_comission,0)),2)) as total_comissions")
            ->selectRaw("count(distinct client_id) as unique_clients")
            ->whereRaw(" (executive_id = $executive_id or executive2_id = $executive_id)")
            ->groupByRaw("year(operations_view.operation_date)")
            ->orderByRaw('year(operation_date) asc')
            ->get();

        $monthly_indicators = DB::table('operations_view')
            ->selectRaw("year(operation_date) as year,month(operation_date) as month, sum(amount) as volume, count(amount) as num_operations, round(sum(comission_amount),2) as billex_comissions, round(sum(if(type='Compra',amount,0)),2) as volume_buying, round(sum(if(type='Venta',amount,0)),2) as volume_selling")
            ->selectRaw("coalesce((select sum(ov.amount) from operations ov where ov.operation_status_id in (1,2,3,4,5) and year(ov.operation_date) = year(operations_view.operation_date) and month(ov.operation_date) = month ),0) as volume_in_progress")
            ->selectRaw("sum(round(if(type = 'Interbancaria', if(currency_id = 2, round(comission_amount * exchange_rate,2) ,comission_amount), comission_amount) * (if(executive_id = $executive_id,executive_comission,0) + if(executive2_id = $executive_id,executive2_comission,0)),2)) as executive_comissions")
            ->selectRaw("coalesce((select sum(ov.amount) from operations ov where ov.operation_status_id in (1,2,3,4,5) and year(ov.operation_date) = year(operations_view.operation_date) and month(ov.operation_date) = month ),0) as volume_in_progress")

            ->selectRaw("(select sum(ov.amount) from operations_view ov where ov.customer_type = 'PJ' and year(ov.operation_date) = year(operations_view.operation_date) and month(ov.operation_date) = month and (ov.executive_id = $executive_id or ov.executive2_id = $executive_id)) as volume_pj")
            ->selectRaw("(select sum(ov.amount) from operations_view ov where ov.customer_type = 'PN' and year(ov.operation_date) = year(operations_view.operation_date) and month(ov.operation_date) = month and (ov.executive_id = $executive_id or ov.executive2_id = $executive_id)) as volume_pn")
            ->selectRaw("coalesce((select sg.goal from executive_goals sg where sg.year = year(operations_view.operation_date) and sg.month = month(operations_view.operation_date) and sg.executive_id = operations_view.executive_id),0) as sales_goal")
            
            ->whereIn("type", ['Compra','Venta'])
            ->whereRaw("((year(operations_view.operation_date)-2000)*12 + month(operations_view.operation_date)) >= ((year(now()) - 2000 )*12 + month(now()) -6)")
            ->whereRaw(" (executive_id = $executive_id or executive2_id = $executive_id)")
            ->groupByRaw("year(operations_view.operation_date), month(operations_view.operation_date)")
            ->orderByRaw('year(operations_view.operation_date) asc, month(operations_view.operation_date)')
            ->limit(7)
            ->get();

        $cumplimiento_meta = DB::table('goals_achievement')
            ->select('operation_executive_id as executive_id','operation_month as month', 'operation_year as year','progress as operations_amount','goal')
            ->selectRaw(" round(achievement,4) as goal_achieved, if( ((operation_executive_id = 2801 or operation_executive_id = 2811) and year(CURRENT_TIMESTAMP) = 2023),0.05, comission_achieved ) as comission_achieved")
            ->selectRaw("(select sum(round( ov.comission_amount*ov.executive_comission ,2)) from operations_view ov where ov.executive_id = goals_achievement.operation_executive_id and month(ov.operation_date) = goals_achievement.operation_month and year(ov.operation_date) = goals_achievement.operation_year) as comission_earned")
            ->where('operation_executive_id', $executive_id)
            ->whereRaw(" operation_month = month(CURRENT_TIMESTAMP) and operation_year = year(CURRENT_TIMESTAMP)")
            ->first();

        $cumplimiento_meta_mensual = DB::table('goals_achievement')
            ->select('operation_executive_id as executive_id','operation_month as month', 'operation_year as year','progress as operations_amount','goal')
            ->selectRaw(" round(achievement,4) as goal_achieved, if( ((operation_executive_id = 2801 or operation_executive_id = 2811) and year(CURRENT_TIMESTAMP) = 2023),0.05, comission_achieved ) as comission_achieved")
            ->selectRaw("(select sum(round( ov.comission_amount*ov.executive_comission ,2)) from operations_view ov where ov.executive_id = goals_achievement.operation_executive_id and month(ov.operation_date) = goals_achievement.operation_month and year(ov.operation_date) = goals_achievement.operation_year) as comission_earned")
            ->where('operation_executive_id', $executive_id)
            ->whereRaw(" ((operation_year-2000)*12 + operation_month) >= ((year(CURRENT_TIMESTAMP)-2000)*12 + MONTH(CURRENT_TIMESTAMP)-6)")
            ->orderByRaw('operation_year asc, operation_month')
            
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'indicators' => $indicators,
                'indicators_graphs' => [
                    'year' => $indicators_graphs->pluck('year'),
                    'total_amount' => $indicators_graphs->pluck('total_amount'),
                    'num_operations' => $indicators_graphs->pluck('num_operations'),
                    'total_comissions' => $indicators_graphs->pluck('total_comissions'),
                    'unique_clients' => $indicators_graphs->pluck('unique_clients')
                ],
                'monthly_indicators' => [
                    'month' => $monthly_indicators->pluck('month'),
                    'year' => $monthly_indicators->pluck('year'),
                    'volume' => $monthly_indicators->pluck('volume'),
                    'volume_in_progress' => $monthly_indicators->pluck('volume_in_progress'),
                    'num_operations' => $monthly_indicators->pluck('num_operations'),
                    'billex_comissions' => $monthly_indicators->pluck('billex_comissions'),
                    'executive_comissions' => $monthly_indicators->pluck('executive_comissions'),

                    'volume_buying' => $monthly_indicators->pluck('volume_buying'),
                    'volume_selling' => $monthly_indicators->pluck('volume_selling'),
                    'sales_goal' => $monthly_indicators->pluck('sales_goal'),
                    'volume_pj' => $monthly_indicators->pluck('volume_pj'),
                    'volume_pn' => $monthly_indicators->pluck('volume_pn')
                ],
                'cumplimiento_meta' => $cumplimiento_meta,
                'cumplimiento_meta_mensual' => $cumplimiento_meta_mensual,
            ]
        ]);
    }

    public function get_special_exchange_rate(Request $request, Client $client) {

        $special_exchange_rate = SpecialExchangeRate::select('id','vendor_id','buying','selling','finished_at')
            ->where('client_id', $client->id)
            ->where('active', true)
            ->with('vendor:id,name,last_name')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'special_exchange_rate' => $special_exchange_rate
            ]
        ]);
    }

    public function create_special_exchange_rate(Request $request, Client $client) {
        $val = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:clients,id',
            "duration_value" => 'required|numeric',
            "duration_period" => 'required|in:minutes,hours'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $client->client_special_exchange_rates()->where('active', true)->update([
            'active' => false
        ]);

        $duration_time = ($request->duration_period == 'minutes') ? $request->duration_value : $request->duration_value * 60;
        $finished_at = Carbon::now()->addMinutes($duration_time);


        $client->client_special_exchange_rates()->create([
            'vendor_id' => $request->vendor_id,
            'buying' => (!is_null($request->buying) && $request->buying != 'null') ? $request->buying : null,
            'selling' => (!is_null($request->selling) && $request->selling != 'null') ? $request->selling : null,
            'duration_time' => $duration_time,
            'active' => true,
            'finished_at' => $finished_at,
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Tipo de cambio especial configurado exitosamente'
            ]
        ]);
    }

    public function delete_special_exchange_rate(Request $request, Client $client) {

        $client->client_special_exchange_rates()->where('active', true)->update([
            'active' => false
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Tipo de cambio especial eliminado exitosamente'
            ]
        ]);
    }
}
