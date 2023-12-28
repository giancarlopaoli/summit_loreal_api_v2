<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use Illuminate\Http\Request;

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
            logger('Error notificación Telegram: notificacionRecepcionFondos@OperationsController', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        return response()->json([
            'success' => true,
            'data' => $rpta,
        ]);
    }

    // Notificación Confirmación envío de fondos
    public function confirm_funds_notification(Request $request) {
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
            logger('Error notificación Telegram: notificacionRecepcionFondos@OperationsController', ["error" => $ch]);
            echo curl_error($ch);
        }
        curl_close($ch);

        return response()->json([
            'success' => true,
            'data' => $rpta,
        ]);
    }
}
