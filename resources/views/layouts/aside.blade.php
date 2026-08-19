@php
    use Illuminate\Support\Str;
    $resp        = request()->path();
    $isDashboard = $resp === 'dashboard';
    $isVentas    = Str::startsWith($resp, ['ventas', 'presupuestos', 'clientes', 'devoluciones', 'quote', 'showlistcusto', 'get-data-client']);
    $isCompras   = Str::startsWith($resp, ['compras', 'compras/proveedor', 'showproveedor', 'saveproveedor', 'provider-list', 'finanzas/gastos', 'finanzas/gasto-categorias']);
    $isInventario = Str::startsWith($resp, ['almacen', 'sucursal', 'articulo', 'variacion', 'color', 'price-list', 'productos-con-stock', 'stock/']);
    $isCaja      = Str::startsWith($resp, ['caja', 'cuentas', 'finanzas']) && !Str::startsWith($resp, ['finanzas/gastos', 'finanzas/gasto-categorias']);
    $isWhatsapp  = Str::startsWith($resp, ['whatsapp']);
    $isReportes  = Str::startsWith($resp, ['graph', 'inventory', 'pdfinventario']);
    $isEcommerce = Str::startsWith($resp, ['orders', 'banner', 'zonas-envio', 'Ecommerce', 'ventas/ecommerce', 'ventas/ordenes', 'publicaciones', 'revendedores-panel', 'envios']);
    $isSistema   = Str::startsWith($resp, ['config', 'admin/', 'chatbot', 'documentation', 'training-videos']);
@endphp

<style>
    /* ── BASE NAV BAR ─────────────────────────────── */
    .dg-nav-bar {
        background: #ffffff !important;
        border-bottom: 1px solid #e5e7eb;
        height: 56px;
        display: flex !important;
        align-items: center;
        padding: 0;
        position: sticky;
        top: 75px;
        z-index: 1029;
        width: 100% !important;
        margin-left: 0 !important;
        left: 0;
        right: 0;
    }

    .dg-nav-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        padding: 0 3%;
    }

    /* ── LISTA DE ITEMS ───────────────────────────── */
    .dg-menu-list {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
        gap: 4px;
        flex-wrap: nowrap;
        height: 100%;
    }

    .dg-menu-item {
        position: relative;
        height: 100%;
        display: flex;
        align-items: center;
    }

    /* ── LINK PRINCIPAL ───────────────────────────── */
    .dg-menu-link {
        color: #334155 !important;
        padding: 8px 15px;
        height: auto;
        font-size: 15.5px;
        font-weight: 500;
        text-decoration: none !important;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        border-radius: 9px;
        transition: background 0.15s ease, color 0.15s ease;
        white-space: nowrap;
        line-height: 1;
    }

    .dg-menu-link i.main-icon {
        font-size: 17px;
        color: #64748b;
        transition: color 0.15s;
        flex-shrink: 0;
    }

    /* Flecha tendencia egreso (FA5: chart-line volteado) */
    .dg-menu-link i.icon-egreso {
        display: inline-block;
        transform: scaleY(-1);
    }

    .dg-menu-link i.chevron {
        font-size: 10px;
        color: #94a3b8;
        margin-left: 1px;
        transition: color 0.15s, transform 0.2s;
    }

    /* ── HOVER ────────────────────────────────────── */
    .dg-menu-link:hover {
        background: #f1f5f9;
        color: #1e293b !important;
    }
    .dg-menu-link:hover i.main-icon { color: #475569; }
    .dg-menu-link:hover i.chevron   { color: #64748b; }

    /* ── ACTIVE STATE (píldora Brisa Suave Sommy) ─── */
    .dg-menu-link.dg-active {
        background: #E0F2FE;
        color: #1B2B5A !important;
        font-weight: 600;
    }
    .dg-menu-link.dg-active i.main-icon { color: #2563EB; }
    .dg-menu-link.dg-active i.chevron   { color: #0EA5E9; }

    /* ── DROPDOWN ─────────────────────────────────── */
    .dg-nav-dropdown {
        position: absolute;
        top: calc(100% - 2px);
        left: 0;
        background: #ffffff;
        min-width: 224px;
        border-radius: 12px;
        box-shadow: 0 12px 40px -6px rgba(15,23,42,0.16), 0 4px 12px -6px rgba(15,23,42,0.08);
        border: 1px solid #e8edf4;
        display: none;
        z-index: 2000;
        padding: 6px;
    }

    .dg-menu-item:hover > .dg-nav-dropdown {
        display: block;
        animation: dgFadeIn 0.15s ease-out;
    }

    @keyframes dgFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0);   }
    }

    /* ── DROP ITEMS ───────────────────────────────── */
    .dg-drop-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        color: #334155 !important;
        text-decoration: none !important;
        font-size: 14px;
        font-weight: 500;
        border-radius: 8px;
        transition: background 0.15s, color 0.15s;
    }
    .dg-drop-item i { font-size: 14px; color: #64748b; flex-shrink: 0; width: 16px; text-align: center; }
    .dg-drop-item:hover { background: #F8FAFC; color: #1B2B5A !important; }
    .dg-drop-item:hover i { color: #2563EB; }
    .dg-drop-item.dg-drop-active { background: #E0F2FE; color: #1B2B5A !important; font-weight: 600; }
    .dg-drop-item.dg-drop-active i { color: #2563EB; }

    /* Quick-action item */
    .dg-drop-item.dg-drop-primary {
        background: #1B2B5A;
        color: #fff !important;
        margin-bottom: 4px;
        font-weight: 600;
    }
    .dg-drop-item.dg-drop-primary i { color: #E0F2FE !important; }
    .dg-drop-item.dg-drop-primary:hover { background: #2563EB; color: #fff !important; }

    .dg-drop-sep { height: 1px; background: #f1f5f9; margin: 4px 6px; }

    .dg-drop-label {
        padding: 6px 12px 3px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #9ca3af;
    }

    /* ── BOTÓN FOOTER (Planes) ────────────────────── */
    .btn-suscribe-pill {
        background: #1B2B5A;
        color: #ffffff !important;
        padding: 8px 18px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none !important;
        transition: background 0.2s, transform 0.1s;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .btn-suscribe-pill:hover { background: #2563EB; }

    /* ── BOTÓN ACADEMIA (dropdown) ───────────────── */
    .dg-drop-item.btn-premium-academy {
        background: linear-gradient(-45deg, #1B2B5A, #2563EB, #0EA5E9, #1B2B5A);
        background-size: 400% 400%;
        animation: premiumGradientMove 8s ease infinite;
        color: #ffffff !important;
        font-weight: 600;
    }
    .dg-drop-item.btn-premium-academy i { color: #E0F2FE !important; }

    @keyframes premiumGradientMove {
        0%   { background-position: 0% 50%; }
        50%  { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* ── HAMBURGUESA MOBILE ─────────────────────── */
    .dg-mobile-toggle {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        background: #1B2B5A;
        color: #ffffff;
        border: none;
        padding: 10px 14px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .dg-mobile-toggle:hover { background: #2563EB; }
    .dg-mobile-toggle .icon-close { display: none; }

    /* ── RESPONSIVE ─────────────────────────────── */
    @media (max-width: 1350px) {
        .dg-nav-container { padding: 0 1.5%; }
        .dg-menu-link { padding: 8px 11px; font-size: 14.5px; gap: 7px; }
        .dg-menu-link i.main-icon { font-size: 16px; }
        .dg-menu-list { gap: 2px; }
    }

    @media (max-width: 992px) {
        .dg-nav-bar { height: auto; padding: 8px; position: relative; top: 0; }
        .dg-nav-container { flex-direction: column; align-items: center; height: auto; padding: 0 8px; }
        .dg-menu-list { flex-direction: column; width: 100%; height: auto; align-items: center; gap: 0; }
        .dg-nav-bar-footer { justify-content: center; padding: 8px 0; flex-wrap: wrap; gap: 8px; }
        .dg-mobile-toggle { display: flex; }
        #dg-main-nav.dg-expanded .dg-mobile-toggle .icon-open  { display: none; }
        #dg-main-nav.dg-expanded .dg-mobile-toggle .icon-close { display: inline-block; }
        #dg-main-nav .dg-menu-list,
        #dg-main-nav .dg-nav-bar-footer { display: none; }
        #dg-main-nav.dg-expanded .dg-menu-list  { display: flex; margin-top: 6px; }
        #dg-main-nav.dg-expanded .dg-nav-bar-footer { display: flex; margin-top: 8px; }
        #dg-main-nav .dg-menu-item { width: 100%; height: auto; display: flex; flex-direction: column; align-items: center; }
        #dg-main-nav .dg-menu-link {
            width: 100%; max-width: 360px; height: auto; padding: 12px 16px;
            border-radius: 8px;
            justify-content: center; font-size: 15.5px; margin: 0 auto;
        }
        #dg-main-nav .dg-menu-link:hover { background: #f1f5f9; }
        #dg-main-nav .dg-menu-link.dg-active { background: #E0F2FE; }
        #dg-main-nav .dg-menu-link i.chevron { margin-left: auto; }
        #dg-main-nav .dg-menu-item.dg-open > .dg-menu-link i.chevron { transform: rotate(180deg); }
        #dg-main-nav .dg-nav-dropdown { display: none !important; position: static; width: 100%; max-width: 360px; box-shadow: none; border: none; background: #f8fafc; border-radius: 8px; margin-top: 2px; padding: 4px 0; }
        #dg-main-nav .dg-menu-item.dg-open > .dg-nav-dropdown { display: block !important; }
        #dg-main-nav .dg-nav-dropdown .dg-drop-item { padding: 10px 16px; }
        #dg-main-nav .dg-nav-dropdown .dg-drop-item.dg-drop-primary { margin: 4px 8px; border-radius: 7px; }
        .btn-suscribe-pill { justify-content: center; }
    }
</style>

<nav class="dg-nav-bar" id="dg-main-nav">
    <div class="dg-nav-container text-center">

        {{-- HAMBURGUESA MOBILE --}}
        <button type="button" class="dg-mobile-toggle" id="dg-mobile-toggle" aria-label="Menú">
            <i class="fas fa-bars icon-open"></i>
            <i class="fas fa-times icon-close"></i>
            <span>Navegación</span>
        </button>

        <ul class="dg-menu-list">

            {{-- INICIO --}}
            <li class="dg-menu-item">
                <a href="{{ url('dashboard') }}" class="dg-menu-link {{ $isDashboard ? 'dg-active' : '' }}">
                    <i class="fas fa-home main-icon"></i> Inicio
                </a>
            </li>

            {{-- INGRESOS (antes Ventas) --}}
            @if(Auth::user()->can('haveaccess','ventas.index') || Auth::user()->can('haveaccess','presupuestos.index') || Auth::user()->can('haveaccess','ventas_cliente.index'))
            <li class="dg-menu-item">
                <a class="dg-menu-link {{ $isVentas ? 'dg-active' : '' }}">
                    <i class="fas fa-hand-holding-usd main-icon"></i> Ventas
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="dg-nav-dropdown">
                    @can('haveaccess','ventas_venta.index')
                    <a href="{{ route('ventas.create') }}" class="dg-drop-item dg-drop-primary">
                        <i class="fas fa-plus-circle"></i> Nueva Venta
                    </a>
                    @endcan
                    @can('haveaccess','ventas.index')
                    <a href="{{ route('ventas.index') }}" class="dg-drop-item {{ Str::startsWith($resp,'ventas') && !Str::startsWith($resp,'ventas/ecommerce') && !Str::startsWith($resp,'ventas/create') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-list-alt"></i> Historial de Ventas
                    </a>
                    @endcan
                    @can('haveaccess','admin.index')
                    <a href="{{ url('orders/order') }}" class="dg-drop-item {{ Str::startsWith($resp,'orders') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-shopping-bag"></i> Pedidos
                    </a>
                    @endcan
                    @can('haveaccess','presupuestos.index')
                    <a href="{{ route('presupuestos.index') }}" class="dg-drop-item {{ Str::startsWith($resp,'presupuestos') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-file-invoice"></i> Presupuestos
                    </a>
                    @endcan
                    @can('haveaccess','ventas_cliente.index')
                    <div class="dg-drop-sep"></div>
                    <a href="{{ url('clientes') }}" class="dg-drop-item {{ Str::startsWith($resp,'clientes') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                    <a href="{{ url('cc') }}" class="dg-drop-item {{ Str::startsWith($resp,'cc') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i> Cuentas Corrientes
                    </a>
                    @endcan
                    @can('haveaccess','devolucion.index')
                    <div class="dg-drop-sep"></div>
                    <a href="{{ route('devoluciones.index') }}" class="dg-drop-item {{ Str::startsWith($resp,'devoluciones') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-undo-alt"></i> Devoluciones
                    </a>
                    @endcan
                </div>
            </li>
            @endif

            {{-- EGRESOS (antes Compras) --}}
            @if(Auth::user()->can('haveaccess','compras.index') || Auth::user()->can('haveaccess','compras_proveedor.index'))
            <li class="dg-menu-item">
                <a class="dg-menu-link {{ $isCompras ? 'dg-active' : '' }}">
                    <i class="fas fa-dolly main-icon"></i> Compras
                    <i class="fas fa-chevron-down chevron"></i>
                </a>

                <div class="dg-nav-dropdown">
                    @can('haveaccess','compras_entrada.index')
                    <a href="{{ route('compras.create') }}" class="dg-drop-item dg-drop-primary">
                        <i class="fas fa-plus-circle"></i> Nueva Compra
                    </a>
                    @endcan
                    @can('haveaccess','compras.index')
                    <a href="{{ route('compras.index') }}" class="dg-drop-item {{ Str::startsWith($resp,'compras') && !Str::startsWith($resp,'compras/proveedor') && !Str::startsWith($resp,'compras/create') && !Str::startsWith($resp,'compras/pedidos') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-list-alt"></i> Historial de Compras
                    </a>
                    <a href="{{ route('pedidos-compra.index') }}" class="dg-drop-item {{ Str::startsWith($resp,'compras/pedidos') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-file-signature"></i> Generar pedido
                    </a>
                    @endcan
                                        @can('haveaccess','finanzas.gastos.index')
                    <a href="{{ url('finanzas/gastos') }}" class="dg-drop-item {{ Str::startsWith($resp,'finanzas/gastos') || Str::startsWith($resp,'finanzas/gasto-categorias') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-receipt"></i> Gastos
                    </a>
                    @endcan
                    
                    @can('haveaccess','compras_proveedor.index')
                    <div class="dg-drop-sep"></div>
                    <a href="{{ url('compras/proveedor') }}" class="dg-drop-item {{ Str::startsWith($resp,'compras/proveedor') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-truck"></i> Proveedores
                    </a>
                    @endcan
                    @can('haveaccess','compras.cxp.index')
                    <a href="{{ url('finanzas/cxp') }}" class="dg-drop-item {{ Str::startsWith($resp,'finanzas/cxp') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-file-invoice"></i> Cuentas por Pagar
                    </a>
                    @endcan
                </div>
            </li>
            @endif

            {{-- PRODUCTOS (antes Inventario) --}}
            @if(Auth::user()->can('haveaccess','almacen.index') || Auth::user()->can('haveaccess','almacen_articulo.index'))
            <li class="dg-menu-item">
                <a class="dg-menu-link {{ $isInventario ? 'dg-active' : '' }}">
                    <i class="fas fa-cube main-icon"></i> Productos
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="dg-nav-dropdown" style="min-width:240px">
                    @can('haveaccess','almacen_articulo.index')
                    <div class="dg-drop-label">Catálogo</div>
                    <a href="{{ url('almacen/articulo') }}" class="dg-drop-item {{ Str::startsWith($resp,'almacen/articulo') || Str::startsWith($resp,'articulo/') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-box"></i> Artículos y variantes
                    </a>
                    <div class="dg-drop-sep"></div>
                    <div class="dg-drop-label">Precios & Stock</div>
                    <a href="{{ route('pricelists.index') }}" class="dg-drop-item {{ Str::startsWith($resp,'almacen/pricelists') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-tag"></i> Listas de Precios
                    </a>
                    <a href="{{ url('sucursal') }}" class="dg-drop-item {{ Str::startsWith($resp,'sucursal') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-store-alt"></i> Sucursales & Stock
                    </a>
                    <div class="dg-drop-sep"></div>
                    @endcan
                </div>
            </li>
            @endif

            {{-- CUENTAS (antes Caja) --}}
            @can('haveaccess','caja.index')
            <li class="dg-menu-item">
                <a class="dg-menu-link {{ $isCaja ? 'dg-active' : '' }}">
                    <i class="fas fa-wallet main-icon"></i> Finanzas
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="dg-nav-dropdown">
                    <div class="dg-drop-label">Cuentas</div>
                    <a href="{{ url('cuentas/gestor') }}" class="dg-drop-item {{ $resp === 'cuentas/gestor' ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-wallet"></i> Gestor de Cuentas
                    </a>
                    <div class="dg-drop-sep"></div>
                    <div class="dg-drop-label">Finanzas</div>
                    @can('haveaccess','finanzas.dashboard')
                    <a href="{{ url('finanzas') }}" class="dg-drop-item {{ $resp === 'finanzas' ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-chart-line"></i> Tablero
                    </a>
                    @endcan
                    @can('haveaccess','finanzas.cobranzas.index')
                    <a href="{{ route('finanzas.cobranzas.index') }}" class="dg-drop-item {{ Str::startsWith($resp,'finanzas/cobranzas') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-robot"></i> Cobranzas (IA)
                    </a>
                    @endcan
                </div>
            </li>
            @endcan

            {{-- WHATSAPP / CRM --}}
            @can('haveaccess','whatsapp.index')
            <li class="dg-menu-item">
                <a class="dg-menu-link {{ $isWhatsapp ? 'dg-active' : '' }}">
                    <i class="fab fa-whatsapp main-icon" style="color:#1EBE5A"></i> WhatsApp
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="dg-nav-dropdown">
                    <a href="{{ url('whatsapp') }}" class="dg-drop-item dg-drop-primary">
                        <i class="fas fa-inbox"></i> Bandeja de atención
                    </a>
                    <a href="{{ url('whatsapp/tablero') }}" class="dg-drop-item {{ $resp === 'whatsapp/tablero' ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-columns"></i> Tablero de atención
                    </a>
                    @can('haveaccess','agents.index')
                    <a href="{{ url('whatsapp/agents') }}" class="dg-drop-item {{ Str::startsWith($resp,'whatsapp/agents') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-robot"></i> Agentes de venta IA
                    </a>
                    @endcan
                    @can('haveaccess','whatsapp.templates')
                    <a href="{{ url('whatsapp/templates') }}" class="dg-drop-item {{ Str::startsWith($resp,'whatsapp/templates') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-file-alt"></i> Plantillas de mensajes
                    </a>
                    @endcan
                </div>
            </li>
            @endcan

            {{-- INFORMES (reportes) --}}
            @can('haveaccess','ventas.index')
            <li class="dg-menu-item">
                <a href="{{ route('graph') }}" class="dg-menu-link {{ $isReportes ? 'dg-active' : '' }}">
                    <i class="fas fa-chart-pie main-icon"></i> Informes
                </a>
            </li>
            @endcan


            {{-- TIENDA (Ecommerce) --}}
            @can('haveaccess','admin.index')
            <li class="dg-menu-item">
                <a class="dg-menu-link {{ $isEcommerce ? 'dg-active' : '' }}">
                    <i class="fas fa-bullhorn main-icon"></i> Comercialización
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="dg-nav-dropdown">
                    <a href="{{ url('publicaciones') }}" class="dg-drop-item {{ Str::startsWith($resp,'publicaciones') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-bullhorn"></i> Estudio de Publicaciones
                    </a>
                    <div class="dg-drop-sep"></div>
                    <div class="dg-drop-label">Red de venta</div>
                    <a href="{{ url('revendedores-panel') }}" class="dg-drop-item {{ Str::startsWith($resp,'revendedores-panel') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-handshake"></i> Revendedores
                    </a>
                    <div class="dg-drop-sep"></div>
                    <a href="{{ url('banner') }}" class="dg-drop-item {{ $resp === 'banner' ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-image"></i> Banner Principal
                    </a>
                </div>
            </li>
            @endcan

            {{-- USUARIOS / SISTEMA --}}
            <li class="dg-menu-item">
                <a class="dg-menu-link {{ $isSistema ? 'dg-active' : '' }}">
                    <i class="fas fa-building main-icon"></i> Administración
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="dg-nav-dropdown">
                    @can('haveaccess','configuracion.index')
                    <div class="dg-drop-label">Empresa</div>
                    <a href="{{ url('config') }}" class="dg-drop-item {{ $resp === 'config' ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-building"></i> Datos de Empresa
                    </a>
                    <a href="{{ url('admin/integraciones') }}" class="dg-drop-item {{ Str::startsWith($resp,'admin/integraciones') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-plug"></i> Integraciones
                    </a>
                    @endcan
                    @can('haveaccess','admin.index')
                    <div class="dg-drop-sep"></div>
                    <div class="dg-drop-label">Gestión</div>
                    <a href="{{ url('admin/user') }}" class="dg-drop-item {{ Str::startsWith($resp,'admin/user') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-user-cog"></i> Gestión de Usuarios
                    </a>
                    <a href="{{ url('admin/role') }}" class="dg-drop-item {{ Str::startsWith($resp,'admin/role') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-shield-alt"></i> Roles y Permisos
                    </a>
                    @endcan
                    <div class="dg-drop-sep"></div>
                    <div class="dg-drop-label">Ayuda</div>
                    <a href="{{ url('procesos') }}" class="dg-drop-item {{ Str::startsWith($resp,'procesos') ? 'dg-drop-active' : '' }}">
                        <i class="fas fa-route"></i> Procesos de trabajo
                    </a>
                    <div class="dg-drop-sep"></div>
                    <a href="{{ route('tenant.training-videos.index') }}" class="dg-drop-item btn-premium-academy">
                        <i class="fas fa-crown"></i> Academia FacturARG
                    </a>
                </div>
            </li>

        </ul>



    </div>
</nav>

<script>
(function () {
    var nav    = document.getElementById('dg-main-nav');
    if (!nav) return;

    var toggle = document.getElementById('dg-mobile-toggle');
    var items  = nav.querySelectorAll('.dg-menu-item');

    function isMobile() { return window.innerWidth <= 992; }

    if (toggle) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('dg-expanded');
        });
    }

    items.forEach(function (item) {
        var link     = item.querySelector(':scope > .dg-menu-link');
        var dropdown = item.querySelector(':scope > .dg-nav-dropdown');
        if (!link || !dropdown) return;

        link.addEventListener('click', function (e) {
            if (!isMobile()) return;
            var href = link.getAttribute('href');
            if (!href || href === '#') e.preventDefault();
            items.forEach(function (other) {
                if (other !== item) other.classList.remove('dg-open');
            });
            item.classList.toggle('dg-open');
        });
    });

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            nav.classList.remove('dg-expanded');
            items.forEach(function (i) { i.classList.remove('dg-open'); });
        }
    });

    document.addEventListener('click', function (e) {
        if (!isMobile() && !nav.contains(e.target)) {
            items.forEach(function (i) { i.classList.remove('dg-open'); });
        }
    });
})();
</script>
{{-- Burbuja flotante del Chat de Reportes (analista IA) --}}
@can('haveaccess','reportes.chat.index')
@if(!Str::startsWith($resp, 'reportes/chat'))
<style>
    .dg-chat-burbuja {
        position: fixed; bottom: 26px; right: 26px; z-index: 2500;
        width: 58px; height: 58px; border-radius: 999px;
        background: linear-gradient(135deg, #1B2B5A, #2563EB);
        color: #fff !important; display: flex; align-items: center; justify-content: center;
        font-size: 23px; text-decoration: none !important;
        box-shadow: 0 12px 30px rgba(27, 43, 90, .40);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .dg-chat-burbuja:hover { transform: scale(1.08); box-shadow: 0 16px 38px rgba(37, 99, 235, .45); color: #fff; }
    .dg-chat-burbuja .puntito {
        position: absolute; top: 3px; right: 5px; width: 11px; height: 11px;
        border-radius: 999px; background: #0EA5E9; border: 2px solid #fff;
        animation: dgChatLate 2s infinite;
    }
    @keyframes dgChatLate { 0%,100% { transform: scale(1); } 50% { transform: scale(1.35); } }
    .dg-chat-burbuja .globito {
        position: absolute; right: 68px; top: 50%; transform: translateY(-50%);
        background: #1B2B5A; color: #fff; font-family: 'Poppins', sans-serif;
        font-size: 12px; font-weight: 500; white-space: nowrap;
        padding: 7px 14px; border-radius: 999px; opacity: 0;
        pointer-events: none; transition: opacity .15s ease;
    }
    .dg-chat-burbuja:hover .globito { opacity: 1; }
    @media (max-width: 767px) { .dg-chat-burbuja { bottom: 18px; right: 16px; } }
</style>
<a href="javascript:void(0)" class="dg-chat-burbuja" id="dgChatBurbuja" title="Chat de Reportes con IA">
    <i class="fas fa-robot" id="dgChatIcono"></i>
    <span class="puntito"></span>
    <span class="globito">Preguntale a tus números 📊</span>
</a>

{{-- Panel flotante del chat (se abre sobre la pantalla actual) --}}
<div id="dgChatPanel" style="display:none;position:fixed;bottom:96px;right:26px;width:400px;max-width:94vw;height:560px;max-height:calc(100vh - 130px);z-index:2499;background:#fff;border-radius:18px;box-shadow:0 24px 70px rgba(27,43,90,.45);overflow:hidden;flex-direction:column;">
    <div style="background:linear-gradient(135deg,#1B2B5A,#2563EB);color:#fff;padding:11px 16px;display:flex;align-items:center;justify-content:space-between;font-family:'Poppins',sans-serif;">
        <b style="font-size:13.5px;">🤖 Analista de Reportes</b>
        <span>
            <a href="{{ route('reportes.chat.index') }}" title="Abrir en pantalla completa" style="color:#C7D0E8;font-size:12px;margin-right:10px;text-decoration:none;"><i class="fas fa-expand-alt"></i></a>
            <button onclick="dgChatCerrar()" style="border:none;background:none;color:#fff;font-size:15px;cursor:pointer;">✕</button>
        </span>
    </div>
    <iframe id="dgChatFrame" data-src="{{ route('reportes.chat.index') }}?embed=1" style="border:none;width:100%;flex:1;"></iframe>
</div>

<script>
(function () {
    var burbuja = document.getElementById('dgChatBurbuja');
    var panel = document.getElementById('dgChatPanel');
    var frame = document.getElementById('dgChatFrame');
    var icono = document.getElementById('dgChatIcono');

    burbuja.addEventListener('click', function () {
        var abierto = panel.style.display !== 'none';
        if (abierto) { dgChatCerrar(); return; }
        if (!frame.src) { frame.src = frame.dataset.src; } // carga perezosa la primera vez
        panel.style.display = 'flex';
        icono.className = 'fas fa-chevron-down';
    });

    window.dgChatCerrar = function () {
        panel.style.display = 'none';
        icono.className = 'fas fa-robot';
    };
})();
</script>
@endif
@endcan
