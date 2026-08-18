@extends('layouts.app')

@section('content')
<style>
    .card-login {
        background: #ffffff !important;
        border: 1px solid var(--sm-border) !important;
        border-radius: 20px !important;
        padding: 24px;
        box-shadow: var(--sm-shadow) !important;
    }

    .login-logo-wrap {
        text-align: center;
        margin-bottom: 6px;
    }
    .login-logo-wrap img {
        max-width: 220px;
        width: 70%;
        height: auto;
    }

    .card-header-farg {
        background: transparent !important;
        border: none !important;
        color: var(--sm-navy) !important;
        font-family: 'Poppins', sans-serif;
        font-weight: 600 !important;
        text-align: center;
        font-size: 1.45rem;
        padding-bottom: 0;
    }

    .login-subtitle {
        text-align: center;
        color: var(--sm-text-2);
        font-size: .9rem;
        font-weight: 300;
        margin: 6px 0 0;
    }

    .form-control-farg {
        background: var(--sm-cotton) !important;
        border: 1px solid var(--sm-border) !important;
        border-radius: 12px !important;
        color: var(--sm-navy) !important;
        padding: 12px 15px !important;
        height: auto !important;
        font-family: 'Poppins', sans-serif;
    }

    .form-control-farg:focus {
        border-color: var(--sm-blue) !important;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, .12) !important;
        background: #fff !important;
    }

    label { color: var(--sm-navy) !important; font-weight: 500; font-size: 0.85rem; }

    /* Botón primario Sommy: pill azul noche, hover Azul Confort */
    .btn-farg-primary {
        background: var(--sm-navy);
        color: #ffffff !important;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 1rem;
        border: none;
        border-radius: 999px;
        padding: 14px;
        width: 100%;
        box-shadow: var(--sm-shadow);
        transition: background .2s ease, transform .1s ease;
    }
    .btn-farg-primary:hover {
        background: var(--sm-blue);
        color: #fff !important;
        transform: translateY(-1px);
    }

    .btn-link-farg {
        color: var(--sm-blue) !important;
        font-size: 0.85rem;
        text-decoration: none !important;
    }
    .btn-link-farg:hover { color: var(--sm-navy) !important; }

    .form-check-label { color: var(--sm-text-2) !important; font-size: .85rem; }
    .form-check-input:checked { background-color: var(--sm-navy); border-color: var(--sm-navy); }

    .login-footer-note {
        color: #8A93AD;
        font-size: 0.8rem;
        font-family: 'Poppins', sans-serif;
        font-weight: 300;
    }
    .login-footer-note .firma {
        font-family: 'Poppins', sans-serif;
        font-style: italic;
        color: var(--sm-navy);
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-login">
                {{-- Logotipo maestro Sommy --}}
                <div class="login-logo-wrap">
                    <img src="{{ asset('imagenes/marca/sommy-logo-magia.png') }}" alt="Sommy">
                </div>
                <div class="card-header card-header-farg">{{ __('Bienvenido de nuevo') }}</div>
                <p class="login-subtitle">Ingresá a tu panel de gestión</p>

                <div class="card-body mt-3">
                    {{-- Rama single-tenant: esta instalación es siempre el tenant --}}
                    @php($tenantSlugLogin = true)
                    <form method="POST" action="{{ $tenantSlugLogin ? route('tenant.login.post') : route('central.login.post') }}">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="email">{{ __('Correo electrónico') }}</label>
                            <input id="email" type="email" class="form-control form-control-farg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="tu@email.com">
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="password">{{ __('Contraseña') }}</label>
                            <input id="password" type="password" class="form-control form-control-farg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                            @error('password')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Recordarme') }}
                                </label>
                            </div>
                            @if (Route::has('password.request'))
                                <a class="btn-link-farg" href="{{ url('/forgot-password') }}">
                                    {{ __('¿Olvidaste la clave?') }}
                                </a>
                            @endif
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-farg-primary">
                                {{ __('Ingresar') }} <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <p class="text-center mt-4 login-footer-note">
                <span class="firma">"Liviano como una pluma."</span><br>
                Sommy &copy; {{ date('Y') }} — El arte del descanso liviano, sereno y confortable.
            </p>
        </div>
    </div>
</div>
@endsection
