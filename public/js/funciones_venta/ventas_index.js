$(document).ready(function () {
    // Buscador del tablero de ventas (filtra las tarjetas por folio, cliente, teléfono, etc.)
    const buscadorVb = document.getElementById('vb-buscador');
    if (buscadorVb) {
        buscadorVb.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.vb-card').forEach(card => {
                card.style.display = (card.dataset.buscar || '').includes(q) ? '' : 'none';
            });
        });
    }
});

/**
 * Anular venta
 */
function anularVenta(idventa) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "¿Desea anular esta venta?",
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            fetch(`/api/devoluciones/anular-venta/${idventa}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', 'Venta anulada correctamente', 'success');
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
                            confirmarCuentaVenta(idventa, result.value);
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

function confirmarCuentaVenta(idventa, cuentaId) {
    fetch(`/api/devoluciones/anular-venta/${idventa}`, {
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
            Swal.fire('Éxito', 'Venta anulada correctamente en la caja seleccionada', 'success');
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

// Función para mostrar detalle de venta en modal
function getDetailVenta(id) {
    fetch(`/ventas/${id}/detail`)
        .then(res => res.json())
        .then(data => {
            // Datos generales
            document.querySelector("#detalle_cliente").innerText = data.venta.cliente;
            document.querySelector("#detalle_fecha").innerText = data.venta.fecha;
            document.querySelector("#detalles_folio").innerText = data.venta.folio;
            document.querySelector("#detalle_tipo").innerText = data.venta.tipo_comprobante;

            // Totales
            document.querySelector("#details_total_neto").innerText = data.venta.total_neto;
            document.querySelector("#details_total_con_iva").innerText = data.venta.total_con_iva;

            // Detalles de artículos
            const tbody = document.querySelector("#show_details_sale");
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

            // ✅ Mostrar IVA discriminado
            const ivaContainer = document.querySelector("#details_iva_discriminado");
            ivaContainer.innerHTML = "";
            if (data.venta.iva_discriminado && data.venta.iva_discriminado.length > 0) {
                data.venta.iva_discriminado.forEach(item => {
                    ivaContainer.innerHTML += `<p>IVA ${item.porcentaje}%: $${item.monto}</p>`;
                });
            }

            // 💰 Cobros: cuánto entró, cuánto falta y a qué caja/banco fue cada pago
            document.querySelector("#details_v_cobrado").innerText = "$" + (data.cobrado || "0,00");
            document.querySelector("#row_details_v_pendiente").style.display = data.tiene_pendiente ? "" : "none";
            document.querySelector("#details_v_pendiente").innerText = "$" + (data.pendiente || "0,00");
            const pagosWrap = document.querySelector("#details_v_pagos_wrap");
            const pagosCont = document.querySelector("#details_v_pagos");
            pagosCont.innerHTML = "";
            if (data.pagos && data.pagos.length > 0) {
                data.pagos.forEach(p => {
                    pagosCont.innerHTML += `<b>${p.cuenta}</b>: $${p.monto} <span style="color:#6E7A96;">(${p.fecha})</span><br>`;
                });
                pagosWrap.style.display = "";
            } else {
                pagosWrap.style.display = "none";
            }

            // Mostrar modal
            $('#ModalDetalleVenta').modal('show');
        })
        .catch(err => {
            console.error(err);
            alert("Error al cargar detalle de la venta");
        });
}

function openPagoModal(idventa, sucursalId) {
    document.querySelector("#venta_compra_id").value = idventa;
    document.querySelector("#sucursal_id_modal").value = sucursalId;

    fetch(`/ventas/${idventa}/pendiente`)
        .then(res => res.json())
        .then(data => {
            document.querySelector("#monto_total").innerText = data.total_con_iva.toFixed(2);
            document.querySelector("#monto_total").dataset.valor = data.total_con_iva;
            document.querySelector("#monto_pendiente").innerText = data.monto_pendiente.toFixed(2);
            document.querySelector("#monto_pendiente").dataset.pendienteInicial = data.monto_pendiente;

            // 🔹 Nueva ruta
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

    $('#ModalPagoCobro').modal('show');
}

function recalcularMontos() {
    const pendienteInicial = parseFloat(document.querySelector("#monto_pendiente").dataset.pendienteInicial) || 0;
    let ingresado = 0;

    document.querySelectorAll("#mediosPagoContainer input[name='montos[]']").forEach(input => {
        ingresado += parseFloat(input.value) || 0;
    });

    document.querySelector("#monto_ingresado").innerText = ingresado.toFixed(2);

    const pendienteFinal = pendienteInicial - ingresado;
    document.querySelector("#monto_pendiente").innerText = (pendienteFinal > 0 ? pendienteFinal : 0).toFixed(2);
}

function validarMontos() {
    const pendienteInicial = parseFloat(document.querySelector("#monto_pendiente").dataset.pendienteInicial) || 0;
    let ingresado = 0;
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

        ingresado += monto;
    });

    if (!cuentasValidas) {
        alert("Debe seleccionar una cuenta y un monto válido (mínimo 0.01) para cada medio de pago.");
        return false;
    }

    if (ingresado > pendienteInicial) {
        alert(`El monto ingresado (${ingresado.toFixed(2)}) no puede superar el pendiente (${pendienteInicial.toFixed(2)}).`);
        return false;
    }
    return true;
}

document.querySelector("#formPagoCobro").addEventListener("submit", function(e) {
    e.preventDefault();

    if (!validarMontos()) return;

    const idventa = document.querySelector("#venta_compra_id").value;
    const formData = new FormData(this);

    fetch(`/ventas/${idventa}/registrar-pago`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Pago registrado correctamente");
            $('#ModalPagoCobro').modal('hide');
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
    const addBtn = document.querySelector("#addMedioPagoBtn");
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
                recalcularMontos();
            });

            row.querySelector(".montoInput").addEventListener("input", () => {
                recalcularMontos();
            });

            recalcularMontos();
        });
    }
});

// Deep-link: /ventas?ver=ID abre el detalle de esa venta (ej: viniendo de un movimiento de cuenta)
document.addEventListener('DOMContentLoaded', function () {
    const verId = new URLSearchParams(window.location.search).get('ver');
    if (verId) getDetailVenta(verId);
});
