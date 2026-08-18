<!-- Modal type payment -->
<style>
  /* El modal debe quedar por encima del header fijo del panel */
  #typePaymentModal { z-index: 2060; }
  #typePaymentModal .modal-content { border-radius: 16px; border: 1px solid #E7EAF2; }
  #typePaymentModal .modal-header { background: #F8FAFC; border-radius: 16px 16px 0 0; }
  .modal-backdrop { z-index: 2050; }
  .pago-elegido {
    background: #E0F2FE;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 14px;
    font-size: 14px;
    color: #1B2B5A;
  }
  .pago-elegido .m { font-weight: 600; }
  .pago-sin-cuentas {
    background: #FBEDE6;
    color: #b4552d;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 13.5px;
  }
  .pago-sin-cuentas a { color: #b4552d; font-weight: 600; }
</style>

<div class="modal fade" id="typePaymentModal"  data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="typePaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="typePaymentModalLabel">Registrar el cobro del pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        {{-- Medio de pago que eligió el cliente en el canal digital --}}
        @php
            $metodoElegidoModal = null;
            if (isset($order) && $order->pago && $order->pago->payment_method_id) {
                $metodoElegidoModal = \Illuminate\Support\Facades\DB::table('payment_methods')
                    ->where('payment_method_id', $order->pago->payment_method_id)
                    ->value('method_name');
            }
            $etiquetasPago = [
                'transferencia' => 'Transferencia bancaria',
                'mercadopago'   => 'Mercado Pago (online)',
                'qr'            => 'Pago con QR',
                'tarjeta'       => 'Tarjeta de débito/crédito',
                'efectivo'      => 'Efectivo',
                'banco'         => 'Depósito bancario',
            ];
        @endphp

        @if($metodoElegidoModal)
        <div class="pago-elegido">
            <i class="fas fa-circle-info"></i>
            El cliente eligió pagar con <span class="m">{{ $etiquetasPago[$metodoElegidoModal] ?? ucfirst($metodoElegidoModal) }}</span>
            @if(isset($order))
              — total <strong>${{ number_format($order->total_amount, 2, ',', '.') }}</strong>
            @endif
            @if($order->pago && $order->pago->mp_payment_id)
              <div class="mt-1"><small>Ya acreditado en Mercado Pago (ID {{ $order->pago->mp_payment_id }})</small></div>
            @endif
        </div>
        @endif

        <label for="select-payment-method" style="font-size:13px;font-weight:500;">¿En qué cuenta ingresa el dinero?</label>
        <select class="form-select" aria-label="Cuenta donde ingresa el cobro" id="select-payment-method" required>
            <option value="">Seleccionar</option>
        </select>

        {{-- Aviso cuando todavía no hay cajas ni bancos abiertos --}}
        <div class="pago-sin-cuentas mt-3" id="aviso-sin-cuentas" style="display:none;">
            <i class="fas fa-triangle-exclamation"></i>
            No hay ninguna caja o cuenta bancaria abierta. Abrí una desde
            <a href="{{ url('cuentas/gestor') }}" target="_blank">Finanzas &rsaquo; Gestor de Cuentas</a>
            y volvé a intentarlo.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-close-payment-method"  data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn-payment-method">Aceptar</button>
      </div>
    </div>
  </div>
</div>
