<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Operation;
use App\Models\OperationStatus;

class PendingDetractions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $operations = Operation::where('operation_date', '>=', '2024-01-01')
            ->join('clients as cl','cl.id','=','operations.client_id')
            ->where('operation_status_id', OperationStatus::where('name', 'Facturado')->first()->id)
            ->whereRaw("( (detraction_amount > 0) or (operations.type = 'Interbancaria' and currency_id = 2 and ( (comission_amount + igv) * exchange_rate > 690)))")
            ->where('detraction_paid', false)
            ->where('cl.customer_type', 'PJ')
            ->get();

        foreach ($operations as $op) {
            $cliente =  $op->client->client_full_name;
            $monto = number_format($op->amount,2);
            $facturado = number_format($op->comission_amount + $op->igv,2);
            $detraccion = number_format($op->detraction_amount,2);
            $moneda = $op->currency->sign;

            $message = "*Operaciones pendientes de Detracción*
Cliente: *$cliente*
Operación: $op->code
Monto: $op->type $moneda$monto
Monto Facturado: $facturado
Detracción: $detraccion";
        
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
            curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
            $postFields = array(
                'chat_id' => env('TELEGRAM_ADMIN_CHANNEL'),
                'text' => $message,
                'parse_mode' => 'markdown',
                'disable_web_page_preview' => false,
            );
            $rpta = curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);


            if(!curl_exec($ch))
                echo curl_error($ch);
            curl_close($ch);
        }
    }
}
