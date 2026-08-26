@extends('connect.master')
{{-- @section('title', 'Login') --}}
@section('content')
<div class="login-box style-login shadow-lg p-3 bg-body rounded"> 
  <!--div class="login-logo">
    <a href="../../index2.html"></a>
  </div-->
  <div class=" row justify-content-center">
    <div class="justify-content-center brand-link text-center">
        {{-- Logotipo maestro Sommy: usarlo siempre tal cual --}}
        <img src="{{ asset('imagenes/marca/sommy-logo-magia.png') }}" alt="Sommy"
            style="max-width: 220px; width: 100%; height: auto;">
    </div>
  </div>
  <br>
  <!-- /.login-logo -->
  <div class="card ">
    <div class="card-body login-card-body style-login">
      <p class="login-box-msg">Ingresá con tu cuenta</p>
      <!--inicio del formulario-->
      {!! Form::open(['url' => request()->getSchemeAndHttpHost() . '/login','autocomplete'=>'off'])!!}
      {{-- <form action="../../index3.html" method="post"> --}}
        <div class="input-group mb-3">
          {{-- <input type="email" class="form-control" placeholder="Email"> --}}
          {!! Form::email('email',null,['class'=>'form-control style-input','placeholder'=>'Correo']) !!}
          <div class="input-group-append">
            <div class="input-group-text style-icon-fas">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          {{-- <input type="password" class="form-control" placeholder="Password"> --}}
          {!! Form::password('password',['class'=>'form-control style-input','placeholder'=>'password']) !!}
          <div class="input-group-append">
            <div class="input-group-text style-icon-fas">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12 mb-2">
            <div class="icheck-primary">
              <input type="checkbox" name="remember" id="remember" value="1">
              <label for="remember">
                Mantener sesión iniciada
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="container">
            {{-- <button type="submit" class="btn btn-primary btn-block">Sign In</button> --}}
            {!! Form::submit('Ingresar',['class'=>'btn btn-login btn-block']) !!}
          </div>
          <!-- /.col -->
        </div>
      {{-- </form> --}}
      {!! Form::close()!!}
      @if(Session::has('message'))
      <div class="container"><br>
        <div class="alert alert-{{ Session::get('typealert') }}" id="loginAlert">
        {{ Session::get('message') }}
          @if($errors->any())
            <ul>
              @foreach($errors->all() as $error )
                <li>{{$error}}</li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
      <script>
      setTimeout(function(){
        var el = document.getElementById('loginAlert');
        if (el) { el.style.display = 'none'; }
      }, 5000);
      </script>
      @endif
      <!--fin del formulario-->  
      {{-- <div class="social-auth-links text-center mb-3">
        <p>- OR -</p>
        <a href="#" class="btn btn-block btn-primary">
          <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
        </a>
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
        </a>
      </div> --}}
      <!-- /.social-auth-links -->

         <p class="mb-1">
        <a href="{{ request()->getSchemeAndHttpHost() . '/forgot-password' }}">Forgot your password?</a>
      </p>
      <p class="mb-1">
        <a href="{{ request()->getSchemeAndHttpHost() . '/email/verify' }}">Resend verification email</a>
      </p>
      <p class="mb-0">
        {{-- <a href="{{ url('/register') }}" class="text-center">Registrar un nuevo usuario</a> --}}
      </p>
      @if(Auth::check())
        <div class="mt-3">
          <a href="{{ request()->getSchemeAndHttpHost() . '/logout' }}" class="btn btn-sm btn-outline-primary btn-block" style="border-color:#1B2B5A;color:#1B2B5A;border-radius:999px;">Cerrar sesión</a>
        </div>
      @endif
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->
<style>
  /* Identidad Sommy: Blanco Algodón + Brisa Suave, tarjeta serena */
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
      border-radius: 12px 0 0 12px !important;
      color: #1B2B5A !important;
  }
  .style-input:focus{
      border-color: #2563EB !important;
      box-shadow: 0 0 0 .2rem rgba(37,99,235,.12) !important;
      background-color: #fff !important;
  }
  .style-icon-fas{
      background-color: #F8FAFC !important;
      border: 1px solid #E7EAF2 !important;
      border-left: none !important;
      border-radius: 0 12px 12px 0 !important;
      color: #8A93AD !important;
  }
  .btn-login{
    background-color: #1B2B5A;
    color: #ffffff !important;
    border-radius: 999px;
    font-weight: 500;
    padding: 12px;
    box-shadow: 0 10px 30px rgba(27,43,90,.10);
    transition: background .2s ease;
  }
  .btn-login:hover{
    background-color: #2563EB;
    color: #ffffff;
  }
  .login-card-body a{ color: #2563EB; }
  .login-card-body a:hover{ color: #1B2B5A; }
  .shadow-lg{ box-shadow: none !important; }
  .bg-body{ background: transparent !important; }
</style>
@stop