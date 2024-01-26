<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Operation;
use App\Models\OperationDocument;
use App\Enums;
use App\Http\Controllers\Admin\Operations\DailyOperationsController;
use Illuminate\Support\Facades\Storage;

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
        $sent_amount = ($this->operation->type == 'Compra') ? (round($this->operation->amount * $this->operation->exchange_rate,2) + $this->operation->comission_amount + $this->operation->igv) : $this->operation->amount;

        $received_amount = ($this->operation->type == 'Venta') ? round($this->operation->amount * $this->operation->exchange_rate,2) - $this->operation->comission_amount - $this->operation->igv : (($this->operation->type == 'Venta') ? $this->operation->amount : round($this->operation->amount *(1 + $this->operation->spread/10000), 2 ));

        if($this->operation->use_escrow_account == 1){
            $documents = OperationDocument::where('operation_id', $this->operation->id)->where('type', Enums\DocumentType::Comprobante)->get();
        }
        else{
            $documents = OperationDocument::where('operation_id', $this->operation->matched_operation[0]->id)->where('type', Enums\DocumentType::Comprobante)->get();
        }

        if($this->operation->use_escrow_account){
            $deposit_account = $this->operation->escrow_accounts;
        }
        else{
            $deposit_account = $this->operation->vendor_bank_accounts->load('client:id,name,last_name,mothers_name,customer_type');
        }

        $emails = (is_null($this->operation->client->accountable_email) || $this->operation->client->accountable_email == "") ? env('MAIL_OPS') : array_merge(explode(",", $this->operation->client->accountable_email), array(env('MAIL_OPS')));

        $email = $this
            ->subject('BILLEX | CONSTANCIA DE LA TRANSFERENCIA - ' . $this->operation->type . ' ' . $this->operation->amount . ' - OP ' . $this->operation->code)
            ->to($this->operation->client->email)
            ->cc($this->operation->user->email)
            ->cc($emails)
            ->cc(env('MAIL_OPS'))
            //->bcc(env('MAIL_TI'))
            ->view('vendor_instructions')
            
            ->with([
                'name' => $this->operation->client->name,
                'codigo' => $this->operation->code,
                'type' => $this->operation->type,
                'use_escrow_account' => $this->operation->use_escrow_account,
                "sent_currency" => ($this->operation->type == 'Compra') ? 'S/': (($this->operation->type == 'Venta') ? '$' : $this->operation->currency->sign),
                "sent_amount" => number_format($sent_amount, 2),
                "received_currency" => ($this->operation->type == 'Venta') ? 'S/': (($this->operation->type == 'Compra') ? '$' : $this->operation->currency->sign),
                "received_amount" => number_format($received_amount, 2),
                'exchange_rate' => number_format($this->operation->exchange_rate,4),
                "bank_accounts" => $this->operation->bank_accounts,
                "escrow_accounts" => $deposit_account
            ]);

            //$consult = new DailyOperationsController();

            foreach ($documents as $document) {
                if($document->type == 'Comprobante'){
                    $email->attach(env('APP_URL') . "/api/res/download-document-operation?operation_id=".$document->operation_id."&document_id=".$document->id, [
                        'as' => $document->document_name
                    ]);

                    /*//$file = $consult->internal_download_file($document->operation_id, $document->id);


                    $document = OperationDocument::where('id',$document->id)->where('operation_id', $document->operation_id)->first();
                    
                    //$file = Storage::disk('s3')->download(env('AWS_ENV').'/operations/' . $document->document_name);

                    $email->attachFromStorageDisk('s3', env('AWS_ENV').'/operations/' . $document->document_name);*/
                }
            }


        return $email;
    }
}
