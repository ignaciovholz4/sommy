@extends('layouts.admin')

@section('contenido')
<style>
    :root { --facturarg-dark: #0f172a; --facturarg-bg: #f1f5f9; }
    .main-container { background-color: var(--facturarg-bg); min-height: 100vh; padding: 3rem 2.5rem; }
    .card-facturarg { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); background: #fff; overflow: hidden; }
    .card-header-facturarg { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 2rem 2.5rem; }
    .table-facturarg thead th { background-color: var(--facturarg-dark); color: white; text-transform: uppercase; font-size: .7rem; font-weight: 800; letter-spacing: 1px; padding: 1rem 1.25rem !important; border: none; }
    .table-facturarg tbody td { padding: 1rem 1.25rem !important; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: .85rem; }
    .style-input { background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; padding: 8px 12px !important; font-weight: 600; font-size: .85rem; }
    .metodo-chip { font-size: .68rem; font-weight: 800; padding: 3px 9px; border-radius: 999px; }
    .metodo-post { background:#DBEAFE; color:#1D4ED8; }
    .metodo-put, .metodo-patch { background:#FEF3C7; color:#92400E; }
    .metodo-delete { background:#FEE2E2; color:#B91C1C; }
    .status-ok { color:#15803D; font-weight:700; }
    .status-error { color:#B91C1C; font-weight:700; }
</style>

<div class="main-container">
    <div class="card-facturarg mb-3">
        <div class="card-header-facturarg">
            <h3 class="fw-bold text-dark m-0"><i class="fas fa-user-secret me-2"></i> Auditoría</h3>
            <p class="text-muted small m-0 mt-1">Quién hizo qué, cuándo y desde dónde — toda acción que modifica datos en el sistema.</p>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1">Usuario</label>
                    <select id="filtro_user_id" class="form-select style-input">
                        <option value="">Todos</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1">Ruta contiene</label>
                    <input type="text" id="filtro_ruta" class="form-control style-input" placeholder="ej: ventas, cheques, user">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted mb-1">Desde</label>
                    <input type="date" id="filtro_desde" class="form-control style-input">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted mb-1">Hasta</label>
                    <input type="date" id="filtro_hasta" class="form-control style-input">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-dark w-100 fw-bold" style="border-radius:10px" onclick="cargarAuditoria(1)"><i class="fas fa-filter me-1"></i> Filtrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-facturarg">
        <div class="table-responsive">
            <table class="table table-facturarg mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Método</th>
                        <th>Ruta</th>
                        <th>IP</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="auditoria_body">
                    <tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="p-3 d-flex justify-content-between align-items-center border-top">
            <span class="small text-muted" id="auditoria_resumen"></span>
            <div>
                <button class="btn btn-sm btn-outline-secondary" id="btnPagAnterior"><i class="fas fa-chevron-left"></i></button>
                <button class="btn btn-sm btn-outline-secondary" id="btnPagSiguiente"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>

{{-- Modal detalle de payload --}}
<div class="modal fade" id="modalAuditoriaDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de la acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>URL:</strong> <span id="detalle_url"></span></p>
                <pre id="detalle_payload" style="background:#f8fafc;border-radius:10px;padding:14px;max-height:400px;overflow:auto;font-size:.8rem;"></pre>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
let auditoriaPagina = 1;
let auditoriaUltimaPagina = 1;

function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

function metodoChip(m) {
    const cls = { POST: 'metodo-post', PUT: 'metodo-put', PATCH: 'metodo-patch', DELETE: 'metodo-delete' }[m] || 'metodo-post';
    return `<span class="metodo-chip ${cls}">${m}</span>`;
}

function cargarAuditoria(pagina) {
    auditoriaPagina = pagina || 1;
    $('#auditoria_body').html('<tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>');

    const params = new URLSearchParams({
        page: auditoriaPagina,
        user_id: $('#filtro_user_id').val(),
        ruta: $('#filtro_ruta').val(),
        desde: $('#filtro_desde').val(),
        hasta: $('#filtro_hasta').val(),
    });

    fetch(`{{ route('auditoria.data') }}?${params}`)
        .then(res => res.json())
        .then(data => {
            if (!data.estado || !data.data.length) {
                $('#auditoria_body').html('<tr><td colspan="7" class="text-center text-muted py-4">Sin resultados.</td></tr>');
                $('#auditoria_resumen').text('');
                return;
            }

            $('#auditoria_body').html(data.data.map((l, i) => `
                <tr>
                    <td>${esc(l.fecha)}</td>
                    <td>${esc(l.usuario)}</td>
                    <td>${metodoChip(l.metodo)}</td>
                    <td>${esc(l.ruta)}</td>
                    <td>${esc(l.ip)}</td>
                    <td class="${l.status && l.status < 400 ? 'status-ok' : 'status-error'}">${l.status ?? '—'}</td>
                    <td><button class="btn btn-sm btn-outline-secondary" onclick='verDetalle(${JSON.stringify(l.url)}, ${JSON.stringify(l.payload)})'><i class="fas fa-eye"></i></button></td>
                </tr>
            `).join(''));

            auditoriaUltimaPagina = data.paginacion.ultima_pagina;
            $('#auditoria_resumen').text(`${data.paginacion.total} registro(s) · página ${data.paginacion.pagina} de ${data.paginacion.ultima_pagina}`);
        });
}

function verDetalle(url, payload) {
    $('#detalle_url').text(url);
    $('#detalle_payload').text(JSON.stringify(payload, null, 2));
    new bootstrap.Modal(document.getElementById('modalAuditoriaDetalle')).show();
}

$(document).ready(function () {
    $('#btnPagAnterior').on('click', () => { if (auditoriaPagina > 1) cargarAuditoria(auditoriaPagina - 1); });
    $('#btnPagSiguiente').on('click', () => { if (auditoriaPagina < auditoriaUltimaPagina) cargarAuditoria(auditoriaPagina + 1); });
    cargarAuditoria(1);
});
</script>
@endsection
@endsection
