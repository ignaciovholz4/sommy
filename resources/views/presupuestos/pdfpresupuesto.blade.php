<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto {{ $folio }}</title>
    <style>
        * { font-family: Helvetica, Arial, sans-serif; box-sizing: border-box; }
        body { color: #1F2A44; font-size: 15px; margin: 0; padding: 0 34px 28px; }

        /* ── Encabezado oscuro (estilo "Somos fabricantes") ────── */
        .top-band {
            background-color: #0C1428;
            margin: 0 -34px 24px;
            padding: 26px 34px 24px;
            border-radius: 0 0 18px 18px;
        }
        .top-band .logo-plate {
            display: inline-block;
            background-color: #fff;
            border-radius: 999px;
            padding: 8px 18px;
        }
        .top-band .logo-plate img { height: 34px; }
        .top-band .title-cell { width: 45%; float: right; text-align: right; margin-top: 4px; }
        .top-band .logo-cell { width: 55%; float: left; }
        .top-band .title-cell .titulo { font-size: 22px; font-weight: bold; color: #fff; letter-spacing: 1.5px; }
        .top-band .title-cell .folio { display: inline-block; margin-top: 8px; padding: 6px 16px; background-color: #C9A227; color: #0C1428; font-size: 13px; font-weight: bold; border-radius: 999px; }
        .clear { clear: both; }

        /* ── Datos cliente / presupuesto ───────────────────────── */
        .info { width: 100%; margin-bottom: 22px; }
        .info .box { width: 48%; float: left; background-color: #F4F7FB; border-radius: 10px; padding: 16px 18px; }
        .info .box.right { float: right; }
        .info .box p { margin: 0 0 8px 0; }
        .info .box p:last-child { margin-bottom: 0; }
        .label { color: #6B7A99; font-size: 11.5px; text-transform: uppercase; letter-spacing: .4px; display: block; }
        .value { font-size: 15px; font-weight: bold; color: #1F2A44; }

        /* ── Tabla de artículos ─────────────────────────────────── */
        .section-title { font-size: 15px; font-weight: bold; color: #1B2B5A; margin: 0 0 10px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.items thead th { background-color: #1B2B5A; color: #fff; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; padding: 10px 8px; text-align: left; }
        table.items thead th.num { text-align: right; }
        table.items tbody td { padding: 10px 8px; border-bottom: 1px solid #E7EAF2; font-size: 14px; vertical-align: middle; }
        table.items tbody td.num { text-align: right; }
        table.items tbody tr:nth-child(even) { background-color: #F8FAFD; }
        table.items td.foto { width: 60px; }
        table.items td.foto img { width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid #E7EAF2; }
        table.items td.foto .sin-foto { width: 52px; height: 52px; border-radius: 8px; background: #F1F4F9; }
        .muted { color: #8A93A6; }

        /* ── Totales ─────────────────────────────────────────────── */
        .totales { width: 100%; }
        .totales .box { width: 46%; float: right; }
        .totales table { width: 100%; border-collapse: collapse; }
        .totales td { padding: 7px 10px; font-size: 14px; }
        .totales td.lbl { color: #6B7A99; }
        .totales td.val { text-align: right; }
        .totales tr.total td { border-top: 2px solid #1B2B5A; padding-top: 12px; font-size: 17px; font-weight: bold; color: #1B2B5A; }

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
            <div class="titulo">PRESUPUESTO</div>
            <div class="folio">{{ $folio }}</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="info">
        <div class="box">
            <p><span class="label">Cliente</span><span class="value">{{ $cliente }}</span></p>
            @if($direccion)<p><span class="label">Dirección</span><span class="value">{{ $direccion }}</span></p>@endif
            @if($telefono)<p><span class="label">Teléfono</span><span class="value">{{ $telefono }}</span></p>@endif
        </div>
        <div class="box right">
            <p><span class="label">Fecha</span><span class="value">{{ $fecha }}</span></p>
        </div>
        <div class="clear"></div>
    </div>

    <p class="section-title">Lista de artículos</p>
    <table class="items">
        <thead>
            <tr>
                <th></th>
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
                <td class="foto">
                    @if($row['imagen'])
                        <img src="{{ $row['imagen'] }}">
                    @else
                        <div class="sin-foto"></div>
                    @endif
                </td>
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
