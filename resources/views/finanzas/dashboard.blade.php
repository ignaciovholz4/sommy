@extends('layouts.admin')

@section('title', 'Tablero financiero')

@section('contenido')
<style>
    .fin-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1250px; margin: 0 auto; }
    .fin-title { font-size: 21px; font-weight: 600; margin-bottom: 16px; }

    .fin-kpis { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 16px; }
    @media (max-width: 1199px) { .fin-kpis { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 767px) { .fin-kpis { grid-template-columns: repeat(2, 1fr); } }
    .fin-kpi { background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 14px 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); }
    .fin-kpi .l { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .fin-kpi .v { font-size: 19px; font-weight: 700; margin-top: 2px; white-space: nowrap; }
    .fin-kpi .v.pos { color: #0d8a4f; }
    .fin-kpi .v.neg { color: #c0392b; }
    .fin-kpi .v.deuda { color: #b4552d; }
    .fin-kpi .v.azul { color: #2563EB; }

    .fin-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 16px; }
    @media (max-width: 991px) { .fin-grid { grid-template-columns: 1fr; } }
    .fin-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px; }
    .fin-card h3 { font-size: 14px; font-weight: 600; margin: 0 0 12px; }
    .fin-card .chart-box { position: relative; height: 300px; }
    .fin-card .chart-box.chico { height: 260px; }
    .fin-vacio { color: #6E7A96; font-weight: 300; font-size: 13.5px; text-align: center; padding: 40px 0; }

    .fin-tabla { width: 100%; border-collapse: collapse; }
    .fin-tabla th { background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; padding: 10px 12px; text-align: left; }
    .fin-tabla td { padding: 10px 12px; border-bottom: 1px solid #F1F4F9; font-size: 13px; }
    .fin-tabla .der { text-align: right; }
    .fin-badge { display: inline-block; border-radius: 999px; font-size: 11px; font-weight: 600; padding: 3px 12px; }
    .fin-badge.vencida { background: #FDECEA; color: #c0392b; }
    .fin-badge.proxima { background: #FEF6E7; color: #9a6b0f; }
</style>

<div class="fin-wrap">
    <div class="fin-title"><i class="fas fa-chart-line" style="color:#2563EB;"></i> Tablero financiero</div>

    {{-- KPIs --}}
    <div class="fin-kpis">
        <div class="fin-kpi">
            <div class="l">Ingresos del mes</div>
            <div class="v pos">${{ number_format($ingresosMes, 2, ',', '.') }}</div>
        </div>
        <div class="fin-kpi">
            <div class="l">Egresos del mes</div>
            <div class="v neg">${{ number_format($egresosMes, 2, ',', '.') }}</div>
        </div>
        <div class="fin-kpi">
            <div class="l">Resultado del mes</div>
            <div class="v {{ $resultadoMes >= 0 ? 'pos' : 'neg' }}">${{ number_format($resultadoMes, 2, ',', '.') }}</div>
        </div>
        <div class="fin-kpi">
            <div class="l">Deuda a proveedores</div>
            <div class="v deuda">${{ number_format($cxpTotal, 2, ',', '.') }}</div>
        </div>
        <div class="fin-kpi">
            <div class="l">CxP vencido</div>
            <div class="v neg">${{ number_format($cxpVencidoTotal, 2, ',', '.') }}</div>
        </div>
        <div class="fin-kpi">
            <div class="l">Por cobrar a clientes</div>
            <div class="v azul">${{ number_format($cxcSaldo, 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Fila 1: ingresos vs egresos + gastos por categoría --}}
    <div class="fin-grid">
        <div class="fin-card">
            <h3>Ingresos vs egresos — últimos 12 meses</h3>
            <div class="chart-box"><canvas id="chartIngresosEgresos"></canvas></div>
        </div>
        <div class="fin-card">
            <h3>Gastos pagados por categoría — este mes</h3>
            @if($gastosPorCategoria->isEmpty())
                <div class="fin-vacio">Sin gastos pagados este mes.</div>
            @else
                <div class="chart-box chico"><canvas id="chartGastosCategoria"></canvas></div>
            @endif
        </div>
    </div>

    {{-- Fila 2: saldos por cuenta + vencimientos --}}
    <div class="fin-grid">
        <div class="fin-card">
            <h3>Saldos de tesorería por cuenta</h3>
            @if($saldosCuentas->isEmpty())
                <div class="fin-vacio">Sin cuentas activas.</div>
            @else
                <div class="chart-box chico"><canvas id="chartSaldosCuentas"></canvas></div>
            @endif
        </div>
        <div class="fin-card">
            <h3>Vencimientos de proveedores (vencidos y próximos 30 días)</h3>
            <div style="overflow-x:auto;">
                <table class="fin-tabla">
                    <thead>
                        <tr><th>Proveedor</th><th>Vence</th><th></th><th class="der">Monto</th></tr>
                    </thead>
                    <tbody>
                        @forelse($cxpVencidas->concat($cxpProximas)->take(12) as $d)
                        <tr>
                            <td style="font-weight:500;">{{ $d->proveedor->nombre ?? 'Proveedor #' . $d->proveedor_id }}</td>
                            <td style="white-space:nowrap;">{{ $d->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>
                                @if($d->fecha_vencimiento->isPast())
                                    <span class="fin-badge vencida">Vencida</span>
                                @else
                                    <span class="fin-badge proxima">Próxima</span>
                                @endif
                            </td>
                            <td class="der" style="font-weight:600;">${{ number_format($d->monto, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="fin-vacio">Sin vencimientos registrados. Todo al día.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($cxpProximasTotal > 0)
                <div style="font-size:12.5px;color:#6E7A96;margin-top:10px;">Total a vencer en 30 días: <strong style="color:#9a6b0f;">${{ number_format($cxpProximasTotal, 2, ',', '.') }}</strong></div>
            @endif
        </div>
    </div>

    @if($resultadoExtranjeroMonedas->isNotEmpty())
    {{-- Fila 2.5: ingresos/egresos/resultado en moneda extranjera, aparte de los pesos --}}
    <div class="fin-grid">
        <div class="fin-card" style="grid-column: 1 / -1;">
            <h3>Resultado en moneda extranjera</h3>
            <p style="font-size:12.5px;color:#6E7A96;margin:-6px 0 12px;">Ingresos menos egresos de tesorería en esa moneda, sin convertir a pesos. "Acumulado" es desde siempre.</p>
            <div style="overflow-x:auto;">
                <table class="fin-tabla">
                    <thead>
                        <tr><th>Moneda</th><th class="der">Ingresos (mes)</th><th class="der">Egresos (mes)</th><th class="der">Resultado (mes)</th><th class="der">Resultado acumulado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($resultadoExtranjeroMonedas as $r)
                        <tr>
                            <td style="font-weight:500;">{{ $r['codigo'] }}</td>
                            <td class="der">{{ $r['simbolo'] }} {{ number_format($r['ingresos_mes'], 2, ',', '.') }}</td>
                            <td class="der">{{ $r['simbolo'] }} {{ number_format($r['egresos_mes'], 2, ',', '.') }}</td>
                            <td class="der" style="font-weight:600;color:{{ $r['resultado_mes'] >= 0 ? '#0d8a4f' : '#c0392b' }};">{{ $r['simbolo'] }} {{ number_format($r['resultado_mes'], 2, ',', '.') }}</td>
                            <td class="der" style="font-weight:700;color:{{ $r['resultado_total'] >= 0 ? '#0d8a4f' : '#c0392b' }};">{{ $r['simbolo'] }} {{ number_format($r['resultado_total'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($saldosMonedaExtranjera->isNotEmpty())
    {{-- Fila 3: tenencias en moneda extranjera (no se suman con los pesos de arriba) --}}
    <div class="fin-grid">
        <div class="fin-card" style="grid-column: 1 / -1;">
            <h3>Tenencias en moneda extranjera</h3>
            <div style="overflow-x:auto;">
                <table class="fin-tabla">
                    <thead>
                        <tr><th>Moneda</th><th>Cuenta</th><th class="der">Saldo</th></tr>
                    </thead>
                    <tbody>
                        @foreach($saldosMonedaExtranjera as $grupo)
                            @foreach($grupo['cuentas'] as $c)
                            <tr>
                                <td style="font-weight:500;">{{ $grupo['codigo'] }}</td>
                                <td>{{ $c['nombre'] }}</td>
                                <td class="der" style="font-weight:600;">{{ $grupo['simbolo'] }} {{ number_format($c['saldo'], 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" style="text-align:right;color:#6E7A96;font-size:12.5px;">Total {{ $grupo['codigo'] }}</td>
                                <td class="der" style="font-weight:700;color:#1B2B5A;">{{ $grupo['simbolo'] }} {{ number_format($grupo['total'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
(function () {
    const INK       = '#1B2B5A';
    const MUTED     = '#6E7A96';
    const GRID      = 'rgba(110, 122, 150, 0.14)';
    const C_INGRESO = '#2563EB'; // azul (serie ingresos)
    const C_EGRESO  = '#b4552d'; // óxido (serie egresos) — par validado para daltonismo
    const C_UNICO   = '#2563EB'; // series únicas de magnitud
    const C_SALDO   = '#0d8a4f';

    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = MUTED;

    const fmtPeso = v => '$' + Number(v).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    const tooltipPeso = ctx => (ctx.dataset.label ? ctx.dataset.label + ': ' : '') + '$' + Number(ctx.parsed.y ?? ctx.parsed.x).toLocaleString('es-AR', { minimumFractionDigits: 2 });

    // ── (a) Ingresos vs egresos, últimos 12 meses ──────────────
    new Chart(document.getElementById('chartIngresosEgresos'), {
        type: 'bar',
        data: {
            labels: @json($mesesLabels),
            datasets: [
                {
                    label: 'Ingresos',
                    data: @json($serieIngresos),
                    backgroundColor: C_INGRESO,
                    borderRadius: 4,
                    borderSkipped: 'start',
                    maxBarThickness: 18,
                    categoryPercentage: 0.62,
                    barPercentage: 0.9
                },
                {
                    label: 'Egresos',
                    data: @json($serieEgresos),
                    backgroundColor: C_EGRESO,
                    borderRadius: 4,
                    borderSkipped: 'start',
                    maxBarThickness: 18,
                    categoryPercentage: 0.62,
                    barPercentage: 0.9
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, color: INK } },
                tooltip: { callbacks: { label: tooltipPeso } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: MUTED } },
                y: { beginAtZero: true, grid: { color: GRID, drawBorder: false }, ticks: { color: MUTED, callback: fmtPeso } }
            }
        }
    });

    // ── (b) Gastos por categoría del mes (barra horizontal, un solo tono) ──
    const gastosCat = @json($gastosPorCategoria);
    if (gastosCat.length > 0) {
        new Chart(document.getElementById('chartGastosCategoria'), {
            type: 'bar',
            data: {
                labels: gastosCat.map(g => g.nombre),
                datasets: [{
                    data: gastosCat.map(g => Number(g.total)),
                    backgroundColor: C_UNICO,
                    borderRadius: 4,
                    borderSkipped: 'start',
                    maxBarThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: tooltipPeso } }
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: GRID, drawBorder: false }, ticks: { color: MUTED, callback: fmtPeso } },
                    y: { grid: { display: false }, ticks: { color: INK } }
                }
            }
        });
    }

    // ── (c) Saldos por cuenta (barra horizontal, un solo tono) ──
    const saldosCuentas = @json($saldosCuentas);
    if (saldosCuentas.length > 0) {
        new Chart(document.getElementById('chartSaldosCuentas'), {
            type: 'bar',
            data: {
                labels: saldosCuentas.map(c => c.nombre),
                datasets: [{
                    data: saldosCuentas.map(c => Number(c.saldo)),
                    backgroundColor: C_SALDO,
                    borderRadius: 4,
                    borderSkipped: 'start',
                    maxBarThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: tooltipPeso } }
                },
                scales: {
                    x: { grid: { color: GRID, drawBorder: false }, ticks: { color: MUTED, callback: fmtPeso } },
                    y: { grid: { display: false }, ticks: { color: INK } }
                }
            }
        });
    }
})();
</script>
@endsection
