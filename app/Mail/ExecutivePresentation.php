<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;
use App\Models\User;

class ExecutivePresentation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_id, $client_id)
    {
        //
        $this->user_id = $user_id;
        $this->client_id = $client_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        $client = Client::find($this->client_id);
        $user = User::find($this->user_id);

        return $this
            ->subject('Billex | Estamos para ayudarte')
            ->to($user->email)
            ->cc($client->executive->user->email)
            ->bcc(env('MAIL_OPS'))
            ->bcc(env('MAIL_TI'))
            ->view('executive')
            ->with([
                'name' => $user->name,
                'executive_name' => $client->executive->user->full_name,
                'executive_phone' => $client->executive->user->phone,
                'executive_email' => $client->executive->user->email,
            ]);
    }
}
