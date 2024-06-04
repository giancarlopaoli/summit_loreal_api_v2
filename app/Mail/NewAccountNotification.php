<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;

class NewAccountNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        //
        $this->client = $client;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail_executive = (isset($this->client->executive->user->email)) ? $this->client->executive->user->email : env('MAIL_TI');

        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->subject('BILLEX | Registro de nueva cuenta bancaria: '.$this->client->client_full_name)
            ->cc(env('MAIL_OPS'))
            ->cc(env('MAIL_TI'))
            ->view('new_account_notification')
            ->with([
                'client_name' => $this->client->client_full_name
            ]);
    }
}
