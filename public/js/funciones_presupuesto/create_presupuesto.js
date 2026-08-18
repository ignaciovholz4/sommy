const presupuestoItems = document.querySelector("#presupuestoItems");
const articuloSelect = document.querySelector("#articuloSelect");
const addArticuloBtn = document.querySelector("#addArticuloBtn");

let itemIndex = document.querySelectorAll("#presupuestoItems tr").length || 0;

/**
 * Inicializa eventos en una fila (cantidad, precio, descuento, iva, eliminar)
 */
function inicializarFila(row) {
    row.querySelector(".cantidad").addEventListener("input", calcularTotal);
    row.querySelector(".precio").addEventListener("input", calcularTotal);
    row.querySelector(".iva").addEventListener("change", calcularTotal);
    row.querySelector(".descuento").addEventListener("input", calcularTotal);
    const priceListSelect = row.querySelector(".price-list");
    if (priceListSelect) {
        priceListSelect.addEventListener("change", calcularTotal);
    }
    row.querySelector(".removeItem").addEventListener("click", () => {
        row.remove();
        calcularTotal();
    });
}

/**
 * Recalcula subtotales, totales y IVA discriminado
 */
function calcularTotal() {
    let totalNeto = 0;
    let totalConIva = 0;
    let ivaDiscriminado = {};

    document.querySelectorAll("#presupuestoItems tr").forEach(row => {
        const cantidad = parseFloat(row.querySelector(".cantidad").value) || 0;
        const precio = parseFloat(row.querySelector(".precio").value) || 0;
        const iva = parseFloat(row.querySelector(".iva").value) || 0;
        const descuento = parseFloat(row.querySelector("input[name*='[descuento]']").value) || 0;

        // lista de precios seleccionada
        const priceListSelect = row.querySelector(".price-list");
        const priceListId = priceListSelect ? priceListSelect.value : null;

        let precioBase = precio;

        // aplicar descuento normal
        const precioConDescuento = precioBase - (precioBase * descuento / 100);

        const subtotalNeto = cantidad * precioConDescuento;
        const montoIva = subtotalNeto * (iva / 100);
        const subtotalConIva = subtotalNeto + montoIva;

        // actualizar celdas visibles
        row.querySelector(".subtotalNeto").innerText = subtotalNeto.toFixed(2);
        row.querySelector(".subtotalConIva").innerText = subtotalConIva.toFixed(2);

        // acumular
        totalNeto += subtotalNeto;
        totalConIva += subtotalConIva;

        // acumular discriminado
        if (!ivaDiscriminado[iva]) ivaDiscriminado[iva] = 0;
        ivaDiscriminado[iva] += montoIva;
    });

    // mostrar totales
    document.querySelector("#totalNeto").innerText = totalNeto.toFixed(2);
    document.querySelector("#totalConIva").innerText = totalConIva.toFixed(2);

    // actualizar hidden inputs
    const inputTotalNeto = document.querySelector("#inputTotalNeto");
    const inputTotalConIva = document.querySelector("#inputTotalConIva");
    if (inputTotalNeto) inputTotalNeto.value = totalNeto.toFixed(2);
    if (inputTotalConIva) inputTotalConIva.value = totalConIva.toFixed(2);

    // mostrar IVA discriminado
    const ivaContainer = document.querySelector("#ivaDiscriminado");
    if (ivaContainer) {
        ivaContainer.innerHTML = "";
        for (const [tipoIva, monto] of Object.entries(ivaDiscriminado)) {
            ivaContainer.innerHTML += `<p>IVA ${tipoIva}%: $${monto.toFixed(2)}</p>`;
        }
    }
}

/**
 * Agregar artículo desde el select
 */
if (addArticuloBtn) {
    addArticuloBtn.addEventListener("click", () => {
        const selected = articuloSelect.options[articuloSelect.selectedIndex];
        if (!selected.value) return;

        const id = selected.value;
        const nombre = selected.text;
        const precio = parseFloat(selected.dataset.precio);
        const ivaDefault = selected.dataset.iva;
        const descuento = parseFloat(selected.dataset.descuento) || 0;

        // Buscar si ya existe una fila con este artículo + combinación + tipo
        let existingRow = presupuestoItems.querySelector(
            `tr[data-idarticulo="${id}"][data-combinacion="${selected.dataset.combinacion || ''}"][data-tipo="${selected.dataset.tipo || 1}"]`
        );
        if (existingRow) {
            let cantidadInput = existingRow.querySelector(".cantidad");
            cantidadInput.value = parseInt(cantidadInput.value) + 1;
            calcularTotal();
            return;
        }

        // Crear nueva fila
        const row = document.createElement("tr");
        row.setAttribute("data-idarticulo", id);
        row.setAttribute("data-combinacion", selected.dataset.combinacion || '');
        row.setAttribute("data-tipo", selected.dataset.tipo || 1);
        row.innerHTML = `
            <td>
                <input type="hidden" name="items[${itemIndex}][idarticulo]" value="${id}">
                <input type="hidden" name="items[${itemIndex}][combinacion_id]" value="${selected.dataset.combinacion || ''}">
                <input type="hidden" name="items[${itemIndex}][tipo_producto_id]" value="${selected.dataset.tipo || 1}">
                ${nombre}
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][cantidad]" value="1" min="1" class="form-control cantidad">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][precio_unitario]" value="${precio}" step="0.01" class="form-control precio">
            </td>
            <td>
                <select name="items[${itemIndex}][price_list_id]" class="form-control price-list">
                    <option value="">Cargando listas...</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][descuento]" value="${descuento}" min="0" max="100" class="form-control descuento">
            </td>
            <td>
                <select name="items[${itemIndex}][iva]" class="form-control iva">
                    ${ivaOptions}
                </select>
            </td>
            <td class="subtotalNeto"></td>
            <td class="subtotalConIva"></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm removeItem">Eliminar</button>
            </td>
        `;
        presupuestoItems.appendChild(row);

        fetch(`/price-lists/applicable/${id}?context=sales`)
            .then(res => res.json())
            .then(lists => {
                let options = '<option value="">Sin lista</option>';
                lists.forEach(l => {
                    options += `<option value="${l.id}">${l.name}</option>`;
                });
                row.querySelector(".price-list").innerHTML = options;
            });

        // Seleccionar por defecto el IVA del artículo
        const ivaSelect = row.querySelector(".iva");
        if (ivaSelect) {
            ivaSelect.value = ivaDefault;
        }

        itemIndex++;

        inicializarFila(row);
        calcularTotal();
    });
}

/**
 * Inicializar filas existentes (caso edit.blade)
 */
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("#presupuestoItems tr").forEach(row => {
        inicializarFila(row);

        const articuloId = row.dataset.idarticulo;
        const selectedListId = row.dataset.selectedList;

        if (articuloId) {
            fetch(`/price-lists/applicable/${articuloId}?context=sales`)
                .then(res => res.json())
                .then(lists => {
                    let options = '<option value="">Sin lista</option>';
                    lists.forEach(l => {
                        options += `<option value="${l.id}" ${l.id == selectedListId ? 'selected' : ''}>${l.name}</option>`;
                    });
                    row.querySelector(".price-list").innerHTML = options;
                });
        }
    });
    calcularTotal();

    const sucursalSelect = document.querySelector("#sucursal_id");
    const articuloSelect = document.querySelector("#articuloSelect");

    if (sucursalSelect) {
        sucursalSelect.addEventListener("change", function() {
            const sucursalId = this.value;
            if (!sucursalId) {
                $(articuloSelect).empty().trigger('change');
                return;
            }

            fetch(`/sucursal/${sucursalId}/articulos-disponibles?context=venta`)
                .then(res => res.json())
                .then(data => {
                    $(articuloSelect).empty();
                    $(articuloSelect).append(new Option("Seleccione un artículo", "", true, false));

                    // Artículos
                    data.articulos.forEach(a => {
                        const opt = new Option(`${a.nombre} (${a.codigo})`, a.idarticulo, false, false);
                        opt.dataset.precio = a.pventa_con_iva;
                        opt.dataset.iva = a.iva_venta;
                        opt.dataset.descuento = a.descuento;
                        opt.dataset.tipo = "1";
                        articuloSelect.add(opt);
                    });

                    // Combinaciones
                    data.combinaciones.forEach(c => {
                        const opt = new Option(`${c.nombre} (${c.codigo})`, c.producto_id, false, false);
                        opt.dataset.combinacion = c.idcombinacion;
                        opt.dataset.tipo = "2";
                        opt.dataset.precio = c.pventa_con_iva;
                        opt.dataset.iva = c.iva_venta;
                        opt.dataset.descuento = 0;
                        articuloSelect.add(opt);
                    });

                    $(articuloSelect).trigger('change');
                });
        });

        enableSelect2("#articuloSelect");
    }
});