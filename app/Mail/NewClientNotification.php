<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;

class NewClientNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($register_message, $client_id)
    {
        //
        $this->register_message = $register_message;
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
        $contact = ($client->users->count() > 0 ) ? $client->users[0] : null;

        $bussiness_name = ($client->customer_type == 'PN') ? $client->name . " " . $client->last_name : $client->name;

        $executive = $client->executive->user;
        $executive2 = ($client->executives_comissions->count() > 0) ? $client->executives_comissions[0]->executive->user : null;

        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->subject('Nuevo registro de cliente: '.$bussiness_name)
            ->to(env('MAIL_CRM'))
            ->cc(env('MAIL_OPS'))
            ->cc(env('MAIL_TI'))
            ->view('new_client_notification')
            ->with([
                'bussiness_name' => $bussiness_name,
                'contact_name' => (isset($contact)) ? $contact->name . " " . $contact->last_name: null,
                'contact_phone' => (isset($contact)) ? $contact->phone : null,
                'contact_email' => (isset($contact)) ? $contact->email : null,
                'register_message' => $this->register_message,
                'ejecutivo_asignado' => $executive->name . ' ' . $executive->last_name,
                'ejecutivo_referido' => $executive2
                
            ]);
    }
}
