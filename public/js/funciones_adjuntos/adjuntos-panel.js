// Panel de adjuntos embebible (ver resources/views/adjuntos/_panel.blade.php).
// Puede usarse "estático" (tipo/id fijos desde el server) o "dinámico"
// (un modal compartido que llama adjuntosPanelSetEntidad al abrirse con un id distinto).

function adjuntosPanelCsrf() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function adjuntosPanelRenderLista(panel, items) {
    const cont = panel.querySelector('.ap-items');
    if (!items || items.length === 0) {
        cont.innerHTML = '<p class="text-muted small mb-0">Sin adjuntos todavía.</p>';
        return;
    }
    cont.innerHTML = items.map(a => `
        <div class="ap-item" title="${a.nombre.replace(/"/g, '')}">
            <a href="${a.url}" target="_blank" rel="noopener">
                ${a.es_imagen
                    ? `<img src="${a.url}" alt="">`
                    : `<div class="ap-filebox"><i class="fas fa-file-alt"></i></div>`}
                <span class="ap-nombre">${a.nombre}</span>
            </a>
        </div>
    `).join('');
}

function adjuntosPanelCargar(panelId) {
    const panel = document.getElementById(panelId);
    if (!panel) return;
    const tipo = panel.dataset.tipo;
    const id = panel.dataset.id;
    if (!tipo || !id) {
        adjuntosPanelRenderLista(panel, []);
        return;
    }
    fetch(`/adjuntos/lista?tipo=${encodeURIComponent(tipo)}&id=${encodeURIComponent(id)}`)
        .then(r => r.json())
        .then(d => adjuntosPanelRenderLista(panel, d.adjuntos || []))
        .catch(() => {});
}

// Llamar cuando un modal compartido (ej. detalle de venta/compra) se abre con un id distinto.
function adjuntosPanelSetEntidad(panelId, tipo, id) {
    const panel = document.getElementById(panelId);
    if (!panel) return;
    panel.dataset.tipo = tipo;
    panel.dataset.id = id;
    adjuntosPanelCargar(panelId);
}

function initAdjuntosPanel(panelId) {
    const panel = document.getElementById(panelId);
    if (!panel || panel.dataset.apInit) return;
    panel.dataset.apInit = '1';

    const input = panel.querySelector('.ap-input');
    const btn = panel.querySelector('.ap-upload-btn');

    input.addEventListener('change', function () {
        const file = input.files[0];
        if (!file) return;
        if (!panel.dataset.tipo || !panel.dataset.id) {
            alert('Guardá primero el registro antes de adjuntar archivos.');
            input.value = '';
            return;
        }

        const original = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Subiendo...';

        const fd = new FormData();
        fd.append('archivo', file);
        fd.append('tipo', panel.dataset.tipo);
        fd.append('id', panel.dataset.id);

        fetch('/adjuntos', { method: 'POST', headers: { 'X-CSRF-TOKEN': adjuntosPanelCsrf() }, body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.estado === 1) {
                    adjuntosPanelCargar(panelId);
                } else {
                    alert(d.mensaje || 'No se pudo subir el archivo.');
                }
            })
            .catch(() => alert('Error al subir el archivo.'))
            .finally(() => {
                btn.innerHTML = original;
                input.value = '';
            });
    });

    adjuntosPanelCargar(panelId);
}
