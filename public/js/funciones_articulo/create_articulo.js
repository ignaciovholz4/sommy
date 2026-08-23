
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const productoId = document.querySelector('#productoId');

let existingVariantForProduct = []; //existing variants for products

/* ============================================================
   GALERÍA DE IMÁGENES (dinámica, tabla producto_imagenes)
   ============================================================ */
const MAX_GALERIA = 8;
let galeriaEliminar = []; // ids de producto_imagenes a borrar

document.addEventListener("DOMContentLoaded", function () {
    const galeriaInput = document.getElementById("galeriaInput");
    const previews = document.getElementById("galeria-previews");

    if (!galeriaInput || !previews) return;

    // Preview de archivos nuevos seleccionados
    galeriaInput.addEventListener("change", function () {
        // Limpiar previews de archivos nuevos anteriores (los existentes tienen data-id)
        previews.querySelectorAll(".galeria-item-nueva").forEach(el => el.remove());

        const existentes = previews.querySelectorAll(".galeria-item").length;
        const disponibles = MAX_GALERIA - existentes;

        if (galeriaInput.files.length > disponibles) {
            Swal.fire("Atención", `Máximo ${MAX_GALERIA} imágenes de galería. Podés agregar ${disponibles} más.`, "warning");
            galeriaInput.value = "";
            return;
        }

        Array.from(galeriaInput.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement("div");
                div.className = "galeria-item-nueva text-center border border-primary rounded p-1";
                div.style.width = "120px";
                div.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;">
                    <small class="d-block text-primary">Nueva</small>
                `;
                previews.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // Eliminar imagen existente (delegación)
    previews.addEventListener("click", function (e) {
        const btn = e.target.closest(".galeria-eliminar");
        if (!btn) return;

        const item = btn.closest(".galeria-item");
        if (item && item.dataset.id) {
            galeriaEliminar.push(item.dataset.id);
            item.remove();
        }
    });
});

// Vuelca eliminaciones y orden a los inputs ocultos del form (llamar antes de new FormData)
function fnSyncGaleriaInputs() {
    const inputEliminar = document.getElementById("imagenes_eliminar");
    const inputOrden = document.getElementById("imagenes_orden");

    if (inputEliminar) {
        inputEliminar.value = galeriaEliminar.join(",");
    }

    if (inputOrden) {
        const mapa = {};
        document.querySelectorAll("#galeria-previews .galeria-item").forEach(item => {
            const ordenInput = item.querySelector(".galeria-orden");
            if (item.dataset.id && ordenInput) {
                mapa[item.dataset.id] = ordenInput.value || 0;
            }
        });
        inputOrden.value = JSON.stringify(mapa);
    }
}

// 🔹 Inicialización principal
document.addEventListener("DOMContentLoaded", function () {
    const selectTipo = document.getElementById("select-tipo-producto");
    const cardVariantes = document.getElementById("card-variantes");
    const btnAddAtributo = document.getElementById("btn-add-atributo");

    // Ejecutar al inicio
    toggleVariantesCard(selectTipo, cardVariantes);

    // Ejecutar cada vez que cambie el select
    selectTipo.addEventListener("change", function () {
        toggleVariantesCard(selectTipo, cardVariantes);
    });

    // Evento para agregar atributo
    btnAddAtributo.addEventListener("click", crearAtributo);
});


// 🔹 Función global: habilita/deshabilita el tab de variantes
function toggleVariantesCard(selectTipo, cardVariantes) {

    if (selectTipo.value === "2") {
        cardVariantes.style.display = "block";
    } else {
        cardVariantes.style.display = "none";
    }

    // Con variantes el precio se carga POR VARIANTE: el precio base no aplica
    const preciosBase = document.getElementById("seccion-precios-base");
    if (preciosBase) {
        preciosBase.style.display = selectTipo.value === "2" ? "none" : "";
    }
}

// 🔹 Función global: crea un nuevo bloque de atributo con variantes
let atributoCount = 0;
window.atributosConfirmados = [];

// Crear atributo
function crearAtributo() {
  atributoCount++;
  const atributosContainer = document.getElementById("atributos-container");
  const atributoId = `atributo-${atributoCount}`;

  const atributoHTML = `
    <div class="card mb-3 atributo-card" id="${atributoId}">
      <div class="card-header bg-primary text-white">
        <div class="row">
          <div class="col-6 d-flex align-items-center">
            <strong>Atributo</strong>
          </div>
          <div class="col-6 text-end">
            <button class="btn btn-sm btn-danger btn-delete-atributo">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Nombre del atributo</label>
          <input type="text" class="form-control atributo-nombre" placeholder="Ej: Talle, Color">
        </div>
        <div class="variantes-section"></div>
        <button class="btn btn-sm btn-success btn-add-variante">
          <i class="fas fa-plus"></i> Agregar variante
        </button>
      </div>
    </div>
  `;

  atributosContainer.insertAdjacentHTML("beforeend", atributoHTML);

  const nuevoAtributo = document.getElementById(atributoId);
  const btnAddVariante = nuevoAtributo.querySelector(".btn-add-variante");
  const btnDeleteAtributo = nuevoAtributo.querySelector(".btn-delete-atributo");
  const variantesSection = nuevoAtributo.querySelector(".variantes-section");

  // Agregar variante
  btnAddVariante.addEventListener("click", function () {
    const varianteHTML = `
      <div class="card bg-light mb-2 variante-card">
        <div class="card-body p-2">
          <div class="row g-2 align-items-center">
            <div class="col">
              <input type="text" class="form-control variante-input" placeholder="Ej: XL, Azul, etc.">
            </div>
            <div class="col-auto">
              <button class="btn btn-sm btn-outline-danger btn-delete-variante">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
    variantesSection.insertAdjacentHTML("beforeend", varianteHTML);

    const nuevaVariante = variantesSection.lastElementChild;
    const btnDeleteVariante = nuevaVariante.querySelector(".btn-delete-variante");
    btnDeleteVariante.addEventListener("click", () => nuevaVariante.remove());
  });

  // Eliminar atributo completo
  btnDeleteAtributo.addEventListener("click", function () {
    nuevoAtributo.remove();
  });
}

// Obtener atributos desde el modal
function getAtributosData() {
  const atributos = [];
  document.querySelectorAll("#atributos-container .atributo-card").forEach(card => {
    const nombre = card.querySelector(".atributo-nombre").value.trim();
    const variantes = [];
    card.querySelectorAll(".variante-input").forEach(input => {
      if (input.value.trim() !== "") {
        variantes.push(input.value.trim());
      }
    });
    if (nombre && variantes.length > 0) {
      atributos.push({ nombre, variantes });
    }
  });
  return atributos;
}

// Generar combinaciones
function generarCombinaciones(atributos) {
  if (atributos.length === 0) return [];
  return atributos.reduce((acc, atributo) => {
    const nuevasCombinaciones = [];
    acc.forEach(combinacion => {
      atributo.variantes.forEach(variante => {
        nuevasCombinaciones.push([...combinacion, variante]);
      });
    });
    return nuevasCombinaciones;
  }, [[]]);
}

function renderCombinacionesTable(combinaciones, precios = []) {
    const tbody = document.querySelector("#combinaciones-table tbody");
    tbody.innerHTML = "";

    combinaciones.forEach((comb, index) => {
        const precio = precios[index] || {};

        const row = document.createElement("tr");
        const idAttr = precio.idcombinacion ? `data-idcombinacion="${precio.idcombinacion}"` : "";

        row.innerHTML = `
            <td ${idAttr}>${comb.join(" / ")}</td>

            <td class="text-center" style="min-width:120px;">
                <img src="${precio.imagen_url ?? ''}"
                     class="img-thumbnail preview-imagen-variante mb-1"
                     data-index="${index}"
                     style="max-height:60px; ${precio.imagen_url ? '' : 'display:none;'}">
                <input type="file"
                       class="form-control form-control-sm imagen-variante"
                       data-index="${index}"
                       accept="image/*">
            </td>

            <td>
                <input type="text"
                       class="form-control sku-variante"
                       data-index="${index}"
                       value="${precio.sku ?? ''}"
                       maxlength="32"
                       placeholder="Automático">
            </td>

            <td>
                <input type="text"
                       class="form-control solo-numeros pcompra-variante"
                       data-index="${index}"
                       value="${precio.pcompra_variante ?? ''}"
                       placeholder="0.00">
            </td>

            <td>
                <input type="text"
                       class="form-control solo-numeros pventa-variante"
                       data-index="${index}"
                       value="${precio.pventa_variante ?? ''}"
                       placeholder="0.00">
            </td>
        `;

        if (precio.idcombinacion) {
            row.dataset.idcombinacion = precio.idcombinacion;
        }

        tbody.appendChild(row);
    });
}

// Preview de la imagen elegida para una combinación
document.addEventListener("change", function (e) {
    if (!e.target.classList.contains("imagen-variante")) return;

    const file = e.target.files[0];
    const preview = e.target.closest("td").querySelector(".preview-imagen-variante");
    if (!preview) return;

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = "";
    }
});


// Abrir modal y cargar atributos confirmados
function openAtributosModal(modo = "editar") {
  const modalTitle = document.getElementById("atributosModalLabel");
  modalTitle.textContent = (modo === "editar") ? "Editar atributos" : "Agregar atributos";

  const atributosContainer = document.getElementById("atributos-container");
  atributosContainer.innerHTML = "";
  atributoCount = 0;

  const data = window.atributosConfirmados || [];
  data.forEach(attr => {
    crearAtributo();
    const card = atributosContainer.lastElementChild;
    card.querySelector(".atributo-nombre").value = attr.nombre;

    const variantesSection = card.querySelector(".variantes-section");
    attr.variantes.forEach(v => {
      const varianteHTML = `
        <div class="card bg-light mb-2 variante-card">
          <div class="card-body p-2">
            <div class="row g-2 align-items-center">
              <div class="col">
                <input type="text" class="form-control variante-input" value="${v}">
              </div>
              <div class="col-auto">
                <button class="btn btn-sm btn-outline-danger btn-delete-variante">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
      variantesSection.insertAdjacentHTML("beforeend", varianteHTML);

      const nuevaVariante = variantesSection.lastElementChild;
      const btnDeleteVariante = nuevaVariante.querySelector(".btn-delete-variante");
      btnDeleteVariante.addEventListener("click", () => nuevaVariante.remove());
    });
  });

  const modal = new bootstrap.Modal(document.getElementById("atributosModal"));
  modal.show();
}

// Inicialización principal
document.addEventListener("DOMContentLoaded", function () {
  const btnSave = document.getElementById("btn-save-atributos");
  const modalEl = document.getElementById("atributosModal");

  // Guardar: persistir y cerrar
  btnSave.addEventListener("click", function () {
      const atributos = getAtributosData();
      window.atributosConfirmados = atributos;

      const nuevasCombinaciones = generarCombinaciones(atributos);

      let preciosPrevios = [];

      // ✅ Si existen combinaciones previas (modo editar), intentamos mantener precios
      if (window.combinacionesFinales && window.preciosCombinaciones) {
          nuevasCombinaciones.forEach((comb, index) => {
              const matchIndex = window.combinacionesFinales.findIndex(oldComb =>
                  JSON.stringify(oldComb) === JSON.stringify(comb)
              );

              if (matchIndex !== -1) {
                  // ✅ Mantener precios existentes
                  preciosPrevios[index] = window.preciosCombinaciones[matchIndex];
              } else {
                  // ✅ Nueva combinación → precios vacíos
                  preciosPrevios[index] = {
                      pcompra_variante: "",
                      pventa_variante: "",
                  };
              }
          });
      }

      // ✅ Guardar nuevas combinaciones globales
      window.combinacionesFinales = nuevasCombinaciones;

      // ✅ Guardar precios globales
      window.preciosCombinaciones = preciosPrevios;

      // ✅ Renderizar tabla con precios
      renderCombinacionesTable(nuevasCombinaciones, preciosPrevios);

      const modal = bootstrap.Modal.getInstance(modalEl);
      modal.hide();
  });

  // Al cerrar modal: limpiar DOM y resetear contador
  modalEl.addEventListener("hidden.bs.modal", function () {
    document.getElementById("atributos-container").innerHTML = "";
    atributoCount = 0;
  });
});

// ✅ Restringir inputs con clase .solo-numeros a números y punto decimal
document.addEventListener("input", function (e) {
    if (e.target.classList.contains("solo-numeros")) {

        // Permitir solo números y punto
        e.target.value = e.target.value.replace(/[^0-9.]/g, "");

        // Evitar más de un punto decimal
        const partes = e.target.value.split(".");
        if (partes.length > 2) {
            e.target.value = partes[0] + "." + partes[1];
        }
    }
});

class articulo {
  constructor() {}

  msg_error(msg,errorform){
    console.log(msg)
    errorform.innerHTML = "";
    errorform.style.display = "block";
    msg.forEach(element =>{
        errorform.innerHTML += `<li>${element}</li>`;
    });

    setTimeout(() => {
        errorform.style.display = "none";
    }, 3000);
  }
  success_message(msg){
    Swal.fire(
      'Exito',
      msg,
      'success'
    )
  }
  img_preview(inputfile,container,imgpreview){
    inputfile.addEventListener("change", (e) => {
      e.preventDefault();
      const file = inputfile.files[0];
      if (file) {
        const reader = new FileReader();
        reader.addEventListener("load", (e) => {
          e.preventDefault();
          console.log(reader.result);
          imgpreview.setAttribute("src", reader.result);
        });
        reader.readAsDataURL(file);
      } else {
        imgpreview.setAttribute("src", "//placehold.it/100?text=IMAGEN");
      }
    });
  }
  refresh_table_product(){
    var TableRefreshProduct = $('#producto_table').dataTable(); 
    TableRefreshProduct.fnDraw(false);
  }
  startLoadIcon(button){
      button.innerHTML = "";
      button.disabled = true;
      button.innerHTML += `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando datos...`;
  }

  endLoadIcon(endbutton,message_end){
      endbutton.disabled = false;
      endbutton.innerHTML = message_end;
        
  }

}

class acciones_articulo extends articulo {
  articulo_main = async (url,formData,errorform,imgpreview) => {
    try {
      let seend = await fetch(url, {
        method: "post",
        body: formData,
      });
      let data = await seend.json();
      console.log(data);
        let resp = data.estado;
        let msg = data.mensaje;
        switch (resp) {
          case 1:
            super.success_message(msg);
            setTimeout(() => {
              window.location.href = '/articulo/' + data.product.idarticulo + '/edit';
            }, 500);
          break;
          case 0:
             alert(msg);
            break;
          case 'errorvalidacion':
            super.msg_error(msg,errorform);
            break;
          default:
           break;
        }
    } catch (error) {
      console.log(error);
    }
  };

  get_data_product = async (id,mymodal) => {
    let response = await fetch('/product-list/'+id)
    let json = await response.json()
    //console.log(json) 
    document.querySelector("#idprod").value = json.idarticulo;
    document.querySelector('#upnombre').value = json.nombre;
    document.querySelector('#upidcategoria').value = json.categoria_id;
    document.querySelector('#upcodigo').value = json.codigo;
    document.querySelector('#upstock').value = json.stock;
    document.querySelector('#uppcompra').value = json.pcompra;
    document.querySelector('#uppventa').value = json.pventa;
    document.querySelector('#updescripcion').value = json.descripcion;
    let img_actual = json.imagen;
    let imgactual = document.querySelector('#imgactual');
    imgactual.innerHTML="";
    imgactual.innerHTML=`
    <div class="row">
    <div class="col ">
        <div class="form-group">
            <label for="nombre">Cambiar imagen</label>
            <input type="file" name="upimagen" id="upimagen" class="form-control" accept="image/*">
        </div>
    </div>
    <div class="col text-center ">
        <div class="form-group" style="margin-top:8px;">
          <figure class="figure">
            <div class="text-center" id="updimagepreview">
                <img src="//placehold.it/100?text=IMAGEN" class="" id="updpreview" width="90px" height="80px"/>
            </div>
            <figcaption class="figure-caption">Imagen nueva</figcaption>
          </figure>
        </div>
    </div>
    <div class="col text-center ">
        <div class="form-group" style="margin-top:8px;">
          <figure class="figure">
            <img src="../imagenes/articulos/${img_actual}" height="80px" width="90px" class="">
            <figcaption class="figure-caption">Imagen actual</figcaption>
          </figure>
        </div>
    </div>
    </div>
    `;
    document.querySelector('#uparticulo_des').value = json.descuento;
    mymodal.show();

    /**FUNCTION THAT GET THE NAME OF INPUT UPIMAGEN*/
    const updimg = document.querySelector("#upimagen");
    const updpreviewcontainer = document.querySelector("#updimagepreview");
    const updpreviewimage = document.querySelector("#updpreview");
    super.img_preview(updimg,updpreviewcontainer,updpreviewimage);
  }

  update_product = async (url, formData, errorform) => {
    try {
      let seend = await fetch(url, {
        method: "post",
        body: formData,
      });
      let data = await seend.json();
      let resp = data.estado;
      let msg = data.mensaje;
      switch (resp) {
        case 1:
          super.success_message(msg);
          super.refresh_table_product();
          break;
        case 0:
          alert(msg);
          break;
        case "errorvalidacion":
          super.msg_error(msg, errorform);
          break;
      }
    } catch (error) {
      console.log(error);
    }
  }

  down_product = async (url,form) => {
    try {
      let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      let response = await fetch(url, {
      headers: {
          'X-CSRF-TOKEN': token
      },
      method: 'POST',
      body: form
      });
      let data = await response.json();
      console.log(data);
      switch (data.estado) {
        case 1:
          Swal.fire(
            'Exito!',
            data.mensaje,
            'success'
          )
          super.refresh_table_product();
          break;
        case 0:
          alert(data.mensaje);
          break;
        default:
          break;
      }
    } catch (error) {
      console.log(error);
    }
  }

  send_data_excel = async (url,form,section_table_products) => {
    try {
      let seend = await fetch(url, {
        method: "post",
        body: new FormData(form),
      });
      let data = await seend.json();
      console.log(data);
      let resp = data.estado;
      let msg = data.mensaje;
      switch (resp) {
        case 1:
          section_table_products.style.display = "block";
          const bodyshow = document.querySelector("#body_show_data_articulos");
          const template = document.querySelector("#template_data_articulos").content;
          let fragment = document.createDocumentFragment();
          let i = 0;
          let data_productos = data.datos;
          data_productos.forEach(element => {
              i++;
              template.querySelector(".auto").textContent = i;
              template.querySelector(".input_categoria").value = data.categoria;
              template.querySelector(".input_codigo").value = element.codigo;
              template.querySelector(".input_name").value = element.name;
              template.querySelector(".input_stock").value = element.stock;
              template.querySelector(".input_compra").value = element.pcompra;
              template.querySelector(".input_venta").value = element.pventa;
              template.querySelector(".input_description").value = element.descripcion;
              template.querySelector(".input_descuento").value = element.descuento;
              const clone = template.cloneNode(true);
              fragment.appendChild(clone);
          });
          bodyshow.appendChild(fragment);
          /*DELETE ROW PRODUCTS */
          const btndeleterow = document.querySelectorAll(".btn-delete-producto");
          //console.log(btndeleterow);
          btndeleterow.forEach(element => {
            element.addEventListener("click", (e) => {
              element.parentElement.parentElement.remove();
              //console.log(element.parentElement.parentElement);
            });
          });
        break;
        case "errorvalidacion":
          let error = document.querySelector(".error-form-upload_file");
          super.msg_error(msg,error);
          break;
        case 0:
          alert(msg);
          break;
        default:
          break;
      }
    } catch (error) {
      console.log(error);
    }
  }

  upload_products_category = async (url,form,section_table_products) => {
    try {
      let seend = await fetch(url, {
        method: "post",
        body: new FormData(form),
      });
      let data = await seend.json();
      console.log(data);
      let resp = data.estado;
      let msgart = data.mensaje;
      switch (resp) {
        case 1:
          const clearbody = document.querySelector("#body_show_data_articulos");
          clearbody.innerHTML = "";
          const formaddexcel = document.querySelector("#form-add-excel");
          formaddexcel.reset();
          section_table_products.style.display = "none";
          super.refresh_table_product();
          super.success_message(msgart);

          break;
        case "error_codigo":
          let code_existent = data.existente;
          code_existent.forEach(element => {
            console.log(element.num_codigo);
            const inputcode = document.querySelectorAll(".input_codigo");
            inputcode.forEach(input => {
              console.log(input.value);
              if (element.num_codigo === input.value) {
                input.style.borderColor = "red";
              }
            });
          });
          break;
        case 0:
          alert(msgart);
          break;
        default:
          break;
      }
    } catch (error) {
      console.log(error);
    }
  }

  get_data = async (path,id,idproducto) => {
    try {
      let response = await fetch(`${path}${id}/${idproducto}`);
      let resp = await response.json()
      console.log(resp) 
      return resp;
    } catch (error) {
      console.log(error);
    }
  }

  save_data = async (url,form) => {
    try {
      let seend = await fetch(url, {
        method: "post",
        headers: {
            'X-CSRF-TOKEN': token,
        },
        body: form,
      });
      let data = await seend.json();
      console.log(data);
      return data;
    } catch (error) {
        console.log(error);     
    }
  }
}

/**PREVIEW IMAGE FORM SAVE ARTICLLE */
const imgupload = document.querySelector("#file");
const previewcontainer = document.querySelector("#imagepreview");
const previewimage = document.querySelector("#preview");
const imgpreviewp = new articulo();
imgpreviewp.img_preview(imgupload,previewcontainer,previewimage);

function getCombinacionesFromTable() {
  const combinaciones = [];
  document.querySelectorAll("#combinaciones-table tbody tr").forEach(row => {
    const cell = row.querySelector("td");
    if (cell) {
      combinaciones.push(cell.textContent.trim());
    }
  });
  return combinaciones;
}

//NUEVA PARTE PARA GUARDAR DATOS CON O SIN VARIANTES
const errorform = document.querySelector(".error-form");
const formsavearticulo = document.querySelector("#save_producto");

function saveProductoConVariantes() {
    let selectTipo = document.getElementById("select-tipo-producto");

    // ✅ Sincronizar inputs ocultos de galería ANTES de armar el FormData
    fnSyncGaleriaInputs();

    const formData = new FormData(formsavearticulo);

    if(selectTipo.value == 2){
      // ✅ Adjuntar atributos confirmados
      formData.append(
          "atributos",
          JSON.stringify(window.atributosConfirmados || [])
      );

      // ✅ Adjuntar combinaciones con precios
      const combos = window.combinacionesFinales || [];
      const precios = [];

      document.querySelectorAll("#combinaciones-table tbody tr").forEach((row, index) => {
          const pcompra = row.querySelector(".pcompra-variante").value || 0;
          const pventa = row.querySelector(".pventa-variante").value || 0;
          const sku = (row.querySelector(".sku-variante")?.value || "").trim();
          const idcombinacion = row.dataset.idcombinacion || null;

          // Imagen propia de la combinación (la tabla está fuera del <form>, se adjunta a mano)
          const fileInput = row.querySelector(".imagen-variante");
          if (fileInput && fileInput.files.length > 0) {
              formData.append(`imagen_variante_${index}`, fileInput.files[0]);
          }

          precios.push({
              idcombinacion: idcombinacion,
              combinacion: combos[index],
              sku: sku,
              pcompra: pcompra,
              pventa: pventa
          });
      });

      formData.append("combinaciones", JSON.stringify(precios));
    }

    // ✅ Detectar si es crear o editar
    const esNuevo = Number(productoId.value) === 0;

    const acciones = new acciones_articulo();

    console.log(formData);
    if (!esNuevo) {
        Swal.fire({
            title: "Advertencia",
            html: "Al modificar combinaciones, las <b>sucursales</b> pueden verse afectadas.<br><br>" +
                  "• Las combinaciones eliminadas perderán su stock.<br>" +
                  "• Las nuevas combinaciones no estarán disponibles en sucursales hasta que se agreguen.<br><br>" +
                  "¿Desea continuar con la actualización?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, actualizar",
            cancelButtonText: "Cancelar",
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                console.log("🟡 Actualizando producto...");
                acciones.update_product("/updateproduct", formData, errorform);
            }
        });
    } else {
        console.log("🟢 Creando producto...");
        acciones.articulo_main("/savearticulo", formData, errorform, previewimage);
    }
}

let btnsaveproducto = document.querySelector("#btnsaveproducto");

btnsaveproducto.addEventListener("click", (e) => {
  e.preventDefault();
  saveProductoConVariantes();
});