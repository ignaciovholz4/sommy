<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f4f5; margin:0; padding:20px;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e4e4e7;">
        <div style="background:#111827; color:#ffffff; padding:20px 30px;">
            <h2 style="margin:0;">{{ $config->name ?? 'Sommy' }}</h2>
            <p style="margin:5px 0 0 0; color:#d1d5db;">Dejaste productos en tu carrito</p>
        </div>

        <div style="padding:30px;">
            <p>Hola <strong>{{ $cliente->nombre }}</strong>,</p>
            <p>Notamos que dejaste estos productos en tu carrito sin terminar la compra. Te los guardamos:</p>

            <table style="width:100%; border-collapse:collapse; margin:20px 0;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="text-align:left; padding:8px; border-bottom:2px solid #e5e7eb;">Producto</th>
                        <th style="text-align:center; padding:8px; border-bottom:2px solid #e5e7eb;">Cant.</th>
                        <th style="text-align:right; padding:8px; border-bottom:2px solid #e5e7eb;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td style="padding:8px; border-bottom:1px solid #f3f4f6;">
                                {{ $item['name'] ?? '' }}
                                @if(!empty($item['rowProdVariant']['combinacion']))
                                    <br><small style="color:#6b7280;">Medida: {{ $item['rowProdVariant']['combinacion'] }}</small>
                                @endif
                            </td>
                            <td style="text-align:center; padding:8px; border-bottom:1px solid #f3f4f6;">{{ $item['cant'] ?? 1 }}</td>
                            <td style="text-align:right; padding:8px; border-bottom:1px solid #f3f4f6;">$ {{ number_format((float) ($item['total'] ?? 0), 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:right; padding:8px; font-weight:bold; border-top:2px solid #e5e7eb;">Total</td>
                        <td style="text-align:right; padding:8px; font-weight:bold; border-top:2px solid #e5e7eb;">$ {{ number_format($total, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <div style="text-align:center; margin:30px 0;">
                <a href="{{ url('/Ecommerceorder') }}" style="display:inline-block; background:#111827; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:999px; font-weight:bold;">
                    Terminar mi compra
                </a>
            </div>

            @if(!empty($config->whatsapp))
                <p style="color:#374151;"><small>¿Tenés alguna duda? Escribinos por WhatsApp al {{ $config->whatsapp }}.</small></p>
            @endif
        </div>
    </div>
</body>
</html>
