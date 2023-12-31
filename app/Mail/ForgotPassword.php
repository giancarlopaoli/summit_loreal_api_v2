<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_id, $new_password)
    {
        //
        $this->user_id = $user_id;
        $this->new_password = $new_password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = User::find($this->user_id);

        return $this
            ->subject('BILLEX | Recuperación de Contraseña')
            ->to($user->email)
            ->bcc(env('MAIL_TI'))
            ->view('forgot_password')
            ->with([
                'names' => $user->name,
                'new_password' => $this->new_password
            ]);
    }
}
