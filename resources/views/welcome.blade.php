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
                          <img src="{{ asset('imagenes/banner/'.$banner->name_image) }}" class="sommy-banner-img desktop-img" alt="{{ $banner->titulo ?: 'Sommy' }}">
                          <img src="{{ asset('imagenes/banner/'.($banner->name_image_movil ?: $banner->name_image)) }}" class="sommy-banner-img mobile-img" alt="{{ $banner->titulo ?: 'Sommy' }}">
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
            @endfor
          </div>
        </div>
      </div>
    </section>
    <!--END MARCAS QUE TRABAJAMOS-->

    @if($getDataCategory->isEmpty())
    <section class="ec-placeholder-section py-5">
      <div class="container-fluid">
        <h2 class="section-title mb-4">Categorias</h2>
        <div class="ec-placeholder-grid">
          @for($i = 0; $i < 5; $i++)
          <div class="ec-placeholder-cat-card">
            <div class="ec-placeholder-cat-thumb">
              <i class="fa-solid fa-tag"></i>
            </div>
            <p class="ec-placeholder-cat-label">Categoría {{ $i + 1 }}</p>
          </div>
          @endfor
        </div>
        <div class="ec-placeholder-inline-hint mt-4">
          <i class="fa-solid fa-circle-info me-1"></i>
          <strong>¿Cómo configurar las categorías?</strong> Cada categoría muestra su imagen y nombre. Tus clientes hacen clic para explorar los productos de esa sección.
          Creá tus categorías desde el panel &rsaquo; <strong>Ecommerce &rsaquo; Categorías</strong>: poné un nombre, subí una imagen representativa y ya aparecerá aquí.
        </div>
      </div>
    </section>
    @else
    {{-- Categorías: tiles de foto con nombre superpuesto --}}
    <section class="py-5" id="categorias">
      <div class="container-fluid">
        {{-- Encabezado con el mismo estilo que "Ultimos productos" --}}
        <div class="tabs-header d-flex justify-content-between border-bottom my-5" data-aos="fade-up">
          <h3>Categorías</h3>
        </div>
        <br>
        <div class="sommy-cat-grid">
          @foreach ($getDataCategory as $cat)
          <a href="{{ url('categoria/' . $cat->slug) }}" class="sommy-cat-tile" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
            <img src="{{ asset('imagenes/categorias/' . $cat->name_imagen) }}" alt="{{ $cat->nombre }}">
            {{-- Las imágenes "categoria_*.webp" ya traen el nombre incorporado: no superponer texto --}}
            @if(!\Illuminate\Support\Str::startsWith($cat->name_imagen, 'categoria_'))
            <span class="veil"></span>
            <span class="label">{{ $cat->nombre }}</span>
            @endif
            <span class="go">Ver productos <i class="fa-solid fa-arrow-right"></i></span>
          </a>
          @endforeach
        </div>
      </div>
    </section>
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
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">¿Qué garantía tienen los colchones?</button>
            </h3>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#sommyFaq">
              <div class="accordion-body">
                Somos fabricantes: todos nuestros colchones tienen garantía de fábrica (el plazo figura en cada ficha de producto).
                Si tu compra llega con algún defecto, la cambiamos sin costo. Ante cualquier problema escribinos:
                respondemos directo, sin intermediarios.
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">¿Cómo puedo pagar?</button>
            </h3>
            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#sommyFaq">
              <div class="accordion-body">
                Podés pagar online al hacer tu pedido o coordinar con nosotros el medio que te quede más cómodo
                (efectivo, transferencia u otras opciones vigentes). Consultanos por promociones y descuentos antes de comprar.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!--END PREGUNTAS FRECUENTES-->

@endsection