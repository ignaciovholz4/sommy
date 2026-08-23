@php
    $resp = request()->path();
@endphp

<style>
    /* Reset y Base para el Layout */
    .content-wrapper { margin-left: 0 !important; background-color: #F8FAFC !important; }
    .main-sidebar { display: none !important; }
    .main-header { margin-left: 0 !important; border-bottom: none !important; padding: 0 !important; }

    /* --- HEADER SOMMY: fondo noche Azul Sommy, sereno --- */
    .dg-header-top {
        background: linear-gradient(120deg, #1B2B5A 0%, #24356B 100%) !important;
        height: 75px;
        display: flex;
        align-items: center;
        padding: 0 5%;
        border-bottom: none;
        position: sticky;
        top: 0;
        z-index: 1100;
        font-family: 'Poppins', -apple-system, sans-serif;
        /* visible: el desplegable del buscador global vive dentro del header */
        overflow: visible;
    }

    /* Destellos ✦ de marca (máx. 3, serenos) */
    .dg-header-top .dg-sparkle {
        position: absolute;
        color: #0EA5E9;
        pointer-events: none;
        font-size: 13px;
        opacity: .55;
    }
    .dg-header-top .dg-sparkle--1 { top: 16px; right: 18%; }
    .dg-header-top .dg-sparkle--2 { bottom: 12px; right: 34%; font-size: 9px; color: #E0F2FE; }
    .dg-header-top .dg-sparkle--3 { top: 30px; left: 32%; font-size: 8px; color: #E0F2FE; opacity: .35; }

    .dg-top-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
    }

    .dg-brand-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* Logo blanco directo sobre el fondo noche, sin placa */
    .dg-logo-plate {
        display: inline-flex;
        align-items: center;
        background: transparent;
        padding: 8px 0;
        margin-left: 84px;
        transition: transform .15s ease;
    }
    .dg-logo-plate:hover { transform: translateY(-1px); }

    .dg-logo {
        height: 52px;
        width: auto;
        display: block;
    }

    .dg-mobile-burger {
        display: none;
        font-size: 18px;
        color: rgba(255, 255, 255, 0.6);
        cursor: pointer;
        width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .dg-tools-right {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-right: 72px;
    }

    /* --- BOTÓN PEDIDOS --- */
    .dg-nav-item {
        color: #E0F2FE !important;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        text-decoration: none !important;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 16px;
        border-radius: 999px;
        transition: all 0.2s ease;
        border: 1px solid rgba(224, 242, 254, 0.25);
        background: rgba(255, 255, 255, 0.04);
    }

    .dg-nav-item:hover {
        background: rgba(14, 165, 233, 0.15);
        color: #ffffff !important;
        border-color: #0EA5E9;
    }

    .dg-nav-item.active {
        background: rgba(14, 165, 233, 0.2);
        color: #ffffff !important;
        border-color: #0EA5E9;
    }

    /* --- BOTÓN "VER MI TIENDA": pill Azul Confort --- */
    .dg-store-btn {
        background: #2563EB;
        color: #ffffff !important;
        font-weight: 500;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 7px 18px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        gap: 7px;
        text-decoration: none !important;
        border: none;
        box-shadow: 0 10px 30px rgba(27, 43, 90, .10);
        height: 32px;
        cursor: pointer;
        transition: background .2s ease, transform .1s ease;
    }
    .dg-store-btn:hover {
        background: #0EA5E9;
        transform: translateY(-1px);
    }

    .dg-divider {
        height: 20px;
        width: 1px;
        background: rgba(224, 242, 254, 0.2);
        margin: 0 5px;
    }

    .dg-logout-btn {
        color: rgba(224, 242, 254, 0.6) !important;
        font-size: 16px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        text-decoration: none !important;
    }
    .dg-logout-btn:hover { color: #ffffff !important; background: rgba(255,255,255,.06); }

    @media (max-width: 992px) {
        .dg-mobile-burger { display: flex; }
        .dg-header-top { padding: 0 1.5rem; }
        .dg-logo-plate { margin-left: 20px; padding: 6px 0; }
        .dg-logo { height: 36px; }
        .dg-tools-right { margin-right: 0; }
        .dg-nav-item span { display: none; }
        .dg-nav-item { display: none; }
    }
</style>

<header class="dg-header-top">
    <span class="dg-sparkle dg-sparkle--1">✦</span>
    <span class="dg-sparkle dg-sparkle--2">✦</span>
    <span class="dg-sparkle dg-sparkle--3">✦</span>
    <div class="dg-top-container">
        <div class="dg-brand-section">
            {{-- Logotipo Sommy en blanco, directo sobre el fondo noche --}}
            <a href="{{url('/dashboard')}}" class="dg-logo-plate" style="text-decoration: none;">
                <img src="{{asset('imagenes/marca/sommy-logo-blanco-texto.png')}}" class="dg-logo" alt="Sommy">
            </a>
        </div>

        {{-- Buscador global: DNI / CUIT / nombre → ficha del cliente, proveedor o revendedor --}}
        <div class="dg-buscador" style="position:relative;flex:1;max-width:430px;margin:0 26px;">
            <input id="dgBuscarPersona" type="search" placeholder="Buscar por DNI / CUIT / nombre..." autocomplete="off"
                   style="width:100%;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);border-radius:999px;padding:9px 20px;font-size:13px;color:#fff;outline:none;">
            <div id="dgBuscarResultados" style="display:none;position:absolute;top:115%;left:0;right:0;background:#fff;border-radius:14px;box-shadow:0 18px 50px rgba(0,0,0,.28);z-index:3000;max-height:360px;overflow-y:auto;"></div>
        </div>
        <style>
            #dgBuscarPersona::placeholder { color: rgba(255,255,255,.55); }
            #dgBuscarPersona:focus { border-color: #7FD4F5; background: rgba(255,255,255,.16); }
            .dg-res-item { display:flex; align-items:center; gap:10px; padding:10px 14px; text-decoration:none !important; color:#1B2B5A; border-bottom:1px solid #F1F4F9; font-family:'Poppins',sans-serif; }
            .dg-res-item:hover { background:#F8FAFC; }
            .dg-res-tipo { border-radius:999px; font-size:9.5px; font-weight:700; padding:2px 9px; text-transform:uppercase; letter-spacing:.04em; flex-shrink:0; }
            .dg-res-tipo.Cliente { background:#E0F2FE; color:#1B2B5A; }
            .dg-res-tipo.Proveedor { background:#FEF3C7; color:#92400E; }
            .dg-res-tipo.Revendedor { background:#DCFCE7; color:#166534; }
            .dg-res-nombre { font-size:13.5px; font-weight:600; }
            .dg-res-sub { font-size:11px; color:#6E7A96; }
            @media (max-width: 992px) { .dg-buscador { display:none; } }
        </style>

        <div class="dg-tools-right">
            {{-- Campanita: centro de notificaciones del negocio --}}
            <a href="javascript:void(0)" class="dg-nav-item" id="dgNotifBtn" title="Notificaciones" style="position:relative;">
                <i class="fas fa-bell"></i>
                <span id="dgNotifBadge" style="display:none;position:absolute;top:-7px;right:-7px;min-width:19px;height:19px;background:#F59E0B;color:#fff;font-size:11px;font-weight:700;border-radius:999px;align-items:center;justify-content:center;padding:0 5px;line-height:19px;text-align:center;">0</span>
            </a>
            <div id="dgNotifPanel" style="display:none;position:absolute;top:64px;right:220px;width:390px;max-width:92vw;background:#fff;border-radius:14px;box-shadow:0 18px 55px rgba(0,0,0,.30);z-index:3000;overflow:hidden;font-family:'Poppins',sans-serif;">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 15px;border-bottom:1px solid #F1F4F9;">
                    <b style="color:#1B2B5A;font-size:13.5px;">🔔 Novedades del negocio</b>
                    <button id="dgNotifLeidas" style="border:none;background:none;color:#2563EB;font-size:11.5px;cursor:pointer;font-family:'Poppins',sans-serif;">Marcar leídas</button>
                </div>
                <div id="dgNotifLista" style="max-height:380px;overflow-y:auto;"></div>
                <a href="{{ url('notificaciones') }}" style="display:block;text-align:center;padding:9px;font-size:12px;color:#2563EB;text-decoration:none;border-top:1px solid #F1F4F9;">Ver historial completo</a>
            </div>

            <div class="dg-divider"></div>

            <a href="{{ url('/orders/order') }}" class="dg-nav-item {{ str_contains($resp, 'pedidos') ? 'active' : '' }}" title="Ver Pedidos Ecommerce" style="position:relative;">
                <i class="fas fa-shopping-basket"></i>
                <span>Pedidos</span>
                <span id="dgOrdersBadge" style="display:none;position:absolute;top:-7px;right:-7px;min-width:19px;height:19px;background:#0EA5E9;color:#fff;font-size:11px;font-weight:700;border-radius:999px;align-items:center;justify-content:center;padding:0 5px;line-height:19px;text-align:center;">0</span>
            </a>

            <div class="dg-divider"></div>

            <a href="{{ url('/envios') }}" class="dg-nav-item {{ \Illuminate\Support\Str::startsWith($resp, 'envios') ? 'active' : '' }}" title="Tablero de envíos y fletes" style="position:relative;">
                <i class="fas fa-shipping-fast"></i>
                <span>Envíos</span>
                <span id="dgEnviosBadge" style="display:none;position:absolute;top:-7px;right:-7px;min-width:19px;height:19px;background:#0EA5E9;color:#fff;font-size:11px;font-weight:700;border-radius:999px;align-items:center;justify-content:center;padding:0 5px;line-height:19px;text-align:center;">0</span>
            </a>

            <div class="dg-divider"></div>

            <a href="{{ url('finanzas/resumen?periodo=hoy') }}" class="dg-nav-item" title="Todos los movimientos de hoy">
                <i class="fas fa-calendar-day"></i>
                <span>Hoy</span>
            </a>

            <a href="{{ url('finanzas/resumen?periodo=mes') }}" class="dg-nav-item" title="Todos los movimientos del mes">
                <i class="fas fa-calendar-alt"></i>
                <span>Mes</span>
            </a>

            <div class="dg-divider"></div>

            <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="dg-store-btn" title="Ver mi tienda online">
                <i class="fas fa-external-link-alt"></i>
                <span>Ver mi tienda</span>
            </a>

            <div class="dg-divider"></div>

            <a href="{{ url('/logout') }}" class="dg-logout-btn" title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</header>

<script>
// Alerta de pedidos nuevos: badge en PEDIDOS + notificación al detectar un pedido entrante
window.addEventListener('load', function () {
    var badge = document.getElementById('dgOrdersBadge');
    if (!badge) return;

    function chequearPedidos() {
        fetch('{{ url('/orders/new-count') }}', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) return;

                // Badge con la cantidad de pedidos pendientes
                if (data.pendientes > 0) {
                    badge.textContent = data.pendientes > 99 ? '99+' : data.pendientes;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }

                // Aviso de pedidos estancados (más de 48 hs en Pendiente), máximo una vez cada 6 horas
                if (data.estancados > 0 && window.toastr) {
                    var ultimoAvisoEstancados = parseInt(localStorage.getItem('sommyAvisoEstancados') || '0', 10);
                    if (Date.now() - ultimoAvisoEstancados > 6 * 60 * 60 * 1000) {
                        toastr.warning(
                            'Hay ' + data.estancados + ' pedido(s) hace más de 48 hs sin atender',
                            '⏰ Pedidos estancados',
                            { timeOut: 15000, onclick: function () { window.location.href = '{{ url('/orders/order') }}'; } }
                        );
                        localStorage.setItem('sommyAvisoEstancados', String(Date.now()));
                    }
                }

                // Notificación cuando aparece un pedido con id mayor al último visto
                var ultimoVisto = parseInt(localStorage.getItem('sommyUltimoPedidoVisto') || '0', 10);
                if (ultimoVisto && data.ultimo_id > ultimoVisto && window.toastr) {
                    toastr.info(
                        'Hacé clic para ver los pedidos',
                        '🛒 ¡Entró un pedido nuevo! (#' + data.ultimo_id + ')',
                        {
                            timeOut: 12000,
                            positionClass: 'toast-top-right',
                            onclick: function () { window.location.href = '{{ url('/orders/order') }}'; }
                        }
                    );
                }
                localStorage.setItem('sommyUltimoPedidoVisto', String(data.ultimo_id));
            })
            .catch(function () {});
    }

    chequearPedidos();
    setInterval(chequearPedidos, 45000);

    // Badge de Envíos: suma lo que espera flete + lo que espera despacho
    var badgeEnvios = document.getElementById('dgEnviosBadge');
    function chequearEnvios() {
        fetch('{{ url('/envios/pending-count') }}', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data || !badgeEnvios) return;
                var total = (data.sin_flete || 0) + (data.despachar || 0);
                if (total > 0) {
                    badgeEnvios.textContent = total > 99 ? '99+' : total;
                    badgeEnvios.style.display = 'inline-flex';
                } else {
                    badgeEnvios.style.display = 'none';
                }
            })
            .catch(function () {});
    }
    chequearEnvios();
    setInterval(chequearEnvios, 45000);

    // 🔔 Centro de notificaciones: badge + desplegable
    var notifBtn = document.getElementById('dgNotifBtn');
    var notifPanel = document.getElementById('dgNotifPanel');
    var notifBadge = document.getElementById('dgNotifBadge');
    var notifLista = document.getElementById('dgNotifLista');

    function pintarNotifs(data) {
        if (data.no_leidas > 0) {
            notifBadge.textContent = data.no_leidas > 99 ? '99+' : data.no_leidas;
            notifBadge.style.display = 'inline-flex';
        } else {
            notifBadge.style.display = 'none';
        }
        if (!data.ultimas.length) {
            notifLista.innerHTML = '<div style="padding:26px;text-align:center;color:#94A3B8;font-size:12.5px;">Sin novedades por ahora. 👌</div>';
            return;
        }
        notifLista.innerHTML = data.ultimas.map(function (n) {
            return '<a href="' + n.ir + '" style="display:flex;gap:10px;padding:11px 15px;text-decoration:none;border-bottom:1px solid #F8FAFC;background:' + (n.leida ? '#fff' : '#F0F7FF') + ';">' +
                '<span style="font-size:19px;flex-shrink:0;">' + n.icono + '</span>' +
                '<span style="min-width:0;">' +
                    '<span style="display:block;font-size:12.5px;font-weight:600;color:#1B2B5A;">' + n.titulo + '</span>' +
                    (n.mensaje ? '<span style="display:block;font-size:11.5px;color:#6E7A96;">' + n.mensaje + '</span>' : '') +
                    '<span style="display:block;font-size:10.5px;color:#94A3B8;margin-top:2px;">' + n.hace + ' · <span style="color:#2563EB;">Revisar →</span></span>' +
                '</span></a>';
        }).join('');
    }

    function chequearNotifs() {
        fetch('{{ url('/notificaciones/feed') }}', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) { if (data) pintarNotifs(data); })
            .catch(function () {});
    }

    if (notifBtn) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            notifPanel.style.display = notifPanel.style.display === 'none' ? '' : 'none';
        });
        document.addEventListener('click', function (e) {
            if (!notifPanel.contains(e.target) && !notifBtn.contains(e.target)) {
                notifPanel.style.display = 'none';
            }
        });
        document.getElementById('dgNotifLeidas').addEventListener('click', function () {
            fetch('{{ url('/notificaciones/leidas') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, credentials: 'same-origin' })
                .then(chequearNotifs);
        });
        chequearNotifs();
        setInterval(chequearNotifs, 45000);
    }

    // Buscador global por DNI/CUIT/nombre → ficha del cliente/proveedor/revendedor
    var inputBuscar = document.getElementById('dgBuscarPersona');
    var panelBuscar = document.getElementById('dgBuscarResultados');
    if (inputBuscar && panelBuscar) {
        var timerBuscar = null;
        inputBuscar.addEventListener('input', function () {
            clearTimeout(timerBuscar);
            var q = this.value.trim();
            if (q.length < 3) { panelBuscar.style.display = 'none'; return; }
            timerBuscar = setTimeout(function () {
                fetch('{{ url('/buscar-persona') }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (!data) return;
                        var res = data.resultados || [];
                        if (!res.length) {
                            panelBuscar.innerHTML = '<div style="padding:14px;text-align:center;color:#94A3B8;font-size:12.5px;font-family:Poppins,sans-serif;">Sin resultados para "' + q.replace(/</g, '&lt;') + '"</div>';
                        } else {
                            panelBuscar.innerHTML = res.map(function (p) {
                                return '<a class="dg-res-item" href="' + p.url + '">' +
                                    '<span class="dg-res-tipo ' + p.tipo + '">' + p.tipo + '</span>' +
                                    '<span><span class="dg-res-nombre">' + p.nombre + '</span><br>' +
                                    '<span class="dg-res-sub">' + [p.doc, p.extra].filter(Boolean).join(' · ') + '</span></span></a>';
                            }).join('');
                        }
                        panelBuscar.style.display = '';
                    }).catch(function () {});
            }, 300);
        });
        document.addEventListener('click', function (e) {
            if (!inputBuscar.contains(e.target) && !panelBuscar.contains(e.target)) {
                panelBuscar.style.display = 'none';
            }
        });
    }
});
</script>
