@extends('layouts.admin')
@section('contenido')
@include('almacen.articulo.header')

<section class="section margindivsection">
  @include('almacen.articulo.list_product')
</section>

@endsection
@section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <script src="{{asset('js/funciones_articulo/articulo_list.js')}}?v={{ filemtime(public_path('js/funciones_articulo/articulo_list.js')) }}"></script>
@endsection