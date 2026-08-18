@extends('layouts.admin')
@section('contenido')

<section><br>
    @if($estaAbierta)
        <button id="btnAgregarMovimiento" class="btn btn-success">
            <i class="fas fa-plus-circle me-2"></i> Nuevo Movimiento
        </button>
    @endif
</section>

<section class="mb-3"><br>
  <div class="d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Movimientos de caja: {{ $caja->nombre }}</h5>
  </div>
</section>

<section>
  <div class="card">
    <div class="card-header">
      <div class="d-flex align-items-center gap-2">
        <span>Listado de movimientos</span>
        @if(!empty($aperturaId))
          <span class="badge bg-light text-dark">Apertura #{{ $aperturaId }}</span>
        @endif
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="table_movimientos"
            class="table table-bordered table-hover"
            data-caja-id="{{ $caja->id }}"
            data-apertura-id="{{ $aperturaId }}">
        <thead class="table-light">
            <tr>
            <th>Fecha</th>
            <th>Movimiento</th>
            <th>Cliente/Proveedor</th>
            <th>Comprobante</th>
            <th>Observaciones</th>
            <th>Efectivo</th>
            <th>Bancos</th>
            <th>Tarjetas</th>
            <th>Total</th>
            <th>Acciones</th>
            </tr>
        </thead>
        </table>
      </div>
    </div>
  </div>
</section>

<section>
    <div class="mt-3 d-flex justify-content-end">
        <a href="{{ route('caja.historial', $caja->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver al Historial
        </a>
    </div>
</section>

{{-- Modal Agregar Movimiento --}}
<div class="modal fade" id="agregarMovimientoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title">Agregar Movimiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formAgregarMovimiento">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Fecha</label>
              <input type="text" class="form-control" id="mov_fecha" value="{{ now()->format('d/m/Y H:i') }}" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Movimiento</label>
              <select class="form-select" id="mov_tipo" name="tipo" required>
                <option value="">Seleccione...</option>
                <option value="ingreso">Recibo (Ingreso)</option>
                <option value="egreso">Pago (Egreso)</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Cliente/Proveedor</label>
              <input type="text" class="form-control" id="mov_cliente" name="cliente_proveedor" placeholder="Nombre o concepto">
            </div>
            <div class="col-md-6">
              <label class="form-label">Comprobante</label>
              <input type="text" class="form-control" id="mov_comprobante" value="MPC-00001" disabled>
              <small class="text-muted">Se genera automáticamente (MPC/MRC)</small>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" id="mov_observaciones" name="observaciones" rows="2"></textarea>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Monto Efectivo</label>
              <input type="number" step="0.01" min="0" class="form-control" id="mov_efectivo" name="efectivo" placeholder="0.00">
            </div>
            <div class="col-md-6">
              <label class="form-label">Total</label>
              <input type="text" class="form-control" id="mov_total" value="0.00" disabled>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i> Cancelar
        </button>
        <button type="submit" form="formAgregarMovimiento" class="btn btn-success">
          <i class="fas fa-save me-2"></i> Guardar Movimiento
        </button>
      </div>
    </div>
  </div>
</div>

@push('ScriptMovimientosCaja')
<script src="{{ asset('js/funciones_caja/movimientos.js') }}"></script>
@endpush

@endsection