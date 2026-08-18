@extends('ecommerce.layouts.main-ecommerce')

@section('meta_title', $getProd[0]->meta_title ?: $getProd[0]->nombre)
@section('meta_description', $getProd[0]->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($getProd[0]->descripcion ?? ''), 155))

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
        .tabla-especificaciones th {
            width: 45%;
            font-weight: 600;
            color: #495057;
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
                                    <img src="{{ asset($img->path) }}"
                                         alt="{{ $img->alt ?? $getProd[0]->nombre }}"
                                         class="{{ $idx === 0 ? 'active' : '' }}"
                                         onclick="cambiarImagenGaleria(this)">
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

                            <div class="mt-3 row">
                                <div class="d-grid col-12 col-md-8 col-lg-6">
                                    <button type="button" class="btn btn-add-prod" id="btn-add-product" style="padding:13px;white-space:nowrap;">
                                        Agregar al carrito
                                    </button>
                                </div>
                            </div>

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
    </script>
@endsection