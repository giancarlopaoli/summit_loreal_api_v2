<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function envejecimiento(Request $request) {

        $fields = array(
            'version' => "9222a21c181b707209ef12b5e0d7e94c994b58f01c7b2fec075d2e892362f13c",
            'input' => array(
                'image' => 'https://f.rpp-noticias.io/2019/02/15/753296descarga-7jpg.jpg',
                'target_age' => 80
            ),
            
        );

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.replicate.com/v1/predictions");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Token 370c8964ec9d1501357983b4f000973e236ef8cf'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $rpta = curl_exec($ch);
        curl_close($ch);


        return response()->json([
            'success' => true,
            'data' => [
                'hola mundo' => json_decode($rpta)
            ]
        ]);
    }
}
