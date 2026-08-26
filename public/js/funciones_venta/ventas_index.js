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
    if (typeof notasPanelSetEntidad === 'function') notasPanelSetEntidad('notasPanelVentaModal', 'venta', id);

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

            // 🧾 Comprobantes de pago asociados (transferencias, recibos)
            ventaDetalleActual = id;
            const compCont = document.querySelector("#details_v_comprobantes");
            compCont.innerHTML = "";
            (data.comprobantes || []).forEach(c => {
                const inner = c.es_imagen
                    ? `<img src="${c.url}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;">`
                    : `<span style="display:inline-block;padding:24px 18px;color:#b4552d;"><i class="fas fa-file-pdf" style="font-size:26px;"></i></span>`;
                compCont.innerHTML += `
                    <div style="position:relative;text-align:center;background:#F8FAFC;border:1px solid #E7EAF2;border-radius:10px;padding:6px;">
                        <a href="${c.url}" target="_blank" title="${c.nota || ''}">${inner}</a>
                        <div style="font-size:10px;color:#6E7A96;">${c.fecha}${c.nota ? '<br>' + c.nota : ''}</div>
                        <button onclick="eliminarComprobanteVenta(${c.id})" title="Eliminar"
                                style="position:absolute;top:2px;right:4px;border:none;background:none;color:#b4552d;cursor:pointer;font-size:11px;">✕</button>
                    </div>`;
            });
            if (!(data.comprobantes || []).length) {
                compCont.innerHTML = '<span style="font-size:12px;color:#94A3B8;">Sin comprobantes. Subí la foto de la transferencia o el recibo.</span>';
            }

            // ↩️ Devoluciones / cambios de esta venta
            const devWrap = document.querySelector("#details_v_devoluciones_wrap");
            const devCont = document.querySelector("#details_v_devoluciones");
            devCont.innerHTML = "";
            if (data.devoluciones && data.devoluciones.length > 0) {
                data.devoluciones.forEach(d => {
                    if (d.resolucion === 'cambio') {
                        const diffNum = parseFloat((d.diferencia || '0').replace(/\./g, '').replace(',', '.'));
                        let diffTxt = 'Sin diferencia de plata';
                        if (diffNum > 0) diffTxt = 'Cliente pagó $' + d.diferencia + ' de diferencia';
                        else if (diffNum < 0) diffTxt = 'Se devolvieron $' + d.diferencia.replace('-', '') + ' de diferencia';
                        devCont.innerHTML += `<div style="margin-bottom:6px;"><b>Cambio</b>: ${d.producto_anterior} → ${d.producto_nuevo}<br>${diffTxt} · <span style="color:#6E7A96;">${d.fecha}</span>${d.motivo ? '<br><i>' + d.motivo + '</i>' : ''}</div>`;
                    } else {
                        devCont.innerHTML += `<div style="margin-bottom:6px;"><b>Reintegro total</b> · <span style="color:#6E7A96;">${d.fecha}</span>${d.motivo ? '<br><i>' + d.motivo + '</i>' : ''}</div>`;
                    }
                });
                devWrap.style.display = "";
            } else {
                devWrap.style.display = "none";
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
                bancos: data.bancos || [],
                terceros: data.terceros || []
            };
            document.querySelector("#mediosPagoContainer").innerHTML = "";
            document.querySelector("#monto_ingresado").innerText = "0";
        });

    $('#ModalPagoCobro').modal('show');
}

// Monto en ARS que aporta una fila: si la cuenta no es ARS, monto (en su moneda) * cotización.
function montoArsDeFila(row) {
    const monto = parseFloat(row.querySelector("input[name='montos[]']").value) || 0;
    const cuentaSelect = row.querySelector("select[name='cajas[]']");
    const moneda = cuentaSelect.selectedOptions[0]?.dataset.moneda;
    if (moneda && moneda !== 'ARS') {
        const cotizacion = parseFloat(row.querySelector("input[name='cotizaciones[]']").value) || 0;
        return monto * cotizacion;
    }
    return monto;
}

function recalcularMontos() {
    const pendienteInicial = parseFloat(document.querySelector("#monto_pendiente").dataset.pendienteInicial) || 0;
    let ingresado = 0;

    document.querySelectorAll("#mediosPagoContainer > .row").forEach(row => {
        ingresado += montoArsDeFila(row);
    });

    document.querySelector("#monto_ingresado").innerText = ingresado.toFixed(2);

    const pendienteFinal = pendienteInicial - ingresado;
    document.querySelector("#monto_pendiente").innerText = (pendienteFinal > 0 ? pendienteFinal : 0).toFixed(2);
}

// Alias/CUIT ya usados en otros cobros, para autocompletar (datalist compartido)
function cargarAliasTercerosConocidos() {
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

function validarMontos() {
    const pendienteInicial = parseFloat(document.querySelector("#monto_pendiente").dataset.pendienteInicial) || 0;
    let ingresado = 0;
    let cuentasValidas = true;
    let faltaAlias = false;
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
        if (cuentaSelect.value.startsWith("tercero-") && !row.querySelector(".aliasTerceroInput").value.trim()) {
            faltaAlias = true;
        }
        if (moneda && moneda !== 'ARS' && !(parseFloat(row.querySelector("input[name='cotizaciones[]']").value) > 0)) {
            faltaCotizacion = true;
        }

        ingresado += montoArsDeFila(row);
    });

    if (faltaAlias) {
        alert("Cuando la plata va a una cuenta de terceros tenés que indicar el alias que la recibió.");
        return false;
    }

    if (faltaCotizacion) {
        alert("Indicá la cotización para el/los cobro(s) en moneda extranjera.");
        return false;
    }

    if (!cuentasValidas) {
        alert("Debe seleccionar una cuenta y un monto válido (mínimo 0.01) para cada medio de pago.");
        return false;
    }

    if (ingresado > pendienteInicial + 0.01) {
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

            // 🔹 Agregar cajas abiertas (con su saldo actual, en su propia moneda)
            window.cuentasDisponibles.cajas.forEach(c => {
                options += `<option value="caja-${c.id}" data-moneda="${c.moneda}">${c.nombre} (Caja - ${c.moneda}) — ${c.saldo.toFixed(2)}</option>`;
            });

            // 🔹 Agregar bancos (con saldo)
            window.cuentasDisponibles.bancos.forEach(b => {
                options += `<option value="banco-${b.id}" data-moneda="${b.moneda}">${b.nombre} (Banco - ${b.moneda}) — ${b.saldo.toFixed(2)}</option>`;
            });

            // 🔹 Agregar cuentas de terceros (el alias se carga en cada cobro)
            window.cuentasDisponibles.terceros.forEach(t => {
                options += `<option value="tercero-${t.id}" data-moneda="${t.moneda}">${t.nombre} (Terceros - ${t.moneda})</option>`;
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
                                placeholder="Alias que recibió la plata (ej: juan.perez.mp)" list="aliasTercerosConocidos" maxlength="60">
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
                            <input type="text" name="cheque_titular[]" class="form-control" placeholder="Quién lo dio (opcional)" maxlength="120">
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(row);
            cargarAliasTercerosConocidos();

            const cotizacionWrap = row.querySelector(".cotizacionWrap");
            const cotizacionInput = row.querySelector(".cotizacionInput");
            const totalArsPreview = row.querySelector(".totalArsPreview");

            function actualizarPreviewArs() {
                totalArsPreview.value = montoArsDeFila(row).toFixed(2);
            }

            // Default inteligente: caja → efectivo, banco → transferencia.
            // Si el destino es una cuenta de terceros, pedir alias/CUIT del que recibió.
            // Si la cuenta no es ARS, pedir la cotización para saber cuánto cubre en pesos.
            row.querySelector("select[name='cajas[]']").addEventListener("change", function () {
                row.querySelector("select[name='medios[]']").value = this.value.startsWith("caja-") ? "efectivo" : "transferencia";
                const esTercero = this.value.startsWith("tercero-");
                row.querySelector(".terceroWrap").style.display = esTercero ? "" : "none";
                if (!esTercero) {
                    row.querySelector(".aliasTerceroInput").value = "";
                    row.querySelector(".cuitTerceroInput").value = "";
                }
                row.querySelector(".chequeWrap").style.display = "none";

                const moneda = this.selectedOptions[0]?.dataset.moneda;
                cotizacionWrap.style.display = (moneda && moneda !== 'ARS') ? "" : "none";
                if (!moneda || moneda === 'ARS') cotizacionInput.value = "";
                actualizarPreviewArs();
                recalcularMontos();
            });

            // Al elegir un alias conocido, autocompletar su CUIT
            row.querySelector(".aliasTerceroInput").addEventListener("change", function () {
                const conocido = (window.aliasTercerosConocidos || []).find(a => a.alias === this.value.trim().toLowerCase());
                const cuitInput = row.querySelector(".cuitTerceroInput");
                if (conocido && conocido.cuit && !cuitInput.value) cuitInput.value = conocido.cuit;
            });

            // Cheque recibido: pedir número/banco/fecha de cobro
            row.querySelector("select[name='medios[]']").addEventListener("change", function () {
                row.querySelector(".chequeWrap").style.display = this.value === "cheque" ? "" : "none";
            });

            row.querySelector(".removeMedioPago").addEventListener("click", () => {
                row.remove();
                recalcularMontos();
            });

            row.querySelector(".montoInput").addEventListener("input", () => {
                actualizarPreviewArs();
                recalcularMontos();
            });

            cotizacionInput.addEventListener("input", () => {
                actualizarPreviewArs();
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

// ── Comprobantes de pago de la venta (transferencias, recibos) ──
let ventaDetalleActual = null;

function subirComprobanteVenta(btn) {
    const archivo = document.querySelector('#ventaCompArchivo').files[0];
    if (!archivo) { alert('Elegí primero la foto o el PDF del comprobante.'); return; }
    const fd = new FormData();
    fd.append('archivo', archivo);
    fd.append('nota', document.querySelector('#ventaCompNota').value);
    btn.disabled = true;
    fetch(`/ventas/${ventaDetalleActual}/comprobante`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
        body: fd
    }).then(res => res.json().then(data => ({ ok: res.ok, data })))
      .then(({ ok, data }) => {
        if (ok && data.success) {
            document.querySelector('#ventaCompArchivo').value = '';
            document.querySelector('#ventaCompNota').value = '';
            getDetailVenta(ventaDetalleActual); // recarga el modal con el comprobante nuevo
        } else {
            alert(data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'No se pudo subir el comprobante'));
        }
    }).finally(() => { btn.disabled = false; });
}

function eliminarComprobanteVenta(compId) {
    if (!confirm('¿Eliminar este comprobante?')) return;
    fetch(`/ventas/comprobante/${compId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
    }).then(() => getDetailVenta(ventaDetalleActual));
}
