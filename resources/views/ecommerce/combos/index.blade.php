@extends('ecommerce.layouts.main-ecommerce')

@section('meta_title', 'Combos en oferta: colchón + base + almohadas | Sommy Córdoba')
@section('meta_description', 'Armá tu combo: colchón + base sommier + almohadas, más barato que comprando todo por separado. Fábrica en Córdoba, envío a toda la ciudad.')
@section('meta_keywords', 'combo colchón y sommier, colchón con base, oferta colchón almohadas, Sommy Córdoba')

@section('contentEcommerce')
<section class="py-5">
  <div class="container-fluid">

    <div class="tabs-header d-flex justify-content-between border-bottom my-5" data-aos="fade-up">
      <h3>Combos en oferta</h3>
    </div>
    <p class="text-muted mb-4" data-aos="fade-up">Colchón + base sommier + almohadas: armando el combo te sale más barato que comprando todo por separado. Elegí el tuyo y armá la medida exacta en la ficha del producto.</p>

    @if($getDataCombos->isEmpty())
      <div class="ec-placeholder-inline-hint mb-4">
        <i class="fa-solid fa-circle-info me-1"></i>
        Por ahora no hay combos activos.
      </div>
    @else
      <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 justify-content-start">
        @foreach ($getDataCombos as $combo)
          @include('ecommerce.partials.combo-card')
        @endforeach
      </div>
    @endif

  </div>
</section>
@endsection
