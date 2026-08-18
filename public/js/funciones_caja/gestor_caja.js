$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#table_cajas').DataTable({
        paging: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '/caja/list',
            type: 'GET'
        },
        columns: [
            { data: 'nombre', name: 'nombre' },
            { data: 'sucursal.nombre', name: 'sucursal.nombre' },
            { 
                data: 'moneda', 
                name: 'moneda.nombre',
                render: function (data, type, row) {
                    return data ? data.nombre + ' (' + data.codigo + ')' : '';
                }
            },
            { 
                data: 'activa', 
                name: 'activa', 
                render: function (data, type, row) {
                    return data == 1 
                        ? '<span class="badge bg-success">Activa</span>' 
                        : '<span class="badge bg-secondary">Inactiva</span>';
                }
            },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });
});

// Capturar click en botón Editar
$(document).on('click', '.btn-edit', function() {
    let id = $(this).data('id');
    let nombre = $(this).data('nombre');

    $('#edit_nombre').val(nombre);

    let actionTemplate = $('#formEditarCaja').attr('action'); 
    let action = actionTemplate.replace('__ID__', id);
    $('#formEditarCaja').attr('action', action);

    let modal = new bootstrap.Modal(document.getElementById('modalEditarCaja'));
    modal.show();
});

// Capturar click en botón Desactivar
$(document).on('click', '.btn-deactivate', function() {
    let id = $(this).data('id');

    Swal.fire({
        title: '¿Desactivar caja?',
        text: "Esta acción marcará la caja como inactiva.",
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            fetch('/caja/' + id + '/desactivar', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.estado === 1) {
                    Swal.fire('Éxito', data.mensaje, 'success');
                    $('#table_cajas').DataTable().ajax.reload();
                } else {
                    Swal.fire('Error', "Error al desactivar la caja", 'error');
                }
            })
            .catch(err => {
                console.error("Error en la petición:", err);
                Swal.fire('Error', "Error inesperado al desactivar la caja", 'error');
            });
        }
    });
});

// Capturar click en botón Activar
$(document).on('click', '.btn-activate', function() {
    let id = $(this).data('id');

    Swal.fire({
        title: '¿Activar caja?',
        text: "Esta acción marcará la caja como activa.",
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            fetch('/caja/' + id + '/activar', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.estado === 1) {
                    Swal.fire('Éxito', data.mensaje, 'success');
                    $('#table_cajas').DataTable().ajax.reload();
                } else {
                    Swal.fire('Error', "Error al activar la caja", 'error');
                }
            })
            .catch(err => {
                console.error("Error en la petición:", err);
                Swal.fire('Error', "Error inesperado al activar la caja", 'error');
            });
        }
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const btnGuardar = document.getElementById("btnGuardarSucursal");

    if (btnGuardar) {
        btnGuardar.addEventListener("click", () => {
            const payload = {
                nombre: document.getElementById("nombre_sucursal").value,
                codigo: document.getElementById("codigo_sucursal").value,
                direccion: document.getElementById("direccion_sucursal").value,
                telefono: document.getElementById("telefono_sucursal").value,
                email: document.getElementById("email_sucursal").value,
            };

            fetch("/sucursal/store", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data.estado === 1) {
                    const modalEl = document.getElementById("modalSucursal");
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl) 
                                     || new bootstrap.Modal(modalEl);
                        modal.hide();
                    }
                    cargarSucursales();
                    Swal.fire('Éxito', 'Sucursal creada correctamente', 'success');
                } else {
                    Swal.fire('Error', data.mensaje || "Error al crear la sucursal", 'error');
                }
            })
            .catch(err => {
                console.error("Error en la petición:", err);
                Swal.fire('Error', "Error inesperado al crear la sucursal", 'error');
            });
        });
    }
});

// ✅ Listar sucursales y refrescar el select
function cargarSucursales() {
    fetch("/sucursal/list")
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                const select = document.getElementById("sucursal");
                if (select) {
                    select.innerHTML = "";
                    data.sucursales.forEach(s => {
                        const opt = document.createElement("option");
                        opt.value = s.id;
                        opt.textContent = s.nombre;
                        select.appendChild(opt);
                    });
                }
            }
        })
        .catch(err => console.error("Error al cargar sucursales:", err));
}