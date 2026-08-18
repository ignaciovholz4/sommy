<?php

namespace App\Http\Controllers\Envios;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Envio;
use App\Models\Venta;
use App\Models\ecommerce\order_ecommerce;

/**
 * Etiquetas de envío 10x15 cm (100x150 mm) listas para impresoras térmicas
 * de etiquetas autoadhesivas. Una por pedido ecommerce o venta manual.
 */
class EtiquetaController extends Controller
{
    public function pedido($id)
    {
        $order = order_ecommerce::with(['cliente', 'detalles'])->findOrFail($id);
        $envio = Envio::with('transportista')->where('order_ecommerce_id', $id)->latest('id')->first();

        return $this->etiqueta([
            'referencia' => 'Pedido #' . $order->order_id,
            'cliente'    => optional($order->cliente)->nombre ?: 'Cliente',
            'telefono'   => optional($order->cliente)->telefono,
            'direccion'  => optional($order->cliente)->direccion,
            'localidad'  => $order->direccion_localidad,
            'provincia'  => $order->direccion_provincia,
            'cp'         => $order->direccion_cp,
            'bultos'     => (int) $order->detalles->sum('quantity'),
            'notas'      => $order->additional_info,
        ], $envio);
    }

    public function venta($id)
    {
        $venta = Venta::with(['cliente', 'detalles'])->findOrFail($id);
        $envio = Envio::with('transportista')->where('venta_id', $id)->latest('id')->first();

        return $this->etiqueta([
            'referencia' => $venta->num_folio ?: 'Venta #' . $venta->idventa,
            'cliente'    => trim(optional($venta->cliente)->nombre . ' ' . optional($venta->cliente)->paterno) ?: 'Cliente',
            'telefono'   => optional($venta->cliente)->telefono,
            'direccion'  => optional($venta->cliente)->direccion,
            'localidad'  => optional($venta->cliente)->localidad,
            'provincia'  => optional($venta->cliente)->provincia,
            'cp'         => optional($venta->cliente)->codigo_postal,
            'bultos'     => (int) $venta->detalles->sum('cantidad'),
            'notas'      => null,
        ], $envio);
    }

    protected function etiqueta(array $datos, ?Envio $envio)
    {
        $datos['remitente'] = optional(Configuracion::first())->razon_social ?: 'Sommy';
        $datos['transportista'] = optional(optional($envio)->transportista)->nombre;
        $datos['pagado_por'] = optional($envio)->pagado_por;
        $datos['tracking'] = optional($envio)->tracking;
        $datos['direccion_envio'] = optional($envio)->direccion_entrega; // pisa la del cliente si se editó al asignar flete
        $datos['copias'] = max(1, min(10, (int) request('copias', 1)));

        return view('envios.etiqueta', $datos);
    }
}
