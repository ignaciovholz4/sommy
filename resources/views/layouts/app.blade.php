<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sommy') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('imagenes/marca/sommy-favicon.svg') }}">

    <script src="{{ asset('js/app.js') }}" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    {{-- Tipografías Sommy: Lora (voz de marca) + Poppins (información) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        :root {
            --sm-navy:   #1B2B5A;
            --sm-blue:   #2563EB;
            --sm-aqua:   #0EA5E9;
            --sm-breeze: #E0F2FE;
            --sm-cotton: #F8FAFC;
            --sm-border: #E7EAF2;
            --sm-text-2: #5D6884;
            --sm-shadow: 0 10px 30px rgba(27, 43, 90, .10);
        }

        body {
            background:
                radial-gradient(ellipse at 15% -10%, var(--sm-breeze) 0%, rgba(224, 242, 254, 0) 55%),
                radial-gradient(ellipse at 100% 110%, rgba(224, 242, 254, .8) 0%, rgba(224, 242, 254, 0) 50%),
                var(--sm-cotton) !important;
            font-family: 'Poppins', sans-serif;
            color: var(--sm-text-2);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Poppins', sans-serif;
            color: var(--sm-navy);
        }

        /* Destellos ✦ serenos de fondo (máx. 3 por vista) */
        .sommy-sparkle {
            position: fixed;
            color: var(--sm-aqua);
            opacity: .35;
            z-index: 0;
            pointer-events: none;
            font-size: 22px;
        }
        .sommy-sparkle--1 { top: 18%; left: 8%; }
        .sommy-sparkle--2 { top: 62%; right: 10%; font-size: 15px; color: var(--sm-blue); }
        .sommy-sparkle--3 { bottom: 10%; left: 22%; font-size: 12px; }

        /* Navbar: blanco algodón, sereno */
        .navbar-farg {
            background: rgba(255, 255, 255, .85) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--sm-border);
        }

        .navbar-brand img { display: block; }

        .nav-link {
            color: var(--sm-navy) !important;
            font-weight: 500;
            font-size: .95rem;
            border-radius: 999px;
            padding: 8px 18px !important;
            transition: background .15s, color .15s;
        }
        .nav-link:hover {
            background: var(--sm-breeze);
            color: var(--sm-blue) !important;
        }

        .dropdown-menu {
            border: 1px solid var(--sm-border);
            border-radius: 14px;
            box-shadow: var(--sm-shadow);
        }
        .dropdown-item { color: var(--sm-navy); font-family: 'Poppins', sans-serif; }
        .dropdown-item:hover { background: var(--sm-cotton); color: var(--sm-blue); }

        .py-4 { padding-top: 3rem !important; padding-bottom: 3rem !important; }
    </style>
</head>
<body>
    <span class="sommy-sparkle sommy-sparkle--1">✦</span>
    <span class="sommy-sparkle sommy-sparkle--2">✦</span>
    <span class="sommy-sparkle sommy-sparkle--3">✦</span>

    <div id="app">
        <nav class="navbar navbar-expand-md navbar-farg">
            <div class="container">
                {{-- Logotipo maestro Sommy: usarlo siempre tal cual, nunca recomponerlo --}}
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('imagenes/marca/sommy-logo-magia.png') }}" height="52" alt="Sommy">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                        {{-- Rama single-tenant: esta instalación es siempre el tenant --}}
                        @php($onTenantSubdomain = true)
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ $onTenantSubdomain ? url('/login') : route('central.login') }}">{{ __('Login') }}</a>
                            </li>
                            @if ($onTenantSubdomain)
                                {{-- Public self-registration GET route is not defined for tenants; admins add users inside the panel. --}}
                            @elseif (Route::has('landlord.register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('landlord.register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    @if ($onTenantSubdomain)
                                        <a class="dropdown-item" href="{{ url('/logout') }}">
                                            {{ __('Logout') }}
                                        </a>
                                    @else
                                        <a class="dropdown-item" href="{{ route('central.logout') }}">
                                            {{ __('Logout') }}
                                        </a>
                                    @endif
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
