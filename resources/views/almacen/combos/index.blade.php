@extends('layouts.admin')
@section('contenido')
<meta name="csrf-token" content="{{ csrf_token() }}">

<section class="margindivsection">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <h4 class="mb-0">Combos</h4>
        <button type="button" class="btn btn6" id="btnshowmodalcombo">
            <i class="fas fa-layer-group mr-2"></i> <strong>Crear combo</strong>
        </button>
    </div>
    <p class="text-muted small mt-2 mb-0">
        Un combo es un producto existente (ej. un colchón) al que le sumás productos relacionados con descuento (ej. una base) y, si querés, un regalo gratis (ej. almohadas) con la cantidad que definas acá.
    </p>
</section>

<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <table id="combo_table" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Descuento</th>
                        <th>Relacionados (con descuento)</th>
                        <th>Regalos (gratis)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

{{-- MODAL: crear/editar combo --}}
<div class="modal fade" id="ModalCombo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header style-modal-form">
                <h5 class="modal-title">Combo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body style-modal-form">
                <form id="form_combo">
                    <input type="hidden" id="combo_id_original" value="">

                    <div class="form-group">
                        <label for="combo_producto_id">Producto principal <small class="text-muted">(el que dispara el armador de combo en su ficha)</small></label>
                        <select id="combo_producto_id" name="producto_id" class="form-control" required>
                            <option value="">Elegí un producto...</option>
                            @foreach($productos as $p)
                                <option value="{{ $p->idarticulo }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="combo_descuento_pct">Descuento por combo (%)</label>
                        <input type="number" id="combo_descuento_pct" name="combo_descuento_pct" class="form-control"
                            placeholder="Ej: 10" min="0.01" max="100" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="combo_relacionados">Productos relacionados <small class="text-muted">(se ofrecen con el descuento de arriba)</small></label>
                        <select id="combo_relacionados" name="relacionados[]" class="form-control" multiple>
                            @foreach($productos as $p)
                                <option value="{{ $p->idarticulo }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label><i class="fa-solid fa-gift text-danger me-1"></i> Regalos <small class="text-muted">(se llevan gratis, no descontados — elegí producto y cantidad)</small></label>
                        <div id="combo_regalos_rows"></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnAgregarRegaloRow">
                            <i class="fas fa-plus"></i> Agregar regalo
                        </button>
                    </div>

                    @include('custom.validate_save_form_ajax')
                </form>
            </div>
            <div class="modal-footer style-modal-form">
                <button type="button" class="btn btn5" data-dismiss="modal"><i class="fas fa-window-close mr-2"></i>Cerrar</button>
                <button type="button" class="btn btn6" id="btnGuardarCombo"><i class="fas fa-check-circle text-success mr-2"></i>Guardar</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script>
    window.PRODUCTOS_COMBO = @json($productos->map(fn($p) => ['id' => $p->idarticulo, 'nombre' => $p->nombre]));
</script>
<script src="{{ asset('js/funciones_articulo/combo.js') }}?v={{ filemtime(public_path('js/funciones_articulo/combo.js')) }}"></script>
@endsection
