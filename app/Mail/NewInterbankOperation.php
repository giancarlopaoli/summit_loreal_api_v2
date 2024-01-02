<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Operation;

class NewInterbankOperation extends Mailable
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
        $mail_executive = (isset($this->operation->client->executive->user->email)) ? $this->operation->client->executive->user->email : env('MAIL_CRM');

        return $this
            ->subject('BILLEX | Nueva Operación Interbancaria')
            ->to($this->operation->user->email)
            ->cc($mail_executive)
            ->cc(env('MAIL_OPS'))
            ->bcc(env('MAIL_TI'))
            ->view('new_interbank_operation')
            ->with([
                'name' => $this->operation->user->name,
                'type' => $this->operation->type,
                'currency_sign' => $this->operation->currency->sign,
                'amount' => number_format($this->operation->amount,2),
            ]);
    }
}
