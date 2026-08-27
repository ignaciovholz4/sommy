<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Exige que el comprador (guard cliente) ya haya verificado su correo antes
 * de completar la compra. Se usa siempre junto con auth.cliente.
 */
class EnsureClienteVerified
{
    public function handle(Request $request, Closure $next)
    {
        $cliente = Auth::guard('cliente')->user();

        if ($cliente && !$cliente->hasVerifiedEmail()) {
            // HTTP 200 a propósito: ver nota en AuthCliente sobre por qué.
            if ($request->is('Ecommercesaveorder') || $request->expectsJson()) {
                return response()->json(['status' => 0, 'message' => 'Confirmá tu correo antes de finalizar la compra.']);
            }
            return redirect()->route('cliente.verification.notice');
        }

        return $next($request);
    }
}
