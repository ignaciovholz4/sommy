@extends('layouts.admin')

@section('title', 'Resumen de movimientos')

@section('contenido')
<style>
    .fin-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px; }
    .res-tot-label { font-size: 12px; color: #47536F; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
    .res-tot-valor { font-size: 22px; font-weight: 700; color: #1B2B5A; }
    .res-tot-valor.rojo { color: #b4552d; }
    .res-tabs a { border-radius: 999px; padding: 7px 16px; font-size: 13px; font-weight: 600; text-decoration: none; margin-right: 8px; }
    .res-tabs a.activo { background: #1B2B5A; color: #fff; }
    .res-tabs a:not(.activo) { background: #F1F4F9; color: #47536F; }
    .res-print-titulo { display: none; }
    @media print {
        header, nav, .res-tabs, .btn-imprimir-resumen, .btn { display: none !important; }
        .res-print-titulo { display: block; }
        .fin-card { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
        body { background: #fff; }
        a { text-decoration: none; color: inherit; }
    }
</style>

<div class="container-fluid" style="padding: 18px 10px;">
    <div class="res-print-titulo mb-3">
        <h3 style="color:#1B2B5A;font-weight:700;">Sommy — Resumen {{ $periodo === 'mes' ? 'del mes' : 'del día' }}</h3>
        <p>{{ $desde->format('d/m/Y') }} @if($periodo === 'mes') al {{ $hasta->format('d/m/Y') }} @endif</p>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;"><i class="fas fa-clipboard-list" style="color:#2563EB;"></i> Resumen de movimientos</h4>
        <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
            <div class="res-tabs">
                <a href="{{ url('finanzas/resumen?periodo=hoy') }}" class="{{ $periodo === 'hoy' ? 'activo' : '' }}">Hoy</a>
                <a href="{{ url('finanzas/resumen?periodo=mes') }}" class="{{ $periodo === 'mes' ? 'activo' : '' }}">Este mes</a>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm btn-imprimir-resumen" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>
    <p class="text-muted small">{{ $desde->format('d/m/Y') }} @if($periodo === 'mes') al {{ $hasta->format('d/m/Y') }} @endif</p>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Ingresos</div>
                <div class="res-tot-valor">${{ number_format($totales['ingresos'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Egresos</div>
                <div class="res-tot-valor rojo">${{ number_format($totales['egresos'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Neto</div>
                <div class="res-tot-valor {{ $totales['neto'] < 0 ? 'rojo' : '' }}">${{ number_format($totales['neto'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Efectivo de fleteros sin rendir</div>
                <div class="res-tot-valor {{ $fleterosEfectivo->sum('pendiente') > 0 ? 'rojo' : '' }}">
                    ${{ number_format($fleterosEfectivo->sum('pendiente'), 2, ',', '.') }}
                </div>
                @if($fleterosEfectivo->isNotEmpty())
                    <div class="small text-muted mt-1">
                        @foreach($fleterosEfectivo as $f)
                            {{ $f->nombre }}: ${{ number_format($f->pendiente, 2, ',', '.') }}@if(!$loop->last), @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-2"><span class="text-muted small">Efectivo:</span> <b>${{ number_format($totales['efectivo'], 2, ',', '.') }}</b></div>
        <div class="col-md-3 mb-2"><span class="text-muted small">Bancos/transferencias:</span> <b>${{ number_format($totales['bancos'], 2, ',', '.') }}</b></div>
        <div class="col-md-3 mb-2"><span class="text-muted small">Tarjetas:</span> <b>${{ number_format($totales['tarjetas'], 2, ',', '.') }}</b></div>
        <div class="col-md-3 mb-2"><span class="text-muted small">Cheques:</span> <b>${{ number_format($totales['cheques'], 2, ',', '.') }}</b></div>
    </div>

    @if($totalesPorMoneda->count() > 1)
    <div class="row mb-4">
        @foreach($totalesPorMoneda->where('moneda', '!=', 'ARS') as $t)
        <div class="col-md-4 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Neto en {{ $t['moneda'] }}</div>
                <div class="res-tot-valor {{ $t['neto'] < 0 ? 'rojo' : '' }}">{{ $t['simbolo'] }} {{ number_format($t['neto'], 2, ',', '.') }}</div>
                <div class="small text-muted mt-1">Ingresos {{ $t['simbolo'] }}{{ number_format($t['ingresos'], 2, ',', '.') }} · Egresos {{ $t['simbolo'] }}{{ number_format($t['egresos'], 2, ',', '.') }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="fin-card mb-4">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:4px;">Actividad del período ({{ $actividad->count() }})</h3>
        <p class="text-muted small mb-3">Todo lo que se cargó en el período — compras, ventas, pedidos, gastos y devoluciones — tenga o no pago registrado todavía.</p>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Tipo</th>
                        <th>Documento</th>
                        <th>Cliente/Proveedor</th>
                        <th>Estado</th>
                        <th class="text-end">Monto</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actividad as $a)
                    <tr>
                        <td>{{ $a['fecha']->format('d/m/Y H:i') }}</td>
                        <td><i class="fas {{ $a['icono'] }}" style="color:#2563EB;"></i> {{ $a['tipo'] }}</td>
                        <td>{{ $a['titulo'] }}</td>
                        <td>{{ $a['subtitulo'] }}</td>
                        <td><span class="badge badge-{{ $a['estadoColor'] }}">{{ $a['estado'] }}</span></td>
                        <td class="text-end fw-bold">${{ number_format($a['monto'], 2, ',', '.') }}</td>
                        <td class="text-end"><a href="{{ $a['link'] }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Sin actividad en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="fin-card mb-4">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:4px;">Posición financiera actual (foto de hoy)</h3>
        <p class="text-muted small mb-3">No es lo movido en el período: es cuánta plata tenés ahora, cuánto te deben y cuánto debés, en este mismo momento.</p>
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Caja + bancos ahora (ARS)</div>
                <div class="res-tot-valor {{ $saldoArs < 0 ? 'rojo' : '' }}">${{ number_format($saldoArs, 2, ',', '.') }}</div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Me deben (clientes)</div>
                <div class="res-tot-valor">${{ number_format($porCobrar['deuda_total'], 2, ',', '.') }}</div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Debo (proveedores)</div>
                <div class="res-tot-valor rojo">${{ number_format($porPagar['deuda_total'], 2, ',', '.') }}</div>
                @if($porPagar['vencido_total'] > 0)
                    <div class="small" style="color:#b4552d;">de eso, ${{ number_format($porPagar['vencido_total'], 2, ',', '.') }} ya vencido</div>
                @endif
            </div>
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Posición neta (ARS)</div>
                <div class="res-tot-valor {{ $posicionNeta < 0 ? 'rojo' : '' }}">${{ number_format($posicionNeta, 2, ',', '.') }}</div>
                <div class="small text-muted">caja ARS + por cobrar − por pagar</div>
            </div>
        </div>

        @if($saldoPorMoneda->except('ARS')->isNotEmpty())
        <div class="row mb-3">
            @foreach($saldoPorMoneda->except('ARS') as $codigo => $s)
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Caja + bancos en {{ $codigo }}</div>
                <div class="res-tot-valor {{ $s['saldo'] < 0 ? 'rojo' : '' }}">{{ $s['simbolo'] }} {{ number_format($s['saldo'], 2, ',', '.') }}</div>
                <div class="small text-muted">no se suma al total en pesos: es otra unidad</div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="row">
            <div class="col-md-4 mb-3">
                <div style="font-size:12px;font-weight:600;color:#47536F;margin-bottom:6px;">Saldo por cuenta</div>
                <table class="table table-sm">
                    <tbody>
                        @forelse($tesoreria['saldo_por_cuenta'] as $c)
                        <tr>
                            <td>{{ $c['cuenta'] }} <span class="text-muted small">({{ $c['tipo'] }})</span></td>
                            <td class="text-end fw-bold {{ $c['saldo'] < 0 ? 'text-danger' : '' }}">${{ number_format($c['saldo'], 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td class="text-muted small">Sin cuentas activas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 mb-3">
                <div style="font-size:12px;font-weight:600;color:#47536F;margin-bottom:6px;">Principales deudores</div>
                <table class="table table-sm">
                    <tbody>
                        @forelse($porCobrar['top_deudores'] as $d)
                        <tr>
                            <td>{{ $d['cliente'] }}</td>
                            <td class="text-end fw-bold">${{ number_format($d['saldo'], 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td class="text-muted small">Nadie te debe plata en cuenta corriente.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 mb-3">
                <div style="font-size:12px;font-weight:600;color:#47536F;margin-bottom:6px;">Proveedores — vencido / próximo a vencer</div>
                <table class="table table-sm">
                    <tbody>
                        @foreach($porPagar['proveedores_vencidos'] as $p)
                        <tr>
                            <td class="text-danger">{{ $p['proveedor'] }} <span class="small">(vencido {{ \Carbon\Carbon::parse($p['vencimiento'])->format('d/m') }})</span></td>
                            <td class="text-end fw-bold text-danger">${{ number_format($p['monto'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        @foreach($porPagar['proveedores_proximos_a_vencer'] as $p)
                        <tr>
                            <td>{{ $p['proveedor'] }} <span class="text-muted small">(vence {{ \Carbon\Carbon::parse($p['vencimiento'])->format('d/m') }})</span></td>
                            <td class="text-end fw-bold">${{ number_format($p['monto'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        @if(empty($porPagar['proveedores_vencidos']) && empty($porPagar['proveedores_proximos_a_vencer']))
                        <tr><td class="text-muted small">Sin vencimientos próximos ni deuda vencida.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="fin-card mb-4">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:4px;">¿Estoy ganando o perdiendo plata? — histórico mes a mes</h3>
        <p class="text-muted small mb-3">
            Comprar mercadería no es una pérdida: esa plata se convierte en stock (un activo), no se esfuma. Por eso
            el resultado de cada mes es <strong>ingresos − gastos operativos reales</strong> (alquiler, sueldos, marketing, etc.).
            Lo pagado en compras de mercadería se muestra aparte, como inversión en stock, y no resta acá.
        </p>
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Ingresos históricos</div>
                <div class="res-tot-valor">${{ number_format($historicoTotal['ingresos'], 2, ',', '.') }}</div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Gastos operativos históricos</div>
                <div class="res-tot-valor rojo">${{ number_format($historicoTotal['egresos_operativos'], 2, ',', '.') }}</div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Invertido en stock (compras)</div>
                <div class="res-tot-valor">${{ number_format($historicoTotal['egresos_stock'], 2, ',', '.') }}</div>
                <div class="small text-muted">no es pérdida: es mercadería</div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Resultado histórico</div>
                <div class="res-tot-valor {{ $historicoTotal['neto'] < 0 ? 'rojo' : '' }}">
                    {{ $historicoTotal['neto'] >= 0 ? 'Ganancia' : 'Pérdida' }}: ${{ number_format(abs($historicoTotal['neto']), 2, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th class="text-end">Ingresos</th>
                        <th class="text-end">Gastos operativos</th>
                        <th class="text-end">Invertido en stock</th>
                        <th class="text-end">Resultado del mes</th>
                        <th class="text-end">Acumulado histórico</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historicoMensual as $h)
                    <tr>
                        <td style="text-transform:capitalize;">{{ $h['fecha']->translatedFormat('F Y') }}</td>
                        <td class="text-end">${{ number_format($h['ingresos'], 2, ',', '.') }}</td>
                        <td class="text-end">${{ number_format($h['egresos_operativos'], 2, ',', '.') }}</td>
                        <td class="text-end text-muted">${{ number_format($h['egresos_stock'], 2, ',', '.') }}</td>
                        <td class="text-end fw-bold {{ $h['neto'] < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $h['neto'] >= 0 ? '+' : '−' }}${{ number_format(abs($h['neto']), 2, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold {{ $h['acumulado'] < 0 ? 'text-danger' : 'text-success' }}">
                            ${{ number_format($h['acumulado'], 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Todavía no hay movimientos de caja/banco cargados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($historicoPorMoneda as $codigo => $h)
    <div class="fin-card mb-4">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:4px;">¿Estoy ganando o perdiendo en {{ $codigo }}? — histórico mes a mes</h3>
        <p class="text-muted small mb-3">Mismo criterio que en pesos: comprar mercadería en {{ $codigo }} no es pérdida, se muestra aparte como inversión en stock.</p>
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Ingresos históricos</div>
                <div class="res-tot-valor">{{ $codigo }} {{ number_format($h['total']['ingresos'], 2, ',', '.') }}</div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Gastos operativos históricos</div>
                <div class="res-tot-valor rojo">{{ $codigo }} {{ number_format($h['total']['egresos_operativos'], 2, ',', '.') }}</div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Invertido en stock (compras)</div>
                <div class="res-tot-valor">{{ $codigo }} {{ number_format($h['total']['egresos_stock'], 2, ',', '.') }}</div>
                <div class="small text-muted">no es pérdida: es mercadería</div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="res-tot-label">Resultado histórico</div>
                <div class="res-tot-valor {{ $h['total']['neto'] < 0 ? 'rojo' : '' }}">
                    {{ $h['total']['neto'] >= 0 ? 'Ganancia' : 'Pérdida' }}: {{ $codigo }} {{ number_format(abs($h['total']['neto']), 2, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th class="text-end">Ingresos</th>
                        <th class="text-end">Gastos operativos</th>
                        <th class="text-end">Invertido en stock</th>
                        <th class="text-end">Resultado del mes</th>
                        <th class="text-end">Acumulado histórico</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($h['mensual'] as $row)
                    <tr>
                        <td style="text-transform:capitalize;">{{ $row['fecha']->translatedFormat('F Y') }}</td>
                        <td class="text-end">{{ $codigo }} {{ number_format($row['ingresos'], 2, ',', '.') }}</td>
                        <td class="text-end">{{ $codigo }} {{ number_format($row['egresos_operativos'], 2, ',', '.') }}</td>
                        <td class="text-end text-muted">{{ $codigo }} {{ number_format($row['egresos_stock'], 2, ',', '.') }}</td>
                        <td class="text-end fw-bold {{ $row['neto'] < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $row['neto'] >= 0 ? '+' : '−' }}{{ $codigo }} {{ number_format(abs($row['neto']), 2, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold {{ $row['acumulado'] < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $codigo }} {{ number_format($row['acumulado'], 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Sin movimientos en {{ $codigo }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @if($operacionesCambio->isNotEmpty())
    <div class="fin-card mb-4">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:4px;">Cómo se transformaron los dólares — cambios de moneda</h3>
        <p class="text-muted small mb-3">Cada vez que entraron o salieron dólares (u otra moneda extranjera) contra pesos: a qué cotización, cuántos pesos resultaron, y la ganancia o pérdida por la diferencia de cambio cuando aplica.</p>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Operación</th>
                        <th class="text-end">Monto en moneda</th>
                        <th class="text-end">Cotización</th>
                        <th class="text-end">Se transformó en (pesos)</th>
                        <th class="text-end">Resultado cambiario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($operacionesCambio as $op)
                    @php
                        $codOp = optional($op->moneda)->codigo ?? '?';
                        $simOp = optional($op->moneda)->simbolo ?? '';
                        $esReal = $op->cuenta_ars_id !== null;
                        $etiqueta = $op->tipo === 'compra'
                            ? ($esReal ? "Compra de {$codOp}" : "Cobro en {$codOp}")
                            : ($esReal ? "Venta de {$codOp}" : "Pago en {$codOp}");
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($op->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $etiqueta }}</td>
                        <td class="text-end">{{ $simOp }} {{ number_format($op->monto_moneda, 2, ',', '.') }}</td>
                        <td class="text-end">${{ number_format($op->cotizacion, 2, ',', '.') }}</td>
                        <td class="text-end fw-bold">${{ number_format($op->monto_ars, 2, ',', '.') }}</td>
                        <td class="text-end {{ $op->resultado !== null ? ($op->resultado < 0 ? 'text-danger fw-bold' : 'text-success fw-bold') : 'text-muted' }}">
                            @if($op->resultado !== null)
                                {{ $op->resultado >= 0 ? '+' : '−' }}${{ number_format(abs($op->resultado), 2, ',', '.') }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="fin-card">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:12px;">Movimientos de caja/banco del período ({{ $movimientos->count() }})</h3>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cuenta</th>
                        <th>Moneda</th>
                        <th>Tipo</th>
                        <th>Medio</th>
                        <th>Cliente/Proveedor</th>
                        <th>Comprobante</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $m)
                    <tr>
                        <td>{{ $m->fecha->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($m->cuenta)->nombre ?: '—' }}</td>
                        <td>{{ optional(optional($m->cuenta)->moneda)->codigo ?? 'ARS' }}</td>
                        <td>
                            <span class="badge {{ $m->tipo === 'ingreso' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($m->tipo) }}
                            </span>
                        </td>
                        <td>{{ $m->medio ? str_replace('_', ' ', $m->medio) : '—' }}</td>
                        <td>{{ $m->cliente_proveedor ?: '—' }}</td>
                        <td>{{ $m->comprobante ?: '—' }}</td>
                        <td class="text-end fw-bold {{ $m->tipo === 'ingreso' ? 'text-success' : 'text-danger' }}">
                            {{ $m->tipo === 'ingreso' ? '+' : '−' }}{{ optional(optional($m->cuenta)->moneda)->simbolo ?? '$' }}{{ number_format($m->total, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Sin movimientos en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="fin-card mt-4">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:12px;">Comprobantes del período ({{ $comprobantes->count() }})</h3>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Origen</th>
                        <th>Archivo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comprobantes as $c)
                    <tr>
                        <td>{{ $c['fecha']->format('d/m/Y H:i') }}</td>
                        <td><a href="{{ $c['link'] }}">{{ $c['titulo'] }}</a></td>
                        <td>{{ $c['archivo'] }}</td>
                        <td>
                            @if($c['es_imagen'])
                                <a href="{{ $c['url'] }}" target="_blank" title="{{ $c['archivo'] }}">
                                    <img src="{{ $c['url'] }}" alt="{{ $c['archivo'] }}"
                                         style="width:50px;height:50px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6">
                                </a>
                            @else
                                <a href="{{ $c['url'] }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-pdf text-danger"></i> Ver
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Sin comprobantes en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
