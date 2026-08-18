@extends('layouts.admin')
@section('contenido')
@include('ventas.cliente.header')

<style>
    /* ADN Visual Facturarg - Consistencia Total */
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-blue: #00A3E0;
        --facturarg-bg: #F8FAFC;
        --facturarg-border: #e2e8f0;
    }

    .margindivsection { margin-top: 24px; }

    /* Card con aire y sombras suaves */
    .card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        padding: 10px;
    }

    /* Header de la Card Estético */
    .card-header {
        background: transparent;
        border: none;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        color: var(--facturarg-dark);
        font-size: 1.1rem;
        padding: 20px 15px 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-header::before {
        content: '';
        width: 4px;
        height: 20px;
        background: var(--facturarg-blue);
        border-radius: 10px;
    }

    .card-body {
        padding: 15px !important;
    }

    /* Header de Tabla Dark - Identidad de Facturarg */
    #proveedor_table thead th {
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

    /* Filas con aire (Padding alto) */
    #proveedor_table tbody td {
        padding: 18px 15px;
        color: #475569;
        font-weight: 500;
        border-bottom: 1px solid var(--facturarg-border);
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 163, 224, 0.04);
        transform: scale(1.002);
    }

    /* Ajustes para DataTables */
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_paginate {
        padding: 15px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.85rem;
        color: #64748b;
    }

    /* Estilo para los botones de acción dentro de la tabla (opcional según tu JS) */
    .btn-action {
        padding: 8px 12px;
        border-radius: 10px;
        transition: all 0.2s;
    }
</style>

<section class="margindivsection">
    <div class="card">
        <h5 class="card-header">Lista de usuarios</h5>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table_customers" class="table  table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>nombre</th>
                            <th>Direccion</th>
                            <th>Telefono</th>
                            <th>correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
@push('ScriptCliente')
<script src="{{asset('js/funciones_cliente/cliente.js')}}"></script> 
@endpush
@endsection