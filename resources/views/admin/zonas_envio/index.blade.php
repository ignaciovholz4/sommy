@extends('layouts.admin')
@section('contenido')
<section class="section margindivsection">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-truck"></i> Zonas de envío</h4>
            <button class="btn btn-primary" id="btn-nueva-zona">
                <i class="fas fa-plus"></i> Nueva zona
            </button>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Las zonas activas aparecen como opciones de envío en el checkout de la tienda.
                Costo 0 se muestra como "Gratis / a coordinar". El orden define la posición en el listado.
            </p>
            <div class="table-responsive">
                <table id="zonas_table" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Costo</th>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

{{-- Modal alta/edición --}}
<div class="modal fade" id="modalZona" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalZonaTitle">Nueva zona de envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="form-zona">
                    <input type="hidden" id="zonaId" value="0">
                    <div class="form-group mb-2">
                        <label for="zona-nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="zona-nombre" maxlength="100"
                               placeholder="Ej: Córdoba Capital">
                    </div>
                    <div class="form-group mb-2">
                        <label for="zona-costo">Costo de envío ($) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="zona-costo" min="0" step="0.01" value="0">
                        <small class="text-muted">0 = gratis o a coordinar (ej: retiro en local)</small>
                    </div>
                    <div class="form-group mb-2">
                        <label for="zona-orden">Orden</label>
                        <input type="number" class="form-control" id="zona-orden" min="0" step="1" value="0">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="zona-activo" checked>
                        <label class="form-check-label" for="zona-activo">Activa (visible en el checkout)</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-zona">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="{{asset('js/funciones_configuracion/zonas_envio.js')}}"></script>
@endsection
