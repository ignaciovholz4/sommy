<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tu link de revendedor Sommy</title>
</head>
<body style="margin:0;padding:0;background:#F4F6FB;font-family:'Poppins',Arial,Helvetica,sans-serif;color:#1B2B5A;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F6FB;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden;">

                <tr>
                    <td style="background:linear-gradient(135deg,#131C36,#1B2B5A);padding:30px 28px;text-align:center;">
                        <div style="color:#ffffff;font-size:22px;font-weight:600;letter-spacing:.4px;">Sommy</div>
                        <div style="color:#C9D2E8;font-size:13px;margin-top:6px;">Programa de revendedores</div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px;">
                        <p style="margin:0 0 14px;font-size:16px;">¡Hola {{ $revendedor->nombre }}!</p>
                        <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#47536F;">
                            Ya sos revendedor de Sommy. Este es tu link personal: cada compra que se haga
                            entrando por acá queda registrada a tu nombre y te la liquidamos nosotros.
                            No tenés que hacer ningún seguimiento ni cargar nada.
                        </p>

                        <div style="background:#F4F6FB;border-radius:14px;padding:18px;text-align:center;">
                            <div style="font-size:12px;color:#7A869F;text-transform:uppercase;letter-spacing:1px;">Tu link</div>
                            <a href="{{ $link }}" style="display:block;margin-top:8px;font-size:15px;font-weight:600;color:#1B2B5A;word-break:break-all;text-decoration:none;">{{ $link }}</a>
                            <div style="margin-top:14px;font-size:12px;color:#7A869F;">Tu código: <strong style="color:#1B2B5A;">{{ $revendedor->codigo }}</strong></div>
                        </div>

                        <p style="margin:22px 0 8px;font-size:14px;font-weight:600;">Tu comisión</p>
                        <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#47536F;">
                            {{ rtrim(rtrim(number_format($revendedor->comision_porcentaje, 2, ',', '.'), '0'), ',') }}% sobre cada venta confirmada.
                            Te la transferimos según lo que acordemos; si querés ver cómo venís, escribinos y te pasamos el detalle.
                        </p>

                        <div style="text-align:center;margin:26px 0 8px;">
                            <a href="{{ route('revendedores.link', $revendedor->codigo) }}"
                               style="display:inline-block;background:#1B2B5A;color:#ffffff;text-decoration:none;padding:13px 28px;border-radius:999px;font-size:14px;font-weight:600;">
                                Ver mi link y descargar mi QR
                            </a>
                        </div>
                        <p style="margin:14px 0 0;font-size:12px;color:#7A869F;text-align:center;line-height:1.6;">
                            Guardá este mail. Desde ese botón podés descargar el QR para imprimir o compartir.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background:#131C36;padding:18px 28px;text-align:center;color:#8E9AB8;font-size:12px;">
                        Sommy — Fabricantes de colchones
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
