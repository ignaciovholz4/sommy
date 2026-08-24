@extends('layouts.admin')
@section('contenido')

<style>
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-cyan: #1591a3;
        --facturarg-bg: #f1f5f9;
        --facturarg-accent: #22d3ee;
    }
    .main-container { background-color: var(--facturarg-bg); min-height: 100vh; padding: 2rem; }
    .card-facturarg { border: none; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); background: white; overflow: hidden; }
    .card-facturarg .card-header { background: var(--facturarg-dark); color: white; font-weight: 700; padding: 1rem 1.5rem; }
    .btn-facturarg-main { background-color: var(--facturarg-dark); color: white; border-radius: 8px; padding: 10px 20px; font-weight: 600; border: none; }
    .btn-facturarg-main:hover { background-color: #1e293b; color: var(--facturarg-accent); }
    .btn-facturarg-main:disabled { opacity: .5; }

    .conc-tabs { display:flex; gap:8px; margin-bottom:1rem; }
    .conc-tab { border:none; background:#e2e8f0; color:#475569; font-weight:700; font-size:.8rem; padding:8px 16px; border-radius:999px; cursor:pointer; }
    .conc-tab.active { background: var(--facturarg-dark); color:white; }

    .mov-chip { display:inline-flex; align-items:center; gap:5px; font-size:.72rem; font-weight:700; padding:4px 10px; border-radius:999px; white-space:nowrap; }
    .mov-chip-ingreso { background:#DCFCE7; color:#15803D; }
    .mov-chip-egreso  { background:#FEE2E2; color:#B91C1C; }
    .mov-chip-pendiente { background:#FEF9C3; color:#854D0E; }
    .mov-chip-conciliado { background:#DCFCE7; color:#15803D; }
    .mov-chip-descartado { background:#F1F5F9; color:#64748B; }

    .conc-sugerencia { background:#F0F9FF; border:1px solid #BAE6FD; border-radius:8px; padding:8px 10px; font-size:.78rem; color:#0369A1; }
    .conc-sugerencia b { color:#0f172a; }

    table.conc-table th { background:var(--facturarg-dark); color:white; text-transform:uppercase; font-size:.7rem; padding:.8rem; border:none; white-space:nowrap; }
    table.conc-table td { padding:.8rem; vertical-align:middle; border-bottom:1px solid #f1f5f9; font-size:.85rem; }

    .dropzone-conc {
        border: 2px dashed #cbd5e1; border-radius: 12px; padding: 28px; text-align:center;
        color:#64748b; background:#f8fafc; cursor:pointer;
    }
    .dropzone-conc:hover { border-color: var(--facturarg-accent); }

    .mapeo-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 14px; }
    .mapeo-grid label { font-size:.72rem; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:4px; display:block; }
</style>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap:12px">
        <div>
            <h1 class="fw-bold h2 text-dark mb-1">Conciliación bancaria</h1>
            <p class="text-muted small mb-0">{{ $cuenta->nombre }} · {{ $cuenta->sucursal->nombre ?? '' }}</p>
        </div>
        <a href="{{ route('cuentas.movimientos.index', $cuenta->id) }}" class="btn btn-link text-decoration-none text-muted fw-bold">
            <i class="fas fa-arrow-left me-2"></i> Volver a movimientos
        </a>
    </div>

    {{-- Paso 1: subir archivo --}}
    <div class="card-facturarg mb-4" id="conc-card-upload">
        <div class="card-header"><i class="fas fa-file-upload me-2"></i> 1. Subir extracto de movimientos</div>
        <div class="card-body p-4">
            <p class="text-muted small mb-3">
                Subí el archivo de movimientos que exportaste del banco, billetera o cuenta CVU/CBU (Excel o CSV).
                En el siguiente paso vas a indicar qué columna es cada dato, porque cada plataforma exporta distinto.
            </p>
            <div class="dropzone-conc" id="conc-dropzone">
                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                <div class="fw-bold">Hacé clic para elegir un archivo</div>
                <div class="small">Excel (.xlsx, .xls) o CSV — hasta 10MB</div>
                <input type="file" id="conc-input-archivo" accept=".xlsx,.xls,.csv,.txt" style="display:none">
            </div>
            <div id="conc-archivo-nombre" class="small text-muted mt-2"></div>
        </div>
    </div>

    {{-- Paso 2: mapeo de columnas (oculto hasta subir archivo) --}}
    <div class="card-facturarg mb-4" id="conc-card-mapeo" style="display:none">
        <div class="card-header"><i class="fas fa-columns me-2"></i> 2. Indicar qué es cada columna</div>
        <div class="card-body p-4">

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="conc-con-encabezado" checked>
                <label class="form-check-label small fw-bold" for="conc-con-encabezado">
                    La primera fila del archivo es un encabezado (no es un movimiento real)
                </label>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered" id="conc-preview-table" style="font-size:.78rem"></table>
            </div>

            <div class="mapeo-grid mb-3">
                <div>
                    <label>Columna Fecha</label>
                    <select class="form-select form-select-sm" id="conc-col-fecha"></select>
                </div>
                <div>
                    <label>Columna Descripción (opcional)</label>
                    <select class="form-select form-select-sm" id="conc-col-descripcion"></select>
                </div>
                <div>
                    <label>Columna Referencia / Nº operación (opcional)</label>
                    <select class="form-select form-select-sm" id="conc-col-referencia"></select>
                </div>
            </div>

            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">¿Cómo viene el importe en el archivo?</label>
            <div class="d-flex gap-4 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="conc-modo-importe" id="conc-modo-signo" value="signo_unico" checked>
                    <label class="form-check-label small" for="conc-modo-signo">Una sola columna, con signo (+ ingreso / − egreso)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="conc-modo-importe" id="conc-modo-dos" value="dos_columnas">
                    <label class="form-check-label small" for="conc-modo-dos">Dos columnas separadas (créditos y débitos)</label>
                </div>
            </div>

            <div class="mapeo-grid mb-4">
                <div id="conc-wrap-importe">
                    <label>Columna Importe</label>
                    <select class="form-select form-select-sm" id="conc-col-importe"></select>
                </div>
                <div id="conc-wrap-ingreso" style="display:none">
                    <label>Columna Ingresos / Créditos</label>
                    <select class="form-select form-select-sm" id="conc-col-ingreso"></select>
                </div>
                <div id="conc-wrap-egreso" style="display:none">
                    <label>Columna Egresos / Débitos</label>
                    <select class="form-select form-select-sm" id="conc-col-egreso"></select>
                </div>
            </div>

            <button class="btn btn-facturarg-main" id="conc-btn-importar">
                <i class="fas fa-check me-2"></i> Importar movimientos
            </button>
            <span class="ms-3 small text-muted" id="conc-importar-status"></span>
        </div>
    </div>

    {{-- Paso 3: listado de importados --}}
    <div class="card-facturarg">
        <div class="card-header"><i class="fas fa-list-check me-2"></i> Movimientos del extracto</div>
        <div class="card-body p-4">
            <div class="conc-tabs">
                <button class="conc-tab active" data-estado="pendiente">Pendientes</button>
                <button class="conc-tab" data-estado="conciliado">Conciliados</button>
                <button class="conc-tab" data-estado="descartado">Descartados</button>
                <button class="conc-tab" data-estado="todos">Todos</button>
            </div>
            <div class="table-responsive">
                <table class="conc-table table mb-0" id="conc-tabla-importados">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Referencia</th>
                            <th>Tipo</th>
                            <th class="text-end">Monto</th>
                            <th>Estado / sugerencia</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="conc-tabla-body">
                        <tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal: buscar movimiento manualmente --}}
<div class="modal fade" id="modalBuscarMovimiento" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title">Vincular movimiento interno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" class="form-control mb-3" id="conc-buscar-input" placeholder="Buscar por comprobante, cliente/proveedor o monto...">
        <div id="conc-buscar-resultados" style="max-height:360px; overflow-y:auto"></div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
    window.CONCILIACION_CONFIG = {
        cuentaId: {{ $cuenta->id }},
        urls: {
            previsualizar: "{{ route('cuentas.conciliacion.previsualizar', $cuenta->id) }}",
            importar: "{{ route('cuentas.conciliacion.importar', $cuenta->id) }}",
            data: "{{ route('cuentas.conciliacion.data', $cuenta->id) }}",
            buscar: "{{ route('cuentas.conciliacion.buscar', $cuenta->id) }}",
            conciliar: "{{ url('cuentas/'.$cuenta->id.'/conciliacion') }}",
        },
        csrf: "{{ csrf_token() }}",
    };
</script>
<script src="{{ asset('js/funciones_cuenta/conciliacion.js') }}?v={{ filemtime(public_path('js/funciones_cuenta/conciliacion.js')) }}"></script>
@endsection
