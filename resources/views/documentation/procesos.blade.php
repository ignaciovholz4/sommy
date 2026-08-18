@extends('layouts.admin')

@section('title', 'Procesos de trabajo')

@section('contenido')
<style>
    .pro-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1250px; margin: 0 auto; }
    .pro-title { font-size: 22px; font-weight: 600; margin-bottom: 2px; }
    .pro-sub { font-size: 13.5px; color: #6E7A96; font-weight: 300; margin-bottom: 24px; }

    .pro-card {
        background: #fff;
        border: 1px solid #E7EAF2;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.06);
        padding: 20px 24px;
        margin-bottom: 18px;
    }
    .pro-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 4px; flex-wrap: wrap; }
    .pro-card-head .ic {
        width: 38px; height: 38px; border-radius: 12px;
        background: #E0F2FE; color: #1B2B5A;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .pro-card-head h2 { font-size: 16.5px; font-weight: 600; margin: 0; }
    .pro-quien {
        font-size: 11px; font-weight: 500; letter-spacing: .06em; text-transform: uppercase;
        color: #2563EB; background: #F8FAFC; border: 1px solid #E7EAF2;
        border-radius: 999px; padding: 4px 12px;
    }
    .pro-desc { font-size: 13px; color: #6E7A96; font-weight: 300; margin: 4px 0 16px 50px; }

    /* Diagrama horizontal */
    .pro-flujo {
        display: flex;
        align-items: stretch;
        gap: 0;
        overflow-x: auto;
        padding: 4px 2px 10px;
    }
    .pro-paso {
        min-width: 150px;
        max-width: 180px;
        flex: 1;
        text-align: center;
        padding: 12px 10px;
        border: 1px solid #E7EAF2;
        border-radius: 12px;
        background: #F8FAFC;
        position: relative;
    }
    .pro-paso .num {
        width: 24px; height: 24px; border-radius: 999px;
        background: #1B2B5A; color: #fff;
        font-size: 12px; font-weight: 600;
        display: inline-flex; align-items: center; justify-content: center;
        margin-bottom: 7px;
    }
    .pro-paso .t { font-size: 12.5px; font-weight: 600; color: #1B2B5A; line-height: 1.3; margin-bottom: 3px; }
    .pro-paso .d { font-size: 11.5px; font-weight: 300; color: #6E7A96; line-height: 1.45; }
    .pro-paso.fin { background: #E0F2FE; border-color: #bae2f8; }

    .pro-flecha {
        display: flex; align-items: center;
        color: #8A93AD; font-size: 14px;
        padding: 0 8px; flex-shrink: 0;
    }

    .pro-nota {
        margin: 12px 0 0 50px;
        font-size: 12.5px;
        color: #47536F;
        background: #F8FAFC;
        border-left: 3px solid #0EA5E9;
        border-radius: 0 10px 10px 0;
        padding: 8px 14px;
    }
    .pro-nota strong { font-weight: 600; }

    /* Checklist de faltantes */
    .pro-falta { list-style: none; margin: 10px 0 0; padding: 0; }
    .pro-falta li {
        display: flex; gap: 10px; align-items: flex-start;
        padding: 8px 0; border-bottom: 1px solid #F1F4F9;
        font-size: 13px; color: #47536F;
    }
    .pro-falta li:last-child { border-bottom: none; }
    .pro-falta i { color: #b4552d; margin-top: 3px; font-size: 12px; }
    .pro-falta strong { color: #1B2B5A; font-weight: 600; }
</style>

<div class="pro-wrap">
    <div class="pro-title"><i class="fas fa-route" style="color:#2563EB;"></i> Procesos de trabajo</div>
    <div class="pro-sub">Cómo funciona cada flujo del sistema, paso a paso. Para que cualquiera del equipo sepa operarlo.</div>

    {{-- 1. Pedido de la tienda online --}}
    <div class="pro-card">
        <div class="pro-card-head">
            <span class="ic"><i class="fas fa-globe"></i></span>
            <h2>1 · Pedido de la tienda online</h2>
            <span class="pro-quien">Automático — lo hace el cliente</span>
        </div>
        <p class="pro-desc">El cliente compra solo desde la web. No requiere intervención hasta que el pedido entra.</p>
        <div class="pro-flujo">
            <div class="pro-paso"><span class="num">1</span><div class="t">Crea su cuenta</div><div class="d">O inicia sesión si ya compró antes</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">2</span><div class="t">Arma el carrito</div><div class="d">Productos, cantidades y medidas</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">3</span><div class="t">Confirma el pedido</div><div class="d">Datos, envío y medio de pago</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">4</span><div class="t">WhatsApp automático</div><div class="d">Se abre el chat con el detalle del pedido</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso fin"><span class="num">5</span><div class="t">Entra al panel</div><div class="d">Aparece "Pendiente" y suena la alerta 🔔</div></div>
        </div>
        <div class="pro-nota"><strong>Pago online:</strong> si eligió Mercado Pago, el cobro se acredita solo y el pedido queda marcado "Pagado online".</div>
    </div>

    {{-- 2. Atender un pedido --}}
    <div class="pro-card">
        <div class="pro-card-head">
            <span class="ic"><i class="fas fa-shopping-basket"></i></span>
            <h2>2 · Atender un pedido (cualquier canal)</h2>
            <span class="pro-quien">Equipo de ventas</span>
        </div>
        <p class="pro-desc">Panel → Pedidos. Cada pedido muestra su canal de origen con un color. Se avanza etapa por etapa con un clic.</p>
        <div class="pro-flujo">
            <div class="pro-paso"><span class="num">1</span><div class="t">Pendiente</div><div class="d">Revisar datos y contactar al cliente si hace falta</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">2</span><div class="t">Comprobación de stock</div><div class="d">Asignar de qué sucursal sale cada producto</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">3</span><div class="t">Pagado</div><div class="d">Registrar el cobro en caja o banco</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">4</span><div class="t">Enviado</div><div class="d">Coordinar entrega por WhatsApp (botón directo)</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso fin"><span class="num">5</span><div class="t">Entregado</div><div class="d">Pedido cerrado ✔</div></div>
        </div>
        <div class="pro-nota"><strong>Anular:</strong> disponible mientras el pedido no esté pagado. Libera el stock asignado automáticamente.</div>
    </div>

    {{-- 3. Cargar pedido de otro canal --}}
    <div class="pro-card">
        <div class="pro-card-head">
            <span class="ic"><i class="fas fa-plus-circle"></i></span>
            <h2>3 · Cargar pedido de otro canal</h2>
            <span class="pro-quien">Equipo de ventas</span>
        </div>
        <p class="pro-desc">Para ventas por MercadoLibre, WhatsApp, Instagram, Facebook o mostrador que quieras seguir con etapas. Pedidos → "Cargar pedido de otro canal".</p>
        <div class="pro-flujo">
            <div class="pro-paso"><span class="num">1</span><div class="t">Elegir canal</div><div class="d">ML, WhatsApp, Instagram, Facebook o Local</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">2</span><div class="t">Datos del cliente</div><div class="d">Si el email/teléfono ya existe, se reusa la ficha</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">3</span><div class="t">Agregar productos</div><div class="d">Cantidad y precio (editable, ej. precio ML)</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso fin"><span class="num">4</span><div class="t">Registrar</div><div class="d">Entra como Pendiente al flujo Nº 2</div></div>
        </div>
    </div>

    {{-- 4. Venta de mostrador --}}
    <div class="pro-card">
        <div class="pro-card-head">
            <span class="ic"><i class="fas fa-cash-register"></i></span>
            <h2>4 · Venta de mostrador (POS)</h2>
            <span class="pro-quien">Cajero / vendedor</span>
        </div>
        <p class="pro-desc">Para ventas presenciales que se cobran en el momento. Ventas → Nueva Venta.</p>
        <div class="pro-flujo">
            <div class="pro-paso"><span class="num">1</span><div class="t">Abrir caja</div><div class="d">Cuentas → apertura del día (una vez)</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">2</span><div class="t">Nueva venta</div><div class="d">Cliente + productos (busca por nombre o código)</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">3</span><div class="t">Cobrar</div><div class="d">Medio de pago y comprobante</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso fin"><span class="num">4</span><div class="t">Listo</div><div class="d">Stock baja solo y queda en informes</div></div>
        </div>
    </div>

    {{-- 5. Alta de producto --}}
    <div class="pro-card">
        <div class="pro-card-head">
            <span class="ic"><i class="fas fa-box"></i></span>
            <h2>5 · Alta de un producto</h2>
            <span class="pro-quien">Administración</span>
        </div>
        <p class="pro-desc">Productos → Artículos y variantes → Nuevo. Lo que cargues acá alimenta la tienda, el buscador y las publicaciones.</p>
        <div class="pro-flujo">
            <div class="pro-paso"><span class="num">1</span><div class="t">Datos básicos</div><div class="d">Nombre, categoría, marca, precios e IVA</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">2</span><div class="t">Ficha de colchón</div><div class="d">Tipo, firmeza, plazas, altura, garantía, noches</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">3</span><div class="t">Fotos</div><div class="d">Principal + galería (varias, para el carrusel)</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">4</span><div class="t">Stock</div><div class="d">Cantidad por sucursal</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso fin"><span class="num">5</span><div class="t">En la tienda</div><div class="d">Visible al instante con su ficha completa</div></div>
        </div>
        <div class="pro-nota"><strong>Tip:</strong> cuanto más completa la ficha técnica, mejores quedan los filtros de la tienda y los textos del Estudio de Publicaciones.</div>
    </div>

    {{-- 6. Compra a proveedor --}}
    <div class="pro-card">
        <div class="pro-card-head">
            <span class="ic"><i class="fas fa-truck"></i></span>
            <h2>6 · Reposición de mercadería</h2>
            <span class="pro-quien">Administración</span>
        </div>
        <p class="pro-desc">Compras → Nueva Compra. Registra el gasto y suma el stock.</p>
        <div class="pro-flujo">
            <div class="pro-paso"><span class="num">1</span><div class="t">Proveedor</div><div class="d">Elegir o crear el proveedor</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">2</span><div class="t">Detalle</div><div class="d">Artículos, cantidades y costos</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">3</span><div class="t">Confirmar</div><div class="d">Queda el comprobante registrado</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso fin"><span class="num">4</span><div class="t">Stock actualizado</div><div class="d">Suma en la sucursal elegida</div></div>
        </div>
    </div>

    {{-- 7. Publicar en canales --}}
    <div class="pro-card">
        <div class="pro-card-head">
            <span class="ic"><i class="fas fa-bullhorn"></i></span>
            <h2>7 · Publicar un producto en redes / ML</h2>
            <span class="pro-quien">Marketing / ventas</span>
        </div>
        <p class="pro-desc">Comercialización → Estudio de Publicaciones. Genera la imagen y los textos listos por canal.</p>
        <div class="pro-flujo">
            <div class="pro-paso"><span class="num">1</span><div class="t">Elegir producto</div><div class="d">Usa su foto y ficha técnica</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">2</span><div class="t">Formato y estilo</div><div class="d">ML 1:1 · Post · Historia — Claro o Noche</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">3</span><div class="t">Descargar imagen</div><div class="d">Y copiar título, descripción y caption</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">4</span><div class="t">Publicar en el canal</div><div class="d">Pegar en ML / Instagram / Facebook</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso fin"><span class="num">5</span><div class="t">Marcar publicado</div><div class="d">Queda el historial por producto</div></div>
        </div>
    </div>

    {{-- 8. Control del negocio --}}
    <div class="pro-card">
        <div class="pro-card-head">
            <span class="ic"><i class="fas fa-chart-pie"></i></span>
            <h2>8 · Control del negocio</h2>
            <span class="pro-quien">Dueño / gerencia</span>
        </div>
        <p class="pro-desc">Informes → tablero ejecutivo. Un vistazo diario alcanza.</p>
        <div class="pro-flujo">
            <div class="pro-paso"><span class="num">1</span><div class="t">KPIs del mes</div><div class="d">Facturación, ventas, ticket, margen bruto</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">2</span><div class="t">Canales</div><div class="d">Qué canal trae pedidos y facturación</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso"><span class="num">3</span><div class="t">Productos y medidas</div><div class="d">Top vendidos y qué plaza se vende más</div></div>
            <div class="pro-flecha"><i class="fas fa-chevron-right"></i></div>
            <div class="pro-paso fin"><span class="num">4</span><div class="t">Stock crítico</div><div class="d">Qué reponer antes de quedarse sin</div></div>
        </div>
    </div>

    {{-- Qué falta --}}
    <div class="pro-card" style="border-left:4px solid #b4552d;">
        <div class="pro-card-head">
            <span class="ic" style="background:#FBEDE6;color:#b4552d;"><i class="fas fa-list-check"></i></span>
            <h2>Qué le falta al ERP para estar completo</h2>
        </div>
        <ul class="pro-falta">
            <li><i class="fas fa-circle"></i><span><strong>Credenciales de Mercado Pago</strong> en el servidor — activa el cobro online real (la integración ya está hecha).</span></li>
            <li><i class="fas fa-circle"></i><span><strong>Mail SMTP real</strong> en producción — hoy los correos de pedidos y recupero de clave no salen del servidor.</span></li>
            <li><i class="fas fa-circle"></i><span><strong>Facturación electrónica ARCA</strong> — para emitir factura legal por cada venta.</span></li>
            <li><i class="fas fa-circle"></i><span><strong>Integración automática con MercadoLibre</strong> — que sus ventas y preguntas entren solas al panel y el stock se sincronice (hoy se cargan manualmente en 1 minuto). Requiere crear la app en developers.mercadolibre.com.ar.</span></li>
            <li><i class="fas fa-circle"></i><span><strong>Feed de catálogo Meta y Google</strong> — productos sincronizados solos en Instagram/Facebook/WhatsApp y Google Shopping.</span></li>
            <li><i class="fas fa-circle"></i><span><strong>Reseñas post-compra</strong> — mail automático para calificar y estrellas en la ficha.</span></li>
            <li><i class="fas fa-circle"></i><span><strong>Medidas como variantes</strong> — un solo colchón con todas sus plazas y precio por medida.</span></li>
            <li><i class="fas fa-circle"></i><span><strong>Carritos abandonados</strong> — recordatorio automático a quien no terminó la compra.</span></li>
            <li><i class="fas fa-circle"></i><span><strong>Textos legales</strong> — botón de arrepentimiento (obligatorio en AR), términos y condiciones, devoluciones.</span></li>
            <li><i class="fas fa-circle"></i><span><strong>Backups automáticos</strong> de la base de datos en el hosting.</span></li>
        </ul>
    </div>
</div>
@endsection
