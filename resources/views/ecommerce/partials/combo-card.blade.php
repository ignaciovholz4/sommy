{{-- Tarjeta de un combo en oferta. Espera $combo (objeto de EcommerceController::combosDisponibles()). --}}
<div class="col" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 5) * 80 }}">
  <div class="product-item">

    @if($combo->ahorro > 0)
      <span class="badge bg-success position-absolute m-3">
        <i class="fas fa-piggy-bank"></i> Ahorrás ${{ number_format($combo->ahorro, 0, ',', '.') }}
      </span>
    @endif

    <figure>
      <a href="{{ url('producto/' . $combo->producto->slug) }}" title="{{ $combo->producto->nombre }}">
        <img src="{{asset('imagenes/articulos/'.$combo->producto->imagen )}}" class="tab-image">
      </a>
    </figure>
    @php
      $extras = array_filter([
        !empty($combo->incluye) ? implode(' + ', $combo->incluye) : null,
        !empty($combo->regalos) ? implode(' + ', $combo->regalos) . ' de regalo' : null,
      ]);
    @endphp
    <div class="name-product">
      <h3>{{ $combo->producto->nombre }}</h3>
      @if(!empty($extras))
        <div style="font-size:12.5px;color:#16a34a;font-weight:600;">
          <i class="fas fa-gift"></i> + {{ implode(' + ', $extras) }}
        </div>
      @endif
    </div>

    <div class="text-center mb-2">
      @if(!empty($combo->precio_desde) && $combo->variantes->count() > 1)
        <div style="font-size:13px;color:#475569;">
          @foreach($combo->variantes as $variante)
            <div class="d-flex justify-content-between">
              <span>{{ $variante->plaza }} <span class="text-muted">{{ $variante->medida }}</span></span>
              <span class="fw-bold">${{ number_format($variante->precio, 0, ',', '.') }}</span>
            </div>
          @endforeach
        </div>
      @else
        <span class="fw-bold">${{ number_format($combo->display_price, 2, ',', '.') }}</span>
      @endif
    </div>

    <div class="text-center div-button-cart d-flex flex-column gap-2">
      <a class="btn btn-add-prod" href="{{ url('producto/' . $combo->producto->slug) }}">Armar combo</a>
      @if(!empty($arrayEmpresa['whatsapp']))
      <a class="btn-whatsapp-prod" target="_blank" rel="noopener noreferrer"
         href="https://wa.me/{{ preg_replace('/\D/', '', $arrayEmpresa['whatsapp']) }}?text={{ urlencode('Hola! Quiero consultar por el combo ' . $combo->producto->nombre . ' a $' . number_format($combo->display_price, 0, ',', '.') . '. Lo vi acá: ' . url('producto/' . $combo->producto->slug)) }}">
        <i class="fa-brands fa-whatsapp"></i> Pedir por WhatsApp
      </a>
      @endif
    </div>
  </div>
</div>
