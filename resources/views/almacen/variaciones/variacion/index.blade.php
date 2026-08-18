
@extends('layouts.admin')
@section('contenido')
@include('almacen.variaciones.variacion.header')
<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="variante_table" class="table table-bordered table-hover">
                    <thead>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Fecha de registro</th>
                        <th>Acciones</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
<div class="row">
    <div class="lg-12 col-md-12 col-sm-12 col-xs-12">
        <div>
             
        </div>
    </div>
</div>

@push('ScriptCategoria')
<script src="{{asset('js/funciones_variacion/variacion.js')}}"></script>    
@endpush
@endsection