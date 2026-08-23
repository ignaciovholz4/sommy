@extends('layouts.admin')
@section('contenido')

<style>
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-red: #ef4444;
        --facturarg-bg: #F8FAFC;
    }

    .dv-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; }
    .dv-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; }
    .dv-title { font-size: 21px; font-weight: 600; }

    .dv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: start; margin-bottom: 20px; }
    @media (max-width: 991px) { .dv-grid { grid-template-columns: 1fr; } }

    .dv-col { background: #F1F4F9; border-radius: 16px; padding: 10px; }
    .dv-col-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 8px 10px; font-size: 12.5px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em; color: #47536F;
    }
    .dv-col-head i { color: #b4552d; }
    .dv-col-buscar { margin: 0 4px 8px; }
    .dv-col-buscar input {
        width: 100%; border: 1px solid #E7EAF2; border-radius: 999px;
        padding: 7px 14px; font-size: 12.5px;
    }
    .dv-lista { max-height: 420px; overflow-y: auto; }

    .dv-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        box-shadow: 0 4px 12px rgba(27,43,90,.05); padding: 11px 13px; margin-bottom: 8px;
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
    }
    .dv-card .info { min-width: 0; }
    .dv-card .folio { font-weight: 700; font-size: 13.5px; }
    .dv-card .folio a { color: #2563EB; text-decoration: none; }
    .dv-card .dato { font-size: 12px; color: #47536F; }
    .dv-card .monto { font-weight: 700; font-size: 13.5px; white-space: nowrap; }

    .dv-chip { border-radius: 999px; font-size: 10px; font-weight: 600; padding: 2px 9px; white-space: nowrap; }
    .chip-estado { background: #E0F2FE; color: #1B2B5A; }

    .dv-btn {
        border: 1.5px solid #f3d9cc; border-radius: 999px; padding: 6px 13px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        background: #fff; color: #b4552d; white-space: nowrap; flex-shrink: 0;
    }
    .dv-btn:hover { background: #FBEDE6; }

    .dv-vacio { text-align: center; color: #94A3B8; font-size: 12.5px; font-weight: 300; padding: 20px 8px; }

    /* Historial */
    .dv-hist-tit {
        font-weight: 800; color: var(--facturarg-dark); font-size: 1rem;
        display: flex; align-items: center; gap: 8px; margin: 4px 0 10px;
    }
    .dv-hist-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        padding: 13px 16px; margin-bottom: 8px;
        display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
    }
    .dv-hist-card .tipo-chip { font-size: 10.5px; font-weight: 700; border-radius: 999px; padding: 3px 11px; }
    .tipo-venta  { background: #DCFCE7; color: #15803D; }
    .tipo-compra { background: #DBEAFE; color: #1D4ED8; }
    .tipo-pedido { background: #EDE9FE; color: #5B21B6; }
    .dv-hist-card .monto-dev { font-weight: 800; color: var(--facturarg-red); font-size: 1rem; white-space: nowrap; }
    .dv-hist-card .motivo { font-size: 12px; color: #94A3B8; }

    .btn-dv-ver {
        border: 1px solid #e2e8f0; background: #f8fafc; color: var(--facturarg-dark);
        border-radius: 9px; padding: 7px 14px; font-size: 0.8rem; font-weight: 700; cursor: pointer;
    }
    .btn-dv-ver:hover { background: #e2e8f0; }

    /* Modal (igual que antes) */
    .modal-content { border: none; border-radius: 20px; }
    .modal-header { background: var(--facturarg-dark); color: white; border-radius: 20px 20px 0 0; padding: 20px; }
    .modal-body h6 { font-weight: 800; color: #00A3E0; margin-top: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; }
    .dev-data-box { background: var(--facturarg-bg); padding: 12px; border-radius: 10px; margin-bottom: 10px; }
    .dev-data-box strong { color: var(--facturarg-dark); display: block; font-size: 0.75rem; text-transform: uppercase; }
</style>

<div class="dv-wrap">
    <div class="dv-head">
        <div class="dv-title"><i class="fas fa-undo-alt" style="color: var(--facturarg-red);"></i> Devoluciones</div>
    </div>
    <div id="messagedevoluciones"></div>

    {{-- Elegir de dónde nace la devolución --}}
    <div class="dv-grid">
        {{-- Ventas --}}
        <div class="dv-col">
            <div class="dv-col-head">
                <span><i class="fas fa-cash-register"></i> Devolver una venta</span>
                <span>{{ $ventasDevolvibles->count() }} recientes</span>
            </div>
            <div class="dv-col-buscar">
                <input type="search" class="dv-buscar" data-target="venta" placeholder="Buscar por folio o cliente...">
            </div>
            <div class="dv-lista">
                @forelse($ventasDevolvibles as $v)
                <div class="dv-card dv-item-venta" data-buscar="{{ mb_strtolower(($v->num_folio ?? '') . ' ' . optional($v->cliente)->nombre . ' ' . optional($v->cliente)->paterno) }}">
                    <div class="info">
                        <div class="folio"><a href="{{ url('ventas?ver=' . $v->idventa) }}">{{ $v->num_folio ?: 'Venta #' . $v->idventa }}</a>
                            <span class="dv-chip chip-estado">{{ ucfirst($v->estado) }}</span>
                        </div>
                        <div class="dato">{{ trim(optional($v->cliente)->nombre . ' ' . optional($v->cliente)->paterno) ?: '—' }} · {{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</div>
                        <div class="monto">${{ number_format($v->total_con_iva, 2, ',', '.') }}</div>
                    </div>
                    <button class="dv-btn" onclick="devolverVenta({{ $v->idventa }}, '{{ e($v->num_folio) }}', {{ $v->sucursal_id }})">
                        <i class="fas fa-undo-alt"></i> Devolver
                    </button>
                </div>
                @empty
                <div class="dv-vacio">No hay ventas de los últimos 60 días.</div>
                @endforelse
            </div>
        </div>

        {{-- Pedidos --}}
        <div class="dv-col">
            <div class="dv-col-head">
                <span><i class="fas fa-shopping-basket"></i> Devolver un pedido (todos los canales)</span>
                <span>{{ $pedidosDevolvibles->count() }} recientes</span>
            </div>
            <div class="dv-col-buscar">
                <input type="search" class="dv-buscar" data-target="pedido" placeholder="Buscar por número o cliente...">
            </div>
            <div class="dv-lista">
                @forelse($pedidosDevolvibles as $p)
                <div class="dv-card dv-item-pedido" data-buscar="{{ mb_strtolower('pedido #' . $p->order_id . ' ' . optional($p->cliente)->nombre) }}">
                    <div class="info">
                        <div class="folio"><a href="{{ url('orders/order/' . $p->order_id) }}">Pedido #{{ $p->order_id }}</a>
                            <span class="dv-chip chip-estado">{{ optional($p->status)->status_name ?: '—' }}</span>
                        </div>
                        <div class="dato">{{ optional($p->cliente)->nombre ?: '—' }} · {{ \Carbon\Carbon::parse($p->order_date)->format('d/m/Y') }}</div>
                        <div class="monto">${{ number_format($p->total_amount, 2, ',', '.') }}</div>
                    </div>
                    <button class="dv-btn" onclick="devolverPedido({{ $p->order_id }})">
                        <i class="fas fa-undo-alt"></i> Devolver
                    </button>
                </div>
                @empty
                <div class="dv-vacio">No hay pedidos de los últimos 60 días.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Historial --}}
    <div class="dv-hist-tit"><i class="fas fa-history text-muted"></i> Devoluciones registradas</div>
    @forelse($devoluciones as $dev)
    <div class="dv-hist-card">
        <div style="min-width:210px">
            <span class="tipo-chip tipo-{{ $dev->tipo }}">{{ ucfirst($dev->tipo) }}</span>
            <span class="folio" style="font-weight:700; margin-left:6px;">
                @if($dev->ref['link'])
                    <a href="{{ $dev->ref['link'] }}" style="color:#2563EB; text-decoration:none;">{{ $dev->ref['folio'] }}</a>
                @else
                    {{ $dev->ref['folio'] }}
                @endif
            </span>
            <div class="dato" style="font-size:12px; color:#47536F; margin-top:3px;">
                {{ $dev->ref['persona'] }} · {{ \Carbon\Carbon::parse($dev->fecha)->format('d/m/Y H:i') }}
            </div>
        </div>
        <div class="motivo flex-grow-1">{{ $dev->motivo }}</div>
        <div class="monto-dev">−${{ number_format($dev->monto, 2, ',', '.') }}</div>
        <button class="btn-dv-ver" onclick="getDetailDevolucion({{ $dev->id }})"><i class="fas fa-eye"></i> Detalle</button>
    </div>
    @empty
    <div class="dv-vacio" style="background:#fff; border:2px dashed #e2e8f0; border-radius:14px; padding:2.5rem;">
        Todavía no se registraron devoluciones. Elegí arriba la venta o el pedido a devolver.
    </div>
    @endforelse
</div>

<section>
    <div class="modal fade" id="ModalDetalleDevolucion" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalle de la Devolución</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3 mb-4">
              <div class="col-md-3">
                  <div class="dev-data-box"><strong>Folio</strong><span id="dev_folio"></span></div>
              </div>
              <div class="col-md-3">
                  <div class="dev-data-box"><strong>Tipo</strong><span id="dev_tipo"></span></div>
              </div>
              <div class="col-md-3">
                  <div class="dev-data-box"><strong>Sucursal</strong><span id="dev_sucursal"></span></div>
              </div>
              <div class="col-md-3">
                  <div class="dev-data-box"><strong>Contacto</strong><span id="dev_persona"></span></div>
              </div>
            </div>

            <h6><i class="fas fa-exchange-alt me-2"></i>Movimientos Contables</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle text-center">
                  <thead class="table-light">
                    <tr>
                      <th>Cuenta</th>
                      <th>Fecha</th>
                      <th>Tipo</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody id="dev_movimientos"></tbody>
                </table>
            </div>

            <h6><i class="fas fa-boxes me-2"></i>Stock Reinvertido</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle text-center">
                  <thead class="table-light">
                    <tr>
                      <th>Artículo</th>
                      <th>Cantidad</th>
                    </tr>
                  </thead>
                  <tbody id="dev_stock"></tbody>
                </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar Detalle</button>
          </div>
        </div>
      </div>
    </div>
</section>

@push('ScriptDevolucion')
<script src="{{ asset('js/funciones_devolucion/devolucion_index.js') }}?v={{ filemtime(public_path('js/funciones_devolucion/devolucion_index.js')) }}"></script>
@endpush
@endsection
