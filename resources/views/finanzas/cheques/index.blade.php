@extends('layouts.admin')

@section('title', 'Cartera de cheques')

@section('contenido')
<div class="container-fluid" style="padding: 18px 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;"><i class="fas fa-money-check-alt" style="color:#2563EB;"></i> Cartera de cheques</h4>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="form-row align-items-end">
                <div class="col-md-3 col-6 mb-2">
                    <label class="mb-0 small text-muted">Tipo</label>
                    <select id="filtro_tipo" class="form-control form-control-sm">
                        <option value="todos">Todos</option>
                        <option value="tercero">De terceros (recibidos)</option>
                        <option value="propio">Propios (emitidos)</option>
                    </select>
                </div>
                <div class="col-md-4 col-6 mb-2">
                    <label class="mb-0 small text-muted">Estado</label>
                    <select id="filtro_estado" class="form-control form-control-sm">
                        <option value="en_cartera" selected>En cartera</option>
                        <option value="depositado">Depositados</option>
                        <option value="acreditado">Acreditados</option>
                        <option value="rechazado">Rechazados</option>
                        <option value="entregado">Entregados (endosados)</option>
                        <option value="anulado">Anulados</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div class="col-md-3 col-12 mb-2">
                    <button class="btn btn-sm btn-primary" onclick="cargarCheques()"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-sm" style="width:100%;">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Número</th>
                        <th>Banco emisor</th>
                        <th>Contraparte</th>
                        <th class="text-right">Monto</th>
                        <th>Fecha de cobro</th>
                        <th>Estado</th>
                        <th>Cuenta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cheques_body">
                    <tr><td colspan="9" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal depositar --}}
<div class="modal fade" id="ModalDepositarCheque" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formDepositarCheque">
                <div class="modal-header">
                    <h5 class="modal-title">Depositar cheque</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="depositar_cheque_id">
                    <div class="form-group">
                        <label>Banco donde se deposita *</label>
                        <select id="depositar_cuenta_id" class="form-control" required>
                            <option value="">Cargando bancos...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Depositar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal rechazar --}}
<div class="modal fade" id="ModalRechazarCheque" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formRechazarCheque">
                <div class="modal-header">
                    <h5 class="modal-title">Rechazar cheque</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rechazar_cheque_id">
                    <div class="form-group">
                        <label>Motivo (opcional)</label>
                        <input type="text" id="rechazar_motivo" class="form-control" maxlength="255" placeholder="Ej: fondos insuficientes">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar</button>
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

function esc(s) {
    return $('<div>').text(s == null ? '' : s).html();
}

const ESTADO_LABEL = {
    en_cartera: '<span class="badge badge-warning">En cartera</span>',
    depositado: '<span class="badge badge-info">Depositado</span>',
    acreditado: '<span class="badge badge-success">Acreditado</span>',
    rechazado: '<span class="badge badge-danger">Rechazado</span>',
    entregado: '<span class="badge badge-secondary">Entregado</span>',
    anulado: '<span class="badge badge-light">Anulado</span>',
};

function cargarCheques() {
    $('#cheques_body').html('<tr><td colspan="9" class="text-center text-muted py-4">Cargando...</td></tr>');

    const params = new URLSearchParams({
        tipo: $('#filtro_tipo').val(),
        estado: $('#filtro_estado').val(),
    });

    fetch(`${URL_FINANZAS}/cheques/data?${params}`)
        .then(res => res.json())
        .then(data => {
            if (!data.estado || !data.data.length) {
                $('#cheques_body').html('<tr><td colspan="9" class="text-center text-muted py-4">No hay cheques en esta vista.</td></tr>');
                return;
            }
            $('#cheques_body').html(data.data.map(filaCheque).join(''));
        });
}

function filaCheque(c) {
    let acciones = '';
    if (c.estado === 'en_cartera' && c.tipo === 'tercero') {
        acciones += `<button class="btn btn-sm btn-primary" onclick="abrirModalDepositar(${c.id})">Depositar</button> `;
    }
    if (c.estado === 'en_cartera' || c.estado === 'depositado') {
        acciones += `<button class="btn btn-sm btn-success" onclick="acreditarCheque(${c.id})">Acreditar</button> `;
        acciones += `<button class="btn btn-sm btn-outline-danger" onclick="abrirModalRechazar(${c.id})">Rechazar</button> `;
    }
    if (c.estado === 'en_cartera') {
        acciones += `<button class="btn btn-sm btn-outline-secondary" onclick="anularCheque(${c.id})">Anular</button>`;
    }

    const vencidoTag = c.vencido ? ' <i class="fas fa-exclamation-triangle text-danger" title="Vencido"></i>' : '';

    return `<tr>
        <td>${c.tipo === 'tercero' ? 'De tercero' : 'Propio'}</td>
        <td>${esc(c.numero)}</td>
        <td>${esc(c.banco_emisor)}</td>
        <td>${esc(c.contraparte_nombre)}</td>
        <td class="text-right">${moneyFmt(c.monto)}</td>
        <td>${esc(c.fecha_cobro)}${vencidoTag}</td>
        <td>${ESTADO_LABEL[c.estado] || esc(c.estado)}</td>
        <td>${esc(c.cuenta)}</td>
        <td>${acciones}</td>
    </tr>`;
}

function abrirModalDepositar(id) {
    $('#depositar_cheque_id').val(id);
    $('#depositar_cuenta_id').html('<option value="">Cargando bancos...</option>');
    fetch("{{ url('cuentas-abiertas') }}")
        .then(res => res.json())
        .then(data => {
            let options = '<option value="">Seleccioná un banco</option>';
            (data.bancos || []).forEach(b => {
                options += `<option value="${b.id}">${b.nombre}</option>`;
            });
            $('#depositar_cuenta_id').html(options);
        });
    $('#ModalDepositarCheque').modal('show');
}

function acreditarCheque(id) {
    fetch(`${URL_FINANZAS}/cheques/${id}/acreditar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } })
        .then(res => res.json())
        .then(data => {
            if (data.estado === 1) { toastr.success(data.mensaje); cargarCheques(); }
            else { toastr.error(data.mensaje || 'No se pudo acreditar el cheque.'); }
        });
}

function anularCheque(id) {
    Swal.fire({
        title: '¿Anular el cheque?',
        text: 'Esta acción no se puede deshacer.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.value) return;
        fetch(`${URL_FINANZAS}/cheques/${id}/anular`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } })
            .then(res => res.json())
            .then(data => {
                if (data.estado === 1) { toastr.success(data.mensaje); cargarCheques(); }
                else { toastr.error(data.mensaje || 'No se pudo anular el cheque.'); }
            });
    });
}

function abrirModalRechazar(id) {
    $('#rechazar_cheque_id').val(id);
    $('#rechazar_motivo').val('');
    $('#ModalRechazarCheque').modal('show');
}

$(document).ready(function () {
    $('#formDepositarCheque').on('submit', function (e) {
        e.preventDefault();
        const id = $('#depositar_cheque_id').val();
        const cuentaId = $('#depositar_cuenta_id').val();
        if (!cuentaId) { toastr.warning('Seleccioná un banco.'); return; }

        fetch(`${URL_FINANZAS}/cheques/${id}/depositar`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({ cuenta_id: cuentaId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                $('#ModalDepositarCheque').modal('hide');
                cargarCheques();
            } else {
                toastr.error(data.mensaje || 'No se pudo depositar el cheque.');
            }
        });
    });

    $('#formRechazarCheque').on('submit', function (e) {
        e.preventDefault();
        const id = $('#rechazar_cheque_id').val();

        fetch(`${URL_FINANZAS}/cheques/${id}/rechazar`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({ motivo: $('#rechazar_motivo').val() })
        })
        .then(res => res.json())
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                $('#ModalRechazarCheque').modal('hide');
                cargarCheques();
            } else {
                toastr.error(data.mensaje || 'No se pudo rechazar el cheque.');
            }
        });
    });

    cargarCheques();
});
</script>
@endsection
