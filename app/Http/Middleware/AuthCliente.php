<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Exige sesión de comprador (guard cliente) para acceder, p. ej., al checkout.
 */
class AuthCliente
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('cliente')->check()) {
            return redirect('/cuenta/login?next=' . urlencode($request->fullUrl()));
        }

        return $next($request);
    }
}
