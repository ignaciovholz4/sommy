const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const orderId = document.querySelector("#orderId");
const modalSelectPayment = document.getElementById('typePaymentModal');
const btnPaymentMethod = document.querySelector('#btn-payment-method');
const selectPaymentMethod = document.querySelector('#select-payment-method');
const btnClosePaymentMethod = document.querySelector('#btn-close-payment-method');

let statusValueOrderId = 0;

fnUpdateStatusOrder = (statusOrder) => {
  statusValueOrderId = Number(statusOrder);

  if (statusOrder === 2) { // Comprobación de stock
    const modalStock = new bootstrap.Modal(document.getElementById('checkStockModal'));
    modalStock.show();

    const tbody = document.getElementById('stock-check-body');
    tbody.innerHTML = '';

    // 🔹 Consultar productos de la orden con sus sucursales
    fetch(`/stock/orden/${orderId.value}/productos-sucursales`)
      .then(res => res.json())
      .then(data => {
        if (data.estado === 1) {
          data.productos.forEach(item => {
            // 🔹 Calcular stock total (simple + combinaciones)
            const totalStock = item.sucursales.reduce((acc, s) => {
              let simple = Number(s.stock_simple || 0);
              let combinaciones = (s.combinaciones || []).reduce((accC, c) => accC + Number(c.stock || 0), 0);
              return acc + simple + combinaciones;
            }, 0);

            tbody.innerHTML += `
              <tr data-product="${item.product_id}">
                <td>${item.name_product}</td>
                <td>${item.quantity}</td>
                <td>${totalStock}</td>
                <td>
                  <div class="sucursal-container"></div>
                  <button type="button" class="btn btn-success btn-add-sucursal" data-product="${item.product_id}">
                    + Añadir sucursal
                  </button>
                </td>
              </tr>
            `;
          });

          // Inicializar botón de añadir sucursal
          document.querySelectorAll('.btn-add-sucursal').forEach(btn => {
            btn.addEventListener('click', () => {
              const productId = btn.dataset.product;
              const container = btn.closest('td').querySelector('.sucursal-container');

              // Crear fila sucursal
              const row = document.createElement('div');
              row.classList.add('row','mb-2','sucursal-row');
              row.innerHTML = `
                <div class="col-6">
                  <select class="form-select sucursal-select" data-product="${productId}">
                    <option value="">Seleccionar sucursal</option>
                  </select>
                </div>
                <div class="col-3">
                  <input type="number" class="form-control cantidad-sucursal" min="1" placeholder="Cant.">
                </div>
                <div class="col-3">
                  <button type="button" class="btn btn-danger btn-remove-sucursal">-</button>
                </div>
              `;
              container.appendChild(row);

              // Llenar opciones de sucursales desde el endpoint
              fetch(`/stock/producto/${productId}/sucursales`)
                .then(res => res.json())
                .then(data => {
                  const select = row.querySelector('.sucursal-select');
                  data.forEach(suc => {
                    // 🔹 Si no tiene combinaciones → mostramos stock simple con sucursal_id como value
                    if (!suc.combinaciones || suc.combinaciones.length === 0) {
                      select.innerHTML += `<option value="${suc.sucursal_id}">
                        ${suc.sucursal} (Stock: ${Number(suc.stock_simple)})
                      </option>`;
                    } else {
                      // 🔹 Si tiene combinaciones → mostramos cada combinación con sucursal_id-combinacion_id
                      suc.combinaciones.forEach(c => {
                        select.innerHTML += `<option value="${suc.sucursal_id}-${c.combinacion_id}">
                          ${suc.sucursal} - ${c.nombre} (Stock: ${Number(c.stock)})
                        </option>`;
                      });
                    }
                  });
                });

              // Botón eliminar sucursal
              row.querySelector('.btn-remove-sucursal').addEventListener('click', () => {
                row.remove();
              });
            });
          });
        }
      });
    return false;
  }

  if (statusOrder === 3) { // Pagado
    const modalPayment = new bootstrap.Modal(modalSelectPayment);
    modalPayment.show();

    const select = document.getElementById('select-payment-method');
    select.innerHTML = '<option value="">Seleccionar</option>';

    // 🔹 Traer todas las cuentas abiertas
    fetch(`/cuentas-abiertas`)
      .then(res => res.json())
      .then(data => {
        if (data.estado === 1) {
          // 🔹 Cargar cajas
          data.cajas.forEach(caja => {
            select.innerHTML += `<option value="caja-${caja.id}">
              Caja: ${caja.nombre} (${caja.moneda}) - ${caja.sucursal}
            </option>`;
          });

          // 🔹 Cargar bancos
          data.bancos.forEach(banco => {
            select.innerHTML += `<option value="banco-${banco.id}">
              Banco: ${banco.nombre} (${banco.moneda}) - ${banco.sucursal}
            </option>`;
          });
        }

        // Si no hay ninguna cuenta abierta, avisar en vez de dejar el select vacío
        const aviso = document.getElementById('aviso-sin-cuentas');
        const sinCuentas = select.options.length <= 1;
        if (aviso) aviso.style.display = sinCuentas ? 'block' : 'none';
        const btnAceptar = document.getElementById('btn-payment-method');
        if (btnAceptar) btnAceptar.disabled = sinCuentas;
      })
      .catch(function () {
        const aviso = document.getElementById('aviso-sin-cuentas');
        if (aviso) aviso.style.display = 'block';
      });

    return false;
  }

  if (statusOrder === 4) { // Enviado
    Swal.fire({
      title: "¿Confirmar envío del pedido?",
      text: "El pedido pasará al estado 'Enviado'.",
      type: "question",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, enviar"
    }).then((result) => {
      if (result.value) {
        let url = "/updateStatusOrder";
        const formData = new FormData();
        formData.append("statusId", 4); // Enviado
        formData.append("orderId", Number(orderId.value));

        seend_data(url, formData).then((resp) => {
          if (resp && resp.status === 1) {
            sweetSuccess("El pedido fue marcado como enviado");
            setTimeout(() => location.reload(), 500);
          } else if (resp && resp.status === 0) {
            sweetError(Array.isArray(resp.mensaje) ? resp.mensaje[0] : resp.mensaje);
          }
        });
      }
    });
    return false;
  }

  if (statusOrder === 5) { // Entregado
    Swal.fire({
      title: "¿Confirmar entrega del pedido?",
      text: "El pedido pasará al estado 'Entregado'.",
      type: "question",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, entregar"
    }).then((result) => {
      if (result.value) {
        let url = "/updateStatusOrder";
        const formData = new FormData();
        formData.append("statusId", 5); // Entregado
        formData.append("orderId", Number(orderId.value));

        seend_data(url, formData).then((resp) => {
          if (resp && resp.status === 1) {
            sweetSuccess("El pedido fue marcado como entregado");
            setTimeout(() => location.reload(), 500);
          } else if (resp && resp.status === 0) {
            sweetError(Array.isArray(resp.mensaje) ? resp.mensaje[0] : resp.mensaje);
          }
        });
      }
    });
    return false;
  }

  if (statusOrder === 6) { // Anulado
    Swal.fire({
      title: "¿Confirmar anulación del pedido?",
      text: "El pedido pasará al estado 'Anulado'. Esta acción no se puede revertir.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Sí, anular"
    }).then((result) => {
      if (result.value) {
        let url = "/updateStatusOrder";
        const formData = new FormData();
        formData.append("statusId", 6);
        formData.append("orderId", Number(orderId.value));

        seend_data(url, formData).then((resp) => {
          if (resp && resp.status === 1) {
            sweetSuccess("El pedido fue anulado correctamente");
            setTimeout(() => location.reload(), 500);
          } else if (resp && resp.status === 0) {
            sweetError(Array.isArray(resp.mensaje) ? resp.mensaje[0] : resp.mensaje);
          }
        });
      }
    });
    return false;
  }

}

const seend_data = async (url,form) => {
  try {
    let seend = await fetch(url, {
      method: "post",
      headers: {
          'X-CSRF-TOKEN': token,
      },
      body: form,
    });
    let data = await seend.json();
    return data;
  } catch (error) {
      console.log(error);     
  }
}

btnPaymentMethod.addEventListener("click", (e) => {
  e.preventDefault();
  const paymentValue = selectPaymentMethod.value;

  if (!paymentValue) {
    sweetError("Debes seleccionar un método de pago");
    return;
  }

  let url = "/updatePaidStatus";
  const formData = new FormData();
  formData.append("statusId", 3); // Pagado
  formData.append("orderId", Number(orderId.value));
  formData.append("paymentId", paymentValue); // "caja-12" o "banco-5"

  seend_data(url, formData)
    .then((resp) => {
      if (resp && resp.status === 1) {
        sweetSuccess(resp.message);
        btnClosePaymentMethod.click();
        setTimeout(() => location.reload(), 500);
      } else if (resp && resp.status === 0) {
        sweetError(Array.isArray(resp.mensaje) ? resp.mensaje[0] : resp.mensaje);
      } else {
        sweetError("Error inesperado en la respuesta del servidor");
      }
    })
    .catch((err) => {
      sweetError("Error en la petición: " + err.message);
    });
});

document.getElementById('btn-confirm-stock').addEventListener('click', () => {
  let url = "/updateStatusOrder";
  const formData = new FormData();
  formData.append("statusId", 2); // Comprobación de stock
  formData.append("orderId", Number(orderId.value));

  // 🔹 Recorrer todas las filas de productos y sucursales
  document.querySelectorAll('tr[data-product]').forEach(tr => {
    const productId = tr.dataset.product;
    const sucursalRows = tr.querySelectorAll('.sucursal-row');
    sucursalRows.forEach((row, idx) => {
      const sucursal = row.querySelector('.sucursal-select').value;
      const cantidad = row.querySelector('.cantidad-sucursal').value;
      if (sucursal && cantidad > 0) {
        formData.append(`producto_${productId}_sucursal_${idx}`, JSON.stringify({ sucursal, cantidad }));
      }
    });
  });

  seend_data(url, formData).then(resp => {
    if(resp.status === 1){
      sweetSuccess("Stock comprobado correctamente");
      setTimeout(() => location.reload(), 500);
    }
    if(resp.status === 0){
      // 🔹 Mostrar todos los errores juntos en un único SweetAlert
      let mensajes = Array.isArray(resp.mensaje) ? resp.mensaje.join('<br>') : resp.mensaje;
      Swal.fire({
        title: "Errores de stock",
        html: mensajes,   // usamos html para mostrar cada error en nueva línea
        type: "error",
        confirmButtonText: "Entendido"
      });
    }
  });
});
