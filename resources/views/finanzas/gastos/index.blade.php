@extends('layouts.admin')

@section('title', 'Gastos')

@section('contenido')
<div class="container-fluid" style="padding: 18px 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;"><i class="fas fa-receipt" style="color:#2563EB;"></i> Gastos</h4>
        <div>
            @can('haveaccess', 'finanzas.gastos.categorias')
            <button class="btn btn-outline-secondary" onclick="abrirModalCategorias()"><i class="fas fa-tags"></i> Categorías</button>
            @endcan
            @can('haveaccess', 'finanzas.gastos.crud')
            <button class="btn btn-primary" onclick="nuevoGasto()"><i class="fas fa-plus"></i> Nuevo gasto</button>
            @endcan
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="form-row align-items-end">
                <div class="col-md-2 col-6 mb-2">
                    <label class="mb-0 small text-muted">Desde</label>
                    <input type="date" id="filtro_desde" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <label class="mb-0 small text-muted">Hasta</label>
                    <input type="date" id="filtro_hasta" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <label class="mb-0 small text-muted">Categoría</label>
                    <select id="filtro_categoria" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <label class="mb-0 small text-muted">Estado</label>
                    <select id="filtro_estado" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="pagado">Pagado</option>
                    </select>
                </div>
                <div class="col-md-3 col-12 mb-2">
                    <button class="btn btn-sm btn-primary" onclick="aplicarFiltrosGastos()"><i class="fas fa-filter"></i> Filtrar</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="limpiarFiltrosGastos()">Limpiar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table id="gastos_table" class="table table-striped table-sm" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Proveedor</th>
                        <th>Descripción</th>
                        <th>Monto</th>
                        <th>Recurrente</th>
                        <th>Estado</th>
                        <th>Comp.</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Modal alta / edición de gasto --}}
<div class="modal fade" id="ModalGasto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formGasto" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalGasto">Nuevo gasto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="gasto_id" value="">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Fecha *</label>
                            <input type="date" name="fecha" id="gasto_fecha" class="form-control" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Categoría *</label>
                            <select name="gasto_categoria_id" id="gasto_categoria_id" class="form-control" required>
                                <option value="">Seleccioná una categoría</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Proveedor</label>
                            <select name="proveedor_id" id="gasto_proveedor_id" class="form-control">
                                <option value="">Sin proveedor</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->idproveedor }}">{{ $prov->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Descripción *</label>
                            <input type="text" name="descripcion" id="gasto_descripcion" class="form-control" maxlength="255" placeholder="Ej: Alquiler depósito agosto" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Monto *</label>
                            <input type="number" name="monto" id="gasto_monto" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Sucursal</label>
                            <select name="sucursal_id" id="gasto_sucursal_id" class="form-control">
                                <option value="">Sin sucursal</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox mt-4">
                                <input type="checkbox" class="custom-control-input" id="gasto_es_recurrente" name="es_recurrente" value="1">
                                <label class="custom-control-label" for="gasto_es_recurrente">Es un gasto recurrente</label>
                            </div>
                        </div>
                        <div class="form-group col-md-4 campo-recurrente" style="display:none;">
                            <label>Frecuencia</label>
                            <select name="frecuencia" id="gasto_frecuencia" class="form-control">
                                <option value="semanal">Semanal</option>
                                <option value="mensual" selected>Mensual</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 campo-recurrente" style="display:none;">
                            <label>Próximo vencimiento</label>
                            <input type="date" name="proximo_vencimiento" id="gasto_proximo_vencimiento" class="form-control">
                        </div>
                    </div>
                    <div class="form-group" id="campo-cuenta-gasto">
                        <label>¿Con qué lo pagaste? (caja o banco)</label>
                        <select name="cuenta" id="gasto_cuenta" class="form-control"></select>
                        <small class="text-muted">Si elegís una cuenta, el gasto se paga en el acto: genera el egreso y <b>descuenta en el cierre diario de esa caja</b>. Si lo dejás en "Pagar después", queda pendiente.</small>
                    </div>
                    <div class="form-group">
                        <label>Comprobante (JPG, PNG o PDF, máx. 5 MB)</label>
                        <input type="file" name="comprobante" id="gasto_comprobante" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal registrar pago --}}
<div class="modal fade" id="ModalPagoGasto" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formPagoGasto">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar pago del gasto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pago_gasto_id" value="">
                    <p class="mb-1"><strong id="pago_gasto_descripcion"></strong></p>
                    <p class="mb-3">Monto a pagar: <strong style="color:#b4552d;">$<span id="pago_gasto_monto"></span></strong></p>
                    <div class="form-group">
                        <label>Cuenta de salida (cajas, bancos o cheques) *</label>
                        <select id="pago_gasto_cuenta" class="form-control" required>
                            <option value="">Cargando cuentas...</option>
                        </select>
                        <small class="text-muted">Si pagás desde una caja, tiene que estar abierta.</small>
                    </div>
                    <div id="campo-cheque-gasto" style="display:none;">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Número de cheque *</label>
                                <input type="text" id="pago_gasto_cheque_numero" class="form-control" maxlength="60">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Banco emisor</label>
                                <input type="text" id="pago_gasto_cheque_banco" class="form-control" maxlength="120">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Fecha de cobro *</label>
                                <input type="date" id="pago_gasto_cheque_fecha" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>A nombre de (opcional)</label>
                                <input type="text" id="pago_gasto_cheque_titular" class="form-control" maxlength="120" placeholder="Proveedor por defecto">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-dollar-sign"></i> Registrar pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal categorías --}}
<div class="modal fade" id="ModalCategorias" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tags"></i> Categorías de gastos</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formCategoria" class="form-row mb-3">
                    <input type="hidden" id="categoria_id" value="">
                    <div class="col-6">
                        <input type="text" id="categoria_nombre" class="form-control form-control-sm" placeholder="Nombre de la categoría" maxlength="120" required>
                    </div>
                    <div class="col-4">
                        <select id="categoria_padre_id" class="form-control form-control-sm">
                            <option value="">Sin categoría madre</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-primary btn-block" id="btnGuardarCategoria">Agregar</button>
                    </div>
                </form>
                <table class="table table-sm">
                    <thead><tr><th>Nombre</th><th>Madre</th><th></th></tr></thead>
                    <tbody id="tbodyCategorias"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const URL_FINANZAS = "{{ url('finanzas') }}";
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let tablaGastos;

$(document).ready(function () {
    tablaGastos = $('#gastos_table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: {
            url: URL_FINANZAS + '/gastos/list',
            data: function (d) {
                d.fecha_desde  = $('#filtro_desde').val();
                d.fecha_hasta  = $('#filtro_hasta').val();
                d.categoria_id = $('#filtro_categoria').val();
                d.estado       = $('#filtro_estado').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'fecha', name: 'fecha' },
            { data: 'categoria', name: 'categoria', orderable: false },
            { data: 'proveedor', name: 'proveedor', orderable: false },
            { data: 'descripcion', name: 'descripcion' },
            { data: 'monto', name: 'monto' },
            { data: 'recurrente', name: 'recurrente', orderable: false, searchable: false },
            { data: 'estado', name: 'estado' },
            { data: 'comprobante', name: 'comprobante', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' }
    });

    $('#gasto_es_recurrente').on('change', function () {
        $('.campo-recurrente').toggle(this.checked);
    });

    // ── Alta / edición ─────────────────────────────
    $('#formGasto').on('submit', function (e) {
        e.preventDefault();
        const id  = $('#gasto_id').val();
        const url = id ? `${URL_FINANZAS}/gastos/${id}/update` : `${URL_FINANZAS}/gastos`;
        const formData = new FormData(this);

        fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body: formData })
            .then(res => res.ok ? res.json() : res.json().then(j => Promise.reject(j)))
            .then(data => {
                if (data.estado === 1) {
                    toastr.success(data.mensaje);
                    $('#ModalGasto').modal('hide');
                    tablaGastos.ajax.reload(null, false);
                } else {
                    toastr.error(data.mensaje || 'No se pudo guardar el gasto.');
                }
            })
            .catch(err => mostrarErroresValidacion(err));
    });

    // ── Pago ───────────────────────────────────────
    $('#pago_gasto_cuenta').on('change', function () {
        $('#campo-cheque-gasto').toggle(this.value === 'cheque-nuevo');
    });

    $('#formPagoGasto').on('submit', function (e) {
        e.preventDefault();
        const id = $('#pago_gasto_id').val();
        const cuenta = $('#pago_gasto_cuenta').val();
        if (!cuenta) { toastr.warning('Seleccioná una cuenta.'); return; }
        if (cuenta === 'cheque-nuevo' && !$('#pago_gasto_cheque_numero').val()) {
            toastr.warning('Indicá el número del cheque.'); return;
        }

        const payload = { cuenta: cuenta };
        if (cuenta === 'cheque-nuevo') {
            payload.cheque_numero = $('#pago_gasto_cheque_numero').val();
            payload.cheque_banco = $('#pago_gasto_cheque_banco').val();
            payload.cheque_fecha_cobro = $('#pago_gasto_cheque_fecha').val();
            payload.cheque_titular = $('#pago_gasto_cheque_titular').val();
        }

        fetch(`${URL_FINANZAS}/gastos/${id}/registrar-pago`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                $('#ModalPagoGasto').modal('hide');
                tablaGastos.ajax.reload(null, false);
            } else {
                toastr.error(data.mensaje || 'No se pudo registrar el pago.');
            }
        })
        .catch(() => toastr.error('Error en la petición.'));
    });

    // ── Categorías ─────────────────────────────────
    $('#formCategoria').on('submit', function (e) {
        e.preventDefault();
        const id  = $('#categoria_id').val();
        const url = id ? `${URL_FINANZAS}/gasto-categorias/${id}/update` : `${URL_FINANZAS}/gasto-categorias`;

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nombre: $('#categoria_nombre').val(),
                padre_id: $('#categoria_padre_id').val() || null
            })
        })
        .then(res => res.ok ? res.json() : res.json().then(j => Promise.reject(j)))
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                resetFormCategoria();
                cargarCategorias();
            } else {
                toastr.error(data.mensaje || 'No se pudo guardar la categoría.');
            }
        })
        .catch(err => mostrarErroresValidacion(err));
    });
});

function mostrarErroresValidacion(err) {
    if (err && err.errors) {
        Object.values(err.errors).forEach(msgs => msgs.forEach(m => toastr.error(m)));
    } else {
        toastr.error((err && err.message) || 'Error en la petición.');
    }
}

function aplicarFiltrosGastos() { tablaGastos.ajax.reload(); }
function limpiarFiltrosGastos() {
    $('#filtro_desde, #filtro_hasta, #filtro_categoria, #filtro_estado').val('');
    tablaGastos.ajax.reload();
}

function nuevoGasto() {
    $('#formGasto')[0].reset();
    $('#gasto_id').val('');
    $('#gasto_fecha').val(new Date().toISOString().slice(0, 10));
    $('.campo-recurrente').hide();
    $('#campo-cuenta-gasto').show();
    cargarCuentasEnSelect('#gasto_cuenta', 'Pagar después (queda pendiente)');
    $('#tituloModalGasto').text('Nuevo gasto');
    $('#ModalGasto').modal('show');
}

function editarGasto(id) {
    fetch(`${URL_FINANZAS}/gastos/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.estado !== 1) { toastr.error('No se pudo cargar el gasto.'); return; }
            const g = data.gasto;
            $('#formGasto')[0].reset();
            $('#gasto_id').val(g.id);
            $('#gasto_fecha').val(g.fecha);
            $('#gasto_categoria_id').val(g.gasto_categoria_id);
            $('#gasto_proveedor_id').val(g.proveedor_id || '');
            $('#gasto_descripcion').val(g.descripcion);
            $('#gasto_monto').val(g.monto);
            $('#gasto_sucursal_id').val(g.sucursal_id || '');
            $('#gasto_es_recurrente').prop('checked', g.es_recurrente);
            $('.campo-recurrente').toggle(g.es_recurrente);
            if (g.es_recurrente) {
                $('#gasto_frecuencia').val(g.frecuencia || 'mensual');
                $('#gasto_proximo_vencimiento').val(g.proximo_vencimiento || '');
            }
            // Al editar no se paga desde acá: el pago tiene su propio botón en la grilla
            $('#campo-cuenta-gasto').hide();
            $('#gasto_cuenta').html('');
            $('#tituloModalGasto').text('Editar gasto #' + g.id);
            $('#ModalGasto').modal('show');
        });
}

function eliminarGasto(id) {
    Swal.fire({
        title: '¿Eliminar el gasto?',
        text: 'Esta acción no se puede deshacer.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.value) return;
        fetch(`${URL_FINANZAS}/gastos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
            .then(res => res.json())
            .then(data => {
                if (data.estado === 1) { toastr.success(data.mensaje); tablaGastos.ajax.reload(null, false); }
                else { toastr.error(data.mensaje); }
            });
    });
}

function abrirModalPagoGasto(id) {
    fetch(`${URL_FINANZAS}/gastos/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.estado !== 1) { toastr.error('No se pudo cargar el gasto.'); return; }
            const g = data.gasto;
            $('#pago_gasto_id').val(g.id);
            $('#pago_gasto_descripcion').text(g.descripcion_pago);
            $('#pago_gasto_monto').text(g.monto_formateado);
            $('#campo-cheque-gasto').hide();
            $('#pago_gasto_cheque_numero, #pago_gasto_cheque_banco, #pago_gasto_cheque_fecha, #pago_gasto_cheque_titular').val('');
            cargarCuentasConChequesEnSelect('#pago_gasto_cuenta');
            $('#ModalPagoGasto').modal('show');
        });
}

// Cajas/bancos + cheques de terceros en cartera (para endosar), usado solo en modales de pago
function cargarCuentasConChequesEnSelect(selector) {
    $(selector).html('<option value="">Cargando cuentas...</option>');
    Promise.all([
        fetch("{{ url('cuentas-abiertas') }}").then(res => res.json()),
        fetch(`${URL_FINANZAS}/cheques/disponibles`).then(res => res.json())
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
            options += `<option value="cheque-${ch.id}">📝 Entregar — ${ch.label}</option>`;
        });
        $(selector).html(options);
    }).catch(() => $(selector).html('<option value="">No se pudieron cargar las cuentas</option>'));
}

// Cajas abiertas + bancos de todas las sucursales (mismo endpoint que el pago de pedidos)
function cargarCuentasEnSelect(selector, placeholder) {
    $(selector).html('<option value="">Cargando cuentas...</option>');
    fetch("{{ url('cuentas-abiertas') }}")
        .then(res => res.json())
        .then(data => {
            let options = '<option value="">' + (placeholder || 'Seleccioná una cuenta') + '</option>';
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

// ── Categorías ─────────────────────────────────────
function abrirModalCategorias() {
    resetFormCategoria();
    cargarCategorias();
    $('#ModalCategorias').modal('show');
}

function cargarCategorias() {
    fetch(`${URL_FINANZAS}/gasto-categorias`)
        .then(res => res.json())
        .then(data => {
            const tbody = $('#tbodyCategorias');
            const selPadre = $('#categoria_padre_id');
            tbody.empty();
            selPadre.html('<option value="">Sin categoría madre</option>');

            (data.categorias || []).forEach(c => {
                selPadre.append(`<option value="${c.id}">${c.nombre}</option>`);
                tbody.append(`
                    <tr class="${c.activo ? '' : 'text-muted'}">
                        <td>${c.nombre}${c.activo ? '' : ' <span class="badge badge-secondary">inactiva</span>'}</td>
                        <td>${c.padre || '—'}</td>
                        <td class="text-right">
                            <button class="btn btn-xs btn-primary" onclick="editarCategoria(${c.id}, '${String(c.nombre).replace(/'/g, "\\'")}', ${c.padre_id || 'null'})"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-xs btn-danger" onclick="eliminarCategoria(${c.id})"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>`);
            });
        });
}

function editarCategoria(id, nombre, padreId) {
    $('#categoria_id').val(id);
    $('#categoria_nombre').val(nombre);
    $('#categoria_padre_id').val(padreId || '');
    $('#btnGuardarCategoria').text('Guardar');
}

function resetFormCategoria() {
    $('#categoria_id').val('');
    $('#categoria_nombre').val('');
    $('#categoria_padre_id').val('');
    $('#btnGuardarCategoria').text('Agregar');
}

function eliminarCategoria(id) {
    Swal.fire({
        title: '¿Eliminar la categoría?',
        text: 'Si tiene gastos asociados, se va a desactivar en lugar de borrarse.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.value) return;
        fetch(`${URL_FINANZAS}/gasto-categorias/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
            .then(res => res.json())
            .then(data => {
                if (data.estado === 1) { toastr.success(data.mensaje); cargarCategorias(); }
                else { toastr.error(data.mensaje); }
            });
    });
}
</script>
@endsection
