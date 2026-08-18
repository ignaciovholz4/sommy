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

/* ── Devolver una venta (reusa el endpoint de anulación con reversa) ── */
function devolverVenta(idventa, folio) {
    Swal.fire({
        title: 'Devolver la venta ' + (folio || '#' + idventa),
        html: 'Se repone el stock y se genera la <b>salida de plata</b> en las cuentas donde se cobró.<br><br>¿Continuar?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, devolver',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.value) return;
        postDevolucion(`/devoluciones/anular-venta/${idventa}`, {}, (cuentaId) =>
            postDevolucion(`/devoluciones/anular-venta/${idventa}`, { cuenta_id: cuentaId }));
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
