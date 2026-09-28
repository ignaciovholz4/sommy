<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Rentabilidad</title>
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

    h2.secc { font-size: 12px; margin: 14px 0 6px; color: #1B2B5A; }

    .kpis { width: 100%; margin-bottom: 6px; }
    .kpis td { width: 33.33%; padding: 8px 10px; border: 1px solid #E7EAF2; background: #F8FAFC; }
    .kpis .l { font-size: 8.5px; text-transform: uppercase; letter-spacing: .05em; color: #6E7A96; display: block; }
    .kpis .v { font-size: 14px; font-weight: bold; }
    .kpis .v.ganancia { color: #0d8a4f; }

    table.t { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.t th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em;
        padding: 6px 8px; text-align: left;
    }
    table.t td { padding: 6px 8px; border-bottom: 1px solid #F1F4F9; font-size: 9.5px; }
    table.t .der { text-align: right; }
    .ganancia { color: #0d8a4f; font-weight: bold; }
</style>
</head>
<body>
    <header>
        <div class="tit">Rentabilidad (venta - costo)</div>
        <div class="sub">Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}</div>
        <div class="fecha">Generado: {{ $fecha }}</div>
    </header>

    <footer>Sommy · Reporte de rentabilidad generado desde el panel. Usa el costo actual cargado en cada producto/medida.</footer>

    <table class="kpis">
        <tr>
            <td><span class="l">Facturado</span><span class="v">${{ number_format($totales->facturado, 2, ',', '.') }}</span></td>
            <td><span class="l">Costo</span><span class="v">${{ number_format($totales->costo, 2, ',', '.') }}</span></td>
            <td><span class="l">Ganancia total ({{ number_format($totales->margen_pct, 1, ',', '.') }}%)</span><span class="v ganancia">${{ number_format($totales->ganancia, 2, ',', '.') }}</span></td>
        </tr>
    </table>

    <h2 class="secc">Ganancia por cliente</h2>
    <table class="t">
        <thead>
            <tr><th>Cliente</th><th class="der">Ventas</th><th class="der">Facturado</th><th class="der">Costo</th><th class="der">Ganancia</th><th class="der">Margen</th></tr>
        </thead>
        <tbody>
            @forelse($porCliente as $c)
            <tr>
                <td>{{ $c->nombre }}</td>
                <td class="der">{{ $c->ventas }}</td>
                <td class="der">${{ number_format($c->facturado, 2, ',', '.') }}</td>
                <td class="der">${{ number_format($c->costo, 2, ',', '.') }}</td>
                <td class="der ganancia">${{ number_format($c->ganancia, 2, ',', '.') }}</td>
                <td class="der">{{ number_format($c->margen_pct, 1, ',', '.') }}%</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:14px;color:#6E7A96;">Sin ventas en el período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="secc">Ganancia por producto</h2>
    <table class="t">
        <thead>
            <tr><th>Producto</th><th class="der">Unidades</th><th class="der">Facturado</th><th class="der">Costo</th><th class="der">Ganancia</th><th class="der">Margen</th></tr>
        </thead>
        <tbody>
            @forelse($porProducto as $p)
            <tr>
                <td>{{ $p->nombre }}</td>
                <td class="der">{{ $p->unidades }}</td>
                <td class="der">${{ number_format($p->facturado, 2, ',', '.') }}</td>
                <td class="der">${{ number_format($p->costo, 2, ',', '.') }}</td>
                <td class="der ganancia">${{ number_format($p->ganancia, 2, ',', '.') }}</td>
                <td class="der">{{ number_format($p->margen_pct, 1, ',', '.') }}%</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:14px;color:#6E7A96;">Sin ventas en el período.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
