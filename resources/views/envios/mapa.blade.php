@extends('layouts.admin')

@section('title', 'Mapa del reparto')

@section('contenido')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    .mp-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; }
    .mp-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
    .mp-title { font-size: 21px; font-weight: 600; }
    .mp-filtros { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .mp-filtros input, .mp-filtros select { border: 1px solid #E7EAF2; border-radius: 10px; padding: 7px 12px; font-size: 13px; color: #1B2B5A; }
    .mp-btn { border: none; border-radius: 999px; padding: 8px 18px; font-size: 12.5px; font-weight: 500; cursor: pointer; background: #1B2B5A; color: #fff; text-decoration: none; }
    .mp-btn:hover { background: #2563EB; color: #fff; }

    .mp-grid { display: grid; grid-template-columns: 1fr 330px; gap: 14px; align-items: start; }
    @media (max-width: 991px) { .mp-grid { grid-template-columns: 1fr; } }
    #mapaReparto { height: 620px; border-radius: 16px; border: 1px solid #E7EAF2; box-shadow: 0 10px 30px rgba(27,43,90,.08); }

    .mp-panel { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 14px; max-height: 620px; overflow-y: auto; }
    .mp-kpis { display: flex; gap: 8px; margin-bottom: 10px; }
    .mp-kpi { flex: 1; background: #F8FAFC; border: 1px solid #E7EAF2; border-radius: 10px; padding: 8px 10px; text-align: center; font-size: 10.5px; color: #6E7A96; text-transform: uppercase; letter-spacing: .04em; }
    .mp-kpi b { display: block; font-size: 17px; color: #1B2B5A; }
    .mp-item { display: flex; gap: 10px; align-items: flex-start; padding: 9px 6px; border-bottom: 1px solid #F1F4F9; font-size: 12.5px; }
    .mp-dot { width: 26px; height: 26px; border-radius: 999px; background: #b4552d; color: #fff; font-weight: 700; font-size: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .mp-item.ok .mp-dot { background: #0d8a4f; }
    .mp-item .quien { font-weight: 600; }
    .mp-item .sub { color: #6E7A96; font-size: 11.5px; }
    .mp-item .cobro { color: #0d8a4f; font-weight: 700; font-size: 12px; }
    .mp-vivo { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; color: #0d8a4f; font-weight: 600; }
    .mp-vivo .punto { width: 8px; height: 8px; border-radius: 999px; background: #0d8a4f; animation: mpLate 1.6s infinite; }
    @keyframes mpLate { 0%,100% { opacity: 1; } 50% { opacity: .25; } }

    .marcador-parada { background: #b4552d; color: #fff; border-radius: 999px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; border: 2.5px solid #fff; box-shadow: 0 3px 10px rgba(0,0,0,.35); font-family: 'Poppins', sans-serif; }
    .marcador-parada.ok { background: #0d8a4f; }
    .marcador-parada.deposito { background: #1B2B5A; width: 32px; height: 32px; font-size: 14px; }
</style>

<div class="mp-wrap">
    <div class="mp-head">
        <div class="mp-title"><i class="fas fa-map-marked-alt" style="color:#2563EB;"></i> Reparto en vivo
            <span class="mp-vivo"><span class="punto"></span> actualiza solo</span>
        </div>
        <form method="GET" action="{{ url('envios/mapa') }}" class="mp-filtros">
            <input type="date" name="fecha" value="{{ $fecha }}">
            <select name="transportista_id">
                <option value="">Todos los fleteros</option>
                @foreach($transportistas as $t)
                    <option value="{{ $t->id }}" {{ (string) $transportistaId === (string) $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
                @endforeach
            </select>
            <button type="submit" class="mp-btn">Ver</button>
            <a class="mp-btn" style="background:#E0F2FE;color:#1B2B5A;" href="{{ url('envios/ruta?fecha=' . $fecha . ($transportistaId ? '&transportista_id=' . $transportistaId : '')) }}"><i class="fas fa-route"></i> Hoja de ruta</a>
        </form>
    </div>

    <div class="mp-grid">
        <div id="mapaReparto"></div>
        <div class="mp-panel">
            <div class="mp-kpis">
                <div class="mp-kpi">Entregadas <b id="mpEntregadas">—</b></div>
                <div class="mp-kpi">Paradas <b id="mpTotal">—</b></div>
                <div class="mp-kpi">Cobrado <b id="mpCobrado">—</b></div>
            </div>
            <div id="mpLista"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const DEPOSITO = @json($deposito);
const URL_DATA = '{{ url('envios/mapa-data') }}?fecha={{ $fecha }}{{ $transportistaId ? '&transportista_id=' . $transportistaId : '' }}';

const mapa = L.map('mapaReparto').setView([DEPOSITO.lat, DEPOSITO.lng], 13);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
}).addTo(mapa);

// Depósito
L.marker([DEPOSITO.lat, DEPOSITO.lng], {
    icon: L.divIcon({ className: '', html: '<div class="marcador-parada deposito">🏭</div>', iconSize: [32, 32], iconAnchor: [16, 16] })
}).addTo(mapa).bindPopup('<b>Depósito Sommy</b><br>' + DEPOSITO.direccion);

let capaMarcadores = L.layerGroup().addTo(mapa);
let capaLinea = null;
let primeraCarga = true;

function pintar(data) {
    document.getElementById('mpEntregadas').textContent = data.entregadas;
    document.getElementById('mpTotal').textContent = data.total;
    document.getElementById('mpCobrado').textContent = '$' + Number(data.cobrado).toLocaleString('es-AR');

    capaMarcadores.clearLayers();
    if (capaLinea) { mapa.removeLayer(capaLinea); }

    const puntosLinea = [[DEPOSITO.lat, DEPOSITO.lng]];
    const bounds = [[DEPOSITO.lat, DEPOSITO.lng]];
    const lista = document.getElementById('mpLista');
    lista.innerHTML = '';

    data.paradas.forEach((p, i) => {
        const num = p.orden || (i + 1);
        const okClass = p.entregado ? 'ok' : '';

        if (p.lat && p.lng) {
            puntosLinea.push([p.lat, p.lng]);
            bounds.push([p.lat, p.lng]);
            L.marker([p.lat, p.lng], {
                icon: L.divIcon({ className: '', html: `<div class="marcador-parada ${okClass}">${p.entregado ? '✓' : num}</div>`, iconSize: [28, 28], iconAnchor: [14, 14] })
            }).addTo(capaMarcadores).bindPopup(
                `<b>${num}. ${p.cliente}</b><br>${p.referencia}<br>${p.direccion || ''}` +
                (p.entregado ? `<br><span style="color:#0d8a4f;font-weight:700;">✓ Entregado ${p.hora_entrega || ''}${p.monto_cobrado != null ? ' · cobró $' + Number(p.monto_cobrado).toLocaleString('es-AR') : ''}</span>` : '')
            );
        }

        lista.innerHTML += `
            <div class="mp-item ${okClass}">
                <div class="mp-dot">${p.entregado ? '✓' : num}</div>
                <div>
                    <div class="quien">${p.cliente} <span style="color:#94A3B8;font-weight:400;">· ${p.referencia}</span></div>
                    <div class="sub">${p.direccion || 'Sin dirección'}${p.fletero ? ' · ' + p.fletero : ''}</div>
                    ${p.entregado ? `<div class="cobro">✓ ${p.hora_entrega || ''}${p.monto_cobrado != null ? ' · cobró $' + Number(p.monto_cobrado).toLocaleString('es-AR') : ''}</div>` : ''}
                </div>
            </div>`;
    });

    if (!data.paradas.length) {
        lista.innerHTML = '<div style="text-align:center;color:#94A3B8;padding:24px;font-size:12.5px;">Sin envíos para este día.</div>';
    }

    // Track del recorrido en el orden de la ruta
    if (puntosLinea.length > 1) {
        capaLinea = L.polyline(puntosLinea, { color: '#2563EB', weight: 3, opacity: .65, dashArray: '6 8' }).addTo(mapa);
    }
    if (primeraCarga && bounds.length > 1) {
        mapa.fitBounds(bounds, { padding: [40, 40] });
        primeraCarga = false;
    }
}

function refrescar() {
    fetch(URL_DATA, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : null)
        .then(data => { if (data) pintar(data); })
        .catch(() => {});
}

refrescar();
setInterval(refrescar, 20000); // en vivo: cada 20 segundos
</script>
@endsection
