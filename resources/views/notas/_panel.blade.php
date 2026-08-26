{{--
Panel de notas embebible.
- Uso estático (la página ya sabe el id al renderizar, ej. ficha de proveedor):
    @include('notas._panel', ['tipo' => 'proveedor', 'id' => $proveedor->idproveedor])
- Uso dinámico (un modal compartido que se rellena por JS con distintos ids,
  ej. detalle de venta/compra): pasar un $panelId fijo y 'id' => null, y desde
  el JS que carga el modal llamar notasPanelSetEntidad('<panelId>', 'venta', idventa).
--}}
@php
    $tipo = $tipo ?? null;
    $id = $id ?? null;
    $notaPanelId = $panelId ?? ('notasPanel_' . $tipo . '_' . $id);
@endphp
@can('haveaccess', 'notas.index')
<div class="notas-panel" id="{{ $notaPanelId }}" data-tipo="{{ $tipo }}" data-id="{{ $id }}">
    <style>
        #{{ $notaPanelId }} .np-nota { background:#FFFBEB; border:1px solid #FDE68A; border-radius:10px; padding:10px 12px; margin-bottom:8px; position:relative; font-size:.82rem; }
        #{{ $notaPanelId }} .np-nota.completada { background:#F1F5F9; border-color:#E2E8F0; opacity:.7; text-decoration: line-through; }
        #{{ $notaPanelId }} .np-nota.vencida { background:#FEF2F2; border-color:#FCA5A5; }
        #{{ $notaPanelId }} .np-meta { font-size:.7rem; color:#64748b; margin-top:4px; }
        #{{ $notaPanelId }} .np-acciones { position:absolute; top:6px; right:8px; display:flex; gap:6px; }
        #{{ $notaPanelId }} .np-acciones button { border:none; background:transparent; color:#94a3b8; cursor:pointer; font-size:.8rem; }
        #{{ $notaPanelId }} .np-acciones button:hover { color:#334155; }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0"><i class="fas fa-sticky-note me-1" style="color:#F59E0B;"></i> Notas</h6>
        <button type="button" class="btn btn-sm btn-outline-dark np-toggle-form"><i class="fas fa-plus"></i> Nota</button>
    </div>
    <form class="np-form mb-2" style="display:none;">
        <textarea class="form-control form-control-sm mb-1 np-contenido" rows="2" placeholder="Escribí la nota..." required></textarea>
        <div class="d-flex gap-2">
            <input type="date" class="form-control form-control-sm np-fecha" placeholder="Recordarme el (opcional)">
            <button type="submit" class="btn btn-sm btn-dark">Guardar</button>
        </div>
    </form>
    <div class="np-lista"><p class="text-muted small">Sin notas todavía.</p></div>
</div>
@once
<script src="{{ asset('js/funciones_notas/notas-panel.js') }}?v={{ filemtime(public_path('js/funciones_notas/notas-panel.js')) }}"></script>
@endonce
<script>document.addEventListener('DOMContentLoaded', () => initNotasPanel('{{ $notaPanelId }}'));</script>
@endcan
