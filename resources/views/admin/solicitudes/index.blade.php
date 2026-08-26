@extends('layouts.admin')

@section('contenido')
<style>
    :root { --facturarg-dark: #0f172a; --facturarg-bg: #f1f5f9; }
    .main-container { background-color: var(--facturarg-bg); min-height: 100vh; padding: 3rem 2.5rem; }
    .card-facturarg { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); background: #fff; overflow: hidden; }
    .card-header-facturarg { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 2rem 2.5rem; }
    .table-facturarg thead th { background-color: var(--facturarg-dark); color: white; text-transform: uppercase; font-size: .7rem; font-weight: 800; letter-spacing: 1px; padding: 1rem 1.25rem !important; border: none; }
    .table-facturarg tbody td { padding: 1rem 1.25rem !important; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: .85rem; }
    .sol-tabs { display:flex; gap:8px; margin-bottom:0; }
    .sol-tab { border:none; background:#e2e8f0; color:#475569; font-weight:700; font-size:.8rem; padding:8px 16px; border-radius:999px; cursor:pointer; }
    .sol-tab.active { background: var(--facturarg-dark); color:white; }
    .badge-tipo { font-size: .68rem; font-weight: 800; padding: 3px 9px; border-radius: 999px; background:#E0F2FE; color:#1D4ED8; text-transform: uppercase; }
    .badge-estado-pendiente { background:#FEF3C7; color:#92400E; }
    .badge-estado-aprobada { background:#DCFCE7; color:#15803D; }
    .badge-estado-rechazada { background:#FEE2E2; color:#B91C1C; }
    .badge-estado { font-size:.7rem; font-weight:800; padding:4px 10px; border-radius:999px; }
</style>

<div class="main-container">
    <div class="card-facturarg mb-3">
        <div class="card-header-facturarg d-flex justify-content-between align-items-center flex-wrap" style="gap:12px">
            <div>
                <h3 class="fw-bold text-dark m-0"><i class="fas fa-hand-paper me-2"></i> Solicitudes de aprobación</h3>
                <p class="text-muted small m-0 mt-1">Anulaciones de ventas/compras, cheques rechazados/anulados, y compra/venta de divisas hechas por usuarios que no son administradores.</p>
            </div>
            <div class="sol-tabs">
                <button class="sol-tab active" data-estado="pendiente">Pendientes</button>
                <button class="sol-tab" data-estado="aprobada">Aprobadas</button>
                <button class="sol-tab" data-estado="rechazada">Rechazadas</button>
                <button class="sol-tab" data-estado="todos">Todas</button>
            </div>
        </div>
    </div>

    <div class="card-facturarg">
        <div class="table-responsive">
            <table class="table table-facturarg mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Solicitante</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="solicitudes_body">
                    <tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal rechazar --}}
<div class="modal fade" id="modalRechazarSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formRechazarSolicitud">
                <div class="modal-header">
                    <h5 class="modal-title">Rechazar solicitud</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rechazar_solicitud_id">
                    <div class="mb-3">
                        <label class="form-label">Motivo (opcional)</label>
                        <input type="text" id="rechazar_solicitud_motivo" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let solicitudEstadoActual = 'pendiente';

function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

function cargarSolicitudes() {
    $('#solicitudes_body').html('<tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr>');

    fetch(`{{ url('admin/solicitudes/data') }}?estado=${solicitudEstadoActual}`)
        .then(res => res.json())
        .then(data => {
            if (!data.estado || !data.data.length) {
                $('#solicitudes_body').html('<tr><td colspan="6" class="text-center text-muted py-4">No hay solicitudes en esta vista.</td></tr>');
                return;
            }
            $('#solicitudes_body').html(data.data.map(filaSolicitud).join(''));
        });
}

function filaSolicitud(s) {
    let acciones = '';
    if (s.estado === 'pendiente') {
        acciones = `
            <button class="btn btn-sm btn-success" onclick="aprobarSolicitud(${s.id})"><i class="fas fa-check"></i> Aprobar</button>
            <button class="btn btn-sm btn-outline-danger" onclick="abrirModalRechazar(${s.id})"><i class="fas fa-times"></i> Rechazar</button>
        `;
    } else if (s.estado === 'rechazada' && s.motivo_rechazo) {
        acciones = `<span class="text-muted small">${esc(s.motivo_rechazo)}</span>`;
    } else if (s.aprobado_por) {
        acciones = `<span class="text-muted small">${s.estado === 'aprobada' ? 'Aprobada' : 'Resuelta'} por ${esc(s.aprobado_por)}</span>`;
    }

    const linkOrigen = s.origen_url ? ` <a href="${s.origen_url}" class="small" target="_blank">ver →</a>` : '';

    return `<tr>
        <td>${esc(s.fecha)}</td>
        <td><span class="badge-tipo">${esc(s.tipo)}</span></td>
        <td>${esc(s.descripcion)}${linkOrigen}</td>
        <td>${esc(s.solicitante)}</td>
        <td><span class="badge-estado badge-estado-${s.estado}">${esc(s.estado)}</span></td>
        <td class="text-end">${acciones}</td>
    </tr>`;
}

function aprobarSolicitud(id) {
    if (!confirm('¿Aprobar y ejecutar esta acción ahora?')) return;
    fetch(`{{ url('admin/solicitudes') }}/${id}/aprobar`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
    })
    .then(res => res.json())
    .then(data => {
        if (data.estado === 1) { toastr.success(data.mensaje); cargarSolicitudes(); }
        else { toastr.error(data.mensaje || 'No se pudo aprobar.'); }
    });
}

function abrirModalRechazar(id) {
    $('#rechazar_solicitud_id').val(id);
    $('#rechazar_solicitud_motivo').val('');
    new bootstrap.Modal(document.getElementById('modalRechazarSolicitud')).show();
}

$(document).ready(function () {
    $('.sol-tab').on('click', function () {
        $('.sol-tab').removeClass('active');
        $(this).addClass('active');
        solicitudEstadoActual = $(this).data('estado');
        cargarSolicitudes();
    });

    $('#formRechazarSolicitud').on('submit', function (e) {
        e.preventDefault();
        const id = $('#rechazar_solicitud_id').val();

        fetch(`{{ url('admin/solicitudes') }}/${id}/rechazar`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json' },
            body: JSON.stringify({ motivo: $('#rechazar_solicitud_motivo').val() })
        })
        .then(res => res.json())
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                bootstrap.Modal.getInstance(document.getElementById('modalRechazarSolicitud'))?.hide();
                cargarSolicitudes();
            } else {
                toastr.error(data.mensaje || 'No se pudo rechazar.');
            }
        });
    });

    cargarSolicitudes();
});
</script>
@endsection
