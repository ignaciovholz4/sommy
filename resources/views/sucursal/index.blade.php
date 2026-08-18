@extends('layouts.admin')

@section('contenido')

@include('sucursal.header')

<section class="section margindivsection">
    @include('sucursal.list')
</section>

@endsection

@section('scripts')
<script src="{{ asset('js/funciones_sucursal/sucursales.js') }}"></script>
@endsection