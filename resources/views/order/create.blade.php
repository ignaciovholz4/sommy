@extends('layouts.admin')

@section('contenido')
@include('order.modal_payment')
@include('order.modal_stock_check')    

<section class="section margindivsection">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Pedido {{$order->order_id}}</h3>
            <div>
                @php
                    $canalesDetalle = [
                        'tienda'    => ['Tienda online', '#1B2B5A'],
                        'meli'      => ['MercadoLibre', '#e6b800'],
                        'whatsapp'  => ['WhatsApp', '#1EBE5A'],
                        'instagram' => ['Instagram', '#C13584'],
                        'facebook'  => ['Facebook', '#1877F2'],
                        'local'     => ['Local', '#47536F'],
                    ];
                    [$canalNombreDet, $canalColorDet] = $canalesDetalle[$order->origen ?? 'tienda'] ?? [ucfirst($order->origen ?? 'tienda'), '#47536F'];
                @endphp
                <span class="badge" style="background:{{ $canalColorDet }};color:#fff;">{{ $canalNombreDet }}</span>
                @if($order->pago && $order->pago->status_payment === 'Completado' && $order->pago->mp_payment_id)
                    <span class="badge bg-success" title="Pago acreditado en MercadoPago (ID {{ $order->pago->mp_payment_id }}). Al cobrar el pedido, registralo en la cuenta banco donde concilia MP.">
                        <i class="fas fa-check-circle"></i> Pagado online (MercadoPago)
                    </span>
                @endif
                @php
                    $metodoElegido = $order->pago ? \Illuminate\Support\Facades\DB::table('payment_methods')->where('payment_method_id', $order->pago->payment_method_id)->value('method_name') : null;
                @endphp
                @if($metodoElegido === 'transferencia')
                    <span class="badge bg-info"><i class="fas fa-university"></i> Eligió transferencia
                        @if($order->descuento_pago > 0) (desc. ${{ number_format($order->descuento_pago, 2, ',', '.') }}) @endif
                    </span>
                @endif
                @if($order->costo_envio > 0)
                    <span class="badge bg-secondary"><i class="fas fa-truck"></i> Envío ${{ number_format($order->costo_envio, 2, ',', '.') }}</span>
                @endif
            </div>
            <input type="hidden" value="{{$order->order_id}}" id="orderId">
        </div>
        <div class="modal-body">
            @include('order.form_order')
        </div>
    </div>

    {{-- Pagos y transacciones del pedido --}}
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
            <h5 class="mb-0"><i class="fas fa-money-check-alt text-success"></i> Pagos y transacciones</h5>
            <button id="btn-registrar-pago" class="btn btn-sm btn-success">
                <i class="fas fa-plus"></i> Registrar pago
            </button>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap mb-3" style="gap:24px">
                <div>
                    <div class="text-muted text-uppercase" style="font-size:0.65rem; font-weight:700; letter-spacing:.5px">Total del pedido</div>
                    <div class="fw-bold" style="font-size:1.3rem" id="pg-total">—</div>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size:0.65rem; font-weight:700; letter-spacing:.5px">Pagado</div>
                    <div class="fw-bold text-success" style="font-size:1.3rem" id="pg-pagado">—</div>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size:0.65rem; font-weight:700; letter-spacing:.5px">Pendiente</div>
                    <div class="fw-bold" style="font-size:1.3rem" id="pg-pendiente">—</div>
                </div>
                <div id="pg-mp-badge" class="align-self-center" style="display:none">
                    <span class="badge bg-primary"><i class="fab fa-cc-mastercard"></i> <span id="pg-mp-texto"></span></span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm mb-0" id="pg-tabla" style="display:none">
                    <thead>
                        <tr class="text-muted" style="font-size:0.75rem; text-transform:uppercase">
                            <th>Fecha</th>
                            <th>Cuenta</th>
                            <th>Medio</th>
                            <th>Detalle</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody id="pg-body"></tbody>
                </table>
            </div>
            <div id="pg-vacio" class="text-muted text-center py-3" style="display:none">
                Todavía no se registraron pagos para este pedido.
            </div>
        </div>
    </div>
</section>

{{-- Modal registrar pago --}}
<div class="modal fade" id="modalRegistrarPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-hand-holding-usd"></i> Registrar pago del pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">¿Dónde entró la plata?</label>
                    <select id="pg-destino" class="form-select">
                        <option value="">Seleccioná una cuenta...</option>
                    </select>
                    <small class="text-muted">Si cobraste por MercadoPago, elegí la cuenta banco donde concilia MP.</small>
                </div>
                <div class="mb-3" id="pg-tercero-wrap" style="display:none;">
                    <label class="form-label">¿A qué alias fue la plata?</label>
                    <input type="text" id="pg-alias-tercero" class="form-control mb-2" maxlength="60"
                           placeholder="Alias que recibió la transferencia (ej: juan.perez.mp)" list="aliasTercerosConocidosPedido">
                    <input type="text" id="pg-cuit-tercero" class="form-control" maxlength="20"
                           placeholder="CUIT del titular (opcional)">
                    <datalist id="aliasTercerosConocidosPedido"></datalist>
                </div>
                <div class="mb-3">
                    <label class="form-label">Medio de pago</label>
                    <select id="pg-medio" class="form-select">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia / MercadoPago</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto</label>
                    <input type="number" step="0.01" min="0.01" id="pg-monto" class="form-control">
                    <small class="text-muted">Podés registrar un pago parcial: lo pendiente queda a la vista.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones (opcional)</label>
                    <input type="text" id="pg-obs" class="form-control" maxlength="255" placeholder="Ej: seña, saldo contra entrega, MP operación #...">
                </div>
                <div class="mb-2">
                    <label class="form-label"><i class="fas fa-paperclip"></i> Comprobante o archivo relacionado (opcional)</label>
                    <input type="file" id="pg-archivo" class="form-control" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    <small class="text-muted">Captura de la transferencia, recibo, o cualquier archivo. Hasta 8 MB.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="pg-guardar"><i class="fas fa-save"></i> Registrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Exportamos datos al JS para que order_show.js los pueda usar
    const detailsOrder = @json($detailsOrder);
    const orderData = @json($order);
</script>
<script src="{{asset('js/funciones_orders/order_show.js')}}"></script>
<script>
// ── Pagos y transacciones del pedido ──
(function () {
    const orderId = document.getElementById('orderId').value;
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const fmt = n => '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    let pendienteActual = 0;

    function cargarPagos() {
        fetch(`/orders/order/${orderId}/pagos`)
            .then(r => r.json())
            .then(d => {
                pendienteActual = d.pendiente;
                document.getElementById('pg-total').textContent = fmt(d.total);
                document.getElementById('pg-pagado').textContent = fmt(d.pagado);
                const pend = document.getElementById('pg-pendiente');
                pend.textContent = fmt(d.pendiente);
                pend.className = 'fw-bold ' + (d.pendiente > 0 ? 'text-danger' : 'text-success');
                pend.style.fontSize = '1.3rem';
                if (d.pendiente <= 0) pend.textContent += ' ✓';

                const mpBadge = document.getElementById('pg-mp-badge');
                if (d.mp) {
                    document.getElementById('pg-mp-texto').textContent = 'MercadoPago: ' + d.mp.status + ' (op. ' + d.mp.payment_id + ')';
                    mpBadge.style.display = '';
                } else {
                    mpBadge.style.display = 'none';
                }

                const body = document.getElementById('pg-body');
                body.innerHTML = '';
                if (d.movimientos.length) {
                    d.movimientos.forEach(m => {
                        const signo = m.tipo === 'ingreso' ? '+' : '−';
                        const color = m.tipo === 'ingreso' ? 'text-success' : 'text-danger';
                        const tr = document.createElement('tr');
                        tr.innerHTML =
                            '<td>' + m.fecha + '</td>' +
                            '<td><strong></strong></td>' +
                            '<td><span class="badge bg-light text-dark"></span></td>' +
                            '<td class="text-muted"></td>' +
                            '<td class="text-end fw-bold ' + color + '">' + signo + fmt(m.monto) + '</td>';
                        tr.children[1].firstChild.textContent = m.cuenta;
                        tr.children[2].firstChild.textContent = m.medio;
                        tr.children[3].textContent = m.observaciones || '';
                        if (m.adjunto_url) {
                            const a = document.createElement('a');
                            a.href = m.adjunto_url;
                            a.target = '_blank';
                            a.rel = 'noopener';
                            a.className = 'ms-2';
                            a.title = m.adjunto_nombre || 'Ver comprobante';
                            a.innerHTML = '<i class="fas fa-paperclip"></i> comprobante';
                            tr.children[3].appendChild(a);
                        }
                        body.appendChild(tr);
                    });
                    document.getElementById('pg-tabla').style.display = '';
                    document.getElementById('pg-vacio').style.display = 'none';
                } else {
                    document.getElementById('pg-tabla').style.display = 'none';
                    document.getElementById('pg-vacio').style.display = '';
                }

                const sel = document.getElementById('pg-destino');
                sel.innerHTML = '<option value="">Seleccioná una cuenta...</option>';
                d.destinos.forEach(x => {
                    const opt = document.createElement('option');
                    opt.value = x.id;
                    opt.textContent = x.nombre;
                    sel.appendChild(opt);
                });
            });
    }

    // Si el destino es una cuenta de terceros, pedir alias/CUIT (dinámico por pago)
    let aliasConocidos = [];
    document.getElementById('pg-destino').addEventListener('change', function () {
        const esTercero = this.value.startsWith('tercero-');
        document.getElementById('pg-tercero-wrap').style.display = esTercero ? '' : 'none';
        if (esTercero && !aliasConocidos.length) {
            fetch('/cuentas/terceros/alias')
                .then(r => r.json())
                .then(d => {
                    aliasConocidos = d.alias || [];
                    document.getElementById('aliasTercerosConocidosPedido').innerHTML =
                        aliasConocidos.map(a => '<option value="' + a.alias + '">' + (a.cuit ? 'CUIT ' + a.cuit : '') + '</option>').join('');
                })
                .catch(() => {});
        }
    });
    document.getElementById('pg-alias-tercero').addEventListener('change', function () {
        const conocido = aliasConocidos.find(a => a.alias === this.value.trim().toLowerCase());
        const cuitInput = document.getElementById('pg-cuit-tercero');
        if (conocido && conocido.cuit && !cuitInput.value) cuitInput.value = conocido.cuit;
    });

    document.getElementById('btn-registrar-pago').addEventListener('click', function () {
        document.getElementById('pg-monto').value = pendienteActual > 0 ? pendienteActual.toFixed(2) : '';
        document.getElementById('pg-obs').value = '';
        new bootstrap.Modal(document.getElementById('modalRegistrarPago')).show();
    });

    document.getElementById('pg-guardar').addEventListener('click', function () {
        const destino = document.getElementById('pg-destino').value;
        const monto = parseFloat(document.getElementById('pg-monto').value);
        if (!destino) { Swal.fire('Atención', 'Elegí la cuenta donde entró la plata.', 'warning'); return; }
        if (!monto || monto <= 0) { Swal.fire('Atención', 'Ingresá un monto válido.', 'warning'); return; }
        if (destino.startsWith('tercero-') && !document.getElementById('pg-alias-tercero').value.trim()) {
            Swal.fire('Atención', 'Indicá el alias del tercero que recibió la plata.', 'warning'); return;
        }

        const formData = new FormData();
        formData.append('destino', destino);
        formData.append('medio', document.getElementById('pg-medio').value);
        if (destino.startsWith('tercero-')) {
            formData.append('alias_tercero', document.getElementById('pg-alias-tercero').value.trim());
            formData.append('cuit_tercero', document.getElementById('pg-cuit-tercero').value.trim());
        }
        formData.append('monto', monto);
        formData.append('observaciones', document.getElementById('pg-obs').value);
        const archivo = document.getElementById('pg-archivo').files[0];
        if (archivo) formData.append('comprobante', archivo);

        this.disabled = true;
        fetch(`/orders/order/${orderId}/registrar-pago`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: formData
        })
        .then(r => r.status === 422
            ? r.json().then(j => ({ status: 0, mensaje: Object.values(j.errors || {}).flat() }))
            : r.json())
        .then(d => {
            if (d.status === 1) {
                bootstrap.Modal.getInstance(document.getElementById('modalRegistrarPago')).hide();
                Swal.fire('Listo', d.message, 'success');
                cargarPagos();
            } else {
                Swal.fire('Error', (d.mensaje || ['No se pudo registrar el pago']).join('<br>'), 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Error de conexión al registrar el pago.', 'error'))
        .finally(() => { this.disabled = false; });
    });

    cargarPagos();
})();
</script>
@endsection