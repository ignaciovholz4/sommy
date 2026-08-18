@extends('layouts.admin')

@section('contenido')
<style>
    /* ADN Visual Facturarg - Gestión de Usuarios */
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

    /* Card Principal */
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
        padding: 2.5rem 3rem; /* Padding interno masivo */
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Tabla Estilizada */
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
        padding: 1.5rem 2rem !important; /* Espaciado para que respire */
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    /* Badges de Rol */
    .badge-rol {
        background-color: #e0f2fe;
        color: #0369a1;
        font-weight: 700;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    /* Botones de Acción en Tabla */
    .btn-action-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin: 0 2px;
        transition: 0.3s;
        border: none;
    }

    .btn-edit-user { background: #f1f5f9; color: var(--facturarg-dark); }
    .btn-edit-user:hover { background: var(--facturarg-dark); color: white; }
    
    .btn-delete-user { background: #fff1f2; color: #e11d48; }
    .btn-delete-user:hover { background: #e11d48; color: white; }

    /* Modales Estilo Facturarg */
    .modal-facturarg .modal-content {
        border: none;
        border-radius: 24px;
        overflow: hidden;
    }

    .modal-header-dark {
        background: var(--facturarg-dark);
        color: white;
        padding: 2rem;
        border: none;
    }

    .style-input {
        background-color: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px !important;
        font-weight: 600;
        transition: 0.3s;
    }

    .style-input:focus {
        border-color: var(--facturarg-cyan);
        background-color: #fff;
        box-shadow: none;
    }

    .input-group-text-facturarg {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-right: none;
        border-radius: 12px 0 0 12px;
        color: #94a3b8;
    }
</style>

<div class="main-container">
    <div class="card-facturarg">
        <div class="card-header-facturarg">
            <div>
                <h3 class="fw-bold text-dark m-0">Gestión de Usuarios</h3>
                <p class="text-muted small m-0 mt-1">Administra los accesos y permisos del personal.</p>
            </div>
            <button type="button" class="btn btn-dark px-4 py-3 fw-bold shadow-sm" style="border-radius: 12px;" data-toggle="modal" data-target="#ModalUserCreate">
                <i class="fas fa-plus-circle me-2 text-info"></i> NUEVO USUARIO
            </button>
        </div>

        <div class="card-body p-0">
            @include('custom.message')
            <div class="table-responsive">
                <table class="table table-facturarg mb-0">
                    <thead>
                        <tr>
                            <th class="ps-5">#</th>
                            <th>Usuario / Nombre</th>
                            <th>Correo Electrónico</th>
                            <th>Rol Asignado</th>
                            <th class="text-center pe-5">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            @if ($user->email != 'admin@admin.com')
                            <tr>
                                <td class="ps-5 text-muted fw-bold">{{$user->id}}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                        <span class="fw-bold text-dark">{{$user->name}}</span>
                                    </div>
                                </td>
                                <td>{{$user->email}}</td>
                                <td>
                                    @isset($user->roles[0]->name)
                                        <span class="badge-rol">{{$user->roles[0]->name}}</span> 
                                    @endisset
                                </td>
                                <td class="text-center pe-5">
                                    <div class="d-flex justify-content-center">
                                        <a href="{{route('user.show',$user->id)}}" class="btn-action-icon btn-edit-user" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{route('user.edit',$user->id)}}" class="btn-action-icon btn-edit-user" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn-action-icon btn-edit-user" onclick="changepassword('{{$user->id}}','{{$user->email}}');" title="Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button type="button" name="{{$user->id}}" data-delete-url="{{ route('delete-users', ['id' => $user->id]) }}" class="btn-action-icon btn-delete-user btnuserdelete">
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
                {{$users->links()}}
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-facturarg" id="ModalUserCreate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-dark">
                <h5 class="modal-title fw-bold text-uppercase" style="letter-spacing: 1px;">Nuevo Acceso</h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
            </div>
            <div class="modal-body p-5">
                <form id="addnewuser" autocomplete="off" data-url="{{ route('register') }}">
                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-2 uppercase">Nombre Completo</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-facturarg"><i class="fas fa-user"></i></span>
                            <input id="name" type="text" class="form-control style-input" name="name" required placeholder="Ej: Juan Pérez">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-2 uppercase">Email Institucional</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-facturarg"><i class="fas fa-envelope"></i></span>
                            <input id="email" type="email" class="form-control style-input" name="email" required placeholder="correo@empresa.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="small fw-bold text-muted mb-2 uppercase">Password</label>
                            <input id="npassword" type="password" class="form-control style-input" name="npassword" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="small fw-bold text-muted mb-2 uppercase">Confirmar</label>
                            <input id="cpassword" type="password" class="form-control style-input" name="cpassword" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-2 uppercase">Rol de Sistema</label>
                        <select name="rol" id="rol" class="form-select style-input">
                            <option value="">Seleccionar un rol...</option>
                            @foreach ($roles as $rol)
                                <option value="{{$rol->id}}">{{$rol->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <div id="erroruser"></div>
                <div class="d-grid gap-2 mt-5">
                    <button type="button" id="adduser" class="btn btn-dark py-3 fw-bold" style="border-radius: 12px;">
                        <i class="fas fa-save me-2 text-info"></i> GUARDAR USUARIO
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scriptusers')
<script src="{{asset('js/funciones_user/user.js')}}"></script>    
@endpush
@endsection