console.log("main_ecommerce.js: funciones globales del carrito y header");

/**************** VARIABLES GLOBALES DEL CARRITO ****************/
// Exponer en window para evitar redeclaraciones entre archivos
window.showQuantityProductsAdded = document.querySelector('.show-total-products-added') || null;
window.showQuantityHeaderProductsAdded = document.querySelector('.show-total-header-products-added') || null;
window.divShowEmptyCart = document.querySelector('#div-show-empty-cart') || null;
window.showShoppingCart = document.querySelector('#list-shopping-cart') || null;
window.divContentButtonFinish = document.querySelector('#div-content-button-finish') || null;
window.cartRelatedProductsContainer = document.querySelector('#cart-related-products') || null;
/***************************************************************/

/**************** INICIALIZACIONES DE VISTA ********************/
document.addEventListener("DOMContentLoaded", function () {
  // Slider autoplay category (si existe en la vista actual)
  const categoryCarouselEl = document.querySelector(".category-carousel");
  if (categoryCarouselEl && typeof Swiper !== "undefined") {
    new Swiper(".category-carousel", {
      loop: true,
      autoplay: {
        delay: 3000,
        disableOnInteraction: true,
      },
      slidesPerView: 'auto',
      grabCursor: true,
      spaceBetween: 20,
      navigation: {
        nextEl: ".category-carousel-next",
        prevEl: ".category-carousel-prev",
      }
    });
  }

  // Pintar carrito si los contenedores existen en la vista
  if (typeof window.fnShowListCartProduct === "function") {
    window.fnShowListCartProduct();
  } else {
    // Si aún no está definida (primera carga del archivo), llamamos luego de definirla
    setTimeout(() => {
      if (typeof window.fnShowListCartProduct === "function") {
        window.fnShowListCartProduct();
      }
    }, 0);
  }
});
/***************************************************************/

/**************** FUNCIONES GLOBALES DEL CARRITO ****************/
window.fnListCartProduct = () => {
  try {
    return JSON.parse(localStorage.getItem('listShoppingCart')) || [];
  } catch (e) {
    return [];
  }
};

window.fnShowListCartProduct = () => {
  const list = window.fnListCartProduct();

  if (!window.divShowEmptyCart || !window.showShoppingCart || !window.divContentButtonFinish) {
    // Si la vista actual no tiene contenedores de carrito (por ejemplo, header sólo badge)
    window.fnShowQuantityProduct(list.length);
    return;
  }

  if (list.length === 0) {
    window.fnShowContentCartEmpty();
    window.fnShowQuantityProduct(0);
    return;
  }

  let quantityProduct = 0;
  let totalCartProduct = 0;

  window.divShowEmptyCart.innerHTML = '';
  window.showShoppingCart.innerHTML = '';

  list.forEach((prod) => {
    // Miniatura del producto (con placeholder pluma si no hay imagen)
    const thumbHtml = prod.image
      ? `<img src="${prod.image}" class="sommy-cart-thumb" alt="">`
      : `<div class="sommy-cart-thumb sommy-cart-thumb--ph"><i class="fa-solid fa-feather" aria-hidden="true"></i></div>`;

    const variantInfo = (prod.tipoProductoId === 2 && prod.rowProdVariant)
      ? ` &middot; ${prod.rowProdVariant.combinacion}`
      : "";

    const badgeSinStock = prod.sinStock
      ? `<span class="sommy-badge-sinstock"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Sin stock — a consultar</span>`
      : "";

    window.showShoppingCart.innerHTML +=
      `
        <li class="list-group-item px-0">
          <div class="sommy-cart-item">
            ${thumbHtml}
            <div style="flex:1;min-width:0;">
              <div class="sommy-cart-name">${prod.name}</div>
              <div class="sommy-cart-meta">Cantidad: ${prod.cant}${variantInfo}</div>
              ${badgeSinStock ? `<div class="mt-1">${badgeSinStock}</div>` : ""}
            </div>
            <div class="text-end">
              <div class="sommy-cart-price">${window.fnFormatMoney(prod.total)}</div>
              <button type="button" class="sommy-cart-remove" onClick="fnDeleteProdByKey('${prod.claveCart}');" aria-label="Quitar producto">
                <i class="fa fa-trash" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </li>
      `;

    quantityProduct++;
    totalCartProduct += Number(prod.total) || 0;
  });

  window.showShoppingCart.innerHTML +=
    `
      <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
        <span class="sommy-cart-total-label">Total</span>
        <span class="sommy-cart-total-value">${window.fnFormatMoney(totalCartProduct)}</span>
      </li>
    `;

  window.divContentButtonFinish.innerHTML =
    `
      <a href="/Ecommerceorder" class="w-100 btn btn-lg btn-1">Finalizar compra</a>
    `;

  window.fnShowQuantityProduct(quantityProduct);
  window.fnShowRelatedProducts();
};

window.fnShowQuantityProduct = (quantity) => {
  // Actualizar TODAS las instancias del contador (desktop, mobile y drawer)
  document.querySelectorAll('.show-total-header-products-added, .show-total-products-added')
    .forEach(function (el) { el.textContent = quantity; });
};

window.fnShowContentCartEmpty = () => {
  if (!window.divShowEmptyCart || !window.showShoppingCart || !window.divContentButtonFinish) return;

  window.showShoppingCart.innerHTML = '';
  window.divContentButtonFinish.innerHTML = '';
  window.divShowEmptyCart.innerHTML =
    `
      <div class="sommy-cart-empty">
        <div class="icon"><i class="fa-solid fa-feather" aria-hidden="true"></i></div>
        <p class="title">Tu carrito está liviano</p>
        <p class="hint">Todavía no agregaste productos.</p>
      </div>
    `;

  if (window.cartRelatedProductsContainer) {
    window.cartRelatedProductsContainer.innerHTML = '';
  }
};

/**************** PRODUCTOS RELACIONADOS EN EL CARRITO ***********/
window.fnShowRelatedProducts = () => {
  if (!window.cartRelatedProductsContainer) return;

  const cart = window.fnListCartProduct();
  if (cart.length === 0) {
    window.cartRelatedProductsContainer.innerHTML = '';
    return;
  }

  const ids = [...new Set(cart.map(prod => prod.productId))];

  fetch(`/Ecommercerelacionados?ids=${ids.join(',')}`)
    .then(res => res.json())
    .then(data => {
      const productos = data.productos || [];
      if (productos.length === 0) {
        window.cartRelatedProductsContainer.innerHTML = '';
        return;
      }

      const esc = (str) => String(str ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      }[c]));

      const cards = productos.map(p => {
        const desdeLabel = p.precio_desde ? `<div class="sommy-related-desde">Desde</div>` : '';
        const thumb = p.imagen
          ? `<img src="${esc(p.imagen)}" alt="" class="sommy-related-thumb">`
          : `<div class="sommy-related-thumb sommy-related-thumb--ph"><i class="fa-solid fa-feather" aria-hidden="true"></i></div>`;

        // Solo se agrega directo si es producto simple (sin variantes) y tiene stock.
        const puedeAgregarDirecto = p.tipo_producto_id === 1 && p.stock > 0;
        const boton = puedeAgregarDirecto
          ? `<button type="button" class="sommy-related-add" data-related-id="${p.id}">Agregar</button>`
          : `<a href="/producto/${esc(p.slug)}" class="sommy-related-add sommy-related-add--link">Ver producto</a>`;

        return `
          <div class="sommy-related-card">
            ${thumb}
            <div class="sommy-related-info">
              <div class="sommy-related-name">${esc(p.nombre)}</div>
              ${desdeLabel}
              <div class="sommy-related-price">${window.fnFormatMoney(p.precio)}</div>
            </div>
            ${boton}
          </div>
        `;
      }).join('');

      window.cartRelatedProductsContainer.innerHTML = `
        <div class="sommy-related-wrap">
          <div class="sommy-related-title">También te puede interesar</div>
          ${cards}
        </div>
      `;

      window.cartRelatedProductsContainer.querySelectorAll('.sommy-related-add[data-related-id]').forEach(btn => {
        const producto = productos.find(p => String(p.id) === btn.getAttribute('data-related-id'));
        if (producto) {
          btn.addEventListener('click', () => window.fnAddRelatedToCart(producto));
        }
      });
    })
    .catch(() => {
      window.cartRelatedProductsContainer.innerHTML = '';
    });
};

// Agrega un producto simple (sin variantes) recomendado desde el carrito
window.fnAddRelatedToCart = (product) => {
  const cart = window.fnListCartProduct();
  const claveCart = String(product.id);
  const existente = cart.find(prod => prod.claveCart === claveCart);

  if (existente) {
    existente.cant += 1;
    existente.total = existente.cant * existente.priceSale;
    existente.sinStock = window.fnCheckStockProduct(product.stock, existente.cant);
  } else {
    cart.push({
      claveCart: claveCart,
      name: product.nombre,
      productId: product.id,
      original_price: product.precio,
      priceSale: product.precio,
      cant: 1,
      total: product.precio,
      rowProdVariant: null,
      tipoProductoId: 1,
      stockProduct: product.stock,
      display_price: product.precio,
      has_offer: product.has_offer || false,
      image: product.imagen,
      sinStock: window.fnCheckStockProduct(product.stock, 1)
    });
  }

  window.fnSaveCartProduct(cart);
  window.fnShowListCartProduct();
  window.fnMessageToastrSuccess("Se agregó con éxito el producto al carrito", "Éxito!");
};
/***************************************************************/

window.fnDeleteProd = (product) => {
  const list = window.fnListCartProduct().filter(item => item.claveCart !== product.claveCart);
  window.fnShowQuantityProduct(list.length);
  window.fnUpdateCartAfterDelete(list);
  window.fnShowListCartProduct();
};

// Variante segura: elimina por clave (evita serializar el producto en el onclick)
window.fnDeleteProdByKey = (claveCart) => {
  window.fnDeleteProd({ claveCart: String(claveCart) });
};

window.fnUpdateCartAfterDelete = (newCart) => {
  localStorage.setItem('listShoppingCart', JSON.stringify(newCart));
};

window.fnSaveCartProduct = (shoppingCart) => {
  localStorage.setItem('listShoppingCart', JSON.stringify(shoppingCart));
};

window.fnFormatMoney = (money) => {
  try {
    return new Intl.NumberFormat('es-AR', {
      style: 'currency',
      currency: 'ARS'
    }).format(Number(money) || 0);
  } catch (e) {
    return `$${Number(money).toFixed(2)}`;
  }
};

window.fnMessageToastrError = (message, info) => {
  if (window.toastr) toastr.error(message, info);
};

window.fnMessageToastrWarning = (message, info) => {
  if (window.toastr) {
    toastr.warning(message, info, { "positionClass": "toast-bottom-center" });
  }
};

window.fnMessageToastrSuccess = (message, info) => {
  if (window.toastr) toastr.success(message, info);
};

window.fnCheckStockProduct = (currentStock, cant) => {
  return Number(cant) > Number(currentStock);
};
/***************************************************************/

/**************** HEADER RESPONSIVE MÓVIL **********************/
// Evitar redeclaración: sólo agregar listeners si existen los elementos
(function initHeaderToggle() {
  const toggleButton = document.getElementById('navbarToggle');
  const navbarMenu = document.querySelector('.navbar-menu-main');

  if (toggleButton && navbarMenu) {
    // Antes de agregar, remover posibles listeners duplicados usando flags simples
    if (!toggleButton.__hasToggleListener) {
      toggleButton.addEventListener('click', () => {
        navbarMenu.classList.toggle('show');
      });
      toggleButton.__hasToggleListener = true;
    }

    if (!navbarMenu.__hasMenuListener) {
      navbarMenu.addEventListener('click', () => {
        navbarMenu.classList.remove('show');
      });
      navbarMenu.__hasMenuListener = true;
    }
  }
})();
/***************************************************************/