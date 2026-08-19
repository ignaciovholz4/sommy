<?php

namespace App\Http\Controllers\Envios;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Order\OrderController;
use App\Models\Envio;
use App\Models\Transportista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Portal del fletero (acceso por link con token, pensado para el celular):
 * sus entregas de la semana en el orden de la hoja de ruta, y la confirmación
 * de cada entrega con monto cobrado, firma del cliente y foto del efectivo.
 * Todo queda asociado al pedido/venta como comprobante, en tiempo real.
 */
class FleteroPortalController extends Controller
{
    protected function transportistaPorToken(string $token): Transportista
    {
        return Transportista::where('token_acceso', $token)->where('activo', true)->firstOrFail();
    }

    public function portal(string $token)
    {
        $fletero = $this->transportistaPorToken($token);

        $envios = Envio::with(['orden.cliente', 'venta.cliente'])
            ->where('transportista_id', $fletero->id)
            ->whereIn('tipo', ['venta', 'venta_manual'])
            ->whereIn('estado', ['pendiente', 'despachado', 'en_transito'])
            ->where(function ($q) {
                $q->whereBetween('fecha_entrega_estimada', [now()->subDay()->toDateString(), now()->addDays(7)->toDateString()])
                  ->orWhereNull('fecha_entrega_estimada');
            })
            ->orderByRaw('fecha_entrega_estimada IS NULL, fecha_entrega_estimada, orden_ruta IS NULL, orden_ruta')
            ->get()
            ->each(fn ($e) => $this->completarParada($e));

        $porDia = $envios->groupBy(fn ($e) => $e->fecha_entrega_estimada
            ? $e->fecha_entrega_estimada->format('Y-m-d')
            : 'sin-fecha');

        $entregadasHoy = DB::table('entregas_fletero')
            ->where('transportista_id', $fletero->id)
            ->whereDate('created_at', now()->toDateString());

        return view('fletero.portal', [
            'fletero' => $fletero,
            'token' => $token,
            'porDia' => $porDia,
            'entregasHoy' => $entregadasHoy->count(),
            'cobradoHoy' => (float) $entregadasHoy->sum('monto_cobrado'),
        ]);
    }

    /** Confirmación de entrega: monto cobrado + firma en pantalla + foto del efectivo. */
    public function confirmarEntrega(Request $request, string $token, $envioId)
    {
        $fletero = $this->transportistaPorToken($token);

        $envio = Envio::with(['orden.cliente', 'venta.cliente'])
            ->where('transportista_id', $fletero->id)
            ->whereIn('estado', ['pendiente', 'despachado', 'en_transito'])
            ->findOrFail($envioId);

        $request->validate([
            'monto_cobrado' => 'required|numeric|min:0',
            'firma'         => 'required|string',                     // PNG en base64 del canvas
            'foto_plata'    => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'nota'          => 'nullable|string|max:300',
        ], [
            'firma.required' => 'Falta la firma del cliente.',
            'monto_cobrado.required' => 'Indicá cuánto cobraste (0 si no cobraste nada).',
        ]);

        $dir = public_path('imagenes/entregas');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Firma: llega como data:image/png;base64
        $firmaPath = null;
        if (preg_match('/^data:image\/png;base64,(.+)$/s', $request->firma, $m)) {
            $firmaPath = 'imagenes/entregas/firma-' . $envio->id . '-' . uniqid() . '.png';
            file_put_contents(public_path($firmaPath), base64_decode($m[1]));
        }

        $fotoPlataPath = null;
        if ($request->hasFile('foto_plata')) {
            $nombre = 'plata-' . $envio->id . '-' . uniqid() . '.' . $request->file('foto_plata')->getClientOriginalExtension();
            $request->file('foto_plata')->move($dir, $nombre);
            $fotoPlataPath = 'imagenes/entregas/' . $nombre;
        }

        DB::table('entregas_fletero')->insert([
            'envio_id'         => $envio->id,
            'transportista_id' => $fletero->id,
            'monto_cobrado'    => $request->monto_cobrado,
            'firma_path'       => $firmaPath,
            'foto_plata_path'  => $fotoPlataPath,
            'nota'             => $request->nota,
        ]);

        // La firma y la foto quedan como comprobantes DE LA OPERACIÓN (pedido o venta)
        $notaComp = 'Entrega del fletero ' . $fletero->nombre . ' — cobró $' . number_format((float) $request->monto_cobrado, 2, ',', '.');
        if ($envio->order_ecommerce_id) {
            foreach (array_filter(['Firma del cliente' => $firmaPath, 'Foto del efectivo' => $fotoPlataPath]) as $titulo => $ruta) {
                DB::table('order_pago_comprobantes')->insert([
                    'order_id' => $envio->order_ecommerce_id,
                    'archivo'  => $ruta,
                    'mime'     => 'image/png',
                    'nota'     => $titulo . ' · ' . $notaComp,
                ]);
            }
        } elseif ($envio->venta_id) {
            foreach (array_filter(['Firma del cliente' => $firmaPath, 'Foto del efectivo' => $fotoPlataPath]) as $titulo => $ruta) {
                DB::table('venta_pago_comprobantes')->insert([
                    'venta_id' => $envio->venta_id,
                    'archivo'  => $ruta,
                    'mime'     => 'image/png',
                    'nota'     => $titulo . ' · ' . $notaComp,
                ]);
            }
        }

        // El envío pasa a entregado; el pedido acompaña (mail al cliente + comisiones)
        $envio->forceFill(['estado' => 'entregado', 'fecha_entrega_real' => now()])->save();
        if ($envio->order_ecommerce_id) {
            app(OrderController::class)->update_status(new Request([
                'orderId'  => $envio->order_ecommerce_id,
                'statusId' => 5,
            ]));
        }

        return redirect(url('fletero/' . $token))->with('entrega_ok', 'Entrega registrada. ¡Gracias!');
    }

    /** Datos de la parada para las tarjetas del portal. */
    protected function completarParada(Envio $e): void
    {
        $cliente = optional($e->orden)->cliente ?? optional($e->venta)->cliente;
        $e->parada_cliente = trim(optional($cliente)->nombre . ' ' . (optional($cliente)->paterno ?? '')) ?: 'Cliente';
        $e->parada_telefono = optional($cliente)->telefono;
        $e->parada_direccion = $e->direccion_entrega ?: trim(implode(', ', array_filter([
            optional($cliente)->direccion,
            optional($cliente)->localidad ?? optional($e->orden)->direccion_localidad,
        ])));
        $e->parada_referencia = $e->order_ecommerce_id ? 'Pedido #' . $e->order_ecommerce_id
            : (optional($e->venta)->num_folio ?: 'Venta #' . $e->venta_id);

        // Cuánto tiene que cobrar en la puerta: total menos lo ya pagado
        // (si el cliente transfirió antes, el pago figura y acá se descuenta)
        if ($e->order_ecommerce_id && $e->orden) {
            $pagado = (float) DB::table('movimientos')->where('comprobante', 'Pedido #' . $e->order_ecommerce_id)->sum('total');
            $e->parada_pagado = $pagado;
            $e->parada_a_cobrar = max((float) $e->orden->total_amount - $pagado, 0);
        } elseif ($e->venta) {
            $pagado = (float) DB::table('movimientos')->where('comprobante', $e->venta->num_folio)->sum('total');
            $e->parada_pagado = $pagado;
            $e->parada_a_cobrar = max((float) $e->venta->total_con_iva - $pagado, 0);
        } else {
            $e->parada_pagado = 0;
            $e->parada_a_cobrar = 0;
        }

        // Más el flete si lo paga el cliente
        if ($e->pagado_por === 'cliente' && (float) $e->costo > 0) {
            $e->parada_a_cobrar += (float) $e->costo;
        }
    }
}
