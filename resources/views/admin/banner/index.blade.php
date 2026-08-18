
@extends('layouts.admin')
@section('contenido')
@include('admin.banner.header')
<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="banner_table" class="table table-bordered table-hover">
                    <thead>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Nombre imagen</th>
                        <th>Nombre imagen movil</th>
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
@endsection
@section('scripts')
<script src="{{asset('js/funciones_configuracion/banner.js')}}"></script>    
@endsection