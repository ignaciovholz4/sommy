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

    .kpi-mini-card {
        background: white;
        border-radius: 10px;
        padding: 0.9rem 0.75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        text-align: center;
        border: 1px solid #f1f5f9;
    }
    .kpi-mini-icon {
        width: 34px; height: 34px;
        background: var(--facturarg-dark);
        color: white;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 0.45rem;
        font-size: 0.8rem;
    }
    .kpi-icon-success { background: #10b981 !important; }
    .kpi-icon-danger  { background: #ef4444 !important; }
    .kpi-mini-label {
        font-size: 0.62rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
    }
    .kpi-mini-value {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--facturarg-dark);
        line-height: 1.2;
    }
    .kpi-mini-sub {
        font-size: 0.72rem;
        font-weight: 600;
        margin-top: 0.15rem;
    }
    .kpi-val-success { color: #10b981; }
    .kpi-val-muted   { color: #94a3b8; }

    /* ── Tarjetas de cuentas ─────────────────────── */
    .cuentas-grupo-titulo {
        font-weight: 800;
        color: var(--facturarg-dark);
        margin: 0 0 0.8rem;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }
    .cuentas-grupo-titulo i { color: #64748b; }

    .cuentas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 16px;
    }

    .cuenta-card {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 16px;
        padding: 18px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        display: flex;
        flex-direction: column;
    }
    .cuenta-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px -10px rgba(15, 23, 42, 0.18);
    }
    .cuenta-inactiva { opacity: 0.55; }

    .cuenta-card-top { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .min-w-0 { min-width: 0; }

    .cuenta-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        color: #fff;
    }
    .cuenta-icon-caja  { background: var(--facturarg-dark); }
    .cuenta-icon-banco { background: #2563EB; }

    .cuenta-nombre {
        font-weight: 800;
        color: var(--facturarg-dark);
        font-size: 1rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cuenta-sub { font-size: 0.78rem; color: #94a3b8; font-weight: 600; }

    .cuenta-chip {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .cuenta-chip i { font-size: 0.65rem; }
    .cuenta-chip-verde { background: #DCFCE7; color: #15803D; }
    .cuenta-chip-gris  { background: #F1F5F9; color: #64748B; }
    .cuenta-chip-azul  { background: #DBEAFE; color: #1D4ED8; }

    .cuenta-saldo-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #94a3b8;
    }
    .cuenta-saldo {
        font-size: 1.9rem;
        font-weight: 800;
        color: #10b981;
        line-height: 1.2;
        margin-bottom: 2px;
    }
    .cuenta-saldo-rojo  { color: #ef4444; }
    .cuenta-saldo-muted { color: #cbd5e1; }

    .cuenta-ultimo { font-size: 0.78rem; color: #475569; margin-bottom: 12px; }

    .cuenta-acciones {
        display: flex;
        gap: 8px;
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }
    .btn-cuenta-principal {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        background: var(--facturarg-bg);
        color: var(--facturarg-dark) !important;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        padding: 8px 10px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none !important;
        transition: background 0.15s;
    }
    .btn-cuenta-principal:hover { background: #e2e8f0; }

    .btn-cuenta-icono {
        width: 36px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        border-radius: 9px;
        transition: all 0.15s;
    }
    .btn-cuenta-icono:hover { background: var(--facturarg-dark); color: #fff; border-color: var(--facturarg-dark); }
    .btn-cuenta-icono-verde { color: #15803D; border-color: #BBF7D0; }
    .btn-cuenta-icono-verde:hover { background: #15803D; border-color: #15803D; }

    .cuentas-empty {
        background: #fff;
        border: 2px dashed #e2e8f0;
        border-radius: 16px;
        text-align: center;
        padding: 3.5rem 1rem;
        color: #94a3b8;
    }
    .cuentas-empty i { font-size: 2.2rem; margin-bottom: 0.8rem; }
    .cuentas-empty p { font-weight: 600; margin-bottom: 1.2rem; }
</style>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold h2 text-dark mb-1">Gestión de Cuentas</h1>
            <p class="text-muted small mb-0">Administre cuentas de caja y bancos.</p>
        </div>
        
        <button class="btn-facturarg-main" data-bs-toggle="modal" data-bs-target="#modalNuevaCuenta">
            <i class="fas fa-plus-circle me-2"></i> NUEVA CUENTA
        </button>
    </div>

    @php
        $cajas  = $cuentas->where('tipo', 'caja');
        $bancos = $cuentas->where('tipo', 'banco');
    @endphp

    @if($cuentas->isEmpty())
        <div class="cuentas-empty">
            <i class="fas fa-wallet"></i>
            <p>Todavía no hay cuentas creadas.</p>
            <button class="btn-facturarg-main" data-bs-toggle="modal" data-bs-target="#modalNuevaCuenta">
                <i class="fas fa-plus-circle me-2"></i> Crear la primera cuenta
            </button>
        </div>
    @else
        <div class="d-flex justify-content-end mb-3">
            <input type="search" id="buscador-cuentas" class="form-control form-control-custom" style="max-width:280px"
                   placeholder="Buscar cuenta o sucursal...">
        </div>

        @foreach([['titulo' => 'Cajas', 'icono' => 'fa-cash-register', 'items' => $cajas],
                  ['titulo' => 'Bancos', 'icono' => 'fa-university', 'items' => $bancos]] as $grupo)
            @if($grupo['items']->isNotEmpty())
            <h5 class="cuentas-grupo-titulo"><i class="fas {{ $grupo['icono'] }}"></i> {{ $grupo['titulo'] }}</h5>
            <div class="cuentas-grid mb-4">
                @foreach($grupo['items'] as $cuenta)
                <div class="cuenta-card {{ $cuenta->activa ? '' : 'cuenta-inactiva' }}"
                     data-buscar="{{ mb_strtolower($cuenta->nombre . ' ' . ($cuenta->sucursal->nombre ?? '')) }}">
                    <div class="cuenta-card-top">
                        <div class="cuenta-icon {{ $cuenta->tipo === 'caja' ? 'cuenta-icon-caja' : 'cuenta-icon-banco' }}">
                            <i class="fas {{ $cuenta->tipo === 'caja' ? 'fa-cash-register' : 'fa-university' }}"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="cuenta-nombre">{{ $cuenta->nombre }}</div>
                            <div class="cuenta-sub">{{ $cuenta->sucursal->nombre ?? '—' }} · {{ $cuenta->moneda->codigo ?? '' }}</div>
                        </div>
                        <div class="text-end">
                            @if(!$cuenta->activa)
                                <span class="cuenta-chip cuenta-chip-gris">Inactiva</span>
                            @elseif($cuenta->tipo === 'caja')
                                @if($cuenta->apertura_abierta)
                                    <span class="cuenta-chip cuenta-chip-verde"><i class="fas fa-door-open"></i> Abierta</span>
                                @else
                                    <span class="cuenta-chip cuenta-chip-gris"><i class="fas fa-door-closed"></i> Cerrada</span>
                                @endif
                            @else
                                <span class="cuenta-chip cuenta-chip-azul">Banco</span>
                            @endif
                        </div>
                    </div>

                    <div class="cuenta-saldo-label">{{ $cuenta->tipo === 'caja' ? 'Plata en caja' : 'Saldo' }}</div>
                    @if($cuenta->saldo_actual === null)
                        <div class="cuenta-saldo cuenta-saldo-muted">—</div>
                        <div class="cuenta-ultimo text-muted">Sin apertura activa: abrí la caja para operar</div>
                    @else
                        <div class="cuenta-saldo {{ $cuenta->saldo_actual < 0 ? 'cuenta-saldo-rojo' : '' }}">
                            ${{ number_format($cuenta->saldo_actual, 2, ',', '.') }}
                        </div>
                        @if($cuenta->ultimo_mov)
                            <div class="cuenta-ultimo">
                                Últ. movimiento:
                                <span class="{{ $cuenta->ultimo_mov->tipo === 'ingreso' ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ $cuenta->ultimo_mov->tipo === 'ingreso' ? '+' : '−' }}${{ number_format($cuenta->ultimo_mov->total, 2, ',', '.') }}
                                </span>
                                <span class="text-muted">· {{ \Carbon\Carbon::parse($cuenta->ultimo_mov->fecha)->format('d/m/Y') }}</span>
                            </div>
                        @else
                            <div class="cuenta-ultimo text-muted">Sin movimientos todavía</div>
                        @endif
                    @endif

                    <div class="cuenta-acciones">
                        @if($cuenta->tipo === 'caja')
                            <a href="{{ route('cuentas.historial', $cuenta->id) }}" class="btn-cuenta-principal">
                                <i class="fas fa-door-open"></i> Historial de caja
                            </a>
                        @else
                            <a href="{{ route('cuentas.movimientos.index', $cuenta->id) }}" class="btn-cuenta-principal">
                                <i class="fas fa-list"></i> Ver movimientos
                            </a>
                        @endif
                        <button class="btn-cuenta-icono btn-edit" title="Editar"
                                data-id="{{ $cuenta->id }}" data-nombre="{{ $cuenta->nombre }}" data-activa="{{ $cuenta->activa }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        @if($cuenta->activa)
                            <button class="btn-cuenta-icono btn-deactivate" title="Desactivar" data-id="{{ $cuenta->id }}">
                                <i class="fas fa-power-off"></i>
                            </button>
                        @else
                            <button class="btn-cuenta-icono btn-cuenta-icono-verde btn-activate" title="Activar" data-id="{{ $cuenta->id }}">
                                <i class="fas fa-check"></i>
                            </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        @endforeach
    @endif

    <div class="mt-5 d-flex justify-content-end">
        <a href="{{ route('admin') }}" class="btn btn-link text-decoration-none text-muted fw-bold">
            <i class="fas fa-arrow-left me-2"></i> VOLVER AL PANEL PRINCIPAL
        </a>
    </div>
</div>

<div class="modal fade" id="modalNuevaCuenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header modal-header-dark">
                <h5 class="modal-title fw-bold">CREAR NUEVA CUENTA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('cuentas.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-custom">Nombre de la cuenta</label>
                        <input type="text" class="form-control form-control-custom" name="nombre" placeholder="Ej: Caja Diaria" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Sucursal</label>
                        <div class="input-group">
                            <select id="sucursal" class="form-select form-control-custom" name="sucursal_id" required>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalSucursal">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Moneda</label>
                        <select class="form-select form-control-custom" name="moneda_id" required>
                            @foreach($monedas as $moneda)
                                <option value="{{ $moneda->id }}">{{ $moneda->nombre }} ({{ $moneda->codigo }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Tipo de cuenta</label>
                        <select class="form-select form-control-custom" name="tipo" required>
                            <option value="caja">Caja</option>
                            <option value="banco">Banco</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="activa" name="activa" value="1" checked>
                        <label class="form-check-label fw-bold text-dark" for="activa">CUENTA ACTIVA</label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-dark px-4 fw-bold">GUARDAR CUENTA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal nueva sucursal -->
<div class="modal fade" id="modalSucursal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Sucursal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formSucursal">
          <input type="hidden" id="sucursal_id">

          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre_sucursal" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Código</label>
            <input type="text" class="form-control" id="codigo_sucursal">
          </div>

          <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion_sucursal">
          </div>

          <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono_sucursal">
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" id="email_sucursal">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarSucursal">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEditarCuenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header modal-header-dark">
                <h5 class="modal-title fw-bold">EDITAR CUENTA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formEditarCuenta" action="{{ route('cuentas.actualizar', ['cuenta' => '__ID__']) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-custom">Nombre de la cuenta</label>
                        <input type="text" class="form-control form-control-custom" id="edit_nombre" name="nombre" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-dark px-4 fw-bold">GUARDAR CAMBIOS</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('ScriptCuentaGestor')
<script src="{{ asset('js/funciones_cuenta/gestor_cuenta.js') }}?v={{ filemtime(public_path('js/funciones_cuenta/gestor_cuenta.js')) }}"></script>
@endpush
@endsection