@extends('layouts.admin')

@section('title', 'Publicaciones')

@section('contenido')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Titan+One&display=swap" rel="stylesheet">
<style>
    .pub-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1150px; margin: 0 auto; }
    .pub-title { font-size: 21px; font-weight: 600; margin-bottom: 2px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .pub-sub { font-size: 13.5px; color: #6E7A96; font-weight: 300; margin-bottom: 18px; }

    .pub-panel {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.08); padding: 20px; margin-top: 16px;
    }
    .pub-panel:first-of-type { margin-top: 0; }
    .pub-panel h3 { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #47536F; margin-bottom: 12px; }
    .pub-panel label { font-size: 13px; font-weight: 500; margin: 10px 0 4px; display: block; }
    .pub-panel select, .pub-panel input[type=text], .pub-panel input[type=datetime-local], .pub-panel textarea {
        width: 100%; border: 1px solid #E7EAF2; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; color: #1B2B5A; font-family: 'Poppins', sans-serif;
    }
    .pub-panel textarea { min-height: 110px; line-height: 1.55; resize: vertical; }

    .pub-cols2 { display: grid; grid-template-columns: 360px 1fr; gap: 16px; align-items: start; }
    @media (max-width: 991px) { .pub-cols2 { grid-template-columns: 1fr; } }

    .pub-opts { display: flex; gap: 8px; flex-wrap: wrap; }
    .pub-opt input { display: none; }
    .pub-opt span {
        display: inline-block; padding: 7px 14px; border-radius: 999px; border: 1.5px solid #E7EAF2;
        font-size: 12.5px; font-weight: 500; color: #47536F; cursor: pointer; transition: all .15s;
    }
    .pub-opt input:checked + span { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }

    .pub-btns { display: flex; gap: 10px; justify-content: center; margin-top: 14px; flex-wrap: wrap; }
    .pub-btn {
        border: none; border-radius: 999px; padding: 10px 22px; font-size: 13.5px; font-weight: 500;
        cursor: pointer; background: #1B2B5A; color: #fff; transition: background .15s; text-decoration: none; display: inline-block;
    }
    .pub-btn:hover { background: #2563EB; color: #fff; }
    .pub-btn.sec { background: #E0F2FE; color: #1B2B5A; }
    .pub-btn.sec:hover { background: #cfe9fb; color: #1B2B5A; }
    .pub-btn.ia { background: linear-gradient(90deg, #2563EB, #0EA5E9); }
    .pub-btn.ia:hover { filter: brightness(1.08); }
    .pub-btn.chico { padding: 6px 14px; font-size: 12px; }
    .pub-btn:disabled { opacity: .5; cursor: not-allowed; }

    .pub-texto { width: 100%; min-height: 130px; }
    .pub-copy { font-size: 12px; }
    .pub-hist { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
    .pub-hist .badge { border-radius: 999px; font-weight: 500; font-size: 11px; padding: 5px 10px; }

    .pub-aviso { font-size: 11.5px; color: #6E7A96; margin-top: 6px; font-weight: 300; }
    .pub-aviso.warn { color: #b45309; }

    /* Chat */
    .pub-chat { flex: 1; min-height: 0; overflow-y: auto; border: 1px solid #E7EAF2; border-radius: 12px; padding: 14px; background: #F8FAFC; display: flex; flex-direction: column; gap: 10px; }
    .pub-msg { max-width: 82%; padding: 9px 14px; border-radius: 14px; font-size: 13px; line-height: 1.5; white-space: pre-wrap; }
    .pub-msg.user { align-self: flex-end; background: #1B2B5A; color: #fff; border-bottom-right-radius: 4px; }
    .pub-msg.assistant { align-self: flex-start; background: #fff; border: 1px solid #E7EAF2; color: #1B2B5A; border-bottom-left-radius: 4px; }
    .pub-msg.sistema { align-self: center; background: #E0F2FE; color: #1B2B5A; font-size: 11.5px; border-radius: 999px; padding: 5px 12px; max-width: 100%; }
    .pub-chat-input { display: flex; gap: 8px; margin-top: 12px; align-items: center; }
    .pub-chat-input input[type=text] { flex: 1; border: 1px solid #E7EAF2; border-radius: 999px; padding: 10px 16px; font-size: 13.5px; font-family: 'Poppins', sans-serif; }
    .pub-chat-input .pub-btn { border-radius: 999px; padding: 10px 16px; }
    .pub-chat-input #btnAdjuntar { padding: 10px 13px; }
    .pub-chat-tools { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }

    /* Brief de campaña */
    .pub-brief-lista { max-height: 340px; overflow-y: auto; border: 1px solid #E7EAF2; border-radius: 12px; }
    .pub-brief-item { display: grid; grid-template-columns: 30px 1fr 90px 90px; gap: 10px; align-items: start; padding: 10px 12px; border-bottom: 1px solid #F1F4F9; font-size: 12px; }
    .pub-brief-item:last-child { border-bottom: none; }
    .pub-brief-item .num { font-weight: 700; color: #94A3B8; }
    .pub-brief-item .tit { font-weight: 600; color: #1B2B5A; }
    .pub-brief-item .badge-modo { font-weight: 400; font-size: 10.5px; color: #2563EB; background: #E0F2FE; border-radius: 999px; padding: 1px 8px; margin-left: 4px; }
    .pub-brief-item .desc { color: #6E7A96; font-weight: 300; margin-top: 2px; }
    .pub-brief-item .estado-item { font-size: 10.5px; font-weight: 600; color: #6E7A96; text-align: right; }
    .pub-brief-item .badge-precio { border: none; border-radius: 999px; padding: 3px 10px; font-size: 10.5px; font-weight: 600; cursor: pointer; white-space: nowrap; }

    /* Historial */
    .pub-hist-tabs { display: flex; gap: 8px; margin-bottom: 12px; }
    .pub-hist-lista { max-height: 420px; overflow-y: auto; border: 1px solid #E7EAF2; border-radius: 12px; }
    .pub-hist-row { padding: 10px 14px; border-bottom: 1px solid #F1F4F9; font-size: 12.5px; }
    .pub-hist-row:last-child { border-bottom: none; }
    .pub-hist-row .fecha { font-size: 10.5px; color: #94A3B8; font-weight: 600; }
    .pub-hist-row .msg-user { color: #1B2B5A; font-weight: 600; margin-top: 3px; }
    .pub-hist-row .msg-resp { color: #47536F; margin-top: 3px; }
    .pub-hist-row .tags span { display: inline-block; font-size: 9.5px; font-weight: 600; color: #2563EB; background: #E0F2FE; border-radius: 999px; padding: 1px 8px; margin-top: 4px; margin-right: 4px; }
    .pub-hist-row .prompt-txt { color: #6E7A96; font-weight: 300; margin-top: 3px; white-space: pre-wrap; }
    .pub-hist-row .estado-ok { color: #166534; font-weight: 600; }
    .pub-hist-row .estado-error { color: #b45309; font-weight: 600; }
    .pub-galeria-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; max-height: 65vh; overflow-y: auto; }
    .pub-galeria-item { position: relative; border-radius: 12px; overflow: hidden; cursor: pointer; background: #F8FAFC; border: 1px solid #E7EAF2; }
    .pub-galeria-item img { width: 100%; aspect-ratio: 4/5; object-fit: cover; display: block; transition: transform .15s; }
    .pub-galeria-item:hover img { transform: scale(1.04); }
    .pub-galeria-item .fecha { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(0deg, rgba(14,23,48,.75), rgba(14,23,48,0)); color: #fff; font-size: 9.5px; font-weight: 600; padding: 12px 8px 5px; }

    /* Variantes (5 opciones para elegir) */
    .pub-variantes { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; margin-top: 10px; }
    .pub-variante { position: relative; border-radius: 12px; overflow: hidden; border: 3px solid transparent; cursor: pointer; background: #F8FAFC; }
    .pub-variante img { width: 100%; aspect-ratio: 4/5; object-fit: cover; display: block; }
    .pub-variante.seleccionada { border-color: #2563EB; box-shadow: 0 6px 18px rgba(37,99,235,.25); }
    .pub-variante .check { position: absolute; top: 6px; right: 6px; width: 22px; height: 22px; border-radius: 999px; background: #2563EB; color: #fff; display: none; align-items: center; justify-content: center; font-size: 11px; }
    .pub-variante.seleccionada .check { display: flex; }
    .pub-variante.error { display: flex; align-items: center; justify-content: center; aspect-ratio: 4/5; font-size: 11px; color: #b45309; text-align: center; padding: 8px; cursor: default; }

    /* Miniaturas de imagen embebidas en la burbuja del chat */
    .pub-msg-imagenes { display: grid; grid-template-columns: repeat(auto-fill, minmax(72px, 1fr)); gap: 6px; margin-top: 8px; max-width: 320px; }
    .pub-variante.chica { border-width: 2px; border-radius: 8px; }
    .pub-variante.chica .check { width: 16px; height: 16px; font-size: 8px; top: 3px; right: 3px; }

    .pub-final-grid { display: grid; grid-template-columns: 300px 1fr; gap: 18px; align-items: start; }
    @media (max-width: 767px) { .pub-final-grid { grid-template-columns: 1fr; } }
    #pubPreview { width: 100%; border-radius: 12px; border: 1px solid #E7EAF2; box-shadow: 0 8px 24px rgba(27,43,90,.10); }
    #pubCanvas { display: none; }

    details.pub-mas summary { cursor: pointer; font-size: 12.5px; color: #2563EB; font-weight: 500; margin-top: 10px; }

    /* Próximas programadas */
    .pub-proxima { display: flex; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid #F1F4F9; font-size: 12.5px; }
    .pub-proxima:last-child { border-bottom: none; }
    .pub-proxima img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; }
    .pub-proxima .cuando { font-weight: 600; color: #1B2B5A; min-width: 130px; }

    /* Simulador de feed */
    .pub-feed-tabs { display: flex; gap: 8px; margin-bottom: 14px; }
    .pub-feed-tab { border: 1.5px solid #E7EAF2; background: #fff; color: #47536F; border-radius: 999px; padding: 6px 16px; font-size: 12.5px; font-weight: 500; cursor: pointer; }
    .pub-feed-tab.activo { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }
    .pub-feed-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .pub-feed-avatar { width: 48px; height: 48px; border-radius: 999px; object-fit: cover; border: 1px solid #E7EAF2; background: #1B2B5A; }
    .pub-feed-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px; }
    .pub-feed-item { position: relative; aspect-ratio: 4/5; background: #F1F4F9; overflow: hidden; }
    .pub-feed-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pub-feed-item .badge-prog { position: absolute; bottom: 4px; left: 4px; right: 4px; background: rgba(27,43,90,.85); color: #fff; font-size: 8.5px; font-weight: 600; text-align: center; border-radius: 6px; padding: 2px 4px; }
    .pub-feed-item.preview { outline: 2px solid #2563EB; outline-offset: -2px; }
    .pub-feed-item .badge-preview { position: absolute; top: 4px; left: 4px; right: 4px; background: rgba(37,99,235,.9); color: #fff; font-size: 8.5px; font-weight: 600; text-align: center; border-radius: 6px; padding: 2px 4px; }
    .pub-feed-item[draggable=true] { cursor: grab; }
    .pub-feed-item.arrastrando { opacity: .4; }
    .pub-feed-item.sobre-drop { outline: 2px dashed #2563EB; outline-offset: -2px; }
    .pub-feed-empty { font-size: 12.5px; color: #6E7A96; padding: 20px 0; }
    .pub-feed-count { display: flex; gap: 6px; margin-bottom: 10px; }
    .pub-feed-count-btn { border: 1.5px solid #E7EAF2; background: #fff; color: #47536F; border-radius: 999px; width: 30px; height: 26px; font-size: 11.5px; font-weight: 600; cursor: pointer; }
    .pub-feed-count-btn.activo { background: #E0F2FE; border-color: #7FD4F5; color: #1B2B5A; }

    /* Chat + feed en vivo, lado a lado */
    .pub-chat-feed-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: stretch; margin-top: 12px; height: calc(100vh - 175px); min-height: 460px; }
    @media (max-width: 991px) { .pub-chat-feed-row { grid-template-columns: 1fr; height: auto; } }
    .pub-chat-card { display: flex; flex-direction: column; margin-top: 0; min-height: 0; }
    .pub-chat-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 10px; }

    /* Recursos de marca */
    .pub-biblio { margin-top: 16px; }
    .pub-rec-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; margin-top: 10px; }
    .pub-rec-card {
        border: 1px solid #E7EAF2; border-radius: 12px; background: #F8FAFC; padding: 10px 12px;
        font-size: 12px; color: #47536F; position: relative;
    }
    .pub-rec-card .tit { font-weight: 600; color: #1B2B5A; font-size: 12.5px; display: flex; align-items: center; gap: 6px; }
    .pub-rec-card .cuerpo { margin-top: 4px; max-height: 74px; overflow: hidden; line-height: 1.5; }
    .pub-rec-card img { max-width: 100%; max-height: 90px; border-radius: 8px; margin-top: 6px; }
    .pub-rec-card .del {
        position: absolute; top: 6px; right: 8px; border: none; background: none; color: #b4552d;
        cursor: pointer; font-size: 12px;
    }
    .pub-rec-tipo { display: inline-block; border-radius: 999px; background: #E0F2FE; color: #1B2B5A; font-size: 9.5px; font-weight: 600; padding: 1px 8px; }

    .pub-spin { display: inline-block; animation: pubspin 1s linear infinite; }
    @keyframes pubspin { to { transform: rotate(360deg); } }

    /* Modal entrenar */
    #modalEntrenar .modal-content { border-radius: 16px; border: none; font-family: 'Poppins', sans-serif; color: #1B2B5A; }
    #modalEntrenar label { font-size: 12.5px; font-weight: 600; margin: 10px 0 4px; display: block; }
    #modalEntrenar textarea { width: 100%; border: 1px solid #E7EAF2; border-radius: 10px; padding: 9px 12px; font-size: 13px; min-height: 120px; font-family: 'Poppins', sans-serif; }
</style>

<div class="pub-wrap">
    <div class="pub-title">
        <span><i class="fas fa-bullhorn" style="color:#2563EB;"></i> Estudio de Publicaciones</span>
        <a href="{{ route('creative-studio.create') }}" class="pub-btn sec chico"><i class="fas fa-wand-magic-sparkles"></i> Modo Create (pro)</a>
    </div>

    {{-- Chat (izquierda) + simulador de feed en vivo (derecha) --}}
    <div class="pub-chat-feed-row">
        <div class="pub-panel pub-chat-card">
            <div class="pub-chat-head">
                @if(!$capacidades['copys'] && !$capacidades['escenas'])
                    <div class="pub-aviso warn" style="margin:0;">Configurá OPENAI_API_KEY y GEMINI_API_KEY.</div>
                @else
                    <div class="pub-aviso" style="margin:0;">Sigue siempre el Manual de Identidad Sommy. Lo que le pidas se suma a esa base, no la reemplaza.</div>
                @endif
            </div>

            <div class="pub-chat" id="pubChat"></div>
            <div class="pub-chat-input">
                <button class="pub-btn sec chico" id="btnAdjuntar" onclick="document.getElementById('pubArchivoChat').click()" title="Adjuntar imagen de referencia a Recursos de marca">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input type="file" id="pubArchivoChat" accept="image/*" style="display:none;" onchange="adjuntarArchivoChat(this)">
                <input type="text" id="pubMensajeChat" placeholder="Ej: generame 10 imágenes de contenido creativo para el feed..." onkeydown="if(event.key==='Enter'){event.preventDefault();enviarChat();}">
                <button class="pub-btn ia" id="btnEnviarChat" onclick="enviarChat()" @if(!$capacidades['copys']) disabled title="Configurá OPENAI_API_KEY" @endif><i class="fas fa-paper-plane"></i></button>
            </div>
            <div class="pub-chat-tools">
                <button class="pub-btn sec chico" id="btnConfig" onclick="$('#modalConfig').modal('show')"><i class="fas fa-sliders-h"></i> <span id="pubConfigResumen">Configurar</span></button>
                <button class="pub-btn sec chico" onclick="$('#modalEntrenar').modal('show')"><i class="fas fa-graduation-cap"></i> Mi marca</button>
                <button class="pub-btn sec chico" onclick="$('#modalRecursos').modal('show')"><i class="fas fa-box-open"></i> Recursos</button>
                <button class="pub-btn sec chico" onclick="renderReferencias(); $('#modalReferencias').modal('show')"><i class="fas fa-images"></i> Imágenes de referencia</button>
                <button class="pub-btn sec chico" onclick="$('#modalBrief').modal('show')"><i class="fas fa-list-check"></i> Brief de campaña</button>
                <button class="pub-btn sec chico" onclick="abrirHistorial()"><i class="fas fa-clock-rotate-left"></i> Historial</button>
                <button class="pub-btn sec chico" onclick="abrirGaleria()"><i class="fas fa-images"></i> Galería</button>
                <button class="pub-btn sec chico" id="btnAbrirProximas" onclick="$('#modalProximas').modal('show')" style="display:none;"><i class="fas fa-calendar-days"></i> Próximas <span id="pubProximasCount"></span></button>
            </div>
        </div>

        <div class="pub-panel pub-chat-card">
            <div class="pub-feed-tabs">
                <button class="pub-feed-tab activo" data-red="instagram" onclick="cambiarRedFeed('instagram', this)">Instagram</button>
                <button class="pub-feed-tab" data-red="facebook" onclick="cambiarRedFeed('facebook', this)">Facebook</button>
            </div>
            <div class="pub-feed-header">
                <img src="{{ asset('imagenes/marca/sommy-logo-header.png') }}" class="pub-feed-avatar" onerror="this.style.display='none'">
                <div><strong id="pubFeedHandle">&#64;sommy_colchoneria</strong><div class="pub-aviso" style="margin:0;">Arrastrá para reordenar. Así se va viendo tu feed a medida que generás, guardás y programás</div></div>
            </div>
            <div class="pub-feed-count">
                <button class="pub-feed-count-btn activo" data-n="9" onclick="cambiarCantidadFeed(9, this)">9</button>
                <button class="pub-feed-count-btn" data-n="12" onclick="cambiarCantidadFeed(12, this)">12</button>
                <button class="pub-feed-count-btn" data-n="18" onclick="cambiarCantidadFeed(18, this)">18</button>
                <button class="pub-feed-count-btn" data-n="24" onclick="cambiarCantidadFeed(24, this)">24</button>
            </div>
            <div style="flex:1; overflow-y:auto;">
                <div class="pub-feed-grid" id="pubFeedGrid"></div>
                <div class="pub-feed-empty" id="pubFeedEmpty" style="display:none;">Todavía no generaste publicaciones. Lo que vayas generando, guardando o programando va apareciendo acá, en el orden en que se ve en tu perfil.</div>
            </div>
        </div>
    </div>

</div>

{{-- Modal Contenido: elegir variante + revisar y subir --}}
<div class="modal fade" id="modalContenido" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-wand-magic-sparkles" style="color:#2563EB;"></i> Tu contenido</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                {{-- 2 · Elegir variante --}}
                <div id="panelVariantes" style="display:none;">
                    <h3>Elegí la imagen que más te gusta</h3>
                    <div id="pubVariantesEstado" class="pub-aviso"></div>
                    <div class="pub-variantes" id="pubVariantes"></div>
                    <details class="pub-mas">
                        <summary>Ver prompt generado (avanzado)</summary>
                        <div class="pub-texto" id="pubPromptDebug" style="font-size:11px;"></div>
                    </details>
                </div>

                {{-- 3 · Revisar y subir --}}
                <div id="panelResultado" style="display:none;">
                    <h3>Revisá y subí</h3>
                    <canvas id="pubCanvas" width="1080" height="1350"></canvas>
                    <div class="pub-final-grid">
                        <div>
                            <img id="pubPreview" alt="Vista previa">
                            <div class="pub-btns">
                                <button class="pub-btn sec chico" id="btnStoryExtra" onclick="generarFormatoExtra('story', this)"><i class="fas fa-plus"></i> Versión Historia 9:16</button>
                                <button class="pub-btn sec chico" id="btnFeedExtra" onclick="generarFormatoExtra('feed', this)" style="display:none;"><i class="fas fa-plus"></i> Versión Feed 4:5</button>
                            </div>
                            <label style="margin-top:10px;">Exportar</label>
                            <div class="pub-btns" style="justify-content:flex-start;">
                                <button class="pub-btn sec chico" onclick="exportarPreset('feed')">Feed 1080×1350</button>
                                <button class="pub-btn sec chico" onclick="exportarPreset('square')">Cuadrado 1080×1080</button>
                                <button class="pub-btn sec chico" onclick="exportarPreset('story')">Historia 1080×1920</button>
                                <button class="pub-btn sec chico" onclick="exportarPreset('reel')">Reel cover 1080×1920</button>
                            </div>
                        </div>
                        <div>
                            <label style="margin-top:0;">Estilo del diseño</label>
                            <div class="pub-opts">
                                <label class="pub-opt"><input type="radio" name="pubEstiloOverlay" value="promocional" checked><span>Promocional (sticker)</span></label>
                                <label class="pub-opt"><input type="radio" name="pubEstiloOverlay" value="institucional"><span>Institucional (suave)</span></label>
                            </div>
                            <details class="pub-mas" open>
                                <summary>Textos sobre la imagen (capas, no generadas por IA)</summary>
                                <label style="margin-top:6px;">Titular</label>
                                <input type="text" id="ovHeadline" placeholder="Nombre del producto por defecto">
                                <label>Llamado a la acción (CTA)</label>
                                <input type="text" id="ovCta" placeholder="Ej: Consultá stock, Comprá ahora...">
                                <label>Badge (etiqueta chica)</label>
                                <input type="text" id="ovBadge" placeholder="Ej: Envío gratis, Nuevo...">
                                <label style="margin-top:8px;font-weight:400;"><input type="checkbox" id="ovWebsite" style="width:auto;margin-right:6px;"> Mostrar sommy.com.ar</label>
                            </details>

                            <label style="margin-top:10px;">Caption (Instagram / Facebook)</label>
                            <textarea id="txtCaption" class="pub-texto"></textarea>

                            <details class="pub-mas">
                                <summary>Ver título/descripción MercadoLibre y mensaje de WhatsApp</summary>
                                <label>Título MercadoLibre <span id="mlTituloLen" style="color:#6E7A96;font-weight:300;"></span></label>
                                <input type="text" id="txtTituloML">
                                <label>Descripción MercadoLibre</label>
                                <textarea id="txtDescML" class="pub-texto"></textarea>
                                <label>Mensaje WhatsApp</label>
                                <textarea id="txtWa" style="min-height:80px;"></textarea>
                            </details>

                            <label>Campaña (opcional)</label>
                            <div class="pub-btns" style="justify-content:flex-start;flex-wrap:wrap;">
                                <select id="pubCampana" style="max-width:220px;"></select>
                                <button class="pub-btn sec chico" onclick="nuevaCampana()"><i class="fas fa-plus"></i> Nueva</button>
                            </div>

                            <label>Publicar</label>
                            <div class="pub-btns" style="justify-content:flex-start;">
                                <button class="pub-btn" id="btnPublicarAhora" onclick="publicarAhora(this)" @if(!$capacidades['facebook'] && !$capacidades['instagram']) disabled title="Configurá las claves de Meta" @endif>
                                    <i class="fas fa-paper-plane"></i> Publicar ahora (Instagram + Facebook)
                                </button>
                                <button class="pub-btn sec" id="btnGuardar" onclick="guardarBorrador(this)"><i class="fas fa-save"></i> Guardar borrador</button>
                            </div>

                            <label>Programar para más adelante</label>
                            <div class="pub-btns" style="justify-content:flex-start;flex-wrap:wrap;">
                                <input type="datetime-local" id="pubFecha" style="max-width:220px;">
                                <button class="pub-btn sec" id="btnProgramar" onclick="programar(this)"><i class="fas fa-calendar-plus"></i> Programar</button>
                            </div>
                            <div class="pub-aviso" id="avisoAccion"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Configurar (producto/combo + formato + precio) --}}
<div class="modal fade" id="modalConfig" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-sliders-h" style="color:#2563EB;"></i> Elegí qué vas a promocionar</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <label style="margin-top:0;">Tipo de contenido</label>
                <div class="pub-opts">
                    <label class="pub-opt"><input type="radio" name="pubModo" value="producto" checked onchange="cambiarModoContenido()"><span><i class="fas fa-bed"></i> Producto o combo</span></label>
                    <label class="pub-opt"><input type="radio" name="pubModo" value="marca" onchange="cambiarModoContenido()"><span><i class="fas fa-wand-magic-sparkles"></i> Contenido de marca (sin producto)</span></label>
                </div>
                <div class="pub-aviso" id="pubModoAviso" style="display:none;">Ideal para contenido de estilo de vida, informativo o con personas — no hace falta que sea sobre un producto puntual.</div>

                <div id="pubModoProducto">
                    <label>Producto o combo</label>
                    <select id="pubProducto"></select>
                    <a id="pubLinkConocimiento" href="#" class="pub-aviso" style="display:inline-block;color:#2563EB;margin-top:6px;">
                        <i class="fas fa-brain"></i> Conocimiento del producto (contexto para la IA)
                    </a>
                    <div id="pubHistorial" class="pub-hist"></div>

                    <label>Mostrar precio</label>
                    <div class="pub-opts">
                        <label class="pub-opt"><input type="radio" name="pubPrecio" value="si" checked><span>Sí</span></label>
                        <label class="pub-opt"><input type="radio" name="pubPrecio" value="no"><span>No</span></label>
                    </div>
                </div>

                <label>Formato</label>
                <div class="pub-opts">
                    <label class="pub-opt"><input type="radio" name="pubFormato" value="feed" checked><span>Feed 4:5</span></label>
                    <label class="pub-opt"><input type="radio" name="pubFormato" value="story"><span>Historia 9:16</span></label>
                    <label class="pub-opt"><input type="radio" name="pubFormato" value="ml"><span>MercadoLibre 1:1</span></label>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E7EAF2;">
                <button type="button" class="pub-btn" data-dismiss="modal" data-bs-dismiss="modal">Listo</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Próximas publicaciones programadas --}}
<div class="modal fade" id="modalProximas" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-calendar-days" style="color:#2563EB;"></i> Próximas publicaciones</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="pubProximas"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Imágenes de referencia (estilo visual a imitar) --}}
<div class="modal fade" id="modalReferencias" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-images" style="color:#2563EB;"></i> Imágenes de referencia</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="pub-aviso" style="margin-top:0;">Subí ejemplos de fotos/anuncios a los que querés que se parezcan tus publicaciones (paleta de color, luz, composición). La IA va a imitar ese estilo visual sin copiar su contenido ni el producto que aparezca ahí.</div>
                <input type="file" id="refArchivo" accept="image/*" multiple style="margin-top:10px;" onchange="subirReferencias(this)">
                <div class="pub-rec-grid" id="pubReferencias" style="margin-top:14px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Brief de campaña --}}
<div class="modal fade" id="modalBrief" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-list-check" style="color:#2563EB;"></i> Brief de campaña</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="pub-aviso" style="margin-top:0;">Pegá tu lista de piezas de contenido (una por línea o bloque). La IA la interpreta usando SOLO tu catálogo real — revisá antes de generar nada.</div>
                <textarea id="briefTexto" style="width:100%;min-height:140px;border:1px solid #E7EAF2;border-radius:10px;padding:10px;font-size:12.5px;font-family:'Poppins',sans-serif;" placeholder="01 Eclipse 30 - foto real, fondo oscuro...&#10;02 Oferta del mes - producto + precio, CTA...&#10;..."></textarea>
                <div class="pub-btns" style="justify-content:flex-start;margin-top:8px;">
                    <button class="pub-btn ia" id="btnInterpretarBrief" onclick="interpretarBriefTexto(this)" @if(!$capacidades['copys']) disabled title="Configurá OPENAI_API_KEY" @endif><i class="fas fa-wand-magic-sparkles"></i> Interpretar</button>
                </div>

                <div id="briefResultado" style="display:none;margin-top:16px;">
                    <div class="pub-aviso" id="briefResumen" style="margin:0 0 8px;"></div>
                    <div id="briefLista" class="pub-brief-lista"></div>

                    <label style="margin-top:12px;">Campaña destino</label>
                    <div class="pub-btns" style="justify-content:flex-start;flex-wrap:wrap;">
                        <select id="briefCampana" style="max-width:220px;"></select>
                        <button class="pub-btn sec chico" onclick="nuevaCampana()"><i class="fas fa-plus"></i> Nueva</button>
                    </div>
                    <div class="pub-aviso">Cada pieza genera 3 variantes para que elijas la mejor (click en la miniatura) — se guarda como borrador en esta campaña recién cuando confirmás, nada se publica solo.</div>
                    <div class="pub-btns" style="justify-content:flex-start;margin-top:10px;">
                        <button class="pub-btn" id="btnGenerarBrief" onclick="generarBriefTodo(this)"><i class="fas fa-play"></i> Generar variantes</button>
                        <button class="pub-btn" id="btnGuardarSeleccionadasBrief" onclick="guardarSeleccionadasBrief(this)" style="display:none;"><i class="fas fa-save"></i> Guardar seleccionadas</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Historial (conversaciones + prompts de imagen) --}}
<div class="modal fade" id="modalHistorial" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-clock-rotate-left" style="color:#2563EB;"></i> Historial</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="pub-hist-tabs">
                    <button class="pub-feed-tab activo" id="histTabConv" onclick="cambiarTabHistorial('conversaciones')">Conversaciones</button>
                    <button class="pub-feed-tab" id="histTabPrompts" onclick="cambiarTabHistorial('prompts')">Prompts de imagen</button>
                </div>
                <div id="histCargando" class="pub-aviso">Cargando...</div>
                <div id="histConversaciones" class="pub-hist-lista"></div>
                <div id="histPrompts" class="pub-hist-lista" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Galería: TODAS las imágenes generadas alguna vez --}}
<div class="modal fade" id="modalGaleria" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-images" style="color:#2563EB;"></i> Galería de imágenes generadas</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="galeriaCargando" class="pub-aviso">Cargando...</div>
                <div id="galeriaGrid" class="pub-galeria-grid"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Recursos de marca --}}
<div class="modal fade" id="modalRecursos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-box-open" style="color:#2563EB;"></i> Recursos de marca</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="pub-aviso" style="margin-top:0;">Información de contexto (direcciones, promos, datos del negocio) que la IA usa al escribir, y logos/imágenes de referencia.</div>
                <div class="pub-cols2" style="margin-top:10px;">
                    <div>
                        <label style="margin-top:0;">Agregar recurso</label>
                        <select id="recTipo" onchange="cambiarTipoRecurso()">
                            <option value="contexto">Información de contexto (la usa la IA)</option>
                            <option value="imagen">Imagen</option>
                            <option value="logo">Logo</option>
                        </select>
                        <input type="text" id="recTitulo" placeholder="Título (ej: Local y horarios)" style="margin-top:8px;">
                        <textarea id="recContenido" placeholder="Contenido del recurso..." style="margin-top:8px;"></textarea>
                        <input type="file" id="recArchivo" accept="image/*" style="display:none; margin-top:8px; width:100%;">
                        <div class="pub-btns" style="justify-content:flex-start;">
                            <button class="pub-btn sec" id="btnRecGuardar" onclick="guardarRecurso(this)"><i class="fas fa-plus"></i> Agregar a la biblioteca</button>
                        </div>
                    </div>
                    <div>
                        <div class="pub-rec-grid" id="pubRecursos"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Mi Marca --}}
<div class="modal fade" id="modalEntrenar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-graduation-cap" style="color:#2563EB;"></i> Mi marca</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <label>Cómo escribe tu marca (voz para los textos)</label>
                <textarea id="entVoz" placeholder="Ej: Tono cercano y sereno, trato de vos, sin gritos de oferta. Siempre mencionar que somos fabricantes...">{{ $ajustes->voz_marca ?? '' }}</textarea>
                <div class="pub-aviso">Se usa en cada generación de textos. Dejalo vacío para usar la voz Sommy por defecto.</div>

                <label>Estilo visual fijo de tus publicaciones</label>
                <textarea id="entEstilo" placeholder="Ej: Dormitorio real prolijo, luz natural, paleta azul noche/blanco/dorado sutil...">{{ $ajustes->estilo_imagen ?? '' }}</textarea>
                <div class="pub-aviso">Este es el ÚNICO estilo que usa la IA para ambientar tus fotos: se aplica siempre igual, para que tu feed se vea homogéneo. Cambialo cuando quieras — se usa desde la próxima generación.</div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E7EAF2;">
                <button type="button" class="pub-btn sec" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="pub-btn" onclick="guardarAjustes(this)"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const PRODUCTOS = [...@json($productos), ...@json($combos)];
const REGISTROS = @json($registros);
const BIBLIOTECA = @json($biblioteca);
const RECURSOS = @json($recursos);
let CAMPANAS = @json($campanas);
let ESTILO_IMG = @json($ajustes->estilo_imagen ?? '');
const LOGO_URL = '{{ asset('imagenes/marca/sommy-logo-magia.png') }}';
const BASE_URL = '{{ url('/') }}';
const CSRF = '{{ csrf_token() }}';

const FORMATOS = { feed: [1080, 1350], story: [1080, 1920], ml: [1200, 1200], square: [1080, 1080], reel: [1080, 1920] };
const REC_LBL = { imagen: 'Imagen', logo: 'Logo', prompt: 'Prompt', contexto: 'Contexto' };
const REC_ICO = { imagen: 'fa-image', logo: 'fa-star', prompt: 'fa-terminal', contexto: 'fa-info-circle' };

const canvas = document.getElementById('pubCanvas');
const ctx = canvas.getContext('2d');
const sel = document.getElementById('pubProducto');
let imgLogo = null;
let variantes = [];
let varianteElegida = null; // { img, url, path, prompt }
let textosIA = null;
let pubGuardadaId = null;
let redFeedActiva = 'instagram';

PRODUCTOS.forEach((p, i) => {
    const o = document.createElement('option');
    o.value = i;
    o.textContent = (p.esCombo ? '🎁 ' : '') + p.nombre;
    sel.appendChild(o);
});

const money = v => '$' + Number(v).toLocaleString('es-AR', { maximumFractionDigits: 0 });
const prod = () => PRODUCTOS[parseInt(sel.value, 10)] || PRODUCTOS[0];
const opcion = name => document.querySelector('input[name=' + name + ']:checked').value;

let modoContenido = 'producto'; // 'producto' | 'marca' (contenido de marca sin producto puntual)
const productoActual = () => modoContenido === 'producto' ? prod() : null;

function cambiarModoContenido() {
    modoContenido = opcion('pubModo');
    document.getElementById('pubModoProducto').style.display = modoContenido === 'producto' ? '' : 'none';
    document.getElementById('pubModoAviso').style.display = modoContenido === 'marca' ? '' : 'none';
    variantes = []; varianteElegida = null; textosIA = null; pubGuardadaId = null; historialChat = [];
    document.getElementById('panelVariantes').style.display = 'none';
    document.getElementById('panelResultado').style.display = 'none';
    renderHistorial();
    renderFeedSimulado();
    actualizarResumenConfig();
    saludoInicial();
}

function postJson(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(body)
    }).then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok || data.status === 0) throw new Error(data.error || 'Error del servidor');
        return data;
    });
}

function cargarLogo(cb) {
    if (imgLogo) { cb(); return; }
    imgLogo = new Image();
    imgLogo.onload = cb; imgLogo.onerror = cb;
    imgLogo.src = LOGO_URL;
}

/* ── 1 · Chat: pedile a la IA lo que quieras (imagen y/o texto) ── */
let historialChat = [];

function bubbleChat(role, texto) {
    const cont = document.getElementById('pubChat');
    const div = document.createElement('div');
    div.className = 'pub-msg ' + role;
    div.textContent = texto;
    cont.appendChild(div);
    cont.scrollTop = cont.scrollHeight;
    return div;
}

function saludoInicial() {
    const cont = document.getElementById('pubChat');
    if (cont) cont.innerHTML = '';
    const p = productoActual();
    if (p) {
        bubbleChat('assistant', '¡Hola! Contame qué contenido querés para "' + p.nombre + '". Por ejemplo: "generame una imagen con luz cálida de atardecer" o "escribime un caption corto y con humor". El estilo de marca y los datos del producto se respetan siempre, no hace falta que los repitas.');
    } else {
        bubbleChat('assistant', '¡Hola! Estás en modo "Contenido de marca": no hace falta que sea sobre un producto puntual. Por ejemplo: "generame una imagen de una persona despertando descansada" o "escribime un tip sobre higiene del sueño". El estilo de marca se respeta siempre.');
    }
}

function enviarChat() {
    const input = document.getElementById('pubMensajeChat');
    const mensaje = input.value.trim();
    if (!mensaje) return;
    const p = productoActual();
    bubbleChat('user', mensaje);
    historialChat.push({ role: 'user', content: mensaje });
    input.value = '';
    const btn = document.getElementById('btnEnviarChat');
    btn.disabled = true;
    const pensando = bubbleChat('assistant', '');
    pensando.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i>';

    postJson('{{ route('publicaciones.chat') }}', {
        producto_id: p ? p.id : null, es_combo: p ? !!p.esCombo : false,
        formato: opcion('pubFormato'), con_precio: p ? opcion('pubPrecio') === 'si' : false,
        mensaje, historial: historialChat.slice(-16)
    }).then(data => {
        pensando.remove();
        const bubble = bubbleChat('assistant', data.reply);
        historialChat.push({ role: 'assistant', content: data.reply });
        if (data.imagenes) {
            variantes = data.imagenes;
            varianteElegida = null;
            document.getElementById('panelResultado').style.display = 'none';
            document.getElementById('panelVariantes').style.display = '';
            renderVariantes();
            renderVariantesEnChat(bubble, data.imagenes);
        }
        if (data.textos) {
            textosIA = data.textos;
            pintarTextos();
            // Si ya había una imagen elegida, mostramos directo la revisión con el texto nuevo.
            // Si todavía no eligió imagen, el texto queda listo y se ve apenas elija una.
            if (!data.imagenes && varianteElegida) {
                document.getElementById('panelVariantes').style.display = 'none';
                document.getElementById('panelResultado').style.display = '';
                $('#modalContenido').modal('show');
            }
        }
    }).catch(e => {
        pensando.remove();
        bubbleChat('assistant', '⚠️ ' + e.message);
    }).finally(() => { btn.disabled = false; });
}

function adjuntarArchivoChat(input) {
    const f = input.files[0];
    if (!f) return;
    const fd = new FormData();
    fd.append('tipo', 'imagen');
    fd.append('titulo', f.name);
    fd.append('archivo', f);
    bubbleChat('sistema', 'Subiendo ' + f.name + '...');
    fetch('{{ route('publicaciones.recursos') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd
    }).then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok || data.status === 0) throw new Error(data.error || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Error del servidor'));
        RECURSOS.unshift({ id: data.id, tipo: 'imagen', titulo: f.name, contenido: null, archivo: null, archivo_url: data.archivo_url });
        renderRecursos();
        bubbleChat('sistema', '📎 ' + f.name + ' agregado a Recursos de marca.');
    }).catch(e => bubbleChat('sistema', '⚠️ No se pudo subir: ' + e.message))
      .finally(() => { input.value = ''; });
}

function renderVariantes() {
    const ok = variantes.filter(v => !v.error).length;
    document.getElementById('pubVariantesEstado').textContent = ok
        ? 'Elegí la imagen que más te guste (' + ok + ' de ' + variantes.length + ' generadas).'
        : 'No se pudo generar ninguna imagen. Probá de nuevo.';
    const cont = document.getElementById('pubVariantes');
    cont.innerHTML = '';
    variantes.forEach((v, i) => {
        const card = document.createElement('div');
        if (v.error) {
            card.className = 'pub-variante error';
            card.textContent = 'Falló esta opción';
        } else {
            card.className = 'pub-variante';
            card.innerHTML = '<img src="' + v.url + '"><div class="check"><i class="fas fa-check"></i></div>';
            card.onclick = () => elegirVariante(i, card);
        }
        cont.appendChild(card);
    });
    const primeraOk = variantes.find(v => !v.error);
    document.getElementById('pubPromptDebug').textContent = primeraOk ? primeraOk.prompt : '';
}

/* Miniaturas embebidas directo en la burbuja del chat (además del modal) — para que se vean sin tener que abrir nada. */
function renderVariantesEnChat(bubble, imagenes) {
    const grid = document.createElement('div');
    grid.className = 'pub-msg-imagenes';
    imagenes.forEach((v, i) => {
        const card = document.createElement('div');
        if (v.error) {
            card.className = 'pub-variante chica error';
            card.textContent = 'Falló';
        } else {
            card.className = 'pub-variante chica';
            card.innerHTML = '<img src="' + v.url + '">';
            card.onclick = () => elegirVarianteDesdeChat(i, card);
        }
        grid.appendChild(card);
    });
    bubble.appendChild(grid);
    const cont = document.getElementById('pubChat');
    cont.scrollTop = cont.scrollHeight;
}

function elegirVarianteDesdeChat(i, card) {
    $('#modalContenido').modal('show');
    document.getElementById('panelVariantes').style.display = 'none';
    elegirVariante(i, card);
}

function elegirVariante(i, card) {
    document.querySelectorAll('.pub-variante').forEach(c => c.classList.remove('seleccionada'));
    card.classList.add('seleccionada');
    const v = variantes[i];
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
        varianteElegida = { img, url: v.url, path: v.path, prompt: v.prompt };
        pubGuardadaId = null;
        const conProducto = !!productoActual();
        document.getElementById('btnStoryExtra').style.display = (conProducto && opcion('pubFormato') !== 'story') ? '' : 'none';
        document.getElementById('btnFeedExtra').style.display = (conProducto && opcion('pubFormato') === 'story') ? '' : 'none';
        document.getElementById('panelResultado').style.display = '';
        cargarLogo(() => {
            dibujar();
            document.getElementById('pubPreview').src = canvas.toDataURL('image/png');
            renderFeedSimulado();
        });
        document.querySelector('#modalContenido .modal-body').scrollTo({ top: document.getElementById('panelResultado').offsetTop - 10, behavior: 'smooth' });
    };
    img.src = v.url;
}

/* + Historia 9:16 / + Feed 4:5 una vez aprobado el diseño */
function generarFormatoExtra(formato, btn) {
    const p = prod();
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Generando...';
    postJson('{{ route('publicaciones.generar-imagen') }}', { producto_id: p.id, formato, es_combo: !!p.esCombo })
        .then(data => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                const asegurar = pubGuardadaId ? Promise.resolve(pubGuardadaId) : guardarBorrador(null);
                asegurar.then(padreId => {
                    const wOld = canvas.width, hOld = canvas.height, formatoOld = document.querySelector('input[name=pubFormato]:checked').value;
                    document.querySelector('input[name=pubFormato][value="' + formato + '"]').checked = true;
                    const ve = { img, url: data.url, path: data.path, prompt: data.prompt };
                    dibujarConVariante(ve, formato);
                    postJson('{{ route('publicaciones.guardar') }}', {
                        producto_id: p.id, padre_id: padreId, es_combo: !!p.esCombo,
                        formato, estilo: 'ia',
                        titulo_ml: document.getElementById('txtTituloML').value,
                        desc_ml: document.getElementById('txtDescML').value,
                        caption: document.getElementById('txtCaption').value,
                        texto_wa: document.getElementById('txtWa').value,
                        imagen_escena: data.path, prompt_escena: data.prompt,
                        imagen_base64: canvas.toDataURL('image/png')
                    }).then(() => {
                        document.getElementById('avisoAccion').textContent = 'Versión ' + (formato === 'story' ? 'Historia 9:16' : 'Feed 4:5') + ' guardada en la biblioteca.';
                        document.querySelector('input[name=pubFormato][value="' + formatoOld + '"]').checked = true;
                        dibujar();
                        document.getElementById('pubPreview').src = canvas.toDataURL('image/png');
                        btn.style.display = 'none';
                    });
                });
            };
            img.src = data.url;
        })
        .catch(e => alert('No se pudo generar: ' + e.message))
        .finally(() => { btn.disabled = false; btn.innerHTML = original; });
}

/* ── Canvas: compone la variante elegida + logo/precio exactos del ERP ── */
function dibujar() { if (varianteElegida) dibujarConVariante(varianteElegida, opcion('pubFormato')); }

function dibujarConVariante(v, formato) {
    const p = productoActual();
    const [W, H] = FORMATOS[formato] || FORMATOS.feed;
    const conPrecio = p && opcion('pubPrecio') === 'si';
    canvas.width = W; canvas.height = H;

    const im = v.img;
    const r = Math.max(W / im.naturalWidth, H / im.naturalHeight);
    const iw = im.naturalWidth * r, ih = im.naturalHeight * r;

    const estiloPromo = document.querySelector('input[name=pubEstiloOverlay]:checked') ? opcion('pubEstiloOverlay') === 'promocional' : true;

    if (estiloPromo) {
        rRect(0, 0, W, H, W * .04);
        ctx.save();
        ctx.clip();
        ctx.drawImage(im, (W - iw) / 2, (H - ih) / 2, iw, ih);
        ctx.restore();
        dibujarOverlayPromocional(p, W, H, formato, conPrecio);
        renderHistorial();
        return;
    }

    ctx.drawImage(im, (W - iw) / 2, (H - ih) / 2, iw, ih);
    const g = ctx.createLinearGradient(0, H * .55, 0, H);
    g.addColorStop(0, 'rgba(14,23,48,0)');
    g.addColorStop(1, 'rgba(14,23,48,.82)');
    ctx.fillStyle = g;
    ctx.fillRect(0, H * .55, W, H * .45);

    if (imgLogo && imgLogo.naturalWidth) {
        const lw = W * .22, lh = lw * imgLogo.naturalHeight / imgLogo.naturalWidth;
        const lx = W * .06, ly = H * .045;
        rRect(lx - W * .02, ly - lh * .25, lw + W * .04, lh * 1.5, lh);
        ctx.fillStyle = '#FFFFFF'; ctx.fill();
        ctx.drawImage(imgLogo, lx, ly, lw, lh);
    }

    const baseY = H - ((formato === 'story' || formato === 'reel') ? H * .30 : H * .32);

    if (!p) {
        // Contenido de marca sin producto: la imagen habla sola, solo la firma abajo.
        ctx.textAlign = 'center';
        ctx.fillStyle = '#7FD4F5';
        ctx.font = '500 ' + (W * .03) + 'px Poppins, sans-serif';
        ctx.fillText('LIVIANO COMO UNA PLUMA', W / 2, H - H * .06);
        renderHistorial();
        return;
    }

    const headlineEl = document.getElementById('ovHeadline');
    const headline = (headlineEl && headlineEl.value.trim()) ? headlineEl.value.trim() : p.nombre;

    ctx.textAlign = 'center';
    ctx.fillStyle = '#FFFFFF';
    ctx.font = '600 ' + (W * .048) + 'px Poppins, sans-serif';
    envolverTexto(headline, W / 2, baseY, W * .84, W * .06);

    const specs = [p.plazas, p.firmeza ? 'Firmeza ' + p.firmeza.toLowerCase() : null, p.altura ? p.altura + ' cm' : null, p.pillow ? 'Pillow top' : null].filter(Boolean).join('  ·  ');
    if (specs) {
        ctx.fillStyle = '#C7D0E8';
        ctx.font = '400 ' + (W * .028) + 'px Poppins, sans-serif';
        ctx.fillText(specs, W / 2, baseY + W * .095);
    }

    if (conPrecio) {
        const py = baseY + W * .17;
        if (p.descuento > 0) {
            ctx.fillStyle = '#C7D0E8';
            ctx.font = '400 ' + (W * .030) + 'px Poppins, sans-serif';
            const vOld = money(p.precio);
            ctx.fillText(vOld, W / 2, py - W * .055);
            const tw = ctx.measureText(vOld).width;
            ctx.strokeStyle = '#C7D0E8'; ctx.lineWidth = W * .003;
            ctx.beginPath(); ctx.moveTo(W / 2 - tw / 2, py - W * .065); ctx.lineTo(W / 2 + tw / 2, py - W * .065); ctx.stroke();
        }
        ctx.fillStyle = '#FFFFFF';
        ctx.font = '700 ' + (W * .075) + 'px Poppins, sans-serif';
        ctx.fillText(money(p.precioFinal), W / 2, py);
        if (p.descuento > 0) {
            ctx.fillStyle = '#7FD4F5';
            ctx.font = '600 ' + (W * .03) + 'px Poppins, sans-serif';
            ctx.fillText('-' + Math.round(p.descuento) + '% OFF', W / 2, py + W * .05);
        }
    }

    const ctaEl = document.getElementById('ovCta');
    const cta = ctaEl ? ctaEl.value.trim() : '';
    const websiteOn = document.getElementById('ovWebsite') && document.getElementById('ovWebsite').checked;

    if (cta) {
        ctx.font = '700 ' + (W * .032) + 'px Poppins, sans-serif';
        const tw = ctx.measureText(cta.toUpperCase()).width;
        const padX = W * .045, pillW = tw + padX * 2, pillH = W * .09;
        const py = H - H * .07 - pillH / 2;
        rRect(W / 2 - pillW / 2, py - pillH / 2, pillW, pillH, pillH / 2);
        ctx.fillStyle = '#2563EB'; ctx.fill();
        ctx.fillStyle = '#FFFFFF';
        ctx.fillText(cta.toUpperCase(), W / 2, py + W * .011);
    } else {
        ctx.fillStyle = '#7FD4F5';
        ctx.font = '500 ' + (W * .026) + 'px Poppins, sans-serif';
        ctx.fillText('DIRECTO DE FÁBRICA  ·  ENVÍO A DOMICILIO', W / 2, H - H * .07);
    }

    if (websiteOn) {
        ctx.fillStyle = '#C7D0E8';
        ctx.font = '400 ' + (W * .020) + 'px Poppins, sans-serif';
        ctx.fillText('sommy.com.ar', W / 2, H - H * .022);
    }

    const badgeEl = document.getElementById('ovBadge');
    const badge = badgeEl ? badgeEl.value.trim() : '';
    if (badge) {
        ctx.font = '700 ' + (W * .026) + 'px Poppins, sans-serif';
        const bw = ctx.measureText(badge.toUpperCase()).width;
        const padX = W * .035, pillW = bw + padX * 2, pillH = W * .065;
        const bx = W - W * .06 - pillW, by = H * .045;
        rRect(bx, by, pillW, pillH, pillH / 2);
        ctx.fillStyle = '#2563EB'; ctx.fill();
        ctx.fillStyle = '#FFFFFF'; ctx.textAlign = 'center';
        ctx.fillText(badge.toUpperCase(), bx + pillW / 2, by + pillH * .68);
    }

    renderHistorial();
}

function rRect(x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
}

/**
 * Overlay promocional: el banner con titular/precio/descuento/specs ya lo
 * dibuja Gemini DENTRO de la foto (ver PromptBuilder::bloqueGraficaPromocional),
 * así que acá solo se agrega lo que la IA no conoce: el logo real de la marca
 * (en el hueco que la IA dejó libre arriba a la izquierda) y los campos que el
 * usuario carga aparte en el editor (condición/CTA, badge, sitio web).
 */
function dibujarOverlayPromocional(p, W, H, formato, conPrecio) {
    const FUCSIA = '#FF1F8F', NAVY = '#1B2B5A';

    // Logo chico arriba a la izquierda, sobre placa blanca.
    if (imgLogo && imgLogo.naturalWidth) {
        const lw = W * .20, lh = lw * imgLogo.naturalHeight / imgLogo.naturalWidth;
        const lx = W * .055, ly = H * .04;
        rRect(lx - W * .02, ly - lh * .22, lw + W * .04, lh * 1.44, lh * .35);
        ctx.fillStyle = 'rgba(255,255,255,.92)'; ctx.fill();
        ctx.drawImage(imgLogo, lx, ly, lw, lh);
    }

    ctx.textAlign = 'center';

    // CTA / condición chica abajo del todo (solo si el usuario la escribió — nunca inventada).
    const ctaEl = document.getElementById('ovCta');
    const cta = ctaEl ? ctaEl.value.trim() : '';
    if (cta) {
        const fh = W * .026;
        ctx.font = '600 ' + fh + 'px Poppins, sans-serif';
        const tw = ctx.measureText(cta.toUpperCase()).width;
        rRect(W / 2 - tw / 2 - W * .035, H - H * .045 - fh * .9, tw + W * .07, fh * 1.5, fh * .75);
        ctx.fillStyle = '#FFFFFF'; ctx.fill();
        ctx.fillStyle = NAVY;
        ctx.fillText(cta.toUpperCase(), W / 2, H - H * .045 + fh * .18);
    }

    const badgeEl = document.getElementById('ovBadge');
    const badge = badgeEl ? badgeEl.value.trim() : '';
    if (badge) {
        const fh = W * .024;
        ctx.font = '700 ' + fh + 'px Poppins, sans-serif';
        const bw = ctx.measureText(badge.toUpperCase()).width;
        const padX = W * .03, pillW = bw + padX * 2, pillH = fh * 1.7;
        const bx = W - W * .05 - pillW, by = H * .04;
        rRect(bx, by, pillW, pillH, pillH / 2);
        ctx.fillStyle = FUCSIA; ctx.fill();
        ctx.fillStyle = '#FFFFFF';
        ctx.fillText(badge.toUpperCase(), bx + pillW / 2, by + pillH * .68);
    }
}

function envolverTexto(texto, x, y, maxW, lineH) {
    const palabras = texto.split(' ');
    let linea = '', yy = y;
    palabras.forEach(pal => {
        const test = linea ? linea + ' ' + pal : pal;
        if (ctx.measureText(test).width > maxW && linea) {
            ctx.fillText(linea, x, yy); linea = pal; yy += lineH;
        } else linea = test;
    });
    ctx.fillText(linea, x, yy);
}

/* ── Textos ── */
function pintarTextos() {
    const t = textosIA;
    if (!t) return;
    document.getElementById('txtTituloML').value = t.titulo_ml || '';
    document.getElementById('mlTituloLen').textContent = '(' + (t.titulo_ml || '').length + '/60)';
    document.getElementById('txtDescML').value = t.desc_ml || '';
    document.getElementById('txtCaption').value = t.caption || '';
    document.getElementById('txtWa').value = t.texto_wa || '';
}

/* ── Entrenamiento y recursos ── */
function guardarAjustes(btn) {
    const original = btn.innerHTML;
    btn.disabled = true;
    postJson('{{ route('publicaciones.ajustes') }}', {
        voz_marca: document.getElementById('entVoz').value,
        estilo_imagen: document.getElementById('entEstilo').value
    }).then(() => {
        ESTILO_IMG = document.getElementById('entEstilo').value;
        btn.innerHTML = '✓ Guardado';
        setTimeout(() => { btn.innerHTML = original; $('#modalEntrenar').modal('hide'); }, 900);
    }).catch(e => alert('No se pudo guardar: ' + e.message))
      .finally(() => { btn.disabled = false; });
}

function cambiarTipoRecurso() {
    const tipo = document.getElementById('recTipo').value;
    const esArchivo = tipo === 'imagen' || tipo === 'logo';
    document.getElementById('recContenido').style.display = esArchivo ? 'none' : '';
    document.getElementById('recArchivo').style.display = esArchivo ? '' : 'none';
}

function guardarRecurso(btn) {
    const tipo = document.getElementById('recTipo').value;
    const fd = new FormData();
    fd.append('tipo', tipo);
    fd.append('titulo', document.getElementById('recTitulo').value);
    if (tipo === 'imagen' || tipo === 'logo') {
        const f = document.getElementById('recArchivo').files[0];
        if (f) fd.append('archivo', f);
    } else {
        fd.append('contenido', document.getElementById('recContenido').value);
    }
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Guardando...';
    fetch('{{ route('publicaciones.recursos') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: fd
    }).then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok || data.status === 0) {
            throw new Error(data.error || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Error del servidor'));
        }
        RECURSOS.unshift({
            id: data.id, tipo: tipo,
            titulo: document.getElementById('recTitulo').value,
            contenido: document.getElementById('recContenido').value,
            archivo: null, archivo_url: data.archivo_url
        });
        document.getElementById('recTitulo').value = '';
        document.getElementById('recContenido').value = '';
        document.getElementById('recArchivo').value = '';
        renderRecursos();
    }).catch(e => alert('No se pudo guardar el recurso: ' + e.message))
      .finally(() => { btn.disabled = false; btn.innerHTML = original; });
}

function eliminarRecurso(id) {
    if (!confirm('¿Eliminar este recurso?')) return;
    fetch('{{ url('publicaciones/recursos') }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).then(() => {
        const i = RECURSOS.findIndex(r => r.id === id);
        if (i >= 0) RECURSOS.splice(i, 1);
        renderRecursos();
        renderReferencias();
    });
}

function renderRecursos() {
    const cont = document.getElementById('pubRecursos');
    const lista = RECURSOS.filter(r => r.tipo !== 'referencia');
    cont.innerHTML = lista.length ? '' : '<div class="pub-aviso">Todavía no hay recursos guardados.</div>';
    lista.forEach(r => {
        const url = r.archivo_url || (r.archivo ? BASE_URL + '/' + r.archivo : null);
        const card = document.createElement('div');
        card.className = 'pub-rec-card';
        card.innerHTML =
            '<button class="del" onclick="eliminarRecurso(' + r.id + ')" title="Eliminar"><i class="fas fa-trash-alt"></i></button>' +
            '<div class="tit"><i class="fas ' + (REC_ICO[r.tipo] || 'fa-file') + '" style="color:#2563EB;"></i> ' + r.titulo +
            ' <span class="pub-rec-tipo">' + (REC_LBL[r.tipo] || r.tipo) + '</span></div>' +
            (url ? '<img src="' + url + '" onclick="window.open(this.src)" style="cursor:pointer;">' : '') +
            (r.contenido ? '<div class="cuerpo">' + String(r.contenido).replace(/</g, '&lt;') + '</div>' : '');
        cont.appendChild(card);
    });
}

/* ── Imágenes de referencia (estilo visual a imitar) ── */
function subirReferencias(input) {
    const archivos = Array.from(input.files || []);
    if (!archivos.length) return;
    Promise.all(archivos.map(f => {
        const fd = new FormData();
        fd.append('tipo', 'referencia');
        fd.append('titulo', f.name);
        fd.append('archivo', f);
        return fetch('{{ route('publicaciones.recursos') }}', {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd
        }).then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok || data.status === 0) throw new Error(data.error || 'Error del servidor');
            RECURSOS.unshift({ id: data.id, tipo: 'referencia', titulo: f.name, contenido: null, archivo: null, archivo_url: data.archivo_url });
        });
    })).then(() => {
        renderReferencias();
        bubbleChat('sistema', '🖼️ Se agregaron ' + archivos.length + ' imagen(es) de referencia. Las próximas generaciones van a imitar ese estilo.');
    }).catch(e => alert('No se pudo subir alguna imagen: ' + e.message))
      .finally(() => { input.value = ''; });
}

function renderReferencias() {
    const cont = document.getElementById('pubReferencias');
    if (!cont) return;
    const lista = RECURSOS.filter(r => r.tipo === 'referencia');
    cont.innerHTML = lista.length ? '' : '<div class="pub-aviso">Todavía no subiste imágenes de referencia.</div>';
    lista.forEach(r => {
        const url = r.archivo_url || (r.archivo ? BASE_URL + '/' + r.archivo : null);
        const card = document.createElement('div');
        card.className = 'pub-rec-card';
        card.innerHTML =
            '<button class="del" onclick="eliminarRecurso(' + r.id + ')" title="Eliminar"><i class="fas fa-trash-alt"></i></button>' +
            (url ? '<img src="' + url + '" onclick="window.open(this.src)" style="cursor:pointer;">' : '');
        cont.appendChild(card);
    });
}

/* ── Guardar / publicar / programar ── */
function payloadBase() {
    const p = productoActual();
    return {
        producto_id: p ? p.id : null, es_combo: p ? !!p.esCombo : false,
        formato: opcion('pubFormato'), estilo: 'ia',
        titulo_ml: document.getElementById('txtTituloML').value,
        desc_ml: document.getElementById('txtDescML').value,
        caption: document.getElementById('txtCaption').value,
        texto_wa: document.getElementById('txtWa').value,
        imagen_escena: varianteElegida ? varianteElegida.path : null,
        prompt_escena: varianteElegida ? varianteElegida.prompt : null,
        imagen_base64: canvas.toDataURL('image/png'),
        campana_id: document.getElementById('pubCampana').value || null,
        overlay: {
            headline: document.getElementById('ovHeadline').value.trim() || null,
            cta: document.getElementById('ovCta').value.trim() || null,
            badge: document.getElementById('ovBadge').value.trim() || null,
            website: document.getElementById('ovWebsite').checked
        }
    };
}

/* ── Campañas ── */
function renderCampanas() {
    ['pubCampana', 'briefCampana'].forEach(id => {
        const s = document.getElementById(id);
        if (!s) return;
        const actual = s.value;
        s.innerHTML = '<option value="">— Sin campaña —</option>';
        CAMPANAS.forEach(c => { s.innerHTML += '<option value="' + c.id + '">' + c.nombre + '</option>'; });
        s.value = actual;
    });
}

function nuevaCampana() {
    const nombre = prompt('Nombre de la campaña (ej: Ofertas Octubre, Eclipse, Hot Sale):');
    if (!nombre || !nombre.trim()) return;
    postJson('{{ route('publicaciones.campanas') }}', { nombre: nombre.trim() }).then(data => {
        CAMPANAS.unshift({ id: data.id, nombre: data.nombre });
        renderCampanas();
        document.getElementById('pubCampana').value = data.id;
        if (document.getElementById('briefCampana')) document.getElementById('briefCampana').value = data.id;
    }).catch(e => alert('No se pudo crear la campaña: ' + e.message));
}

/* ── Brief de campaña: interpreta y genera en serie ── */
let BRIEF_ITEMS = [];

function interpretarBriefTexto(btn) {
    const texto = document.getElementById('briefTexto').value.trim();
    if (!texto) return;
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Interpretando...';
    postJson('{{ route('publicaciones.brief') }}', { brief: texto }).then(data => {
        BRIEF_ITEMS = data.items || [];
        renderBriefItems();
        document.getElementById('briefResultado').style.display = BRIEF_ITEMS.length ? '' : 'none';
    }).catch(e => alert('No se pudo interpretar el brief: ' + e.message))
      .finally(() => { btn.disabled = false; btn.innerHTML = original; });
}

function renderBriefItems() {
    document.getElementById('briefResumen').textContent = BRIEF_ITEMS.length + ' piezas interpretadas. Revisá el producto detectado antes de generar — nada se generó todavía.';
    document.getElementById('btnGuardarSeleccionadasBrief').style.display = 'none';
    const cont = document.getElementById('briefLista');
    cont.innerHTML = '';
    BRIEF_ITEMS.forEach((it, i) => {
        const esCombo = it.modo === 'combo';
        const prod = it.producto_id ? PRODUCTOS.find(p => p.id === it.producto_id && !!p.esCombo === esCombo) : null;
        const row = document.createElement('div');
        row.className = 'pub-brief-item';
        row.innerHTML =
            '<span class="num">' + (it.numero ?? (i + 1)) + '</span>' +
            '<div><div class="tit">' + it.titulo + '<span class="badge-modo">' + it.modo + (prod ? ' · ' + prod.nombre : '') + '</span></div>' +
            '<div class="desc">' + it.instrucciones + (it.menciona_tambien ? ' <em>(también: ' + it.menciona_tambien + ')</em>' : '') + '</div>' +
            '<div class="pub-msg-imagenes" id="briefVariantes' + i + '"></div></div>' +
            '<button type="button" class="badge-precio" id="briefPrecio' + i + '" onclick="toggleBriefPrecio(' + i + ')"></button>' +
            '<span class="estado-item" id="briefEstado' + i + '">Pendiente</span>';
        cont.appendChild(row);
        pintarBriefPrecio(i);
    });
}

function pintarBriefPrecio(i) {
    const btn = document.getElementById('briefPrecio' + i);
    if (!btn) return;
    const on = !!BRIEF_ITEMS[i].con_precio;
    btn.textContent = on ? '$ con precio' : 'sin precio';
    btn.style.background = on ? '#FFE600' : '#F1F4F9';
    btn.style.color = '#1B2B5A';
}

function toggleBriefPrecio(i) {
    BRIEF_ITEMS[i].con_precio = !BRIEF_ITEMS[i].con_precio;
    pintarBriefPrecio(i);
}

function generarBriefTodo(btn) {
    if (!BRIEF_ITEMS.length) return;
    if (!confirm('Se van a generar 3 variantes por cada una de las ' + BRIEF_ITEMS.length + ' piezas, una por una (puede tardar varios minutos). Después elegís la mejor de cada una antes de guardar. ¿Continuar?')) return;
    btn.disabled = true;

    let cadena = Promise.resolve();
    BRIEF_ITEMS.forEach((it, i) => {
        cadena = cadena.then(() => {
            const estadoEl = document.getElementById('briefEstado' + i);
            estadoEl.textContent = 'Generando...';
            return generarVariantesItemBrief(it, i)
                .then(() => { estadoEl.textContent = '👉 Elegí una'; })
                .catch(e => { estadoEl.textContent = '⚠️ Falló'; console.error(it.titulo, e); });
        });
    });
    cadena.then(() => {
        btn.disabled = false;
        document.getElementById('btnGuardarSeleccionadasBrief').style.display = '';
        alert('Listo. Elegí la mejor variante de cada pieza (click en la miniatura) y después tocá "Guardar seleccionadas".');
    });
}

/** Fase 1: genera 3 variantes de imagen + copy para una pieza, sin guardar nada todavía. */
function generarVariantesItemBrief(it, i) {
    const esCombo = it.modo === 'combo';
    const formato = it.formato === 'story' ? 'story' : 'feed';

    return postJson('{{ route('publicaciones.chat') }}', {
        producto_id: it.producto_id || null,
        es_combo: esCombo,
        formato: formato,
        con_precio: !!it.con_precio,
        mensaje: 'Generá 3 variantes de imagen y el texto para esta pieza de campaña: "' + it.titulo + '". ' + it.instrucciones
            + (it.producto_id ? ' El titular del banner de la imagen tiene que ser exactamente: "' + it.titulo + '".' : ''),
        historial: []
    }).then(data => {
        const variantes = (data.imagenes || []).filter(v => !v.error);
        if (!variantes.length) throw new Error('No se generó ninguna imagen para esta pieza.');
        it.variantes = variantes;
        it.textos = data.textos || null;
        it.seleccion = 0;
        renderVariantesBrief(i);
    });
}

function renderVariantesBrief(i) {
    const it = BRIEF_ITEMS[i];
    const cont = document.getElementById('briefVariantes' + i);
    cont.innerHTML = it.variantes.map((v, j) =>
        '<div class="pub-variante chica' + (j === it.seleccion ? ' seleccionada' : '') + '" onclick="elegirVarianteBrief(' + i + ',' + j + ')">' +
            '<img src="' + v.url + '"><div class="check"><i class="fas fa-check"></i></div>' +
        '</div>'
    ).join('');
}

function elegirVarianteBrief(i, j) {
    BRIEF_ITEMS[i].seleccion = j;
    renderVariantesBrief(i);
}

/** Fase 2: dibuja (logo + overlay real) y guarda la variante que el usuario eligió de cada pieza. */
function guardarSeleccionadasBrief(btn) {
    const pendientes = BRIEF_ITEMS.filter(it => it.variantes && it.variantes.length);
    if (!pendientes.length) { alert('Primero generá las variantes.'); return; }
    btn.disabled = true;
    const campanaId = document.getElementById('briefCampana').value || null;

    let cadena = Promise.resolve();
    BRIEF_ITEMS.forEach((it, i) => {
        if (!it.variantes || !it.variantes.length) return;
        cadena = cadena.then(() => {
            const estadoEl = document.getElementById('briefEstado' + i);
            estadoEl.textContent = 'Guardando...';
            return guardarUnaSeleccionBrief(it, campanaId)
                .then(() => { estadoEl.textContent = '✓ Guardada'; })
                .catch(e => { estadoEl.textContent = '⚠️ Falló al guardar'; console.error(it.titulo, e); });
        });
    });
    cadena.then(() => {
        btn.disabled = false;
        renderFeedSimulado(); renderProximas();
        alert('Listo, se guardaron las piezas seleccionadas como borrador — revisalas en la Biblioteca/Feed antes de publicar.');
    });
}

function guardarUnaSeleccionBrief(it, campanaId) {
    const esCombo = it.modo === 'combo';
    const prodIdx = it.producto_id ? PRODUCTOS.findIndex(p => p.id === it.producto_id && !!p.esCombo === esCombo) : -1;
    const formato = it.formato === 'story' ? 'story' : 'feed';
    const v = it.variantes[it.seleccion];

    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            if (prodIdx >= 0) { sel.value = prodIdx; modoContenido = 'producto'; }
            else { modoContenido = 'marca'; }
            document.querySelector('input[name=pubFormato][value="' + formato + '"]').checked = true;
            document.querySelector('input[name=pubPrecio][value="' + (it.con_precio ? 'si' : 'no') + '"]').checked = true;
            varianteElegida = { img, url: v.url, path: v.path, prompt: v.prompt };
            pubGuardadaId = null;
            ['ovHeadline', 'ovCta', 'ovBadge'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('ovWebsite').checked = false;
            if (it.textos) {
                document.getElementById('txtCaption').value = it.textos.caption || '';
                document.getElementById('txtTituloML').value = it.textos.titulo_ml || it.titulo;
                document.getElementById('txtDescML').value = it.textos.desc_ml || '';
                document.getElementById('txtWa').value = it.textos.texto_wa || '';
            }
            cargarLogo(() => {
                dibujar();
                postJson('{{ route('publicaciones.guardar') }}', Object.assign(payloadBase(), { campana_id: campanaId }))
                    .then(() => resolve())
                    .catch(reject);
            });
        };
        img.onerror = () => reject(new Error('No se pudo cargar la imagen generada.'));
        img.src = v.url;
    });
}

/* ── Historial de conversaciones y prompts ── */
let HISTORIAL_CACHE = null;

function abrirHistorial() {
    $('#modalHistorial').modal('show');
    cambiarTabHistorial('conversaciones');
    if (HISTORIAL_CACHE) { renderHistorialListas(); return; }
    document.getElementById('histCargando').style.display = '';
    fetch('{{ route('publicaciones.historial') }}', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            HISTORIAL_CACHE = data;
            renderHistorialListas();
        })
        .catch(() => { document.getElementById('histCargando').textContent = 'No se pudo cargar el historial.'; })
        .finally(() => { document.getElementById('histCargando').style.display = 'none'; });
}

/* ── Galería: todas las imágenes generadas alguna vez, con acceso directo a cada una ── */
let GALERIA_CACHE = null;

function abrirGaleria() {
    $('#modalGaleria').modal('show');
    if (GALERIA_CACHE) { renderGaleria(); return; }
    document.getElementById('galeriaCargando').style.display = '';
    fetch('{{ route('publicaciones.galeria') }}', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            GALERIA_CACHE = data.imagenes || [];
            renderGaleria();
        })
        .catch(() => { document.getElementById('galeriaCargando').textContent = 'No se pudo cargar la galería.'; })
        .finally(() => { document.getElementById('galeriaCargando').style.display = 'none'; });
}

function renderGaleria() {
    const grid = document.getElementById('galeriaGrid');
    grid.innerHTML = GALERIA_CACHE.length ? '' : '<div class="pub-aviso" style="padding:14px;">Todavía no se generó ninguna imagen.</div>';
    GALERIA_CACHE.forEach(img => {
        const fecha = new Date(img.created_at).toLocaleString('es-AR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
        const item = document.createElement('div');
        item.className = 'pub-galeria-item';
        item.title = 'Abrir imagen completa';
        item.innerHTML = '<img src="' + img.url + '" loading="lazy"><div class="fecha">' + fecha + '</div>';
        item.onclick = () => window.open(img.url, '_blank');
        grid.appendChild(item);
    });
}

function cambiarTabHistorial(tab) {
    document.getElementById('histTabConv').classList.toggle('activo', tab === 'conversaciones');
    document.getElementById('histTabPrompts').classList.toggle('activo', tab === 'prompts');
    document.getElementById('histConversaciones').style.display = tab === 'conversaciones' ? '' : 'none';
    document.getElementById('histPrompts').style.display = tab === 'prompts' ? '' : 'none';
}

function renderHistorialListas() {
    if (!HISTORIAL_CACHE) return;

    const contConv = document.getElementById('histConversaciones');
    contConv.innerHTML = HISTORIAL_CACHE.conversaciones.length ? '' : '<div class="pub-aviso" style="padding:14px;">Todavía no hay conversaciones registradas.</div>';
    HISTORIAL_CACHE.conversaciones.forEach(c => {
        const nombre = c.producto_id ? ((PRODUCTOS.find(p => p.id === c.producto_id) || {}).nombre || 'Producto') : 'Contenido de marca';
        const tags = (c.genero_imagenes ? '<span>Imágenes</span>' : '') + (c.genero_textos ? '<span>Texto</span>' : '');
        let imagenesHtml = '';
        if (c.imagenes_json) {
            try {
                const imgs = JSON.parse(c.imagenes_json).filter(v => !v.error);
                if (imgs.length) {
                    imagenesHtml = '<div class="pub-msg-imagenes">' + imgs.map(v =>
                        '<div class="pub-variante chica" onclick="window.open(\'' + v.url + '\', \'_blank\')"><img src="' + v.url + '"></div>'
                    ).join('') + '</div>';
                }
            } catch (e) {}
        }
        const row = document.createElement('div');
        row.className = 'pub-hist-row';
        row.innerHTML =
            '<div class="fecha">' + new Date(c.created_at).toLocaleString('es-AR') + ' · ' + nombre + '</div>' +
            '<div class="msg-user">' + String(c.mensaje_usuario).replace(/</g, '&lt;') + '</div>' +
            '<div class="msg-resp">' + String(c.respuesta_asistente || '').replace(/</g, '&lt;') + '</div>' +
            imagenesHtml +
            (tags ? '<div class="tags">' + tags + '</div>' : '');
        contConv.appendChild(row);
    });

    const contProm = document.getElementById('histPrompts');
    contProm.innerHTML = HISTORIAL_CACHE.generaciones.length ? '' : '<div class="pub-aviso" style="padding:14px;">Todavía no hay generaciones registradas.</div>';
    HISTORIAL_CACHE.generaciones.forEach(g => {
        const row = document.createElement('div');
        row.className = 'pub-hist-row';
        row.innerHTML =
            '<div class="fecha">' + new Date(g.created_at).toLocaleString('es-AR') + ' · ' + g.modelo + ' · ' + g.formato +
            ' · <span class="' + (g.estado === 'ok' ? 'estado-ok' : 'estado-error') + '">' + g.estado + '</span></div>' +
            '<div class="prompt-txt">' + String(g.prompt_final || '').replace(/</g, '&lt;') + '</div>' +
            (g.error ? '<div class="prompt-txt estado-error">' + String(g.error).replace(/</g, '&lt;') + '</div>' : '');
        contProm.appendChild(row);
    });
}

/* ── Export a tamaños fijos (no cambia el formato elegido, solo exporta) ── */
function exportarPreset(preset) {
    if (!varianteElegida) return;
    const formatoActual = opcion('pubFormato');
    dibujarConVariante(varianteElegida, preset);
    const p = productoActual();
    const slug = p ? 'sommy-' + p.nombre.toLowerCase().replace(/[^a-z0-9]+/g, '-') : 'sommy-contenido-marca';
    const a = document.createElement('a');
    a.download = slug + '-' + preset + '.png';
    a.href = canvas.toDataURL('image/png');
    a.click();
    dibujarConVariante(varianteElegida, formatoActual);
    document.getElementById('pubPreview').src = canvas.toDataURL('image/png');
}

function guardarBorrador(btn) {
    const original = btn ? btn.innerHTML : null;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Guardando...'; }
    return postJson('{{ route('publicaciones.guardar') }}', payloadBase()).then(data => {
        pubGuardadaId = data.id;
        const p = productoActual();
        BIBLIOTECA.unshift({ id: data.id, producto_id: p ? p.id : null, imagen_url: data.imagen_url, estado: 'borrador', programado_para: null, created_at: new Date().toISOString() });
        renderFeedSimulado(); renderProximas();
        if (btn) { btn.innerHTML = '✓ Guardado'; document.getElementById('avisoAccion').textContent = 'Guardado como borrador en la biblioteca.'; setTimeout(() => btn.innerHTML = original, 1500); }
        return data.id;
    }).finally(() => { if (btn) { btn.disabled = false; if (btn.innerHTML.includes('Guardando')) btn.innerHTML = original; } });
}

function publicarAhora(btn) {
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Publicando...';
    const asegurar = pubGuardadaId ? Promise.resolve(pubGuardadaId) : guardarBorrador(null);
    asegurar.then(id =>
        postJson('{{ route('publicaciones.publicar') }}', { publicacion_id: id, canales: ['facebook', 'instagram'] })
    ).then(data => {
        const p = productoActual();
        if (p) {
            (data.publicados || []).forEach(canal => {
                if (!REGISTROS[p.id]) REGISTROS[p.id] = [];
                REGISTROS[p.id].unshift({ canal, created_at: new Date().toISOString().slice(0, 10) });
            });
        }
        renderHistorial();
        const item = BIBLIOTECA.find(b => b.id === pubGuardadaId);
        if (item) item.estado = 'publicada';
        renderFeedSimulado();
        const errMsgs = Object.values(data.errores || {});
        document.getElementById('avisoAccion').textContent = errMsgs.length
            ? 'Publicado en ' + (data.publicados || []).join(', ') + '. Falló: ' + errMsgs.join(' | ')
            : 'Publicado en Instagram y Facebook.';
        btn.innerHTML = '✓ Publicado';
        setTimeout(() => btn.innerHTML = original, 2000);
    }).catch(e => {
        alert('No se pudo publicar: ' + e.message);
        btn.innerHTML = original;
    }).finally(() => { btn.disabled = false; });
}

function programar(btn) {
    const fecha = document.getElementById('pubFecha').value;
    if (!fecha) { alert('Elegí fecha y hora.'); return; }
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Programando...';
    postJson('{{ route('publicaciones.guardar') }}', Object.assign(payloadBase(), {
        programado_para: fecha, canales_programados: ['facebook', 'instagram']
    })).then(data => {
        pubGuardadaId = data.id;
        const p = productoActual();
        BIBLIOTECA.unshift({ id: data.id, producto_id: p ? p.id : null, imagen_url: data.imagen_url, estado: 'programada', programado_para: fecha, created_at: new Date().toISOString() });
        renderFeedSimulado(); renderProximas();
        document.getElementById('avisoAccion').textContent = 'Programado para el ' + new Date(fecha).toLocaleString('es-AR') + '. Se publica solo.';
        btn.innerHTML = '✓ Programado';
        setTimeout(() => btn.innerHTML = original, 2000);
    }).catch(e => alert('No se pudo programar: ' + e.message))
      .finally(() => { btn.disabled = false; });
}

function copiarTexto(id, btn) {
    navigator.clipboard.writeText(document.getElementById(id).value || document.getElementById(id).textContent).then(() => {
        const t = btn.textContent; btn.textContent = '¡Copiado!';
        setTimeout(() => btn.textContent = t, 1500);
    });
}

function descargarContenido() {
    const p = productoActual();
    const slug = p ? 'sommy-' + p.nombre.toLowerCase().replace(/[^a-z0-9]+/g, '-') : 'sommy-contenido-marca';
    const a = document.createElement('a');
    a.download = slug + '-' + opcion('pubFormato') + '.png';
    a.href = canvas.toDataURL('image/png');
    a.click();
}

function renderHistorial() {
    const p = productoActual();
    const cont = document.getElementById('pubHistorial');
    if (!p) { cont.innerHTML = ''; return; }
    document.getElementById('pubLinkConocimiento').href = BASE_URL + '/articulo/' + p.id + '/conocimiento';
    const regs = REGISTROS[p.id] || [];
    cont.innerHTML = regs.length ? '<label style="width:100%;">Historial de este producto</label>' : '';
    regs.slice(0, 8).forEach(r => {
        cont.innerHTML += '<span class="badge" style="background:#E0F2FE;color:#1B2B5A;">' +
            r.canal + ' · ' + String(r.created_at).slice(0, 10) + '</span>';
    });
}

/* ── Próximas programadas ── */
function renderProximas() {
    const prog = BIBLIOTECA.filter(b => b.estado === 'programada' && b.programado_para)
        .sort((a, b) => new Date(a.programado_para) - new Date(b.programado_para));
    document.getElementById('btnAbrirProximas').style.display = prog.length ? '' : 'none';
    document.getElementById('pubProximasCount').textContent = prog.length ? '(' + prog.length + ')' : '';
    const cont = document.getElementById('pubProximas');
    cont.innerHTML = '';
    prog.forEach(b => {
        const nombre = b.producto_id ? ((PRODUCTOS.find(p => p.id === b.producto_id) || {}).nombre || 'Producto') : 'Contenido de marca';
        const url = b.imagen_url || (b.imagen_final ? BASE_URL + '/' + b.imagen_final : null);
        const row = document.createElement('div');
        row.className = 'pub-proxima';
        row.innerHTML = (url ? '<img src="' + url + '">' : '') +
            '<span class="cuando">' + new Date(b.programado_para).toLocaleString('es-AR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) + '</span>' +
            '<span>' + nombre + '</span>';
        cont.appendChild(row);
    });
}

/* ── Simulador de feed ── */
function cambiarRedFeed(red, btn) {
    redFeedActiva = red;
    document.querySelectorAll('.pub-feed-tab').forEach(t => t.classList.remove('activo'));
    btn.classList.add('activo');
    document.getElementById('pubFeedHandle').textContent = red === 'instagram' ? '@sommy_colchoneria' : 'Sommy';
    renderFeedSimulado();
}

let feedCantidad = 9;
function cambiarCantidadFeed(n, btn) {
    feedCantidad = n;
    document.querySelectorAll('.pub-feed-count-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    renderFeedSimulado();
}

function renderFeedSimulado() {
    const visible = b => (b.imagen_url || b.imagen_final) && ['publicada', 'programada', 'borrador'].includes(b.estado);
    const conOrden = BIBLIOTECA.filter(b => visible(b) && b.orden_feed !== null && b.orden_feed !== undefined)
        .sort((a, b) => a.orden_feed - b.orden_feed);
    const sinOrden = BIBLIOTECA.filter(b => visible(b) && (b.orden_feed === null || b.orden_feed === undefined))
        .sort((a, b) => new Date(b.programado_para || b.created_at) - new Date(a.programado_para || a.created_at));
    const items = conOrden.concat(sinOrden);

    const cont = document.getElementById('pubFeedGrid');
    cont.innerHTML = '';

    // Vista previa en vivo: lo que se está armando ahora, todavía sin guardar.
    if (varianteElegida && !pubGuardadaId) {
        const previo = document.createElement('div');
        previo.className = 'pub-feed-item preview';
        previo.innerHTML = '<img src="' + canvas.toDataURL('image/png') + '"><div class="badge-preview">Vista previa</div>';
        cont.appendChild(previo);
    }

    document.getElementById('pubFeedEmpty').style.display = (items.length || (varianteElegida && !pubGuardadaId)) ? 'none' : '';
    items.slice(0, feedCantidad).forEach(b => {
        const url = b.imagen_url || (BASE_URL + '/' + b.imagen_final);
        const item = document.createElement('div');
        item.className = 'pub-feed-item';
        item.draggable = true;
        item.dataset.id = b.id;
        const badge = b.estado === 'programada'
            ? '<div class="badge-prog">Programada · ' + new Date(b.programado_para).toLocaleDateString('es-AR', { day: '2-digit', month: 'short' }) + '</div>'
            : (b.estado === 'borrador' ? '<div class="badge-prog" style="background:rgba(71,83,111,.85);">Borrador</div>' : '');
        item.innerHTML = '<img src="' + url + '">' + badge;
        item.addEventListener('dragstart', () => item.classList.add('arrastrando'));
        item.addEventListener('dragend', () => item.classList.remove('arrastrando'));
        item.addEventListener('dragover', e => { e.preventDefault(); item.classList.add('sobre-drop'); });
        item.addEventListener('dragleave', () => item.classList.remove('sobre-drop'));
        item.addEventListener('drop', e => {
            e.preventDefault();
            item.classList.remove('sobre-drop');
            const origen = cont.querySelector('.arrastrando');
            if (!origen || origen === item) return;
            const nodos = Array.from(cont.children).filter(n => n.dataset && n.dataset.id);
            const idxOrigen = nodos.indexOf(origen), idxDestino = nodos.indexOf(item);
            if (idxOrigen < idxDestino) item.after(origen); else item.before(origen);
            guardarOrdenFeed();
        });
        cont.appendChild(item);
    });
}

function guardarOrdenFeed() {
    const ids = Array.from(document.getElementById('pubFeedGrid').children)
        .filter(n => n.dataset && n.dataset.id)
        .map(n => parseInt(n.dataset.id, 10));
    ids.forEach((id, i) => {
        const b = BIBLIOTECA.find(x => x.id === id);
        if (b) b.orden_feed = i;
    });
    postJson('{{ route('publicaciones.feed-orden') }}', { orden: ids }).catch(() => {});
}

/* ── Configurar (producto/combo + formato + precio) ── */
const FORMATO_LBL = { feed: 'Feed 4:5', story: 'Historia 9:16', ml: 'MercadoLibre 1:1' };
function actualizarResumenConfig() {
    const p = productoActual();
    const nombre = p ? p.nombre : 'Contenido de marca';
    document.getElementById('pubConfigResumen').textContent = nombre + ' · ' + FORMATO_LBL[opcion('pubFormato')];
}

/* ── Eventos ── */
sel.addEventListener('change', () => {
    variantes = []; varianteElegida = null; textosIA = null; pubGuardadaId = null; historialChat = [];
    document.getElementById('panelVariantes').style.display = 'none';
    document.getElementById('panelResultado').style.display = 'none';
    renderHistorial();
    renderFeedSimulado();
    actualizarResumenConfig();
    saludoInicial();
});
document.querySelectorAll('input[name=pubPrecio]').forEach(el => el.addEventListener('change', () => { dibujar(); renderFeedSimulado(); }));
document.querySelectorAll('input[name=pubFormato]').forEach(el => el.addEventListener('change', actualizarResumenConfig));

['ovHeadline', 'ovCta', 'ovBadge'].forEach(id => document.getElementById(id).addEventListener('input', () => { if (varianteElegida) { dibujar(); document.getElementById('pubPreview').src = canvas.toDataURL('image/png'); } }));
document.getElementById('ovWebsite').addEventListener('change', () => { if (varianteElegida) { dibujar(); document.getElementById('pubPreview').src = canvas.toDataURL('image/png'); } });
document.querySelectorAll('input[name=pubEstiloOverlay]').forEach(el => el.addEventListener('change', () => { if (varianteElegida) { dibujar(); document.getElementById('pubPreview').src = canvas.toDataURL('image/png'); renderFeedSimulado(); } }));

document.fonts.ready.then(() => {
    cargarLogo(() => {});
    renderHistorial();
    renderRecursos();
    renderFeedSimulado();
    renderProximas();
    renderCampanas();
    actualizarResumenConfig();
    cambiarTipoRecurso();
    saludoInicial();
});
</script>
@endsection
