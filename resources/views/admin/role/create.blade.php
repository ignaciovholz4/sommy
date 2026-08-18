@extends('layouts.admin')

@section('contenido')
<style>
    /* ADN Visual Facturarg - Creador de Roles */
    :root {
        --facturarg-dark: #0f172a;    
        --facturarg-cyan: #1591a3;    
        --facturarg-bg: #f1f5f9;      
    }

    .main-container {
        background-color: var(--facturarg-bg);
        min-height: 100vh;
        padding: 2rem;
    }

    .card-facturarg {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        background: #fff;
        margin-bottom: 1.5rem;
    }

    .card-header-facturarg {
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.5rem 2rem;
    }

    /* Estilo de Inputs y Checks */
    .style-input {
        background-color: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 15px;
        font-weight: 600;
        transition: 0.3s;
    }

    .style-input:focus {
        border-color: var(--facturarg-cyan);
        box-shadow: none;
        background-color: #fff;
    }

    /* Grid de Permisos */
    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }

    .permission-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        transition: 0.3s;
        height: 100%;
    }

    .permission-card:hover {
        border-color: var(--facturarg-cyan);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(21, 145, 163, 0.1);
    }

    .permission-title {
        background: #f8fafc;
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 16px 16px 0 0;
        font-weight: 800;
        color: var(--facturarg-dark);
        text-transform: uppercase;
        font-size: 0.85rem;
    }

    /* Custom Checkbox */
    .custom-check-container {
        padding: 10px 15px;
        border-radius: 8px;
        transition: 0.2s;
    }

    .custom-check-container:hover {
        background: #f1f5f9;
    }

    .checkbox-description {
        display: block;
        font-size: 0.75rem;
        color: #64748b;
        margin-left: 1.8rem;
    }
</style>

<div class="main-container">
    <form action="{{route('role.store')}}" method="post" autocomplete="off">
        @csrf

        <div class="card-facturarg">
            <div class="card-header-facturarg">
                <h4 class="fw-bold text-dark m-0"><i class="fas fa-shield-alt me-2 text-info"></i> Crear Nuevo Rol</h4>
            </div>
            <div class="card-body p-4">
                @include('custom.message')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold text-muted mb-2 uppercase">Nombre del Rol</label>
                        <input type="text" class="form-control style-input" name="name" id="name" value="{{old('name')}}" placeholder="Ej: Supervisor de Ventas">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold text-muted mb-2 uppercase">Descripción Funcional</label>
                        <textarea class="form-control style-input" name="description" id="description" rows="1" placeholder="¿Qué responsabilidades tiene este rol?">{{old('description')}}</textarea>
                    </div>
                    <input type="hidden" name="slug" id="slug" value="{{old('slug')}}">
                </div>

                <hr class="my-4" style="opacity: 0.1;">

                <div class="text-center">
                    <h5 class="fw-bold mb-1">¿Acceso Total al Sistema?</h5>
                    <p class="text-muted small mb-3">Si selecciona "Sí", el usuario ignorará las restricciones individuales.</p>
                    
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="full-access" id="fullaccessyes" value="yes" onclick="getvalueradio(this.value);" @if(old('full-access') == "yes") checked @endif>
                        <label class="btn btn-outline-success px-4 fw-bold" for="fullaccessyes">SÍ, ACCESO TOTAL</label>

                        <input type="radio" class="btn-check" name="full-access" id="fullaccessno" value="no" onclick="getvalueradio(this.value);" @if(old('full-access') != "yes") checked @endif>
                        <label class="btn btn-outline-secondary px-4 fw-bold" for="fullaccessno">NO, RESTRINGIDO</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="div_roles_permisos">
            <div class="permissions-grid">
                
                @php
                    $secciones = [
                        ['titulo' => 'Menú Principal', 'data' => $permissions, 'icon' => 'fa-home'],
                        ['titulo' => 'Caja y Finanzas', 'data' => $permission_caja, 'icon' => 'fa-cash-register'],
                        ['titulo' => 'Almacén e Inventario', 'data' => $permission_almacen, 'icon' => 'fa-boxes'],
                        ['titulo' => 'Compras', 'data' => $permission_compras, 'icon' => 'fa-shopping-cart'],
                        ['titulo' => 'Ventas', 'data' => $permission_ventas, 'icon' => 'fa-tag'],
                        ['titulo' => 'Devoluciones', 'data' => $permission_devolucion, 'icon' => 'fa-undo'],
                        ['titulo' => 'Reportes', 'data' => $permission_reports, 'icon' => 'fa-chart-line'],
                        ['titulo' => 'Configuración', 'data' => $permission_configuracion, 'icon' => 'fa-cogs'],
                        ['titulo' => 'Cotizaciones', 'data' => $permission_cotizaciones, 'icon' => 'fa-file-invoice-dollar'],
                    ];
                @endphp

                @foreach($secciones as $seccion)
                <div class="card-facturarg permission-card">
                    <div class="permission-title">
                        <i class="fas {{$seccion['icon']}} me-2 text-info"></i> {{$seccion['titulo']}}
                    </div>
                    <div class="card-body">
                        @foreach($seccion['data'] as $p)
                        <div class="custom-control custom-checkbox custom-check-container">
                            <input type="checkbox" class="custom-control-input" id="p_{{$p->id}}" value="{{$p->id}}" name="permission[]"
                                @if(is_array(old('permission')) && in_array("$p->id", old('permission'))) checked @endif>
                            <label class="custom-control-label fw-bold" for="p_{{$p->id}}" style="cursor:pointer;">
                                {{$p->name}}
                            </label>
                            <span class="checkbox-description">{{$p->description}}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

            </div>
        </div>

        <div class="card-facturarg mt-4">
            <div class="card-body p-4 text-center">
                <a href="{{route('role.index')}}" class="btn btn-light px-5 py-3 fw-bold me-3" style="border-radius: 12px; border: 1px solid #e2e8f0;">
                    <i class="fas fa-arrow-left me-2"></i> REGRESAR
                </a>
                <button type="submit" class="btn btn-dark px-5 py-3 fw-bold shadow" style="border-radius: 12px; background: var(--facturarg-dark);">
                    <i class="fas fa-check-circle me-2 text-info"></i> GUARDAR ROL DEFINITIVO
                </button>
            </div>
        </div>
    </form>
</div>

@push('ScriptRoleCreate')
<script src="{{asset('js/funciones_role/role_create.js')}}"></script>
@endpush
@endsection