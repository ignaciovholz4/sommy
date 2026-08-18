@extends('layouts.admin')

@section('title', 'Movimientos')

@section('contenido')
<style>
    .mov-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1150px; margin: 0 auto; }
    .mov-volver { font-size: 13.5px; color: #2563EB; text-decoration: none; }
    .mov-head { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin: 10px 0 16px; }
    .mov-title { font-size: 21px; font-weight: 600; }
    .mov-sub { font-size: 13px; color: #6E7A96; font-weight: 300; }

    .mov-filtro {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.06);
        padding: 14px 18px; margin-bottom: 14px;
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    }
    .mov-filtro label { font-size: 12.5px; font-weight: 500; color: #47536F; margin: 0; }
    .mov-filtro input[type=date] { border: 1px solid #E7EAF2; border-radius: 10px; padding: 7px 12px; font-size: 13.5px; color: #1B2B5A; }
    .mov-filtro .aplicar { border: none; background: #1B2B5A; color: #fff; border-radius: 999px; padding: 8px 22px; font-size: 13px; font-weight: 500; cursor: pointer; }
    .mov-filtro .aplicar:hover { background: #2563EB; }
    .mov-descargar {
        margin-left: auto;
        display: inline-flex; align-items: center; gap: 8px;
        background: #0d8a4f; color: #fff !important; border-radius: 999px;
        padding: 9px 22px; font-size: 13px; font-weight: 500; text-decoration: none !important;
    }
    .mov-descargar:hover { background: #0b7a45; }

    .mov-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 14px; }
    @media (max-width: 767px) { .mov-kpis { grid-template-columns: 1fr; } }
    .mov-kpi { background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 14px 18px; box-shadow: 0 10px 30px rgba(27,43,90,.06); }
    .mov-kpi .l { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .mov-kpi .v { font-size: 21px; font-weight: 700; }
    .mov-kpi.in .v { color: #0d8a4f; }
    .mov-kpi.out .v { color: #b4552d; }

    .mov-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); overflow: hidden; }
    .mov-table { width: 100%; border-collapse: collapse; }
    .mov-table th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
        padding: 11px 16px; text-align: left; position: sticky; top: 0;
    }
    .mov-table td { padding: 10px 16px; border-bottom: 1px solid #F1F4F9; font-size: 13.5px; }
    .mov-table .der { text-align: right; white-space: nowrap; }
    .mov-tipo { display: inline-block; border-radius: 999px; font-size: 11px; font-weight: 600; padding: 3px 12px; white-space: nowrap; }
    .mov-tipo.Venta { background: #DCFCE7; color: #166534; }
    .mov-tipo.CobroCC { background: #E0F2FE; color: #1B2B5A; }
    .mov-tipo.Compra { background: #FBEDE6; color: #b4552d; }
    .mov-tipo.Devolucion { background: #FEF3C7; color: #92400E; }
    .mov-tipo.Stocksalida, .mov-tipo.Stockentrada, .mov-tipo.Stockajuste { background: #F1F4F9; color: #47536F; }
    .mov-in { color: #0d8a4f; font-weight: 600; }
    .mov-out { color: #b4552d; font-weight: 600; }
</style>

<div class="mov-wrap">
    <a href="{{ url('/graph') }}?desde={{ $desde->format('Y-m-d') }}&hasta={{ $hasta->format('Y-m-d') }}" class="mov-volver"><i class="fas fa-arrow-left"></i> Volver a Informes</a>
    <div class="mov-head">
        <div>
            <div class="mov-title"><i class="fas fa-book" style="color:#2563EB;"></i> Libro de movimientos</div>
            <div class="mov-sub">Ventas, cobros, compras y devoluciones del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }} · {{ $movs->count() }} movimiento(s)</div>
        </div>
    </div>

    <form method="GET" action="{{ url('/graph/movimientos') }}" class="mov-filtro">
        <label>Desde</label>
        <input type="date" name="desde" value="{{ $desde->format('Y-m-d') }}">
        <label>Hasta</label>
        <input type="date" name="hasta" value="{{ $hasta->format('Y-m-d') }}">
        <button type="submit" class="aplicar"><i class="fas fa-filter"></i> Aplicar</button>
        <a class="mov-descargar" href="{{ url('/graph/movimientos') }}?desde={{ $desde->format('Y-m-d') }}&hasta={{ $hasta->format('Y-m-d') }}&export=csv">
            <i class="fas fa-file-download"></i> Descargar Excel (CSV)
        </a>
    </form>

    <div class="mov-kpis" style="grid-template-columns: repeat(5, 1fr);">
        <div class="mov-kpi in"><div class="l">Ingresos (ventas + cobros)</div><div class="v">${{ number_format($totIngresos, 2, ',', '.') }}</div></div>
        <div class="mov-kpi out"><div class="l">Egresos (compras + devol.)</div><div class="v">${{ number_format($totEgresos, 2, ',', '.') }}</div></div>
        <div class="mov-kpi"><div class="l">Resultado neto</div><div class="v" style="color:{{ $neto >= 0 ? '#0d8a4f' : '#b4552d' }};">${{ number_format($neto, 2, ',', '.') }}</div></div>
        <div class="mov-kpi"><div class="l">Unidades vendidas</div><div class="v">{{ $unidadesVendidas }}</div></div>
        <div class="mov-kpi"><div class="l">Unidades compradas</div><div class="v">{{ $unidadesCompradas }}</div></div>
    </div>

    {{-- Resumen mensual de todo lo que pasó --}}
    <div class="mov-card" style="margin-bottom:14px;">
        <div style="padding:14px 18px 0;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#47536F;">
            <i class="fas fa-calendar-alt" style="color:#2563EB;"></i> Resumen por mes
        </div>
        <table class="mov-table">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th class="der">Ventas</th>
                    <th class="der">Cobros CC</th>
                    <th class="der">Compras</th>
                    <th class="der">Devoluciones</th>
                    <th class="der">Neto</th>
                    <th class="der">U. vendidas</th>
                    <th class="der">U. compradas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($resumenMensual as $rm)
                <tr>
                    <td style="font-weight:600;">{{ $rm->mes }}</td>
                    <td class="der mov-in">${{ number_format($rm->ventas, 2, ',', '.') }}</td>
                    <td class="der mov-in">${{ number_format($rm->cobros, 2, ',', '.') }}</td>
                    <td class="der mov-out">${{ number_format($rm->compras, 2, ',', '.') }}</td>
                    <td class="der mov-out">${{ number_format($rm->devol, 2, ',', '.') }}</td>
                    <td class="der" style="font-weight:700;color:{{ $rm->neto >= 0 ? '#0d8a4f' : '#b4552d' }};">${{ number_format($rm->neto, 2, ',', '.') }}</td>
                    <td class="der">{{ $rm->uVendidas }}</td>
                    <td class="der">{{ $rm->uCompradas }}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:24px;color:#6E7A96;font-weight:300;">Sin actividad en el período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mov-card">
        <table class="mov-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Referencia</th>
                    <th>Detalle</th>
                    <th class="der">Ingreso</th>
                    <th class="der">Egreso</th>
                    <th class="der">Cant.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movs as $m)
                <tr>
                    <td style="white-space:nowrap;color:#6E7A96;">{{ \Carbon\Carbon::parse($m->fecha)->format('d/m/Y H:i') }}</td>
                    <td><span class="mov-tipo {{ str_replace([' ', 'ó'], ['', 'o'], $m->tipo) }}">{{ $m->tipo }}</span></td>
                    <td style="color:#47536F;">{{ $m->referencia }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($m->detalle, 60) }}</td>
                    <td class="der">@if($m->signo > 0 && $m->monto !== null)<span class="mov-in">${{ number_format($m->monto, 2, ',', '.') }}</span>@endif</td>
                    <td class="der">@if($m->signo < 0 && $m->monto !== null)<span class="mov-out">${{ number_format($m->monto, 2, ',', '.') }}</span>@endif</td>
                    <td class="der">
                        @if($m->cantidad !== null)
                            <span style="font-weight:600;color:{{ $m->cantidad >= 0 ? '#0d8a4f' : '#b4552d' }};">{{ $m->cantidad > 0 ? '+' : '' }}{{ $m->cantidad }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:36px;color:#6E7A96;font-weight:300;">Sin movimientos en el período seleccionado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p style="font-size:12px;color:#8A93AD;font-weight:300;margin-top:12px;">
        <i class="fas fa-circle-info"></i> Los pedidos web no figuran como movimiento propio: ingresan como Venta o Cobro CC al procesarse, para no duplicar montos.
    </p>
</div>
@endsection
