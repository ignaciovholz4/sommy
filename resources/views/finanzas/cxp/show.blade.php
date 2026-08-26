@extends('layouts.admin')

@section('title', 'Cuenta corriente proveedor')

@section('contenido')
<style>
    .cpd-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1150px; margin: 0 auto; }
    .cpd-volver { font-size: 13.5px; color: #2563EB; text-decoration: none; }
    .cpd-head { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin: 10px 0 18px; }
    .cpd-title { font-size: 21px; font-weight: 600; }
    .cpd-sub { font-size: 13px; color: #6E7A96; font-weight: 300; }
    .cpd-acciones { display: flex; gap: 8px; flex-wrap: wrap; }
    .cpd-btn {
        border: none; border-radius: 999px; padding: 9px 22px; font-size: 13px; font-weight: 500; color: #fff; cursor: pointer;
    }
    .cpd-btn.pagar { background: #0d8a4f; }
    .cpd-btn.pagar:hover { background: #0b7a45; }
    .cpd-btn.ajuste { background: #1B2B5A; }
    .cpd-btn.ajuste:hover { background: #2563EB; }

    .cpd-resumen { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
    @media (max-width: 767px) { .cpd-resumen { grid-template-columns: 1fr; } }
    .cpd-kpi { background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 14px 18px; box-shadow: 0 10px 30px rgba(27,43,90,.06); }
    .cpd-kpi .l { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .cpd-kpi .v { font-size: 21px; font-weight: 700; }
    .cpd-kpi.saldo .v { color: #b4552d; }
    .cpd-kpi.saldo.alDia .v { color: #0d8a4f; }

    .cpd-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); overflow-x: auto; margin-bottom: 16px; }
    .cpd-card h3 { font-size: 14px; font-weight: 600; padding: 14px 16px 0; margin: 0; }
    .cpd-table { width: 100%; border-collapse: collapse; }
    .cpd-table th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
        padding: 11px 16px; text-align: left; white-space: nowrap;
    }
    .cpd-table td { padding: 11px 16px; border-bottom: 1px solid #F1F4F9; font-size: 13.5px; }
    .cpd-table .der { text-align: right; }
    .cpd-tipo { display: inline-block; border-radius: 999px; font-size: 11px; font-weight: 600; padding: 3px 12px; }
    .cpd-tipo.debe { background: #FBEDE6; color: #b4552d; }
    .cpd-tipo.haber { background: #DCFCE7; color: #166534; }
    .cpd-estado { display: inline-block; border-radius: 999px; font-size: 11px; font-weight: 600; padding: 3px 12px; }
    .cpd-estado.pendiente { background: #FEF6E7; color: #9a6b0f; }
    .cpd-estado.parcial { background: #E0F2FE; color: #1d4ed8; }
    .cpd-estado.pagado { background: #DCFCE7; color: #166534; }
    .cpd-vencida { color: #c0392b; font-weight: 600; }
</style>

<div class="cpd-wrap">
    <a href="{{ url('finanzas/cxp') }}" class="cpd-volver"><i class="fas fa-arrow-left"></i> Cuentas por pagar</a>
    <div class="cpd-head">
        <div>
            <div class="cpd-title">{{ $proveedor->nombre }}</div>
            <div class="cpd-sub">
                {{ $proveedor->telefono ?: '' }} {{ $proveedor->telefono && $proveedor->email ? '·' : '' }} {{ $proveedor->email ?: '' }}
                @if($proveedor->cuit) · CUIT {{ $proveedor->cuit }} @endif
                · Plazo: {{ $proveedor->condicion_pago_dias > 0 ? $proveedor->condicion_pago_dias . ' días' : 'contado' }}
            </div>
        </div>
        <div class="cpd-acciones">
            @can('haveaccess', 'finanzas.cxp.pagar')
            <button class="cpd-btn pagar" onclick="abrirModalPagoCxp()" {{ $saldo <= 0 ? 'disabled' : '' }}><i class="fas fa-hand-holding-usd"></i> Registrar pago</button>
            @endcan
            @can('haveaccess', 'finanzas.cxp.ajustar')
            <button class="cpd-btn ajuste" onclick="abrirModalAjusteCxp()"><i class="fas fa-sliders-h"></i> Ajuste manual</button>
            @endcan
        </div>
    </div>

    {{-- Resumen --}}
    @php $deudaGlobal = $saldo + $deudaCompras; @endphp
    <div class="cpd-resumen" style="grid-template-columns: repeat(4, 1fr);">
        <div class="cpd-kpi"><div class="l">Total debe</div><div class="v">${{ number_format($debe, 2, ',', '.') }}</div></div>
        <div class="cpd-kpi"><div class="l">Total haber (pagos)</div><div class="v">${{ number_format($haber, 2, ',', '.') }}</div></div>
        <div class="cpd-kpi saldo {{ $deudaCompras <= 0 ? 'alDia' : '' }}">
            <div class="l">Compras a pagar (contado)</div>
            <div class="v">${{ number_format($deudaCompras, 2, ',', '.') }}</div>
        </div>
        <div class="cpd-kpi saldo {{ $deudaGlobal <= 0 ? 'alDia' : '' }}">
            <div class="l">Le debemos (total) {{ $deudaGlobal < 0 ? '(a favor nuestro)' : ($deudaGlobal == 0 ? '— al día' : '') }}</div>
            <div class="v">${{ number_format(abs($deudaGlobal), 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Compras al contado con pago pendiente o parcial --}}
    @if($comprasAPagar->isNotEmpty())
    <div class="cpd-card" style="margin-bottom:16px;">
        <h3><i class="fas fa-shopping-cart" style="color:#b4552d;"></i> Compras a pagar (lo que falta de cada una)</h3>
        <table class="cpd-table">
            <thead>
                <tr>
                    <th>Compra</th>
                    <th>Fecha</th>
                    <th class="der">Total</th>
                    <th class="der">Pagado</th>
                    <th class="der">Falta</th>
                    <th>Pagado desde</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comprasAPagar as $c)
                <tr>
                    <td style="font-weight:600;">{{ $c->num_folio ?: '#' . $c->idcompra }}</td>
                    <td style="color:#6E7A96;">{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
                    <td class="der">${{ number_format($c->total_con_iva, 2, ',', '.') }}</td>
                    <td class="der" style="color:#0d8a4f;font-weight:600;">${{ number_format($c->pagado, 2, ',', '.') }}</td>
                    <td class="der" style="color:#b4552d;font-weight:700;">${{ number_format($c->pendiente, 2, ',', '.') }}</td>
                    <td style="font-size:12px;color:#166534;">
                        @forelse($c->movimientos as $m)
                            {{ optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta' }}: ${{ number_format($m->total, 0, ',', '.') }}<br>
                        @empty — @endforelse
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Deudas pendientes con vencimiento (orden FIFO) --}}
    <div class="cpd-card">
        <h3><i class="fas fa-hourglass-half" style="color:#b4552d;"></i> Deudas pendientes (se cancelan en este orden)</h3>
        <table class="cpd-table">
            <thead>
                <tr><th>Origen</th><th>Descripción</th><th>Vencimiento</th><th>Estado</th><th class="der">Monto</th></tr>
            </thead>
            <tbody>
                @forelse($deudasPendientes as $d)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $d->origen)) }}{{ $d->compra_id ? ' #' . $d->compra_id : '' }}</td>
                    <td>{{ $d->descripcion ?: '—' }}</td>
                    <td class="{{ $d->fecha_vencimiento && $d->fecha_vencimiento->isPast() ? 'cpd-vencida' : '' }}">
                        {{ $d->fecha_vencimiento ? $d->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento' }}
                        @if($d->fecha_vencimiento && $d->fecha_vencimiento->isPast()) (vencida) @endif
                    </td>
                    <td><span class="cpd-estado {{ $d->estado }}">{{ ucfirst($d->estado) }}</span></td>
                    <td class="der" style="font-weight:600;">${{ number_format($d->monto, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:24px;color:#6E7A96;font-weight:300;">Sin deudas pendientes. Cuenta al día.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Historial completo --}}
    <div class="cpd-card">
        <h3><i class="fas fa-list" style="color:#2563EB;"></i> Historial de movimientos</h3>
        <table class="cpd-table">
            <thead>
                <tr><th>Fecha</th><th>Tipo</th><th>Origen</th><th>Descripción</th><th>Vencimiento</th><th>Estado</th><th class="der">Monto</th></tr>
            </thead>
            <tbody>
                @forelse($movimientos as $m)
                <tr>
                    <td style="white-space:nowrap;color:#6E7A96;">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td><span class="cpd-tipo {{ $m->tipo }}">{{ $m->tipo === 'debe' ? 'Debe' : 'Haber' }}</span></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $m->origen)) }}{{ $m->compra_id ? ' #' . $m->compra_id : '' }}</td>
                    <td>{{ $m->descripcion ?: '—' }}</td>
                    <td style="color:#6E7A96;">{{ $m->fecha_vencimiento ? $m->fecha_vencimiento->format('d/m/Y') : '—' }}</td>
                    <td>
                        @if($m->tipo === 'debe')
                            <span class="cpd-estado {{ $m->estado }}">{{ ucfirst($m->estado) }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="der" style="font-weight:600;{{ $m->tipo === 'haber' ? 'color:#0d8a4f;' : '' }}">
                        {{ $m->tipo === 'haber' ? '-' : '' }}${{ number_format($m->monto, 2, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:30px;color:#6E7A96;font-weight:300;">Sin movimientos todavía.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal pago --}}
<div class="modal fade" id="ModalPagoCxp" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formPagoCxp">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar pago a {{ $proveedor->nombre }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Saldo adeudado: <strong style="color:#b4552d;">${{ number_format(max(0, $saldo), 2, ',', '.') }}</strong></p>
                    <div class="form-group">
                        <label>Monto *</label>
                        <input type="number" id="pago_cxp_monto" class="form-control" step="0.01" min="0.01" max="{{ max(0, $saldo) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Cuenta de salida (cajas, bancos o cheques) *</label>
                        <select id="pago_cxp_cuenta" class="form-control" required>
                            <option value="">Cargando cuentas...</option>
                        </select>
                        <small class="text-muted">Si pagás desde una caja, tiene que estar abierta.</small>
                    </div>
                    <div id="campo-cheque-cxp" style="display:none;">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Número de cheque *</label>
                                <input type="text" id="pago_cxp_cheque_numero" class="form-control" maxlength="60">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Banco emisor</label>
                                <input type="text" id="pago_cxp_cheque_banco" class="form-control" maxlength="120">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Fecha de cobro *</label>
                                <input type="date" id="pago_cxp_cheque_fecha" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>A nombre de (opcional)</label>
                                <input type="text" id="pago_cxp_cheque_titular" class="form-control" maxlength="120" placeholder="{{ $proveedor->nombre }} por defecto">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" id="pago_cxp_descripcion" class="form-control" maxlength="255" placeholder="Ej: Pago facturas agosto">
                    </div>
                    <small class="text-muted">El pago se imputa automáticamente a las deudas más viejas (FIFO).</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-dollar-sign"></i> Registrar pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal ajuste --}}
<div class="modal fade" id="ModalAjusteCxp" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formAjusteCxp">
                <div class="modal-header">
                    <h5 class="modal-title">Ajuste manual de cuenta corriente</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tipo *</label>
                            <select id="ajuste_tipo" class="form-control">
                                <option value="debe">Debe (aumenta la deuda)</option>
                                <option value="haber">Haber (reduce la deuda)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Origen *</label>
                            <select id="ajuste_origen" class="form-control">
                                <option value="ajuste">Ajuste</option>
                                <option value="nota_credito">Nota de crédito</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Monto *</label>
                            <input type="number" id="ajuste_monto" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group col-md-6" id="wrap_ajuste_vencimiento">
                            <label>Vencimiento (solo debe)</label>
                            <input type="date" id="ajuste_vencimiento" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción *</label>
                        <input type="text" id="ajuste_descripcion" class="form-control" maxlength="255" placeholder="Ej: Diferencia de precio factura A-0001" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar ajuste</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const URL_PROV = "{{ url('finanzas/cxp/' . $proveedor->idproveedor) }}";
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function abrirModalPagoCxp() {
    $('#campo-cheque-cxp').hide();
    $('#pago_cxp_cheque_numero, #pago_cxp_cheque_banco, #pago_cxp_cheque_fecha, #pago_cxp_cheque_titular').val('');
    cargarCuentasConChequesEnSelect('#pago_cxp_cuenta');
    $('#ModalPagoCxp').modal('show');
}

// Cajas/bancos + cheques de terceros en cartera (para endosar)
function cargarCuentasConChequesEnSelect(selector) {
    $(selector).html('<option value="">Cargando cuentas...</option>');
    Promise.all([
        fetch("{{ url('cuentas-abiertas') }}").then(res => res.json()),
        fetch("{{ url('finanzas/cheques/disponibles') }}").then(res => res.json())
    ]).then(([cuentas, cheques]) => {
        let options = '<option value="">Seleccioná una cuenta</option>';
        (cuentas.cajas || []).forEach(c => {
            options += `<option value="caja-${c.id}">${c.nombre} (Caja · ${c.sucursal || ''} · ${c.moneda})</option>`;
        });
        (cuentas.bancos || []).forEach(b => {
            options += `<option value="banco-${b.id}">${b.nombre} (Banco · ${b.sucursal || ''} · ${b.moneda})</option>`;
        });
        options += '<option value="cheque-nuevo">📝 Cheque propio (nuevo)</option>';
        (cheques.data || []).forEach(ch => {
            options += `<option value="cheque-${ch.id}" data-monto="${ch.monto}">📝 Entregar — ${ch.label}</option>`;
        });
        $(selector).html(options);
    }).catch(() => $(selector).html('<option value="">No se pudieron cargar las cuentas</option>'));
}

function abrirModalAjusteCxp() {
    $('#formAjusteCxp')[0].reset();
    $('#wrap_ajuste_vencimiento').show();
    $('#ModalAjusteCxp').modal('show');
}

$(document).ready(function () {
    $('#ajuste_tipo').on('change', function () {
        $('#wrap_ajuste_vencimiento').toggle(this.value === 'debe');
    });

    $('#pago_cxp_cuenta').on('change', function () {
        $('#campo-cheque-cxp').toggle(this.value === 'cheque-nuevo');
        const esEndoso = this.value.startsWith('cheque-') && this.value !== 'cheque-nuevo';
        $('#pago_cxp_monto').prop('readonly', esEndoso);
        if (esEndoso) {
            const monto = this.selectedOptions[0].dataset.monto;
            if (monto) $('#pago_cxp_monto').val(monto);
        }
    });

    $('#formPagoCxp').on('submit', function (e) {
        e.preventDefault();
        const cuenta = $('#pago_cxp_cuenta').val();
        if (!cuenta) { toastr.warning('Seleccioná una cuenta.'); return; }
        if (cuenta === 'cheque-nuevo' && !$('#pago_cxp_cheque_numero').val()) {
            toastr.warning('Indicá el número del cheque.'); return;
        }

        const payload = {
            monto: $('#pago_cxp_monto').val(),
            cuenta: cuenta,
            descripcion: $('#pago_cxp_descripcion').val() || null
        };
        if (cuenta === 'cheque-nuevo') {
            payload.cheque_numero = $('#pago_cxp_cheque_numero').val();
            payload.cheque_banco = $('#pago_cxp_cheque_banco').val();
            payload.cheque_fecha_cobro = $('#pago_cxp_cheque_fecha').val();
            payload.cheque_titular = $('#pago_cxp_cheque_titular').val();
        }

        fetch(`${URL_PROV}/registrar-pago`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.ok ? res.json() : res.json().then(j => Promise.reject(j)))
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                setTimeout(() => location.reload(), 900);
            } else {
                toastr.error(data.mensaje || 'No se pudo registrar el pago.');
            }
        })
        .catch(err => mostrarErroresCxp(err));
    });

    $('#formAjusteCxp').on('submit', function (e) {
        e.preventDefault();

        fetch(`${URL_PROV}/registrar-ajuste`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({
                tipo: $('#ajuste_tipo').val(),
                origen: $('#ajuste_origen').val(),
                monto: $('#ajuste_monto').val(),
                descripcion: $('#ajuste_descripcion').val(),
                fecha_vencimiento: $('#ajuste_vencimiento').val() || null
            })
        })
        .then(res => res.ok ? res.json() : res.json().then(j => Promise.reject(j)))
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                setTimeout(() => location.reload(), 900);
            } else {
                toastr.error(data.mensaje || 'No se pudo registrar el ajuste.');
            }
        })
        .catch(err => mostrarErroresCxp(err));
    });
});

function mostrarErroresCxp(err) {
    if (err && err.errors) Object.values(err.errors).forEach(msgs => msgs.forEach(m => toastr.error(m)));
    else toastr.error('Error en la petición.');
}

// Cajas abiertas + bancos de todas las sucursales
function cargarCuentasEnSelect(selector) {
    $(selector).html('<option value="">Cargando cuentas...</option>');
    fetch("{{ url('cuentas-abiertas') }}")
        .then(res => res.json())
        .then(data => {
            let options = '<option value="">Seleccioná una cuenta</option>';
            (data.cajas || []).forEach(c => {
                options += `<option value="caja-${c.id}">${c.nombre} (Caja · ${c.sucursal || ''} · ${c.moneda})</option>`;
            });
            (data.bancos || []).forEach(b => {
                options += `<option value="banco-${b.id}">${b.nombre} (Banco · ${b.sucursal || ''} · ${b.moneda})</option>`;
            });
            $(selector).html(options);
        })
        .catch(() => $(selector).html('<option value="">No se pudieron cargar las cuentas</option>'));
}
</script>
@endsection
