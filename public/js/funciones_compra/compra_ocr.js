/**
 * Carga de comprobantes con IA: sube la foto/PDF de una factura o remito de
 * proveedor, precarga cabecera + items en el formulario de compras/create,
 * y deja marcados en rojo los datos que la IA no pudo confirmar para que el
 * usuario los complete a mano antes de guardar.
 */
document.addEventListener("DOMContentLoaded", () => {
    const procesarBtn = document.querySelector("#ocrProcesarBtn");
    if (!procesarBtn) return;

    const archivoInput = document.querySelector("#ocrArchivo");
    const spinner = document.querySelector("#ocrSpinner");
    const errorBox = document.querySelector("#ocrError");

    procesarBtn.addEventListener("click", () => {
        const archivo = archivoInput.files[0];
        if (!archivo) {
            mostrarError("Elegí un archivo primero.");
            return;
        }

        errorBox.classList.add("d-none");
        spinner.classList.remove("d-none");
        procesarBtn.disabled = true;

        const formData = new FormData();
        formData.append("archivo", archivo);

        fetch(ocrUploadUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            },
            body: formData,
        })
            .then((res) => res.json())
            .then((data) => {
                spinner.classList.add("d-none");
                procesarBtn.disabled = false;

                if (!data.success) {
                    mostrarError(data.error || "No se pudo procesar el comprobante.");
                    return;
                }

                precargarFormulario(data);
                $("#modalOcrComprobante").modal("hide");
            })
            .catch(() => {
                spinner.classList.add("d-none");
                procesarBtn.disabled = false;
                mostrarError("Error de conexión al procesar el comprobante.");
            });
    });

    function mostrarError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.remove("d-none");
    }

    function marcarPendiente(el) {
        if (!el) return;
        el.classList.add("is-invalid", "border-danger");
    }

    function precargarFormulario(data) {
        const proveedorSelect = document.querySelector("#proveedor_id");
        if (data.proveedor_id) {
            proveedorSelect.value = data.proveedor_id;
        } else {
            marcarPendiente(proveedorSelect);
        }
        if (data.proveedor_necesita_confirmacion) {
            marcarPendiente(proveedorSelect);
        }

        if (data.fecha) {
            document.querySelector("#fecha").value = data.fecha;
        }

        const tipoSelect = document.querySelector("#tipo_comprobante_id");
        if (data.tipo_comprobante_id) {
            tipoSelect.value = data.tipo_comprobante_id;
        } else {
            marcarPendiente(tipoSelect);
        }

        if (data.num_folio_extraido) {
            const aviso = document.createElement("div");
            aviso.className = "alert alert-info mt-2";
            aviso.innerHTML = `Comprobante leído: <strong>${data.num_folio_extraido}</strong>` +
                (data.confianza != null ? ` (confianza IA: ${Math.round(data.confianza * 100)}%)` : "");
            document.querySelector(".compra-head-grid").insertAdjacentElement("afterend", aviso);
        }

        (data.items || []).forEach((item) => agregarFilaOcr(item));
        calcularTotal();
    }

    function agregarFilaOcr(item) {
        const row = document.createElement("tr");
        row.setAttribute("data-idarticulo", item.idarticulo || "");

        const nombreCelda = item.idarticulo
            ? `<input type="hidden" name="items[${itemIndex}][idarticulo]" value="${item.idarticulo}">
               <input type="hidden" name="items[${itemIndex}][tipo_producto_id]" value="${item.tipo_producto_id || 1}">
               ${item.nombre}`
            : `<span class="badge badge-danger">Sin identificar</span> "${item.descripcion_extraida || ''}"
               <select name="items[${itemIndex}][idarticulo]" class="form-control mt-1 articulo-manual is-invalid" required>
                   <option value="">Elegí el artículo...</option>
               </select>
               <input type="hidden" name="items[${itemIndex}][tipo_producto_id]" value="1">`;

        row.innerHTML = `
            <td>${nombreCelda}</td>
            <td>
                <input type="number" name="items[${itemIndex}][cantidad]" value="${item.cantidad || 1}" min="1" class="form-control cantidad">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][precio_unitario]" value="${item.precio_unitario ?? item.pcompra_con_iva ?? 0}" step="0.01" class="form-control precio">
            </td>
            <td>
                <select name="items[${itemIndex}][price_list_id]" class="form-control price-list">
                    <option value="">Sin lista</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][descuento]" value="${item.descuento || 0}" min="0" max="100" class="form-control descuento">
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
        compraItems.appendChild(row);

        const ivaSelect = row.querySelector(".iva");
        if (ivaSelect && item.iva_compra != null) ivaSelect.value = item.iva_compra;

        // Si el artículo no se pudo identificar, ofrecemos elegirlo entre los
        // disponibles de la sucursal ya cargada en el selector principal.
        if (!item.idarticulo) {
            const manualSelect = row.querySelector(".articulo-manual");
            document.querySelectorAll("#articuloSelect option").forEach((opt) => {
                if (opt.value) manualSelect.add(new Option(opt.text, opt.value));
            });
        }

        itemIndex++;
        inicializarFila(row);
    }
});
