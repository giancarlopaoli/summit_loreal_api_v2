<?php

namespace App\Http\Middleware;

use App\Enums\ClientUserStatus;
use Closure;
use Illuminate\Http\Request;
use App\Models\Client;
use Carbon\Carbon;

class ValidateClientUser
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
        $user = auth()->user();
        $user->last_active = Carbon::now();
        $user->save();
                
        if($request->has('client_id') && auth()->check()) {
            $user = auth()->user();

            $clients = $user->clients()
                ->wherePivot('status', '!=', ClientUserStatus::Inactivo)
                ->get();

            if($clients->contains($request->client_id)) {
                return $next($request);
            } else {
                abort(response()->json([
                    'success' => false,
                    'errors' => [
                        'El cliente no pertenece al usuario'
                    ]
                ], 403));
            }
        } else {
            return $next($request);
        }
    }
}
