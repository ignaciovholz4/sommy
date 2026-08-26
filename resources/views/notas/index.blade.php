@extends('layouts.admin')

@section('contenido')
<style>
    :root { --facturarg-dark: #0f172a; --facturarg-bg: #f1f5f9; }
    .main-container { background-color: var(--facturarg-bg); min-height: 100vh; padding: 3rem 2.5rem; }
    .card-facturarg { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); background: #fff; overflow: hidden; }
    .card-header-facturarg { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 1.5rem 2rem; }
    .nota-card { background:#FFFBEB; border:1px solid #FDE68A; border-radius:14px; padding:14px 16px; margin-bottom:12px; position:relative; }
    .nota-card.completada { background:#F1F5F9; border-color:#E2E8F0; opacity:.7; }
    .nota-card.vencida { background:#FEF2F2; border-color:#FCA5A5; }
    .nota-contenido { white-space: pre-wrap; font-size:.88rem; color:#1e293b; margin-bottom:8px; }
    .nota-meta { font-size:.72rem; color:#64748b; }
    .nota-fecha-badge { font-size:.68rem; font-weight:800; padding:3px 9px; border-radius:999px; background:#FEF3C7; color:#92400E; }
    .nota-fecha-badge.vencida { background:#FEE2E2; color:#B91C1C; }
    .nota-acciones { position:absolute; top:10px; right:10px; display:flex; gap:6px; }
    .nota-acciones button { border:none; background:transparent; color:#94a3b8; cursor:pointer; font-size:.85rem; }
    .nota-acciones button:hover { color:#334155; }
</style>

<div class="main-container">
    <div class="card-facturarg mb-3">
        <div class="card-header-facturarg">
            <h3 class="fw-bold text-dark m-0"><i class="fas fa-sticky-note me-2" style="color:#F59E0B;"></i> Notas recordatorias</h3>
            <p class="text-muted small m-0 mt-1">Notas sueltas para no olvidarte de nada. También podés dejar una nota pegada a un cliente, proveedor, venta o compra puntual desde su propia pantalla.</p>
        </div>
        <div class="p-3" style="border-top:1px solid #f1f5f9;">
            <form id="formNuevaNotaGeneral" class="row g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label small fw-bold">Nota</label>
                    <textarea name="contenido" class="form-control" rows="2" placeholder="Ej: llamar al proveedor X por la demora del pedido" required></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Recordarme el (opcional)</label>
                    <input type="date" name="fecha_recordatorio" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100"><i class="fas fa-plus"></i> Agregar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card-facturarg p-3 mb-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-bell me-1" style="color:#B91C1C;"></i> Con fecha, pendientes ({{ $pendientesConFecha->count() }})</h6>
                @forelse($pendientesConFecha as $n)
                    <div class="nota-card {{ $n->vencida ? 'vencida' : '' }}" data-id="{{ $n->id }}">
                        <div class="nota-acciones">
                            <button type="button" class="btn-completar" title="Marcar como hecha"><i class="fas fa-check"></i></button>
                            <button type="button" class="btn-borrar" title="Borrar"><i class="fas fa-trash-alt"></i></button>
                        </div>
                        <div class="nota-contenido">{{ $n->contenido }}</div>
                        @if($n->etiqueta_entidad)
                            <div class="nota-meta mb-1"><i class="fas fa-link"></i> {{ ucfirst($n->notable_type) }}: {{ $n->etiqueta_entidad }}</div>
                        @endif
                        <span class="nota-fecha-badge {{ $n->vencida ? 'vencida' : '' }}">
                            {{ $n->vencida ? 'Vencida' : 'Para el' }} {{ $n->fecha_recordatorio->format('d/m/Y') }}
                        </span>
                    </div>
                @empty
                    <p class="text-muted small">No hay notas con fecha pendiente.</p>
                @endforelse
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card-facturarg p-3 mb-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-list me-1"></i> Tablero general ({{ $generales->count() }})</h6>
                <div id="notasGenerales">
                    @forelse($generales as $n)
                        <div class="nota-card {{ $n->completada ? 'completada' : '' }}" data-id="{{ $n->id }}">
                            <div class="nota-acciones">
                                <button type="button" class="btn-completar" title="{{ $n->completada ? 'Marcar como pendiente' : 'Marcar como hecha' }}"><i class="fas fa-check"></i></button>
                                <button type="button" class="btn-borrar" title="Borrar"><i class="fas fa-trash-alt"></i></button>
                            </div>
                            <div class="nota-contenido">{{ $n->contenido }}</div>
                            <div class="nota-meta">
                                {{ optional($n->usuario)->name ?? 'Sistema' }} · {{ $n->created_at->format('d/m/Y H:i') }}
                                @if($n->fecha_recordatorio) · <span class="nota-fecha-badge {{ $n->vencida ? 'vencida' : '' }}">{{ $n->fecha_recordatorio->format('d/m/Y') }}</span> @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small">Todavía no hay notas generales.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.getElementById('formNuevaNotaGeneral').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fetch('{{ route("notas.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: fd
        })
        .then(r => r.json())
        .then(d => { if (d.estado === 1) location.reload(); else alert('No se pudo guardar la nota.'); })
        .catch(() => alert('Error al guardar la nota.'));
    });

    document.querySelectorAll('.btn-completar').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.closest('.nota-card').dataset.id;
            fetch(`/notas/${id}/completar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } })
                .then(r => r.json())
                .then(() => location.reload());
        });
    });

    document.querySelectorAll('.btn-borrar').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('¿Borrar esta nota?')) return;
            const id = this.closest('.nota-card').dataset.id;
            fetch(`/notas/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } })
                .then(r => r.json())
                .then(() => location.reload());
        });
    });
});
</script>
@endpush
@endsection
