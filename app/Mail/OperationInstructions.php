<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Configuration;
use App\Models\Operation;
use Illuminate\Support\Str;
use App\Http\Controllers\Admin\AdminController;

class OperationInstructions extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($operation_id)
    {
        //
        $this->operation_id = $operation_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $operation = Operation::find($this->operation_id)
            ->load('user','client');

        if($operation->type == 'Compra' || $operation->type == 'Venta'){

            $pen_amount = $operation->type == 'Compra' ? ($operation->amount*$operation->exchange_rate + $operation->comission_amount + $operation->igv) :  ($operation->amount*$operation->exchange_rate - $operation->comission_amount - $operation->igv);
            $final_exchange_rate = $operation->type == 'Compra' ? round($operation->exchange_rate + $operation->comission_spread/10000 ,4) : round($operation->exchange_rate - $operation->comission_spread/10000 ,4);


            $data = [
                'username' => Str::of($operation->user->name)->ucfirst() . " " . Str::of($operation->user->last_name)->ucfirst(),
                'client_name' => $operation->client->customer_type == 'PJ' ? $operation->client->name : $operation->client->name ." " . $operation->client->last_name . " " . $operation->client->mothers_name,
                'code' => $operation->code,
                'operation_date' => date('d F Y', strtotime($operation->operation_date)),
                'operation_time' => date('H:i', strtotime($operation->operation_date)),
                'type' => $operation->type == 'Compra' ? 'comprar': 'vender',
                'currency_sign' => $operation->currency->sign,
                'amount' => number_format($operation->amount,2),
                'exchange_rate' => round($operation->exchange_rate,4),
                'comission_amount' => $operation->comission_amount,
                'igv' => $operation->igv,
                'final_exchange_rate' => $final_exchange_rate,
                'pen_type' => $operation->type == 'Compra' ? 'depositar': 'recibir',
                'pen_amount' => number_format($pen_amount,2),
                'deposit_sign' => $operation->type == 'Compra' ? 'S/' : '$',
                'deposit_amount' => $operation->type == 'Compra' ? number_format($pen_amount,2) : number_format($operation->amount,2),
                'receive_sign' => $operation->type == 'Compra' ? '$' : 'S/',
                'receive_amount' => $operation->type == 'Compra' ? number_format($operation->amount,2) : number_format($pen_amount,2)
            ];

            
        }
        else{

            $financial_expenses = round($operation->amount * $operation->spread/10000,2);
            $spread = round(($operation->spread + 1)*$operation->exchange_rate - $operation->exchange_rate,2);
            $exchange_rate_selling = round($operation->exchange_rate + $spread/10000, 4) ;
            $counter_value = $operation->amount + $financial_expenses;

            $data = [
                'username' => Str::of($operation->user->name)->ucfirst() . " " . Str::of($operation->user->last_name)->ucfirst(),
                'client_name' => $operation->client->customer_type == 'PJ' ? $operation->client->name : $operation->client->name ." " . $operation->client->last_name . " " . $operation->client->mothers_name,
                'code' => $operation->code,
                'operation_date' => date('d F Y', strtotime($operation->operation_date)),
                'operation_time' => date('H:i', strtotime($operation->operation_date)),
                'type' => 'transferir',
                'currency_sign' => $operation->currency->sign,
                'amount' => number_format($operation->amount,2),
                'exchange_rate' => round($operation->exchange_rate,4),
                'exchange_rate_selling' => round($exchange_rate_selling,4),
                'comission_amount' => $operation->comission_amount,
                'igv' => $operation->igv,
                'counter_value' => number_format($counter_value,2),
                'deposit_sign' => $operation->currency->sign,
                'deposit_amount' => $operation->client->type == 'PL' ? number_format($operation->amount,2) : number_format($counter_value + $operation->comission_amount + $operation->igv,2),
                'receive_amount' => $operation->client->type == 'PL' ? number_format($counter_value + $operation->comission_amount + $operation->igv,2) : number_format($operation->amount,2),
                'escrow_accounts' => $operation->escrow_accounts->load('bank','currency'),
                'bank_accounts' => $operation->bank_accounts->load('bank','currency')
            ];

        }

        $mail_executive = (isset($operation->client->executive->user->email)) ? $operation->client->executive->user->email : env('MAIL_OPS');

        $emails = (is_null($operation->client->accountable_email) || $operation->client->accountable_email == "") ? env('MAIL_OPS') : array_merge(explode(",", $operation->client->accountable_email), array(env('MAIL_OPS')));

        // Verificando si es cliente autoplan
        if($operation->client->association_id == 1){
            $mails_autoplan = Configuration::where('shortname', 'MAILAUTOPLAN')->first()->value;

            $mail_executive = array_merge(explode(",", $mail_executive), explode(",", $mails_autoplan));
        }

        $consult = new AdminController();
        $instruction = $consult->instruction($operation);

        return $this
            ->subject('BILLEX | Instrucciones de la Operación')
            ->to($operation->client->email)
            ->to($operation->user->email)
            ->cc($mail_executive)
            ->cc($emails)
            ->cc(env('MAIL_OPS'))
            //->bcc(env('MAIL_TI'))
            ->view('operation_instructions')
            ->attachData($instruction, 'Instrucciones.pdf', [
                'mime' => 'application/pdf',
            ])
            ->with($data);
    }
}
