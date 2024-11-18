<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Models\OperationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TelegramNotificationsControllers extends Controller
{
    // Notificación Nueva operación
    public function new_operation_confirmation(Request $request, $operation_id) {
        $operation = Operation::find($operation_id);

        $client = $operation->client->client_full_name;
        $currency = $operation->currency->sign;
        $executive = (!is_null($operation->client->executive)) ? $operation->client->executive->user->full_name : 'Sin ejecutivo';
        $analyst = (!is_null($operation->operations_analyst)) ? $operation->operations_analyst->user->full_name : 'No Asignado';
        $url = env('APP_URL');

        $message = "*Nueva Operación Creada*
Cliente: *$client*
Operación: $operation->code
Monto: $operation->type $currency$operation->amount
Ejecutivo: $executive
Analista: $analyst
Instructivo: [Descargar]($url/api/res/instruction/$operation_id)";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
        $postFields = array(
            'chat_id' => env('TELEGRAM_OPS_CHANNEL'),
            'text' => $message,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => false,
        );
        $rpta = curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        if(!curl_exec($ch)){
            logger('Error notificación Telegram: new_operation_confirmation@TelegramNotificationsControllers', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        // Enviando a equipo comercial
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
        $postFields = array(
            'chat_id' => env('TELEGRAM_VENTAS_CHANNEL'),
            'text' => $message,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => false,
        );
        $rpta = curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        if(!curl_exec($ch)){
            logger('Error notificación Telegram: new_operation_confirmation@TelegramNotificationsControllers', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        return response()->json([
            'success' => true,
            'data' => $rpta,
        ]);
    }

    // Notificación Envío Comprobante
    public function client_voucher(Request $request) {
        $operation = Operation::find($request->operation_id);

        $client = $operation->client->client_full_name;
        $currency = $operation->currency->sign;
        $executive = (!is_null($operation->client->executive)) ? $operation->client->executive->user->full_name : 'Sin ejecutivo';
        $analyst = (!is_null($operation->operations_analyst)) ? $operation->operations_analyst->user->full_name : 'No Asignado';

        $message = "_Comprobante enviado_
Cliente: *$client*
Operación: $operation->code
Monto: $operation->type $currency$operation->amount
Ejecutivo: $executive
Analista: $analyst";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
        $postFields = array(
            'chat_id' => env('TELEGRAM_OPS_CHANNEL'),
            'text' => $message,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => false,
        );
        $rpta = curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        if(!curl_exec($ch)){
            logger('Error notificación Telegram: client_voucher@TelegramNotificationsControllers', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        return response()->json([
            'success' => true,
            'data' => $rpta,
        ]);
    }

    // Notificación Confirmación envío fondos
    public function confirm_funds_notification(Request $request) {
        $operation = Operation::find($request->operation_id);

        // Si es operación creadora
        if($operation->matches->count() > 0) {
            $next_action = "1ra FIRMA";
        }
        else {
            $next_action = "2da FIRMA";
            $operation = Operation::find($request->operation_id)->matched_operation[0];
        }


        $client = $operation->client->client_full_name;
        $currency = $operation->currency->sign;
        $executive = (!is_null($operation->client->executive)) ? $operation->client->executive->user->full_name : 'Sin ejecutivo';
        $analyst = (!is_null($operation->operations_analyst)) ? $operation->operations_analyst->user->full_name : 'No Asignado';


        $banco_destino_cliente = "";

        foreach ($operation->bank_accounts as $key => $value) {
            $banco_destino_cliente .= $value->bank->shortname . " ";
        }

        $message = "_Confirmación envío de fondos_
Cliente: *$client*
Operación: $operation->code
Monto: $operation->type $currency$operation->amount
Banco Dest Cliente: $banco_destino_cliente
Ejecutivo: $executive
Analista: $analyst
Siguiente acción: *$next_action*";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
        $postFields = array(
            'chat_id' => env('TELEGRAM_OPS_CHANNEL'),
            'text' => $message,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => false,
        );
        $rpta = curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        if(!curl_exec($ch)){
            logger('Error notificación Telegram: confirm_funds_notification@TelegramNotificationsControllers', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        return response()->json([
            'success' => true,
            'data' => $rpta,
        ]);
    }

    // Notificación Envío Firmas
    public function sign_notification(Request $request) {
        if($request->sign == 1) {
            $accion = "1ra FIRMA";
            $operation = Operation::find($request->operation_id)->matched_operation[0];
        }
        else {
            $accion = "2da FIRMA";
            $operation = Operation::find($request->operation_id);
        }

        $client = $operation->client->client_full_name;
        $currency = $operation->currency->sign;
        $executive = (!is_null($operation->client->executive)) ? $operation->client->executive->user->full_name : 'Sin ejecutivo';
        $analyst = (!is_null($operation->operations_analyst)) ? $operation->operations_analyst->user->full_name : 'No Asignado';


        $message = "*$accion* enviada
Cliente: *$client*
Operación: $operation->code
Monto: $operation->type $currency$operation->amount
Ejecutivo: $executive
Analista: $analyst";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
        $postFields = array(
            'chat_id' => env('TELEGRAM_OPS_CHANNEL'),
            'text' => $message,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => false,
        );
        $rpta = curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        if(!curl_exec($ch)){
            logger('Error notificación Telegram: sign_notification@TelegramNotificationsControllers', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        return response()->json([
            'success' => true,
            'data' => $rpta,
        ]);
    }

    // Notificación Confirmación en cuenta
    public function confirm_deposit_notification(Request $request) {
        $operation = Operation::find($request->operation_id);

        $client = $operation->client->client_full_name;
        $currency = $operation->currency->sign;
        $executive = (!is_null($operation->client->executive)) ? $operation->client->executive->user->full_name : 'Sin ejecutivo';
        $analyst = (!is_null($operation->operations_analyst)) ? $operation->operations_analyst->user->full_name : 'No Asignado';

        $bank_account_operation = DB::table('bank_account_operation')
            ->where('bank_account_operation.id', $request->bank_account_operation_id)
            ->where('bank_account_operation.operation_id', $operation->id)
            ->join('bank_accounts as ba', 'ba.id','=','bank_account_operation.bank_account_id')
            ->join('banks as bk', 'bk.id','=','ba.bank_id')
            ->join('currencies as cu','cu.id','=','ba.currency_id')
            ->first();

        $total_amount = number_format($operation->amount,2);
        $deposit_amount = number_format($bank_account_operation->amount,2);

        $message = "*Confirmación depósito en cuenta*
Cliente: *$client*
Operación: $operation->code
Monto total: $operation->type $currency$total_amount
Monto depositado: $bank_account_operation->sign$deposit_amount
Cuenta Dest Cliente: $bank_account_operation->shortname $bank_account_operation->account_number
Ejecutivo: $executive
Analista: $analyst";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
        $postFields = array(
            'chat_id' => env('TELEGRAM_VENTAS_CHANNEL'),
            'text' => $message,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => false,
        );
        $rpta = curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        if(!curl_exec($ch)){
            logger('Error notificación Telegram: confirm_funds_notification@TelegramNotificationsControllers', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        return response()->json([
            'success' => true,
            'data' => $rpta,
        ]);
    }

    // Notificación Envío Firmas
    public function client_deposit_confirmation(Request $request) {
        if($request->sign == 1) {
            $accion = "1ra FIRMA";
            $operation = Operation::find($request->operation_id)->matched_operation[0];
        }
        else {
            $accion = "2da FIRMA";
            $operation = Operation::find($request->operation_id);
        }

        $client = $operation->client->client_full_name;
        $currency = $operation->currency->sign;
        $executive = (!is_null($operation->client->executive)) ? $operation->client->executive->user->full_name : 'Sin ejecutivo';
        $analyst = (!is_null($operation->operations_analyst)) ? $operation->operations_analyst->user->full_name : 'No Asignado';

        $voucher_time = OperationDocument::where('operation_id', $operation->id)->where('type','Comprobante')->limit(1)->first()->created_at;

        //$start  = new Carbon($operation->funds_confirmation_date);
        $start  = new Carbon($voucher_time);
        $end    = new Carbon($operation->deposit_date);
        $total_time = $start->diffInMinutes($end);


        $message = "*Confirmación Depósito a Cliente*
Cliente: *$client*
Operación: $operation->code
Monto: $operation->type $currency$operation->amount
Ejecutivo: $executive
Analista: $analyst
Tiempo Total: $total_time minutes
Estado: *FACTURADO*";

        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
        $postFields = array(
            'chat_id' => env('TELEGRAM_OPS_CHANNEL'),
            'text' => $message,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => false,
        );
        $rpta = curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        if(!curl_exec($ch)){
            logger('Error notificación Telegram: client_deposit_confirmation@TelegramNotificationsControllers', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        // Enviando a equipo comercial
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
        $postFields = array(
            'chat_id' => env('TELEGRAM_VENTAS_CHANNEL'),
            'text' => $message,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => false,
        );
        $rpta = curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        if(!curl_exec($ch)){
            logger('Error notificación Telegram: client_deposit_confirmation@TelegramNotificationsControllers', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        return response()->json([
            'success' => true,
            'data' => $rpta,
        ]);
    }

}
