<?php

namespace App\Http\Middleware;

use App\Models\AuditLog as AuditLogModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Registra toda accion que modifica datos hecha por un usuario del staff
 * logueado (guard "web"; el checkout de clientes ecommerce usa otro guard
 * y no se audita aca). No instrumenta controlador por controlador: cualquier
 * POST/PUT/PATCH/DELETE queda registrado con quien, que ruta y cuando.
 * Corre en terminate() para no sumarle latencia a la respuesta.
 */
class AuditLog
{
    private const CAMPOS_REDACTADOS = ['password', 'npassword', 'cpassword', 'password_confirmation', 'token', 'secret', 'client_secret', 'cheque_numero'];

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if ($request->isMethod('get') || $request->isMethod('head') || $request->isMethod('options')) {
            return;
        }

        if (!Auth::guard('web')->check()) {
            return;
        }

        try {
            $payload = $this->redactar($request->except(array_merge(['_token', '_method'], array_keys($request->allFiles()))));
            if (strlen(json_encode($payload)) > 20000) {
                $payload = ['_aviso' => 'payload demasiado grande, no se guardo el detalle'];
            }

            AuditLogModel::create([
                'user_id' => Auth::guard('web')->id(),
                'metodo' => $request->method(),
                'ruta' => optional($request->route())->getName(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'payload' => $payload,
                'status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // La auditoria nunca debe romper el flujo principal
            Log::warning('No se pudo registrar el log de auditoria: ' . $e->getMessage());
        }
    }

    private function redactar(array $payload): array
    {
        foreach ($payload as $clave => $valor) {
            if (is_array($valor)) {
                $payload[$clave] = $this->redactar($valor);
                continue;
            }

            foreach (self::CAMPOS_REDACTADOS as $sensible) {
                if (stripos((string) $clave, $sensible) !== false) {
                    $payload[$clave] = '***';
                    break;
                }
            }
        }

        return $payload;
    }
}
