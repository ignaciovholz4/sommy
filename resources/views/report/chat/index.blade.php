@extends('layouts.admin')

@section('title', 'Chat de Reportes')

@section('contenido')
@if(request('embed'))
<style>
    /* Modo embebido (burbuja flotante): solo el chat, sin header ni menú */
    .dg-header-top, .dg-nav-bar, .main-footer, footer, .dg-chat-burbuja { display: none !important; }
    .chat-wrap { grid-template-columns: 1fr !important; margin: 8px !important; max-width: 100% !important; }
    .chat-sesiones { display: none !important; }
    .chat-panel { height: calc(100vh - 16px) !important; }
    body, .content-wrapper { background: #fff !important; }
</style>
@endif
<style>
    .chat-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; max-width: 1100px; margin: 18px auto; display: grid; grid-template-columns: 260px 1fr; gap: 16px; }
    @media (max-width: 800px) { .chat-wrap { grid-template-columns: 1fr; } }

    .chat-sesiones { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; padding: 14px; height: fit-content; }
    .chat-sesiones h6 { font-weight: 700; font-size: 12px; text-transform: uppercase; color: #6E7A96; margin-bottom: 10px; }
    .chat-sesiones .sesion-item { display: block; width: 100%; text-align: left; background: none; border: none; padding: 9px 10px; border-radius: 10px; font-size: 13px; color: #1B2B5A; cursor: pointer; }
    .chat-sesiones .sesion-item:hover, .chat-sesiones .sesion-item.activa { background: #F1F4F9; }
    .chat-sesiones .btn-nueva { width: 100%; margin-bottom: 12px; }

    .chat-panel { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; display: flex; flex-direction: column; height: 70vh; }
    .chat-mensajes { flex: 1; overflow-y: auto; padding: 18px; }
    .chat-msg { max-width: 75%; margin-bottom: 12px; padding: 10px 14px; border-radius: 14px; font-size: 14px; line-height: 1.45; white-space: pre-wrap; }
    .chat-msg.user { background: #1B2B5A; color: #fff; margin-left: auto; border-bottom-right-radius: 4px; }
    .chat-msg.assistant { background: #F1F4F9; color: #1B2B5A; margin-right: auto; border-bottom-left-radius: 4px; }
    .chat-vacio { color: #94A3B8; text-align: center; margin-top: 40px; font-size: 14px; }

    .chat-input-wrap { border-top: 1px solid #E7EAF2; padding: 12px; display: flex; gap: 8px; }
    .chat-input-wrap input { flex: 1; border: 1.5px solid #E7EAF2; border-radius: 999px; padding: 10px 16px; font-size: 14px; }
    .chat-input-wrap button { border: none; background: #1B2B5A; color: #fff; border-radius: 999px; padding: 0 20px; font-weight: 600; }
    .chat-input-wrap button:disabled { opacity: .6; }
</style>

<div class="chat-wrap">
    <div class="chat-sesiones">
        <button class="btn btn-primary btn-nueva" id="btnNuevaSesion"><i class="fas fa-plus me-1"></i> Nueva consulta</button>
        <h6>Consultas anteriores</h6>
        <div id="listaSesiones">
            @forelse($sesiones as $s)
                <button class="sesion-item" data-id="{{ $s->id }}">{{ $s->titulo ?: 'Sin título' }}</button>
            @empty
                <p class="text-muted small">Todavía no hiciste ninguna consulta.</p>
            @endforelse
        </div>
    </div>

    <div class="chat-panel">
        <div class="chat-mensajes" id="chatMensajes">
            <div class="chat-vacio">
                Preguntale al analista de datos: "¿cuánto facturamos este mes?", "¿qué productos se vendieron más la semana pasada?",
                "¿quiénes son los principales deudores?", "¿cuál es el margen del último trimestre?"...
            </div>
        </div>
        <div class="chat-input-wrap">
            <input type="text" id="inputPregunta" placeholder="Escribí tu pregunta..." maxlength="1000">
            <button id="btnEnviar"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const chatMensajes = document.getElementById('chatMensajes');
    const inputPregunta = document.getElementById('inputPregunta');
    const btnEnviar = document.getElementById('btnEnviar');
    const listaSesiones = document.getElementById('listaSesiones');
    let sesionActualId = null;

    function pintarMensajes(mensajes) {
        chatMensajes.innerHTML = '';
        if (!mensajes.length) {
            chatMensajes.innerHTML = '<div class="chat-vacio">Escribí tu primera pregunta abajo.</div>';
            return;
        }
        mensajes.forEach(m => {
            const div = document.createElement('div');
            div.className = 'chat-msg ' + m.role;
            div.textContent = m.content;
            chatMensajes.appendChild(div);
        });
        chatMensajes.scrollTop = chatMensajes.scrollHeight;
    }

    function agregarMensaje(role, content) {
        if (chatMensajes.querySelector('.chat-vacio')) chatMensajes.innerHTML = '';
        const div = document.createElement('div');
        div.className = 'chat-msg ' + role;
        div.textContent = content;
        chatMensajes.appendChild(div);
        chatMensajes.scrollTop = chatMensajes.scrollHeight;
        return div;
    }

    function marcarActiva(id) {
        listaSesiones.querySelectorAll('.sesion-item').forEach(b => b.classList.toggle('activa', b.dataset.id == id));
    }

    function abrirSesion(id) {
        sesionActualId = id;
        marcarActiva(id);
        fetch(`/reportes/chat/sesion/${id}`)
            .then(r => r.json())
            .then(data => pintarMensajes(data.mensajes));
    }

    document.getElementById('btnNuevaSesion').addEventListener('click', () => {
        fetch('/reportes/chat/sesion', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf }
        })
        .then(r => r.json())
        .then(data => {
            sesionActualId = data.sesion_id;
            const btn = document.createElement('button');
            btn.className = 'sesion-item';
            btn.dataset.id = data.sesion_id;
            btn.textContent = 'Nueva consulta';
            btn.addEventListener('click', () => abrirSesion(data.sesion_id));
            listaSesiones.prepend(btn);
            pintarMensajes([]);
            marcarActiva(data.sesion_id);
            inputPregunta.focus();
        });
    });

    listaSesiones.querySelectorAll('.sesion-item').forEach(btn => {
        btn.addEventListener('click', () => abrirSesion(btn.dataset.id));
    });

    function enviar() {
        const pregunta = inputPregunta.value.trim();
        if (!pregunta) return;

        if (!sesionActualId) {
            fetch('/reportes/chat/sesion', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } })
                .then(r => r.json())
                .then(data => {
                    sesionActualId = data.sesion_id;
                    const btn = document.createElement('button');
                    btn.className = 'sesion-item activa';
                    btn.dataset.id = data.sesion_id;
                    btn.textContent = 'Nueva consulta';
                    btn.addEventListener('click', () => abrirSesion(data.sesion_id));
                    listaSesiones.prepend(btn);
                    enviarPregunta(pregunta);
                });
        } else {
            enviarPregunta(pregunta);
        }
    }

    function enviarPregunta(pregunta) {
        agregarMensaje('user', pregunta);
        inputPregunta.value = '';
        inputPregunta.disabled = true;
        btnEnviar.disabled = true;

        const pensando = agregarMensaje('assistant', 'Pensando...');

        fetch(`/reportes/chat/sesion/${sesionActualId}/enviar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ pregunta })
        })
        .then(r => r.json())
        .then(data => {
            pensando.textContent = data.respuesta || 'No obtuve respuesta.';
            if (data.titulo) {
                const btnSesion = listaSesiones.querySelector(`[data-id="${sesionActualId}"]`);
                if (btnSesion) btnSesion.textContent = data.titulo;
            }
        })
        .catch(() => {
            pensando.textContent = 'Error de conexión, probá de nuevo.';
        })
        .finally(() => {
            inputPregunta.disabled = false;
            btnEnviar.disabled = false;
            inputPregunta.focus();
        });
    }

    btnEnviar.addEventListener('click', enviar);
    inputPregunta.addEventListener('keydown', e => {
        if (e.key === 'Enter') enviar();
    });
});
</script>
@endsection
