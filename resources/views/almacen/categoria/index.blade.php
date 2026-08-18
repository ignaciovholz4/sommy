@extends('layouts.admin')
@section('contenido')
@include('almacen.categoria.header')

<style>
    /* ADN Visual Facturarg - Listado de Categorías */
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-blue: #00A3E0;
        --facturarg-bg: #F8FAFC;
        --facturarg-border: #e2e8f0;
    }

    .margindivsection { margin-top: 24px; }

    /* Card con sombreado suave y bordes amplios */
    .card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        padding: 10px; /* Espacio extra para que los controles no toquen los bordes */
    }

    .card-body {
        padding: 15px !important;
    }

    /* Header de Tabla Dark Estilo Facturarg */
    #categoria_table thead th {
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

    /* Padding profundo en las filas para dar "aire" */
    #categoria_table tbody td {
        padding: 20px 15px;
        color: #475569;
        font-weight: 500;
        vertical-align: middle;
        border-bottom: 1px solid var(--facturarg-border);
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 163, 224, 0.04);
        transform: scale(1.001);
    }

    /* Ajustes para DataTables (Buscador y Paginado) */
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_paginate {
        padding: 15px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.85rem;
    }
</style>

<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive rounded-3">
                <table id="categoria_table" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="50px">#</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th width="150px" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

@push('ScriptCategoria')
<script src="{{asset('js/funciones_categoria/categoria.js')}}"></script>     
@endpush
@endsection