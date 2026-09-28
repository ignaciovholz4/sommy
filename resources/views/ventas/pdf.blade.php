<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Factura {{ $venta->num_folio ?: $venta->idventa }}</title>
<style>
    @page { margin: 30px 36px; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; color: #1B2B5A; font-size: 11px; }

    .top { width: 100%; border-bottom: 2px solid #1B2B5A; padding-bottom: 12px; margin-bottom: 16px; }
    .top .empresa { font-size: 17px; font-weight: bold; }
    .top .empresa-sub { font-size: 9.5px; color: #47536F; margin-top: 3px; line-height: 1.5; }
    .top .comp { float: right; text-align: right; border: 1.5px solid #1B2B5A; border-radius: 8px; padding: 10px 16px; }
    .top .comp .tipo { font-size: 14px; font-weight: bold; }
    .top .comp .folio { font-size: 13px; margin-top: 3px; }
    .top .comp .fecha { font-size: 9.5px; color: #47536F; margin-top: 3px; }

    .datos { width: 100%; margin-bottom: 16px; }
    .datos td { vertical-align: top; padding-right: 20px; font-size: 10.5px; }
    .datos .l { font-size: 8.5px; text-transform: uppercase; letter-spacing: .05em; color: #6E7A96; display: block; margin-bottom: 2px; }

    table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.items th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em;
        padding: 8px 10px; text-align: left;
    }
    table.items td { padding: 8px 10px; border-bottom: 1px solid #F1F4F9; font-size: 10.5px; }
    table.items .der { text-align: right; }

    .totales { width: 260px; float: right; margin-bottom: 16px; }
    .totales td { padding: 5px 10px; font-size: 11px; }
    .totales .der { text-align: right; }
    .totales .final td { border-top: 1.5px solid #1B2B5A; font-size: 14px; font-weight: bold; padding-top: 8px; }
    .totales .pendiente td { color: #b4552d; font-weight: bold; }
    .totales .cobrado td { color: #0d8a4f; }

    .pagos { clear: both; padding-top: 10px; }
    .pagos h4 { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #6E7A96; margin-bottom: 6px; }
    .pagos div { font-size: 10.5px; margin-bottom: 3px; }

    .anulada-stamp {
        position: fixed; top: 250px; left: 100px; font-size: 60px; font-weight: bold;
        color: rgba(180, 85, 45, 0.25); transform: rotate(-25deg); z-index: -1;
    }
</style>
</head>
<body>
    @if($venta->estado === 'anulada')
        <div class="anulada-stamp">ANULADA</div>
    @endif

    <div class="top">
        <div class="comp">
            <div class="tipo">{{ optional($venta->tipoComprobante)->descripcion ?: 'Comprobante' }}</div>
            <div class="folio">{{ $venta->num_folio ?: 'Venta #' . $venta->idventa }}</div>
            <div class="fecha">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}</div>
        </div>
        <div class="empresa">{{ $razonSocial }}</div>
        <div class="empresa-sub">
            @if($cuit)CUIT: {{ $cuit }}<br>@endif
            @if($empresa->adress){{ $empresa->adress }}<br>@endif
            @if($empresa->phone){{ $empresa->phone }}@endif
            @if($empresa->phone && $empresa->email) · @endif
            @if($empresa->email){{ $empresa->email }}@endif
        </div>
    </div>

    <table class="datos">
        <tr>
            <td>
                <span class="l">Cliente</span>
                {{ trim(optional($venta->cliente)->nombre . ' ' . optional($venta->cliente)->paterno) ?: 'Consumidor final' }}
            </td>
            <td>
                <span class="l">DNI/CUIT</span>
                {{ optional($venta->cliente)->dni_cuit ?: '—' }}
            </td>
            <td>
                <span class="l">Teléfono</span>
                {{ optional($venta->cliente)->telefono ?: '—' }}
            </td>
            <td>
                <span class="l">Sucursal</span>
                {{ optional($venta->sucursal)->nombre ?: '—' }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Medida</th>
                <th class="der">Cantidad</th>
                <th class="der">Precio unit.</th>
                <th class="der">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $d)
            <tr>
                <td>{{ optional($d->articulo)->nombre ?: 'Producto eliminado' }}</td>
                <td>{{ optional($d->combinacion)->combinacion ?: '—' }}</td>
                <td class="der">{{ $d->cantidad }}</td>
                <td class="der">${{ number_format($d->precio_unitario, 2, ',', '.') }}</td>
                <td class="der">${{ number_format($d->subtotal_con_iva, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        @if(round($venta->total_neto, 2) !== round($venta->total_con_iva, 2))
        <tr><td>Neto</td><td class="der">${{ number_format($venta->total_neto, 2, ',', '.') }}</td></tr>
        @endif
        @if($cobrado > 0.009)
        <tr class="cobrado"><td>Cobrado</td><td class="der">${{ number_format($cobrado, 2, ',', '.') }}</td></tr>
        @if($pendiente > 0.009)
        <tr class="pendiente"><td>Pendiente</td><td class="der">${{ number_format($pendiente, 2, ',', '.') }}</td></tr>
        @endif
        @endif
        <tr class="final"><td>Total</td><td class="der">${{ number_format($venta->total_con_iva, 2, ',', '.') }}</td></tr>
    </table>

    @if($venta->movimientos->isNotEmpty())
    <div class="pagos">
        <h4>Pagos registrados</h4>
        @foreach($venta->movimientos as $m)
        <div>{{ optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta' }}: ${{ number_format($m->total, 2, ',', '.') }} — {{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}</div>
        @endforeach
    </div>
    @endif
</body>
</html>
