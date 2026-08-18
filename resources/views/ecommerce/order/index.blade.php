
@extends('ecommerce.layouts.main-ecommerce')
@section('contentEcommerce')
<style>
    .section-content-order{
        background:#f8f9fa;
    }
    .header-content-card{
        background-color:#ffffff;
    }
    .input-form{
        border:1px solid #C8C8C8;
    }
    .card-title{
        float:left;
        font-size:1.1rem;
        font-weight:400;
        margin:0;
    }
    .card-tools{
        float:right;
        margin-right: -.625rem;
    }

    /* ── Medios de pago: tarjetas grandes y clickeables ── */
    #form-pago .form-check {
        border: 1.5px solid #E7EAF2;
        border-radius: 14px;
        padding: 14px 16px 14px 44px;
        margin-bottom: 10px;
        transition: border-color .15s, background .15s;
        cursor: pointer;
    }
    #form-pago .form-check:hover { border-color: #2563EB; }
    #form-pago .form-check .form-check-input { margin-left: -30px; margin-top: 3px; }
    #form-pago .form-check .form-check-input:checked { background-color: #1B2B5A; border-color: #1B2B5A; }
    #form-pago .form-check:has(.form-check-input:checked) { border-color: #1B2B5A; background: #F8FAFC; }
    #form-pago .form-check-label { cursor: pointer; width: 100%; }

    /* ── Zonas de envío: mismo estilo ── */
    #form-envio .form-check {
        border: 1.5px solid #E7EAF2;
        border-radius: 14px;
        padding: 13px 16px 13px 44px;
        margin-bottom: 10px;
        cursor: pointer;
    }
    #form-envio .form-check .form-check-input { margin-left: -30px; margin-top: 3px; }
    #form-envio .form-check:has(.form-check-input:checked) { border-color: #1B2B5A; background: #F8FAFC; }
    #form-envio .form-check-label { cursor: pointer; }

    /* ── MOBILE: una sola columna, todo cómodo al dedo ── */
    @media (max-width: 767px) {
        .cart-wrapper { padding: 16px 0; }
        .section-content-order .container,
        .section-content-order .container-fluid { padding-left: 12px; padding-right: 12px; }

        .header-content-card { padding: 12px 14px; }
        .card-title { font-size: 1rem; font-weight: 600; }
        #div-content-section-order .card-body { padding: 14px; }
        #div-content-section-order > div { margin-top: 14px !important; }

        /* Campos más altos para el dedo */
        .input-form, .form-control, .form-select {
            font-size: 16px !important;   /* evita el zoom automático de iOS */
            padding: 11px 13px !important;
            border-radius: 10px !important;
        }
        .form-label { font-size: 13px; margin-bottom: 4px; }

        /* Botones de sección a lo ancho */
        #div-content-section-order .btn-primary {
            width: 100%;
            padding: 13px;
            font-size: 15px;
            border-radius: 999px;
        }

        /* Resumen: deja de ser sticky para no tapar el formulario */
        .summary-card { position: static !important; margin-top: 16px; }

        /* Productos del pedido en columna */
        .product-card .row > div { margin-bottom: 8px; }
        .product-image, .sommy-order-thumb { width: 64px !important; height: 64px !important; }

        #form-pago .form-check, #form-envio .form-check { padding: 13px 14px 13px 42px; }
    }
    /******************************** */
    /******************************** */
    .cart-wrapper {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 40px 0;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            transition: transform 0.2s;
        }

        .product-card:hover {
            /*transform: translateY(-2px);*/
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 6px;
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            position: sticky;
            top: 20px;
        }

        .checkout-btn {
            background: #1B2B5A;
            border: none;
            transition: transform 0.2s;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            background: #2563EB;
        }

        .remove-btn {
            color: #dc2626;
            cursor: pointer;
            transition: all 0.2s;
        }

        .remove-btn:hover {
            color: #991b1b;
        }

        .quantity-btn {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: #f3f4f6;
            border: none;
            transition: all 0.2s;
        }

        .quantity-btn:hover {
            background: #e5e7eb;
        }

        .discount-badge {
            background: #dcfce7;
            color: #166534;
            font-size: 0.875rem;
            padding: 4px 8px;
            border-radius: 6px;
        }
</style>
<section class="py-5 section-content-order" style="">
  <div class="container-fluid">
        <div class="container" id="div-content-order-global">
            <div class="row">
                <div class="col-md-8">
                    <div id="div-content-cart-detail">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header shadow-sm header-content-card">
                                <strong>Lista de productos agregados</strong> 
                            </div>
                            <div class="">
                                <div class="d-flex flex-column gap-3 show-product-added-cart">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="div-content-section-order" style="display:none;">
                        <div style="">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0 shadow-sm header-content-card" style="">
                                    <strong class="card-title">Email</strong>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnSectionEmail" data-card-widget="collapse">
                                            <i class="fa-solid fa-pencil text-secondary" id="icon-email" aria-hidden="true"></i>
                                        </button>
                                    </div><br>
                                    <div id="div-show-email" class="form-text"></div>
                                </div>
                                <div class="card-body" id="card-section-email">
                                    <form id="form-email">
                                    <div class="mb-3">
                                        <label for="exampleInputEmail1" class="form-label">Ingresa tu correo electronico</label>
                                        <input type="email" class="form-control input-form" name="email" aria-describedby="emailHelp"
                                               value="{{ Auth::guard('cliente')->check() ? Auth::guard('cliente')->user()->email : '' }}">
                                        <div id="emailHelp" class="form-text">
                                            @if(Auth::guard('cliente')->check())
                                                Comprando con tu cuenta ({{ Auth::guard('cliente')->user()->email }}).
                                            @else
                                                Guardamos tu correo electronico para identificar tu perfil.
                                            @endif
                                        </div>
                                    </div>
                                    </form>
                                    <button type="button" id="btnAddEmail" class="btn btn-primary">Continuar</button>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:25px;">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0 shadow-sm header-content-card">
                                    <strong class="card-title">Identificacion</strong>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnSectionIdentificacion" data-card-widget="collapse">
                                            <i class="fa-solid fa-pencil text-secondary" id="icon-identificacion" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" id="card-section-identification">
                                    {{-- Datos precargados de la cuenta del comprador --}}
                                    @php($cli = Auth::guard('cliente')->user())
                                    <form class="row g-3" id="form-identificacion" >
                                        <div class="col-md-12">
                                            <label for="inputEmail4" class="form-label">Nombre</label>
                                            <input type="text" class="form-control input-form" name="name" id="name" required
                                                   value="{{ $cli->nombre ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inputPassword4" class="form-label">Apellido</label>
                                            <input type="text" class="form-control input-form" name="materno" id="materno" required
                                                   value="{{ $cli->materno ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inputAddress" class="form-label">Telefono</label>
                                            <input type="text" class="form-control input-form" name="phone" id="phone" placeholder="XXX XXX XXXX" required
                                                   value="{{ $cli->telefono ?? '' }}">
                                        </div>
                                        <div class="col-12">
                                            <button type="button" id="btnAddIdentificacion" class="btn btn-primary">Ir para la entrega</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:25px;">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0 shadow-sm header-content-card">
                                    <strong class="card-title">Entrega</strong>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnSectionEntrega" data-card-widget="collapse">
                                            <i class="fa-solid fa-pencil text-secondary" id="icon-entrega" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" id="card-section-entrega">
                                    <form class="row g-3" id="form-entrega">
                                        <div class="col-md-12">
                                            <label for="calle" class="form-label">Calle</label>
                                            <input type="text" class="form-control input-form" name="calle" id="calle"
                                                   value="{{ $cli->direccion ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="numberExterior" class="form-label">Numero</label>
                                            <input type="number" min="0" class="form-control input-form" name="numberExterior" id="numberExterior"
                                                   value="{{ $cli->number_exterior ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="numberInterior" class="form-label">Piso / Depto <small class="text-muted">(opcional)</small></label>
                                            <input type="text" class="form-control input-form" name="numberInterior" id="numberInterior" >
                                        </div>
                                        <div class="col-md-6">
                                            <label for="localidad" class="form-label">Localidad</label>
                                            <input type="text" class="form-control input-form" name="localidad" id="localidad"
                                                   value="{{ $cli->localidad ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="provincia" class="form-label">Provincia</label>
                                            <select class="form-control input-form" name="provincia" id="provincia">
                                                <option value="">Seleccionar</option>
                                                @foreach (['Buenos Aires','CABA','Catamarca','Chaco','Chubut','Córdoba','Corrientes','Entre Ríos','Formosa','Jujuy','La Pampa','La Rioja','Mendoza','Misiones','Neuquén','Río Negro','Salta','San Juan','San Luis','Santa Cruz','Santa Fe','Santiago del Estero','Tierra del Fuego','Tucumán'] as $prov)
                                                    <option value="{{ $prov }}" {{ ($cli->provincia ?? '') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="codigoPostal" class="form-label">Código postal</label>
                                            <input type="text" class="form-control input-form" name="codigoPostal" id="codigoPostal" maxlength="10"
                                                   value="{{ $cli->codigo_postal ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="dniCuit" class="form-label">DNI / CUIT</label>
                                            <input type="text" class="form-control input-form" name="dniCuit" id="dniCuit" maxlength="13" placeholder="Para la factura"
                                                   value="{{ $cli->dni_cuit ?? '' }}">
                                        </div>
                                        <div class="col-12">
                                            <label for="infoAdicional" class="form-label">Informacion adicional <small class="text-muted">(opcional)</small></label>
                                            <textarea class="form-control input-form" name="infoAdicional" id="infoAdicional" rows="3" placeholder="Referencias de la dirección, horarios, etc."></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="button" id="btnAddEntrega" class="btn btn-primary">Ir al envío</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN ENVÍO --}}
                        <div style="margin-top:25px;">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0 shadow-sm header-content-card">
                                    <strong class="card-title">Envío</strong>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnSectionEnvio" data-card-widget="collapse">
                                            <i class="fa-solid fa-pencil text-secondary" id="icon-envio" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" id="card-section-envio">
                                    <form id="form-envio">
                                        @foreach ($zonasEnvio as $zona)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input radio-zona-envio" type="radio" name="zonaEnvio"
                                                       id="zona-{{ $zona->id }}" value="{{ $zona->id }}" data-costo="{{ $zona->costo }}">
                                                <label class="form-check-label d-flex justify-content-between" for="zona-{{ $zona->id }}" style="width:100%;max-width:400px;">
                                                    <span>{{ $zona->nombre }}</span>
                                                    <strong>{{ $zona->costo > 0 ? format_money_global($zona->costo) : 'Gratis' }}</strong>
                                                </label>
                                            </div>
                                        @endforeach
                                        @if($zonasEnvio->isEmpty())
                                            <p class="text-muted">El envío se coordina luego de confirmar el pedido.</p>
                                        @endif
                                        <button type="button" id="btnAddEnvio" class="btn btn-primary mt-2">Ir al pago</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN PAGO --}}
                        <div style="margin-top:25px;">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0 shadow-sm header-content-card">
                                    <strong class="card-title">Pago</strong>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnSectionPago" data-card-widget="collapse">
                                            <i class="fa-solid fa-pencil text-secondary" id="icon-pago" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" id="card-section-pago">
                                    <form id="form-pago">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input radio-metodo-pago" type="radio" name="metodoPago" id="pago-transferencia" value="transferencia">
                                            <label class="form-check-label" for="pago-transferencia">
                                                <strong>Transferencia bancaria</strong>
                                                @if(($configPago['descuento_transferencia'] ?? 0) > 0)
                                                    <span class="discount-badge ms-2">{{ rtrim(rtrim(number_format($configPago['descuento_transferencia'], 2), '0'), '.') }}% de descuento</span>
                                                @endif
                                            </label>
                                        </div>
                                        <div id="datos-transferencia" class="ms-4 mb-3 p-3 bg-light rounded" style="display:none;">
                                            <p class="mb-1"><strong>Datos para transferir:</strong></p>
                                            @if(!empty($configPago['razon_social']))<p class="mb-1">Titular: {{ $configPago['razon_social'] }}</p>@endif
                                            @if(!empty($configPago['cuit']))<p class="mb-1">CUIT: {{ $configPago['cuit'] }}</p>@endif
                                            @if(!empty($configPago['cbu']))<p class="mb-1">CBU: {{ $configPago['cbu'] }}</p>@endif
                                            @if(!empty($configPago['alias_cbu']))<p class="mb-1">Alias: <strong>{{ $configPago['alias_cbu'] }}</strong></p>@endif
                                            <p class="mb-0 text-muted"><small>Al confirmar el pedido vas a recibir estos datos por email. Envianos el comprobante por WhatsApp.</small></p>
                                        </div>
                                        @if(!empty($configPago['mp_habilitado']))
                                        <div class="form-check mb-2">
                                            <input class="form-check-input radio-metodo-pago" type="radio" name="metodoPago" id="pago-mercadopago" value="mercadopago">
                                            <label class="form-check-label" for="pago-mercadopago">
                                                <strong>Mercado Pago</strong> <span class="text-muted">— tarjeta de crédito/débito, en cuotas</span>
                                            </label>
                                        </div>
                                        @endif

                                        <div class="form-check mb-2">
                                            <input class="form-check-input radio-metodo-pago" type="radio" name="metodoPago" id="pago-tarjeta" value="tarjeta">
                                            <label class="form-check-label" for="pago-tarjeta">
                                                <strong>Tarjeta de débito o crédito</strong> <span class="text-muted">— coordinamos el cobro al confirmar</span>
                                            </label>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input radio-metodo-pago" type="radio" name="metodoPago" id="pago-efectivo" value="efectivo">
                                            <label class="form-check-label" for="pago-efectivo">
                                                <strong>Efectivo</strong> <span class="text-muted">— al recibir el pedido o en el local</span>
                                            </label>
                                        </div>

                                        <button type="button" id="btnAddPago" class="btn btn-primary mt-2">Confirmar datos del pedido</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header shadow-sm header-content-card">
                            <strong>Resumen del Pedido</strong> 
                        </div>
                        <div class="">
                            <div class="summary-card p-4 shadow-sm">
                        
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Subtotal</span>
                                    <span id="showSubtotalPedido">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3" id="row-envio-pedido" style="display:none !important;">
                                    <span class="text-muted">Envío</span>
                                    <span id="showEnvioPedido">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3" id="row-descuento-pedido" style="display:none !important;">
                                    <span class="text-muted">Desc. transferencia</span>
                                    <span class="text-success" id="showDescuentoPedido">-$0.00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-4">
                                    <span class="fw-bold">Total</span>
                                    <span class="fw-bold" id="showTotalPedido">$0.00</span>
                                </div>

                                <button class="btn btn-primary checkout-btn w-100 mb-3" id="btnEndBuy">Finalizar compra</button>
                                <button class="btn btn-primary checkout-btn w-100 mb-3" id="btnRegisterOrder" style="display:none;">Realizar pedido</button>
                                
                                <div class="d-flex justify-content-center gap-2" id="btnKeepShopping">
                                    <i class="bi bi-shield-check text-success"></i>
                                    <a class="text-muted" href="{{url('/')}}" style="text-decoration:none;">Seguir comprando</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container" id="div-section-whatsapp" style="display:none;">
            <div class="row">
                <div class="p-5 mb-4 bg-light rounded-3">
                    <div class="container-fluid py-5 text-center">
                        <h2 class="display-5 fw-bold">¡Pedido realizado con éxito!</h2>
                        {{-- Número de pedido bien visible --}}
                        <p style="font-size:20px;margin:8px 0 2px;">Tu número de pedido es</p>
                        <p id="p-numero-pedido" style="font-size:34px;font-weight:700;color:#1B2B5A;margin-bottom:10px;">#—</p>
                        <p class="fs-4" id="p-name-customer">Gracias nombre apellido1 apellido2</p>
                        <p class="col-md-12" style="font-size:15px;color:#47536F;">Te abrimos WhatsApp con el detalle. Podés seguir el estado de tu compra en cualquier momento desde <strong>Mis pedidos</strong>.</p>
                        <button type="button" class="btn btn-lg" id="btnSendDataOrderWhatsapp" style="background-color:#5FFC7B;color:#ffffff;">
                            Enviar detalle por WhatsApp
                        </button><br>
                        <a class="btn btn-lg mt-4" href="{{ url('/cuenta/pedidos') }}" style="background:#1B2B5A;color:#fff;border-radius:999px;padding:12px 28px;">
                            <i class="fa-solid fa-box"></i> Seguir mi pedido
                        </a>
                        <a class="btn btn-light btn-lg border border-2 mt-4" href="{{url('/')}}">Continuar comprando</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('scriptEcommerce')
<script>
    window.CONFIG_WHATSAPP = @json($configPago['whatsapp'] ?? '');
    window.DESC_TRANSFERENCIA = {{ (float) ($configPago['descuento_transferencia'] ?? 0) }};
</script>
<script src="{{asset('js/ecommerce/cart-main-shopping.js')}}"></script>
<script src="{{asset('js/ecommerce/order-shopping-card.js')}}"></script>
@endsection