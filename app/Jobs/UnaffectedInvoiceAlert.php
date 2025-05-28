<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Operation;

class UnaffectedInvoiceAlert implements ShouldQueue
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
        $operations = Operation::where('operation_date', '>=', '2025-05-01')
            ->join('clients as cl','cl.id','=','operations.client_id')
            ->whereIn('operation_status_id', [6,7])
            ->where('cl.type', 'Cliente')
            ->where('unaffected_invoice_url', null)
            ->get();

        //echo($operations);

        foreach ($operations as $op) {
            $cliente =  $op->client->client_full_name;
            $monto = number_format($op->amount,2);
            $moneda = $op->currency->sign;

            $message = "*Factura Inafecta no creada*
Cliente: *$cliente*
Operación: $op->code
Monto: $op->type $moneda$monto";
        
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
            curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot1636692227:AAGHJX8dyPEKWGkvuti4Xfk84A0FlY1fIpk/sendMessage');
            $postFields = array(
                'chat_id' => env('TELEGRAM_TI_CHANNEL'),
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
