$(function () {
    const CFG = window.CHYTAPAY_CONFIG;
    if (!CFG) return;

    function csrfHeaders(extra) {
        return Object.assign({
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }, extra || {});
    }

    function esc(s) {
        return $('<div>').text(s == null ? '' : s).html();
    }

    function cargarEstado() {
        fetch(CFG.urls.estado)
            .then(r => r.json())
            .then(pintar)
            .catch(() => {
                $('#chytapay-body').html('<div class="text-danger small">No se pudo consultar el estado de Chytapay.</div>');
            });
    }

    function pintar(resp) {
        if (!resp.habilitado) {
            $('#chytapay-body').html('<div class="text-muted small"><i class="fas fa-circle-info me-1"></i> Chytapay no está configurado en este entorno.</div>');
            return;
        }

        if (!resp.conexion) {
            $('#chytapay-body').html(
                '<p class="text-muted small mb-3">Conectá esta cuenta a Chytapay para traer automáticamente los cobros pagados como movimientos pendientes de conciliar.</p>' +
                '<button class="btn btn-facturarg-main" id="chytapay-btn-conectar"><i class="fas fa-link me-2"></i> Conectar con Chytapay</button>'
            );
            return;
        }

        const c = resp.conexion;
        $('#chytapay-body').html(
            '<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:12px">' +
            '<div>' +
            '<span class="mov-chip mov-chip-conciliado"><i class="fas fa-check-double"></i> Conectada</span>' +
            (c.comercio_nombre || c.comercio_email
                ? '<div class="small text-muted mt-1">' + esc(c.comercio_nombre) + (c.comercio_email ? ' · ' + esc(c.comercio_email) : '') + '</div>'
                : '') +
            '<div class="small text-muted mt-1">Última sincronización: ' + (c.last_sync_at ? esc(c.last_sync_at) : 'todavía no corrió') + '</div>' +
            '</div>' +
            '<div>' +
            '<button class="btn btn-sm btn-outline-primary me-2" id="chytapay-btn-sync"><i class="fas fa-rotate me-1"></i> Sincronizar ahora</button>' +
            '<button class="btn btn-sm btn-outline-danger" id="chytapay-btn-desconectar"><i class="fas fa-unlink me-1"></i> Desconectar</button>' +
            '</div>' +
            '</div>' +
            '<div class="small mt-2" id="chytapay-sync-status"></div>'
        );
    }

    $(document).on('click', '#chytapay-btn-conectar', function () {
        $(this).prop('disabled', true).text('Redirigiendo...');
        fetch(CFG.urls.conectar, { method: 'POST', headers: csrfHeaders() })
            .then(r => r.json())
            .then(resp => {
                if (resp.estado && resp.url) {
                    window.location.href = resp.url;
                } else {
                    alert(resp.mensaje || 'No se pudo iniciar la conexión con Chytapay.');
                    cargarEstado();
                }
            });
    });

    $(document).on('click', '#chytapay-btn-sync', function () {
        const $btn = $(this).prop('disabled', true);
        $('#chytapay-sync-status').text('Sincronizando...');
        fetch(CFG.urls.sincronizar, { method: 'POST', headers: csrfHeaders() })
            .then(r => r.json())
            .then(resp => {
                $btn.prop('disabled', false);
                $('#chytapay-sync-status').text(resp.mensaje || '');
                if (resp.estado && resp.creados > 0 && typeof window.location.reload === 'function') {
                    // Refresca la tabla de conciliación para mostrar los nuevos importados
                    $('.conc-tab[data-estado="pendiente"]').trigger('click');
                }
            });
    });

    $(document).on('click', '#chytapay-btn-desconectar', function () {
        if (!confirm('¿Desconectar esta cuenta de Chytapay? Vas a tener que volver a autorizarla para reactivar la sincronización automática.')) return;
        fetch(CFG.urls.desconectar, { method: 'POST', headers: csrfHeaders() })
            .then(r => r.json())
            .then(() => cargarEstado());
    });

    cargarEstado();
});
