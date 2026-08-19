<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Entregas — {{ $fletero->nombre }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
    body { font-family: 'Poppins', Arial, sans-serif; background: #F1F4F9; color: #1B2B5A; padding-bottom: 40px; }
    .cab { background: linear-gradient(120deg, #1B2B5A, #24356B); color: #fff; padding: 18px 16px 14px; border-radius: 0 0 18px 18px; }
    .cab h1 { font-size: 17px; font-weight: 700; }
    .cab p { color: #C7D0E8; font-size: 12px; margin-top: 2px; }
    .cab .resumen { display: flex; gap: 8px; margin-top: 10px; }
    .cab .kpi { flex: 1; background: rgba(255,255,255,.10); border-radius: 10px; padding: 7px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #C7D0E8; }
    .cab .kpi b { display: block; font-size: 16px; color: #fff; }

    .exito { margin: 12px 14px 0; background: #DCFCE7; color: #166534; border-radius: 12px; padding: 10px 14px; font-size: 13px; font-weight: 600; }
    .dia { margin: 18px 14px 6px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #47536F; }

    .parada { background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; margin: 8px 14px; padding: 13px 14px; box-shadow: 0 5px 16px rgba(27,43,90,.06); }
    .parada .top { display: flex; gap: 10px; align-items: center; }
    .parada .num { width: 30px; height: 30px; border-radius: 999px; background: #1B2B5A; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .parada .quien { font-weight: 700; font-size: 14.5px; }
    .parada .ref { color: #94A3B8; font-size: 11px; }
    .parada .dir { font-size: 13px; color: #47536F; margin-top: 6px; }
    .parada .acciones { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
    .btn { border: none; border-radius: 999px; padding: 10px 16px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; }
    .btn.azul { background: #E0F2FE; color: #1B2B5A; }
    .btn.verde { background: #0d8a4f; color: #fff; flex: 1; }
    .btn.navy { background: #1B2B5A; color: #fff; width: 100%; }
    .btn.gris { background: #F1F4F9; color: #47536F; }
    .cobrar { margin-top: 8px; background: #FEF3C7; color: #92400E; border-radius: 10px; padding: 8px 12px; font-size: 13px; font-weight: 700; text-align: center; }
    .cobrar.nada { background: #DCFCE7; color: #166534; }

    .form-entrega { display: none; margin-top: 12px; border-top: 1px dashed #E7EAF2; padding-top: 12px; }
    .form-entrega.abierto { display: block; }
    .form-entrega label { font-size: 12px; font-weight: 600; display: block; margin: 10px 0 4px; }
    .form-entrega input[type=number], .form-entrega input[type=text] { width: 100%; border: 1.5px solid #E7EAF2; border-radius: 10px; padding: 11px 13px; font-size: 15px; }
    .form-entrega input[type=file] { width: 100%; font-size: 13px; }
    canvas.firma { width: 100%; height: 150px; border: 1.5px dashed #94A3B8; border-radius: 10px; background: #fff; touch-action: none; }
    .firma-acciones { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; }
    .vacio { text-align: center; color: #94A3B8; padding: 50px 20px; font-size: 14px; }
    .error { margin: 12px 14px 0; background: #FEE2E2; color: #991B1B; border-radius: 12px; padding: 10px 14px; font-size: 12.5px; }
</style>
</head>
<body>
    <div class="cab">
        <div class="resumen" style="margin-top:0;">
            <div class="kpi">Pendientes <b>{{ $porDia->flatten()->count() }}</b></div>
            <div class="kpi">Entregadas hoy <b>{{ $entregasHoy }}</b></div>
            <div class="kpi">Cobrado hoy <b>${{ number_format($cobradoHoy, 0, ',', '.') }}</b></div>
        </div>
    </div>

    @if(session('entrega_ok'))<div class="exito">✅ {{ session('entrega_ok') }}</div>@endif
    @if($errors->any())<div class="error">⚠ {{ $errors->first() }}</div>@endif

    @forelse($porDia as $dia => $envios)
        <div class="dia">
            @if($dia === 'sin-fecha') 📌 Sin fecha asignada
            @else 📅 {{ \Carbon\Carbon::parse($dia)->locale('es')->isoFormat('dddd D/M') }}
                {{ \Carbon\Carbon::parse($dia)->isToday() ? '— HOY' : '' }}
            @endif
        </div>

        @foreach($envios as $e)
        <div class="parada">
            <div class="top">
                <div class="num">{{ $e->orden_ruta ?: '·' }}</div>
                <div>
                    <div class="quien">{{ $e->parada_cliente }}</div>
                    <div class="ref">{{ $e->parada_referencia }}</div>
                </div>
            </div>
            <div class="dir">📍 {{ $e->parada_direccion ?: 'Consultar dirección con la oficina' }}</div>

            @if($e->parada_a_cobrar > 0.009)
                @if(($e->parada_pagado ?? 0) > 0.009)
                    <div class="cobrar">
                        💵 COBRAR SOLO: ${{ number_format($e->parada_a_cobrar, 2, ',', '.') }}
                        <div style="font-weight:500;font-size:11.5px;">El cliente ya transfirió ${{ number_format($e->parada_pagado, 2, ',', '.') }} — cobrale únicamente la diferencia.</div>
                    </div>
                @else
                    <div class="cobrar">💵 COBRAR: ${{ number_format($e->parada_a_cobrar, 2, ',', '.') }}</div>
                @endif
            @else
                <div class="cobrar nada">✓ EL CLIENTE YA PAGÓ (transferencia registrada) — <b>NO COBRAR</b>, solo entregar</div>
            @endif

            <div class="acciones">
                @if($e->parada_direccion)
                    <a class="btn azul" href="https://www.google.com/maps/dir/?api=1&destination={{ $e->lat && $e->lng ? $e->lat . ',' . $e->lng : rawurlencode($e->parada_direccion) }}" target="_blank">🧭 Ir</a>
                @endif
                @if($e->parada_telefono)
                    <a class="btn azul" href="https://wa.me/{{ preg_replace('/\D/', '', $e->parada_telefono) }}" target="_blank">💬</a>
                @endif
                <button type="button" class="btn verde" onclick="this.closest('.parada').querySelector('.form-entrega').classList.toggle('abierto')">✅ ENTREGUÉ</button>
            </div>

            {{-- Confirmación: monto + firma + foto de la plata --}}
            <form class="form-entrega" method="POST" action="{{ url('fletero/' . $token . '/entrega/' . $e->id) }}" enctype="multipart/form-data"
                  onsubmit="return prepararFirma(this)">
                @csrf
                <label>💵 Monto cobrado (lo que te dio el cliente)</label>
                <input type="number" name="monto_cobrado" step="0.01" min="0" value="{{ number_format($e->parada_a_cobrar, 2, '.', '') }}" required>

                <label>✍ Firma del cliente (que firme acá con el dedo)</label>
                <canvas class="firma" width="600" height="220"></canvas>
                <input type="hidden" name="firma">
                <div class="firma-acciones">
                    <span style="font-size:11px;color:#94A3B8;">Recibí conforme</span>
                    <button type="button" class="btn gris" style="padding:5px 12px;font-size:11px;" onclick="limpiarFirma(this)">Borrar firma</button>
                </div>

                <label>📷 Foto de la plata {{ $e->parada_a_cobrar > 0.009 ? '(obligatoria si cobraste)' : '(si cobraste algo)' }}</label>
                <input type="file" name="foto_plata" accept="image/*" capture="environment">

                <label>Nota (opcional)</label>
                <input type="text" name="nota" maxlength="300" placeholder="Ej: recibió la esposa / pagó con billetes de 1000">

                <button type="submit" class="btn navy" style="margin-top:12px;">CONFIRMAR ENTREGA</button>
            </form>
        </div>
        @endforeach
    @empty
        <div class="vacio">🎉 No tenés entregas pendientes esta semana.<br><small>Cuando la oficina te asigne envíos, van a aparecer acá.</small></div>
    @endforelse

<script>
// Firma táctil en canvas (dedo o mouse)
document.querySelectorAll('canvas.firma').forEach(cv => {
    const ctx = cv.getContext('2d');
    ctx.lineWidth = 2.5; ctx.lineCap = 'round'; ctx.strokeStyle = '#1B2B5A';
    let dibujando = false;
    cv.dataset.usada = '0';

    const pos = ev => {
        const r = cv.getBoundingClientRect();
        const p = ev.touches ? ev.touches[0] : ev;
        return [(p.clientX - r.left) * (cv.width / r.width), (p.clientY - r.top) * (cv.height / r.height)];
    };
    const empezar = ev => { ev.preventDefault(); dibujando = true; cv.dataset.usada = '1'; const [x, y] = pos(ev); ctx.beginPath(); ctx.moveTo(x, y); };
    const mover = ev => { if (!dibujando) return; ev.preventDefault(); const [x, y] = pos(ev); ctx.lineTo(x, y); ctx.stroke(); };
    const soltar = () => { dibujando = false; };

    cv.addEventListener('pointerdown', empezar);
    cv.addEventListener('pointermove', mover);
    window.addEventListener('pointerup', soltar);
});

function limpiarFirma(btn) {
    const cv = btn.closest('.form-entrega').querySelector('canvas.firma');
    cv.getContext('2d').clearRect(0, 0, cv.width, cv.height);
    cv.dataset.usada = '0';
}

function prepararFirma(form) {
    const cv = form.querySelector('canvas.firma');
    if (cv.dataset.usada !== '1') {
        alert('Falta la firma del cliente: pedile que firme con el dedo en el recuadro.');
        return false;
    }
    const monto = parseFloat(form.querySelector('[name=monto_cobrado]').value) || 0;
    const foto = form.querySelector('[name=foto_plata]').files.length;
    if (monto > 0 && !foto) {
        alert('Cobraste plata: sacale una foto al efectivo antes de confirmar.');
        return false;
    }
    form.querySelector('[name=firma]').value = cv.toDataURL('image/png');
    return confirm('¿Confirmar la entrega' + (monto > 0 ? ' con cobro de $' + monto.toLocaleString('es-AR') : '') + '?');
}
</script>
</body>
</html>
