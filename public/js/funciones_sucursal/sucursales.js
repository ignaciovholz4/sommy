document.addEventListener("DOMContentLoaded", function () {

    const tabla = document.querySelector("#tablaSucursales tbody");
    const modalEl = document.getElementById("modalSucursal");
    const modal = new bootstrap.Modal(modalEl);

    const btnNueva = document.getElementById("btnNuevaSucursal");
    const btnGuardar = document.getElementById("btnGuardarSucursal");

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    // ✅ Cargar sucursales al iniciar
    cargarSucursales();

    function cargarSucursales() {
        fetch("/sucursal/list")
            .then(r => r.json())
            .then(data => {
                tabla.innerHTML = "";

                data.sucursales.forEach(s => {
                    const claseFila = s.activo ? "" : "table-secondary";

                    tabla.innerHTML += `
                        <tr class="${claseFila}">
                            <td>${s.nombre}</td>
                            <td>${s.codigo ?? '-'}</td>
                            <td>${s.direccion ?? '-'}</td>
                            <td>${s.telefono ?? '-'}</td>
                            <td>
                                <span class="badge ${s.activo ? 'bg-success' : 'bg-danger'}">
                                    ${s.activo ? 'Activa' : 'Inactiva'}
                                </span>
                            </td>
                            <td>${s.productos ?? 0}</td>
                            <td>
                                ${
                                    s.activo
                                    ? `
                                        <button class="btn btn-sm btn-info btnEditar" data-id="${s.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning btnStock" data-id="${s.id}">
                                            <i class="fas fa-boxes"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btnEliminar" data-id="${s.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    `
                                    : `
                                        <button class="btn btn-sm btn-success btnReactivar" data-id="${s.id}">
                                            <i class="fas fa-undo"></i> Reactivar
                                        </button>
                                    `
                                }
                            </td>
                        </tr>
                    `;
                });

                asignarEventos();
            });
    }

    function asignarEventos() {
        document.querySelectorAll(".btnEditar").forEach(btn => {
            btn.addEventListener("click", () => editarSucursal(btn.dataset.id));
        });

        document.querySelectorAll(".btnEliminar").forEach(btn => {
            btn.addEventListener("click", () => eliminarSucursal(btn.dataset.id));
        });

        document.querySelectorAll(".btnStock").forEach(btn => {
            btn.addEventListener("click", () => {
                window.location.href = `/sucursal/${btn.dataset.id}/stock`;
            });
        });

        document.querySelectorAll(".btnReactivar").forEach(btn => {
            btn.addEventListener("click", () => reactivarSucursal(btn.dataset.id));
        });
    }

    // ✅ Nueva sucursal
    btnNueva.addEventListener("click", () => {
        document.getElementById("formSucursal").reset();
        document.getElementById("sucursal_id").value = "";
        modal.show();
    });

    // ✅ Guardar sucursal (crear o editar)
    btnGuardar.addEventListener("click", () => {
        const id = document.getElementById("sucursal_id").value;

        const payload = {
            id: id,
            nombre: document.getElementById("nombre").value,
            codigo: document.getElementById("codigo").value,
            direccion: document.getElementById("direccion").value,
            telefono: document.getElementById("telefono").value,
            email: document.getElementById("email").value,
        };

        const url = id ? "/sucursal/update" : "/sucursal/store";

        fetch(url, {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                modal.hide();
                cargarSucursales();
            } else {
                alert(data.mensaje.join("\n"));
            }
        });
    });

    // ✅ Editar sucursal
    function editarSucursal(id) {
        fetch(`/sucursal/show/${id}`)
            .then(r => r.json())
            .then(data => {
                const s = data.sucursal;

                document.getElementById("sucursal_id").value = s.id;
                document.getElementById("nombre").value = s.nombre;
                document.getElementById("codigo").value = s.codigo;
                document.getElementById("direccion").value = s.direccion;
                document.getElementById("telefono").value = s.telefono;
                document.getElementById("email").value = s.email;

                modal.show();
            });
    }

    // ✅ Eliminar sucursal
    function eliminarSucursal(id) {
        if (!confirm("¿Desactivar sucursal?")) return;

        fetch("/sucursal/delete", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarSucursales();
            } else {
                alert(data.mensaje);
            }
        });
    }

    function reactivarSucursal(id) {
        fetch("/sucursal/activar", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({ id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarSucursales();
            } else {
                alert(data.mensaje);
            }
        });
    }

    const modalTransferir = document.getElementById('ModalTransferirStock');
    modalTransferir.addEventListener('hidden.bs.modal', function () {
        cargarSucursales(); // 🔹 recarga la lista de sucursales al cerrar
    });

});

document.addEventListener('DOMContentLoaded', function () {
    const btnTransferirStock = document.getElementById('btnTransferirStock');

    if (btnTransferirStock) {
        btnTransferirStock.addEventListener('click', function () {
            const modalEl = document.getElementById('ModalTransferirStock');

            // 🔹 Resetear campos
            document.getElementById('stock_origen').innerHTML = '<option value="">Seleccione...</option>';
            document.getElementById('stock_destino').innerHTML = '<option value="">Seleccione...</option>';
            document.getElementById('stock_producto').innerHTML = '<option value="">Seleccione...</option>';
            document.getElementById('stock_cantidad').value = '';

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            // 🔹 Cargar sucursales desde /sucursal/list
            fetch('/sucursal/list')
                .then(r => r.json())
                .then(data => {
                    if (data.estado === 1) {
                        const origenSelect = document.getElementById('stock_origen');
                        const destinoSelect = document.getElementById('stock_destino');
                        origenSelect.innerHTML = '<option value="">Seleccione...</option>';
                        destinoSelect.innerHTML = '<option value="">Seleccione...</option>';
                        data.sucursales.forEach(s => {
                            origenSelect.insertAdjacentHTML('beforeend', `<option value="${s.id}">${s.nombre}</option>`);
                            destinoSelect.insertAdjacentHTML('beforeend', `<option value="${s.id}">${s.nombre}</option>`);
                        });
                    }
                })
                .catch(err => console.error("Error al cargar sucursales:", err));
        });

        // 🔹 Cuando se selecciona sucursal origen, cargar artículos disponibles
        document.getElementById('stock_origen').addEventListener('change', function () {
            const sucursalId = this.value;
            if (!sucursalId) return;

            fetch(`/sucursal/${sucursalId}/articulos-disponibles`)
                .then(r => r.json())
                .then(data => {
                    if (data.estado === 1) {
                        const productoSelect = document.getElementById('stock_producto');
                        productoSelect.innerHTML = '<option value="">Seleccione...</option>';

                        // Artículos simples
                        data.articulos.forEach(a => {
                            productoSelect.insertAdjacentHTML('beforeend',
                                `<option value="${a.idarticulo}" data-tipo="simple">${a.nombre} (Stock: ${a.stock})</option>`
                            );
                        });

                        // Combinaciones
                        data.combinaciones.forEach(c => {
                            productoSelect.insertAdjacentHTML('beforeend',
                                `<option value="${c.idcombinacion}" data-tipo="combinacion">${c.nombre} (Stock: ${c.stock})</option>`
                            );
                        });
                    }
                })
                .catch(err => console.error("Error al cargar artículos:", err));
        });
    }

    // 🔹 Confirmar transferencia
    const formTransferirStock = document.getElementById('formTransferirStock');
    if (formTransferirStock) {
        formTransferirStock.addEventListener('submit', function (e) {
            e.preventDefault();

            const origen_id   = document.getElementById('stock_origen').value;
            const destino_id  = document.getElementById('stock_destino').value;
            const productoSel = document.getElementById('stock_producto').selectedOptions[0];
            const cantidad    = document.getElementById('stock_cantidad').value;

            if (origen_id === destino_id) {
                Swal.fire('Error', 'La sucursal origen y destino deben ser diferentes.', 'error');
                return;
            }

            const tipo = productoSel.dataset.tipo;
            const producto_id = productoSel.value;

            fetch('/stock/transferir', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    origen_id,
                    destino_id,
                    producto_id,
                    cantidad,
                    tipo
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', data.message, 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('ModalTransferirStock'));
                    modal.hide();
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error al transferir stock', 'error');
            });
        });
    }
});