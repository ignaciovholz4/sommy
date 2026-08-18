<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f4f5; margin:0; padding:20px;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e4e4e7;">
        <div style="background:#111827; color:#ffffff; padding:20px 30px;">
            <h2 style="margin:0;">{{ $config->name ?? 'Tienda' }}</h2>
            <p style="margin:5px 0 0 0; color:#d1d5db;">Confirmación de pedido #{{ $order->order_id }}</p>
        </div>

        <div style="padding:30px;">
            <p>Hola <strong>{{ $cliente->nombre }}</strong>,</p>
            <p>¡Gracias por tu compra! Recibimos tu pedido correctamente. Este es el detalle:</p>

            <table style="width:100%; border-collapse:collapse; margin:20px 0;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="text-align:left; padding:8px; border-bottom:2px solid #e5e7eb;">Producto</th>
                        <th style="text-align:center; padding:8px; border-bottom:2px solid #e5e7eb;">Cant.</th>
                        <th style="text-align:right; padding:8px; border-bottom:2px solid #e5e7eb;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detalles as $detalle)
                        <tr>
                            <td style="padding:8px; border-bottom:1px solid #f3f4f6;">
                                {{ $detalle->producto_nombre }}
                                @if(!empty($detalle->variante_nombre))
                                    <br><small style="color:#6b7280;">Medida: {{ $detalle->variante_nombre }}</small>
                                @endif
                            </td>
                            <td style="text-align:center; padding:8px; border-bottom:1px solid #f3f4f6;">{{ $detalle->quantity }}</td>
                            <td style="text-align:right; padding:8px; border-bottom:1px solid #f3f4f6;">$ {{ number_format($detalle->total, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:right; padding:8px;">Subtotal</td>
                        <td style="text-align:right; padding:8px;">$ {{ number_format($order->subtotal_amount, 2, ',', '.') }}</td>
                    </tr>
                    @if($order->costo_envio > 0)
                    <tr>
                        <td colspan="2" style="text-align:right; padding:8px;">Envío</td>
                        <td style="text-align:right; padding:8px;">$ {{ number_format($order->costo_envio, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($order->descuento_pago > 0)
                    <tr>
                        <td colspan="2" style="text-align:right; padding:8px; color:#16a34a;">Descuento transferencia</td>
                        <td style="text-align:right; padding:8px; color:#16a34a;">-$ {{ number_format($order->descuento_pago, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="2" style="text-align:right; padding:8px; font-weight:bold; border-top:2px solid #e5e7eb;">Total</td>
                        <td style="text-align:right; padding:8px; font-weight:bold; border-top:2px solid #e5e7eb;">$ {{ number_format($order->total_amount, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            @if($metodoPago === 'transferencia')
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:15px 20px; margin:20px 0;">
                    <h3 style="margin:0 0 10px 0; color:#166534;">Datos para transferir</h3>
                    @if(!empty($config->razon_social))<p style="margin:4px 0;">Titular: <strong>{{ $config->razon_social }}</strong></p>@endif
                    @if(!empty($config->cuit))<p style="margin:4px 0;">CUIT: {{ $config->cuit }}</p>@endif
                    @if(!empty($config->cbu))<p style="margin:4px 0;">CBU: <strong>{{ $config->cbu }}</strong></p>@endif
                    @if(!empty($config->alias_cbu))<p style="margin:4px 0;">Alias: <strong>{{ $config->alias_cbu }}</strong></p>@endif
                    <p style="margin:10px 0 0 0;">Monto a transferir: <strong>$ {{ number_format($order->total_amount, 2, ',', '.') }}</strong></p>
                    @if(!empty($config->whatsapp))
                        <p style="margin:10px 0 0 0; color:#374151;"><small>Una vez realizada la transferencia, envianos el comprobante por WhatsApp al {{ $config->whatsapp }} para coordinar la entrega.</small></p>
                    @endif
                </div>
            @elseif($metodoPago === 'mercadopago')
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:15px 20px; margin:20px 0;">
                    <p style="margin:0;">Elegiste pagar con <strong>MercadoPago</strong>. Si todavía no completaste el pago, podés hacerlo desde el link que se abrió al confirmar el pedido.</p>
                </div>
            @endif

            @if(!empty($order->direccion_localidad))
                <p style="color:#374151;">
                    <strong>Entrega:</strong>
                    {{ $cliente->direccion }} {{ $cliente->number_exterior }},
                    {{ $order->direccion_localidad }}, {{ $order->direccion_provincia }} (CP {{ $order->direccion_cp }})
                </p>
            @endif

            <p style="color:#6b7280; margin-top:30px;"><small>Ante cualquier consulta respondé este correo o escribinos por WhatsApp.</small></p>
        </div>
    </div>
</body>
</html>
