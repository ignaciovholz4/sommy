$(document).ready(function () {
    // Buscador del tablero de compras (filtra las tarjetas por folio, proveedor, etc.)
    const buscadorVc = document.getElementById('vc-buscador');
    if (buscadorVc) {
        buscadorVc.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.vc-card').forEach(card => {
                card.style.display = (card.dataset.buscar || '').includes(q) ? '' : 'none';
            });
        });
    }
});

/**
 * Anular compra
 */
function anularCompra(idcompra) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "¿Desea anular esta compra?",
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            fetch(`/api/devoluciones/anular-compra/${idcompra}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', 'Compra anulada correctamente', 'success');
                    location.reload();
                } else if (data.cuentas) {
                    let options = data.cuentas.map(c => {
                        let extra = c.tipo === 'caja' 
                            ? `Apertura: ${c.fecha_apertura}` 
                            : 'Banco';
                        return `<option value="${c.id}">${c.nombre} (${c.tipo}) - ${extra}</option>`;
                    }).join('');

                    Swal.fire({
                        title: 'Seleccione una cuenta',
                        html: `<select id="cuentaSeleccionada" class="form-select">${options}</select>`,
                        showCancelButton: true,
                        confirmButtonText: 'Confirmar',
                        cancelButtonText: 'Cancelar',
                        preConfirm: () => {
                            const cuentaId = document.getElementById('cuentaSeleccionada').value;
                            if (!cuentaId) {
                                Swal.showValidationMessage('Debe seleccionar una cuenta');
                            }
                            return cuentaId;
                        }
                    }).then((result) => {
                        if (result.value) {
                            confirmarCuentaCompra(idcompra, result.value);
                        }
                    });
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error al procesar la anulación', 'error');
            });
        }
    });
}

function confirmarCuentaCompra(idcompra, cuentaId) {
    fetch(`/api/devoluciones/anular-compra/${idcompra}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cuenta_id: cuentaId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', 'Compra anulada correctamente en la cuenta seleccionada', 'success');
            location.reload();
        } else {
            Swal.fire('Error', data.error, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Error al confirmar la cuenta', 'error');
    });
}

// Función para mostrar detalle de compra en modal
function getDetailCompra(id) {
    fetch(`/compras/${id}/detail`)
        .then(res => res.json())
        .then(data => {
            // Datos generales
            document.querySelector("#detalle_proveedor").innerText = data.compra.proveedor;
            document.querySelector("#detalle_fecha").innerText = data.compra.fecha;
            document.querySelector("#detalles_folio").innerText = data.compra.folio;
            document.querySelector("#detalle_tipo").innerText = data.compra.tipo_comprobante;

            // Totales
            document.querySelector("#details_total_neto").innerText = data.compra.total_neto;
            document.querySelector("#details_total_con_iva").innerText = data.compra.total_con_iva;

            // Detalles
            const tbody = document.querySelector("#show_details_purchase");
            tbody.innerHTML = "";
            data.detalles.forEach(d => {
                // Armar nombre del artículo con combinación si existe
                let detalleNombre = d.articulo;
                if (d.combinacion) {
                    detalleNombre += " - " + d.combinacion;
                }

                const row = `
                    <tr>
                        <td>${detalleNombre}</td>
                        <td>${d.cantidad}</td>
                        <td>${d.precio_unitario}</td>
                        <td>${d.price_list_name ?? ''}</td>
                        <td>${d.descuento ?? 0}%</td>
                        <td>${d.iva}% ${d.iva_label ? '('+d.iva_label+')' : ''}</td>
                        <td>${d.subtotal_neto}</td>
                        <td>${d.subtotal_con_iva}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Mostrar IVA discriminado
            const ivaContainer = document.querySelector("#details_iva_discriminado");
            ivaContainer.innerHTML = "";
            if (data.compra.iva_discriminado && data.compra.iva_discriminado.length > 0) {
                data.compra.iva_discriminado.forEach(item => {
                    ivaContainer.innerHTML += `<p>IVA ${item.porcentaje}%: $${item.monto}</p>`;
                });
            }

            // Comprobantes adjuntos (remitos, facturas)
            const adjWrapper = document.querySelector("#details_adjuntos_wrapper");
            const adjContainer = document.querySelector("#details_adjuntos");
            adjContainer.innerHTML = "";
            if (data.adjuntos && data.adjuntos.length > 0) {
                data.adjuntos.forEach(a => {
                    if (a.es_imagen) {
                        adjContainer.innerHTML += `
                            <a href="${a.url}" target="_blank" title="${a.name}">
                                <img src="${a.url}" alt="${a.name}"
                                     style="width:90px; height:90px; object-fit:cover; border-radius:8px; border:1px solid #dee2e6">
                            </a>`;
                    } else {
                        adjContainer.innerHTML += `
                            <a href="${a.url}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-file-pdf text-danger"></i> ${a.name}
                            </a>`;
                    }
                });
                adjWrapper.style.display = "";
            } else {
                adjWrapper.style.display = "none";
            }

            // Mostrar modal
            $('#ModalDetalleCompra').modal('show');
        })
        .catch(err => {
            console.error(err);
            alert("Error al cargar detalle de la compra");
        });
}

function openPagoModalCompra(idcompra, sucursalId) {
    document.querySelector("#compra_id").value = idcompra;
    document.querySelector("#sucursal_id_modal").value = sucursalId;

    fetch(`/compras/${idcompra}/pendiente`)
        .then(res => res.json())
        .then(data => {
            document.querySelector("#monto_total").innerText = data.total_con_iva.toFixed(2);
            document.querySelector("#monto_total").dataset.valor = data.total_con_iva;
            document.querySelector("#monto_pendiente").innerText = data.monto_pendiente.toFixed(2);
            document.querySelector("#monto_pendiente").dataset.pendienteInicial = data.monto_pendiente;

            return fetch(`/sucursal/${sucursalId}/cuentas-abiertas`);
        })
        .then(res => res.json())
        .then(data => {
            window.cuentasDisponibles = {
                cajas: data.cajas || [],
                bancos: data.bancos || []
            };
            document.querySelector("#mediosPagoContainer").innerHTML = "";
            document.querySelector("#monto_ingresado").innerText = "0";
        });

    $('#ModalPagoCompra').modal('show');
}

function recalcularMontosCompra() {
    const pendienteInicial = parseFloat(document.querySelector("#monto_pendiente").dataset.pendienteInicial) || 0;
    let pagado = 0;

    document.querySelectorAll("#mediosPagoContainer input[name='montos[]']").forEach(input => {
        pagado += parseFloat(input.value) || 0;
    });

    document.querySelector("#monto_ingresado").innerText = pagado.toFixed(2);

    const pendienteFinal = pendienteInicial - pagado;
    document.querySelector("#monto_pendiente").innerText = (pendienteFinal > 0 ? pendienteFinal : 0).toFixed(2);
}

function validarMontos() {
    const pendienteInicial = parseFloat(document.querySelector("#monto_pendiente").dataset.pendienteInicial) || 0;
    let pagado = 0;
    let cuentasValidas = true;

    document.querySelectorAll("#mediosPagoContainer .row").forEach(row => {
        const cuentaSelect = row.querySelector("select[name='cajas[]']");
        const montoInput = row.querySelector("input[name='montos[]']");
        const monto = parseFloat(montoInput.value) || 0;

        if (!cuentaSelect.value) {
            cuentasValidas = false;
        }
        if (monto < 0.01) {
            cuentasValidas = false;
        }

        pagado += monto;
    });

    if (!cuentasValidas) {
        alert("Debe seleccionar una cuenta y un monto válido (mínimo 0.01) para cada medio de pago.");
        return false;
    }

    if (pagado > pendienteInicial) {
        alert(`El monto ingresado (${pagado.toFixed(2)}) no puede superar el pendiente (${pendienteInicial.toFixed(2)}).`);
        return false;
    }
    return true;
}

document.querySelector("#formPagoCompra").addEventListener("submit", function(e) {
    e.preventDefault();

    if (!validarMontos()) return;

    const idcompra = document.querySelector("#compra_id").value;
    const formData = new FormData(this);

    fetch(`/compras/${idcompra}/registrar-pago`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Pago de compra registrado correctamente");
            $('#ModalPagoCompra').modal('hide');
            location.reload();
        } else {
            alert(data.error || "Error al registrar el pago");
        }
    })
    .catch(err => {
        console.error(err);
        alert("Error en la petición");
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const addBtn = document.querySelector("#addMedioPagoBtnCompra");
    if (addBtn) {
        addBtn.addEventListener("click", () => {
            const container = document.querySelector("#mediosPagoContainer");

            let options = '<option value="">Seleccione una cuenta</option>';

            // 🔹 Agregar cajas abiertas
            window.cuentasDisponibles.cajas.forEach(c => {
                options += `<option value="caja-${c.id}">${c.nombre} (Caja - ${c.moneda})</option>`;
            });

            // 🔹 Agregar bancos
            window.cuentasDisponibles.bancos.forEach(b => {
                options += `<option value="banco-${b.id}">${b.nombre} (Banco - ${b.moneda})</option>`;
            });

            const row = document.createElement("div");
            row.classList.add("row", "mb-2");
            row.innerHTML = `
                <div class="col-md-6">
                    <select name="cajas[]" class="form-control">${options}</select>
                </div>
                <div class="col-md-4">
                    <input type="number" name="montos[]" class="form-control montoInput" 
                        placeholder="Monto" min="0" step="0.01">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger removeMedioPago">X</button>
                </div>
            `;

            container.appendChild(row);

            row.querySelector(".removeMedioPago").addEventListener("click", () => {
                row.remove();
                recalcularMontosCompra();
            });

            row.querySelector(".montoInput").addEventListener("input", () => {
                recalcularMontosCompra();
            });

            recalcularMontosCompra();
        });
    }
});
// Deep-link: /compras?ver=ID abre el detalle de esa compra (ej: viniendo de un movimiento de cuenta)
document.addEventListener('DOMContentLoaded', function () {
    const verId = new URLSearchParams(window.location.search).get('ver');
    if (verId) getDetailCompra(verId);
});
