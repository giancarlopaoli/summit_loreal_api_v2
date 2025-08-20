<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\BankAccount;
use App\Models\EscrowAccount;
use App\Models\Executive;
use App\Models\Operation;
use App\Models\OperationOnline;
use App\Models\OperationsAnalyst;
use App\Models\OperationsAnalystLog;
use App\Models\OperationStatus;
use App\Models\OperationDocument;
use App\Models\OperationHistory;
use App\Models\Configuration;
use App\Models\Currency;
use App\Enums\BankAccountStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Enums;
use Illuminate\Support\Facades\Storage;
use App\Events\AvailableOperations;
use Illuminate\Support\Facades\Mail;
use App\Mail\OperationInstructions;
use App\Mail\OperationSign;
use App\Mail\VendorInstructions;
use App\Mail\Invoice;
use App\Http\Controllers\Admin\Operations\DailyOperationsController;
use App\Http\Controllers\Admin\Operations\WsCorfidController;
use App\Http\Controllers\Admin\Operations\TelegramNotificationsControllers;

class DailyOperationsController extends Controller
{
    public function daily_operations(Request $request) {
        $val = Validator::make($request->all(), [
            'date' => 'date',
            'status' => 'required|in:Todas,Pendientes,Finalizadas',
            'operations_analyst_id' => 'nullable|exists:operations_analysts,id',
        ]);
        if($val->fails()) return response()->json($val->messages());

        ########### Configuration ##################

        $date = isset($request->date) ? $request->date : Carbon::now()->format('Y-m-d');
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $now = Carbon::now();
        $daydiff = $now->diff($date)->days;


        $pendientes = OperationStatus::wherein('name', ['Disponible','Pendiente envio fondos','Pendiente fondos contraparte','Contravalor recaudado','Fondos enviados','Pendiente facturar'])->get()->pluck('id');
        $finalizadas = OperationStatus::wherein('name', ['Facturado','Finalizado sin factura'])->get()->pluck('id');
        $todas = OperationStatus::get()->pluck('id');

        ############### Status Filter #############

        if($request->status == 'Pendientes'){
            $status = $pendientes;
            $status_str = "(op1.operation_status_id in (" . substr($pendientes, 1, Str::length($pendientes)-2) . ") or op2.operation_status_id in (" . substr($pendientes, 1, Str::length($pendientes)-2) . ") )";
            $where_str = "(1)";
        }
        elseif($request->status == 'Finalizadas'){
            $status = $finalizadas;
            $status_str = "(op1.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and op2.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") )";
            $where_str = "(date(operation_matches.created_at) = '$date')";
        }
        else{
            $status = $todas;
            $status_str = "(1)";
            $where_str = "(date(operation_matches.created_at) = '$date')";
        }

        ############### Operations Analyst Filter #############

        if(is_null($request->operations_analyst_id) || $request->operations_analyst_id==''){
            $operations_analyst = "(1)";
        }

        else{
            $operations_analyst = "op1.operations_analyst_id = " . $request->operations_analyst_id;
        }

        #############################################


        $graphs = OperationOnline::
            selectRaw("day(operation_date) as dia, sum(amount) as amount, count(amount) as num_operations")
            ->selectRaw("(select sum(amount) from view_operations_online as op2 where month(op2.operation_date) = month(CURRENT_TIMESTAMP) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= day(view_operations_online.operation_date) and operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and type in ('Compra','Venta') and op2.client_id not in (select id from clients where type = 'PL')) as accumulated_amount")

            ->selectRaw("(select count(amount) from view_operations_online as op2 where month(op2.operation_date) = month(CURRENT_TIMESTAMP) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= day(view_operations_online.operation_date) and operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and type in ('Compra','Venta') and op2.client_id not in (select id from clients where type = 'PL')) as accumulated_num_operations")

            ->whereIn('operation_status_id', $finalizadas)
            ->whereIn('type', ['Compra', 'Venta'])
            ->whereNotIn('client_id', Client::where('type','PL')->get()->pluck('id'))
            ->whereRaw('month(operation_date) = month(CURRENT_TIMESTAMP) and year(operation_date) = year(CURRENT_TIMESTAMP) ')
            ->groupByRaw("day(operation_date)")
            ->orderByRaw('day(operation_date)')
            ->get();

        if($daydiff > 33){
            $indicators = Operation::selectRaw("coalesce(sum(amount),0) as total_amount, count(id) as num_operations")
                ->selectRaw("(select sum(op1.amount) from operations op1 inner join clients cl on op1.client_id = cl.id where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id not in (9,10) and cl.type != 'PL') as monthly_amount")
                ->selectRaw("(select count(op1.amount) from operations op1 inner join clients cl on op1.client_id = cl.id where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id not in (9,10) and cl.type != 'PL') as monthly_operations")
                ->whereRaw("date(operation_date) = '$date'")
                ->whereNotIn('client_id', Client::where('type','PL')->get()->pluck('id'))
                ->whereNotIn('operation_status_id', [9,10])
                ->get();

            $pending_operations = Operation::select('id','code','class','type','client_id','user_id','use_escrow_account','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date')
                ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                    ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate + (spread/10000),4))) as final_exchange_rate")
                    ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(round(amount/exchange_rate*(exchange_rate+spread/10000), 2) + comission_amount + igv, 2)) ) as counter_value")
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2) - amount , null ) as financial_expenses")
                ->whereIn('operation_status_id', OperationStatus::wherein('name', ['Disponible'])->get()->pluck('id'))
                ->whereRaw("date(operation_date) = '$date'")
                ->with('client:id,name,last_name,mothers_name,customer_type,type,document_type_id,document_number,executive_id','client.document_type:id,name','client.executive.user:id,name,last_name')
                ->with('currency:id,name:sign')
                ->with('status:id,name')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number,account_type_id','bank_accounts.currency:id,name,sign','bank_accounts.bank:id,name,shortname,image')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number','escrow_accounts.currency:id,name,sign','escrow_accounts.bank:id,name,shortname,image')
                ->with('vendor_bank_accounts:id,bank_id,currency_id,client_id,account_number,cci_number,account_type_id','vendor_bank_accounts.bank:id,name,shortname,image')
                ->with('documents:id,operation_id,type')
                ->get();

            $matched_operations = DB::table('operation_matches')
                ->select('operation_id', 'matched_id')
                ->join('operations as op1', 'op1.id', "=", "operation_matches.operation_id")
                ->join('operations as op2', 'op2.id', "=", "operation_matches.matched_id")
                ->whereRaw($where_str)
                ->whereRaw("$status_str")
                ->whereRaw($operations_analyst)
                ->get();


            $matched_operations->each(function ($item, $key) {

                $item->created_operation = Operation::where('id',$item->operation_id)
                    ->select('operations.id','code','class','type','client_id','user_id','use_escrow_account','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url', 'unaffected_invoice_url','operations_analyst_id','corfid_id','corfid_message')
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                    ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate + (spread/10000),4))) as final_exchange_rate")
                    ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(round(amount/exchange_rate*(exchange_rate+spread/10000), 2) + comission_amount + igv, 2)) ) as counter_value")
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2) - amount , null ) as financial_expenses")
                    ->with('operations_analyst.user:id,name,last_name')
                    ->with('status:id,name')
                    ->with('client:id,name,last_name,mothers_name,customer_type,type,document_type_id,document_number,executive_id','client.document_type:id,name','client.executive.user:id,name,last_name')
                    ->with('currency:id,name:sign')
                    ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number,account_type_id','bank_accounts.bank:id,name,shortname,image','bank_accounts.currency:id,name,sign')
                    ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number','escrow_accounts.currency:id,name,sign','escrow_accounts.bank:id,name,shortname,image')
                    ->with('vendor_bank_accounts:id,bank_id,currency_id,client_id,account_number,cci_number,account_type_id','vendor_bank_accounts.currency:id,name,sign','vendor_bank_accounts.bank:id,name,shortname,image')
                    ->with('documents:id,operation_id,type')
                    ->first();

                $item->created_operation->escrow_account_operation = DB::table('escrow_account_operation')->where('operation_id', $item->matched_id)->get();

                $item->created_operation->time = Operation::select('operations.id','operations.code','operations.operation_date','operations.sign_date')
                ->where('operations.id', $item->operation_id)
                ->join('clients', 'clients.id', '=','operations.client_id')
                ->join('operation_matches', 'operation_matches.operation_id', '=', 'operations.id')
                ->join('operations as op2', 'op2.id', '=', 'operation_matches.matched_id')
                ->selectRaw("coalesce(TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1 ),operations.deposit_date),TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1 ),now())) as total_time")
                ->selectRaw("if(operations.operation_status_id = 2 && (select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1) is null,
                    TIMESTAMPDIFF(MINUTE,operations.operation_date,now()),
                    if(operations.operation_status_id = 2 && (select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1) is not null,
                    TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1 ),now()),
                    if(operations.operation_status_id=3 && (op2.sign_date is null),
                    TIMESTAMPDIFF(MINUTE,operations.funds_confirmation_date,now()),
                    if(operations.operation_status_id = 3 && (op2.sign_date is not null),
                    TIMESTAMPDIFF(MINUTE,op2.sign_date,now()),
                    if(operations.operation_status_id = 4 && op2.operation_status_id = 5,
                    TIMESTAMPDIFF(MINUTE,op2.deposit_date,now()),
                    if(operations.operation_status_id = 4 && op2.operation_status_id = 7 && (operations.sign_date is null),
                    TIMESTAMPDIFF(MINUTE,op2.funds_confirmation_date,now()),
                    if(operations.operation_status_id = 4 && op2.operation_status_id = 7 && (operations.sign_date is not null),
                    TIMESTAMPDIFF(MINUTE,operations.sign_date,now()),

                    if((operations.operation_status_id in (6,7,8)) && (op2.operation_status_id in (6,7,8)),
                    TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1 ),operations.deposit_date),


                    0)))))))) as currenttime")->first();

                $item->matched_operation = Operation::where('id',$item->matched_id)
                    ->select('operations.id','code','class','type','client_id','user_id','use_escrow_account','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url','unaffected_invoice_url','operations_analyst_id','corfid_id','corfid_message')
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                    ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate + (spread/10000),4))) as final_exchange_rate")
                    ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(round(amount/exchange_rate*(exchange_rate+spread/10000), 2) + comission_amount + igv, 2)) ) as counter_value")
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2) - amount , null ) as financial_expenses")
                    ->with('operations_analyst.user:id,name,last_name')
                    ->with('status:id,name')
                    ->with('client:id,name,last_name,mothers_name,customer_type,type,document_type_id,document_number,executive_id','client.document_type:id,name','client.executive.user:id,name,last_name')
                    ->with('currency:id,name:sign')
                    ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number,account_type_id','bank_accounts.currency:id,name,sign','bank_accounts.bank:id,name,shortname,image')
                    ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number','escrow_accounts.currency:id,name,sign','escrow_accounts.bank:id,name,shortname,image')
                    ->with('vendor_bank_accounts:id,bank_id,currency_id,client_id,account_number,cci_number,account_type_id','vendor_bank_accounts.bank:id,name,shortname,image')
                    ->with('documents:id,operation_id,type')
                    ->first();
            });
        
            $pending_deposits = DB::table('operation_matches')
                ->select('op1.id as operation_id', 'op2.id as matched_id','op1.code','op1.type','op1.amount','op2.sign_date','op2.deposit_date','eao.deposit_at','bao.amount as client_amount')
                ->selectRaw("if(cl1.customer_type = 'PJ',cl1.name,concat(cl1.name,' ',cl1.last_name,' ',cl1.mothers_name)) as client_name, cl2.last_name as pl_name")
                ->selectRaw("eao.amount as expected_amount, ea.account_number, ba.shortname,cu.sign as currency_sign, cu.name as currency_name, concat(us.name, ' ', us.last_name) as analyst")
                ->selectRaw("if(eao.deposit_at is null, 'Pendiente fondos', if(bao.signed_at is null, '2da firma pendiente', if(bao.signed_at is not null and bao.deposit_at is null,'Depósito en proceso',0))) as deposit_status")
                ->join('operations as op1', 'op1.id', "=", "operation_matches.operation_id")
                ->join('operations as op2', 'op2.id', "=", "operation_matches.matched_id")
                ->join('clients as cl1', 'cl1.id', "=", "op1.client_id")
                ->join('clients as cl2', 'cl2.id', "=", "op2.client_id")
                ->join('escrow_account_operation as eao', 'eao.operation_id', "=", "op2.id")
                ->join('escrow_accounts as ea', 'eao.escrow_account_id', "=", "ea.id")
                ->join('banks as ba', 'ba.id', "=", "ea.bank_id")
                ->join('currencies as cu', 'cu.id', "=", "ea.currency_id")
                ->join('users as us', 'us.id', "=", "op1.operations_analyst_id")
                ->join('bank_account_operation as bao', 'bao.escrow_account_operation_id', "=", "eao.id")
                ->whereRaw("op1.operation_status_id = 4 and bao.deposit_at is null")
                ->get();
        }
        else{
            $indicators = OperationOnline::selectRaw("coalesce(sum(amount),0) as total_amount, count(id) as num_operations")
                ->selectRaw("(select sum(op1.amount) from view_operations_online op1 inner join clients cl on op1.client_id = cl.id where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id not in (9,10) and cl.type != 'PL') as monthly_amount")
                ->selectRaw("(select count(op1.amount) from view_operations_online op1 inner join clients cl on op1.client_id = cl.id where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id not in (9,10) and cl.type != 'PL') as monthly_operations")
                ->whereRaw("date(operation_date) = '$date'")
                ->whereNotIn('client_id', Client::where('type','PL')->get()->pluck('id'))
                ->whereNotIn('operation_status_id', [9,10])
                ->get();

            $pending_operations = OperationOnline::select('id','code','class','type','client_id','user_id','use_escrow_account','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date')
                ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                    ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate + (spread/10000),4))) as final_exchange_rate")
                    ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(round(amount/exchange_rate*(exchange_rate+spread/10000), 2) + comission_amount + igv, 2)) ) as counter_value")
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2) - amount , null ) as financial_expenses")
                ->whereIn('operation_status_id', OperationStatus::wherein('name', ['Disponible'])->get()->pluck('id'))
                ->whereRaw("date(operation_date) = '$date'")
                ->with('client:id,name,last_name,mothers_name,customer_type,type,document_type_id,document_number,executive_id','client.document_type:id,name','client.executive.user:id,name,last_name')
                ->with('currency:id,name:sign')
                ->with('status:id,name')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number,account_type_id','bank_accounts.currency:id,name,sign','bank_accounts.bank:id,name,shortname,image')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number','escrow_accounts.currency:id,name,sign','escrow_accounts.bank:id,name,shortname,image')
                ->with('vendor_bank_accounts:id,bank_id,currency_id,client_id,account_number,cci_number,account_type_id','vendor_bank_accounts.bank:id,name,shortname,image')
                ->with('documents:id,operation_id,type')
                ->get();

            $matched_operations = DB::table('operation_matches')
                ->select('operation_id', 'matched_id')
                ->join('view_operations_online as op1', 'op1.id', "=", "operation_matches.operation_id")
                ->join('view_operations_online as op2', 'op2.id', "=", "operation_matches.matched_id")
                ->whereRaw($where_str)
                ->whereRaw("$status_str")
                ->whereRaw($operations_analyst)
                ->get();


            $matched_operations->each(function ($item, $key) {

                $item->created_operation = OperationOnline::where('id',$item->operation_id)
                    ->select('view_operations_online.id','code','class','type','client_id','user_id','use_escrow_account','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url', 'unaffected_invoice_url','operations_analyst_id','corfid_id','corfid_message')
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                    ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate + (spread/10000),4))) as final_exchange_rate")
                    ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(round(amount/exchange_rate*(exchange_rate+spread/10000), 2) + comission_amount + igv, 2)) ) as counter_value")
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2) - amount , null ) as financial_expenses")
                    ->with('operations_analyst.user:id,name,last_name')
                    ->with('status:id,name')
                    ->with('client:id,name,last_name,mothers_name,customer_type,type,document_type_id,document_number,executive_id','client.document_type:id,name','client.executive.user:id,name,last_name')
                    ->with('currency:id,name:sign')
                    ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number,account_type_id','bank_accounts.bank:id,name,shortname,image','bank_accounts.currency:id,name,sign')
                    ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number','escrow_accounts.currency:id,name,sign','escrow_accounts.bank:id,name,shortname,image')
                    ->with('vendor_bank_accounts:id,bank_id,currency_id,client_id,account_number,cci_number,account_type_id','vendor_bank_accounts.currency:id,name,sign','vendor_bank_accounts.bank:id,name,shortname,image')
                    ->with('documents:id,operation_id,type')
                    ->first();

                $item->created_operation->escrow_account_operation = DB::table('escrow_account_operation')->where('operation_id', $item->matched_id)->get();

                $item->created_operation->time = OperationOnline::select('view_operations_online.id','view_operations_online.code','view_operations_online.operation_date','view_operations_online.sign_date')
                ->where('view_operations_online.id', $item->operation_id)
                ->join('clients', 'clients.id', '=','view_operations_online.client_id')
                ->join('operation_matches', 'operation_matches.operation_id', '=', 'view_operations_online.id')
                ->join('view_operations_online as op2', 'op2.id', '=', 'operation_matches.matched_id')
                ->selectRaw("coalesce(TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = view_operations_online.id and od.type = 'Comprobante' order by id limit 1 ),view_operations_online.deposit_date),TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = view_operations_online.id and od.type = 'Comprobante' order by id limit 1 ),now())) as total_time")
                ->selectRaw("if(view_operations_online.operation_status_id = 2 && (select od.created_at from operation_documents od where od.operation_id = view_operations_online.id and od.type = 'Comprobante' order by id limit 1) is null,
                    TIMESTAMPDIFF(MINUTE,view_operations_online.operation_date,now()),
                    if(view_operations_online.operation_status_id = 2 && (select od.created_at from operation_documents od where od.operation_id = view_operations_online.id and od.type = 'Comprobante' order by id limit 1) is not null,
                    TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = view_operations_online.id and od.type = 'Comprobante' order by id limit 1 ),now()),
                    if(view_operations_online.operation_status_id=3 && (op2.sign_date is null),
                    TIMESTAMPDIFF(MINUTE,view_operations_online.funds_confirmation_date,now()),
                    if(view_operations_online.operation_status_id = 3 && (op2.sign_date is not null),
                    TIMESTAMPDIFF(MINUTE,op2.sign_date,now()),
                    if(view_operations_online.operation_status_id = 4 && op2.operation_status_id = 5,
                    TIMESTAMPDIFF(MINUTE,op2.deposit_date,now()),
                    if(view_operations_online.operation_status_id = 4 && op2.operation_status_id = 7 && (view_operations_online.sign_date is null),
                    TIMESTAMPDIFF(MINUTE,op2.funds_confirmation_date,now()),
                    if(view_operations_online.operation_status_id = 4 && op2.operation_status_id = 7 && (view_operations_online.sign_date is not null),
                    TIMESTAMPDIFF(MINUTE,view_operations_online.sign_date,now()),

                    if((view_operations_online.operation_status_id in (6,7,8)) && (op2.operation_status_id in (6,7,8)),
                    TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = view_operations_online.id and od.type = 'Comprobante' order by id limit 1 ),view_operations_online.deposit_date),


                    0)))))))) as currenttime")->first();

                $item->matched_operation = OperationOnline::where('id',$item->matched_id)
                    ->select('view_operations_online.id','code','class','type','client_id','user_id','use_escrow_account','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url','unaffected_invoice_url','operations_analyst_id','corfid_id','corfid_message')
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                    ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate + (spread/10000),4))) as final_exchange_rate")
                    ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(round(amount/exchange_rate*(exchange_rate+spread/10000), 2) + comission_amount + igv, 2)) ) as counter_value")
                    ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2) - amount , null ) as financial_expenses")
                    ->with('operations_analyst.user:id,name,last_name')
                    ->with('status:id,name')
                    ->with('client:id,name,last_name,mothers_name,customer_type,type,document_type_id,document_number,executive_id','client.document_type:id,name','client.executive.user:id,name,last_name')
                    ->with('currency:id,name:sign')
                    ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number,account_type_id','bank_accounts.currency:id,name,sign','bank_accounts.bank:id,name,shortname,image')
                    ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number','escrow_accounts.currency:id,name,sign','escrow_accounts.bank:id,name,shortname,image')
                    ->with('vendor_bank_accounts:id,bank_id,currency_id,client_id,account_number,cci_number,account_type_id','vendor_bank_accounts.bank:id,name,shortname,image')
                    ->with('documents:id,operation_id,type')
                    ->first();
            });
        
            $pending_deposits = DB::table('operation_matches')
                ->select('op1.id as operation_id', 'op2.id as matched_id','op1.code','op1.type','op1.amount','op2.sign_date','op2.deposit_date','eao.deposit_at','bao.amount as client_amount')
                ->selectRaw("if(cl1.customer_type = 'PJ',cl1.name,concat(cl1.name,' ',cl1.last_name,' ',cl1.mothers_name)) as client_name, cl2.last_name as pl_name")
                ->selectRaw("eao.amount as expected_amount, ea.account_number, ba.shortname,cu.sign as currency_sign, cu.name as currency_name, concat(us.name, ' ', us.last_name) as analyst")
                ->selectRaw("if(eao.deposit_at is null, 'Pendiente fondos', if(bao.signed_at is null, '2da firma pendiente', if(bao.signed_at is not null and bao.deposit_at is null,'Depósito en proceso',0))) as deposit_status")
                ->join('view_operations_online as op1', 'op1.id', "=", "operation_matches.operation_id")
                ->join('view_operations_online as op2', 'op2.id', "=", "operation_matches.matched_id")
                ->join('clients as cl1', 'cl1.id', "=", "op1.client_id")
                ->join('clients as cl2', 'cl2.id', "=", "op2.client_id")
                ->join('escrow_account_operation as eao', 'eao.operation_id', "=", "op2.id")
                ->join('escrow_accounts as ea', 'eao.escrow_account_id', "=", "ea.id")
                ->join('banks as ba', 'ba.id', "=", "ea.bank_id")
                ->join('currencies as cu', 'cu.id', "=", "ea.currency_id")
                ->join('users as us', 'us.id', "=", "op1.operations_analyst_id")
                ->join('bank_account_operation as bao', 'bao.escrow_account_operation_id', "=", "eao.id")
                ->whereRaw("op1.operation_status_id = 4 and bao.deposit_at is null")
                ->get();
        }

            

        return response()->json([
            'success' => true,
            'data' => [
                'indicators' => $indicators,
                'graphs' => $graphs,
                'pending_operations' => $pending_operations,
                'matched_operations' => $matched_operations,
                'pending_deposits' => $pending_deposits
            ]
        ]);

    }

    public function vendor_list(Request $request) {

        return response()->json([
            'success' => true,
            'data' => [
                'vendors' => Client::select('id','name','last_name','type')->where('type', 'PL')->where('client_status_id', 3)->get()
            ]
        ]);
    }

    public function match_operation(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        ####### Validating operation is not previusly matched ##########
        $operation_match = DB::table('operation_matches')
            ->where("operation_id", $operation->id)
            ->get();

        if($operation_match->count() > 0) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación ya se encuentra emparejada'
                ]
            ]);
        }

        ######### Creating vendor operation #############

        $op_code = Carbon::now()->format('ymdHisv') . rand(0,9);
        $status_id = OperationStatus::where('name', 'Pendiente envio fondos')->first()->id;

        // Calculando detracción
        $detraction_percentage = Configuration::where('shortname', 'DETRACTION')->first()->value;
        $detraction_amount = 0;

        $matched_operation = Operation::create([
            'code' => $op_code,
            'class' => Enums\OperationClass::Inmediata,
            'type' => ($operation->type == "Compra") ? 'Venta' : ($operation->type == "Venta" ? 'Compra' : 'Interbancaria'),
            'client_id' => $request->client_id,
            'user_id' => auth()->id(),
            'use_escrow_account' => $operation->use_escrow_account,
            'amount' => $operation->amount,
            'currency_id' => $operation->currency_id,
            'exchange_rate' => $operation->exchange_rate,
            'comission_spread' => 0,
            'comission_amount' => 0,
            'detraction_amount' => $detraction_amount,
            'detraction_percentage' => $detraction_percentage,
            'igv' => 0,
            'spread' => ($operation->type == "Interbancaria") ? $operation->spread : 0,
            'operation_status_id' => $status_id,
            'operation_date' => Carbon::now(),
            'post' => false
        ]);

        if($matched_operation){
            foreach ($operation->bank_accounts as $bank_account_data) {
                
                $escrow_account = EscrowAccount::where('bank_id',$bank_account_data->bank_id)
                    ->where('currency_id', $bank_account_data->currency_id)
                    ->where('active', 1)
                    ->first();

                $vendor_escrow = BankAccount::where('bank_id', $bank_account_data->bank_id)
                    ->where('client_id', $request->client_id)
                    ->where('currency_id', $bank_account_data->currency_id)
                    ->get();

                if(!is_null($escrow_account) && $vendor_escrow->count() > 0){
                    $matched_operation->escrow_accounts()->attach($escrow_account->id, [
                        'amount' => $bank_account_data->pivot->amount + $bank_account_data->pivot->comission_amount,
                        'comission_amount' => 0,
                        'created_at' => Carbon::now()
                    ]);
                }
                else{

                    // Si es banco Pichincha que devuelva error porque solo lo debe tomar coril (banbif tb por si es mi banco)
                    if($bank_account_data->bank_id == 8 || $bank_account_data->bank_id == 6){
                        return response()->json([
                            'success' => false,
                            'errors' => [
                                'Error en cuenta fideicomiso'
                            ]
                        ], 404);
                    }

                    $escrow_account = EscrowAccount::where('bank_id', Configuration::where('shortname', 'DEFAULTBANK')->first()->value)
                        ->where('currency_id', $bank_account_data->currency_id)
                        ->first();

                    if(!is_null($escrow_account)){
                        $matched_operation->escrow_accounts()->attach($escrow_account->id, [
                            'amount' => $bank_account_data->pivot->amount + $bank_account_data->pivot->comission_amount,
                            'comission_amount' => 0,
                            'created_at' => Carbon::now()
                        ]);
                    }
                    else{
                        return response()->json([
                            'success' => false,
                            'errors' => [
                                'Error en cuenta bancaria'
                            ]
                        ], 404);
                    }
                }

                // Obteniendo el bank accont operation de la op creadora para poder actualizar la cuenta de fideicomiso de donde saldrán los fondos
                $bank_account_operation = DB::table('bank_account_operation')
                    ->where('operation_id', $operation->id)
                    ->where('bank_account_id', $bank_account_data->id);

                // Actualizando escrow_account_operation_id en tabla bank_account_operation para saber de donde salndrán los fondos
                $matched_operation_insert = DB::table('escrow_account_operation')
                    ->where('operation_id', $matched_operation->id)
                    ->where('escrow_account_id', $escrow_account->id)
                    ->first();

                $bank_account_operation->update([
                    'escrow_account_operation_id' => $matched_operation_insert->id
                ]);
            }

            foreach ($operation->escrow_accounts as $escrow_account_data) {
                
                $bank_account = BankAccount::where('bank_id',$escrow_account_data->bank_id)
                    ->where('client_id', $request->client_id)
                    ->where('currency_id', $escrow_account_data->currency_id)
                    ->first();

                $escrow_account_operation = DB::table('escrow_account_operation')
                                ->where('operation_id', $operation->id)
                                ->where('escrow_account_id', $escrow_account_data->id);

                if(!is_null($bank_account)){
                    $matched_operation->bank_accounts()->attach($bank_account->id, [
                        'amount' => $escrow_account_data->pivot->amount - $escrow_account_data->pivot->comission_amount,
                        'comission_amount' => 0,
                        'escrow_account_operation_id' => ($escrow_account_operation->get()->count() > 0 ) ? $escrow_account_operation->first()->id : null,
                        'created_at' => Carbon::now()
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en cuenta bancaria'
                        ]
                    ], 404);
                }

            }

            $operations_matches = $operation->matches()->attach($matched_operation->id, ['created_at' => Carbon::now()]);

            $operation->operation_status_id = $status_id;
            $operation->save();
        }

        // Enviar correo instrucciones ()
        $rpta_mail = Mail::send(new OperationInstructions($operation->id));
        $rpta_mail = Mail::send(new OperationInstructions($matched_operation->id));

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación emparejada"]);

        AvailableOperations::dispatch();

        return response()->json([
            'success' => true,
            'data' => [
                'Operación emparejada exitosamente'
            ]
        ]);
    }

    public function cancel(Request $request, Operation $operation) {

        $operation->operation_status_id = OperationStatus::where('name', 'Cancelado')->first()->id;
        $operation->canceled_at = Carbon::now();
        $operation->save();

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación cancelada"]);

        return response()->json([
            'success' => true,
            'data' => [
                'Operación cancelada'
            ]
        ]);
    }

    public function confirm_funds(Request $request, Operation $operation) {

        if( $operation->operation_status_id != OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación no se encuentra en estado Pendiente envio fondos'
                ]
            ]);
        }

        if($operation->use_escrow_account == 1){
            if($operation->matches->count() > 0) { // Si es operación creadora
                if($operation->matches[0]->operation_status_id == OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){

                    $escrow_account = DB::table('escrow_account_operation')
                        ->where('operation_id', $operation->id)
                        ->where('voucher_id', null)
                        ->get();

                    if($escrow_account->count() == 0){
                        $operation->operation_status_id = OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id;
                        $operation->funds_confirmation_date = Carbon::now();
                        $operation->save();

                        ########### Envío operación a WS CORFID
                        // Notificación Telegram
                        try {
                            $consult = new WsCorfidController();
                            $result = $consult->register_operation($request, $operation)->getData();
                        } catch (\Exception $e) {
                            logger('ERROR: envío operación a WS CORFID: DailyOperationsController@confirm_funds', ["error" => $e]);
                        }
                    }
                    else{
                        return response()->json([
                            'success' => false,
                            'errors' => [
                                'No se han subido los comprobantes en todas las cuentas'
                            ]
                        ]);
                    }
                    
                }
                elseif($operation->matches[0]->operation_status_id == OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id){
                    $operation->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                    $operation->funds_confirmation_date = Carbon::now();
                    $operation->save();

                    $operation->matches[0]->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                    $operation->matches[0]->save();
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en el estado de la operación emparejadora'
                        ]
                    ]);
                }
            }
            elseif ($operation->matched_operation->count() > 0) { // Si es operación emparejadora
                
                $escrow_account = DB::table('escrow_account_operation')
                    ->where('operation_id', $operation->id);

                if(is_null($escrow_account->first())){
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en número de operación',
                        ]
                    ]);
                }

                if($operation->matched_operation[0]->operation_status_id == OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){
                    $operation->operation_status_id = OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id;
                    $operation->funds_confirmation_date = Carbon::now();
                    $operation->save();
                }
                elseif($operation->matched_operation[0]->operation_status_id == OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id){
                    
                    if($operation->client->type == 'PL'){
                        $operation->operation_status_id = OperationStatus::where('name', 'Fondos enviados')->first()->id;
                        $operation->deposit_date = Carbon::now();
                    }
                    else{
                        $operation->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                        $operation->funds_confirmation_date = Carbon::now();
                    }
                    
                    
                    $operation->save();

                    $operation->matched_operation[0]->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                    $operation->matched_operation[0]->save();
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en el estado de la operación emparejadora'
                        ]
                    ]);
                }

                /*if($escrow_account->first()->transfer_number != null){

                }
                else{
                    $escrow_account->update(['transfer_number' => 1]);
                }*/
            }
        }
        else{
            if($operation->matches->count() > 0) { // Si es operación creadora
                if($operation->matches[0]->operation_status_id == OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){
                    $operation->operation_status_id = OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id;
                    $operation->funds_confirmation_date = Carbon::now();
                    $operation->save();

                    $operation->matches[0]->operation_status_id = OperationStatus::where('name', 'Fondos enviados')->first()->id;
                    $operation->matches[0]->save();

                    /*DailyOperationsController::vendor_instruction($request, Operation::where('id', $operation->matches[0]->id)->first());*/
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en el estado de la operación emparejadora'
                        ]
                    ]);
                }
            }
        }

            
        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Fondos confirmados"]);

        // Notificación Telegram
        try {
            $request['operation_id'] = $operation->id;
            $consult = new TelegramNotificationsControllers();
            $notification = $consult->confirm_funds_notification($request)->getData();
        } catch (\Exception $e) {
            logger('ERROR: notificación telegram: DailyOperationsController@confirm_funds', ["error" => $e]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function upload_voucher(Request $request) {
        $val = Validator::make($request->all(), [
            'operation_id' => 'required|exists:operations,id',
            'escrow_account_id' => 'nullable|exists:escrow_accounts,id',
            'vendor_bank_account_id' => 'nullable|exists:bank_accounts,id',
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());

        logger('Archivo adjunto: DailyOperationsController@upload_voucher', ["operation_id" => $request->operation_id, "escrow_account_id" => $request->escrow_account_id]);

        $operation_id = $request->operation_id;
        $operation = Operation::find($request->operation_id);

        if($operation->use_escrow_account == 1){
            $escrow_account = DB::table('escrow_account_operation')
                ->where('escrow_account_id', $request->escrow_account_id)
                ->where('operation_id', $request->operation_id);

            if(is_null($escrow_account->first())){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en número de operación'
                    ]
                ]);
            }
        }
        else{
            $escrow_account = DB::table('vendor_bank_account_operation')
                ->where('bank_account_id', $request->vendor_bank_account_id)
                ->where('operation_id', $request->operation_id);

            if(is_null($escrow_account->first())){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en número de operación'
                    ]
                ]);
            }
        }

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/operations/';

            
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            if($longitud >= 10) {
                $filename = $operation->code . "_cprbte_" . substr($original_name, $longitud - 10, $longitud);
            }
            else{
                $filename = $operation->code . "_cprbte_" . $original_name;
            }

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                $creation_date = Carbon::now();
                // Si es operación de Cliente se elimina comprobantes anteriores
                if($operation->client->type == 'Cliente'){
                    // Si existe otro documento, se captura la fecha de creación para no perderla
                    $document = OperationDocument::where('id', $escrow_account->first()->voucher_id)
                        ->where('type', Enums\DocumentType::Comprobante)->first();
                    if(!is_null($document)){
                        $creation_date = $document->created_at;
                    }

                    // eliminando cualquier comprobante anterior
                    $delete = OperationDocument::where('id', $escrow_account->first()->voucher_id)
                        ->where('type', Enums\DocumentType::Comprobante)
                        ->delete();
                }

                $insert = OperationDocument::create([
                    'operation_id' => $request->operation_id,
                    'type' => Enums\DocumentType::Comprobante,
                    'created_at' => $creation_date,
                    'document_name' => $filename
                ]);

                if($insert){
                    $escrow_account->update([
                        "voucher_id" => $insert->id
                    ]);
                }

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: DailyOperationsController@upload_voucher', ["error" => $e]);

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el archivo adjunto'
                    ]
                ]);
            }

            OperationHistory::create(["operation_id" => $request->operation_id,"user_id" => auth()->id(),"action" => "Comprobante cargado", "detail" => 'filename: ' . $filename . ', id: ' . $insert->id]);

            // Notificación Telegram
            try {
                $consult = new TelegramNotificationsControllers();
                $notification = $consult->client_voucher($request)->getData();
            } catch (\Exception $e) {
                logger('ERROR: notificación telegram: DailyOperationsController@upload_voucher', ["error" => $e]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'Archivo agregado'
                ]
            ]);

        } else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error en el archivo adjunto'
                ]
            ]);
        }
    }

    public function delete_voucher(Request $request) {
        $val = Validator::make($request->all(), [
            'operation_id' => 'required|exists:operations,id',
        ]);
        if($val->fails()) return response()->json($val->messages());

        logger('Archivo adjunto: DailyOperationsController@delete_voucher', ["operation_id" => $request->operation_id, "operation_document_id" => $request->operation_document_id]);

        $document = OperationDocument::where('id', $request->operation_document_id)->where('operation_id', $request->operation_id);

        // Confirming that document exists
        if($document->count() == 0){
            return response()->json([
                'success' => false,
                'errors' => [
                    'No se encontró el comprobante'
                ]
            ]);
        }

        // Deleting file from S3
        try {
            $document_name = $document->first()->document_name;
            $rpta = Storage::disk('s3')->delete(env('AWS_ENV').'/operations/' . $document_name);
        } catch (\Exception $e) {
            logger('ERROR: eliminando voucher S3: DailyOperationsController@delete_voucher', ["error" => $e]);
        }


        // deleting voucher from escrow_account_operations
        $escrow_account = DB::table('escrow_account_operation')
            ->where('voucher_id', $request->operation_document_id)
            ->where('operation_id', $request->operation_id)->update(['voucher_id' => null]);


        // deleting voucher from operation_documents table
        $delete = OperationDocument::where('id', $request->operation_document_id)->where('operation_id', $request->operation_id)
            ->where('type', Enums\DocumentType::Comprobante)
            ->delete();

        OperationHistory::create(["operation_id" => $request->operation_id,"user_id" => auth()->id(),"action" => "Comprobante eliminado", "detail" => 'operation_document_id: ' . $request->operation_document_id]);

        return response()->json([
            'success' => true,
            'data' => [
                'Comprobante eliminado exitosamente'
            ]
        ]);
    }

    public function upload_deposit_client(Request $request) {
        $val = Validator::make($request->all(), [
            'operation_id' => 'required|exists:operations,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());

        logger('Archivo adjunto: DailyOperationsController@upload_deposit_client', ["operation_id" => $request->operation_id, "escrow_account_id" => $request->escrow_account_id]);

        $bank_account = DB::table('bank_account_operation')
            ->where('bank_account_id', $request->bank_account_id)
            ->where('operation_id', $request->operation_id);

        if(is_null($bank_account->first())){
            return response()->json([
                'success' => false,
                'errors' => 'Error en número de operación',
            ]);
        }

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/operations/';

            $operation = Operation::find($request->operation_id);
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            if($longitud >= 10) {
                $filename = $operation->code . "_dpst_" . substr($original_name, $longitud - 10, $longitud);
            }
            else{
                $filename = $operation->code . "_dpst_" . $original_name;
            }

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                // eliminando cualquier comprobante anterior
                $delete = OperationDocument::where('id', $bank_account->first()->voucher_id)
                    ->where('type', Enums\DocumentType::Firma2)
                    ->delete();

                $insert = OperationDocument::create([
                    'operation_id' => $request->operation_id,
                    'type' => Enums\DocumentType::Firma2,
                    'document_name' => $filename
                ]);

                if($insert){
                    $bank_account->update([
                        "voucher_id" => $insert->id
                    ]);
                }

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: DailyOperationsController@upload_deposit_client', ["error" => $e]);

                return response()->json([
                    'success' => false,
                    'errors' => 'Error en el archivo adjunto',
                ]);
            }

            OperationHistory::create(["operation_id" => $request->operation_id,"user_id" => auth()->id(),"action" => "Comprobante cargado", "detail" => 'filename: ' . $filename]);

            /*// Notificación Telegram
            try {
                $consult = new TelegramNotificationsControllers();
                $notification = $consult->client_voucher($request)->getData();
            } catch (\Exception $e) {
                logger('ERROR: notificación telegram: DailyOperationsController@upload_deposit_client', ["error" => $e]);
            }*/

            return response()->json([
                'success' => true,
                'data' => [
                    'Archivo agregado'
                ]
            ]);

        } else{
            return response()->json([
                'success' => false,
                'errors' => 'Error en el archivo adjunto',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'Archivo agregado'
            ]
        ]);
    }

    public function upload_document(Request $request) {
        $val = Validator::make($request->all(), [
            'operation_id' => 'required|exists:operations,id',
            'sign' => 'required|in:1,2',
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());

        logger('Archivo adjunto: DailyOperationsController@upload_documents', ["operation_id" => $request->operation_id]);

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/operations/';

            $operation = Operation::find($request->operation_id);
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            if($longitud >= 10) {
                $filename = $operation->code . "_firma" . $request->sign . "_" . substr($original_name, $longitud - 10, $longitud);
            }
            else{
                $filename = $operation->code . "_firma" . $request->sign . "_" . $original_name;
            }

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                $insert = OperationDocument::create([
                    'operation_id' => $request->operation_id,
                    'type' => $request->sign == 1 ? Enums\DocumentType::Firma1 : Enums\DocumentType::Firma2,
                    'document_name' => $filename
                ]);

                // Si cliente emparejador es Cliente y no PL, se guarda archivo en bank_account_operation
                if($operation->client->type == 'Cliente' && $request->sign == 2){
                    $bank_account = DB::table('bank_account_operation')
                        ->where('operation_id', $request->operation_id);

                    $bank_account->update([
                        "voucher_id" => $insert->id
                    ]);
                }

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: DailyOperationsController@upload_documents', ["error" => $e]);
            }

            OperationHistory::create(["operation_id" => $request->operation_id,"user_id" => auth()->id(),"action" => "Documento firma ". $request->sign ." cargado", "detail" => 'filename: ' . $filename]);

            return response()->json([
                'success' => true,
                'data' => [
                    'Archivo agregado'
                ]
            ]);

        } else{
            return response()->json([
                'success' => false,
                'errors' => 'Error en el archivo adjunto',
            ]);
        }
    }

    public function delete_document(Request $request, OperationDocument $operation_document) {
        logger('Archivo adjunto: DailyOperationsController@delete_document', ["operation_document" => $operation_document->id]);

        if(is_null($operation_document)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error en documento enviado'
                ]
            ]);
        }

        if (Storage::disk('s3')->exists(env('AWS_ENV').'/operations/' . $operation_document->document_name)) {
            $rpta = Storage::disk('s3')->delete(env('AWS_ENV').'/operations/' . $operation_document->document_name);

            $operation_document->delete();

            return response()->json([
                'success' => true,
                'data' => [
                    'Documento eliminado exitosamente'
                ]
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Archivo no encontrado'
                ]
            ]);
        }

        return Storage::disk('s3')->delete(env('AWS_ENV').'/operations/' . $operation_document->name);
    }

    public function to_pending_funds(Request $request, Operation $operation) {

        $operation->operation_status_id = OperationStatus::where('name', 'Pendiente envio fondos')->first()->id;
        $operation->save();

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "A pendiente envío de fondos"]);

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }


    public function invoice(Request $request, Operation $operation) {

        $configurations = new Configuration();
        
        if($operation->operation_status_id == OperationStatus::where('name', 'Facturado')->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación ya se encuentra facturada'
                ]
            ]);
        }

        if($operation->matches->count() > 0 && $operation->use_escrow_account == 1) { // Si es operación creadora
            $bank_account = DB::table('bank_account_operation')
            ->where('operation_id', $operation->id)
            ->where('deposit_at', null);

            if($bank_account->get()->count() == 1){
                $bank_account->update([
                    "deposit_at" => Carbon::now()
                ]);
            }
            elseif($bank_account->get()->count() > 1){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Aún no se confirma el depósito de todas las cuentas del cliente'
                    ]
                ]);
            }
        }

        if($operation->comission_amount > 0){

            if(is_null($operation->client->invoice_to)){
                $client_name = $operation->client->client_full_name;
                $customer_type = ($operation->client->customer_type == 'PJ') ? 1 : 2;
                $invoice_serie = (($operation->client->customer_type == 'PJ') ? 'F' : 'B') .'001';
                $client_document_type = ($operation->client->document_type->name == 'RUC') ? 6 : ($operation->client->document_type->name == 'DNI' ? 1 : ($operation->client->document_type->name == 'Carné de extranjería' ? 4 : null));
                $client_document_number = $operation->client->document_number;
                $localidad = isset($operation->client->district) ? $operation->client->district->name . " - " . $operation->client->district->province->name ." - " . $operation->client->district->province->department->name : "";
                $client_address = $operation->client->address . ", " . $localidad;
                $client_type = $operation->client->customer_type;
            }
            else{
                $client = Client::find($operation->client->invoice_to);

                $client_name = $client->client_full_name;
                $customer_type = ($client->customer_type == 'PJ') ? 1 : 2;
                $invoice_serie = (($client->customer_type == 'PJ') ? 'F' : 'B') .'001';
                $client_document_type = ($client->document_type->name == 'RUC') ? 6 : ($client->document_type->name == 'DNI' ? 1 : ($client->document_type->name == 'Carné de extranjería' ? 4 : null));
                $client_document_number = $client->document_number;
                $localidad = isset($client->district) ? $client->district->name . " - " . $client->district->province->name ." - " . $client->district->province->department->name : "";
                $client_address = $client->address . ", " . $localidad;
                $client_type = $client->customer_type;
            }

            $executive_email = (!is_null($operation->client->executive)) ? $operation->client->executive->user->email : null;

            $detraction = ($operation->detraction_amount > 0 && $client_type == 'PJ') ? "true" : "false";
            $detraction_type = ($operation->detraction_amount > 0 && $client_type == 'PJ') ? 35 : "";
            $detraction_total = ($operation->detraction_amount > 0 && $client_type == 'PJ') ? round($operation->detraction_amount,2) : "";
            $detraction_percentage = ($operation->detraction_amount > 0) ? Configuration::where('shortname', 'DETRACTION')->first()->value : "";
            $detraction_payment = ($operation->detraction_amount > 0 && $client_type == 'PJ') ? 1 : "";

            $currency_detraction = ($operation->type == 'Interbancaria') ? $operation->currency->sign : 'S/';

            $observation = ($operation->detraction_amount > 0 && $client_type == 'PJ') ? "Monto detracción: " . $currency_detraction . round($operation->detraction_amount,2) : "";

            try{

                $data = array(
                    "operacion"                         => "generar_comprobante",
                    "tipo_de_comprobante"               => $customer_type,
                    "serie"                             => $invoice_serie,
                    "numero"                            => "",
                    "sunat_transaction"                 => "1",
                    "cliente_tipo_de_documento"         => $client_document_type,
                    "cliente_numero_de_documento"       => $client_document_number,
                    "cliente_denominacion"              => ucfirst($client_name),
                    "cliente_direccion"                 => $client_address,
                    "cliente_email"                     => "",
                    "cliente_email_1"                   => "",
                    "cliente_email_2"                   => "",
                    "fecha_de_emision"                  => Carbon::now()->format('d-m-Y'),
                    "fecha_de_vencimiento"              => Carbon::now()->format('d-m-Y'),
                    "moneda"                            => ($operation->type == 'Interbancaria') ? $operation->currency_id : 1,
                    "tipo_de_cambio"                    => ($operation->type == 'Interbancaria' && $operation->currency_id == 2) ? $operation->exchange_rate : "",
                    "porcentaje_de_igv"                 => $configurations->get_value('IGV'),
                    "descuento_global"                  => "",
                    "descuento_global"                  => "",
                    "total_descuento"                   => "",
                    "total_anticipo"                    => "",
                    "total_gravada"                     => round($operation->comission_amount, 2),
                    "total_inafecta"                    => "",
                    "total_exonerada"                   => "",
                    "total_igv"                         => round($operation->igv, 2),
                    "total_gratuita"                    => "",
                    "total_otros_cargos"                => "",
                    "total"                             => round($operation->comission_amount + $operation->igv, 2),
                    "percepcion_tipo"                   => "",
                    "percepcion_base_imponible"         => "",
                    "total_percepcion"                  => "",
                    "total_incluido_percepcion"         => "",
                    "detraccion"                        => $detraction,
                    "detraccion_tipo"                   => $detraction_type,
                    "detraccion_total"                  => $detraction_total,
                    "detraccion_porcentaje"             => $detraction_percentage,
                    "medio_de_pago_detraccion"          => $detraction_payment,
                    "observaciones"                     => $observation,
                    "documento_que_se_modifica_tipo"    => "",
                    "documento_que_se_modifica_serie"   => "",
                    "documento_que_se_modifica_numero"  => "",
                    "tipo_de_nota_de_credito"           => "",
                    "tipo_de_nota_de_debito"            => "",
                    "enviar_automaticamente_a_la_sunat" => "true",
                    "enviar_automaticamente_al_cliente" => "true",
                    "codigo_unico"                      => $operation->code,
                    "condiciones_de_pago"               => "CONTADO",
                    "medio_de_pago"                     => "",
                    "placa_vehiculo"                    => "",
                    "orden_compra_servicio"             => "",
                    "tabla_personalizada_codigo"        => "",
                    "formato_de_pdf"                    => "A4",
                    "items" => array(
                                    
                        array(
                            "unidad_de_medida"          => "ZZ",
                            "codigo"                    => "COMBILL",
                            "descripcion"               => "SERVICIOS PLATAFORMA BILLEX (" . date("d-m-Y", strtotime($operation->operation_date)) . " - " . strtoupper($operation->type) . " DE " . strtoupper($operation->currency->name) . " " . $operation->currency->sign . $operation->amount . " - TC " . round($operation->exchange_rate,6) . " - " . $operation->code . ")",
                            "cantidad"                  => "1",
                            "valor_unitario"            => round($operation->comission_amount, 2),
                            "precio_unitario"           => round($operation->comission_amount + $operation->igv, 2),
                            "descuento"                 => "",
                            "subtotal"                  => round($operation->comission_amount, 2),
                            "tipo_de_igv"               => "1",
                            "igv"                       => round($operation->igv, 2),
                            "total"                     => round($operation->comission_amount + $operation->igv, 2),
                            "anticipo_regularizacion"   => "false",
                            "anticipo_serie"            => "",
                            "anticipo_documento_numero" => ""
                        )   
                    )
                );

                // Executing Nubefact API
                $consulta = Http::withToken(env('NUBEFACT_TOKEN'))->post(env('NUBEFACT_URL'), $data);

                $rpta_json = json_decode($consulta);

                if(is_object($rpta_json)){
                    if(isset($rpta_json->errors)){
                        logger('ERROR: archivo adjunto: DailyOperationsController@invoice', ["error" => $rpta_json]);

                        $operation->operation_status_id = OperationStatus::where('name', 'Pendiente facturar')->first()->id;
                        $operation->deposit_date = Carbon::now();
                        $operation->save();

                        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Monto depositado, error al facturar."]);

                        return response()->json([
                            'success' => false,
                            'errors' => [
                                $rpta_json->errors
                            ]
                        ]);
                    }
                    else{
                        $operation->invoice_serie = $rpta_json->serie;
                        $operation->invoice_number = $rpta_json->numero;
                        $operation->invoice_url = $rpta_json->enlace;
                        $operation->invoice_date = Carbon::now();
                        $operation->operation_status_id = OperationStatus::where('name', 'Facturado')->first()->id;
                        if(is_null($operation->deposit_date)){
                            $operation->deposit_date = Carbon::now();
                        }
                        $operation->save();

                        // Generación de Factura inafecta
                        try {
                            $invoice = DailyOperationsController::invoice_unaffected($request, $operation);
                        } catch (\Exception $e) {
                            logger('ERROR: creación factura inafecta: DailyOperationsController@invoice', ["error" => $e]);
                        }

                        // Notificación Telegram
                        try {
                            $request['operation_id'] = $operation->id;
                            $consult = new TelegramNotificationsControllers();
                            $notification = $consult->client_deposit_confirmation($request)->getData();
                        } catch (\Exception $e) {
                            logger('ERROR: notificación telegram: DailyOperationsController@invoice', ["error" => $e]);
                        }

                        // Enviando Mail de facturación
                        try {
                            if(!is_null($operation->invoice_url)){
                                $rpta_mail = Mail::send(new Invoice($operation));
                            }
                        } catch (\Exception $e) {
                            logger('ERROR: Invoice Email: DailyOperationsController@invoice', ["error" => $e]);
                        }

                        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación facturada"]);

                        return response()->json([
                            'success' => true,
                            'data' => [
                                'Factura creada exitosamente'
                            ]
                        ]);
                    }
                }

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: Creación de Factura: DailyOperationsController@invoice', ["error" => $e]);
            }
        }
        else{
            $operation->operation_status_id = OperationStatus::where('name', 'Finalizado sin factura')->first()->id;
            if(is_null($operation->deposit_date)){
                $operation->deposit_date = Carbon::now();
            }
            $operation->save();

            OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación finalizada sin factura"]);

            return response()->json([
                'success' => true,
                'data' => [
                    'Operación finalizada exitosamente'
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'errors' => [
                'Ocurrió un error al facturar'
            ]
        ]);
    }

    public function invoice_email(Request $request, Operation $operation) {

        $rpta_mail = Mail::send(new Invoice($operation));

        return response()->json([
            'success' => true,
            'data' => [
                'Factura enviada'
            ]
        ]);
    }

    public function invoice_unaffected(Request $request, Operation $operation) {

        $configurations = new Configuration();

        if(!is_null($operation->unaffected_invoice_url)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación inafecta ya se encuentra facturada'
                ]
            ]);
        }

        if($operation->matches->count() > 0 && $operation->use_escrow_account == 1) { // Si es operación creadora
            $bank_account = DB::table('bank_account_operation')
            ->where('operation_id', $operation->id)
            ->where('signed_at', null)
            ->get();

            if($bank_account->count() > 0){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Aún no se envían todas las firmas para todas las cuentas del cliente'
                    ]
                ]);
            }
        }

        if(is_null($operation->client->invoice_to)){
            $client_name = $operation->client->client_full_name;
            $customer_type = ($operation->client->customer_type == 'PJ') ? 1 : 2;
            $invoice_serie = (($operation->client->customer_type == 'PJ') ? 'F' : 'B') .'001';
            $client_document_type = ($operation->client->document_type->name == 'RUC') ? 6 : ($operation->client->document_type->name == 'DNI' ? 1 : ($operation->client->document_type->name == 'Carné de extranjería' ? 4 : null));
            $client_document_number = $operation->client->document_number;
            $localidad = isset($operation->client->district) ? $operation->client->district->name . " - " . $operation->client->district->province->name ." - " . $operation->client->district->province->department->name : "";
            $client_address = $operation->client->address . ", " . $localidad;
            $client_type = $operation->client->customer_type;
        }
        else{
            $client = Client::find($operation->client->invoice_to);

            $client_name = $client->client_full_name;
            $customer_type = ($client->customer_type == 'PJ') ? 1 : 2;
            $invoice_serie = (($client->customer_type == 'PJ') ? 'F' : 'B') .'001';
            $client_document_type = ($client->document_type->name == 'RUC') ? 6 : ($client->document_type->name == 'DNI' ? 1 : ($client->document_type->name == 'Carné de extranjería' ? 4 : null));
            $client_document_number = $client->document_number;
            $localidad = isset($client->district) ? $client->district->name . " - " . $client->district->province->name ." - " . $client->district->province->department->name : "";
            $client_address = $client->address . ", " . $localidad;
            $client_type = $client->customer_type;
        }

        $executive_email = (!is_null($operation->client->executive)) ? $operation->client->executive->user->email : null;

        $currency = ($operation->type == 'Compra') ? 2 : (($operation->type == 'Venta') ? 1 : $operation->currency_id);
        $total_amount = ($operation->type == 'Venta') ? round($operation->amount * $operation->exchange_rate,2) : (($operation->type == 'Compra') ? $operation->amount : round($operation->amount/$operation->exchange_rate*($operation->exchange_rate+$operation->spread/10000), 2));

        $countervalue = round($operation->amount * $operation->exchange_rate,2);


        try{

            $data = array(
                "operacion"                         => "generar_comprobante",
                "tipo_de_comprobante"               => $customer_type,
                "serie"                             => $invoice_serie,
                "numero"                            => "",
                "sunat_transaction"                 => "1",
                "cliente_tipo_de_documento"         => $client_document_type,
                "cliente_numero_de_documento"       => $client_document_number,
                "cliente_denominacion"              => ucfirst($client_name),
                "cliente_direccion"                 => $client_address,
                "cliente_email"                     => "",
                "cliente_email_1"                   => "",
                "cliente_email_2"                   => "",
                "fecha_de_emision"                  => Carbon::now()->format('d-m-Y'),
                "fecha_de_vencimiento"              => Carbon::now()->format('d-m-Y'),
                "moneda"                            => $currency,
                "tipo_de_cambio"                    => $operation->exchange_rate,
                "porcentaje_de_igv"                 => $configurations->get_value('IGV'),
                "descuento_global"                  => "",
                "descuento_global"                  => "",
                "total_descuento"                   => "",
                "total_anticipo"                    => "",
                "total_gravada"                     => "",
                "total_inafecta"                    => $total_amount,
                "total_exonerada"                   => "",
                "total_igv"                         => 0,
                "total_gratuita"                    => "",
                "total_otros_cargos"                => "",
                "total"                             => $total_amount,
                "percepcion_tipo"                   => "",
                "percepcion_base_imponible"         => "",
                "total_percepcion"                  => "",
                "total_incluido_percepcion"         => "",
                "detraccion"                        => "",
                "detraccion_tipo"                   => "",
                "detraccion_total"                  => "",
                "detraccion_porcentaje"             => "",
                "medio_de_pago_detraccion"          => "",
                "observaciones"                     => "",
                "documento_que_se_modifica_tipo"    => "",
                "documento_que_se_modifica_serie"   => "",
                "documento_que_se_modifica_numero"  => "",
                "tipo_de_nota_de_credito"           => "",
                "tipo_de_nota_de_debito"            => "",
                "enviar_automaticamente_a_la_sunat" => "true",
                "enviar_automaticamente_al_cliente" => "true",
                "codigo_unico"                      => "X".$operation->code,
                "condiciones_de_pago"               => "CONTADO",
                "medio_de_pago"                     => "",
                "placa_vehiculo"                    => "",
                "orden_compra_servicio"             => "",
                "tabla_personalizada_codigo"        => "",
                "formato_de_pdf"                    => "A4",
                "items" => array(
                                
                    array(
                        "unidad_de_medida"          => "ZZ",
                        "codigo"                    => ($operation->class == 'Inmediata') ? "OPCV" : "OPITBC",
                        //"descripcion"               => "OP " . $operation->code . " - CLIENTE " . ($operation->type == "Compra" ? "COMPRA " : "VENDE ") . $operation->currency->sign . $operation->amount . " - CLIENTE " . ($operation->type == "Compra" ? "ENVIA S/" : "RECIBE S/") . $countervalue . " - TIPO DE CAMBIO: " . round($operation->exchange_rate, 6),
                        "descripcion"               => "OP " . $operation->code . " - CLIENTE " . ($operation->type == "Compra" ? "COMPRA " : ($operation->type == "Venta" ? "VENDE " : "TRANSFIERE ")) . $operation->currency->sign . $operation->amount . " - CLIENTE " . ($operation->type == "Compra" ? "ENVIA S/" : ($operation->type == "Venta" ? "RECIBE S/" : ("ENVIA ".$operation->currency->sign." "))) . ($operation->type == 'Venta' ? $countervalue : $total_amount) . " - TIPO DE CAMBIO: " . round($operation->exchange_rate, 6),

                        "cantidad"                  => "1",
                        "valor_unitario"            => $total_amount,
                        "precio_unitario"           => $total_amount,
                        "descuento"                 => "",
                        "subtotal"                  => $total_amount,
                        "tipo_de_igv"               => "9",
                        "igv"                       => 0,
                        "total"                     => $total_amount,
                        "anticipo_regularizacion"   => "false",
                        "anticipo_serie"            => "",
                        "anticipo_documento_numero" => ""
                    )   
                )
            );

            // Executing Nubefact API
            $consulta = Http::withToken(env('NUBEFACT_TOKEN'))->post(env('NUBEFACT_URL'), $data);

            $rpta_json = json_decode($consulta);

            if(is_object($rpta_json)){
                if(isset($rpta_json->errors)){
                    logger('ERROR: archivo adjunto: DailyOperationsController@invoice', ["error" => $rpta_json]);

                    OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Monto depositado, error al facturar inafecto."]);

                    return response()->json([
                        'success' => false,
                        'errors' => [
                            $rpta_json->errors
                        ]
                    ]);
                }
                else{
                    $operation->unaffected_invoice_serie = $rpta_json->serie;
                    $operation->unaffected_invoice_number = $rpta_json->numero;
                    $operation->unaffected_invoice_url = $rpta_json->enlace;
                    $operation->save();

                    OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación facturada inafecto"]);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'Factura creada exitosamente'
                        ]
                    ]);
                }
            }

        } catch (\Exception $e) {
            // Registrando el el log los datos ingresados
            logger('ERROR: archivo adjunto: DailyOperationsController@invoice', ["error" => $e]);
        }

        return response()->json([
            'success' => false,
            'errors' => [
                'Ocurrió un error al facturar inafecto'
            ]
        ]);
    }

    
    public function download_file(Request $request) {
        $val = Validator::make($request->all(), [
            'operation_id' => 'required|exists:operations,id',
            'document_id' => 'required|exists:operation_documents,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $document = OperationDocument::where('id',$request->document_id)->where('operation_id', $request->operation_id)->first();

        if(is_null($document)){
            return response()->json([
                'success' => false,
                'data' => [
                    $document->document_name
                ]
            ]);
        }

        if (Storage::disk('s3')->exists(env('AWS_ENV').'/operations/' . $document->document_name)) {
            return Storage::disk('s3')->download(env('AWS_ENV').'/operations/' . $document->document_name);
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Archivo no encontrado'
                ]
            ]);
        }

        return Storage::disk('s3')->download(env('AWS_ENV').'/operations/' . $document->name);
    }
    
    public function countervalue_list(Request $request) {

        $val = Validator::make($request->all(), [
            'date' => 'date'
        ]);
        if($val->fails()) return response()->json($val->messages());

        ########### Configuration ##################

        $date = isset($request->date) ? $request->date : Carbon::now()->format('Y-m-d');
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $status = substr(OperationStatus::wherein('name', ['Pendiente fondos contraparte', 'Contravalor recaudado'])->get()->pluck('id'), 1, Str::length(OperationStatus::wherein('name', ['Pendiente fondos contraparte', 'Contravalor recaudado'])->get()->pluck('id'))-2);

        $matched_operations = DB::table('operation_matches')
            ->select('operation_id', 'matched_id')
            ->join('view_operations_online as op1', 'op1.id', "=", "operation_matches.operation_id")
            ->join('view_operations_online as op2', 'op2.id', "=", "operation_matches.matched_id")
            //->whereRaw("date(operation_matches.created_at) = '$date'")
            ->whereRaw("(op1.operation_status_id in ($status) or op2.operation_status_id in ($status))")
            ->get();

        $matched_operations->each(function ($item, $key) {

            $item->created_operation = OperationOnline::where('id',$item->operation_id)
                ->select('view_operations_online.id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url','use_escrow_account')
                ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate + (spread/10000),4))) as final_exchange_rate")
                ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(round(amount/exchange_rate*(exchange_rate+spread/10000), 2) + comission_amount + igv, 2)) ) as counter_value")
                ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2) - amount , null ) as financial_expenses")
                ->with('status:id,name')
                ->with('client:id,name,last_name,mothers_name,customer_type,type')
                ->with('currency:id,name:sign')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('bank_accounts.bank:id,name,shortname,image')
                ->with('bank_accounts.currency:id,name,sign')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('escrow_accounts.currency:id,name,sign')
                ->with('escrow_accounts.bank:id,name,shortname,image')
                ->with('documents:id,operation_id,type')
                ->first();

            $item->matched_operation = OperationOnline::where('id',$item->matched_id)
                ->select('view_operations_online.id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url','use_escrow_account')
                ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate + (spread/10000),4))) as final_exchange_rate")
                ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(round(amount/exchange_rate*(exchange_rate+spread/10000), 2) + comission_amount + igv, 2)) ) as counter_value")
                ->selectRaw("if(type = 'Interbancaria', round(amount/exchange_rate*(exchange_rate+spread/10000), 2) - amount , null ) as financial_expenses")
                ->with('status:id,name')
                ->with('client:id,name,last_name,mothers_name,customer_type,type')
                ->with('currency:id,name:sign')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('bank_accounts.currency:id,name,sign')
                ->with('bank_accounts.bank:id,name,shortname,image')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('escrow_accounts.currency:id,name,sign')
                ->with('escrow_accounts.bank:id,name,shortname,image')
                ->with('documents:id,operation_id,type')
                ->first();
        });

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $matched_operations
            ]
        ]);
    }

    public function operation_sign(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'sign' => 'required|in:1,2',
            'bank_account_id' => 'nullable|exists:bank_accounts,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($request->sign == 1 && $operation->operation_status_id != OperationStatus::wherein('name', ['Pendiente envio fondos'])->first()->id && $operation->client->type == 'PL'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación debe encontrarse en estado Pendiente envio fondos.'
                ]
            ]);
        }
        elseif($request->sign == 1 && $operation->operation_status_id != OperationStatus::wherein('name', ['Contravalor recaudado'])->first()->id && $operation->client->type == 'Cliente'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación debe encontrarse en estado Pendiente envio fondos.'
                ]
            ]);
        }
        elseif($request->sign == 1) {
            
            // Enviar Correo()
            $created_operation = $operation->matched_operation[0];
            $rpta_mail = Mail::send(new OperationSign($created_operation, $request->sign));

            $operation->sign_date = Carbon::now();
            $operation->save();
        }

        if($request->sign == 2 && $operation->operation_status_id != OperationStatus::wherein('name', ['Contravalor recaudado'])->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación debe encontrarse en estado Contravalor recaudado.'
                ]
            ]);
        }
        elseif($request->sign == 2){

            $bank_account = DB::table('bank_account_operation')
                ->where('bank_account_id', $request->bank_account_id)
                ->where('operation_id', $operation->id);

            // calculando el monto que se ha confirmado en las cuentas de fideicomiso        
            $confirmed_amount = DB::table('escrow_account_operation')
                ->selectRaw('sum(amount - comission_amount) as confirmed_amount')
                ->where('id', $bank_account->first()->escrow_account_operation_id)
                ->where('deposit_at', '!=', null)
                ->first()->confirmed_amount;

            $sent_amount = DB::table('bank_account_operation')
                ->selectRaw('coalesce(sum(amount - comission_amount),0) as sent_amount')
                ->where('operation_id', $operation->id)
                ->where('escrow_account_operation_id', $bank_account->first()->escrow_account_operation_id)
                ->where('signed_at', '!=', null)
                ->first()->sent_amount;

            $available_amount = $confirmed_amount - $sent_amount;

            if($available_amount < $bank_account->first()->amount){
                return response()->json([
                    'success' => false,
                    'errors' => ['No se han confirmados los fondos suficientes para atender esta operación.']
                ]);
            }

            if(is_null($bank_account->first())){
                return response()->json([
                    'success' => false,
                    'errors' => ['Error en número de operación']
                ]);
            }

            if($bank_account->first()->voucher_id == null){
                return response()->json([
                    'success' => false,
                    'errors' => ['No se encontró el comprobante de 2da Firma']
                ]);
            }
            
            // Enviar Correo()
            $rpta_mail = Mail::send(new OperationSign($operation, $request->sign,$request->bank_account_id));

            $bank_account->update(['signed_at' => Carbon::now()]);

            if(is_null($operation->sign_date)){
                $operation->sign_date = Carbon::now();
                $operation->save();
            }

            
        }

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Firma enviada", "detail" => 'firma: ' . $request->sign]);

        // Notificación Telegram
        try {
            $request['operation_id'] = $operation->id;
            $consult = new TelegramNotificationsControllers();
            $notification = $consult->sign_notification($request)->getData();
        } catch (\Exception $e) {
            logger('ERROR: notificación telegram: DailyOperationsController@operation_sign', ["error" => $e]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'Correo de firma enviado',
            ]
        ]);
    }

    public function confirm_funds_pl(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'escrow_account_id' => 'required|exists:escrow_accounts,id',
        ]);
        if($val->fails()) return response()->json($val->messages());


        if( $operation->operation_status_id != OperationStatus::where('name', 'Fondos enviados')->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación debe encontrarse en estado Fondos enviados.'
                ]
            ]);
        }

        $escrow_account = DB::table('escrow_account_operation')
            ->where('escrow_account_id', $request->escrow_account_id)
            ->where('operation_id', $operation->id);

        if(is_null($escrow_account->first())){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error en número de operación',
                ]
            ]);
        }

        if($escrow_account->first()->deposit_at != null){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El depósito ya había sido confirmado',
                ]
            ]);
        }
        else{
            $escrow_account->update(['deposit_at' => Carbon::now()]);

            if(is_null($operation->funds_confirmation_date)){
                $operation->funds_confirmation_date = Carbon::now();
                $operation->save();
            }

            $escrow_accounts_pending = DB::table('escrow_account_operation')
                ->where('operation_id', $operation->id)
                ->where('deposit_at', null)
                ->get();

            if($escrow_accounts_pending->count() == 0){
                $close = DailyOperationsController::close_operation($request, $operation);
            }
        }

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Fondos PL confirmados"]);

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $escrow_account->get()
            ]
        ]);
    }
    
    public function confirm_deposit(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'bank_account_operation_id' => 'required|exists:bank_account_operation,id',
        ]);
        if($val->fails()) return response()->json($val->messages());


        if( $operation->operation_status_id != OperationStatus::where('name', 'Contravalor recaudado')->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación debe encontrarse en estado Contravalor recaudado.'
                ]
            ]);
        }

        $bank_account_operation = DB::table('bank_account_operation')
            ->where('bank_account_operation.id', $request->bank_account_operation_id)
            ->where('bank_account_operation.operation_id', $operation->id);

        if(is_null($bank_account_operation->first())){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error en número de operación',
                ]
            ]);
        }

        if($bank_account_operation->first()->deposit_at != null){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El depósito ya había sido confirmado',
                ]
            ]);
        }
        elseif($bank_account_operation->first()->signed_at == null){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Aún no se envía la firma correspondiente',
                ]
            ]);
        }
        else{
            $bank_account_operation->update(['deposit_at' => Carbon::now()]);
        }

        OperationHistory::create(["operation_id" => $operation->id, "user_id" => auth()->id(),"action" => "Confirmación de depósito a cliente"]);

        // Notificación Telegram
        try {
            $request->operation_id = $operation->id;
            $consult = new TelegramNotificationsControllers();
            $notification = $consult->confirm_deposit_notification($request)->getData();
        } catch (\Exception $e) {
            logger('ERROR: envío Notificación Telegram: DailyOperationsController@confirm_deposit', ["error" => $e]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $bank_account_operation->get()
            ]
        ]);
    }

    public function close_operation(Request $request, Operation $operation) {

        if($operation->operation_status_id != OperationStatus::wherein('name', ['Fondos enviados'])->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación debe encontrarse en estado Fondos enviados.'
                ]
            ]); 
        }
        else{

            $escrow_accounts = DB::table('escrow_account_operation')
                ->where('operation_id', $operation->id)
                ->where('deposit_at', null)
                ->get();

            // Si hay más de una cuenta de fideicomiso pendiente de confirmación de deposito
            if($escrow_accounts->count() > 1 ){

                return response()->json([
                        'success' => false,
                        'errors' => [
                            'No se han confirmado el depósito de todas las cuentas'
                        ]
                    ]);
            }
            else{
                $escrow_accounts = DB::table('escrow_account_operation')
                ->where('operation_id', $operation->id)
                ->where('deposit_at', null)
                ->update(['deposit_at' => Carbon::now()]);
            }

            $operation->operation_status_id = OperationStatus::where('name', 'Finalizado sin factura')->first()->id;
            $operation->funds_confirmation_date = is_null($operation->funds_confirmation_date) ? Carbon::now() : null;
            $operation->save();
        }

        if($operation->use_escrow_account==0){
            $operation->matched_operation[0]->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
            $operation->matched_operation[0]->save();
        }

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación Finalizada"]);

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function voucher_vendor_instruction(Request $request, Operation $operation) {

        if($operation->use_escrow_account == 1){
            $val = Validator::make($request->all(), [
                'file' => 'required|file'
            ]);
            if($val->fails()) return response()->json($val->messages());

            if($operation->client->type == 'Cliente'){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'El cliente de la operación no es un Proveedor de Liquidez'
                    ]
                ]);
            }

            if($request->hasFile('file')){
                $file = $request->file('file');
                $path = env('AWS_ENV').'/operations/';

                $original_name = $file->getClientOriginalName();
                $longitud = Str::length($file->getClientOriginalName());

                if($longitud >= 10) {
                    $filename = $operation->code . "_voucher_" . substr($original_name, $longitud - 10, $longitud);
                }
                else{
                    $filename = $operation->code . "_voucher_" . $original_name;
                }

                try {
                    $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);
 
                    $insert = OperationDocument::create([
                        'operation_id' => $operation->id,
                        'type' => Enums\DocumentType::Comprobante,
                        'document_name' => $filename
                    ]);

                } catch (\Exception $e) {
                    // Registrando el el log los datos ingresados
                    logger('ERROR: archivo adjunto: DailyOperationsController@voucher_vendor_instruction', ["error" => $e]);

                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en el archivo adjunto',
                        ]
                    ]);
                }

                OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Comprobante cargado", "detail" => 'filename: ' . $filename]);

            } else{
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el archivo adjunto',
                    ]
                ]);
            }
        }
        else{
            $document = OperationDocument::where('operation_id', $operation->matched_operation[0]->id)->where('type', Enums\DocumentType::Comprobante)->first();

            if(is_null($document)){
                return response()->json([
                    'success' => false,
                    'document' => $document,
                    'errors' => [
                        'Error: no se encontró el comprobante de transferencia del cliente',
                    ]
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'comprobante de instrucciones subido exitosamente',
            ]
        ]);
    }

    public function vendor_instruction(Request $request, Operation $operation) {
        try {
            // Enviar Correo()
            $rpta_mail = Mail::send(new VendorInstructions($operation));

            if(is_null($operation->mail_instructions)){
                $operation->mail_instructions = Carbon::now();
                $operation->save();
            }

        } catch (\Exception $e) {
            // Registrando el el log los datos ingresados
            logger('ERROR: envio correo PL: DailyOperationsController@vendor_instruction', ["error" => $e]);

            // eliminando cualquier comprobante anterior
            $delete = OperationDocument::where('operation_id', $operation->id)
                ->where('type', Enums\DocumentType::Comprobante)
                ->delete();

            return response()->json([
                'success' => false,
                'errors' => [
                    'Error al enviar correo'
                ]
            ]);
        }

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Correo de instrucciones PL enviado"]);

        return response()->json([
            'success' => true,
            'data' => [
                'Correo de instrucciones enviado exitosamente',
            ]
        ]);
    }

    // Actualización de parámetros de operación
    public function update(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'field' => 'required|in:amount,comission_spread,exchange_rate',
            'value' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if(OperationStatus::wherein('name', ['Facturado', 'Finalizado sin factura','Pendiente facturar'])->pluck('id')->contains($operation->operation_status_id)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación no puede estar finalizada para ser editada',
                ]
            ]);
        }
        else{
            if($request->field == 'amount'){
                $total_comission = round($request->value * ($operation->comission_spread/10000), 2);

                $igv_percetage = Configuration::where('shortname', 'IGV')->first()->value / 100;
                $comission_amount = round($total_comission / (1+$igv_percetage), 2);

                $igv = round($total_comission - $comission_amount,2);

                // Calculando detracción
                $detraction_percentage = Configuration::where('shortname', 'DETRACTION')->first()->value;
                $detraction_amount = 0;

                if($total_comission >= 700) {
                    $detraction_amount = round( ($total_comission) * ($detraction_percentage / 100), 0);
                }

                $operation->amount = $request->value;
                $operation->comission_amount = $comission_amount;
                $operation->igv = $igv;
                $operation->detraction_amount = $detraction_amount;
                $operation->save();
            }
            elseif($request->field == 'comission_spread'){
                $total_comission = round($operation->amount * $request->value/10000, 2);

                $igv_percetage = Configuration::where('shortname', 'IGV')->first()->value / 100;
                $comission_amount = round($total_comission / (1+$igv_percetage), 2);

                $igv = round($total_comission - $comission_amount,2);

                // Calculando detracción
                $detraction_percentage = Configuration::where('shortname', 'DETRACTION')->first()->value;
                $detraction_amount = 0;

                if($total_comission >= 700) {
                    $detraction_amount = round( ($total_comission) * ($detraction_percentage / 100), 0);
                }

                $operation->comission_spread = $request->value;
                $operation->comission_amount = $comission_amount;
                $operation->igv = $igv;
                $operation->detraction_amount = $detraction_amount;
                $operation->save();
            }
            else{
                $operation->exchange_rate = $request->value;
                $operation->save();
            }   
        }

        // Actualizando cuentas bancarias
        $mensaje = "";
        if($operation->bank_accounts()->count() > 1){
            $operation->bank_accounts()->detach();

            $mensaje .= "Debe configurar las cuentas bancarias de destino. ";
        }
        else{
            if($operation->type == 'Compra'){
                $operation->bank_accounts[0]->pivot->amount = $operation->amount;
                $operation->bank_accounts[0]->pivot->updated_at = Carbon::now();
                $operation->bank_accounts[0]->pivot->save();
            }
            elseif($operation->type == 'Venta'){
                $operation->bank_accounts[0]->pivot->amount = round($operation->amount * $operation->exchange_rate, 2) - $operation->comission_amount - $operation->igv;
                $operation->bank_accounts[0]->pivot->comission_amount = $operation->comission_amount + $operation->igv;
                $operation->bank_accounts[0]->pivot->updated_at = Carbon::now();
                $operation->bank_accounts[0]->pivot->save();
            }
        }

        // Actualizando cuentas de fideicomiso
        if($operation->escrow_accounts()->count() > 1){
            $operation->escrow_accounts()->detach();

            $mensaje .= "Debe configurar las cuentas de fideicomiso. ";
        }
        else{
            if($operation->type == 'Venta'){
                $operation->escrow_accounts[0]->pivot->amount = $operation->amount;
                $operation->escrow_accounts[0]->pivot->updated_at = Carbon::now();
                $operation->escrow_accounts[0]->pivot->save();
            }
            elseif($operation->type == 'Compra'){
                $operation->escrow_accounts[0]->pivot->amount = round($operation->amount * $operation->exchange_rate, 2) + $operation->comission_amount + $operation->igv;
                $operation->escrow_accounts[0]->pivot->comission_amount = $operation->comission_amount + $operation->igv;
                $operation->escrow_accounts[0]->pivot->updated_at = Carbon::now();
                $operation->escrow_accounts[0]->pivot->save();
            }
        }

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación actualizada", "detail" => $request->field . ":" . $request->value]);

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation,
                'mensaje' => $mensaje
            ]
        ]);
    }

    public function update_escrow_accounts(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'escrow_accounts' => 'required|array'
        ]);

        if($val->fails()) return response()->json($val->messages());

        $soles_id = Currency::where('name', 'Soles')->first()->id;
        $dolares_id = Currency::where('name', 'Dolares')->first()->id;
        $total_amount_escrow = 0;
        $total_comission = round($operation->comission_amount + $operation->igv);

        //Validating Escrow Accounts
        $escrow_accounts = [];
        foreach ($request->escrow_accounts as $escrow_account_data) {
            $escrow_account = EscrowAccount::where('id', $escrow_account_data['id'])
                ->where('active', true)
                ->first();

            if(is_null($escrow_account)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'La cuenta fideicomiso ' . $escrow_account_data['id'] . ' no es valida'
                    ]
                ]);
            }

            if($operation->type == 'Compra') {
                if($escrow_account->currency_id != $soles_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta fideicomiso ' . $escrow_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                if($escrow_account_data['amount'] >= $total_comission){
                    $escrow_account->comission_amount = $total_comission;
                    $total_comission = 0;
                }
                else{
                    $escrow_account->comission_amount = $escrow_account_data['amount'];
                    $total_comission = $total_comission -  $escrow_account_data['amount'];
                }

            } else {
                if($escrow_account->currency_id != $dolares_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta fideicomiso ' . $escrow_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                $escrow_account->comission_amount = 0;
            }

            $escrow_account->amount = $escrow_account_data['amount'];
            $total_amount_escrow = round(round($total_amount_escrow, 2) +  round($escrow_account_data['amount'],2),2);
            $escrow_accounts[] = $escrow_account;
        }

        //Validating amounts in accounts
        if($operation->type == 'Compra') {
            $envia = round($operation->amount * $operation->exchange_rate + $operation->comission_amount + $operation->igv,2);
            $recibe = $operation->amount;
        } else {
            $envia = $operation->amount;
            $recibe = round($operation->amount * $operation->exchange_rate - $operation->comission_amount - $operation->igv,2);
        }

        if( $envia != $total_amount_escrow){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La suma de montos enviados en las cuentas de fideicomiso es incorrecto = ' . $total_amount_escrow . '. Debería ser ' . $envia 
                ]
            ]);
        }

        //Setting null escrow account operation id in client destiny accounts
        DB::table('bank_account_operation')->whereIn('escrow_account_operation_id', $operation->escrow_accounts->pluck('pivot.id'))->update(['escrow_account_operation_id' => null]);

        // Detaching old escrow accounts from operation
        $operation->escrow_accounts()->detach();

        // attaching new escrow accounts
        foreach ($escrow_accounts as $escrow_account_data) {
            $operation->escrow_accounts()->attach($escrow_account_data['id'], [
                'amount' => $escrow_account_data['amount'],
                'comission_amount' => $escrow_account_data['comission_amount'],
                'created_at' => Carbon::now()
            ]);
        }

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Cuentas fideicomiso actualizadas"]);

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function update_client_accounts(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'bank_accounts' => 'required|array'
        ]);
        if($val->fails()) return response()->json($val->messages());


        //Validating Bank Accounts
        $soles_id = Currency::where('name', 'Soles')->first()->id;
        $dolares_id = Currency::where('name', 'Dolares')->first()->id;

        $bank_accounts = [];
        $total_amount_bank = 0;
        $total_comission = round($operation->comission_amount + $operation->igv);

        foreach ($request->bank_accounts as $bank_account_data) {
            $bank_account = BankAccount::where('id', $bank_account_data['id'])
                ->where('client_id', $operation->client_id)
                ->where('bank_account_status_id', BankAccountStatus::Activo)
                ->first();

            // Validating that the bank account is valid.
            if(is_null($bank_account)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en la cuenta bancaria id = ' . $bank_account_data['id']
                    ]
                ]);
            }

            if($operation->type == 'Compra') {
                if($bank_account->currency_id != $dolares_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta bancaria ' . $bank_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                $bank_account->comission_amount = 0 ;
            } else {
                if($bank_account->currency_id != $soles_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta bancaria ' . $bank_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                if($bank_account_data['amount'] >= $total_comission){
                    $bank_account->comission_amount = $total_comission;
                    $total_comission = 0;
                }
                else{
                    $bank_account->comission_amount = $bank_account_data['amount'];
                    $total_comission = $total_comission -  $bank_account_data['amount'];
                }
            }

            $bank_account->amount = $bank_account_data['amount'];
            $bank_account->escrow_account_operation_id = $bank_account_data['escrow_account_operation_id'];
            $total_amount_bank = round(round($total_amount_bank,2) + round($bank_account_data['amount'],2),2);
            $bank_accounts[] = $bank_account;
        }

        //Validating amounts in accounts
        if($operation->type == 'Compra') {
            $envia = round($operation->amount * $operation->exchange_rate + $operation->comission_amount + $operation->igv,2);
            $recibe = $operation->amount;
        } else {
            $envia = $operation->amount;
            $recibe = round($operation->amount * $operation->exchange_rate - $operation->comission_amount - $operation->igv,2);
        }

        if( $recibe != $total_amount_bank){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La suma de montos enviados en las cuentas bancarias del cliente es incorrecto = ' . $total_amount_bank . '. Debería ser ' . $recibe 
                ]
            ]);
        }

        // Detaching old client accounts from operation
        $operation->bank_accounts()->detach();

        // attaching new escrow accounts
        foreach ($bank_accounts as $bank_account_data) {
            $operation->bank_accounts()->attach($bank_account_data['id'], [
                'amount' => $bank_account_data['amount'],
                'comission_amount' => $bank_account_data['comission_amount'],
                'escrow_account_operation_id' => $bank_account_data['escrow_account_operation_id'],
                'created_at' => Carbon::now()
            ]);
        }

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Cuentas cliente actualizadas"]);

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }


    ################################################
    ###### Operation Analyst


    public function operation_analyst(Request $request) {

        $analyst = OperationsAnalyst::select('id','online','start_time','end_time')
            ->selectRaw("(select count(*) from operations where operations.operations_analyst_id = operations_analysts.id and operations.operation_status_id not in (6,7,9,10) and date(operations.operation_date) = date(now())) as ops_in_progress")
            ->selectRaw("(select count(*) from operations where operations.operations_analyst_id = operations_analysts.id and operations.operation_status_id in (6,7) and date(operations.operation_date) = date(now())) as ops_finished")
            ->where('id', auth()->id())
            ->where('status', 'Activo')
            ->first();


        if(is_null($analyst)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El usuario no está registrado como analista de operaciones'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'analyst' => $analyst,
            ]
        ]);
    }

    public function analyst_status(Request $request) {
        $analyst = OperationsAnalyst::where('id', auth()->id())
            ->where('status', 'Activo')
            ->first();


        if(is_null($analyst)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El usuario no está registrado como analista de operaciones o no se encuentra Activo'
                ]
            ]);
        }

        $analyst->online = !$analyst->online;
        $analyst->updated_at = Carbon::now();
        $analyst->save();

        OperationsAnalystLog::create([
            'operations_analyst_id' => auth()->id(),
            'online' => $analyst->online,
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'analyst' => $analyst,
            ]
        ]);
    }

    public function operation_analyst_summary(Request $request) {
        $analysts = OperationsAnalyst::select('id','online','start_time','end_time')
            ->selectRaw("(select count(*) from operations where operations.operations_analyst_id = operations_analysts.id and operations.operation_status_id not in (6,7,9,10)) as ops_in_progress")
            ->selectRaw("(select count(*) from operations where operations.operations_analyst_id = operations_analysts.id and operations.operation_status_id in (6,7) and date(operations.deposit_date) = date(now())) as ops_finished")
            ->selectRaw("coalesce(round((select sum(TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1 ),operations.deposit_date)) from operations where date(operations.deposit_date) = date(now()) and operations.operations_analyst_id = operations_analysts.id and operations.operation_status_id in (6,7,8))/(select count(*) from operations where operations.operations_analyst_id = operations_analysts.id and operations.operation_status_id in (6,7,8) and date(operations.deposit_date) = date(now())),0),0) as avg_time")
            ->where('status', 'Activo')
            ->with('user:id,name,last_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'analyst' => $analysts,
            ]
        ]);
    }

    public function operation_statuses(Request $request) {
        $statuses = OperationStatus::select('id','name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statuses' => $statuses,
            ]
        ]);
    }

    public function change_status(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'status' => 'required|exists:operation_statuses,id',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $operation->operation_status_id = $request->status;
        $operation->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Estado modificado exitosamente'
            ]
        ]);
    }

    public function executives_summary(Request $request) {
        $analysts = Executive::select('id')
            ->selectRaw("(select count(*) from operations inner join clients on operations.client_id = clients.id where clients.executive_id = executives.id and operations.operation_status_id not in (6,7,9,10)) as ops_in_progress")
            ->selectRaw("(select count(*) from operations inner join clients on operations.client_id = clients.id where clients.executive_id = executives.id and operations.operation_status_id in (6,7) and date(operations.deposit_date) = date(now())) as ops_finished")
            ->selectRaw("coalesce((select sum(amount) from operations inner join clients on operations.client_id = clients.id where clients.executive_id = executives.id and operations.operation_status_id not in (9,10) and date(operations.operation_date) = date(now())),0) as total_volume")
            ->where('type', 'Tiempo Completo')
            ->where('status', 'Activo')
            ->with('user:id,name,last_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'analyst' => $analysts,
            ]
        ]);
    }

    public function confirm_operation_pl(Request $request, Operation $operation) {

        if($operation->post != 2){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación no está en pendiente de confirmación PL'
                ]
            ]);
        }

        $operation->post = 3;
        $operation->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Operación confirmada con PL'
            ]
        ]);
    }

    public function origin_bank_accounts(Request $request, Operation $operation) {

        $currency = ($operation->type == 'Compra') ? 1 : 2;

        $bank_account = BankAccount::select('id','client_id','alias','bank_id','account_number','cci_number','currency_id')
            ->where('client_id', $operation->client_id)
            ->where('bank_account_status_id',1)
            ->where('currency_id', $currency)
            ->with('currency:id,name,sign')
            ->with('bank:id,name,shortname,image')
            ->with('client:id,name,last_name,mothers_name,customer_type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'client_bank_accounts' => $bank_account
            ]
        ]);
    }

    public function get_escrow_account_operation(Request $request) {
        $val = Validator::make($request->all(), [
            'escrow_account_operation_id' => 'required|exists:escrow_account_operation,id',
        ]);
        if($val->fails()) return response()->json($val->messages());


        $escrow_account_operation = DB::table('escrow_account_operation as eao')
            ->join('escrow_accounts as ea', 'ea.id','=','eao.escrow_account_id')
            ->join('currencies as cu', 'cu.id','=','ea.currency_id')
            ->join('operations as op', 'op.id','=','eao.operation_id')
            ->join('clients as cl', 'cl.id','=','op.client_id')
            ->join('banks as bk', 'bk.id','=','ea.bank_id')
            ->select('eao.id','eao.escrow_account_id','eao.operation_id','eao.amount','eao.comission_amount','ea.bank_id','ea.account_number','ea.currency_id','cu.name as currency_name','cu.sign as currency_sign','cl.last_name as pl_name','bk.shortname as bank_name')
            ->where('eao.id', $request->escrow_account_operation_id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'escrow_account_operation' => $escrow_account_operation
            ]
        ]);
    }

    public function get_escrow_account_operations(Request $request, Operation $operation) {

        $matched_operation_id = null;
        if($operation->matches->count() == 1){
            $matched_operation_id = $operation->matches->first()->id;
        }

        if($operation->matched_operation->count() == 1){
            $matched_operation_id = $operation->matched_operation->first()->id;
        }


        $escrow_account_operation = DB::table('escrow_account_operation as eao')
            ->join('escrow_accounts as ea', 'ea.id','=','eao.escrow_account_id')
            ->join('currencies as cu', 'cu.id','=','ea.currency_id')
            ->join('operations as op', 'op.id','=','eao.operation_id')
            ->join('clients as cl', 'cl.id','=','op.client_id')
            ->join('banks as bk', 'bk.id','=','ea.bank_id')
            ->select('eao.id','eao.escrow_account_id','eao.operation_id','eao.amount','eao.comission_amount','ea.bank_id','ea.account_number','ea.currency_id','cu.name as currency_name','cu.sign as currency_sign','cl.last_name as pl_name','bk.shortname as bank_name')
            ->where('op.id', $matched_operation_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'escrow_account_operations' => $escrow_account_operation
            ]
        ]);
    }
    
}
