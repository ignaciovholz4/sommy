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
    if (typeof notasPanelSetEntidad === 'function') notasPanelSetEntidad('notasPanelCompraModal', 'compra', id);
    if (typeof adjuntosPanelSetEntidad === 'function') adjuntosPanelSetEntidad('adjuntosPanelCompraModal', 'compra', id);

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

            // 💰 Pagos: cuánto se pagó, cuánto falta y de qué caja/banco salió cada pago
            document.querySelector("#details_c_pagado").innerText = "$" + (data.pagado || "0,00");
            document.querySelector("#row_details_c_pendiente").style.display = data.tiene_pendiente ? "" : "none";
            document.querySelector("#details_c_pendiente").innerText = "$" + (data.pendiente || "0,00");
            const pagosWrapC = document.querySelector("#details_c_pagos_wrap");
            const pagosContC = document.querySelector("#details_c_pagos");
            pagosContC.innerHTML = "";
            if (data.pagos && data.pagos.length > 0) {
                data.pagos.forEach(p => {
                    const montoTxt = p.moneda && p.moneda !== 'ARS'
                        ? `${p.moneda} ${p.monto} <span style="color:#6E7A96;">(≈ $${p.monto_ars} ARS)</span>`
                        : `$${p.monto}`;
                    pagosContC.innerHTML += `<b>${p.cuenta}</b>: ${montoTxt} <span style="color:#6E7A96;">(${p.fecha})</span><br>`;
                });
                pagosWrapC.style.display = "";
            } else {
                pagosWrapC.style.display = "none";
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
                bancos: data.bancos || [],
                terceros: data.terceros || []
            };
            document.querySelector("#mediosPagoContainer").innerHTML = "";
            document.querySelector("#monto_ingresado").innerText = "0";

            return fetch("/finanzas/cheques/disponibles");
        })
        .then(res => res.json())
        .then(data => {
            window.chequesDisponibles = data.data || [];
        });

    $('#ModalPagoCompra').modal('show');
}

// Monto en ARS que aporta una fila: si la cuenta no es ARS, monto (en su moneda) * cotización.
function montoArsDeFilaCompra(row) {
    const monto = parseFloat(row.querySelector("input[name='montos[]']").value) || 0;
    const cuentaSelect = row.querySelector("select[name='cajas[]']");
    const moneda = cuentaSelect.selectedOptions[0]?.dataset.moneda;
    if (moneda && moneda !== 'ARS') {
        const cotizacion = parseFloat(row.querySelector("input[name='cotizaciones[]']").value) || 0;
        return monto * cotizacion;
    }
    return monto;
}

function recalcularMontosCompra() {
    const pendienteInicial = parseFloat(document.querySelector("#monto_pendiente").dataset.pendienteInicial) || 0;
    let pagado = 0;

    document.querySelectorAll("#mediosPagoContainer > .row").forEach(row => {
        pagado += montoArsDeFilaCompra(row);
    });

    document.querySelector("#monto_ingresado").innerText = pagado.toFixed(2);

    const pendienteFinal = pendienteInicial - pagado;
    document.querySelector("#monto_pendiente").innerText = (pendienteFinal > 0 ? pendienteFinal : 0).toFixed(2);
}

function validarMontos() {
    const pendienteInicial = parseFloat(document.querySelector("#monto_pendiente").dataset.pendienteInicial) || 0;
    let pagado = 0;
    let cuentasValidas = true;
    let faltaCotizacion = false;

    document.querySelectorAll("#mediosPagoContainer > .row").forEach(row => {
        const cuentaSelect = row.querySelector("select[name='cajas[]']");
        const montoInput = row.querySelector("input[name='montos[]']");
        const monto = parseFloat(montoInput.value) || 0;
        const moneda = cuentaSelect.selectedOptions[0]?.dataset.moneda;

        if (!cuentaSelect.value) {
            cuentasValidas = false;
        }
        if (monto < 0.01) {
            cuentasValidas = false;
        }
        if (moneda && moneda !== 'ARS' && !(parseFloat(row.querySelector("input[name='cotizaciones[]']").value) > 0)) {
            faltaCotizacion = true;
        }

        pagado += montoArsDeFilaCompra(row);
    });

    if (faltaCotizacion) {
        alert("Indicá la cotización para el/los pago(s) en moneda extranjera.");
        return false;
    }

    if (!cuentasValidas) {
        alert("Debe seleccionar una cuenta y un monto válido (mínimo 0.01) para cada medio de pago.");
        return false;
    }

    if (pagado > pendienteInicial + 0.01) {
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

// Alias/CUIT de terceros ya usados, para autocompletar (datalist compartido)
function cargarAliasTercerosConocidosCompra() {
    if (window.aliasTercerosConocidos) return;
    window.aliasTercerosConocidos = [];
    if (!document.getElementById("aliasTercerosConocidos")) {
        const dl = document.createElement("datalist");
        dl.id = "aliasTercerosConocidos";
        document.body.appendChild(dl);
    }
    fetch('/cuentas/terceros/alias')
        .then(r => r.json())
        .then(d => {
            window.aliasTercerosConocidos = d.alias || [];
            const dl = document.getElementById("aliasTercerosConocidos");
            dl.innerHTML = window.aliasTercerosConocidos
                .map(a => `<option value="${a.alias}">${a.cuit ? 'CUIT ' + a.cuit : ''}</option>`).join("");
        })
        .catch(() => {});
}

document.addEventListener("DOMContentLoaded", () => {
    const addBtn = document.querySelector("#addMedioPagoBtnCompra");
    if (addBtn) {
        addBtn.addEventListener("click", () => {
            const container = document.querySelector("#mediosPagoContainer");

            let options = '<option value="">Seleccione una cuenta</option>';

            // 🔹 Agregar cajas abiertas (con su saldo actual, en su propia moneda)
            window.cuentasDisponibles.cajas.forEach(c => {
                options += `<option value="caja-${c.id}" data-moneda="${c.moneda}">${c.nombre} (Caja - ${c.moneda}) — ${c.saldo.toFixed(2)}</option>`;
            });

            // 🔹 Agregar bancos (con saldo)
            window.cuentasDisponibles.bancos.forEach(b => {
                options += `<option value="banco-${b.id}" data-moneda="${b.moneda}">${b.nombre} (Banco - ${b.moneda}) — ${b.saldo.toFixed(2)}</option>`;
            });

            // 🔹 Pago al proveedor con la transferencia de un tercero (no sale de una cuenta propia)
            (window.cuentasDisponibles.terceros || []).forEach(t => {
                options += `<option value="tercero-${t.id}" data-moneda="${t.moneda}">${t.nombre} (Terceros - ${t.moneda})</option>`;
            });

            // 🔹 Cheques de terceros en cartera, para entregar (endosar) como pago
            (window.chequesDisponibles || []).forEach(ch => {
                options += `<option value="cheque-${ch.id}" data-monto="${ch.monto}">📝 Entregar — ${ch.label}</option>`;
            });

            const row = document.createElement("div");
            row.classList.add("row", "mb-2");
            row.innerHTML = `
                <div class="col-md-4">
                    <select name="cajas[]" class="form-control">${options}</select>
                </div>
                <div class="col-md-4">
                    <select name="medios[]" class="form-control" title="Medio de pago">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta_debito">Tarjeta débito</option>
                        <option value="tarjeta_credito">Tarjeta crédito</option>
                        <option value="cheque">Cheque</option>
                        <option value="mercadopago">MercadoPago</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" name="montos[]" class="form-control montoInput"
                        placeholder="Monto" min="0" step="0.01">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger removeMedioPago">X</button>
                </div>
                <div class="col-12 cotizacionWrap" style="display:none;">
                    <div class="row mt-1">
                        <div class="col-md-6">
                            <input type="number" name="cotizaciones[]" class="form-control cotizacionInput"
                                placeholder="Cotización (1 unidad = $ ARS)" min="0" step="0.0001">
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control totalArsPreview" readonly placeholder="≈ $ ARS">
                        </div>
                    </div>
                </div>
                <div class="col-12 terceroWrap" style="display:none;">
                    <div class="row mt-1">
                        <div class="col-md-6">
                            <input type="text" name="alias_tercero[]" class="form-control aliasTerceroInput"
                                placeholder="Alias del tercero que le transfirió al proveedor" list="aliasTercerosConocidos" maxlength="60">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="cuit_tercero[]" class="form-control cuitTerceroInput"
                                placeholder="CUIT del titular (opcional)" maxlength="20">
                        </div>
                    </div>
                </div>
                <div class="col-12 chequeWrap" style="display:none;">
                    <div class="row mt-1">
                        <div class="col-md-6">
                            <input type="text" name="cheque_numero[]" class="form-control" placeholder="Número de cheque" maxlength="60">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="cheque_banco[]" class="form-control" placeholder="Banco emisor" maxlength="120">
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-6">
                            <input type="date" name="cheque_fecha_cobro[]" class="form-control" title="Fecha de cobro">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="cheque_titular[]" class="form-control" placeholder="A nombre de (opcional)" maxlength="120">
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(row);
            cargarAliasTercerosConocidosCompra();

            const cajaSelect = row.querySelector("select[name='cajas[]']");
            const medioSelect = row.querySelector("select[name='medios[]']");
            const montoInput = row.querySelector(".montoInput");
            const chequeWrap = row.querySelector(".chequeWrap");
            const terceroWrap = row.querySelector(".terceroWrap");
            const cotizacionWrap = row.querySelector(".cotizacionWrap");
            const cotizacionInput = row.querySelector(".cotizacionInput");
            const totalArsPreview = row.querySelector(".totalArsPreview");

            function actualizarPreviewArs() {
                totalArsPreview.value = montoArsDeFilaCompra(row).toFixed(2);
            }

            function actualizarSegunCuenta() {
                const esEndoso = cajaSelect.value.startsWith("cheque-");
                const esTercero = cajaSelect.value.startsWith("tercero-");
                const moneda = cajaSelect.selectedOptions[0]?.dataset.moneda;

                terceroWrap.style.display = esTercero ? "" : "none";
                if (!esTercero) {
                    row.querySelector(".aliasTerceroInput").value = "";
                    row.querySelector(".cuitTerceroInput").value = "";
                }

                cotizacionWrap.style.display = (!esEndoso && moneda && moneda !== 'ARS') ? "" : "none";
                if (esEndoso || !moneda || moneda === 'ARS') {
                    cotizacionInput.value = "";
                }

                if (esEndoso) {
                    // No se deshabilita el <select>: un campo disabled no viaja en el FormData
                    // y desalinearía los índices de cajas[]/medios[]/montos[]. El backend igual
                    // fuerza medio=cheque para las filas de endoso sin importar este valor.
                    medioSelect.value = "cheque";
                    montoInput.value = cajaSelect.selectedOptions[0].dataset.monto || "";
                    montoInput.readOnly = true;
                    chequeWrap.style.display = "none"; // el cheque ya existe, no se cargan datos nuevos
                } else {
                    montoInput.readOnly = false;
                    medioSelect.value = cajaSelect.value.startsWith("caja-") ? "efectivo" : "transferencia";
                    chequeWrap.style.display = "none";
                }
                actualizarPreviewArs();
                recalcularMontosCompra();
            }

            // Default inteligente: caja → efectivo, banco/tercero → transferencia; cheque-{id} → endoso
            cajaSelect.addEventListener("change", actualizarSegunCuenta);

            medioSelect.addEventListener("change", function () {
                if (!cajaSelect.value.startsWith("cheque-")) {
                    chequeWrap.style.display = this.value === "cheque" ? "" : "none";
                }
            });

            // Al elegir un alias conocido, autocompletar su CUIT
            row.querySelector(".aliasTerceroInput").addEventListener("change", function () {
                const conocido = (window.aliasTercerosConocidos || []).find(a => a.alias === this.value.trim().toLowerCase());
                const cuitInput = row.querySelector(".cuitTerceroInput");
                if (conocido && conocido.cuit && !cuitInput.value) cuitInput.value = conocido.cuit;
            });

            row.querySelector(".removeMedioPago").addEventListener("click", () => {
                row.remove();
                recalcularMontosCompra();
            });

            row.querySelector(".montoInput").addEventListener("input", () => {
                actualizarPreviewArs();
                recalcularMontosCompra();
            });

            cotizacionInput.addEventListener("input", () => {
                actualizarPreviewArs();
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
