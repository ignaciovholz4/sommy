@extends('layouts.admin')

@section('title', 'Envíos y Fletes')

@section('contenido')
<div class="container-fluid" style="padding: 18px 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;"><i class="fas fa-shipping-fast" style="color:#2563EB;"></i> Envíos y Fletes</h4>
        @can('haveaccess', 'finanzas.envios.store')
        <button class="btn btn-primary" onclick="nuevoEnvio()"><i class="fas fa-plus"></i> Nuevo envío</button>
        @endcan
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="form-row align-items-end">
                <div class="col-md-3 col-6 mb-2">
                    <label class="mb-0 small text-muted">Estado</label>
                    <select id="filtro_estado" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="despachado">Despachado</option>
                        <option value="en_transito">En tránsito</option>
                        <option value="entregado">Entregado</option>
                        <option value="fallido">Fallido</option>
                    </select>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <label class="mb-0 small text-muted">Transportista</label>
                    <select id="filtro_transportista" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($transportistas as $t)
                            <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-12 mb-2">
                    <button class="btn btn-sm btn-primary" onclick="tablaEnvios.ajax.reload()"><i class="fas fa-filter"></i> Filtrar</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="limpiarFiltrosEnvios()">Limpiar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table id="envios_table" class="table table-striped table-sm" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Referencia</th>
                        <th>Transportista</th>
                        <th>Costo</th>
                        <th>Lo paga</th>
                        <th>Estado</th>
                        <th>Fechas</th>
                        <th>Tracking</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Modal alta / edición de envío --}}
<div class="modal fade" id="ModalEnvio" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formEnvio">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalEnvio">Nuevo envío</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="envio_id" value="">
                    <div class="form-row campos-alta-envio">
                        <div class="form-group col-md-4">
                            <label>Tipo *</label>
                            <select id="envio_tipo" class="form-control">
                                <option value="venta">Venta (pedido a cliente)</option>
                                <option value="compra">Compra (flete de proveedor)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4" id="wrap_envio_order">
                            <label>N° de pedido *</label>
                            <input type="number" id="envio_order_id" class="form-control" min="1" placeholder="Ej: 123">
                        </div>
                        <div class="form-group col-md-4" id="wrap_envio_compra" style="display:none;">
                            <label>N° de compra *</label>
                            <input type="number" id="envio_compra_id" class="form-control" min="1" placeholder="Ej: 45">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Transportista *</label>
                            <select id="envio_transportista_id" class="form-control" required>
                                <option value="">Seleccioná</option>
                                @foreach($transportistas as $t)
                                    <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Costo del flete</label>
                            <input type="number" id="envio_costo" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="form-group col-md-4">
                            <label>¿Quién lo paga?</label>
                            <select id="envio_pagado_por" class="form-control">
                                <option value="cliente">Cliente</option>
                                <option value="empresa">Empresa</option>
                            </select>
                            <small class="text-muted">Si lo paga la empresa, se genera un gasto pendiente en "Fletes".</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Dirección de entrega</label>
                            <input type="text" id="envio_direccion" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Entrega estimada</label>
                            <input type="date" id="envio_fecha_estimada" class="form-control">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tracking</label>
                            <input type="text" id="envio_tracking" class="form-control" maxlength="120">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea id="envio_notas" class="form-control" rows="2" placeholder="Horario de entrega, piso, indicaciones..."></textarea>
                    </div>
                    @include('adjuntos._panel', ['panelId' => 'adjuntosPanelEnvioModal', 'tipo' => 'envio', 'id' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
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
let tablaEnvios;

$(document).ready(function () {
    tablaEnvios = $('#envios_table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: {
            url: URL_FINANZAS + '/envios/list',
            data: function (d) {
                d.estado           = $('#filtro_estado').val();
                d.transportista_id = $('#filtro_transportista').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'tipo', name: 'tipo' },
            { data: 'referencia', name: 'referencia', orderable: false, searchable: false },
            { data: 'transportista', name: 'transportista', orderable: false },
            { data: 'costo', name: 'costo' },
            { data: 'pagado_por', name: 'pagado_por' },
            { data: 'estado', name: 'estado' },
            { data: 'fechas', name: 'fechas', orderable: false, searchable: false },
            { data: 'tracking', name: 'tracking', defaultContent: '' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' }
    });

    $('#envio_tipo').on('change', function () {
        const esVenta = this.value === 'venta';
        $('#wrap_envio_order').toggle(esVenta);
        $('#wrap_envio_compra').toggle(!esVenta);
    });

    $('#formEnvio').on('submit', function (e) {
        e.preventDefault();
        const id = $('#envio_id').val();

        const payload = {
            transportista_id: $('#envio_transportista_id').val(),
            costo: $('#envio_costo').val() || 0,
            pagado_por: $('#envio_pagado_por').val(),
            direccion_entrega: $('#envio_direccion').val() || null,
            fecha_entrega_estimada: $('#envio_fecha_estimada').val() || null,
            tracking: $('#envio_tracking').val() || null,
            notas: $('#envio_notas').val() || null
        };

        let url;
        if (id) {
            url = `${URL_FINANZAS}/envios/${id}/update`;
        } else {
            url = `${URL_FINANZAS}/envios`;
            payload.tipo = $('#envio_tipo').val();
            payload.order_ecommerce_id = payload.tipo === 'venta' ? ($('#envio_order_id').val() || null) : null;
            payload.compra_id = payload.tipo === 'compra' ? ($('#envio_compra_id').val() || null) : null;
        }

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.ok ? res.json() : res.json().then(j => Promise.reject(j)))
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                $('#ModalEnvio').modal('hide');
                tablaEnvios.ajax.reload(null, false);
            } else {
                toastr.error(data.mensaje || 'No se pudo guardar el envío.');
            }
        })
        .catch(err => {
            if (err && err.errors) Object.values(err.errors).forEach(msgs => msgs.forEach(m => toastr.error(m)));
            else toastr.error('Error en la petición.');
        });
    });
});

function limpiarFiltrosEnvios() {
    $('#filtro_estado, #filtro_transportista').val('');
    tablaEnvios.ajax.reload();
}

function nuevoEnvio() {
    $('#formEnvio')[0].reset();
    $('#envio_id').val('');
    $('.campos-alta-envio').show();
    $('#envio_tipo').val('venta').trigger('change');
    $('#tituloModalEnvio').text('Nuevo envío');
    if (typeof adjuntosPanelSetEntidad === 'function') adjuntosPanelSetEntidad('adjuntosPanelEnvioModal', 'envio', null);
    $('#ModalEnvio').modal('show');
}

function editarEnvio(btn) {
    const e = JSON.parse(btn.dataset.envio);
    $('#formEnvio')[0].reset();
    $('#envio_id').val(e.id);
    $('.campos-alta-envio').hide();
    $('#envio_transportista_id').val(e.transportista_id);
    $('#envio_costo').val(e.costo);
    $('#envio_pagado_por').val(e.pagado_por);
    $('#envio_direccion').val(e.direccion_entrega || '');
    $('#envio_fecha_estimada').val(e.fecha_entrega_estimada || '');
    $('#envio_tracking').val(e.tracking || '');
    $('#envio_notas').val(e.notas || '');
    $('#tituloModalEnvio').text('Editar envío #' + e.id);
    if (typeof adjuntosPanelSetEntidad === 'function') adjuntosPanelSetEntidad('adjuntosPanelEnvioModal', 'envio', e.id);
    $('#ModalEnvio').modal('show');
}

function avanzarEstadoEnvio(id, estado, label) {
    Swal.fire({
        title: '¿Marcar el envío como "' + label + '"?',
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.value) return;
        fetch(`${URL_FINANZAS}/envios/${id}/estado`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado: estado })
        })
        .then(res => res.json())
        .then(data => {
            if (data.estado === 1) { toastr.success(data.mensaje); tablaEnvios.ajax.reload(null, false); }
            else { toastr.error(data.mensaje); }
        });
    });
}

function eliminarEnvio(id) {
    Swal.fire({
        title: '¿Eliminar el envío?',
        text: 'Si tiene un gasto de flete pendiente, también se elimina.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.value) return;
        fetch(`${URL_FINANZAS}/envios/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
            .then(res => res.json())
            .then(data => {
                if (data.estado === 1) { toastr.success(data.mensaje); tablaEnvios.ajax.reload(null, false); }
                else { toastr.error(data.mensaje); }
            });
    });
}
</script>
@endsection
