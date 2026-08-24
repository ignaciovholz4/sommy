$(function () {
    const CFG = window.CONCILIACION_CONFIG;
    if (!CFG) return;

    let fileToken = null;
    let previewFilas = [];
    let estadoActual = 'pendiente';
    let importadoParaVincular = null;

    function csrfHeaders(extra) {
        return Object.assign({
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }, extra || {});
    }

    function moneyFmt(n) {
        return '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function esc(s) {
        return $('<div>').text(s == null ? '' : s).html();
    }

    // ── Paso 1: subir archivo ─────────────────────────────
    $('#conc-dropzone').on('click', function () {
        $('#conc-input-archivo').trigger('click');
    });

    $('#conc-input-archivo').on('change', function () {
        const archivo = this.files[0];
        if (!archivo) return;

        $('#conc-archivo-nombre').text('Analizando ' + archivo.name + '...');

        const fd = new FormData();
        fd.append('archivo', archivo);

        fetch(CFG.urls.previsualizar, {
            method: 'POST',
            headers: csrfHeaders(),
            body: fd,
        })
            .then(r => r.json())
            .then(resp => {
                if (!resp.estado) {
                    $('#conc-archivo-nombre').text('');
                    alert(resp.mensaje || 'No se pudo leer el archivo.');
                    return;
                }
                fileToken = resp.file_token;
                previewFilas = resp.preview || [];
                $('#conc-archivo-nombre').html(
                    '<i class="fas fa-check-circle text-success me-1"></i> ' + esc(resp.archivo_nombre) +
                    ' — ' + resp.total_filas + ' fila(s) detectadas'
                );
                $('#conc-input-archivo').data('nombre-original', resp.archivo_nombre);
                pintarPreviewYMapeo(resp.columnas_count);
                $('#conc-card-mapeo').show()[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
            })
            .catch(() => {
                $('#conc-archivo-nombre').text('');
                alert('Error al subir el archivo.');
            });
    });

    function pintarPreviewYMapeo(columnasCount) {
        // Tabla de preview
        const $thead = $('<thead><tr>' +
            Array.from({ length: columnasCount }, (_, i) => '<th>Columna ' + numeroALetra(i) + '</th>').join('') +
            '</tr></thead>');
        const $tbody = $('<tbody>' +
            previewFilas.map(fila =>
                '<tr>' + Array.from({ length: columnasCount }, (_, i) => '<td>' + esc(fila[i]) + '</td>').join('') + '</tr>'
            ).join('') +
            '</tbody>');
        $('#conc-preview-table').empty().append($thead).append($tbody);

        // Selects de mapeo
        const opciones = ['<option value="">— No aplica —</option>'].concat(
            Array.from({ length: columnasCount }, (_, i) => '<option value="' + i + '">Columna ' + numeroALetra(i) + '</option>')
        ).join('');

        ['#conc-col-fecha', '#conc-col-descripcion', '#conc-col-referencia',
         '#conc-col-importe', '#conc-col-ingreso', '#conc-col-egreso'].forEach(sel => {
            $(sel).html(opciones);
        });
        // Fecha e importe son obligatorios: sacamos la opción "no aplica" ahí
        $('#conc-col-fecha option[value=""]').remove();
        $('#conc-col-importe option[value=""]').remove();
    }

    function numeroALetra(i) {
        let letra = '';
        i++;
        while (i > 0) {
            const mod = (i - 1) % 26;
            letra = String.fromCharCode(65 + mod) + letra;
            i = Math.floor((i - mod) / 26);
        }
        return letra;
    }

    // ── Toggle modo importe ─────────────────────────────
    $('input[name="conc-modo-importe"]').on('change', function () {
        const modo = $('input[name="conc-modo-importe"]:checked').val();
        if (modo === 'signo_unico') {
            $('#conc-wrap-importe').show();
            $('#conc-wrap-ingreso, #conc-wrap-egreso').hide();
        } else {
            $('#conc-wrap-importe').hide();
            $('#conc-wrap-ingreso, #conc-wrap-egreso').show();
        }
    });

    // ── Paso 2: confirmar importación ─────────────────────
    $('#conc-btn-importar').on('click', function () {
        if (!fileToken) return;

        const modo = $('input[name="conc-modo-importe"]:checked').val();
        const colFecha = $('#conc-col-fecha').val();

        if (colFecha === '' || colFecha === null) {
            alert('Elegí la columna Fecha.');
            return;
        }
        if (modo === 'signo_unico' && !$('#conc-col-importe').val()) {
            alert('Elegí la columna Importe.');
            return;
        }
        if (modo === 'dos_columnas' && (!$('#conc-col-ingreso').val() || !$('#conc-col-egreso').val())) {
            alert('Elegí las columnas de Ingresos y Egresos.');
            return;
        }

        const payload = {
            file_token: fileToken,
            archivo_nombre: $('#conc-input-archivo').data('nombre-original') || null,
            con_encabezado: $('#conc-con-encabezado').is(':checked') ? 1 : 0,
            col_fecha: colFecha,
            col_descripcion: $('#conc-col-descripcion').val() || null,
            col_referencia: $('#conc-col-referencia').val() || null,
            modo_importe: modo,
            col_importe: $('#conc-col-importe').val() || null,
            col_ingreso: $('#conc-col-ingreso').val() || null,
            col_egreso: $('#conc-col-egreso').val() || null,
        };

        $('#conc-btn-importar').prop('disabled', true);
        $('#conc-importar-status').text('Importando...');

        fetch(CFG.urls.importar, {
            method: 'POST',
            headers: csrfHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify(payload),
        })
            .then(r => r.json())
            .then(resp => {
                $('#conc-btn-importar').prop('disabled', false);
                $('#conc-importar-status').text(resp.mensaje || '');
                if (resp.estado) {
                    $('#conc-card-mapeo').hide();
                    $('#conc-input-archivo').val('');
                    fileToken = null;
                    cargarTabla();
                }
            })
            .catch(() => {
                $('#conc-btn-importar').prop('disabled', false);
                $('#conc-importar-status').text('Error al importar.');
            });
    });

    // ── Paso 3: tabla de importados ────────────────────────
    $('.conc-tab').on('click', function () {
        $('.conc-tab').removeClass('active');
        $(this).addClass('active');
        estadoActual = $(this).data('estado');
        cargarTabla();
    });

    function cargarTabla() {
        $('#conc-tabla-body').html('<tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>');

        fetch(CFG.urls.data + '?estado=' + estadoActual)
            .then(r => r.json())
            .then(resp => {
                if (!resp.estado || !resp.data.length) {
                    $('#conc-tabla-body').html('<tr><td colspan="7" class="text-center text-muted py-4">No hay movimientos en esta vista.</td></tr>');
                    return;
                }
                $('#conc-tabla-body').html(resp.data.map(filaHtml).join(''));
            });
    }

    function filaHtml(item) {
        const chipTipo = item.tipo === 'ingreso'
            ? '<span class="mov-chip mov-chip-ingreso"><i class="fas fa-arrow-up"></i> Ingreso</span>'
            : '<span class="mov-chip mov-chip-egreso"><i class="fas fa-arrow-down"></i> Egreso</span>';

        let estadoCol = '';
        if (item.estado === 'pendiente') {
            if (item.sugerido) {
                estadoCol = '<div class="conc-sugerencia mb-1">' +
                    '<i class="fas fa-lightbulb me-1"></i> Sugerido: <b>' + esc(item.sugerido.comprobante || item.sugerido.cliente_proveedor || ('Mov #' + item.sugerido.id)) + '</b>' +
                    ' · ' + moneyFmt(item.sugerido.total) + ' · ' + item.sugerido.fecha +
                    '</div>' +
                    '<button class="btn btn-sm btn-success btn-conc-confirmar" data-id="' + item.id + '" data-mov="' + item.sugerido.id + '">' +
                    '<i class="fas fa-check me-1"></i> Confirmar</button> ';
            } else {
                estadoCol = '<span class="mov-chip mov-chip-pendiente">Sin sugerencia</span> ';
            }
            estadoCol += '<button class="btn btn-sm btn-outline-primary btn-conc-buscar" data-id="' + item.id + '"><i class="fas fa-search"></i></button> ' +
                '<button class="btn btn-sm btn-outline-secondary btn-conc-descartar" data-id="' + item.id + '"><i class="fas fa-times"></i></button>';
        } else if (item.estado === 'conciliado') {
            estadoCol = '<span class="mov-chip mov-chip-conciliado"><i class="fas fa-check-double"></i> Conciliado</span>' +
                (item.movimiento ? '<div class="small text-muted mt-1">' + esc(item.movimiento.comprobante) + '</div>' : '') +
                '<button class="btn btn-sm btn-link text-danger btn-conc-deshacer" data-id="' + item.id + '">Deshacer</button>';
        } else {
            estadoCol = '<span class="mov-chip mov-chip-descartado">Descartado</span>';
        }

        return '<tr>' +
            '<td>' + esc(item.fecha) + '</td>' +
            '<td>' + esc(item.descripcion) + '</td>' +
            '<td>' + esc(item.referencia) + '</td>' +
            '<td>' + chipTipo + '</td>' +
            '<td class="text-end fw-bold">' + moneyFmt(item.monto) + '</td>' +
            '<td>' + estadoCol + '</td>' +
            '<td></td>' +
            '</tr>';
    }

    // Confirmar sugerencia
    $(document).on('click', '.btn-conc-confirmar', function () {
        const id = $(this).data('id');
        const movId = $(this).data('mov');
        conciliar(id, movId);
    });

    // Deshacer
    $(document).on('click', '.btn-conc-deshacer', function () {
        const id = $(this).data('id');
        fetch(CFG.urls.conciliar + '/' + id + '/deshacer', {
            method: 'POST',
            headers: csrfHeaders(),
        }).then(() => cargarTabla());
    });

    // Descartar
    $(document).on('click', '.btn-conc-descartar', function () {
        const id = $(this).data('id');
        if (!confirm('¿Descartar esta fila? No se va a poder conciliar (ej: saldo de apertura del extracto).')) return;
        fetch(CFG.urls.conciliar + '/' + id + '/descartar', {
            method: 'POST',
            headers: csrfHeaders(),
        }).then(() => cargarTabla());
    });

    // Buscar manualmente
    $(document).on('click', '.btn-conc-buscar', function () {
        importadoParaVincular = $(this).data('id');
        $('#conc-buscar-input').val('');
        $('#conc-buscar-resultados').html('<div class="text-muted small text-center py-3">Escribí para buscar...</div>');
        new bootstrap.Modal(document.getElementById('modalBuscarMovimiento')).show();
        buscarMovimientos('');
    });

    let buscarTimeout = null;
    $('#conc-buscar-input').on('input', function () {
        clearTimeout(buscarTimeout);
        const q = $(this).val();
        buscarTimeout = setTimeout(() => buscarMovimientos(q), 300);
    });

    function buscarMovimientos(q) {
        fetch(CFG.urls.buscar + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(resp => {
                if (!resp.estado || !resp.movimientos.length) {
                    $('#conc-buscar-resultados').html('<div class="text-muted small text-center py-3">Sin resultados.</div>');
                    return;
                }
                $('#conc-buscar-resultados').html(resp.movimientos.map(m =>
                    '<div class="d-flex justify-content-between align-items-center p-2 border-bottom conc-buscar-item" ' +
                    'style="cursor:pointer" data-mov="' + m.id + '">' +
                    '<div><div class="fw-bold small">' + esc(m.comprobante || m.cliente_proveedor || ('Mov #' + m.id)) + '</div>' +
                    '<div class="text-muted small">' + m.fecha + ' · ' + m.tipo + '</div></div>' +
                    '<div class="fw-bold">' + moneyFmt(m.total) + '</div>' +
                    '</div>'
                ).join(''));
            });
    }

    $(document).on('click', '.conc-buscar-item', function () {
        const movId = $(this).data('mov');
        conciliar(importadoParaVincular, movId);
        bootstrap.Modal.getInstance(document.getElementById('modalBuscarMovimiento')).hide();
    });

    function conciliar(importadoId, movimientoId) {
        fetch(CFG.urls.conciliar + '/' + importadoId + '/conciliar', {
            method: 'POST',
            headers: csrfHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ movimiento_id: movimientoId }),
        })
            .then(r => r.json())
            .then(resp => {
                if (!resp.estado) {
                    alert(resp.mensaje || 'No se pudo conciliar.');
                    return;
                }
                cargarTabla();
            });
    }

    cargarTabla();
});
