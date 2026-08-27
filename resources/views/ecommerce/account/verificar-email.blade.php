@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Confirmá tu correo | Sommy')
@section('contentEcommerce')

<section class="py-5">
    <div class="container" style="max-width:460px;">
        <div class="sommy-account-card" style="text-align:center;">
            <div style="font-size:40px;color:#2563EB;margin-bottom:10px;">✅</div>
            <h1>Confirmá tu correo</h1>
            <p class="sub">
                Te mandamos un enlace a <strong>{{ Auth::guard('cliente')->user()->email }}</strong>.
                Abrilo para poder finalizar tu compra.
            </p>

            @if(session('reenviado'))
                <div class="alert alert-success" style="border-radius:12px;font-size:13.5px;">
                    Te reenviamos el correo de verificación ✅
                </div>
            @endif

            <form method="POST" action="{{ url('/cuenta/verificar-email/reenviar') }}">
                @csrf
                <button type="submit" class="btn-sommy-primary w-100" style="border:none;cursor:pointer;">Reenviar correo</button>
            </form>

            <p class="alt">
                ¿Ya lo confirmaste? <a href="{{ url('/Ecommerceorder') }}">Volver a la compra</a>
            </p>
        </div>
    </div>
</section>

<style>
.sommy-account-card {
    background: #fff;
    border: 1px solid #E7EAF2;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(27,43,90,.10);
    padding: 34px 30px;
}
.sommy-account-card h1 {
    font-size: 24px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 4px;
}
.sommy-account-card .sub {
    text-align: center;
    font-size: 14px;
    color: #47536F;
    font-weight: 300;
    margin-bottom: 22px;
}
.sommy-account-card button { margin-top: 6px; width: 100%; }
.sommy-account-card .alt {
    text-align: center;
    font-size: 13.5px;
    color: #47536F;
    margin: 18px 0 0;
}
.sommy-account-card .alt a { color: #2563EB; font-weight: 500; text-decoration: none; }
</style>

@endsection
