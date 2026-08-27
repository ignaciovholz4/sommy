@extends('ecommerce.layouts.main-ecommerce')

@section('meta_title', 'Buscar: ' . $busqueda)

@section('contentEcommerce')
<style>
.product-name{
  height:50px; overflow: hidden; text-overflow: ellipsis;
}
</style>
<section class="py-5">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">

        <div class="bootstrap-tabs product-tabs">
          <div class="tabs-header d-flex justify-content-between border-bottom my-5">
            <h3>Resultados para: "{{ $busqueda }}"</h3>
            <span class="text-muted align-self-end">{{ $getDataProd->total() }} producto(s)</span>
          </div>

          @if($getDataProd->isEmpty())
            <div class="alert alert-light border text-center my-5">
              No encontramos productos para "<strong>{{ $busqueda }}</strong>". Probá con otra palabra o navegá por las categorías.
            </div>
          @endif

          <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
            @foreach ($getDataProd as $product)
              <div class="col">
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

                  @if($product->precio_desde)
                    <span class="d-block" style="font-size:11px;color:#64748b;">Desde</span>
                  @endif
                  <span class="price">{{ format_money_global($product->display_price) }}</span>

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

      </div>
    </div>
  </div>
</section>
@endsection
