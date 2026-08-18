@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Botón de arrepentimiento | Sommy')
@section('contentEcommerce')

<section class="py-5">
    <div class="container" style="max-width:640px;">
        <h1 style="font-size:26px;font-weight:600;margin-bottom:8px;">Botón de arrepentimiento</h1>
        <p style="font-weight:300;color:#47536F;line-height:1.7;margin-bottom:24px;">
            Si compraste en nuestra tienda online podés arrepentirte de la compra dentro de los
            <strong>10 días corridos</strong> desde la entrega, según la Ley de Defensa del Consumidor (art. 34, Ley 24.240).
            Completá este formulario y te contactamos para gestionar la devolución <strong>sin costo alguno</strong>.
        </p>

        @if(session('arrepentimiento_ok'))
            <div class="alert alert-success" style="border-radius:14px;">
                <strong>Recibimos tu solicitud.</strong> Te vamos a contactar dentro de las próximas 24 hs hábiles para coordinar la devolución.
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="border-radius:14px;">{{ $errors->first() }}</div>
        @endif

        <div class="sommy-account-card">
            <form method="POST" action="{{ url('/arrepentimiento') }}">
                @csrf
                <label for="nombre">Nombre y apellido *</label>
                <input id="nombre" type="text" name="nombre" required value="{{ old('nombre') }}">

                <label for="email">Correo electrónico *</label>
                <input id="email" type="email" name="email" required value="{{ old('email') }}">

                <label for="telefono">Teléfono</label>
                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono') }}">

                <label for="pedido">Número de pedido (si lo tenés)</label>
                <input id="pedido" type="text" name="pedido" placeholder="Ej: #12" value="{{ old('pedido') }}">

                <label for="motivo">Contanos brevemente el motivo (opcional)</label>
                <textarea id="motivo" name="motivo" rows="4">{{ old('motivo') }}</textarea>

                <button type="submit" class="btn-sommy-primary w-100" style="border:none;cursor:pointer;margin-top:22px;">Enviar solicitud</button>
            </form>
        </div>
    </div>
</section>

<style>
.sommy-account-card {
    background: #fff; border: 1px solid #E7EAF2; border-radius: 20px;
    box-shadow: 0 10px 30px rgba(27,43,90,.10); padding: 30px 28px;
}
.sommy-account-card label { display: block; font-size: 13px; font-weight: 500; color: #1B2B5A; margin: 14px 0 5px; }
.sommy-account-card input, .sommy-account-card textarea {
    width: 100%; border: 1px solid #E7EAF2; background: #F8FAFC;
    border-radius: 12px; padding: 11px 14px; font-size: 14px; color: #1B2B5A;
}
.sommy-account-card input:focus, .sommy-account-card textarea:focus {
    outline: none; border-color: #2563EB; box-shadow: 0 0 0 .2rem rgba(37,99,235,.12); background: #fff;
}
</style>

@endsection
