@extends('layouts.admin')

@section('title', 'WhatsApp')

@section('styles')
<style>
.wa-wrap { display:flex; height: calc(100vh - 220px); min-height: 480px; background:#fff; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; position:relative; }
.wa-col-list { width:320px; min-width:260px; border-right:1px solid #e5e7eb; display:flex; flex-direction:column; }
.wa-col-thread { flex:1; display:flex; flex-direction:column; background:#f6f4ef; min-width:0; }
.wa-col-info { width:300px; min-width:260px; border-left:1px solid #e5e7eb; overflow-y:auto; background:#fff; }
.wa-filters { padding:8px; border-bottom:1px solid #f1f5f9; }
.wa-filters .form-control, .wa-filters .custom-select { font-size:12.5px; }
.wa-conv-list { flex:1; overflow-y:auto; }
.wa-conv-item { padding:10px 12px; border-bottom:1px solid #f1f5f9; cursor:pointer; }
.wa-conv-item:hover { background:#f8fafc; }
.wa-conv-item.active { background:#E0F2FE; }
.wa-conv-name { font-weight:600; font-size:13.5px; color:#1e293b; display:flex; justify-content:space-between; }
.wa-conv-prev { font-size:12px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.wa-conv-meta { display:flex; gap:4px; align-items:center; margin-top:3px; flex-wrap:wrap; }
.wa-badge { font-size:10px; padding:2px 7px; border-radius:99px; color:#fff; }
.wa-unread { background:#1EBE5A; color:#fff; border-radius:99px; font-size:11px; min-width:19px; height:19px; display:inline-flex; align-items:center; justify-content:center; padding:0 5px; font-weight:700; }
.wa-thread-header { background:#fff; padding:8px 14px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.wa-thread-msgs { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:6px; }
.wa-msg { max-width:70%; padding:7px 11px; border-radius:10px; font-size:13.5px; white-space:pre-wrap; word-wrap:break-word; box-shadow:0 1px 1px rgba(0,0,0,.06); }
.wa-msg.in { background:#fff; align-self:flex-start; border-top-left-radius:2px; }
.wa-msg.out { background:#DCF8C6; align-self:flex-end; border-top-right-radius:2px; }
.wa-msg.note { background:#FEF9C3; align-self:center; max-width:85%; font-size:12.5px; color:#713F12; border:1px dashed #FACC15; }
.wa-msg .meta { font-size:10.5px; color:#94a3b8; margin-top:3px; text-align:right; }
.wa-msg .sender { font-size:10.5px; color:#2563EB; font-weight:600; }
.wa-msg img { max-width:230px; border-radius:8px; display:block; margin-bottom:4px; }
.wa-composer { background:#fff; border-top:1px solid #e5e7eb; padding:10px 14px; }
.wa-session-closed { background:#FEF2F2; color:#991B1B; font-size:12.5px; padding:6px 10px; border-radius:8px; margin-bottom:8px; }
.wa-info-block { padding:12px 14px; border-bottom:1px solid #f1f5f9; }
.wa-info-block h6 { font-size:11px; text-transform:uppercase; letter-spacing:.6px; color:#94a3b8; font-weight:700; margin-bottom:8px; }
.wa-empty { flex:1; display:flex; align-items:center; justify-content:center; color:#94a3b8; flex-direction:column; gap:8px; }
.wa-mode-pill { font-size:11px; border-radius:99px; padding:3px 10px; font-weight:600; cursor:pointer; border:1px solid transparent; }
.wa-mode-bot { background:#EDE9FE; color:#5B21B6; }
.wa-mode-humano { background:#DBEAFE; color:#1D4ED8; }
.wa-draft-card { border:1px solid #FDE68A; background:#FFFBEB; border-radius:8px; padding:8px 10px; font-size:12.5px; margin-bottom:8px; }
.wa-back { display:none; border:none; background:transparent; padding:2px 10px 2px 0; font-size:17px; color:#1e293b; }
.wa-info-toggle { display:none; }
@media (max-width: 992px) {
    /* Ficha del cliente como panel deslizable sobre el hilo */
    .wa-col-info { display:none; position:absolute; top:0; right:0; bottom:0; width:85%; max-width:320px; z-index:20; box-shadow:-6px 0 16px rgba(0,0,0,.15); }
    .wa-wrap.show-info .wa-col-info { display:block; }
    .wa-info-toggle { display:inline-flex; }
}
@media (max-width: 768px) {
    /* Una sola vista por vez: lista O hilo */
    .wa-wrap { height: calc(100vh - 170px); height: calc(100dvh - 170px); min-height: 420px; }
    .wa-col-list { width:100%; min-width:0; border-right:none; }
    .wa-col-thread { display:none; }
    .wa-wrap.mobile-thread .wa-col-list { display:none; }
    .wa-wrap.mobile-thread .wa-col-thread { display:flex; }
    .wa-back { display:inline-flex; align-items:center; }
    .wa-msg { max-width:85%; }
    .wa-msg img { max-width:100%; }
    .wa-thread-msgs { padding:10px; }
    .wa-thread-header { padding:8px 10px; gap:6px; }
    /* 16px evita el zoom automático de iOS al enfocar inputs */
    #f-q, #composer-text { font-size:16px; }
}
</style>
@endsection

@section('contenido')
<div class="d-flex justify-content-between align-items-center py-2 flex-wrap">
    <h4 class="mb-0">
        <i class="fab fa-whatsapp text-success"></i><i class="fab fa-facebook-messenger" style="color:#1877F2"></i><i class="fab fa-instagram" style="color:#C13584"></i>
        Bandeja de atención
    </h4>
    <div class="d-flex align-items-center" style="gap:8px">
        <button id="btn-drafts" class="btn btn-sm btn-warning position-relative" style="display:none">
            <i class="fas fa-clipboard-check"></i> Pedidos por confirmar <span id="drafts-count" class="badge badge-dark">0</span>
        </button>
        <button id="btn-conectar-wa" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i> Conectar WhatsApp</button>
        <a href="{{ route('whatsapp.board') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-columns"></i> Tablero</a>
        @can('haveaccess','agents.index')
        <a href="{{ route('whatsapp.agents.index') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-robot"></i> Agentes IA</a>
        @endcan
        @can('haveaccess','whatsapp.templates')
        @endcan
    </div>
</div>

<div class="wa-wrap">
    {{-- Columna 1: lista de conversaciones --}}
    <div class="wa-col-list">
        <div class="wa-filters">
            <input type="text" id="f-q" class="form-control form-control-sm mb-1" placeholder="Buscar nombre o teléfono...">
            <select id="f-channel" class="custom-select custom-select-sm mb-1">
                <option value="">Todos los canales</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="messenger">Facebook (Messenger)</option>
                <option value="instagram">Instagram</option>
            </select>
            <div class="d-flex" style="gap:4px">
                <select id="f-status" class="custom-select custom-select-sm">
                    <option value="">Abiertas</option>
                    <option value="nueva">Nuevas</option>
                    <option value="en_atencion">En atención</option>
                    <option value="esperando_cliente">Esperando cliente</option>
                    <option value="cerrada">Cerradas</option>
                </select>
                <select id="f-assigned" class="custom-select custom-select-sm">
                    <option value="">Todos</option>
                    <option value="me">Mías</option>
                    <option value="none">Sin asignar</option>
                    @foreach($vendedores as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="wa-conv-list" id="conv-list">
            <div class="text-center text-muted p-4"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    </div>

    {{-- Columna 2: hilo --}}
    <div class="wa-col-thread" id="thread-col">
        <div class="wa-empty" id="thread-empty">
            <i class="fab fa-whatsapp fa-3x"></i>
            <div>Elegí una conversación para empezar</div>
        </div>
        <div id="thread-content" style="display:none; flex:1; flex-direction:column; min-height:0;">
            <div class="wa-thread-header">
                <button class="wa-back" id="btn-back" title="Volver a la lista"><i class="fas fa-arrow-left"></i></button>
                <div style="flex:1; min-width:150px">
                    <strong id="th-name"></strong>
                    <div class="text-muted" style="font-size:12px" id="th-phone"></div>
                </div>
                <span id="th-mode" class="wa-mode-pill wa-mode-bot" title="Clic para alternar bot/humano">🤖 Bot</span>
                <select id="th-status" class="custom-select custom-select-sm" style="width:auto">
                    <option value="nueva">Nueva</option>
                    <option value="en_atencion">En atención</option>
                    <option value="esperando_cliente">Esperando cliente</option>
                    <option value="cerrada">Cerrada</option>
                </select>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" id="btn-assign-me">Asignarme</button>
                    @can('haveaccess','whatsapp.assign')
                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"></button>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($vendedores as $v)
                            <a class="dropdown-item assign-user" href="#" data-id="{{ $v->id }}">{{ $v->name }}</a>
                        @endforeach
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item assign-user" href="#" data-id="">Quitar asignación</a>
                    </div>
                    @endcan
                </div>
                <button class="btn btn-sm btn-outline-secondary wa-info-toggle" id="btn-info-toggle" title="Datos del cliente"><i class="fas fa-address-card"></i></button>
            </div>
            <div class="wa-thread-msgs" id="msgs"></div>
            <div class="wa-composer">
                <div id="session-closed" class="wa-session-closed" style="display:none">
                    ⏳ Pasaron más de 24 h del último mensaje del cliente: solo se pueden enviar <strong>plantillas aprobadas</strong>.
                </div>
                <div class="d-flex" style="gap:8px">
                    <div style="flex:1">
                        <textarea id="composer-text" class="form-control form-control-sm" rows="2" placeholder="Escribí un mensaje... (Enter envía, Shift+Enter salto de línea)"></textarea>
                        <div id="composer-template" style="display:none">
                            <select id="template-select" class="custom-select custom-select-sm mb-1">
                                <option value="">— Elegí una plantilla —</option>
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}" data-body="{{ $t->body_text }}">{{ $t->name }} ({{ $t->language }})</option>
                                @endforeach
                            </select>
                            <div id="template-params"></div>
                        </div>
                    </div>
                    <div class="d-flex flex-column" style="gap:4px">
                        <button id="btn-send" class="btn btn-success btn-sm"><i class="fas fa-paper-plane"></i></button>
                        <button id="btn-note" class="btn btn-outline-warning btn-sm" title="Nota interna (no se envía al cliente)"><i class="fas fa-sticky-note"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Columna 3: ficha del cliente --}}
    <div class="wa-col-info" id="info-col">
        <div class="wa-info-block" id="info-cliente">
            <h6>Cliente</h6>
            <div class="text-muted" style="font-size:13px">Sin conversación seleccionada</div>
        </div>
        <div class="wa-info-block" id="info-drafts" style="display:none">
            <h6>Pedido en curso</h6>
            <div id="draft-container"></div>
        </div>
        <div class="wa-info-block">
            <h6>Etiquetas</h6>
            <div id="tag-container" class="mb-2"></div>
            <div class="d-flex" style="gap:4px">
                <select id="tag-select" class="custom-select custom-select-sm">
                    <option value="">— etiqueta —</option>
                    @foreach($tags as $t)
                        <option value="{{ $t->id }}" data-color="{{ $t->color }}">{{ $t->nombre }}</option>
                    @endforeach
                </select>
                <button id="btn-tag" class="btn btn-sm btn-outline-secondary"><i class="fas fa-tag"></i></button>
            </div>
        </div>
    </div>
</div>

{{-- Modal vincular cliente --}}
<div class="modal fade" id="modal-link" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Vincular cliente</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label style="font-size:12.5px" class="mb-1">Buscar cliente existente</label>
                <input type="text" id="link-search" class="form-control form-control-sm mb-1" placeholder="Nombre, teléfono, email o DNI...">
                <div id="link-results" class="list-group mb-3"></div>
                <hr>
                <label style="font-size:12.5px" class="mb-1">O crear uno nuevo</label>
                <input type="text" id="link-nombre" class="form-control form-control-sm mb-1" placeholder="Nombre y apellido *">
                <input type="email" id="link-email" class="form-control form-control-sm mb-1" placeholder="Email (opcional)">
                <input type="text" id="link-direccion" class="form-control form-control-sm mb-2" placeholder="Dirección (opcional)">
                <button id="btn-create-cliente" class="btn btn-sm btn-primary btn-block">Crear y vincular</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal pedidos por confirmar --}}
<div class="modal fade" id="modal-drafts" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-clipboard-check"></i> Pedidos del bot por confirmar</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="drafts-body"></div>
        </div>
    </div>
</div>

{{-- Modal conectar WhatsApp (QR del bridge Baileys) --}}
<div class="modal fade" id="modal-conectar-wa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fab fa-whatsapp text-success"></i> Conectar WhatsApp</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center" style="min-height:340px;">
                <div id="wa-qr-estado" class="mb-2 text-muted">Consultando estado...</div>
                <img id="wa-qr-img" src="" alt="QR de WhatsApp" onerror="this.style.display='none'" style="display:none;width:280px;height:280px;border:1px solid #e5e7eb;border-radius:8px;">
                <div id="wa-qr-conectado" style="display:none;">
                    <i class="fas fa-check-circle text-success" style="font-size:64px;"></i>
                    <p class="mt-2 mb-0 font-weight-bold">¡WhatsApp conectado!</p>
                    <p class="text-muted small">Ya podés recibir y enviar mensajes desde el CRM.</p>
                </div>
                <div id="wa-qr-apagado" style="display:none;">
                    <i class="fas fa-plug text-danger" style="font-size:48px;"></i>
                    <p class="mt-2 mb-0 font-weight-bold">El bridge de WhatsApp no está corriendo</p>
                    <p class="text-muted small mb-0">Esperá un minuto (el vigilante lo levanta solo) y volvé a abrir este modal.</p>
                </div>
                <p class="text-muted small mt-3 mb-0">En tu teléfono: WhatsApp → Ajustes → <b>Dispositivos vinculados</b> → <b>Vincular un dispositivo</b> → escaneá el QR.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
window.WA_CONFIG = {
    base: "{{ url('whatsapp') }}",
    csrf: "{{ csrf_token() }}",
    userId: {{ auth()->id() }},
    canConfirm: {{ auth()->user()->can('haveaccess','whatsapp.orders.confirm') ? 'true' : 'false' }},
    canReject: {{ auth()->user()->can('haveaccess','whatsapp.orders.reject') ? 'true' : 'false' }}
};
</script>
<script src="{{ asset('js/whatsapp-inbox.js') }}?v={{ filemtime(public_path('js/whatsapp-inbox.js')) }}"></script>
<script>
// ── Conexión del número por QR (bridge Baileys) ──
(function () {
    let waQrTimer = null;

    function pollQr() {
        fetch("{{ route('whatsapp.bridge.estado') }}")
            .then(r => r.json())
            .then(d => {
                const img = document.getElementById('wa-qr-img');
                const estado = document.getElementById('wa-qr-estado');
                const ok = document.getElementById('wa-qr-conectado');
                const off = document.getElementById('wa-qr-apagado');
                img.style.display = 'none'; ok.style.display = 'none'; off.style.display = 'none';

                if (d.estado === 'conectado') {
                    estado.style.display = 'none';
                    ok.style.display = '';
                    clearInterval(waQrTimer); waQrTimer = null;
                } else if (d.estado === 'esperando_qr') {
                    estado.style.display = '';
                    estado.textContent = 'Escaneá el QR con tu teléfono (se renueva solo):';
                    img.src = "{{ route('whatsapp.bridge.qr') }}?ts=" + Date.now();
                    img.style.display = '';
                } else {
                    estado.style.display = 'none';
                    off.style.display = '';
                }
            })
            .catch(() => {});
    }

    document.getElementById('btn-conectar-wa').addEventListener('click', function () {
        $('#modal-conectar-wa').modal('show');
        pollQr();
        if (waQrTimer) clearInterval(waQrTimer);
        waQrTimer = setInterval(pollQr, 4000);
    });

    $('#modal-conectar-wa').on('hidden.bs.modal', function () {
        if (waQrTimer) { clearInterval(waQrTimer); waQrTimer = null; }
    });
})();
</script>
@endsection
