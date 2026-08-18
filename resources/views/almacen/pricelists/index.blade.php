@extends('layouts.admin')

@section('contenido')
<style>
    /* Paleta Profesional Unificada */
    :root {
        --dark-navy: #1e293b;
        --brand-blue: #0f172a;
        --accent-blue: #3b82f6;
        --soft-bg: #f8fafc;
        --border-color: #e2e8f0;
    }

    .content-wrapper-custom {
        background-color: var(--soft-bg);
        min-height: 100vh;
        padding: 1.5rem;
    }

    /* Cards Modernas */
    .card-modern { 
        border: none; 
        border-radius: 12px; 
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); 
        background: #fff; 
        overflow: hidden;
        height: 100%;
    }

    /* Encabezados con Azul Profundo */
    .card-header-dark { 
        background-color: var(--brand-blue); 
        color: #ffffff;
        padding: 1.25rem;
        border-bottom: none;
    }

    .card-header-dark h5 { 
        font-weight: 700; 
        color: #f1f5f9;
        margin: 0;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Estilo de Tabla similar a Sucursales */
    .table thead th { 
        background-color: #f1f5f9; 
        color: var(--dark-navy);
        text-transform: uppercase; 
        font-size: 0.7rem; 
        font-weight: 800;
        letter-spacing: 1px;
        border-bottom: 2px solid var(--border-color);
        padding: 1rem;
    }

    .table tbody td { 
        vertical-align: middle; 
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .table tbody tr:hover {
        background-color: #f1f7ff;
    }

    /* Botones de Acción Circulares (IDÉNTICOS A SUCURSALES) */
    .action-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #f1f5f9;
        margin-left: 4px;
        transition: 0.2s;
        border: none;
        color: var(--brand-blue);
        text-decoration: none;
    }

    .action-icon:hover {
        background: var(--brand-blue);
        color: white !important;
    }

    .btn-delete-icon:hover {
        background: #dc2626;
    }

    /* Badges Soft */
    .badge-status {
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .badge-active { background: #dcfce7; color: #15803d; }
    .badge-inactive { background: #fee2e2; color: #b91c1c; }

    .btn-new-top {
        background-color: var(--accent-blue);
        color: white;
        font-weight: 700;
        font-size: 0.75rem;
        border-radius: 6px;
        transition: all 0.3s;
    }

    .btn-new-top:hover {
        background-color: #2563eb;
        transform: translateY(-1px);
        color: white;
    }
</style>

<div class="content-wrapper-custom">
    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card-modern">
                <div class="card-header-dark d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-file-invoice-dollar me-2 text-info"></i> Listas de Venta</h5>
                    <a href="{{ route('pricelists.create') }}?context=sales" class="btn btn-new-top btn-sm px-3">
                        <i class="fas fa-plus me-1"></i> NUEVA
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Configuración</th>
                                    <th>Ajuste</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salesLists as $list)
                                <tr>
                                    <td class="ps-4">
                                        <div style="font-weight: 700; color: var(--brand-blue);">{{ $list->name }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            @switch($list->type)
                                                @case('amount') MONTO FIJO @break
                                                @case('product_percentage') % POR PRODUCTO @break
                                                @case('general_percentage') % GENERAL @break
                                            @endswitch
                                        </div>
                                    </td>
                                    <td>
                                        @if($list->type === 'general_percentage')
                                            <span class="fw-bold {{ $list->value_type === 'discount' ? 'text-danger' : 'text-primary' }}">
                                                {{ $list->value_type === 'discount' ? '-' : '+' }}{{ $list->percentage }}%
                                            </span>
                                        @else
                                            <span class="text-muted small italic">Variable</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-status {{ $list->active ? 'badge-active' : 'badge-inactive' }}">
                                            {{ $list->active ? 'ACTIVO' : 'INACTIVO' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('pricelists.edit', $list->id) }}" class="action-icon" title="Editar">
                                            <i class="fas fa-pen fa-xs"></i>
                                        </a>
                                        <form action="{{ route('pricelists.destroy', $list->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="action-icon btn-delete-icon text-danger border-0" onclick="return confirm('¿Eliminar lista?')">
                                                <i class="fas fa-trash-alt fa-xs"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5 text-muted">No hay listas de venta.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card-modern">
                <div class="card-header-dark d-flex justify-content-between align-items-center" style="border-left: 4px solid #10b981;">
                    <h5><i class="fas fa-box-open me-2 text-success"></i> Listas de Compra</h5>
                    <a href="{{ route('pricelists.create') }}?context=purchase" class="btn btn-new-top btn-sm px-3" style="background-color: #10b981;">
                        <i class="fas fa-plus me-1"></i> NUEVA
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Proveedor / Lista</th>
                                    <th>Ajuste</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($purchaseLists as $list)
                                <tr>
                                    <td class="ps-4">
                                        <div style="font-weight: 700; color: var(--brand-blue);">{{ $list->name }}</div>
                                        <div class="text-muted" style="font-size: 11px;">COSTOS PROVEEDOR</div>
                                    </td>
                                    <td>
                                        <span class="fw-bold {{ $list->value_type === 'discount' ? 'text-danger' : 'text-success' }}">
                                            {{ $list->value_type === 'discount' ? '-' : '+' }}{{ $list->percentage }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status {{ $list->active ? 'badge-active' : 'badge-inactive' }}">
                                            {{ $list->active ? 'ACTIVO' : 'INACTIVO' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('pricelists.edit', $list->id) }}" class="action-icon" title="Editar">
                                            <i class="fas fa-pen fa-xs"></i>
                                        </a>
                                        <form action="{{ route('pricelists.destroy', $list->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="action-icon btn-delete-icon text-danger border-0">
                                                <i class="fas fa-trash-alt fa-xs"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5 text-muted">No hay listas de compra.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection