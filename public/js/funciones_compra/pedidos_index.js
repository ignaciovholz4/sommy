$(document).ready(function () {
    $('#pedidos_table').DataTable({
        paging: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '/compras/pedidos/list',
            type: 'GET',
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'num_folio', name: 'num_folio' },
            { data: 'proveedor', name: 'proveedor' },
            { data: 'fecha', name: 'fecha' },
            { data: 'tipo_comprobante', name: 'tipo_comprobante' },
            { data: 'sucursal', name: 'sucursal' },
            { data: 'total_neto', name: 'total_neto' },
            { data: 'total_con_iva', name: 'total_con_iva' },
            { data: 'estado', name: 'estado' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']]
    });
});

/**
 * Convertir pedido en compra real (suma stock y genera CxP si corresponde)
 */
function convertirPedido(id, aCredito) {
    Swal.fire({
        title: 'Convertir en compra',
        html: 'Se creará una <b>compra real</b>: se sumará el stock en la sucursal' +
              (aCredito ? ' y se generará la <b>deuda en Cuentas por Pagar</b> (pedido marcado a crédito).' : '.') +
              '<div style="text-align:left; margin-top:16px">' +
                  '<label for="conv-adjuntos" style="font-size:13px; font-weight:600; display:block; margin-bottom:4px">' +
                      '<i class="fa fa-paperclip"></i> Adjuntar comprobantes (opcional)' +
                  '</label>' +
                  '<input type="file" id="conv-adjuntos" class="form-control" multiple accept="image/*,.pdf">' +
                  '<small id="conv-adjuntos-info" class="text-muted" style="display:block; margin-top:4px">' +
                      'Foto del remito, factura, etc. Imágenes o PDF, hasta 10 archivos de 8 MB c/u.' +
                  '</small>' +
              '</div>' +
              '<br>¿Desea continuar?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, convertir',
        cancelButtonText: 'Cancelar',
        onOpen: () => {
            document.getElementById('conv-adjuntos').addEventListener('change', function () {
                const info = document.getElementById('conv-adjuntos-info');
                info.innerText = this.files.length
                    ? this.files.length + ' archivo(s) seleccionado(s): ' + Array.from(this.files).map(f => f.name).join(', ')
                    : 'Foto del remito, factura, etc. Imágenes o PDF, hasta 10 archivos de 8 MB c/u.';
            });
        },
        preConfirm: () => document.getElementById('conv-adjuntos').files
    }).then((result) => {
        if (result.value) {
            const formData = new FormData();
            Array.from(result.value).forEach(f => formData.append('adjuntos[]', f));

            fetch(`/compras/pedidos/${id}/convertir`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(res => res.json().then(data => ({ ok: res.ok, status: res.status, data })))
            .then(({ ok, status, data }) => {
                if (ok && data.success) {
                    Swal.fire('Éxito', `Pedido convertido en la compra ${data.num_folio ?? ''}`, 'success');
                    $('#pedidos_table').DataTable().ajax.reload();
                } else if (status === 422 && data.errors) {
                    Swal.fire('Archivos inválidos', Object.values(data.errors).flat().join('<br>'), 'error');
                } else {
                    Swal.fire('Error', data.error || 'No se pudo convertir el pedido', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error al procesar la conversión', 'error');
            });
        }
    });
}

/**
 * Anular pedido (solo borradores; no afecta stock porque nunca lo tocó)
 */
function anularPedido(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: '¿Desea anular este pedido de compra?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            fetch(`/compras/pedidos/${id}/anular`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', 'Pedido anulado correctamente', 'success');
                    $('#pedidos_table').DataTable().ajax.reload();
                } else {
                    Swal.fire('Error', data.error || 'No se pudo anular el pedido', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error al procesar la anulación', 'error');
            });
        }
    });
}

/**
 * Detalle del pedido en modal
 */
function getDetailPedido(id) {
    if (typeof adjuntosPanelSetEntidad === 'function') adjuntosPanelSetEntidad('adjuntosPanelPedidoModal', 'pedido_compra', id);
    if (typeof notasPanelSetEntidad === 'function') notasPanelSetEntidad('notasPanelPedidoModal', 'pedido_compra', id);

    fetch(`/compras/pedidos/${id}/detail`)
        .then(res => res.json())
        .then(data => {
            document.querySelector('#pedido_proveedor').innerText = data.pedido.proveedor;
            document.querySelector('#pedido_fecha').innerText = data.pedido.fecha;
            document.querySelector('#pedido_folio').innerText = data.pedido.folio;
            document.querySelector('#pedido_sucursal').innerText = data.pedido.sucursal;

            const obsWrapper = document.querySelector('#pedido_obs_wrapper');
            if (data.pedido.observaciones) {
                document.querySelector('#pedido_observaciones').innerText = data.pedido.observaciones;
                obsWrapper.style.display = '';
            } else {
                obsWrapper.style.display = 'none';
            }

            document.querySelector('#pedido_total_neto').innerText = data.pedido.total_neto;
            document.querySelector('#pedido_total_con_iva').innerText = data.pedido.total_con_iva;

            const tbody = document.querySelector('#show_details_pedido');
            tbody.innerHTML = '';
            data.detalles.forEach(d => {
                let detalleNombre = d.articulo;
                if (d.combinacion) {
                    detalleNombre += ' - ' + d.combinacion;
                }

                tbody.innerHTML += `
                    <tr>
                        <td>${detalleNombre}</td>
                        <td>${d.cantidad}</td>
                        <td>${d.precio_unitario}</td>
                        <td>${d.price_list_name ?? ''}</td>
                        <td>${d.descuento ?? 0}%</td>
                        <td>${d.iva}% ${d.iva_label ? '(' + d.iva_label + ')' : ''}</td>
                        <td>${d.subtotal_neto}</td>
                        <td>${d.subtotal_con_iva}</td>
                    </tr>
                `;
            });

            const ivaContainer = document.querySelector('#pedido_iva_discriminado');
            ivaContainer.innerHTML = '';
            if (data.pedido.iva_discriminado && data.pedido.iva_discriminado.length > 0) {
                data.pedido.iva_discriminado.forEach(item => {
                    ivaContainer.innerHTML += `<p>IVA ${item.porcentaje}%: $${item.monto}</p>`;
                });
            }

            // Comprobantes adjuntos de la compra generada (remito, factura)
            const adjWrapper = document.querySelector('#pedido_adjuntos_wrapper');
            const adjContainer = document.querySelector('#pedido_adjuntos');
            adjContainer.innerHTML = '';
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
                adjWrapper.style.display = '';
            } else {
                adjWrapper.style.display = 'none';
            }

            $('#ModalDetallePedido').modal('show');
        })
        .catch(err => {
            console.error(err);
            alert('Error al cargar el detalle del pedido');
        });
}
