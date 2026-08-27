{{--
Panel de adjuntos embebible (remitos, comprobantes, cualquier archivo).
No tiene borrado a propósito: lo que se sube queda.
- Uso estático: @include('adjuntos._panel', ['tipo' => 'venta', 'id' => $venta->idventa])
- Uso dinámico (modal compartido): pasar un $panelId fijo y 'id' => null, y
  desde el JS que carga el modal llamar adjuntosPanelSetEntidad('<panelId>', 'compra', idcompra).
--}}
@php
    $tipo = $tipo ?? null;
    $id = $id ?? null;
    $adjPanelId = $panelId ?? ('adjuntosPanel_' . $tipo . '_' . $id);
@endphp
@can('haveaccess', 'adjuntos.index')
<div class="adjuntos-panel" id="{{ $adjPanelId }}" data-tipo="{{ $tipo }}" data-id="{{ $id }}">
    <style>
        #{{ $adjPanelId }} .ap-items { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
        #{{ $adjPanelId }} .ap-item { width:84px; text-align:center; font-size:.68rem; color:#475569; }
        #{{ $adjPanelId }} .ap-item img { width:70px; height:70px; object-fit:cover; border-radius:8px; border:1px solid #E2E8F0; }
        #{{ $adjPanelId }} .ap-item .ap-filebox { width:70px; height:70px; border-radius:8px; border:1px solid #E2E8F0; background:#F8FAFC; display:flex; align-items:center; justify-content:center; }
        #{{ $adjPanelId }} .ap-item .ap-filebox i { font-size:1.4rem; color:#94A3B8; }
        #{{ $adjPanelId }} .ap-item a { color:inherit; text-decoration:none; display:block; }
        #{{ $adjPanelId }} .ap-item .ap-nombre { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-top:2px; }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0"><i class="fas fa-paperclip me-1" style="color:#2563EB;"></i> Adjuntos</h6>
        <label class="btn btn-sm btn-outline-dark mb-0 ap-upload-btn" style="cursor:pointer;">
            <i class="fas fa-upload"></i> Subir
            <input type="file" class="ap-input d-none">
        </label>
    </div>
    <div class="ap-items"><p class="text-muted small mb-0">Sin adjuntos todavía.</p></div>
</div>
@once
<script src="{{ asset('js/funciones_adjuntos/adjuntos-panel.js') }}?v={{ filemtime(public_path('js/funciones_adjuntos/adjuntos-panel.js')) }}"></script>
@endonce
<script>document.addEventListener('DOMContentLoaded', () => initAdjuntosPanel('{{ $adjPanelId }}'));</script>
@endcan
