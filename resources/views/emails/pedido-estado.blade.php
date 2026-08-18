<!doctype html>
<html lang="es">
<body style="margin:0;padding:0;background:#F8FAFC;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:560px;margin:0 auto;padding:28px 16px;">
        <div style="background:#1B2B5A;border-radius:16px 16px 0 0;padding:22px;text-align:center;">
            <span style="color:#ffffff;font-size:26px;font-weight:bold;letter-spacing:.5px;">Sommy</span>
        </div>
        <div style="background:#ffffff;border:1px solid #E7EAF2;border-top:none;border-radius:0 0 16px 16px;padding:28px 26px;">
            <h1 style="color:#1B2B5A;font-size:20px;margin:0 0 6px;">{{ $titulo }}</h1>
            <p style="color:#8A93AD;font-size:13px;margin:0 0 18px;">Pedido #{{ $pedido->order_id }}</p>

            <p style="color:#47536F;font-size:15px;line-height:1.7;margin:0 0 10px;">
                Hola {{ explode(' ', trim($cliente->nombre ?? ''))[0] ?: 'cliente' }},
            </p>
            <p style="color:#47536F;font-size:15px;line-height:1.7;margin:0 0 22px;">{{ $mensaje }}</p>

            <table style="width:100%;border-collapse:collapse;margin-bottom:22px;">
                <tr>
                    <td style="padding:10px 0;border-top:1px solid #F1F4F9;color:#8A93AD;font-size:13px;">Total del pedido</td>
                    <td style="padding:10px 0;border-top:1px solid #F1F4F9;color:#1B2B5A;font-size:15px;font-weight:bold;text-align:right;">${{ number_format($pedido->total_amount, 2, ',', '.') }}</td>
                </tr>
                @if(!empty($pedido->fecha_entrega))
                <tr>
                    <td style="padding:10px 0;border-top:1px solid #F1F4F9;color:#8A93AD;font-size:13px;">Entrega programada</td>
                    <td style="padding:10px 0;border-top:1px solid #F1F4F9;color:#1B2B5A;font-size:15px;font-weight:bold;text-align:right;">{{ \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y') }}</td>
                </tr>
                @endif
            </table>

            <div style="text-align:center;">
                <a href="{{ url('/cuenta/pedidos/' . $pedido->order_id) }}"
                   style="display:inline-block;background:#1B2B5A;color:#ffffff;text-decoration:none;border-radius:999px;padding:13px 32px;font-size:14px;font-weight:bold;">
                    Seguir mi pedido
                </a>
            </div>
        </div>
        <p style="text-align:center;color:#8A93AD;font-size:11.5px;margin-top:16px;">
            Este es un aviso automático de tu compra en Sommy. Ante cualquier duda, respondé este correo o escribinos por WhatsApp.
        </p>
    </div>
</body>
</html>
