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
        return $this
            ->subject('BILLEX | INSTRUCCIÓN DE TRANSFERENCIA')
            ->to(explode(",",Configuration::where('shortname', 'MAILSCORFID')->first()->value))
            ->to(env('MAIL_OPS'))
            ->cc(env('MAIL_TI'))
            ->view('operation_sign')
            ->with([
            ]);
    }
}
