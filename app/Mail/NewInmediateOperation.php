<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Operation;

class NewInmediateOperation extends Mailable
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

        $mail_executive = (isset($this->operation->client->executive->user->email)) ? $this->operation->client->executive->user->email : env('MAIL_CRM');

        return $this
            ->subject('BILLEX | Nueva Operación de Cambio')
            ->to($operation->user->email)
            ->cc($mail_executive)
            ->bcc(env('MAIL_OPS'))
            ->bcc(env('MAIL_TI'))
            ->view('new_inmediate_operation')
            ->with([
                'name' => $operation->client->customer_type == 'PJ' ? $operation->client->name : $operation->client->name ." " . $operation->client->last_name . " " . $operation->client->mothers_name,
                'type' => $operation->type,
                'amount' => number_format($operation->amount,2),
            ]);
    }
}
