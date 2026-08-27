<style>
/* ── HEADER ─────────────────────────────────────────── */
.ec-header {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1050;
    background: #fff;
    border-bottom: 1px solid #E7EAF2;
    transition: box-shadow 0.3s ease;
}
.ec-header.scrolled {
    box-shadow: 0 10px 30px rgba(27,43,90,.10);
}
.ec-header-inner {
    display: flex;
    align-items: center;
    height: 68px;
    padding: 0 24px;
    max-width: 1600px;
    margin: 0 auto;
    gap: 32px;
}

/* Logo */
.ec-logo img {
    height: 64px;
    width: auto;
    object-fit: contain;
    flex-shrink: 0;
}
@media (max-width: 991px) {
    .ec-logo img { height: 52px; }
}

/* Nav categories */
.ec-nav {
    display: flex;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    flex: 1;
}
.ec-nav .ec-nav-link {
    display: block;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: color 0.15s;
    white-space: nowrap;
}

/* Dropdown Categorías */
.ec-nav-drop { position: relative; }
.ec-nav-drop-panel {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 200px;
    background: #fff;
    border: 1px solid #E7EAF2;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(27,43,90,.14);
    padding: 8px;
    display: none;
    z-index: 1100;
}
.ec-nav-drop:hover .ec-nav-drop-panel { display: block; }
.ec-nav-drop-item {
    display: block;
    padding: 9px 14px;
    font-size: 14px;
    font-weight: 500;
    color: #1B2B5A;
    text-decoration: none;
    border-radius: 10px;
    transition: background .15s, color .15s;
}
.ec-nav-drop-item:hover { background: #E0F2FE; color: #1B2B5A; }
.ec-nav-drop-item.is-active { background: #F8FAFC; color: #2563EB; }

/* Actions */
.ec-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}
.ec-action-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    font-size: 14px;
    font-weight: 500;
    color: #1B2B5A;
    text-decoration: none;
    border-radius: 999px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
.ec-action-btn:hover {
    background: #E0F2FE;
    color: #2563EB;
}
.ec-action-btn i { font-size: 16px; }

/* Cart badge */
.ec-cart-btn { position: relative; }
.ec-cart-count {
    position: absolute;
    top: 0px; right: 0px;
    min-width: 18px; height: 18px;
    background: #2563EB;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 99px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 4px;
    line-height: 1;
}

/* Mobile toggle */
.ec-mobile-toggle {
    display: none;
    align-items: center;
    justify-content: center;
    width: 40px; height: 40px;
    border-radius: 999px;
    border: 1px solid #E7EAF2;
    background: transparent;
    cursor: pointer;
    color: #1B2B5A;
    margin-left: auto;
}
.ec-mobile-toggle i { font-size: 18px; }

/* Mobile drawer */
.ec-mobile-menu {
    display: none;
    flex-direction: column;
    padding: 12px 16px 16px;
    border-top: 1px solid #E7EAF2;
    background: #fff;
    max-height: calc(100vh - 64px);
    overflow-y: auto;
}
.ec-mobile-menu.open { display: flex; }
.ec-mobile-menu .ec-nav-link {
    display: block;
    padding: 10px 12px;
    font-size: 15px;
    font-weight: 500;
    color: #1B2B5A;
    text-decoration: none;
    border-radius: 12px;
    transition: background 0.15s;
}
.ec-mobile-menu .ec-nav-link:hover { background: #F8FAFC; }
.ec-mobile-menu .ec-action-btn {
    justify-content: flex-start;
    padding: 10px 12px;
    width: 100%;
    border-radius: 12px;
}
.ec-mobile-label {
    padding: 10px 12px 4px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #8A93AD;
}
.ec-mobile-sep { height: 1px; background: #E7EAF2; margin: 8px 0; }

/* Carrito mobile: oculto en desktop */
.ec-cart-mobile { display: none; }

@media (max-width: 991px) {
    .ec-nav, .ec-actions { display: none !important; }
    .ec-mobile-toggle { display: flex; margin-left: 0; }
    .ec-cart-mobile { display: flex; margin-left: auto; font-size: 17px; }
    .ec-header-inner { gap: 12px; height: 64px; }
}

/* ── OFFCANVAS: CARRITO ─────────────────────────────── */
#offcanvasCart .offcanvas-header {
    padding: 20px 20px 16px;
    border-bottom: 1px solid #E7EAF2;
}
#offcanvasCart .offcanvas-header h5 {
    font-size: 16px;
    margin: 0;
}
#offcanvasCart .offcanvas-body { padding: 20px; }

/* ── OFFCANVAS: BÚSQUEDA ────────────────────────────── */
#offcanvasSearch .offcanvas-header {
    border-bottom: 1px solid #E7EAF2;
    padding: 18px 20px;
}
#offcanvasSearch .offcanvas-body { padding: 24px 20px; }
#offcanvasSearch .form-control {
    border-radius: 999px 0 0 999px;
    border: 1px solid #E7EAF2;
    font-size: 15px;
    padding: 10px 20px;
}
</style>


<header class="ec-header" id="ec-header">
    <div class="ec-header-inner" style="margin-top: 10px;">
        {{-- Logo --}}
        {{-- Logotipo maestro Sommy: wordmark serif azul noche + pluma (usar siempre tal cual) --}}
        <a href="{{ url('/') }}" class="ec-logo" style="flex-shrink:0;">
            {{-- Header blanco: logo completo con la pluma de colores · Nav transparente: solo letras blancas --}}
            <img src="{{ asset('imagenes/marca/sommy-logo-magia.png') }}" alt="Sommy" class="logo-color">
            <img src="{{ asset('imagenes/marca/sommy-logo-blanco-texto.png') }}" alt="Sommy" class="logo-blanco" style="margin-top:7px;">
        </a>
        {{-- Navegación desktop --}}
        <ul class="ec-nav">
            <li>
                <a class="ec-nav-link {{ request()->is('/') ? 'is-active' : '' }}" href="{{ url('/') }}">Inicio</a>
            </li>
            <li class="ec-nav-drop">
                <a class="ec-nav-link {{ request()->is('categoria/*') ? 'is-active' : '' }}" href="{{ url('/#categorias') }}">
                    Categorías <i class="fa-solid fa-chevron-down" style="font-size:10px;margin-left:4px;"></i>
                </a>
                <div class="ec-nav-drop-panel">
                    <a class="ec-nav-drop-item {{ request()->is('productos') ? 'is-active' : '' }}" href="{{ url('/productos') }}" style="font-weight:600;">
                        Ver todo el catálogo
                    </a>
                    <div style="height:1px;background:#E7EAF2;margin:6px 8px;"></div>
                    @foreach ($getCategoryLimit as $catLimit)
                    <a class="ec-nav-drop-item {{ request()->is('categoria/' . $catLimit->slug) ? 'is-active' : '' }}" href="{{ url('categoria/' . $catLimit->slug) }}">
                        {{ $catLimit->nombre }}
                    </a>
                    @endforeach
                </div>
            </li>
            <li>
                <a class="ec-nav-link {{ request()->is('contacto') ? 'is-active' : '' }}" href="{{ url('/contacto') }}">Contacto</a>
            </li>
        </ul>

        {{-- Acciones desktop --}}
        <div class="ec-actions">
            @auth
            <a class="ec-action-btn" href="{{ url('/dashboard') }}">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span>Panel</span>
            </a>
            @elseif(Auth::guard('cliente')->check())
            <a class="ec-action-btn" href="{{ url('/cuenta/pedidos') }}" title="Seguí tus pedidos">
                <i class="fa-solid fa-box"></i>
                <span>Mis pedidos</span>
            </a>
            <span class="ec-action-btn" style="cursor:default;">
                <i class="fa-solid fa-user-check"></i>
                <span>{{ explode(' ', trim(Auth::guard('cliente')->user()->nombre))[0] }}</span>
            </span>
            <a class="ec-action-btn" href="{{ url('/cuenta/salir') }}" title="Cerrar sesión">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
            @else
            <a class="ec-action-btn" href="{{ url('/cuenta/login') }}">
                <i class="fa-solid fa-user"></i>
                <span>Ingresar</span>
            </a>
            @endauth

            <button class="ec-action-btn ec-cart-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-label="Carrito">
                <i class="fa-solid fa-bag-shopping"></i>
                <span class="ec-cart-count show-total-header-products-added">0</span>
            </button>
        </div>

        {{-- Carrito mobile (visible solo en pantallas chicas, junto a la hamburguesa) --}}
        <button class="ec-action-btn ec-cart-btn ec-cart-mobile" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-label="Carrito">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="ec-cart-count show-total-header-products-added">0</span>
        </button>

        {{-- Toggle mobile --}}
        <button class="ec-mobile-toggle" id="ec-mobile-toggle" aria-label="Menú">
            <i class="fa fa-bars"></i>
        </button>
    </div>

    {{-- Menú mobile --}}
    <div class="ec-mobile-menu" id="ec-mobile-menu">
        <a class="ec-nav-link" href="{{ url('/') }}">Inicio</a>
        <a class="ec-nav-link" href="{{ url('/productos') }}">Todos los productos</a>
        <div class="ec-mobile-label">Categorías</div>
        @foreach ($getCategoryLimit as $catLimit)
        <a class="ec-nav-link" href="{{ url('categoria/' . $catLimit->slug) }}">
            {{ $catLimit->nombre }}
        </a>
        @endforeach
        <div class="ec-mobile-sep"></div>
        <a class="ec-nav-link" href="{{ url('/contacto') }}">Contacto</a>
        <div class="ec-mobile-sep"></div>
        @auth
        <a class="ec-action-btn" href="{{ url('/dashboard') }}">
            <i class="fa-solid fa-screwdriver-wrench"></i> Panel
        </a>
        @elseif(Auth::guard('cliente')->check())
        <a class="ec-action-btn" href="{{ url('/cuenta/pedidos') }}">
            <i class="fa-solid fa-box"></i> Mis pedidos
        </a>
        <a class="ec-action-btn" href="{{ url('/cuenta/salir') }}">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión ({{ explode(' ', trim(Auth::guard('cliente')->user()->nombre))[0] }})
        </a>
        @else
        <a class="ec-action-btn" href="{{ url('/cuenta/login') }}">
            <i class="fa-solid fa-user"></i> Ingresar
        </a>
        @endauth
        <button class="ec-action-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
            <i class="fa-solid fa-bag-shopping"></i> Carrito
            <span class="show-total-header-products-added" style="background:#2563EB;color:#fff;border-radius:99px;padding:1px 8px;font-size:12px;font-weight:700;">0</span>
        </button>
    </div>
</header>

{{-- Offcanvas Carrito --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
    <div class="offcanvas-header">
        <h5 id="offcanvasCartLabel"><i class="fa-solid fa-bag-shopping me-2" style="color:#1B2B5A;"></i>Tu carrito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <div id="div-show-empty-cart">
            <div class="sommy-cart-empty">
                <div class="icon"><i class="fa-solid fa-feather" aria-hidden="true"></i></div>
                <p class="title">Tu carrito está liviano</p>
                <p class="hint">Todavía no agregaste productos.</p>
            </div>
        </div>
        <ul id="list-shopping-cart" class="list-group list-group-flush mb-3"></ul>
        <div id="div-content-button-finish"></div>
        <div id="cart-related-products"></div>
    </div>
</div>

{{-- Offcanvas Búsqueda --}}
<div class="offcanvas offcanvas-top" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel" style="height:auto;">
    <div class="offcanvas-header">
        <h5 id="offcanvasSearchLabel" style="font-size:15px;font-weight:500;color:#1B2B5A;">Buscar productos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <form action="{{ url('/buscar') }}" method="GET" class="d-flex" style="max-width:600px;margin:0 auto;">
            <input class="form-control" type="search" name="q" placeholder="¿Qué estás buscando?" aria-label="Buscar">
            <button class="btn" type="submit" style="border-radius:0 999px 999px 0;background:#1B2B5A;color:#fff;padding:10px 22px;">
                <i class="fa fa-search"></i>
            </button>
        </form>
    </div>
</div>

@section('scriptEcommerce')
<script src="{{ assetv('js/ecommerce/main_ecommerce.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    fnShowListCartProduct();

    // Scroll shadow
    var header = document.getElementById('ec-header');
    window.addEventListener('scroll', function () {
        header.classList.toggle('scrolled', window.scrollY > 10);
    });

    // Mobile toggle
    var toggle = document.getElementById('ec-mobile-toggle');
    var menu   = document.getElementById('ec-mobile-menu');
    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            menu.classList.toggle('open');
        });
    }
});
</script>
@endsection
