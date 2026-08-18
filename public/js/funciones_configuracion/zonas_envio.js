const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let tablaZonas = null;

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    tablaZonas = $('#zonas_table').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '/showzonasenvio',
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'costo_formateado', name: 'costo', orderable: false },
            { data: 'orden', name: 'orden' },
            { data: 'estado', name: 'activo', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[3, 'asc']]
    });
});

function abrirModalZona(datos = null) {
    document.getElementById('zonaId').value = datos ? datos.id : 0;
    document.getElementById('zona-nombre').value = datos ? datos.nombre : '';
    document.getElementById('zona-costo').value = datos ? datos.costo : 0;
    document.getElementById('zona-orden').value = datos ? datos.orden : 0;
    document.getElementById('zona-activo').checked = datos ? datos.activo == 1 : true;
    document.getElementById('modalZonaTitle').textContent = datos ? 'Editar zona de envío' : 'Nueva zona de envío';

    const modal = new bootstrap.Modal(document.getElementById('modalZona'));
    modal.show();
}

document.getElementById('btn-nueva-zona').addEventListener('click', () => abrirModalZona());

$(document).on('click', '.btn-edit-zona', function () {
    abrirModalZona({
        id: $(this).data('id'),
        nombre: $(this).data('nombre'),
        costo: $(this).data('costo'),
        orden: $(this).data('orden'),
        activo: $(this).data('activo')
    });
});

document.getElementById('btn-guardar-zona').addEventListener('click', async () => {
    const formData = new FormData();
    formData.append('zonaId', document.getElementById('zonaId').value);
    formData.append('nombre', document.getElementById('zona-nombre').value);
    formData.append('costo', document.getElementById('zona-costo').value);
    formData.append('orden', document.getElementById('zona-orden').value);
    formData.append('activo', document.getElementById('zona-activo').checked ? 1 : 0);

    try {
        const resp = await fetch('/savezonaenvio', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token },
            body: formData
        });
        const data = await resp.json();

        if (data.status === 1) {
            toastr.success(data.message);
            bootstrap.Modal.getInstance(document.getElementById('modalZona')).hide();
            tablaZonas.ajax.reload();
        } else {
            (Array.isArray(data.message) ? data.message : [data.message]).forEach(m => toastr.error(m));
        }
    } catch (e) {
        toastr.error('Error de conexión');
    }
});

$(document).on('click', '.btn-delete-zona', function () {
    const id = $(this).data('id');

    Swal.fire({
        title: '¿Desactivar zona?',
        text: 'La zona dejará de aparecer en el checkout. Los pedidos existentes no se modifican.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (!result.value) return;

        const formData = new FormData();
        formData.append('id', id);

        const resp = await fetch('/deletezonaenvio', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token },
            body: formData
        });
        const data = await resp.json();

        if (data.status === 1) {
            toastr.success(data.message);
            tablaZonas.ajax.reload();
        } else {
            toastr.error(data.message);
        }
    });
});
