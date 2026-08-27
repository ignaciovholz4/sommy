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
            // El checkout llama a /Ecommercesaveorder por fetch() sin Accept
            // header: si no está logueado (sesión vencida a mitad de compra)
            // le devolvemos JSON en vez de un redirect que rompe el fetch.
            // HTTP 200 a propósito: el checkout evalúa resp.status en el body,
            // no el código HTTP, y con un código de error fetch() ni siquiera
            // llega a parsear la respuesta (revienta antes por !response.ok).
            if ($request->is('Ecommercesaveorder') || $request->expectsJson()) {
                return response()->json(['status' => 0, 'message' => 'Necesitás iniciar sesión para completar la compra.']);
            }
            return redirect('/cuenta/login?next=' . urlencode($request->fullUrl()));
        }

        return $next($request);
    }
}
