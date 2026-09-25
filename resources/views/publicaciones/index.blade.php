@extends('layouts.admin')

@section('title', 'Publicaciones')

@section('contenido')
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

    /* Variantes (5 opciones para elegir) */
    .pub-variantes { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; margin-top: 10px; }
    .pub-variante { position: relative; border-radius: 12px; overflow: hidden; border: 3px solid transparent; cursor: pointer; background: #F8FAFC; }
    .pub-variante img { width: 100%; aspect-ratio: 4/5; object-fit: cover; display: block; }
    .pub-variante.seleccionada { border-color: #2563EB; box-shadow: 0 6px 18px rgba(37,99,235,.25); }
    .pub-variante .check { position: absolute; top: 6px; right: 6px; width: 22px; height: 22px; border-radius: 999px; background: #2563EB; color: #fff; display: none; align-items: center; justify-content: center; font-size: 11px; }
    .pub-variante.seleccionada .check { display: flex; }
    .pub-variante.error { display: flex; align-items: center; justify-content: center; aspect-ratio: 4/5; font-size: 11px; color: #b45309; text-align: center; padding: 8px; cursor: default; }

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
    .pub-feed-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px; max-width: 480px; }
    .pub-feed-item { position: relative; aspect-ratio: 4/5; background: #F1F4F9; overflow: hidden; }
    .pub-feed-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pub-feed-item .badge-prog { position: absolute; bottom: 4px; left: 4px; right: 4px; background: rgba(27,43,90,.85); color: #fff; font-size: 8.5px; font-weight: 600; text-align: center; border-radius: 6px; padding: 2px 4px; }
    .pub-feed-empty { font-size: 12.5px; color: #6E7A96; padding: 20px 0; }

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
        <button class="pub-btn sec chico" onclick="$('#modalEntrenar').modal('show')"><i class="fas fa-graduation-cap"></i> Mi marca</button>
    </div>
    <div class="pub-sub">Elegí un producto o combo, generá el contenido con tu branding y publicalo o programalo — todo desde acá.</div>

    {{-- 1 · Producto/combo + generar --}}
    <div class="pub-panel">
        <h3>1 · Elegí qué vas a promocionar</h3>
        <div class="pub-cols2">
            <div>
                <label>Producto o combo</label>
                <select id="pubProducto"></select>
                <a id="pubLinkConocimiento" href="#" class="pub-aviso" style="display:inline-block;color:#2563EB;margin-top:6px;">
                    <i class="fas fa-brain"></i> Conocimiento del producto (contexto para la IA)
                </a>
                <div id="pubHistorial" class="pub-hist"></div>
            </div>
            <div>
                <label style="margin-top:0;">Formato</label>
                <div class="pub-opts">
                    <label class="pub-opt"><input type="radio" name="pubFormato" value="feed" checked><span>Feed 4:5</span></label>
                    <label class="pub-opt"><input type="radio" name="pubFormato" value="story"><span>Historia 9:16</span></label>
                    <label class="pub-opt"><input type="radio" name="pubFormato" value="ml"><span>MercadoLibre 1:1</span></label>
                </div>
                <label>Mostrar precio</label>
                <div class="pub-opts">
                    <label class="pub-opt"><input type="radio" name="pubPrecio" value="si" checked><span>Sí</span></label>
                    <label class="pub-opt"><input type="radio" name="pubPrecio" value="no"><span>No</span></label>
                </div>
                <div class="pub-btns" style="justify-content:flex-start;margin-top:16px;">
                    <button class="pub-btn ia" id="btnGenerar" onclick="generarContenido(this)" @if(!$capacidades['escenas']) disabled @endif>
                        <i class="fas fa-wand-magic-sparkles"></i> Generar contenido
                    </button>
                </div>
                @if(!$capacidades['escenas'])
                    <div class="pub-aviso warn">Configurá GEMINI_API_KEY en el .env para generar contenido con IA.</div>
                @else
                    <div class="pub-aviso">Genera 5 imágenes con tu estilo de marca fijo (así tu feed queda homogéneo) + el texto, todo junto.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- 2 · Elegir variante --}}
    <div class="pub-panel" id="panelVariantes" style="display:none;">
        <h3>2 · Elegí la imagen que más te gusta</h3>
        <div id="pubVariantesEstado" class="pub-aviso"></div>
        <div class="pub-variantes" id="pubVariantes"></div>
    </div>

    {{-- 3 · Revisar y subir --}}
    <div class="pub-panel" id="panelResultado" style="display:none;">
        <h3>3 · Revisá y subí</h3>
        <canvas id="pubCanvas" width="1080" height="1350"></canvas>
        <div class="pub-final-grid">
            <div>
                <img id="pubPreview" alt="Vista previa">
                <div class="pub-btns">
                    <button class="pub-btn sec chico" onclick="descargarContenido()"><i class="fas fa-download"></i> Descargar</button>
                    <button class="pub-btn sec chico" id="btnStoryExtra" onclick="generarFormatoExtra('story', this)"><i class="fas fa-plus"></i> Versión Historia 9:16</button>
                    <button class="pub-btn sec chico" id="btnFeedExtra" onclick="generarFormatoExtra('feed', this)" style="display:none;"><i class="fas fa-plus"></i> Versión Feed 4:5</button>
                </div>
            </div>
            <div>
                <label style="margin-top:0;">Caption (Instagram / Facebook)</label>
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

    {{-- Próximas publicaciones programadas --}}
    <div class="pub-panel pub-biblio" id="panelProximas" style="display:none;">
        <h3><i class="fas fa-calendar-days" style="color:#2563EB;"></i> Próximas publicaciones</h3>
        <div id="pubProximas"></div>
    </div>

    {{-- Simulador de feed --}}
    <div class="pub-panel pub-biblio">
        <h3><i class="fas fa-table-cells" style="color:#2563EB;"></i> Simulá tu feed</h3>
        <div class="pub-feed-tabs">
            <button class="pub-feed-tab activo" data-red="instagram" onclick="cambiarRedFeed('instagram', this)">Instagram</button>
            <button class="pub-feed-tab" data-red="facebook" onclick="cambiarRedFeed('facebook', this)">Facebook</button>
        </div>
        <div class="pub-feed-header">
            <img src="{{ asset('imagenes/marca/sommy-logo-header.png') }}" class="pub-feed-avatar" onerror="this.style.display='none'">
            <div><strong id="pubFeedHandle">&#64;sommy_colchoneria</strong><div class="pub-aviso" style="margin:0;">Así se va viendo tu perfil a medida que subís y programás</div></div>
        </div>
        <div class="pub-feed-grid" id="pubFeedGrid"></div>
        <div class="pub-feed-empty" id="pubFeedEmpty" style="display:none;">Todavía no generaste publicaciones. Las que guardes, programes o publiques van a aparecer acá, en el orden en que se van a ver en tu perfil.</div>
    </div>

    {{-- Biblioteca de recursos de marca --}}
    <div class="pub-panel pub-biblio">
        <h3><i class="fas fa-box-open" style="color:#2563EB;"></i> Recursos de marca</h3>
        <div class="pub-aviso">Información de contexto (direcciones, promos, datos del negocio) que la IA usa al escribir, y logos/imágenes de referencia.</div>

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
let ESTILO_IMG = @json($ajustes->estilo_imagen ?? '');
const LOGO_URL = '{{ asset('imagenes/marca/sommy-logo-magia.png') }}';
const BASE_URL = '{{ url('/') }}';
const CSRF = '{{ csrf_token() }}';

const FORMATOS = { feed: [1080, 1350], story: [1080, 1920], ml: [1200, 1200] };
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

/* ── 1 · Generar contenido (imagen x5 + copy, juntos) ── */
function generarContenido(btn) {
    const p = prod();
    const formato = opcion('pubFormato');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Generando (30-90 seg)...';

    variantes = []; varianteElegida = null; textosIA = null; pubGuardadaId = null;
    document.getElementById('panelResultado').style.display = 'none';
    document.getElementById('panelVariantes').style.display = '';
    document.getElementById('pubVariantesEstado').textContent = 'Generando 5 opciones con tu estilo de marca y escribiendo el texto...';
    document.getElementById('pubVariantes').innerHTML = '';
    window.scrollTo({ top: document.getElementById('panelVariantes').offsetTop - 20, behavior: 'smooth' });

    const pImagenes = postJson('{{ route('publicaciones.generar-variantes') }}', {
        producto_id: p.id, formato, es_combo: !!p.esCombo, cantidad: 5
    });
    const pTextos = postJson('{{ route('publicaciones.generar-copy') }}', {
        producto_id: p.id, con_precio: opcion('pubPrecio') === 'si', es_combo: !!p.esCombo
    }).catch(e => ({ error: e.message }));

    Promise.all([pImagenes, pTextos]).then(([dataImgs, dataTxt]) => {
        variantes = dataImgs.variantes || [];
        if (dataTxt && !dataTxt.error) { textosIA = dataTxt.textos; pintarTextos(); }
        renderVariantes();
    }).catch(e => {
        document.getElementById('pubVariantesEstado').textContent = '';
        alert('No se pudo generar el contenido: ' + e.message);
    }).finally(() => { btn.disabled = false; btn.innerHTML = original; });
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
        document.getElementById('btnStoryExtra').style.display = opcion('pubFormato') === 'story' ? 'none' : '';
        document.getElementById('btnFeedExtra').style.display = opcion('pubFormato') === 'story' ? '' : 'none';
        document.getElementById('panelResultado').style.display = '';
        cargarLogo(() => { dibujar(); document.getElementById('pubPreview').src = canvas.toDataURL('image/png'); });
        window.scrollTo({ top: document.getElementById('panelResultado').offsetTop - 20, behavior: 'smooth' });
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
    const p = prod();
    const [W, H] = FORMATOS[formato] || FORMATOS.feed;
    const conPrecio = opcion('pubPrecio') === 'si';
    canvas.width = W; canvas.height = H;

    const im = v.img;
    const r = Math.max(W / im.naturalWidth, H / im.naturalHeight);
    const iw = im.naturalWidth * r, ih = im.naturalHeight * r;
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

    const baseY = H - (formato === 'story' ? H * .30 : H * .32);

    ctx.textAlign = 'center';
    ctx.fillStyle = '#FFFFFF';
    ctx.font = '600 ' + (W * .048) + 'px Poppins, sans-serif';
    envolverTexto(p.nombre, W / 2, baseY, W * .84, W * .06);

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

    ctx.fillStyle = '#7FD4F5';
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

/* ── Guardar / publicar / programar ── */
function payloadBase() {
    const p = prod();
    return {
        producto_id: p.id, es_combo: !!p.esCombo,
        formato: opcion('pubFormato'), estilo: 'ia',
        titulo_ml: document.getElementById('txtTituloML').value,
        desc_ml: document.getElementById('txtDescML').value,
        caption: document.getElementById('txtCaption').value,
        texto_wa: document.getElementById('txtWa').value,
        imagen_escena: varianteElegida ? varianteElegida.path : null,
        prompt_escena: varianteElegida ? varianteElegida.prompt : null,
        imagen_base64: canvas.toDataURL('image/png')
    };
}

function guardarBorrador(btn) {
    const original = btn ? btn.innerHTML : null;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch pub-spin"></i> Guardando...'; }
    return postJson('{{ route('publicaciones.guardar') }}', payloadBase()).then(data => {
        pubGuardadaId = data.id;
        const p = prod();
        BIBLIOTECA.unshift({ id: data.id, producto_id: p.id, imagen_url: data.imagen_url, estado: 'borrador', programado_para: null, created_at: new Date().toISOString() });
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
        const p = prod();
        (data.publicados || []).forEach(canal => {
            if (!REGISTROS[p.id]) REGISTROS[p.id] = [];
            REGISTROS[p.id].unshift({ canal, created_at: new Date().toISOString().slice(0, 10) });
        });
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
        const p = prod();
        BIBLIOTECA.unshift({ id: data.id, producto_id: p.id, imagen_url: data.imagen_url, estado: 'programada', programado_para: fecha, created_at: new Date().toISOString() });
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
    const p = prod();
    const slug = 'sommy-' + p.nombre.toLowerCase().replace(/[^a-z0-9]+/g, '-');
    const a = document.createElement('a');
    a.download = slug + '-' + opcion('pubFormato') + '.png';
    a.href = canvas.toDataURL('image/png');
    a.click();
}

function renderHistorial() {
    const p = prod();
    document.getElementById('pubLinkConocimiento').href = BASE_URL + '/articulo/' + p.id + '/conocimiento';
    const cont = document.getElementById('pubHistorial');
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
    document.getElementById('panelProximas').style.display = prog.length ? '' : 'none';
    const cont = document.getElementById('pubProximas');
    cont.innerHTML = '';
    prog.forEach(b => {
        const nombre = (PRODUCTOS.find(p => p.id === b.producto_id) || {}).nombre || 'Producto';
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

function renderFeedSimulado() {
    const items = BIBLIOTECA.filter(b => b.estado === 'publicada' || b.estado === 'programada')
        .filter(b => b.imagen_url || b.imagen_final)
        .sort((a, b) => new Date(b.programado_para || b.created_at) - new Date(a.programado_para || a.created_at));
    const cont = document.getElementById('pubFeedGrid');
    cont.innerHTML = '';
    document.getElementById('pubFeedEmpty').style.display = items.length ? 'none' : '';
    items.slice(0, 24).forEach(b => {
        const url = b.imagen_url || (BASE_URL + '/' + b.imagen_final);
        const item = document.createElement('div');
        item.className = 'pub-feed-item';
        item.innerHTML = '<img src="' + url + '">' +
            (b.estado === 'programada' ? '<div class="badge-prog">Programada · ' + new Date(b.programado_para).toLocaleDateString('es-AR', { day: '2-digit', month: 'short' }) + '</div>' : '');
        cont.appendChild(item);
    });
}

/* ── Eventos ── */
sel.addEventListener('change', () => {
    variantes = []; varianteElegida = null; textosIA = null; pubGuardadaId = null;
    document.getElementById('panelVariantes').style.display = 'none';
    document.getElementById('panelResultado').style.display = 'none';
    renderHistorial();
});
document.querySelectorAll('input[name=pubPrecio]').forEach(el => el.addEventListener('change', dibujar));

document.fonts.ready.then(() => {
    cargarLogo(() => {});
    renderHistorial();
    renderRecursos();
    renderFeedSimulado();
    renderProximas();
    cambiarTipoRecurso();
});
</script>
@endsection
