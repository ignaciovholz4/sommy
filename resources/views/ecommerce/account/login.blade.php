@extends('ecommerce.layouts.main-ecommerce')
@section('meta_title', 'Iniciar sesión | Sommy')
@section('contentEcommerce')

<section class="py-5">
    <div class="container" style="max-width:460px;">
        <div class="sommy-account-card">
            <h1>Iniciá sesión</h1>
            <p class="sub">Para completar tu compra necesitás una cuenta.</p>

            @if($errors->any())
                <div class="alert alert-danger" style="border-radius:12px;font-size:13.5px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/cuenta/login') }}">
                @csrf
                <input type="hidden" name="next" value="{{ $next ?? '/' }}">

                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" required autocomplete="email" autofocus
                       placeholder="tu@email.com" value="{{ old('email') }}">

                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       placeholder="••••••••">

                <button type="submit" class="btn-sommy-primary w-100" style="border:none;cursor:pointer;">Ingresar</button>
            </form>

            <p class="alt">
                ¿Primera vez que comprás?
                <a href="{{ url('/cuenta/registro') }}?next={{ urlencode($next ?? '/') }}">Creá tu cuenta</a>
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
