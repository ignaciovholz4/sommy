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
      <h3>
        {{ $combo->producto->nombre }}
        @if($combo->incluye_sommier)
          <span class="badge bg-primary" style="font-size:10px;vertical-align:middle;">+ Sommier</span>
        @endif
      </h3>
      @if(!empty($combo->incluye))
        <div style="font-size:12px;color:#16a34a;">
          <i class="fas fa-check-circle"></i> Incluye {{ implode(' + ', $combo->incluye) }}
        </div>
      @endif
      @if(!empty($combo->regalos))
        <div style="font-size:12px;color:#16a34a;">
          <i class="fas fa-gift"></i> + {{ implode(' + ', $combo->regalos) }} de regalo
        </div>
      @endif
    </div>

    <div class="text-center mb-2">
      <span class="d-block" style="font-size:11px;color:#94a3b8;">{{ $combo->incluye_sommier ? 'Combo colchón + sommier' : 'Precio del combo' }}</span>
      <span class="d-block" style="font-size:11px;color:#94a3b8;text-decoration:line-through;">${{ number_format($combo->precio_separado, 2, ',', '.') }} por separado</span>
      <span class="fw-bold">${{ number_format($combo->display_price, 2, ',', '.') }}</span>
    </div>

    <div class="text-center div-button-cart">
      <a class="btn btn-add-prod" href="{{ url('producto/' . $combo->producto->slug) }}">Armar combo</a>
    </div>
  </div>
</div>
