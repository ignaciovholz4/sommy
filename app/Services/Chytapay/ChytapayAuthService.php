<?php

namespace App\Services\Chytapay;

use App\Models\ChytapayConexion;
use App\Models\Cuenta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Flujo OAuth2 (Authorization Code) de Chytapay: conecta una Cuenta interna
 * a una cuenta de comercio de Chytapay. El intercambio de code/refresh se
 * hace contra auth-api; los cobros en si se consultan en ChytapayPaymentService
 * contra integration-api.
 */
class ChytapayAuthService
{
    public function habilitado(): bool
    {
        return (bool) config('services.chytapay.enabled')
            && !empty(config('services.chytapay.client_id'))
            && !empty(config('services.chytapay.client_secret'))
            && !empty(config('services.chytapay.redirect_uri'));
    }

    /**
     * Genera la URL de /integration/oauth2/authorize y guarda un state
     * anti-CSRF en sesion, atado a la Cuenta que se esta conectando.
     */
    public function buildAuthorizeUrl(Cuenta $cuenta): string
    {
        $state = Str::random(40);
        session(['chytapay_oauth_state' => $state, 'chytapay_oauth_cuenta_id' => $cuenta->id]);

        $query = http_build_query([
            'clientId' => config('services.chytapay.client_id'),
            'redirectUri' => config('services.chytapay.redirect_uri'),
            'responseType' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
        ]);

        return config('services.chytapay.auth_base_url') . '/integration/oauth2/authorize?' . $query;
    }

    /**
     * Verifica el state devuelto por Chytapay contra el guardado en sesion.
     * Devuelve la Cuenta que inicio el flujo, o null si no coincide.
     */
    public function resolverCuentaDesdeState(?string $state): ?Cuenta
    {
        if (!$state || $state !== session('chytapay_oauth_state')) {
            return null;
        }

        $cuentaId = session('chytapay_oauth_cuenta_id');
        session()->forget(['chytapay_oauth_state', 'chytapay_oauth_cuenta_id']);

        return $cuentaId ? Cuenta::find($cuentaId) : null;
    }

    /**
     * Intercambia el code por tokens y crea/actualiza la ChytapayConexion de la Cuenta.
     */
    public function exchangeCode(Cuenta $cuenta, string $code, ?int $userId = null): ChytapayConexion
    {
        $response = Http::asJson()->post(config('services.chytapay.auth_base_url') . '/integration/oauth2/token', [
            'clientId' => config('services.chytapay.client_id'),
            'clientSecret' => config('services.chytapay.client_secret'),
            'code' => $code,
            'grantType' => 'authorization_code',
            'redirectUri' => config('services.chytapay.redirect_uri'),
        ])->throw();

        $datos = $response->json();

        $comercio = $this->clientInfo($datos['idToken']);

        return ChytapayConexion::updateOrCreate(
            ['cuenta_id' => $cuenta->id],
            [
                'id_token' => $datos['idToken'],
                'refresh_token' => $datos['refreshToken'],
                'token_expires_at' => now()->addSeconds((int) ($datos['expiresIn'] ?? 3600)),
                'comercio_nombre' => $comercio['name'] ?? null,
                'comercio_email' => $comercio['email'] ?? null,
                'conectado_por' => $userId,
                'conectado_at' => now(),
            ]
        );
    }

    /**
     * Refresca el token si esta vencido (o a punto de vencer). Devuelve la
     * conexion ya actualizada, lista para usar id_token en el header Bearer.
     */
    public function refreshIfNeeded(ChytapayConexion $conexion): ChytapayConexion
    {
        if ($conexion->token_expires_at && now()->addMinutes(2)->lessThan($conexion->token_expires_at)) {
            return $conexion;
        }

        $response = Http::asJson()->post(config('services.chytapay.auth_base_url') . '/integration/oauth2/refresh', [
            'clientId' => config('services.chytapay.client_id'),
            'clientSecret' => config('services.chytapay.client_secret'),
            'refreshToken' => $conexion->refresh_token,
            'grantType' => 'refresh_token',
        ])->throw();

        $datos = $response->json();

        $conexion->update([
            'id_token' => $datos['idToken'],
            'refresh_token' => $datos['refreshToken'] ?? $conexion->refresh_token,
            'token_expires_at' => now()->addSeconds((int) ($datos['expiresIn'] ?? 3600)),
        ]);

        return $conexion;
    }

    private function clientInfo(string $idToken): array
    {
        try {
            $response = Http::withToken($idToken)
                ->get(config('services.chytapay.auth_base_url') . '/integration/oauth2/client-info');

            return $response->successful() ? $response->json() : [];
        } catch (\Throwable $th) {
            return [];
        }
    }
}
