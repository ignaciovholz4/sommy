<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido de compra {{ $folio }}</title>
    <style>
        * { font-family: Helvetica; font-size: 12px; }
        h3 { background-color: #03569F; color: #fff; text-align: center; padding: 6px; margin: 0 0 10px 0; font-size: 16px; }
        .letter { font-weight: bold; }
        .coti { padding: 3px 8px; background-color: #03569F; font-weight: bold; color: #fff; }
        .info-table { width: 100%; margin-bottom: 12px; }
        .info-table td { vertical-align: top; padding: 1px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .table th, .table td { border: 1px solid #b2b2b2; padding: 4px 5px; }
        .table th { background-color: #f0f4f8; text-align: left; }
        .num { text-align: right; }
        .totales td { border: none; }
        .totales .lbl { font-weight: bold; text-align: right; padding-right: 8px; }
        .totales .val { border: 1px solid #b2b2b2; text-align: right; width: 110px; }
        .obs { margin-top: 12px; border: 1px dashed #b2b2b2; padding: 6px 8px; background-color: #fafafa; }
        .badge-credito { color: #B45309; font-weight: bold; }
    </style>
</head>
<body>
    <h3>PEDIDO DE COMPRA</h3>

    <table class="info-table">
        <tr>
            <td style="width:55%">
                <span class="letter">Proveedor: </span>{{ $proveedor }}<br>
                @if($proveedor_dir)<span class="letter">Dirección: </span>{{ $proveedor_dir }}<br>@endif
                @if($proveedor_tel)<span class="letter">Teléfono: </span>{{ $proveedor_tel }}<br>@endif
                @if($proveedor_email)<span class="letter">Email: </span>{{ $proveedor_email }}<br>@endif
            </td>
            <td style="width:45%; text-align:right">
                <span class="coti">Folio: {{ $folio }}</span><br><br>
                <span class="letter">Fecha: </span>{{ $fecha }}<br>
                <span class="letter">Estado: </span>{{ $estado }}<br>
                <span class="letter">Sucursal de entrega: </span>{{ $sucursal }}<br>
                <span class="letter">Comprobante: </span>{{ $tipo_comprobante }}<br>
                @if($solicitante)<span class="letter">Solicitado por: </span>{{ $solicitante }}<br>@endif
                @if($a_credito)<span class="badge-credito">Compra a crédito</span>@endif
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Artículo</th>
                <th class="num">Cant.</th>
                <th class="num">Precio unit.</th>
                <th class="num">Desc.</th>
                <th class="num">IVA</th>
                <th class="num">Subtotal neto</th>
                <th class="num">Subtotal c/IVA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalle as $row)
            <tr>
                <td>{{ $row['codigo'] }}</td>
                <td>{{ $row['nombre'] }}</td>
                <td class="num">{{ $row['cantidad'] }}</td>
                <td class="num">${{ $row['precio_unitario'] }}</td>
                <td class="num">{{ $row['descuento'] }}%</td>
                <td class="num">{{ $row['iva'] }}%</td>
                <td class="num">${{ $row['subtotal_neto'] }}</td>
                <td class="num">${{ $row['subtotal_con_iva'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="table totales" style="margin-top:10px">
        <tr>
            <td class="lbl">Total neto:</td>
            <td class="val">${{ $total_neto }}</td>
        </tr>
        @foreach($iva_discriminado as $iva)
        <tr>
            <td class="lbl">IVA {{ $iva['porcentaje'] }}%:</td>
            <td class="val">${{ $iva['monto'] }}</td>
        </tr>
        @endforeach
        <tr>
            <td class="lbl" style="font-size:13px">TOTAL:</td>
            <td class="val" style="font-size:13px; font-weight:bold">${{ $total_con_iva }}</td>
        </tr>
    </table>

    @if($observaciones)
    <div class="obs">
        <span class="letter">Observaciones: </span>{{ $observaciones }}
    </div>
    @endif
</body>
</html>
