@extends('layouts.admin')

@section('contenido')
@include('presupuestos.header')

<style>
    /* Consistencia de Branding Facturarg - Módulo Presupuestos */
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-blue: #00A3E0;
        --facturarg-green: #66DD7D; /* Color distintivo de presupuestos */
        --facturarg-bg: #F8FAFC;
    }

    .margindivsection { margin-top: 20px; }

    /* Tarjeta y Tabla principal */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    #quote_table thead th {
        background-color: var(--facturarg-dark);
        color: #ffffff;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        font-weight: 700;
        padding: 15px;
        border: none;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(102, 221, 125, 0.05); /* Hover sutil en verde */
        transition: background 0.2s ease;
    }

    /* Estilización de Modales */
    .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        background: var(--facturarg-dark);
        color: white;
        border-radius: 20px 20px 0 0;
        padding: 20px;
    }

    .modal-title {
        font-weight: 800;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Campos de detalle dentro del Modal */
    .form-group label {
        color: var(--facturarg-green); /* Etiqueta en verde presupuestos */
        font-weight: 700;
        font-size: 0.85rem;
        margin-bottom: 5px;
        display: block;
    }

    .form-group p {
        background: var(--facturarg-bg);
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: 500;
        color: var(--facturarg-dark);
        min-height: 38px;
    }

    /* Tabla interna de ítems */
    #detallesPresupuesto thead th {
        background: var(--facturarg-bg);
        color: var(--facturarg-dark);
        border-bottom: 2px solid var(--facturarg-green);
    }

    .panel-primary {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-top: 20px;
        padding: 15px;
    }

    /* Resalte de totales */
    #detailTotalConIva {
        color: var(--facturarg-green);
        font-size: 1.2rem;
        font-weight: 800;
    }
</style>

<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="quote_table" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Fecha</th>
                            <th>Folio</th>
                            <th>Total Neto</th>
                            <th>Total con IVA</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="modal fade" id="ModalDetalleQoute" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Detalle del presupuesto</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="container-fluid">     
                <div class="row g-3">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Cliente</label>
                            <p id="detalle_cliente">---</p>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Fecha</label>
                            <p id="detalle_fecha">---</p>
                        </div>
                    </div> 
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Folio</label>
                            <p id="detalles_folio">---</p>
                        </div>
                    </div>  
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Sucursal</label>
                            <p id="detalle_sucursal">---</p>
                        </div>
                    </div>  
                </div>

                <div class="panel panel-primary">
                    <div class="table-responsive">
                        <table id="detallesPresupuesto" class="table text-center align-middle">
                            <thead>
                                <tr>
                                    <th>Artículo</th>
                                    <th>Cant.</th>
                                    <th>Precio Unit.</th>
                                    <th>Lista</th>
                                    <th>Desc. (%)</th>
                                    <th>IVA (%)</th>
                                    <th>Subtotal Neto</th>
                                    <th>Total c/IVA</th>
                                </tr>
                            </thead>
                            <tbody id="showDetailQuote">
                                </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end">Total Neto</td>
                                    <td><span id="detailTotalNeto"></span></td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end">IVA discriminado</td>
                                    <td id="detailIvaDiscriminado"></td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end"><strong>Total con IVA</strong></td>
                                    <td><strong id="detailTotalConIva"></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>  
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
</section>

@push('ScriptVentasIndex')
<script src="{{ asset('js/funciones_presupuesto/index_presupuesto.js') }}"></script> 
@endpush
@endsection