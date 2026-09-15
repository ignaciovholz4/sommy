<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmá tu correo — Sommy</title>
</head>
<body style="font-family: 'Poppins', Arial, Helvetica, sans-serif; background:#F8FAFC; margin:0; padding:24px 16px;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:18px; overflow:hidden; border:1px solid #E7EAF2; box-shadow:0 10px 30px rgba(27,43,90,.10);">

        <div style="background:#1B2B5A; padding:28px 30px; text-align:center;">
            <img src="{{ asset('imagenes/marca/sommy-logo-blanco.png') }}" alt="Sommy" style="height:34px; max-width:180px;">
        </div>

        <div style="padding:36px 34px;">
            <h1 style="margin:0 0 4px; font-size:20px; color:#1B2B5A;">¡Hola{{ $nombre ? ' ' . $nombre : '' }}! 👋</h1>
            <p style="margin:0 0 20px; font-size:15px; color:#47536F; line-height:1.55;">
                ¡Bienvenido a Sommy! Antes de poder finalizar tu compra, necesitamos que confirmes tu correo electrónico.
            </p>

            <div style="text-align:center; margin:32px 0;">
                <a href="{{ $url }}" style="display:inline-block; background:#1B2B5A; color:#ffffff; text-decoration:none; padding:14px 36px; border-radius:999px; font-weight:600; font-size:15px;">
                    Confirmar mi correo
                </a>
            </div>

            <p style="margin:0 0 4px; font-size:13px; color:#6E7A96; line-height:1.5;">
                El enlace vence en 60 minutos. Si no creaste esta cuenta, ignorá este mensaje: no se va a activar nada.
            </p>

            <hr style="border:none; border-top:1px solid #E7EAF2; margin:28px 0;">

            <p style="margin:0 0 6px; font-size:12.5px; color:#94A3B8;">
                Si el botón no funciona, copiá y pegá este enlace en tu navegador:
            </p>
            <p style="margin:0; font-size:12px; color:#2563EB; word-break:break-all;">
                {{ $url }}
            </p>
        </div>

        <div style="background:#F8FAFC; padding:18px 30px; text-align:center; border-top:1px solid #E7EAF2;">
            <p style="margin:0; font-size:12px; color:#94A3B8;">Sommy — Liviano como una pluma.</p>
        </div>
    </div>
</body>
</html>
