@extends('layouts.admin')

@section('title', 'Conocimiento del producto')

@section('contenido')
<style>
    .con-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1050px; margin: 0 auto; }
    .con-volver { font-size: 13.5px; color: #2563EB; text-decoration: none; }
    .con-title { font-size: 21px; font-weight: 600; margin: 8px 0 2px; }
    .con-sub { font-size: 13px; color: #6E7A96; font-weight: 300; margin-bottom: 18px; }
    .con-interno {
        display: inline-block; background: #FEF3C7; color: #92400E; border-radius: 999px;
        font-size: 11px; font-weight: 600; padding: 4px 14px; margin-left: 8px; vertical-align: middle;
    }

    .con-grid { display: grid; grid-template-columns: 340px 1fr; gap: 16px; align-items: start; }
    @media (max-width: 991px) { .con-grid { grid-template-columns: 1fr; } }

    .con-panel {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.08); padding: 18px;
    }
    .con-panel h3 { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #47536F; margin-bottom: 12px; }
    .con-panel label { font-size: 13px; font-weight: 500; margin: 10px 0 4px; display: block; }
    .con-panel select, .con-panel input[type=text], .con-panel textarea, .con-panel input[type=file] {
        width: 100%; border: 1px solid #E7EAF2; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; color: #1B2B5A; font-family: 'Poppins', sans-serif;
    }
    .con-panel textarea { min-height: 140px; line-height: 1.55; }

    .con-btn {
        border: none; border-radius: 999px; padding: 10px 24px; font-size: 13.5px; font-weight: 500;
        cursor: pointer; background: #1B2B5A; color: #fff; margin-top: 12px;
    }
    .con-btn:hover { background: #2563EB; }

    .con-item {
        border: 1px solid #E7EAF2; border-radius: 14px; background: #F8FAFC;
        padding: 14px 16px; margin-bottom: 10px; position: relative;
    }
    .con-item .tit { font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .con-item .tit i { color: #2563EB; }
    .con-item .tipo {
        display: inline-block; background: #E0F2FE; color: #1B2B5A; border-radius: 999px;
        font-size: 10px; font-weight: 600; padding: 2px 10px;
    }
    .con-item .cuerpo { font-size: 13px; color: #47536F; margin-top: 8px; white-space: pre-wrap; line-height: 1.6; }
    .con-item img { max-width: 240px; border-radius: 10px; margin-top: 8px; display: block; }
    .con-item video, .con-item audio { max-width: 100%; margin-top: 8px; display: block; border-radius: 10px; }
    .con-item .del {
        position: absolute; top: 12px; right: 14px; border: none; background: none;
        color: #b4552d; cursor: pointer; font-size: 13px;
    }
    .con-vacio { text-align: center; color: #94A3B8; font-size: 13px; font-weight: 300; padding: 30px; }
    .con-aviso { font-size: 11.5px; color: #6E7A96; font-weight: 300; margin-top: 6px; }
</style>

<div class="con-wrap">
    <a href="{{ url('almacen/articulo') }}" class="con-volver"><i class="fas fa-arrow-left"></i> Productos</a>
    <div class="con-title">
        <i class="fas fa-brain" style="color:#2563EB;"></i> Conocimiento: {{ $articulo->nombre }}
        <span class="con-interno"><i class="fas fa-lock"></i> Solo interno — no se ve en el ecommerce</span>
    </div>
    <div class="con-sub">Todo lo que cargues acá lo usan el bot de ventas del CRM (responde con esta info por WhatsApp) y el Estudio de Publicaciones (para generar contenido con contexto real).</div>

    <div style="background:#fff;border-radius:12px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:10px;box-shadow:0 2px 8px rgba(15,23,42,.06);">
        <div>
            <b><i class="fas fa-robot" style="color:#2563EB;"></i> Ofrecer este producto en el bot de ventas</b>
            <div class="con-sub" style="margin:2px 0 0;">Si está apagado, el bot no lo menciona ni lo recomienda por WhatsApp.</div>
        </div>
        <button id="btn-bot-toggle" class="btn {{ $articulo->bot_ofrecer ? 'btn-success' : 'btn-outline-secondary' }}" style="border-radius:999px;min-width:130px;font-weight:700;">
            {{ $articulo->bot_ofrecer ? 'ACTIVADO' : 'APAGADO' }}
        </button>
    </div>
    <script>
        document.getElementById('btn-bot-toggle').addEventListener('click', function () {
            fetch('{{ route('articulo.bot.toggle', $articulo->idarticulo) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => r.json()).then(d => {
                this.className = 'btn ' + (d.bot_ofrecer ? 'btn-success' : 'btn-outline-secondary');
                this.style.cssText = 'border-radius:999px;min-width:130px;font-weight:700;';
                this.textContent = d.bot_ofrecer ? 'ACTIVADO' : 'APAGADO';
            });
        });
    </script>

    @if(session('con_ok'))
        <div class="alert alert-success" style="border-radius:12px;">{{ session('con_ok') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="border-radius:12px;">{{ $errors->first() }}</div>
    @endif

    <div class="con-grid">
        {{-- Alta --}}
        <div class="con-panel">
            <h3><i class="fas fa-plus-circle"></i> Agregar conocimiento</h3>
            <form method="POST" action="{{ url('articulo/' . $articulo->idarticulo . '/conocimiento') }}" enctype="multipart/form-data">
                @csrf
                <label>Tipo</label>
                <select name="tipo" id="conTipo" onchange="conCambiarTipo()">
                    @foreach($tipos as $valor => $etiqueta)
                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                    @endforeach
                </select>

                <label>Título</label>
                <input type="text" name="titulo" placeholder="Ej: Cómo rotar el colchón / Video demo pillow top" required>

                <div id="conCampoTexto">
                    <label>Contenido</label>
                    <textarea name="contenido" placeholder="Escribí el conocimiento: instrucciones, materiales, respuestas a preguntas típicas..."></textarea>
                </div>

                <div id="conCampoArchivo" style="display:none;">
                    <label>Archivo</label>
                    <input type="file" name="archivo" id="conArchivo">
                    <div class="con-aviso">Imágenes, videos (mp4/mov/webm), audios (mp3/wav/ogg/m4a) o PDF. Hasta 50 MB.</div>
                </div>

                <label>Prioridad para el bot</label>
                <input type="number" name="prioridad" min="0" max="10" value="0">
                <div class="con-aviso">De 0 a 10. El bot usa primero lo de mayor prioridad — marcá con un número alto la foto principal o el video que querés que mande primero cuando presente el producto.</div>

                <button type="submit" class="con-btn"><i class="fas fa-save"></i> Guardar</button>
                <div class="con-aviso"><i class="fas fa-cloud"></i> Se guarda en el almacenamiento del sistema (respaldado con el servidor; configurable a nube S3 con CONOCIMIENTO_DISK).</div>
            </form>
        </div>

        {{-- Listado --}}
        <div>
            @forelse($items as $item)
            <div class="con-item" id="con-item-{{ $item->id }}">
                <button class="del" onclick="conEliminar({{ $item->id }}, this)" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                <button class="del" style="right:44px;color:#2563EB;" onclick="conEditar({{ $item->id }})" title="Editar"><i class="fas fa-edit"></i></button>
                <div class="tit">
                    <i class="fas {{ ['instrucciones' => 'fa-list-ol', 'caracteristicas' => 'fa-cogs', 'faq' => 'fa-question-circle', 'nota' => 'fa-sticky-note', 'imagen' => 'fa-image', 'video' => 'fa-video', 'audio' => 'fa-microphone', 'documento' => 'fa-file-pdf'][$item->tipo] ?? 'fa-file' }}"></i>
                    <span class="con-item-titulo">{{ $item->titulo }}</span>
                    <span class="tipo">{{ $tipos[$item->tipo] ?? $item->tipo }}</span>
                    @if($item->prioridad > 0)
                        <span class="tipo" style="background:#DCFCE7;color:#166534;" title="Prioridad para el bot">★ {{ $item->prioridad }}</span>
                    @endif
                </div>
                @if($item->contenido)
                    <div class="cuerpo con-item-contenido">{{ $item->contenido }}</div>
                @endif
                <div class="con-item-editor" style="display:none;margin-top:8px;">
                    <input type="text" class="form-control con-edit-titulo" maxlength="150" value="{{ $item->titulo }}" style="margin-bottom:6px;">
                    @if($item->esTexto())
                        <textarea class="form-control con-edit-contenido" rows="5" maxlength="8000">{{ $item->contenido }}</textarea>
                    @endif
                    <label style="margin-top:6px;">Prioridad para el bot (0 a 10)</label>
                    <input type="number" class="form-control con-edit-prioridad" min="0" max="10" value="{{ $item->prioridad }}">
                    <div style="display:flex;gap:6px;margin-top:8px;">
                        <button type="button" class="btn btn-sm btn-primary" onclick="conGuardarEdicion({{ $item->id }}, this)"><i class="fas fa-save"></i> Guardar</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="conEditar({{ $item->id }})">Cancelar</button>
                    </div>
                </div>
                @if($item->archivo_url)
                    @if($item->tipo === 'imagen')
                        <a href="{{ $item->archivo_url }}" target="_blank"><img src="{{ $item->archivo_url }}" alt="{{ $item->titulo }}"></a>
                    @elseif($item->tipo === 'video')
                        <video src="{{ $item->archivo_url }}" controls preload="metadata" style="max-height:260px;"></video>
                    @elseif($item->tipo === 'audio')
                        <audio src="{{ $item->archivo_url }}" controls preload="metadata"></audio>
                    @else
                        <div style="margin-top:8px;"><a href="{{ $item->archivo_url }}" target="_blank" class="con-volver"><i class="fas fa-file-download"></i> Ver / descargar archivo</a></div>
                    @endif
                @endif
            </div>
            @empty
            <div class="con-panel con-vacio">
                Todavía no hay conocimiento cargado para este producto.<br>
                <small>Empezá con las instrucciones de uso o las características técnicas — el bot las va a usar en cada consulta.</small>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const TIPOS_TEXTO = ['instrucciones', 'caracteristicas', 'faq', 'nota'];

function conCambiarTipo() {
    const tipo = document.getElementById('conTipo').value;
    const esTexto = TIPOS_TEXTO.includes(tipo);
    document.getElementById('conCampoTexto').style.display = esTexto ? '' : 'none';
    document.getElementById('conCampoArchivo').style.display = esTexto ? 'none' : '';
    const acepta = { imagen: 'image/*', video: 'video/*', audio: 'audio/*', documento: '.pdf' };
    document.getElementById('conArchivo').accept = acepta[tipo] || '';
}

function conEliminar(id, btn) {
    if (!confirm('¿Eliminar este conocimiento?')) return;
    btn.disabled = true;
    fetch('{{ url('articulo/conocimiento') }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(() => location.reload());
}

function conEditar(id) {
    const item = document.getElementById('con-item-' + id);
    const editor = item.querySelector('.con-item-editor');
    const abierto = editor.style.display !== 'none';
    editor.style.display = abierto ? 'none' : '';
    const cuerpo = item.querySelector('.con-item-contenido');
    if (cuerpo) cuerpo.style.display = abierto ? '' : 'none';
}

function conGuardarEdicion(id, btn) {
    const item = document.getElementById('con-item-' + id);
    const titulo = item.querySelector('.con-edit-titulo').value.trim();
    const contenidoEl = item.querySelector('.con-edit-contenido');
    const prioridadEl = item.querySelector('.con-edit-prioridad');
    if (!titulo) { alert('El título no puede quedar vacío.'); return; }
    btn.disabled = true;

    const datos = new FormData();
    datos.append('titulo', titulo);
    if (contenidoEl) datos.append('contenido', contenidoEl.value);
    if (prioridadEl) datos.append('prioridad', prioridadEl.value);

    fetch('{{ url('articulo/conocimiento') }}/' + id + '/editar', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: datos
    }).then(r => r.json()).then(d => {
        if (d.estado === 1) location.reload();
        else { alert(d.mensaje || 'No se pudo guardar'); btn.disabled = false; }
    }).catch(() => { alert('Error al guardar'); btn.disabled = false; });
}
</script>
@endsection
