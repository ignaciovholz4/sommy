@extends('layouts.admin')
@section('contenido')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .combo-img-thumb {
        position: relative;
        width: 110px;
        height: 110px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        overflow: hidden;
    }
    .combo-img-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .combo-img-thumb .combo-img-acciones {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        justify-content: space-between;
        background: rgba(15, 23, 42, 0.72);
    }
    .combo-img-thumb .combo-img-acciones button {
        border: 0;
        background: transparent;
        color: #fff;
        font-size: 11px;
        line-height: 1;
        padding: 5px 7px;
        cursor: pointer;
    }
    .combo-img-thumb .combo-img-acciones button[disabled] {
        opacity: 0.35;
        cursor: default;
    }
    .combo-img-thumb .combo-img-principal {
        position: absolute;
        top: 4px;
        left: 4px;
        background: #16a34a;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        border-radius: 4px;
        padding: 2px 5px;
    }
</style>

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
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Descuento</th>
                        <th>Relacionados (con descuento)</th>
                        <th>Regalos (gratis)</th>
                        <th>Precio de venta</th>
                        <th>Ganancia</th>
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
                        <label>
                            <i class="fa-solid fa-images text-primary me-1"></i> Imágenes del combo
                            <small class="text-muted">(la primera es la que se ve en la vidriera de combos, en lugar de la foto del producto suelto)</small>
                        </label>

                        <div id="combo_imagenes_vacio" class="text-muted small mb-2">
                            Elegí primero el producto principal para poder subir imágenes.
                        </div>

                        <div id="combo_imagenes_box" style="display:none;">
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <input type="file" id="combo_imagenes_input" class="form-control" accept="image/*" multiple style="max-width:320px;">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnSubirComboImagenes">
                                    <i class="fas fa-upload"></i> Subir
                                </button>
                                <span class="text-muted small">JPG, PNG o WEBP, hasta 8 MB cada una.</span>
                            </div>
                            <div id="combo_imagenes_grid" class="d-flex flex-wrap gap-2"></div>
                        </div>
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
