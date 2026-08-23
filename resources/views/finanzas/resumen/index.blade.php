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
</style>

<div class="container-fluid" style="padding: 18px 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;"><i class="fas fa-clipboard-list" style="color:#2563EB;"></i> Resumen de movimientos</h4>
        <div class="res-tabs">
            <a href="{{ url('finanzas/resumen?periodo=hoy') }}" class="{{ $periodo === 'hoy' ? 'activo' : '' }}">Hoy</a>
            <a href="{{ url('finanzas/resumen?periodo=mes') }}" class="{{ $periodo === 'mes' ? 'activo' : '' }}">Este mes</a>
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

    <div class="fin-card">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:12px;">Movimientos del período ({{ $movimientos->count() }})</h3>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cuenta</th>
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
                        <td>
                            <span class="badge {{ $m->tipo === 'ingreso' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($m->tipo) }}
                            </span>
                        </td>
                        <td>{{ $m->medio ? str_replace('_', ' ', $m->medio) : '—' }}</td>
                        <td>{{ $m->cliente_proveedor ?: '—' }}</td>
                        <td>{{ $m->comprobante ?: '—' }}</td>
                        <td class="text-end fw-bold {{ $m->tipo === 'ingreso' ? 'text-success' : 'text-danger' }}">
                            {{ $m->tipo === 'ingreso' ? '+' : '−' }}${{ number_format($m->total, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Sin movimientos en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
