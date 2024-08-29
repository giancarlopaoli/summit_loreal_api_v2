<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Operation;
use App\Models\OperationDocument;
use Illuminate\Support\Facades\Storage;
use App\Enums;

class SelfDetraction extends Mailable
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
        $emails = (is_null($this->operation->client->accountable_email) || $this->operation->client->accountable_email == "") ? env('MAIL_OPS') : array_merge(explode(",", $this->operation->client->accountable_email), array(env('MAIL_OPS')));

        $mail_executive = (isset($this->operation->client->executive->user->email)) ? $this->operation->client->executive->user->email : env('MAIL_CRM');

        $document = OperationDocument::where('type','Detraccion')->where('operation_id', $this->operation->id)->first();

        $email = $this
            ->subject('BILLEX | COMPROBANTE DE AUTODETRACCIÓN')
            ->to($this->operation->client->email)
            ->cc($this->operation->user->email)
            ->cc($emails)
            ->cc($mail_executive)
            ->cc(env('MAIL_OPS'))
            ->bcc(env('MAIL_TI'))
            ->view('selfdetraction')
            ->attachFromStorageDisk('s3',env('AWS_ENV').'/operations/' . $document->document_name)
            ->with([
                "code" => $this->operation->code,
                "client_name" => $this->operation->client->client_full_name,
                "nro_factura" => $this->operation->invoice_serie ."-".$this->operation->invoice_number,
            ]);

        return $email;
    }
}
