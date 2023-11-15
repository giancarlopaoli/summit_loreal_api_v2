<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Operation;
use App\Models\OperationDocument;
use App\Enums;

class VendorInstructions extends Mailable
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
        $sent_amount = ($this->operation->type == 'Compra') ? (round($this->operation->amount * $this->operation->exchange_rate,2) + $this->operation->comission_amount + $this->operation->igv) : (($this->operation->type == 'Venta') ? $this->operation->amount : (round(round($this->operation->matches[0]->amount + round($this->operation->matches[0]->amount * $this->operation->matches[0]->spread/10000, 2 ), 2) + $this->operation->comission_amount + $this->operation->igv,2)));

        $received_amount = ($this->operation->type == 'Venta') ? round($this->operation->amount * $this->operation->exchange_rate,2) - $this->operation->comission_amount - $this->operation->igv : $this->operation->amount;

        if($this->operation->use_escrow_account == 1){
            $document = OperationDocument::where('operation_id', $this->operation->id)->where('type', Enums\DocumentType::Comprobante)->first();
        }
        else{
            $document = OperationDocument::where('operation_id', $this->operation->matched_operation[0]->id)->where('type', Enums\DocumentType::Comprobante)->first();
        }

        return $this
            ->subject('BILLEX | CONSTANCIA DE LA TRANSFERENCIA - ' . $this->operation->type . ' ' . $this->operation->amount . ' - OP ' . $this->operation->code)
            //->to($operation->user->email)
            ->bcc(env('MAIL_OPS'))
            ->bcc(env('MAIL_TI'))
            ->view('vendor_instructions')
            ->attach(env('APP_URL') . "/api/res/download-document-operation?operation_id=".$document->operation_id."&document_id=".$document->id, [
                'as' => $document->document_name
            ])
            ->with([
                'name' => $this->operation->client->name,
                'codigo' => $this->operation->code,
                'type' => $this->operation->type,
                "sent_currency" => ($this->operation->type == 'Compra') ? 'S/': (($this->operation->type == 'Venta') ? '$' : $this->operation->currency->sign),
                "sent_amount" => number_format($sent_amount, 2),
                "received_currency" => ($this->operation->type == 'Venta') ? 'S/': (($this->operation->type == 'Compra') ? '$' : $this->operation->currency->sign),
                "received_amount" => number_format($received_amount, 2),
                'exchange_rate' => $this->operation->exchange_rate,
                "bank_accounts" => $this->operation->bank_accounts,
                "escrow_accounts" => $this->operation->escrow_accounts
            ]);
    }
}
