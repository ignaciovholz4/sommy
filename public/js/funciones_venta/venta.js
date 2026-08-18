import {SeachCustomer} from "../export_funcion/export_function_customers.js";
import {Search} from "../export_funcion/export_function search.js";
const mysearch = document.querySelector("#BuscarVentaProducto");
const ul_add_li = document.querySelector("#autocompleteventa");
const inputventa = document.querySelector("#pcantidad");
const div_error_modal = document.querySelector(".show_alert_modal");
let div_show_email = document.querySelector("#show_input_email");
//const btnprintpdf = document.querySelector("#btnPrintPdf");
const ventaId = document.querySelector("#idventa");//.value = data.sale_now.sale.idventa;        
const idli = "prodventa";
const myurl ="/findproducto";

const searchprodventa = new Search(myurl,mysearch,ul_add_li,idli);
searchprodventa.Inputsearch();
const id_ul = "#autocompleteventa";
searchprodventa.InputKeydown(id_ul);

/**INPUT SEARCH DATA FROM CUSTOMERS*/
const inputSearchCustomer = document.querySelector("#customers-searchs");
const searchcustomer = new SeachCustomer(inputSearchCustomer);
searchcustomer.InputSearchCustomer();

/*AL REARGAR LA PAGINA SE EJECUTA LA CONSULTA QUE MUESTRA LA TABLA SI TIENE DATOS TEMPORALES*/
window.onload = ()  =>{
    mostrarventaproductostemp();
    loadSalesPriceLists();
}

/**
 * Load sales price lists into the select dropdown
 */
const loadSalesPriceLists = () => {
    const priceListSelect = document.getElementById('sales_price_list_id');
    if (!priceListSelect) return;
    
    fetch('/ventas/price-lists')
        .then(response => response.json())
        .then(data => {
            // Clear existing options except the first one
            while (priceListSelect.children.length > 1) {
                priceListSelect.removeChild(priceListSelect.lastChild);
            }
            
            // Add new options
            data.forEach(list => {
                const option = document.createElement('option');
                option.value = list.id;
                option.textContent = `${list.name} (${list.type})`;
                priceListSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading price lists:', error);
        });
}

/**
 * Handle price list change and apply to cart
 */
const handlePriceListChange = () => {
    const priceListSelect = document.getElementById('sales_price_list_id');
    const idarticuloSelect = document.getElementById('idarticulo');
    const selectedPriceListId = priceListSelect.value;
    const productId = idarticuloSelect.value;

    const priceListInfo = document.getElementById('price-list-info');
    
    if (!selectedPriceListId) {
        // If no price list selected, hide info and refresh cart to show original prices
        if (priceListInfo) priceListInfo.style.display = 'none';
        mostrarventaproductostemp();
        return;
    }
    
    // Apply price list to cart
    const idUser = document.getElementById('id_user').value;
    const formData = new FormData();
    formData.append('price_list_id', selectedPriceListId);
    formData.append('id_user', idUser);
    formData.append('product_id', productId);

    // ✅ Only append variant if dropdown exists
    const selectElementVariant = document.getElementById('selectElementVariant');
    const selectElementVariantTipo = document.getElementById('selectElementVariantTipo');
    if (selectElementVariant && selectElementVariant.value) {
        formData.append('variant_type_id', selectElementVariant.value);
        formData.append('variant_id', selectElementVariantTipo.value);
    }
    fetch('/ventas/apply-price-list', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === 1) {
            // Show price list information
            const selectedOption = priceListSelect.options[priceListSelect.selectedIndex];
            const priceListName = selectedOption.textContent;
            
            const appliedPriceListNameElement = document.getElementById('applied-price-list-name');
            if (appliedPriceListNameElement) {
                appliedPriceListNameElement.textContent = priceListName;
            }
            console.log(data);
            
            // Calculate total savings
            // let totalSavings = 0;
            // data.item.forEach(item => {
            //     if (item.original_price && item.effective_price) {
            //         const itemSavings = (item.original_price - item.effective_price) * item.cantidad;
            //         totalSavings += itemSavings;
            //     }
            // });
            //
            // const totalSavingsElement = document.getElementById('total-savings');
            // if (totalSavingsElement) {
            //     totalSavingsElement.textContent = `$${totalSavings.toFixed(2)}`;
            // }
            // if (priceListInfo) priceListInfo.style.display = 'block';
            //
            // Refresh the cart display with updated prices
            mostrarventaproductostemp();
            
            // Update the total display

            const priceInput = document.getElementById('pvprecio_venta');
            const totalInput = document.getElementById('inputventatotal');
            const ventaTotalInput = document.getElementById('venttotal_venta');

            if (totalInput) totalInput.value = data.total.toFixed(2);
            if (ventaTotalInput) ventaTotalInput.value = data.total.toFixed(2);
            if (priceInput) priceInput.value = data.total.toFixed(2);
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Lista de precios aplicada',
                text: data.mensaje,
                timer: 1000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: Array.isArray(data.mensaje) ? data.mensaje.join(', ') : data.mensaje
            });
        }
    })
    .catch(error => {
        console.error('Error applying price list:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al aplicar la lista de precios'
        });
    });
}

// Add event listener for price list change
document.addEventListener('DOMContentLoaded', function() {
    const priceListSelect = document.getElementById('sales_price_list_id');
    if (priceListSelect) {
        priceListSelect.addEventListener('change', handlePriceListChange);
    }
});

/**FUNCTIONN QUE PERMITE CONSULTARLOS PRODUCTOS AGREGADOS TEMPORALMENTE Y NO SE HAN VENDIDO*/
const mostrarventaproductostemp = () => {
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr("content");
  var id_user = document.getElementById("id_user").value;
  var url = "/showproductosventatemp";
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
        console.log('Success:', data);
        if (data.estado == 1) {
            pintar_tabla_venta(data);
        }else if (data.estado == 0) {
            mensaje_error_save_venta_temp(data);
        }
    })
    .catch(function(error){
        console.error('Error:', error)
    });

}

/*FUNCTION QUE PERMITE GUARDAR LOS PRDUCTOS EN LA TABLA TEMPORAL*/
const FormSaveProducto = document.querySelector("#save_producto_venta");
const btnaddtempprod = document.querySelector("#btn_add_prod_tem_vent");
btnaddtempprod.addEventListener("click", (e) => { 
    e.preventDefault();
        let prodcantidad = document.querySelector("#pcantidad");
        let prodstock = document.querySelector("#pvstock");
        let numCantidad = Number(prodcantidad.value);
        let numStock = Number(prodstock.value);
        /*if (numCantidad > numStock) {
            Swal.fire(
            'Error!',
            'La cantidad es mayor al stock!',
            'error'
            )
            return false;
        }*/
        const btnsavetempventprod = document.querySelector("#btn_add_prod_tem_vent");
        btnsavetempventprod.innerHTML = "";
        btnsavetempventprod.disabled = true;
        btnsavetempventprod.innerHTML += `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando datos...`;
        const datosprodvent = new FormData(document.getElementById("save_producto_venta"));
        console.log(datosprodvent.entries())
        // for (let [key, value] of datosprodvent.entries()) {
        //     console.log(`${key}: ${value}`);
        // }

        var url = "/saveprodtempvent";
        fetch(url,{
            method: "post",
            body:datosprodvent
        })
        .then(data => data.json())
        .then(data => {
            //console.log("Success:", data);
            if (data.estado == 1) {
                mysearch.focus();
                pintar_tabla_venta(data);
                FormSaveProducto.reset();
                const divContentVariantes = document.querySelector("#div-content-variantes");
                const divContentVariaciones = document.querySelector("#div-content-variaciones");
                const divContentDescuento =  document.querySelector("#div-content-descuento");
                divContentVariantes.style.display = "none";
                divContentVariaciones.style.display = "none";
                divContentDescuento.style.display = "block";
                btnsavetempventprod.disabled = false;
                btnsavetempventprod.innerHTML = `<i class="fas fa-check-circle text-success"></i> Agregar`;
            }else if (data.estado == 0) {
            mensaje_error_save_venta_temp(data);
                btnsavetempventprod.disabled = false;
                btnsavetempventprod.innerHTML = '<i class="fas fa-check-circle text-success"></i> Agregar';
            }else if (data.estado == "errorvalidacion") {
            mensaje_error_save_venta_temp(data);
                btnsavetempventprod.disabled = false;
                btnsavetempventprod.innerHTML = '<i class="fas fa-check-circle text-success"></i> Agregar';
            }
        })
        .catch(function(error) {
            console.error("Error:", error);

        });

})
/**MENSAJES DE ERRROR */
var mensaje_error_save_venta_temp = (data) => {
    var errores = document.querySelector(".print-save-error-msg");
    if (errores) {
        errores.innerHTML = "";
        errores.style.display = "block";
    }
    const mensaje_validacion_ventas= data.mensaje;
    if (errores && mensaje_validacion_ventas) {
        mensaje_validacion_ventas.forEach(element => {
            // console.log(element);
            errores.innerHTML += "<li>" + element + "</li>";
        });
    }
    window.setTimeout(function() {
      const diverror =  document.querySelector(".print-save-error-msg");
      if (diverror) {
          diverror.style.display="none";
      }
    }, 3000);
};

/*FUNCTION QUE PINTA LOS DATOS OBTENIDOS DE LA TABLA TEMPORAL DE LAS VENTAS DE LOS PRODUCTOS*/
const pintar_tabla_venta = (data) => {
    const productos_venta_temp = data.productos;
    //console.log(productos_venta_temp)
    const pintar_tabla_producto_temp = document.querySelector("#tabla_venta_productos_temp");
    pintar_tabla_producto_temp.innerHTML = "";
    let count = 0; 
    for (let item of productos_venta_temp ) {
        console.log(item);
        console.log(item.cantidad);


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
        
        // Handle price display - show original and effective prices if available
        let originalPrice = item.original_price || item.precio;
        let effectivePrice = item.effective_price || item.precio;
        let priceDisplay = '';
        
        if (item.original_price && item.effective_price && item.original_price !== item.effective_price) {
            // Show both prices when price list is applied
            priceDisplay = `
                <td><input type="number" class="size_input" readonly name="precio_original[]" value="${originalPrice}">
                    <small class="text-muted d-block">$${parseFloat(originalPrice).toFixed(2)}</small>
                </td>
                <td><input type="number" class="size_input" readonly name="precio_venta[]" value="${effectivePrice}">
                    <small class="text-success d-block">$${parseFloat(effectivePrice).toFixed(2)}</small>
                </td>
            `;
        } else {
            // Show single price when no price list is applied
            priceDisplay = `
                <td><input type="number" class="size_input" readonly name="precio_original[]" value="${originalPrice}">
                    <small class="text-muted d-block">$${parseFloat(originalPrice).toFixed(2)}</small>
                </td>
                <td><input type="number" class="size_input" readonly name="precio_venta[]" value="${effectivePrice}">
                    <small class="text-muted d-block">$${parseFloat(effectivePrice).toFixed(2)}</small>
                </td>
            `;
        }
        
        count++;
        pintar_tabla_producto_temp.innerHTML +=`
        <tr>
        <td>${count}</td>
        <td><input type="hidden" name="idarticulo[]" value="${item.id_articulo}">${item.nombre}</td>
        <td><input type="hidden" name="tipoProductoId[]" value="${item.tipo_producto_id}"><h5><span class="badge bg-primary">${variante}</span></h5></td>
        <td><input type="hidden" name="variacionId[]" value="${variacionId}"><h5><span class="badge bg-info">${variacion}</span></h5></td>
        <td><input type="number" class="size_input" readonly name="cantidad[]" value="${item.cantidad}"></td>
        ${priceDisplay}
        <td><input type="number" class="size_input" readonly name="descuento[]" value="${item.descuento}"></td>
        <td><input type="text" class="size_input" readonly name="subtotal[]" value="${item.total_format}"></td>
        <td><button type="button" class="btn btn-danger btn-sm delete_btn_prod_venta" name="${item.idart},${item.id_articulo},${item.tipo_producto_id},${item.producto_variacion_variante_id} "><i class="fas fa-trash-alt"></i></button></td>
        </tr>
        `; 
    }
    /**SCROLL QUE PERMITE PONER EL SCROLL A LA TABLA SI PASA LOS 200PX */
    var divscroll = document.querySelector(".tableFixHead");
    divscroll.style.height="268px";
    /**SE ENVIA EL TOTAL DE LA VENTA A LOS INPUTS CORESPONDIENTES*/
    const inputventotal =  document.querySelector("#inputventatotal");
    inputventotal.value = data.totales.total;
    //inputventotal.style.fontSize="15px";

    const inputtotalv = document.querySelector("#venttotal_venta");
    inputtotalv.value = data.totales.total;
    //inputtotalv.style.fontSize="15px";
    /**FUNCTION THAT DELETE PRODUCT SALE*/
    const btnventadelete =  document.querySelectorAll(".delete_btn_prod_venta");
    btnventadelete.forEach(btn=>{
        btn.addEventListener("click", (e) =>{
            e.preventDefault();
            const id = btn.getAttribute('name');
            let mySplit = id.split(",");
            delete_venta_product_temp(mySplit[0],mySplit[1],mySplit[2],mySplit[3]);
        });
    });

}
/**FUNCTION QUE ME PERMITE ELEMINAR UN PRODUCTO DE LA TABLA DE LOS PRODUCTOS */
const delete_venta_product_temp = (id,idArticulo,tipo_producto_id,producto_variacion_variante_id) => {
    //Token de seguridad
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr("content");
    var id_user = document.getElementById("id_user").value;
    var url = "/deleteventaproducto";
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
            pintar_tabla_venta (data);
        }else if(data.estado == 0){
            mensaje_error_save_venta_temp(data);
        }else if(data.estado == "sinproductos"){
            const sin_producto = document.querySelector("#tabla_venta_productos_temp");
            sin_producto.innerHTML = ""
        }
    })
    .catch(function(error){
        console.error('Error:', error)
    });

}
/**FUNCTION QUE ME PERMITE REALIZAR EL CALCULO DEL CAMBIO DE LA CANTIDAD QUE SE ENTRGO*/
const pcantidadv = document.querySelector("#ventdinero");
pcantidadv.addEventListener("keyup", event =>{
    //console.log("exit");
    //pcantidadv.style.fontSize="23px";
    const  vcambio = document.querySelector("#ventsuelto");
    const  ventventa = document.querySelector("#venttotal_venta");
    const vcantidad = Number(pcantidadv.value);
    const totalreplace = ventventa.value.replace(/\s+/g, '');
    const vtotal = Number.parseFloat(totalreplace);
    const tt = ventventa.value;
    //console.log(tt)
    if ( tt === '0.00') {
        //console.log("no se genero con exito");
        return false;
    }
    //console.log("se formateo "+tt);
    //console.log('cantidad :'+ vcantidad+ 'total : '+vtotal);
    if(vcantidad < vtotal){
        //console.log("la cantidad es menor");
        vcambio.value="";
    }else if(vcantidad >= vtotal){
        const ventacambio = vcantidad - vtotal;
        //vcambio.style.fontSize="23px";
        let mycamb = truncateDecimals(ventacambio,2);
        //vcambio.value=ventacambio;
        ////console.log("EL CAMBIO :"+ ventacambio);
        vcambio.value = mycamb;
        //console.log("EL CAMBIO :"+ mycamb);
    }
});
/**fUNCION QUE PARSEA EL CAMBIO A 2 DECIMALES */
function truncateDecimals (num, digits) 
{
       var numS = num.toString();
       var decPos = numS.indexOf('.');
       var substrLength = decPos == -1 ? numS.length : 1 + decPos + digits;
       var trimmedResult = numS.substr(0, substrLength);
       var finalResult = isNaN(trimmedResult) ? 0 : trimmedResult;

       // adds leading zeros to the right
       if (decPos != -1){
           var s = trimmedResult+"";
           decPos = s.indexOf('.');
           var decLength = s.length - decPos;

               while (decLength <= digits){
                   s = s + "0";
                   decPos = s.indexOf('.');
                   decLength = s.length - decPos;
                   substrLength = decPos == -1 ? s.length : 1 + decPos + digits;
               };
           finalResult = s;
       }
       return finalResult;
};

/**FUNCTION THAT SAVE THE SALE*/
const save_guardar_venta = document.querySelector("#save_venta_total");
save_guardar_venta.addEventListener('submit', (e) =>{
    e.preventDefault();
    const btnsaveprodventa = document.querySelector("#venta_productos_realizada");
    btnsaveprodventa.innerHTML = "";
    btnsaveprodventa.disabled = true;
    btnsaveprodventa.innerHTML += `<span class="spinner-border spinner-border-sm" 
    role="status" aria-hidden="true"></span> Enviando datos...`;
    //alert("se ejecuto");
    const formdatosventa = new FormData(save_guardar_venta);
    //console.log(formdatosventa);
    var url ="/saveformventa";
    fetch(url,{
        method:'post',
        body:formdatosventa
    })
    .then(data => data.json())
    .then(data =>{
        //console.log("Success:", data);
        div_show_email.style.display = "none";
        if(data.estado == 1){
            //toastr.success(data.mensaje);
            const email = document.querySelector("#ventidcliente");
            //let get_text = email_cliente.options[email_cliente.selectedIndex].text;
            const email_cliente = document.querySelector("#nomcliente");
            if (email_cliente) {
                let get_text = email_cliente.value;
                if (get_text != "Publico en general" && email) {
                    let getemail = email.getAttribute("class");
                    const nowemail = document.querySelector("#nowemail");
                    if (nowemail) nowemail.value = getemail;
                }
            }

            const send_cambio_modal = document.querySelector("#ventsuelto");
            const suelt_vent = document.querySelector("#suelt_vent");
            if (send_cambio_modal && suelt_vent) {
                suelt_vent.value = send_cambio_modal.value;
            }

            save_guardar_venta.reset();
            const ventfonio = document.querySelector("#ventfonio");
            if (ventfonio) ventfonio.value = data.folio;
            
            btnsaveprodventa.disabled = false;
            btnsaveprodventa.innerHTML = `<i class="fas fa-check-circle text-success"></i> Aceptar`;
            const tablaventasindatos = document.querySelector("#tabla_venta_productos_temp");
            if (tablaventasindatos) tablaventasindatos.innerHTML = ""
            
            const customerSearch = document.querySelector("#customers-searchs");
            if (customerSearch) customerSearch.value = "";
            
            const showCustomers = document.querySelector("#show-customers");
            if (showCustomers) showCustomers.innerHTML = "";
            print_ticket(data);
            var myModal = new bootstrap.Modal(document.getElementById('printModal'));
            myModal.show();
            
        }else if(data.estado == 0){
            mensaje_error_save_venta_temp(data);            
            btnsaveprodventa.disabled = false;
            btnsaveprodventa.innerHTML = `<i class="fas fa-check-circle text-success"></i> Aceptar`;

        }else if (data.estado == "errorvalidacion") {
            mensaje_error_save_venta_temp(data); 
            btnsaveprodventa.disabled = false;
            btnsaveprodventa.innerHTML = `<i class="fas fa-check-circle text-success"></i> Aceptar`;

        }
    })
    .catch(function(error){
        console.error("Error:", error);
            btnsaveprodventa.disabled = false;
            btnsaveprodventa.innerHTML = `<i class="fas fa-check-circle text-success"></i> Aceptar`;

    });

});
/**FUNCTION THAT PRINT THE TICKET*/
const print_ticket = (data) => {
    //console.log(data);
    /**the add properties the modal messsage cambio and success sale*/
    const messageLabel = document.querySelector("#messsageModalLabelSuccess");
    if (messageLabel) messageLabel.textContent = data.mensaje;
    
    const cambioSale = document.querySelector("#cambio_sale");
    if (cambioSale) cambioSale.textContent = data.suelto;
    
    /**The add properties the ticket for print*/
    let settings = data.settings;
    const telefono = document.querySelector("#telefono");
    if (telefono && settings && settings.phone) telefono.textContent = `Telefono ${settings.phone}`;
    
    const adress = document.querySelector("#adress");
    if (adress && settings && settings.adress) adress.textContent = `${settings.adress}`;
    
    let detail =  data.sale_now ? data.sale_now.detail : [];
    //console.log(detail);
    const bodytabledetails = document.querySelector("#tbody_details");
    if (bodytabledetails) bodytabledetails.innerHTML = "";
    
    const template_details = document.querySelector("#template_details");
    const fragmetdetail = document.createDocumentFragment();
    
    const saleCliente = document.querySelector("#sale_cliente");
    if (saleCliente && data.sale_now && data.sale_now.sale) saleCliente.textContent = `Cliente ${data.sale_now.sale.nombre}`;
    
    const saleDate = document.querySelector("#sale_date");
    if (saleDate && data.sale_now && data.sale_now.sale) saleDate.textContent =  `Fecha ${data.sale_now.sale.fecha_hora}`;        
    
    const saleFolio = document.querySelector("#sale_folio");
    if (saleFolio && data.sale_now && data.sale_now.sale) saleFolio.textContent = `Folio ${data.sale_now.sale.num_folio}`;        
    
    const saleTotal = document.querySelector("#sale_total");
    if (saleTotal && data.sale_now && data.sale_now.sale) saleTotal.textContent = data.sale_now.sale.total_venta;        
    
    const saleCambio = document.querySelector("#sale_cambio");
    if (saleCambio) saleCambio.textContent = data.suelto;        
    
    const cajero = document.querySelector("#cajero");
    if (cajero && data.user && data.user[0]) cajero.textContent = `Cajero ${data.user[0].name}`;        
    
    const saleEfectivo = document.querySelector("#sale_efectivo");
    if (saleEfectivo) saleEfectivo.textContent = data.efectivo;        
    
    const consSendEmail = document.querySelector("#cons_send_email");
    if (consSendEmail && data.sale_now && data.sale_now.sale) consSendEmail.value = data.sale_now.sale.idventa;        

    if (template_details && bodytabledetails && detail) {
        detail.forEach(element => {
            //console.log(element);
            const detailCantidad = template_details.querySelector(".detail_cantidd");
            const detailNombre = template_details.querySelector(".detail_nombre");
            const detailVenta = template_details.querySelector(".detail_venta");
            const detailSubtotal = template_details.querySelector(".detail_subtotal");
            
            if (detailCantidad && element.cantidad) detailCantidad.textContent = element.cantidad;
            if (detailNombre && element.articulo) detailNombre.textContent = element.articulo;
            if (detailVenta && element.precio_venta) detailVenta.textContent = element.precio_venta;
            if (detailSubtotal && element.subtotal) detailSubtotal.textContent = element.subtotal;
            
            const clonedetail = template_details.cloneNode(true);
            fragmetdetail.appendChild(clonedetail);
        });
        bodytabledetails.appendChild(fragmetdetail);
    }
    /**********************NEW*****************************************/
    let dataSale = {
        "idventa":data.sale_now && data.sale_now.sale ? data.sale_now.sale.idventa : "",
        "suelto":data.suelto || "",
        "efectivo":data.efectivo || "",
    }
    if (ventaId) {
        ventaId.value = "";
        ventaId.value = JSON.stringify(dataSale); 
    }

}

/**FUNCTION THAT PRINT THE TICKET FROM SALE*/
const now_print = document.querySelector("#print_now_ticket");
now_print.addEventListener("click", (e) =>{
    e.preventDefault();
    printPdf();
    let myModal = new bootstrap.Modal(document.getElementById('generatePdfModal'));
    myModal.show();
   /*let printContents = document.querySelector("#ticket").innerHTML;
    let iFrame = document.getElementById('print-iframe');
    iFrame.contentDocument.body.innerHTML = printContents;
    iFrame.focus();
    iFrame.contentWindow.print();*/
});


/**FUNCTION QUE PERMITE CANCELAR LA VENTA POR COMPLETO*/
const cancelventaprod = document.querySelector("#cancelventaproducto");
cancelventaprod.addEventListener('click', (e) =>{ 
    cancelventaprod.innerHTML = "";
    cancelventaprod.disabled = true;
    cancelventaprod.innerHTML += `<span class="spinner-border spinner-border-sm" 
    role="status" aria-hidden="true"></span> Enviando datos...`;
     //Token de seguridad
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr("content");
    var id_user_eliminar = document.getElementById("id_user").value;
    var url = "/deleteventa";
    var datoselim = new FormData();
    datoselim.append('id_user',id_user_eliminar);
    fetch(url,{
        headers: {
        'X-CSRF-TOKEN': CSRF_TOKEN// <--- aquí el token de seguridad.
        },
        method:'post',
        body:datoselim
    })
    .then(data=> data.json())
    .then(data =>{
        //console.log('success',data);
        if(data.estado == 1){
            toastr.success(data.mensaje); 
            /**se reinicia el formulario de la venta mediante el id del form*/
            save_guardar_venta.reset();
            /**aqui termina */
            cancelventaprod.disabled = false;
            cancelventaprod.innerHTML = `<i class="far fa-window-close mr-2"></i> Cancelar la venta`;
            const limpiar_tabla = document.querySelector("#tabla_venta_productos_temp");
            if (limpiar_tabla) limpiar_tabla.innerHTML = ""
        }else if (data.estado == 0) {
            mensaje_error_save_venta_temp(data);
            cancelventaprod.disabled = false;
            cancelventaprod.innerHTML = "Aceptar";   
        }
    })
    .catch(function(error){
        console.error('error',error);
        cancelventaprod.disabled = false;
        cancelventaprod.innerHTML = "Aceptar";
    });
   
});
/******************************************************** */
class acciones{

    startLoadIcon(button){
        button.innerHTML = "";
        button.disabled = true;
        button.innerHTML += `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ...`;
    }

    endLoadIcon(endbutton,message_end){
        endbutton.disabled = false;
        endbutton.innerHTML = message_end;
        
    }
}

/**function for email */
let btn_email = document.querySelector("#btn-show-email");
btn_email.addEventListener("click", (e) => {
    e.preventDefault();
    div_show_email.style.display="block";
});

let form_date = document.querySelector("#send-email-now");
let send_email = document.querySelector("#btn-send-ticket-email");
send_email.addEventListener('click', (e) =>{
    e.preventDefault();
    let url = "/contact";

    const send = new acciones();
    send.startLoadIcon(send_email);
    setTimeout(() => {
        send_email_backend(url,form_date);

        let message_end = "Aceptar";
        send.endLoadIcon(send_email,message_end);
    }, 2000);
});

const send_email_backend = async (url,form) =>{
    try {
        let seend = await fetch(url, {
            method: "post",
            body: new FormData(form),
        });

        let data = await seend.json();
        //console.log(data);
        let msg = data.mensaje;
        let status = data.estatus;
        switch (status) {
            case 1:
                const closeModalBtn = document.querySelector("#btn-close-modal");
                if (closeModalBtn) closeModalBtn.click();
                if (form_date) form_date.reset();
                if (div_show_email) div_show_email.style.display = "none";
                toastr.success(data.mensaje); 
            break;
            case "errorvalidacion":
                msg_error(msg,div_error_modal)
            break;
            
            default:
                break;
        }

    } catch (error) {
        console.log(error);
    }
};

const msg_error = (msg,div_error) =>{
    //console.log(msg)
    if (div_error) {
        div_error.innerHTML = "";
        div_error.style.display = "block";
        div_error.style.marginTop += "10px";
        if (msg && Array.isArray(msg)) {
            msg.forEach(element =>{
                div_error.innerHTML += `<li>${element}</li>`;
            });
        }
    }

    setTimeout(() => {
        if (div_error) {
            div_error.style.display = "none";
        }
    }, 3000);
}

/*btnprintpdf.addEventListener('click', () => {
    printPdf();
})*/

const printPdf =  async () => {
    /******************** */
    console.log(JSON.parse(ventaId.value));
    let getDataSale = ventaId.value;
    fetch('/ventas/print/'+getDataSale)
    .then(response => response.json())
    .then(data => {
        console.log(data);
        let pdfContent = atob(data.pdf); // Decode base64
        document.getElementById('pdf-iframe').src = "data:application/pdf;base64," + btoa(pdfContent);
    });
};

// Quick Customer Creation Functionality
$(document).ready(function() {
    // Quick Customer Creation
    $('#saveQuickCustomer').on('click', function() {
        var customerName = $('#quickCustomerForm input[name="nombre"]').val().trim();
        var customerAddress = $('#quickCustomerForm input[name="direccion"]').val().trim();
        var customerPhone = $('#quickCustomerForm input[name="telefono"]').val().trim();
        var customerEmail = $('#quickCustomerForm input[name="email"]').val().trim();
        
        if (!customerName || !customerAddress || !customerPhone || !customerEmail) {
            Swal.fire('Error', 'Todos los campos son requeridos', 'error');
            return;
        }
        
        // Show loading
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: '/quick-create-customer',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                nombre: customerName,
                direccion: customerAddress,
                telefono: customerPhone,
                email: customerEmail
            },
            success: function(response) {
                if (response.success) {
                    // Close the quick customer modal
                    $('#quickCustomerModal').modal('hide');
                    
                    // Show success message
                    Swal.fire('Éxito', response.message, 'success');
                    
                    // Reset form
                    $('#quickCustomerForm')[0].reset();
                    $('#quick-customer-errors').hide();
                    
                    // Automatically select the newly created customer
                    const nomCustomer = document.querySelector("#nomcliente");
                    const idCustomer = document.querySelector("#ventidcliente");
                    
                    if (nomCustomer && idCustomer) {
                        nomCustomer.value = response.customer.nombre;
                        idCustomer.value = response.customer.idcliente;
                        
                        // Add email class to idCustomer for reference
                        const existingClass = idCustomer.getAttribute('class');
                        if (existingClass) {
                            idCustomer.classList.remove(existingClass);
                        }
                        idCustomer.classList.add(response.customer.email);
                    }
                    
                    // Reopen the customer search modal to show the new customer
                    setTimeout(() => {
                        $('#exampleModal').modal('show');
                    }, 500);
                    
                } else {
                    Swal.fire('Error', response.message || 'Error al crear el cliente', 'error');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Error al crear el cliente';
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
                    $('#quick-customer-error-list').html(errorList);
                    $('#quick-customer-errors').show();
                    errorMessage = 'Por favor corrige los errores en el formulario';
                }
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                // Re-enable button
                $('#saveQuickCustomer').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cliente');
            }
        });
    });
    
    // Reset modal when closed
    $('#quickCustomerModal').on('hidden.bs.modal', function() {
        $('#quickCustomerForm')[0].reset();
        $('#quick-customer-errors').hide();
    });
    
    // Handle modal transition from quick customer to customer search
    $('#quickCustomerModal').on('hidden.bs.modal', function() {
        // If we're coming from the customer search modal, reopen it
        if ($('#exampleModal').hasClass('show')) {
            setTimeout(() => {
                $('#exampleModal').modal('show');
            }, 150);
        }
    });
});