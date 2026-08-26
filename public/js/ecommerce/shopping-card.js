$(document).ready(function() {
    // Inicializar Fancybox
    $('[data-fancybox="gallery"]').fancybox({
        loop: true,
        buttons: ["zoom", "slideShow", "fullScreen", "thumbs", "close"],
        animationEffect: "fade",
        transitionEffect: "slide"
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // Pintar carrito inicial
    if (typeof window.fnShowListCartProduct === "function") {
        window.fnShowListCartProduct();
    }

    // Inicializar primera variante si es producto con combinaciones
    if(productValue[0].tipo_producto_id === 2){
        const dataEachVariant = document.querySelector('#dataEachVariant');
        const productEachVariant = JSON.parse(dataEachVariant.value);
        if(productEachVariant.length > 0){
            const firstVariant = productEachVariant[0];
            const precio = firstVariant.combinacion.pventa_variante;

            showPriceVariant.textContent = window.fnFormatMoney(precio);
            priceProduct.value = Number(precio);

            if (firstVariant.combinacion.url) {
                fnSetImagenVariante(firstVariant.combinacion.url);
            }

            btnAddProduct.setAttribute("data-value", JSON.stringify({
                tipoProductoId: productValue[0].tipo_producto_id,
                idarticulo: productValue[0].idarticulo,
                name_product: productValue[0].nombre,
                price: precio,
                stock: firstVariant.total_stock,
                combinacion: firstVariant.combinacion
            }));

            const firstRadio = document.querySelector('input[name="variantProduct"]');
            if(firstRadio) firstRadio.checked = true;

            // ✅ Actualizar badge oferta inicial para la primera variante
            updateBadgeOffer(precio);
        }
    }

    // ✅ Si es producto simple, actualizar badge oferta inicial
    if(productValue[0].tipo_producto_id === 1){
        const precioBase = productValue[0].pventa_con_iva;
        const precioEfectivo = productValue[0].display_price || precioBase;
        updateBadgeOffer(precioEfectivo);
    }
});

const btnLessCant = document.querySelector('#btnLessCant');
const btnAddCantMore = document.querySelector('#btnAddCantMore');
const priceProduct = document.querySelector('#priceProduct');
const showPriceVariant = document.querySelector('#showPriceVariant');
const showImageVariant = document.querySelector('#showImageVariant');
const cantProduct = document.querySelector('#cantProduct');
const dataProduct = document.querySelector('#dataProduct');
let productValue = JSON.parse(dataProduct.value);
const badgeContainer = document.getElementById("badgeOffer");
const linkImageVariant = document.querySelector('#linkImageVariant');

// Cambia la imagen principal (y su zoom/thumb activo) según la variante seleccionada
function fnSetImagenVariante(url) {
    if (!url) return;
    if (showImageVariant) showImageVariant.src = url;
    if (linkImageVariant) linkImageVariant.href = url;
    document.querySelectorAll('#galeriaThumbs img').forEach(t => {
        t.classList.toggle('active', t.src === url);
    });
}

// Listener para radios de combinaciones
document.querySelectorAll('input[name="variantProduct"]').forEach(radio => {
    radio.addEventListener('change', function() {
        try {
            const variantData = JSON.parse(this.value);
            const precio = variantData.combinacion.pventa_variante;

            // Actualizar precio mostrado
            showPriceVariant.textContent = window.fnFormatMoney(precio);
            priceProduct.value = Number(precio);

            // Actualizar imagen si existe
            if (variantData.combinacion.url) {
                fnSetImagenVariante(variantData.combinacion.url);
            }

            // Actualizar data para el botón de agregar al carrito
            btnAddProduct.setAttribute("data-value", JSON.stringify({
                tipoProductoId: productValue[0].tipo_producto_id,
                idarticulo: productValue[0].idarticulo,
                name_product: productValue[0].nombre,
                price: precio,
                stock: variantData.total_stock,
                combinacion: variantData.combinacion
            }));

            // ✅ Actualizar badge oferta dinámicamente
            updateBadgeOffer(precio);

        } catch (err) {
            console.error("Error parseando variante:", err);
        }
    });
});

// Botón agregar al carrito
const btnAddProduct = document.querySelector("#btn-add-product");
btnAddProduct.addEventListener("click", (e) => {
    e.preventDefault();

    let tipoProductoId = productValue[0].tipo_producto_id;

    if(tipoProductoId == 2){
        const dataValue = btnAddProduct.getAttribute("data-value");
        if(!dataValue){
            window.fnMessageToastrError("Debes seleccionar una combinación", "Error");
            return;
        }
        const variantData = JSON.parse(dataValue);
        addShoppingCart(productValue[0], variantData);
    }

    if(tipoProductoId == 1){
        addShoppingCart(productValue[0]);
    }
});

// Función carrito
const addShoppingCart = (product, variantData = null) => {
    let tipoProductoId = product.tipo_producto_id;
    let currentCantProduct = Number(cantProduct.value);
    let nombre = product.nombre;
    let productId = product.idarticulo;
    let generateClaveCart = "";
    let productStock = 0;
    let rowProdVariant = null;
    let addProduct = {};

    // Imagen actual del producto (refleja la variante seleccionada si la hay)
    const productImage = (typeof showImageVariant !== "undefined" && showImageVariant && showImageVariant.src)
        ? showImageVariant.src
        : null;

    if(tipoProductoId === 1){
        // Producto simple
        generateClaveCart = productId;
        productStock = Number(product.stock);

        const basePrice = product.pventa_con_iva;
        const effectivePrice = product.display_price || basePrice;

        addProduct = {
            claveCart: String(generateClaveCart),
            name: nombre,
            productId: productId,
            original_price: basePrice,                 // precio normal
            priceSale: effectivePrice,                 // precio usado en el carrito
            cant: currentCantProduct,
            total: currentCantProduct * effectivePrice,
            rowProdVariant: null,
            tipoProductoId: tipoProductoId,
            stockProduct: productStock,
            display_price: effectivePrice,             // precio efectivo
            has_offer: product.has_offer || false,     // bandera de oferta
            image: productImage,                        // miniatura para carrito/checkout
            sinStock: window.fnCheckStockProduct(productStock, currentCantProduct) // sin stock suficiente: se agrega igual, queda "a consultar"
        };
    }

    if(tipoProductoId === 2 && variantData){
        // Producto con combinaciones
        generateClaveCart = productId + "-" + variantData.combinacion.idcombinacion;
        rowProdVariant = variantData.combinacion;
        productStock = Number(variantData.stock);

        const basePrice = product.pventa_con_iva; // precio padre
        const comboPrice = Number(variantData.combinacion.pventa_variante); // precio final de la combinación

        addProduct = {
            claveCart: String(generateClaveCart),
            name: nombre,
            productId: productId,
            original_price: basePrice,                 // precio normal del padre
            priceSale: comboPrice,                     // precio final de la combinación
            cant: currentCantProduct,
            total: currentCantProduct * comboPrice,
            rowProdVariant: rowProdVariant,
            tipoProductoId: tipoProductoId,
            stockProduct: productStock,
            display_price: comboPrice,                 // siempre el precio de la combinación
            has_offer: basePrice > comboPrice,         // oferta si el padre es más caro
            image: productImage,                        // miniatura para carrito/checkout
            sinStock: window.fnCheckStockProduct(productStock, currentCantProduct) // sin stock suficiente: se agrega igual, queda "a consultar"
        };
    }

    let getDataCardProduct = window.fnListCartProduct();

    const checkIfExistProduct = getDataCardProduct.find(prod => {
        if(tipoProductoId === 1){
            return prod.productId === productId;
        }
        if(tipoProductoId === 2){
            return prod.productId === productId &&
                   prod.rowProdVariant.idcombinacion === rowProdVariant.idcombinacion;
        }
    });

    if(checkIfExistProduct){
        let sumaStock = Number(checkIfExistProduct.cant) + currentCantProduct;
        checkIfExistProduct.cant += currentCantProduct;
        checkIfExistProduct.total = checkIfExistProduct.cant * checkIfExistProduct.priceSale;
        checkIfExistProduct.sinStock = window.fnCheckStockProduct(productStock, sumaStock);
        if(checkIfExistProduct.sinStock){
            window.fnMessageToastrWarning("Se agregaron igual, sin stock suficiente: queda a consultar por WhatsApp.","Sin stock");
        }else{
            window.fnMessageToastrSuccess("Se agregaron más unidades","Éxito!");
        }
    }else{
        getDataCardProduct.push(addProduct);
        if(addProduct.sinStock){
            window.fnMessageToastrWarning("Lo agregamos igual, no hay stock suficiente: queda a consultar por WhatsApp.","Sin stock");
        }else{
            window.fnMessageToastrSuccess("Se agregó con éxito el producto al carrito","Éxito!");
        }
    }

    window.fnSaveCartProduct(getDataCardProduct);
    window.fnShowListCartProduct();
};

function updateBadgeOffer(precioVariante) {
    const precioBase = productValue[0].pventa_con_iva ?? productValue[0].display_price;
    if (precioVariante < precioBase) {
        badgeContainer.innerHTML = `
            <span class="badge bg-success mb-2">
                <i class="fas fa-tags"></i> Oferta
            </span>
        `;
    } else {
        badgeContainer.innerHTML = "";
    }
}

// Botones cantidad
btnAddCantMore.addEventListener("click", (e) =>{
    e.preventDefault();
    fnAddCantProduct(cantProduct);
});

const fnAddCantProduct = (inputCant) => {
    let tipoProductoId = productValue[0].tipo_producto_id;
    let productStock = 0;

    if(tipoProductoId === 1){
        productStock = Number(productValue[0].stock);
    }
    if(tipoProductoId === 2){
        const dataValue = btnAddProduct.getAttribute("data-value");
        if(dataValue){
            const variantData = JSON.parse(dataValue);
            productStock = Number(variantData.stock);
        }
    }

    let newCant = Number(inputCant.value) + 1;
    // Se permite pedir más de lo que hay: al cruzar el stock disponible se avisa
    // una sola vez (no en cada click), y el producto queda "a consultar" igual.
    if(newCant > productStock && newCant - 1 <= productStock){
        window.fnMessageToastrWarning("A partir de acá no hay stock suficiente: se puede pedir igual, queda a consultar.","Sin stock");
    }

    inputCant.value = newCant;
}

btnLessCant.addEventListener("click", (e) => {
    e.preventDefault();
    fnLessCant(cantProduct);
});

const fnLessCant = (inputCant) => {
    let cant = Number(inputCant.value);
    if(cant > 1){
        inputCant.value = cant - 1;
    }
}

const fnCheckDefaultValue = () => {
    window.fnMessageToastrError("No existe la variante para el producto.","Error!");
};

function changeImage(event, src) {
    document.querySelector('.product-main-image').src = src;
    document.querySelector('#image-fancibox-main').href = src;
}