@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Pedido #' . $pedido->order_id . ' | Sommy')
@section('contentEcommerce')

<section class="py-5">
    <div class="container" style="max-width:820px;">
        <a href="{{ url('/cuenta/pedidos') }}" style="font-size:13.5px;color:#2563EB;text-decoration:none;">
            <i class="fa-solid fa-arrow-left"></i> Mis pedidos
        </a>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2" style="margin:10px 0 26px;">
            <h1 style="font-size:26px;font-weight:600;margin:0;">Pedido #{{ $pedido->order_id }}</h1>
            <span style="font-size:13px;font-weight:300;color:#6E7A96;">{{ \Carbon\Carbon::parse($pedido->order_date)->format('d/m/Y H:i') }} hs</span>
        </div>

        {{-- Estado --}}
        <div class="sommy-seg-card" data-aos="fade-up">
            @if($pedido->status_order_id == 6)
                <div style="background:#FBEDE6;color:#b4552d;border-radius:12px;padding:14px 18px;font-weight:500;">
                    <i class="fa-solid fa-ban"></i> Este pedido fue anulado. Si tenés dudas, escribinos por WhatsApp.
                </div>
            @else
            @if(!empty($pedido->fecha_entrega))
            <div style="background:#E0F2FE;color:#1B2B5A;border-radius:12px;padding:11px 16px;font-size:13.5px;font-weight:500;margin-bottom:16px;">
                <i class="fa-solid fa-truck"></i> Entrega programada: <strong>{{ \Carbon\Carbon::parse($pedido->fecha_entrega)->locale('es')->isoFormat('dddd D [de] MMMM') }}</strong>
            </div>
            @endif
            <div class="sommy-seg-stepper">
                @foreach($etapas as $idEtapa => $etapa)
                <div class="seg-paso {{ $pedido->status_order_id > $idEtapa ? 'hecho' : '' }} {{ $pedido->status_order_id == $idEtapa ? 'actual' : '' }}">
                    <div class="seg-icono"><i class="fa-solid {{ $pedido->status_order_id > $idEtapa ? 'fa-check' : $etapa['icono'] }}"></i></div>
                    <div class="seg-nombre">{{ $etapa['nombre'] }}</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Productos --}}
        <div class="sommy-seg-card" data-aos="fade-up">
            <h2 class="seg-titulo">Productos</h2>
            @foreach($pedido->detalles as $det)
            <div class="seg-prod">
                <img src="{{ asset('imagenes/articulos/' . optional($det->producto)->imagen) }}" alt="">
                <div style="flex:1;min-width:0;">
                    <div class="n">{{ optional($det->producto)->nombre ?? 'Producto' }}</div>
                    <div class="c">Cantidad: {{ $det->quantity }}</div>
                </div>
                <div class="p">${{ number_format($det->total, 2, ',', '.') }}</div>
            </div>
            @endforeach

            <div class="seg-totales">
                @if($pedido->costo_envio > 0)
                <div><span>Envío</span><strong>${{ number_format($pedido->costo_envio, 2, ',', '.') }}</strong></div>
                @endif
                @if($pedido->descuento_pago > 0)
                <div><span>Descuento</span><strong>-${{ number_format($pedido->descuento_pago, 2, ',', '.') }}</strong></div>
                @endif
                <div class="tot"><span>Total</span><strong>${{ number_format($pedido->total_amount, 2, ',', '.') }}</strong></div>
            </div>
        </div>

        @if(!empty($arrayEmpresa['phone']))
        <div style="text-align:center;margin-top:24px;">
            <p style="font-size:13.5px;font-weight:300;color:#6E7A96;margin-bottom:12px;">¿Consultas sobre este pedido?</p>
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $arrayEmpresa['whatsapp']) }}?text={{ urlencode('Hola! Quiero consultar por mi pedido #' . $pedido->order_id) }}"
               target="_blank" rel="noopener noreferrer" class="btn-sommy-whatsapp">
                <i class="fa-brands fa-whatsapp"></i> Consultar por WhatsApp
            </a>
        </div>
        @endif
    </div>
</section>

<style>
.sommy-seg-card {
    background: #fff; border: 1px solid #E7EAF2; border-radius: 18px;
    box-shadow: 0 10px 30px rgba(27,43,90,.08); padding: 26px 26px; margin-bottom: 16px;
}
.seg-titulo { font-size: 15px; font-weight: 600; color: #47536F; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 16px; }

/* Stepper horizontal del cliente */
.sommy-seg-stepper { display: flex; align-items: flex-start; overflow-x: auto; padding: 4px 0; }
.seg-paso { flex: 1; min-width: 92px; text-align: center; position: relative; }
.seg-paso:not(:last-child)::after {
    content: ''; position: absolute; top: 20px; left: calc(50% + 26px);
    width: calc(100% - 52px); height: 3px; background: #E7EAF2; border-radius: 999px;
}
.seg-paso.hecho:not(:last-child)::after { background: #1B2B5A; }
.seg-icono {
    width: 40px; height: 40px; border-radius: 999px; margin: 0 auto 8px;
    border: 2px solid #E7EAF2; background: #fff; color: #9AA5C0;
    display: flex; align-items: center; justify-content: center; font-size: 15px;
}
.seg-paso.hecho .seg-icono { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }
.seg-paso.actual .seg-icono { background: #E0F2FE; border-color: #2563EB; color: #1B2B5A; animation: segPulso 2s ease-in-out infinite; }
@keyframes segPulso {
    0%, 100% { box-shadow: none; }
    50% { box-shadow: 0 0 0 8px rgba(37, 99, 235, .15); }
}
.seg-nombre { font-size: 12px; font-weight: 500; color: #8A93AD; line-height: 1.3; }
.seg-paso.hecho .seg-nombre, .seg-paso.actual .seg-nombre { color: #1B2B5A; }

/* Productos */
.seg-prod { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid #F1F4F9; }
.seg-prod:last-of-type { border-bottom: none; }
.seg-prod img { width: 58px; height: 58px; border-radius: 12px; object-fit: cover; border: 1px solid #E7EAF2; background: #E0F2FE; }
.seg-prod .n { font-weight: 500; font-size: 14.5px; color: #1B2B5A; }
.seg-prod .c { font-size: 12.5px; font-weight: 300; color: #6E7A96; }
.seg-prod .p { font-weight: 700; color: #1B2B5A; white-space: nowrap; }

.seg-totales { border-top: 1px solid #E7EAF2; margin-top: 8px; padding-top: 14px; }
.seg-totales > div { display: flex; justify-content: space-between; font-size: 14px; color: #47536F; padding: 3px 0; }
.seg-totales .tot { font-size: 17px; color: #1B2B5A; }
.seg-totales .tot strong { font-size: 20px; font-weight: 700; }
</style>

@endsection
