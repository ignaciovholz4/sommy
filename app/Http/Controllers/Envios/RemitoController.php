<?php

namespace App\Http\Controllers\Envios;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Envio;
use App\Models\Venta;
use App\Models\ecommerce\order_ecommerce;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Remito (nota de entrega) en PDF, uno por envío: detalle de lo entregado,
 * destinatario y transportista. Sin montos — el respaldo de facturación es
 * la factura/comprobante de la venta, no este documento.
 */
class RemitoController extends Controller
{
    public function pedido($id)
    {
        $order = order_ecommerce::with(['cliente', 'detalles.producto', 'detalles.combinacion'])->findOrFail($id);
        $envio = Envio::with('transportista')->where('order_ecommerce_id', $id)->latest('id')->first();

        $items = $order->detalles->map(function ($d) {
            return [
                'nombre'  => optional($d->producto)->nombre ?: 'Producto',
                'medida'  => optional($d->combinacion)->combinacion,
                'cantidad' => $d->quantity,
            ];
        });

        return $this->remito([
            'referencia' => 'Pedido #' . $order->order_id,
            'cliente'    => optional($order->cliente)->nombre ?: 'Cliente',
            'telefono'   => optional($order->cliente)->telefono,
            'direccion'  => optional($order->cliente)->direccion,
            'localidad'  => $order->direccion_localidad,
            'provincia'  => $order->direccion_provincia,
            'cp'         => $order->direccion_cp,
            'notas_pedido' => $order->additional_info,
            'items'      => $items,
        ], $envio);
    }

    public function venta($id)
    {
        $venta = Venta::with(['cliente', 'detalles.articulo', 'detalles.combinacion'])->findOrFail($id);
        $envio = Envio::with('transportista')->where('venta_id', $id)->latest('id')->first();

        $items = $venta->detalles->map(function ($d) {
            return [
                'nombre'  => optional($d->articulo)->nombre ?: 'Producto',
                'medida'  => optional($d->combinacion)->combinacion,
                'cantidad' => $d->cantidad,
            ];
        });

        return $this->remito([
            'referencia' => $venta->num_folio ?: 'Venta #' . $venta->idventa,
            'cliente'    => trim(optional($venta->cliente)->nombre . ' ' . optional($venta->cliente)->paterno) ?: 'Cliente',
            'telefono'   => optional($venta->cliente)->telefono,
            'direccion'  => optional($venta->cliente)->direccion,
            'localidad'  => optional($venta->cliente)->localidad,
            'provincia'  => optional($venta->cliente)->provincia,
            'cp'         => optional($venta->cliente)->codigo_postal,
            'notas_pedido' => null,
            'items'      => $items,
        ], $envio);
    }

    protected function remito(array $datos, ?Envio $envio)
    {
        $logoPath = public_path('imagenes/marca/sommy-logo-magia.png');
        $datos['logo'] = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        $datos['remitente'] = optional(Configuracion::first())->razon_social ?: 'Sommy';
        $datos['transportista'] = optional(optional($envio)->transportista)->nombre;
        $datos['pagado_por'] = optional($envio)->pagado_por;
        $datos['tracking'] = optional($envio)->tracking;
        $datos['direccion_envio'] = optional($envio)->direccion_entrega;
        $datos['fecha_despacho'] = optional($envio)->fecha_despacho;
        $datos['fecha_entrega_real'] = optional($envio)->fecha_entrega_real;
        $datos['estado'] = optional($envio)->estado;
        $datos['notas_envio'] = optional($envio)->notas;

        $pdf = Pdf::loadView('envios.remito-pdf', $datos)->setPaper('a4');
        return $pdf->stream('remito-' . str_replace(['#', ' '], ['', '-'], $datos['referencia']) . '.pdf');
    }
}
