@extends('layouts.admin')
@section('contenido')
@include('admin.reels.header')
<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="reel_table" class="table table-bordered table-hover">
                    <thead>
                        <th>#</th>
                        <th>Título / leyenda</th>
                        <th>Link</th>
                        <th>Orden</th>
                        <th>Acciones</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
@section('scripts')
<script src="{{asset('js/funciones_configuracion/reels.js')}}?v={{ filemtime(public_path('js/funciones_configuracion/reels.js')) }}"></script>
@endsection
