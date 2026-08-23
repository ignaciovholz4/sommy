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
                <label style="display:block;margin-bottom:12px;"><input type="radio" name="dv-resolucion" value="cambio"> Cambio por otro producto</label>
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
            fetch(`/sucursal/${sucursalId}/articulos-disponibles?context=venta`).then(r => r.json())
        ]).then(([ventaData, disponibles]) => {
            abrirCambioProducto(idventa, sucursalId, motivo, ventaData, disponibles);
        }).catch(err => {
            console.error(err);
            Swal.fire('Error', 'No se pudo cargar la venta o los productos disponibles.', 'error');
        });
    });
}

/* ── Cambio de producto: elegir qué línea se devuelve y el producto nuevo ── */
function abrirCambioProducto(idventa, sucursalId, motivo, ventaData, disponibles) {
    const detalles = ventaData.detalles || [];

    const checks = detalles.map((d, i) => `
        <label style="display:block;margin-bottom:4px;">
            <input type="checkbox" class="dv-linea" value="${d.id_detalle}" ${detalles.length === 1 ? 'checked' : ''}>
            ${d.cantidad} x ${d.articulo}${d.combinacion ? ' - ' + d.combinacion : ''} ($${d.subtotal_con_iva})
        </label>`).join('');

    let options = '<option value="">Elegí el producto nuevo...</option>';
    (disponibles.articulos || []).forEach(a => {
        options += `<option value="a-${a.idarticulo}" data-tipo="1" data-combinacion="" data-precio="${a.pventa_con_iva}" data-iva="${a.iva_venta}" data-descuento="${a.descuento || 0}" data-producto="${a.idarticulo}">${a.nombre} (${a.codigo}) — $${a.pventa_con_iva}</option>`;
    });
    (disponibles.combinaciones || []).forEach(c => {
        options += `<option value="c-${c.idcombinacion}" data-tipo="2" data-combinacion="${c.idcombinacion}" data-precio="${c.pventa_con_iva}" data-iva="${c.iva_venta}" data-descuento="0" data-producto="${c.producto_id}">${c.nombre} (${c.codigo}) — $${c.pventa_con_iva}</option>`;
    });

    Swal.fire({
        title: 'Cambio de producto',
        width: 560,
        html: `
            <div class="text-start">
                <label style="font-size:13px;font-weight:600;">¿Qué producto de la venta se devuelve?</label>
                <div style="margin-bottom:10px;">${checks}</div>
                <label style="font-size:13px;font-weight:600;">Producto nuevo</label>
                <select id="dv-producto-nuevo" class="form-select" style="margin-bottom:8px;">${options}</select>
                <label style="font-size:13px;font-weight:600;">Cantidad</label>
                <input id="dv-cantidad-nueva" type="number" min="1" value="1" class="form-control">
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Confirmar cambio',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const lineas = Array.from(document.querySelectorAll('.dv-linea:checked')).map(c => c.value);
            const sel = document.getElementById('dv-producto-nuevo');
            const opt = sel.options[sel.selectedIndex];
            if (!lineas.length) { Swal.showValidationMessage('Elegí qué producto se devuelve.'); return false; }
            if (!opt.value) { Swal.showValidationMessage('Elegí el producto nuevo.'); return false; }
            return {
                detalles_devueltos: lineas,
                articulo_nuevo_id: opt.dataset.producto,
                combinacion_nueva_id: opt.dataset.combinacion || null,
                tipo_producto_id_nuevo: opt.dataset.tipo,
                cantidad_nueva: document.getElementById('dv-cantidad-nueva').value || 1,
                precio_unitario_nuevo: opt.dataset.precio,
                iva_nuevo: opt.dataset.iva,
                descuento_nuevo: opt.dataset.descuento,
            };
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
                // La venta quedó "a cobrar" por la diferencia: se termina de cobrar
                // con el flujo normal de Ventas (mismo deep-link que usan las notificaciones).
                Swal.fire({ title: 'Cambio registrado', text: 'Falta cobrar la diferencia: $' + data.pendiente_cobro.toFixed(2) + '. Te llevamos a cobrarla.', type: 'success', timer: 2200, showConfirmButton: false })
                    .then(() => { window.location.href = '/ventas?ver=' + idventa; });
            } else {
                Swal.fire({ title: 'Listo', text: 'Cambio de producto registrado correctamente.', type: 'success', timer: 1600, showConfirmButton: false })
                    .then(() => location.reload());
                setTimeout(() => location.reload(), 1800);
            }
        } else if (data.cuentas) {
            const options = data.cuentas.map(c => `<option value="${c.id}">${c.nombre} (${c.tipo})</option>`).join('');
            Swal.fire({
                title: '¿A qué cuenta le sale la diferencia?',
                html: '<p style="font-size:13px; color:#64748b;">' + (data.error || '') + '</p>' +
                      `<select id="cuentaDifDev" class="form-select">${options}</select>`,
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => document.getElementById('cuentaDifDev').value
            }).then((r) => {
                if (r.value) enviarCambioProducto(idventa, sucursalId, Object.assign({}, payload, { cuenta_id: r.value }));
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
