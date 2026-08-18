@extends('layouts.admin')

@section('contenido')
<style>
    /* Estética Unificada Facturarg */
    :root {
        --facturarg-dark: #0f172a;    /* Azul Oxford */
        --facturarg-cyan: #1591a3;    /* Cian de acciones */
        --facturarg-bg: #f1f5f9;      /* Gris claro de fondo */
        --facturarg-accent: #22d3ee;  /* Cian brillante */
    }

    .main-container {
        background-color: var(--facturarg-bg);
        min-height: 100vh;
        padding: 2rem;
    }

    /* Botón Principal (Estilo "Realizar una venta") */
    .btn-facturarg-main {
        background-color: var(--facturarg-dark);
        color: white;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .btn-facturarg-main:hover {
        background-color: #1e293b;
        color: var(--facturarg-accent);
        transform: translateY(-1px);
    }

    /* Card y Tabla */
    .card-facturarg {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        background: white;
        overflow: hidden;
    }

    .table-facturarg thead th {
        background-color: var(--facturarg-dark);
        color: white;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
    }

    .table-facturarg tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.9rem;
    }

    .table-facturarg tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Estilos de Formulario en Modales */
    .modal-content {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }

    .modal-header-dark {
        background-color: var(--facturarg-dark);
        color: white;
        padding: 1.25rem;
    }

    .form-label-custom {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .form-control-custom {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.6rem 1rem;
    }

    .form-control-custom:focus {
        border-color: var(--facturarg-accent);
        box-shadow: none;
    }
</style>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0 text-dark">Gestión de Cajas</h2>
        <button class="btn-facturarg-main" data-bs-toggle="modal" data-bs-target="#modalNuevaCaja">
            <i class="fas fa-plus-circle me-2"></i> NUEVA CAJA
        </button>
    </div>

    <div class="card-facturarg">
        <div class="table-responsive">
            <table id="table_cajas" class="table table-facturarg mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nombre</th>
                        <th>Sucursal</th>
                        <th>Moneda</th>
                        <th>Estado</th>
                        <th class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header modal-header-dark">
                <h5 class="modal-title fw-bold">CREAR NUEVA CAJA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('caja.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-custom">Nombre de la caja</label>
                        <input type="text" class="form-control form-control-custom" name="nombre" placeholder="Ej: Caja Diaria" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Sucursal</label>
                        <div class="input-group">
                            <select class="form-select form-control-custom" name="sucursal_id" required>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalSucursal">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Moneda</label>
                        <select class="form-select form-control-custom" name="moneda_id" required>
                            @foreach($monedas as $moneda)
                                <option value="{{ $moneda->id }}">{{ $moneda->nombre }} ({{ $moneda->codigo }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="activa" name="activa" value="1" checked>
                        <label class="form-check-label fw-bold text-dark" for="activa">CAJA ACTIVA</label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-dark px-4 fw-bold">GUARDAR CAJA</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reutilizo la estructura de modal-header-dark y form-label-custom para mantener la coherencia --}}

@push('ScriptcajaGestor')
<script src="{{ asset('js/funciones_caja/gestor_caja.js') }}"></script>
@endpush
@endsection