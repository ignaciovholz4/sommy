@extends('layouts.admin')
@section('contenido')

<style>
    /* Estética Unificada Facturarg */
    :root {
        --facturarg-dark: #0f172a;    /* Azul Oxford */
        --facturarg-cyan: #1591a3;    /* Cian de acciones */
        --facturarg-bg: #f1f5f9;      /* Gris claro de fondo */
        --facturarg-accent: #22d3ee;  /* Cian brillante */
    }

    .main-container {
        background-color: var(--facturarg-bg);
        min-height: 100vh;
        padding: 2rem;
    }

    .btn-facturarg-main {
        background-color: var(--facturarg-dark);
        color: white;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .btn-facturarg-main:hover {
        background-color: #1e293b;
        color: var(--facturarg-accent);
        transform: translateY(-1px);
    }

    .card-facturarg {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        background: white;
        overflow: hidden;
    }

    .table-facturarg thead th {
        background-color: var(--facturarg-dark);
        color: white;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
    }

    .table-facturarg tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.9rem;
    }

    .table-facturarg tbody tr:hover {
        background-color: #f8fafc;
    }

    .modal-content {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }

    .modal-header-dark {
        background-color: var(--facturarg-dark);
        color: white;
        padding: 1.25rem;
    }

    .form-label-custom {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .form-control-custom {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.6rem 1rem;
    }

    .form-control-custom:focus {
        border-color: var(--facturarg-accent);
        box-shadow: none;
    }

    /* ── Banda hero: saldo + resumen del mes ─────── */
    .saldo-hero {
        background: var(--facturarg-dark);
        border-radius: 16px;
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
        color: white;
    }
    .saldo-hero-main { display: flex; align-items: center; gap: 16px; }
    .saldo-hero-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.12);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .saldo-hero-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: rgba(255,255,255,0.55);
    }
    .saldo-hero-valor { font-size: 2.1rem; font-weight: 800; line-height: 1.15; }
    .saldo-hero-valor.negativo { color: #FCA5A5; }

    .saldo-hero-stats { display: flex; gap: 28px; flex-wrap: wrap; }
    .saldo-hero-stat .saldo-hero-label { margin-bottom: 2px; }
    .saldo-hero-stat-valor { font-size: 1.05rem; font-weight: 700; }
    .stat-verde { color: #6EE7B7; }
    .stat-rojo  { color: #FCA5A5; }

    /* ── Chips y montos de la tabla ──────────────── */
    .mov-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .mov-chip i { font-size: 0.65rem; }
    .mov-chip-ingreso { background: #DCFCE7; color: #15803D; }
    .mov-chip-egreso  { background: #FEE2E2; color: #B91C1C; }
    .mov-chip-medio   { background: #F1F5F9; color: #475569; font-weight: 600; }

    .mov-monto-ingreso { color: #10b981; font-weight: 800; font-size: 0.95rem; white-space: nowrap; }
    .mov-monto-egreso  { color: #ef4444; font-weight: 800; font-size: 0.95rem; white-space: nowrap; }

    .mov-detalle-principal { font-weight: 700; color: var(--facturarg-dark); }
    .mov-detalle-sub { font-size: 0.78rem; color: #94a3b8; }
</style>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap:12px">
        <div>
            <h1 class="fw-bold h2 text-dark mb-1">{{ $cuenta->nombre }}</h1>
            <p class="text-muted small mb-0">
                {{ $cuenta->esCaja() ? 'Caja' : 'Banco' }} · {{ $cuenta->sucursal->nombre ?? '' }}
                @if($cuenta->esCaja() && !empty($aperturaId))
                    · Apertura #{{ $aperturaId }}
                @endif
            </p>
        </div>

        <div class="d-flex" style="gap:10px">
            @if(!$cuenta->esCaja())
                <a href="{{ route('cuentas.conciliacion.index', $cuenta->id) }}" class="btn btn-outline-secondary fw-bold">
                    <i class="fas fa-check-double me-2"></i> Conciliación bancaria
                </a>
            @endif
            <button id="btnTransferencia" class="btn btn-facturarg-main btn-abrir-principal">
                <i class="fas fa-exchange-alt me-2"></i> Transferencia
            </button>
            @if(!$cuenta->esCaja() || $estaAbierta)
                <button id="btnAgregarMovimiento" class="btn-facturarg-main btn-abrir-principal">
                    <i class="fas fa-plus-circle me-2"></i> Nuevo Movimiento
                </button>
            @endif
        </div>
    </div>

    {{-- Saldo + resumen del mes --}}
    <div class="saldo-hero">
        <div class="saldo-hero-main">
            <div class="saldo-hero-icon">
                <i class="fas {{ $cuenta->esCaja() ? 'fa-cash-register' : 'fa-university' }}"></i>
            </div>
            <div>
                <div class="saldo-hero-label">Saldo actual</div>
                <div class="saldo-hero-valor {{ $saldoActual < 0 ? 'negativo' : '' }}">
                    ${{ number_format($saldoActual, 2, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="saldo-hero-stats">
            <div class="saldo-hero-stat">
                <div class="saldo-hero-label">Ingresos del mes</div>
                <div class="saldo-hero-stat-valor stat-verde"><i class="fas fa-arrow-up me-1"></i>${{ number_format($ingresosMes, 2, ',', '.') }}</div>
            </div>
            <div class="saldo-hero-stat">
                <div class="saldo-hero-label">Egresos del mes</div>
                <div class="saldo-hero-stat-valor stat-rojo"><i class="fas fa-arrow-down me-1"></i>${{ number_format($egresosMes, 2, ',', '.') }}</div>
            </div>
            <div class="saldo-hero-stat">
                <div class="saldo-hero-label">Hoy</div>
                <div class="saldo-hero-stat-valor">
                    <span class="stat-verde">+${{ number_format($ingresosHoy, 2, ',', '.') }}</span>
                    <span class="stat-rojo ms-2">−${{ number_format($egresosHoy, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card-facturarg">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table_movimientos"
                       class="table table-facturarg mb-0"
                       data-cuenta-id="{{ $cuenta->id }}"
                       data-apertura-id="{{ $aperturaId }}"
                       data-sucursal-id="{{ $cuenta->sucursal_id }}">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Movimiento</th>
                            <th>Detalle</th>
                            <th style="display:none"></th>
                            <th style="display:none"></th>
                            <th>Medio</th>
                            <th class="text-end">Monto</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-5 d-flex justify-content-end">
        @if($cuenta->esCaja())
            <a href="{{ route('cuentas.historial', $cuenta->id) }}" class="btn btn-link text-decoration-none text-muted fw-bold">
                <i class="fas fa-arrow-left me-2"></i> Volver al Historial de Aperturas
            </a>
        @else
            <a href="{{ route('cuentas.index') }}" class="btn btn-link text-decoration-none text-muted fw-bold">
                <i class="fas fa-arrow-left me-2"></i> Volver al Gestor de Cuentas
            </a>
        @endif
    </div>
</div>

{{-- Modal Agregar Movimiento --}}
<div class="modal fade" id="agregarMovimientoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title">Agregar Movimiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formAgregarMovimiento">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Fecha</label>
              <input type="text" class="form-control" id="mov_fecha" value="{{ now()->format('d/m/Y H:i') }}" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Movimiento</label>
              <select class="form-select" id="mov_tipo" name="tipo" required>
                <option value="">Seleccione...</option>
                <option value="ingreso">Recibo (Ingreso)</option>
                <option value="egreso">Pago (Egreso)</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Cliente/Proveedor</label>
              <input type="text" class="form-control" id="mov_cliente" name="cliente_proveedor" placeholder="Nombre o concepto">
            </div>
            <div class="col-md-6">
              <label class="form-label">Comprobante</label>
              <input type="text" class="form-control" id="mov_comprobante" value="MPC-00001" disabled>
              <small class="text-muted">Se genera automáticamente (MPC/MRC)</small>
            </div>
          </div>

          @if($cuenta->esTercero())
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Alias del tercero</label>
              <input type="text" class="form-control" id="mov_alias_tercero" maxlength="60"
                     placeholder="Alias que tiene/recibió la plata" list="movAliasConocidos">
              <datalist id="movAliasConocidos"></datalist>
            </div>
            <div class="col-md-6">
              <label class="form-label">CUIT del titular (opcional)</label>
              <input type="text" class="form-control" id="mov_cuit_tercero" maxlength="20" placeholder="XX-XXXXXXXX-X">
            </div>
          </div>
          @endif

          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" id="mov_observaciones" name="observaciones" rows="2"></textarea>
          </div>

          <div class="row mb-3">
            @if($cuenta->esCaja())
              <div class="col-md-6">
                <label class="form-label">Monto Efectivo</label>
                <input type="number" step="0.01" min="0" class="form-control" id="mov_efectivo" name="efectivo" placeholder="0.00">
              </div>
            @else
              <div class="col-md-6">
                <label class="form-label">Monto Banco</label>
                <input type="number" step="0.01" min="0" class="form-control" id="mov_bancos" name="bancos" placeholder="0.00">
              </div>
            @endif
            <div class="col-md-6">
              <label class="form-label">Total</label>
              <input type="text" class="form-control" id="mov_total" value="0.00" disabled>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i> Cancelar
        </button>
        <button type="submit" form="formAgregarMovimiento" class="btn btn-success">
          <i class="fas fa-save me-2"></i> Guardar Movimiento
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ======================================================
     MODAL DETALLE DE MOVIMIENTO
     ====================================================== --}}
<div class="modal fade" id="modalDetalleMovimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border:none; border-radius:16px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.2);">

            {{-- Header dinámico (se pinta por JS) --}}
            <div class="modal-header" id="detalle-modal-header" style="padding:20px 28px; border:none; background:var(--facturarg-dark);">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div id="detalle-tipo-icon" style="width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:white;flex-shrink:0;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white" id="detalle-tipo-label" style="font-size:1rem; font-family:'Plus Jakarta Sans',sans-serif;">Detalle del Movimiento</div>
                        <div class="text-white-50 small" id="detalle-comprobante-num"></div>
                    </div>
                    <span id="detalle-tipo-badge" class="badge ms-2" style="font-size:0.78rem; padding:6px 14px; border-radius:20px; background:rgba(255,255,255,0.18); color:white; font-weight:700;"></span>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                {{-- Spinner de carga --}}
                <div id="detalle-loading" class="text-center py-5">
                    <div class="spinner-border text-secondary" style="width:2.5rem;height:2.5rem;"></div>
                    <div class="mt-3 text-muted small fw-bold">Cargando detalle...</div>
                </div>

                {{-- Contenido principal (oculto hasta cargar) --}}
                <div id="detalle-contenido" style="display:none;">

                    {{-- ── Sección: Datos del Movimiento (siempre visible) ── --}}
                    <div style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:20px 28px;">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="small text-uppercase fw-bold" style="color:#94a3b8; font-size:0.65rem; letter-spacing:.5px; margin-bottom:4px;">Fecha</div>
                                <div class="fw-bold" id="detalle-fecha" style="color:#0f172a; font-size:0.9rem;"></div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="small text-uppercase fw-bold" style="color:#94a3b8; font-size:0.65rem; letter-spacing:.5px; margin-bottom:4px;">Tipo</div>
                                <div id="detalle-badge-tipo"></div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="small text-uppercase fw-bold" style="color:#94a3b8; font-size:0.65rem; letter-spacing:.5px; margin-bottom:4px;">Cliente / Proveedor</div>
                                <div class="fw-bold" id="detalle-clienteprov" style="color:#0f172a; font-size:0.9rem;"></div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="small text-uppercase fw-bold" style="color:#94a3b8; font-size:0.65rem; letter-spacing:.5px; margin-bottom:4px;">Observaciones</div>
                                <div id="detalle-observaciones" style="color:#475569; font-size:0.85rem;"></div>
                            </div>
                        </div>

                        {{-- Medios de pago del movimiento --}}
                        <div class="row g-2 mt-3" id="detalle-medios-pago">
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center" style="background:white; border:1px solid #e2e8f0;">
                                    <div class="small text-muted fw-bold mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px;">Efectivo</div>
                                    <div class="fw-bold" id="detalle-efectivo" style="font-size:1rem; color:#0f172a;"></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center" style="background:white; border:1px solid #e2e8f0;">
                                    <div class="small text-muted fw-bold mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px;">Bancos</div>
                                    <div class="fw-bold" id="detalle-bancos" style="font-size:1rem; color:#0f172a;"></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center" style="background:white; border:1px solid #e2e8f0;">
                                    <div class="small text-muted fw-bold mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px;">Tarjetas</div>
                                    <div class="fw-bold" id="detalle-tarjetas" style="font-size:1rem; color:#0f172a;"></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center" style="background:var(--facturarg-dark); border:1px solid var(--facturarg-dark);">
                                    <div class="small fw-bold mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.6);">Total</div>
                                    <div class="fw-bold" id="detalle-total" style="font-size:1.1rem; color:white;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Sección: Comprobante externo (Venta / Compra / Devolución) ── --}}
                    <div id="detalle-seccion-comprobante" style="display:none; padding:24px 28px;">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                            <div>
                                <h6 class="fw-bold mb-1" id="detalle-comp-titulo" style="color:#0f172a; font-size:0.95rem;"></h6>
                                <div class="d-flex gap-4 flex-wrap mt-2" id="detalle-comp-meta" style="font-size:0.82rem; color:#64748b;"></div>
                            </div>
                            <span id="detalle-comp-estado-badge" class="badge" style="font-size:0.78rem; padding:6px 14px;"></span>
                        </div>

                        {{-- Tabla de ítems --}}
                        <div class="table-responsive mb-4">
                            <table class="table mb-0" style="font-size:0.83rem;">
                                <thead>
                                    <tr style="background:#0f172a; color:white;">
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none;">Producto</th>
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none; text-align:right;">Cant.</th>
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none; text-align:right;">P. Unit.</th>
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none; text-align:right;">Desc.%</th>
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none; text-align:right;">IVA%</th>
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none; text-align:right;">Subtotal Neto</th>
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none; text-align:right;">Subtotal c/IVA</th>
                                    </tr>
                                </thead>
                                <tbody id="detalle-comp-items" style="color:#334155;"></tbody>
                            </table>
                        </div>

                        {{-- Resumen financiero --}}
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <table class="table table-sm mb-0" style="font-size:0.85rem;">
                                    <tbody id="detalle-comp-resumen" style="color:#334155;"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ── Sección: Pedido Ecommerce ── --}}
                    <div id="detalle-seccion-ecommerce" style="display:none; padding:24px 28px;">
                        <h6 class="fw-bold mb-3" style="color:#0f172a; font-size:0.95rem;">
                            <i class="fas fa-shopping-cart me-2 text-primary"></i>Detalle del Pedido Online
                        </h6>
                        <div class="row g-3 mb-4" id="detalle-ec-meta"></div>
                        <div class="table-responsive">
                            <table class="table mb-0" style="font-size:0.83rem;">
                                <thead>
                                    <tr style="background:#0f172a; color:white;">
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none;">Producto</th>
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none; text-align:right;">Cant.</th>
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none; text-align:right;">Precio</th>
                                        <th style="padding:10px 14px; font-weight:700; text-transform:uppercase; font-size:0.68rem; letter-spacing:.5px; border:none; text-align:right;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="detalle-ec-items" style="color:#334155;"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ── Sección: Transferencia ── --}}
                    <div id="detalle-seccion-transferencia" style="display:none; padding:24px 28px;">
                        <div class="d-flex align-items-center gap-3 p-4 rounded-3" style="background:#f0f9ff; border:1px solid #bae6fd;">
                            <i class="fas fa-exchange-alt fa-2x" style="color:#0284c7;"></i>
                            <div>
                                <div class="fw-bold" style="color:#0f172a;">Transferencia entre cuentas</div>
                                <div class="small text-muted">Este movimiento corresponde a una transferencia interna. El monto fue acreditado/debitado en las cuentas involucradas.</div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Sección: Movimiento Manual ── --}}
                    <div id="detalle-seccion-manual" style="display:none; padding:24px 28px;">
                        <div class="d-flex align-items-center gap-3 p-4 rounded-3" style="background:#fefce8; border:1px solid #fde047;">
                            <i class="fas fa-hand-paper fa-2x" style="color:#ca8a04;"></i>
                            <div>
                                <div class="fw-bold" style="color:#0f172a;">Movimiento registrado manualmente</div>
                                <div class="small text-muted">Este movimiento fue ingresado de forma manual y no tiene un comprobante de origen asociado.</div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /detalle-contenido --}}
            </div>{{-- /modal-body --}}

            <div class="modal-footer" style="border-top:1px solid #e2e8f0; padding:14px 28px; background:#f8fafc;">
                <a id="detalle-btn-abrir" href="#" class="btn btn-primary rounded-3 fw-bold px-4 me-auto" style="display:none">
                    <i class="fas fa-external-link-alt me-2"></i>Abrir registro
                </a>
                <button type="button" class="btn btn-secondary rounded-3 fw-bold px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
{{-- ====================================================== --}}

{{-- Modal Transferencia --}}
<div class="modal fade" id="transferenciaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title">Nueva Transferencia entre Cuentas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formTransferencia">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Fecha</label>
              <input type="text" class="form-control" id="trans_fecha" value="{{ now()->format('d/m/Y H:i') }}" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Monto</label>
              <input type="number" step="0.01" min="0.01" class="form-control" id="trans_monto" name="monto" placeholder="0.00" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Cuenta Origen</label>
              <select class="form-select" id="trans_origen" name="origen_id" required>
                <option value="">Seleccione...</option>
                {{-- Se cargan dinámicamente las cuentas/cajas abiertas --}}
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cuenta Destino</label>
              <select class="form-select" id="trans_destino" name="destino_id" required>
                <option value="">Seleccione...</option>
                {{-- Se cargan dinámicamente las cuentas/cajas abiertas --}}
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" id="trans_observaciones" name="observaciones" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i> Cancelar
        </button>
        <button type="submit" form="formTransferencia" class="btn btn-success">
          <i class="fas fa-exchange-alt me-2"></i> Confirmar Transferencia
        </button>
      </div>
    </div>
  </div>
</div>

@push('ScriptMovimientosCuenta')
<script src="{{ asset('js/funciones_cuenta/movimientos.js') }}?v={{ filemtime(public_path('js/funciones_cuenta/movimientos.js')) }}"></script>
@endpush

@endsection