@extends('layouts.admin')

@section('contenido')
<style>
/* ── VARIABLES ─────────────────────────────────────── */
:root {
    --d-bg:      #f4f6f9;
    --d-white:   #ffffff;
    --d-border:  #edf0f4;
    --d-text:    #111827;
    --d-sub:     #6b7280;
    --d-label:   #9ca3af;
    --d-blue:    #2563eb;
    --d-amber:   #f59e0b;
    --d-purple:  #7c3aed;
    --d-green:   #10b981;
    --d-red:     #ef4444;
    --d-radius:  14px;
    --d-shadow:  0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
}

/* ── CONTAINER ─────────────────────────────────────── */
.dashboard-container {
    background: var(--d-bg);
    padding: 1.5rem 1.25rem;
    min-height: 100vh;
}

/* ── SECTION LABEL ─────────────────────────────────── */
.section-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--d-label);
    margin: 0 0 0.6rem;
}

/* ── BASE CARD ─────────────────────────────────────── */
.d-card {
    background: var(--d-white);
    border-radius: var(--d-radius);
    border: 1px solid var(--d-border);
    box-shadow: var(--d-shadow);
    height: 100%;
}

/* ── KPI CARD (estilo Don Gestión) ─────────────────── */
.kpi-card {
    background: var(--d-white);
    border-radius: var(--d-radius);
    border: 1px solid var(--d-border);
    box-shadow: var(--d-shadow);
    padding: 1.1rem 1.1rem 0.9rem;
    height: 100%;
    position: relative;
    transition: box-shadow 0.2s, transform 0.2s;
}
.kpi-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}
.kpi-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.55rem;
}
.kpi-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--d-sub);
    line-height: 1.3;
}
.kpi-icon {
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
    border: 1.5px solid;
}
.kpi-icon.blue   { background: #eff6ff; color: var(--d-blue);    border-color: #bfdbfe; }
.kpi-icon.purple { background: #f5f3ff; color: var(--d-purple);  border-color: #ddd6fe; }
.kpi-icon.amber  { background: #fffbeb; color: var(--d-amber);   border-color: #fde68a; }
.kpi-icon.cyan   { background: #ecfeff; color: #0891b2;          border-color: #a5f3fc; }
.kpi-icon.green  { background: #f0fdf4; color: var(--d-green);   border-color: #bbf7d0; }
.kpi-icon.red    { background: #fef2f2; color: var(--d-red);     border-color: #fecaca; }

.kpi-number {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--d-text);
    line-height: 1.1;
    letter-spacing: -0.03em;
    margin-bottom: 0.35rem;
}
.kpi-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.5rem;
    border-top: 1px solid var(--d-border);
}
.kpi-compare {
    font-size: 0.8rem;
    color: var(--d-label);
    font-weight: 500;
}
.kpi-trend {
    display: inline-flex; align-items: center; gap: 2px;
    font-size: 0.7rem; font-weight: 700;
    padding: 2px 7px; border-radius: 99px;
}
.kpi-trend.up   { background: #dcfce7; color: #16a34a; }
.kpi-trend.down { background: #fee2e2; color: #dc2626; }
.kpi-trend.neu  { background: #f1f5f9; color: var(--d-sub); }

/* ── ALERT CARD ────────────────────────────────────── */
.alert-card {
    background: var(--d-white);
    border-radius: var(--d-radius);
    border: 1px solid var(--d-border);
    box-shadow: var(--d-shadow);
    padding: 1.1rem 1.2rem;
    height: 100%;
}
.alert-icon-wrap {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; margin-bottom: 0.8rem;
    border: 1.5px solid;
}
.alert-icon-wrap.red    { background: #fef2f2; color: var(--d-red);    border-color: #fecaca; }
.alert-icon-wrap.amber  { background: #fffbeb; color: var(--d-amber);  border-color: #fde68a; }
.alert-icon-wrap.blue   { background: #eff6ff; color: var(--d-blue);   border-color: #bfdbfe; }
.alert-title {
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: var(--d-label); margin-bottom: 4px;
}
.alert-number {
    font-size: 2rem; font-weight: 800; line-height: 1;
    letter-spacing: -0.04em; margin-bottom: 2px;
}
.alert-sub {
    font-size: 0.72rem; color: var(--d-sub); font-weight: 500; display: block; margin-bottom: 0.7rem;
}
.alert-row {
    display: flex; justify-content: space-between;
    font-size: 0.78rem; padding: 5px 0;
    border-top: 1px solid var(--d-border);
}
.alert-row span:first-child { color: var(--d-sub); }
.alert-row span:last-child  { font-weight: 700; color: var(--d-text); }

/* ── FINANCE CARD ──────────────────────────────────── */
.finance-card {
    background: var(--d-white);
    border-radius: var(--d-radius);
    border: 1px solid var(--d-border);
    box-shadow: var(--d-shadow);
    height: 100%; overflow: hidden;
}
.finance-header {
    padding: 0.85rem 1.2rem;
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid var(--d-border);
}
.finance-header-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: var(--d-sub);
}
.finance-header-title .fh-dot {
    width: 8px; height: 8px; border-radius: 50%;
}
.finance-header small { font-size: 0.7rem; color: var(--d-label); }
.finance-body { padding: 1rem 1.2rem; }
.data-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 7px 0; border-bottom: 1px solid var(--d-border);
}
.data-row:last-of-type { border-bottom: none; }
.data-label { font-size: 0.82rem; color: var(--d-sub); font-weight: 500; }
.data-value { font-size: 0.95rem; font-weight: 700; color: var(--d-text); }
.finance-total {
    text-align: center; padding: 0.8rem 0 0.4rem;
    border-top: 1px solid var(--d-border); margin-top: 0.5rem;
}
.finance-total-label {
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.08em; color: var(--d-label); margin-bottom: 2px;
}
.finance-total-number {
    font-size: 2rem; font-weight: 800; letter-spacing: -0.04em; line-height: 1.1;
}

/* ── CHART CARD ────────────────────────────────────── */
.chart-card-clean {
    background: var(--d-white); border-radius: var(--d-radius);
    border: 1px solid var(--d-border); box-shadow: var(--d-shadow);
    padding: 1.1rem; height: 100%;
}
.chart-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 0.75rem;
}
.chart-title {
    font-size: 0.78rem; font-weight: 700; color: var(--d-sub);
    text-transform: uppercase; letter-spacing: 0.05em;
}
.chart-badge {
    font-size: 0.68rem; font-weight: 600;
    padding: 3px 10px; border-radius: 99px;
    background: var(--d-bg); color: var(--d-sub);
}

/* ── ANALYTICS CARD ────────────────────────────────── */
.analytics-card {
    background: var(--d-white); border-radius: var(--d-radius);
    border: 1px solid var(--d-border); box-shadow: var(--d-shadow);
    overflow: hidden; height: 100%;
}
.analytics-header {
    padding: 0.8rem 1.1rem;
    border-bottom: 1px solid var(--d-border);
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: var(--d-sub);
    display: flex; align-items: center; gap: 7px;
}
.analytics-body { padding: 0.6rem 1.1rem 0.8rem; }

/* Top productos */
.top-product-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 0; border-bottom: 1px solid var(--d-border);
}
.top-product-row:last-child { border-bottom: none; }
.rank-badge {
    width: 24px; height: 24px; border-radius: 50%;
    background: #e2e8f0; color: #64748b;
    font-size: 0.68rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.rank-1 { background: #fef3c7; color: #b45309; }
.rank-2 { background: #f1f5f9; color: #475569; }
.rank-3 { background: #fdf2d0; color: #92400e; }
.product-name  { font-size: 0.82rem; font-weight: 600; color: var(--d-text); flex: 1; min-width: 0; }
.product-qty   { font-size: 0.75rem; color: var(--d-sub); font-weight: 600; min-width: 42px; text-align: right; }
.product-total { font-size: 0.82rem; font-weight: 700; color: var(--d-blue); min-width: 78px; text-align: right; }

/* Mes a mes */
.mes-stat { padding: 9px 0; border-bottom: 1px solid var(--d-border); }
.mes-stat:last-child { border-bottom: none; }
.mes-label  { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--d-label); margin-bottom: 2px; }
.mes-amount { font-size: 1.2rem; font-weight: 800; color: var(--d-text); line-height: 1.2; }
.variacion-badge {
    display: inline-flex; align-items: center; gap: 2px;
    font-size: 0.68rem; font-weight: 800; padding: 1px 6px;
    border-radius: 99px; margin-left: 6px; vertical-align: middle;
}
.variacion-up   { background: #dcfce7; color: #16a34a; }
.variacion-down { background: #fee2e2; color: #dc2626; }

.row.g-3 { --bs-gutter-y: 0.65rem; --bs-gutter-x: 0.65rem; }

/* ── ACADEMIA CARD (estilo Don Gestión) ─────────────── */
.academia-card {
    background: var(--d-white);
    border-radius: var(--d-radius);
    border: 1px solid var(--d-border);
    box-shadow: var(--d-shadow);
    height: 100%;
    display: flex;
    align-items: stretch;
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.academia-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.09); }
.academia-card-body {
    padding: 1.5rem 1.4rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    flex: 1;
    min-width: 0;
}
.academia-card-tag {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 0.08em; color: var(--d-blue);
    background: #eff6ff; padding: 3px 10px; border-radius: 99px;
    width: fit-content; margin-bottom: 0.7rem;
}
.academia-card-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--d-text);
    line-height: 1.35;
    margin-bottom: 0.5rem;
}
.academia-card-desc {
    font-size: 0.78rem;
    color: var(--d-sub);
    line-height: 1.55;
    margin-bottom: 1rem;
}
.academia-card-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: #1e3a8a;
    color: #fff !important;
    padding: 9px 18px;
    border-radius: 99px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none !important;
    transition: background 0.2s, transform 0.15s;
    width: fit-content;
}
.academia-card-btn:hover { background: #1e40af; transform: translateX(2px); }
.academia-card-illus {
    width: 130px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(145deg, #eff6ff 0%, #e0e7ff 100%);
    font-size: 64px;
    color: #3b82f6;
    opacity: 0.85;
}
@media (max-width: 575px) { .academia-card-illus { display: none; } }

/* ── DG FEATURE CARD (Don Gestión style) ────────────── */
.dg-feat-card {
    border-radius: var(--d-radius);
    height: 100%;
    min-height: 108px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1rem 1.1rem;
    position: relative;
    overflow: hidden;
    text-decoration: none !important;
    cursor: pointer;
    transition: transform 0.18s, box-shadow 0.18s;
    border: 1px solid transparent;
}
.dg-feat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(0,0,0,0.12);
}
.dg-feat-card-title {
    font-size: 0.92rem;
    font-weight: 800;
    line-height: 1.3;
    position: relative;
    z-index: 1;
    color: #1e293b;
}
.dg-feat-card-bg-icon {
    font-size: 52px;
    opacity: 0.18;
    position: absolute;
    right: 10px;
    bottom: 6px;
    line-height: 1;
    z-index: 0;
}
/* Colores de cada feature card */
.dg-feat-ecommerce  { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-color: #6ee7b7; }
.dg-feat-ecommerce  .dg-feat-card-title { color: #065f46; }
.dg-feat-ecommerce  .dg-feat-card-bg-icon { color: #059669; }

.dg-feat-qr         { background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); border-color: #c4b5fd; }
.dg-feat-qr         .dg-feat-card-title { color: #4c1d95; }
.dg-feat-qr         .dg-feat-card-bg-icon { color: #7c3aed; }

.dg-feat-scanner    { background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%); border-color: #fdba74; }
.dg-feat-scanner    .dg-feat-card-title { color: #7c2d12; }
.dg-feat-scanner    .dg-feat-card-bg-icon { color: #ea580c; }

.dg-feat-venta      { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-color: #93c5fd; }
.dg-feat-venta      .dg-feat-card-title { color: #1e3a8a; }
.dg-feat-venta      .dg-feat-card-bg-icon { color: #2563eb; }

/* ── ACCESOS RÁPIDOS ───────────────────────────────── */
.quick-access-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
}
@media (max-width: 768px) {
    .quick-access-grid { grid-template-columns: repeat(2, 1fr); }
}
.qa-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0.85rem 0.5rem;
    border-radius: 12px;
    background: var(--d-bg);
    border: 1px solid var(--d-border);
    text-decoration: none;
    color: var(--d-text);
    font-size: 0.72rem;
    font-weight: 600;
    transition: all 0.18s;
    text-align: center;
}
.qa-btn:hover {
    background: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    color: var(--d-blue);
    border-color: #bfdbfe;
    transform: translateY(-1px);
    text-decoration: none;
}
.qa-btn i { font-size: 20px; margin-bottom: 2px; }
</style>

<div class="dashboard-container">

    {{-- ROW 1: KPI cards ─────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Ventas hoy</span>
                    <div class="kpi-icon blue"><i class="fas fa-shopping-cart"></i></div>
                </div>
                <div class="kpi-number">{{ $ventas }}</div>
                <div class="kpi-footer">
                    <span class="kpi-compare">${{ number_format($ventasHoyMonto, 0, ',', '.') }} facturado</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Total de ventas</span>
                    <div class="kpi-icon purple"><i class="fas fa-chart-line"></i></div>
                </div>
                <div class="kpi-number">${{ number_format($ventasMes, 0, ',', '.') }}</div>
                <div class="kpi-footer">
                    <span class="kpi-compare">vs mes anterior</span>
                    @if($variacionMes !== null)
                        <span class="kpi-trend {{ $variacionMes >= 0 ? 'up' : 'down' }}">
                            <i class="fas fa-arrow-{{ $variacionMes >= 0 ? 'up' : 'down' }}"></i> {{ abs($variacionMes) }}%
                        </span>
                    @else
                        <span class="kpi-trend neu">—</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Compras hoy</span>
                    <div class="kpi-icon amber"><i class="fas fa-truck-loading"></i></div>
                </div>
                <div class="kpi-number">{{ $entradas }}</div>
                <div class="kpi-footer">
                    <span class="kpi-compare">${{ number_format($comprasHoyMonto, 0, ',', '.') }} en compras</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Artículos</span>
                    <div class="kpi-icon cyan"><i class="fas fa-box"></i></div>
                </div>
                <div class="kpi-number">{{ $productos }}</div>
                <div class="kpi-footer">
                    <span class="kpi-compare">{{ number_format($totalClientes) }} clientes activos</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Pedidos web</span>
                    <div class="kpi-icon purple"><i class="fas fa-globe"></i></div>
                </div>
                <div class="kpi-number">{{ $pedidosPendientes }}</div>
                <div class="kpi-footer">
                    <span class="kpi-compare">{{ $pedidosMesCount }} pedidos este mes</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Cajas abiertas</span>
                    <div class="kpi-icon green"><i class="fas fa-cash-register"></i></div>
                </div>
                <div class="kpi-number">{{ $cajas }}</div>
                <div class="kpi-footer">
                    <span class="kpi-compare">{{ $devolucionesMes }} devoluciones</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 3: Alertas ───────────────────────────────────── --}}
    <p class="section-label">Alertas del día</p>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="alert-card">
                <div class="alert-icon-wrap red"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="alert-title">Stock crítico</div>
                <div class="alert-number" style="color:var(--d-red);">{{ $sinStock }}</div>
                <span class="alert-sub">productos sin stock disponible</span>
                <div class="alert-row">
                    <span>Stock bajo (≤ 5 u.)</span>
                    <span style="color:var(--d-amber);">{{ $stockBajo }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert-card">
                <div class="alert-icon-wrap amber"><i class="fas fa-file-invoice"></i></div>
                <div class="alert-title">Presupuestos abiertos</div>
                <div class="alert-number" style="color:var(--d-amber);">{{ $presupuestosPendientes }}</div>
                <span class="alert-sub">sin convertir en venta</span>
                <div class="alert-row">
                    <span>Monto en juego</span>
                    <span>${{ number_format($presupuestosMonto, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert-card">
                <div class="alert-icon-wrap blue"><i class="fas fa-balance-scale"></i></div>
                <div class="alert-title">Flujo de caja hoy</div>
                <div class="alert-number" style="color:{{ $saldoCajaHoy >= 0 ? 'var(--d-green)' : 'var(--d-red)' }};">
                    ${{ number_format(abs($saldoCajaHoy), 0, ',', '.') }}
                </div>
                <span class="alert-sub">saldo neto del día</span>
                <div class="alert-row">
                    <span>Ingresó</span>
                    <span style="color:var(--d-green);">${{ number_format($ingresoHoy->total ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="alert-row">
                    <span>Egresó</span>
                    <span style="color:var(--d-red);">${{ number_format($egresoHoy->total ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 3: Finance panels --}}
    <p class="section-label">Resumen financiero global</p>
    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="finance-card">
                <div class="finance-header">
                    <div class="finance-header-title">
                        <span class="fh-dot" style="background:var(--d-green);"></span>
                        A Cobrar
                    </div>
                    <small>Ventas</small>
                </div>
                <div class="finance-body">
                    <div class="data-row"><span class="data-label">Operado</span><span class="data-value">${{ $totalVentas }}</span></div>
                    <div class="data-row"><span class="data-label">Cobrado</span><span class="data-value" style="color:var(--d-green);">${{ $ingresadoVentas }}</span></div>
                    <div class="finance-total">
                        <div class="finance-total-label">Saldo Pendiente</div>
                        <div class="finance-total-number" style="color:var(--d-green);">${{ $pendienteVentas }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="finance-card">
                <div class="finance-header">
                    <div class="finance-header-title">
                        <span class="fh-dot" style="background:var(--d-red);"></span>
                        A Pagar
                    </div>
                    <small>Compras</small>
                </div>
                <div class="finance-body">
                    <div class="data-row"><span class="data-label">Total</span><span class="data-value">${{ $totalCompras }}</span></div>
                    <div class="data-row"><span class="data-label">Pagado</span><span class="data-value" style="color:var(--d-red);">${{ $pagadoCompras }}</span></div>
                    <div class="finance-total">
                        <div class="finance-total-label">Deuda Total</div>
                        <div class="finance-total-number" style="color:var(--d-red);">${{ $pendienteCompras }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="finance-card">
                <div class="finance-header">
                    <div class="finance-header-title">
                        <span class="fh-dot" style="background:var(--d-purple);"></span>
                        Ecommerce
                    </div>
                    <small>Este mes</small>
                </div>
                <div class="finance-body">
                    @php
                        $statPagados    = $pedidosPorEstado->firstWhere('estado', 'Pagado');
                        $statEntregados = $pedidosPorEstado->firstWhere('estado', 'Entregado');
                    @endphp
                    <div class="data-row"><span class="data-label">Pedidos del mes</span><span class="data-value">{{ $pedidosMesCount }}</span></div>
                    <div class="data-row"><span class="data-label">Pendientes</span><span class="data-value" style="color:var(--d-amber);">{{ $pedidosPendientes }}</span></div>
                    <div class="data-row"><span class="data-label">Pagados</span><span class="data-value" style="color:var(--d-green);">{{ $statPagados ? $statPagados->total : 0 }}</span></div>
                    <div class="data-row"><span class="data-label">Entregados</span><span class="data-value" style="color:var(--d-blue);">{{ $statEntregados ? $statEntregados->total : 0 }}</span></div>
                    <div class="finance-total">
                        <div class="finance-total-label">Total Facturado</div>
                        <div class="finance-total-number" style="color:var(--d-purple);">${{ number_format($ecommerceMes, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 4: Gráfico Ventas vs Compras por mes del año --}}
    <p class="section-label">Evolución anual {{ now()->year }}</p>
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="chart-card-clean">
                <div class="chart-header">
                    <span class="chart-title"><i class="fas fa-chart-bar mr-1"></i> Ventas vs Compras por mes</span>
                    <span class="chart-badge">{{ now()->year }}</span>
                </div>
                <div style="height:220px;"><canvas id="chart-anio"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card-clean">
                <div class="chart-header">
                    <span class="chart-title"><i class="fas fa-globe mr-1" style="color:var(--farg-purple-light);"></i> Pedidos web</span>
                    <span class="chart-badge">por mes</span>
                </div>
                <div style="height:220px;"><canvas id="chart-ecommerce"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ROW 5: Gráfico ventas día a día del mes actual --}}
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="chart-card-clean">
                <div class="chart-header">
                    <span class="chart-title"><i class="fas fa-chart-area mr-1 text-primary"></i> Ventas día a día — {{ now()->locale('es')->isoFormat('MMMM YYYY') }}</span>
                    <span class="chart-badge">${{ number_format($ventasMes, 0, ',', '.') }} acumulado</span>
                </div>
                <div style="height:180px;"><canvas id="chart-dia"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ROW 6: Analytics --}}
    <p class="section-label">Análisis del mes</p>
    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <div class="analytics-card">
                <div class="analytics-header">
                    <i class="fas fa-trophy" style="color:#f59e0b;"></i>
                    Top 5 Productos — {{ now()->locale('es')->isoFormat('MMMM') }}
                </div>
                <div class="analytics-body">
                    @forelse($topProductos as $i => $prod)
                        <div class="top-product-row">
                            <div class="rank-badge rank-{{ $i + 1 }}">{{ $i + 1 }}</div>
                            <span class="product-name text-truncate">{{ Str::limit($prod->nombre, 30) }}</span>
                            <span class="product-qty">{{ number_format($prod->total_vendido) }} u.</span>
                            <span class="product-total">${{ number_format($prod->total_monto, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 small mb-0">Sin ventas registradas este mes</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="analytics-card">
                <div class="analytics-header">
                    <i class="fas fa-calendar-alt" style="color:var(--farg-blue-light);"></i>
                    Mes a Mes
                </div>
                <div class="analytics-body">
                    <div class="mes-stat">
                        <div class="mes-label">{{ now()->locale('es')->isoFormat('MMMM YYYY') }}</div>
                        <div class="mes-amount">
                            ${{ number_format($ventasMes, 0, ',', '.') }}
                            @if($variacionMes !== null)
                                <span class="variacion-badge {{ $variacionMes >= 0 ? 'variacion-up' : 'variacion-down' }}">
                                    <i class="fas fa-arrow-{{ $variacionMes >= 0 ? 'up' : 'down' }}"></i> {{ abs($variacionMes) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="mes-stat">
                        <div class="mes-label">{{ now()->subMonth()->locale('es')->isoFormat('MMMM YYYY') }}</div>
                        <div class="mes-amount">${{ number_format($ventasMesAnterior, 0, ',', '.') }}</div>
                    </div>
                    <div class="mes-stat">
                        <div class="mes-label">Total Clientes</div>
                        <div class="mes-amount">{{ number_format($totalClientes) }}</div>
                    </div>
                    <div class="mes-stat">
                        <div class="mes-label">Devoluciones del mes</div>
                        <div class="mes-amount" style="font-size:1.1rem;color:{{ $devolucionesMes > 0 ? '#dc2626' : '#1e293b' }}">
                            {{ $devolucionesMes }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="analytics-card">
                <div class="analytics-header">
                    <i class="fas fa-chart-pie" style="color:var(--farg-purple-light);"></i>
                    Pedidos Web por Estado
                </div>
                <div class="analytics-body" style="padding-top:0.4rem;">
                    @if($pedidosPorEstado->isNotEmpty())
                        <div style="height:210px;"><canvas id="pedidos-estado-chart"></canvas></div>
                    @else
                        <p class="text-muted text-center py-5 small mb-0">Sin pedidos registrados</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 7: Gráficos hoy vs ayer (existentes) --}}
    <p class="section-label">Rendimiento diario</p>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="chart-card-clean">
                <div class="chart-header">
                    <span class="chart-title">Ventas de hoy</span>
                    <span id="span_estatus" class="small">
                        <i id="fas_estatus"></i> <span id="up_down"></span>
                    </span>
                </div>
                <span class="show_total_today d-none"></span>
                <div style="height:180px;"><canvas id="sales-chart"></canvas></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-card-clean">
                <div class="chart-header">
                    <span class="chart-title">Hoy vs Ayer</span>
                </div>
                <div style="height:180px;"><canvas id="sales-chart-comparation"></canvas></div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="{{ asset('js/functions_dashboard/admin.js') }}" type="module"></script>
<script>
    var dashData = {
        meses:          {!! json_encode($mesesLabels) !!},
        ventasAnio:     {!! json_encode($graficoVentasAnio) !!},
        comprasAnio:    {!! json_encode($graficoComprasAnio) !!},
        ecommerceCount: {!! json_encode($graficoEcommerceCount) !!},
        diasLabels:     {!! json_encode($graficoDiasLabels) !!},
        ventasDia:      {!! json_encode($graficoVentasDia) !!},
        pedidosEstados: {!! json_encode($pedidosPorEstado->pluck('estado')->values()) !!},
        pedidosTotales: {!! json_encode($pedidosPorEstado->pluck('total')->values()) !!}
    };

    function initDashboardCharts() {
        if (typeof Chart === 'undefined') { return setTimeout(initDashboardCharts, 100); }

        var BLUE      = 'rgba(24,90,219,0.85)';
        var AMBER     = 'rgba(245,158,11,0.85)';
        var PURPLE    = 'rgba(124,58,237,0.85)';
        var BLUE_LINE = 'rgba(24,90,219,1)';
        var BLUE_FILL = 'rgba(24,90,219,0.08)';

        function moneyTick(v) {
            if (v >= 1000000) return '$' + (v / 1000000).toFixed(1) + 'M';
            if (v >= 1000)    return '$' + Math.round(v / 1000) + 'k';
            return '$' + v;
        }

        // 1. Ventas vs Compras por mes (Chart.js v3)
        var elAnio = document.getElementById('chart-anio');
        if (elAnio) {
            new Chart(elAnio, {
                type: 'bar',
                data: {
                    labels: dashData.meses,
                    datasets: [
                        { label: 'Ventas',  backgroundColor: BLUE,  data: dashData.ventasAnio,  barPercentage: 0.45 },
                        { label: 'Compras', backgroundColor: AMBER, data: dashData.comprasAnio, barPercentage: 0.45 }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': $' + ctx.parsed.y.toLocaleString('es-AR');
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: moneyTick }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 2. Ventas día a día del mes (Chart.js v3)
        var elDia = document.getElementById('chart-dia');
        if (elDia) {
            new Chart(elDia, {
                type: 'line',
                data: {
                    labels: dashData.diasLabels,
                    datasets: [{
                        label: 'Ventas del día',
                        borderColor: BLUE_LINE,
                        backgroundColor: BLUE_FILL,
                        data: dashData.ventasDia,
                        fill: true,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        tension: 0.3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: { label: function(ctx) { return '$' + ctx.parsed.y.toLocaleString('es-AR'); } }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: moneyTick }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 3. Pedidos web por mes (Chart.js v3)
        var elEco = document.getElementById('chart-ecommerce');
        if (elEco) {
            new Chart(elEco, {
                type: 'bar',
                data: {
                    labels: dashData.meses,
                    datasets: [{
                        label: 'Pedidos',
                        backgroundColor: PURPLE,
                        data: dashData.ecommerceCount,
                        barPercentage: 0.55
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 4. Donut pedidos por estado (Chart.js v3)
        var elDonut = document.getElementById('pedidos-estado-chart');
        if (elDonut && dashData.pedidosEstados.length > 0) {
            new Chart(elDonut, {
                type: 'doughnut',
                data: {
                    labels: dashData.pedidosEstados,
                    datasets: [{
                        data: dashData.pedidosTotales,
                        backgroundColor: ['#f59e0b','#3b82f6','#10b981','#6366f1','#059669','#ef4444'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12, padding: 8 } }
                    }
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    } else {
        initDashboardCharts();
    }
</script>
@endsection
