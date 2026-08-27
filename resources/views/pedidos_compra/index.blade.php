@extends('layouts.admin')
@section('contenido')

<style>
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-blue: #00A3E0;
        --facturarg-bg: #F8FAFC;
        --facturarg-border: #e2e8f0;
    }

    .margindivsection { margin-top: 24px; }

    .card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        padding: 10px;
    }

    .card-body { padding: 15px !important; }

    #pedidos_table thead th {
        background-color: var(--facturarg-dark);
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 1px;
        font-weight: 700;
        padding: 20px 15px;
        border: none;
    }

    #pedidos_table tbody td {
        padding: 18px 15px;
        color: #475569;
        font-weight: 500;
    }

    .table-hover tbody tr { transition: all 0.2s ease; }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 163, 224, 0.04);
        transform: scale(1.002);
    }

    .modal-content {
        border: none;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: var(--facturarg-dark);
        color: white;
        border-radius: 24px 24px 0 0;
        padding: 24px;
        border-bottom: none;
    }

    .modal-title { font-weight: 800; letter-spacing: -0.5px; }

    .form-group label, .form-label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #64748b;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .form-group p, .data-display-box {
        background: var(--facturarg-bg);
        padding: 14px 18px;
        border-radius: 12px;
        font-weight: 600;
        color: var(--facturarg-dark);
        border: 1px solid var(--facturarg-border);
        margin-bottom: 0;
    }

    #detallesPedido thead th {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 15px;
        border: none;
    }

    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 15px;
    }
</style>

<section class="section margindivsection">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-file-signature mr-2"></i> Generador de Pedidos de Compra</h4>
            <div>
                @can('haveaccess', 'compras.reposicion.index')
                <a href="{{ route('finanzas.reposicion.ajustes') }}" class="btn btn-outline-secondary" title="Ajustes de reposición inteligente">
                    <i class="fas fa-sliders-h"></i>
                </a>
                @endcan
                @can('haveaccess', 'compras.reposicion.generar')
                <form action="{{ route('finanzas.reposicion.generar-ahora') }}" method="POST" class="d-inline" onsubmit="return confirm('¿Generar pedidos de compra borrador según la reposición inteligente ahora?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-robot mr-1"></i> Generar reposición ahora
                    </button>
                </form>
                @endcan
                <a href="{{ route('pedidos-compra.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle mr-1"></i> Nuevo Pedido
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive rounded-3">
                <table id="pedidos_table" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Folio</th>
                            <th>Proveedor</th>
                            <th>Fecha</th>
                            <th>Tipo comprobante</th>
                            <th>Sucursal</th>
                            <th>Total Neto</th>
                            <th>Total con IVA</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="ModalDetallePedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-signature me-2"></i> Detalle del Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Proveedor</label>
                            <p id="pedido_proveedor">---</p>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Fecha</label>
                            <p id="pedido_fecha">---</p>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>N° Folio</label>
                            <p id="pedido_folio">---</p>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Sucursal</label>
                            <p id="pedido_sucursal">---</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4" id="pedido_obs_wrapper" style="display:none;">
                    <div class="col-12">
                        <div class="form-group">
                            <label>Observaciones</label>
                            <p id="pedido_observaciones">---</p>
                        </div>
                    </div>
                </div>

                <div class="table-responsive rounded-4 border">
                    <table id="detallesPedido" class="table text-center align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Artículo</th>
                                <th>Cant.</th>
                                <th>Precio Unit.</th>
                                <th>Lista</th>
                                <th>Desc.</th>
                                <th>IVA</th>
                                <th>Neto</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="show_details_pedido"></tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="7" class="text-end fw-bold py-3">Total Neto:</td>
                                <td id="pedido_total_neto" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end fw-bold py-3">IVA:</td>
                                <td id="pedido_iva_discriminado"></td>
                            </tr>
                            <tr style="font-size: 1.1rem; color: var(--facturarg-blue);">
                                <td colspan="7" class="text-end fw-bold py-4">TOTAL FINAL:</td>
                                <td><strong id="pedido_total_con_iva"></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div id="pedido_adjuntos_wrapper" class="mt-3" style="display:none">
                    <h6 class="fw-bold"><i class="fas fa-paperclip me-1"></i> Comprobantes adjuntos</h6>
                    <div id="pedido_adjuntos" class="d-flex flex-wrap" style="gap:10px"></div>
                </div>

                <div class="mt-3">
                    @include('adjuntos._panel', ['panelId' => 'adjuntosPanelPedidoModal', 'tipo' => 'pedido_compra', 'id' => null])
                </div>

                <div class="mt-3">
                    @include('notas._panel', ['panelId' => 'notasPanelPedidoModal', 'tipo' => 'pedido_compra', 'id' => null])
                </div>
            </div>
        </div>
    </div>
</div>

@push('ScriptComprasIndex')
<script src="{{ asset('js/funciones_compra/pedidos_index.js') }}?v={{ filemtime(public_path('js/funciones_compra/pedidos_index.js')) }}"></script>
@endpush
@endsection
