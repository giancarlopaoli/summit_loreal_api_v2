<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\Operation;
use App\Models\OperationStatus;
use Illuminate\Database\Eloquent\Builder;

class WsCorfidOperations implements ShouldQueue
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
        /*$operaciones = Operacion::whereIn('EstadoId', ['FAC','FSF','COR','PFC'])
            ->whereNotIn('ClienteId', [366,1884,2815,3166,4280,4540])
            ->where('FechaOperacion','>=', Carbon::now()->format('Y-m-d'))
            ->where(function ($query) {
               $query->where('EstadoCorfidId', null)
                      ->orwhere('EstadoCorfidId', '!=' , 1);
                })
            ->with('cliente')
            ->get();*/

        $operations = Operation::where('operation_date', '>=', '2024-01-01')
            ->whereIn('operation_status_id', OperationStatus::whereIn('name', ['Contravalor recaudado','Facturado', 'Finalizado sin factura'])->get()->pluck('id'))
            ->where('detraction_paid', false)
            ->whereHas('client', function (Builder $query) {
                    $query->where('type', 'Cliente');
                })
            ->where(function ($query) {
                    $query->where('corfid_id', null)
                        ->orwhere('corfid_id', '!=' , 1);
                })
            ->get();

        foreach ($operations as $op) {
            $cliente =  $op->client->client_full_name;
            $monto = number_format($op->amount,2);
            $moneda = $op->currency->sign;

            $message = "*Error en envío a WS Corfid*
Cliente: *$cliente*
Operación: $op->code
Monto: $op->type $moneda$monto
Error ID: $op->corfid_id
Msg: $op->corfid_message";
        
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
