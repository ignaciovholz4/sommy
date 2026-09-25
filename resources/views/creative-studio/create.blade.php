@extends('layouts.admin')

@section('title', 'Sommy Creative Studio')

@section('contenido')
<style>
    .cs-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1500px; margin: 0 auto; }
    .cs-title { font-size: 21px; font-weight: 600; margin-bottom: 2px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .cs-sub { font-size: 13px; color: #6E7A96; font-weight: 300; margin-bottom: 16px; }

    .cs-btn { border: none; border-radius: 999px; padding: 9px 20px; font-size: 13px; font-weight: 500; cursor: pointer; background: #1B2B5A; color: #fff; text-decoration: none; display: inline-block; }
    .cs-btn:hover { background: #2563EB; color: #fff; }
    .cs-btn.sec { background: #E0F2FE; color: #1B2B5A; }
    .cs-btn.ia { background: linear-gradient(90deg, #2563EB, #0EA5E9); width: 100%; padding: 12px; font-size: 14px; }
    .cs-btn:disabled { opacity: .5; cursor: not-allowed; }
    .cs-btn.chico { padding: 6px 14px; font-size: 12px; }

    .cs-layout { display: grid; grid-template-columns: 300px 1fr 320px; gap: 16px; align-items: start; }
    @media (max-width: 1200px) { .cs-layout { grid-template-columns: 1fr; } }

    .cs-panel { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.08); padding: 18px; }
    .cs-panel + .cs-panel { margin-top: 14px; }
    .cs-panel h4 { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #47536F; margin: 0 0 10px; }
    .cs-panel h4:not(:first-child) { margin-top: 16px; }
    .cs-panel select, .cs-panel input[type=text] { width: 100%; border: 1px solid #E7EAF2; border-radius: 10px; padding: 8px 10px; font-size: 13px; color: #1B2B5A; font-family: 'Poppins', sans-serif; }

    .cs-sidebar-scroll { max-height: calc(100vh - 160px); overflow-y: auto; padding-right: 4px; }

    .cs-opts { display: flex; gap: 6px; flex-wrap: wrap; }
    .cs-opt input { display: none; }
    .cs-opt span { display: inline-block; padding: 6px 12px; border-radius: 999px; border: 1.5px solid #E7EAF2; font-size: 11.5px; font-weight: 500; color: #47536F; cursor: pointer; }
    .cs-opt input:checked + span { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }

    .cs-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .cs-card { border: 1.5px solid #E7EAF2; border-radius: 10px; padding: 8px; font-size: 11px; font-weight: 500; color: #47536F; cursor: pointer; text-align: center; }
    .cs-card input { display: none; }
    .cs-card.activa { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }

    .cs-gallery { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
    .cs-gallery img { width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 2px solid #E7EAF2; }
    .cs-gallery img.principal { border-color: #2563EB; }

    .cs-center { min-height: 520px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    #csCanvas { display: none; }
    #csPreview { max-width: 100%; max-height: 640px; border-radius: 14px; border: 1px solid #E7EAF2; box-shadow: 0 10px 30px rgba(27,43,90,.10); }
    .cs-center-empty { color: #94A3B8; font-size: 13px; text-align: center; padding: 40px; }

    .cs-variantes { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px; }
    .cs-variante { position: relative; border-radius: 12px; overflow: hidden; border: 3px solid transparent; cursor: pointer; background: #F8FAFC; }
    .cs-variante img { width: 100%; aspect-ratio: 4/5; object-fit: cover; display: block; }
    .cs-variante.seleccionada { border-color: #2563EB; }
    .cs-variante.error { display: flex; align-items: center; justify-content: center; aspect-ratio: 4/5; font-size: 10.5px; color: #b45309; text-align: center; padding: 6px; }

    .cs-spin { display: inline-block; animation: csspin 1s linear infinite; }
    @keyframes csspin { to { transform: rotate(360deg); } }
    .cs-aviso { font-size: 11px; color: #6E7A96; margin-top: 6px; }
    .cs-aviso.warn { color: #b45309; }
    details.cs-mas summary { cursor: pointer; font-size: 11.5px; color: #2563EB; font-weight: 500; margin-top: 8px; }
</style>

<div class="cs-wrap">
    <div class="cs-title">
        <span><i class="fas fa-wand-magic-sparkles" style="color:#2563EB;"></i> Sommy Creative Studio</span>
        <a href="{{ route('publicaciones.index') }}" class="cs-btn sec chico"><i class="fas fa-comment"></i> Chat rápido</a>
    </div>
    <div class="cs-sub">Modo Create: control fino de escena, cámara, composición y zona de texto. Mismo motor de marca/fidelidad que el chat.</div>

    @if(!$capacidades['escenas'])
        <div class="cs-panel"><div class="cs-aviso warn">Configurá GEMINI_API_KEY en el .env para generar escenas.</div></div>
    @endif

    <div class="cs-layout">
        {{-- LEFT: configuración --}}
        <div class="cs-sidebar-scroll">
            <div class="cs-panel">
                <h4>1 · Producto</h4>
                <select id="csProducto"></select>
                <div class="cs-gallery" id="csGaleria"></div>

                <h4>2 · Objetivo</h4>
                <div class="cs-cards" id="csObjetivo"></div>

                <h4>3 · Intensidad comercial</h4>
                <div class="cs-opts" id="csIntensidad"></div>

                <h4>4 · Escena</h4>
                <select id="csEscena">
                    <option value="dormitorio">Dormitorio moderno</option>
                    <option value="noche">Dormitorio oscuro premium</option>
                    <option value="familia">Hogar cotidiano</option>
                    <option value="minimal">Minimalista / estudio</option>
                    <option value="estudio">Estudio publicitario</option>
                    <option value="fabrica">Fábrica</option>
                </select>
                <label style="font-size:11px;font-weight:500;margin-top:8px;display:block;">Densidad de la escena</label>
                <div class="cs-opts" id="csDensidad"></div>
            </div>

            <div class="cs-panel">
                <h4>5 · Iluminación</h4>
                <select id="csIluminacion"></select>
                <h4>6 · Cámara</h4>
                <select id="csCamara"></select>
                <h4>7 · Composición</h4>
                <select id="csComposicion"></select>
                <h4>8 · Zona reservada para texto</h4>
                <select id="csZonaTexto"></select>
                <h4>9 · Personas</h4>
                <select id="csPersonas"></select>
            </div>

            <div class="cs-panel">
                <h4>Formato y cantidad</h4>
                <div class="cs-opts">
                    <label class="cs-opt"><input type="radio" name="csFormato" value="feed" checked><span>Feed 4:5</span></label>
                    <label class="cs-opt"><input type="radio" name="csFormato" value="story"><span>Historia 9:16</span></label>
                    <label class="cs-opt"><input type="radio" name="csFormato" value="ml"><span>MercadoLibre 1:1</span></label>
                </div>
                <div class="cs-opts" style="margin-top:8px;">
                    <label class="cs-opt"><input type="radio" name="csCantidad" value="1"><span>1 imagen</span></label>
                    <label class="cs-opt"><input type="radio" name="csCantidad" value="2" checked><span>2 variaciones</span></label>
                    <label class="cs-opt"><input type="radio" name="csCantidad" value="4"><span>4 variaciones</span></label>
                </div>
                <div class="cs-opts" style="margin-top:8px;">
                    <label class="cs-opt"><input type="radio" name="csPrecio" value="si" checked><span>Con precio</span></label>
                    <label class="cs-opt"><input type="radio" name="csPrecio" value="no"><span>Sin precio</span></label>
                </div>
                <button class="cs-btn ia" id="csBtnGenerar" onclick="generarEscenaStudio(this)" style="margin-top:14px;" @if(!$capacidades['escenas']) disabled @endif>
                    <i class="fas fa-wand-magic-sparkles"></i> Generate Scene
                </button>
            </div>
        </div>

        {{-- CENTER: preview --}}
        <div class="cs-panel cs-center">
            <canvas id="csCanvas" width="1080" height="1350"></canvas>
            <img id="csPreview" style="display:none;" alt="Vista previa">
            <div class="cs-center-empty" id="csCenterEmpty">Elegí un producto y tocá "Generate Scene" para ver el resultado acá.</div>
        </div>

        {{-- RIGHT: variantes + acciones --}}
        <div class="cs-sidebar-scroll">
            <div class="cs-panel">
                <h4>Variantes</h4>
                <div id="csVariantesEstado" class="cs-aviso"></div>
                <div class="cs-variantes" id="csVariantes"></div>
                <details class="cs-mas">
                    <summary>Ver prompt generado</summary>
                    <div style="font-size:10.5px;color:#6E7A96;margin-top:6px;white-space:pre-wrap;" id="csPromptDebug"></div>
                </details>
            </div>

            <div class="cs-panel" id="csAcciones" style="display:none;">
                <h4>Textos sobre la imagen (capas)</h4>
                <input type="text" id="csOvHeadline" placeholder="Titular (nombre del producto por defecto)">
                <input type="text" id="csOvCta" placeholder="CTA, ej: Consultá stock" style="margin-top:6px;">
                <input type="text" id="csOvBadge" placeholder="Badge, ej: Envío gratis" style="margin-top:6px;">
                <label style="font-weight:400;margin-top:6px;display:block;"><input type="checkbox" id="csOvWebsite" style="width:auto;margin-right:6px;"> Mostrar sommy.com.ar</label>

                <h4>Texto</h4>
                <textarea id="csCaption" style="width:100%;min-height:100px;border:1px solid #E7EAF2;border-radius:10px;padding:8px;font-size:12.5px;font-family:'Poppins',sans-serif;" placeholder="Caption..."></textarea>
                <button class="cs-btn sec chico" onclick="generarCaptionStudio(this)" style="margin-top:6px;">Generar texto con IA</button>

                <h4>Campaña</h4>
                <select id="csCampana"></select>
                <button class="cs-btn sec chico" onclick="nuevaCampanaStudio()" style="margin-top:6px;">+ Nueva campaña</button>

                <h4>Exportar</h4>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="cs-btn sec chico" onclick="exportarPresetStudio('feed')">Feed</button>
                    <button class="cs-btn sec chico" onclick="exportarPresetStudio('square')">Cuadrado</button>
                    <button class="cs-btn sec chico" onclick="exportarPresetStudio('story')">Historia</button>
                    <button class="cs-btn sec chico" onclick="exportarPresetStudio('reel')">Reel</button>
                </div>

                <h4>Publicar</h4>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <button class="cs-btn" id="csBtnPublicar" onclick="publicarStudio(this)">Publicar ahora (IG + FB)</button>
                    <button class="cs-btn sec" onclick="guardarStudio(this)">Guardar borrador</button>
                    <input type="datetime-local" id="csFecha">
                    <button class="cs-btn sec" onclick="programarStudio(this)">Programar</button>
                    <button class="cs-btn sec" onclick="descargarStudio()"><i class="fas fa-download"></i> Descargar</button>
                </div>
                <div class="cs-aviso" id="csAvisoAccion"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const PRODUCTOS = @json($productos);
let CAMPANAS = @json($campanas);
const LOGO_URL = '{{ asset('imagenes/marca/sommy-logo-magia.png') }}';
const CSRF = '{{ csrf_token() }}';
const FORMATOS = { feed: [1080, 1350], story: [1080, 1920], ml: [1200, 1200], square: [1080, 1080], reel: [1080, 1920] };

const OBJETIVOS = { producto: 'Producto', oferta: 'Oferta', lifestyle: 'Lifestyle', fabricacion: 'Fabricación', educativo: 'Educativo', combo: 'Combo', institucional: 'Institucional', comparativa: 'Comparativa', testimonial: 'Testimonial', lanzamiento: 'Lanzamiento' };
const INTENSIDADES = { institucional: 'Institucional', equilibrado: 'Equilibrado', comercial: 'Comercial', promo_fuerte: 'Promo fuerte' };
const DENSIDADES = { minimal: 'Minimal', normal: 'Normal', decorated: 'Decorado' };
const ILUMINACION = { natural_soft: 'Natural suave', morning: 'Mañana', golden_hour: 'Golden hour', studio: 'Estudio', dark_premium: 'Oscura premium', night: 'Nocturna', soft_window: 'Ventana suave' };
const CAMARA = { frontal: 'Frontal', '3-4-izquierda': '3/4 izquierda', '3-4-derecha': '3/4 derecha', lateral: 'Lateral', 'close-up': 'Close-up', 'top-detail': 'Top detail', 'low-angle': 'Low angle' };
const COMPOSICION = { 'product-left-copy-right': 'Producto izq. / texto der.', 'copy-left-product-right': 'Texto izq. / producto der.', 'center-hero': 'Centro hero', 'product-bottom': 'Producto abajo', 'full-product': 'Full producto', 'split': 'Split 50/50' };
const ZONAS = { ninguna: 'Ninguna', 'superior-izquierda': 'Superior izquierda', 'superior-derecha': 'Superior derecha', 'inferior-izquierda': 'Inferior izquierda', 'inferior-derecha': 'Inferior derecha', centro: 'Centro' };
const PERSONAS = { ninguna: 'Ninguna', una: 'Una persona', pareja: 'Pareja', manos: 'Solo manos', lifestyle: 'Interacción lifestyle' };

const canvas = document.getElementById('csCanvas');
const ctx = canvas.getContext('2d');
const sel = document.getElementById('csProducto');
let imgLogo = null;
let variantes = [];
let varianteElegida = null;
let pubGuardadaId = null;

const money = v => '$' + Number(v).toLocaleString('es-AR', { maximumFractionDigits: 0 });
const prod = () => PRODUCTOS[parseInt(sel.value, 10)] || PRODUCTOS[0];
const opcion = name => document.querySelector('input[name=' + name + ']:checked').value;

function poblarSelect(id, dict) {
    const s = document.getElementById(id);
    s.innerHTML = '';
    Object.keys(dict).forEach(k => {
        const o = document.createElement('option');
        o.value = k; o.textContent = dict[k];
        s.appendChild(o);
    });
}
function poblarOpts(id, dict, name, def) {
    const cont = document.getElementById(id);
    cont.innerHTML = '';
    Object.keys(dict).forEach(k => {
        cont.innerHTML += '<label class="cs-opt"><input type="radio" name="' + name + '" value="' + k + '"' + (k === def ? ' checked' : '') + '><span>' + dict[k] + '</span></label>';
    });
}
function poblarCards(id, dict, name, def) {
    const cont = document.getElementById(id);
    cont.innerHTML = '';
    Object.keys(dict).forEach(k => {
        const card = document.createElement('label');
        card.className = 'cs-card' + (k === def ? ' activa' : '');
        card.innerHTML = '<input type="radio" name="' + name + '" value="' + k + '"' + (k === def ? ' checked' : '') + '>' + dict[k];
        card.querySelector('input').addEventListener('change', () => {
            cont.querySelectorAll('.cs-card').forEach(c => c.classList.remove('activa'));
            card.classList.add('activa');
        });
        cont.appendChild(card);
    });
}

PRODUCTOS.forEach((p, i) => {
    const o = document.createElement('option');
    o.value = i; o.textContent = p.nombre;
    sel.appendChild(o);
});

function renderGaleria() {
    const p = prod();
    const cont = document.getElementById('csGaleria');
    cont.innerHTML = '';
    (p.galeria || []).forEach(img => {
        const el = document.createElement('img');
        el.src = img.url;
        el.className = img.principal ? 'principal' : '';
        el.title = img.principal ? 'Imagen de referencia principal' : (img.angulo || '');
        cont.appendChild(el);
    });
}

function cargarLogo(cb) {
    if (imgLogo) { cb(); return; }
    imgLogo = new Image();
    imgLogo.onload = cb; imgLogo.onerror = cb;
    imgLogo.src = LOGO_URL;
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

/* ── Generar ── */
function generarEscenaStudio(btn) {
    const p = prod();
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch cs-spin"></i> Generando...';

    variantes = []; varianteElegida = null; pubGuardadaId = null;
    document.getElementById('csAcciones').style.display = 'none';
    document.getElementById('csVariantesEstado').textContent = 'Generando...';
    document.getElementById('csVariantes').innerHTML = '';

    postJson('{{ route('creative-studio.generar') }}', {
        producto_id: p.id,
        formato: opcion('csFormato'),
        cantidad: parseInt(opcion('csCantidad'), 10),
        objetivo: opcion('csObjetivo'), intensidad: opcion('csIntensidad'), escena: document.getElementById('csEscena').value,
        densidad: opcion('csDensidad'), iluminacion: document.getElementById('csIluminacion').value,
        camara: document.getElementById('csCamara').value, composicion: document.getElementById('csComposicion').value,
        zona_texto: document.getElementById('csZonaTexto').value, personas: document.getElementById('csPersonas').value
    }).then(data => {
        variantes = data.variantes || [];
        renderVariantesStudio();
    }).catch(e => {
        document.getElementById('csVariantesEstado').textContent = '';
        alert('No se pudo generar: ' + e.message);
    }).finally(() => { btn.disabled = false; btn.innerHTML = original; });
}

function renderVariantesStudio() {
    const ok = variantes.filter(v => !v.error).length;
    document.getElementById('csVariantesEstado').textContent = ok
        ? 'Elegí una (' + ok + ' de ' + variantes.length + ').'
        : 'No se pudo generar ninguna imagen.';
    const cont = document.getElementById('csVariantes');
    cont.innerHTML = '';
    variantes.forEach((v, i) => {
        const card = document.createElement('div');
        if (v.error) {
            card.className = 'cs-variante error';
            card.textContent = 'Falló';
        } else {
            card.className = 'cs-variante';
            card.innerHTML = '<img src="' + v.url + '">';
            card.onclick = () => elegirVarianteStudio(i, card);
        }
        cont.appendChild(card);
    });
    const primeraOk = variantes.find(v => !v.error);
    document.getElementById('csPromptDebug').textContent = primeraOk ? primeraOk.prompt : '';
}

function elegirVarianteStudio(i, card) {
    document.querySelectorAll('.cs-variante').forEach(c => c.classList.remove('seleccionada'));
    card.classList.add('seleccionada');
    const v = variantes[i];
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
        varianteElegida = { img, url: v.url, path: v.path, prompt: v.prompt };
        pubGuardadaId = null;
        document.getElementById('csAcciones').style.display = '';
        document.getElementById('csCenterEmpty').style.display = 'none';
        cargarLogo(() => {
            dibujarStudio();
            document.getElementById('csPreview').style.display = '';
            document.getElementById('csPreview').src = canvas.toDataURL('image/png');
        });
    };
    img.src = v.url;
}

/* ── Canvas ── */
function dibujarStudio(formatoForzado) {
    if (!varianteElegida) return;
    const p = prod();
    const formato = formatoForzado || opcion('csFormato');
    const [W, H] = FORMATOS[formato] || FORMATOS.feed;
    const conPrecio = opcion('csPrecio') === 'si';
    canvas.width = W; canvas.height = H;

    const im = varianteElegida.img;
    const r = Math.max(W / im.naturalWidth, H / im.naturalHeight);
    const iw = im.naturalWidth * r, ih = im.naturalHeight * r;
    ctx.drawImage(im, (W - iw) / 2, (H - ih) / 2, iw, ih);
    const g = ctx.createLinearGradient(0, H * .55, 0, H);
    g.addColorStop(0, 'rgba(14,23,48,0)'); g.addColorStop(1, 'rgba(14,23,48,.82)');
    ctx.fillStyle = g; ctx.fillRect(0, H * .55, W, H * .45);

    if (imgLogo && imgLogo.naturalWidth) {
        const lw = W * .22, lh = lw * imgLogo.naturalHeight / imgLogo.naturalWidth;
        const lx = W * .06, ly = H * .045;
        rRectStudio(lx - W * .02, ly - lh * .25, lw + W * .04, lh * 1.5, lh);
        ctx.fillStyle = '#FFFFFF'; ctx.fill();
        ctx.drawImage(imgLogo, lx, ly, lw, lh);
    }

    const baseY = H - ((formato === 'story' || formato === 'reel') ? H * .30 : H * .32);
    const headline = document.getElementById('csOvHeadline').value.trim() || p.nombre;
    ctx.textAlign = 'center'; ctx.fillStyle = '#FFFFFF';
    ctx.font = '600 ' + (W * .048) + 'px Poppins, sans-serif';
    envolverTextoStudio(headline, W / 2, baseY, W * .84, W * .06);

    const specs = [p.plazas, p.firmeza ? 'Firmeza ' + p.firmeza.toLowerCase() : null, p.altura ? p.altura + ' cm' : null, p.pillow ? 'Pillow top' : null].filter(Boolean).join('  ·  ');
    if (specs) { ctx.fillStyle = '#C7D0E8'; ctx.font = '400 ' + (W * .028) + 'px Poppins, sans-serif'; ctx.fillText(specs, W / 2, baseY + W * .095); }

    if (conPrecio) {
        const py = baseY + W * .17;
        if (p.descuento > 0) {
            ctx.fillStyle = '#C7D0E8'; ctx.font = '400 ' + (W * .030) + 'px Poppins, sans-serif';
            const vOld = money(p.precio); ctx.fillText(vOld, W / 2, py - W * .055);
            const tw = ctx.measureText(vOld).width;
            ctx.strokeStyle = '#C7D0E8'; ctx.lineWidth = W * .003;
            ctx.beginPath(); ctx.moveTo(W / 2 - tw / 2, py - W * .065); ctx.lineTo(W / 2 + tw / 2, py - W * .065); ctx.stroke();
        }
        ctx.fillStyle = '#FFFFFF'; ctx.font = '700 ' + (W * .075) + 'px Poppins, sans-serif';
        ctx.fillText(money(p.precioFinal), W / 2, py);
        if (p.descuento > 0) {
            ctx.fillStyle = '#7FD4F5'; ctx.font = '600 ' + (W * .03) + 'px Poppins, sans-serif';
            ctx.fillText('-' + Math.round(p.descuento) + '% OFF', W / 2, py + W * .05);
        }
    }

    const cta = document.getElementById('csOvCta').value.trim();
    const websiteOn = document.getElementById('csOvWebsite').checked;
    if (cta) {
        ctx.font = '700 ' + (W * .032) + 'px Poppins, sans-serif';
        const tw = ctx.measureText(cta.toUpperCase()).width;
        const padX = W * .045, pillW = tw + padX * 2, pillH = W * .09;
        const py = H - H * .07 - pillH / 2;
        rRectStudio(W / 2 - pillW / 2, py - pillH / 2, pillW, pillH, pillH / 2);
        ctx.fillStyle = '#2563EB'; ctx.fill();
        ctx.fillStyle = '#FFFFFF';
        ctx.fillText(cta.toUpperCase(), W / 2, py + W * .011);
    } else {
        ctx.fillStyle = '#7FD4F5'; ctx.font = '500 ' + (W * .026) + 'px Poppins, sans-serif';
        ctx.fillText('DIRECTO DE FÁBRICA  ·  ENVÍO A DOMICILIO', W / 2, H - H * .07);
    }
    if (websiteOn) {
        ctx.fillStyle = '#C7D0E8'; ctx.font = '400 ' + (W * .020) + 'px Poppins, sans-serif';
        ctx.fillText('sommy.com.ar', W / 2, H - H * .022);
    }
    const badge = document.getElementById('csOvBadge').value.trim();
    if (badge) {
        ctx.font = '700 ' + (W * .026) + 'px Poppins, sans-serif';
        const bw = ctx.measureText(badge.toUpperCase()).width;
        const padX = W * .035, pillW = bw + padX * 2, pillH = W * .065;
        const bx = W - W * .06 - pillW, by = H * .045;
        rRectStudio(bx, by, pillW, pillH, pillH / 2);
        ctx.fillStyle = '#2563EB'; ctx.fill();
        ctx.fillStyle = '#FFFFFF'; ctx.textAlign = 'center';
        ctx.fillText(badge.toUpperCase(), bx + pillW / 2, by + pillH * .68);
    }
}

function rRectStudio(x, y, w, h, r) {
    ctx.beginPath(); ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r); ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r); ctx.arcTo(x, y, x + w, y, r); ctx.closePath();
}
function envolverTextoStudio(texto, x, y, maxW, lineH) {
    const palabras = texto.split(' '); let linea = '', yy = y;
    palabras.forEach(pal => {
        const test = linea ? linea + ' ' + pal : pal;
        if (ctx.measureText(test).width > maxW && linea) { ctx.fillText(linea, x, yy); linea = pal; yy += lineH; }
        else linea = test;
    });
    ctx.fillText(linea, x, yy);
}

/* ── Texto / guardar / publicar / programar ── */
function generarCaptionStudio(btn) {
    const p = prod();
    const original = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch cs-spin"></i>';
    postJson('{{ route('publicaciones.generar-copy') }}', { producto_id: p.id, con_precio: opcion('csPrecio') === 'si' })
        .then(data => { document.getElementById('csCaption').value = data.textos.caption || ''; })
        .catch(e => alert('No se pudo generar el texto: ' + e.message))
        .finally(() => { btn.disabled = false; btn.innerHTML = original; });
}

function payloadStudio() {
    const p = prod();
    return {
        producto_id: p.id, formato: opcion('csFormato'), estilo: 'ia-studio',
        titulo_ml: p.nombre, desc_ml: '', caption: document.getElementById('csCaption').value, texto_wa: '',
        imagen_escena: varianteElegida ? varianteElegida.path : null,
        prompt_escena: varianteElegida ? varianteElegida.prompt : null,
        imagen_base64: canvas.toDataURL('image/png'),
        campana_id: document.getElementById('csCampana').value || null,
        overlay: {
            headline: document.getElementById('csOvHeadline').value.trim() || null,
            cta: document.getElementById('csOvCta').value.trim() || null,
            badge: document.getElementById('csOvBadge').value.trim() || null,
            website: document.getElementById('csOvWebsite').checked
        }
    };
}

/* ── Campañas ── */
function renderCampanasStudio() {
    const s = document.getElementById('csCampana');
    s.innerHTML = '<option value="">— Sin campaña —</option>';
    CAMPANAS.forEach(c => { s.innerHTML += '<option value="' + c.id + '">' + c.nombre + '</option>'; });
}
function nuevaCampanaStudio() {
    const nombre = prompt('Nombre de la campaña:');
    if (!nombre || !nombre.trim()) return;
    postJson('{{ route('publicaciones.campanas') }}', { nombre: nombre.trim() }).then(data => {
        CAMPANAS.unshift({ id: data.id, nombre: data.nombre });
        renderCampanasStudio();
        document.getElementById('csCampana').value = data.id;
    }).catch(e => alert('No se pudo crear la campaña: ' + e.message));
}

/* ── Export a tamaños fijos (no cambia el formato elegido, solo exporta) ── */
function exportarPresetStudio(preset) {
    if (!varianteElegida) return;
    dibujarStudio(preset);
    const p = prod();
    const a = document.createElement('a');
    a.download = 'sommy-' + p.nombre.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-' + preset + '.png';
    a.href = canvas.toDataURL('image/png');
    a.click();
    dibujarStudio();
    document.getElementById('csPreview').src = canvas.toDataURL('image/png');
}

function guardarStudio(btn) {
    const original = btn ? btn.innerHTML : null;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch cs-spin"></i>'; }
    return postJson('{{ route('publicaciones.guardar') }}', payloadStudio()).then(data => {
        pubGuardadaId = data.id;
        if (btn) { btn.innerHTML = '✓ Guardado'; document.getElementById('csAvisoAccion').textContent = 'Guardado como borrador.'; setTimeout(() => btn.innerHTML = original, 1500); }
        return data.id;
    }).finally(() => { if (btn) btn.disabled = false; });
}

function publicarStudio(btn) {
    const original = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch cs-spin"></i> Publicando...';
    const asegurar = pubGuardadaId ? Promise.resolve(pubGuardadaId) : guardarStudio(null);
    asegurar.then(id => postJson('{{ route('publicaciones.publicar') }}', { publicacion_id: id, canales: ['facebook', 'instagram'] }))
        .then(data => {
            const errMsgs = Object.values(data.errores || {});
            document.getElementById('csAvisoAccion').textContent = errMsgs.length ? 'Falló: ' + errMsgs.join(' | ') : 'Publicado en Instagram y Facebook.';
            btn.innerHTML = '✓ Publicado'; setTimeout(() => btn.innerHTML = original, 2000);
        }).catch(e => { alert('No se pudo publicar: ' + e.message); btn.innerHTML = original; })
        .finally(() => { btn.disabled = false; });
}

function programarStudio(btn) {
    const fecha = document.getElementById('csFecha').value;
    if (!fecha) { alert('Elegí fecha y hora.'); return; }
    const original = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch cs-spin"></i>';
    postJson('{{ route('publicaciones.guardar') }}', Object.assign(payloadStudio(), { programado_para: fecha, canales_programados: ['facebook', 'instagram'] }))
        .then(data => {
            pubGuardadaId = data.id;
            document.getElementById('csAvisoAccion').textContent = 'Programado para el ' + new Date(fecha).toLocaleString('es-AR') + '.';
            btn.innerHTML = '✓ Programado'; setTimeout(() => btn.innerHTML = original, 2000);
        }).catch(e => alert('No se pudo programar: ' + e.message))
        .finally(() => { btn.disabled = false; });
}

function descargarStudio() {
    const p = prod();
    const a = document.createElement('a');
    a.download = 'sommy-' + p.nombre.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-studio.png';
    a.href = canvas.toDataURL('image/png');
    a.click();
}

/* ── Init ── */
sel.addEventListener('change', renderGaleria);

document.fonts.ready.then(() => {
    poblarCards('csObjetivo', OBJETIVOS, 'csObjetivo', 'producto');
    poblarOpts('csIntensidad', INTENSIDADES, 'csIntensidad', 'equilibrado');
    poblarOpts('csDensidad', DENSIDADES, 'csDensidad', 'normal');
    poblarSelect('csIluminacion', ILUMINACION);
    poblarSelect('csCamara', CAMARA);
    poblarSelect('csComposicion', COMPOSICION);
    poblarSelect('csZonaTexto', ZONAS);
    poblarSelect('csPersonas', PERSONAS);
    renderGaleria();
    cargarLogo(() => {});
    renderCampanasStudio();
    ['csOvHeadline', 'csOvCta', 'csOvBadge'].forEach(id => document.getElementById(id).addEventListener('input', () => {
        if (varianteElegida) { dibujarStudio(); document.getElementById('csPreview').src = canvas.toDataURL('image/png'); }
    }));
    document.getElementById('csOvWebsite').addEventListener('change', () => {
        if (varianteElegida) { dibujarStudio(); document.getElementById('csPreview').src = canvas.toDataURL('image/png'); }
    });
});
</script>
@endsection
