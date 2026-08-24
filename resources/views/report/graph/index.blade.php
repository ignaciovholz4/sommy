@extends('layouts.admin')

@section('title', 'Informes')

@section('contenido')
<style>
    .ceo-wrap { font-family: 'Poppins', sans-serif; padding: 18px 6px; color: #1B2B5A; max-width: 1250px; margin: 0 auto; }
    .ceo-title { font-size: 21px; font-weight: 600; }
    .ceo-sub { font-size: 13px; color: #6E7A96; font-weight: 300; margin-bottom: 16px; }

    /* Filtro de período */
    .ceo-filtro {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.06);
        padding: 14px 18px; margin-bottom: 16px;
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    }
    .ceo-filtro label { font-size: 12.5px; font-weight: 500; color: #47536F; margin: 0; }
    .ceo-filtro input[type=date] {
        border: 1px solid #E7EAF2; border-radius: 10px; padding: 7px 12px;
        font-size: 13.5px; color: #1B2B5A; font-family: 'Poppins', sans-serif;
    }
    .ceo-filtro .aplicar {
        border: none; background: #1B2B5A; color: #fff; border-radius: 999px;
        padding: 8px 22px; font-size: 13px; font-weight: 500; cursor: pointer;
    }
    .ceo-filtro .aplicar:hover { background: #2563EB; }
    .ceo-preset {
        font-size: 12px; font-weight: 500; color: #47536F; text-decoration: none;
        border: 1px solid #E7EAF2; border-radius: 999px; padding: 6px 14px;
        transition: all .15s;
    }
    .ceo-preset:hover, .ceo-preset.on { background: #E0F2FE; border-color: #bae2f8; color: #1B2B5A; }

    /* Pestañas */
    .ceo-tabs { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
    .ceo-tab {
        border: 1.5px solid #E7EAF2; background: #fff; color: #47536F;
        border-radius: 999px; padding: 9px 22px; font-size: 13.5px; font-weight: 500;
        cursor: pointer; transition: all .15s; font-family: 'Poppins', sans-serif;
    }
    .ceo-tab:hover { border-color: #1B2B5A; color: #1B2B5A; }
    .ceo-tab.on { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }
    .ceo-pane { display: none; }
    .ceo-pane.on { display: block; }

    /* KPIs */
    .ceo-kpis { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 16px; }
    @media (max-width: 1300px) { .ceo-kpis { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 767px)  { .ceo-kpis { grid-template-columns: repeat(2, 1fr); } }
    .ceo-kpi {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        padding: 14px 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06);
    }
    .ceo-kpi .k-label { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .ceo-kpi .k-value { font-size: 20px; font-weight: 700; margin: 3px 0 2px; white-space: nowrap; }
    .ceo-kpi .k-delta { font-size: 11.5px; font-weight: 500; display: block; }
    .k-delta.up { color: #0d8a4f; } .k-delta.down { color: #b4552d; } .k-delta.flat { color: #6E7A96; }

    /* Paneles */
    .ceo-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 12px; }
    @media (max-width: 991px) { .ceo-grid { grid-template-columns: 1fr; } }
    .ceo-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
    @media (max-width: 991px) { .ceo-grid-2 { grid-template-columns: 1fr; } }
    .ceo-panel {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px;
    }
    .ceo-panel h3 { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #47536F; margin-bottom: 12px; }
    .ceo-panel .empty { font-size: 13px; color: #6E7A96; font-weight: 300; padding: 20px 0; text-align: center; }

    .ceo-list { list-style: none; margin: 0; padding: 0; }
    .ceo-list li { display: flex; justify-content: space-between; gap: 10px; padding: 7px 0; border-bottom: 1px solid #F1F4F9; font-size: 13px; }
    .ceo-list li:last-child { border-bottom: none; }
    .ceo-list .n { color: #1B2B5A; font-weight: 500; }
    .ceo-list .v { color: #47536F; white-space: nowrap; }
    .ceo-list .crit { color: #b4552d; font-weight: 600; }
    .ceo-link { font-size: 12.5px; color: #2563EB; text-decoration: none; }
</style>

@php
    $money = fn ($v) => '$' . number_format($v, 0, ',', '.');
    $deltaBadge = function ($delta) {
        if (is_null($delta)) return '<span class="k-delta flat">— sin datos previos</span>';
        $clase = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat');
        $flecha = $delta > 0 ? '▲' : ($delta < 0 ? '▼' : '=');
        return '<span class="k-delta ' . $clase . '">' . $flecha . ' ' . number_format(abs($delta), 1, ',', '.') . '% vs período anterior</span>';
    };
    $desdeStr = $desde->format('Y-m-d');
    $hastaStr = $hasta->format('Y-m-d');
    $preset = fn ($d, $h) => url('/graph') . '?desde=' . $d . '&hasta=' . $h;
@endphp

<div class="ceo-wrap">
    <div class="ceo-title"><i class="fas fa-chart-pie" style="color:#2563EB;"></i> Informes del negocio</div>
    <div class="ceo-sub">Período analizado: <strong>{{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</strong> ({{ $dias }} día{{ $dias > 1 ? 's' : '' }})</div>

    {{-- Filtro por período con calendario --}}
    <form method="GET" action="{{ url('/graph') }}" class="ceo-filtro">
        <label>Desde</label>
        <input type="date" name="desde" value="{{ $desdeStr }}">
        <label>Hasta</label>
        <input type="date" name="hasta" value="{{ $hastaStr }}">
        <button type="submit" class="aplicar"><i class="fas fa-filter"></i> Aplicar</button>
        <span style="width:1px;height:22px;background:#E7EAF2;"></span>
        <a class="ceo-preset" href="{{ $preset(now()->format('Y-m-d'), now()->format('Y-m-d')) }}">Hoy</a>
        <a class="ceo-preset" href="{{ $preset(now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d')) }}">7 días</a>
        <a class="ceo-preset" href="{{ $preset(now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')) }}">Este mes</a>
        <a class="ceo-preset" href="{{ $preset(now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')) }}">Mes pasado</a>
        <a class="ceo-preset" href="{{ $preset(now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')) }}">Este año</a>
        <a href="{{ url('/graph/movimientos') }}?desde={{ $desdeStr }}&hasta={{ $hastaStr }}"
           style="margin-left:auto;display:inline-flex;align-items:center;gap:8px;background:#1B2B5A;color:#fff;border-radius:999px;padding:9px 22px;font-size:13px;font-weight:500;text-decoration:none;">
            <i class="fas fa-book"></i> Ver todos los movimientos
        </a>
    </form>

    {{-- Pestañas --}}
    <div class="ceo-tabs">
        <button class="ceo-tab on" data-pane="resumen"><i class="fas fa-gauge-high"></i> Resumen</button>
        <button class="ceo-tab" data-pane="ventas"><i class="fas fa-hand-holding-usd"></i> Ventas y productos</button>
        <button class="ceo-tab" data-pane="clientes"><i class="fas fa-users"></i> Clientes y cuentas</button>
        <button class="ceo-tab" data-pane="stock"><i class="fas fa-boxes"></i> Stock</button>
        <button class="ceo-tab" data-pane="finanzas"><i class="fas fa-sack-dollar"></i> Finanzas</button>
    </div>

    {{-- ═══ PESTAÑA RESUMEN ═══ --}}
    <div class="ceo-pane on" id="pane-resumen">
        <div class="ceo-kpis">
            <div class="ceo-kpi">
                <div class="k-label">Facturación</div>
                <div class="k-value">{{ $money($kpis['facturacion']['valor']) }}</div>
                {!! $deltaBadge($kpis['facturacion']['delta']) !!}
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Ventas</div>
                <div class="k-value">{{ $kpis['ventas']['valor'] }}</div>
                {!! $deltaBadge($kpis['ventas']['delta']) !!}
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Ticket promedio</div>
                <div class="k-value">{{ $money($kpis['ticket']['valor']) }}</div>
                {!! $deltaBadge($kpis['ticket']['delta']) !!}
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Pedidos web</div>
                <div class="k-value">{{ $kpis['pedidosWeb']['valor'] }}</div>
                {!! $deltaBadge($kpis['pedidosWeb']['delta']) !!}
                @if($kpis['pedidosWeb']['pendientes'] > 0)
                    <span class="k-delta down">{{ $kpis['pedidosWeb']['pendientes'] }} pendiente(s) ahora</span>
                @endif
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Margen bruto (est.)</div>
                <div class="k-value">{{ $money($kpis['margen']['valor']) }}</div>
                <span class="k-delta {{ $kpis['margen']['pct'] >= 30 ? 'up' : 'flat' }}">{{ number_format($kpis['margen']['pct'], 1, ',', '.') }}% sobre venta</span>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Compras</div>
                <div class="k-value">{{ $money($kpis['compras']['valor']) }}</div>
                <span class="k-delta flat">egresos a proveedores</span>
            </div>
        </div>

        <div class="ceo-grid">
            <div class="ceo-panel">
                <h3>Evolución en el período</h3>
                <canvas id="chartEvolucion" height="105"></canvas>
            </div>
            <div class="ceo-panel">
                <h3>Pedidos por canal</h3>
                @if($pedidosPorCanal->isEmpty())
                    <div class="empty">Sin pedidos en el período.</div>
                @else
                    <canvas id="chartCanales" height="170"></canvas>
                    <ul class="ceo-list" style="margin-top:10px;">
                        @foreach($pedidosPorCanal as $canal)
                        <li><span class="n">{{ $canal->nombre }}</span><span class="v">{{ $canal->pedidos }} · {{ $money($canal->facturado) }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ PESTAÑA VENTAS Y PRODUCTOS ═══ --}}
    <div class="ceo-pane" id="pane-ventas">
        <div class="ceo-grid-2">
            <div class="ceo-panel">
                <h3>Facturación por categoría</h3>
                @if($mixCategorias->isEmpty())
                    <div class="empty">Sin ventas en el período.</div>
                @else
                    <canvas id="chartCategorias" height="190"></canvas>
                @endif
            </div>
            <div class="ceo-panel">
                <h3>¿Qué medida se vende más?</h3>
                @if($mixPlazas->isEmpty())
                    <div class="empty">Sin datos de plazas en el período.</div>
                @else
                    <canvas id="chartPlazas" height="190"></canvas>
                @endif
            </div>
        </div>
        <div class="ceo-panel">
            <h3>Top productos del período</h3>
            @if($topProductos->isEmpty())
                <div class="empty">Sin ventas en el período.</div>
            @else
                <ul class="ceo-list">
                    @foreach($topProductos as $tp)
                    <li>
                        <span class="n">{{ $loop->iteration }}. {{ \Illuminate\Support\Str::limit($tp->nombre, 45) }}</span>
                        <span class="v">{{ $tp->unidades }} u · {{ $money($tp->facturado) }}</span>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- ═══ PESTAÑA CLIENTES Y CUENTAS ═══ --}}
    <div class="ceo-pane" id="pane-clientes">
        <div class="ceo-kpis" style="grid-template-columns: repeat(3, 1fr);">
            <div class="ceo-kpi">
                <div class="k-label">Clientes registrados</div>
                <div class="k-value">{{ $clientesTotal }}</div>
                <span class="k-delta flat">{{ $clientesConCuenta }} con cuenta online</span>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Deuda de clientes (CC)</div>
                <div class="k-value" style="color:{{ $deudaCC > 0 ? '#b4552d' : '#0d8a4f' }};">{{ $money($deudaCC) }}</div>
                <a class="ceo-link" href="{{ url('/cc') }}">Ir a cuentas corrientes →</a>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Ticket promedio del período</div>
                <div class="k-value">{{ $money($kpis['ticket']['valor']) }}</div>
                {!! $deltaBadge($kpis['ticket']['delta']) !!}
            </div>
        </div>

        <div class="ceo-grid-2">
            <div class="ceo-panel">
                <h3>Mejores clientes del período</h3>
                @if($topClientes->isEmpty())
                    <div class="empty">Sin ventas con cliente en el período.</div>
                @else
                    <ul class="ceo-list">
                        @foreach($topClientes as $tc)
                        <li>
                            <span class="n">{{ $loop->iteration }}. {{ $tc->nombre }}</span>
                            <span class="v">{{ $tc->compras }} compra(s) · {{ $money($tc->facturado) }}</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="ceo-panel">
                <h3>Principales deudores (cuenta corriente)</h3>
                @if($topDeudores->isEmpty())
                    <div class="empty">Nadie debe nada. 🎉</div>
                @else
                    <ul class="ceo-list">
                        @foreach($topDeudores as $td)
                        <li>
                            <span class="n"><a class="ceo-link" href="{{ url('/cc/cliente/' . $td->idcliente) }}">{{ $td->nombre }}</a></span>
                            <span class="v crit">{{ $money($td->saldo) }}</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ PESTAÑA STOCK ═══ --}}
    <div class="ceo-pane" id="pane-stock">
        <div class="ceo-kpis" style="grid-template-columns: repeat(3, 1fr);">
            <div class="ceo-kpi">
                <div class="k-label">Unidades en stock</div>
                <div class="k-value">{{ number_format($inv->unidades, 0, ',', '.') }}</div>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Valor a costo</div>
                <div class="k-value">{{ $money($inv->valor_costo) }}</div>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Valor a precio de venta</div>
                <div class="k-value">{{ $money($inv->valor_venta) }}</div>
            </div>
        </div>

        <div class="ceo-grid-2">
            <div class="ceo-panel">
                <h3>Stock por categoría</h3>
                @if($stockPorCategoria->isEmpty())
                    <div class="empty">Sin stock cargado.</div>
                @else
                    <ul class="ceo-list">
                        @foreach($stockPorCategoria as $sc)
                        <li><span class="n">{{ $sc->nombre }}</span><span class="v">{{ $sc->unidades }} u · {{ $money($sc->valor) }} (a venta)</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="ceo-panel">
                <h3>Stock crítico (≤ 3 unidades)</h3>
                @if($stockCritico->isEmpty())
                    <div class="empty">Sin productos en nivel crítico. ✔</div>
                @else
                    <ul class="ceo-list">
                        @foreach($stockCritico as $sc)
                        <li><span class="n">{{ \Illuminate\Support\Str::limit($sc->nombre, 40) }}</span><span class="v crit">{{ $sc->stock }} u</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ PESTAÑA FINANZAS ═══ --}}
    <div class="ceo-pane" id="pane-finanzas">
        <div class="ceo-kpis">
            <div class="ceo-kpi">
                <div class="k-label">Saldo total en cuentas</div>
                <div class="k-value" style="color:{{ $saldoTotalCuentas >= 0 ? '#0d8a4f' : '#b4552d' }};">{{ $money($saldoTotalCuentas) }}</div>
                <span class="k-delta flat">caja + bancos, foto actual</span>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Ingresos del período</div>
                <div class="k-value">{{ $money($ingresosPeriodo) }}</div>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Egresos del período</div>
                <div class="k-value">{{ $money($egresosPeriodo) }}</div>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Resultado del período</div>
                <div class="k-value" style="color:{{ $resultadoPeriodo >= 0 ? '#0d8a4f' : '#b4552d' }};">{{ $money($resultadoPeriodo) }}</div>
                <span class="k-delta flat">ingresos − egresos</span>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Gastos operativos del período</div>
                <div class="k-value">{{ $money($gastosPeriodo) }}</div>
                <a class="ceo-link" href="{{ url('/finanzas/gastos') }}">Cargar gasto →</a>
            </div>
            <div class="ceo-kpi">
                <div class="k-label">Devoluciones del período</div>
                <div class="k-value" style="color:{{ $devolucionesPeriodo->monto > 0 ? '#b4552d' : '#0d8a4f' }};">{{ $money($devolucionesPeriodo->monto) }}</div>
                <span class="k-delta flat">{{ $devolucionesPeriodo->cantidad }} devolución(es)</span>
            </div>
        </div>

        <div class="ceo-grid">
            <div class="ceo-panel">
                <h3>Ingresos vs. egresos en el período</h3>
                <canvas id="chartTesoreria" height="105"></canvas>
            </div>
            <div class="ceo-panel">
                <h3>Gastos por categoría del período</h3>
                @if($gastosPorCategoriaPeriodo->isEmpty())
                    <div class="empty">Sin gastos cargados en el período. <br><a class="ceo-link" href="{{ url('/finanzas/gastos') }}">Cargar el primero →</a></div>
                @else
                    <canvas id="chartGastos" height="170"></canvas>
                    <ul class="ceo-list" style="margin-top:10px;">
                        @foreach($gastosPorCategoriaPeriodo as $gc)
                        <li><span class="n">{{ $gc->nombre }}</span><span class="v">{{ $money($gc->total) }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="ceo-grid-2">
            <div class="ceo-panel">
                <h3>Cuentas por pagar — vencidas ({{ $money($cxpVencidoTotal) }})</h3>
                @if($cxpVencidas->isEmpty())
                    <div class="empty">No hay deuda vencida con proveedores. ✔</div>
                @else
                    <ul class="ceo-list">
                        @foreach($cxpVencidas as $cv)
                        <li>
                            <span class="n">{{ $cv->nombre }}</span>
                            <span class="v crit">{{ $money($cv->monto) }} · vence {{ \Carbon\Carbon::parse($cv->vencimiento)->format('d/m/Y') }}</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
                <a class="ceo-link" href="{{ route('finanzas.dashboard') }}">Ver dashboard financiero completo →</a>
            </div>
            <div class="ceo-panel">
                <h3>Cuentas por pagar — próximos 30 días ({{ $money($cxpProximasTotal) }})</h3>
                @if($cxpProximas->isEmpty())
                    <div class="empty">Sin vencimientos en los próximos 30 días.</div>
                @else
                    <ul class="ceo-list">
                        @foreach($cxpProximas as $cp)
                        <li>
                            <span class="n">{{ $cp->nombre }}</span>
                            <span class="v">{{ $money($cp->monto) }} · vence {{ \Carbon\Carbon::parse($cp->vencimiento)->format('d/m/Y') }}</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
                <a class="ceo-link" href="{{ url('/cc') }}">Ver cuenta corriente de clientes →</a>
            </div>
        </div>

        @if($comparativaProveedores->isNotEmpty())
        <div class="ceo-panel">
            <h3>Comparación de precios de proveedores por categoría (últimos 12 meses)</h3>
            <div class="ceo-grid-2">
                @foreach($comparativaProveedores as $categoria => $filas)
                <div>
                    <div style="font-weight:600;font-size:12.5px;color:#1B2B5A;margin-bottom:4px;">{{ $categoria }}</div>
                    <ul class="ceo-list">
                        @foreach($filas as $i => $f)
                        <li>
                            <span class="n">{{ $f->proveedor }} @if($i === 0)<span style="color:#0d8a4f;font-weight:700;">· más barato</span>@endif</span>
                            <span class="v">{{ $money($f->precio_promedio) }}/u · {{ (int) $f->unidades }} u</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Pestañas ──
    var tabs = document.querySelectorAll('.ceo-tab');
    var iniciados = {};
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('on'); });
            document.querySelectorAll('.ceo-pane').forEach(function (p) { p.classList.remove('on'); });
            tab.classList.add('on');
            var pane = document.getElementById('pane-' + tab.dataset.pane);
            if (pane) pane.classList.add('on');
            iniciarCharts(tab.dataset.pane);
        });
    });

    // ── Charts (se inician recién cuando su pestaña se muestra) ──
    var NAVY = '#1B2B5A', AQUA = '#0EA5E9';
    var PALETA = [NAVY, '#2563EB', AQUA, '#7FB8E6', '#B9C3DE', '#47536F'];
    var fmt = function (v) { return '$' + Number(v).toLocaleString('es-AR'); };

    function dona(id, labels, data, esDinero) {
        var el = document.getElementById(id);
        if (!el) return;
        new Chart(el, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: PALETA, borderWidth: 2, borderColor: '#fff' }] },
            options: {
                responsive: true, cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Poppins', size: 11 } } },
                    tooltip: { callbacks: { label: function (c) { return c.label + ': ' + (esDinero ? fmt(c.parsed) : c.parsed + ' u'); } } }
                }
            }
        });
    }

    function iniciarCharts(pane) {
        if (iniciados[pane]) return;
        iniciados[pane] = true;

        if (pane === 'resumen') {
            var evo = document.getElementById('chartEvolucion');
            if (evo) {
                new Chart(evo, {
                    type: 'bar',
                    data: {
                        labels: @json($labelsEvolucion),
                        datasets: [
                            { label: 'Ventas', data: @json($serieVentas), backgroundColor: NAVY, borderRadius: 5 },
                            { label: 'Pedidos web', data: @json($serieWeb), backgroundColor: AQUA, borderRadius: 5 }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Poppins' } } },
                            tooltip: { callbacks: { label: function (c) { return c.dataset.label + ': ' + fmt(c.parsed.y); } } }
                        },
                        scales: {
                            y: { ticks: { callback: function (v) { return fmt(v); }, font: { family: 'Poppins', size: 10 } }, grid: { color: '#F1F4F9' } },
                            x: { grid: { display: false }, ticks: { font: { family: 'Poppins', size: 10 }, maxTicksLimit: 16 } }
                        }
                    }
                });
            }
            dona('chartCanales', @json($pedidosPorCanal->pluck('nombre')), @json($pedidosPorCanal->pluck('facturado')), true);
        }

        if (pane === 'ventas') {
            dona('chartCategorias', @json($mixCategorias->pluck('nombre')), @json($mixCategorias->pluck('facturado')), true);
            dona('chartPlazas', @json($mixPlazas->pluck('plazas')), @json($mixPlazas->pluck('unidades')), false);
        }

        if (pane === 'finanzas') {
            var tes = document.getElementById('chartTesoreria');
            if (tes) {
                new Chart(tes, {
                    type: 'bar',
                    data: {
                        labels: @json($labelsEvolucion),
                        datasets: [
                            { label: 'Ingresos', data: @json($serieIngresos), backgroundColor: '#0d8a4f', borderRadius: 5 },
                            { label: 'Egresos', data: @json($serieEgresos), backgroundColor: '#b4552d', borderRadius: 5 }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Poppins' } } },
                            tooltip: { callbacks: { label: function (c) { return c.dataset.label + ': ' + fmt(c.parsed.y); } } }
                        },
                        scales: {
                            y: { ticks: { callback: function (v) { return fmt(v); }, font: { family: 'Poppins', size: 10 } }, grid: { color: '#F1F4F9' } },
                            x: { grid: { display: false }, ticks: { font: { family: 'Poppins', size: 10 }, maxTicksLimit: 16 } }
                        }
                    }
                });
            }
            dona('chartGastos', @json($gastosPorCategoriaPeriodo->pluck('nombre')), @json($gastosPorCategoriaPeriodo->pluck('total')), true);
        }
    }

    iniciarCharts('resumen');
});
</script>
@endsection
