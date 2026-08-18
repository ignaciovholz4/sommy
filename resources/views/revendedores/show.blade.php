@extends('layouts.admin')

@section('title', 'Revendedor ' . $revendedor->nombre)

@section('contenido')
<style>
    .rv-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1200px; margin: 0 auto; }
    .rv-volver { font-size: 13px; color: #6E7A96; text-decoration: none; }
    .rv-volver:hover { color: #2563EB; }
    .rv-title { font-size: 22px; font-weight: 600; margin: 8px 0 2px; }
    .rv-mail { font-size: 13px; color: #6E7A96; }

    .rv-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px; margin: 18px 0; }
    @media (max-width: 900px) { .rv-grid { grid-template-columns: 1fr; } }

    .rv-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); overflow: hidden; }
    .rv-card .h { padding: 14px 18px; border-bottom: 1px solid #F1F4F9; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6E7A96; }
    .rv-card .b { padding: 18px; }

    .rv-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .rv-kpi { background: #F8FAFC; border-radius: 12px; padding: 13px 16px; }
    .rv-kpi .l { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .rv-kpi .v { font-size: 20px; font-weight: 700; margin-top: 3px; }
    .rv-kpi .v.verde { color: #0d8a4f; }
    .rv-kpi .v.naranja { color: #b4552d; }

    .rv-linkbox { background: linear-gradient(135deg,#131C36,#1B2B5A); border-radius: 14px; padding: 18px; color: #fff; text-align: center; }
    .rv-linkbox .u { font-family: monospace; font-size: 13px; color: #fff; word-break: break-all; display: block; margin: 8px 0 14px; text-decoration: none; }
    .rv-linkbox img { background: #fff; border-radius: 12px; padding: 8px; width: 190px; height: auto; }

    .rv-btn { border: none; background: #1B2B5A; color: #fff; border-radius: 999px; padding: 9px 22px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; }
    .rv-btn:hover { background: #2563EB; color: #fff; }
    .rv-btn.ghost { background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,.55); }
    .rv-btn.ghost:hover { background: #fff; color: #1B2B5A; }
    .rv-btn.claro { background: #fff; color: #1B2B5A; }
    .rv-btn.chico { padding: 4px 14px; font-size: 12px; }
    .rv-btn.rojo { background: #b4552d; }
    .rv-btn.verde { background: #0d8a4f; }

    .rv-form label { display: block; font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; margin: 12px 0 4px; }
    .rv-form input, .rv-form select, .rv-form textarea { width: 100%; border: 1px solid #E7EAF2; border-radius: 10px; padding: 9px 13px; font-size: 13.5px; color: #1B2B5A; font-family: inherit; }
    .rv-form .fila { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    .rv-table { width: 100%; border-collapse: collapse; }
    .rv-table th { background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; padding: 10px 14px; text-align: left; white-space: nowrap; }
    .rv-table td { padding: 11px 14px; border-bottom: 1px solid #F1F4F9; font-size: 13.5px; }
    .rv-table .der { text-align: right; }
    .rv-chip { display: inline-block; border-radius: 999px; padding: 3px 11px; font-size: 11px; font-weight: 600; }
    .rv-chip.pendiente { background: #FDF3E2; color: #b4552d; }
    .rv-chip.aprobada { background: #E7EEFB; color: #2563EB; }
    .rv-chip.pagada { background: #E4F5EC; color: #0d8a4f; }
    .rv-chip.anulada { background: #F1F4F9; color: #9AA5BD; }
    .rv-vacio { padding: 34px; text-align: center; color: #6E7A96; font-weight: 300; font-size: 13.5px; }
    .rv-alerta { border-radius: 12px; padding: 12px 18px; font-size: 13.5px; margin-bottom: 14px; }
    .rv-alerta.ok { background: #E4F5EC; color: #0d8a4f; }
    .rv-alerta.err { background: #FBEDE6; color: #b4552d; }
    .rv-scroll { overflow-x: auto; }
    @media (max-width: 820px) { .rv-table { min-width: 720px; } }
</style>

<div class="rv-wrap">

    <a href="{{ url('/revendedores-panel') }}" class="rv-volver"><i class="fas fa-arrow-left"></i> Volver a revendedores</a>
    <div class="rv-title">{{ $revendedor->nombre }}</div>
    <div class="rv-mail">
        {{ $revendedor->email }}
        @if($revendedor->telefono) · {{ $revendedor->telefono }} @endif
        @if($revendedor->instagram) · <a href="https://instagram.com/{{ $revendedor->instagram }}" target="_blank" style="color:#2563EB;">&#64;{{ $revendedor->instagram }}</a> @endif
        · Alta {{ $revendedor->created_at->format('d/m/Y') }}
    </div>

    @if(session('exito'))<div class="rv-alerta ok" style="margin-top:14px;"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>@endif
    @if(session('error'))<div class="rv-alerta err" style="margin-top:14px;"><i class="fas fa-triangle-exclamation"></i> {{ session('error') }}</div>@endif

    <div class="rv-grid">

        <div class="rv-card">
            <div class="h">Resumen</div>
            <div class="b">
                <div class="rv-kpis">
                    <div class="rv-kpi"><div class="l">Ventas</div><div class="v">{{ $totales['ventas'] }}</div></div>
                    <div class="rv-kpi"><div class="l">Facturado</div><div class="v verde">${{ number_format($totales['facturado'], 2, ',', '.') }}</div></div>
                    <div class="rv-kpi"><div class="l">Comisión generada</div><div class="v">${{ number_format($totales['generado'], 2, ',', '.') }}</div></div>
                    <div class="rv-kpi"><div class="l">Le debo</div><div class="v naranja">${{ number_format($totales['a_pagar'], 2, ',', '.') }}</div></div>
                    <div class="rv-kpi"><div class="l">Ya cobró</div><div class="v">${{ number_format($totales['pagado'], 2, ',', '.') }}</div></div>
                    <div class="rv-kpi"><div class="l">Visitas al link</div><div class="v">{{ $revendedor->visitas }}</div></div>
                </div>

                <div style="margin-top:18px;background:#F8FAFC;border-radius:12px;padding:14px 16px;font-size:13px;color:#47536F;line-height:1.6;">
                    <strong>Cómo pagarle:</strong>
                    @if($revendedor->alias_cbu || $revendedor->cbu)
                        {{ $revendedor->titular_cuenta ?: $revendedor->nombre }}
                        @if($revendedor->alias_cbu) — alias <strong>{{ $revendedor->alias_cbu }}</strong>@endif
                        @if($revendedor->cbu) — CBU <strong>{{ $revendedor->cbu }}</strong>@endif
                        @if($revendedor->dni_cuit) — DNI/CUIT {{ $revendedor->dni_cuit }}@endif
                    @else
                        todavía no cargó datos bancarios. Podés completarlos abajo.
                    @endif
                </div>

                @if($revendedor->como_vende)
                <div style="margin-top:12px;font-size:13px;color:#6E7A96;line-height:1.6;">
                    <strong style="color:#1B2B5A;">Dónde vende:</strong> {{ $revendedor->como_vende }}
                </div>
                @endif
            </div>
        </div>

        <div class="rv-card">
            <div class="h">Su link y su QR</div>
            <div class="b">
                <div class="rv-linkbox">
                    <img src="{{ $qrDataUri }}" alt="QR de {{ $revendedor->nombre }}">
                    <a href="{{ $revendedor->link }}" target="_blank" class="u">{{ $revendedor->link }}</a>
                    <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                        <button type="button" class="rv-btn claro" onclick="copiarLink()"><i class="fas fa-copy"></i> Copiar link</button>
                        <a href="{{ route('rev.qr', $revendedor->id) }}" class="rv-btn ghost"><i class="fas fa-download"></i> Bajar QR</a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="rv-grid">

        <div class="rv-card">
            <div class="h">Liquidar comisiones</div>
            <div class="b">
                @if($totales['a_pagar'] > 0)
                    <p style="font-size:13.5px;color:#47536F;line-height:1.6;margin-bottom:4px;">
                        Le debés <strong style="color:#b4552d;">${{ number_format($totales['a_pagar'], 2, ',', '.') }}</strong>,
                        de los cuales <strong>${{ number_format($totales['aprobado'], 2, ',', '.') }}</strong>
                        corresponden a pedidos ya entregados.
                    </p>
                    <form method="POST" action="{{ route('rev.liquidar', $revendedor->id) }}" class="rv-form">
                        @csrf
                        <div class="fila">
                            <div>
                                <label>Medio de pago</label>
                                <select name="medio">
                                    <option value="transferencia">Transferencia</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="mercadopago">Mercado Pago</option>
                                    <option value="producto">Canje por producto</option>
                                </select>
                            </div>
                            <div>
                                <label>Comprobante / referencia</label>
                                <input type="text" name="referencia" placeholder="Nº de operación">
                            </div>
                        </div>
                        <label>Observación</label>
                        <input type="text" name="observacion" placeholder="Opcional">
                        <label style="text-transform:none;letter-spacing:0;font-size:13px;font-weight:400;color:#47536F;display:flex;align-items:center;gap:8px;margin-top:14px;">
                            <input type="checkbox" name="incluir_pendientes" value="1" style="width:auto;">
                            Incluir también comisiones de pedidos aún no entregados
                        </label>
                        <button type="submit" class="rv-btn verde" style="margin-top:14px;"
                                onclick="return confirm('¿Confirmás la liquidación? Las comisiones quedarán marcadas como pagadas.')">
                            <i class="fas fa-money-bill-transfer"></i> Registrar pago
                        </button>
                    </form>
                @else
                    <div class="rv-vacio" style="padding:20px 0;">No hay comisiones pendientes de pago.</div>
                @endif

                @if($pagos->count())
                <div style="margin-top:20px;">
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6E7A96;margin-bottom:8px;">Liquidaciones anteriores</div>
                    <table class="rv-table">
                        <tbody>
                        @foreach($pagos as $p)
                            <tr>
                                <td>{{ $p->fecha->format('d/m/Y') }}</td>
                                <td>{{ ucfirst($p->medio) }} @if($p->referencia)<span style="color:#9AA5BD;">· {{ $p->referencia }}</span>@endif</td>
                                <td class="der" style="font-weight:600;color:#0d8a4f;">${{ number_format($p->monto, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <div class="rv-card">
            <div class="h">Datos y condiciones</div>
            <div class="b">
                <form method="POST" action="{{ route('rev.update', $revendedor->id) }}" class="rv-form">
                    @csrf
                    <div class="fila">
                        <div>
                            <label>Comisión (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="comision_porcentaje" value="{{ $revendedor->comision_porcentaje }}" required>
                        </div>
                        <div>
                            <label>Estado</label>
                            <select name="estado">
                                <option value="activo" @selected($revendedor->estado==='activo')>Activo</option>
                                <option value="pendiente" @selected($revendedor->estado==='pendiente')>Pendiente</option>
                                <option value="suspendido" @selected($revendedor->estado==='suspendido')>Suspendido</option>
                            </select>
                        </div>
                    </div>
                    <div class="fila">
                        <div><label>Alias</label><input type="text" name="alias_cbu" value="{{ $revendedor->alias_cbu }}"></div>
                        <div><label>CBU</label><input type="text" name="cbu" value="{{ $revendedor->cbu }}"></div>
                    </div>
                    <div class="fila">
                        <div><label>Titular de la cuenta</label><input type="text" name="titular_cuenta" value="{{ $revendedor->titular_cuenta }}"></div>
                        <div><label>Teléfono</label><input type="text" name="telefono" value="{{ $revendedor->telefono }}"></div>
                    </div>
                    <label>Notas internas</label>
                    <textarea name="notas" rows="3" placeholder="Solo las ves vos">{{ $revendedor->notas }}</textarea>
                    <button type="submit" class="rv-btn" style="margin-top:14px;"><i class="fas fa-save"></i> Guardar</button>
                </form>
                <p style="font-size:12px;color:#9AA5BD;margin-top:12px;line-height:1.6;">
                    Si lo suspendés, su link deja de atribuir ventas nuevas. Las comisiones ya generadas se mantienen.
                </p>
            </div>
        </div>

    </div>

    <div class="rv-card">
        <div class="h">Ventas atribuidas</div>
        <div class="rv-scroll">
            <table class="rv-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Estado del pedido</th>
                        <th class="der">Venta</th>
                        <th class="der">%</th>
                        <th class="der">Comisión</th>
                        <th>Comisión</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($comisiones as $c)
                    <tr>
                        <td>{{ $c->created_at->format('d/m/Y') }}</td>
                        @if($c->order_id)
                            <td><a href="{{ url('orders/order/' . $c->order_id) }}" style="color:#2563EB;font-weight:600;">#{{ $c->order_id }}</a></td>
                            <td>{{ optional(optional($c->order)->cliente)->nombre ?? '—' }}</td>
                            <td>{{ optional(optional($c->order)->status)->status_name ?? '—' }}</td>
                        @else
                            <td style="font-weight:600;color:#1B2B5A;">Venta #{{ $c->venta_id }}</td>
                            <td>{{ optional(optional($c->venta)->cliente)->nombre ?? '—' }}</td>
                            <td>Venta manual</td>
                        @endif
                        <td class="der">${{ number_format($c->monto_venta, 2, ',', '.') }}</td>
                        <td class="der">{{ rtrim(rtrim(number_format($c->porcentaje, 2, ',', '.'), '0'), ',') }}%</td>
                        <td class="der" style="font-weight:600;">${{ number_format($c->comision, 2, ',', '.') }}</td>
                        <td><span class="rv-chip {{ $c->estado }}">{{ ucfirst($c->estado) }}</span></td>
                        <td class="der">
                            @if($c->estado !== 'pagada')
                                <form method="POST" action="{{ route('rev.comision.estado', $c->id) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="estado" value="{{ $c->estado === 'anulada' ? 'pendiente' : 'anulada' }}">
                                    <button type="submit" class="rv-btn chico {{ $c->estado === 'anulada' ? '' : 'rojo' }}">
                                        {{ $c->estado === 'anulada' ? 'Reactivar' : 'Anular' }}
                                    </button>
                                </form>
                            @else
                                <span style="font-size:12px;color:#9AA5BD;">Liquidada {{ optional($c->pagada_at)->format('d/m/Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="rv-vacio">Todavía no entró ninguna venta por su link.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function copiarLink() {
    const url = @json($revendedor->link);
    navigator.clipboard.writeText(url).then(function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'Link copiado', type: 'success', timer: 1200, showConfirmButton: false });
        } else {
            alert('Link copiado');
        }
    });
}
</script>
@endsection
