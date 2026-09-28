<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Cuentas corrientes de clientes</title>
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

    .resumen { text-align: right; margin-bottom: 14px; }
    .resumen .l { font-size: 9px; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .resumen .v { font-size: 18px; font-weight: bold; color: #b4552d; }

    table.cc { width: 100%; border-collapse: collapse; }
    table.cc th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em;
        padding: 7px 8px; text-align: left;
    }
    table.cc td { padding: 7px 8px; border-bottom: 1px solid #F1F4F9; font-size: 10px; }
    table.cc .der { text-align: right; }
    .pos { color: #b4552d; font-weight: bold; }
    .cero { color: #0d8a4f; }
</style>
</head>
<body>
    <header>
        <div class="tit">Cuentas corrientes de clientes</div>
        <div class="sub">Sommy @if($q !== '') · Búsqueda: "{{ $q }}" @endif</div>
        <div class="fecha">Generado: {{ $fecha }}</div>
    </header>

    <footer>Sommy · Estado de cuentas corrientes generado desde el panel.</footer>

    <div class="resumen">
        <div class="l">Deuda total de clientes</div>
        <div class="v">${{ number_format($totalDeuda, 2, ',', '.') }}</div>
    </div>

    <table class="cc">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Contacto</th>
                <th class="der">Cargos</th>
                <th class="der">Pagos</th>
                <th class="der">Ventas a cobrar</th>
                <th class="der">Debe (total)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $c)
            <tr>
                <td>{{ $c->nombre }} {{ $c->paterno }} {{ $c->materno }}</td>
                <td style="color:#6E7A96;">{{ $c->telefono ?: $c->email }}</td>
                <td class="der">${{ number_format($c->cargos, 2, ',', '.') }}</td>
                <td class="der">${{ number_format($c->pagos, 2, ',', '.') }}</td>
                <td class="der">${{ number_format($c->ventas_pendiente, 2, ',', '.') }}</td>
                <td class="der {{ $c->saldo_total > 0 ? 'pos' : 'cero' }}">
                    ${{ number_format($c->saldo_total, 2, ',', '.') }}
                    @if($c->saldo_total < 0) (a favor) @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:20px;color:#6E7A96;">Sin clientes con movimientos.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
