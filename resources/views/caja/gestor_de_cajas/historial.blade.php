@extends('layouts.admin')

@section('contenido')
<style>
    /* ADN Visual Facturarg - Padding y Estética */
    :root {
        --facturarg-dark: #0f172a;    
        --facturarg-cyan: #1591a3;    
        --facturarg-bg: #f1f5f9;      
        --facturarg-accent: #22d3ee;  
    }

    .main-container {
        background-color: var(--facturarg-bg);
        min-height: 100vh;
        padding: 3rem 2.5rem; /* Padding exterior amplio */
    }

    /* Botón de Acción Principal (Abrir Caja) */
    .btn-facturarg-main {
        background-color: var(--facturarg-dark);
        color: white;
        border-radius: 12px;
        padding: 16px 32px; /* Padding interno generoso */
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-facturarg-main:hover {
        background-color: #1e293b;
        color: var(--facturarg-accent);
        transform: translateY(-2px);
    }

    /* Card y Tabla con Espaciado Interno */
    .card-facturarg {
        border: none;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        background: #fff;
        overflow: hidden;
        margin-top: 2rem;
    }

    .card-header-facturarg {
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        padding: 2rem 2.5rem; /* Padding interno del header */
    }

    .table-facturarg thead th {
        background-color: var(--facturarg-dark);
        color: white;
        text-transform: uppercase;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 1px;
        padding: 1.5rem 2rem !important; /* Padding interno por dentro del recuadro */
        border: none;
    }

    .table-facturarg tbody td {
        padding: 1.5rem 2rem !important; /* Espacio interno de las celdas */
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.95rem;
    }

    /* Modal de Resumen Estilizado */
    .modal-content-facturarg {
        border: none;
        border-radius: 20px;
        overflow: hidden;
    }

    .modal-header-dark {
        background-color: var(--facturarg-dark);
        color: white;
        padding: 1.75rem 2.5rem;
    }

    .modal-body-large {
        padding: 2.5rem !important; /* Padding interno del modal */
    }

    .resumen-table th {
        background-color: #f8fafc;
        color: #64748b;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 1.2rem !important;
        border: 1px solid #edf2f7;
    }

    .resumen-table td {
        padding: 1.2rem !important;
        font-weight: 600;
        color: var(--facturarg-dark);
        border: 1px solid #edf2f7;
    }

</style>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold h2 text-dark mb-1">Control de Caja</h1>
            <p class="text-muted small mb-0">Gestione aperturas, cierres y flujo de efectivo.</p>
        </div>
        
        <button class="btn-facturarg-main btn-abrir-principal" data-id="{{ $caja->id }}">
            <i class="fas fa-plus-circle me-2"></i> ABRIR CAJA
        </button>
    </div>

    <div class="card-facturarg">
        <div class="card-header-facturarg">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-history me-2 text-muted"></i> Historial de: {{ $caja->nombre }}
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="table_historial" class="table table-facturarg mb-0">
                    <thead>
                        <tr>
                            <th class="ps-5">Fecha apertura</th>
                            <th>Fecha cierre</th>
                            <th>Fondo de caja</th>
                            <th>Estado</th>
                            <th class="text-center pe-5">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-5 d-flex justify-content-end">
        <a href="{{ route('caja.listado') }}" class="btn btn-link text-decoration-none text-muted fw-bold">
            <i class="fas fa-arrow-left me-2"></i> VOLVER AL GESTOR DE CAJAS
        </a>
    </div>
</div>

{{-- Modal Resumen con Estética Facturarg --}}
<div class="modal fade" id="detailCajaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-content-facturarg shadow-lg">
            <div class="modal-header modal-header-dark">
                <h5 class="modal-title fw-bold">RESUMEN DE OPERACIÓN</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body modal-body-large">
                <div class="table-responsive">
                    <table class="table resumen-table mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 35%">Empresa</th>
                                <td id="name_empresa" class="text-info">—</td>
                            </tr>
                            <tr>
                                <th>Fecha cierre</th>
                                <td id="date_operation">—</td>
                            </tr>
                            <tr>
                                <th>Cajero Responsable</th>
                                <td id="name_cajero">—</td>
                            </tr>
                            <tr>
                                <th>Fondo Inicial</th>
                                <td id="fondo_caja" class="text-dark">0.00</td>
                            </tr>
                            <tr>
                                <th>Ingresos Extra</th>
                                <td id="resumen_ingresos" class="text-success">+ 0.00</td>
                            </tr>
                            <tr>
                                <th>Egresos Extra</th>
                                <td id="resumen_egresos" class="text-danger">- 0.00</td>
                            </tr>
                            <tr>
                                <th>Total Ventas Efectivo</th>
                                <td id="sale_efectivo" class="fw-bold">0.00</td>
                            </tr>
                            <tr class="bg-light">
                                <th class="text-primary">Efectivo Final en Caja</th>
                                <td id="efectivo_caja" class="text-primary fw-bolder" style="font-size: 1.1rem;">0.00</td>
                            </tr>
                            <tr>
                                <th>Faltante</th>
                                <td id="faltante_caja" class="text-danger">0.00</td>
                            </tr>
                            <tr>
                                <th>Sobrante</th>
                                <td id="sobrante_caja" class="text-success">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light p-4 border-0">
                <button class="btn btn-dark px-5 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px;">
                    ENTENDIDO
                </button>
            </div>
        </div>
    </div>
</div>

@push('ScriptaperturaCierreCaja')
<script src="{{ asset('js/funciones_caja/apertura_cierre_caja.js') }}"></script>
@endpush
@endsection