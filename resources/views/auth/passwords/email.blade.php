@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card" style="border:1px solid var(--sm-border);border-radius:20px;box-shadow:var(--sm-shadow);padding:10px;">
                <div class="card-body">
                    <h1 style="font-size:1.35rem;font-weight:600;text-align:center;margin-bottom:6px;">Recuperar contraseña</h1>
                    <p style="text-align:center;font-size:.9rem;color:var(--sm-text-2);margin-bottom:22px;">
                        Ingresá tu correo y te enviamos un enlace para crear una clave nueva.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert" style="border-radius:12px;">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/forgot-password') }}">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="email" style="font-weight:500;font-size:.85rem;">Correo electrónico</label>
                            <input id="email" type="email" name="email" required autocomplete="email" autofocus
                                   placeholder="tu@email.com" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   style="border-radius:12px;padding:12px 15px;height:auto;background:var(--sm-cotton);border:1px solid var(--sm-border);">
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <button type="submit" class="btn w-100"
                                style="background:var(--sm-navy);color:#fff;border-radius:999px;padding:13px;font-weight:500;box-shadow:var(--sm-shadow);">
                            Enviarme el enlace
                        </button>

                        <p class="text-center mt-3 mb-0">
                            <a href="{{ url('/login') }}" style="font-size:.85rem;">Volver a iniciar sesión</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
