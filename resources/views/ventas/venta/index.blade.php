@extends('layouts.admin')
@section('contenido')
@include('ventas.venta.header')

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="ventas_table" class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Teléfono</th>
                        <th>Fecha</th>
                        <th>Folio</th>
                        <th>Tipo</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('ventas.venta.reimpresion')

<div class="modal fade" id="ModalDetalleVenta" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header modal-header-dark">
        <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-shopping-cart me-2"></i>Detalle de la venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="dev-data-box"><strong>Cliente</strong><span id="detalle_cliente"></span></div>
            </div>
            <div class="col-md-4">
                <div class="dev-data-box"><strong>Tipo</strong><span id="detalle_tipo"></span></div>
            </div>
            <div class="col-md-4">
                <div class="dev-data-box"><strong>Folio</strong><span id="detalles_folio"></span></div>
            </div>
        </div>
        <div class="table-responsive">
            <table id="detalles" class="table table-sm align-middle text-center">
                <thead>
                    <tr>
                        <th>Artículo</th>
                        <th>Cantidad</th>
                        <th>Precio Venta</th>
                        <th>Descuento</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="show_details_sale"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold text-dark">Total</td>
                        <td class="fw-bold text-dark"><strong id="details_total_sale"></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

@push('ScriptVentasIndex')
<script src="{{asset('js/funciones_venta/ventas_index.js')}}"></script>
@endpush
@endsection
