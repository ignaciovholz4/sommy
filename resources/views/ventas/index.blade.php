@extends('layouts.admin')
@section('contenido')
@include('ventas.header')

<style>
    /* Estilos de Branding Facturarg */
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-blue: #00A3E0;
        --facturarg-orange: #FF9900;
        --facturarg-bg: #F8FAFC;
    }

    .margindivsection { margin-top: 20px; }

    /* Tarjeta y Tabla */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    #ventas_table thead th {
        background-color: var(--facturarg-dark);
        color: #ffffff;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        font-weight: 700;
        padding: 15px;
        border: none;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 163, 224, 0.05);
        transition: background 0.2s ease;
    }

    /* Modales Estilizados */
    .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        background: var(--facturarg-dark);
        color: white;
        padding: 20px;
        margin-top: -5px;
    }

    .modal-title {
        font-weight: 800;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Labels y Detalles en Modal */
    .form-group label {
        color: var(--facturarg-blue);
        font-weight: 700;
        font-size: 0.85rem;
        margin-bottom: 5px;
        display: block;
    }

    .form-group p {
        background: var(--facturarg-bg);
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: 500;
        color: var(--facturarg-dark);
    }

    /* Tabla de Detalles interna */
    #detallesVenta thead th {
        background: var(--facturarg-bg);
        color: var(--facturarg-dark);
        border-bottom: 2px solid var(--facturarg-blue);
    }

    .panel-primary {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-top: 20px;
        padding: 15px;
    }

    /* Totales resaltados */
    tfoot tr:last-child {
        background: var(--facturarg-bg);
        color: var(--facturarg-dark);
        font-size: 1.1rem;
    }

    #details_total_con_iva {
        color: var(--facturarg-orange);
        font-size: 1.2rem;
    }

    /* Botones de acción dentro de la tabla (usualmente inyectados por JS) */
    .btn-action { /* Clase sugerida para tus botones en JS */
        border-radius: 8px;
        padding: 5px 10px;
        transition: 0.3s;
    }
</style>

{{-- Tablero de ventas por estado (mismo patrón que el tablero de fletes) --}}
<style>
    .vb-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; margin-top: 18px; }
    .vb-board { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; align-items: start; }
    @media (max-width: 991px) { .vb-board { grid-template-columns: 1fr; } }
    .vb-col { background: #F1F4F9; border-radius: 16px; padding: 10px; }
    .vb-col-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 8px 10px; font-size: 12.5px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em; color: #47536F;
    }
    .vb-col-head .num {
        background: #1B2B5A; color: #fff; border-radius: 999px; min-width: 24px; height: 24px;
        display: inline-flex; align-items: center; justify-content: center; font-size: 12px;
    }
    .vb-col.c1 .vb-col-head i { color: #b4552d; }
    .vb-col.c2 .vb-col-head i { color: #0d8a4f; }
    .vb-col.c3 .vb-col-head i { color: #94A3B8; }
    .vb-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        box-shadow: 0 6px 18px rgba(27,43,90,.06); padding: 13px 14px; margin-bottom: 10px;
    }
    .vb-card.anulada { opacity: .65; }
    .vb-card .top { display: flex; justify-content: space-between; align-items: center; gap: 6px; margin-bottom: 4px; }
    .vb-card .folio { font-weight: 700; font-size: 14px; }
    .vb-card .dato { font-size: 12.5px; color: #47536F; line-height: 1.55; }
    .vb-card .dato i { width: 15px; text-align: center; color: #94A3B8; }
    .vb-card .monto { font-weight: 700; font-size: 15px; margin-top: 4px; }
    .vb-rev {
        display: inline-block; background: #FEF3C7; color: #92400E; border-radius: 999px;
        font-size: 10px; font-weight: 600; padding: 2px 10px;
    }
    .vb-plata {
        margin-top: 7px; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px;
        padding: 7px 10px; font-size: 12px; color: #166534;
    }
    .vb-plata b { display: block; margin-bottom: 2px; }
    .vb-btns { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
    .vb-btn {
        border: none; border-radius: 999px; padding: 6px 14px; font-size: 12px; font-weight: 500;
        cursor: pointer; background: #E0F2FE; color: #1B2B5A;
    }
    .vb-btn:hover { background: #cfe9fb; }
    .vb-btn.cobrar { background: #0d8a4f; color: #fff; }
    .vb-btn.cobrar:hover { background: #0b7a45; }
    .vb-btn.rojo { background: #fff; color: #b4552d; border: 1.5px solid #f3d9cc; }
    .vb-btn.rojo:hover { background: #FBEDE6; }
    .vb-vacio { text-align: center; color: #94A3B8; font-size: 12.5px; font-weight: 300; padding: 22px 8px; }
    .vb-historial-tit { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #47536F; margin: 24px 0 4px; }
</style>

@php
    // Datos de cada venta para la tarjeta (los mismos que tenía la grilla)
    $cardDatos = function ($v) {
        return [
            'cliente'  => trim(optional($v->cliente)->nombre . ' ' . optional($v->cliente)->paterno) ?: '—',
            'telefono' => optional($v->cliente)->telefono,
            'compSuc'  => trim(implode(' · ', array_filter([
                optional($v->tipoComprobante)->descripcion,
                optional($v->sucursal)->nombre,
            ]))),
            'buscar'   => mb_strtolower(implode(' ', array_filter([
                $v->num_folio, 'venta #' . $v->idventa,
                optional($v->cliente)->nombre, optional($v->cliente)->paterno,
                optional($v->cliente)->telefono,
                optional($v->cliente)->dni_cuit,
                optional($v->tipoComprobante)->descripcion,
                optional($v->sucursal)->nombre,
            ]))),
        ];
    };
@endphp

<div class="vb-wrap">
    <div class="d-flex justify-content-end mb-2">
        <input type="search" id="vb-buscador" class="form-control form-control-sm" style="max-width:300px"
               placeholder="Buscar por folio, cliente, teléfono, sucursal...">
    </div>
    <div class="vb-board">
        {{-- A cobrar --}}
        <div class="vb-col c1">
            <div class="vb-col-head"><span><i class="fas fa-hand-holding-usd"></i> A cobrar</span><span class="num">{{ $aCobrar->count() }}</span></div>
            @forelse($aCobrar as $v)
            @php $d = $cardDatos($v); @endphp
            <div class="vb-card" data-buscar="{{ $d['buscar'] }}">
                <div class="top">
                    <span class="folio">{{ $v->num_folio ?: 'Venta #' . $v->idventa }}</span>@if($v->tipo_venta === 'mayorista')<span class="vb-rev" style="background:#E0F2FE;color:#1B2B5A;"><i class="fas fa-boxes"></i> Mayorista</span>@endif
                    @if($v->revendedor)<span class="vb-rev"><i class="fas fa-handshake"></i> {{ $v->revendedor->nombre }}</span>@endif
                </div>
                <div class="dato"><i class="fas fa-user"></i> {{ $d['cliente'] }}</div>
                @if($d['telefono'])
                    <div class="dato"><i class="fab fa-whatsapp"></i> <a href="https://wa.me/{{ preg_replace('/\D/', '', $d['telefono']) }}" target="_blank" rel="noopener noreferrer" style="color:#0d8a4f; text-decoration:none;">{{ $d['telefono'] }}</a></div>
                @endif
                @if(optional($v->cliente)->dni_cuit)<div class="dato"><i class="fas fa-id-card"></i> {{ $v->cliente->dni_cuit }}</div>@endif
                <div class="dato"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</div>
                @if($d['compSuc'])<div class="dato"><i class="fas fa-file-alt"></i> {{ $d['compSuc'] }}</div>@endif
                @php $cobradoV = (float) $v->movimientos->sum('total'); @endphp
                @if($cobradoV > 0.009)
                    <div class="monto" style="color:#0d8a4f;">${{ number_format($cobradoV, 2, ',', '.') }} <span style="color:#6E7A96;font-weight:400;font-size:12px;">de ${{ number_format($v->total_con_iva, 2, ',', '.') }}</span></div>
                    <div style="color:#b4552d;font-weight:700;font-size:13px;">Faltan ${{ number_format($v->total_con_iva - $cobradoV, 2, ',', '.') }}</div>
                    <div class="vb-plata" style="margin-top:5px;">
                        <b><i class="fas fa-piggy-bank"></i> Cobrado en</b>
                        @foreach($v->movimientos as $m)
                            {{ optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta' }}: ${{ number_format($m->total, 0, ',', '.') }}<br>
                        @endforeach
                    </div>
                @else
                <div class="monto">${{ number_format($v->total_con_iva, 2, ',', '.') }}
                    @if(round($v->total_neto, 2) !== round($v->total_con_iva, 2))
                        <small class="text-muted" style="font-weight:400">(neto ${{ number_format($v->total_neto, 2, ',', '.') }})</small>
                    @endif
                </div>
                @endif
                <div class="vb-btns">
                    <button class="vb-btn cobrar" onclick="openPagoModal({{ $v->idventa }}, {{ $v->sucursal_id ?: 'null' }})"><i class="fas fa-dollar-sign"></i> Cobrar</button>
                    <button class="vb-btn" onclick="getDetailVenta({{ $v->idventa }})"><i class="fas fa-eye"></i> Ver</button>
                    <button class="vb-btn rojo" onclick="anularVenta({{ $v->idventa }})">Anular</button>
                </div>
            </div>
            @empty
            <div class="vb-vacio">Nada pendiente de cobro. 👌</div>
            @endforelse
        </div>

        {{-- Cobradas --}}
        <div class="vb-col c2">
            <div class="vb-col-head"><span><i class="fas fa-check-circle"></i> Cobradas (30 días)</span><span class="num">{{ $cobradas->count() }}</span></div>
            @forelse($cobradas as $v)
            @php $d = $cardDatos($v); @endphp
            <div class="vb-card" data-buscar="{{ $d['buscar'] }}">
                <div class="top">
                    <span class="folio">{{ $v->num_folio ?: 'Venta #' . $v->idventa }}</span>@if($v->tipo_venta === 'mayorista')<span class="vb-rev" style="background:#E0F2FE;color:#1B2B5A;"><i class="fas fa-boxes"></i> Mayorista</span>@endif
                    @if($v->revendedor)<span class="vb-rev"><i class="fas fa-handshake"></i> {{ $v->revendedor->nombre }}</span>@endif
                </div>
                <div class="dato"><i class="fas fa-user"></i> {{ $d['cliente'] }}</div>
                <div class="dato"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</div>
                @if($d['compSuc'])<div class="dato"><i class="fas fa-file-alt"></i> {{ $d['compSuc'] }}</div>@endif
                <div class="monto">${{ number_format($v->total_con_iva, 2, ',', '.') }}</div>
                @if($v->movimientos->isNotEmpty())
                <div class="vb-plata">
                    <b><i class="fas fa-piggy-bank"></i> Dónde está la plata</b>
                    @foreach($v->movimientos as $m)
                        {{ optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta' }}:
                        ${{ number_format($m->total, 2, ',', '.') }}<br>
                    @endforeach
                </div>
                @endif
                <div class="vb-btns">
                    <button class="vb-btn" onclick="getDetailVenta({{ $v->idventa }})"><i class="fas fa-eye"></i> Ver</button>
                </div>
            </div>
            @empty
            <div class="vb-vacio">Sin cobros en el último mes.</div>
            @endforelse
        </div>

        {{-- Anuladas --}}
        <div class="vb-col c3">
            <div class="vb-col-head"><span><i class="fas fa-ban"></i> Anuladas (30 días)</span><span class="num">{{ $anuladas->count() }}</span></div>
            @forelse($anuladas as $v)
            @php $d = $cardDatos($v); @endphp
            <div class="vb-card anulada" data-buscar="{{ $d['buscar'] }}">
                <div class="top"><span class="folio">{{ $v->num_folio ?: 'Venta #' . $v->idventa }}</span>@if($v->tipo_venta === 'mayorista')<span class="vb-rev" style="background:#E0F2FE;color:#1B2B5A;"><i class="fas fa-boxes"></i> Mayorista</span>@endif</div>
                <div class="dato"><i class="fas fa-user"></i> {{ $d['cliente'] }}</div>
                <div class="dato"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</div>
                @if($d['compSuc'])<div class="dato"><i class="fas fa-file-alt"></i> {{ $d['compSuc'] }}</div>@endif
                <div class="monto" style="text-decoration:line-through;">${{ number_format($v->total_con_iva, 2, ',', '.') }}</div>
                <div class="vb-btns">
                    <button class="vb-btn" onclick="getDetailVenta({{ $v->idventa }})"><i class="fas fa-eye"></i> Ver</button>
                </div>
            </div>
            @empty
            <div class="vb-vacio">Ninguna anulada. 🎉</div>
            @endforelse
        </div>
    </div>

</div>

<section>
    <div class="modal fade" id="ModalDetalleVenta" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <br><br>
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-file-invoice-dollar me-2"></i>Detalle de la venta</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="container-fluid">     
                <div class="row g-3">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Cliente</label>
                            <p id="detalle_cliente">---</p>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Fecha</label>
                            <p id="detalle_fecha">---</p>
                        </div>
                    </div> 
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Folio</label>
                            <p id="detalles_folio">---</p>
                        </div>
                    </div>  
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Comprobante</label>
                            <p id="detalle_tipo">---</p>
                        </div>
                    </div>  
                </div>

                <div class="panel panel-primary">
                    <table id="detallesVenta" class="table text-center align-middle">
                        <thead>
                            <tr>
                                <th>Artículo</th>
                                <th>Cant.</th>
                                <th>Precio Unit.</th>
                                <th>Lista</th>
                                <th>Desc. (%)</th>
                                <th>IVA (%)</th>
                                <th>Subtotal Neto</th>
                                <th>Total c/IVA</th>
                            </tr>
                        </thead>
                        <tbody id="show_details_sale"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-end">Total Neto</td>
                                <td><span id="details_total_neto"></span></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end">IVA Discriminado</td>
                                <td id="details_iva_discriminado"></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end"><strong>Total con IVA</strong></td>
                                <td><strong id="details_total_con_iva"></strong></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end" style="color:#0d8a4f;">Cobrado</td>
                                <td id="details_v_cobrado" style="color:#0d8a4f;font-weight:700;"></td>
                            </tr>
                            <tr id="row_details_v_pendiente" style="display:none;">
                                <td colspan="7" class="text-end" style="color:#b4552d;"><strong>Falta cobrar</strong></td>
                                <td id="details_v_pendiente" style="color:#b4552d;font-weight:800;"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div id="details_v_pagos_wrap" style="display:none;margin-top:10px;text-align:left;">
                        <label style="color:var(--facturarg-blue);font-weight:700;font-size:0.85rem;">💰 Dónde está la plata</label>
                        <div id="details_v_pagos" style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:10px 14px;font-size:13.5px;color:#166534;"></div>
                    </div>

                    {{-- Comprobantes de pago (transferencias, recibos) --}}
                    <div style="margin-top:12px;text-align:left;">
                        <label style="color:var(--facturarg-blue);font-weight:700;font-size:0.85rem;"><i class="fas fa-receipt"></i> Comprobantes de pago</label>
                        <div id="details_v_comprobantes" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px;"></div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                            <input type="file" id="ventaCompArchivo" accept="image/*,.pdf" style="font-size:12px;max-width:220px;">
                            <input type="text" id="ventaCompNota" placeholder="Nota (ej: transferencia Galicia)" maxlength="200"
                                   style="border:1px solid #E7EAF2;border-radius:8px;padding:6px 10px;font-size:12.5px;">
                            <button type="button" class="btn btn-sm btn-dark" onclick="subirComprobanteVenta(this)"><i class="fas fa-upload"></i> Subir</button>
                        </div>
                    </div>

                    {{-- Devoluciones / cambios de esta venta --}}
                    <div id="details_v_devoluciones_wrap" style="display:none;margin-top:12px;text-align:left;">
                        <label style="color:var(--facturarg-blue);font-weight:700;font-size:0.85rem;"><i class="fas fa-undo"></i> Devoluciones / cambios</label>
                        <div id="details_v_devoluciones" style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:10px;padding:10px 14px;font-size:13px;color:#9A3412;"></div>
                    </div>

                    {{-- Notas de esta venta: el panel es fijo, JS le setea el id al abrir el modal --}}
                    <div style="margin-top:12px;text-align:left;">
                        @include('notas._panel', ['panelId' => 'notasPanelVentaModal', 'tipo' => 'venta', 'id' => null])
                    </div>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
</section>

<section>
<div class="modal fade" id="ModalPagoCobro" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: var(--facturarg-blue);">
        <h5 class="modal-title"><i class="fas fa-cash-register me-2"></i>Registrar Pago</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formPagoCobro">
            <input type="hidden" id="venta_compra_id" name="venta_compra_id">
            <input type="hidden" id="sucursal_id_modal" name="sucursal_id">

            <div class="row text-center mb-4">
                <div class="col-4">
                    <small class="text-muted d-block">Total</small>
                    <span id="monto_total" class="fw-bold text-dark"></span>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Ingresado</small>
                    <span id="monto_ingresado" class="fw-bold text-success">0</span>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Pendiente</small>
                    <span id="monto_pendiente" class="fw-bold text-danger"></span>
                </div>
            </div>

            <div id="mediosPagoContainer" class="mb-3"></div>
            
            <button type="button" id="addMedioPagoBtn" class="btn btn-outline-primary w-100 mb-3">
                <i class="fas fa-plus-circle me-1"></i> Agregar medio de pago
            </button>

            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                CONFIRMAR OPERACIÓN
            </button>
        </form>
      </div>
    </div>
  </div>
</div>
</section>

@push('ScriptVentasIndex')
<script src="{{ asset('js/funciones_venta/ventas_index.js') }}?v={{ filemtime(public_path('js/funciones_venta/ventas_index.js')) }}"></script>
<script>
// Deep-link desde otros módulos (ej: tablero de Envíos): /ventas?ver=ID abre el detalle
window.addEventListener('load', function () {
    var ver = new URLSearchParams(window.location.search).get('ver');
    if (ver && typeof getDetailVenta === 'function') {
        getDetailVenta(parseInt(ver, 10));
    }
});
</script>
@endpush
@endsection