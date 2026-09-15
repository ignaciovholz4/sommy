@extends('ecommerce.layouts.main-ecommerce')

@section('meta_title', $getProd[0]->meta_title ?: ($getProd[0]->nombre . ' | Sommy Córdoba'))
@section('meta_description', $getProd[0]->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($getProd[0]->descripcion ?? ($getProd[0]->nombre . ' — de fábrica, en Córdoba. Comprá online con envío a toda la ciudad.')), 155))
@section('meta_keywords', $getProd[0]->nombre . ', ' . $getProd[0]->nombre . ' Córdoba, comprar ' . strtolower($getProd[0]->nombre) . ', Sommy')
@section('meta_og_type', 'product')
@section('meta_imagen', $imagenesGaleria->first() ? asset($imagenesGaleria->first()->path) : asset('imagenes/marca/sommy-hero-poster-h.jpg'))

@php
    $seoPrecio = $getProd[0]->tipo_producto_id == 2
        ? ($getVariantesData->where('pventa_variante', '>', 0)->min('pventa_variante') ?: 0)
        : ($getProd[0]->display_price ?? $getProd[0]->pventa_con_iva ?? 0);
    $seoHayStock = $getProd[0]->tipo_producto_id == 2
        ? collect($getEachVarianteProd)->sum('total_stock') > 0
        : ($getProd[0]->stock ?? 0) > 0;
@endphp
@section('meta_structured_data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": {!! json_encode($getProd[0]->nombre) !!},
    "image": {!! json_encode($imagenesGaleria->first() ? asset($imagenesGaleria->first()->path) : asset('imagenes/marca/sommy-hero-poster-h.jpg')) !!},
    "description": {!! json_encode(\Illuminate\Support\Str::limit(strip_tags($getProd[0]->descripcion ?? ''), 300)) !!},
    "brand": { "@type": "Brand", "name": "Sommy" },
    "offers": {
        "@type": "Offer",
        "url": {!! json_encode(url()->current()) !!},
        "priceCurrency": "ARS",
        "price": "{{ number_format((float) $seoPrecio, 2, '.', '') }}",
        "availability": "https://schema.org/{{ $seoHayStock ? 'InStock' : 'OutOfStock' }}"
    }
}
</script>
@endsection

@section('contentEcommerce')
    <style>
        .galeria-thumbs {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: .75rem;
        }
        .galeria-thumbs img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: border-color .2s;
        }
        .galeria-thumbs img.active,
        .galeria-thumbs img:hover {
            border-color: #212529;
        }
        .galeria-thumb-video {
            position: relative;
            display: block;
            width: 64px;
            height: 64px;
            border-radius: 6px;
            border: 2px solid transparent;
            overflow: hidden;
            transition: border-color .2s;
        }
        .galeria-thumb-video:hover { border-color: #212529; }
        .galeria-thumb-video video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            pointer-events: none;
        }
        .galeria-thumb-video i {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
            font-size: 22px;
            text-shadow: 0 1px 4px rgba(0,0,0,.6);
            pointer-events: none;
        }
        .tabla-especificaciones th {
            width: 45%;
            font-weight: 600;
            color: #495057;
        }
        .combo-item {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            max-width: 480px;
        }
        .combo-item img {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
        }
        .combo-item-info { flex: 1; min-width: 0; }
        .combo-item-name { font-weight: 600; font-size: 0.85rem; }
        .combo-item-price { font-size: 0.8rem; color: #495057; }
        .combo-variant-select {
            font-size: 0.78rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 2px 6px;
            margin-top: 3px;
        }
        /* Flechas del carrusel de imágenes */
        .galeria-flecha {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid #E7EAF2;
            background: rgba(255,255,255,.92);
            color: #1B2B5A;
            font-size: 15px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(27,43,90,.14);
            transition: background .15s, color .15s;
            z-index: 5;
        }
        .galeria-flecha:hover { background: #1B2B5A; color: #fff; }
        .galeria-flecha--prev { left: 8px; }
        .galeria-flecha--next { right: 8px; }

        /* ── Mobile: más aire, menos ruido, foco en comprar ── */
        @media (max-width: 767px) {
            .content-product-item-main .product-main-image { max-height: 300px; }
            .galeria-thumbs { margin: 16px 0 30px; }

            .sommy-ficha-kicker { margin-top: 6px; letter-spacing: .14em; }
            h1 { font-size: 26px !important; line-height: 1.25; margin: 6px 0 14px !important; }

            .fs-4 { margin: 14px 0 4px; }
            .sommy-price-old { display: block; margin: 0 0 2px; }

            .sommy-chips { gap: 8px; margin: 20px 0 8px; }
            .sommy-chip { font-size: 12px; padding: 6px 12px; }

            .sommy-firmeza { margin: 24px 0 12px; }

            hr.my-6, hr { margin: 26px 0 !important; }

            .w-20 { margin: 6px 0 14px; }
            #btn-add-product {
                font-size: 16.5px;
                padding: 16px !important;
                box-shadow: 0 10px 26px rgba(27, 43, 90, .25);
            }

            .tabla-especificaciones th,
            .tabla-especificaciones td { font-size: 13.5px; padding: 10px 12px; }

            .form-label.fw-bold { margin-top: 8px; }
        }
    </style>
    <style>
        .content-product-item-main {
            display: flex;
            justify-content: center !important;
            align-items: center !important;
        }
        .content-product-item-main .product-main-image {
            max-height: 400px;
            object-fit: cover;
        }
        .w-20{ width: 160px; }
        .w-20 .input-spinner { flex-wrap: nowrap; }
        .gap-1{ gap: .25rem !important; }
        .mb-5{ margin-bottom: 1.25rem !important; }
        @media (min-width: 992px) {
            .ps-lg-10{ padding-left: 3rem !important; }
        }
        .color-option {
            width: 2rem; height: 2rem; border-radius: 50%; cursor: pointer;
            transition: all 0.3s ease; position: relative;
        }
        .color-option.selected::after {
            content: ''; position: absolute; top: -3px; left: -3px; right: -3px; bottom: -3px;
            border: 1px solid #696969; border-radius: 50%;
        }
        .custom-radios div { display: inline-block; }
        .custom-radios input[type=radio] { display: none; }
        .custom-radios input[type=radio] + label { font-size: 14px; }
        .custom-radios input[type=radio] + label span {
            display: inline-block; width: 40px; height: 40px; margin: -1px 4px 0 0;
            vertical-align: middle; cursor: pointer; border-radius: 50%;
            border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,.33);
            background-repeat: no-repeat; background-position: center; text-align: center;
            line-height: 44px;
        }
        .custom-radios input[type=radio] + label span i { opacity: 0; transition: all 0.3s ease; }
        .custom-radios input[type=radio]:checked + label span i { opacity: 1; }
    </style>

    <section class="py-5">
        <div class="container-fluid">
            <div class="container">
                <div>
                    <input type="hidden" id="dataProduct" value="{{ json_encode($getProd, true) }}">
                    <input type="hidden" id="dataVariant" value="{{ json_encode($getVariantesData, true) }}">
                    <input type="hidden" id="dataEachVariant" value="{{ json_encode($getEachVarianteProd, true) }}">
                    <input type="hidden" id="dataCheckVariant" value="{{ json_encode($firstMatchVariant, true) }}">
                </div>

                <div class="row">
                    <div class="col-md-6" data-aos="fade-right">
                        @php
                            $imagenPrincipalUrl = $imagenesGaleria->isNotEmpty()
                                ? asset($imagenesGaleria->first()->path)
                                : asset('imagenes/articulos/'.$getProd[0]->imagen);
                        @endphp
                        <div class="content-product-item-main" style="position:relative;">
                            <a href="{{ $imagenPrincipalUrl }}" data-fancybox="gallery" id="linkImageVariant" title="{{ $getProd[0]->nombre }}">
                                <img src="{{ $imagenPrincipalUrl }}" class="product-main-image" id="showImageVariant" alt="{{ $getProd[0]->nombre }}">
                            </a>
                            @if($imagenesGaleria->count() > 1)
                            {{-- Flechas del carrusel de imágenes --}}
                            <button type="button" class="galeria-flecha galeria-flecha--prev" onclick="moverGaleria(-1)" aria-label="Imagen anterior">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button type="button" class="galeria-flecha galeria-flecha--next" onclick="moverGaleria(1)" aria-label="Imagen siguiente">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                            @endif
                        </div>
                        @if($imagenesGaleria->count() > 1)
                            <div class="galeria-thumbs" id="galeriaThumbs">
                                @foreach ($imagenesGaleria as $idx => $img)
                                    @if($img->tipo === 'video')
                                        {{-- Los videos no reemplazan la imagen principal: se abren en el lightbox --}}
                                        <a href="{{ asset($img->path) }}" data-fancybox="gallery" data-type="video" class="galeria-thumb-video" title="Ver video">
                                            <video src="{{ asset($img->path) }}" muted preload="metadata"></video>
                                            <i class="fa-solid fa-circle-play"></i>
                                        </a>
                                    @else
                                        <img src="{{ asset($img->path) }}"
                                             alt="{{ $img->alt ?? $getProd[0]->nombre }}"
                                             class="{{ $idx === 0 ? 'active' : '' }}"
                                             onclick="cambiarImagenGaleria(this)">
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6" data-aos="fade-left" data-aos-delay="150">
                        <div class="ps-lg-10 mt-6 mt-md-0">
                            @php
                                $fichaProd = $getProd[0];
                                $fichaMarca = \App\Models\Marca::find($fichaProd->marca_id);
                            @endphp
                            <span class="sommy-ficha-kicker">
                                {{ $fichaMarca->nombre ?? 'Sommy' }}
                                @if($fichaProd->tipo_colchon) · {{ \App\Models\Articulo::TIPOS_COLCHON[$fichaProd->tipo_colchon] ?? $fichaProd->tipo_colchon }} @endif
                            </span>
                            <h1 class="mb-1">{{ $getProd[0]->nombre }}</h1>

                            {{-- Badge de oferta si corresponde --}}
                            <span id="badgeOffer">
                                @if($getProd[0]->tipo_producto_id == 1)
                                    {{-- Producto simple: usa has_offer --}}
                                    @if(!empty($getProd[0]->has_offer) && $getProd[0]->has_offer)
                                        <span class="badge bg-success mb-2">
                                            <i class="fas fa-tags"></i> Oferta
                                        </span>
                                    @endif
                                @elseif($getProd[0]->tipo_producto_id == 2 && $firstMatchVariant)
                                    {{-- Producto con combinaciones: compara precio variante vs precio base --}}
                                    @php
                                        $precioBase = $getProd[0]->pventa_con_iva ?? $getProd[0]->display_price;
                                        $precioVariante = $firstMatchVariant->combinacion->pventa_variante;
                                    @endphp
                                    @if($precioVariante < $precioBase)
                                        <span class="badge bg-success mb-2">
                                            <i class="fas fa-tags"></i> Oferta
                                        </span>
                                    @endif
                                @endif
                            </span>
                            
                            <div class="fs-4">
                                @if($getProd[0]->tipo_producto_id == 2 && $firstMatchVariant)
                                    <input type="hidden" value="{{ $firstMatchVariant->combinacion->pventa_variante }}" id="priceProduct">
                                    <span class="fw-bold text-dark" id="showPriceVariant">
                                        {{ format_money_global($firstMatchVariant->combinacion->pventa_variante) }}
                                    </span>
                                @elseif($getProd[0]->tipo_producto_id == 1)
                                    <input type="hidden" value="{{ $getProd[0]->display_price }}" id="priceProduct">
                                    @if(!empty($fichaProd->has_offer) && $fichaProd->has_offer && $fichaProd->display_price < $fichaProd->pventa_con_iva)
                                        <span class="sommy-price-old">{{ format_money_global($fichaProd->pventa_con_iva) }}</span>
                                    @endif
                                    <span class="fw-bold text-dark" id="showPriceVariant">
                                        {{ format_money_global($getProd[0]->display_price) }}
                                    </span>
                                    @if($fichaProd->descuento > 0)
                                        <span class="sommy-off-chip">-{{ rtrim(rtrim(number_format($fichaProd->descuento, 2), '0'), '.') }}%</span>
                                    @endif
                                @else
                                    <span>No se encontró ninguno</span>
                                @endif
                            </div>

                            {{-- Chips destacados del colchón --}}
                            @php
                                $chips = [];
                                if ($fichaProd->plazas) {
                                    $chips[] = ['fa-bed', \App\Models\Articulo::PLAZAS[$fichaProd->plazas] ?? $fichaProd->plazas];
                                }
                                if ($fichaProd->altura_cm) {
                                    $chips[] = ['fa-ruler-vertical', rtrim(rtrim(number_format($fichaProd->altura_cm, 1, ',', '.'), '0'), ',') . ' cm de altura'];
                                }
                                if (!is_null($fichaProd->pillow_top) && $fichaProd->pillow_top) {
                                    $chips[] = ['fa-cloud', 'Pillow top'];
                                }
                                if ($fichaProd->tela) {
                                    $chips[] = ['fa-feather', $fichaProd->tela];
                                }
                            @endphp
                            @if(count($chips))
                            <div class="sommy-chips">
                                @foreach($chips as $chip)
                                <span class="sommy-chip"><i class="fa-solid {{ $chip[0] }}"></i>{{ $chip[1] }}</span>
                                @endforeach
                            </div>
                            @endif

                            {{-- Medidor de firmeza --}}
                            @if($fichaProd->firmeza)
                            @php
                                $nivelFirmeza = ['suave' => 1, 'media' => 2, 'firme' => 3][$fichaProd->firmeza] ?? 2;
                            @endphp
                            <div class="sommy-firmeza">
                                <span class="sommy-firmeza-label">Firmeza: {{ \App\Models\Articulo::FIRMEZAS[$fichaProd->firmeza] ?? $fichaProd->firmeza }}</span>
                                <div class="sommy-firmeza-track">
                                    @for($i = 1; $i <= 3; $i++)
                                    <div class="sommy-firmeza-seg {{ $i <= $nivelFirmeza ? 'on' : '' }}"></div>
                                    @endfor
                                </div>
                                <div class="sommy-firmeza-names">
                                    <span class="{{ $fichaProd->firmeza === 'suave' ? 'on' : '' }}">Suave</span>
                                    <span class="{{ $fichaProd->firmeza === 'media' ? 'on' : '' }}">Media</span>
                                    <span class="{{ $fichaProd->firmeza === 'firme' ? 'on' : '' }}">Firme</span>
                                </div>
                            </div>
                            @endif

                            <hr class="my-6" />

                            @if($getProd[0]->tipo_producto_id == 2 && $getEachVarianteProd)
                                <label class="form-label fw-bold">Medida</label>
                                <div class="mb-5 d-flex flex-wrap gap-2">
                                    @foreach ($getEachVarianteProd as $comb)
                                        <input type="radio"
                                            class="btn-check"
                                            name="variantProduct"
                                            id="variant-{{ $comb->combinacion_id }}"
                                            value="{{ json_encode($comb) }}"
                                            @if($firstMatchVariant && $firstMatchVariant->combinacion_id == $comb->combinacion_id) checked @endif>
                                        <label class="btn btn-outline-dark size-btn" for="variant-{{ $comb->combinacion_id }}">
                                            {{ $comb->combinacion->combinacion }}
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <div class="w-20">
                                <div class="input-spinner input-group">
                                    <button type="button" id="btnLessCant" class="button-minus text-white btn btn-danger btn-sm">-</button>
                                    <input class="quantity-field form-input form-control form-control-sm"
                                        type="text"
                                        value="1"
                                        name="quantity"
                                        id="cantProduct"
                                        readonly />
                                    <button type="button" id="btnAddCantMore" class="button-plus text-white btn btn-danger btn-sm">+</button>
                                </div>
                            </div>

                            @if($regalos->isNotEmpty())
                            <div class="mt-4" id="regaloPicker">
                                <label class="form-label fw-bold"><i class="fa-solid fa-gift text-danger me-1"></i> Elegí tu regalo</label>
                                <div class="d-flex flex-column gap-2" style="max-width:420px;">
                                    @foreach($regalos as $regalo)
                                    <label class="d-flex align-items-center gap-2 border rounded p-2" style="cursor:pointer;">
                                        <input type="radio" name="regaloElegido" value="{{ $regalo->id }}" class="form-check-input mt-0"
                                               data-nombre="{{ $regalo->nombre }}" data-precio="{{ $regalo->precio }}"
                                               data-cantidad="{{ $regalo->cantidad }}"
                                               data-stock="{{ $regalo->stock }}" data-imagen="{{ $regalo->imagen_url }}"
                                               {{ $loop->first ? 'checked' : '' }}>
                                        @if($regalo->imagen_url)
                                        <img src="{{ $regalo->imagen_url }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                                        @endif
                                        <span class="flex-grow-1">{{ $regalo->nombre }}{{ $regalo->cantidad > 1 ? ' x' . $regalo->cantidad : '' }}</span>
                                        <span class="badge bg-success">GRATIS</span>
                                    </label>
                                    @endforeach
                                    <label class="d-flex align-items-center gap-2" style="cursor:pointer;">
                                        <input type="radio" name="regaloElegido" value="" class="form-check-input mt-0">
                                        <span class="text-muted small">No quiero el regalo</span>
                                    </label>
                                </div>
                            </div>
                            @endif

                            <div class="mt-3 row">
                                <div class="d-grid col-12 col-md-8 col-lg-6">
                                    <button type="button" class="btn btn-add-prod" id="btn-add-product" style="padding:13px;white-space:nowrap;">
                                        Agregar al carrito
                                    </button>
                                </div>
                            </div>

                            @if(($getProd[0]->combo_descuento_pct ?? 0) > 0)
                            <div id="comboBuilder" class="mt-5" style="display:none;"
                                 data-product-id="{{ $getProd[0]->idarticulo }}"
                                 data-discount="{{ $getProd[0]->combo_descuento_pct }}">
                                <hr class="my-6" />
                                <label class="form-label fw-bold">
                                    Armá tu combo y ahorrá {{ rtrim(rtrim(number_format($getProd[0]->combo_descuento_pct, 2, ',', '.'), '0'), ',') }}%
                                </label>
                                <p class="text-muted small mb-3">Elegí qué sumar: el descuento se aplica sobre el total del combo.</p>
                                <div id="comboItemsList" class="d-flex flex-column gap-2 mb-3"></div>
                                <div class="mb-3">
                                    <div class="text-muted small" id="comboPrecioNormal" style="text-decoration:line-through;"></div>
                                    <div class="fw-bold" style="font-size:1.25rem;color:#212529;" id="comboPrecioFinal"></div>
                                </div>
                                <div class="d-grid col-12 col-md-8 col-lg-6">
                                    <button type="button" id="btnAgregarCombo" class="btn btn-add-prod" style="padding:13px;white-space:nowrap;">
                                        Agregar combo al carrito
                                    </button>
                                </div>
                            </div>
                            @endif

                            @if(!empty($especificaciones))
                                <hr class="my-6" />
                                <label class="form-label fw-bold">Especificaciones</label>
                                <table class="table table-sm table-striped tabla-especificaciones">
                                    <tbody>
                                        @foreach ($especificaciones as $etiqueta => $valor)
                                            <tr>
                                                <th>{{ $etiqueta }}</th>
                                                <td>{{ $valor }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <hr class="my-6" />
                            <label class="form-label fw-bold">Descripción</label>
                            <div>
                                <p>{!! nl2br(e($getProd[0]->descripcion)) !!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scriptEcommerce')
    <script src="{{ assetv('js/ecommerce/shopping-card.js') }}"></script>
    <script>
        function cambiarImagenGaleria(thumb) {
            const principal = document.getElementById('showImageVariant');
            const link = document.getElementById('linkImageVariant');
            if (principal) principal.src = thumb.src;
            if (link) link.href = thumb.src;
            document.querySelectorAll('#galeriaThumbs img').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        }

        // Carrusel: avanzar/retroceder entre las imágenes de la galería
        function moverGaleria(dir) {
            const thumbs = Array.from(document.querySelectorAll('#galeriaThumbs img'));
            if (!thumbs.length) return;
            let idx = thumbs.findIndex(t => t.classList.contains('active'));
            if (idx < 0) idx = 0;
            idx = (idx + dir + thumbs.length) % thumbs.length;
            cambiarImagenGaleria(thumbs[idx]);
        }

        @if(config('services.meta_ads.pixel_id'))
        if (typeof fbq === 'function') {
            fbq('track', 'ViewContent', {
                content_ids: ['{{ $getProd[0]->idarticulo }}'],
                content_type: 'product',
                value: {{ (float) ($getProd[0]->display_price ?? 0) }},
                currency: 'ARS'
            });
        }
        @endif
    </script>

    @if(($getProd[0]->combo_descuento_pct ?? 0) > 0)
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const comboBuilder = document.getElementById('comboBuilder');
        if (!comboBuilder) return;

        const productoId = comboBuilder.getAttribute('data-product-id');
        const descuentoPct = Number(comboBuilder.getAttribute('data-discount'));
        const itemsList = document.getElementById('comboItemsList');
        const precioNormalEl = document.getElementById('comboPrecioNormal');
        const precioFinalEl = document.getElementById('comboPrecioFinal');
        const btnAgregarCombo = document.getElementById('btnAgregarCombo');
        let comboProductos = [];

        // Precio + stock del producto de ESTA ficha, según la variante/cantidad ya elegida arriba
        function precioProductoActual() {
            const cant = Number(document.getElementById('cantProduct')?.value) || 1;
            if (productValue[0].tipo_producto_id === 2) {
                const dataValue = document.getElementById('btn-add-product')?.getAttribute('data-value');
                if (!dataValue) return null;
                const variantData = JSON.parse(dataValue);
                return { precio: Number(variantData.combinacion.pventa_variante), stock: Number(variantData.stock), cant };
            }
            return { precio: Number(productValue[0].display_price || productValue[0].pventa_con_iva), stock: Number(productValue[0].stock), cant };
        }

        function recalcularTotales() {
            const base = precioProductoActual();
            if (!base) {
                precioNormalEl.textContent = '';
                precioFinalEl.textContent = 'Elegí una medida para ver el precio del combo';
                return;
            }

            // El descuento del combo se aplica SOLO a lo que se suma (relacionados
            // tildados), nunca al producto principal de esta ficha: su precio
            // queda siempre el mismo, con o sin combo.
            const anchor = base.precio * base.cant;
            let addons = 0;
            itemsList.querySelectorAll('.combo-item-check:checked').forEach(chk => {
                const producto = comboProductos.find(p => String(p.id) === chk.getAttribute('data-product-id'));
                if (!producto) return;
                if (producto.tipo_producto_id === 2) {
                    const select = itemsList.querySelector(`.combo-variant-select[data-product-id="${producto.id}"]`);
                    const variante = (producto.variantes || []).find(v => String(v.idcombinacion) === select?.value);
                    if (variante) addons += variante.precio;
                } else {
                    addons += producto.precio;
                }
            });

            const normal = anchor + addons;
            const final = Math.round((anchor + addons * (1 - descuentoPct / 100)) * 100) / 100;
            precioNormalEl.textContent = window.fnFormatMoney(normal);
            precioFinalEl.textContent = window.fnFormatMoney(final) + ' con el combo';
        }

        fetch(`/Ecommercerelacionados?ids=${productoId}`)
            .then(res => res.json())
            .then(data => {
                comboProductos = data.productos || [];
                if (comboProductos.length === 0) return;

                const esc = (str) => String(str ?? '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                }[c]));

                itemsList.innerHTML = comboProductos.map(p => {
                    const variantes = Array.isArray(p.variantes) ? p.variantes : [];
                    const tieneStock = p.tipo_producto_id === 1 ? p.stock > 0 : variantes.length > 0;
                    if (!tieneStock) return '';

                    const thumb = p.imagen ? `<img src="${esc(p.imagen)}" alt="">` : '';
                    const selector = (p.tipo_producto_id === 2 && variantes.length > 0)
                        ? `<select class="combo-variant-select form-control" data-product-id="${p.id}">
                             ${variantes.map(v => `<option value="${v.idcombinacion}">${esc(v.label)} — ${window.fnFormatMoney(v.precio)}</option>`).join('')}
                           </select>`
                        : `<div class="combo-item-price">${window.fnFormatMoney(p.precio)}</div>`;

                    return `
                        <label class="combo-item">
                            <input type="checkbox" class="combo-item-check" data-product-id="${p.id}">
                            ${thumb}
                            <div class="combo-item-info">
                                <div class="combo-item-name">${esc(p.nombre)}</div>
                                ${selector}
                            </div>
                        </label>
                    `;
                }).join('');

                if (!itemsList.innerHTML.trim()) return;

                comboBuilder.style.display = '';
                recalcularTotales();

                itemsList.querySelectorAll('.combo-item-check, .combo-variant-select').forEach(el => {
                    el.addEventListener('change', recalcularTotales);
                });
            })
            .catch(() => {});

        // El precio del producto principal cambia si el cliente elige otra medida o cantidad
        document.querySelectorAll('input[name="variantProduct"]').forEach(r => r.addEventListener('change', recalcularTotales));
        document.getElementById('btnAddCantMore')?.addEventListener('click', () => setTimeout(recalcularTotales, 0));
        document.getElementById('btnLessCant')?.addEventListener('click', () => setTimeout(recalcularTotales, 0));

        btnAgregarCombo.addEventListener('click', function () {
            const base = precioProductoActual();
            if (!base) {
                window.fnMessageToastrError('Elegí una medida antes de armar el combo', 'Error');
                return;
            }

            const seleccionados = Array.from(itemsList.querySelectorAll('.combo-item-check:checked'));
            if (seleccionados.length === 0) {
                window.fnMessageToastrError('Elegí al menos un producto para armar el combo', 'Error');
                return;
            }

            const factor = 1 - (descuentoPct / 100);
            const imagenActual = (typeof showImageVariant !== 'undefined' && showImageVariant && showImageVariant.src) ? showImageVariant.src : null;

            const items = [{
                claveCart: productValue[0].tipo_producto_id === 2
                    ? `${productValue[0].idarticulo}-${JSON.parse(document.getElementById('btn-add-product').getAttribute('data-value')).combinacion.idcombinacion}`
                    : String(productValue[0].idarticulo),
                name: productValue[0].nombre,
                productId: productValue[0].idarticulo,
                precio: base.precio,
                stock: base.stock,
                cant: base.cant,
                rowProdVariant: productValue[0].tipo_producto_id === 2
                    ? JSON.parse(document.getElementById('btn-add-product').getAttribute('data-value')).combinacion
                    : null,
                tipoProductoId: productValue[0].tipo_producto_id,
                image: imagenActual,
                esAnchor: true,
            }];

            seleccionados.forEach(chk => {
                const producto = comboProductos.find(p => String(p.id) === chk.getAttribute('data-product-id'));
                if (!producto) return;

                let variante = null;
                if (producto.tipo_producto_id === 2) {
                    const select = itemsList.querySelector(`.combo-variant-select[data-product-id="${producto.id}"]`);
                    variante = (producto.variantes || []).find(v => String(v.idcombinacion) === select?.value);
                    if (!variante) return;
                }

                items.push({
                    claveCart: variante ? `${producto.id}-${variante.idcombinacion}` : String(producto.id),
                    name: producto.nombre,
                    productId: producto.id,
                    precio: variante ? variante.precio : producto.precio,
                    stock: variante ? variante.stock : producto.stock,
                    cant: 1,
                    rowProdVariant: variante ? { idcombinacion: variante.idcombinacion, combinacion: variante.label, pventa_variante: variante.precio } : null,
                    tipoProductoId: producto.tipo_producto_id,
                    image: producto.imagen,
                    esAnchor: false,
                });
            });

            const cart = window.fnListCartProduct();

            items.forEach(it => {
                // El producto principal de la ficha queda siempre a su precio normal:
                // el descuento del combo es solo para lo que se suma.
                const precioConDescuento = it.esAnchor ? it.precio : Math.round(it.precio * factor * 100) / 100;
                const existente = cart.find(p => p.claveCart === it.claveCart);

                if (existente) {
                    existente.cant += it.cant;
                    existente.total = existente.cant * existente.priceSale;
                    existente.sinStock = window.fnCheckStockProduct(it.stock, existente.cant);
                } else {
                    cart.push({
                        claveCart: it.claveCart,
                        name: it.name,
                        productId: it.productId,
                        original_price: it.precio,
                        priceSale: precioConDescuento,
                        cant: it.cant,
                        total: it.cant * precioConDescuento,
                        rowProdVariant: it.rowProdVariant,
                        tipoProductoId: it.tipoProductoId,
                        stockProduct: it.stock,
                        display_price: precioConDescuento,
                        has_offer: !it.esAnchor,
                        image: it.image,
                        sinStock: window.fnCheckStockProduct(it.stock, it.cant)
                    });
                }
            });

            window.fnSaveCartProduct(cart);
            window.fnShowListCartProduct();
            window.fnMessageToastrSuccess(`Combo agregado con ${descuentoPct}% off`, 'Éxito!');
        });
    });
    </script>
    @endif

    @if($regalos->isNotEmpty())
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const regaloPicker = document.getElementById('regaloPicker');
        const btnAdd = document.getElementById('btn-add-product');
        if (!regaloPicker || !btnAdd) return;

        // Se cuelga del mismo botón de "Agregar al carrito": shopping-card.js agrega el
        // producto principal primero (este script corrió después en la página), acá solo
        // sumamos el regalo elegido como una línea aparte a precio $0.
        btnAdd.addEventListener('click', function () {
            // Si es un producto con variantes y no hay medida elegida, shopping-card.js ya
            // mostró el error y no agregó nada: no sumar el regalo tampoco.
            if (productValue[0].tipo_producto_id === 2 && !btnAdd.getAttribute('data-value')) return;

            const seleccionado = regaloPicker.querySelector('input[name="regaloElegido"]:checked');
            if (!seleccionado || !seleccionado.value) return;

            const cantidadRegalo = Number(seleccionado.getAttribute('data-cantidad')) || 1;
            const cart = window.fnListCartProduct();
            const claveCart = 'regalo-' + seleccionado.value;
            const existente = cart.find(p => p.claveCart === claveCart);

            // El regalo es de cantidad fija: si ya está en el carrito (ej. el
            // cliente clickeó "Agregar al carrito" más de una vez), no se
            // duplica ni se suma de nuevo.
            if (!existente) {
                cart.push({
                    claveCart: claveCart,
                    name: seleccionado.getAttribute('data-nombre') + (cantidadRegalo > 1 ? ` x${cantidadRegalo}` : '') + ' (regalo)',
                    productId: Number(seleccionado.value),
                    original_price: Number(seleccionado.getAttribute('data-precio')) || 0,
                    priceSale: 0,
                    cant: cantidadRegalo,
                    total: 0,
                    rowProdVariant: null,
                    tipoProductoId: 1,
                    stockProduct: Number(seleccionado.getAttribute('data-stock')) || 0,
                    display_price: 0,
                    has_offer: true,
                    image: seleccionado.getAttribute('data-imagen') || null,
                    sinStock: false
                });
            }

            window.fnSaveCartProduct(cart);
            window.fnShowListCartProduct();
        });
    });
    </script>
    @endif
@endsection