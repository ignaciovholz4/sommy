@extends('layouts.admin')

@section('contenido')
<style>
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-cyan: #1591a3;
        --facturarg-bg: #f1f5f9;
    }
    .terceros-container { background-color: var(--facturarg-bg); min-height: 100vh; padding: 2rem; }
    .terceros-card { background: #fff; border-radius: 14px; padding: 1.25rem 1.5rem; box-shadow: 0 4px 10px rgba(15,23,42,.06); }
    .terceros-kpi { background: #fff; border-radius: 14px; padding: 1rem 1.25rem; box-shadow: 0 4px 10px rgba(15,23,42,.06); }
    .terceros-kpi .kpi-label { font-size: .75rem; font-weight: 700; letter-spacing: .04em; color: #64748b; text-transform: uppercase; }
    .terceros-kpi .kpi-valor { font-size: 1.4rem; font-weight: 800; color: var(--facturarg-dark); }
    .tabla-terceros th { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
    .tabla-terceros tbody tr { cursor: pointer; }
    .tabla-terceros tbody tr:hover { background: #f8fafc; }
    .alias-pill { background: #e0f2fe; color: #0369a1; border-radius: 999px; padding: .15rem .6rem; font-weight: 700; font-size: .85rem; }
</style>

<div class="terceros-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:var(--facturarg-dark)"><i class="fas fa-user-friends me-2"></i>Control de terceros</h2>
            <p class="text-muted small mb-0">Cuánta plata fue a cada alias/CUIT. El alias se registra en cada cobro.</p>
        </div>
        <a href="{{ route('cuentas.index') }}" class="btn btn-outline-dark fw-bold">
            <i class="fas fa-arrow-left me-2"></i> VOLVER A CUENTAS
        </a>
    </div>

    <div class="terceros-card mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">ALIAS</label>
                <input type="search" id="f-alias" class="form-control" placeholder="Ej: juan.perez.mp">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">CUIT</label>
                <input type="search" id="f-cuit" class="form-control" placeholder="Con o sin guiones">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">DESDE</label>
                <input type="date" id="f-desde" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">HASTA</label>
                <input type="date" id="f-hasta" class="form-control">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button id="f-buscar" class="btn btn-dark fw-bold flex-grow-1"><i class="fas fa-search me-1"></i> Filtrar</button>
                <button id="f-limpiar" class="btn btn-outline-secondary" title="Limpiar filtros"><i class="fas fa-eraser"></i></button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="terceros-kpi"><div class="kpi-label">Entró (ingresos)</div><div class="kpi-valor text-success" id="kpi-ingresos">—</div></div></div>
        <div class="col-6 col-md-3"><div class="terceros-kpi"><div class="kpi-label">Salió (egresos)</div><div class="kpi-valor text-danger" id="kpi-egresos">—</div></div></div>
        <div class="col-6 col-md-3"><div class="terceros-kpi"><div class="kpi-label">En manos de terceros</div><div class="kpi-valor" id="kpi-neto">—</div></div></div>
        <div class="col-6 col-md-3"><div class="terceros-kpi"><div class="kpi-label">Alias distintos</div><div class="kpi-valor" id="kpi-aliases">—</div></div></div>
    </div>

    <div class="terceros-card">
        <div class="table-responsive">
            <table class="table align-middle tabla-terceros mb-0">
                <thead>
                    <tr>
                        <th>Alias</th>
                        <th>CUIT</th>
                        <th class="text-end">Entró</th>
                        <th class="text-end">Salió</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Movs.</th>
                        <th>Último</th>
                    </tr>
                </thead>
                <tbody id="tabla-body">
                    <tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="text-muted small mt-2"><i class="fas fa-info-circle me-1"></i> Hacé click en un alias para ver todos sus movimientos.</div>
    </div>
</div>

{{-- Modal detalle de movimientos de un alias --}}
<div class="modal fade" id="modalDetalleAlias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header modal-header-dark" style="background:var(--facturarg-dark);color:#fff;">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-list me-2"></i>Movimientos de <span id="det-alias"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Fecha</th><th>Tipo</th><th>Medio</th><th>Cliente/Prov.</th>
                                <th>Operación</th><th>Observaciones</th><th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody id="det-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const fmt = n => '$' + Number(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function cargar() {
        const params = new URLSearchParams();
        const alias = document.getElementById('f-alias').value.trim();
        const cuit = document.getElementById('f-cuit').value.trim();
        const desde = document.getElementById('f-desde').value;
        const hasta = document.getElementById('f-hasta').value;
        if (alias) params.set('alias', alias);
        if (cuit) params.set('cuit', cuit);
        if (desde) params.set('desde', desde);
        if (hasta) params.set('hasta', hasta);

        fetch('{{ route('cuentas.terceros.data') }}?' + params.toString())
            .then(r => r.json())
            .then(d => {
                document.getElementById('kpi-ingresos').textContent = fmt(d.totales.ingresos);
                document.getElementById('kpi-egresos').textContent = fmt(d.totales.egresos);
                document.getElementById('kpi-neto').textContent = fmt(d.totales.neto);
                document.getElementById('kpi-aliases').textContent = d.totales.aliases;

                const body = document.getElementById('tabla-body');
                body.innerHTML = '';
                if (!d.resumen.length) {
                    body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Sin movimientos a terceros con esos filtros.<br><small>Se registran al cobrar eligiendo una cuenta de terceros e indicando el alias.</small></td></tr>';
                    return;
                }
                d.resumen.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td><span class="alias-pill"></span></td>' +
                        '<td class="text-muted"></td>' +
                        '<td class="text-end text-success fw-bold">' + fmt(r.ingresos) + '</td>' +
                        '<td class="text-end text-danger fw-bold">' + fmt(r.egresos) + '</td>' +
                        '<td class="text-end fw-bold">' + fmt(r.neto) + '</td>' +
                        '<td class="text-center">' + r.cantidad + '</td>' +
                        '<td class="text-muted small">' + (r.ultimo ? r.ultimo.substring(0, 10).split('-').reverse().join('/') : '—') + '</td>';
                    tr.querySelector('.alias-pill').textContent = r.alias;
                    tr.children[1].textContent = r.cuit || '—';
                    tr.addEventListener('click', () => verDetalle(r.alias));
                    body.appendChild(tr);
                });
            })
            .catch(() => {
                document.getElementById('tabla-body').innerHTML =
                    '<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar los datos.</td></tr>';
            });
    }

    function verDetalle(alias) {
        document.getElementById('det-alias').textContent = alias;
        const body = document.getElementById('det-body');
        body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Cargando...</td></tr>';
        new bootstrap.Modal(document.getElementById('modalDetalleAlias')).show();

        fetch('{{ route('cuentas.terceros.movimientos') }}?alias=' + encodeURIComponent(alias))
            .then(r => r.json())
            .then(d => {
                body.innerHTML = '';
                d.movimientos.forEach(m => {
                    const esIngreso = m.tipo === 'ingreso';
                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td class="small">' + (m.fecha ? m.fecha.substring(0, 16).replace('T', ' ') : '—') + '</td>' +
                        '<td><span class="badge ' + (esIngreso ? 'bg-success' : 'bg-danger') + '">' + m.tipo + '</span></td>' +
                        '<td class="small"></td><td class="small"></td><td class="small fw-bold"></td><td class="small text-muted"></td>' +
                        '<td class="text-end fw-bold ' + (esIngreso ? 'text-success' : 'text-danger') + '">' + (esIngreso ? '+' : '−') + fmt(m.total) + '</td>';
                    tr.children[2].textContent = m.medio ? m.medio.replace('_', ' ') : '—';
                    tr.children[3].textContent = m.cliente_proveedor || '—';
                    tr.children[4].textContent = m.comprobante || '—';
                    tr.children[5].textContent = m.observaciones || '';
                    body.appendChild(tr);
                });
                if (!d.movimientos.length) {
                    body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Sin movimientos.</td></tr>';
                }
            });
    }

    document.getElementById('f-buscar').addEventListener('click', cargar);
    document.getElementById('f-limpiar').addEventListener('click', () => {
        ['f-alias', 'f-cuit', 'f-desde', 'f-hasta'].forEach(id => document.getElementById(id).value = '');
        cargar();
    });
    ['f-alias', 'f-cuit'].forEach(id => document.getElementById(id).addEventListener('keyup', e => {
        if (e.key === 'Enter') cargar();
    }));

    cargar();
})();
</script>
@endsection
