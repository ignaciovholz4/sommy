/* Módulo de devoluciones: elegir venta/pedido → confirmar → reversa de plata y stock */

const CSRF_DEV = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

$(function () {
    // Buscadores de las listas (filtran las tarjetas client-side)
    document.querySelectorAll('.dv-buscar').forEach(input => {
        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.dv-item-' + this.dataset.target).forEach(card => {
                card.style.display = (card.dataset.buscar || '').includes(q) ? '' : 'none';
            });
        });
    });
});

/* ── Devolver una venta: reintegro total o cambio por otro producto ── */
function devolverVenta(idventa, folio, sucursalId) {
    Swal.fire({
        title: 'Devolver la venta ' + (folio || '#' + idventa),
        html: `
            <div class="text-start">
                <label style="display:block;margin-bottom:6px;"><input type="radio" name="dv-resolucion" value="reintegro" checked> Reintegro total (se devuelve toda la plata)</label>
                <label style="display:block;margin-bottom:12px;"><input type="radio" name="dv-resolucion" value="cambio"> Cambio, devolución parcial o agregar productos</label>
                <label style="font-size:13px;font-weight:600;">Motivo (opcional)</label>
                <input id="dv-motivo" type="text" class="form-control" maxlength="255" placeholder="Ej: producto fallado, cliente se arrepintió...">
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => ({
            resolucion: document.querySelector('input[name="dv-resolucion"]:checked').value,
            motivo: document.getElementById('dv-motivo').value || ''
        })
    }).then((result) => {
        if (!result.value) return;
        const { resolucion, motivo } = result.value;

        if (resolucion === 'reintegro') {
            Swal.fire({
                title: 'Confirmar reintegro',
                html: 'Se repone el stock y se genera la <b>salida de plata</b> en las cuentas donde se cobró.<br><br>¿Continuar?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, devolver',
                cancelButtonText: 'Cancelar'
            }).then((r2) => {
                if (!r2.value) return;
                postDevolucion(`/devoluciones/anular-venta/${idventa}`, { resolucion: 'reintegro', motivo }, (cuentaId) =>
                    postDevolucion(`/devoluciones/anular-venta/${idventa}`, { resolucion: 'reintegro', motivo, cuenta_id: cuentaId }));
            });
            return;
        }

        Promise.all([
            fetch(`/ventas/${idventa}/detail`).then(r => r.json()),
            fetch(`/sucursal/${sucursalId}/articulos-disponibles?context=venta`).then(r => r.json()),
            fetch(`/sucursal/${sucursalId}/cuentas-abiertas`).then(r => r.json()),
            fetch('/cuentas/terceros/alias').then(r => r.json()).catch(() => ({ alias: [] }))
        ]).then(([ventaData, disponibles, cuentasData, aliasData]) => {
            abrirCambioProducto(idventa, sucursalId, motivo, ventaData, disponibles, cuentasData, aliasData.alias || []);
        }).catch(err => {
            console.error(err);
            Swal.fire('Error', 'No se pudo cargar la venta o los productos disponibles.', 'error');
        });
    });
}

/* ── Gestor de cambio/devolución parcial: elegir qué vuelve, qué se lleva,
      ver la diferencia en vivo y resolver la plata por caja/banco/terceros ── */
function abrirCambioProducto(idventa, sucursalId, motivo, ventaData, disponibles, cuentasData, aliasConocidos) {
    const detalles = ventaData.detalles || [];
    const totalOriginal = ventaData.venta && ventaData.venta.total_con_iva_raw !== undefined
        ? Number(ventaData.venta.total_con_iva_raw)
        : detalles.reduce((s, d) => s + Number(d.subtotal_con_iva_raw || 0), 0);
    const fmt = n => '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const checks = detalles.map(d => `
        <label style="display:block;margin-bottom:4px;">
            <input type="checkbox" class="dv-linea" value="${d.id_detalle}" data-subtotal="${d.subtotal_con_iva_raw || 0}">
            ${d.cantidad} x ${d.articulo}${d.combinacion ? ' - ' + d.combinacion : ''} ($${d.subtotal_con_iva})
        </label>`).join('');

    let options = '<option value="">Elegí un producto...</option>';
    (disponibles.articulos || []).forEach(a => {
        options += `<option value="a-${a.idarticulo}" data-tipo="1" data-combinacion="" data-precio="${a.pventa_con_iva}" data-iva="${a.iva_venta}" data-descuento="${a.descuento || 0}" data-producto="${a.idarticulo}">${a.nombre} (${a.codigo}) — $${a.pventa_con_iva}</option>`;
    });
    (disponibles.combinaciones || []).forEach(c => {
        options += `<option value="c-${c.idcombinacion}" data-tipo="2" data-combinacion="${c.idcombinacion}" data-precio="${c.pventa_con_iva}" data-iva="${c.iva_venta}" data-descuento="0" data-producto="${c.producto_id}">${c.nombre} (${c.codigo}) — $${c.pventa_con_iva}</option>`;
    });

    // Cuentas para mover la diferencia (mismo formato que usa el backend)
    let cuentaOptions = '<option value="">Elegí la cuenta...</option>';
    (cuentasData.cajas || []).forEach(c => { cuentaOptions += `<option value="caja-${c.id}">${c.nombre} (Caja)</option>`; });
    (cuentasData.bancos || []).forEach(b => { cuentaOptions += `<option value="banco-${b.id}">${b.nombre} (Banco)</option>`; });
    (cuentasData.terceros || []).forEach(t => { cuentaOptions += `<option value="tercero-${t.id}">${t.nombre} (Terceros)</option>`; });

    const aliasDatalist = (aliasConocidos || [])
        .map(a => `<option value="${a.alias}">${a.cuit ? 'CUIT ' + a.cuit : ''}</option>`).join('');

    Swal.fire({
        title: 'Cambio / devolución parcial',
        width: 680,
        html: `
            <div class="text-start" style="font-size:14px;">
                <label style="font-size:13px;font-weight:700;">1. ¿Qué productos se devuelven? <small style="font-weight:400;color:#64748b;">(podés no marcar ninguno si solo agrega)</small></label>
                <div style="margin-bottom:12px;max-height:130px;overflow-y:auto;">${checks}</div>

                <label style="font-size:13px;font-weight:700;">2. ¿Qué se lleva a cambio o de más? <small style="font-weight:400;color:#64748b;">(podés dejarlo vacío si solo devuelve)</small></label>
                <div style="display:flex;gap:6px;margin-bottom:6px;">
                    <select id="dv-prod-select" class="form-select" style="flex:1;">${options}</select>
                    <input id="dv-prod-cant" type="number" min="1" value="1" class="form-control" style="width:80px;" title="Cantidad">
                    <button type="button" id="dv-prod-add" class="btn btn-dark" style="white-space:nowrap;">+ Agregar</button>
                </div>
                <div id="dv-prod-lista" style="margin-bottom:12px;"></div>

                <div id="dv-resumen" style="background:#f1f5f9;border-radius:10px;padding:10px 14px;margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;"><span>Total actual de la venta</span><b>${fmt(totalOriginal)}</b></div>
                    <div style="display:flex;justify-content:space-between;"><span>Se devuelve</span><b id="dv-res-devuelto" class="text-danger">$0,00</b></div>
                    <div style="display:flex;justify-content:space-between;"><span>Se lleva nuevo</span><b id="dv-res-nuevo" class="text-success">$0,00</b></div>
                    <hr style="margin:6px 0;">
                    <div style="display:flex;justify-content:space-between;"><span>Nuevo total de la venta</span><b id="dv-res-total">${fmt(totalOriginal)}</b></div>
                    <div id="dv-res-diferencia" style="margin-top:6px;font-weight:700;"></div>
                </div>

                <div id="dv-plata-wrap" style="display:none;">
                    <label style="font-size:13px;font-weight:700;" id="dv-plata-label">3. ¿Por qué cuenta se mueve la plata?</label>
                    <div id="dv-cobro-modo-wrap" style="display:none;margin-bottom:6px;">
                        <label style="display:inline-block;margin-right:14px;"><input type="radio" name="dv-cobro-modo" value="ahora" checked> Cobra ahora</label>
                        <label style="display:inline-block;"><input type="radio" name="dv-cobro-modo" value="despues"> Queda a cobrar</label>
                    </div>
                    <select id="dv-cuenta" class="form-select" style="margin-bottom:6px;">${cuentaOptions}</select>
                    <div id="dv-tercero-wrap" style="display:none;">
                        <input id="dv-alias" type="text" class="form-control" maxlength="60" style="margin-bottom:6px;"
                               placeholder="Alias del tercero por el que pasa la plata" list="dvAliasConocidos">
                        <input id="dv-cuit" type="text" class="form-control" maxlength="20" placeholder="CUIT del titular (opcional)">
                        <datalist id="dvAliasConocidos">${aliasDatalist}</datalist>
                    </div>
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Confirmar operación',
        cancelButtonText: 'Cancelar',
        didOpen: () => {
            const nuevos = [];
            window._dvNuevos = nuevos;

            const recalcular = () => {
                const devuelto = Array.from(document.querySelectorAll('.dv-linea:checked'))
                    .reduce((s, c) => s + Number(c.dataset.subtotal || 0), 0);
                const nuevoTotalProd = nuevos.reduce((s, n) => s + n.subtotal, 0);
                const nuevoTotal = totalOriginal - devuelto + nuevoTotalProd;
                const diferencia = Math.round((nuevoTotal - totalOriginal) * 100) / 100;

                document.getElementById('dv-res-devuelto').textContent = '−' + fmt(devuelto);
                document.getElementById('dv-res-nuevo').textContent = '+' + fmt(nuevoTotalProd);
                document.getElementById('dv-res-total').textContent = fmt(nuevoTotal);

                const difEl = document.getElementById('dv-res-diferencia');
                const plataWrap = document.getElementById('dv-plata-wrap');
                const cobroModo = document.getElementById('dv-cobro-modo-wrap');
                const label = document.getElementById('dv-plata-label');

                if (diferencia < -0.009) {
                    difEl.innerHTML = '<span class="text-danger">Hay que devolverle ' + fmt(Math.abs(diferencia)) + ' al cliente</span>';
                    plataWrap.style.display = ''; cobroModo.style.display = 'none';
                    label.textContent = '3. ¿De qué cuenta sale la plata que se devuelve?';
                } else if (diferencia > 0.009) {
                    difEl.innerHTML = '<span class="text-success">El cliente tiene que pagar ' + fmt(diferencia) + ' de diferencia</span>';
                    plataWrap.style.display = ''; cobroModo.style.display = '';
                    label.textContent = '3. ¿Dónde entra la plata de la diferencia?';
                    actualizarModoCobro();
                } else {
                    difEl.innerHTML = '<span style="color:#64748b;">Sin diferencia de plata: cambio mano a mano</span>';
                    plataWrap.style.display = 'none';
                }
                window._dvDiferencia = diferencia;
            };

            const actualizarModoCobro = () => {
                const modo = document.querySelector('input[name="dv-cobro-modo"]:checked');
                const esAhora = !modo || modo.value === 'ahora';
                document.getElementById('dv-cuenta').style.display = esAhora ? '' : 'none';
                if (!esAhora) document.getElementById('dv-tercero-wrap').style.display = 'none';
                else if (document.getElementById('dv-cuenta').value.startsWith('tercero-'))
                    document.getElementById('dv-tercero-wrap').style.display = '';
            };

            document.querySelectorAll('.dv-linea').forEach(c => c.addEventListener('change', recalcular));
            document.querySelectorAll('input[name="dv-cobro-modo"]').forEach(r => r.addEventListener('change', actualizarModoCobro));

            document.getElementById('dv-cuenta').addEventListener('change', function () {
                document.getElementById('dv-tercero-wrap').style.display = this.value.startsWith('tercero-') ? '' : 'none';
            });
            document.getElementById('dv-alias').addEventListener('change', function () {
                const conocido = (aliasConocidos || []).find(a => a.alias === this.value.trim().toLowerCase());
                const cuitInput = document.getElementById('dv-cuit');
                if (conocido && conocido.cuit && !cuitInput.value) cuitInput.value = conocido.cuit;
            });

            document.getElementById('dv-prod-add').addEventListener('click', () => {
                const sel = document.getElementById('dv-prod-select');
                const opt = sel.options[sel.selectedIndex];
                if (!opt.value) return;
                const cantidad = Math.max(1, parseInt(document.getElementById('dv-prod-cant').value) || 1);
                const precio = Number(opt.dataset.precio) || 0;
                const descuento = Number(opt.dataset.descuento) || 0;
                const iva = Number(opt.dataset.iva) || 0;
                const precioConDesc = precio - (precio * descuento / 100);
                const subtotal = (cantidad * precioConDesc) * (1 + iva / 100);

                nuevos.push({
                    articulo_id: opt.dataset.producto,
                    combinacion_id: opt.dataset.combinacion || null,
                    tipo_producto_id: opt.dataset.tipo,
                    cantidad: cantidad,
                    precio_unitario: precio,
                    iva: iva,
                    descuento: descuento,
                    nombre: opt.text,
                    subtotal: subtotal,
                });

                const lista = document.getElementById('dv-prod-lista');
                const item = document.createElement('div');
                item.style.cssText = 'display:flex;justify-content:space-between;align-items:center;background:#e0f2fe;border-radius:8px;padding:4px 10px;margin-bottom:4px;font-size:13px;';
                item.innerHTML = `<span>${cantidad} x ${opt.text}</span>
                    <button type="button" class="btn btn-sm btn-danger" style="padding:0 8px;">✕</button>`;
                item.querySelector('button').addEventListener('click', () => {
                    nuevos.splice(nuevos.indexOf(nuevos.find(n => n === item._nuevo)), 1);
                    item.remove();
                    recalcular();
                });
                item._nuevo = nuevos[nuevos.length - 1];
                lista.appendChild(item);
                sel.selectedIndex = 0;
                document.getElementById('dv-prod-cant').value = 1;
                recalcular();
            });

            recalcular();
        },
        preConfirm: () => {
            const lineas = Array.from(document.querySelectorAll('.dv-linea:checked')).map(c => c.value);
            const nuevos = window._dvNuevos || [];
            const diferencia = window._dvDiferencia || 0;

            if (!lineas.length && !nuevos.length) {
                Swal.showValidationMessage('Marcá qué se devuelve o agregá al menos un producto nuevo.');
                return false;
            }

            const payload = {
                detalles_devueltos: lineas,
                nuevos: nuevos.map(n => ({
                    articulo_id: n.articulo_id,
                    combinacion_id: n.combinacion_id,
                    tipo_producto_id: n.tipo_producto_id,
                    cantidad: n.cantidad,
                    precio_unitario: n.precio_unitario,
                    iva: n.iva,
                    descuento: n.descuento,
                })),
            };

            const cuenta = document.getElementById('dv-cuenta').value;
            const alias = document.getElementById('dv-alias').value.trim();
            const cuit = document.getElementById('dv-cuit').value.trim();
            const modoEl = document.querySelector('input[name="dv-cobro-modo"]:checked');
            const cobraAhora = !modoEl || modoEl.value === 'ahora';

            if (diferencia < -0.009) {
                if (!cuenta) { Swal.showValidationMessage('Elegí de qué cuenta sale la plata a devolver.'); return false; }
                if (cuenta.startsWith('tercero-') && !alias) { Swal.showValidationMessage('Indicá el alias del tercero.'); return false; }
                payload.cuenta_id = cuenta;
            } else if (diferencia > 0.009 && cobraAhora) {
                if (!cuenta) { Swal.showValidationMessage('Elegí dónde entra la plata de la diferencia (o marcá "Queda a cobrar").'); return false; }
                if (cuenta.startsWith('tercero-') && !alias) { Swal.showValidationMessage('Indicá el alias del tercero que recibe la plata.'); return false; }
                payload.cuenta_cobro_id = cuenta;
            }
            if (cuenta.startsWith('tercero-')) {
                payload.alias_tercero = alias;
                payload.cuit_tercero = cuit;
            }
            return payload;
        }
    }).then((result) => {
        if (!result.value) return;
        const payload = Object.assign({ resolucion: 'cambio', motivo }, result.value);
        enviarCambioProducto(idventa, sucursalId, payload);
    });
}

function enviarCambioProducto(idventa, sucursalId, payload) {
    fetch(`/devoluciones/anular-venta/${idventa}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_DEV, 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.pendiente_cobro > 0) {
                // Quedó "a cobrar" por decisión del usuario: lo llevamos al flujo normal de cobro
                Swal.fire({ title: 'Operación registrada', text: 'Queda a cobrar la diferencia: $' + data.pendiente_cobro.toFixed(2) + '. Te llevamos a la venta.', type: 'success', timer: 2200, showConfirmButton: false })
                    .then(() => { window.location.href = '/ventas?ver=' + idventa; });
                setTimeout(() => { window.location.href = '/ventas?ver=' + idventa; }, 2400);
            } else {
                Swal.fire({ title: 'Listo', text: 'Cambio/devolución registrado: stock y plata ya ajustados.', type: 'success', timer: 1800, showConfirmButton: false })
                    .then(() => location.reload());
                setTimeout(() => location.reload(), 2000);
            }
        } else if (data.cuentas) {
            const options = data.cuentas.map(c => `<option value="${c.id}">${c.nombre} (${c.tipo})</option>`).join('');
            Swal.fire({
                title: '¿De qué cuenta sale la plata?',
                html: '<p style="font-size:13px; color:#64748b;">' + (data.error || '') + '</p>' +
                      `<select id="cuentaDifDev" class="form-select">${options}</select>` +
                      `<div id="cuentaDifTercero" style="display:none;margin-top:6px;">
                          <input id="cuentaDifAlias" type="text" class="form-control" maxlength="60" placeholder="Alias del tercero" style="margin-bottom:6px;">
                          <input id="cuentaDifCuit" type="text" class="form-control" maxlength="20" placeholder="CUIT (opcional)">
                       </div>`,
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                didOpen: () => {
                    document.getElementById('cuentaDifDev').addEventListener('change', function () {
                        document.getElementById('cuentaDifTercero').style.display = this.value.startsWith('tercero-') ? '' : 'none';
                    });
                },
                preConfirm: () => {
                    const cuenta = document.getElementById('cuentaDifDev').value;
                    const alias = document.getElementById('cuentaDifAlias').value.trim();
                    if (cuenta.startsWith('tercero-') && !alias) { Swal.showValidationMessage('Indicá el alias del tercero.'); return false; }
                    return { cuenta, alias, cuit: document.getElementById('cuentaDifCuit').value.trim() };
                }
            }).then((r) => {
                if (r.value) enviarCambioProducto(idventa, sucursalId, Object.assign({}, payload, {
                    cuenta_id: r.value.cuenta,
                    alias_tercero: r.value.alias || payload.alias_tercero,
                    cuit_tercero: r.value.cuit || payload.cuit_tercero,
                }));
            });
        } else {
            Swal.fire('Error', data.error || 'No se pudo registrar el cambio.', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Error en la petición.', 'error');
    });
}

/* ── Devolver un pedido multicanal ── */
function devolverPedido(orderId) {
    Swal.fire({
        title: 'Devolver el Pedido #' + orderId,
        html: 'Se devuelve la plata cobrada, se repone el stock (si ya estaba descontado) y el pedido queda <b>Cancelado</b>.' +
              '<div class="text-start mt-3"><label style="font-size:13px; font-weight:600;">Motivo (opcional)</label>' +
              '<input id="dev-motivo" type="text" class="form-control" maxlength="255" placeholder="Ej: producto fallado, cliente se arrepintió..."></div>',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, devolver',
        cancelButtonText: 'Cancelar',
        preConfirm: () => document.getElementById('dev-motivo').value
    }).then((result) => {
        if (result.value === undefined || result.dismiss) return;
        const motivo = result.value || '';
        postDevolucion(`/devoluciones/anular-pedido/${orderId}`, { motivo }, (cuentaId) =>
            postDevolucion(`/devoluciones/anular-pedido/${orderId}`, { motivo, cuenta_id: cuentaId }));
    });
}

/* POST genérico: si el backend pide elegir cuenta (cajas cerradas), abre el selector */
function postDevolucion(url, payload, reintentarConCuenta) {
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_DEV, 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ title: 'Listo', text: 'Devolución registrada correctamente.', type: 'success', timer: 1600, showConfirmButton: false })
                .then(() => location.reload());
            setTimeout(() => location.reload(), 1800);
        } else if (data.cuentas) {
            const options = data.cuentas.map(c => {
                const extra = c.tipo === 'caja' ? (c.fecha_apertura ? ' — abierta ' + c.fecha_apertura : ' (caja)') : ' (banco)';
                return `<option value="${c.id}">${c.nombre}${extra}</option>`;
            }).join('');
            Swal.fire({
                title: '¿De qué cuenta sale la plata?',
                html: '<p style="font-size:13px; color:#64748b;">' + (data.error || '') + '</p>' +
                      `<select id="cuentaSeleccionadaDev" class="form-select">${options}</select>`,
                showCancelButton: true,
                confirmButtonText: 'Confirmar devolución',
                cancelButtonText: 'Cancelar',
                preConfirm: () => document.getElementById('cuentaSeleccionadaDev').value
            }).then((r) => {
                if (r.value && reintentarConCuenta) reintentarConCuenta(r.value);
            });
        } else {
            Swal.fire('Error', data.error || 'No se pudo registrar la devolución.', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Error en la petición.', 'error');
    });
}

/* ── Detalle de una devolución registrada ── */
function getDetailDevolucion(iddevolucion) {
    fetch(`/devoluciones/detalle/${iddevolucion}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                Swal.fire('Error', data.error, 'error');
                return;
            }

            const dev = data.devolucion;

            document.getElementById('dev_folio').textContent = dev.folio ?? '-';
            document.getElementById('dev_tipo').textContent = dev.tipo;
            document.getElementById('dev_sucursal').textContent = dev.sucursal;
            document.getElementById('dev_persona').textContent = dev.persona ?? '-';

            const esc = s => $('<div>').text(s ?? '').html();

            document.getElementById('dev_movimientos').innerHTML = dev.movimientos.map(m => `
                <tr>
                    <td>${esc(m.cuenta)}</td>
                    <td>${esc(m.fecha)}</td>
                    <td>${esc(m.tipo)}</td>
                    <td>${esc(m.total)}</td>
                </tr>
            `).join('') || '<tr><td colspan="4" class="text-muted">Sin movimientos</td></tr>';

            document.getElementById('dev_stock').innerHTML = dev.stock.map(s => `
                <tr>
                    <td>${esc(s.articulo)}</td>
                    <td>${esc(s.cantidad)}</td>
                </tr>
            `).join('') || '<tr><td colspan="2" class="text-muted">Sin artículos</td></tr>';

            $('#ModalDetalleDevolucion').modal('show');
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Error al obtener detalle de la devolución', 'error');
        });
}
