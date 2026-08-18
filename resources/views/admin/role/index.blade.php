@extends('layouts.admin')

@section('contenido')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* ADN Visual Facturarg - Gestión de Roles */
    :root {
        --facturarg-dark: #0f172a;    
        --facturarg-cyan: #1591a3;    
        --facturarg-bg: #f1f5f9;      
    }

    .main-container {
        background-color: var(--facturarg-bg);
        min-height: 100vh;
        padding: 3rem 2.5rem;
    }

    /* Card de Roles */
    .card-facturarg {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        background: #fff;
        overflow: hidden;
    }

    .card-header-facturarg {
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        padding: 2.5rem 3rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Tabla con Espaciado Premium */
    .table-facturarg thead th {
        background-color: var(--facturarg-dark);
        color: white;
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1px;
        padding: 1.5rem 2rem !important;
        border: none;
    }

    .table-facturarg tbody td {
        padding: 1.8rem 2rem !important; /* Más espacio para descripciones */
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    /* Chips de Estado */
    .chip-role {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
    }

    .chip-access-full {
        background-color: #dcfce7;
        color: #15803d;
        font-weight: 800;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    .chip-access-limited {
        background-color: #fef9c3;
        color: #a16207;
        font-weight: 800;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    /* Botones de Acción Estilizados */
    .btn-action-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin: 0 3px;
        transition: 0.3s;
        border: none;
        text-decoration: none;
    }

    .btn-view { background: #f1f5f9; color: var(--facturarg-dark); }
    .btn-view:hover { background: var(--facturarg-dark); color: white; }
    
    .btn-edit { background: #ecfeff; color: #0891b2; }
    .btn-edit:hover { background: #0891b2; color: white; }

    .btn-delete { background: #fff1f2; color: #e11d48; }
    .btn-delete:hover { background: #e11d48; color: white; }

</style>

<div class="main-container">
    <div class="card-facturarg">
        <div class="card-header-facturarg">
            <div>
                <h3 class="fw-bold text-dark m-0">Roles y Permisos</h3>
                <p class="text-muted small m-0 mt-1">Define los niveles de acceso para la seguridad del sistema.</p>
            </div>
            <a href="{{url('admin/role/create')}}" class="btn btn-dark px-4 py-3 fw-bold shadow-sm" style="border-radius: 12px;">
                <i class="fas fa-plus-circle me-2 text-info"></i> CREAR NUEVO ROL
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="px-5 pt-3">
                    @include('custom.message')
                </div>
                <table class="table table-facturarg mb-0">
                    <thead>
                        <tr>
                            <th class="ps-5">#</th>
                            <th>Nombre del Rol</th>
                            <th>Slug / Identificador</th>
                            <th>Descripción</th>
                            <th class="text-center">Acceso Total</th>
                            <th class="text-center pe-5">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            @if ($role->name != 'Admin')
                            <tr>
                                <td class="ps-5 text-muted fw-bold">{{$role->id}}</td>
                                <td><span class="chip-role">{{$role->name}}</span></td>
                                <td class="text-muted small">{{$role->slug}}</td>
                                <td style="max-width: 300px;">
                                    <span class="text-secondary">{{$role->description}}</span>
                                </td>
                                <td class="text-center">
                                    @if($role['full-access'] == 'yes')
                                        <span class="chip-access-full">Si</span>
                                    @else
                                        <span class="chip-access-limited">Limitado</span>
                                    @endif
                                </td>
                                <td class="text-center pe-5">
                                    <div class="d-flex justify-content-center">
                                        <a href="{{route('role.show',$role->id)}}" class="btn-action-icon btn-view" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{route('role.edit',$role->id)}}" class="btn-action-icon btn-edit" title="Editar Permisos">
                                            <i class="fas fa-shield-alt"></i>
                                        </a>
                                        <button class="btn-action-icon btn-delete" onclick="deleteRol('{{$role->id}}');" title="Eliminar Rol">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-top">
                {{$roles->links()}}
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{asset('js/funciones_role/role.js')}}"></script>
@endsection