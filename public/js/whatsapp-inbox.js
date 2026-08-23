/* Bandeja de WhatsApp — polling + hilo + acciones */
(function ($) {
    'use strict';

    var C = window.WA_CONFIG;
    var state = {
        currentId: null,
        lastMsgId: 0,
        sessionOpen: false,
        pollTimer: null,
        conversations: [],
    };

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': C.csrf } });

    function esc(text) {
        return $('<div>').text(text == null ? '' : text).html();
    }

    // ---------- Lista de conversaciones ----------

    var CHANNEL_ICONS = {
        whatsapp: '<i class="fab fa-whatsapp" style="color:#1EBE5A"></i>',
        messenger: '<i class="fab fa-facebook-messenger" style="color:#1877F2"></i>',
        instagram: '<i class="fab fa-instagram" style="color:#C13584"></i>'
    };

    function loadList() {
        $.get(C.base + '/conversations', {
            status: $('#f-status').val(),
            assigned: $('#f-assigned').val(),
            channel: $('#f-channel').val(),
            q: $('#f-q').val()
        }).done(function (res) {
            state.conversations = res.conversations;
            renderList();
        });
    }

    function renderList() {
        var $list = $('#conv-list').empty();
        if (!state.conversations.length) {
            $list.append('<div class="text-center text-muted p-4" style="font-size:13px">Sin conversaciones<br>por ahora 😴</div>');
            return;
        }
        state.conversations.forEach(function (c) {
            var name = c.cliente_nombre || c.profile_name || c.phone;
            var badges = '';
            if (c.mode === 'bot') badges += '<span class="wa-badge" style="background:#7C3AED">🤖 bot</span>';
            if (c.assigned_user) badges += '<span class="wa-badge" style="background:#2563EB">' + esc(c.assigned_user) + '</span>';
            var statusColors = { nueva: '#F59E0B', en_atencion: '#10B981', esperando_cliente: '#6B7280', cerrada: '#94A3B8' };
            badges += '<span class="wa-badge" style="background:' + statusColors[c.status] + '">' + c.status.replace('_', ' ') + '</span>';
            (c.tags || []).forEach(function (t) {
                badges += '<span class="wa-badge" style="background:' + t.color + '">' + esc(t.nombre) + '</span>';
            });

            var $item = $('<div class="wa-conv-item' + (c.id === state.currentId ? ' active' : '') + '">')
                .data('id', c.id)
                .append(
                    '<div class="wa-conv-name"><span>' + (CHANNEL_ICONS[c.channel] || '') + ' ' + esc(name) + '</span>' +
                    (c.unread_count > 0 ? '<span class="wa-unread">' + c.unread_count + '</span>' : '<small class="text-muted">' + (c.last_message_human || '') + '</small>') +
                    '</div>' +
                    (c.phone && c.phone !== name ? '<div style="font-size:11.5px;color:#059669;font-weight:600;"><i class="fab fa-whatsapp"></i> ' + esc(c.phone) + '</div>' : '') +
                    '<div class="wa-conv-prev">' + esc(c.last_message_preview || '') + '</div>' +
                    '<div class="wa-conv-meta">' + badges + '</div>'
                );
            $list.append($item);
        });
    }

    $('#conv-list').on('click', '.wa-conv-item', function () {
        openConversation($(this).data('id'));
    });

    $('#f-status, #f-assigned, #f-channel').on('change', loadList);
    var qTimer;
    $('#f-q').on('input', function () { clearTimeout(qTimer); qTimer = setTimeout(loadList, 350); });

    // ---------- Hilo ----------

    function openConversation(id) {
        state.currentId = id;
        state.lastMsgId = 0;
        $('.wa-wrap').addClass('mobile-thread').removeClass('show-info');
        $('#thread-empty').hide();
        $('#thread-content').css('display', 'flex');
        $('#msgs').empty().append('<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i></div>');
        fetchThread(true);
        renderList();
    }

    function fetchThread(initial) {
        if (!state.currentId) return;
        var params = initial ? {} : { after_id: state.lastMsgId };
        $.get(C.base + '/conversations/' + state.currentId, params).done(function (res) {
            if (initial) $('#msgs').empty();
            updateHeader(res.conversation);
            state.sessionOpen = res.session_open;
            updateComposerMode();
            res.messages.forEach(appendMessage);
            if (res.messages.length) scrollBottom();
            if (initial) {
                renderClientePanel(res.cliente);
                renderDrafts(res.drafts || []);
            }
        });
    }

    function updateHeader(c) {
        state.channel = c.channel;
        $('#th-name').html((CHANNEL_ICONS[c.channel] || '') + ' ' + esc(c.cliente_nombre || c.profile_name || c.phone));
        $('#th-phone').text((c.phone || 'número no disponible') + (c.cliente_nombre && c.profile_name ? ' · ' + c.profile_name : ''));
        $('#th-status').val(c.status);
        var $mode = $('#th-mode');
        if (c.mode === 'bot') {
            $mode.removeClass('wa-mode-humano').addClass('wa-mode-bot').html('🤖 Bot atendiendo');
        } else {
            $mode.removeClass('wa-mode-bot').addClass('wa-mode-humano').html('👤 Atención humana');
        }
        renderTags(c.tags || []);
    }

    function appendMessage(m) {
        if (m.id > state.lastMsgId) state.lastMsgId = m.id;

        var cls = m.is_internal_note ? 'note' : m.direction;
        var content = '';
        if (m.has_media && m.media_mime && m.media_mime.indexOf('image') === 0) {
            content += '<a href="' + m.media_url + '" target="_blank"><img src="' + m.media_url + '"></a>';
        } else if (m.has_media && m.media_mime && m.media_mime.indexOf('audio') === 0) {
            content += '<audio controls style="max-width:220px" src="' + m.media_url + '"></audio>';
        } else if (m.has_media) {
            content += '<a href="' + m.media_url + '" target="_blank"><i class="fas fa-paperclip"></i> Adjunto</a><br>';
        } else if (m.type !== 'text' && m.type !== 'template' && !m.body) {
            content += '<em class="text-muted">[' + m.type + ']</em>';
        }
        if (m.body) content += esc(m.body);

        var meta = m.time || '';
        if (m.direction === 'out' && !m.is_internal_note) {
            var ticks = { pending: '🕓', sent: '✓', delivered: '✓✓', read: '<span style="color:#2563EB">✓✓</span>', failed: '<span style="color:#DC2626">✗ falló</span>' };
            meta += ' ' + (ticks[m.status] || '');
            if (m.status === 'failed' && m.error_detail) meta += ' <span title="' + esc(m.error_detail) + '">ℹ️</span>';
        }
        var sender = (m.sent_by && !m.is_internal_note) ? '<div class="sender">' + esc(m.sent_by) + '</div>' : '';
        var noteTag = m.is_internal_note ? '<div class="sender">📌 Nota interna' + (m.sent_by ? ' — ' + esc(m.sent_by) : '') + '</div>' : '';

        $('#msgs').append('<div class="wa-msg ' + cls + '" data-id="' + m.id + '">' + noteTag + sender + content + '<div class="meta">' + meta + '</div></div>');
    }

    function scrollBottom() {
        var el = document.getElementById('msgs');
        if (el) el.scrollTop = el.scrollHeight;
    }

    // ---------- Navegación mobile ----------

    $('#btn-back').on('click', function () {
        $('.wa-wrap').removeClass('mobile-thread show-info');
    });

    $('#btn-info-toggle').on('click', function () {
        $('.wa-wrap').toggleClass('show-info');
    });

    // ---------- Composer ----------

    function updateComposerMode() {
        if (state.sessionOpen) {
            $('#session-closed').hide();
            $('#composer-text').show();
            $('#composer-template').hide();
            $('#btn-send').prop('disabled', false);
        } else if (state.channel === 'whatsapp') {
            $('#session-closed').html('⏳ Pasaron más de 24 h del último mensaje del cliente: solo se pueden enviar <strong>plantillas aprobadas</strong>.').show();
            $('#composer-text').hide();
            $('#composer-template').show();
            $('#btn-send').prop('disabled', false);
        } else {
            $('#session-closed').html('⏳ Pasaron más de 24 h del último mensaje: en este canal hay que esperar a que el cliente vuelva a escribir.').show();
            $('#composer-text').hide();
            $('#composer-template').hide();
            $('#btn-send').prop('disabled', true);
        }
    }

    $('#template-select').on('change', function () {
        var body = $(this).find(':selected').data('body') || '';
        var matches = body.match(/\{\{\d+\}\}/g) || [];
        var $params = $('#template-params').empty();
        matches.forEach(function (ph, i) {
            $params.append('<input type="text" class="form-control form-control-sm mb-1 tpl-param" placeholder="Valor para ' + ph + '">');
        });
    });

    function send() {
        if (!state.currentId) return;
        var payload;
        if (state.sessionOpen) {
            var text = $('#composer-text').val().trim();
            if (!text) return;
            payload = { body: text };
        } else {
            var tplId = $('#template-select').val();
            if (!tplId) { toastr.warning('Elegí una plantilla para enviar.'); return; }
            var params = $('.tpl-param').map(function () { return $(this).val(); }).get();
            payload = { template_id: tplId, template_params: params };
        }

        $('#btn-send').prop('disabled', true);
        $.post(C.base + '/conversations/' + state.currentId + '/send', payload)
            .done(function (res) {
                $('#composer-text').val('');
                appendMessage(res.message);
                scrollBottom();
                loadList();
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && (xhr.responseJSON.mensaje || xhr.responseJSON.message)) || 'No se pudo enviar';
                toastr.error(msg);
                if (xhr.status === 422) fetchThread(true);
            })
            .always(function () { $('#btn-send').prop('disabled', false); });
    }

    $('#btn-send').on('click', send);
    $('#composer-text').on('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
    });

    $('#btn-note').on('click', function () {
        var text = $('#composer-text').val().trim();
        if (!text || !state.currentId) { toastr.info('Escribí la nota en el campo de texto y tocá el botón de nota.'); return; }
        $.post(C.base + '/conversations/' + state.currentId + '/note', { body: text }).done(function (res) {
            $('#composer-text').val('');
            appendMessage(res.message);
            scrollBottom();
        });
    });

    // ---------- Acciones del header ----------

    $('#btn-assign-me').on('click', function () { assign(C.userId); });
    $(document).on('click', '.assign-user', function (e) { e.preventDefault(); assign($(this).data('id')); });

    function assign(userId) {
        if (!state.currentId) return;
        $.post(C.base + '/conversations/' + state.currentId + '/assign', { user_id: userId }).done(function (res) {
            updateHeader(res.conversation);
            loadList();
            toastr.success('Conversación asignada.');
        });
    }

    $('#th-status').on('change', function () {
        if (!state.currentId) return;
        $.post(C.base + '/conversations/' + state.currentId + '/status', { status: $(this).val() }).done(loadList);
    });

    $('#th-mode').on('click', function () {
        if (!state.currentId) return;
        var newMode = $(this).hasClass('wa-mode-bot') ? 'humano' : 'bot';
        $.post(C.base + '/conversations/' + state.currentId + '/mode', { mode: newMode }).done(function () {
            fetchThread(true);
            toastr.info(newMode === 'bot' ? 'El bot vuelve a atender esta conversación.' : 'Bot silenciado: atención humana.');
        });
    });

    // ---------- Ficha de cliente ----------

    function renderClientePanel(cliente) {
        var $block = $('#info-cliente').empty().append('<h6>Cliente</h6>');
        if (!cliente) {
            $block.append(
                '<div class="text-muted mb-2" style="font-size:13px">Contacto sin vincular a un cliente.</div>' +
                '<button class="btn btn-sm btn-primary btn-block" data-toggle="modal" data-target="#modal-link"><i class="fas fa-link"></i> Vincular / crear cliente</button>'
            );
            return;
        }
        var pedidos = (cliente.pedidos || []).map(function (p) {
            return '<div style="font-size:12px" class="d-flex justify-content-between">' +
                '<a href="/orders/order/' + p.order_id + '" target="_blank">#' + p.order_id + '</a>' +
                '<span>$' + Number(p.total_amount).toLocaleString('es-AR') + '</span></div>';
        }).join('') || '<div class="text-muted" style="font-size:12px">Sin pedidos</div>';

        $block.append(
            '<div style="font-size:13.5px"><strong>' + esc(cliente.nombre) + '</strong></div>' +
            '<div style="font-size:12.5px" class="text-muted">' + esc(cliente.telefono || '') + (cliente.email ? '<br>' + esc(cliente.email) : '') + '</div>' +
            (cliente.direccion ? '<div style="font-size:12.5px" class="text-muted">' + esc(cliente.direccion) + (cliente.localidad ? ', ' + esc(cliente.localidad) : '') + '</div>' : '') +
            '<div style="font-size:12.5px" class="mt-1">CC: <strong class="' + (cliente.saldo_cc > 0 ? 'text-danger' : 'text-success') + '">$' + Number(cliente.saldo_cc).toLocaleString('es-AR') + '</strong></div>' +
            '<a href="/clientes" target="_blank" style="font-size:12px">Ver ficha completa</a>' +
            '<hr class="my-2"><h6>Últimos pedidos</h6>' + pedidos
        );
    }

    // Vincular cliente
    var searchTimer;
    $('#link-search').on('input', function () {
        var q = $(this).val();
        clearTimeout(searchTimer);
        if (q.length < 2) { $('#link-results').empty(); return; }
        searchTimer = setTimeout(function () {
            $.get(C.base + '/clientes/buscar', { q: q }).done(function (rows) {
                var $r = $('#link-results').empty();
                rows.forEach(function (c) {
                    $r.append('<a href="#" class="list-group-item list-group-item-action py-1 link-pick" data-id="' + c.idcliente + '" style="font-size:13px">' +
                        esc((c.nombre || '') + ' ' + (c.paterno || '')) + ' <small class="text-muted">' + esc(c.telefono || '') + '</small></a>');
                });
            });
        }, 300);
    });

    $(document).on('click', '.link-pick', function (e) {
        e.preventDefault();
        linkCliente({ cliente_id: $(this).data('id') });
    });

    $('#btn-create-cliente').on('click', function () {
        var nombre = $('#link-nombre').val().trim();
        if (!nombre) { toastr.warning('Ingresá el nombre.'); return; }
        linkCliente({ nombre: nombre, email: $('#link-email').val(), direccion: $('#link-direccion').val() });
    });

    function linkCliente(payload) {
        $.post(C.base + '/conversations/' + state.currentId + '/link-cliente', payload).done(function (res) {
            $('#modal-link').modal('hide');
            renderClientePanel(res.cliente);
            fetchThread(true);
            toastr.success('Cliente vinculado.');
        });
    }

    // ---------- Etiquetas ----------

    function renderTags(tags) {
        var $c = $('#tag-container').empty();
        tags.forEach(function (t) {
            $c.append('<span class="wa-badge mr-1 tag-chip" data-id="' + t.id + '" style="background:' + t.color + ';cursor:pointer" title="Clic para quitar">' + esc(t.nombre) + ' ×</span>');
        });
        if (!tags.length) $c.append('<span class="text-muted" style="font-size:12px">Sin etiquetas</span>');
    }

    $('#btn-tag').on('click', function () {
        var tagId = $('#tag-select').val();
        if (!tagId || !state.currentId) return;
        toggleTag(tagId);
    });
    $(document).on('click', '.tag-chip', function () { toggleTag($(this).data('id')); });

    function toggleTag(tagId) {
        $.post(C.base + '/conversations/' + state.currentId + '/tag', { tag_id: tagId }).done(function (res) {
            renderTags(res.tags);
            loadList();
        });
    }

    // ---------- Borradores de pedido (drafts) ----------

    function renderDrafts(drafts) {
        var $block = $('#info-drafts');
        var $c = $('#draft-container').empty();
        if (!drafts.length) { $block.hide(); return; }
        $block.show();
        drafts.forEach(function (d) {
            var items = (d.items || []).map(function (i) {
                return i.cantidad + 'x ' + (i.descripcion || ('#' + i.producto_id));
            }).join('<br>');
            var badge = d.status === 'pendiente_confirmacion'
                ? '<span class="badge badge-warning">por confirmar</span>'
                : '<span class="badge badge-secondary">cotizando</span>';
            var actions = (d.status === 'pendiente_confirmacion' && C.canConfirm)
                ? '<div class="mt-1"><button class="btn btn-xs btn-success btn-sm py-0 draft-confirm" data-id="' + d.id + '">✔ Confirmar</button> ' +
                  '<button class="btn btn-xs btn-outline-danger btn-sm py-0 draft-reject" data-id="' + d.id + '">✗ Rechazar</button></div>'
                : '';
            $c.append('<div class="wa-draft-card">' + badge + '<div>' + items + '</div><strong>Total: $' + Number(d.total).toLocaleString('es-AR') + '</strong>' + actions + '</div>');
        });
    }

    $(document).on('click', '.draft-confirm', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: '¿Confirmar el pedido?',
            text: 'Se crea el pedido real (canal WhatsApp) y se le avisa al cliente.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.post(C.base + '/drafts/' + id + '/confirm', {}).done(function (res) {
                toastr.success('Pedido #' + res.order_id + ' creado.');
                fetchThread(true);
                loadDraftsBadge();
            }).fail(function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.mensaje) || 'No se pudo confirmar');
            });
        });
    });

    $(document).on('click', '.draft-reject', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Rechazar borrador',
            input: 'text',
            inputPlaceholder: 'Motivo (opcional)',
            showCancelButton: true,
            confirmButtonText: 'Rechazar',
            cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.post(C.base + '/drafts/' + id + '/reject', { motivo: r.value || '' }).done(function () {
                toastr.info('Borrador rechazado.');
                fetchThread(true);
                loadDraftsBadge();
            });
        });
    });

    // Badge global + modal de pendientes
    function loadDraftsBadge() {
        $.get(C.base + '/drafts/pending').done(function (res) {
            if (res.count > 0) {
                $('#btn-drafts').show();
                $('#drafts-count').text(res.count);
            } else {
                $('#btn-drafts').hide();
            }
            window._pendingDrafts = res.drafts;
        });
    }

    $('#btn-drafts').on('click', function () {
        var $b = $('#drafts-body').empty();
        (window._pendingDrafts || []).forEach(function (d) {
            var who = (d.cliente ? (d.cliente.nombre + ' ' + (d.cliente.paterno || '')) : null)
                || (d.conversation && d.conversation.profile_name)
                || (d.conversation && d.conversation.phone_e164) || '—';
            var items = (d.items || []).map(function (i) { return i.cantidad + 'x ' + (i.descripcion || i.producto_id); }).join(', ');
            $b.append('<div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center">' +
                '<div><strong>' + esc(who) + '</strong><br><span style="font-size:13px">' + esc(items) + '</span><br><strong>$' + Number(d.total).toLocaleString('es-AR') + '</strong></div>' +
                '<button class="btn btn-sm btn-primary open-conv" data-id="' + d.conversation_id + '">Abrir chat</button></div>');
        });
        if (!(window._pendingDrafts || []).length) $b.append('<div class="text-muted">No hay pedidos pendientes.</div>');
        $('#modal-drafts').modal('show');
    });

    $(document).on('click', '.open-conv', function () {
        $('#modal-drafts').modal('hide');
        openConversation($(this).data('id'));
    });

    // ---------- Polling ----------

    function poll() {
        loadList();
        loadDraftsBadge();
        if (state.currentId) fetchThread(false);
    }

    loadList();
    loadDraftsBadge();
    state.pollTimer = setInterval(poll, 5000);

    // Deep-link: /whatsapp?conv=ID abre esa conversación (ej: viniendo del tablero)
    var convParam = new URLSearchParams(window.location.search).get('conv');
    if (convParam) openConversation(parseInt(convParam, 10));

})(jQuery);
