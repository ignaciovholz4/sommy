<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Cuenta corriente - {{ $cliente->nombre }} {{ $cliente->paterno }}</title>
<style>
    @page { margin: 70px 30px 50px 30px; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; color: #1B2B5A; font-size: 10.5px; }

    header {
        position: fixed; top: -50px; left: 0; right: 0; height: 40px;
        border-bottom: 2px solid #E0F2FE; padding-bottom: 8px;
    }
    header .tit { font-size: 15px; font-weight: bold; }
    header .sub { font-size: 9.5px; color: #47536F; }
    header .fecha { float: right; font-size: 9.5px; color: #47536F; }

    footer {
        position: fixed; bottom: -32px; left: 0; right: 0; height: 24px;
        border-top: 1px solid #E7EAF2; padding-top: 6px;
        font-size: 8.5px; color: #47536F; text-align: center;
    }

    h2.secc { font-size: 12px; margin: 16px 0 8px; color: #1B2B5A; }

    .kpis { width: 100%; margin-bottom: 6px; }
    .kpis td { width: 25%; padding: 8px 10px; border: 1px solid #E7EAF2; background: #F8FAFC; }
    .kpis .l { font-size: 8.5px; text-transform: uppercase; letter-spacing: .05em; color: #6E7A96; display: block; }
    .kpis .v { font-size: 14px; font-weight: bold; }
    .kpis .v.deuda { color: #b4552d; }

    table.t { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.t th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em;
        padding: 6px 8px; text-align: left;
    }
    table.t td { padding: 6px 8px; border-bottom: 1px solid #F1F4F9; font-size: 9.5px; }
    table.t .der { text-align: right; }
    table.detalle-items { width: 100%; border-collapse: collapse; margin: 0 0 10px; }
    table.detalle-items td {
        padding: 3px 8px 3px 20px; font-size: 8.5px; color: #47536F; border-bottom: none;
    }
    .tipo-cargo { color: #b4552d; font-weight: bold; }
    .tipo-pago { color: #166534; font-weight: bold; }
    .falta { color: #b4552d; font-weight: bold; }
    .cobrado { color: #0d8a4f; }
</style>
</head>
<body>
    <header>
        <div class="tit">{{ $cliente->nombre }} {{ $cliente->paterno }} {{ $cliente->materno }}</div>
        <div class="sub">{{ $cliente->telefono ?: '' }} {{ $cliente->telefono && $cliente->email ? '·' : '' }} {{ $cliente->email ?: '' }}</div>
        <div class="fecha">Generado: {{ $fecha }}</div>
    </header>

    <footer>Sommy · Estado de cuenta corriente generado desde el panel.</footer>

    @php $saldoGlobal = $saldo + $deudaVentas; @endphp
    <table class="kpis">
        <tr>
            <td><span class="l">Total cargos</span><span class="v">${{ number_format($cargos, 2, ',', '.') }}</span></td>
            <td><span class="l">Total pagos</span><span class="v">${{ number_format($pagos, 2, ',', '.') }}</span></td>
            <td><span class="l">Deuda por facturas</span><span class="v deuda">${{ number_format($deudaVentas, 2, ',', '.') }}</span></td>
            <td><span class="l">Debe total</span><span class="v deuda">${{ number_format(abs($saldoGlobal), 2, ',', '.') }}{{ $saldoGlobal < 0 ? ' (a favor)' : '' }}</span></td>
        </tr>
    </table>

    @if($ventasACobrar->isNotEmpty())
    <h2 class="secc">Facturas a cobrar — detalle</h2>
    @foreach($ventasACobrar as $v)
        <table class="t">
            <thead>
                <tr>
                    <th>Factura</th>
                    <th>Fecha</th>
                    <th class="der">Total</th>
                    <th class="der">Cobrado</th>
                    <th class="der">Falta</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight:bold;">{{ $v->num_folio ?: '#' . $v->idventa }}</td>
                    <td>{{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</td>
                    <td class="der">${{ number_format($v->total_con_iva, 2, ',', '.') }}</td>
                    <td class="der cobrado">${{ number_format($v->cobrado, 2, ',', '.') }}</td>
                    <td class="der falta">${{ number_format($v->pendiente, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        @if($v->relationLoaded('detalles') && $v->detalles->isNotEmpty())
        <table class="detalle-items">
            @foreach($v->detalles as $d)
            <tr>
                <td style="width:55%;">{{ $d->cantidad }} x {{ $d->articulo->nombre ?? 'Producto #' . $d->articulo_id }}</td>
                <td style="width:20%;text-align:right;">${{ number_format($d->precio_unitario, 2, ',', '.') }} c/u</td>
                <td style="width:25%;text-align:right;">${{ number_format($d->subtotal_con_iva, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
        @endif
    @endforeach
    @endif

    <h2 class="secc">Movimientos de cuenta corriente (cargos y pagos)</h2>
    <table class="t">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Concepto</th>
                <th>Medio</th>
                <th>Ref.</th>
                <th class="der">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $m)
            <tr>
                <td>{{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}</td>
                <td class="{{ $m->tipo === 'cargo' ? 'tipo-cargo' : 'tipo-pago' }}">{{ $m->tipo === 'cargo' ? 'Cargo' : 'Pago' }}</td>
                <td>{{ $m->concepto }}</td>
                <td>{{ $m->medio_pago ? ucfirst($m->medio_pago) : '—' }}</td>
                <td>{{ $m->referencia ?: '—' }}</td>
                <td class="der">{{ $m->tipo === 'pago' ? '-' : '' }}${{ number_format($m->monto, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:14px;color:#6E7A96;">Sin movimientos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
