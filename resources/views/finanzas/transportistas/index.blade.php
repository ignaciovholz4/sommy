@extends('layouts.admin')

@section('title', 'Transportistas')

@section('contenido')
<div class="container-fluid" style="padding: 18px 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;"><i class="fas fa-truck" style="color:#2563EB;"></i> Transportistas</h4>
        <button class="btn btn-primary" onclick="nuevoTransportista()"><i class="fas fa-plus"></i> Nuevo transportista</button>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table id="transportistas_table" class="table table-striped table-sm" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>CUIT</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Envíos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Modal alta / edición --}}
<div class="modal fade" id="ModalTransportista" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formTransportista">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalTransportista">Nuevo transportista</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="transportista_id" value="">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" id="transportista_nombre" class="form-control" maxlength="150" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>CUIT</label>
                            <input type="text" id="transportista_cuit" class="form-control" maxlength="20" placeholder="XX-XXXXXXXX-X">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Teléfono</label>
                            <input type="text" id="transportista_telefono" class="form-control" maxlength="50">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="transportista_email" class="form-control" maxlength="150">
                    </div>
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea id="transportista_notas" class="form-control" rows="2" placeholder="Zonas que cubre, tarifas, contacto alternativo..."></textarea>
                    </div>
                    <div class="custom-control custom-checkbox" id="wrapTransportistaActivo" style="display:none;">
                        <input type="checkbox" class="custom-control-input" id="transportista_activo" checked>
                        <label class="custom-control-label" for="transportista_activo">Activo</label>
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
@endsection

@section('scripts')
<script>
const URL_FINANZAS = "{{ url('finanzas') }}";
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let tablaTransportistas;

$(document).ready(function () {
    tablaTransportistas = $('#transportistas_table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: { url: URL_FINANZAS + '/transportistas/list' },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'cuit', name: 'cuit', defaultContent: '' },
            { data: 'telefono', name: 'telefono', defaultContent: '' },
            { data: 'email', name: 'email', defaultContent: '' },
            { data: 'envios_count', name: 'envios_count', searchable: false },
            { data: 'activo', name: 'activo' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' }
    });

    $('#formTransportista').on('submit', function (e) {
        e.preventDefault();
        const id  = $('#transportista_id').val();
        const url = id ? `${URL_FINANZAS}/transportistas/${id}/update` : `${URL_FINANZAS}/transportistas`;

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nombre: $('#transportista_nombre').val(),
                cuit: $('#transportista_cuit').val() || null,
                telefono: $('#transportista_telefono').val() || null,
                email: $('#transportista_email').val() || null,
                notas: $('#transportista_notas').val() || null,
                activo: $('#transportista_activo').is(':checked')
            })
        })
        .then(res => res.ok ? res.json() : res.json().then(j => Promise.reject(j)))
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                $('#ModalTransportista').modal('hide');
                tablaTransportistas.ajax.reload(null, false);
            } else {
                toastr.error(data.mensaje || 'No se pudo guardar el transportista.');
            }
        })
        .catch(err => {
            if (err && err.errors) Object.values(err.errors).forEach(msgs => msgs.forEach(m => toastr.error(m)));
            else toastr.error('Error en la petición.');
        });
    });
});

function nuevoTransportista() {
    $('#formTransportista')[0].reset();
    $('#transportista_id').val('');
    $('#transportista_activo').prop('checked', true);
    $('#wrapTransportistaActivo').hide();
    $('#tituloModalTransportista').text('Nuevo transportista');
    $('#ModalTransportista').modal('show');
}

function editarTransportista(btn) {
    const t = JSON.parse(btn.dataset.transportista);
    $('#transportista_id').val(t.id);
    $('#transportista_nombre').val(t.nombre);
    $('#transportista_cuit').val(t.cuit || '');
    $('#transportista_telefono').val(t.telefono || '');
    $('#transportista_email').val(t.email || '');
    $('#transportista_notas').val(t.notas || '');
    $('#transportista_activo').prop('checked', !!t.activo);
    $('#wrapTransportistaActivo').show();
    $('#tituloModalTransportista').text('Editar transportista');
    $('#ModalTransportista').modal('show');
}

function eliminarTransportista(id) {
    Swal.fire({
        title: '¿Eliminar el transportista?',
        text: 'Si tiene envíos asociados, se va a desactivar en lugar de borrarse.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.value) return;
        fetch(`${URL_FINANZAS}/transportistas/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
            .then(res => res.json())
            .then(data => {
                if (data.estado === 1) { toastr.success(data.mensaje); tablaTransportistas.ajax.reload(null, false); }
                else { toastr.error(data.mensaje); }
            });
    });
}
</script>
@endsection
