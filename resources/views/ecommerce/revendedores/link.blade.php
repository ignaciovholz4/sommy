@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Tu link de revendedor Sommy')
@section('contentEcommerce')

<style>
    .rvl { font-family: 'Poppins', sans-serif; color: #1B2B5A; background: #F4F6FB; padding: 56px 20px 80px; min-height: 70vh; }
    .rvl-card {
        max-width: 640px; margin: 0 auto; background: #fff; border: 1px solid #E7EAF2;
        border-radius: 26px; padding: 44px 40px; text-align: center;
        box-shadow: 0 24px 60px rgba(27,43,90,.09);
    }
    .rvl-check { width: 62px; height: 62px; border-radius: 50%; background: #E4F5EC; color: #0d8a4f; display: flex; align-items: center; justify-content: center; font-size: 27px; margin: 0 auto 20px; }
    .rvl-card h1 { font-size: 26px; font-weight: 600; margin-bottom: 10px; }
    .rvl-card .sub { font-size: 14.5px; font-weight: 300; color: #5D6884; line-height: 1.7; margin-bottom: 30px; }

    .rvl-linkbox { background: linear-gradient(135deg,#131C36,#1B2B5A); border-radius: 20px; padding: 28px 24px; color: #fff; }
    .rvl-linkbox .l { font-size: 11px; letter-spacing: .18em; text-transform: uppercase; color: #A8B5D4; }
    .rvl-linkbox .u { display: block; font-size: 15px; font-weight: 500; color: #fff; word-break: break-all; margin: 10px 0 20px; text-decoration: none; }
    .rvl-linkbox .u:hover { color: #C6A15B; }
    .rvl-acciones { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
    .rvl-btn { border: none; background: #fff; color: #1B2B5A; border-radius: 999px; padding: 13px 28px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; font-family: inherit; }
    .rvl-btn:hover { color: #1B2B5A; opacity: .88; }
    .rvl-btn.ghost { background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,.55); }
    .rvl-btn.ghost:hover { background: #fff; color: #1B2B5A; opacity: 1; }
    .rvl-btn.wsp { background: #1EBE5A; color: #fff; }
    .rvl-btn.wsp:hover { color: #fff; }

    .rvl-qr { margin-top: 28px; }
    .rvl-qr img { width: 220px; height: 220px; border: 1px solid #E7EAF2; border-radius: 18px; padding: 10px; background: #fff; }
    .rvl-qr .t { font-size: 13px; color: #5D6884; font-weight: 300; margin-top: 12px; line-height: 1.6; }

    .rvl-codigo { display: inline-block; font-family: monospace; font-size: 14px; letter-spacing: .12em; background: #F1F4F9; border-radius: 10px; padding: 8px 18px; color: #47536F; margin-top: 22px; }

    .rvl-nota { max-width: 640px; margin: 24px auto 0; background: #fff; border: 1px solid #E7EAF2; border-radius: 20px; padding: 26px 30px; }
    .rvl-nota h3 { font-size: 15.5px; font-weight: 600; margin-bottom: 12px; }
    .rvl-nota li { font-size: 13.5px; font-weight: 300; color: #5D6884; line-height: 1.8; }

    @media (max-width: 560px) {
        .rvl-card { padding: 32px 22px; border-radius: 20px; }
        .rvl-qr img { width: 190px; height: 190px; }
    }
</style>

<div class="rvl">

    <div class="rvl-card">
        <div class="rvl-check"><i class="fas fa-check"></i></div>
        <h1>¡Listo, {{ explode(' ', $revendedor->nombre)[0] }}!</h1>
        <p class="sub">
            Este es tu link de revendedor. Todo lo que se compre entrando por acá queda registrado a tu nombre
            y te lo liquidamos nosotros — no tenés que anotar ni reclamar nada.
            También te lo mandamos por mail a <strong>{{ $revendedor->email }}</strong>.
        </p>

        <div class="rvl-linkbox">
            <div class="l">Tu link</div>
            <a href="{{ $revendedor->link }}" target="_blank" class="u" id="rvl-url">{{ $revendedor->link }}</a>
            <div class="rvl-acciones">
                <button type="button" class="rvl-btn" onclick="rvlCopiar()"><i class="fas fa-copy"></i> Copiar</button>
                <a class="rvl-btn wsp" target="_blank"
                   href="https://wa.me/?text={{ urlencode('Mirá los colchones y sommiers de Sommy, comprás directo de fábrica: ' . $revendedor->link) }}">
                    <i class="fab fa-whatsapp"></i> Compartir
                </a>
                <a class="rvl-btn ghost" href="{{ route('revendedores.qr', $revendedor->codigo) }}"><i class="fas fa-download"></i> Bajar QR</a>
            </div>
        </div>

        <div class="rvl-qr">
            <img src="{{ \App\Http\Controllers\Ecommerce\RevendedorPublicController::qrDataUri($revendedor->link, 500) }}"
                 alt="QR del link de {{ $revendedor->nombre }}">
            <div class="t">Imprimilo o mostralo desde el celular.<br>Quien lo escanee entra a la tienda con tu venta ya asociada.</div>
        </div>

        <div class="rvl-codigo">{{ $revendedor->codigo }}</div>
    </div>

    <div class="rvl-nota">
        <h3>Tres cosas para tener en cuenta</h3>
        <ul>
            <li>Guardá el mail que te enviamos: desde ahí volvés a esta página cuando quieras.</li>
            <li>La atribución dura <strong>30 días</strong> desde que la persona abre tu link, así que sirve aunque no compre en el momento.</li>
            <li>Las comisiones se liquidan una vez entregado y cobrado el pedido. Cualquier consulta, escribinos por WhatsApp.</li>
        </ul>
    </div>

</div>

<script>
function rvlCopiar() {
    const url = document.getElementById('rvl-url').textContent.trim();
    navigator.clipboard.writeText(url).then(function () {
        const btn = event.target.closest('button');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
        setTimeout(function () { btn.innerHTML = original; }, 1800);
    });
}
</script>

@endsection
