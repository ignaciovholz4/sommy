@extends('layouts.admin')

@section('title', 'Revendedores')

@section('contenido')
<style>
    .rv-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1200px; margin: 0 auto; }
    .rv-head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
    .rv-title { font-size: 21px; font-weight: 600; }
    .rv-sub { font-size: 13px; color: #6E7A96; font-weight: 300; margin-top: 4px; max-width: 620px; line-height: 1.5; }

    .rv-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 18px; }
    .rv-kpi { background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 14px 18px; box-shadow: 0 10px 30px rgba(27,43,90,.06); }
    .rv-kpi .l { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .rv-kpi .v { font-size: 22px; font-weight: 700; margin-top: 4px; }
    .rv-kpi .v.azul { color: #1B2B5A; }
    .rv-kpi .v.verde { color: #0d8a4f; }
    .rv-kpi .v.naranja { color: #b4552d; }

    .rv-filtros { background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 14px 18px; margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
    .rv-filtros label { display: block; font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; margin-bottom: 4px; }
    .rv-filtros input, .rv-filtros select { border: 1px solid #E7EAF2; border-radius: 10px; padding: 8px 12px; font-size: 13.5px; color: #1B2B5A; }
    .rv-btn { border: none; background: #1B2B5A; color: #fff; border-radius: 999px; padding: 9px 22px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; }
    .rv-btn:hover { background: #2563EB; color: #fff; }
    .rv-btn.ghost { background: #fff; color: #1B2B5A; border: 1.5px solid #1B2B5A; }
    .rv-btn.ghost:hover { background: #1B2B5A; color: #fff; }

    .rv-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); overflow: hidden; }
    .rv-table { width: 100%; border-collapse: collapse; }
    .rv-table th { background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; padding: 11px 16px; text-align: left; white-space: nowrap; }
    .rv-table td { padding: 12px 16px; border-bottom: 1px solid #F1F4F9; font-size: 14px; }
    .rv-table tr:hover td { background: #F8FAFC; }
    .rv-table .der { text-align: right; }
    .rv-nombre { font-weight: 600; }
    .rv-cod { font-family: monospace; font-size: 12px; background: #F1F4F9; border-radius: 6px; padding: 2px 8px; color: #47536F; }
    .rv-chip { display: inline-block; border-radius: 999px; padding: 3px 12px; font-size: 11.5px; font-weight: 600; }
    .rv-chip.activo { background: #E4F5EC; color: #0d8a4f; }
    .rv-chip.pendiente { background: #FDF3E2; color: #b4552d; }
    .rv-chip.suspendido { background: #F1F4F9; color: #6E7A96; }
    .rv-deuda { color: #b4552d; font-weight: 700; }
    .rv-cero { color: #9AA5BD; }
    .rv-vacio { padding: 46px 24px; text-align: center; color: #6E7A96; font-weight: 300; font-size: 14px; }
    .rv-vacio a { color: #2563EB; font-weight: 500; }
    .rv-alerta { border-radius: 12px; padding: 12px 18px; font-size: 13.5px; margin-bottom: 14px; }
    .rv-alerta.ok { background: #E4F5EC; color: #0d8a4f; }
    .rv-alerta.err { background: #FBEDE6; color: #b4552d; }

    .rv-link-publico { background: linear-gradient(135deg,#131C36,#1B2B5A); color: #fff; border-radius: 14px; padding: 16px 20px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
    .rv-link-publico .t { font-size: 13.5px; font-weight: 500; }
    .rv-link-publico .u { font-size: 12.5px; color: #C9D2E8; font-family: monospace; margin-top: 3px; }
    .rv-link-publico a.rv-btn { background: #fff; color: #1B2B5A; }

    @media (max-width: 820px) {
        .rv-scroll { overflow-x: auto; }
        .rv-table { min-width: 780px; }
    }
</style>

<div class="rv-wrap">

    <div class="rv-head">
        <div>
            <div class="rv-title"><i class="fas fa-handshake" style="color:#2563EB;"></i> Revendedores</div>
            <div class="rv-sub">
                Cada revendedor tiene un link y un QR propios. Toda compra que entre por ahí queda
                atribuida automáticamente y te genera la comisión a pagar. Ellos no ven nada: la gestión es tuya.
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('rev.export', ['desde' => $desde, 'hasta' => $hasta]) }}" class="rv-btn ghost">
                <i class="fas fa-file-csv"></i> Exportar comisiones
            </a>
        </div>
    </div>

    @if(session('exito'))<div class="rv-alerta ok"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>@endif
    @if(session('error'))<div class="rv-alerta err"><i class="fas fa-triangle-exclamation"></i> {{ session('error') }}</div>@endif

    <div class="rv-link-publico">
        <div>
            <div class="t"><i class="fas fa-user-plus"></i> Página de alta para nuevos revendedores</div>
            <div class="u">{{ url('/revendedores') }}</div>
        </div>
        <a href="{{ url('/revendedores') }}" target="_blank" class="rv-btn">Ver página</a>
    </div>

    <div class="rv-kpis">
        <div class="rv-kpi"><div class="l">Revendedores activos</div><div class="v azul">{{ $resumen['activos'] }}<span style="font-size:13px;color:#9AA5BD;font-weight:400;"> / {{ $resumen['revendedores'] }}</span></div></div>
        <div class="rv-kpi"><div class="l">Ventas del período</div><div class="v azul">{{ $resumen['ventas'] }}</div></div>
        <div class="rv-kpi"><div class="l">Facturado por ellos</div><div class="v verde">${{ number_format($resumen['facturado'], 2, ',', '.') }}</div></div>
        <div class="rv-kpi"><div class="l">Comisiones generadas</div><div class="v azul">${{ number_format($resumen['comisiones'], 2, ',', '.') }}</div></div>
        <div class="rv-kpi"><div class="l">Les debo hoy</div><div class="v naranja">${{ number_format($resumen['a_pagar'], 2, ',', '.') }}</div></div>
    </div>

    <form method="GET" action="{{ url('/revendedores-panel') }}" class="rv-filtros">
        <div><label>Desde</label><input type="date" name="desde" value="{{ $desde }}"></div>
        <div><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta }}"></div>
        <div>
            <label>Estado</label>
            <select name="estado">
                <option value="">Todos</option>
                <option value="activo" @selected($estado==='activo')>Activos</option>
                <option value="pendiente" @selected($estado==='pendiente')>Pendientes</option>
                <option value="suspendido" @selected($estado==='suspendido')>Suspendidos</option>
            </select>
        </div>
        <div style="flex:1;min-width:200px;">
            <label>Buscar</label>
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Nombre, email o código" style="width:100%;">
        </div>
        <button type="submit" class="rv-btn"><i class="fas fa-filter"></i> Aplicar</button>
    </form>

    <div class="rv-card">
        <div class="rv-scroll">
        <table class="rv-table">
            <thead>
                <tr>
                    <th>Revendedor</th>
                    <th>Código</th>
                    <th>Estado</th>
                    <th class="der">Comisión</th>
                    <th class="der">Ventas</th>
                    <th class="der">Facturado</th>
                    <th class="der">Le debo</th>
                    <th class="der">Ya cobró</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($revendedores as $r)
                    @php
                        $t = $porRevendedor[$r->id] ?? null;
                        $debo = (float) ($deudaViva[$r->id] ?? 0);
                    @endphp
                    <tr>
                        <td>
                            <div class="rv-nombre">{{ $r->nombre }}</div>
                            <div style="font-size:12px;color:#6E7A96;">{{ $r->email }}</div>
                        </td>
                        <td><span class="rv-cod">{{ $r->codigo }}</span></td>
                        <td><span class="rv-chip {{ $r->estado }}">{{ ucfirst($r->estado) }}</span></td>
                        <td class="der">{{ rtrim(rtrim(number_format($r->comision_porcentaje, 2, ',', '.'), '0'), ',') }}%</td>
                        <td class="der">{{ $t->ventas ?? 0 }}</td>
                        <td class="der">${{ number_format($t->facturado ?? 0, 2, ',', '.') }}</td>
                        <td class="der">
                            @if($debo > 0)
                                <span class="rv-deuda">${{ number_format($debo, 2, ',', '.') }}</span>
                            @else
                                <span class="rv-cero">—</span>
                            @endif
                        </td>
                        <td class="der">${{ number_format($t->pagado ?? 0, 2, ',', '.') }}</td>
                        <td class="der"><a href="{{ route('rev.show', $r->id) }}" class="rv-btn ghost" style="padding:5px 16px;font-size:12.5px;">Ver ficha</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="rv-vacio">
                            Todavía no hay revendedores registrados.<br>
                            Compartí <a href="{{ url('/revendedores') }}" target="_blank">{{ url('/revendedores') }}</a> con quien quiera vender Sommy.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

</div>
@endsection
