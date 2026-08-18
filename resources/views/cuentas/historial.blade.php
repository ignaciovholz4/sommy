@extends('layouts.admin')

@section('contenido')
<style>
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-bg: #f1f5f9;
    }

    .main-container {
        background-color: var(--facturarg-bg);
        min-height: 100vh;
        padding: 2rem;
    }

    .btn-facturarg-main {
        background-color: var(--facturarg-dark);
        color: white;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 700;
        border: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }
    .btn-facturarg-main:hover { background-color: #1e293b; color: #22d3ee; transform: translateY(-1px); }
    .btn-facturarg-main.btn-verde { background: #10b981; }
    .btn-facturarg-main.btn-verde:hover { background: #059669; color: #fff; }
    .btn-facturarg-main.btn-rojo { background: #fff; color: #b91c1c; border: 2px solid #fecaca; box-shadow: none; }
    .btn-facturarg-main.btn-rojo:hover { background: #fef2f2; color: #991b1b; }

    /* ── Banda hero de estado ─────────────────────── */
    .caja-hero {
        border-radius: 16px;
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
        color: white;
        background: var(--facturarg-dark);
    }
    .caja-hero-main { display: flex; align-items: center; gap: 16px; }
    .caja-hero-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.12);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .caja-hero-label {
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.7px; color: rgba(255,255,255,0.55);
    }
    .caja-hero-valor { font-size: 2.1rem; font-weight: 800; line-height: 1.15; }
    .caja-hero-stats { display: flex; gap: 28px; flex-wrap: wrap; }
    .caja-hero-stat-valor { font-size: 1.05rem; font-weight: 700; }
    .stat-verde { color: #6EE7B7; }
    .stat-rojo  { color: #FCA5A5; }
    .caja-abierta-chip {
        background: #DCFCE7; color: #15803D; font-size: 0.7rem; font-weight: 700;
        border-radius: 999px; padding: 4px 12px; display: inline-flex; align-items: center; gap: 5px;
    }

    /* Estado cerrada */
    .caja-cerrada {
        background: #fff;
        border: 2px dashed #e2e8f0;
        border-radius: 16px;
        text-align: center;
        padding: 3rem 1.5rem;
        color: #64748b;
        margin-bottom: 1.5rem;
    }
    .caja-cerrada i { font-size: 2.4rem; color: #94a3b8; margin-bottom: 0.8rem; }
    .caja-cerrada h5 { color: var(--facturarg-dark); font-weight: 800; }
    .caja-cerrada p { max-width: 420px; margin: 0.3rem auto 1.3rem; font-size: 0.9rem; }

    /* ── Sesiones (historial) ─────────────────────── */
    .sesiones-titulo {
        font-weight: 800; color: var(--facturarg-dark);
        display: flex; align-items: center; gap: 8px;
        margin: 0 0 0.8rem; font-size: 1rem;
    }
    .sesion-card {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        transition: box-shadow 0.15s;
    }
    .sesion-card:hover { box-shadow: 0 10px 24px -10px rgba(15,23,42,0.18); }
    .sesion-card.abierta { border: 2px solid #A7F3D0; background: #F0FDF9; }

    .sesion-fechas { min-width: 210px; }
    .sesion-rango { font-weight: 700; color: var(--facturarg-dark); font-size: 0.9rem; }
    .sesion-sub { font-size: 0.75rem; color: #94a3b8; }

    .sesion-cifras { display: flex; gap: 22px; flex-wrap: wrap; }
    .sesion-cifra .lbl {
        font-size: 0.62rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: #94a3b8;
    }
    .sesion-cifra .val { font-weight: 800; font-size: 0.95rem; color: var(--facturarg-dark); }
    .val-verde { color: #10b981 !important; }
    .val-rojo  { color: #ef4444 !important; }

    .sesion-acciones { display: flex; gap: 8px; }
    .btn-sesion {
        border: 1px solid #e2e8f0; background: #f8fafc; color: var(--facturarg-dark);
        border-radius: 9px; padding: 7px 14px; font-size: 0.8rem; font-weight: 700;
        text-decoration: none !important; display: inline-flex; align-items: center; gap: 6px;
        transition: background 0.15s; cursor: pointer;
    }
    .btn-sesion:hover { background: #e2e8f0; color: var(--facturarg-dark); }
    .btn-sesion.cerrar { background: #fff; color: #b91c1c; border-color: #fecaca; }
    .btn-sesion.cerrar:hover { background: #fef2f2; }

    .modal-content-facturarg { border: none; border-radius: 20px; overflow: hidden; }
    .modal-header-dark { background-color: var(--facturarg-dark); color: white; padding: 1.5rem 2rem; }
    .resumen-table th {
        background-color: #f8fafc; color: #64748b; text-transform: uppercase;
        font-size: 0.75rem; font-weight: 800; padding: 1rem !important; border: 1px solid #edf2f7;
    }
    .resumen-table td { padding: 1rem !important; font-weight: 600; color: var(--facturarg-dark); border: 1px solid #edf2f7; }
</style>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap:12px">
        <div>
            <h1 class="fw-bold h2 text-dark mb-1"><i class="fas fa-cash-register me-2 text-muted"></i>{{ $cuenta->nombre }}</h1>
            <p class="text-muted small mb-0">Caja · {{ $cuenta->sucursal->nombre ?? '' }} — aperturas, cierres y flujo de efectivo</p>
        </div>

        <div class="d-flex flex-wrap" style="gap:10px">
            @if($kpiAbierta)
                <a href="{{ route('cuentas.movimientos.index', ['cuenta' => $cuenta->id, 'apertura' => $aperturaActivaId]) }}" class="btn-facturarg-main btn-verde">
                    <i class="fas fa-exchange-alt me-2"></i> Ver / registrar movimientos
                </a>
                <button class="btn-facturarg-main btn-rojo btn-cerrar" data-id="{{ $cuenta->id }}">
                    <i class="fas fa-door-closed me-2"></i> Cerrar caja
                </button>
            @else
                <button class="btn-facturarg-main btn-abrir-principal" data-id="{{ $cuenta->id }}">
                    <i class="fas fa-door-open me-2"></i> Abrir caja
                </button>
            @endif
        </div>
    </div>

    @if($kpiAbierta)
        {{-- Caja abierta: la plata ahora, bien grande --}}
        <div class="caja-hero">
            <div class="caja-hero-main">
                <div class="caja-hero-icon"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="caja-hero-label">Plata en caja ahora</div>
                    <div class="caja-hero-valor">${{ number_format($kpiSaldo, 2, ',', '.') }}</div>
                    <span class="caja-abierta-chip mt-1"><i class="fas fa-door-open"></i> Abierta {{ $kpiDesde ? 'desde ' . $kpiDesde->format('d/m H:i') : '' }}</span>
                </div>
            </div>
            <div class="caja-hero-stats">
                <div>
                    <div class="caja-hero-label">Fondo inicial</div>
                    <div class="caja-hero-stat-valor">${{ number_format($kpiFondo, 2, ',', '.') }}</div>
                </div>
                <div>
                    <div class="caja-hero-label">Ingresos</div>
                    <div class="caja-hero-stat-valor stat-verde"><i class="fas fa-arrow-up me-1"></i>${{ number_format($kpiIngresos, 2, ',', '.') }}</div>
                </div>
                <div>
                    <div class="caja-hero-label">Egresos</div>
                    <div class="caja-hero-stat-valor stat-rojo"><i class="fas fa-arrow-down me-1"></i>${{ number_format($kpiEgresos, 2, ',', '.') }}</div>
                </div>
                <div>
                    <div class="caja-hero-label">Movimientos</div>
                    <div class="caja-hero-stat-valor">{{ $kpiMovCount }}</div>
                </div>
            </div>
        </div>
    @else
        {{-- Caja cerrada: mensaje claro con la acción principal --}}
        <div class="caja-cerrada">
            <i class="fas fa-door-closed d-block"></i>
            <h5>La caja está cerrada</h5>
            <p>Para cobrar ventas o registrar movimientos en efectivo, abrí la caja indicando con cuánta plata arranca (el fondo inicial).</p>
            <button class="btn-facturarg-main btn-abrir-principal" data-id="{{ $cuenta->id }}">
                <i class="fas fa-door-open me-2"></i> Abrir caja
            </button>
        </div>
    @endif

    {{-- Historial de sesiones --}}
    <h5 class="sesiones-titulo"><i class="fas fa-history text-muted"></i> Sesiones anteriores</h5>

    @forelse($aperturas as $ap)
        @php $abierta = $ap->estaAbierta(); @endphp
        <div class="sesion-card {{ $abierta ? 'abierta' : '' }}">
            <div class="sesion-fechas">
                <div class="sesion-rango">
                    {{ \Carbon\Carbon::parse($ap->fecha_apertura)->format('d/m/Y H:i') }}
                    →
                    @if($abierta)
                        <span class="val-verde">en curso</span>
                    @else
                        {{ $ap->fecha_cierre ? \Carbon\Carbon::parse($ap->fecha_cierre)->format('d/m/Y H:i') : '—' }}
                    @endif
                </div>
                <div class="sesion-sub">{{ $ap->mov_cant }} movimiento{{ $ap->mov_cant == 1 ? '' : 's' }}</div>
            </div>

            <div class="sesion-cifras">
                <div class="sesion-cifra">
                    <div class="lbl">Fondo inicial</div>
                    <div class="val">${{ number_format($ap->fondo_inicial, 2, ',', '.') }}</div>
                </div>
                <div class="sesion-cifra">
                    <div class="lbl">Ingresos</div>
                    <div class="val val-verde">+${{ number_format($ap->mov_ingresos, 2, ',', '.') }}</div>
                </div>
                <div class="sesion-cifra">
                    <div class="lbl">Egresos</div>
                    <div class="val val-rojo">−${{ number_format($ap->mov_egresos, 2, ',', '.') }}</div>
                </div>
                <div class="sesion-cifra">
                    <div class="lbl">{{ $abierta ? 'Saldo actual' : 'Saldo final' }}</div>
                    <div class="val {{ $ap->saldo_final < 0 ? 'val-rojo' : '' }}">${{ number_format($ap->saldo_final, 2, ',', '.') }}</div>
                </div>
            </div>

            <div class="sesion-acciones">
                <a href="{{ route('cuentas.movimientos.index', ['cuenta' => $cuenta->id, 'apertura' => $ap->id]) }}" class="btn-sesion" title="Ver los movimientos de esta sesión">
                    <i class="fas fa-list"></i> Movimientos
                </a>
                @if($abierta)
                    <button class="btn-sesion cerrar btn-cerrar" data-id="{{ $cuenta->id }}">
                        <i class="fas fa-door-closed"></i> Cerrar
                    </button>
                @else
                    <button class="btn-sesion btn-resumen" data-apertura-id="{{ $ap->id }}">
                        <i class="fas fa-file-alt"></i> Resumen
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="text-muted text-center py-4">Esta caja todavía no tuvo aperturas.</div>
    @endforelse

    <div class="mt-4 d-flex justify-content-end">
        <a href="{{ route('cuentas.index') }}" class="btn btn-link text-decoration-none text-muted fw-bold">
            <i class="fas fa-arrow-left me-2"></i> Volver al gestor de cuentas
        </a>
    </div>
</div>

{{-- Modal Resumen --}}
<div class="modal fade" id="detailCuentaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-content-facturarg shadow-lg">
            <div class="modal-header modal-header-dark">
                <h5 class="modal-title fw-bold">RESUMEN DE OPERACIÓN</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table resumen-table mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 35%">Empresa</th>
                                <td id="name_empresa" class="text-info">—</td>
                            </tr>
                            <tr>
                                <th>Fecha cierre</th>
                                <td id="date_operation">—</td>
                            </tr>
                            <tr>
                                <th>Responsable</th>
                                <td id="name_responsable">—</td>
                            </tr>
                            <tr>
                                <th>Fondo Inicial</th>
                                <td id="fondo_cuenta" class="text-dark">0.00</td>
                            </tr>
                            <tr>
                                <th>Ingresos Extra</th>
                                <td id="resumen_ingresos" class="text-success">+ 0.00</td>
                            </tr>
                            <tr>
                                <th>Egresos Extra</th>
                                <td id="resumen_egresos" class="text-danger">- 0.00</td>
                            </tr>
                            <tr>
                                <th>Total Ventas Efectivo</th>
                                <td id="sale_efectivo" class="fw-bold">0.00</td>
                            </tr>
                            <tr class="bg-light">
                                <th class="text-primary">Efectivo Final</th>
                                <td id="efectivo_cuenta" class="text-primary fw-bolder" style="font-size: 1.1rem;">0.00</td>
                            </tr>
                            <tr>
                                <th>Faltante</th>
                                <td id="faltante_cuenta" class="text-danger">0.00</td>
                            </tr>
                            <tr>
                                <th>Sobrante</th>
                                <td id="sobrante_cuenta" class="text-success">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light p-4 border-0">
                <button class="btn btn-dark px-5 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px;">
                    ENTENDIDO
                </button>
            </div>
        </div>
    </div>
</div>

@push('ScriptaperturaCierreCuenta')
<script src="{{ asset('js/funciones_cuenta/apertura_cierre_cuenta.js') }}?v={{ filemtime(public_path('js/funciones_cuenta/apertura_cierre_cuenta.js')) }}"></script>
@endpush
@endsection
