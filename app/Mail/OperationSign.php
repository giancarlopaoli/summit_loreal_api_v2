<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Configuration;
use App\Models\Operation;
use App\Models\OperationDocument;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Admin\AdminController;

class OperationSign extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Operation $operation, $sign, $bank_account_id=null)
    {
        //
        $this->operation = $operation;
        $this->sign = $sign;
        $this->bank_account_id = $bank_account_id;
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

        $phase = ($this->sign == 1) ? "Primera Firma - " . $counterpart_name : 'Segunda Firma - '. $client_name;

        $sent_amount = ($this->operation->type == 'Compra') ? (round($this->operation->amount * $this->operation->exchange_rate,2) + $this->operation->comission_amount + $this->operation->igv) : (($this->operation->type == 'Venta') ? $this->operation->amount : (round(round($this->operation->matches[0]->amount/$this->operation->exchange_rate*($this->operation->exchange_rate+$this->operation->matches[0]->spread/10000), 2) + $this->operation->comission_amount + $this->operation->igv,2)));

        $received_amount = ($this->operation->type == 'Venta') ? round($this->operation->amount * $this->operation->exchange_rate,2) - $this->operation->comission_amount - $this->operation->igv : $this->operation->amount;

        $counterpart_sent_amount = ($this->operation->matches[0]->type == 'Compra') ? (round($this->operation->matches[0]->amount * $this->operation->matches[0]->exchange_rate,2) + $this->operation->matches[0]->comission_amount + $this->operation->matches[0]->igv) : (($this->operation->matches[0]->type == 'Venta') ? $this->operation->matches[0]->amount : (round($this->operation->matches[0]->amount + $this->operation->matches[0]->comission_amount + $this->operation->matches[0]->igv,2)));

        $counterpart_received_amount = ($this->operation->matches[0]->type == 'Venta') ? round($this->operation->matches[0]->amount * $this->operation->matches[0]->exchange_rate,2) - $this->operation->matches[0]->comission_amount - $this->operation->matches[0]->igv : (($this->operation->type == 'Compra') ? $this->operation->matches[0]->amount :  round(round($this->operation->matches[0]->amount/$this->operation->exchange_rate*($this->operation->exchange_rate+$this->operation->matches[0]->spread/10000), 2), 2));

        $operation_id = ($this->sign == 1) ? $this->operation->matches[0]->id : $this->operation->id;
        
        $client_to_sign = ($this->sign == 1) ? "Cuenta Destino Contraparte" : 'Cuenta Destino Cliente';

        if($this->sign == 1){
            $account_to_sign = $this->operation->matches[0]->bank_accounts;
        }
        else{
            $account_to_sign = [];

            foreach ($this->operation->bank_accounts as $account) {
                if($account->pivot->bank_account_id == $this->bank_account_id){
                    $account_to_sign[] = $account;
                }
            }
            /*$account_to_sign = DB::table('bank_account_operation')
                ->where('bank_account_id', $this->bank_account_id)
                ->where('operation_id', $this->operation->id)
                ->get();*/
        }

        $consult = new AdminController();
        $instruction = $consult->instruction($this->operation);

        $sent_currency = ($this->operation->type == 'Compra') ? 'S/': (($this->operation->type == 'Venta') ? '$' : $this->operation->currency->sign);

        $received_currency = ($this->operation->type == 'Venta') ? 'S/': (($this->operation->type == 'Compra') ? '$' : $this->operation->currency->sign);

        $email = $this
            ->subject('BILLEX | INSTRUCCIÓN DE TRANSFERENCIA')
            ->to(explode(",",Configuration::where('shortname', 'MAILSCORFID')->first()->value))
            ->cc(env('MAIL_OPS'))
            ->view('operation_sign')

            ->attachData($instruction, 'Instrucciones.pdf', [
                'mime' => 'application/pdf',
            ])
            ->with([
                "phase" => $phase,
                "type" => $this->operation->type,
                "date" => date('d/m/y', strtotime($this->operation->operation_date)),

                "client_name" => $client_name,
                "sent_currency" => $sent_currency,
                "sent_amount" => number_format($sent_amount, 2),
                "received_currency" => $received_currency,
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
                "show_image_counterpart" => ($this->sign == 2) ? 'none' : 'inline',
                "client_to_sign" => $client_to_sign,
                "account_to_sign" => $account_to_sign,
                "sign_currency" => ($this->sign == 1) ? $sent_currency : $received_currency
            ]);

            if($this->sign == 1){
                foreach ($this->operation->documents as $document) {
                    if($document->type == 'Comprobante' || $document->type == '1ra firma'){
                        $document = OperationDocument::where('id',$document->id)->where('operation_id', $document->operation_id)->first();
                        $email->attachFromStorageDisk('s3',env('AWS_ENV').'/operations/' . $document->document_name);
                    }
                }
            }
            else{
                /*foreach ($this->operation->matches[0]->documents as $document) {
                    if($document->type == '2da firma'){
                        $document = OperationDocument::where('id',$document->id)->where('operation_id', $document->operation_id)->first();
                        $email->attachFromStorageDisk('s3',env('AWS_ENV').'/operations/' . $document->document_name);
                    }
                }*/
                foreach ($this->operation->documents as $document) {
                    if($document->type == '2da firma' && $document->id == $account_to_sign[0]['pivot']['voucher_id']){
                        $document = OperationDocument::where('id',$document->id)->where('operation_id', $document->operation_id)->first();
                        $email->attachFromStorageDisk('s3',env('AWS_ENV').'/operations/' . $document->document_name);
                    }
                }

                foreach ($this->operation->matches[0]->documents as $document) {
                    if($document->type == 'Comprobante'){
                        $document = OperationDocument::where('id',$document->id)->where('operation_id', $document->operation_id)->first();
                        $email->attachFromStorageDisk('s3',env('AWS_ENV').'/operations/' . $document->document_name);
                    }
                }
            }

        return $email;
    }
}
