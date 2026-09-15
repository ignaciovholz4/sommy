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
    <div class="name-product">
      <h3>{{ $combo->producto->nombre }}</h3>
      @if(!empty($combo->incluye))
        <div class="text-muted" style="font-size:11px;">+ {{ implode(' + ', $combo->incluye) }}</div>
      @endif
    </div>

    <div class="text-center mb-2">
      <span class="d-block" style="font-size:11px;color:#94a3b8;text-decoration:line-through;">${{ number_format($combo->precio_separado, 2, ',', '.') }} por separado</span>
      <span class="fw-bold">${{ number_format($combo->display_price, 2, ',', '.') }}</span>
    </div>

    <div class="text-center div-button-cart">
      <a class="btn btn-add-prod" href="{{ url('producto/' . $combo->producto->slug) }}">Armar combo</a>
    </div>
  </div>
</div>
