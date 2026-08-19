@extends('layouts.admin')

@section('title', 'Envíos')

@section('contenido')
<style>
    .env-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; }
    .env-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; }
    .env-title { font-size: 21px; font-weight: 600; }
    .env-links { display: flex; gap: 8px; flex-wrap: wrap; }
    .env-link {
        border: 1.5px solid #E7EAF2; color: #47536F; background: #fff; border-radius: 999px;
        padding: 7px 16px; font-size: 12.5px; font-weight: 500; text-decoration: none;
    }
    .env-link:hover { border-color: #2563EB; color: #2563EB; text-decoration: none; }

    .env-board { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; align-items: start; }
    @media (max-width: 1300px) { .env-board { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 767px)  { .env-board { grid-template-columns: 1fr; } }

    .env-col { background: #F1F4F9; border-radius: 16px; padding: 10px; }
    .env-col-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 8px 10px; font-size: 12.5px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em; color: #47536F;
    }
    .env-col-head .num {
        background: #1B2B5A; color: #fff; border-radius: 999px; min-width: 24px; height: 24px;
        display: inline-flex; align-items: center; justify-content: center; font-size: 12px;
    }
    .env-col.c1 .env-col-head i { color: #b4552d; }
    .env-col.c2 .env-col-head i { color: #2563EB; }
    .env-col.c3 .env-col-head i { color: #0EA5E9; }
    .env-col.c4 .env-col-head i { color: #0d8a4f; }

    .env-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        box-shadow: 0 6px 18px rgba(27,43,90,.06); padding: 13px 14px; margin-bottom: 10px;
    }
    .env-card .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; gap: 6px; }
    .env-card .pid { font-weight: 700; font-size: 14.5px; }
    .env-card .pid a { color: #2563EB; text-decoration: none; }
    .env-card .badge-est {
        border-radius: 999px; font-size: 10.5px; font-weight: 600; padding: 3px 10px;
        background: #E0F2FE; color: #1B2B5A; white-space: nowrap;
    }
    .env-card .badge-est.vmanual { background: #FEF3C7; color: #92400E; }
    .env-card .badge-est.transito { background: #DBEAFE; color: #1D4ED8; }
    .env-card .badge-est.entregado { background: #DCFCE7; color: #166534; }
    .env-card .badge-est.fallido { background: #FEE2E2; color: #991B1B; }
    .env-card .dato { font-size: 12.5px; color: #47536F; line-height: 1.55; }
    .env-card .dato i { width: 15px; text-align: center; color: #94A3B8; }
    .env-card .dato a { color: #0d8a4f; text-decoration: none; }
    .env-card .monto { font-weight: 700; font-size: 14px; margin-top: 4px; }
    .env-card .flete {
        margin-top: 7px; background: #F8FAFC; border: 1px solid #E7EAF2; border-radius: 10px;
        padding: 7px 10px; font-size: 12px; color: #47536F;
    }
    .env-card .flete b { color: #1B2B5A; }

    .env-btns { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
    .env-btn {
        border: none; border-radius: 999px; padding: 7px 14px; font-size: 12px; font-weight: 500;
        cursor: pointer; background: #1B2B5A; color: #fff;
    }
    .env-btn:hover { background: #2563EB; }
    .env-btn.sec { background: #E0F2FE; color: #1B2B5A; }
    .env-btn.sec:hover { background: #cfe9fb; }
    .env-btn.rojo { background: #fff; color: #b4552d; border: 1.5px solid #f3d9cc; }
    .env-btn.rojo:hover { background: #FBEDE6; }
    .env-btn:disabled { opacity: .55; cursor: not-allowed; }
    .env-vacio { text-align: center; color: #94A3B8; font-size: 12.5px; font-weight: 300; padding: 22px 8px; }

    /* Modal asignar flete */
    #modalFlete .modal-content { border-radius: 16px; border: none; font-family: 'Poppins', sans-serif; color: #1B2B5A; }
    #modalFlete label { font-size: 12.5px; font-weight: 500; margin: 8px 0 3px; }
    #modalFlete input, #modalFlete select, #modalFlete textarea {
        width: 100%; border: 1px solid #E7EAF2; border-radius: 10px; padding: 8px 12px; font-size: 13.5px; color: #1B2B5A;
    }
    #modalFlete .pagador { display: flex; gap: 8px; }
    #modalFlete .pagador label {
        flex: 1; border: 1.5px solid #E7EAF2; border-radius: 999px; text-align: center;
        padding: 8px; cursor: pointer; font-weight: 500; margin: 0;
    }
    #modalFlete .pagador input { display: none; }
    #modalFlete .pagador label.on { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }
    #modalFlete .pista { font-size: 11px; color: #6E7A96; font-weight: 300; margin-top: 3px; }
</style>

<div class="env-wrap">
    <div class="env-head">
        <div class="env-title"><i class="fas fa-truck" style="color:#2563EB;"></i> Envíos</div>
        <div class="env-links">
            <a class="env-link" href="{{ url('envios/ruta') }}" style="border-color:#2563EB;color:#2563EB;font-weight:600;"><i class="fas fa-route"></i> Hoja de ruta del día</a>
            <a class="env-link" href="{{ url('envios/mapa') }}" style="border-color:#0d8a4f;color:#0d8a4f;font-weight:600;"><i class="fas fa-satellite-dish"></i> Reparto en vivo</a>
            <a class="env-link" href="{{ url('zonas-envio') }}"><i class="fas fa-map-marked-alt"></i> Zonas de envío</a>
            <a class="env-link" href="{{ route('finanzas.transportistas.index') }}"><i class="fas fa-id-card"></i> Fleteros</a>
            <a class="env-link" href="{{ route('finanzas.envios.index') }}"><i class="fas fa-list"></i> Listado completo (incluye compras)</a>
        </div>
    </div>

    <div class="env-board">
        {{-- 1 · Listos, sin flete: pedidos ecommerce + ventas manuales --}}
        <div class="env-col c1">
            <div class="env-col-head"><span><i class="fas fa-box-open"></i> Para asignar flete</span><span class="num">{{ $paraAsignar->count() + $ventasParaAsignar->count() }}</span></div>

            @foreach($paraAsignar as $o)
            @php
                $dirPedido = trim(implode(', ', array_filter([
                    optional($o->cliente)->direccion,
                    $o->direccion_localidad,
                    $o->direccion_provincia,
                ])));
            @endphp
            <div class="env-card">
                <div class="top">
                    <span class="pid"><a href="{{ url('orders/order/' . $o->order_id) }}">Pedido #{{ $o->order_id }}</a></span>
                    <span class="badge-est">{{ optional($o->status)->status_name ?: '—' }}</span>
                </div>
                <div class="dato"><i class="fas fa-user"></i> {{ optional($o->cliente)->nombre ?: 'Cliente' }}</div>
                @if(optional($o->cliente)->telefono)
                    <div class="dato"><i class="fab fa-whatsapp"></i> <a href="https://wa.me/{{ preg_replace('/\D/', '', $o->cliente->telefono) }}" target="_blank" rel="noopener noreferrer">{{ $o->cliente->telefono }}</a></div>
                @endif
                <div class="dato"><i class="fas fa-map-marker-alt"></i> {{ $dirPedido ?: 'Sin dirección' }}</div>
                <div class="monto">${{ number_format($o->total_amount, 2, ',', '.') }}</div>
                <div class="env-btns">
                    <button class="env-btn" onclick="abrirModalFlete('pedido', {{ $o->order_id }}, this.dataset.dir)" data-dir="{{ $dirPedido }}">
                        <i class="fas fa-truck"></i> Asignar flete
                    </button>
                    <a class="env-btn sec" href="{{ url('etiqueta/pedido/' . $o->order_id) }}" target="_blank" title="Etiqueta 10x15 para impresora térmica"><i class="fas fa-tag"></i> Etiqueta</a>
                </div>
            </div>
            @endforeach

            @foreach($ventasParaAsignar as $v)
            @php
                $dirVenta = trim(implode(', ', array_filter([
                    optional($v->cliente)->direccion,
                    optional($v->cliente)->localidad,
                    optional($v->cliente)->provincia,
                ])));
            @endphp
            <div class="env-card">
                <div class="top">
                    <span class="pid"><a href="{{ url('ventas?ver=' . $v->idventa) }}">Venta #{{ $v->idventa }}</a></span>
                    <span class="badge-est vmanual">Venta manual</span>
                </div>
                <div class="dato"><i class="fas fa-user"></i> {{ trim(optional($v->cliente)->nombre . ' ' . optional($v->cliente)->paterno) ?: 'Cliente' }}</div>
                @if(optional($v->cliente)->telefono)
                    <div class="dato"><i class="fab fa-whatsapp"></i> <a href="https://wa.me/{{ preg_replace('/\D/', '', $v->cliente->telefono) }}" target="_blank" rel="noopener noreferrer">{{ $v->cliente->telefono }}</a></div>
                @endif
                <div class="dato"><i class="fas fa-map-marker-alt"></i> {{ $dirVenta ?: 'Sin dirección' }}</div>
                <div class="monto">${{ number_format($v->total_con_iva, 2, ',', '.') }}</div>
                <div class="env-btns">
                    <button class="env-btn" onclick="abrirModalFlete('venta', {{ $v->idventa }}, this.dataset.dir)" data-dir="{{ $dirVenta }}">
                        <i class="fas fa-truck"></i> Asignar flete
                    </button>
                    <a class="env-btn sec" href="{{ url('etiqueta/venta/' . $v->idventa) }}" target="_blank" title="Etiqueta 10x15 para impresora térmica"><i class="fas fa-tag"></i> Etiqueta</a>
                </div>
            </div>
            @endforeach

            @if($paraAsignar->isEmpty() && $ventasParaAsignar->isEmpty())
            <div class="env-vacio">Sin pedidos ni ventas esperando flete.<br>Acá entran solos al vender o comprobar stock.</div>
            @endif
        </div>

        {{-- 2 · Flete asignado --}}
        <div class="env-col c2">
            <div class="env-col-head"><span><i class="fas fa-dolly"></i> Para despachar</span><span class="num">{{ $paraDespachar->count() }}</span></div>
            @forelse($paraDespachar as $e)
            <div class="env-card">
                <div class="top">
                    @if($e->order_ecommerce_id)
                        <span class="pid"><a href="{{ url('orders/order/' . $e->order_ecommerce_id) }}">Pedido #{{ $e->order_ecommerce_id }}</a></span>
                    @else
                        <span class="pid"><a href="{{ url('ventas?ver=' . $e->venta_id) }}">Venta #{{ $e->venta_id }}</a></span>
                    @endif
                </div>
                <div class="dato"><i class="fas fa-user"></i> {{ optional(optional($e->orden)->cliente ?? optional($e->venta)->cliente)->nombre ?: '—' }}</div>
                <div class="dato"><i class="fas fa-map-marker-alt"></i> {{ $e->direccion_entrega ?: 'Sin dirección' }}</div>
                <div class="flete">
                    <b><i class="fas fa-truck"></i> {{ optional($e->transportista)->nombre ?: 'Sin fletero' }}</b><br>
                    Flete ${{ number_format($e->costo, 2, ',', '.') }} · paga {{ $e->pagado_por === 'empresa' ? 'la empresa' : 'el cliente' }}
                    @if($e->fecha_entrega_estimada)<br>Entrega estimada: {{ $e->fecha_entrega_estimada->format('d/m/Y') }}@endif
                </div>
                <div class="env-btns">
                    <button class="env-btn" onclick="etapa({{ $e->id }}, 'despachar', this)"><i class="fas fa-arrow-right"></i> Despachar</button>
                    <a class="env-btn sec" href="{{ $e->order_ecommerce_id ? url('etiqueta/pedido/' . $e->order_ecommerce_id) : url('etiqueta/venta/' . $e->venta_id) }}" target="_blank" title="Etiqueta 10x15 para impresora térmica"><i class="fas fa-tag"></i> Etiqueta</a>
                    <button class="env-btn rojo" onclick="quitarFlete({{ $e->id }}, this)" title="Vuelve a la etapa anterior">Quitar flete</button>
                </div>
            </div>
            @empty
            <div class="env-vacio">Nada esperando despacho.</div>
            @endforelse
        </div>

        {{-- 3 · En camino --}}
        <div class="env-col c3">
            <div class="env-col-head"><span><i class="fas fa-shipping-fast"></i> En camino</span><span class="num">{{ $enCamino->count() }}</span></div>
            @forelse($enCamino as $e)
            <div class="env-card">
                <div class="top">
                    @if($e->order_ecommerce_id)
                        <span class="pid"><a href="{{ url('orders/order/' . $e->order_ecommerce_id) }}">Pedido #{{ $e->order_ecommerce_id }}</a></span>
                    @else
                        <span class="pid"><a href="{{ url('ventas?ver=' . $e->venta_id) }}">Venta #{{ $e->venta_id }}</a></span>
                    @endif
                    <span class="badge-est {{ $e->estado === 'en_transito' ? 'transito' : '' }}">{{ $e->estado === 'en_transito' ? 'En tránsito' : 'Despachado' }}</span>
                </div>
                <div class="dato"><i class="fas fa-user"></i> {{ optional(optional($e->orden)->cliente ?? optional($e->venta)->cliente)->nombre ?: '—' }}</div>
                <div class="flete">
                    <b><i class="fas fa-truck"></i> {{ optional($e->transportista)->nombre ?: '—' }}</b>
                    @if($e->tracking)<br>Tracking: {{ $e->tracking }}@endif
                    @if($e->fecha_despacho)<br>Salió el {{ $e->fecha_despacho->format('d/m/Y H:i') }}@endif
                </div>
                <div class="env-btns">
                    @if($e->estado === 'despachado')
                        <button class="env-btn sec" onclick="etapa({{ $e->id }}, 'transito', this)">En tránsito</button>
                    @endif
                    <button class="env-btn" onclick="etapa({{ $e->id }}, 'entregar', this)"><i class="fas fa-check"></i> Entregado</button>
                    <a class="env-btn sec" href="{{ $e->order_ecommerce_id ? url('etiqueta/pedido/' . $e->order_ecommerce_id) : url('etiqueta/venta/' . $e->venta_id) }}" target="_blank" title="Etiqueta 10x15"><i class="fas fa-tag"></i> Etiqueta</a>
                    <button class="env-btn rojo" onclick="etapa({{ $e->id }}, 'fallido', this)">Fallido</button>
                </div>
            </div>
            @empty
            <div class="env-vacio">Nada en la calle ahora.</div>
            @endforelse
        </div>

        {{-- 4 · Finalizados --}}
        <div class="env-col c4">
            <div class="env-col-head"><span><i class="fas fa-flag-checkered"></i> Finalizados (30 días)</span><span class="num">{{ $finalizados->count() }}</span></div>
            @forelse($finalizados as $e)
            <div class="env-card">
                <div class="top">
                    @if($e->order_ecommerce_id)
                        <span class="pid"><a href="{{ url('orders/order/' . $e->order_ecommerce_id) }}">Pedido #{{ $e->order_ecommerce_id }}</a></span>
                    @else
                        <span class="pid"><a href="{{ url('ventas?ver=' . $e->venta_id) }}">Venta #{{ $e->venta_id }}</a></span>
                    @endif
                    <span class="badge-est {{ $e->estado }}">{{ $e->estado === 'entregado' ? 'Entregado' : 'Fallido' }}</span>
                </div>
                <div class="dato"><i class="fas fa-user"></i> {{ optional(optional($e->orden)->cliente ?? optional($e->venta)->cliente)->nombre ?: '—' }}</div>
                <div class="dato"><i class="fas fa-truck"></i> {{ optional($e->transportista)->nombre ?: '—' }}
                    @if($e->fecha_entrega_real) · {{ $e->fecha_entrega_real->format('d/m/Y') }}@endif
                </div>
                @if($e->estado === 'fallido')
                <div class="env-btns">
                    <button class="env-btn sec" onclick="etapa({{ $e->id }}, 'despachar', this)">Reintentar envío</button>
                </div>
                @endif
            </div>
            @empty
            <div class="env-vacio">Sin entregas en el último mes.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal asignar flete --}}
<div class="modal fade" id="modalFlete" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-truck" style="color:#2563EB;"></i> Asignar flete <span id="fleteNumPedido"></span></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fleteOrderId">
                <input type="hidden" id="fleteVentaId">

                <label>Fletero / transportista</label>
                <select id="fleteTransportista">
                    @foreach($transportistas as $t)
                        <option value="{{ $t->id }}">{{ $t->nombre }}{{ $t->telefono ? ' — ' . $t->telefono : '' }}</option>
                    @endforeach
                </select>
                @if($transportistas->isEmpty())
                    <div style="font-size:12px;color:#b4552d;margin-top:4px;">No hay fleteros cargados: agregalos en <a href="{{ route('finanzas.transportistas.index') }}">Fleteros</a>.</div>
                @endif

                <label>Costo del flete $</label>
                <input type="number" id="fleteCosto" step="0.01" min="0" placeholder="0.00">

                <label>¿Quién paga el flete?</label>
                <div class="pagador" id="fletePagador">
                    <label class="on" data-v="cliente">Cliente</label>
                    <label data-v="empresa">Empresa</label>
                </div>

                <div id="fleteCuentaWrap" style="display:none;">
                    <label>¿De qué caja o banco sale la plata?</label>
                    <select id="fleteCuenta">
                        <option value="">Pagar después (queda pendiente en Gastos › Fletes)</option>
                    </select>
                    <div class="pista">Si elegís una cuenta, el gasto del flete se paga en el acto y se registra el egreso.</div>
                </div>

                <label>Dirección de entrega (calle, localidad, provincia)</label>
                <input type="text" id="fleteDireccion" placeholder="Calle y número, localidad, provincia">

                <label>Fecha de entrega estimada</label>
                <input type="date" id="fleteFecha">

                <label>Tracking / observaciones (opcional)</label>
                <input type="text" id="fleteTracking" placeholder="Nro de seguimiento, horario pactado...">
            </div>
            <div class="modal-footer" style="border-top:1px solid #E7EAF2;">
                <button type="button" class="env-btn sec" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="env-btn" id="fleteGuardar" onclick="guardarFlete(this)"><i class="fas fa-save"></i> Asignar flete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const CSRF_ENVIOS = '{{ csrf_token() }}';
let pagadorFlete = 'cliente';
let cuentasCargadas = false;

document.querySelectorAll('#fletePagador label').forEach(l => l.addEventListener('click', () => {
    document.querySelectorAll('#fletePagador label').forEach(x => x.classList.remove('on'));
    l.classList.add('on');
    pagadorFlete = l.dataset.v;
    document.getElementById('fleteCuentaWrap').style.display = pagadorFlete === 'empresa' ? '' : 'none';
    if (pagadorFlete === 'empresa') cargarCuentas();
}));

// Cajas abiertas y bancos, mismo origen que usa el módulo de Gastos
function cargarCuentas() {
    if (cuentasCargadas) return;
    fetch('{{ url('cuentas-abiertas') }}')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('fleteCuenta');
            (data.cajas || []).forEach(c => {
                sel.innerHTML += `<option value="caja-${c.id}">${c.nombre} (Caja · ${c.sucursal || ''} · ${c.moneda})</option>`;
            });
            (data.bancos || []).forEach(b => {
                sel.innerHTML += `<option value="banco-${b.id}">${b.nombre} (Banco · ${b.sucursal || ''} · ${b.moneda})</option>`;
            });
            cuentasCargadas = true;
        })
        .catch(() => {});
}

function abrirModalFlete(tipo, id, direccion) {
    document.getElementById('fleteOrderId').value = tipo === 'pedido' ? id : '';
    document.getElementById('fleteVentaId').value = tipo === 'venta' ? id : '';
    document.getElementById('fleteNumPedido').textContent = (tipo === 'pedido' ? 'al pedido #' : 'a la venta #') + id;
    document.getElementById('fleteDireccion').value = direccion || '';
    $('#modalFlete').modal('show');
}

function postEnvios(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_ENVIOS, 'Accept': 'application/json' },
        body: JSON.stringify(body)
    }).then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok || data.estado === 0) {
            const msg = data.mensaje || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Error del servidor');
            throw new Error(msg);
        }
        return data;
    });
}

function guardarFlete(btn) {
    btn.disabled = true;
    postEnvios('{{ route('envios.asignar') }}', {
        order_ecommerce_id: document.getElementById('fleteOrderId').value || null,
        venta_id: document.getElementById('fleteVentaId').value || null,
        transportista_id: document.getElementById('fleteTransportista').value,
        costo: document.getElementById('fleteCosto').value || 0,
        pagado_por: pagadorFlete,
        cuenta: pagadorFlete === 'empresa' ? (document.getElementById('fleteCuenta').value || null) : null,
        direccion_entrega: document.getElementById('fleteDireccion').value,
        fecha_entrega_estimada: document.getElementById('fleteFecha').value || null,
        tracking: document.getElementById('fleteTracking').value
    }).then(() => location.reload())
      .catch(e => { alert('No se pudo asignar el flete: ' + e.message); btn.disabled = false; });
}

function etapa(envioId, accion, btn) {
    const mensajes = {
        despachar: '¿Despachar este envío? Si es un pedido, pasa a "Enviado" y se le avisa al cliente por mail.',
        entregar: '¿Confirmar la entrega?',
        fallido: '¿Marcar el envío como fallido?'
    };
    if (mensajes[accion] && !confirm(mensajes[accion])) return;
    btn.disabled = true;
    postEnvios('{{ url('envios') }}/' + envioId + '/etapa', { accion: accion })
        .then(() => location.reload())
        .catch(e => { alert('No se pudo avanzar la etapa: ' + e.message); btn.disabled = false; });
}

function quitarFlete(envioId, btn) {
    if (!confirm('¿Quitar el flete? Vuelve a "Para asignar flete".')) return;
    btn.disabled = true;
    fetch('{{ url('finanzas/envios') }}/' + envioId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF_ENVIOS, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.estado === 1) location.reload();
        else { alert(data.mensaje || 'No se pudo quitar el flete'); btn.disabled = false; }
    });
}
</script>
@endsection
