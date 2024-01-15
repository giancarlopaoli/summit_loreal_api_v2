<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nullix\CryptoJsAes\CryptoJsAes;

class EncryptResponses
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
        $response = $next($request);

        if(!env('APP_DEBUG', true) ){
            try {
                return response(CryptoJsAes::encrypt($response, env('APP_PSW')));
            } catch (\Exception $e) {
                return $response;
            }
        } else {

            //logger('ROUTE LOG', ["data" => request()->fullUrl(), "ip" => request()->ip()]);

            return $response; //descomentar para pruebas de desarrollo
        }
    }
}
