@extends('layouts.admin')

@section('contenido')
<div class="container-fluid" style="max-width: 720px; padding: 2rem 1.5rem;">
    <div class="card shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-4">
            <h4 class="mb-1"><i class="fas fa-shield-alt mr-2"></i>Verificación en dos pasos</h4>
            <p class="text-muted">Sumá una capa extra de seguridad a tu cuenta pidiendo un código de tu celular además de la contraseña.</p>

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('recoveryCodes'))
                <div class="alert alert-warning">
                    <strong>Guardá estos códigos de recuperación en un lugar seguro.</strong>
                    <p class="mb-2">Cada uno sirve para entrar una sola vez si perdés el acceso a tu app de autenticación. No se van a volver a mostrar.</p>
                    <div class="d-flex flex-wrap" style="gap:8px;">
                        @foreach(session('recoveryCodes') as $recoveryCode)
                            <code style="background:#fff;border:1px solid #E7EAF2;border-radius:8px;padding:4px 10px;">{{ $recoveryCode }}</code>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($enabled)
                <div class="alert alert-info"><i class="fas fa-check-circle mr-1"></i> La verificación en dos pasos está activada en tu cuenta.</div>

                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf
                    <div class="form-group">
                        <label>Confirmá tu contraseña para desactivarla</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-danger">Desactivar verificación en dos pasos</button>
                </form>

            @elseif($qrDataUri)
                <p>Escaneá este código QR con Google Authenticator, Authy u otra app compatible, y después ingresá el código de 6 dígitos que te muestra.</p>
                <div class="text-center mb-3">
                    <img src="{{ $qrDataUri }}" alt="Código QR 2FA" style="max-width: 220px;">
                </div>
                <p class="text-muted small">¿No podés escanear? Ingresá esta clave manualmente: <code>{{ $pendingSecret }}</code></p>

                <form method="POST" action="{{ route('two-factor.confirm') }}" class="form-inline">
                    @csrf
                    <input type="text" name="code" class="form-control mr-2" placeholder="Código de 6 dígitos" autocomplete="one-time-code" required>
                    <button type="submit" class="btn btn-primary">Confirmar y activar</button>
                </form>

            @else
                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Activar verificación en dos pasos</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
