<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorfidWS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = substr($request->header('Authorization'), 7);

        if($token == env('TOKEN_WSBILLEX')){
            return $next($request);
        }
        
        abort(403);
    }
}
