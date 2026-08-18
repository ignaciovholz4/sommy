
<!DOCTYPE html>
<html lang="es">
  <head>
    <title>@yield('meta_title', 'Sommy')</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="@yield('meta_description', '')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('imagenes/marca/sommy-favicon.svg') }}">

    {{-- AOS: animaciones al hacer scroll (alojado localmente, carga async e init resiliente) --}}
    <link rel="stylesheet" href="{{ assetv('css/ecommerce/aos.css') }}">
    <script src="{{ assetv('js/ecommerce/aos.js') }}" async></script>
    <script>
        (function () {
            var intentos = 0;
            var timer = setInterval(function () {
                if (window.AOS && document.body) {
                    clearInterval(timer);
                    AOS.init({
                        duration: 700,
                        easing: 'ease-out-cubic',
                        once: true,
                        offset: 60
                    });
                } else if (++intentos > 80) {
                    clearInterval(timer);
                }
            }, 150);
        })();
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <!--library fancybox-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" rel="stylesheet">
    <!--link rel="stylesheet" type="text/css" href="css/vendor.css">-->
    <!--link rel="stylesheet" type="text/css" href="style.css">-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link rel="stylesheet" type="text/css" href="{{ assetv('css/ecommerce/vendor.css') }}">
    <link rel="stylesheet" href="{{ assetv('css/ecommerce/main_ecommerce.css') }}">
    <link rel="stylesheet" href="{{ assetv('css/ecommerce/sommy-brand.css') }}">

    <link rel="stylesheet" href="{{ assetv('css/ecommerce/icon-fontawesome/all.min.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Tipografías Sommy: Lora (titulares, precios) + Poppins (cuerpo, fichas, formularios) --}}
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

  </head>
  <body class="@yield('bodyClass')">


    <div class="preloader-wrapper" id="ec-preloader">
        <div class="preloader"></div>
    </div>
    <script>
        window.addEventListener('load', function() {
            var p = document.getElementById('ec-preloader');
            if (p) { p.style.opacity = '0'; setTimeout(function(){ p.style.display='none'; }, 300); }
        });
    </script>

    <!-- START SHOW SHOPPING CARD -->
    @include('ecommerce.layouts.show-shopping-card')
    <!-- END SHOW SHOPPING CARD -->
    <!--https://www.vitra.com/en-lp/product/details/suita-ottoman-and-daybed-->
    <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasSearch" aria-labelledby="Search">
      <div class="offcanvas-header justify-content-center">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <div class="order-md-last">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-primary">Buscar</span>
          </h4>
          <form role="search" action="{{ url('/buscar') }}" method="get" class="d-flex mt-3 gap-0">
            <input class="form-control rounded-start rounded-0 bg-light" type="search" name="q" placeholder="¿Qué estás buscando?" aria-label="¿Qué estás buscando?" required>
            <button class="btn btn-dark rounded-end rounded-0" type="submit">Buscar</button>
          </form>
        </div>
      </div>
    </div>

    <!-- START HEADER -->
    @include('ecommerce.layouts.header')
    <!-- END HEADER -->

    <!--START MAIN SECTION CONTENT-->
    <main class="main-content-ecommerce">
      @yield('contentEcommerce')
    </main>
    <!--END MAIN SECTION CONTENT-->

    

    <!--START FOOTER-->
    @include('ecommerce.layouts.footer')
    <!--END FOOTER-->

    {{-- Música de ambiente: intenta sonar al entrar; si el navegador la bloquea,
         arranca con la primera interacción. Botón para silenciar (recuerda la elección). --}}
    <audio id="sommyMusica" src="{{ asset('audio/sommy-tema.mp3') }}" loop preload="auto"></audio>
    <span class="sommy-musica-hint" id="sommyMusicaHint">✨ Tocá y viví la magia completa</span>
    <button type="button" id="sommyMusicaBtn" class="sommy-musica-btn" aria-label="Música de ambiente">
        <i class="fa-solid fa-music"></i>
    </button>
    <script>
    (function () {
        var audio = document.getElementById('sommyMusica');
        var btn = document.getElementById('sommyMusicaBtn');
        if (!audio || !btn) return;

        audio.volume = 0.18;
        var apagada = localStorage.getItem('sommyMusicaOff') === '1';

        // ── Continuidad entre páginas: retomar desde donde quedó ──
        var posGuardada = parseFloat(sessionStorage.getItem('sommyMusicaPos') || '0');
        function aplicarPosicion() {
            if (posGuardada > 0 && posGuardada < (audio.duration || 9999)) {
                try { audio.currentTime = posGuardada; } catch (e) {}
            }
        }
        if (audio.readyState >= 1) { aplicarPosicion(); }
        else { audio.addEventListener('loadedmetadata', aplicarPosicion, { once: true }); }

        // Guardar la posición constantemente (y al salir de la página)
        audio.addEventListener('timeupdate', function () {
            sessionStorage.setItem('sommyMusicaPos', String(audio.currentTime));
        });
        window.addEventListener('pagehide', function () {
            sessionStorage.setItem('sommyMusicaPos', String(audio.currentTime));
        });

        var hint = document.getElementById('sommyMusicaHint');
        function pintar() {
            btn.innerHTML = audio.paused
                ? '<i class="fa-solid fa-music"></i><span class="tachado"></span>'
                : '<i class="fa-solid fa-music"></i>';
            btn.classList.toggle('sonando', !audio.paused);
            // El globito invita mientras la música no suena
            if (hint) hint.classList.toggle('oculto', !audio.paused ? true : apagada);
        }

        function intentarReproducir() {
            if (apagada) { pintar(); return; }
            audio.play().then(pintar).catch(function () {
                // Bloqueada por el navegador: arrancar con la primera interacción
                var arrancar = function () {
                    if (localStorage.getItem('sommyMusicaOff') === '1') return limpiar();
                    audio.play().then(pintar).catch(function () {});
                    limpiar();
                };
                var limpiar = function () {
                    ['click', 'touchstart', 'scroll', 'keydown'].forEach(function (ev) {
                        window.removeEventListener(ev, arrancar);
                    });
                };
                ['click', 'touchstart', 'scroll', 'keydown'].forEach(function (ev) {
                    window.addEventListener(ev, arrancar, { once: false, passive: true });
                });
                pintar();
            });
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (audio.paused) {
                apagada = false;
                localStorage.setItem('sommyMusicaOff', '0');
                audio.play().then(pintar).catch(function () {});
            } else {
                apagada = true;
                localStorage.setItem('sommyMusicaOff', '1');
                audio.pause();
                pintar();
            }
        });

        intentarReproducir();
    })();
    </script>

    {{-- Botón flotante de WhatsApp (acceso rápido en toda la tienda) --}}
    @if(!empty($arrayEmpresa['phone']))
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $arrayEmpresa['whatsapp']) }}"
       target="_blank" rel="noopener noreferrer"
       class="sommy-wsp-flotante" aria-label="Escribinos por WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    @endif

    <script src="{{asset('js/ecommerce/jquery.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <!--library fancybox-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
    <!--script src="js/plugins.js"></script>-->
    <!--script src="js/ecommerce/script.js"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="{{ assetv('js/ecommerce/script.js') }}"></script>
    <script src="{{ assetv('js/ecommerce/plugins.js') }}"></script>
    <script src="{{ assetv('js/ecommerce/main_ecommerce.js') }}"></script>

    @yield('scriptEcommerce')
  </body>
</html>