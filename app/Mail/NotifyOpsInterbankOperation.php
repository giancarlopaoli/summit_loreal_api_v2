<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Operation;

class NotifyOpsInterbankOperation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Operation $operation)
    {
        //
        $this->operation = $operation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        $monto_total = $this->operation->amount + round($this->operation->amount * $this->operation->spread/10000,2) + $this->operation->comission_amount + $this->operation->igv;
        $spread = round(round(round($this->operation->amount * (1+$this->operation->spread/10000),2)/$this->operation->amount*$this->operation->exchange_rate ,4)- $this->operation->exchange_rate,6)*10000;

        return $this
            ->subject('BILLEX | Nueva Operación Interbancaria')
            ->to(env('MAIL_OPS'))
            ->to(env('MAIL_CRM'))
            ->cc(env('MAIL_TI'))
            ->view('notify_new_interbank_operation')
            ->with([
                'client' => ($this->operation->client->customer_type == 'PJ') ? $this->operation->client->name : $this->operation->client->name ." " . $this->operation->client->last_name . " " . $this->operation->client->mothers_name,
                'currency_sign' => $this->operation->currency->sign,
                'amount' => number_format($this->operation->amount,2),
                'total_amount' => ($this->operation->currency->sign . number_format($monto_total,2)), //$this->monto_op,
                'tc_compra' => round($this->operation->exchange_rate,2),
                'tc_venta' => round($this->operation->exchange_rate + $spread/10000,4),
                'spread_caja' => $spread,
                'comision_caja' => $this->operation->currency->sign . round($this->operation->amount * $this->operation->spread/10000,2),
                'comision' => ($this->operation->currency->sign . number_format($this->operation->comission_amount + $this->operation->igv,2)),
                'banco_origen' => $this->operation->escrow_accounts[0]->bank->shortname,
                'banco_destino' => $this->operation->bank_accounts[0]->bank->shortname
            ]);
    }
}
