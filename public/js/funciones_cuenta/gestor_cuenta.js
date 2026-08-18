$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Buscador de tarjetas de cuentas (filtra por nombre o sucursal)
    const buscador = document.getElementById('buscador-cuentas');
    if (buscador) {
        buscador.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.cuenta-card').forEach(card => {
                card.style.display = card.dataset.buscar.includes(q) ? '' : 'none';
            });
        });
    }
});

// Capturar click en botón Editar
$(document).on('click', '.btn-edit', function() {
    let id = $(this).data('id');
    let nombre = $(this).data('nombre');

    $('#edit_nombre').val(nombre);

    let actionTemplate = $('#formEditarCuenta').attr('action'); 
    let action = actionTemplate.replace('__ID__', id);
    $('#formEditarCuenta').attr('action', action);

    let modal = new bootstrap.Modal(document.getElementById('modalEditarCuenta'));
    modal.show();
});

// Capturar click en botón Desactivar
$(document).on('click', '.btn-deactivate', function() {
    let id = $(this).data('id');

    Swal.fire({
        title: '¿Desactivar cuenta?',
        text: "Esta acción marcará la cuenta como inactiva.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            fetch('/cuentas/' + id + '/desactivar', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.estado === 1) {
                    Swal.fire({ title: 'Éxito', text: data.mensaje, icon: 'success', timer: 1200, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', "Error al desactivar la cuenta", 'error');
                }
            })
            .catch(err => {
                console.error("Error en la petición:", err);
                Swal.fire('Error', "Error inesperado al desactivar la cuenta", 'error');
            });
        }
    });
});

// Capturar click en botón Activar
$(document).on('click', '.btn-activate', function() {
    let id = $(this).data('id');

    Swal.fire({
        title: '¿Activar cuenta?',
        text: "Esta acción marcará la cuenta como activa.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            fetch('/cuentas/' + id + '/activar', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.estado === 1) {
                    Swal.fire({ title: 'Éxito', text: data.mensaje, icon: 'success', timer: 1200, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', "Error al activar la cuenta", 'error');
                }
            })
            .catch(err => {
                console.error("Error en la petición:", err);
                Swal.fire('Error', "Error inesperado al activar la cuenta", 'error');
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
                console.log(select);
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