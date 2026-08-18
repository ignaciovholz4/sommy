<?php

namespace App\Http\Controllers\Envios;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Finanzas\EnvioController;
use App\Http\Controllers\Order\OrderController;
use App\Models\Envio;
use App\Models\Transportista;
use App\Models\Venta;
use App\Models\ecommerce\order_ecommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Módulo Envíos: tablero único por etapas para toda la logística de pedidos.
 *
 *  1. Para asignar flete: pedidos con stock comprobado/pagados, sin envío aún
 *     (entran solos, no hay que cargarlos).
 *  2. Para despachar: ya tienen fletero asignado.
 *  3. En camino: despachados / en tránsito.
 *  4. Finalizados: entregados o fallidos (últimos 30 días).
 *
 * Reusa la maquinaria existente: EnvioController (alta de envío + gasto de
 * flete automático) y OrderController::update_status (mails al cliente y
 * comisiones de revendedor acompañan la etapa).
 */
class EnvioBoardController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'finanzas.envios.index');

        $paraAsignar = $this->pedidosSinFlete()->get();
        $ventasParaAsignar = $this->ventasSinFlete()->get();

        $base = fn () => Envio::with(['orden.cliente', 'venta.cliente', 'transportista'])
            ->whereIn('tipo', ['venta', 'venta_manual']);

        $paraDespachar = $base()->where('estado', 'pendiente')
            ->orderByRaw('fecha_entrega_estimada IS NULL, fecha_entrega_estimada')
            ->get();

        $enCamino = $base()->whereIn('estado', ['despachado', 'en_transito'])
            ->orderBy('fecha_despacho')
            ->get();

        $finalizados = $base()->whereIn('estado', ['entregado', 'fallido'])
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        $transportistas = Transportista::where('activo', true)->orderBy('nombre')->get();

        return view('envios.board', compact('paraAsignar', 'ventasParaAsignar', 'paraDespachar', 'enCamino', 'finalizados', 'transportistas'));
    }

    /** Pedidos ecommerce listos para salir (stock ya comprobado) sin flete asignado. */
    protected function pedidosSinFlete()
    {
        $conEnvio = Envio::whereNotNull('order_ecommerce_id')->pluck('order_ecommerce_id');

        return order_ecommerce::with(['cliente', 'status'])
            ->where('active', 1)
            ->whereIn('status_order_id', [2, 3])
            ->whereNotIn('order_id', $conEnvio)
            ->orderBy('order_date');
    }

    /** Ventas manuales recientes (30 días) sin flete asignado. */
    protected function ventasSinFlete()
    {
        $conEnvio = Envio::whereNotNull('venta_id')->pluck('venta_id');

        return Venta::with('cliente')
            ->where('estado', '!=', 'anulada')
            ->where('fecha', '>=', now()->subDays(30)->toDateString())
            ->whereNotIn('idventa', $conEnvio)
            ->orderBy('fecha');
    }

    /** Contador para el badge del header: todo lo que espera flete o despacho. */
    public function pendingCount()
    {
        return response()->json([
            'sin_flete'  => $this->pedidosSinFlete()->count() + $this->ventasSinFlete()->count(),
            'despachar'  => Envio::where('estado', 'pendiente')->whereIn('tipo', ['venta', 'venta_manual'])->count(),
        ]);
    }

    /** Asigna el fletero: crea el envío (y el gasto de flete si lo paga la empresa). */
    public function asignar(Request $request, EnvioController $envios)
    {
        $request->merge(['tipo' => $request->filled('venta_id') ? 'venta_manual' : 'venta']);

        return $envios->store($request);
    }

    /**
     * Avanza la etapa del envío. El pedido acompaña: despachar => Enviado(4),
     * entregar => Entregado(5) — con mails al cliente y comisiones incluidos.
     */
    public function etapa(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'finanzas.envios.manage');

        $request->validate(['accion' => 'required|in:despachar,transito,entregar,fallido']);

        $envio = Envio::findOrFail($id);

        $estadoEnvio = [
            'despachar' => 'despachado',
            'transito'  => 'en_transito',
            'entregar'  => 'entregado',
            'fallido'   => 'fallido',
        ][$request->accion];

        app(EnvioController::class)->setEstado(new Request(['estado' => $estadoEnvio]), $envio->id);

        $statusPedido = ['despachar' => 4, 'entregar' => 5][$request->accion] ?? null;
        if ($statusPedido && $envio->order_ecommerce_id) {
            app(OrderController::class)->update_status(new Request([
                'orderId'  => $envio->order_ecommerce_id,
                'statusId' => $statusPedido,
            ]));
        }

        return response()->json(['estado' => 1, 'mensaje' => 'El envío avanzó de etapa.']);
    }
}
