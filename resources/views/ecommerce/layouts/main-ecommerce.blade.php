
<!DOCTYPE html>
<html lang="es">
  <head>
    {{-- Google Tag Manager: lo mas arriba posible en <head>, segun pide GTM --}}
    @if(config('services.gtm.container_id'))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ config('services.gtm.container_id') }}');</script>
    @endif

    {{-- Google tag (gtag.js) — Google Analytics 4, instalado manual (no via GTM) --}}
    @if(config('services.ga4.measurement_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga4.measurement_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.ga4.measurement_id') }}');
    </script>
    @endif
    @php
        $seoTituloDefault = 'Sommy — Fábrica de colchones y sommiers en Córdoba';
        $seoDescDefault = 'Fabricamos colchones, sommiers, almohadas y sábanas en Córdoba. Comprá online con envíos a toda la ciudad. Directo de fábrica, sin intermediarios.';
        $seoKeywordsDefault = 'colchones Córdoba, colchones a medida, fábrica de colchones, sommiers Córdoba, colchones y sommiers, almohadas, sábanas, Sommy colchones, comprar colchón online Córdoba';
        $seoImagenDefault = asset('imagenes/marca/sommy-hero-poster-h.jpg');
    @endphp
    <title>@yield('meta_title', $seoTituloDefault)</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="Sommy">
    <meta name="robots" content="index, follow">
    <meta name="keywords" content="@yield('meta_keywords', $seoKeywordsDefault)">
    <meta name="description" content="@yield('meta_description', $seoDescDefault)">
    <link rel="canonical" href="@yield('meta_canonical', url()->current())">

    {{-- Open Graph / Facebook e Instagram --}}
    <meta property="og:type" content="@yield('meta_og_type', 'website')">
    <meta property="og:site_name" content="Sommy">
    <meta property="og:locale" content="es_AR">
    <meta property="og:url" content="@yield('meta_canonical', url()->current())">
    <meta property="og:title" content="@yield('meta_title', $seoTituloDefault)">
    <meta property="og:description" content="@yield('meta_description', $seoDescDefault)">
    <meta property="og:image" content="@yield('meta_imagen', $seoImagenDefault)">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('meta_title', $seoTituloDefault)">
    <meta name="twitter:description" content="@yield('meta_description', $seoDescDefault)">
    <meta name="twitter:image" content="@yield('meta_imagen', $seoImagenDefault)">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('imagenes/marca/sommy-favicon.svg') }}">

    {{-- Datos estructurados: negocio local (ayuda a aparecer con dirección/teléfono en Google) --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FurnitureStore",
        "name": "Sommy",
        "image": "{{ $seoImagenDefault }}",
        "url": "{{ url('/') }}",
        "telephone": "{{ $arrayEmpresa['phone'] ?? '' }}",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Córdoba",
            "addressRegion": "Córdoba",
            "addressCountry": "AR"
        },
        "areaServed": "Córdoba, Argentina",
        "priceRange": "$$"
    }
    </script>
    @yield('meta_structured_data')

    {{-- Microsoft Clarity: mapas de calor + grabaciones de sesion (gratis, no toca nuestro server) --}}
    @if(config('services.clarity.project_id'))
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "{{ config('services.clarity.project_id') }}");
    </script>
    @endif

    {{-- Meta Pixel: eventos de navegacion/compra para Ads. Ver ViewContent/AddToCart/
         InitiateCheckout/Purchase en las vistas puntuales del ecommerce. --}}
    @if(config('services.meta_ads.pixel_id'))
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ config('services.meta_ads.pixel_id') }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ config('services.meta_ads.pixel_id') }}&ev=PageView&noscript=1"
    /></noscript>
    @endif

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
    {{-- Tipografías Sommy: Lora (titulares, precios) + Poppins (cuerpo, fichas, formularios) + Titan One (piezas promocionales/oferta) --}}
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500&family=Poppins:wght@300;400;500&family=Titan+One&display=swap" rel="stylesheet">

  </head>
  <body class="@yield('bodyClass')">
    @if(config('services.gtm.container_id'))
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.gtm.container_id') }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

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