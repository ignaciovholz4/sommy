@extends('layouts.admin')
@section('contenido')

<section class="section margindivsection">
    <div class="card">
  <div class="card-header">
    <b>Edicion masiva de precios</b>
  </div>
  <div class="card-body">
    <div class="row">
        <div class="col-md-4">
            <label for="inputPassword5" class="form-label">Password</label>
            <select class="form-select form-select-sm" aria-label="Default select example">
                <option selected>Open this select menu</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="inputPassword5" class="form-label">Password</label>
            <select class="form-select form-select-sm" aria-label="Default select example">
                <option selected>Open this select menu</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="inputPassword5" class="form-label">Password</label>
            <input type="password" id="inputPassword5" class="form-control form-control-sm" aria-describedby="passwordHelpBlock">
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="d-grid gap-2">
            <button class="btn btn-primary btn-sm" type="button">Aplicar descuentos generales</button>
            </div>
        </div>
    </div>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table id="producto_table" class="table table-bordered table-hover">
        <thead>
            <th>#</th>
            <th>Codigo</th>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>stock</th>
            <th>P. compra</th>
            <th>P. venta</th>
            <th>Acciones</th>
        </thead>
      </table>
    </div>
  </div>
</div>
</section>

@endsection
@section('scripts')
  <script src="{{asset('js/funciones_articulo/articulos.js')}}"></script> 
@endsection