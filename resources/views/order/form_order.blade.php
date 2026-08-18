<style>
    /* --- Detalle de pedido Sommy: compacto y sereno --- */
    .smo-wrap {
        font-family: 'Poppins', sans-serif;
        color: #1B2B5A;
    }

    .smo-card {
        background: #fff;
        border: 1px solid #E7EAF2;
        border-radius: 14px;
        margin-bottom: 14px;
        overflow: hidden;
    }
    .smo-card-header {
        padding: 10px 18px;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #47536F;
        background: #F8FAFC;
        border-bottom: 1px solid #E7EAF2;
    }
    .smo-card-body { padding: 14px 18px; }

    .smo-label {
        font-size: 11.5px;
        font-weight: 500;
        color: #6E7A96;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 1px;
    }
    .smo-data { font-weight: 500; font-size: 14.5px; color: #1B2B5A; }
    .smo-data a { color: #2563EB; text-decoration: none; }
    .smo-data a:hover { color: #1B2B5A; }

    /* Stepper compacto */
    .smo-stepper {
        display: flex;
        align-items: flex-start;
        padding: 6px 0 2px;
        position: relative;
    }
    .smo-step { flex: 1; text-align: center; position: relative; z-index: 1; }
    .smo-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 16px;
        left: calc(50% + 22px);
        width: calc(100% - 44px);
        height: 2px;
        background: #E7EAF2;
        border-radius: 999px;
    }
    .smo-step.complete:not(:last-child)::after { background: #1B2B5A; }

    .smo-step-icon {
        width: 32px; height: 32px;
        border-radius: 999px;
        border: 2px solid #E7EAF2;
        background: #fff;
        color: #9AA5C0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        transition: all .15s ease;
        padding: 0;
    }
    .smo-step-icon:hover:not(:disabled) { border-color: #2563EB; color: #2563EB; }
    .smo-step-icon:disabled { cursor: default; opacity: .55; }

    .smo-step.complete .smo-step-icon {
        background: #1B2B5A;
        border-color: #1B2B5A;
        color: #fff;
    }
    .smo-step.active .smo-step-icon {
        background: #E0F2FE;
        border-color: #2563EB;
        color: #1B2B5A;
    }

    .smo-step-name {
        font-size: 11.5px;
        font-weight: 500;
        color: #6E7A96;
        margin-top: 6px;
        line-height: 1.25;
    }
    .smo-step.active .smo-step-name,
    .smo-step.complete .smo-step-name { color: #1B2B5A; }

    .smo-step--anular .smo-step-icon { border-color: #F3D3C4; color: #b4552d; }
    .smo-step--anular .smo-step-icon:hover { border-color: #b4552d; background: #FBEDE6; color: #b4552d; }

    /* Tabla compacta */
    .smo-table { width: 100%; border-collapse: collapse; }
    .smo-table thead th {
        background: #F8FAFC;
        border-bottom: 1px solid #E7EAF2;
        color: #6E7A96;
        font-size: 11.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 9px 14px;
        text-align: left;
    }
    .smo-table tbody td {
        padding: 9px 14px;
        border-bottom: 1px solid #F1F4F9;
        font-size: 14px;
        color: #1B2B5A;
    }
    .smo-table .text-right { text-align: right; }
    .smo-table .text-center { text-align: center; }

    /* Totales en línea */
    .smo-totals {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 26px;
        flex-wrap: wrap;
        padding: 12px 18px;
        background: #F8FAFC;
        border-top: 1px solid #E7EAF2;
    }
    .smo-totals .item { text-align: right; }
    .smo-totals .item .v { font-weight: 500; font-size: 14.5px; }
    .smo-totals .total .v { font-weight: 700; font-size: 24px; color: #1B2B5A; }

    .smo-anulado {
        background: #FBEDE6;
        color: #b4552d;
        border-radius: 12px;
        padding: 12px 18px;
        font-weight: 500;
        font-size: 14.5px;
    }
</style>

<div class="smo-wrap">
    <div class="row">
        {{-- Cliente --}}
        <div class="col-lg-5">
            <div class="smo-card">
                <div class="smo-card-header">Cliente</div>
                <div class="smo-card-body">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <div class="smo-label">Nombre</div>
                            <div class="smo-data">{{$order->cliente->nombre}} {{$order->cliente->materno}} {{$order->cliente->paterno}}</div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="smo-label">Teléfono</div>
                            <div class="smo-data">
                                @if($order->cliente->telefono)
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $order->cliente->telefono) }}" target="_blank" rel="noopener noreferrer" title="Abrir WhatsApp">
                                    <i class="fab fa-whatsapp"></i> {{$order->cliente->telefono}}
                                </a>
                                @else — @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="smo-label">Correo</div>
                            <div class="smo-data" style="overflow-wrap:anywhere;">{{$order->cliente->email}}</div>
                        </div>
                        <div class="col-6">
                            <div class="smo-label">Entrega</div>
                            <div class="smo-data">{{$order->cliente->direccion}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estado / etapas --}}
        <div class="col-lg-7">
            <div class="smo-card">
                <div class="smo-card-header" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                    <span>Seguimiento del pedido</span>
                    {{-- Programar fecha de entrega (avisa al cliente por mail) --}}
                    <form method="POST" action="{{ url('/orders/order/' . $order->order_id . '/entrega') }}" style="display:flex;align-items:center;gap:8px;margin:0;">
                        @csrf
                        <label style="font-size:11px;color:#6E7A96;margin:0;text-transform:none;letter-spacing:0;">Entrega:</label>
                        <input type="date" name="fecha_entrega" value="{{ $order->fecha_entrega ?? '' }}" required
                               style="border:1px solid #E7EAF2;border-radius:8px;padding:4px 8px;font-size:12.5px;color:#1B2B5A;">
                        <button type="submit" style="border:none;background:#1B2B5A;color:#fff;border-radius:999px;padding:5px 14px;font-size:11.5px;font-weight:500;cursor:pointer;">
                            {{ $order->fecha_entrega ? 'Cambiar' : 'Programar' }}
                        </button>
                    </form>
                </div>
                <div class="smo-card-body">
                    @if ($order->status_order_id != 6)
                        <div class="smo-stepper">
                            @foreach($statusOrder as $status)
                                @if($status->status_id < 6)
                                <div class="smo-step {{$status->action}} {{$status->current_active}}">
                                    <button class="smo-step-icon"
                                            title="Marcar: {{ $status->status_name }}"
                                            onclick="fnUpdateStatusOrder({{$status->status_id}});"
                                            {{$status->current_active == 'inactive' ? 'disabled':''}}>
                                        <i class="{{ $status->icon }}"></i>
                                    </button>
                                    <div class="smo-step-name">{{ $status->status_name }}</div>
                                </div>
                                @endif
                            @endforeach

                            @if ($order->status_order_id < 3)
                            <div class="smo-step smo-step--anular">
                                <button class="smo-step-icon" title="Anular pedido" onclick="fnUpdateStatusOrder({{$statusOrder[5]->status_id}});">
                                    <i class="fas fa-ban"></i>
                                </button>
                                <div class="smo-step-name">Anular</div>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="smo-anulado"><i class="fas fa-ban mr-2"></i> Pedido anulado</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Comprobante de pago (transferencia, captura, PDF) --}}
        <div class="col-12">
            <div class="smo-card">
                <div class="smo-card-header" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                    <span><i class="fas fa-receipt" style="color:#2563EB;"></i> Comprobante de pago</span>
                    <form method="POST" action="{{ url('/orders/order/' . $order->order_id . '/comprobante') }}" enctype="multipart/form-data"
                          style="display:flex;align-items:center;gap:8px;margin:0;flex-wrap:wrap;">
                        @csrf
                        <input type="file" name="archivo" accept="image/*,.pdf" required
                               style="border:1px solid #E7EAF2;border-radius:8px;padding:4px 8px;font-size:12px;color:#1B2B5A;max-width:230px;">
                        <input type="text" name="nota" placeholder="Nota (ej: transferencia Galicia)" maxlength="200"
                               style="border:1px solid #E7EAF2;border-radius:8px;padding:5px 10px;font-size:12.5px;color:#1B2B5A;">
                        <button type="submit" style="border:none;background:#1B2B5A;color:#fff;border-radius:999px;padding:6px 16px;font-size:11.5px;font-weight:500;cursor:pointer;">
                            <i class="fas fa-upload"></i> Subir
                        </button>
                    </form>
                </div>
                <div class="smo-card-body">
                    @if(session('comp_ok'))
                        <div class="alert alert-success" style="border-radius:10px;padding:8px 14px;font-size:13px;">{{ session('comp_ok') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger" style="border-radius:10px;padding:8px 14px;font-size:13px;">{{ $errors->first() }}</div>
                    @endif
                    @if($comprobantesPago->isEmpty())
                        <div style="font-size:12.5px;color:#94A3B8;font-weight:300;">
                            Sin comprobantes todavía. Subí la foto de la transferencia o el PDF para que quede registrado en el pedido.
                        </div>
                    @else
                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            @foreach($comprobantesPago as $comp)
                            <div style="border:1px solid #E7EAF2;border-radius:12px;background:#F8FAFC;padding:8px;text-align:center;position:relative;width:130px;">
                                <button onclick="if(confirm('¿Eliminar comprobante?')){fetch('{{ url('orders/comprobante/' . $comp->id) }}',{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload());}"
                                        style="position:absolute;top:4px;right:6px;border:none;background:none;color:#b4552d;cursor:pointer;font-size:12px;" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                @if(str_starts_with((string) $comp->mime, 'image/'))
                                    <a href="{{ asset($comp->archivo) }}" target="_blank">
                                        <img src="{{ asset($comp->archivo) }}" style="width:100%;height:90px;object-fit:cover;border-radius:8px;">
                                    </a>
                                @else
                                    <a href="{{ asset($comp->archivo) }}" target="_blank" style="display:block;padding:26px 0;color:#b4552d;">
                                        <i class="fas fa-file-pdf" style="font-size:30px;"></i>
                                    </a>
                                @endif
                                <div style="font-size:10px;color:#47536F;margin-top:4px;">
                                    {{ \Carbon\Carbon::parse($comp->created_at)->format('d/m/Y H:i') }}
                                    @if($comp->nota)<br>{{ \Illuminate\Support\Str::limit($comp->nota, 40) }}@endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Productos + totales --}}
        <div class="col-12">
            <div class="smo-card">
                <div class="smo-card-header">Productos</div>
                <div class="table-responsive">
                    <table class="smo-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th>Combinación</th>
                                <th class="text-right">Precio</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detailsOrder as $product)
                            <tr>
                                <td style="font-weight:500;">{{ $product->producto->nombre }}</td>
                                <td class="text-center">{{ $product->quantity }}</td>
                                <td>
                                    @if(!empty($product->json_detalle))
                                        <small class="badge badge-light border">{{ implode(' / ', $product->json_detalle) }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">${{ number_format($product->price, 2, ',', '.') }}</td>
                                <td class="text-right" style="font-weight:600;">${{ number_format($product->total, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center">No hay productos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="smo-totals">
                    <div class="item">
                        <div class="smo-label">Subtotal</div>
                        <div class="v">${{ number_format($order->subtotal_amount, 2, ',', '.') }}</div>
                    </div>
                    @if($order->costo_envio > 0)
                    <div class="item">
                        <div class="smo-label">Envío</div>
                        <div class="v">${{ number_format($order->costo_envio, 2, ',', '.') }}</div>
                    </div>
                    @endif
                    @if($order->descuento_pago > 0)
                    <div class="item">
                        <div class="smo-label">Desc. transferencia</div>
                        <div class="v">-${{ number_format($order->descuento_pago, 2, ',', '.') }}</div>
                    </div>
                    @endif
                    <div class="item total">
                        <div class="smo-label">Total a cobrar</div>
                        <div class="v">${{ number_format($order->total_amount, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
