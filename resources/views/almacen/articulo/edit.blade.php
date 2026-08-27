@extends('layouts.admin')
@section('contenido')
<style>
    .select-slim-variantes.ss-main {
        width: 100%;
        height: calc(2.25rem + 2px);
    }
    .show-variante-product{
        height:390px;
        overflow: auto;    
    }
</style>
<section class="section margindivsection">
    <div class="card">
        <div class="modal-body">
            @include('almacen.articulo.form_product')        
        </div>
    </div>
</section>

@endsection
@section('scripts')
    <script src="{{ asset('js/funciones_articulo/create_articulo.js') }}?v={{ filemtime(public_path('js/funciones_articulo/create_articulo.js')) }}"></script>

    @php
        // ✅ Atributos confirmados
        $atributosJson = $atributos->map(function($a) {
            return [
                'nombre'    => $a->nombre,
                'variantes' => $a->variantes->pluck('valor')->values()->toArray(),
            ];
        })->values()->toArray();

        // ✅ Combinaciones (solo valores del json_detalle, manejando string/array)
        $combinacionesJson = $combinaciones->map(function($c) {
            return is_string($c->json_detalle)
                ? json_decode($c->json_detalle, true)
                : $c->json_detalle;
        })->values()->toArray();

        // ✅ Precios + idcombinacion + sku + imagen de la variante
        $preciosCombinacionesJson = $combinaciones->map(function($c) {
            return [
                'idcombinacion'    => $c->idcombinacion,
                'sku'              => $c->sku,
                'pcompra_variante' => $c->pcompra_variante,
                'pventa_variante'  => $c->pventa_variante,
                'imagen_url'       => $c->imagen ? asset($c->imagen->path) : null,
            ];
        })->values()->toArray();
    @endphp

    <script>
        // Cargar datos desde PHP ya serializados
        window.atributosConfirmados   = {!! json_encode($atributosJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
        const combinaciones           = {!! json_encode($combinacionesJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
        const preciosCombinaciones    = {!! json_encode($preciosCombinacionesJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

        // Inicialización
        document.addEventListener("DOMContentLoaded", function () {
            window.combinacionesFinales   = combinaciones;
            window.preciosCombinaciones   = preciosCombinaciones;

            renderCombinacionesTable(combinaciones, preciosCombinaciones);
        });
    </script>
@endsection