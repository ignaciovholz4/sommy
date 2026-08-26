@extends('layouts.admin')

@section('title', 'Compra/venta de divisas')

@section('contenido')
<div class="container-fluid" style="padding: 18px 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;"><i class="fas fa-exchange-alt" style="color:#2563EB;"></i> Compra/venta de divisas</h4>
        @can('haveaccess', 'finanzas.divisas.manage')
        <button class="btn btn-primary" onclick="abrirModalOperacion('compra')"><i class="fas fa-plus"></i> Comprar</button>
        <button class="btn btn-outline-primary" onclick="abrirModalOperacion('venta')"><i class="fas fa-minus"></i> Vender</button>
        @endcan
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-sm" style="width:100%;">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Moneda</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">Cotización</th>
                        <th class="text-right">Total ARS</th>
                        <th>Cuentas</th>
                        <th class="text-right">Resultado</th>
                    </tr>
                </thead>
                <tbody id="divisas_body">
                    <tr><td colspan="8" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal operación --}}
<div class="modal fade" id="ModalOperacionCambio" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formOperacionCambio">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalOperacion">Comprar moneda extranjera</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="op_tipo" value="compra">
                    <div class="form-group">
                        <label>Moneda *</label>
                        <select id="op_moneda_id" class="form-control" required>
                            <option value="">Seleccioná una moneda</option>
                            @foreach($monedas as $m)
                                <option value="{{ $m->id }}">{{ $m->codigo }} — {{ $m->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="op_disponible_wrap" style="display:none;">
                        <small class="text-muted">Disponible para vender: <b id="op_disponible_cantidad"></b> (costo promedio $<span id="op_disponible_costo"></span> por unidad)</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label id="op_label_cuenta_ars">Cuenta en pesos (de donde sale) *</label>
                            <select id="op_cuenta_ars_id" class="form-control" required>
                                <option value="">Elegí primero la moneda</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label id="op_label_cuenta_moneda">Cuenta en moneda extranjera (a donde entra) *</label>
                            <select id="op_cuenta_moneda_id" class="form-control" required>
                                <option value="">Elegí primero la moneda</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label id="op_label_monto">Monto en moneda extranjera *</label>
                            <input type="number" id="op_monto_moneda" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Cotización (pesos por unidad) *</label>
                            <input type="number" id="op_cotizacion" class="form-control" step="0.0001" min="0.0001" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Total en pesos</label>
                        <input type="text" id="op_total_ars" class="form-control" readonly>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Fecha</label>
                            <input type="date" id="op_fecha" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Observaciones</label>
                            <input type="text" id="op_observaciones" class="form-control" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const URL_FINANZAS = "{{ url('finanzas') }}";
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function moneyFmt(n) {
    return '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

function cargarDivisas() {
    $('#divisas_body').html('<tr><td colspan="8" class="text-center text-muted py-4">Cargando...</td></tr>');
    fetch(`${URL_FINANZAS}/divisas/data`)
        .then(res => res.json())
        .then(data => {
            if (!data.estado || !data.data.length) {
                $('#divisas_body').html('<tr><td colspan="8" class="text-center text-muted py-4">Todavía no hay operaciones registradas.</td></tr>');
                return;
            }
            $('#divisas_body').html(data.data.map(filaOperacion).join(''));
        });
}

function filaOperacion(o) {
    const tipoBadge = o.tipo === 'compra'
        ? '<span class="badge badge-success">Compra</span>'
        : '<span class="badge badge-info">Venta</span>';
    let resultado = '—';
    if (o.resultado !== null) {
        const cls = o.resultado >= 0 ? 'text-success' : 'text-danger';
        resultado = `<span class="${cls} font-weight-bold">${moneyFmt(o.resultado)}</span>`;
    }
    return `<tr>
        <td>${esc(o.fecha)}</td>
        <td>${tipoBadge}</td>
        <td>${esc(o.moneda)}</td>
        <td class="text-right">${Number(o.monto_moneda).toLocaleString('es-AR', {minimumFractionDigits:2})}</td>
        <td class="text-right">${moneyFmt(o.cotizacion)}</td>
        <td class="text-right">${moneyFmt(o.monto_ars)}</td>
        <td>${esc(o.cuenta_ars)} ↔ ${esc(o.cuenta_moneda)}</td>
        <td class="text-right">${resultado}</td>
    </tr>`;
}

function abrirModalOperacion(tipo) {
    $('#formOperacionCambio')[0].reset();
    $('#op_tipo').val(tipo);
    $('#op_disponible_wrap').hide();
    $('#tituloModalOperacion').text(tipo === 'compra' ? 'Comprar moneda extranjera' : 'Vender moneda extranjera');
    $('#op_label_cuenta_ars').text(tipo === 'compra' ? 'Cuenta en pesos (de donde sale) *' : 'Cuenta en pesos (a donde entra) *');
    $('#op_label_cuenta_moneda').text(tipo === 'compra' ? 'Cuenta en moneda extranjera (a donde entra) *' : 'Cuenta en moneda extranjera (de donde sale) *');
    $('#op_label_monto').text(tipo === 'venta' ? 'Monto a vender *' : 'Monto a comprar *');
    $('#op_cuenta_ars_id, #op_cuenta_moneda_id').html('<option value="">Elegí primero la moneda</option>');
    $('#op_fecha').val(new Date().toISOString().slice(0, 10));
    $('#ModalOperacionCambio').modal('show');
}

function cargarFormData() {
    const monedaId = $('#op_moneda_id').val();
    if (!monedaId) return;

    fetch(`${URL_FINANZAS}/divisas/form-data?moneda_id=${monedaId}`)
        .then(res => res.json())
        .then(data => {
            const optsArs = (data.cuentas_ars || []).map(c => `<option value="${c.id}">${esc(c.nombre)}</option>`).join('');
            const optsMoneda = (data.cuentas_moneda || []).map(c => `<option value="${c.id}">${esc(c.nombre)}</option>`).join('');
            $('#op_cuenta_ars_id').html('<option value="">Seleccioná una cuenta</option>' + optsArs);
            $('#op_cuenta_moneda_id').html('<option value="">Seleccioná una cuenta</option>' + optsMoneda);

            if ($('#op_tipo').val() === 'venta') {
                $('#op_disponible_cantidad').text(Number(data.disponible.cantidad).toLocaleString('es-AR', {minimumFractionDigits:2}));
                $('#op_disponible_costo').text(Number(data.disponible.costo_promedio).toLocaleString('es-AR', {minimumFractionDigits:2}));
                $('#op_disponible_wrap').show();
            } else {
                $('#op_disponible_wrap').hide();
            }
        });
}

function recalcularTotal() {
    const monto = parseFloat($('#op_monto_moneda').val()) || 0;
    const cotizacion = parseFloat($('#op_cotizacion').val()) || 0;
    $('#op_total_ars').val(moneyFmt(monto * cotizacion));
}

$(document).ready(function () {
    $('#op_moneda_id').on('change', cargarFormData);
    $('#op_monto_moneda, #op_cotizacion').on('input', recalcularTotal);

    $('#formOperacionCambio').on('submit', function (e) {
        e.preventDefault();

        const payload = {
            tipo: $('#op_tipo').val(),
            moneda_id: $('#op_moneda_id').val(),
            cuenta_ars_id: $('#op_cuenta_ars_id').val(),
            cuenta_moneda_id: $('#op_cuenta_moneda_id').val(),
            monto_moneda: $('#op_monto_moneda').val(),
            cotizacion: $('#op_cotizacion').val(),
            fecha: $('#op_fecha').val(),
            observaciones: $('#op_observaciones').val(),
        };

        if (!payload.moneda_id || !payload.cuenta_ars_id || !payload.cuenta_moneda_id) {
            toastr.warning('Completá la moneda y las dos cuentas.');
            return;
        }
        if (payload.cuenta_ars_id === payload.cuenta_moneda_id) {
            toastr.warning('La cuenta en pesos y la cuenta en moneda extranjera tienen que ser distintas.');
            return;
        }

        fetch(`${URL_FINANZAS}/divisas`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje + (data.resultado !== null && data.resultado !== undefined ? ' Resultado: ' + moneyFmt(data.resultado) : ''));
                $('#ModalOperacionCambio').modal('hide');
                cargarDivisas();
            } else {
                toastr.error(data.mensaje || 'No se pudo registrar la operación.');
            }
        })
        .catch(() => toastr.error('Error en la petición.'));
    });

    cargarDivisas();
});
</script>
@endsection
