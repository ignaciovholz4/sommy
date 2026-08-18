@extends('ecommerce.layouts.main-ecommerce')

@section('meta_title', 'Categoría: ' . $getCategory[0]->nombre)

@section('contentEcommerce')
<style>
.product-name{
  height:50px; overflow: hidden; text-overflow: ellipsis;
}
.sidebar-filtros .form-check-label { font-size: .9rem; }
.sidebar-filtros h6 {
  text-transform: uppercase; font-size: .78rem; letter-spacing: .5px;
  color: #6c757d; margin-top: 1rem;
}
</style>
<section class="py-5">
  <div class="container-fluid">
    <div class="row">

      {{-- Sidebar de filtros: siempre cerrado, se despliega con el botón Filtros --}}
      <div class="col-md-3 col-lg-2" id="colFiltros" style="display:none;">
        <div class="sidebar-filtros border rounded p-3">
          <h5 class="mb-0">Filtrar</h5>
          <form method="GET" action="">
            @php
              $etiquetasFirmeza = \App\Models\Articulo::FIRMEZAS;
              $etiquetasTipo = \App\Models\Articulo::TIPOS_COLCHON;
              $etiquetasPlazas = \App\Models\Articulo::PLAZAS;
              $selFirmeza = (array) request('firmeza', []);
              $selTipo = (array) request('tipo', []);
              $selPlazas = (array) request('plazas', []);
              $selMarca = (array) request('marca', []);
            @endphp

            @if($opcionesFiltro['firmezas']->isNotEmpty())
              <h6>Firmeza</h6>
              @foreach ($opcionesFiltro['firmezas'] as $valor)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="firmeza[]" value="{{ $valor }}"
                         id="f-firmeza-{{ $valor }}" {{ in_array($valor, $selFirmeza) ? 'checked' : '' }}>
                  <label class="form-check-label" for="f-firmeza-{{ $valor }}">{{ $etiquetasFirmeza[$valor] ?? $valor }}</label>
                </div>
              @endforeach
            @endif

            @if($opcionesFiltro['tipos']->isNotEmpty())
              <h6>Tipo</h6>
              @foreach ($opcionesFiltro['tipos'] as $valor)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="tipo[]" value="{{ $valor }}"
                         id="f-tipo-{{ $valor }}" {{ in_array($valor, $selTipo) ? 'checked' : '' }}>
                  <label class="form-check-label" for="f-tipo-{{ $valor }}">{{ $etiquetasTipo[$valor] ?? $valor }}</label>
                </div>
              @endforeach
            @endif

            @if($opcionesFiltro['plazas']->isNotEmpty())
              <h6>Plazas</h6>
              @foreach ($opcionesFiltro['plazas'] as $valor)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="plazas[]" value="{{ $valor }}"
                         id="f-plazas-{{ $valor }}" {{ in_array($valor, $selPlazas) ? 'checked' : '' }}>
                  <label class="form-check-label" for="f-plazas-{{ $valor }}">{{ $etiquetasPlazas[$valor] ?? $valor }}</label>
                </div>
              @endforeach
            @endif

            @if($opcionesFiltro['marcas']->count() > 1)
              <h6>Marca</h6>
              @foreach ($opcionesFiltro['marcas'] as $marcaOp)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="marca[]" value="{{ $marcaOp->idmarca }}"
                         id="f-marca-{{ $marcaOp->idmarca }}" {{ in_array($marcaOp->idmarca, $selMarca) ? 'checked' : '' }}>
                  <label class="form-check-label" for="f-marca-{{ $marcaOp->idmarca }}">{{ $marcaOp->nombre }}</label>
                </div>
              @endforeach
            @endif

            @if($opcionesFiltro['hayOfertas'] || $opcionesFiltro['hayPillowTop'])
              <h6>Destacados</h6>
              @if($opcionesFiltro['hayOfertas'])
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="oferta" value="1"
                       id="f-oferta" {{ request()->boolean('oferta') ? 'checked' : '' }}>
                <label class="form-check-label" for="f-oferta">Solo ofertas</label>
              </div>
              @endif
              @if($opcionesFiltro['hayPillowTop'])
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="pillow" value="1"
                       id="f-pillow" {{ request()->boolean('pillow') ? 'checked' : '' }}>
                <label class="form-check-label" for="f-pillow">Con pillow top</label>
              </div>
              @endif
            @endif

            <h6>Altura mínima</h6>
            <div class="input-group input-group-sm mb-2">
              <input type="number" class="form-control" name="altura_min" placeholder="Ej: 25"
                     min="0" value="{{ request('altura_min') }}">
              <span class="input-group-text">cm</span>
            </div>

            <h6>Precio</h6>
            <div class="d-flex gap-1 mb-2">
              <input type="number" class="form-control form-control-sm" name="precio_min" placeholder="Mín"
                     min="0" value="{{ request('precio_min') }}">
              <input type="number" class="form-control form-control-sm" name="precio_max" placeholder="Máx"
                     min="0" value="{{ request('precio_max') }}">
            </div>

            @if(request('orden'))
              <input type="hidden" name="orden" value="{{ request('orden') }}">
            @endif

            <button type="submit" class="btn btn-dark btn-sm w-100 mt-2">Aplicar</button>
            @if(request()->hasAny(['firmeza', 'tipo', 'plazas', 'marca', 'oferta', 'pillow', 'altura_min', 'precio_min', 'precio_max']))
              <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm w-100 mt-1">Limpiar filtros</a>
            @endif
          </form>
        </div>
      </div>

      {{-- Ancho completo con filtros cerrados; se ajusta al abrirlos --}}
      <div class="col-12" id="colProductos">

        <div class="bootstrap-tabs product-tabs">
          <div class="tabs-header d-flex flex-wrap align-items-end justify-content-between border-bottom my-5 gap-2">
            <h3 class="mb-2">{{ $getCategory[0]->nombre }}</h3>
            <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
              <button type="button" id="btnFiltros"
                      class="btn d-flex align-items-center gap-2"
                      style="border:1.5px solid #1B2B5A;color:#1B2B5A;background:transparent;border-radius:999px;padding:7px 18px;font-size:13.5px;font-weight:500;">
                <i class="fa-solid fa-sliders"></i> Filtros
              </button>
              <span class="text-muted">{{ $getDataProd->total() }} producto(s)</span>
              <form method="GET" action="" class="d-flex align-items-center gap-2">
                @foreach (request()->except(['orden', 'page']) as $qk => $qv)
                  @if(is_array($qv))
                    @foreach ($qv as $qvItem)
                      <input type="hidden" name="{{ $qk }}[]" value="{{ $qvItem }}">
                    @endforeach
                  @else
                    <input type="hidden" name="{{ $qk }}" value="{{ $qv }}">
                  @endif
                @endforeach
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
          <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-all" role="tabpanel" aria-labelledby="nav-all-tab">

              @if($getDataProd->isEmpty())
                <div class="alert alert-light border text-center my-5">
                  No hay productos que coincidan con los filtros seleccionados.
                </div>
              @endif

              {{-- Grilla alineada a la izquierda: 1 por fila en mobile, más anchas en desktop --}}
              <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 justify-content-start">
                @foreach ($getDataProd as $product)
                  <div class="col" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 4) * 80 }}">
                    <div class="product-item">

                      {{-- Badge de oferta solo si corresponde --}}
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

                      {{-- Precio efectivo (con tachado si hay oferta) --}}
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
              <!-- / product-grid -->

              <div class="d-flex justify-content-center mt-4">
                {{ $getDataProd->links() }}
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnFiltros');
    var col = document.getElementById('colFiltros');
    if (!btn || !col) return;
    var colProd = document.getElementById('colProductos');
    btn.addEventListener('click', function () {
        var abierto = col.style.display !== 'none';
        col.style.display = abierto ? 'none' : '';
        btn.style.background = abierto ? 'transparent' : '#1B2B5A';
        btn.style.color = abierto ? '#1B2B5A' : '#fff';
        // Productos: ancho completo cuando los filtros están cerrados
        if (colProd) {
            if (abierto) {
                colProd.classList.remove('col-md-9', 'col-lg-10');
                colProd.classList.add('col-12');
            } else {
                colProd.classList.remove('col-12');
                colProd.classList.add('col-md-9', 'col-lg-10');
            }
        }
    });
});
</script>
@endsection
