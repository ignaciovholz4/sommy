@extends('ecommerce.layouts.main-ecommerce')

@section('meta_title', 'Todos los productos | Sommy')

@section('contentEcommerce')
<style>
.product-name{
  height:50px; overflow: hidden; text-overflow: ellipsis;
}
.cat-chip {
    display: inline-block;
    padding: 8px 18px;
    border-radius: 999px;
    border: 1.5px solid #E7EAF2;
    font-size: 13.5px;
    font-weight: 500;
    color: #47536F;
    text-decoration: none;
    transition: all .15s;
    white-space: nowrap;
}
.cat-chip:hover { border-color: #1B2B5A; color: #1B2B5A; }
.cat-chip.activa { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }
.cat-chips-row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 8px; }
</style>
<section class="py-5">
  <div class="container-fluid">

    <div class="tabs-header d-flex flex-wrap align-items-end justify-content-between border-bottom my-5 gap-2">
      <h3 class="mb-2">Todos los productos</h3>
      <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
        <span class="text-muted">{{ $getDataProd->total() }} producto(s)</span>
        <form method="GET" action="" class="d-flex align-items-center gap-2">
          @if(request('categoria'))
            <input type="hidden" name="categoria" value="{{ request('categoria') }}">
          @endif
          <label for="ordenSelect" class="text-muted small mb-0" style="white-space:nowrap;">Ordenar por</label>
          <select name="orden" id="ordenSelect" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <option value="" {{ request('orden') === null || request('orden') === '' ? 'selected' : '' }}>Nombre (A-Z)</option>
            <option value="precio_asc" {{ request('orden') === 'precio_asc' ? 'selected' : '' }}>Menor precio</option>
            <option value="precio_desc" {{ request('orden') === 'precio_desc' ? 'selected' : '' }}>Mayor precio</option>
            <option value="nuevos" {{ request('orden') === 'nuevos' ? 'selected' : '' }}>Más nuevos</option>
            <option value="ofertas" {{ request('orden') === 'ofertas' ? 'selected' : '' }}>Mejores ofertas</option>
          </select>
        </form>
      </div>
    </div>

    {{-- Chips de categorías --}}
    <div class="cat-chips-row">
      <a href="{{ url('/productos') }}{{ request('orden') ? '?orden=' . request('orden') : '' }}"
         class="cat-chip {{ empty($categoriaActiva) ? 'activa' : '' }}">Todas</a>
      @foreach ($categorias as $catChip)
      <a href="{{ url('/productos') }}?categoria={{ $catChip->slug }}{{ request('orden') ? '&orden=' . request('orden') : '' }}"
         class="cat-chip {{ $categoriaActiva === $catChip->slug ? 'activa' : '' }}">{{ $catChip->nombre }}</a>
      @endforeach
    </div>
    <br>
    @if($getDataProd->isEmpty())
      <div class="alert alert-light border text-center my-5">
        No hay productos publicados en esta selección.
      </div>
    @endif

    <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 justify-content-start mt-2">
      @foreach ($getDataProd as $product)
        <div class="col" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 4) * 80 }}">
          <div class="product-item">
            @if($product->has_offer)
              <span class="badge bg-success position-absolute m-3">
                <i class="fas fa-tags"></i> Oferta
              </span>
            @endif

            <figure>
              <a href="{{ url('producto/'.$product->producto->slug) }}" title="{{ $product->producto->nombre }}">
                <img src="{{asset('imagenes/articulos/'.$product->producto->imagen )}}" class="tab-image">
              </a>
            </figure>
            <div class="product-name">
              <h3>{{ $product->producto->nombre }}</h3>
            </div>

            @php
                $miniSpecs = [];
                if ($product->producto->plazas) {
                    $miniSpecs[] = \App\Models\Articulo::PLAZAS[$product->producto->plazas] ?? $product->producto->plazas;
                }
                if ($product->producto->firmeza) {
                    $miniSpecs[] = 'Firmeza ' . strtolower(\App\Models\Articulo::FIRMEZAS[$product->producto->firmeza] ?? $product->producto->firmeza);
                }
                if ($product->producto->altura_cm) {
                    $miniSpecs[] = rtrim(rtrim(number_format($product->producto->altura_cm, 1, ',', '.'), '0'), ',') . ' cm';
                }
            @endphp
            <span class="qty">{{ implode(' · ', $miniSpecs) }}</span><span class="rating"></span>

            @if($product->has_offer && $product->display_price < $product->producto->pventa_con_iva)
              <div class="price-container">
                <span class="price original-price text-muted text-decoration-line-through">{{ format_money_global($product->producto->pventa_con_iva) }}</span>
                <span class="price effective-price fw-bold">{{ format_money_global($product->display_price) }}</span>
              </div>
            @else
              <span class="price">{{ format_money_global($product->display_price) }}</span>
            @endif

            <div class="text-center">
              <a href="{{ url('producto/'.$product->producto->slug) }}" class="btn btn-add-prod">Ver producto</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
      {{ $getDataProd->links() }}
    </div>

  </div>
</section>
@endsection
