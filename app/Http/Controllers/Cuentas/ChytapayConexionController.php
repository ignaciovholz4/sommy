<?php

namespace App\Http\Controllers\Cuentas;

use App\Http\Controllers\Controller;
use App\Models\ChytapayConexion;
use App\Models\Cuenta;
use App\Services\Chytapay\ChytapayAuthService;
use App\Services\Chytapay\ChytapayPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ChytapayConexionController extends Controller
{
    public function __construct(private ChytapayAuthService $auth, private ChytapayPaymentService $pagos)
    {
    }

    /** Estado de conexion de la Cuenta, para pintar la card en conciliacion/index. */
    public function estado(Cuenta $cuenta)
    {
        return response()->json([
            'estado' => 1,
            'habilitado' => $this->auth->habilitado(),
            'conexion' => $this->conexionData($cuenta->chytapayConexion),
        ]);
    }

    public function conectar(Cuenta $cuenta)
    {
        Gate::authorize('haveaccess', 'finanzas.chytapay.manage');

        if (!$this->auth->habilitado()) {
            return response()->json(['estado' => 0, 'mensaje' => 'Chytapay no esta configurado (revisar .env).'], 422);
        }

        return response()->json(['estado' => 1, 'url' => $this->auth->buildAuthorizeUrl($cuenta)]);
    }

    public function callback(Request $request)
    {
        $cuenta = $this->auth->resolverCuentaDesdeState($request->query('state'));

        if (!$cuenta) {
            abort(419, 'La sesion de conexion con Chytapay vencio o es invalida, intenta de nuevo.');
        }

        $code = $request->query('code');
        if (!$code) {
            return redirect()->route('cuentas.conciliacion.index', $cuenta->id)
                ->with('error', 'Chytapay no devolvio un code de autorizacion.');
        }

        try {
            $this->auth->exchangeCode($cuenta, $code, Auth::id());
        } catch (\Throwable $th) {
            return redirect()->route('cuentas.conciliacion.index', $cuenta->id)
                ->with('error', 'No se pudo completar la conexion con Chytapay: ' . $th->getMessage());
        }

        return redirect()->route('cuentas.conciliacion.index', $cuenta->id)
            ->with('success', 'Cuenta conectada a Chytapay correctamente.');
    }

    public function desconectar(Cuenta $cuenta)
    {
        Gate::authorize('haveaccess', 'finanzas.chytapay.manage');

        $cuenta->chytapayConexion()->delete();

        return response()->json(['estado' => 1, 'mensaje' => 'Cuenta desconectada de Chytapay.']);
    }

    public function sincronizarAhora(Cuenta $cuenta)
    {
        Gate::authorize('haveaccess', 'finanzas.chytapay.manage');

        $conexion = $cuenta->chytapayConexion;
        if (!$conexion) {
            return response()->json(['estado' => 0, 'mensaje' => 'Esta cuenta no esta conectada a Chytapay.'], 422);
        }

        $creados = $this->pagos->sincronizar($conexion);

        return response()->json([
            'estado' => 1,
            'mensaje' => $creados > 0 ? "Se importaron {$creados} cobro(s) nuevo(s)." : 'No hay cobros nuevos.',
            'creados' => $creados,
            'conexion' => $this->conexionData($conexion->refresh()),
        ]);
    }

    private function conexionData(?ChytapayConexion $conexion): ?array
    {
        if (!$conexion) {
            return null;
        }

        return [
            'comercio_nombre' => $conexion->comercio_nombre,
            'comercio_email' => $conexion->comercio_email,
            'conectado_at' => optional($conexion->conectado_at)->format('d/m/Y H:i'),
            'last_sync_at' => optional($conexion->last_sync_at)->format('d/m/Y H:i'),
        ];
    }
}
