@extends('layouts.admin')

@section('title', 'Rentabilidad')

@section('contenido')
<style>
    .rent-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1150px; margin: 0 auto; }
    .rent-volver { font-size: 13.5px; color: #2563EB; text-decoration: none; }
    .rent-head { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin: 10px 0 16px; }
    .rent-title { font-size: 21px; font-weight: 600; }
    .rent-sub { font-size: 13px; color: #6E7A96; font-weight: 300; }

    .rent-filtro {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.06);
        padding: 14px 18px; margin-bottom: 14px;
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    }
    .rent-filtro label { font-size: 12.5px; font-weight: 500; color: #47536F; margin: 0; }
    .rent-filtro input[type=date] { border: 1px solid #E7EAF2; border-radius: 10px; padding: 7px 12px; font-size: 13.5px; color: #1B2B5A; }
    .rent-filtro .aplicar { border: none; background: #1B2B5A; color: #fff; border-radius: 999px; padding: 8px 22px; font-size: 13px; font-weight: 500; cursor: pointer; }
    .rent-filtro .aplicar:hover { background: #2563EB; }
    .rent-descargar {
        margin-left: auto;
        display: inline-flex; align-items: center; gap: 8px;
        background: #0d8a4f; color: #fff !important; border-radius: 999px;
        padding: 9px 22px; font-size: 13px; font-weight: 500; text-decoration: none !important;
    }
    .rent-descargar:hover { background: #0b7a45; }

    .rent-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
    @media (max-width: 767px) { .rent-kpis { grid-template-columns: 1fr; } }
    .rent-kpi { background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 14px 18px; box-shadow: 0 10px 30px rgba(27,43,90,.06); }
    .rent-kpi .l { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .rent-kpi .v { font-size: 22px; font-weight: 700; }
    .rent-kpi.ganancia .v { color: #0d8a4f; }
    .rent-kpi .pct { font-size: 12px; color: #6E7A96; }

    .rent-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); overflow: hidden; margin-bottom: 16px; }
    .rent-card h3 { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #47536F; padding: 14px 18px 0; }
    .rent-table { width: 100%; border-collapse: collapse; }
    .rent-table th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
        padding: 11px 16px; text-align: left;
    }
    .rent-table td { padding: 10px 16px; border-bottom: 1px solid #F1F4F9; font-size: 13.5px; }
    .rent-table .der { text-align: right; white-space: nowrap; }
    .rent-ganancia { color: #0d8a4f; font-weight: 700; }
    .rent-vacio { padding: 30px; text-align: center; color: #6E7A96; font-weight: 300; font-size: 13px; }
</style>

<div class="rent-wrap">
    <a href="{{ url('/graph') }}?desde={{ $desde->format('Y-m-d') }}&hasta={{ $hasta->format('Y-m-d') }}" class="rent-volver"><i class="fas fa-arrow-left"></i> Volver a Informes</a>
    <div class="rent-head">
        <div>
            <div class="rent-title"><i class="fas fa-sack-dollar" style="color:#0d8a4f;"></i> Rentabilidad (venta - costo)</div>
            <div class="rent-sub">Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }} · usa el costo actual cargado en cada producto/medida</div>
        </div>
    </div>

    <form method="GET" action="{{ url('/graph/rentabilidad') }}" class="rent-filtro">
        <label>Desde</label>
        <input type="date" name="desde" value="{{ $desde->format('Y-m-d') }}">
        <label>Hasta</label>
        <input type="date" name="hasta" value="{{ $hasta->format('Y-m-d') }}">
        <button type="submit" class="aplicar">Aplicar</button>
        <a href="{{ route('graph.rentabilidad.pdf', ['desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}" target="_blank" class="rent-descargar">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </a>
    </form>

    <div class="rent-kpis">
        <div class="rent-kpi">
            <div class="l">Facturado</div>
            <div class="v">${{ number_format($totales->facturado, 2, ',', '.') }}</div>
        </div>
        <div class="rent-kpi">
            <div class="l">Costo</div>
            <div class="v">${{ number_format($totales->costo, 2, ',', '.') }}</div>
        </div>
        <div class="rent-kpi ganancia">
            <div class="l">Ganancia total</div>
            <div class="v">${{ number_format($totales->ganancia, 2, ',', '.') }}</div>
            <div class="pct">{{ number_format($totales->margen_pct, 1, ',', '.') }}% de margen</div>
        </div>
    </div>

    <div class="rent-card">
        <h3>Ganancia por cliente</h3>
        <table class="rent-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th class="der">Ventas</th>
                    <th class="der">Facturado</th>
                    <th class="der">Costo</th>
                    <th class="der">Ganancia</th>
                    <th class="der">Margen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($porCliente as $c)
                <tr>
                    <td>{{ $c->nombre }}</td>
                    <td class="der">{{ $c->ventas }}</td>
                    <td class="der">${{ number_format($c->facturado, 2, ',', '.') }}</td>
                    <td class="der">${{ number_format($c->costo, 2, ',', '.') }}</td>
                    <td class="der rent-ganancia">${{ number_format($c->ganancia, 2, ',', '.') }}</td>
                    <td class="der">{{ number_format($c->margen_pct, 1, ',', '.') }}%</td>
                </tr>
                @empty
                <tr><td colspan="6" class="rent-vacio">Sin ventas en el período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rent-card">
        <h3>Ganancia por producto</h3>
        <table class="rent-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="der">Unidades</th>
                    <th class="der">Facturado</th>
                    <th class="der">Costo</th>
                    <th class="der">Ganancia</th>
                    <th class="der">Margen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($porProducto as $p)
                <tr>
                    <td>{{ $p->nombre }}</td>
                    <td class="der">{{ $p->unidades }}</td>
                    <td class="der">${{ number_format($p->facturado, 2, ',', '.') }}</td>
                    <td class="der">${{ number_format($p->costo, 2, ',', '.') }}</td>
                    <td class="der rent-ganancia">${{ number_format($p->ganancia, 2, ',', '.') }}</td>
                    <td class="der">{{ number_format($p->margen_pct, 1, ',', '.') }}%</td>
                </tr>
                @empty
                <tr><td colspan="6" class="rent-vacio">Sin ventas en el período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
