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

        const variantes = Array.isArray(p.variantes) ? p.variantes : [];
        const tieneStock = p.tipo_producto_id === 1 ? p.stock > 0 : variantes.length > 0;

        // Producto simple: agrega directo. Con variantes: agrega directo si hay una sola
        // opción con stock, o abre un mini selector si hay varias.
        const boton = tieneStock
          ? `<button type="button" class="sommy-related-add sommy-related-add--icon" data-related-id="${p.id}" aria-label="Agregar al carrito">+</button>`
          : '';

        const picker = (p.tipo_producto_id === 2 && variantes.length > 1)
          ? `
            <div class="sommy-related-picker" data-picker-id="${p.id}" hidden>
              <select class="sommy-related-picker-select">
                ${variantes.map(v => `<option value="${v.idcombinacion}">${esc(v.label)}</option>`).join('')}
              </select>
              <button type="button" class="sommy-related-picker-confirm" data-confirm-id="${p.id}" aria-label="Confirmar">+</button>
            </div>
          `
          : '';

        return `
          <div class="sommy-related-card">
            <a href="/producto/${esc(p.slug)}" class="sommy-related-link">
              ${thumb}
              <div class="sommy-related-info">
                <div class="sommy-related-name">${esc(p.nombre)}</div>
                ${desdeLabel}
                <div class="sommy-related-price">${window.fnFormatMoney(p.precio)}</div>
              </div>
            </a>
            ${boton}
            ${picker}
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
        const id = btn.getAttribute('data-related-id');
        const producto = productos.find(p => String(p.id) === id);
        if (!producto) return;

        btn.addEventListener('click', () => {
          const variantes = Array.isArray(producto.variantes) ? producto.variantes : [];

          if (producto.tipo_producto_id === 1) {
            window.fnAddRelatedToCart(producto);
            return;
          }

          // Producto con variantes: si hay una sola opción se agrega directo,
          // si hay varias se despliega el selector para elegir cuál.
          if (variantes.length === 1) {
            window.fnAddRelatedVariantToCart(producto, variantes[0]);
            return;
          }

          const picker = window.cartRelatedProductsContainer.querySelector(`.sommy-related-picker[data-picker-id="${id}"]`);
          if (picker) picker.hidden = !picker.hidden;
        });
      });

      window.cartRelatedProductsContainer.querySelectorAll('.sommy-related-picker-confirm[data-confirm-id]').forEach(btn => {
        const id = btn.getAttribute('data-confirm-id');
        const producto = productos.find(p => String(p.id) === id);
        if (!producto) return;

        btn.addEventListener('click', () => {
          const picker = btn.closest('.sommy-related-picker');
          const select = picker.querySelector('.sommy-related-picker-select');
          const variante = (producto.variantes || []).find(v => String(v.idcombinacion) === select.value);
          if (variante) {
            window.fnAddRelatedVariantToCart(producto, variante);
            picker.hidden = true;
          }
        });
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

// Agrega una variante puntual (medida/combinación) de un producto recomendado
window.fnAddRelatedVariantToCart = (product, variante) => {
  const cart = window.fnListCartProduct();
  const claveCart = `${product.id}-${variante.idcombinacion}`;
  const existente = cart.find(prod => prod.claveCart === claveCart);

  if (existente) {
    existente.cant += 1;
    existente.total = existente.cant * existente.priceSale;
    existente.sinStock = window.fnCheckStockProduct(variante.stock, existente.cant);
  } else {
    cart.push({
      claveCart: claveCart,
      name: product.nombre,
      productId: product.id,
      original_price: variante.precio,
      priceSale: variante.precio,
      cant: 1,
      total: variante.precio,
      rowProdVariant: { idcombinacion: variante.idcombinacion, combinacion: variante.label, pventa_variante: variante.precio },
      tipoProductoId: 2,
      stockProduct: variante.stock,
      display_price: variante.precio,
      has_offer: false,
      image: product.imagen,
      sinStock: window.fnCheckStockProduct(variante.stock, 1)
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
  window.fnSyncCarritoServidor(newCart);
};

window.fnSaveCartProduct = (shoppingCart) => {
  localStorage.setItem('listShoppingCart', JSON.stringify(shoppingCart));
  window.fnSyncCarritoServidor(shoppingCart);
};

// Manda el carrito al servidor SOLO si hay un cliente logueado (el propio
// endpoint no hace nada si no lo hay) — sirve para poder avisarle por mail
// si lo abandona. Con debounce para no pegarle al servidor en cada click.
let __carritoSyncTimeout = null;
window.fnSyncCarritoServidor = (shoppingCart) => {
  clearTimeout(__carritoSyncTimeout);
  __carritoSyncTimeout = setTimeout(() => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) return;

    const total = shoppingCart.reduce((acc, p) => acc + (Number(p.total) || 0), 0);

    fetch('/Ecommercecarritosync', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
      },
      body: JSON.stringify({ items: shoppingCart, total }),
    }).catch(() => {});
  }, 600);
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