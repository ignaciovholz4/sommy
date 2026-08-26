@extends('layouts.admin')
@section('contenido')
@include('compras.header')

<style>
    /* ADN Visual Facturarg - Consistencia Total con Ventas */
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-blue: #00A3E0;
        --facturarg-bg: #F8FAFC;
        --facturarg-border: #e2e8f0;
    }

    .margindivsection { margin-top: 24px; }

    /* Card Estilo Ventas con Padding Interno Optimizado */
    .card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        padding: 10px; /* Padding extra para que los controles de la tabla no toquen el borde */
    }

    /* Padding específico para el contenedor de la tabla */
    .card-body {
        padding: 15px !important; 
    }

    /* Header de Tabla idéntico a Ventas */
    #compras_table thead th {
        background-color: var(--facturarg-dark);
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 1px;
        font-weight: 700;
        padding: 20px 15px; /* Más padding en el header */
        border: none;
    }

    /* Padding en las celdas del cuerpo */
    #compras_table tbody td {
        padding: 18px 15px;
        color: #475569;
        font-weight: 500;
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 163, 224, 0.04);
        transform: scale(1.002);
    }

    /* Modales Estilo Premium */
    .modal-content {
        border: none;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: var(--facturarg-dark);
        color: white;
        border-radius: 24px 24px 0 0;
        padding: 24px;
        border-bottom: none;
    }

    .modal-title {
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    /* Labels y Visualización de Datos con más aire */
    .form-group label, .form-label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #64748b;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .form-group p, .data-display-box {
        background: var(--facturarg-bg);
        padding: 14px 18px; /* Padding aumentado */
        border-radius: 12px;
        font-weight: 600;
        color: var(--facturarg-dark);
        border: 1px solid var(--facturarg-border);
        margin-bottom: 0;
    }

    /* Tabla de Detalles interna */
    #detallesCompra thead th {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 15px;
        border: none;
    }

    /* Botonera de Acción */
    .btn-facturarg {
        border-radius: 12px;
        font-weight: 700;
        padding: 14px 24px; /* Botones más grandes */
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-add-pago {
        background: var(--facturarg-dark);
        color: white;
    }

    .btn-add-pago:hover {
        background: var(--facturarg-blue);
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 163, 224, 0.2);
    }

    .btn-confirmar {
        background: #10b981;
        color: white;
    }

    .btn-confirmar:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(16, 185, 129, 0.2);
    }

    /* Ajuste para DataTables (Espaciado de buscador y paginado) */
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_paginate {
        padding: 15px;
    }
</style>

{{-- Tablero de compras por estado (misma UX que el tablero de ventas) --}}
<style>
    .vc-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; margin-top: 18px; }
    .vc-board { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; align-items: start; }
    @media (max-width: 991px) { .vc-board { grid-template-columns: 1fr; } }
    .vc-col { background: #F1F4F9; border-radius: 16px; padding: 10px; }
    .vc-col-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 8px 10px; font-size: 12.5px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em; color: #47536F;
    }
    .vc-col-head .num {
        background: #1B2B5A; color: #fff; border-radius: 999px; min-width: 24px; height: 24px;
        display: inline-flex; align-items: center; justify-content: center; font-size: 12px;
    }
    .vc-col.c1 .vc-col-head i { color: #b4552d; }
    .vc-col.c2 .vc-col-head i { color: #0d8a4f; }
    .vc-col.c3 .vc-col-head i { color: #94A3B8; }
    .vc-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        box-shadow: 0 6px 18px rgba(27,43,90,.06); padding: 13px 14px; margin-bottom: 10px;
    }
    .vc-card.anulada { opacity: .65; }
    .vc-card .top { display: flex; justify-content: space-between; align-items: center; gap: 6px; margin-bottom: 4px; }
    .vc-card .folio { font-weight: 700; font-size: 14px; }
    .vc-card .dato { font-size: 12.5px; color: #47536F; line-height: 1.55; }
    .vc-card .dato i { width: 15px; text-align: center; color: #94A3B8; }
    .vc-card .monto { font-weight: 700; font-size: 15px; margin-top: 4px; }
    .vc-adj {
        display: inline-block; background: #E0F2FE; color: #1B2B5A; border-radius: 999px;
        font-size: 10px; font-weight: 600; padding: 2px 10px;
    }
    .vc-plata {
        margin-top: 7px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px;
        padding: 7px 10px; font-size: 12px; color: #991B1B;
    }
    .vc-plata b { display: block; margin-bottom: 2px; }
    .vc-btns { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
    .vc-btn {
        border: none; border-radius: 999px; padding: 6px 14px; font-size: 12px; font-weight: 500;
        cursor: pointer; background: #E0F2FE; color: #1B2B5A;
    }
    .vc-btn:hover { background: #cfe9fb; }
    .vc-btn.pagar { background: #0d8a4f; color: #fff; }
    .vc-btn.pagar:hover { background: #0b7a45; }
    .vc-btn.rojo { background: #fff; color: #b4552d; border: 1.5px solid #f3d9cc; }
    .vc-btn.rojo:hover { background: #FBEDE6; }
    .vc-vacio { text-align: center; color: #94A3B8; font-size: 12.5px; font-weight: 300; padding: 22px 8px; }
</style>

@php
    $compraDatos = function ($c) {
        return [
            'proveedor' => optional($c->proveedor)->nombre ?: '—',
            'telefono'  => optional($c->proveedor)->telefono,
            'compSuc'   => trim(implode(' · ', array_filter([
                optional($c->tipoComprobante)->descripcion,
                optional($c->sucursal)->nombre,
            ]))),
            'buscar'    => mb_strtolower(implode(' ', array_filter([
                $c->num_folio, 'compra #' . $c->idcompra,
                optional($c->proveedor)->nombre,
                optional($c->proveedor)->telefono,
                optional($c->proveedor)->cuit,
                optional($c->tipoComprobante)->descripcion,
                optional($c->sucursal)->nombre,
            ]))),
        ];
    };
@endphp

<div class="vc-wrap">
    <div class="d-flex justify-content-end mb-2">
        <input type="search" id="vc-buscador" class="form-control form-control-sm" style="max-width:300px"
               placeholder="Buscar por folio, proveedor, teléfono, sucursal...">
    </div>
    <div class="vc-board">
        {{-- A pagar --}}
        <div class="vc-col c1">
            <div class="vc-col-head"><span><i class="fas fa-hand-holding-usd"></i> A pagar</span><span class="num">{{ $aPagar->count() }}</span></div>
            @forelse($aPagar as $c)
            @php $d = $compraDatos($c); @endphp
            <div class="vc-card" data-buscar="{{ $d['buscar'] }}">
                <div class="top">
                    <span class="folio">{{ $c->num_folio ?: 'Compra #' . $c->idcompra }}</span>
                    @if($c->adjuntos->isNotEmpty())<span class="vc-adj"><i class="fas fa-paperclip"></i> {{ $c->adjuntos->count() }}</span>@endif
                </div>
                <div class="dato"><i class="fas fa-truck"></i> {{ $d['proveedor'] }}</div>
                @if($d['telefono'])
                    <div class="dato"><i class="fab fa-whatsapp"></i> <a href="https://wa.me/{{ preg_replace('/\D/', '', $d['telefono']) }}" target="_blank" rel="noopener noreferrer" style="color:#0d8a4f; text-decoration:none;">{{ $d['telefono'] }}</a></div>
                @endif
                @if(optional($c->proveedor)->cuit)<div class="dato"><i class="fas fa-id-card"></i> CUIT {{ $c->proveedor->cuit }}</div>@endif
                <div class="dato"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</div>
                @if($d['compSuc'])<div class="dato"><i class="fas fa-file-alt"></i> {{ $d['compSuc'] }}</div>@endif
                @php $pagadoC = (float) $c->movimientos->sum('total'); @endphp
                @if($pagadoC > 0.009)
                    <div class="monto" style="color:#0d8a4f;">${{ number_format($pagadoC, 2, ',', '.') }} <span style="color:#6E7A96;font-weight:400;font-size:12px;">de ${{ number_format($c->total_con_iva, 2, ',', '.') }}</span></div>
                    <div style="color:#b4552d;font-weight:700;font-size:13px;">Faltan ${{ number_format($c->total_con_iva - $pagadoC, 2, ',', '.') }}</div>
                    <div class="vc-plata" style="margin-top:5px;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:7px 10px;font-size:12px;color:#166534;">
                        <b style="display:block;"><i class="fas fa-piggy-bank"></i> Pagado desde</b>
                        @foreach($c->movimientos as $m)
                            {{ optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta' }}: ${{ number_format($m->total, 0, ',', '.') }}<br>
                        @endforeach
                    </div>
                @else
                <div class="monto">${{ number_format($c->total_con_iva, 2, ',', '.') }}
                    @if(round($c->total_neto, 2) !== round($c->total_con_iva, 2))
                        <small class="text-muted" style="font-weight:400">(neto ${{ number_format($c->total_neto, 2, ',', '.') }})</small>
                    @endif
                </div>
                @endif
                <div class="vc-btns">
                    <button class="vc-btn pagar" onclick="openPagoModalCompra({{ $c->idcompra }}, {{ $c->sucursal_id ?: 'null' }})"><i class="fas fa-dollar-sign"></i> Pagar</button>
                    <button class="vc-btn" onclick="getDetailCompra({{ $c->idcompra }})"><i class="fas fa-eye"></i> Ver</button>
                    <button class="vc-btn rojo" onclick="anularCompra({{ $c->idcompra }})">Anular</button>
                </div>
            </div>
            @empty
            <div class="vc-vacio">Nada pendiente de pago. 👌</div>
            @endforelse
        </div>

        {{-- Pagadas --}}
        <div class="vc-col c2">
            <div class="vc-col-head"><span><i class="fas fa-check-circle"></i> Pagadas (30 días)</span><span class="num">{{ $pagadas->count() }}</span></div>
            @forelse($pagadas as $c)
            @php $d = $compraDatos($c); @endphp
            <div class="vc-card" data-buscar="{{ $d['buscar'] }}">
                <div class="top">
                    <span class="folio">{{ $c->num_folio ?: 'Compra #' . $c->idcompra }}</span>
                    @if($c->adjuntos->isNotEmpty())<span class="vc-adj"><i class="fas fa-paperclip"></i> {{ $c->adjuntos->count() }}</span>@endif
                </div>
                <div class="dato"><i class="fas fa-truck"></i> {{ $d['proveedor'] }}</div>
                <div class="dato"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</div>
                @if($d['compSuc'])<div class="dato"><i class="fas fa-file-alt"></i> {{ $d['compSuc'] }}</div>@endif
                <div class="monto">${{ number_format($c->total_con_iva, 2, ',', '.') }}</div>
                @if($c->movimientos->isNotEmpty())
                <div class="vc-plata">
                    <b><i class="fas fa-piggy-bank"></i> De dónde salió la plata</b>
                    @foreach($c->movimientos as $m)
                        {{ optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta' }}:
                        ${{ number_format($m->total, 2, ',', '.') }}<br>
                    @endforeach
                </div>
                @endif
                <div class="vc-btns">
                    <button class="vc-btn" onclick="getDetailCompra({{ $c->idcompra }})"><i class="fas fa-eye"></i> Ver</button>
                </div>
            </div>
            @empty
            <div class="vc-vacio">Sin pagos en el último mes.</div>
            @endforelse
        </div>

        {{-- Anuladas --}}
        <div class="vc-col c3">
            <div class="vc-col-head"><span><i class="fas fa-ban"></i> Anuladas (30 días)</span><span class="num">{{ $anuladas->count() }}</span></div>
            @forelse($anuladas as $c)
            @php $d = $compraDatos($c); @endphp
            <div class="vc-card anulada" data-buscar="{{ $d['buscar'] }}">
                <div class="top"><span class="folio">{{ $c->num_folio ?: 'Compra #' . $c->idcompra }}</span></div>
                <div class="dato"><i class="fas fa-truck"></i> {{ $d['proveedor'] }}</div>
                <div class="dato"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</div>
                @if($d['compSuc'])<div class="dato"><i class="fas fa-file-alt"></i> {{ $d['compSuc'] }}</div>@endif
                <div class="monto" style="text-decoration:line-through;">${{ number_format($c->total_con_iva, 2, ',', '.') }}</div>
                <div class="vc-btns">
                    <button class="vc-btn" onclick="getDetailCompra({{ $c->idcompra }})"><i class="fas fa-eye"></i> Ver</button>
                </div>
            </div>
            @empty
            <div class="vc-vacio">Ninguna anulada. 🎉</div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="ModalDetalleCompra" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shopping-bag me-2"></i> Detalle de Compra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Proveedor</label>
                            <p id="detalle_proveedor">---</p>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Fecha Emisión</label>
                            <p id="detalle_fecha">---</p>
                        </div>
                    </div> 
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>N° Folio</label>
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

                <div class="table-responsive rounded-4 border">
                    <table id="detallesCompra" class="table text-center align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Artículo</th>
                                <th>Cant.</th>
                                <th>Precio Unit.</th>
                                <th>Lista</th>
                                <th>Desc.</th>
                                <th>IVA</th>
                                <th>Neto</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="show_details_purchase"></tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="7" class="text-end fw-bold py-3">Total Neto:</td>
                                <td id="details_total_neto" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end fw-bold py-3">IVA:</td>
                                <td id="details_iva_discriminado"></td>
                            </tr>
                            <tr style="font-size: 1.1rem; color: var(--facturarg-blue);">
                                <td colspan="7" class="text-end fw-bold py-4">TOTAL FINAL:</td>
                                <td><strong id="details_total_con_iva"></strong></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end fw-bold py-3" style="color:#0d8a4f;">Pagado:</td>
                                <td id="details_c_pagado" style="color:#0d8a4f;font-weight:700;"></td>
                            </tr>
                            <tr id="row_details_c_pendiente" style="display:none;">
                                <td colspan="7" class="text-end fw-bold py-3" style="color:#b4552d;">Falta pagar:</td>
                                <td id="details_c_pendiente" style="color:#b4552d;font-weight:800;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div id="details_c_pagos_wrap" class="mt-3" style="display:none">
                    <h6 class="fw-bold"><i class="fas fa-piggy-bank me-1" style="color:#0d8a4f;"></i> Dónde salió la plata</h6>
                    <div id="details_c_pagos" style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:10px 14px;font-size:13.5px;color:#166534;"></div>
                </div>

                <div id="details_adjuntos_wrapper" class="mt-3" style="display:none">
                    <h6 class="fw-bold"><i class="fas fa-paperclip me-1"></i> Comprobantes adjuntos</h6>
                    <div id="details_adjuntos" class="d-flex flex-wrap" style="gap:10px"></div>
                </div>

                <div class="mt-3">
                    @include('notas._panel', ['panelId' => 'notasPanelCompraModal', 'tipo' => 'compra', 'id' => null])
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalPagoCompra" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i> Registrar Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formPagoCompra">
                    <input type="hidden" id="compra_id" name="compra_id">
                    <input type="hidden" id="sucursal_id_modal" name="sucursal_id">
                    
                    <div class="row g-2 mb-4">
                        <div class="col-4 text-center">
                            <label class="form-label">A Pagar</label>
                            <div class="data-display-box" id="monto_total"></div>
                        </div>
                        <div class="col-4 text-center">
                            <label class="form-label">Abonado</label>
                            <div class="data-display-box" id="monto_ingresado">0</div>
                        </div>
                        <div class="col-4 text-center">
                            <label class="form-label">Resta</label>
                            <div class="data-display-box text-danger" id="monto_pendiente"></div>
                        </div>
                    </div>

                    <div id="mediosPagoContainer" class="mb-4"></div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" id="addMedioPagoBtnCompra" class="btn-facturarg btn-add-pago justify-content-center">
                            <i class="fas fa-plus-circle"></i> Agregar Medio de Pago
                        </button>
                        <button type="submit" class="btn-facturarg btn-confirmar justify-content-center">
                            <i class="fas fa-check-double"></i> Confirmar Operación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('ScriptComprasIndex')
<script src="{{ asset('js/funciones_compra/compras_index.js') }}?v={{ filemtime(public_path('js/funciones_compra/compras_index.js')) }}"></script>
@endpush
@endsection