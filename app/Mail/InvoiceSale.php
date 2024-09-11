<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Sale;

class InvoiceSale extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Sale $sale)
    {
        //
        $this->sale = $sale;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emails = (is_null($this->sale->client->accountable_email) || $this->sale->client->accountable_email == "") ? env('MAIL_OPS') : array_merge(explode(",", $this->sale->client->accountable_email), array(env('MAIL_OPS')));

        $mail_executive = (isset($this->sale->client->executive->user->email)) ? $this->sale->client->executive->user->email : env('MAIL_CRM');

        $email = $this
            ->subject('BILLEX | FACTURA ELECTRÓNICA ' . $this->sale->invoice_serie . '-' . $this->sale->invoice_number)
            ->to($this->sale->client->email)
            ->cc($emails)
            ->cc($mail_executive)
            ->cc(env('MAIL_OPS'))
            ->bcc(env('MAIL_TI'))
            ->view('invoice_sale')
            ->with([
                "client_name" => $this->sale->client->client_full_name,
                "invoice_serie" => $this->sale->invoice_serie,
                "invoice_number" => $this->sale->invoice_number,
                "invoice_url" => $this->sale->invoice_url
            ]);

        return $email;
    }
}
