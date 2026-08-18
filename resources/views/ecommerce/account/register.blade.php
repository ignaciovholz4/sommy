@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Crear cuenta | Sommy')
@section('contentEcommerce')

<section class="py-5">
    <div class="container" style="max-width:460px;">
        <div class="sommy-account-card">
            <h1>Creá tu cuenta</h1>
            <p class="sub">Es tu primera compra: registrate en un minuto y seguí con tu pedido.</p>

            @if($errors->any())
                <div class="alert alert-danger" style="border-radius:12px;font-size:13.5px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/cuenta/registro') }}">
                @csrf
                <input type="hidden" name="next" value="{{ $next ?? '/' }}">

                <label for="nombre">Nombre y apellido</label>
                <input id="nombre" type="text" name="nombre" required autofocus
                       placeholder="Ej: Ana Pérez" value="{{ old('nombre') }}">

                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" required autocomplete="email"
                       placeholder="tu@email.com" value="{{ old('email') }}">

                <label for="telefono">Teléfono / WhatsApp (opcional)</label>
                <input id="telefono" type="tel" name="telefono"
                       placeholder="Ej: 3511234567" value="{{ old('telefono') }}">

                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       placeholder="Mínimo 8 caracteres">

                <label for="password_confirmation">Repetir contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       placeholder="••••••••">

                <button type="submit" class="btn-sommy-primary w-100" style="border:none;cursor:pointer;">Crear cuenta y continuar</button>
            </form>

            <p class="alt">
                ¿Ya tenés cuenta?
                <a href="{{ url('/cuenta/login') }}?next={{ urlencode($next ?? '/') }}">Iniciá sesión</a>
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
.sommy-account-card label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #1B2B5A;
    margin: 14px 0 5px;
}
.sommy-account-card input:not([type=hidden]) {
    width: 100%;
    border: 1px solid #E7EAF2;
    background: #F8FAFC;
    border-radius: 12px;
    padding: 11px 14px;
    font-size: 14px;
    color: #1B2B5A;
}
.sommy-account-card input:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 .2rem rgba(37,99,235,.12);
    background: #fff;
}
.sommy-account-card button { margin-top: 22px; width: 100%; }
.sommy-account-card .alt {
    text-align: center;
    font-size: 13.5px;
    color: #47536F;
    margin: 18px 0 0;
}
.sommy-account-card .alt a { color: #2563EB; font-weight: 500; text-decoration: none; }
</style>

@endsection
