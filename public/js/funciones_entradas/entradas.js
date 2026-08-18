import {SearchProducto} from "../export_funcion/export_function_entradas.js";
import {Search} from "../export_funcion/export_function search.js";
const mysearchp = document.querySelector("#BuscarEntradaProducto");
const ul_add_lip = document.querySelector("#autocompleteentrada");
const idlip = "prodentradap";
const myurlp ="/nombrearticuloentrada";
const searchproductenttrada = new SearchProducto(myurlp,mysearchp,ul_add_lip,idlip);
searchproductenttrada.InputSearchProduct();
const id_ulp = "#autocompleteentrada";
searchproductenttrada.InputKeydownEntradas(id_ulp);
/**AL RECARGAR LA PAGINA*/
window.onload = () => {
   mostrarproductostemp();
};
/**FUNCTION QUE PERMITE IR GUARDANDO LOS PRODUCTOS TEMPORALMENTE AGREGADOS EN LA TABLA ENTRADAS*/
const FormEnTemp = document.querySelector("#temp_datos_entradas");
//FormEnTemp.addEventListener("submit", (e) => {
const btnaddtempprodent = document.querySelector("#btn_addentradas");
btnaddtempprodent.addEventListener("click", (e) => {
    e.preventDefault();
    const idbuttontemp = document.querySelector("#btn_addentradas");
    idbuttontemp.innerHTML = "";
    idbuttontemp.disabled = true;
    idbuttontemp.innerHTML += `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando datos...`;
    //alert("se ejecuto");
    // const idbtnentrada =
    const datosforment = new FormData(
        document.getElementById("temp_datos_entradas")
    );
    var url = "/temp_datos";
    fetch(url, {
        method: "post",
        body: datosforment
    })
    .then(data => data.json())
    .then(data => {
        //console.log("Success:", data);
        if (data.estado == 1) {
        /**se manda a llamar la function que pinta los productos obtenidos de la tabla temporal*/
        mysearchp.focus();
        FormEnTemp.reset();
        document.querySelector("#tipoProductoId").value = "";
        const divContentVariantes = document.querySelector("#div-content-variantes");
        const divContentVariaciones = document.querySelector("#div-content-variaciones");
        divContentVariantes.style.display = "none";
        divContentVariaciones.style.display = "none";
        idbuttontemp.disabled = false;
        idbuttontemp.innerHTML = `<i class="fas fa-check-circle text-success"></i> Agregar`;
        pintar_productos_tabla(data);
        } else if (data.estado == 0) {
            //console.log(data.mensaje);
            mensaje_error_save_entrada_temp(data);
            idbuttontemp.disabled = false;
            idbuttontemp.innerHTML = `<i class="fas fa-check-circle text-success"></i> Agregar`;
            //toastr.error(data.mensaje);
        } else if (data.estado == "errorvalidacion") {
            mensaje_error_save_entrada_temp(data);
            idbuttontemp.disabled = false;
            idbuttontemp.innerHTML = `<i class="fas fa-check-circle text-success"></i> Agregar`;
        }
    })
    .catch(function(error) {
        console.error("Error:", error);
        idbuttontemp.disabled = false;
        idbuttontemp.innerHTML = `<i class="fas fa-check-circle text-success"></i> Agregar`;
    });
});
/**MENSAJES DE ERRROR */
var mensaje_error_save_entrada_temp = (data) => {
    var errores = document.querySelector(".print-save-error-msg");
    errores.innerHTML = "";
    errores.style.display = "block";
    const mensaje_validacion_entradas = data.mensaje;
    mensaje_validacion_entradas.forEach(element => {
        // console.log(element);
        errores.innerHTML += "<li>" + element + "</li>";
    });
    window.setTimeout(function() {
        $(".print-save-error-msg").slideUp(function() {});
    }, 3000);
};

/*ELIMINAR EL PRODUCTO DE LA TABLA TEMPORAL ENTRADAS*/
const delete_temp_prod_entrada = (id,idArticulo,tipo_producto_id,producto_variacion_variante_id) => {
  //  alert(id);
  //Token de seguridad
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr("content");
  var id_user = document.getElementById("id_user").value;
  var url = "/deleteproduct";
  var datos = new FormData();
  datos.append('id_user',id_user);
  datos.append('idprod', id);
  datos.append('idArticulo', idArticulo);
  datos.append('tipo_producto_id', tipo_producto_id);
  datos.append('producto_variacion_variante_id', producto_variacion_variante_id);
  fetch(url, {
    headers: {
       'X-CSRF-TOKEN': CSRF_TOKEN// <--- aquí el token de seguridad.
    },
    method:'post',
    body:datos
  })
  .then(data => data.json())
  .then(data => {
    //console.log('Success:', data);
    if (data.estado == 1 ) {
        pintar_productos_tabla(data);
    }
  })
  .catch(function(error){
    console.error('Error:', error)
  });
}

/**FUNCTION QUE PERMITE CONSULTAR LOS PRODUCTOS AGREGADOS Y QUE NO SE HAN REGISTRADO EN LA TABLA ENTRADAS*/
var mostrarproductostemp = () => {
  //Token de seguridad
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr("content");
  var id_user = document.getElementById("id_user").value;
  var url = "/showproductostemp";
  var datos = new FormData();
  datos.append('id_user',id_user);
  fetch(url,{
    headers: {
        'X-CSRF-TOKEN': CSRF_TOKEN// <--- aquí el token de seguridad.
    },
    method:'post',
    body:datos
  })
  .then(data => data.json())
  .then(data => {
    //console.log('Success:', data);
    if (data.estado == 1) {
        pintar_productos_tabla(data);
    }
  })
  .catch(function(error){
    console.error('Error:', error)
  });
}
/**FUNCTION QUE GUARDA LOS DATOS DE LA ENTRADA DE LOS PRODUCTOS*/
const formsaveproducto = document.querySelector("#save_producto_entradas");
formsaveproducto.addEventListener("submit", (e) => {
    e.preventDefault();
    const idbuttonsave = document.querySelector("#form_save_entradas");
    idbuttonsave.innerHTML = "";
    idbuttonsave.disabled = true;
    idbuttonsave.innerHTML += `<span class="spinner-border spinner-border-sm" 
    role="status" aria-hidden="true"></span> Enviando datos...`;
    //alert("se genero el formulario con exito");
    var saveprod = new FormData(document.getElementById('save_producto_entradas'));
    var url = "/saveproductoentrada";
    fetch(url,{
        method:'post',
        body:saveprod
    })
    .then(data => data.json())
    .then(data => {
        //console.log('Success:', data);
        //formsaveproducto.reset();
        if (data.estado == 1) {
            toastr.success(data.mensaje);
            formsaveproducto.reset(); 
            idbuttonsave.disabled = false;
            idbuttonsave.innerHTML = `<i class="fas fa-check-circle text-success"></i> Aceptar`;
            const tablesindatos = document.querySelector("#tabla_tmp_productos");
            document.querySelector("#folio").value = data.newfolio;
            tablesindatos.innerHTML = "";
        }else if (data.estado == 0) {
            mensaje_error_save_entrada_temp(data);
            idbuttonsave.disabled = false;
            idbuttonsave.innerHTML = `<i class="fas fa-check-circle text-success"></i> Aceptar`;
        } else if (data.estado == "errorvalidacion") {
            mensaje_error_save_entrada_temp(data);
            idbuttonsave.disabled = false;
            idbuttonsave.innerHTML = `<i class="fas fa-check-circle text-success"></i> Aceptar`;
        }
    })
    .catch(function(error){
        console.error('Error:', error)
        idbuttonsave.disabled = false;
        idbuttonsave.innerHTML = "Aceptar";
    });
    //var inputValue = saveprod.get("idarticulo[]");
    // for (var [key, value] of saveprod.entries()) { 
    //     console.log(key, value);
    // }
});

/*FUNCTION QUE PINTA LOS DATOS OBTENIDOS DE LA TABLA TEMPORAL DE LOS PRODUCTOS*/
var pintar_productos_tabla = (data) => {
    var productos_temp = data.productos;
    //console.log(productos_temp);
    var pintartabla = document.querySelector("#tabla_tmp_productos");
    pintartabla.innerHTML = "";
    var i = 0;
    for (let item of productos_temp) {
        console.log(item);
        console.log(formatNumberPais(Number(item.cantidad)));
        let tipoName = "";
        let variante = "";
        let variacion = "";
        let variacionId = 0;
        if(item.tipo_producto_id == 1){
            tipoName = "P. simple";
            //variante = ;
        }
        if(item.tipo_producto_id == 2){
            tipoName = "P. personalizado";
            variante = item.dataVariante.name_variante;
            variacion = item.dataVariante.name_color;
            variacionId = item.dataVariante.id;
        }
        i++;
        pintartabla.innerHTML += `
        <tr>
        <td><input type="text" class="size_input" hidden="true" name="idarticulo[]" value="${item.idarticulo}">${i}</td>
        <td><input type="hidden" name="tipoProductoId[]" value="${item.tipo_producto_id}"><h5><span class="badge bg-secondary">${tipoName}</span></h5></td>
        <td>
        <div style="width: 250px;">${item.nombre}</div>    
        </td>
        <td><h5><span class="badge bg-primary">${variante}</span></h5></td>
        <td><input type="hidden" name="variacionId[]" value="${variacionId}"><h5><span class="badge bg-info">${variacion}</span></h5></td>
        <td style="width: 5%;"><input type="number" name="cantidad[]" class="size_input" step="any" value="${item.cantidad}" readonly></td>
        <td style="width: 5%;"><input type="number" name="pcompra[]" class="size_input" step="any" size="4" value="${item.pcompra}" readonly></td>
        <td style="width: 5%;"><input type="number" name="pventa[]" class="size_input" step="any" size="4" value="${item.pventa}" readonly></td>
        <td><input type="text" class="size_input" name="subtotalprod[]" value="${item.subtotal_format}" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm delete_btn_entra" name="${item.idtemp},${item.idarticulo},${item.tipo_producto_id},${item.producto_variacion_variante_id}"><i class="fas fa-trash-alt"></i></button></td>
        </tr>
        `;
    }
    //onclick="delete(${item.idtemp});"
    /**SCROLL QUE PERMITE PONER EL SCROLL A LA TABLA SI PASA LOS 200PX */
    var divscroll = document.querySelector(".tableFixHead");
    divscroll.style.height="290px";
    var totalg = data.total;
    /**SE ENVIA EL TOTAL DE LA COMPRA QUE SE REALIZO AL PROVEEDOR A LOS INPUTS CORESPONDIENTES*/
    var inputtolal = document.querySelector("#total_general");
    inputtolal.value=totalg.total;
    //inputtolal.style.fontSize="25px";
    
    var total_input = document.querySelector("#total_input");
    total_input.value=totalg.total;
    // total_input.style.fontSize="25px";
    // input.style.fontSize = `${fontSize}px` 
    // document.getElementById("total_general").value=
    // console.log(totalg);
    /**FUNCTION THAT DELETE PRODUCT OF LIST */
    const btnent = document.querySelectorAll(".delete_btn_entra");
    btnent.forEach(btn=>{
        btn.addEventListener('click', (e) =>{
            e.preventDefault();
            const id = btn.getAttribute('name');
            let mySplit = id.split(",");
            delete_temp_prod_entrada(mySplit[0],mySplit[1],mySplit[2],mySplit[3]);
        })
    })
}

/**************************************************************************************/
/**SEARCH PROVEEDOR*/
new SlimSelect({
    select: '#mySelectProveedor',
        placeholder: 'Seleccione un proveedor',
    allowDeselect: true
});
// Listen for change
document.getElementById('mySelectProveedor').addEventListener('change', function () {
    const selectedValue = this.value;
    console.log('Selected value:', selectedValue);
    // You can now use `selectedValue` in your logic
});
/*const mysearch = document.querySelector("#myInput");
const ul_add_li = document.querySelector("#autocompleteli");

const idli = "proveedor";
const myurl = "/showproveedores";
const searchproveedor = new Search(myurl,mysearch,ul_add_li,idli);
searchproveedor.Inputsearch();
const id_ul = "#autocompleteli";
searchproveedor.InputKeydown(id_ul);*/

// Quick Supplier Creation Functionality
$(document).ready(function() {
    // Quick Supplier Creation
    $('#saveQuickSupplier').on('click', function() {
        var supplierName = $('#quickSupplierForm input[name="nombre"]').val().trim();
        var supplierAddress = $('#quickSupplierForm input[name="direccion"]').val().trim();
        var supplierPhone = $('#quickSupplierForm input[name="telefono"]').val().trim();
        var supplierEmail = $('#quickSupplierForm input[name="email"]').val().trim();
        
        if (!supplierName || !supplierAddress || !supplierPhone || !supplierEmail) {
            Swal.fire('Error', 'Todos los campos son requeridos', 'error');
            return;
        }
        
        // Show loading
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: '/quick-create-supplier',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                nombre: supplierName,
                direccion: supplierAddress,
                telefono: supplierPhone,
                email: supplierEmail
            },
            success: function(response) {
                if (response.success) {
                    // Close the quick supplier modal
                    $('#quickSupplierModal').modal('hide');
                    
                    // Show success message
                    Swal.fire('Éxito', response.message, 'success');
                    
                    // Reset form
                    $('#quickSupplierForm')[0].reset();
                    $('#quick-supplier-errors').hide();
                    
                    // Automatically select the newly created supplier
                    const supplierSelect = document.querySelector("#mySelectProveedor");
                    
                    if (supplierSelect) {
                        // Create new option
                        var newOption = new Option(response.supplier.nombre, response.supplier.idproveedor, true, true);
                        supplierSelect.add(newOption);
                        
                        // Trigger change event to update any listeners
                        $(supplierSelect).trigger('change');
                    }
                    
                } else {
                    Swal.fire('Error', response.message || 'Error al crear el proveedor', 'error');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al crear el proveedor';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Handle validation errors
                    var errors = xhr.responseJSON.errors;
                    var errorList = '';
                    for (var field in errors) {
                        errors[field].forEach(function(error) {
                            errorList += '<li>' + error + '</li>';
                        });
                    }
                    $('#quick-supplier-error-list').html(errorList);
                    $('#quick-supplier-errors').show();
                    errorMessage = 'Por favor corrige los errores en el formulario';
                }
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                // Re-enable button
                $('#saveQuickSupplier').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Proveedor');
            }
        });
    });
    
    // Reset modal when closed
    $('#quickSupplierModal').on('hidden.bs.modal', function() {
        $('#quickSupplierForm')[0].reset();
        $('#quick-supplier-errors').hide();
    });
});
