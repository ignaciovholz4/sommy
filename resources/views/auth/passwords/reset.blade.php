@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card" style="border:1px solid var(--sm-border);border-radius:20px;box-shadow:var(--sm-shadow);padding:10px;">
                <div class="card-body">
                    <h1 style="font-size:1.35rem;font-weight:600;text-align:center;margin-bottom:6px;">Crear nueva contraseña</h1>
                    <p style="text-align:center;font-size:.9rem;color:var(--sm-text-2);margin-bottom:22px;">
                        Elegí una clave nueva de al menos 8 caracteres.
                    </p>

                    <form method="POST" action="{{ url('/reset-password') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group mb-3">
                            <label for="email" style="font-weight:500;font-size:.85rem;">Correo electrónico</label>
                            <input id="email" type="email" name="email" required autocomplete="email"
                                   value="{{ $email ?? old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   style="border-radius:12px;padding:12px 15px;height:auto;background:var(--sm-cotton);border:1px solid var(--sm-border);">
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password" style="font-weight:500;font-size:.85rem;">Nueva contraseña</label>
                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                   placeholder="••••••••"
                                   class="form-control @error('password') is-invalid @enderror"
                                   style="border-radius:12px;padding:12px 15px;height:auto;background:var(--sm-cotton);border:1px solid var(--sm-border);">
                            @error('password')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="password-confirm" style="font-weight:500;font-size:.85rem;">Repetir contraseña</label>
                            <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password"
                                   placeholder="••••••••" class="form-control"
                                   style="border-radius:12px;padding:12px 15px;height:auto;background:var(--sm-cotton);border:1px solid var(--sm-border);">
                        </div>

                        <button type="submit" class="btn w-100"
                                style="background:var(--sm-navy);color:#fff;border-radius:999px;padding:13px;font-weight:500;box-shadow:var(--sm-shadow);">
                            Guardar nueva contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
