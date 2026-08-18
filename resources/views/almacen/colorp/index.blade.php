
@extends('layouts.admin')
@section('contenido')
@include('almacen.colorp.header')
<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="color_table" class="table table-bordered table-hover">
                    <thead>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Hexadecimal</th>
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
<script src="{{asset('js/funciones_articulo/color.js')}}"></script>    
@endpush
@endsection