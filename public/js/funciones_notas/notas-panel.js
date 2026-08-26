// Panel de notas embebible (ver resources/views/notas/_panel.blade.php).
// Puede usarse "estático" (tipo/id fijos desde el server) o "dinámico"
// (un modal compartido que llama notasPanelSetEntidad al abrirse con un id distinto).

function notasPanelCsrf() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function notasPanelRenderLista(panel, notas) {
    const lista = panel.querySelector('.np-lista');
    if (!notas || notas.length === 0) {
        lista.innerHTML = '<p class="text-muted small">Sin notas todavía.</p>';
        return;
    }
    lista.innerHTML = notas.map(n => `
        <div class="np-nota ${n.completada ? 'completada' : ''} ${n.vencida ? 'vencida' : ''}" data-id="${n.id}">
            <div class="np-acciones">
                <button type="button" class="np-completar" title="${n.completada ? 'Marcar como pendiente' : 'Marcar como hecha'}"><i class="fas fa-check"></i></button>
                <button type="button" class="np-borrar" title="Borrar"><i class="fas fa-trash-alt"></i></button>
            </div>
            <div>${n.contenido.replace(/</g, '&lt;')}</div>
            <div class="np-meta">
                ${n.autor || 'Sistema'} · ${n.fecha_creacion}
                ${n.fecha_recordatorio ? ' · Recordatorio: ' + n.fecha_recordatorio.split('-').reverse().join('/') : ''}
            </div>
        </div>
    `).join('');
}

function notasPanelCargar(panelId) {
    const panel = document.getElementById(panelId);
    if (!panel) return;
    const tipo = panel.dataset.tipo;
    const id = panel.dataset.id;
    if (!tipo || !id) {
        notasPanelRenderLista(panel, []);
        return;
    }
    fetch(`/notas/lista?tipo=${encodeURIComponent(tipo)}&id=${encodeURIComponent(id)}`)
        .then(r => r.json())
        .then(d => notasPanelRenderLista(panel, d.notas || []))
        .catch(() => {});
}

// Llamar cuando un modal compartido (ej. detalle de venta/compra) se abre con un id distinto.
function notasPanelSetEntidad(panelId, tipo, id) {
    const panel = document.getElementById(panelId);
    if (!panel) return;
    panel.dataset.tipo = tipo;
    panel.dataset.id = id;
    notasPanelCargar(panelId);
}

function initNotasPanel(panelId) {
    const panel = document.getElementById(panelId);
    if (!panel || panel.dataset.npInit) return;
    panel.dataset.npInit = '1';

    const toggleBtn = panel.querySelector('.np-toggle-form');
    const form = panel.querySelector('.np-form');
    toggleBtn.addEventListener('click', () => {
        form.style.display = form.style.display === 'none' ? '' : 'none';
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const contenido = panel.querySelector('.np-contenido').value.trim();
        if (!contenido) return;
        const fd = new FormData();
        fd.append('contenido', contenido);
        fd.append('fecha_recordatorio', panel.querySelector('.np-fecha').value);
        if (panel.dataset.tipo) fd.append('tipo', panel.dataset.tipo);
        if (panel.dataset.id) fd.append('id', panel.dataset.id);

        fetch('/notas', { method: 'POST', headers: { 'X-CSRF-TOKEN': notasPanelCsrf() }, body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.estado === 1) {
                    panel.querySelector('.np-contenido').value = '';
                    panel.querySelector('.np-fecha').value = '';
                    form.style.display = 'none';
                    notasPanelCargar(panelId);
                } else {
                    alert('No se pudo guardar la nota.');
                }
            })
            .catch(() => alert('Error al guardar la nota.'));
    });

    panel.querySelector('.np-lista').addEventListener('click', function (e) {
        const item = e.target.closest('.np-nota');
        if (!item) return;
        const id = item.dataset.id;

        if (e.target.closest('.np-completar')) {
            fetch(`/notas/${id}/completar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': notasPanelCsrf() } })
                .then(r => r.json())
                .then(() => notasPanelCargar(panelId));
        } else if (e.target.closest('.np-borrar')) {
            if (!confirm('¿Borrar esta nota?')) return;
            fetch(`/notas/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': notasPanelCsrf() } })
                .then(r => r.json())
                .then(() => notasPanelCargar(panelId));
        }
    });

    notasPanelCargar(panelId);
}
