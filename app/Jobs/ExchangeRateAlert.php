<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ExchangeRate;
use Carbon\Carbon;

class ExchangeRateAlert implements ShouldQueue
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
        $datatec = ExchangeRate::selectRaw(' (TIMESTAMPDIFF(MINUTE, created_at, now())) as tiempo_total')
            ->latest()
            ->first();
        
        $tiempo = $datatec->tiempo_total;

        if($tiempo > 15) {

            // Se envía notificación a Telegram
            $message = "*Alerta Tipo Cambio DATATEC*
El tipo de cambio no se ha actualizado desde hace *$tiempo * minutos";
        
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
