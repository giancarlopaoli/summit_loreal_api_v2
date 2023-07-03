<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Configuration;
use App\Models\Operation;

class OperationSign extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Operation $operation, $sign)
    {
        //
        $this->operation = $operation;
        $this->sign = $sign;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        $client_name = ($this->operation->client->customer_type == 'PJ') ? $this->operation->client->name : $this->operation->client->name . " " . $this->operation->client->last_name . " " . $this->operation->client->mothers_name;

        $counterpart_name = ($this->operation->matches[0]->client->customer_type == 'PJ') ? $this->operation->matches[0]->client->name : $this->operation->matches[0]->client->name . " " . $this->operation->matches[0]->client->last_name . " " . $this->operation->matches[0]->client->mothers_name;

        $phase = ($this->sign == 1) ? '<b>Primera Firma </b>- ' . $client_name : '<b>Segunda Firma </b>- '. $counterpart_name;

        $sent_amount = ($this->operation->type == 'Compra') ? (round($this->operation->amount * $this->operation->exchange_rate,2) + $this->operation->comission_amount + $this->operation->igv) : (($this->operation->type == 'Venta') ? $this->operation->amount : (round(round($this->operation->matches[0]->amount + round($this->operation->matches[0]->amount * $this->operation->matches[0]->spread/10000, 2 ), 2) + $this->operation->comission_amount + $this->operation->igv,2)));

        $received_amount = ($this->operation->type == 'Venta') ? round($this->operation->amount * $this->operation->exchange_rate,2) - $this->operation->comission_amount - $this->operation->igv : $this->operation->amount;

        $counterpart_sent_amount = ($this->operation->matches[0]->type == 'Compra') ? (round($this->operation->matches[0]->amount * $this->operation->matches[0]->exchange_rate,2) + $this->operation->matches[0]->comission_amount + $this->operation->matches[0]->igv) : (($this->operation->matches[0]->type == 'Venta') ? $this->operation->matches[0]->amount : (round($this->operation->matches[0]->amount + $this->operation->matches[0]->comission_amount + $this->operation->matches[0]->igv,2)));

        $counterpart_received_amount = ($this->operation->matches[0]->type == 'Venta') ? round($this->operation->matches[0]->amount * $this->operation->matches[0]->exchange_rate,2) - $this->operation->matches[0]->comission_amount - $this->operation->matches[0]->igv : (($this->operation->type == 'Compra') ? $this->operation->matches[0]->amount :  round($this->operation->matches[0]->amount + round($this->operation->matches[0]->amount * $this->operation->matches[0]->spread/10000, 2 ), 2));

        return $this
            ->subject('BILLEX | INSTRUCCIÓN DE TRANSFERENCIA')
            ->to(explode(",",Configuration::where('shortname', 'MAILSCORFID')->first()->value))
            ->to(env('MAIL_OPS'))
            ->cc(env('MAIL_TI'))
            ->view('operation_sign')
            ->with([
                "phase" => $phase,
                "type" => $this->operation->type,
                "date" => date('d/m/y', strtotime($this->operation->operation_date)),

                "client_name" => $client_name,
                "sent_currency" => ($this->operation->type == 'Compra') ? 'S/': (($this->operation->type == 'Venta') ? '$' : $this->operation->currency->sign),
                "sent_amount" => number_format($sent_amount, 2),
                "received_currency" => ($this->operation->type == 'Venta') ? 'S/': (($this->operation->type == 'Compra') ? '$' : $this->operation->currency->sign),
                "received_amount" => number_format($received_amount, 2),
                "client_account" => $this->operation->bank_accounts,
                "client_escrow_accounts" => $this->operation->escrow_accounts,
                "show_image_client" => ($this->sign == 1) ? 'none' : 'inline',

                "counterpart_name" => $counterpart_name,
                "counterpart_sent_amount" => number_format($counterpart_sent_amount,2),
                "counterpart_received_amount" => number_format($counterpart_received_amount,2),
                "exchange_rate" => round($this->operation->exchange_rate,4),
                "comission_currency" => ($this->operation->type == 'Interbancaria') ? $this->operation->currency->sign : 'S/',
                "comission_amount" => number_format($this->operation->comission_amount + $this->operation->igv, 2),
                "counterpart_comission_amount" => number_format($this->operation->matches[0]->comission_amount + $this->operation->matches[0]->igv, 2),
                "client_spread_comission" => $this->operation->comission_spread,
                "counterpart_spread_comission" => $this->operation->matches[0]->comission_spread,
                "counterpart_bank_account" => $this->operation->matches[0]->bank_accounts,
                "counterpart_escrow_accounts" => $this->operation->matches[0]->escrow_accounts,
                "show_image_counterpart" => ($this->sign == 2) ? 'none' : 'inline'
            ]);
    }
}
