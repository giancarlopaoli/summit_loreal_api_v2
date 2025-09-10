<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailingMasivo;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    //
    public function list (Request $request) {
        $users = User::get();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ]);
    }

    public function mailing (Request $request) {
        //$users = User::get();
        //$users = User::where('id', '<', 301)->get();
        $users = User::where('email', '!=', 'tecnologia@billex.pe')->get();

        //$rpta_mail = Mail::bcc($users->pluck('email'))->send(new MailingMasivo());

        $chunks = array_chunk($users->pluck('email')->toArray(), 15);

        foreach ($chunks as $chunk) {
            
            //$op = DB::connection('mysql')->table('operations')->updateOrCreate(json_decode( json_encode($chunk), true));

            $rpta_mail = Mail::bcc($chunk)->send(new MailingMasivo());
            logger('Envío Mailing: mailing@UsersController', ["mails" => $chunk]);
            sleep(1);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $chunks,
                'rpta_mail' => $rpta_mail
            ]
        ]);
    }
}
