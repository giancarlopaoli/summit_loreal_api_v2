<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BankAccountOpError implements ShouldQueue
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
        // Bank Account Operations que no tienen el escrow account operation id
        $bank_account_operations = DB::table('bank_account_operation as bao')
            ->join('operations as op', 'bao.operation_id', '=', 'op.id')
            ->select('op.id','op.code','op.type','op.amount','bao.id as bao_id')
            //->where('op.operation_date','>=', Carbon::now()->format('Y-m-d'))
            ->where('bao.escrow_account_operation_id', null)
            ->whereIn('op.operation_status_id', [2,3,4,5])
            ->get();


        foreach ($bank_account_operations as $op) {
            $monto = number_format($op->amount,2);

            $message = "*Error en Cuenta Bancaria*
Operación: $op->code
Monto: $op->type $ $monto
Bank Operation Id: *$op->bao_id*
No se encontró la cuenta de fideicomiso de fondeo de la operación";
        
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
