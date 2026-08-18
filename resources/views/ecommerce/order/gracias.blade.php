@extends('ecommerce.layouts.main-ecommerce')

@section('meta_title', 'Pedido #' . $order->order_id . ' confirmado')

@section('contentEcommerce')
<section class="py-5">
    <div class="container" style="max-width:760px;">
        <div class="p-5 bg-light rounded-3 text-center">
            @php $pagado = optional($order->pago)->status_payment === 'Completado'; @endphp

            <div class="mb-3">
                @if($pagado)
                    <i class="fas fa-check-circle text-success" style="font-size:4rem;"></i>
                @else
                    <i class="fas fa-clock text-warning" style="font-size:4rem;"></i>
                @endif
            </div>

            <h2 class="fw-bold">¡Gracias por tu compra, {{ optional($order->cliente)->nombre }}!</h2>
            <p class="fs-5">Tu pedido <strong>#{{ $order->order_id }}</strong> quedó registrado.</p>

            @if($pagado)
                <div class="alert alert-success d-inline-block">
                    <i class="fas fa-check"></i> Pago acreditado con MercadoPago. Nos vamos a contactar para coordinar la entrega.
                </div>
            @elseif($metodoPago === 'mercadopago')
                <div class="alert alert-warning d-inline-block">
                    El pago todavía figura pendiente. Si ya pagaste, puede demorar unos minutos.
                </div>
                <div>
                    <button class="btn btn-outline-dark" id="btnVerificarPago" data-order="{{ $order->order_id }}">
                        <i class="fas fa-sync"></i> Verificar estado del pago
                    </button>
                </div>
            @else
                <div class="alert alert-info text-start d-inline-block">
                    <strong>Datos para transferir:</strong><br>
                    @if(!empty($config->razon_social)) Titular: {{ $config->razon_social }}<br>@endif
                    @if(!empty($config->cuit)) CUIT: {{ $config->cuit }}<br>@endif
                    @if(!empty($config->cbu)) CBU: <strong>{{ $config->cbu }}</strong><br>@endif
                    @if(!empty($config->alias_cbu)) Alias: <strong>{{ $config->alias_cbu }}</strong><br>@endif
                    Monto: <strong>{{ format_money_global($order->total_amount) }}</strong>
                </div>
                <p class="text-muted">También te enviamos estos datos por email. Envianos el comprobante por WhatsApp.</p>
            @endif

            <hr class="my-4">

            <table class="table table-sm text-start">
                <tbody>
                    @foreach ($order->detalles as $detalle)
                        <tr>
                            <td>{{ optional($detalle->producto)->nombre }} × {{ $detalle->quantity }}</td>
                            <td class="text-end">{{ format_money_global($detalle->total) }}</td>
                        </tr>
                    @endforeach
                    @if($order->costo_envio > 0)
                        <tr><td>Envío</td><td class="text-end">{{ format_money_global($order->costo_envio) }}</td></tr>
                    @endif
                    @if($order->descuento_pago > 0)
                        <tr class="text-success"><td>Descuento transferencia</td><td class="text-end">-{{ format_money_global($order->descuento_pago) }}</td></tr>
                    @endif
                    <tr class="fw-bold"><td>Total</td><td class="text-end">{{ format_money_global($order->total_amount) }}</td></tr>
                </tbody>
            </table>

            <a class="btn btn-dark btn-lg mt-3" href="{{ url('/') }}">Seguir comprando</a>
        </div>
    </div>
</section>
@endsection

@section('scriptEcommerce')
<script>
    const btnVerificar = document.getElementById('btnVerificarPago');
    if (btnVerificar) {
        btnVerificar.addEventListener('click', async () => {
            btnVerificar.disabled = true;
            try {
                const resp = await fetch(`/pedido/verificar-pago/${btnVerificar.dataset.order}`);
                const data = await resp.json();
                if (data.pagado) {
                    location.reload();
                } else {
                    toastr.warning('El pago todavía no se acreditó. Estado: ' + (data.mp_status || 'sin registrar'));
                }
            } catch (e) {
                toastr.error('No se pudo verificar el pago');
            }
            btnVerificar.disabled = false;
        });
    }
</script>
@endsection
