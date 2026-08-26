<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Moneda;
use App\Models\OperacionCambio;
use App\Services\OperacionCambioService;
use App\Services\SolicitudAprobacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Compra/venta de moneda extranjera (USD, USDT) contra pesos, con costeo
 * FIFO. Ver App\Services\OperacionCambioService para la lógica de negocio.
 */
class OperacionCambioController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'finanzas.divisas.index');

        $monedas = Moneda::where('codigo', '!=', 'ARS')->orderBy('codigo')->get();

        return view('finanzas.divisas.index', compact('monedas'));
    }

    public function data()
    {
        Gate::authorize('haveaccess', 'finanzas.divisas.index');

        $operaciones = OperacionCambio::with(['moneda', 'cuentaArs', 'cuentaMoneda'])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'estado' => 1,
            'data' => $operaciones->map(fn (OperacionCambio $o) => [
                'id' => $o->id,
                'tipo' => $o->tipo,
                'moneda' => $o->moneda->codigo,
                'fecha' => $o->fecha->format('d/m/Y'),
                'monto_moneda' => (float) $o->monto_moneda,
                'cotizacion' => (float) $o->cotizacion,
                'monto_ars' => (float) $o->monto_ars,
                'cuenta_ars' => optional($o->cuentaArs)->nombre,
                'cuenta_moneda' => optional($o->cuentaMoneda)->nombre,
                'disponible' => $o->tipo === 'compra' ? (float) $o->disponible : null,
                'resultado' => $o->resultado !== null ? (float) $o->resultado : null,
                'observaciones' => $o->observaciones,
                // Cuando no hay cuenta ARS real (pago/cobro directo de una compra/venta)
                'referencia' => $this->etiquetaReferencia($o),
            ])->values(),
        ]);
    }

    /** Para operaciones sin cuenta ARS (pago/cobro directo de una compra/venta), un link legible al origen. */
    private function etiquetaReferencia(OperacionCambio $o): ?string
    {
        if (!$o->referencia_type || !$o->referencia_id) {
            return null;
        }

        return match ($o->referencia_type) {
            \App\Models\Compra::class => 'Compra #' . $o->referencia_id,
            \App\Models\Venta::class => 'Venta #' . $o->referencia_id,
            default => null,
        };
    }

    /** Cuentas y disponible de una moneda, para armar el formulario de compra/venta. */
    public function formData(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.divisas.index');

        $request->validate(['moneda_id' => 'required|exists:monedas,id']);

        $ars = Moneda::where('codigo', 'ARS')->first();

        $cuentasArs = Cuenta::where('moneda_id', optional($ars)->id)->where('activa', true)
            ->get(['id', 'nombre']);
        $cuentasMoneda = Cuenta::where('moneda_id', $request->moneda_id)->where('activa', true)
            ->get(['id', 'nombre']);

        $service = app(OperacionCambioService::class);

        return response()->json([
            'estado' => 1,
            'cuentas_ars' => $cuentasArs,
            'cuentas_moneda' => $cuentasMoneda,
            'disponible' => $service->disponible((int) $request->moneda_id),
        ]);
    }

    public function store(Request $request, OperacionCambioService $service, SolicitudAprobacionService $solicitudes)
    {
        Gate::authorize('haveaccess', 'finanzas.divisas.manage');

        $validated = $request->validate([
            'tipo' => 'required|in:compra,venta',
            'moneda_id' => 'required|exists:monedas,id',
            'cuenta_ars_id' => 'required|exists:cuentas,id',
            'cuenta_moneda_id' => 'required|exists:cuentas,id',
            'monto_moneda' => 'required|numeric|min:0.01',
            'cotizacion' => 'required|numeric|min:0.0001',
            'fecha' => 'nullable|date',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $moneda = Moneda::find($validated['moneda_id']);
        $descripcion = ($validated['tipo'] === 'compra' ? 'Comprar ' : 'Vender ')
            . number_format($validated['monto_moneda'], 2, ',', '.') . ' ' . optional($moneda)->codigo
            . ' a $' . number_format($validated['cotizacion'], 2, ',', '.');

        try {
            $resultado = $solicitudes->ejecutarOSolicitar(
                'divisa.' . $validated['tipo'],
                $descripcion,
                $validated,
                null,
                fn () => $validated['tipo'] === 'compra'
                    ? $service->registrarCompra($validated, Auth::id())
                    : $service->registrarVenta($validated, Auth::id())
            );
        } catch (\RuntimeException $e) {
            return response()->json(['estado' => 0, 'mensaje' => $e->getMessage()], 422);
        }

        if (!$resultado['ejecutado']) {
            return response()->json(['estado' => 1, 'pendiente' => true, 'mensaje' => 'Tu solicitud quedó pendiente de aprobación del administrador.']);
        }

        $operacion = $resultado['resultado'];

        return response()->json([
            'estado' => 1,
            'mensaje' => $validated['tipo'] === 'compra' ? 'Compra registrada.' : 'Venta registrada.',
            'resultado' => $operacion->resultado !== null ? (float) $operacion->resultado : null,
        ]);
    }
}
