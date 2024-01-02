<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Welcome extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($names, $email, $company)
    {
        //
        $this->names = $names;
        $this->email = $email;
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Bienvenido a Billex')
            ->to($this->email)
            ->bcc(env('MAIL_OPS'))
            ->bcc(env('MAIL_TI'))
            ->view('welcome')
            ->attach('https://bill-upload.s3.amazonaws.com/static/Manual_Billex.pdf')
            ->attach('https://bill-upload.s3.amazonaws.com/static/Contrato+de+Afiliaci%C3%B3n+a+Plataforma+BILLEX.pdf')
            ->with([
                'names' => $this->names,
                'email' => $this->email,
                'company' => $this->company
            ]);
    }
}
