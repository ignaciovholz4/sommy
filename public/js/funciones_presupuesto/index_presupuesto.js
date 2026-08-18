const showDetailQuote = document.querySelector("#showDetailQuote");
const detalleCliente = document.querySelector("#detalle_cliente");
const detallesFolio  = document.querySelector("#detalles_folio");
const detalleFecha = document.querySelector("#detalle_fecha");

$(document).ready(function () {
    // Inicializar DataTable
    $('#quote_table').DataTable({
        paging: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '/presupuestos-list',
            type: 'GET',
        },
        columns: [
            { data: 'idpresupuesto', name: 'idpresupuesto' },
            { data: 'cliente', name: 'cliente' },
            { data: 'telefono', name: 'telefono' },
            { data: 'fecha', name: 'fecha' },
            { data: 'folio', name: 'folio' },
            { data: 'total_neto', name: 'total_neto' },
            { data: 'total_con_iva', name: 'total_con_iva' },
            { data: 'estado', name: 'estado', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']]
    });

    // Manejar cambio de estado desde el select con confirmación SweetAlert2
    $('#quote_table').on('change', '.estado-select', function() {
        const id = $(this).data('id');
        const estado = $(this).val();
        const selectElement = this;

        if (estado === 'venta') {
            // ✅ Confirmación simple: convertir a venta
            Swal.fire({
                title: 'Convertir presupuesto en venta',
                text: '¿Seguro que deseas convertir este presupuesto en una venta?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, convertir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    fetch(`/presupuestos/${id}/estado`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ estado: estado }) // 🔹 ya no se envía caja_apertura_id
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Éxito', 'Presupuesto convertido en venta correctamente.', 'success');
                            $('#quote_table').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', data.error, 'error');
                            $(selectElement).val($(selectElement).find('option[selected]').val());
                        }
                    });
                } else {
                    $(selectElement).val($(selectElement).find('option[selected]').val());
                }
            });
        } else {
            // Confirmación normal para otros estados
            let mensaje = '';
            if (estado === 'confirmado') {
                mensaje = '¿Seguro que deseas aprobar este presupuesto? Una vez aprobado no podrá volver a borrador.';
            }

            Swal.fire({
                title: 'Confirmar cambio de estado',
                text: mensaje,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    fetch(`/presupuestos/${id}/estado`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ estado: estado })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Éxito', 'Estado actualizado a: ' + data.estado, 'success');
                            $('#quote_table').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', data.error, 'error');
                            $(selectElement).val($(selectElement).find('option[selected]').val());
                        }
                    });
                } else {
                    $(selectElement).val($(selectElement).find('option[selected]').val());
                }
            });
        }
    });
});

// Función para mostrar detalle de presupuesto en modal
function getDetailQuote(idpresupuesto) {
    fetch(`/presupuestos/${idpresupuesto}/detail`)
        .then(res => res.json())
        .then(data => {
            detalleCliente.innerText = data.presupuesto.cliente;
            detalleFecha.innerText = data.presupuesto.fecha;
            detallesFolio.innerText = data.presupuesto.folio;
            document.querySelector("#detalle_sucursal").innerText = data.presupuesto.sucursal; // ✅ mostrar sucursal

            document.querySelector("#detailTotalNeto").innerText = data.presupuesto.total_neto;
            document.querySelector("#detailTotalConIva").innerText = data.presupuesto.total_con_iva;

            showDetailQuote.innerHTML = "";
            data.detalles.forEach(d => {
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
                showDetailQuote.innerHTML += row;
            });

            const ivaContainer = document.querySelector("#detailIvaDiscriminado");
            ivaContainer.innerHTML = "";
            if (data.presupuesto.iva_discriminado) {
                data.presupuesto.iva_discriminado.forEach(item => {
                    ivaContainer.innerHTML += `<p>IVA ${item.porcentaje}%: $${item.monto}</p>`;
                });
            }

            $('#ModalDetalleQoute').modal('show');
        })
        .catch(err => {
            console.error(err);
            alert("Error al cargar detalle de la venta");
        });
}