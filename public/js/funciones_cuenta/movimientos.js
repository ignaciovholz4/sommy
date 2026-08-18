$(document).ready(function () {
    const cuentaId   = $('#table_movimientos').data('cuenta-id');
    const aperturaId = $('#table_movimientos').data('apertura-id');

    // Inicializar DataTable
    const tablaMovimientos = $('#table_movimientos').DataTable({
        paging: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        language: {
            emptyTable: "No hay movimientos registrados",
            zeroRecords: "No se encontraron movimientos",
            processing: "Cargando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ movimientos",
            infoEmpty: "Mostrando 0 a 0 de 0 movimientos",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        },
        ajax: {
            url: `/cuentas/${cuentaId}/movimientos/data`,
            type: 'GET',
            data: function(d){
                if (aperturaId) d.apertura = aperturaId; // solo aplica si es caja
            }
        },
        columns: [
            { data: 'fecha_fmt', name: 'fecha' },
            { data: 'estado', name: 'tipo', orderable: false, searchable: false },
            { data: 'cliente_proveedor', name: 'cliente_proveedor',
              render: function(data, type, row){
                  const esc = s => $('<div>').text(s || '').html();
                  const principal = esc(data) || '<span class="text-muted">Sin detalle</span>';
                  let sub = [esc(row.comprobante), esc(row.observaciones)].filter(Boolean).join(' · ');
                  // Si el movimiento nació de una operación (venta, pedido, gasto...), el detalle la abre
                  if (sub && row.operacion_url) {
                      sub = '<a href="' + row.operacion_url + '" title="Abrir la operación" ' +
                            'style="color:#2563EB;text-decoration:none;">' + sub + ' <i class="fas fa-external-link-alt" style="font-size:0.65rem;"></i></a>';
                  }
                  return '<div class="mov-detalle-principal">' + principal + '</div>' +
                         (sub ? '<div class="mov-detalle-sub">' + sub + '</div>' : '');
              } },
            // Columnas ocultas: mantienen la búsqueda por comprobante y observaciones
            { data: 'comprobante', name: 'comprobante', visible: false },
            { data: 'observaciones', name: 'observaciones', visible: false },
            { data: 'medio', name: 'medio', orderable: false, searchable: false },
            { data: 'monto', name: 'total', orderable: true, searchable: false, className: 'text-end' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']]
    });

    // ============================
    // Modal "Agregar Movimiento"
    // ============================

    // Abrir modal
    $(document).on('click', '#btnAgregarMovimiento', function() {
        $('#formAgregarMovimiento')[0].reset();
        $('#mov_total').val('0.00');
        $('#mov_comprobante').val('');
        const modal = new bootstrap.Modal(document.getElementById('agregarMovimientoModal'));
        modal.show();
    });

    // Generar comprobante automático según tipo
    $('#mov_tipo').on('change', function() {
        const tipo = $(this).val();
        const prefix = tipo === 'ingreso' ? 'MRC' : (tipo === 'egreso' ? 'MPC' : '');
        if (prefix) {
            $('#mov_comprobante').val(`${prefix}-${Date.now()}`);
        } else {
            $('#mov_comprobante').val('');
        }
    });

    // Calcular total en tiempo real
    function calcularTotal() {
        const efectivo = parseFloat($('#mov_efectivo').val()) || 0;
        const bancos   = parseFloat($('#mov_bancos').val()) || 0;
        const tarjetas = parseFloat($('#mov_tarjetas').val()) || 0;
        const total    = efectivo + bancos + tarjetas;
        $('#mov_total').val(total.toFixed(2));
    }

    $('#mov_efectivo, #mov_bancos, #mov_tarjetas').on('input', calcularTotal);

    // Envío del formulario
    $('#formAgregarMovimiento').on('submit', function(e) {
        e.preventDefault();

        // Detectar si es caja (aperturaId presente) o banco
        const esCaja = Boolean(aperturaId);

        const data = {
            tipo: $('#mov_tipo').val(),
            cliente_proveedor: $('#mov_cliente').val(),
            comprobante: $('#mov_comprobante').val(),
            observaciones: $('#mov_observaciones').val(),
            efectivo: esCaja ? (parseFloat($('#mov_efectivo').val()) || 0) : 0,   // 🔹 Caja usa efectivo, Banco lo fuerza a 0
            bancos: esCaja ? 0 : (parseFloat($('#mov_bancos').val()) || 0),       // 🔹 Banco usa bancos, Caja lo fuerza a 0
            tarjetas: parseFloat($('#mov_tarjetas').val()) || 0,
            total: parseFloat($('#mov_total').val()) || 0
        };

        // Solo enviar apertura_id si existe (caja)
        if (aperturaId) {
            data.apertura_id = aperturaId;
        }

        if (!data.tipo || !data.comprobante) {
            Swal.fire('Error', 'Debe seleccionar el tipo de movimiento', 'error');
            return;
        }

        fetch(`/cuentas/${cuentaId}/movimientos`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(result => {
            if (result.estado === 1) {
                Swal.fire('Éxito', result.mensaje, 'success').then(() => {
                    bootstrap.Modal.getInstance(document.getElementById('agregarMovimientoModal')).hide();
                    tablaMovimientos.ajax.reload(null, false);
                });
            } else {
                Swal.fire('Error', result.mensaje || 'No se pudo registrar el movimiento', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Error inesperado al registrar el movimiento', 'error');
        });
    });

});

document.addEventListener('DOMContentLoaded', function () {
    const btnTransferencia = document.getElementById('btnTransferencia');

    if (btnTransferencia) {
        btnTransferencia.addEventListener('click', function () {
            const modal = new bootstrap.Modal(document.getElementById('transferenciaModal'));
            modal.show();

            const table = document.getElementById('table_movimientos');
            const sucursalId = table.dataset.sucursalId;

            fetch(`/sucursal/${sucursalId}/cuentas-abiertas`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.estado === 1) {
                    const origenSelect  = document.getElementById('trans_origen');
                    const destinoSelect = document.getElementById('trans_destino');

                    origenSelect.innerHTML  = '<option value="">Seleccione...</option>';
                    destinoSelect.innerHTML = '<option value="">Seleccione...</option>';

                    // 🔹 Cajas abiertas
                    if (data.cajas) {
                        data.cajas.forEach(c => {
                            const optionValue = `caja-${c.id}`;
                            const optionLabel = `${c.nombre} (Caja) - Apertura: ${c.fecha_apertura}`;
                            origenSelect.insertAdjacentHTML('beforeend', `<option value="${optionValue}">${optionLabel}</option>`);
                            destinoSelect.insertAdjacentHTML('beforeend', `<option value="${optionValue}">${optionLabel}</option>`);
                        });
                    }

                    // 🔹 Bancos
                    if (data.bancos) {
                        data.bancos.forEach(b => {
                            const optionValue = `banco-${b.id}`;
                            const optionLabel = `${b.nombre} (Banco)`;
                            origenSelect.insertAdjacentHTML('beforeend', `<option value="${optionValue}">${optionLabel}</option>`);
                            destinoSelect.insertAdjacentHTML('beforeend', `<option value="${optionValue}">${optionLabel}</option>`);
                        });
                    }
                } else {
                    Swal.fire('Error', 'No se pudieron cargar las cuentas abiertas', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error al cargar las cuentas disponibles', 'error');
            });
        });
    }
});

/* ============================================================
   MÓDULO: DETALLE DE MOVIMIENTO (botón .btn-ver)
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {

    const TIPOS_CONFIG = {
        venta: {
            label:  'Comprobante de Venta',
            icon:   'fa-file-invoice-dollar',
            bg:     '#10b981',
            badge:  'Venta',
        },
        devolucion_venta: {
            label:  'Devolución / Anulación de Venta',
            icon:   'fa-undo-alt',
            bg:     '#f59e0b',
            badge:  'Dev. Venta',
        },
        compra: {
            label:  'Comprobante de Compra',
            icon:   'fa-shopping-basket',
            bg:     '#3b82f6',
            badge:  'Compra',
        },
        devolucion_compra: {
            label:  'Devolución / Anulación de Compra',
            icon:   'fa-undo-alt',
            bg:     '#f59e0b',
            badge:  'Dev. Compra',
        },
        ecommerce: {
            label:  'Pedido Ecommerce',
            icon:   'fa-shopping-cart',
            bg:     '#8b5cf6',
            badge:  'Ecommerce',
        },
        transferencia: {
            label:  'Transferencia entre Cuentas',
            icon:   'fa-exchange-alt',
            bg:     '#0ea5e9',
            badge:  'Transferencia',
        },
        manual: {
            label:  'Movimiento Manual',
            icon:   'fa-hand-paper',
            bg:     '#64748b',
            badge:  'Manual',
        },
    };

    function fmt(n) {
        return '$ ' + parseFloat(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function estadoBadge(estado) {
        const map = {
            activa:    { cls: 'bg-success', txt: 'Activa' },
            completada:{ cls: 'bg-success', txt: 'Completada' },
            anulada:   { cls: 'bg-danger',  txt: 'Anulada' },
            pendiente: { cls: 'bg-warning text-dark', txt: 'Pendiente' },
        };
        const found = map[(estado || '').toLowerCase()];
        if (found) return `<span class="badge ${found.cls}">${found.txt}</span>`;
        return `<span class="badge bg-secondary">${estado || '—'}</span>`;
    }

    function resetModal() {
        document.getElementById('detalle-loading').style.display    = 'block';
        document.getElementById('detalle-contenido').style.display  = 'none';
        ['comprobante','ecommerce','transferencia','manual'].forEach(s => {
            const el = document.getElementById('detalle-seccion-' + s);
            if (el) el.style.display = 'none';
        });
        const btnAbrir = document.getElementById('detalle-btn-abrir');
        if (btnAbrir) btnAbrir.style.display = 'none';
    }

    function renderMovimientoBase(mov) {
        document.getElementById('detalle-fecha').textContent         = mov.fecha || '—';
        document.getElementById('detalle-clienteprov').textContent   = mov.cliente_proveedor || '—';
        document.getElementById('detalle-observaciones').textContent = mov.observaciones || '—';
        document.getElementById('detalle-comprobante-num').textContent = mov.comprobante || '';
        document.getElementById('detalle-efectivo').textContent  = fmt(mov.efectivo);
        document.getElementById('detalle-bancos').textContent    = fmt(mov.bancos);
        document.getElementById('detalle-tarjetas').textContent  = fmt(mov.tarjetas);
        document.getElementById('detalle-total').textContent     = fmt(mov.total);

        const badgeTipo = document.getElementById('detalle-badge-tipo');
        if (mov.tipo === 'ingreso') {
            badgeTipo.innerHTML = '<span class="badge bg-success">Ingreso</span>';
        } else {
            badgeTipo.innerHTML = '<span class="badge bg-danger">Egreso</span>';
        }
    }

    function renderHeader(tipo) {
        const cfg = TIPOS_CONFIG[tipo] || TIPOS_CONFIG.manual;
        const icon  = document.getElementById('detalle-tipo-icon');
        const label = document.getElementById('detalle-tipo-label');
        const badge = document.getElementById('detalle-tipo-badge');
        const hdr   = document.getElementById('detalle-modal-header');

        icon.innerHTML  = `<i class="fas ${cfg.icon}"></i>`;
        icon.style.background = 'rgba(255,255,255,0.18)';
        label.textContent = cfg.label;
        badge.textContent = cfg.badge;
        hdr.style.background  = cfg.bg;
    }

    function renderComprobante(tipo, comp) {
        const seccion = document.getElementById('detalle-seccion-comprobante');
        seccion.style.display = '';

        const esVenta    = tipo === 'venta' || tipo === 'devolucion_venta';
        const esDevolucion = tipo.includes('devolucion');
        const contraparte = esVenta ? 'Cliente' : 'Proveedor';

        // Título + meta
        document.getElementById('detalle-comp-titulo').textContent =
            (esDevolucion ? 'Devolución sobre ' : '') + (esVenta ? 'Venta ' : 'Compra ') + (comp.num_folio || '');

        const meta = document.getElementById('detalle-comp-meta');
        meta.innerHTML = [
            comp.tipo_comprobante ? `<span><i class="fas fa-tag me-1"></i>${comp.tipo_comprobante}</span>` : '',
            comp.contraparte      ? `<span><i class="fas fa-user me-1"></i>${contraparte}: <strong>${comp.contraparte}</strong></span>` : '',
            comp.usuario          ? `<span><i class="fas fa-user-tie me-1"></i>Operador: <strong>${comp.usuario}</strong></span>` : '',
            comp.sucursal         ? `<span><i class="fas fa-store me-1"></i>${comp.sucursal}</span>` : '',
            comp.fecha            ? `<span><i class="fas fa-calendar me-1"></i>${comp.fecha}</span>` : '',
        ].filter(Boolean).join('');

        // Badge de estado
        const estadoEl = document.getElementById('detalle-comp-estado-badge');
        estadoEl.outerHTML = estadoEl.outerHTML; // reset
        document.getElementById('detalle-comp-estado-badge').innerHTML = estadoBadge(comp.estado);

        // Ítems
        const tbody = document.getElementById('detalle-comp-items');
        tbody.innerHTML = '';
        (comp.detalles || []).forEach((d, i) => {
            const nombreFull = d.combinacion ? `${d.articulo} <small class="text-muted">(${d.combinacion})</small>` : d.articulo;
            const tr = document.createElement('tr');
            tr.style.background = i % 2 === 0 ? '#f8fafc' : 'white';
            tr.innerHTML = `
                <td style="padding:10px 14px; border:none;">${nombreFull}</td>
                <td style="padding:10px 14px; border:none; text-align:right;">${d.cantidad}</td>
                <td style="padding:10px 14px; border:none; text-align:right;">${fmt(d.precio_unitario)}</td>
                <td style="padding:10px 14px; border:none; text-align:right;">${parseFloat(d.descuento || 0).toFixed(1)}%</td>
                <td style="padding:10px 14px; border:none; text-align:right;">${parseFloat(d.iva || 0).toFixed(1)}%</td>
                <td style="padding:10px 14px; border:none; text-align:right;">${fmt(d.subtotal_neto)}</td>
                <td style="padding:10px 14px; border:none; text-align:right; font-weight:700;">${fmt(d.subtotal_con_iva)}</td>
            `;
            tbody.appendChild(tr);
        });

        // Resumen financiero
        const resumen = document.getElementById('detalle-comp-resumen');
        let resumenHtml = `
            <tr>
                <td class="text-muted" style="border:none; padding:6px 4px;">Subtotal neto</td>
                <td class="text-end fw-bold" style="border:none; padding:6px 4px;">${fmt(comp.total_neto)}</td>
            </tr>
        `;
        if (comp.iva_discriminado && typeof comp.iva_discriminado === 'object') {
            Object.entries(comp.iva_discriminado).forEach(([pct, monto]) => {
                resumenHtml += `
                    <tr>
                        <td class="text-muted" style="border:none; padding:6px 4px;">IVA ${pct}%</td>
                        <td class="text-end" style="border:none; padding:6px 4px;">${fmt(monto)}</td>
                    </tr>
                `;
            });
        }
        resumenHtml += `
            <tr style="border-top:2px solid #e2e8f0;">
                <td class="fw-bold" style="border:none; padding:8px 4px; font-size:1rem; color:#0f172a;">TOTAL</td>
                <td class="text-end fw-bold" style="border:none; padding:8px 4px; font-size:1.05rem; color:#0f172a;">${fmt(comp.total_con_iva)}</td>
            </tr>
        `;
        resumen.innerHTML = resumenHtml;
    }

    function renderEcommerce(orden) {
        const seccion = document.getElementById('detalle-seccion-ecommerce');
        seccion.style.display = '';

        const meta = document.getElementById('detalle-ec-meta');
        meta.innerHTML = [
            { label: 'Pedido #', val: orden.order_id },
            { label: 'Fecha',    val: orden.fecha },
            { label: 'Cliente',  val: orden.cliente },
            { label: 'Estado',   val: orden.estado },
        ].map(f => `
            <div class="col-6 col-md-3">
                <div class="small text-uppercase fw-bold" style="color:#94a3b8; font-size:0.65rem; letter-spacing:.5px; margin-bottom:4px;">${f.label}</div>
                <div class="fw-bold" style="color:#0f172a; font-size:0.9rem;">${f.val || '—'}</div>
            </div>
        `).join('');

        const tbody = document.getElementById('detalle-ec-items');
        tbody.innerHTML = '';
        (orden.detalles || []).forEach((d, i) => {
            const tr = document.createElement('tr');
            tr.style.background = i % 2 === 0 ? '#f8fafc' : 'white';
            tr.innerHTML = `
                <td style="padding:10px 14px; border:none;">${d.articulo || '—'}</td>
                <td style="padding:10px 14px; border:none; text-align:right;">${d.cantidad}</td>
                <td style="padding:10px 14px; border:none; text-align:right;">${fmt(d.precio)}</td>
                <td style="padding:10px 14px; border:none; text-align:right; font-weight:700;">${fmt(d.subtotal)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    // ── Click en .btn-ver ──
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-ver');
        if (!btn) return;

        const movId   = btn.dataset.id;
        const tabla   = document.getElementById('table_movimientos');
        const cuentaId = tabla.dataset.cuentaId;

        resetModal();
        const modal = new bootstrap.Modal(document.getElementById('modalDetalleMovimiento'));
        modal.show();

        fetch(`/cuentas/${cuentaId}/movimientos/${movId}/detalle`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.estado !== 1) throw new Error(res.mensaje || 'Error al cargar');

            const { tipo, movimiento, comprobante, orden, link } = res;

            renderHeader(tipo);
            renderMovimientoBase(movimiento);

            // Link al registro de origen (venta, compra o pedido)
            const btnAbrir = document.getElementById('detalle-btn-abrir');
            if (btnAbrir) {
                if (link) {
                    btnAbrir.href = link;
                    btnAbrir.style.display = '';
                } else {
                    btnAbrir.style.display = 'none';
                }
            }

            if ((tipo === 'venta' || tipo === 'devolucion_venta' || tipo === 'compra' || tipo === 'devolucion_compra') && comprobante) {
                renderComprobante(tipo, comprobante);
            } else if (tipo === 'ecommerce' && orden) {
                renderEcommerce(orden);
            } else if (tipo === 'transferencia') {
                document.getElementById('detalle-seccion-transferencia').style.display = '';
            } else {
                document.getElementById('detalle-seccion-manual').style.display = '';
            }

            document.getElementById('detalle-loading').style.display   = 'none';
            document.getElementById('detalle-contenido').style.display = '';
        })
        .catch(err => {
            console.error(err);
            document.getElementById('detalle-loading').innerHTML =
                '<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-3"></i><br>No se pudo cargar el detalle.</div>';
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const formTransferencia = document.getElementById('formTransferencia');

    if (formTransferencia) {
        formTransferencia.addEventListener('submit', function (e) {
            e.preventDefault();

            const origen_id   = document.getElementById('trans_origen').value;
            const destino_id  = document.getElementById('trans_destino').value;
            const monto       = document.getElementById('trans_monto').value;
            const observaciones = document.getElementById('trans_observaciones').value;

            fetch('/cuentas/transferencias', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    origen_id,
                    destino_id,
                    monto,
                    observaciones
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', data.message, 'success');
                    // 🔹 Recargar la tabla de movimientos si está presente
                    if (window.table_movimientos) {
                        $('#table_movimientos').DataTable().ajax.reload();
                    }
                    // 🔹 Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('transferenciaModal'));
                    modal.hide();
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error al procesar la transferencia', 'error');
            });
        });
    }
});