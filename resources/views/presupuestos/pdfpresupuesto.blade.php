<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto {{ $folio }}</title>
    <style>
        * { font-family: Helvetica, Arial, sans-serif; box-sizing: border-box; }
        body { color: #1F2A44; font-size: 13px; margin: 0; padding: 28px 34px; }

        /* ── Encabezado ─────────────────────────────────────────── */
        .header { width: 100%; margin-bottom: 22px; }
        .header .logo-cell { width: 55%; float: left; }
        .header .logo-cell img { height: 46px; }
        .header .title-cell { width: 45%; float: left; text-align: right; }
        .header .title-cell .titulo { font-size: 22px; font-weight: bold; color: #03569F; letter-spacing: .5px; }
        .header .title-cell .folio { display: inline-block; margin-top: 6px; padding: 5px 12px; background-color: #03569F; color: #fff; font-size: 12px; font-weight: bold; border-radius: 3px; }
        .clear { clear: both; }
        .divider { border-bottom: 2px solid #03569F; margin-bottom: 18px; }

        /* ── Datos cliente / presupuesto ───────────────────────── */
        .info { width: 100%; margin-bottom: 22px; }
        .info .box { width: 48%; float: left; background-color: #F4F7FB; border-radius: 6px; padding: 14px 16px; }
        .info .box.right { float: right; }
        .info .box p { margin: 0 0 6px 0; }
        .info .box p:last-child { margin-bottom: 0; }
        .label { color: #6B7A99; font-size: 10.5px; text-transform: uppercase; letter-spacing: .4px; display: block; }
        .value { font-size: 13px; font-weight: bold; color: #1F2A44; }
        .badge-estado { display: inline-block; padding: 2px 9px; border-radius: 10px; background-color: #E7EEF8; color: #03569F; font-weight: bold; font-size: 11px; text-transform: capitalize; }

        /* ── Tabla de artículos ─────────────────────────────────── */
        .section-title { font-size: 13px; font-weight: bold; color: #1F2A44; margin: 0 0 8px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.items thead th { background-color: #03569F; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; padding: 9px 8px; text-align: left; }
        table.items thead th.num { text-align: right; }
        table.items tbody td { padding: 8px; border-bottom: 1px solid #E7EAF2; font-size: 12.5px; }
        table.items tbody td.num { text-align: right; }
        table.items tbody tr:nth-child(even) { background-color: #F8FAFD; }
        .muted { color: #8A93A6; }

        /* ── Totales ─────────────────────────────────────────────── */
        .totales { width: 100%; }
        .totales .box { width: 46%; float: right; }
        .totales table { width: 100%; border-collapse: collapse; }
        .totales td { padding: 6px 10px; font-size: 12.5px; }
        .totales td.lbl { color: #6B7A99; }
        .totales td.val { text-align: right; }
        .totales tr.total td { border-top: 2px solid #03569F; padding-top: 10px; font-size: 15px; font-weight: bold; color: #03569F; }

        .footer-note { margin-top: 34px; padding-top: 10px; border-top: 1px solid #E7EAF2; font-size: 10.5px; color: #8A93A6; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-cell">
            @if(is_file($logo))<img src="{{ $logo }}">@endif
        </div>
        <div class="title-cell">
            <div class="titulo">PRESUPUESTO</div>
            <div class="folio">{{ $folio }}</div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="divider"></div>

    <div class="info">
        <div class="box">
            <p><span class="label">Cliente</span><span class="value">{{ $cliente }}</span></p>
            @if($direccion)<p><span class="label">Dirección</span><span class="value">{{ $direccion }}</span></p>@endif
            @if($telefono)<p><span class="label">Teléfono</span><span class="value">{{ $telefono }}</span></p>@endif
            @if($email)<p><span class="label">Email</span><span class="value">{{ $email }}</span></p>@endif
        </div>
        <div class="box right">
            <p><span class="label">Fecha</span><span class="value">{{ $fecha }}</span></p>
            <p><span class="label">Estado</span><span class="badge-estado">{{ $estado }}</span></p>
        </div>
        <div class="clear"></div>
    </div>

    <p class="section-title">Lista de artículos</p>
    <table class="items">
        <thead>
            <tr>
                <th>Código</th>
                <th>Artículo</th>
                <th>Medida</th>
                <th class="num">Cantidad</th>
                <th class="num">Precio Unitario</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalle as $row)
            <tr>
                <td>{{ $row['codigo'] }}</td>
                <td>{{ $row['nombre'] }}</td>
                <td>{{ $row['medida'] ?: '—' }}</td>
                <td class="num">{{ $row['cantidad'] }}</td>
                <td class="num">${{ $row['precio_unitario'] }}</td>
                <td class="num">${{ $row['subtotal_con_iva'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <div class="box">
            <table>
                @if($tieneIva)
                <tr>
                    <td class="lbl">Subtotal Neto</td>
                    <td class="val">${{ $total_neto }}</td>
                </tr>
                @foreach($iva_discriminado as $iva)
                <tr>
                    <td class="lbl">IVA {{ $iva['porcentaje'] }}%</td>
                    <td class="val">${{ $iva['monto'] }}</td>
                </tr>
                @endforeach
                @endif
                <tr class="total">
                    <td>Total</td>
                    <td class="val">${{ $total_con_iva }}</td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>

    <p class="footer-note">Sommy · Presupuesto sujeto a disponibilidad de stock y modificación sin previo aviso.</p>
</body>
</html>
