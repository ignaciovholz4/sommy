@extends('connect.master')
@section('content')
<div class="login-box style-login shadow-lg p-3 bg-body rounded">
  <div class=" row justify-content-center">
    <div class="justify-content-center brand-link text-center">
        <img src="{{ asset('imagenes/marca/sommy-logo-magia.png') }}" alt="Sommy"
            style="max-width: 220px; width: 100%; height: auto;">
    </div>
  </div>
  <br>
  <div class="card">
    <div class="card-body login-card-body style-login">
      <p class="login-box-msg">Verificación en dos pasos</p>
      <p class="text-muted small">Ingresá el código de tu app de autenticación para <strong>{{ $email }}</strong>, o un código de recuperación.</p>

      @if($errors->any())
        <div class="alert alert-danger">
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf
        <div class="input-group mb-3">
          <input type="text" name="code" class="form-control style-input" placeholder="Código de 6 dígitos o de recuperación" autocomplete="one-time-code" autofocus required>
        </div>
        <div class="container">
          <button type="submit" class="btn btn-login btn-block">Verificar</button>
        </div>
      </form>

      <p class="mb-0 mt-3 text-center">
        <a href="{{ route('two-factor.cancel') }}">Cancelar e iniciar sesión de nuevo</a>
      </p>
    </div>
  </div>
</div>
<style>
  .style-body{
    background:
      radial-gradient(ellipse at 15% -10%, #E0F2FE 0%, rgba(224,242,254,0) 55%),
      radial-gradient(ellipse at 100% 110%, rgba(224,242,254,.8) 0%, rgba(224,242,254,0) 50%),
      #F8FAFC !important;
    font-family: 'Poppins', sans-serif;
  }
  .style-login{
    background-color: #ffffff;
    color: #5D6884 !important;
    border-radius: 20px;
  }
  .login-box .card{
    border: 1px solid #E7EAF2;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(27,43,90,.10);
  }
  .login-box-msg{
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 1.25rem;
    color: #1B2B5A;
  }
  .style-input{
      background-color: #F8FAFC !important;
      border: 1px solid #E7EAF2 !important;
      border-radius: 12px !important;
      color: #1B2B5A !important;
  }
  .btn-login{
    background-color: #1B2B5A;
    color: #ffffff !important;
    border-radius: 999px;
    font-weight: 500;
    padding: 12px;
    box-shadow: 0 10px 30px rgba(27,43,90,.10);
  }
  .btn-login:hover{ background-color: #2563EB; color: #ffffff; }
  .login-card-body a{ color: #2563EB; }
  .shadow-lg{ box-shadow: none !important; }
  .bg-body{ background: transparent !important; }
</style>
@stop
