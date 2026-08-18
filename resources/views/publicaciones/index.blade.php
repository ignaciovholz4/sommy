@extends('layouts.admin')

@section('title', 'Publicaciones')

@section('contenido')
<style>
    .pub-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1150px; margin: 0 auto; }
    .pub-title { font-size: 21px; font-weight: 600; margin-bottom: 2px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .pub-sub { font-size: 13.5px; color: #6E7A96; font-weight: 300; margin-bottom: 18px; }

    .pub-stepper { display: flex; align-items: center; margin-bottom: 20px; }
    .pub-step { display: flex; align-items: center; gap: 9px; cursor: pointer; user-select: none; }
    .pub-step .circ {
        width: 34px; height: 34px; border-radius: 999px; display: flex; align-items: center; justify-content: center;
        background: #fff; border: 2px solid #E7EAF2; color: #94A3B8; font-weight: 600; font-size: 14px; transition: all .2s;
    }
    .pub-step .lbl { font-size: 12.5px; font-weight: 500; color: #94A3B8; white-space: nowrap; }
    .pub-step.activo .circ { background: #1B2B5A; border-color: #1B2B5A; color: #fff; box-shadow: 0 6px 18px rgba(27,43,90,.25); }
    .pub-step.activo .lbl { color: #1B2B5A; font-weight: 600; }
    .pub-step.hecho .circ { background: #E0F2FE; border-color: #7FD4F5; color: #2563EB; }
    .pub-step.hecho .lbl { color: #47536F; }
    .pub-step-linea { flex: 1; height: 2px; background: #E7EAF2; margin: 0 12px; min-width: 24px; }
    @media (max-width: 767px) { .pub-step .lbl { display: none; } }

    .pub-panel {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.08); padding: 20px;
    }
    .pub-panel h3 { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #47536F; margin-bottom: 12px; }
    .pub-panel label { font-size: 13px; font-weight: 500; margin: 10px 0 4px; display: block; }
    .pub-panel select, .pub-panel input[type=text], .pub-panel textarea {
        width: 100%; border: 1px solid #E7EAF2; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; color: #1B2B5A; font-family: 'Poppins', sans-serif;
    }
    .pub-panel textarea { min-height: 110px; line-height: 1.55; resize: vertical; }

    .pub-paso { display: none; }
    .pub-paso.activo { display: block; }

    .pub-cols2 { display: grid; grid-template-columns: 360px 1fr; gap: 16px; align-items: start; }
    @media (max-width: 991px) { .pub-cols2 { grid-template-columns: 1fr; } }

    .pub-opts { display: flex; gap: 8px; flex-wrap: wrap; }
    .pub-opt input { display: none; }
    .pub-opt span {
        display: inline-block; padding: 7px 14px; border-radius: 999px; border: 1.5px solid #E7EAF2;
        font-size: 12.5px; font-weight: 500; color: #47536F; cursor: pointer; transition: all .15s;
    }
    .pub-opt input:checked + span { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }

    .pub-canvas-box { text-align: center; }
    #pubCanvas, #pubVideoPreview {
        max-width: 100%; max-height: 520px; border-radius: 12px;
        border: 1px solid #E7EAF2; box-shadow: 0 10px 30px rgba(27,43,90,.10);
    }
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

    .pub-nav { display: flex; justify-content: space-between; margin-top: 18px; }

    .pub-texto {
        background: #F8FAFC; border: 1px solid #E7EAF2; border-radius: 12px;
        padding: 12px; font-size: 13px; color: #47536F; white-space: pre-wrap;
        max-height: 170px; overflow-y: auto; margin-bottom: 8px; line-height: 1.6;
    }
    .pub-copy { font-size: 12px; }
    .pub-hist { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
    .pub-hist .badge { border-radius: 999px; font-weight: 500; font-size: 11px; padding: 5px 10px; }

    .pub-aviso { font-size: 11.5px; color: #6E7A96; margin-top: 6px; font-weight: 300; }
    .pub-aviso.warn { color: #b45309; }

    .pub-final-grid { display: grid; grid-template-columns: 280px 1fr; gap: 18px; align-items: start; }
    @media (max-width: 767px) { .pub-final-grid { grid-template-columns: 1fr; } }
    #pubMini, #pubMiniVideo { width: 100%; border-radius: 12px; border: 1px solid #E7EAF2; box-shadow: 0 8px 24px rgba(27,43,90,.10); }

    .pub-biblio { margin-top: 16px; }
    .pub-biblio-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; margin-top: 6px; }
    .pub-biblio-card { border: 1px solid #E7EAF2; border-radius: 12px; overflow: hidden; background: #F8FAFC; text-align: center; }
    .pub-biblio-card img { width: 100%; aspect-ratio: 1/1; object-fit: cover; display: block; cursor: pointer; }
    .pub-biblio-card .meta { font-size: 10.5px; color: #47536F; padding: 6px 4px; }
    .pub-biblio-card .estado { display: inline-block; border-radius: 999px; padding: 1px 8px; font-size: 9.5px; font-weight: 600; }
    .pub-biblio-card .estado.publicada { background: #DCFCE7; color: #166534; }
    .pub-biblio-card .estado.borrador { background: #E0F2FE; color: #1B2B5A; }

    /* Recursos de marca */
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
        <button class="pub-btn sec chico" onclick="$('#modalEntrenar').modal('show')"><i class="fas fa-graduation-cap"></i> Entrenar IA</button>
    </div>
    <div class="pub-sub">Creá el contenido paso a paso y subilo a tus redes — todo con los datos reales del ERP.</div>

    {{-- Stepper --}}
    <div class="pub-stepper">
        <div class="pub-step activo" data-paso="1" onclick="irPaso(1)"><span class="circ">1</span><span class="lbl">Producto</span></div>
        <div class="pub-step-linea"></div>
        <div class="pub-step" data-paso="2" onclick="irPaso(2)"><span class="circ">2</span><span class="lbl">Contenido</span></div>
        <div class="pub-step-linea"></div>
        <div class="pub-step" data-paso="3" onclick="irPaso(3)"><span class="circ">3</span><span class="lbl">Textos</span></div>
        <div class="pub-step-linea"></div>
        <div class="pub-step" data-paso="4" onclick="irPaso(4)"><span class="circ">4</span><span class="lbl">Publicar</span></div>
    </div>

    {{-- PASO 1 --}}
    <div class="pub-paso activo" id="paso1">
        <div class="pub-panel">
            <h3>1 · Elegí el producto y el formato</h3>
            <div class="pub-cols2">
                <div>
                    <label>Producto</label>
                    <select id="pubProducto"></select>
                    <a id="pubLinkConocimiento" href="#" class="pub-aviso" style="display:inline-block;color:#2563EB;margin-top:6px;">
                        <i class="fas fa-brain"></i> Conocimiento del producto (contexto para la IA)
                    </a>
                    <div id="pubHistorial" class="pub-hist"></div>
                </div>
                <div>
                    <label style="margin-top:0;">Formato</label>
                    <div class="pub-opts" id="pubFormatos">
                        <label class="pub-opt"><input type="radio" name="pubFormato" value="ml" checked><span>MercadoLibre 1:1</span></label>
                        <label class="pub-opt"><input type="radio" name="pubFormato" value="post"><span>Post IG/FB 1:1</span></label>
                        <label class="pub-opt"><input type="radio" name="pubFormato" value="story"><span>Historia 9:16</span></label>
                    </div>

                    <label>Estilo</label>
                    <div class="pub-opts">
                        <label class="pub-opt"><input type="radio" name="pubEstilo" value="claro" checked><span>Claro (catálogo)</span></label>
                        <label class="pub-opt"><input type="radio" name="pubEstilo" value="noche"><span>Noche (premium)</span></label>
                    </div>

                    <label>Mostrar precio</label>
                    <div class="pub-opts">
                        <label class="pub-opt"><input type="radio" name="pubPrecio" value="si" checked><span>Sí</span></label>
                        <label class="pub-opt"><input type="radio" name="pubPrecio" value="no"><span>No</span></label>
                    </div>
                </div>
            </div>
            <div class="pub-nav">
                <span></span>
                <button class="pub-btn" onclick="irPaso(2)">Siguiente: Contenido <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
    </div>

    {{-- PASO 2 · Contenido (imagen o video) --}}
    <div class="pub-paso" id="paso2">
        <div class="pub-panel">
            <h3>2 · Armá el contenido</h3>

            <div class="pub-opts" style="margin-bottom:12px;">
                <label class="pub-opt"><input type="radio" name="pubTipoContenido" value="imagen" checked onchange="cambiarTipoContenido()"><span><i class="fas fa-image"></i> Imagen</span></label>
                <label class="pub-opt"><input type="radio" name="pubTipoContenido" value="video" onchange="cambiarTipoContenido()"><span><i class="fas fa-video"></i> Video IA (persona vendiendo)</span></label>
            </div>

            <div class="pub-cols2">
                <div>
                    {{-- MODO IMAGEN --}}
                    <div id="modoImagen">
                        <label style="margin-top:0;">Escena base <i class="fas fa-magic" style="color:#0EA5E9;"></i></label>
                        <div class="pub-opts">
                            <label class="pub-opt"><input type="radio" name="pubEscena" value="dormitorio" checked onchange="armarPromptEscena()"><span>Dormitorio</span></label>
                            <label class="pub-opt"><input type="radio" name="pubEscena" value="noche" onchange="armarPromptEscena()"><span>Noche</span></label>
                            <label class="pub-opt"><input type="radio" name="pubEscena" value="minimal" onchange="armarPromptEscena()"><span>Minimalista</span></label>
                            <label class="pub-opt"><input type="radio" name="pubEscena" value="familia" onchange="armarPromptEscena()"><span>Familiar</span></label>
                        </div>

                        <label>Prompt guardado</label>
                        <select id="pubPromptGuardado" onchange="usarPromptGuardado(this, 'pubPromptEscena')"></select>

                        <label>Prompt de la escena (editalo a gusto)</label>
                        <textarea id="pubPromptEscena"></textarea>
                        <div class="pub-aviso">El sistema siempre agrega solo: mantener el producto fiel a la foto, el encuadre del formato y la prohibición de textos/logos. El precio y el logo los pone el editor, exactos.</div>

                        <div class="pub-btns" style="justify-content:flex-start;">
                            <button class="pub-btn ia" id="btnEscena" onclick="generarEscenaIA(this)" @if(!$capacidades['escenas']) disabled @endif>
                                <i class="fas fa-magic"></i> Generar escena
                            </button>
                            <button class="pub-btn sec" id="btnQuitarEscena" onclick="quitarEscena()" style="display:none;">Quitar escena</button>
                        </div>
                        @if(!$capacidades['escenas'])
                            <div class="pub-aviso warn">Configurá GEMINI_API_KEY en el .env para generar escenas con IA.</div>
                        @endif
                    </div>

                    {{-- MODO VIDEO --}}
                    <div id="modoVideo" style="display:none;">
                        <label style="margin-top:0;">Guión del video (editalo a gusto) <i class="fas fa-video" style="color:#0EA5E9;"></i></label>
                        <select id="pubPromptGuardadoVideo" onchange="usarPromptGuardado(this, 'pubPromptVideo')" style="margin-bottom:6px;"></select>
                        <textarea id="pubPromptVideo" style="min-height:180px;"></textarea>
                        <div class="pub-aviso">Video corto (≈8 seg) con una persona presentando el producto a cámara, con voz, estilo selfie/UGC. La foto real del producto se usa como referencia. Tarda 1 a 3 minutos y tiene costo por video en Google AI.</div>

                        <div class="pub-btns" style="justify-content:flex-start;">
                            <button class="pub-btn ia" id="btnVideo" onclick="generarVideoIA(this)" @if(!$capacidades['video']) disabled @endif>
                                <i class="fas fa-video"></i> Generar video
                            </button>
                            <button class="pub-btn sec" id="btnQuitarVideo" onclick="quitarVideo()" style="display:none;">Quitar video</button>
                        </div>
                        @if(!$capacidades['video'])
                            <div class="pub-aviso warn">Configurá GEMINI_API_KEY en el .env para generar videos con IA.</div>
                        @endif
                    </div>
                </div>

                <div class="pub-canvas-box">
                    <canvas id="pubCanvas" width="1200" height="1200"></canvas>
                    <video id="pubVideoPreview" controls style="display:none;"></video>
                    <div class="pub-btns">
                        <button class="pub-btn sec" id="btnDescargar" onclick="descargarContenido()"><i class="fas fa-download"></i> Descargar</button>
                    </div>
                </div>
            </div>
            <div class="pub-nav">
                <button class="pub-btn sec" onclick="irPaso(1)"><i class="fas fa-arrow-left"></i> Atrás</button>
                <button class="pub-btn" onclick="irPaso(3)">Siguiente: Textos <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
    </div>

    {{-- PASO 3 · Textos --}}
    <div class="pub-paso" id="paso3">
        <div class="pub-panel">
            <h3>3 · Escribí los textos</h3>

            <div class="pub-cols2">
                <div>
                    <input type="text" id="pubInstCopy" placeholder="Instrucción para la IA (ej: enfocar en el Día de la Madre)">
                    <div class="pub-btns" style="justify-content:flex-start; margin-top:8px;">
                        <button class="pub-btn ia" id="btnCopys" onclick="generarCopysIA(this)" @if(!$capacidades['copys']) disabled title="Configurá OPENAI_API_KEY" @endif>
                            <i class="fas fa-magic"></i> Generar textos con IA
                        </button>
                    </div>
                    @if(!$capacidades['copys'])
                        <div class="pub-aviso warn">Configurá OPENAI_API_KEY en el .env para generar textos con IA.</div>
                    @else
                        <div class="pub-aviso">La IA usa tu voz de marca entrenada, la información de contexto de la biblioteca y aprende de las publicaciones guardadas.</div>
                    @endif
                </div>
                <div>
                    <label style="margin-top:0;">Título MercadoLibre <span id="mlTituloLen" style="color:#6E7A96;font-weight:300;"></span></label>
                    <div class="pub-texto" id="txtTituloML" style="max-height:60px;"></div>
                    <button class="pub-btn sec pub-copy" onclick="copiarTexto('txtTituloML', this)">Copiar título</button>

                    <label>Descripción MercadoLibre</label>
                    <div class="pub-texto" id="txtDescML"></div>
                    <button class="pub-btn sec pub-copy" onclick="copiarTexto('txtDescML', this)">Copiar descripción</button>

                    <label>Caption Instagram / Facebook</label>
                    <div class="pub-texto" id="txtCaption"></div>
                    <button class="pub-btn sec pub-copy" onclick="copiarTexto('txtCaption', this)">Copiar caption</button>

                    <label>Mensaje WhatsApp</label>
                    <div class="pub-texto" id="txtWa" style="max-height:110px;"></div>
                    <button class="pub-btn sec pub-copy" onclick="copiarTexto('txtWa', this)">Copiar mensaje</button>
                </div>
            </div>

            <div class="pub-nav">
                <button class="pub-btn sec" onclick="irPaso(2)"><i class="fas fa-arrow-left"></i> Atrás</button>
                <button class="pub-btn" onclick="irPaso(4)">Siguiente: Publicar <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
    </div>

    {{-- PASO 4 · Publicar --}}
    <div class="pub-paso" id="paso4">
        <div class="pub-panel">
            <h3>4 · Guardá y subí a tus redes</h3>
            <div class="pub-final-grid">
                <div>
                    <img id="pubMini" alt="Vista previa">
                    <video id="pubMiniVideo" controls style="display:none;"></video>
                    <div class="pub-aviso" style="text-align:center;">Así queda tu publicación</div>
                </div>
                <div>
                    <label style="margin-top:0;">1 · Guardala en la biblioteca (alimenta a la IA)</label>
                    <div class="pub-btns" style="justify-content:flex-start;">
                        <button class="pub-btn sec" id="btnGuardar" onclick="guardarPublicacion(this)"><i class="fas fa-save"></i> Guardar en biblioteca</button>
                        <button class="pub-btn sec" onclick="descargarContenido()"><i class="fas fa-download"></i> Descargar</button>
                    </div>

                    <label>2 · Publicá directo</label>
                    <div class="pub-btns" style="justify-content:flex-start;">
                        <button class="pub-btn" id="btnPubFb" onclick="publicarMeta('facebook', this)" @if(!$capacidades['facebook']) disabled title="Configurá FB_PAGE_ID y FB_PAGE_TOKEN" @endif><i class="fab fa-facebook"></i> Facebook</button>
                        <button class="pub-btn" id="btnPubIg" onclick="publicarMeta('instagram', this)" @if(!$capacidades['instagram']) disabled title="Configurá IG_ACCOUNT_ID" @endif><i class="fab fa-instagram"></i> Instagram</button>
                    </div>
                    <div class="pub-aviso" id="avisoPublicar">Publica la imagen con el caption del paso 3. Instagram requiere el sitio accesible desde internet.</div>

                    <label>3 · ¿Publicaste a mano? Marcalo</label>
                    <div class="pub-btns" style="justify-content:flex-start;">
                        <button class="pub-btn sec pub-copy" onclick="marcarPublicado('meli', this)">MercadoLibre</button>
                        <button class="pub-btn sec pub-copy" onclick="marcarPublicado('whatsapp', this)">WhatsApp</button>
                        <button class="pub-btn sec pub-copy" onclick="marcarPublicado('google', this)">Google</button>
                    </div>

                    <label>Extra · Catálogo completo</label>
                    <div class="pub-btns" style="justify-content:flex-start;">
                        <a class="pub-btn sec" href="{{ route('publicaciones.catalogo') }}" target="_blank"><i class="fas fa-file-pdf"></i> Catálogo PDF con precios</a>
                        <a class="pub-btn sec" href="{{ route('publicaciones.catalogo', ['precios' => 0]) }}" target="_blank"><i class="fas fa-file-pdf"></i> Sin precios</a>
                    </div>
                </div>
            </div>
            <div class="pub-nav">
                <button class="pub-btn sec" onclick="irPaso(3)"><i class="fas fa-arrow-left"></i> Atrás</button>
                <button class="pub-btn" onclick="irPaso(1)"><i class="fas fa-plus"></i> Nueva publicación</button>
            </div>
        </div>
    </div>

    {{-- Biblioteca de publicaciones --}}
    <div class="pub-panel pub-biblio">
        <h3><i class="fas fa-images" style="color:#2563EB;"></i> Biblioteca de publicaciones</h3>
        <div class="pub-aviso">Las publicaciones guardadas alimentan a la IA como referencia de tono para las próximas.</div>
        <div class="pub-biblio-grid" id="pubBiblioteca"></div>
    </div>

    {{-- Biblioteca de recursos de marca --}}
    <div class="pub-panel pub-biblio">
        <h3><i class="fas fa-box-open" style="color:#2563EB;"></i> Recursos de marca</h3>
        <div class="pub-aviso">Imágenes y logos para tus piezas, prompts guardados para las escenas, e información de contexto (direcciones, promos, datos del negocio) que la IA usa al escribir.</div>

        <div class="pub-cols2" style="margin-top:10px;">
            <div>
                <label style="margin-top:0;">Agregar recurso</label>
                <select id="recTipo" onchange="cambiarTipoRecurso()">
                    <option value="contexto">Información de contexto (la usa la IA)</option>
                    <option value="prompt">Prompt guardado (para escenas/videos)</option>
                    <option value="imagen">Imagen</option>
                    <option value="logo">Logo</option>
                </select>
                <input type="text" id="recTitulo" placeholder="Título (ej: Local y horarios / Prompt verano)" style="margin-top:8px;">
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

{{-- Modal Entrenar IA --}}
<div class="modal fade" id="modalEntrenar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #E7EAF2;">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-graduation-cap" style="color:#2563EB;"></i> Entrenar la IA con tu estilo</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <label>Cómo escribe tu marca (voz para los textos)</label>
                <textarea id="entVoz" placeholder="Ej: Tono cercano y sereno, trato de vos, sin gritos de oferta. Siempre mencionar que somos fabricantes...">{{ $ajustes->voz_marca ?? '' }}</textarea>
                <div class="pub-aviso">Se usa en cada generación de textos. Dejalo vacío para usar la voz Sommy por defecto.</div>

                <label>Cómo se ven tus imágenes (estilo visual de las escenas)</label>
                <textarea id="entEstilo" placeholder="Ej: Luz cálida de mañana, tonos celestes y blancos, dormitorios reales argentinos, nada de lujo exagerado...">{{ $ajustes->estilo_imagen ?? '' }}</textarea>
                <div class="pub-aviso">Se suma al prompt de cada escena que generes. También podés guardar prompts completos en Recursos de marca.</div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E7EAF2;">
                <button type="button" class="pub-btn sec" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="pub-btn" onclick="guardarAjustes(this)"><i class="fas fa-save"></i> Guardar entrenamiento</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const PRODUCTOS = @json($productos);
const REGISTROS = @json($registros);
const BIBLIOTECA = @json($biblioteca);
const RECURSOS = @json($recursos);
const ESCENAS_TXT = @json($escenasTexto);
let ESTILO_IMG = @json($ajustes->estilo_imagen ?? '');
const LOGO_URL = '{{ asset('imagenes/marca/sommy-logo-magia.png') }}';
const BASE_URL = '{{ url('/') }}';
const CSRF = '{{ csrf_token() }}';

const FORMATOS = { ml: [1200, 1200], post: [1080, 1080], story: [1080, 1920] };
const CANAL_LBL = { meli: 'MercadoLibre', instagram: 'Instagram', facebook: 'Facebook', whatsapp: 'WhatsApp', google: 'Google' };
const REC_LBL = { imagen: 'Imagen', logo: 'Logo', prompt: 'Prompt', contexto: 'Contexto' };
const REC_ICO = { imagen: 'fa-image', logo: 'fa-star', prompt: 'fa-terminal', contexto: 'fa-info-circle' };

const canvas = document.getElementById('pubCanvas');
const ctx = canvas.getContext('2d');
const sel = document.getElementById('pubProducto');
let imgProducto = null, imgLogo = null;
let escenaIA = null;
let videoIA = null;   // { path, url, prompt }
let textosIA = null;
let pubGuardadaId = null;
let pasoActual = 1;

PRODUCTOS.forEach((p, i) => {
    const o = document.createElement('option');
    o.value = i;
    o.textContent = p.nombre;
    sel.appendChild(o);
});

const money = v => '$' + Number(v).toLocaleString('es-AR', { maximumFractionDigits: 0 });
const prod = () => PRODUCTOS[parseInt(sel.value, 10)] || PRODUCTOS[0];
const opcion = name => document.querySelector('input[name=' + name + ']:checked').value;
const esVideo = () => opcion('pubTipoContenido') === 'video';

/* ── Stepper ── */
function irPaso(n) {
    pasoActual = n;
    document.querySelectorAll('.pub-paso').forEach(p => p.classList.remove('activo'));
    document.getElementById('paso' + n).classList.add('activo');
    document.querySelectorAll('.pub-step').forEach(s => {
        const num = parseInt(s.dataset.paso, 10);
        s.classList.toggle('activo', num === n);
        s.classList.toggle('hecho', num < n);
    });
    if (n === 2) { dibujar(); }
    if (n === 3) generarTextos();
    if (n === 4) {
        const conVideo = videoIA && esVideo();
        document.getElementById('pubMini').style.display = conVideo ? 'none' : '';
        document.getElementById('pubMiniVideo').style.display = conVideo ? '' : 'none';
        document.getElementById('avisoPublicar').textContent = conVideo
            ? 'A Facebook va el video con el caption del paso 3. Para Instagram descargalo y subilo como Reel.'
            : 'Publica la imagen con el caption del paso 3. Instagram requiere el sitio accesible desde internet.';
        if (conVideo) {
            document.getElementById('pubMiniVideo').src = videoIA.url;
        } else {
            dibujar();
            document.getElementById('pubMini').src = canvas.toDataURL('image/png');
        }
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ── Prompts editables ── */
function armarPromptEscena() {
    let cuerpo = (ESCENAS_TXT[opcion('pubEscena')] || '') + '.';
    cuerpo += ESTILO_IMG && ESTILO_IMG.trim()
        ? ' Estilo de la marca: ' + ESTILO_IMG.trim()
        : ' Estilo: fotografía comercial realista de alta calidad, colores serenos (azules, celestes, blancos), sin personas.';
    document.getElementById('pubPromptEscena').value = cuerpo;
}

function armarPromptVideo() {
    const p = prod();
    const conPrecio = opcion('pubPrecio') === 'si';
    const specs = [p.plazas, p.firmeza ? 'firmeza ' + p.firmeza.toLowerCase() : null, p.pillow ? 'pillow top' : null, p.noches ? p.noches + ' noches de prueba' : null].filter(Boolean).join(', ');
    let g = 'Video selfie vertical estilo UGC: una persona argentina de unos 35 años, real y cercana, ';
    g += 'se graba a sí misma en primera persona con el celular en su dormitorio luminoso, mostrando el colchón de la imagen (mantener el colchón fiel a la foto). ';
    g += 'Habla a cámara en español argentino, con entusiasmo genuino y sin sobreactuar: ';
    g += '"Chicos, tengo que mostrarles el ' + p.nombre + '. ' + (specs ? specs.charAt(0).toUpperCase() + specs.slice(1) + '. ' : '');
    g += 'Es directo de fábrica, sin intermediarios.';
    if (conPrecio) g += ' Cuesta ' + money(p.precioFinal) + (p.descuento > 0 ? ' con ' + Math.round(p.descuento) + '% de descuento' : '') + '.';
    g += ' Se los recomiendo de verdad, duermo increíble." ';
    g += 'Al final palmea el colchón sonriendo. Estética casera de video para redes: cámara en mano, luz natural, un solo plano.';
    document.getElementById('pubPromptVideo').value = g;
}

function cargarPromptsGuardados() {
    const prompts = RECURSOS.filter(r => r.tipo === 'prompt');
    ['pubPromptGuardado', 'pubPromptGuardadoVideo'].forEach(id => {
        const s = document.getElementById(id);
        s.innerHTML = '<option value="">— Usar prompt guardado —</option>';
        prompts.forEach(r => { s.innerHTML += `<option value="${r.id}">${r.titulo}</option>`; });
        s.style.display = prompts.length ? '' : 'none';
    });
}

function usarPromptGuardado(selEl, destinoId) {
    const r = RECURSOS.find(x => x.id === parseInt(selEl.value, 10));
    if (r) document.getElementById(destinoId).value = r.contenido || '';
    selEl.value = '';
}

function cambiarTipoContenido() {
    const video = esVideo();
    document.getElementById('modoImagen').style.display = video ? 'none' : '';
    document.getElementById('modoVideo').style.display = video ? '' : 'none';
    canvas.style.display = (video && videoIA) ? 'none' : '';
    const vp = document.getElementById('pubVideoPreview');
    vp.style.display = (video && videoIA) ? '' : 'none';
    if (video && !document.getElementById('pubPromptVideo').value) armarPromptVideo();
    if (video && videoIA) vp.src = videoIA.url;
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

function cargarImagenes(cb) {
    const p = prod();
    let pend = 2;
    const done = () => { if (--pend === 0) cb(); };
    imgProducto = new Image();
    imgProducto.crossOrigin = 'anonymous';
    imgProducto.onload = done; imgProducto.onerror = done;
    imgProducto.src = p.imagen;
    if (imgLogo) { done(); return; }
    imgLogo = new Image();
    imgLogo.onload = done; imgLogo.onerror = done;
    imgLogo.src = LOGO_URL;
}

function dibujar() {
    const p = prod();
    const [W, H] = FORMATOS[opcion('pubFormato')];
    const estilo = opcion('pubEstilo');
    const conPrecio = opcion('pubPrecio') === 'si';
    const conEscena = escenaIA && escenaIA.img && escenaIA.img.naturalWidth;
    canvas.width = W; canvas.height = H;

    if (conEscena) {
        const im = escenaIA.img;
        const r = Math.max(W / im.naturalWidth, H / im.naturalHeight);
        const iw = im.naturalWidth * r, ih = im.naturalHeight * r;
        ctx.drawImage(im, (W - iw) / 2, (H - ih) / 2, iw, ih);
        const g = ctx.createLinearGradient(0, H * .55, 0, H);
        g.addColorStop(0, 'rgba(14,23,48,0)');
        g.addColorStop(1, 'rgba(14,23,48,.82)');
        ctx.fillStyle = g;
        ctx.fillRect(0, H * .55, W, H * .45);
    } else if (estilo === 'noche') {
        const g = ctx.createLinearGradient(0, 0, W, H);
        g.addColorStop(0, '#0E1730'); g.addColorStop(.55, '#1B2B5A'); g.addColorStop(1, '#24356B');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, W, H);
    } else {
        const g = ctx.createLinearGradient(0, 0, 0, H);
        g.addColorStop(0, '#F8FAFC'); g.addColorStop(1, '#E0F2FE');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, W, H);
    }

    const oscuro = conEscena || estilo === 'noche';
    const navy = oscuro ? '#FFFFFF' : '#1B2B5A';
    const sub  = oscuro ? '#C7D0E8' : '#47536F';

    if (!conEscena) {
        ctx.fillStyle = estilo === 'noche' ? '#0EA5E9' : '#7FB8E6';
        dibujarDestello(W * .88, H * .10, W * .012);

        const areaImg = { x: W * .10, y: H * (opcion('pubFormato') === 'story' ? .16 : .17), w: W * .80, h: H * (opcion('pubFormato') === 'story' ? .38 : .46) };
        if (estilo === 'noche') {
            rRect(areaImg.x - W * .02, areaImg.y - W * .02, areaImg.w + W * .04, areaImg.h + W * .04, W * .03);
            ctx.fillStyle = '#FFFFFF'; ctx.fill();
        }
        if (imgProducto && imgProducto.naturalWidth) {
            const r = Math.min(areaImg.w / imgProducto.naturalWidth, areaImg.h / imgProducto.naturalHeight);
            const iw = imgProducto.naturalWidth * r, ih = imgProducto.naturalHeight * r;
            ctx.drawImage(imgProducto, areaImg.x + (areaImg.w - iw) / 2, areaImg.y + (areaImg.h - ih) / 2, iw, ih);
        }
    }

    if (imgLogo && imgLogo.naturalWidth) {
        const lw = W * .22, lh = lw * imgLogo.naturalHeight / imgLogo.naturalWidth;
        const lx = W * .06, ly = H * .045;
        if (oscuro) {
            rRect(lx - W*.02, ly - lh*.25, lw + W*.04, lh * 1.5, lh);
            ctx.fillStyle = '#FFFFFF'; ctx.fill();
        }
        ctx.drawImage(imgLogo, lx, ly, lw, lh);
    }

    const baseY = conEscena
        ? H - (opcion('pubFormato') === 'story' ? H * .30 : H * .32)
        : (H * (opcion('pubFormato') === 'story' ? .16 : .17) + H * (opcion('pubFormato') === 'story' ? .38 : .46) + H * .07);

    ctx.textAlign = 'center';
    ctx.fillStyle = navy;
    ctx.font = '600 ' + (W * .048) + 'px Poppins, sans-serif';
    envolverTexto(p.nombre, W / 2, baseY, W * .84, W * .06);

    const specs = [p.plazas, p.firmeza ? 'Firmeza ' + p.firmeza.toLowerCase() : null, p.altura ? p.altura + ' cm' : null, p.pillow ? 'Pillow top' : null].filter(Boolean).join('  ·  ');
    if (specs) {
        ctx.fillStyle = sub;
        ctx.font = '400 ' + (W * .028) + 'px Poppins, sans-serif';
        ctx.fillText(specs, W / 2, baseY + W * .095);
    }

    if (conPrecio) {
        const py = baseY + W * (conEscena ? .17 : (opcion('pubFormato') === 'story' ? .19 : .17));
        if (p.descuento > 0) {
            ctx.fillStyle = sub;
            ctx.font = '400 ' + (W * .030) + 'px Poppins, sans-serif';
            const vOld = money(p.precio);
            ctx.fillText(vOld, W / 2, py - W * .055);
            const tw = ctx.measureText(vOld).width;
            ctx.strokeStyle = sub; ctx.lineWidth = W * .003;
            ctx.beginPath(); ctx.moveTo(W/2 - tw/2, py - W * .065); ctx.lineTo(W/2 + tw/2, py - W * .065); ctx.stroke();
        }
        ctx.fillStyle = navy;
        ctx.font = '700 ' + (W * .075) + 'px Poppins, sans-serif';
        ctx.fillText(money(p.precioFinal), W / 2, py);
        if (p.descuento > 0) {
            ctx.fillStyle = '#0EA5E9';
            ctx.font = '600 ' + (W * .03) + 'px Poppins, sans-serif';
            ctx.fillText('-' + Math.round(p.descuento) + '% OFF', W / 2, py + W * .05);
        }
    }

    ctx.fillStyle = oscuro ? '#7FD4F5' : '#2563EB';
    ctx.font = '500 ' + (W * .026) + 'px Poppins, sans-serif';
    ctx.fillText('DIRECTO DE FÁBRICA  ·  ENVÍO A DOMICILIO', W / 2, H - H * .045);

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

function dibujarDestello(x, y, r) {
    ctx.save(); ctx.translate(x, y); ctx.beginPath();
    for (let i = 0; i < 8; i++) {
        const a = i * Math.PI / 4;
        const rr = i % 2 === 0 ? r : r * .38;
        ctx.lineTo(Math.cos(a) * rr, Math.sin(a) * rr);
    }
    ctx.closePath(); ctx.fill(); ctx.restore();
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

/* ── IA: escena ── */
function generarEscenaIA(btn) {
    const p = prod();
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Generando (30-60 seg)...';
    postJson('{{ route('publicaciones.generar-imagen') }}', {
        producto_id: p.id,
        escena: opcion('pubEscena'),
        formato: opcion('pubFormato'),
        prompt_libre: document.getElementById('pubPromptEscena').value
    }).then(data => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            escenaIA = { path: data.path, url: data.url, prompt: data.prompt, img: img };
            pubGuardadaId = null;
            document.getElementById('btnQuitarEscena').style.display = '';
            dibujar();
        };
        img.src = data.url;
    }).catch(e => alert('No se pudo generar la escena: ' + e.message))
      .finally(() => { btn.disabled = false; btn.innerHTML = original; });
}

function quitarEscena() {
    escenaIA = null;
    pubGuardadaId = null;
    document.getElementById('btnQuitarEscena').style.display = 'none';
    dibujar();
}

/* ── IA: video ── */
function generarVideoIA(btn) {
    const p = prod();
    if (!confirm('Generar el video tarda 1 a 3 minutos y tiene costo en Google AI. ¿Continuar?')) return;
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Generando video (1-3 min)...';
    postJson('{{ route('publicaciones.generar-video') }}', {
        producto_id: p.id,
        formato: opcion('pubFormato'),
        prompt_libre: document.getElementById('pubPromptVideo').value
    }).then(data => {
        videoIA = { path: data.path, url: data.url, prompt: data.prompt };
        pubGuardadaId = null;
        document.getElementById('btnQuitarVideo').style.display = '';
        cambiarTipoContenido();
    }).catch(e => alert('No se pudo generar el video: ' + e.message))
      .finally(() => { btn.disabled = false; btn.innerHTML = original; });
}

function quitarVideo() {
    videoIA = null;
    pubGuardadaId = null;
    document.getElementById('btnQuitarVideo').style.display = 'none';
    cambiarTipoContenido();
}

/* ── IA: textos ── */
function generarCopysIA(btn) {
    const p = prod();
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Escribiendo...';
    postJson('{{ route('publicaciones.generar-copy') }}', {
        producto_id: p.id,
        con_precio: opcion('pubPrecio') === 'si',
        instrucciones: document.getElementById('pubInstCopy').value
    }).then(data => {
        textosIA = data.textos;
        pubGuardadaId = null;
        pintarTextos();
    }).catch(e => alert('No se pudieron generar los textos: ' + e.message))
      .finally(() => { btn.disabled = false; btn.innerHTML = original; });
}

/* ── Textos ── */
function generarTextos() {
    if (textosIA) { pintarTextos(); return; }
    const p = prod();
    const specsTit = [p.plazas, p.tipo, p.altura ? p.altura + 'cm' : null, p.pillow ? 'Pillow Top' : null].filter(Boolean);

    let titulo = p.nombre;
    specsTit.forEach(s => { if ((titulo + ' ' + s).length <= 55 && !titulo.toLowerCase().includes(String(s).toLowerCase())) titulo += ' ' + s; });
    if ((titulo + ' Fábrica').length <= 60) titulo += ' Fábrica';

    let desc = p.nombre.toUpperCase() + '\n\n';
    if (p.descripcion) desc += p.descripcion + '\n\n';
    desc += 'CARACTERÍSTICAS\n';
    if (p.tipo) desc += '• Tipo: ' + p.tipo + '\n';
    if (p.plazas) desc += '• Medida: ' + p.plazas + '\n';
    if (p.firmeza) desc += '• Firmeza: ' + p.firmeza + '\n';
    if (p.altura) desc += '• Altura: ' + p.altura + ' cm\n';
    if (p.pillow) desc += '• Pillow top incorporado\n';
    if (p.tela) desc += '• Tela: ' + p.tela + '\n';
    if (p.garantia) desc += '• Garantía: ' + p.garantia + ' años\n';
    if (p.noches) desc += '• ' + p.noches + ' noches de prueba\n';
    desc += '\nSOMOS FABRICANTES: comprás directo de fábrica, sin intermediarios.\nEnvío a domicilio coordinado. Consultanos por medios de pago y promociones.';

    let cap = '😴 ' + p.nombre + '\n\n';
    cap += 'Dormí liviano, despertá mejor. ';
    if (p.tipo) cap += p.tipo + (p.pillow ? ' con pillow top' : '') + ', ';
    if (p.firmeza) cap += 'firmeza ' + p.firmeza.toLowerCase() + ', ';
    cap += 'hecho en nuestra fábrica y directo a tu casa 🏭➡️🏠\n\n';
    if (opcion('pubPrecio') === 'si') cap += '💙 ' + money(p.precioFinal) + (p.descuento > 0 ? ' (' + Math.round(p.descuento) + '% OFF)' : '') + '\n';
    cap += '🚚 Envío a domicilio\n📲 Pedilo por WhatsApp o en nuestra tienda online\n\n';
    cap += '#colchones #descanso #sommy #dormibien #colchon' + (p.plazas ? ' #' + p.plazas.replace(/[^a-z0-9]/gi, '').toLowerCase() : '');

    let wa = '😴 *' + p.nombre + '*\n';
    if (opcion('pubPrecio') === 'si') wa += '💙 ' + money(p.precioFinal) + (p.descuento > 0 ? ' (' + Math.round(p.descuento) + '% OFF)' : '') + '\n';
    wa += '🏭 Directo de fábrica, envío a domicilio.\n¿Te paso más info?';

    pintarTextos({ titulo_ml: titulo, desc_ml: desc, caption: cap, texto_wa: wa });
}

function pintarTextos(base) {
    const t = textosIA || base;
    if (!t) return;
    document.getElementById('txtTituloML').textContent = t.titulo_ml || '';
    document.getElementById('mlTituloLen').textContent = '(' + (t.titulo_ml || '').length + '/60)';
    document.getElementById('txtDescML').textContent = t.desc_ml || '';
    document.getElementById('txtCaption').textContent = t.caption || '';
    document.getElementById('txtWa').textContent = t.texto_wa || '';
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
        armarPromptEscena();
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
        cargarPromptsGuardados();
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
        cargarPromptsGuardados();
    });
}

function renderRecursos() {
    const cont = document.getElementById('pubRecursos');
    cont.innerHTML = RECURSOS.length ? '' : '<div class="pub-aviso">Todavía no hay recursos guardados.</div>';
    RECURSOS.forEach(r => {
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

/* ── Guardar y publicar ── */
function textosActuales() {
    return {
        titulo_ml: document.getElementById('txtTituloML').textContent,
        desc_ml: document.getElementById('txtDescML').textContent,
        caption: document.getElementById('txtCaption').textContent,
        texto_wa: document.getElementById('txtWa').textContent
    };
}

function guardarPublicacion(btn, silencioso) {
    const p = prod();
    const t = textosActuales();
    const conVideo = videoIA && esVideo();
    const original = btn ? btn.innerHTML : null;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Guardando...'; }
    return postJson('{{ route('publicaciones.guardar') }}', {
        producto_id: p.id,
        formato: opcion('pubFormato'),
        estilo: conVideo ? 'video-ugc' : (escenaIA ? 'escena-' + opcion('pubEscena') : opcion('pubEstilo')),
        titulo_ml: t.titulo_ml, desc_ml: t.desc_ml, caption: t.caption, texto_wa: t.texto_wa,
        imagen_escena: escenaIA ? escenaIA.path : null,
        prompt_escena: conVideo ? videoIA.prompt : (escenaIA ? escenaIA.prompt : null),
        imagen_base64: canvas.toDataURL('image/png'),
        video_final: conVideo ? videoIA.path : null
    }).then(data => {
        pubGuardadaId = data.id;
        BIBLIOTECA.unshift({ id: data.id, producto_id: p.id, imagen_final: null, imagen_url: data.imagen_url, estado: 'borrador', created_at: new Date().toISOString() });
        renderBiblioteca();
        if (!silencioso && btn) { btn.innerHTML = '✓ Guardada'; setTimeout(() => btn.innerHTML = original, 1500); }
        return data.id;
    }).finally(() => { if (btn) { btn.disabled = false; if (btn.innerHTML.includes('Guardando')) btn.innerHTML = original; } });
}

function publicarMeta(canal, btn) {
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Publicando...';
    const asegurarGuardada = pubGuardadaId ? Promise.resolve(pubGuardadaId) : guardarPublicacion(null, true);
    asegurarGuardada.then(id =>
        postJson('{{ route('publicaciones.publicar') }}', { publicacion_id: id, canales: [canal] })
    ).then(data => {
        if (data.errores && data.errores[canal]) throw new Error(data.errores[canal]);
        const p = prod();
        if (!REGISTROS[p.id]) REGISTROS[p.id] = [];
        REGISTROS[p.id].unshift({ canal: canal, created_at: new Date().toISOString().slice(0, 10) });
        renderHistorial();
        const item = BIBLIOTECA.find(b => b.id === pubGuardadaId);
        if (item) item.estado = 'publicada';
        renderBiblioteca();
        btn.innerHTML = '✓ Publicado';
        setTimeout(() => btn.innerHTML = original, 2000);
    }).catch(e => {
        alert('No se pudo publicar en ' + CANAL_LBL[canal] + ': ' + e.message);
        btn.innerHTML = original;
    }).finally(() => { btn.disabled = false; });
}

function copiarTexto(id, btn) {
    navigator.clipboard.writeText(document.getElementById(id).textContent).then(() => {
        const t = btn.textContent; btn.textContent = '¡Copiado!';
        setTimeout(() => btn.textContent = t, 1500);
    });
}

function descargarContenido() {
    const p = prod();
    const slug = 'sommy-' + p.nombre.toLowerCase().replace(/[^a-z0-9]+/g, '-');
    const a = document.createElement('a');
    if (videoIA && esVideo()) {
        a.download = slug + '-video.mp4';
        a.href = videoIA.url;
    } else {
        a.download = slug + '-' + opcion('pubFormato') + '.png';
        a.href = canvas.toDataURL('image/png');
    }
    a.click();
}

function marcarPublicado(canal, btn) {
    const p = prod();
    postJson('{{ route('publicaciones.registrar') }}', { producto_id: p.id, canal: canal, formato: opcion('pubFormato') })
    .then(() => {
        if (!REGISTROS[p.id]) REGISTROS[p.id] = [];
        REGISTROS[p.id].unshift({ canal: canal, created_at: new Date().toISOString().slice(0, 10) });
        renderHistorial();
        const t = btn.textContent; btn.textContent = '✓ Registrado';
        setTimeout(() => btn.textContent = t, 1500);
    });
}

function renderHistorial() {
    const p = prod();
    document.getElementById('pubLinkConocimiento').href = BASE_URL + '/articulo/' + p.id + '/conocimiento';
    const cont = document.getElementById('pubHistorial');
    const regs = REGISTROS[p.id] || [];
    cont.innerHTML = regs.length ? '<label style="width:100%;">Historial de este producto</label>' : '';
    regs.slice(0, 8).forEach(r => {
        cont.innerHTML += '<span class="badge" style="background:#E0F2FE;color:#1B2B5A;">' +
            (CANAL_LBL[r.canal] || r.canal) + ' · ' + String(r.created_at).slice(0, 10) + '</span>';
    });
}

function renderBiblioteca() {
    const cont = document.getElementById('pubBiblioteca');
    cont.innerHTML = '';
    BIBLIOTECA.slice(0, 24).forEach(b => {
        const url = b.imagen_url || (b.imagen_final ? BASE_URL + '/' + b.imagen_final : null);
        if (!url) return;
        const nombre = (PRODUCTOS.find(p => p.id === b.producto_id) || {}).nombre || 'Producto';
        const esVideoPub = !!b.video_final;
        const card = document.createElement('div');
        card.className = 'pub-biblio-card';
        card.innerHTML = '<img src="' + url + '" title="' + nombre + '" onclick="window.open(\'' + (esVideoPub ? BASE_URL + '/' + b.video_final : url) + '\')">' +
            '<div class="meta">' + (esVideoPub ? '<i class="fas fa-video" style="color:#2563EB;"></i> ' : '') + String(b.created_at).slice(0, 10) +
            ' <span class="estado ' + b.estado + '">' + b.estado + '</span></div>';
        cont.appendChild(card);
    });
}

/* ── Eventos ── */
sel.addEventListener('change', () => {
    escenaIA = null; textosIA = null; videoIA = null; pubGuardadaId = null;
    document.getElementById('btnQuitarEscena').style.display = 'none';
    document.getElementById('btnQuitarVideo').style.display = 'none';
    document.getElementById('pubPromptVideo').value = '';
    if (esVideo()) armarPromptVideo();
    cambiarTipoContenido();
    cargarImagenes(dibujar);
});
document.querySelectorAll('input[name=pubFormato], input[name=pubEstilo], input[name=pubPrecio]')
    .forEach(el => el.addEventListener('change', () => { pubGuardadaId = null; dibujar(); }));

// Esperar a que la fuente Poppins esté disponible para el canvas
document.fonts.ready.then(() => {
    cargarImagenes(dibujar);
    renderBiblioteca();
    renderRecursos();
    cargarPromptsGuardados();
    armarPromptEscena();
    generarTextos();
    cambiarTipoRecurso();
});
</script>
@endsection
