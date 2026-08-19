document.addEventListener("DOMContentLoaded", function () {

    const sucursalId = window.location.pathname.split("/")[2];
    const tabla = document.querySelector("#tablaStockSucursal tbody");
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    cargarStock();

    function cargarStock() {
        fetch(`/sucursal/${sucursalId}/articulos`)
            .then(r => r.json())
            .then(data => {
                tabla.innerHTML = "";

                // Artículos simples
                data.articulos.forEach(sa => {
                    const claseFila = sa.activo ? "" : "table-secondary";
                    tabla.innerHTML += `
                        <tr class="${claseFila}">
                            <td>${sa.articulo.nombre}</td>
                            <td>${sa.articulo.codigo}</td>
                            <td>${sa.stock}</td>
                            <td>${sa.stock_minimo ?? '-'}</td>
                            <td>${sa.ubicacion ?? '-'}</td>
                            <td>
                                ${
                                    sa.activo
                                    ? `
                                        <button class="btn btn-sm btn-warning btnEditStock" data-id="${sa.id}" data-tipo="simple">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-secondary btnEditMinimo" data-id="${sa.id}" data-minimo="${sa.stock_minimo ?? ''}" title="Stock mínimo (reposición IA)">
                                            <i class="fas fa-triangle-exclamation"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info btnEditUbicacion" data-id="${sa.id}" data-tipo="simple">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btnEliminarArticulo" data-id="${sa.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    `
                                    : `
                                        <button class="btn btn-sm btn-success btnReactivarArticulo" data-id="${sa.id}">
                                            <i class="fas fa-undo"></i> Reactivar
                                        </button>
                                    `
                                }
                            </td>
                        </tr>
                    `;
                });

                // Combinaciones
                if (data.combinaciones) {
                    data.combinaciones.forEach(sc => {
                        const claseFila = sc.activo ? "" : "table-secondary";
                        const a = sc.combinacion.producto;

                        tabla.innerHTML += `
                            <tr class="${claseFila}">
                                <td>
                                    <div><strong>${a.nombre}</strong> <small>(${a.codigo})</small></div>
                                    <div class="text-muted">${sc.combinacion.combinacion}</div>
                                </td>
                                <td>${a.codigo}</td>
                                <td>${sc.stock}</td>
                                <td>-</td>
                                <td>${sc.ubicacion ?? '-'}</td>
                                <td>
                                    ${
                                        sc.activo
                                        ? `
                                            <button class="btn btn-sm btn-warning btnEditStock" data-id="${sc.id}" data-tipo="combinacion">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info btnEditUbicacion" data-id="${sc.id}" data-tipo="combinacion">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btnEliminarComb" data-id="${sc.id}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        `
                                        : `
                                            <button class="btn btn-sm btn-success btnReactivarComb" data-id="${sc.id}">
                                                <i class="fas fa-undo"></i> Reactivar
                                            </button>
                                        `
                                    }
                                </td>
                            </tr>
                        `;
                    });
                }

                asignarEventos();
            });
    }

    function asignarEventos() {
        //  Artículos simples y Combinaciones
        document.querySelectorAll(".btnEditStock").forEach(btn => {
            btn.addEventListener("click", () => editarStock(btn.dataset.id, btn.dataset.tipo));
        });

        document.querySelectorAll(".btnEditUbicacion").forEach(btn => {
            btn.addEventListener("click", () => editarUbicacion(btn.dataset.id, btn.dataset.tipo));
        });

        document.querySelectorAll(".btnEditMinimo").forEach(btn => {
            btn.addEventListener("click", () => editarMinimo(btn.dataset.id, btn.dataset.minimo));
        });

        // Eliminar para Articulos Simple
        document.querySelectorAll(".btnEliminarArticulo").forEach(btn => {
            btn.addEventListener("click", () => eliminarArticulo(btn.dataset.id));
        });

        document.querySelectorAll(".btnReactivarArticulo").forEach(btn => {
            btn.addEventListener("click", () => reactivarArticulo(btn.dataset.id));
        });
        
        // Eliminar para Combinacion
        document.querySelectorAll(".btnEliminarComb").forEach(btn => {
            btn.addEventListener("click", () => eliminarCombinacion(btn.dataset.id));
        });

        document.querySelectorAll(".btnReactivarComb").forEach(btn => {
            btn.addEventListener("click", () => reactivarCombinacion(btn.dataset.id));
        });
    }

    //  Abrir modal para agregar artículo o combinación
    document.getElementById("btnAgregarArticuloSucursal").addEventListener("click", () => {
        cargarArticulosDisponibles();
        cargarFiltros();
        new bootstrap.Modal(document.getElementById("modalAgregarArticuloSucursal")).show();
    });

    // Cargar artículos disponibles para agregar
    function cargarArticulosDisponibles() {
        const tablaBuscar = document.querySelector("#tablaBuscarArticulosSucursal tbody");

        fetch(`/articulos/listar`)
            .then(r => r.json())
            .then(data => {
                tablaBuscar.innerHTML = "";

                data.items.forEach(item => {
                    if (item.tipo === "simple") {
                        tablaBuscar.innerHTML += `
                            <tr class="table-light">
                                <td><input type="checkbox" class="chkArticulo" 
                                    data-tipo="simple" 
                                    data-id="${item.idarticulo}" 
                                    data-marca="${item.marca}" 
                                    data-categoria="${item.categoria}"></td>
                                <td>${item.nombre}</td>
                                <td>${item.codigo}</td>
                                <td>${item.marca ?? '-'}</td>
                                <td>${item.categoria ?? '-'}</td>
                            </tr>
                        `;
                    } else if (item.tipo === "personalizado") {
                        // Cabecera del artículo con combinaciones
                        tablaBuscar.innerHTML += `
                            <tr class="table-secondary fw-bold">
                                <td colspan="5"><strong>${item.nombre}</strong> (${item.codigo})</td>
                            </tr>
                        `;
                    } else if (item.tipo === "combinacion") {
                        // Combinación → fila gris para distinguir
                        tablaBuscar.innerHTML += `
                            <tr class="table-grey">
                                <td><input type="checkbox" class="chkArticulo" 
                                    data-tipo="combinacion" 
                                    data-id="${item.idcombinacion}" 
                                    data-marca="${item.marca}" 
                                    data-categoria="${item.categoria}"></td>
                                <td class="ps-4">${item.nombre} - ${item.atributos}</td>
                                <td>${item.codigo}</td>
                                <td>${item.marca ?? '-'}</td>
                                <td>${item.categoria ?? '-'}</td>
                            </tr>
                        `;
                    }
                });
            });
    }


    document.getElementById("btnAgregarSeleccionados").addEventListener("click", () => {
        const seleccionados = document.querySelectorAll(".chkArticulo:checked");
        if (seleccionados.length === 0) {
            Swal.fire('Atención', 'No seleccionaste ningún artículo.', 'warning');
            return;
        }

        const promises = [];
        seleccionados.forEach(chk => {
            const id = chk.dataset.id;
            const tipo = chk.dataset.tipo;

            if (tipo === "simple") {
                promises.push(fetch(`/sucursal-articulo/store`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
                    body: JSON.stringify({ sucursal_id: sucursalId, articulo_id: id, stock: 0, ubicacion: null })
                }).then(r => r.json()));
            } else {
                promises.push(fetch(`/sucursal-combinacion/store`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
                    body: JSON.stringify({ sucursal_id: sucursalId, combinacion_id: id, stock: 0, ubicacion: null })
                }).then(r => r.json()));
            }
        });

        Promise.all(promises).then(results => {
            const errores = results.filter(r => r.estado === 0);
            if (errores.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    html: errores.map(e => e.mensaje).join("<br>"),
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#1591a3'
                });
            }
            cargarStock();
            bootstrap.Modal.getInstance(document.getElementById("modalAgregarArticuloSucursal")).hide();
        });
    });

    document.getElementById('btnSelectCategoria').addEventListener('click', function() {
        const categoria = document.getElementById('filtroCategoria').value;
        if (!categoria) return;

        document.querySelectorAll('.chkArticulo').forEach(chk => {
            if (chk.dataset.categoria === categoria) {
                chk.checked = true;
            }
        });
    });

    document.getElementById('btnSelectMarca').addEventListener('click', function() {
        const marca = document.getElementById('filtroMarca').value;
        if (!marca) return;

        document.querySelectorAll('.chkArticulo').forEach(chk => {
            if (chk.dataset.marca === marca) {
                chk.checked = true;
            }
        });
    });

    document.getElementById('btnToggleSeleccion').addEventListener('click', function() {
        const checks = document.querySelectorAll('.chkArticulo');
        const allSelected = Array.from(checks).every(chk => chk.checked);
        checks.forEach(chk => chk.checked = !allSelected);
    });

    function cargarFiltros() {
        fetch('/articulos/load-options/categoria')
            .then(r => r.json())
            .then(data => {
                const sel = document.getElementById('filtroCategoria');
                sel.innerHTML = '<option value="">-- Categoria --</option>';
                data.forEach(c => {
                    sel.innerHTML += `<option value="${c.nombre}">${c.nombre}</option>`;
                });
            });

        fetch('/articulos/load-options/marca')
            .then(r => r.json())
            .then(data => {
                const sel = document.getElementById('filtroMarca');
                sel.innerHTML = '<option value="">-- Marca --</option>';
                data.forEach(m => {
                    sel.innerHTML += `<option value="${m.nombre}">${m.nombre}</option>`;
                });
            });
    }

    //  Cargar artículos disponibles para agregar
    // function cargarArticulosDisponibles() {
    //     const tablaBuscar = document.querySelector("#tablaBuscarArticulosSucursal tbody");

    //     fetch(`/articulos/listar`)
    //         .then(r => r.json())
    //         .then(data => {
    //             tablaBuscar.innerHTML = "";

    //             data.items.forEach(item => {
    //                 if (item.tipo === "simple") {
    //                     //  Artículo simple
    //                     tablaBuscar.innerHTML += `
    //                         <tr>
    //                             <td>${item.nombre}</td>
    //                             <td>${item.codigo}</td>
    //                             <td>${item.marca_id ?? '-'}</td>
    //                             <td>
    //                                 <button class="btn btn-sm btn-success btnAgregarArticulo" data-id="${item.idarticulo}">
    //                                     <i class="fas fa-plus"></i>
    //                                 </button>
    //                             </td>
    //                         </tr>
    //                     `;
    //                 } else if (item.tipo === "personalizado") {
    //                     //  Cabecera de artículo con combinaciones
    //                     tablaBuscar.innerHTML += `
    //                         <tr class="table-secondary">
    //                             <td colspan="4"><strong>${item.nombre}</strong> (${item.codigo})</td>
    //                         </tr>
    //                     `;
    //                 } else if (item.tipo === "combinacion") {
    //                     //  Combinación del artículo
    //                     tablaBuscar.innerHTML += `
    //                         <tr>
    //                             <td class="ps-4">${item.nombre} - ${item.atributos}</td>
    //                             <td>${item.codigo}</td>
    //                             <td>${item.marca_id ?? '-'}</td>
    //                             <td>
    //                                 <button class="btn btn-sm btn-success btnAgregarCombinacion" data-id="${item.idcombinacion}">
    //                                     <i class="fas fa-plus"></i>
    //                                 </button>
    //                             </td>
    //                         </tr>
    //                     `;
    //                 }
    //             });

    //             //  Asignar eventos
    //             document.querySelectorAll(".btnAgregarArticulo").forEach(btn => {
    //                 btn.addEventListener("click", () => agregarArticulo(btn.dataset.id));
    //             });

    //             document.querySelectorAll(".btnAgregarCombinacion").forEach(btn => {
    //                 btn.addEventListener("click", () => agregarCombinacion(btn.dataset.id));
    //             });
    //         });
    // }

    //  Agregar artículo simple a la sucursal
    function agregarArticulo(articuloId) {
        fetch(`/sucursal-articulo/store`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({
                sucursal_id: sucursalId,
                articulo_id: articuloId,
                stock: 0,
                ubicacion: null
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarStock();
                bootstrap.Modal.getInstance(document.getElementById("modalAgregarArticuloSucursal")).hide();
            } else {
                alert(data.mensaje.join("\n"));
            }
        });
    }

    // Agregar combinación a la sucursal
    function agregarCombinacion(combinacionId) {
        fetch(`/sucursal-combinacion/store`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({
                sucursal_id: sucursalId,
                combinacion_id: combinacionId,
                stock: 0,
                ubicacion: null
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarStock();
                bootstrap.Modal.getInstance(document.getElementById("modalAgregarArticuloSucursal")).hide();
            } else {
                alert(data.mensaje.join("\n"));
            }
        });
    }

    // Editar stock del articulo por tipo
    function editarStock(id, tipo) {
        document.getElementById("registro_id").value = id;
        document.getElementById("tipo_registro").value = tipo;
        document.getElementById("stock_edit").value = "";
        new bootstrap.Modal(document.getElementById("modalEditarStockSucursal")).show();
    }

    document.getElementById("btnGuardarStockSucursal").addEventListener("click", () => {
        const id = document.getElementById("registro_id").value;
        const tipo = document.getElementById("tipo_registro").value;
        const stock = document.getElementById("stock_edit").value;

        const url = tipo === "simple" 
            ? "/sucursal-articulo/update-stock" 
            : "/sucursal-combinacion/update-stock";

        fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
            body: JSON.stringify({ id, stock })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarStock();
                bootstrap.Modal.getInstance(document.getElementById("modalEditarStockSucursal")).hide();
            } else {
                alert(data.mensaje);
            }
        });
    });

    // Editar ubicacion del articulo por tipo
    function editarUbicacion(id, tipo) {
        document.getElementById("registro_id_ubicacion").value = id;
        document.getElementById("tipo_registro_ubicacion").value = tipo;
        document.getElementById("ubicacion_edit").value = "";
        new bootstrap.Modal(document.getElementById("modalEditarUbicacionSucursal")).show();
    }

    document.getElementById("btnGuardarUbicacionSucursal").addEventListener("click", () => {
        const id = document.getElementById("registro_id_ubicacion").value;
        const tipo = document.getElementById("tipo_registro_ubicacion").value;
        const ubicacion = document.getElementById("ubicacion_edit").value;

        const url = tipo === "simple" 
            ? "/sucursal-articulo/update-ubicacion" 
            : "/sucursal-combinacion/update-ubicacion";

        fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
            body: JSON.stringify({ id, ubicacion })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarStock();
                bootstrap.Modal.getInstance(document.getElementById("modalEditarUbicacionSucursal")).hide();
            } else {
                alert(data.mensaje);
            }
        });
    });

    // Editar stock mínimo (solo artículos simples: lo usa la reposición inteligente)
    function editarMinimo(id, minimoActual) {
        document.getElementById("registro_id_minimo").value = id;
        document.getElementById("minimo_edit").value = minimoActual || "";
        new bootstrap.Modal(document.getElementById("modalEditarMinimoSucursal")).show();
    }

    document.getElementById("btnGuardarMinimoSucursal").addEventListener("click", () => {
        const id = document.getElementById("registro_id_minimo").value;
        const stock_minimo = document.getElementById("minimo_edit").value;

        fetch("/sucursal-articulo/update-stock-minimo", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
            body: JSON.stringify({ id, stock_minimo: stock_minimo === "" ? null : stock_minimo })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarStock();
                bootstrap.Modal.getInstance(document.getElementById("modalEditarMinimoSucursal")).hide();
            } else {
                alert(data.mensaje);
            }
        });
    });

    //  Eliminar / Reactivar artículo simple
    function eliminarArticulo(id) {
        if (!confirm("¿Desactivar artículo en esta sucursal?")) return;

        fetch(`/sucursal-articulo/delete`, {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({ id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarStock();
            } else {
                alert(data.mensaje);
            }
        });
    }

    function reactivarArticulo(id) {
        fetch(`/sucursal-articulo/activar`, {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({ id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarStock();
            } else {
                alert(data.mensaje);
            }
        });
    }

    //  Eliminar / Reactivar combinación
    function eliminarCombinacion(id) {
        if (!confirm("¿Desactivar combinación en esta sucursal?")) return;

        fetch(`/sucursal-combinacion/delete`, {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({ id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarStock();
            } else {
                alert(data.mensaje);
            }
        });
    }

    function reactivarCombinacion(id) {
        fetch(`/sucursal-combinacion/activar`, {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({ id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.estado === 1) {
                cargarStock();
            } else {
                alert(data.mensaje);
            }
        });
    }

});