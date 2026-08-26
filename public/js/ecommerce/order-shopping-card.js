console.log("Starting")
/******************** UN SOLO FORMULARIO, UN SOLO BOTON ******************************/
const btnRegisterOrder = document.querySelector("#btnRegisterOrder");
const divContentOrderGlobal = document.querySelector("#div-content-order-global");

/**********CODE FORM FOR SECTION****************/
const formEmail = document.querySelector("#form-email");
const formIdentificacion = document.querySelector("#form-identificacion");
const formEntrega = document.querySelector("#form-entrega");
const formEnvio = document.querySelector("#form-envio");
const formPago = document.querySelector("#form-pago");

/**********CAMPOS OPCIONALES DE ENTREGA (no bloquean la validación)****/
const CAMPOS_ENTREGA_OPCIONALES = ["numberInterior", "infoAdicional"];
/************BTN SEEND DATA VIA WHATSAPP*********************************** */
const btnSendDataOrderWhatsapp = document.querySelector("#btnSendDataOrderWhatsapp");
const divSectionWhatsapp = document.querySelector("#div-section-whatsapp");

const showSubtotalPedido = document.querySelector("#showSubtotalPedido");
const showTotalPedido = document.querySelector("#showTotalPedido");
const showEnvioPedido = document.querySelector("#showEnvioPedido");
const showDescuentoPedido = document.querySelector("#showDescuentoPedido");
const rowEnvioPedido = document.querySelector("#row-envio-pedido");
const rowDescuentoPedido = document.querySelector("#row-descuento-pedido");

/********** NAME SHOW ********** */
const nameCustomerCurrent = document.querySelector("#p-name-customer");
/*******Array***************** */
let pedidoArray = [];
let dataOrder = {};
let dataOrderDetail = [];

let listCartProdOrder = fnListCartProduct();
document.addEventListener('DOMContentLoaded', function () {
    if(listCartProdOrder.length === 0) btnRegisterOrder.disabled = true;//disabled button
    fnShowCurrentCartProduct();//function for show list cart product
    fnActualizarResumen();
});

/*********************UPDATE FOR VERSION 2************************ */

const fnShowCurrentCartProduct = () => {
    let totalCartProduct = 0;
    let quantityProduct = 0;
    const divShowProductCart = document.querySelector('.show-product-added-cart');
    divShowProductCart.innerHTML = "";

    if(listCartProdOrder.length === 0){
        btnRegisterOrder.disabled = true;
        fnShowQuantityProduct(0);
        showSubtotalPedido.textContent = fnFormatMoney(0);
        showTotalPedido.textContent = fnFormatMoney(0);
        return false;
    }

    btnRegisterOrder.disabled = false;

    listCartProdOrder.forEach((product,index) => {
        let addRow = "";
        // Mostrar combinación si existe
        if(product.tipoProductoId === 2 && product.rowProdVariant){
            addRow = `
                <p class="text-muted mb-0"><b>Combinación:</b> ${product.rowProdVariant.combinacion}</p>
            `;
        }

        // Mostrar precios
        let priceDisplay = '';
        if (product.has_offer) {
            priceDisplay = `
                <span class="badge bg-success mb-1">
                    <i class="fas fa-tags"></i> Oferta
                </span>
                <div class="price-container">
                    <span class="price original-price text-muted text-decoration-line-through">
                        ${fnFormatMoney(product.original_price)}
                    </span>
                    <span class="price effective-price text-success fw-bold">
                        ${fnFormatMoney(product.display_price)}
                    </span>
                </div>
            `;
        } else {
            priceDisplay = `<span class="fw-bold">${fnFormatMoney(product.display_price || product.priceSale)}</span>`;
        }

        let identifyProduct = `product-${index + 1}`;

        // Miniatura del producto (placeholder pluma si no hay imagen guardada)
        const thumbOrder = product.image
            ? `<img src="${product.image}" class="sommy-order-thumb" alt="">`
            : `<div class="sommy-order-thumb sommy-order-thumb--ph"><i class="fa-solid fa-feather" aria-hidden="true"></i></div>`;

        const badgeSinStock = product.sinStock
            ? `<span class="sommy-badge-sinstock"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Sin stock — a consultar</span>`
            : "";

        divShowProductCart.innerHTML += `
            <div class="product-card p-3 shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            ${thumbOrder}
                            <div style="min-width:0;">
                                <h6 class="mb-1">${product.name}</h6>
                                ${addRow}
                                ${badgeSinStock ? `<div class="mt-1">${badgeSinStock}</div>` : ""}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center gap-2">
                            <button class="quantity-btn" onclick="updateSubtractionQuantity('${product.claveCart}')">-</button>
                            <input type="number" class="quantity-input" id="${identifyProduct}" value="${product.cant}" min="1" disabled>
                            <button class="quantity-btn" onclick="updateSumQuantity('${product.claveCart}')">+</button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        ${priceDisplay}
                    </div>
                    <div class="col-md-2 text-md-end">
                        <button class="btn" onclick="fnDeleteProductOrder('${product.claveCart}');"><i class="fas fa-trash remove-btn"></i></button>
                    </div>
                </div>
            </div>
        `;
        quantityProduct++;
        totalCartProduct += product.total;
    });

    showSubtotalPedido.textContent = fnFormatMoney(totalCartProduct);
    fnActualizarResumen();
    fnShowQuantityProduct(quantityProduct);
};

/**
 * Recalcula el resumen: subtotal + envío − descuento transferencia.
 * El total definitivo SIEMPRE lo recalcula el servidor; esto es informativo.
 */
const fnActualizarResumen = () => {
    const subtotal = listCartProdOrder.reduce((acc, p) => acc + Number(p.total || 0), 0);

    // Envío según zona seleccionada
    let costoEnvio = 0;
    const zonaSeleccionada = document.querySelector('.radio-zona-envio:checked');
    if (zonaSeleccionada) {
        costoEnvio = Number(zonaSeleccionada.dataset.costo || 0);
        rowEnvioPedido.style.setProperty('display', 'flex', 'important');
        showEnvioPedido.textContent = costoEnvio > 0 ? fnFormatMoney(costoEnvio) : 'Gratis';
    } else if (rowEnvioPedido) {
        rowEnvioPedido.style.setProperty('display', 'none', 'important');
    }

    // Descuento por transferencia
    let descuento = 0;
    const metodoSeleccionado = document.querySelector('.radio-metodo-pago:checked');
    const porcDesc = Number(window.DESC_TRANSFERENCIA || 0);
    if (metodoSeleccionado && metodoSeleccionado.value === 'transferencia' && porcDesc > 0) {
        descuento = subtotal * (porcDesc / 100);
        rowDescuentoPedido.style.setProperty('display', 'flex', 'important');
        showDescuentoPedido.textContent = '-' + fnFormatMoney(descuento);
    } else if (rowDescuentoPedido) {
        rowDescuentoPedido.style.setProperty('display', 'none', 'important');
    }

    showSubtotalPedido.textContent = fnFormatMoney(subtotal);
    showTotalPedido.textContent = fnFormatMoney(subtotal + costoEnvio - descuento);
};

// Recalcular resumen al cambiar zona o método de pago
document.addEventListener('change', (e) => {
    if (e.target.classList && (e.target.classList.contains('radio-zona-envio') || e.target.classList.contains('radio-metodo-pago'))) {
        fnActualizarResumen();
    }
    // Mostrar/ocultar datos bancarios
    if (e.target.classList && e.target.classList.contains('radio-metodo-pago')) {
        const datosTransf = document.querySelector('#datos-transferencia');
        if (datosTransf) {
            datosTransf.style.display = e.target.value === 'transferencia' && e.target.checked ? 'block' : 'none';
        }
    }
});

const fnDeleteProductOrder = (claveProductCart) => {
    console.log(claveProductCart);
    console.log(listCartProdOrder);

    listCartProdOrder = listCartProdOrder.filter( item => {
        return item.claveCart !== claveProductCart;
    });
    console.log(listCartProdOrder)
    fnSaveCartProduct(listCartProdOrder);// function for saving cart after delete product 
    fnMessageToastrSuccess("Se elimino el producto del pedido","Exito!");
    fnShowCurrentCartProduct();//function for show list cart product updated
} 

const updateSumQuantity = (claveProduct) => {
    const findProductIndex = listCartProdOrder.findIndex(item => item.claveCart === claveProduct);//find the product for update
    let currentCantProduct = Number(listCartProdOrder[findProductIndex].cant);
    let currentStockProduct = Number(listCartProdOrder[findProductIndex].stockProduct);
    let sumaCant = currentCantProduct + 1;
    const sinStock = fnCheckStockProduct(currentStockProduct, sumaCant);// no bloquea: solo marca el item
    listCartProdOrder[findProductIndex].cant  += 1;
    listCartProdOrder[findProductIndex].total = Number(listCartProdOrder[findProductIndex].cant) * Number(listCartProdOrder[findProductIndex].priceSale);
    listCartProdOrder[findProductIndex].sinStock = sinStock;
    fnSaveCartProduct(listCartProdOrder);// function for saving cart product updated quantity
    if (sinStock) {
        fnMessageToastrWarning("Sin stock suficiente: queda a consultar por WhatsApp.","Sin stock");
    } else {
        fnMessageToastrSuccess("Se agregaron mas unidades","Exito!");
    }
    fnShowCurrentCartProduct();//function for show list cart product updated
}

const updateSubtractionQuantity = (claveProduct) => {
    console.log(claveProduct);
    console.log(listCartProdOrder)
    const findProductIndex = listCartProdOrder.findIndex(item => item.claveCart === claveProduct);//find the product for update
    console.log(findProductIndex);
    console.log(listCartProdOrder[findProductIndex].cant);
    if(listCartProdOrder[findProductIndex].cant <= 1){// if cant product is less than 1
        fnMessageToastrError("Superaste la cantidad minima","Error");
        return false;
    }
    listCartProdOrder[findProductIndex].cant  -= 1;
    listCartProdOrder[findProductIndex].total = Number(listCartProdOrder[findProductIndex].cant) * Number(listCartProdOrder[findProductIndex].priceSale);
    listCartProdOrder[findProductIndex].sinStock = fnCheckStockProduct(
        Number(listCartProdOrder[findProductIndex].stockProduct),
        Number(listCartProdOrder[findProductIndex].cant)
    );
    fnSaveCartProduct(listCartProdOrder);// function for saving cart product updated quantity
    fnMessageToastrWarning("Menos 1 unidad","Exito!");
    fnShowCurrentCartProduct();//function for show list cart product updated
}
/*********************************************************************** */

// Candado: garantiza que el pedido viaje UNA sola vez aunque haya doble click
let pedidoEnviandose = false;

const fnRegistrarPedido = () => {
    if (pedidoEnviandose) {
        return false; // ya hay un envío en curso
    }

    if(pedidoArray.length == 0){
        fnMessageToastrError("No has ingresado los datos para el pedido","Error");
        return false;
    }

    // Validar por nombre de sección (no por cantidad)
    const seccionesRequeridas = ["emailSection", "identificacionSection", "entregaSection", "envioSection", "pagoSection"];
    const seccionesPresentes = pedidoArray.map(s => s.nameSection);
    const faltantes = seccionesRequeridas.filter(s => !seccionesPresentes.includes(s));

    if(faltantes.length > 0){
        fnMessageToastrError("Faltan completar datos del pedido","Error");
        return false;
    }

    if(listCartProdOrder.length == 0){
        fnMessageToastrError("No hay productos agregados al pedido","Error");
        return false;
    }

    pedidoArray = pedidoArray.filter(s => s.nameSection !== "products");
    pedidoArray.push({nameSection: "products", data: listCartProdOrder});

    const formDataEcommerce = new FormData();
    formDataEcommerce.append('data', JSON.stringify(pedidoArray));

    pedidoEnviandose = true;
    btnRegisterOrder.disabled = true;
    btnRegisterOrder.textContent = "Enviando pedido...";

    saveDataEcommerce(formDataEcommerce,'/Ecommercesaveorder').then((resp) => {
        console.log(resp);
        if(resp.status === 1){
            pedidoArray = [];//reset array order
            localStorage.removeItem('listShoppingCart');//remove the variable listShoppingCart

            // Rama MercadoPago: redirigir al Checkout Pro
            if(resp.mp_init_point){
                window.location.href = resp.mp_init_point;
                return;
            }

            fnMessageToastrSuccess(resp.message,"Exito!");
            fnExecuteAfterRegisterOrder(resp);
            // El pedido ya está registrado: el botón NO se rehabilita (evita duplicados)
            btnRegisterOrder.style.display = "none";
            fnShowListCartProduct();//load the list cart product
            fnShowQuantityProduct(0);//reset cart product
        }
        if(resp.status === 0){
            fnMessageToastrError(resp.message,"Error");
            pedidoEnviandose = false;
            btnRegisterOrder.disabled = false;
            btnRegisterOrder.textContent = "Realizar pedido";
        }
    }).catch(() => {
        fnMessageToastrError("No se pudo enviar el pedido. Revisá tu conexión e intentá de nuevo.","Error");
        pedidoEnviandose = false;
        btnRegisterOrder.disabled = false;
        btnRegisterOrder.textContent = "Realizar pedido";
    });
};

// Único botón del checkout: valida los 5 grupos de datos de una sola vez y,
// si está todo OK, registra el pedido (sin pasos intermedios).
btnRegisterOrder.addEventListener("click", (e) => {
    e.preventDefault();
    if (fnAddFormDataArrayForEachSection() === true) {
        fnActualizarResumen();
        fnRegistrarPedido();
    }
});

const fnExecuteAfterRegisterOrder = (data) => {
    pedidoArray = [];
    dataOrder = data.dataOrder;
    dataOrderDetail = data.dataOrderDetail;
    nameCustomerCurrent.textContent = `Gracias ${data.dataCustomer.nombre} ${data.dataCustomer.materno}`
    // Mostrar el número de pedido
    var numeroPedidoEl = document.getElementById('p-numero-pedido');
    if (numeroPedidoEl && dataOrder && dataOrder.order_id) {
        numeroPedidoEl.textContent = '#' + dataOrder.order_id;
    }
    divContentOrderGlobal.innerHTML = "";
    divSectionWhatsapp.style.display = "block";

    // Abrir WhatsApp automáticamente con el detalle del pedido
    // (el botón verde queda como respaldo por si el navegador bloquea la apertura)
    const numeroTelefono = (window.CONFIG_WHATSAPP || "").replace(/\D/g, "");
    if (numeroTelefono) {
        const mensaje = fnGenerateMessageOrder(dataOrder, dataOrderDetail);
        const enlace = fnSeendDataOrderWhatsApp(numeroTelefono, mensaje);
        const ventana = window.open(enlace, "_blank");
        if (!ventana) {
            // Bloqueado por el navegador: navegar en la misma pestaña tras un instante
            setTimeout(function () { window.location.href = enlace; }, 1200);
        }
    }
}

const fnValidateEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Al salir del campo email (sin botón): si ya existe un cliente con ese correo,
// completa el resto del formulario solo. No bloquea ni valida nada acá — la
// validación completa pasa una sola vez, al hacer click en "Realizar pedido".
const inputEmailCliente = document.querySelector('#inputEmailCliente');
if (inputEmailCliente) {
    inputEmailCliente.addEventListener('blur', () => {
        const inputValueEmail = inputEmailCliente.value.trim();
        if (!inputValueEmail || fnValidateEmail(inputValueEmail) !== true) return;

        const formDataEcommerce = new FormData();
        formDataEcommerce.append('email', inputValueEmail);
        getDataEcommerce(formDataEcommerce, '/EcommerceFindEmailCustomer').then((resp) => {
            if (resp.status === 1 && resp.exist === true) {
                let dataCustomer = resp.dataCustomer;
                document.querySelector('#name').value = dataCustomer.nombre || '';
                document.querySelector('#materno').value = dataCustomer.materno || '';
                document.querySelector('#phone').value = dataCustomer.telefono || '';
                document.querySelector('#calle').value = dataCustomer.direccion || '';
                document.querySelector('#numberExterior').value = dataCustomer.number_exterior || '';
                document.querySelector('#numberInterior').value = dataCustomer.number_interior || '';
                document.querySelector('#localidad').value = dataCustomer.localidad || '';
                document.querySelector('#provincia').value = dataCustomer.provincia || '';
                document.querySelector('#codigoPostal').value = dataCustomer.codigo_postal || '';
                document.querySelector('#dniCuit').value = dataCustomer.dni_cuit || '';
                fnMessageToastrWarning(resp.message, 'Verificar!');
            }
        });
    });
}

fnAddFormDataArrayForEachSection = () => {
    pedidoArray = []; // reset para evitar secciones duplicadas si el usuario edita y reconfirma

    /**************FORM EMAIL****************************** */
    const dataEmail = new FormData(formEmail);
    const emailValues = Object.fromEntries(dataEmail.entries());
    let inputValueEmail = emailValues.email;
    if(inputValueEmail === ""){
        fnMessageToastrError("No has ingresado un correo electronico","Error");
        return false;
    }

    if(fnValidateEmail(inputValueEmail) !== true){
        fnMessageToastrError("El correo electronico es invalido","Error");
        return false;
    }
    pedidoArray.push({nameSection: "emailSection", data: {email: inputValueEmail}});

    /************* FORM IDENTIFICACION ********************* */
    const dataIdentificacion = new FormData(formIdentificacion);
    const identificationValues = Object.fromEntries(dataIdentificacion.entries());
    let validateForm = 0;
    for (const nameInput in identificationValues) {
        if(identificationValues[`${nameInput}`] === ""){
            validateForm++;
        }
    }
    if( validateForm !== 0){
        fnMessageToastrError("Faltan datos por llenar para la seccion identificacion","Error");
        return false;
    }
    pedidoArray.push({nameSection: "identificacionSection", data: {...identificationValues}});

    /*************FORM ENTREGA************************************* */
    const dataEntrega = new FormData(formEntrega);
    const entregaValues = Object.fromEntries(dataEntrega.entries());
    let validateFormEntrega = 0;
    for (const nameInput in entregaValues) {
        if(CAMPOS_ENTREGA_OPCIONALES.includes(nameInput)) continue;
        if(entregaValues[`${nameInput}`] === ""){
            validateFormEntrega++;
        }
    }
    if( validateFormEntrega !== 0){
        fnMessageToastrError("Faltan datos por llenar para la seccion entrega","Error");
        return false;
    }
    pedidoArray.push({nameSection: "entregaSection", data: {...entregaValues}});

    /*************SECCION ENVIO************************************* */
    const zonasDisponibles = document.querySelectorAll('.radio-zona-envio');
    const zonaSeleccionada = document.querySelector('.radio-zona-envio:checked');
    if (zonasDisponibles.length > 0 && !zonaSeleccionada) {
        fnMessageToastrError("Seleccioná una opción de envío","Error");
        return false;
    }
    pedidoArray.push({
        nameSection: "envioSection",
        data: { zonaEnvioId: zonaSeleccionada ? zonaSeleccionada.value : null }
    });

    /*************SECCION PAGO************************************* */
    const metodoSeleccionado = document.querySelector('.radio-metodo-pago:checked');
    if (!metodoSeleccionado) {
        fnMessageToastrError("Seleccioná un método de pago","Error");
        return false;
    }
    pedidoArray.push({
        nameSection: "pagoSection",
        data: { metodo: metodoSeleccionado.value }
    });

    return true;
}

btnSendDataOrderWhatsapp.addEventListener("click", (e) => {
    e.preventDefault();
    const numeroTelefono = (window.CONFIG_WHATSAPP || "").replace(/\D/g, "");
    if (!numeroTelefono) {
        fnMessageToastrError("La tienda no tiene un número de WhatsApp configurado","Error");
        return;
    }
    const mensaje = fnGenerateMessageOrder(dataOrder,dataOrderDetail);

    const enlace = fnSeendDataOrderWhatsApp(numeroTelefono, mensaje);
    window.open(enlace, "_blank");
});

const fnSeendDataOrderWhatsApp = (numero,mensaje) => {
    // Codificar el mensaje para URL
    const mensajeCodificado = encodeURIComponent(mensaje);
    // Crear el enlace
    return `https://wa.me/${numero}?text=${mensajeCodificado}`;
}

const fnGenerateMessageOrder = (orderc,orderDetaild) => {
    console.log(orderc);
    console.log(orderDetaild);
    let mensaje = `Hola, aquí están los datos de mi pedido:\n\n`;
    mensaje += `Pedido:\n`;
    mensaje += `Pedido ID: ${orderc.order_id}\n`;
    mensaje += `Subtotal: $${orderc.subtotal_amount}\n`;
    if (Number(orderc.costo_envio) > 0) {
        mensaje += `Envío: $${orderc.costo_envio}\n`;
    }
    if (Number(orderc.descuento_pago) > 0) {
        mensaje += `Descuento transferencia: -$${orderc.descuento_pago}\n`;
    }
    mensaje += `Total: $${orderc.total_amount}\n\n`;
    mensaje += `Detalles del Pedido:\n`;

    const hayItemsSinStock = orderDetaild.some(d => Number(d.sin_stock) === 1);
    if (hayItemsSinStock) {
        mensaje += `⚠️ Hay productos sin stock disponible ahora mismo, marcados abajo. Se piden igual, quedan a consultar.\n\n`;
    }

    orderDetaild.forEach((detail, index) => {
        const sinStock = Number(detail.sin_stock) === 1;
        mensaje += `Producto ${index + 1}${sinStock ? ' — ⚠️ SIN STOCK, A CONSULTAR' : ' — ✅ con stock, entrega inmediata'}:\n`;
        mensaje += `- Nombre: ${detail.producto_nombre}\n`;
        if(detail.variante_nombre){
            mensaje += `- Medida: ${detail.variante_nombre}\n`;
        }
        mensaje += `- Cantidad: ${detail.quantity}\n`;
        mensaje += `- Precio: $${detail.price}\n`;
        mensaje += `- Total: $${detail.total}\n\n`;
    });

    return mensaje;
}

const fnValidatePhoneNumber = (phoneNumber) => {
    const usPhoneRegex = /^\(\d{3}\)\s\d{3}-\d{4}$/;  // (XXX) XXX-XXXX format
    return usPhoneRegex.test(phoneNumber);
}
