<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Remito {{ $referencia }}</title>
<style>
    * { font-family: Helvetica, Arial, sans-serif; box-sizing: border-box; }
    body { color: #1F2A44; font-size: 15px; margin: 0; padding: 0 34px 28px; }

    .top-band { background-color: #0C1428; margin: 0 -34px 24px; padding: 26px 34px 24px; border-radius: 0 0 18px 18px; }
    .top-band .logo-plate { display: inline-block; background-color: #fff; border-radius: 999px; padding: 8px 18px; }
    .top-band .logo-plate img { height: 34px; }
    .top-band .logo-cell { width: 55%; float: left; }
    .top-band .title-cell { width: 45%; float: right; text-align: right; margin-top: 4px; }
    .top-band .title-cell .titulo { font-size: 22px; font-weight: bold; color: #fff; letter-spacing: 1.5px; }
    .top-band .title-cell .folio { display: inline-block; margin-top: 8px; padding: 6px 16px; background-color: #C9A227; color: #0C1428; font-size: 13px; font-weight: bold; border-radius: 999px; }
    .clear { clear: both; }

    .info { width: 100%; margin-bottom: 22px; }
    .info .box { width: 48%; float: left; background-color: #F4F7FB; border-radius: 10px; padding: 16px 18px; }
    .info .box.right { float: right; }
    .info .box p { margin: 0 0 8px 0; }
    .info .box p:last-child { margin-bottom: 0; }
    .label { color: #6B7A99; font-size: 11.5px; text-transform: uppercase; letter-spacing: .4px; display: block; }
    .value { font-size: 15px; font-weight: bold; color: #1F2A44; }
    .badge-est { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: bold; background: #E7EEF8; color: #1B2B5A; text-transform: capitalize; }

    .section-title { font-size: 15px; font-weight: bold; color: #1B2B5A; margin: 0 0 10px 0; }
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table.items thead th { background-color: #1B2B5A; color: #fff; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; padding: 10px 8px; text-align: left; }
    table.items thead th.num { text-align: right; }
    table.items tbody td { padding: 10px 8px; border-bottom: 1px solid #E7EAF2; font-size: 14px; }
    table.items tbody td.num { text-align: right; }
    table.items tbody tr:nth-child(even) { background-color: #F8FAFD; }

    .notas { margin-bottom: 26px; font-size: 13.5px; color: #47536F; }
    .notas b { color: #1F2A44; }

    .firma { width: 100%; margin-top: 40px; }
    .firma .box { width: 46%; float: left; text-align: center; }
    .firma .box.right { float: right; }
    .firma .linea { border-top: 1.5px solid #1F2A44; margin-top: 46px; padding-top: 6px; font-size: 12px; color: #6B7A99; }

    .footer-note { margin-top: 34px; padding-top: 10px; border-top: 1px solid #E7EAF2; font-size: 11.5px; color: #8A93A6; text-align: center; }
</style>
</head>
<body>
    <div class="top-band">
        <div class="logo-cell">
            @if($logo)
            <div class="logo-plate"><img src="{{ $logo }}"></div>
            @endif
        </div>
        <div class="title-cell">
            <div class="titulo">REMITO</div>
            <div class="folio">{{ $referencia }}</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="info">
        <div class="box">
            <p><span class="label">Destinatario</span><span class="value">{{ $cliente }}</span></p>
            @if($telefono)<p><span class="label">Teléfono</span><span class="value">{{ $telefono }}</span></p>@endif
            <p>
                <span class="label">Dirección de entrega</span>
                <span class="value">
                    {{ $direccion_envio ?: ($direccion ?: 'A coordinar') }}
                    @if(!$direccion_envio)
                        @if($localidad) · {{ $localidad }}@endif{{ $provincia ? ', ' . $provincia : '' }}
                        @if($cp) (CP {{ $cp }})@endif
                    @endif
                </span>
            </p>
        </div>
        <div class="box right">
            @if($estado)<p><span class="label">Estado</span><span class="badge-est">{{ str_replace('_', ' ', $estado) }}</span></p>@endif
            <p><span class="label">Transportista</span><span class="value">{{ $transportista ?: 'A asignar' }}</span></p>
            <p><span class="label">Paga el envío</span><span class="value">{{ $pagado_por === 'empresa' ? $remitente : 'Cliente' }}</span></p>
            @if($fecha_despacho)<p><span class="label">Fecha de despacho</span><span class="value">{{ $fecha_despacho->format('d/m/Y') }}</span></p>@endif
            @if($fecha_entrega_real)<p><span class="label">Fecha de entrega</span><span class="value">{{ $fecha_entrega_real->format('d/m/Y') }}</span></p>@endif
            @if($tracking)<p><span class="label">Tracking</span><span class="value">{{ $tracking }}</span></p>@endif
        </div>
        <div class="clear"></div>
    </div>

    <p class="section-title">Detalle de lo entregado</p>
    <table class="items">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Medida</th>
                <th class="num">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item['nombre'] }}</td>
                <td>{{ $item['medida'] ?: '—' }}</td>
                <td class="num">{{ $item['cantidad'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($notas_pedido || $notas_envio)
    <div class="notas">
        @if($notas_pedido)<p><b>Obs. del pedido:</b> {{ $notas_pedido }}</p>@endif
        @if($notas_envio)<p><b>Obs. del envío:</b> {{ $notas_envio }}</p>@endif
    </div>
    @endif

    <div class="firma">
        <div class="box">
            <div class="linea">Firma</div>
        </div>
        <div class="box right">
            <div class="linea">Aclaración y DNI</div>
        </div>
        <div class="clear"></div>
    </div>

    <p class="footer-note">{{ $remitente }} · Directo de fábrica · Documento no válido como factura.</p>
</body>
</html>
