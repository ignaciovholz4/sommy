@extends('ecommerce.layouts.main-ecommerce')
@section('contentEcommerce')
    <style>
      .category-item{
        box-shadow: rgba(0, 0, 0, 0.05) 0px 0px 0px 1px, rgb(209, 213, 219) 0px 0px 0px 1px inset !important;
      }
    </style>

    <!--START SECCION BANNER-->
    @if($getDataBanner->isEmpty())
    {{-- Portada por defecto: se reemplaza en cuanto se carga un banner desde /banner --}}
    <section class="sommy-hero sommy-hero--centrado">
        <div class="sommy-hero-inner">
            <h1 data-aos="fade-up">Dormí liviano.<br>Despertá mejor.</h1>
            <p class="sub" data-aos="fade-up" data-aos-delay="150">Distribuimos colchones directo de fabrica, y tenemos todo lo que tu habitación necesita: colchones, almohadas, sommiers y sabanas para que cada noche sea especial.</p>
            <div class="btn-row" data-aos="fade-up" data-aos-delay="300">
                <a href="#productos" class="btn-sommy-dark">Ver productos</a>
                @if(!empty($arrayEmpresa['phone']))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $arrayEmpresa['whatsapp']) }}" target="_blank" rel="noopener noreferrer" class="btn-sommy-whatsapp">
                    <i class="fa-brands fa-whatsapp"></i> Asesorate
                </a>
                @endif
            </div>
            <p class="sommy-hero-admin-tip">
                <i class="fa-solid fa-circle-info me-1"></i>
                Esta es la portada por defecto. Cargá una imagen y un texto propio desde <a href="{{ route('banner.index') }}">Banners</a>.
            </p>
        </div>
    </section>
    @else
    <section class="sommy-banner-section">
        <div id="heroBannerCarousel" class="carousel slide sommy-banner" data-bs-ride="carousel" data-bs-interval="6000">
            <div class="carousel-inner">
                @foreach ($getDataBanner as $banner)
                  <div class="carousel-item sommy-banner-slide {{ $loop->first ? 'active' : '' }} {{ ($banner->titulo || $banner->subtitulo || $banner->boton_texto) ? '' : 'sommy-banner-slide--full' }}">
                      <div class="sommy-banner-media">
                          @if($banner->tipo === 'video')
                          <video class="sommy-banner-img desktop-img" muted loop playsinline preload="none">
                              <source data-src="{{ asset('imagenes/banner/'.$banner->name_image) }}">
                          </video>
                          <video class="sommy-banner-img mobile-img" muted loop playsinline preload="none">
                              <source data-src="{{ asset('imagenes/banner/'.($banner->name_image_movil ?: $banner->name_image)) }}">
                          </video>
                          @else
                          <img src="{{ asset('imagenes/banner/'.$banner->name_image) }}" class="sommy-banner-img desktop-img" alt="{{ $banner->titulo ?: 'Sommy' }}">
                          <img src="{{ asset('imagenes/banner/'.($banner->name_image_movil ?: $banner->name_image)) }}" class="sommy-banner-img mobile-img" alt="{{ $banner->titulo ?: 'Sommy' }}">
                          @endif
                      </div>
                      @if($banner->titulo || $banner->subtitulo || $banner->boton_texto)
                      <div class="sommy-banner-copy">
                          @if($banner->titulo) <h2>{{ $banner->titulo }}</h2> @endif
                          @if($banner->subtitulo) <p class="sub">{{ $banner->subtitulo }}</p> @endif
                          @if($banner->boton_texto)
                          <a href="{{ $banner->boton_url ?: '#productos' }}" class="btn-sommy-dark">{{ $banner->boton_texto }}</a>
                          @endif
                      </div>
                      @endif
                  </div>
                @endforeach
            </div>

            @if($getDataBanner->count() > 1)
            <div class="carousel-indicators sommy-banner-indicators">
                @foreach ($getDataBanner as $banner)
                  <button type="button" data-bs-target="#heroBannerCarousel" data-bs-slide-to="{{ $loop->index }}"
                          class="{{ $loop->first ? 'active' : '' }}"
                          aria-current="{{ $loop->first ? 'true' : 'false' }}"
                          aria-label="Slide {{ $loop->iteration }}"></button>
                @endforeach
            </div>
            <button class="carousel-control-prev sommy-banner-nav" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next sommy-banner-nav" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
            @endif
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const heroCarousel = document.getElementById('heroBannerCarousel');
            if (!heroCarousel) return;

            // Solo se carga/reproduce UN video a la vez: el de escritorio o el de mobile
            // (según el ancho de pantalla, el otro queda oculto por CSS) del slide activo
            // nada más. Antes los dos (y los de TODOS los slides) tenían autoplay+preload
            // a la vez, lo que tumbaba la carga en mobile.
            const cargarYReproducir = (video) => {
                if (!video || getComputedStyle(video).display === 'none') return;
                const source = video.querySelector('source');
                if (source && !source.src && source.dataset.src) {
                    source.src = source.dataset.src;
                    video.load();
                }
                video.play().catch(() => {});
            };

            const manejarSlide = (slide) => {
                if (!slide) return;
                slide.querySelectorAll('video.sommy-banner-img').forEach(cargarYReproducir);
            };

            const pausarSlide = (slide) => {
                if (!slide) return;
                slide.querySelectorAll('video.sommy-banner-img').forEach(v => v.pause());
            };

            manejarSlide(heroCarousel.querySelector('.carousel-item.active'));

            heroCarousel.addEventListener('slide.bs.carousel', function (e) {
                pausarSlide(e.relatedTarget?.previousElementSibling ?? null);
                heroCarousel.querySelectorAll('.carousel-item').forEach(slide => {
                    if (slide !== e.relatedTarget) pausarSlide(slide);
                });
            });
            heroCarousel.addEventListener('slid.bs.carousel', function (e) {
                manejarSlide(e.relatedTarget);
            });

            // Cambió de escritorio a mobile (o al revés): puede cambiar cuál video es el visible
            window.addEventListener('resize', function () {
                manejarSlide(heroCarousel.querySelector('.carousel-item.active'));
            });
        });
    </script>
    @endif
    <!--END SECCION BANNER-->

    <!--START MARCAS QUE TRABAJAMOS-->
    {{-- Logos ficticios de referencia: reemplazar por los logos reales de las marcas --}}
    <section class="pb-4 pt-4" style="padding-top:2.5rem !important;padding-bottom:2.5rem !important;">
      <div class="container-fluid">
        <p class="text-center" style="font-size:12px;font-weight:500;letter-spacing:.2em;text-transform:uppercase;color:#8A93AD;margin-bottom:42px;">Que vendemos</p>
        <div class="sommy-marcas">
          <div class="sommy-marcas-track">
            @for ($rep = 0; $rep < 2; $rep++)
            <span class="sommy-marca-logo sommy-marca-logo--serif"><i class="fa-solid fa-feather"></i><span class="n">Colchones</span></span>
            <span class="sommy-marca-logo sommy-marca-logo--caps"><i class="fa-solid fa-moon"></i><span class="n">Sommiers</span></span>
            <span class="sommy-marca-logo"><i class="fa-solid fa-bed"></i><span class="n">Almohadas</span></span>
            <span class="sommy-marca-logo sommy-marca-logo--serif"><i class="fa-solid fa-star"></i><span class="n">Sabanas</span></span>
            <span class="sommy-marca-logo sommy-marca-logo--serif"><i class="fa-solid fa-feather"></i><span class="n">Colchones</span></span>
            <span class="sommy-marca-logo sommy-marca-logo--caps"><i class="fa-solid fa-moon"></i><span class="n">Sommiers</span></span>
            <span class="sommy-marca-logo"><i class="fa-solid fa-bed"></i><span class="n">Almohadas</span></span>
            <span class="sommy-marca-logo sommy-marca-logo--serif"><i class="fa-solid fa-star"></i><span class="n">Sabanas</span></span>
           
            @endfor
          </div>
        </div>
      </div>
    </section>
    <!--END MARCAS QUE TRABAJAMOS-->

    @if($getReels->isNotEmpty())
    <!--CARRUSEL DE REELS DE INSTAGRAM-->
    <section class="py-5" id="reels-instagram">
      <div class="container-fluid">
        <div class="tabs-header d-flex justify-content-between border-bottom my-5" data-aos="fade-up">
          <h3>Mirá lo último en Instagram</h3>
        </div>
        <div class="sommy-reels-wrap">
          <button type="button" class="sommy-reels-arrow sommy-reels-arrow--prev" onclick="moverReelsCarrusel(-1)" aria-label="Anterior">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <div class="sommy-reels-track" id="sommyReelsTrack">
            @foreach($getReels as $reel)
              @if($reel->video_url)
              <div class="sommy-reel-card">
                <video class="sommy-reel-video" data-src="{{ $reel->video_url }}"
                       @if($reel->poster_url) poster="{{ $reel->poster_url }}" @endif
                       muted loop playsinline preload="none"></video>
                <button type="button" class="sommy-reel-sound" aria-label="Activar sonido">
                  <i class="fa-solid fa-volume-xmark"></i>
                </button>
                @if($reel->url)
                <a href="{{ $reel->url }}" target="_blank" rel="noopener noreferrer" class="sommy-reel-ig-badge" aria-label="Ver en Instagram">
                  <i class="fa-brands fa-instagram"></i>
                </a>
                @endif
                @if($reel->titulo)
                <div class="sommy-reel-caption">{{ $reel->titulo }}</div>
                @endif
              </div>
              @elseif($reel->embed_url)
              {{-- Reel viejo, cargado solo con el link (sin video propio subido): se muestra con el embed de Instagram --}}
              <div class="sommy-reel-card sommy-reel-card--embed">
                <iframe src="{{ $reel->embed_url }}" loading="lazy" scrolling="no" allowtransparency="true" title="{{ $reel->titulo ?: 'Reel de Instagram' }}"></iframe>
              </div>
              @endif
            @endforeach
          </div>
          <button type="button" class="sommy-reels-arrow sommy-reels-arrow--next" onclick="moverReelsCarrusel(1)" aria-label="Siguiente">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </section>
    <!--END CARRUSEL DE REELS DE INSTAGRAM-->
    <script>
        function moverReelsCarrusel(dir) {
            const track = document.getElementById('sommyReelsTrack');
            if (!track) return;
            const card = track.querySelector('.sommy-reel-card');
            const paso = card ? (card.offsetWidth + 16) : 280;
            track.scrollBy({ left: dir * paso, behavior: 'smooth' });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const videos = Array.from(document.querySelectorAll('.sommy-reel-video'));
            if (!videos.length) return;

            const track = document.getElementById('sommyReelsTrack');

            const silenciarTodos = () => {
                videos.forEach(v => {
                    v.muted = true;
                    const btn = v.closest('.sommy-reel-card')?.querySelector('.sommy-reel-sound i');
                    if (btn) { btn.classList.remove('fa-volume-high'); btn.classList.add('fa-volume-xmark'); }
                });
            };

            // No hay src hasta que el video está por entrar en pantalla: si todos
            // arrancan a bajar el archivo apenas carga la home, en un celular con
            // datos móviles se traba todo (varios videos pesados bajando a la vez).
            const cargarSiHaceFalta = (video) => {
                if (!video.src && video.dataset.src) {
                    video.src = video.dataset.src;
                    video.load();
                }
            };

            // Observer de CARGA: dispara un poco antes de que el video esté 100% a la
            // vista (rootMargin), así ya tiene algo buffereado cuando le toca reproducir.
            const loadObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        cargarSiHaceFalta(entry.target);
                    }
                });
            }, { root: track, rootMargin: '0px 300px 0px 300px', threshold: 0.01 });

            // Observer de REPRODUCCIÓN: solo reproduce el que está realmente a la vista,
            // pausa los demás (evita decodificar varios videos en simultáneo).
            const playObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const video = entry.target;
                    if (entry.isIntersecting) {
                        cargarSiHaceFalta(video);
                        video.play().catch(() => {});
                    } else {
                        video.pause();
                    }
                });
            }, { root: track, threshold: 0.6 });

            videos.forEach(video => {
                loadObserver.observe(video);
                playObserver.observe(video);

                const card = video.closest('.sommy-reel-card');
                const btnSound = card?.querySelector('.sommy-reel-sound');

                const toggleSonido = () => {
                    cargarSiHaceFalta(video);
                    const activarSonido = video.muted; // estaba muteado: el click lo activa
                    silenciarTodos();
                    video.muted = !activarSonido;
                    const icon = btnSound?.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-volume-high', activarSonido);
                        icon.classList.toggle('fa-volume-xmark', !activarSonido);
                    }
                    if (video.paused) video.play().catch(() => {});
                };

                video.addEventListener('click', toggleSonido);
                btnSound?.addEventListener('click', function (e) {
                    e.stopPropagation();
                    toggleSonido();
                });
            });
        });
    </script>
    @endif

    <!--Ultimos productos agregados-->
    <section class="py-5" id="productos">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="bootstrap-tabs product-tabs">
              <div class="tabs-header d-flex justify-content-between border-bottom my-5" data-aos="fade-up">
                <h3>Ultimos productos</h3>
                @if($getDataProd->isNotEmpty())
                @endif
              </div>
              <br>
              @if($getDataProd->isEmpty())
              <div class="ec-placeholder-products-wrap">
                <div class="ec-placeholder-inline-hint mb-4">
                  <i class="fa-solid fa-circle-info me-1"></i>
                  <strong>¿Cómo publicar productos en la tienda?</strong> Acá van a aparecer los últimos artículos con foto, nombre y precio para que tus clientes los agreguen al carrito.
                  Para publicarlos: ingresá al panel &rsaquo; <strong>Artículos</strong>, creá o editá un artículo, asignale una imagen, precio y stock, y habilitá la opción de venta online.
                </div>
                <div class="product-grid row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5">
                  @for($i = 0; $i < 5; $i++)
                  <div class="col">
                    <div class="ec-placeholder-prod-card">
                      <div class="ec-placeholder-prod-thumb">
                        <i class="fa-solid fa-box-open"></i>
                      </div>
                      <div class="ec-placeholder-prod-name"></div>
                      <div class="ec-placeholder-prod-price"></div>
                      <div class="ec-placeholder-prod-btn">Agregar al carrito</div>
                    </div>
                  </div>
                  @endfor
                </div>
              </div>
              @else
              <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-all" role="tabpanel" aria-labelledby="nav-all-tab">
                  <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 justify-content-start">
                    @foreach ($getDataProd as $product)
                      <div class="col" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 5) * 80 }}">
                        <div class="product-item">

                          @if($product->has_offer)
                            <span class="badge bg-success position-absolute m-3">
                              <i class="fas fa-tags"></i> Oferta
                            </span>
                          @endif

                          <figure>
                            <a href="{{ url('producto/' . $product->producto->slug) }}" title="{{ $product->producto->nombre }}">
                              <img src="{{asset('imagenes/articulos/'.$product->producto->imagen )}}" class="tab-image">
                            </a>
                          </figure>
                          <div class="name-product">
                            <h3>{{ $product->producto->nombre }}</h3>
                          </div>

                          <div class="text-center mb-2">
                            @if($product->precio_desde)
                                <span class="d-block" style="font-size:11px;color:#64748b;">Desde</span>
                            @endif
                            <span class="fw-bold">${{ number_format($product->display_price, 2, ',', '.') }}</span>
                          </div>

                          <div class="text-center div-button-cart">
                            <a class="btn btn-add-prod" href="{{ url('producto/' . $product->producto->slug) }}">Agregar al carrito</a>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
              @endif

            </div>
          </div>
        </div>
      </div>
    </section>

    <!--START SOMOS FABRICANTES-->
    <section class="pb-5">
      <div class="container-fluid">
        <div class="sommy-cta sommy-noche-estrellas" style="position:relative;overflow:hidden;" data-aos="zoom-in">
          <span class="sommy-estrellas-b" aria-hidden="true"></span>
          <img src="{{ asset('imagenes/marca/sommy-magia-onda.png') }}" alt="" aria-hidden="true"
               style="position:absolute;right:2%;bottom:6%;width:260px;opacity:.35;pointer-events:none;transform:scaleX(-1);">
          <img src="{{ asset('imagenes/marca/sommy-magia-rulo.png') }}" alt="" aria-hidden="true"
               style="position:absolute;left:2%;top:8%;width:180px;opacity:.22;pointer-events:none;">
          <div style="position:relative;">
            <h2>Somos fabricantes de colchones.</h2>
            <p>De nuestra fábrica directo a tu casa: colchones de la mejor calidad, hechos con materiales nobles y controlados de punta a punta. Sin intermediarios, siempre al mejor precio.</p>
            <div class="btn-row">
              <a href="{{ url('/productos') }}" class="btn-sommy-primary">Ver todos los productos</a>
              @if(!empty($arrayEmpresa['phone']))
              <a href="https://wa.me/{{ preg_replace('/\D/', '', $arrayEmpresa['whatsapp']) }}" target="_blank" rel="noopener noreferrer" class="btn-sommy-whatsapp">
                <i class="fa-brands fa-whatsapp"></i> Consultanos
              </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>
    <!--END SOMOS FABRICANTES-->

    <!--START PREGUNTAS FRECUENTES-->
    <section class="pb-5">
      <div class="container-fluid" style="max-width:860px;">
        <h2 class="sommy-section-heading" data-aos="fade-up">Preguntas frecuentes</h2>
        <p class="sommy-section-sub" data-aos="fade-up" data-aos-delay="100">Lo que más nos consultan antes de comprar.</p>
        <div class="accordion sommy-faq" id="sommyFaq" data-aos="fade-up">
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">¿Cómo elijo la firmeza correcta?</button>
            </h3>
            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#sommyFaq">
              <div class="accordion-body">
                <strong>Suave</strong>: ideal si dormís de costado o preferís sentirte "abrazado" por el colchón.
                <strong>Media</strong>: la más elegida, equilibra sostén y confort para la mayoría de los cuerpos y posiciones.
                <strong>Firme</strong>: recomendada si dormís boca arriba o abajo, o si preferís máximo sostén de columna.
                En cada ficha de producto vas a ver el medidor de firmeza; si dudás, escribinos y te asesoramos según cómo dormís.
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">¿Qué medida necesito?</button>
            </h3>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#sommyFaq">
              <div class="accordion-body">
                <strong>1 plaza</strong> (80×190 cm) para camas individuales · <strong>1 plaza y media</strong> (100×190 cm) si querés más espacio ·
                <strong>2 plazas</strong> (140×190 cm) la matrimonial clásica · <strong>Queen</strong> (160×200 cm) y <strong>King</strong> (200×200 cm) para máximo espacio.
                Medí tu base o sommier antes de comprar: el colchón debe tener la misma medida.
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">¿Cómo son los envíos?</button>
            </h3>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#sommyFaq">
              <div class="accordion-body">
                Hacemos envíos a domicilio coordinando día y franja horaria con vos. Los tiempos y costos dependen de tu zona:
                al confirmar tu pedido te los detallamos por WhatsApp antes de cerrar la compra, sin sorpresas.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!--END PREGUNTAS FRECUENTES-->

@endsection