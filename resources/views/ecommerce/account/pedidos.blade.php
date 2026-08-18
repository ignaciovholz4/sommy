@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Mis pedidos | Sommy')
@section('contentEcommerce')

<section class="py-5">
    <div class="container" style="max-width:760px;">
        <h1 style="font-size:26px;font-weight:600;margin-bottom:4px;">Mis pedidos</h1>
        <p style="font-weight:300;color:#47536F;margin-bottom:28px;">
            Hola, {{ explode(' ', trim(Auth::guard('cliente')->user()->nombre))[0] }} — acá podés seguir el estado de tus compras.
        </p>

        @if($pedidos->isEmpty())
            <div class="sommy-account-card" style="text-align:center;">
                <div style="width:64px;height:64px;border-radius:999px;background:#E0F2FE;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:#1B2B5A;font-size:22px;">
                    <i class="fa-solid fa-feather"></i>
                </div>
                <p style="font-weight:600;color:#1B2B5A;margin-bottom:4px;">Todavía no tenés pedidos</p>
                <p style="font-size:13.5px;font-weight:300;color:#6E7A96;margin-bottom:18px;">Cuando hagas tu primera compra, la vas a poder seguir desde acá.</p>
                <a href="{{ url('/productos') }}" class="btn-sommy-primary">Ver productos</a>
            </div>
        @endif

        @foreach($pedidos as $p)
        <a href="{{ url('/cuenta/pedidos/' . $p->order_id) }}" class="sommy-pedido-row" data-aos="fade-up">
            <div>
                <div class="np">Pedido #{{ $p->order_id }}</div>
                <div class="fe">{{ \Carbon\Carbon::parse($p->order_date)->format('d/m/Y') }}</div>
            </div>
            <div class="es {{ $p->status_order_id == 6 ? 'anulado' : ($p->status_order_id == 5 ? 'entregado' : '') }}">
                {{ $p->status->status_name ?? '—' }}
            </div>
            <div class="to">${{ number_format($p->total_amount, 2, ',', '.') }}</div>
            <i class="fa-solid fa-chevron-right fl"></i>
        </a>
        @endforeach
    </div>
</section>

<style>
.sommy-account-card {
    background: #fff; border: 1px solid #E7EAF2; border-radius: 20px;
    box-shadow: 0 10px 30px rgba(27,43,90,.10); padding: 34px 30px;
}
.sommy-pedido-row {
    display: flex; align-items: center; gap: 18px;
    background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
    padding: 18px 22px; margin-bottom: 12px;
    text-decoration: none !important; color: #1B2B5A;
    transition: box-shadow .15s ease, transform .12s ease;
}
.sommy-pedido-row:hover { box-shadow: 0 10px 30px rgba(27,43,90,.12); transform: translateY(-1px); color: #1B2B5A; }
.sommy-pedido-row .np { font-weight: 600; font-size: 15.5px; }
.sommy-pedido-row .fe { font-size: 12.5px; font-weight: 300; color: #6E7A96; }
.sommy-pedido-row .es {
    margin-left: auto; font-size: 12px; font-weight: 500;
    background: #E0F2FE; color: #1B2B5A; border-radius: 999px; padding: 5px 14px; white-space: nowrap;
}
.sommy-pedido-row .es.entregado { background: #DCFCE7; color: #166534; }
.sommy-pedido-row .es.anulado { background: #FBEDE6; color: #b4552d; }
.sommy-pedido-row .to { font-weight: 700; font-size: 15.5px; white-space: nowrap; }
.sommy-pedido-row .fl { color: #8A93AD; font-size: 13px; }
@media (max-width: 575px) {
    .sommy-pedido-row { flex-wrap: wrap; gap: 8px 14px; }
    .sommy-pedido-row .es { margin-left: 0; }
}
</style>

@endsection
