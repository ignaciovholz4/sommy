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

    /**
     * Hoja de ruta del día: envíos del día (y sin fecha) por fletero, con las
     * paradas ordenadas por cercanía saliendo del depósito (vecino más cercano).
     */
    public function hojaRuta(Request $request, \App\Services\Envios\GeocodificadorService $geo)
    {
        Gate::authorize('haveaccess', 'finanzas.envios.index');

        $fecha = $request->query('fecha') ?: now()->toDateString();
        $transportistaId = $request->query('transportista_id');
        $incluirSinFecha = $request->query('sin_fecha', '1') === '1';

        $envios = Envio::with(['orden.cliente', 'venta.cliente', 'transportista'])
            ->whereIn('tipo', ['venta', 'venta_manual'])
            ->whereIn('estado', ['pendiente', 'despachado', 'en_transito'])
            ->when($transportistaId, fn ($q) => $q->where('transportista_id', $transportistaId))
            ->where(function ($q) use ($fecha, $incluirSinFecha) {
                $q->whereDate('fecha_entrega_estimada', $fecha);
                if ($incluirSinFecha) {
                    $q->orWhereNull('fecha_entrega_estimada');
                }
            })
            ->get();

        // Completar datos de cada parada (dirección + cliente + pendiente de cobro)
        $envios->each(function ($e) {
            $cliente = optional($e->orden)->cliente ?? optional($e->venta)->cliente;
            $e->parada_cliente = trim(optional($cliente)->nombre . ' ' . (optional($cliente)->paterno ?? '')) ?: 'Cliente';
            $e->parada_telefono = optional($cliente)->telefono;
            $e->parada_direccion = $e->direccion_entrega ?: trim(implode(', ', array_filter([
                optional($cliente)->direccion,
                optional($cliente)->localidad ?? optional($e->orden)->direccion_localidad,
                optional($cliente)->provincia ?? optional($e->orden)->direccion_provincia,
            ])));
            $e->parada_referencia = $e->order_ecommerce_id ? 'Pedido #' . $e->order_ecommerce_id
                : (optional($e->venta)->num_folio ?: 'Venta #' . $e->venta_id);
        });

        // Geocodificar las paradas que aún no tienen coordenadas (respetando ~1 req/seg)
        $pendientesGeo = $envios->filter(fn ($e) => (!$e->lat || !$e->lng) && $e->parada_direccion);
        foreach ($pendientesGeo as $i => $e) {
            if ($coords = $geo->geocodificar($e->parada_direccion)) {
                $e->forceFill(['lat' => $coords['lat'], 'lng' => $coords['lng']])->save();
            }
            if ($i < $pendientesGeo->count() - 1) {
                usleep(1100000); // política de uso de Nominatim: máx 1 consulta/seg
            }
        }

        $ruta = $geo->ordenarRuta($envios);

        // Persistir el orden calculado
        $ruta['ordenados']->values()->each(fn ($e, $i) => $e->forceFill(['orden_ruta' => $i + 1])->save());

        // Link de navegación con toda la ruta (Google Maps con paradas en orden)
        $deposito = \App\Services\Envios\GeocodificadorService::deposito();
        $puntos = collect([$deposito['direccion']])
            ->concat($ruta['ordenados']->filter(fn ($e) => $e->parada_direccion)->map(fn ($e) => $e->parada_direccion))
            ->map(fn ($d) => rawurlencode($d))->implode('/');
        $urlMaps = 'https://www.google.com/maps/dir/' . $puntos;

        $transportistas = Transportista::where('activo', true)->orderBy('nombre')->get();

        return view('envios.ruta', [
            'paradas' => $ruta['ordenados'],
            'kmTotal' => $ruta['km_total'],
            'fecha' => $fecha,
            'transportistaId' => $transportistaId,
            'incluirSinFecha' => $incluirSinFecha,
            'transportistas' => $transportistas,
            'deposito' => $deposito,
            'urlMaps' => $urlMaps,
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
