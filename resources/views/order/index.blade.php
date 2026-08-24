@extends('layouts.admin')

@section('contenido')
<style>
    /* --- Listado de pedidos: estilo Sommy --- */
    /* Pedidos que nadie abrió todavía: fila amarilla hasta que se entra al detalle */
    tr.pedido-no-visto > td { background-color: #FEF9C3 !important; }
    tr.pedido-no-visto:hover > td { background-color: #FEF3A2 !important; }
    .section-farg {
        padding: 20px;
        background-color: #F8FAFC;
        min-height: 100vh;
        font-family: 'Poppins', sans-serif;
    }

    .farg-header-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
    }

    .farg-title-main {
        font-weight: 600;
        font-size: 21px;
        color: #1B2B5A;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .farg-title-main i { color: #2563EB; }

    .card-farg {
        background: #fff;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 10px 30px rgba(27,43,90,.10);
        border: 1px solid #E7EAF2;
    }

    #orders_table thead th {
        background-color: #F8FAFC;
        color: #47536F;
        text-transform: uppercase;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.06em;
        padding: 13px 16px;
        border: none;
        border-bottom: 1px solid #E7EAF2;
        vertical-align: middle;
    }

    #orders_table tbody td {
        padding: 13px 16px;
        font-size: 13.5px;
        color: #1B2B5A;
        border-bottom: 1px solid #F1F4F9;
        vertical-align: middle;
    }
    #orders_table tbody tr:hover { background: #F8FAFC; }
    #orders_table .badge { border-radius: 999px; font-weight: 500; font-size: 11px; padding: 5px 12px; }
    #orders_table h5 { margin: 0; }

    /* --- Pipeline de estados --- */
    .estados-pipeline {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .estado-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid #E7EAF2;
        border-radius: 999px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        color: #47536F;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }
    .estado-tab:hover { border-color: var(--tab-color, #2563EB); color: var(--tab-color, #2563EB); }
    .estado-tab i { color: var(--tab-color, #64748B); }
    .estado-tab .estado-count {
        background: #F1F4F9;
        color: #47536F;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        min-width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 7px;
    }
    .estado-tab.activo {
        background: var(--tab-color, #2563EB);
        border-color: var(--tab-color, #2563EB);
        color: #fff;
    }
    .estado-tab.activo i, .estado-tab.activo .estado-count { color: #fff; background: rgba(255,255,255,0.22); }
    .estado-flecha { color: #CBD5E1; font-size: 12px; }
    .estado-sep { width: 1px; height: 26px; background: #E7EAF2; margin: 0 6px; }
</style>

<section class="section-farg">
    <div class="farg-header-info">
        <div class="farg-title-main">
            <i class="fas fa-shopping-basket"></i>
            Pedidos — todos los canales
        </div>
        <a href="{{ url('/orders/manual') }}" class="btn btn-primary" style="border-radius:999px;padding:9px 22px;font-size:13.5px;">
            <i class="fas fa-plus"></i> Cargar pedido de otro canal
        </a>
    </div>

    @php
        $estiloEstados = [
            'Pendiente'             => ['color' => '#64748B', 'icono' => 'fa-clock'],
            'Comprobación de stock' => ['color' => '#0EA5E9', 'icono' => 'fa-boxes'],
            'Pagado'                => ['color' => '#F59E0B', 'icono' => 'fa-dollar-sign'],
            'Enviado'               => ['color' => '#2563EB', 'icono' => 'fa-shipping-fast'],
            'Entregado'             => ['color' => '#10B981', 'icono' => 'fa-check-circle'],
            'Cancelado'             => ['color' => '#EF4444', 'icono' => 'fa-times-circle'],
        ];
    @endphp

    {{-- Pipeline de estados: el pedido va avanzando de izquierda a derecha --}}
    <div class="estados-pipeline">
        <div class="estado-tab activo" data-status="" style="--tab-color:#1B2B5A">
            <i class="fas fa-layer-group"></i> Todos
            <span class="estado-count">{{ $conteos->sum() }}</span>
        </div>
        <div class="estado-sep"></div>
        @foreach($estados as $estado)
            @php $st = $estiloEstados[$estado->status_name] ?? ['color' => '#64748B', 'icono' => 'fa-circle']; @endphp
            @if($estado->status_name === 'Cancelado')
                <div class="estado-sep"></div>
            @elseif(!$loop->first)
                <i class="fas fa-chevron-right estado-flecha"></i>
            @endif
            <div class="estado-tab" data-status="{{ $estado->status_id }}" style="--tab-color:{{ $st['color'] }}">
                <i class="fas {{ $st['icono'] }}"></i> {{ $estado->status_name }}
                <span class="estado-count">{{ $conteos[$estado->status_id] ?? 0 }}</span>
            </div>
        @endforeach
    </div>

    <div class="card card-farg">
        <div class="table-responsive">
            <table id="orders_table" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Canal</th>
                        <th>Cliente</th>
                        <th>Dirección de entrega</th>
                        <th>Productos</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                </table>
        </div>
    </div>
</section>

<div class="row">
    <div class="col-lg-12">
        <div id="modal_container"></div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/funciones_orders/orders.js') }}?v={{ filemtime(public_path('js/funciones_orders/orders.js')) }}"></script>
@endsection