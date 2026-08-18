@extends('layouts.admin')

@section('contenido')

@include('sucursal.stock_header')

<section class="section margindivsection">
    @include('sucursal.stock_list')
</section>

@endsection

@section('scripts')
<script src="{{ asset('js/funciones_sucursal/stock.js') }}"></script>
@endsection